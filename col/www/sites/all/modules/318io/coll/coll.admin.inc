<?php
function _coll_item_save($values, $yaml = true, $tt='') {
  $opts = &drupal_static(__FUNCTION__);

  if (!isset($opts)) {
    if($tt == 'pub') {
      $vars = _collpub_ef_variables();
    } else {
      $vars = _coll_ef_variables();
    }
    $vars = DTEF::entity_create_prepare($vars['node']);
    $entities = $vars['entity'];

    $opts['nodetype'] = key($entities);
    $opts['entity'] = current($entities);
  }

  extract($opts);
  $allowcomment = 0;
  if(array_key_exists('allowcomment', $values) && $values['allowcomment']) {
    $allowcomment = 1;
  }

  $variables = array();
  $variables['title'] = $values['field_identifier'];
  $variables['fields'] = array();

  $nid = $values['field_identifier'];
  if(!$nid) {
    $repository_id = $values['field_repository_id'];
    if($repository_id) {
      $result = db_query("SELECT r.entity_id FROM {field_data_field_repository_id} AS r WHERE r.field_repository_id_value = :repository_id", array(
                           ':repository_id' => $repository_id,
                         ));
      $nid = $result->fetchField();
    }
  }

  foreach ($values as $fn => $d) {
    $var = $entity['field'][$fn]['vardef'];
    $cb = array('DTEF', 'add_field_data_'.$var['type']);
    if(is_callable($cb)) {
      if($var['type'] == 'taxon') {
        $variables['fields'][$fn] = call_user_func($cb, $d, $var['cardinality'], $var['vocabulary']);
      } else if($var['type'] == 'longtext') {
        $d =  str_replace(COLL_NEWLINE, "\n", $d);
        $format = DT::array_get($var, 'format', null);
        $variables['fields'][$fn] = call_user_func($cb, $d, $var['cardinality'], $format);
      } else {
        $variables['fields'][$fn] = call_user_func($cb, $d, $var['cardinality']);
      }
    }
  }

  $node = DTEF::node_create($nodetype, $variables, $nid);
  if($allowcomment) $node->comment = COMMENT_NODE_OPEN;

  node_save($node);
  if($yaml) _coll_node_save_yaml($node);
  return $node;
}

function _coll_node_save_yaml($node) {
  $opts = &drupal_static(__FUNCTION__);
  if (!isset($opts)) {
    $vars = _coll_ef_variables();
    $vars = DTEF::entity_create_prepare($vars['node']);
    $entities = $vars['entity'];
    $opts['nodetype'] = key($entities);
    $opts['entity'] = current($entities);

    $opts['nodetype'] = key($entities);
    $entity = current($entities);
    foreach ($entity['field'] as $key => $v) {
      $opts['fmap'][$key] = $v['vardef']['name'];
    }
  }

  extract($opts);

  $values = array();
  $lang = 'und';

  foreach($fmap as $f => $n) {
    if(!property_exists($node, $f)) continue;
    $d = $node->$f;
    $v = array();

    if($d && array_key_exists($lang, $d)) {
      $v0 = $d['und'];
      $vardef = $entity['field'][$f]['vardef'];
      if($v0) {
        switch($vardef['type']) {
          case 'text':
          case 'longtext':
            foreach($v0 as $v00) {
              $v[] = $v00['value'];
            }
            break;
          case 'taxon':
            foreach($v0 as $v00) {
              $v[] = $v00['name'];
            }
            break;
          default:
            dbug('unknown type '.$vardef['type']);

        }
      }
    }
    $values[$n] = $v;
  }
  $identifier = $node->nid;
  $vars0 = _coll_ef_variables();
  $vars_path = $vars0['path'];

  $pathid = _coll_get_pathpart($identifier);
  $fileuri_pathpart = $vars_path['meta'].'/'.$pathid;
  $x = file_prepare_directory($fileuri_pathpart, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
  $fileuri = $vars_path['meta'].'/'.$pathid.'/'.$identifier.'.yaml';
  $s = yaml_export($values);
  file_unmanaged_save_data($s, $fileuri, FILE_EXISTS_REPLACE);
}
