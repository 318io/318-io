<?php
function _collarchive_crud_process_import($file_path) {
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
    _coll_process_csv_batch(
      $fcsv,
      '_collarchive_crud_process_import_csv_row_cb',
      '_collarchive_crud_batch_import_finished',
      'Import', 'admin/config/coll/expsearch_build_ft');
  }
  return;
}

function _collarchive_crud_process_import_csv_row_cb($row, $fields) {
  $opts = &drupal_static(__FUNCTION__);
  if (!isset($opts)) {
    $vars = _coll_ef_variables();
    $vars = DTEF::entity_create_prepare($vars['node']);
    $entities = $vars['entity'];
    $entity = current($entities);
    foreach ($entity['field'] as $key => $v) {
      $f = $v['instance'];
      $fn = $f['field_name'];
      $opts['sslabels'][$f['sslabel']] = $fn;
    }
    $opts['sslabels']['action'] = 'action';
  }

  $values = array();
  foreach ($opts['sslabels'] as $lbl => $fn) {
    $d = DT::array_get($row, $lbl);
    $d = str_replace('"', '＂', $d);
    $values[$fn] = $d;
  }
  if($values['action'] == 'delete') {
    $identifier = $nid = $values['field_identifier'];
    if($identifier) {
      node_delete($nid);
      $vars0 = _coll_ef_variables();
      $vars_path = $vars0['path'];
      $pathid = _coll_get_pathpart($identifier);
      $fileuri_pathpart = $vars_path['meta'].'/'.$pathid;
      $fileuri = $vars_path['meta'].'/'.$pathid.'/'.$identifier.'.yaml';
      if(file_exists($fileuri)) {
        file_unmanaged_delete($fileuri);
      }
    }
  } else {
    $node = _coll_item_save($values);
    $nid = $node->nid;
  }
  return $nid;
}

function _collarchive_crud_batch_import_finished($success, $results, $operations) {
  if ($success) {
    $rows = count($results['successed']);
    _collarchive_crud_relcol('relatedto');
    _collarchive_crud_relcol('partof');
    drupal_set_message("import complete! $rows record imported");
  } else {
    $error_operation = reset($operations);
    $message = t('An error occurred while processing %error_operation with arguments: @arguments', array(
                   '%error_operation' => $error_operation[0],
                   '@arguments' => print_r($error_operation[1], TRUE)
                 ));
    drupal_set_message($message, 'error');
  }
}

function _collarchive_crud_relcol($fie) {
  $sql = 'SELECT
         rel.entity_id as nid, rel.field_relcol_'.$fie.'_value as relv
         from field_data_field_relcol_'.$fie.' rel
         where field_relcol_'.$fie.'_value regexp :regexp
         group by nid
         ';
  $nids = db_query($sql, array(':regexp' => '[^0-9]'))->fetchCol();
  if($nids) {
    $sql_oid = 'SELECT
               rid.field_repository_id_value as oid, rid.entity_id as nid
               from field_data_field_relcol_'.$fie.' rel
               JOIN field_data_field_repository_id rid
               ON rid.field_repository_id_value =rel.field_relcol_'.$fie.'_value
               where field_relcol_'.$fie.'_value regexp :regexp
               ';

    $map = db_query($sql_oid, array(':regexp' => '[^0-9]'))->fetchAllKeyed();
    $field = 'field_relcol_'.$fie;
    foreach($nids as $nid) {
      $n = node_load($nid);
      $ffs = $n->$field;
      foreach($ffs['und'] as &$ff) {
        $oid = $ff['value'];
        $nnid = $map[$oid];
        $ff['value'] = $ff['safe_value'] = $nnid;
      }
      $n->$field = $ffs;
      node_save($n);
      _coll_node_save_yaml($n);
    }
  }
}
