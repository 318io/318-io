<?php

function mylog($data, $file_name) {
  if (file_destination('public://log', FILE_EXISTS_ERROR)) {
    // The file doesn't exist. do something
    drupal_mkdir('public://log');
  }
  file_save_data(print_r($data,true), "public://log/$file_name", FILE_EXISTS_REPLACE);
}

/*
  $extra = Array(
     'field1' => 'value1',
     'field2' => 'value2'
  );

*/
function new_empty_node($title, $bundle_type, $extra = NULL, $lang = LANGUAGE_NONE) {
  $node = new stdClass();
  $node->type     = $bundle_type;
  $node->language = $lang;  // und
  $node->status   = 1;      // published
  $node->is_new   = true;
  $node->title    = $title;

  if(!empty($extra)) { foreach($extra as $k => $v) { $node->{$k} = $v; } }

  node_object_prepare($node);
  node_save($node);
  ft_table_insert($node); // defined in expsearch.admin.inc
  return $node;

  /*
  $records = db_query("SELECT max(nid) as nid FROM node");
  $nid = 0;
  foreach($records as $record) { $nid = $record->nid; }
  return $nid;
  */
}
