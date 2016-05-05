<?php
namespace Drupal\wg;

class DT {

  /********************** string***********************/
  static public function sanitize($string) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â€”", "â€“", ",", "<", ".", ">", "/", "?");
    $clean = trim(str_replace($strip, "_", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = mb_strtolower($clean, 'UTF-8');
    return $clean;
  }

  static public function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
  }

  static public function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
  }

  static public function random_string($length = 10, $lowercase = false) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if($lowercase) $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
  }

  static public function random_num_list($min, $max, $num) {
    $cc = array();
    for($x=0; $x<$num; $x++) {
      $n = rand($min, $max);
      if(!in_array($n, $cc)) {
        $cc[] = $n;
      } else {
        $x--;
      }
    }
    return $cc;
  }

  static public function random_in_list($ar) {
    $keys = array_keys($ar);
    $n = rand(1, count($keys));
    return $ar[$keys[$n-1]];
  }

  /********************** array***********************/
  /**
   * Retrieves a value from an array (nested or one dimension) with default value.
   *
   * @param $ar
   *   array.
   * @param $parents
   *   key name or keys array
   * @param $default
   *   default value if key not exists
   * @return
   *   value or default.
   *
   */

  static public function array_get($ar, $parents, $default=null) {
    if(!$ar) return $default;
    if(!is_array($ar)) return false;
    if(is_array($parents)) {
      $ref = &$ar;
      foreach($parents as $k) {
        if(self::array_get($ref, $k) !== FALSE) {
          $ref = &$ref[$k];
        } else {
          return $default;
        }
      }
      return $ref;
    } else {
      if(!is_array($ar) || is_null($parents) || $parents === false) return $default;
      if(!array_key_exists($parents, $ar)) return $default;
    }
    return $ar[$parents];
  }


  static public function array_insert_element($ar, $index, $element) {
    $head = array_slice($ar, 0, $index);
    $tail = array_slice($ar, $index);
    $r = $head;
    $r[] = $element;
    $r = array_merge($r, $tail);
    return $r;
  }

  /********************** io ***********************/
  static public function create_temp_file($ext = 'temp', $path = '/tmp') {
    $fn = $path.'/'.self::random_string(8).'.'.$ext;
    return $fn;
  }

  static public function fetch_file_save($url, $dest) {
    //$str =  "fetch $url \n";
    //DT::dnotice($str);
    $data = file_get_contents($url);
    if($data) {
      file_put_contents ( $dest , $data);
      return true;
    } else {
      DT::dnotice($url.' fetch fail!');
      return false;
    }
  }

  /********************** fetch url***********************/
  static public function fetch_url($url, $cache_path = false, $fn=null, $reset = false) {
    if($cache_path) {
      file_prepare_directory($cache_path, FILE_CREATE_DIRECTORY);
      if(!$fn) {
        $fn = $url;
      }
      $fn = self::sanitize($fn);
      $fn_cache = $cache_path .'/'.$fn;
      if(!file_exists($fn_cache) || $reset) {
        $data  = self::_fetch_url_data($url);
        file_put_contents($fn_cache, $data);
      } else {
        $data = file_get_contents($fn_cache);
      }
    } else {
      $data  = self::_fetch_url_data($url);
    }
    return $data;
  }

  static public function _fetch_url_data($url) {
    $data = false;
    sleep(1);
    if(($data = self::_fetch_url_data0($url))) return $data;
    if(($data = self::_fetch_url_data1($url))) return $data;
    if(($data = self::_fetch_url_data2($url))) return $data;
    return $data;
  }

  static private function _fetch_url_data0($url) {
    $data  = @file_get_contents($url);
    return $data;
  }

  static private function _fetch_url_data1($url) {
    $opts = array(
              'http'=>array('method'=>"GET", 'header'=>"Accept-language: en\r\n" . "Cookie: foo=bar\r\n")
            );
    $context = stream_context_create($opts);
    $data = @file_get_contents($url, false, $context);
    return $data;
  }

  static public function _fetch_url_data2($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    return $output;
  }

  /********************** simple debug***********************/
  static public function dbug($v, $flag="", $printfriendly = TRUE) {
    ob_start();
    if($flag) echo 'FLAG: '.$flag."\n";
    if(is_string($v)) echo $v."\n";
    else var_export($v);
    $s = ob_get_contents();
    ob_end_clean();
    if($printfriendly) {
      $s0 = "<div style='border:1px solid #ccc;background:LightGreen;width:800px; '>";
      if(!empty($flag)) $s0 .= "<h1>$flag</h1>";
      $s1 = htmlspecialchars($s);
      $s1 = str_replace("\n", "<br/>", $s1);
      $s1 = str_replace(" ", "&nbsp;", $s1);
      $s0 .= $s1;
      $s0 .= "</div>";
      $s = $s0;
    }
    echo $s;
  }

  static public function dbugmessage($v, $flag="", $printfriendly = TRUE) {
    ob_start();
    if($flag) echo 'FLAG: '.$flag."\n";
    if(is_string($v)) echo $v."\n";
    else var_export($v);
    $s = ob_get_contents();
    ob_end_clean();
    if($printfriendly) {
      $s0 = "<div style='border:1px solid #ccc;background:LightGreen;width:800px; '>";
      if(!empty($flag)) $s0 .= "<h1>$flag</h1>";
      $s1 = htmlspecialchars($s);
      $s1 = str_replace("\n", "<br/>", $s1);
      $s1 = str_replace(" ", "&nbsp;", $s1);
      $s0 .= $s1;
      $s0 .= "</div>";
      $s = $s0;
    }
    drupal_set_message($s);
    //echo $s;
  }

  static public function dnotice($msg) {
    $level = E_USER_NOTICE;
    trigger_error($msg, $level);
  }

  static public function dobjiterate($o, $deep = 1, $return = false) {
    $t = gettype($o);
    $ar = self::_darrayiterate($o, $deep);
    if($return) {
      $r = var_export($ar, true);
      return $r;
    } else {
      self::dbug($ar, $t);
    }
  }

  static public function _darrayiterate(&$o, $deep = 1) {
    //DT::dbug(gettype($o));
    $ar = array();
    foreach($o as $key => &$value) {
      $t = gettype($value);
      switch($t) {
        case 'string':
        case 'integer':
          $c = "$key: ($t) $value";
          break;
        case 'boolean':
          $c = "$key: ($t) ".(($value)?'true':'false');
          break;
        case 'object':
          $c = $key.':'.$t;

        default:
          $c = $key.':'.$t;
      }

      if($deep > 1) {
        if($t == 'array') {
          $c = array('self' => $c);
          $child = self::_darrayiterate($value, $deep-1);
          $c['child'] = $child;
        } else if($t == 'object') {
          $c = array('self' => $c);
          $child = self::_darrayiterate($value, $deep-1);
          $c['child'] = $child;
        }

      }
      $ar[] = $c;
    }
    return $ar;
  }
}
