<?php
function collapi_build_datas() {
  $table = 'col_api_data';
  $sql = 'SELECT nid as identifier, field_description_value as text, m.field_mediainfo_value as mediainfo from node n
         left join field_data_field_description d on n.nid=d.entity_id and d.entity_type=:entity_type and d.bundle = :bundle
         left join field_data_field_mediainfo m on n.nid=m.entity_id and m.entity_type=:entity_type and m.bundle = :bundle
         where n.type = :bundle
         ';
  $rows = db_query($sql, array(':entity_type'=>'node', ':bundle' => 'collection', ))->fetchAll();
  db_query('TRUNCATE '.$table);
  $query = db_insert($table)->fields(array('identifier', 'type', 'icon', 'icon_info', 'thumb', 'medium', 'text'));
  $values = array();
  foreach($rows as $row) {
    $record = array(
                'identifier' => $row->identifier,
                'type'=> 'image',
                'icon' => _coll_get_feature_image_url($row->identifier),
                'icon_info' => $row->mediainfo,
                'thumb' => _coll_get_feature_image_url($row->identifier, 'icon'),
                'medium' => _coll_get_feature_image_url($row->identifier, 'medium'),
                'text' => $row->text,
              );
    $query->values($record);
  }
  $query->execute();
}

function _coll_get_feature_image_url($identifier, $style_name = 'large', $linkurl=false, $retboolifnofile = false) {
  $f = false;
  $files = _coll_get_digifiles($identifier, 'public', 'webm');
  if($files) {
    //webm;
    $file0 = array_shift($files);
    $f = _coll_get_video_icon($file0);
  }
  if(!$files) {
    $files = _coll_get_digifiles($identifier, 'public', 'jpg');
    if($files) $f = array_shift($files);
  }
  if($f === false) {
    return false;
  } else {
    $r = image_style_url($style_name, $f);
  }
  return $r;
}

function _collpubapi_get_data($qs = null) {
  $query = db_select('col_api_data', 'd');
  $query->fields('d');
  if($qs) {
    if(preg_match('%id:([0-9]{5,})%', $qs, $m)) {
      $identifiers = $m[1];
      if(!is_array($identifiers)) $identifiers = array($identifiers);
    } else if (preg_match('% *(([0-9]+, *)+) *%', $qs)) {
      $ids = explode(',', $qs);
      $identifiers = [];
      foreach ($ids as $id) {
        $identifiers[] = trim($id);
      }
    } else {
      $qr = _coll_search($qs, 0, '100000');
      if($qr['identifier']) {
        $identifiers = $qr['identifier'];
      }
    }
    if($identifiers) {
      $query->condition('d.identifier', $identifiers, 'IN');
      $rows = $query->execute()->fetchAll();
    }
  } else {
    $rows = $query->execute()->fetchAll();
  }
  return $rows;
}

function _collpubapi_getcol() {
  $rowperpage = 1000;

  $datas = array();

  $qs = $_GET['qs'];
  $qs = str_replace(array('　'), ' ', $qs);
  $qs = trim($qs);
  $datas = array();
  if (preg_match('%^([0-9]{5,}$)%', $qs, $m)) {
    $datas = _collpubapi_get_data('id:'.$qs);
  }

  if(!$datas) {
    $datas = _collpubapi_get_data($qs);
  }

  $ret = new stdClass();
  $ret->request = $qs;
  if($datas) {
    $ret->status = 'OK';
    $ret->results = $datas;
  } else {
    $ret->status = 'NOT FOUND';
    $ret->results = array();
  }
  drupal_json_output($ret);
  drupal_exit();
}
