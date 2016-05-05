<?php
namespace Drupal\expo;

use Drupal\wg\DT;
use Drupal\wg\WG;

class Sticky {
  public static function gen($str, $uri, $opts) {
    extract(self::_get_opts($str, $opts));
    if(!file_exists($uri)) {
      $dest = drupal_realpath($uri);
      self::_gen($str, $dest, $w, $h, $fontsize, $t, $gravity, $verticle);
    }
    return $uri;
  }

  private static function _gen($str, $dest, $w, $h, $fontsize, $t='caption', $gravity='center', $verticle = false) {
    if($verticle) $str = preg_replace('%(.)%u', '$1\n', $str);
    $uri_font = 'public://font/GenShinGothic-Monospace-Bold.ttf';
    $font = drupal_realpath($uri_font);
    $background = '#fcfc84';
    $fill = '#000';
    $cmd = "convert ";
    $cmd .= "-background '$background' -fill '$fill' ";
    $cmd .= "-size ${w}x$h ";
    $cmd .= "-gravity $gravity ";
    $cmd .= "-pointsize $fontsize ";
    $cmd .= "-font $font ";
    $cmd .= "$t:'$str' $dest";

    shell_exec($cmd);
  }

  private static function _get_opts($str, $opts) {
    $verticle = false;
    extract($opts);
    $s = preg_replace('/[\x80-\xFF]/', 'aa', $str);
    $l = mb_strlen($str);

    foreach($ar as $k => $v) {
      if($l < $k) break;
      $opt = $v;
      if($l == $k) break;
    }
    $r = array(
           'w' => $w,
           'h' => $h,
           'verticle' => ($verticle)?true: false,
        't' => 'caption',
        'gravity' => 'center',
         );
    $r = array_merge($r, $opt);
    return $r;
  }

}
