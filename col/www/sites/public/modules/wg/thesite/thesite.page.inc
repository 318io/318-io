<?php
function _thesite_api_meta($identifier) {
  $nid = $identifier;
  $node = node_load($nid);
  $opts = &drupal_static(__FUNCTION__);
  if (!isset($opts)) {
    $vars = _collpub_ef_variables();
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
        if($vardef['name'] == 'mediainfo') {
          foreach($v0 as $v00) {
            if($v00['value']) {
              $x = str_replace('＂', '"', $v00['value']);
              $v = json_decode($x);
            }
          }
        } else {
          switch($vardef['type']) {
            case 'text':
            case 'longtext':
              foreach($v0 as $v00) {
                if($v00['value'])
                  $v[] = $v00['value'];
              }
              break;
            case 'taxon':
              foreach($v0 as $v00) {
                $tid = $v00['tid'];
                if($tid) {
                  $term = taxonomy_term_load($tid);
                  $v[] = $term->name;
                }
              }
              break;
            default:
          }
        }
      }
    }
    if($v)  $values[$n] = $v;
  }
  $r = array(
         'id'=>$identifier,
         'file' => array(),
         'metadata' => $values,
       );

  _coll_get_feature_image($identifier); // to generate video icon
  $files = _coll_get_digifiles($identifier);
  if($files) {
    foreach($files as $file) {
      $r['file'][] = file_create_url($file);
    }
  }
  drupal_json_output($r);
  exit;
}
