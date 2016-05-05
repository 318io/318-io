<?php

function _get_field_instance($bundle_name, $field_name) {
  return field_info_instance('node', $field_name , $bundle_name);
}

function get_taxon_subtype($field_name) {
  $subtype_def = array(
    'field_tagtag'   => 'tag',   // subtype 設定為 tag 的欄位才可以新增新的 tag, 否則只能選擇
    'field_material' => 'tag',
    'field_public'       => 'boolean',
    'field_privacy'      => 'boolean',
    'field_release'      => 'boolean',
    'field_high_resolution' => 'boolean',
  );

  if(array_key_exists($field_name, $subtype_def)) return $subtype_def[$field_name];
  else                                            return 'category';
}

function get_text_subtype($field_name) {
  $subtype_def = array(
    'field_collected_time' => 'date',
    'field_created_time'   => 'date',
    'field_digi_time'      => 'date',
  );

  if(array_key_exists($field_name, $subtype_def)) return $subtype_def[$field_name];
  else                                            return 'text';
}


function get_active($field_name) {
  $disable_def = array(
    'field_identifier'    => 0,
    'field_recorded_time' => 0,
    'field_updated_time'  => 0,
  );
  if(array_key_exists($field_name, $disable_def)) return 'disable';
  else                                            return 'enable';
}


function get_fields($bundle_name) {

  $fields = field_read_fields(array('entity_type' => 'node', 'bundle' => $bundle_name));

  $result = array();

  foreach($fields as $name => $field) {

    $result[$name] = array('type' => $field['type'],
                           'card' => $field['cardinality']); // FIELD_CARDINALITY_UNLIMITED = -1

    $f_type = trim($field['type']);

    $vocab = ""; $sub = ""; $active = "";

    if($f_type == 'taxonomy_term_reference') {
      $vocab = $field['settings']['allowed_values'][0]['vocabulary'];
      if(!empty($vocab)) $result[$name]['vocab'] = $vocab;

      $sub   = empty($field['settings']['subtype']) ? get_taxon_subtype($name) : $field['settings']['subtype'];
      //$sub   = $field['settings']['subtype'];
      //if(empty($sub)) $sub = get_taxon_subtype($name);
    }

    if($f_type == 'text') {
      $sub = empty($field['settings']['subtype']) ? get_text_subtype($name) : $field['settings']['subtype'];
      //if(empty($sub)) $sub = get_text_subtype($name);
    }

    $result[$name]['subtype'] = $sub;

    // for every field
    $active_value = empty($field['settings']['active']) ? get_active($name) : $field['settings']['active'];
    //$active_value = $field['settings']['active'];
    //if(empty($active_value)) $active_value = get_active($name);

    if($active_value == 'enable') $result[$name]['active'] = true;
    else                          $result[$name]['active'] = false;

    $instance = _get_field_instance($bundle_name, $name);
    $result[$name]['label'] = $instance['label'];
  }
  //mylog(print_r($fields, true), 'fields.txt');
  //mylog(print_r($result, true), 'result.txt');

  return $result;
}

// text_filed('first_name', 'First Name', true, 1, 'width: 300px')
function _text_field($name, $caption, $subtype, $active, $require, $card, $style, $init = 1) {
  $_style = 'style="' . $style . '"';
  $attr = $_style; // default attributes

  $_init = 'init_num="' . $init . '"';

  switch($card) {
    case FIELD_CARDINALITY_UNLIMITED:
      $attr = 'class="multiple_field" max_num="999" ' . $_init . ' ' . $_style;
      break;
    default:
      if($card > 1) $attr = 'class="multiple_field" max_num="' . $card . '" ' . $_init . ' ' . $_style;
      break;
  }

  if(!$active) $attr .= ' disabled';

  $text_field = array(
    'name' => $name,
    'type' => $subtype,
    'require' => $require,
    'html' => array(
      'caption' => $caption,
      'attr' => $attr,
    ),
  );
  return $text_field;
}

// textarea_field('comment', 'Comment', true, 'width: 300px; height: 90px')
function _textarea_field($name, $caption, $active, $require, $card, $style, $init = 1) {
  $_style = 'style="' . $style . '"';
  $attr = $_style; // default attributes

  $_init = 'init_num="' . $init . '"';

  switch($card) {
    case FIELD_CARDINALITY_UNLIMITED:
      $attr = 'class="multiple_field" max_num="999" ' . $_init . ' ' . $_style;
      break;
    default:
      if($card > 1) $attr = 'class="multiple_field" max_num="' . $card . '" ' . $_init . ' ' . $_style;
      break;
  }

  if(!$active) $attr .= ' disabled';

  $textarea_field = array(
    'name' => $name,
    'type' => 'textarea',
    'require' => $require,
    'html' => array(
      'caption' => $caption,
      'attr' => $attr,
    ),
  );
  return $textarea_field;
}


function _get_term_id_array_by_vid($vocab_id) {
  $records = db_query("SELECT tid FROM taxonomy_term_data where vid = $vocab_id");
  $ids = array();
  foreach($records as $record) { $ids[] = $record->tid; }
  return $ids;
}


/*
  return following json structure:

  { name: 'taxon', type: 'enum', required: false,
    options: {
      items: ['hello', {id: 1, text:'world'}],
      openOnFocus: true,
      markSearch: true,
      onNew: function (event) { event.item.style = 'background-color: rgb(255, 232, 232); border: 1px solid red;';}
    },
    html: { caption: 'Taxonomy' }
  }
*/
function _taxonomy_field($name, $vocab_name, $caption, $subtype, $active, $require, $card) {

  $vocab = taxonomy_vocabulary_machine_name_load($vocab_name);

  $items = array();
  if(!empty($vocab)) {
    $terms = taxonomy_term_load_multiple(_get_term_id_array_by_vid($vocab->vid));
    foreach($terms as $tid => $term) {
      $items[] = array('id' => $tid, 'text' => $term->name);
    }
  }

  $max = $card;
  if($card == FIELD_CARDINALITY_UNLIMITED) $max = 0;

  $attr = '';
  if(!$active) $attr .= ' disabled';

  if($subtype == 'category' || $subtype == 'tag') {
    $taxon_field = array(
      'name'    => $name,
      'type'    => 'enum',
      'require' => $require,
      'options' => array(
        'items' => $items,
        'max'   => $max,
        'openOnFocus' => true,
        'markSearch'  => true,
      ),
      'html' => array(
        'caption' => $caption,
        'attr' => $attr
      )
    );

    if($subtype == 'tag')
      $taxon_field['options']['onNew'] = 'function(event) { event.item.style = "background-color: rgb(255, 232, 232); border: 1px solid red;";}';
  } else {  // boolean

    $taxon_field = array(
      'name'  => $name,
      'type'  => 'toggle',
      'require' => $require,
      'html' => array(
        'caption' => $caption,
      )
    );
  }

  return $taxon_field;
}


/*
   field types: https://www.drupal.org/node/1879542
    - file
    - image
    - taxonomy_term_reference
    - Lists
      - list_boolean
      - list_float
      - list_integer
      - list_text
    - Number
      - number_decimal
      - number_float
      - number_integer
    - Text
      - text
      - text_long
      - text_with_summary
*/
function _form_model($bundle_name, $node = NULL) {

  $fields = get_fields($bundle_name);

  $models = array();

  foreach($fields as $name => $field) {

    switch($field['type']) {
      case 'text':
           if(isset($node) && !empty($node->{$name})) $count = count($node->{$name}['und']);
           else                                       $count = 1;
           $models[] = _text_field($name, $field['label'], $field['subtype'], $field['active'], true, $field['card'], 'width: 300px', $count);
           break;
      case 'text_long':
      case 'text_with_summary':
           if(isset($node) && !empty($node->{$name})) $count = count($node->{$name}['und']);
           else                                       $count = 1;
           $models[] = _textarea_field($name, $field['label'], $field['active'], true, $field['card'], 'width: 300px; height: 90px', $count);
           break;
      case 'taxonomy_term_reference':
           $models[] = _taxonomy_field($name, $field['vocab'], $field['label'], $field['subtype'], $field['active'], true, $field['card']);
           break;
      default:
           break;
    }
  }
  return $models;
}

// $d_string === 20140414
function to_w2ui_date($d_string) {
  $pattern = '/^(\d{4,4})(\d{2,2})(\d{2,2})$/';
  preg_match($pattern, $d_string, $m);
  $return = $d_string;
  if(!empty($m)) {
    $return = $m[2] . '/' . $m[3] . '/' . $m[1];
  }
  return $return;
}

/*
  transform node object to w2ui form record

  $node object

  // text field
  $node->job_post_company['und'][0]['value']

  // for taxonomy term
  $term_id_1 = $node->field_term['und'][0]['tid'];
  $term_id_2 = $node->field_term['und'][1]['tid'];
*/
function _get_form_record($bundle_name, $node) {

  $fields = get_fields($bundle_name);

  $record = array();

  //$node_json = json_encode($node);
  //mylog($node_json, 'raw_node.txt');

  foreach($fields as $name => $field) {

    if(empty($node->{$name})) continue;

    switch($field['type']) {
      case 'text':
      case 'text_long':
      case 'text_with_summary':
           //if(count($node->{$name} == 0)) { drupal_set_message($name); break; }
           $card = $field['card'];
           if($card > 1 || $card == FIELD_CARDINALITY_UNLIMITED) { // multiple field 多值的處理
             foreach($node->{$name}['und'] as $key => $_field) {
               // $_field['value'];
               $new_key = $name . '[' . $key . ']';
               $record[$new_key] = $_field['value'];
             }
           } else {   // single field
             if($field['subtype'] == 'date')
               $record[$name] = to_w2ui_date($node->{$name}['und'][0]['value']);
             else
               $record[$name] = $node->{$name}['und'][0]['value'];
           }
           break;
      case 'taxonomy_term_reference':

           if($field['subtype'] == 'boolean') {
             $tid = $node->{$name}['und'][0]['tid'];
             $term = taxonomy_term_load($tid);
             if($term->name == '是') $value = 1;
             else                    $value = 0; // default false
             $record[$name] = $value;
           } else {
             if(count($node->{$name}) == 0) break;
             foreach($node->{$name}['und'] as $key => $_field) {
               $tid = $_field['tid'];
               $term = taxonomy_term_load($tid);
               $record[$name][] = array('id' => $tid, 'text' => $term->name);
             }
           }
           break;
      default:
           break;
    }
  }

  $props = get_object_vars($node);

  foreach($props as $prop => $value) {
    if(!_is_field($prop)) $record[$prop] = $value; // save node propertis
  }

  $record_obj = new stdClass();
  foreach($record as $k => $v) { $record_obj->$k = $v; }

/*
  if(isset($node->nid)) $record_obj->nid = $node->nid;
  if(isset($node->vid)) $record_obj->vid = $node->vid;
  if(isset($node->title)) $record_obj->title = $node->title;
  if(isset($node->uid)) $record_obj->uid = $node->uid;
  if(isset($node->status)) $record_obj->status = $node->status;
  if(isset($node->comment)) $record_obj->comment = $node->comment;
  if(isset($node->sticky)) $record_obj->sticky = $node->sticky;
  if(isset($node->type)) $record_obj->type = $node->type;
  if(isset($node->language)) $record_obj->language = $node->language;
  if(isset($node->created)) $record_obj->created = $node->created;
  if(isset($node->changed)) $record_obj->changed = $node->changed;
  if(isset($node->tnid)) $record_obj->tnid = $node->tnid;
  if(isset($node->translate)) $record_obj->translate = $node->translate;
  if(isset($node->revision_timestamp)) $record_obj->revision_timestamp = $node->revision_timestamp;
  if(isset($node->revision_uid)) $record_obj->revision_uid = $node->revision_uid;
  if(isset($node->name)) $record_obj->name = $node->name;
  if(isset($node->picture)) $record_obj->picture = $node->picture;
  if(isset($node->data)) $record_obj->data = $node->data;
  if(isset($node->timestamp)) $record_obj->timestamp = $node->timestamp;
*/
  return $record_obj;
}

function _get_vocab_id_from_taxon_field($bundle_name, $field_name) {
  $fields = get_fields($bundle_name);

  foreach($fields as $name => $field) {
    switch($field['type']) {
      case 'taxonomy_term_reference':
           if($name == $field_name) {
             $vocab = taxonomy_vocabulary_machine_name_load($field['vocab']);
             if(!empty($vocab)) {
               return $vocab->vid;
             }
           }
           break;
    }
  }
  return FALSE;
}


function _create_new_term_for_taxon_field($bundle_name, $field_name, $term_name) {
  $vid = _get_vocab_id_from_taxon_field($bundle_name, $field_name);

  if(!$vid) return FALSE;

  $new_term = array(
    'vid' => $vid,
    'name' => $term_name
  );
  taxonomy_save_term($new_term);

  return $new_term['tid'];
}

/*
   Prepare object for saving to Drupal.
   https://api.drupal.org/api/drupal/modules%21node%21node.module/function/node_object_prepare/7
   $node = new stdClass();
   $node->type = “forum_post”;
   $node->title = ?
   $node->body  = ?
   $node->forum_post_taxonomy['und'][0..N]['tid'] = ?
   $node->field['und'][0..N][value] = ?

   node_object_prepare($node);

   Save the prepared object to Drupal.
   node_save($node); // The $node object to be saved. If $node->nid is omitted (or $node->is_new is TRUE), a new node will be added.
*/
function _create_node_from_normalized_json($normalized_json, $bundle_name, $title) {

  $node = new stdClass();
  //$node->type = $bundle_name;
  //$node->title = $title;
  //$node->language = LANGUAGE_NONE; // == 'und'
  //$node->status = 1; // published

  foreach($normalized_json as $name => $field) {
    $type = $field['type'];
    unset($field['type']);

    switch($type) {
      case 'text':
           foreach($field as $k => $v) {
             $node->{$name}[LANGUAGE_NONE][$k]['value'] = $v;
             //$node->$name = array(LANGUAGE_NONE => array($k => array('value' => $v)));
           }
           break;
      case 'property':
           $node->$name = $field[0];
           break;
      case 'taxonomy':
           foreach($field as $k => $v) {
             $tid = $v['id'];
             if($v['id'] == $v['text']) { $tid = _create_new_term_for_taxon_field($bundle_name, $name, $v['text']); }
             $node->{$name}[LANGUAGE_NONE][$k]['tid'] = $tid;
             //$node->$name = array(LANGUAGE_NONE => array( $k => array('tid' => $tid)));
           }
           break;
      default:
           break;
    }
  }

  node_object_prepare($node);
  return $node;
}


/*
  raw_json:
    Array
    (
        [nid] => 1001
        [first_name[0]] => Harry
        [last_name[0]] => Chang
        [taxon] => Array
            (
                [0] => Array
                    (
                        [id] => hello
                        [text] => hello
                    )

                [1] => Array
                    (
                        [id] => test
                        [text] => test
                        [style] => background-color: rgb(255, 232, 232); border: 1p
                    )

            )

    )

  normalized_json:

    Array
    (
        [nid] => Array
            (
               [type] => property
               [0] => 1001
            )
        [first_name] => Array
            (
                [type] => text
                [0] => Harry
            )

        [last_name] => Array
            (
                [type] => text
                [0] => Chang
            )

        [age] => Array
            (
                [type] => text
                [0] => 13
            )

        [taxon] => Array
            (
                [0] => Array
                    (
                        [id] => test
                        [text] => test
                        [style] => background-color: rgb(255, 232, 232); border: 1p
                    )

                [type] => taxonomy
            )

    )
*/
function _normalize_form_json($raw_json) {
  $pattern = '/([a-zA-Z0-9\-_]+)\[[0-9]+\]/';
  //$field_pattern = '/field_[a-zA-Z0-9_-]+/';
  $njson = array();
  foreach($raw_json as $field_name => $field_value) {
    preg_match($pattern, $field_name, $matches);
    if(empty($matches)) {
      if(is_array($field_value)) {
        $field_value['type'] = 'taxonomy';
        $njson[$field_name] = $field_value;
      } else {
        if(_is_field($field_name)) {
          $new_value = array('type' => 'text', '0' => $field_value); // field value
        } else {
          $new_value = array('type' => 'property', '0' => $field_value);  // node property
        }
        $njson[$field_name] = $new_value;
      }
    } else {
      $name = $matches[1];
      $njson[$name]['type'] = 'text';
      $njson[$name][] = $field_value;
    }
  }
  return $njson;
}

function get_taxon_fields($bundle_name) {
  //$fields = field_read_fields(array('entity_type' => 'node', 'bundle' => $bundle_name));
  $fields = get_fields($bundle_name);
  $result = array();
  foreach($fields as $name => $field) {
    if($field['type'] == 'taxonomy_term_reference') $result[$name] = $field['subtype'];
  }
  return $result;
}


function get_taxon_boolean_fields($bundle_name) {
  $taxon_fields = get_taxon_fields($bundle_name);
  $result = array();
  foreach($taxon_fields as $name => $subtype) {
    if($subtype == 'boolean') $result[$name] = null;
  }
  return $result;
}

function is_taxon_field($name) {
  $tb_fields_names = get_taxon_fields('collection');
  return array_key_exists($name, $tb_fields_names);
}

function is_taxon_boolean_field($name) {
  $tb_fields_names = get_taxon_boolean_fields('collection');
  return array_key_exists($name, $tb_fields_names);
}

function _is_field($name) {
  $field_pattern = '/field_[a-zA-Z0-9_-]+/';
  preg_match($field_pattern, $name, $match);
  if(empty($match)) return FALSE;
  else              return TRUE;
}

/*
  subtype in the setting is considered

  raw json

    Array
    (
        [nid] => 1001
        [first_name[0]] => Harry
        [last_name[0]] => Chang
        [taxon] => Array
            (
                [0] => Array
                    (
                        [id] => hello
                        [text] => hello
                    )

                [1] => Array
                    (
                        [id] => test
                        [text] => test
                        [style] => background-color: rgb(255, 232, 232); border: 1p
                    )

            ),
        [taxon_boolean] = empty or 1
    )
*/
function _normalize_for_collection_save($raw_json) {
  $pattern = '/([a-zA-Z0-9\-_]+)\[[0-9]+\]/';
  $njson = array();

  foreach($raw_json as $field_name => $field_value) {

    preg_match($pattern, $field_name, $matches);

    if(empty($matches)) {                 // normal field name, not array type
      if(is_taxon_field($field_name)) {
        if(is_array($field_value)) {
          if(count($field_value) == 0) {
            $njson[$field_name][] = '';
          } else {
            foreach($field_value as $index => $term_arr) {
              $njson[$field_name][] = $term_arr['text'];
            }
          }
        } else if(is_taxon_boolean_field($field_name)) {  // taxon boolean
          if(empty($field_value) || $field_value == 0) $njson[$field_name] = '否';
          else                                         $njson[$field_name] = '是';
        } else {
          mylog('_normalize_for_collection_save(): fatal error.', 'form_model.php.error.txt');
        }
      } else {
        $njson[$field_name] = $field_value;
      }
    } else {                              // array type field name blabla[0..9]+
      $name = $matches[1];
      $njson[$name][] = $field_value;
    }
  }

  $result = array();
  foreach($njson as $name => $value) {
    if(is_array($value)) $result[$name] = implode(';', $value);
    else                 $result[$name] = $value;
  }

  return $result;
}
