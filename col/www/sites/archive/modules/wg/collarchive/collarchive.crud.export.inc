<?php
function _collarchive_crud_process_export_archive($options) {
  if($options['nid_range']){
    $id_range = trim($options['nid_range']);
    $arr = DT::explode_map( '-', $id_range, array('min', 'max'));
    $options['nid_min'] = trim($arr['min']);
    $options['nid_max'] = trim($arr['max']);
  }
  extract($options);

  $opts = &drupal_static(__FUNCTION__);
  if (!isset($opts)) {
    $vars = _coll_ef_variables();
    $vars = DTEF::entity_create_prepare($vars['node']);
    $entities = $vars['entity'];
    $opts['entity'] = current($entities);
    $opts['nodetype'] = key($entities);
    $entity = current($entities);
    foreach ($entity['field'] as $key => $v) {
      $opts['fmap'][$key] = $v['vardef']['sslabel'];
    }

    $ar = $opts['fmap'];
    $opts['fmap'] = array();

    $i = 0;
    foreach($ar as $key=>$v) {
      if($i==1) {
        $opts['fmap']['action'] = 'action';
      }
      $opts['fmap'][$key] = $v;
      $i++;
    }
  }
  extract($opts);
  $csv = DT::create_temp_file('csv', file_directory_temp());
  $fp = fopen($csv, 'w');
  DT::fputcsv($fp, $fmap);
  fclose($fp);
  if(!$nids){
    $nids = _collarchive_curd_process_export_archive_pre($nodetype, $options);
  }
  if ($nids) {
    $batch = array(
               'operations' => array(),
               'finished' => '_collarchive_curd_batch_export_archive_finished',
               'title' => t('Export'),
               'init_message' => t('starting...'),
               'progress_message' => t('Processing...'),
               'error_message' => t('error.')
             );
    $rownum = 0;
    $anids = array();
    foreach($nids as $nid) {
      $rownum++;
      $anids[] = $nid;
      if($rownum % COLL_BATCH_CHUNK_SIZE == 0) {
        $batch['operations'][] = array('_collarchive_curd_batch_export_node2row_op', array($anids, $csv, $fmap, $entity, $options));
        $anids = array();
      }
    }
    if($anids) {
      $batch['operations'][] = array('_collarchive_curd_batch_export_node2row_op', array($anids, $csv, $fmap, $entity, $options));
    }
    batch_set($batch);
    //batch_process('admin/config/coll/archive_crud'); // The path to redirect to when done.
    batch_process('admin/config/coll'); // The path to redirect to when done.
  }
  return true;
}

function _collarchive_curd_process_export_archive_pre($nodetype, $options) {
  $types = array($nodetype);
  $query = db_select('node', 'n');
  $query->condition('n.status', 1, '>=')
  ->condition('n.type', $types, 'IN');

  $query->fields('n', array('nid'));
  $nid_min = (int)$options['nid_min'];
  $nid_max = (int)$options['nid_max'];

  if(($nid_min == 0) && ($nid_max == 0)) {
    // do nothing
  } else {
    $nminid = db_query("SELECT nid FROM {node} ORDER BY nid")->fetchField();
    $nmaxid = db_query("SELECT nid FROM {node} ORDER BY nid DESC")->fetchField();
    if(($nid_min == 0) || ($nid_min < $nminid)) $nid_min = $nminid;
    if(($nid_max == 0) || ($nid_max > $nmaxid)) $nid_max = $nmaxid;
    if(($nid_min == $nminid) && ($nid_max == $nmaxid)) {
      // do nothing
    } else {
      $query->condition('n.nid', $nid_min, '>=');
      $query->condition('n.nid', $nid_max, '<=');
    }

  }
  $query->orderBy('n.nid', 'ASC');
  $num_rows = $query->countQuery()->execute()->fetchField();
  $result = $query->execute();
  $nids = $result->fetchCol();
  return $nids;
}

function _collarchive_curd_batch_export_archive_finished($success, $results, $operations) {
  if ($success) {
    $csv = $results['csv'];
    $vars = _coll_ef_variables();
    $path = $vars['path']['export'];
    $uri = $path.'/'.'metadata_'.date('Ymdhm_').DT::random_string(3).'.csv';
    file_unmanaged_copy($csv, $uri, FILE_EXISTS_REPLACE);
    $url = file_create_url($uri);
    drupal_set_message('export complete! '.'<a href="'.$url.'">get file (csv) </a>');

  } else {
    $error_operation = reset($operations);
    $message = t('An error occurred while processing %error_operation with arguments: @arguments', array(
                   '%error_operation' => $error_operation[0],
                   '@arguments' => print_r($error_operation[1], TRUE)
                 ));
    drupal_set_message($message, 'error');
  }
}

function _collarchive_curd_batch_export_node2row_op($nids, $csv, $fmap, $entity, $options, &$context) {
  foreach($nids as $nid) {
    $node = node_load($nid, null, true);
    $row0 = _coll_node2plain($fmap, $node, $entity);
    if(empty($row0['識別號'])) $row0['識別號'] = $nid;
    $row = array();
    $i = 0;
    foreach($row0 as $k=>$v) {
      if($i == 1) $row['action'] = '';
      $row[$k] = $v;
      $i++;
    }
    $fp = fopen($csv, 'a');
    DT::fputcsv($fp, $row, $options['force_text']);
    fclose($fp);
  }
  $context['results']['csv'] = $csv;
}
