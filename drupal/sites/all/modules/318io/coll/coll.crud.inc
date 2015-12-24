<?php
function _coll_process_csv_batch($csv, $cb, $finished, $title, $redirect_url) {
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

function _coll_process_csv_batch_row_op($cb, $rows, $fields, $rownum, &$context) {
  foreach($rows as $row) {
    if($r = $cb($row, $fields)) {
      $context['results']['successed'][] = $r;
    }
  }
  $context['message'] = "Now processing $rownum...";
}

function _coll_node2plain($fmap, $node, $entity) {
  $row = array();
  foreach($fmap as $f => $n) {
    if(!property_exists($node, $f)) continue;
    $d = $node->$f;
    $v = array();

    if($d && array_key_exists(COLL_LANG, $d)) {
      $v0 = $d[COLL_LANG];
      $vardef = $entity['field'][$f]['vardef'];
      if($v0) {
        switch($vardef['type']) {
          case 'text':
          case 'longtext':
            foreach($v0 as $v00) {
              $val = $v00['value'];
              $val = str_replace(array("\r\n","\r","\n"), COLL_NEWLINE,$val);
              $v[] = $val;
            }
            break;
          case 'taxon':
            foreach($v0 as $v00) {
              if(!array_key_exists('name', $v00)) {
                $tid = $v00['tid'];
                $v00 = taxonomy_term_load($tid);
                $v00 = (array) $v00;
                if(!$v00) {
                  // todo error
                  dbug($v00);
                  die();
                }
              }
              $v[] = $v00['name'];
            }
            break;
          default:
            dbug($vardef);
            dbug($v);
            dbug($v0);
            die();// todo error
        }
      }
    }
    $row[$n] = implode(';', $v);
  }
  return $row;
}
