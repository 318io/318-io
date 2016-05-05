<?php

/*
 table creation query

 CREATE TABLE ft_table (
    ->   id INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    ->   content TEXT,
    -> )
*/
function _build_ftsearch_schema() {
  return array(
           'description' => 'Table for full text search',
           'fields' => array(
             'id' => array('type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE),
             'nid' => array('type' => 'int', 'unsigned' => TRUE, 'not null' => TRUE),
             'content' => array('type' => 'text')
           ),
           'primary key' => array('id')
         );
}

function _create_ftsearch_table($table_name) {
  db_drop_table($table_name);
  $schema = _build_ftsearch_schema();
  db_create_table($table_name, $schema);
}

function _expsearch_get_field_label($field_name) {
  $instance = field_info_instance('node', $field_name , 'collection');
  return $instance['label'];
}

function _expsearch_admin_settings_ft_field_options() {
  $fields = field_read_fields(array('entity_type' => 'node', 'bundle' => 'collection'));
  foreach($fields as $name => $field) {
    if($name == 'field_mediainfo') continue; // never index mediainfo

    $label = _expsearch_get_field_label($name);

    $ftype = $field['type'];
    if($ftype == 'text_with_summary' || $ftype == 'text_long' || $ftype == 'text') $options[$name] = $label ;

    if($ftype == 'taxonomy_term_reference') $options['taxo_'.$name] = $label;
  }
  return $options;
}

function _expsearch_admin_settings_index_table() {
  $conf_path = conf_path();
  if($conf_path == 'sites/318_public') {
    $index_table = 0;
    $index_table_name = 'idx318public';
  } else {
    $index_table = 1;
    $index_table_name = 'idx318archive';
  }
  variable_set('index_table', $index_table);
  return $index_table_name;
}

function _expsearch_admin_settings_form($form, &$form_state) {
  $options = _expsearch_admin_settings_ft_field_options();
  $ft_columns = variable_get('ft_columns', array_keys($options));
  $form['expsearch_fields'] = array(
                                '#type' => 'checkboxes',
                                '#title' => t('Fileds for full text search.'),
                                '#options' => $options,
                                '#default_value' => $ft_columns,
                              );

  $index_table_name = _expsearch_admin_settings_index_table();
  $form['expsearch_index'] =
    array(
      '#markup' => t('<p>indextable is :index_table</p>', array(':index_table'=>$index_table_name)),
    );

  $form['submit'] =
    array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );
  return $form;
}

function _expsearch_admin_settings_form_submit($form, &$form_state) {
  $ft_columns = array();
  foreach($form_state['values']['expsearch_fields'] as $key => $value) {
    if($value) $ft_columns[] = $value;
  }
  variable_set('ft_columns', $ft_columns);
  $msg = 'configuration saved! to (re)build index, please visit <a href="/admin/config/coll/expsearch_build_ft">/admin/config/coll/expsearch_build_ft</a>)';
  drupal_set_message($msg);
}

function _expsearch_admin_build_ft_form($form, &$form_state) {
  //variable_set('ft_columns', null);
  $ft_columns = variable_get('ft_columns', null);

  if(!$ft_columns) {
    drupal_set_message('please set Fileds for full text search', 'warning');
    drupal_goto('admin/config/coll/expsearch_settings');
  }

  $form['submit'] =
    array(
      '#type' => 'submit',
      '#value' => t('Build'),
    );
  return $form;
}

/*
 * 2015.11.19 add
 * NO batch UI for building full-text search table.
 */
function _expsearch_build_ft() {

  _create_ftsearch_table('ft_table'); // drop the old, create a new

  // insert all nodes
  $nodes = node_load_multiple(_node_id_array());
  //$values = array();
  foreach($nodes as $node) {
    $for_insert = _prepare_for_insertion($node);
    if(!empty($for_insert)) {
      db_insert('ft_table')->fields($for_insert)->execute();
      //$values[] = $for_insert;
    }
  }

  /*
  $query = db_insert('ft_table')->fields(array('id', 'nid', 'content'));
  foreach ($values as $value) {
    $query->values($value);
  }
  $query->execute();
  */

  bg_index();
}


function _expsearch_admin_build_ft_form_submit($form, &$form_state) {

  //$ft_columns = variable_get('ft_columns', null);
  //$index_table = variable_set('index_table', null);

  _expsearch_admin_process_build_ft();
}

function _expsearch_admin_process_build_ft() {
  _create_ftsearch_table('ft_table');

  $batch = array(
             'title' => t('Processing full-text search table building.'),
             'operations' => array(
               array('build_ft_table', array()),
             ),
             'finished'         => 'build_ft_table_finished',
             'init_message'     => t('starting.'),
             'progress_message' => t('Processed @current out of @total.'),
             'error_message'    => t('error.'),
           );
  batch_set($batch);
}
/*
  $node->job_post_company['und'][0]['value']

  // for taxonomy term
  $term_id_1 = $node->field_term['und'][0]['tid'];
  $term_id_2 = $node->field_term['und'][1]['tid'];
*/
function _prepare_for_insertion($node) {

  $ft_columns = variable_get('ft_columns', array());

  if(empty($ft_columns)) {
    drupal_set_message("expsearch_node_load(): no full text search table found.");
    return array();
  }

  $taxo_pattern = '/^taxo_(.*)/';

  $content = array_reduce($ft_columns, function($acc, $column) use ($node, $taxo_pattern) {
    $match = preg_match($taxo_pattern, $column, $matches);
    $to_gather = "";
    if($match) {
      $tax = "";
      if(is_array($node->{$matches[1]}['und'])) {
        foreach($node->{$matches[1]} ['und'] as $t) {
          $term = taxonomy_term_load($t['tid']);
          $tax = $tax . " " . $term->name;
        }
        $to_gather = $tax;
      }
    } else {
      $to_gather = $node->{$column} ['und'][0]['value'];
    }
    $acc = $acc . " " . $to_gather;
    return $acc;
  }, "");

  return array('id' => $node->nid,
               'nid' => $node->nid,
               'content' => $content);
}


function build_ft_table(&$context) {
  global $kb;

  // set up batch
  if (empty($context['sandbox'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['current_node'] = 0;
    $context['sandbox']['max'] = db_query("SELECT COUNT(DISTINCT nid) FROM {node} where type='collection'")->fetchField();
  }

  $limit = 50;
  $result = db_select('node')
            ->fields('node', array('nid'))
            ->condition('nid', $context['sandbox']['current_node'], '>')
            ->condition('type', 'collection', '=')
            ->orderBy('nid')
            ->range(0, $limit)
            ->execute();

  $values = array();

  foreach ($result as $row) {
    $node = node_load($row->nid, NULL, TRUE);
    $for_insert = _prepare_for_insertion($node);
    if(!empty($for_insert)) {
      $values[] = $for_insert;
      //db_insert('ft_table')->fields($for_insert)->execute();
    }
    $context['results'][] = $node->nid . ' : ' . check_plain($node->title);
    $context['sandbox']['progress']++;
    $context['sandbox']['current_node'] = $node->nid;
    $context['message'] = check_plain($node->title);
  }

  $query = db_insert('ft_table')->fields(array('id', 'nid', 'content'));
  foreach ($values as $value) {
    $query->values($value);
  }
  $query->execute();


  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
  /*
    // insert all nodes
    $nodes = node_load_multiple(_node_id_array());
    $values = array();
    foreach($nodes as $node) {
      $for_insert = _prepare_for_insertion($node);
      if(!empty($for_insert)) {
        $values[] = $for_insert;
      }
    }
  */



  //drupal_set_message('done');

  //db_insert('ft_table')->fields($for_insert)->execute();


  /*
    $values = array_reduce($_raw_values, function($acc, $item){
      return $acc . ' ' . $item;
    }, "");
    sphinx_rt_minsert($values);

    // build knowledge base
    $terms = taxonomy_term_load_multiple(_term_id_array());

    foreach($terms as $term) {
      $kb->addInstance($term->tid);
    }
  */
}

function build_ft_table_finished($success, $results, $operations) {
  // The 'success' parameter means no fatal PHP errors were detected. All
  // other error management should be handled using 'results'.
  if ($success) {
    $message = format_plural(count($results), 'One post processed.', '@count posts processed.');
  } else {
    $message = t('Finished with an error.');
  }
  drupal_set_message($message);
  // Providing data for the redirected page is done through $_SESSION.
  foreach ($results as $result) {
    $items[] = t('Loaded node %title.', array('%title' => $result));
  }
  $_SESSION['my_batch_results'] = $items;

  bg_index();

  drupal_goto('/');
}


function ft_table_insert($node) {
  $for_insert = _prepare_for_insertion($node);
  if(!empty($for_insert)) {
    db_insert('ft_table')->fields($for_insert)->execute();
  }
}

function ft_table_update($node) {
  $for_insert = _prepare_for_insertion($node);
  if(!empty($for_insert)) {
    db_update('ft_table')
    ->fields(array('content' => $for_insert['content']))
    ->condition('id', $for_insert['id'], '=')
    ->execute();
  }
}

// $nodes == [node]
function ft_table_multiple_update($nodes) {
  foreach($nodes as $node) {
    ft_table_update($node);
  }
}

function ft_table_delete($nid) {
  if(!empty($nid)) {
    db_delete('ft_table')
    ->condition('id', $nid)
    ->execute();
  }
}

function bg_index() {
  $cmd = '/usr/bin/indexer --all --rotate';
  $mm = @shell_exec($cmd);
//  proc_close(proc_open($cmd, array(), $foo));
}
