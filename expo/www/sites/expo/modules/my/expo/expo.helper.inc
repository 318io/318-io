<?php
use Drupal\wg\DT;
use Drupal\wg\WG;
use Drupal\Component\Serialization\Json;
use Drupal\wg\my\expo\Sticker;

use Drupal\Component\Utility\SafeMarkup;


use Drupal\Core\Url;
use Drupal\field_collection\Entity\FieldCollectionItem;

function _expo_ajax_url($type, $id){
  $url = Url::fromRoute('expo.ajaxcontent', array('type' => $type, 'id' =>$id));
  return $url;
}

function _expo_coll_url($id, $link=false, $ns = 'public318') {
  $url = WG::config_get('expo.settings', 'ns.'.$ns).'/'.$id;
  if($link){
    $r = '<a href="'.$url.'" target="_blank">'.$link.'</a>';
  }
  else{
    $r = $url;
  }
  return $r;
}

function _expo_coll_fetchurl($ns = 'public318', $path='api/getcol') {
  $r = WG::config_get('expo.settings', 'ns.'.$ns).'/'.$path;
  return $r;
}

function _expo_get_json_fn($fn, $path='/', $ext='json') {
  $schema_base = WG::config_get('expo.settings', 'schema.base');
  $schema_json = $schema_base . WG::config_get('expo.settings', 'schema.json');
  if($path != '/') {
    $p = $schema_json.$path;
    file_prepare_directory($p, FILE_CREATE_DIRECTORY);
  }
  $r = $schema_json.$path.$fn.'.json';
  return $r;
}

function _expo_public318_api_url($qs) {
  $ns = 'public318';
  $url = _expo_coll_fetchurl($ns);
  $qs = rawurlencode($qs);
  $qs = SafeMarkup::checkPlain($qs);
  $url .= '?qs='.$qs;
  return $url;
}

function _expo_public318_get_content_by_id($id, $field) {
  $schema_base = WG::config_get('expo.settings', 'schema.base');
  $schema_json = $schema_base . WG::config_get('expo.settings', 'schema.json');
  $schema_icon = $schema_base . WG::config_get('expo.settings', 'schema.icon');
  $ns = 'public318';
  $url = _expo_public318_api_url($id);
  $cache_fn     = _expo_public318_get_json_uri($id);
  $json = WG::fetch_url($url, $cache_fn);
  //todo: not found
  $d = Json::decode($json);
  $a = array_shift($d['results']);
  return $a[$field];
}

function _expo_public318_get_json_uri($identifier, $ext='json') {
  $schema_base = WG::config_get('expo.settings', 'schema.base');
  $schema_json = $schema_base . WG::config_get('expo.settings', 'schema.json');

  $pathpart = _expo_public318_get_pathpart($identifier);
  $uri = $schema_json.'/'.$pathpart;
  file_prepare_directory($uri, FILE_CREATE_DIRECTORY);
  $uri .= '/'.$identifier.'.'.$ext;
  return $uri;
}

function _expo_public318_get_icon_uri($identifier, $suffix='_001') {
  $ext='jpg';
  $schema_base = WG::config_get('expo.settings', 'schema.base');
  $schema_media = $schema_base . WG::config_get('expo.settings', 'schema.media');

  $pathpart = _expo_public318_get_pathpart($identifier);
  $uri = $schema_media.'/'.$pathpart;
  file_prepare_directory($uri, FILE_CREATE_DIRECTORY);
  $uri .= '/'.$identifier.$suffix.'.'.$ext;
  return $uri;
}

function _expo_public318_get_pathpart($identifier) {
  $pathid = floor($identifier/1000);
  $pathid = str_pad($pathid, 3, "0", STR_PAD_LEFT);
  return $pathid;
}

function _expo_public318_get_icon_raw_by_id($id, $dest_uri = null) {
  $ext = 'jpg';
  if(!$dest_uri) $dest_uri = _expo_public318_get_icon_uri($id);

  $icon_url = _expo_public318_get_content_by_id($id, 'icon');
  WG::fetch_file_save_as($icon_url, $dest_uri);
  return $dest_uri;
}

function _expo_public318_get_icon_by_id($id, $icon_info) {
  $ext = 'jpg';

  $icon_uri = _expo_public318_get_icon_uri($id);
  if(!file_exists($icon_uri)) {
    $tempfn = DT::create_temp_file($ext, file_directory_temp());
    _expo_public318_get_icon_raw_by_id($id, $tempfn);
    $icon_realpath = \Drupal::service('file_system')->realpath($icon_uri);
    if($icon_info) {
      $str = str_replace('＂', '"', $icon_info);
      $info0 = Json::decode($str);
      $info = array_shift($info0);
      $w = $info['width'];
      $h = $info['height'];
      $rw = 480;
      $rh = 480 * 770/800;
      $ratio = $w/$h;
      if($w > $h) {
        $rh = $rh/$ratio;
      } else {
        $rw = $rw * $ratio;
      }
    }
    $cmd = 'convert '.$tempfn.' -gravity North -crop '.$rw.'x'.$rh.'+0+0 +repage '.$icon_realpath;
    shell_exec($cmd);
  }

  return $icon_uri;
}

function _expo_extract_collitem_target($target){
  preg_match('%^([^:]+)://([0-9]+)$%', $target, $m);
  $itemtype = $m[1];
  $id = $m[2];
  $r = compact("itemtype", "id");
  return $r;
}
