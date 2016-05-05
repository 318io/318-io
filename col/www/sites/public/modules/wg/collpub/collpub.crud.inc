<?php
function _collpub_sync_drush() {
  _dt_switch_to_maintenance('網站維護中');
  watchdog('sync', 'start');
  drupal_flush_all_caches();
  cache_clear_all();
  _collpub_crud_process_import_all();
  watchdog('sync', 'build ft');
  _expsearch_build_ft(); //defined at expsearch.admin.inc
  watchdog('sync', 'built ft done');
  watchdog('sync', 'done');
  _dt_switch_to_online();
}

function _collpub_crud_page() {
  $form = drupal_get_form('_collpub_crud_form');
  return $form;
}

function _collpub_crud_form($form, &$form_state) {
  $form = array();
  $vars = _coll_ef_variables();
  $form['import'] =
    array(
      '#type' => 'fieldset',
      '#title' => t('Import metadata'),
      '#collapsible' => FALSE,
      '#description' => t('important: all collections will be deleted before import'),
      '#parents' => array(),
    );
  $form['import']['import_file'] =
    array(
      '#title' => t('File to import'),
      '#type' => 'managed_file',
      '#description' => t('Choose a file (.xlsx or .csv).'),
      '#default_value' => '',
      '#upload_validators' => array(
        'file_validate_extensions' => array('xlsx csv'),
      ),
      '#upload_location' => $vars['path']['source'],
      '#process' => array('_file_element_process')
    );
  $form['import']['button_import'] =
    array(
      '#type' => 'submit',
      '#value' => t('Import'),
    );
  return $form;
}

function _collpub_crud_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  switch($form_state['clicked_button']['#value']) {
    case $values['button_import']:
      $file = file_load($values['import_file']);
      $uploaded = file_save($file);
      if ($uploaded == TRUE) {
        $file_path = $file->uri;
        _collpub_crud_process_import($file_path);
        collapi_build_datas();
      } else {
        drupal_set_message(t('The file could not be uploaded. Please contact the site administrator.'), 'error');
        return;
      }
      break;
  }
  return;
}

function _file_element_process($element, &$form_state, $form) {
  $element = file_managed_file_process($element, $form_state, $form);
  $element['upload_button']['#access'] = FALSE;
  return $element;
}

function _collpub_crud_process_import_all() {
  $uri = COLL_FILE_BASE_SCHEMA.'/import/meta.csv';
  $csv = $uri;

  if(!file_exists($csv)) {
    die('no file');
  }
  $cb = '_collpub_crub_process_import_csv_row_cb';
  $finish = '_collpub_crud_batch_import_finished';
  $title = 'Import';
  $redirect_url = 'admin/config/coll/expsearch_build_ft';

  $rownum = 0;
  $fields = array();
  $fp = fopen($csv, "r");
  if ($fp !== FALSE) {
    fseek($fp, 0);
    // delete all collections
    $nids = db_query('SELECT nid FROM {node} WHERE type = :type', array(':type'=>'collection'))->fetchCol();
    if($nids) {
      DT::dnotice('DELETE nodes');
      node_delete_multiple($nids);
    }

    DT::dnotice('importing...');
    $pidx = 1;
    $rows = array();
    while (($data = fgetcsv($fp)) !== FALSE) {
      $rownum++;
      $num = count($data);
      if(!$fields) {
        for ($c=0; $c < $num; $c++) {
          $d = $data[$c];
          $d = trim($d);
          if($d!='' && ($d[0] != '*')) $fields[$c] = $d;
        }
        continue;
      } else {
        $row = array();
        for ($c=0; $c < $num; $c++) {
          $d = $data[$c];
          $d = trim($d);

          if(array_key_exists($c, $fields) && ($fields[$c] != '')) {
            $row[$fields[$c]] = $d;
          }
        }
        _collpub_crub_process_import_csv_row_cb($row, $fields);
        dt_progress($pidx++);
      }
    }

    fclose($fp);
    _collpub_crud_batch_import_finished2($rownum);
  }
  return true;
}

function _collpub_crud_process_import($file_path) {
  $p = pathinfo($file_path);
  switch($p['extension']) {
    case 'xlsx':
      $fcsv = file_directory_temp().'/'.$p['filename'].'.csv';
      dtf_xlsx2csv($file_path, $fcsv);
      break;
    case 'csv':
      $fcsv = $file_path;
      break;
  }

  if($fcsv) {
    _collpub_crud_process_import0(
      $fcsv,
      '_collpub_crub_process_import_csv_row_cb',
      '_collpub_crud_batch_import_finished',
      'Import', 'admin/config/coll/expsearch_build_ft');
  }
  return;
}

function _collpub_crud_process_import0($csv, $cb, $finished, $title, $redirect_url) {
  $rownum = 0;
  $fields = array();
  $fp = fopen($csv, "r");
  if ($fp !== FALSE) {
    fseek($fp, 0);
    $batch = array(
               'operations' => array(),
               'finished' => $finished,
               'title' => $title,
               'init_message' => t('starting...'),
               'progress_message' => t('Processing...'),
               'error_message' => t('error.')
             );
    // delete all collections
    $op = '_collpub_crud_process_import0_deletenode_op';
    $nids = db_query('SELECT nid FROM {node} WHERE type = :type', array(':type'=>'collection'))->fetchCol();
    _wg_batch_chunck($nids, $batch, COLL_BATCH_CHUNK_SIZE, $op, array());
    $rows = array();
    while (($data = fgetcsv($fp)) !== FALSE) {
      $rownum++;
      $num = count($data);
      if(!$fields) {
        for ($c=0; $c < $num; $c++) {
          $d = $data[$c];
          $d = trim($d);
          if($d!='' && ($d[0] != '*')) $fields[$c] = $d;
        }
        continue;
      } else {
        $row = array();
        for ($c=0; $c < $num; $c++) {
          $d = $data[$c];
          $d = trim($d);

          if(array_key_exists($c, $fields) && ($fields[$c] != '')) {
            $row[$fields[$c]] = $d;
          }
        }
        $rows[] = $row;
        if($rownum % COLL_BATCH_CHUNK_SIZE == 0) {
          $batch['operations'][] = array('_coll_process_csv_batch_row_op', array($cb, $rows, $fields, $rownum));
          $rows = array();
        }
      }
    }
    if($rows) {
      $batch['operations'][] = array('_coll_process_csv_batch_row_op', array($cb, $rows, $fields, $rownum));
    }
    fclose($fp);

    batch_set($batch);
    batch_process($redirect_url);
  }
  return true;
}

function _collpub_crud_process_import0_deletenode_op($nids, &$context) {
  foreach($nids as $nid) {
    $node = node_delete($nid);
  }
  $context['message'] = t('deleting original collections');
}

function _collpub_crub_process_import_csv_row_cb($row, $fields) {
  $opts = &drupal_static(__FUNCTION__);
  if (!isset($opts)) {
    $vars = _collpub_ef_variables();
    $vars = DTEF::entity_create_prepare($vars['node']);
    $entities = $vars['entity'];
    $entity = current($entities);
    foreach ($entity['field'] as $key => $v) {
      $f = $v['instance'];
      $fn = $f['field_name'];
      $opts['sslabels'][$f['sslabel']] = $fn;
    }
  }

  $values = array();
  foreach ($opts['sslabels'] as $lbl => $fn) {
    $d = DT::array_get($row, $lbl);
    $values[$fn] = $d;
  }

  $values['allowcomment'] = false;
  $node = _coll_item_save($values, false, 'pub');
  return $node->nid;
}

function _collpub_crud_batch_import_finished($success, $results, $operations) {
  if ($success) {
    $count = count($results['successed']);
    _collpub_crud_batch_import_finished2($count);
  } else {
    $error_operation = reset($operations);
    $message = t('An error occurred while processing %error_operation with arguments: @arguments', array(
                   '%error_operation' => $error_operation[0],
                   '@arguments' => print_r($error_operation[1], TRUE)
                 ));
    drupal_set_message($message, 'error');
  }
}

function _collpub_crud_batch_import_finished2($count) {
  //flush styles
  watchdog('import', 'flush styles');
  $p2 = drupal_realpath('public://styles');
  DT::rrmdir($p2, true);

  drupal_set_message("import complete! $count record imported");
  drupal_set_message("you should rebuild fulltext index now!");
  module_invoke_all('collpubimportfin');
}
