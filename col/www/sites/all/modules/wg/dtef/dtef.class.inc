<?php
class DTEF {

  /*
  * taxonomy
  */
  static public function taxonomy_vocabulary_add($name, $machine_name='') {
    if(!$machine_name) {
      $machine_name = $name;
    }
    $machine_name = DT::str2var($machine_name);
    $vocabulary = taxonomy_vocabulary_machine_name_load($machine_name);
    if(!$vocabulary) {
      $vocabulary = new stdClass();
      $vocabulary->name = $name;
      $vocabulary->machine_name = $machine_name;
      taxonomy_vocabulary_save($vocabulary);
    }
    return $vocabulary;
  }

  /**
   * taxonomy_add_terms
   * @param $t
   *   as taxonomy_get_term
   * @return
   *   vocabulary object.
   *
   */
  static public function taxonomy_add_term($t, $voc) {
    $t = trim($t);
    if(empty($t)) return false;

    $vocabulary =self::taxonomy_get_vocabulary($voc);
    if(!$vocabulary) {
      dbug($t, 'zzz');
      die();
    }
    $save = false;
    $term = self::taxonomy_get_term($t, $vocabulary);

    if(!$term) {
      $term = array(
                'vid' => $vocabulary->vid,
                'name' => $t,
                'vocabulary_machine_name' => $vocabulary->machine_name,
              );
      $save = true;
    }
    $term = (object)$term;
    if(is_object($t)) {
      $t = (array)$t;
    }
    if(is_array($t) && (DT::array_get($t, 'name') != $term->name)) {
      $term->name = $t['name'];
      $save = true;
    }
    if($save) {
      $term = (object)$term;
      taxonomy_term_save($term);
    }

    return $term;
  }

  static public function taxonomy_get_vocabulary_by_vid($vid) {
    $vocabularies = taxonomy_vocabulary_load_multiple(array($vid));
    if($vocabularies) {
      $vocabulary = current($vocabularies);
    }
    return $vocabulary;
  }

  /**
   * taxonomy_get_term / taxonomy_get_terms
   *
   * @param $condition
   *   is numeric: tid
   *   is string: name and/or synonym
   * @param $vocabularies
   *   is numeric: vid
   *   is string: vocabulary name
   *   is object: vocabulary object
   *   is array: list of vids
   * @return
   *   term object.
   *
   */
  static public function taxonomy_get_term($condition, $vocabularies = array(), $load = true) {
    if(is_object($condition) && property_exists($condition, 'tid')) {
      return $condition;
    }
    $terms = self::taxonomy_get_terms($condition, $vocabularies, $load);
    if($terms) {
      return current($terms);
    } else {
      return false;
    }
  }

  static public function taxonomy_get_terms($condition, $vocabularies = array(), $load = true) {
    if(empty($condition)) {
      return false;
    }

    if(is_array($condition)) {
      if(DT::array_get($condition, 'tid')) {
        $condition = $condition['tid'];
      } else if(DT::array_get($condition, 'name')) {
        $condition = $condition['name'];
      } else {
        dbug($condition, 'taxonomy_get_terms error');
        die();
      }
    }

    if(is_numeric($condition)) {
      $tids = array($condition);
    } else {
      $args = array();
      $args[':vid'] = array();
      if(is_numeric($vocabularies)) {
        $vocabulary = self::taxonomy_get_vocabulary_by_vid($vocabularies);
        $args[':vid'][] = $vocabulary->vid;
      } else if(is_string($vocabularies)) {
        $vocabulary = taxonomy_vocabulary_machine_name_load($vocabularies);
        $args[':vid'][] = $vocabulary->vid;
      } else if(is_object($vocabularies)) {
        if(property_exists($vocabularies, 'vid')) {
          $args[':vid'][] = $vocabularies->vid;
        }
      } else {
        $args[':vid'] = $vocabularies;
      }
      $args[':name'] = $condition;

      $query = db_select('taxonomy_term_data', 't');
      $query->addField('t', 'tid');

      $query->where('t.name = :name', $args);
      if($args[':vid']) $query->where('t.vid IN (:vid)', $args);
      $tids = $query->execute()->fetchCol();
    }
    if($tids) {
      $tids = array_unique($tids);
      if($load) {
        return taxonomy_term_load_multiple($tids);
      } else {
        return $tids;
      }
    }
    return false;

  }

  /**
   * taxonomy_get_vocabulary
   * @param $condition
   *   is numeric: vid
   *   is string: vocabulary machine name
   *   is object/array: vocabulary object/array
   * @return
   *   vocabulary object.
   *
   */
  static public function taxonomy_get_vocabulary($condition) {
    if(empty($condition)) {
      return false;
    }
    if(is_numeric($condition)) {
      $vocabulary = self::taxonomy_get_vocabulary_by_vid($condition);
    } else if(is_string($condition)) {
      $vocabulary = taxonomy_vocabulary_machine_name_load($condition);
    } else if(is_object($condition)) {
      $vocabulary = $condition;
    } else {
      if($vid = DT::array_get($condition, 'vid')) {
        $vocabulary = self::taxonomy_get_vocabulary_by_vid($condition);
      } else if($machine_name = DT::array_get($condition, 'machine_name')) {
        $vocabulary = taxonomy_vocabulary_machine_name_load($machine_name);
      } else {
        return false;
      }
    }
    return $vocabulary;
  }

  /*
  * nodetype
  */
  static public function ef_create($vars) {
    $settings = self::entity_create_prepare($vars);
    self::entity_create($settings);
    return;
  }

  static public function ef_create_path($vars) {
    if(DT::array_get($vars, 'path')) {
      foreach($vars['path'] as $path) {
        file_prepare_directory($path, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
      }
    }
  }

  static public function entity_create($settings) {
    if(array_key_exists('vocabulary', $settings) && $settings['vocabulary']) {
      foreach($settings['vocabulary'] as $voc) {
        self::taxonomy_vocabulary_add($voc['name']);
      }
    }

    foreach($settings['entity'] as $entityname => $settings_entity) {
      $field_settings  = $settings_entity['field'];
      $entity = $settings_entity['entity'];

      foreach($field_settings as $fielditem) {
        $field = $fielditem['field'];
        if(is_array($field)) {
          field_create_field($field);
        } else {
          dbug_message('error def: '.$field); // todo error
        }
      }
      switch($settings_entity['vardef']['bundle']) {
        case 'node':
          self::_entity_create_node($entity, $field_settings);
          break;
        case 'taxonomy':
          self::_entity_create_taxonomy($entity, $field_settings);
          break;
      }
      continue;
    }
  }

  static private function _entity_create_node($entity, $field_settings) {
    $options = $entity['opts'];
    unset($entity['opts']);
    $content_type = node_type_set_defaults($entity);
    node_type_save($content_type);

    foreach($field_settings as $fielditem) {
      $instance = $fielditem['instance'];
      if(!array_key_exists('entity_type', $instance)) $instance['entity_type'] = 'node';
      if(!array_key_exists('bundle', $instance)) $instance['bundle'] = $entity['type'];
      field_create_instance($instance);
    }

    foreach($options as $optn => $optv) {
      $optn = $optn."_".$entity['type'];
      variable_set($optn, $optv);
    }
  }

  static private function _entity_create_taxonomy($entity, $field_settings) {
    $voc = self::taxonomy_vocabulary_add($entity['name'], $entity['machine_name']);
    $voc->description= $entity['description'];

    taxonomy_vocabulary_save($voc);

    foreach($field_settings as $fielditem) {
      $instance = $fielditem['instance'];
      if(!array_key_exists('bundle', $instance)) $instance['bundle'] = $entity['type'];
      field_create_instance($instance);
    }
  }

  static public function entity_create_prepare($vars) {
    $settings = array();
    $settings = array();
    foreach ($vars as $vars_item) {
      switch($vars_item['bundle']) {
        case 'node':
          self::_entity_create_prepare_node($vars_item, $settings);
          break;
        case 'taxonomy':
          self::_entity_create_prepare_taxonomy($vars_item, $settings);
          break;
      }
    }
    return $settings;
  }

  static private function _entity_create_prepare_node($vars_item, &$settings) {
    $nodetype = $vars_item['entity'];
    $n = $nodetype['name'];
    DT::array_set($nodetype, 'label', $n);
    $n = preg_replace('%[^a-z0-9_]%i', '_', $n);
    $entity = array(
                'type' => $n,
                'name' => $n,
                'base' => 'node_content',
                'description' => $nodetype['description'],
                'custom' => 1,
                'modified' => 1,
                'locked' => 0,
                'opts' => array(),
              );

    if(DT::array_get($nodetype, 'not_published')) {
      // todo
    } else {
      if(DT::array_get($nodetype, 'promote')) {
        $entity['opts']['node_options'] = array('status', 'promote');
      } else {
        $entity['opts']['node_options'] = array('status');
      }
    }
    if(module_exists('comment') && (DT::array_get($nodetype, 'comment') == 'hidden')) {
      $entity['opts']['comment'] = COMMENT_NODE_HIDDEN;
    }

    if(DT::array_get($nodetype, 'display_author')) {
      $entity['opts']['node_submitted'] = TRUE;
    } else {
      $entity['opts']['node_submitted'] = FALSE;
    }

    $entity_fields = array();
    if(array_key_exists('field', $vars_item)) {
      foreach($vars_item['field'] as $field) {
        $field = self::_apply_field_normalize($field, null, 'node');
        $entity_fields[$field['field']] = $field;
        if(array_key_exists('child', $field)) {
          foreach ($field['child'] as $field_child) {
            $field_child = self::_apply_field_normalize($field_child, $field, 'node');
            $entity_fields[$field_child['field']] = $field_child;
          }
          unset($entity_fields[$field['field']]['child']);
        }
      }
      foreach($entity_fields as $key => $field) {
        if(array_key_exists('type', $field)) {
          $field = self::_field_setting($field['type'], $field, $settings, $n, 'node');
        }
        $entity_fields[$key] = $field;
      }
    }

    $settings['entity'][$n] = array(
                                'entity' => $entity,
                                'field'  => $entity_fields,
                                'vardef' => $vars_item,
                              );
  }

  static private function _entity_create_prepare_taxonomy($vars_item, &$settings) {
    $e = $vars_item['entity'];
    $n = $e['name'];
    DT::array_set($e, 'label', $n);
    $n = preg_replace('%[^a-z0-9_]%i', '_', $n);
    $entity = array(
                'name' => $e['label'],
                'machine_name' => $n,
                'description' => $e['description'],
                'hierarchy' => '0',
                'module' => 'taxonomy',
                'weight' => '0',
                'bc_entity_type' => 'taxonomy_term',
              );

    $entity_fields = array();
    if(array_key_exists('field', $vars_item)) {
      foreach($vars_item['field'] as $field) {
        $field = self::_apply_field_normalize($field, null, 'taxonomy_term');
        $entity_fields[$field['field']] = $field;
        if(array_key_exists('child', $field)) {
          foreach ($field['child'] as $field_child) {
            $field_child = self::_apply_field_normalize($field_child, $field, 'taxonomy_term');
            $entity_fields[$field_child['field']] = $field_child;
          }
          unset($entity_fields[$field['field']]['child']);
        }
      }
      foreach($entity_fields as $key => $field) {
        if(array_key_exists('type', $field)) {
          $field = self::_field_setting($field['type'], $field, $settings, $n, 'taxonomy_term');
        }
        $entity_fields[$key] = $field;
      }
    }

    $settings['entity'][$n] = array(
                                'entity' => $entity,
                                'field'  => $entity_fields,
                                'vardef' => $vars_item,
                              );
  }

  static private function _apply_field_normalize($field, $parent = null, $entity_type) {
    DT::array_set($field, 'field', 'field_'.$field['name']);
    DT::array_set($field, 'cardinality', 1);
    DT::array_set($field, 'sslabel', $field['label']);
    if($parent) {
      if($parent['type'] == 'field_collection') {
        DT::array_set($field, 'entity_type', 'field_collection_item');
        DT::array_set($field, 'bundle', $parent['field']);
        DT::array_set($field, 'bundles', array('field_collection_item' => array($parent['field'])));
      }
    } else {
      DT::array_set($field, 'entity_type', $entity_type);
    }
    return $field;
  }

  static public function _field_setting_general($var, $settings, $entity_name, $bundle) {
    $r =
      array(
        'field'=>array(
          'field_name' => $var['field'],
          'cardinality' => $var['cardinality'],

          'translatable' => '0',
          'entity_types' => array(),
          'active' => '1',
          'locked' => '0',
          'deleted' => '0',
        ),
        'instance' => array(
          'field_name' => $var['field'],
          'label' => t($var['label']),
          'sslabel' => $var['sslabel'],
          'display' => array(
            'teaser' => array('type' => 'hidden'),
            'default' => array('label' => 'inline'),
          ),
          'required' => 0,
          'description' => '',
          'default_value' => NULL,
          'deleted' => '0',
        ),
        'vardef' => $var,
      );
    $entity_names = array($entity_name);
    $r['field']['bundles'] = DT::array_get($var, 'bundles',  array($bundle => $entity_names));
    $r['instance']['entity_type'] = DT::array_get($var, 'entity_type', $bundle);
    $entity_name = DT::array_get_first($entity_names);
    $r['instance']['bundle'] = DT::array_get($var, 'bundle', $entity_name);
    return $r;
  }

  static public function _field_setting($type, $field, &$settings, $entity_name, $bundle) {
    $cb = 'self::_field_setting_'.$type;
    if(is_callable($cb)) {
      $args = array($field, &$settings, $entity_name, $bundle);
      $field = call_user_func_array($cb, $args);
    }
    return $field;
  }

  static private function _field_setting_text($var, &$settings, $entity_name, $bundle) {
    $r = self::_field_setting_general($var, $settings, $entity_name, $bundle);
    $r['field']['module'] = 'text';
    $r['field']['type'] = 'text';
    $r['field']['settings'] =  array('max_length' => '255',);
    return $r;
  }

  static private function _field_setting_longtext($var, &$settings, $entity_name, $bundle) {
    $r = self::_field_setting_general($var, $settings, $entity_name, $bundle);
    $r['field']['module'] = 'text';
    $r['field']['type'] = 'text_long';
    $r['field']['settings'] = array();
    return $r;
  }

  static private function _field_setting_number($var, &$settings, $entity_name, $bundle) {
    $r = self::_field_setting_general($var, $settings, $entity_name, $bundle);
    $r['field']['module'] = 'number';
    $r['field']['type'] = 'number_integer';
    $r['field']['settings'] = array();
    return $r;
  }

  static private function _field_setting_taxon($var, &$settings, $entity_name, $bundle) {
    if(!array_key_exists('vocabulary', $settings)) $settings['vocabulary'] = array();
    $settings['vocabulary'][$var['vocabulary']] = array('name'=>$var['vocabulary']);

    $r = self::_field_setting_general($var, $settings, $entity_name, $bundle);
    $r['field']['module'] = 'taxonomy';
    $r['field']['type'] = 'taxonomy_term_reference';
    $r['field']['settings'] = array(
                                'allowed_values' => array(
                                  array(
                                    'vocabulary' => $var['vocabulary'],
                                    'parent' => '0',
                                  ),
                                ),
                              );
    $r['instance']['widget'] = array(
                                 'type' => 'taxonomy_autocomplete',
                                 'module' => 'taxonomy',
                                 'active' => 0,
                                 'settings' => array(
                                   'size' => 60,
                                   'autocomplete_path' => 'taxonomy/autocomplete',
                                 )
                               );
    return $r;
  }

  static private function _field_setting_image($var, &$settings, $entity_name, $bundle) {
    $r = self::_field_setting_general($var, $settings, $entity_name, $bundle);
    $r['field']['module'] = 'image';
    $r['field']['type'] = 'image';
    $r['field']['settings'] = array(
                                'uri_scheme' => 'public',
                                'default_image' => 0,
                              );
    return $r;
  }

  static private function _field_setting_field_collection($var, &$settings, $entity_name, $bundle) {
    $r = self::_field_setting_general($var, $settings, $entity_name, $bundle);
    $r['field']['module'] = 'field_collection';
    $r['field']['type'] = 'field_collection';
    $r['instance']['widget'] = array(
                                 'type' => 'field_collection_embed',
                                 'module' => 'field_collection',
                                 'active' => 0,
                                 'settings' => array(),
                               );
    return $r;
  }

  static private function _add_field_data_general($d, $cardinality = 1) {
    $r = array();
    if(is_string($d)) {
      if($cardinality == 1) {
        $r[] = array('value' => $d );
      } else {
        $d = str_replace(array("\r\n", "\n", '；'), ';', $d);
        $ar = explode(';', $d);
        foreach($ar as $item) {
          $item = trim($item);
          if($item)
            $r[] = array('value' => $item );
        }
      }
    }
    elseif(is_array($d)) {
      $ar = $d;
      foreach($ar as $item) {
        $item = trim($item);
        if($item)
          $r[] = array('value' => $item );
      }
    }
    return $r;
  }

  static public function add_field_data_text($d, $cardinality = 1) {
    $r = self::_add_field_data_general($d, $cardinality);
    return $r;
  }

  static public function add_field_data_longtext($d, $cardinality = 1, $format = null) {
    $r = array();
    if(is_string($d)) {
      if($cardinality == 1) {
        $r[] = array('value' => $d, 'format' => $format);
      } else {
        $d = str_replace(array("\r\n", "\n", '；'), ';', $d);
        $ar = explode(';', $d);
        foreach($ar as $item) {
          $item = trim($item);
          if($item)
            $r[] = array('value' => $item, 'format' => $format);
        }
      }
    }
    elseif(is_array($d)) {
      $ar = $d;
      foreach($ar as $item) {
        $item = trim($item);
        if($item)
          $r[] = array('value' => $item, 'format' => $format);
      }
    }
    return $r;
  }

  static public function add_field_data_number($d, $cardinality = 1) {
    $r = self::_add_field_data_general($d, $cardinality);
    return $r;
  }

  static public function add_field_data_taxon($taxonstr, $cardinality = 1, $voc) {
    $r = array();
    $taxonstr = str_replace('、', ';', $taxonstr);
    $ar2 = explode(';', $taxonstr);
    foreach($ar2 as $s) {
      $s = trim($s);
      $term = self::taxonomy_add_term($s, $voc);
      if($term) {
        $term = (array)$term;
        $r[] = $term;
      }
    }
    return $r;
  }

  static public function attach_field_collection($node, $fc_field, $values) {
    module_load_include('inc', 'entity', 'includes/entity.controller');
    $fc_values = array(
                   'field_name' => $fc_field,
                 );
    foreach($values as $n=>$v) {
      $fc_values[$n] = array(LANGUAGE_NONE => $v);
    }
    $entity = entity_create('field_collection_item', $fc_values);
    $entity->setHostEntity('node', $node);
    $entity->save();
  }

  static public function get_node_by_title($s) {
    $c = db_query('SELECT nid FROM {node} WHERE title = :title', array(':title'=>$s))->fetchField();
    return $c;
  }

  static public function node_exists($nid) {
    $c = db_query('SELECT COUNT(*) FROM {node} WHERE nid = :nid', array(':nid'=>$nid))->fetchField();
    return $c;
  }

  static public function node_create($nodetype, $variables = array(), $nid = false) {
    $default = array(
                 'language' => 'und',
                 'fields' => array(),
                 'format' => 'plaintext',
                 'title' => 'notitle',
                 'sticky' => 0,
               );
    $variables = array_merge($default, $variables);
    if($nid) $variables['nid' ] =$nid;

    if(empty($variables['title'])) $variables['title'] = 'notitle';
    $fields = $variables['fields'];
    unset($variables['fields']);
    $format = $variables['format'];
    unset($variables['format']);
    $language = $variables['language'];

    $node = null;

    if($nid = DT::array_get($variables, 'nid')) {
      if(self::node_exists($nid)) {
        $node = node_load($nid);
      }
    }
    if($node) {
      $node->is_new = FALSE;
    } else {
      $node = new stdClass();
      $node->is_new = TRUE;
    }
    $node->type = $nodetype;

    if($fields) {
      foreach($fields as $field_name=>$field_values) {
        if(!is_array($field_values)) {
          $node->$field_name = array($language=>array(0=>array('value'=>$field_values)));
        } else {
          if(empty($field_values)) {
            $node->$field_name = array($language=>array());
          }
          elseif(is_numeric(key($field_values))) {
            if(!is_array($field_values[0])) {
              $v2 = array();
              foreach($field_values as $field_value) {
                $v2[] = array('value'=>$field_value);
              }
              $node->$field_name = array($language=>$v2);

            } else {
              $node->$field_name = array($language=>$field_values);
            }
          }
          else {
            $node->$field_name = array($language=>array($field_values));
          }
        }
      }
    }
    unset($variables['fields']);
    foreach($variables as $k=>$v) {
      $node->$k = $v;
    }

    return $node;
  }
}
