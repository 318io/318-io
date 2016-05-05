<?php
function _collarchive_sync_drush() {
  watchdog('sync', 'export...');
  $source = _collarchive_crud_process_export_public_all();
  $public_system_www_root = variable_get('public_system_www_root');
  $public_site_path = variable_get('public_site_path');
  $basename = pathinfo ( $source, PATHINFO_BASENAME);
  $tgt = $public_system_www_root.'/sites/'.$public_site_path.'/files/digicoll/import/meta.csv';
  if (!copy($source, $tgt)) {
    watchdog('sync', "failed to copy $source...");
  }
  watchdog('sync', 'export end...');
}

function _collarchive_crud_process_export_public_all() {
  $vars = _coll_ef_variables();
  $path = $vars['path'];
  $public_system_www_root = variable_get('public_system_www_root');
  $public_site_path = variable_get('public_site_path');
  $options['digifolder'] = $public_system_www_root.'/sites/'.$public_site_path.'/files/digicoll/public';
  $options['archivefolder'] = $public_system_www_root.'/sites/'.$public_site_path.'/files/digicoll/archive';
  $options['metafolder'] = $path['export'];
  $options['withfile'] = true;
  $opts['lang'] = 'und';
  $vars = _coll_ef_variables();
  $opts['path'] = $vars['path'];
  $vars = DTEF::entity_create_prepare($vars['node']);
  $entities = $vars['entity'];
  $opts['entity'] = current($entities);
  $opts['nodetype'] = key($entities);
  $entity = current($entities);
  $opts['entity'] = $entity;
  foreach ($entity['field'] as $key => $v) {
    $vardef = $v['vardef'];
    if(!array_key_exists('is-public', $vardef)) $vardef['is-public'] = false;
    if(!$vardef['is-public']) continue;
    $opts['fmap'][$key] = $vardef['sslabel'];
  }
  extract($opts);
  $digipath = $options['digifolder'];
  file_prepare_directory($digipath, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
  $archivepath = $options['archivefolder'];
  file_prepare_directory($archivepath, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);

  $csv = DT::create_temp_file('csv', file_directory_temp());
  $options['csv'] = $csv;
  $fp = fopen($csv, 'w');
  $fmap2 = $fmap;
  $fmap2[] = 'mediainfo';
  DT::fputcsv($fp, $fmap2);
  fclose($fp);

  $options['fmap'] = $fmap;
  $op_withfile = '_collarchive_crud_batch_export_public_withfile_op';
  $op_finish   = '_collarchive_crud_batch_export_public_finished';
  $op_export   = '_collarchive_crud_batch_export_public_node_op';
  $redirecturl = 'admin/config/coll/archive_crud';

  $context = array();
  $nids = _collarchive_crud_process_export_public_pre($nodetype, $options);
  $op_withfile($options, $context);
  $op_export($nids, $entity, $options, $context);
  $uri = _collarchive_crud_batch_export_public_finished2($csv, $options);
  $path = drupal_realpath($uri);
  return $path;
}

function _collarchive_crud_process_export_public($options) {
  $opts['lang'] = 'und';
  $vars = _coll_ef_variables();
  $opts['path'] = $vars['path'];
  $vars = DTEF::entity_create_prepare($vars['node']);
  $entities = $vars['entity'];
  $opts['entity'] = current($entities);
  $opts['nodetype'] = key($entities);
  $entity = current($entities);
  $opts['entity'] = $entity;
  foreach ($entity['field'] as $key => $v) {
    $vardef = $v['vardef'];
    if(!array_key_exists('is-public', $vardef)) $vardef['is-public'] = false;
    if(!$vardef['is-public']) continue;
    $opts['fmap'][$key] = $vardef['sslabel'];
  }
  extract($opts);
  $digipath = $options['digifolder'];
  file_prepare_directory($digipath, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
  $archivepath = $options['archivefolder'];
  file_prepare_directory($archivepath, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);

  $csv = DT::create_temp_file('csv', file_directory_temp());
  $options['csv'] = $csv;
  $fp = fopen($csv, 'w');
  $fmap2 = $fmap;
  $fmap2[] = 'mediainfo';
  DT::fputcsv($fp, $fmap2);
  fclose($fp);
  $options['fmap'] = $fmap;
  $op_withfile = '_collarchive_crud_batch_export_public_withfile_op';
  $op_finish   = '_collarchive_crud_batch_export_public_finished';
  $op_export   = '_collarchive_crud_batch_export_public_node_op';
  $redirecturl = 'admin/config/coll/archive_crud';
  $batch = array(
             'operations' => array(),
             'finished' => $op_finish,
             'title' => t('Export'),
             'init_message' => t('starting...'),
             'progress_message' => t('Processing...'),
             'error_message' => t('error.')
           );
  $batch['operations'][] = array($op_withfile, array($options));

  $nids = _collarchive_crud_process_export_public_pre($nodetype, $options);
  _wg_batch_chunck($nids, $batch, COLL_BATCH_CHUNK_SIZE, $op_export, array($entity, $options));
  batch_set($batch);
  batch_process($redirecturl);
  return true;
}

function _collarchive_crud_process_export_public_pre($nodetype, $options) {
  $types = array($nodetype);
  $ispublis_tid = db_query("select tid from taxonomy_term_data as t, taxonomy_vocabulary as v WHERE t.vid=v.vid AND v.name = 'boolean' AND t.name = '是'")->fetchField();
  $query = db_select('node', 'n');
  $query->join('field_data_field_public', 'p', 'n.nid = p.entity_id');
  $query->condition('n.status', 1, '>=')->condition('n.type', $types, 'IN');
  $query->condition('p.field_public_tid', $ispublis_tid, '=');
  $query->fields('n', array('nid'));
  $query->orderBy('n.nid', 'ASC');
  $num_rows = $query->countQuery()->execute()->fetchField();
  $result = $query->execute();
  $nids = $result->fetchCol();
  return $nids;
}

function _collarchive_crud_batch_export_public_withfile_op($options, &$context) {
  if($options['withfile']) {
    DT::rrmdir($options['digifolder'], false);
    DT::rrmdir($options['archivefolder'], false);
    $context['message'] = t('deleting original files');
  }

  $digipath = $options['digifolder'];
  $archivepath = $options['archivefolder'];
  $digipath2 = $options['digifolder'].'2';
  $archivepath2 = $options['archivefolder'].'2';
  rename($digipath, $digipath2);
  rename($archivepath, $archivepath2);
  file_prepare_directory($digipath, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
  file_prepare_directory($archivepath, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
}

function _collarchive_crud_batch_export_public_node_op($nids, $entity, $options, &$context) {
  _collarchive_crud_batch_export_public_node_op0($nids, $options['csv'], $options['fmap'], $entity, $options);
  $context['results']['csv'] = $options['csv'];
  $context['results']['options'] = $options;
}

function _collarchive_crud_batch_export_public_node_op0($nids, $csv, $fmap, $entity, $options) {
  $i = 1;
  foreach($nids as $nid) {
    _collarchive_crud_batch_export_public_node_op0_nid($nid, $csv, $fmap, $entity, $options);
    dt_progress($i++);
  }
}

function _collarchive_crud_batch_export_public_node_op0_nid($nid, $csv, $fmap, $entity, $options) {
  $node = node_load($nid, null, true);
  $row = _coll_node2plain($fmap, $node, $entity);
  $identifier = $node->nid;
  $files = _coll_get_digifiles($identifier, 'public');
  if($files) {
    $mediainfo = _collarchive_crud_get_public_file_info($files);
    $row['mediainfo'] = json_encode($mediainfo);
  }
  $fp = fopen($csv, 'a');
  DT::fputcsv($fp, $row);
  fclose($fp);
  $pathid = _coll_get_pathpart($identifier);
  if($files) {
    $digipath = $options['digifolder'];
    $dest = $digipath.'/'.$pathid;
    file_prepare_directory($dest, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    $digipath2 = $options['digifolder'].'2';
    $possisrc = $digipath2.'/'.$pathid;
    _collarchive_crud_export_public_file($files, $dest, $identifier, $possisrc);
  }

  //archive, high resolution
  $hr = $node->field_high_resolution;
  if(!$hr) return;
  $hrtid = $hr['und'][0]['tid'];
  $hrterm = taxonomy_term_load($hrtid);
  if($hrterm->name != '是') return;

  $files = _coll_get_digifiles($identifier, 'archive');
  if($files) {
    $digipath = $options['archivefolder'];
    $dest = $digipath.'/'.$pathid;
    file_prepare_directory($dest, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    $digipath2 = $options['archivefolder'].'2';
    $possisrc = $digipath2.'/'.$pathid;
    _collarchive_crud_export_archive_file($files, $dest, $identifier, $possisrc);
  }
}

function _collarchive_crud_get_public_file_info($files) {
  $r = array();
  foreach($files as $file) {
    $p = pathinfo($file);
    switch($p['extension']) {
      case 'jpeg':
      case 'jpg':
        $mediainfo = image_get_info($file);
        unset($mediainfo['extension']);
        unset($mediainfo['mime_type']);
        unset($mediainfo['file_size']);
        $r[$p['basename']] = $mediainfo;
        break;
      case 'webm':
      default:
    }
  }
  return $r;
}

function _collarchive_crud_export_archive_file($files, $dest, $identifier, $possisrc) {
  foreach($files as $file) {
    $p = pathinfo($file);
    $destination = $dest.'/'.$p['basename'];
    $possisource = $possisrc.'/'.$p['basename'];
    if(file_exists($possisource)) {
      rename($possisource, $destination);
      continue;
    }
    switch($p['extension']) {
      case 'tif':
      case 'tiff':
      case 'jpeg':
      case 'jpg':
        file_unmanaged_copy($file, $destination, FILE_EXISTS_REPLACE );
      default:
        // do nothing
    }
  }
}

function _collarchive_crud_export_public_file($files, $dest, $identifier, $possisrc) {
  foreach($files as $file) {
    $p = pathinfo($file);
    $destination = $dest.'/'.$p['basename'];
    $possisource = $possisrc.'/'.$p['basename'];
    if(file_exists($possisource)) {
      rename($possisource, $destination);
      continue;
    }

    switch($p['extension']) {
      case 'jpeg':
      case 'jpg':
        $rfile = drupal_realpath($file);
        $rdestination = drupal_realpath($destination);
        if(!$rdestination) $rdestination = $destination;
        $public_system_url = variable_get('public_system_url');
        $paras = array(
                   'convert',
                   $rfile,
                   "-resize" => "800x770",
                   "-size" => "800x800",
                   "xc:white",
                   "+swap",
                   "-gravity North",
                   "-composite",
                   "-pointsize 16",
                   "-gravity South",
                   "-annotate 0",
                   "'".$public_system_url."/".$identifier."'",
                   $rdestination
                 );
        $cmd = DT::makeparameter($paras);
        shell_exec($cmd);
        break;
      case 'webm':
      default:
        file_unmanaged_copy($file, $destination, FILE_EXISTS_REPLACE );
    }
  }
}

function _collarchive_crud_batch_export_public_finished2($csv, $options) {
  DT::rrmdir($options['digifolder'].'2', true);
  DT::rrmdir($options['archivefolder'].'2', true);

  $uri = $options['metafolder'].'/'.'metadata_'.date('Ymdhm_').DT::random_string(3).'.csv';
  file_unmanaged_copy($csv, $uri, FILE_EXISTS_REPLACE);
  return $uri;
}

function _collarchive_crud_batch_export_public_finished($success, $results, $operations) {
  if ($success) {
    $csv = $results['csv'];
    $options = $results['options'];
    $uri = _collarchive_crud_batch_export_public_finished2($csv, $options);
    $metadataurl = file_create_url($uri);
    $public_system_url = variable_get('public_system_url', null);
    $msg = '<p>export complete! ';
    if(!$options['withfile']) $msg .= '(no file updated)';
    else $msg .= '(file updated)';
    $msg .= '</p>';
    $msg .= '<p>please follow these steps to updata public system:</p>';
    $msg .= '<p>1. <a href="!metadataurl">download metadata file here (csv) </a>;</p>';
    $msg .= '<p>2. visit <a href="!url">!url</a> and import the downloaded file</p>';
    $msg_args = array(
                  '!metadataurl' => $metadataurl,
                  '!url' => $public_system_url.'/admin/config/coll/public_crud'
                );
    drupal_set_message(t($msg, $msg_args), 'warning');
    watchdog('sync', t($msg, $msg_args));
  } else {
    $error_operation = reset($operations);
    $message = t('An error occurred while processing %error_operation with arguments: @arguments', array(
                   '%error_operation' => $error_operation[0],
                   '@arguments' => print_r($error_operation[1], TRUE)
                 ));
    drupal_set_message($message, 'error');
    drupal_set_message(l('Run again', 'my_module/batch_fix'));
  }
}
