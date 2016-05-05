<?php
class DT {

  static public function filesystem_base_path() {
    if(!isset($GLOBALS['filesystem_base_path'])) {
      $search = "includes".DIRECTORY_SEPARATOR."bootstrap.inc";

      for($path=dirname(__FILE__); !file_exists($path.DIRECTORY_SEPARATOR.$search); $path.= DIRECTORY_SEPARATOR."..") {
        // do nothing
      }
      $GLOBALS['filesystem_base_path'] = realpath($path);
    }
    return $GLOBALS['filesystem_base_path'];
  }

  static public function list_extract_values($string) {
    $values = array();

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    foreach ($list as $position => $text) {
      $value = $key = FALSE;
      $matches = array();
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        $key = strtolower(trim($matches[1]));
        $value = trim($matches[2]);
      } else {
        return;
      }
      $values[$key] = $value;
    }
    return $values;
  }

  /**
   * wildcrad ls
   */
  static public function ls ($path=".", $rec=false, $pattern="/.*/") {
    $files = self::_ls($path, $rec, $pattern);
    $r = drupal_map_assoc($files);
    ksort($r);
    return $r;
  }

  static private function _ls($path=".", $rec=false, $pattern="/.*/") {
    $pattern=preg_replace("|(/.*/[^S]*)|s", "\\1S", $pattern);
    while (substr($path,-1,1)=="/") $path=substr($path,0,-1);
    if (!is_dir($path)) $path=dirname($path);
    if ($rec!==true) $rec=false;
    $d=dir($path);
    $ret=Array();
    while (false!==($e=$d->read())) {
      if($e[0] == '.') continue; // exclude all hidden file
      if ($rec && is_dir($path."/".$e)) {
        $ret=array_merge($ret, self::_ls($path."/".$e,$rec,$pattern));
        continue;
      }
      if (!preg_match($pattern,$e)) continue;
      $ret[]=$path."/".$e;
    }
    return $ret;
  }

  /**
   * recursive rm dir
   */
  static public function rrmdir($dir, $include_self = true) {
    if(!file_exists($dir)) return;
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
      $fullname = $dir.'/'.$file;
      if(is_dir($fullname)) {
        self::rrmdir($fullname);
      } else {
        unlink($fullname);
      }
    }
    if($include_self) {
      rmdir($dir);
    }
  }

  /**
   * fputcsv, convert all " to ＂(fullwidth quotation mark ＂ U+FF02 &#65282;)
   */
  static public function fputcsv($fh, array $fields, $force_text = false) {
    $delimiter = ',';
    $enclosure = '"';
    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');

    $output = array();
    foreach ($fields as $field) {
      $field = str_replace('"', '＂', $field);
      $s = $field;
      if($force_text) {
        if(empty($s)) {
          $s = $enclosure .$s . $enclosure;
        } else {
          $s = '"' . '='.'"' . '"' . $s . '"' . '"' . '"';
        }
      } else {
        if(preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field)) {
          $s = str_replace($enclosure, $enclosure . $enclosure, $field);
        } else {
          $s = $field;
        }
        $s = $enclosure .$s . $enclosure;
      }
      $output[] = $s;
    }

    fwrite($fh, join($delimiter, $output) . "\n");
  }

  static public function makeparameter($ar ,$assign=' ', $sep=' ') {
    $tt = array();
    foreach($ar as $k=>$v) {
      if(is_numeric($k)) {
        $tt[] = $v;
      } else {
        $tt[] = $k.$assign.$v;
      }
    }
    $s = implode($sep, $tt);
    return $s;
  }

  static public function tiff2jpeg($tiff, $jpeg) {
    // use imagemagick
    $tiff = drupal_realpath($tiff);
    $jpeg = drupal_realpath($jpeg);
    $cmd = 'convert '.$tiff.' '.$jpeg;
    shell_exec($cmd);
  }

  static public function ogg2webm($video, $webm, $size='480x360', $tmppath = '/tmp') {
    // use avconv
    $video = drupal_realpath($video);
    $tmp = $tmppath.'/'.str_replace('/', '_', $video).'.webm';
    $cmd = "avconv -i ".$video." -s ".$size." -bt 700k -ab 128k -f webm -y ".$tmp;
    @shell_exec($cmd);
    $webm = drupal_realpath($webm);
    rename($tmp, $webm);
  }

  static public function video_get_icon($video, $jpeg, $size='480x360', $itsoffset=104) {
    // use avconv
    $cmd = "avconv -y -itsoffset -".$itsoffset." -i ".$video." -vcodec mjpeg -vframes 1 -an -f rawvideo -s ".$size." ".$jpeg;
    shell_exec($cmd);
  }

  static public function get_file_info($fn) {
    $info = array();
    if(!file_exists($fn)) return $info;
    $mime = self::get_file_mime($fn);
    if($mime === false ) return $info;
    $info = pathinfo($fn);
    $info['size'] = filesize($fn);
    $info['hsize'] = self::human_filesize($info['size'], 1);
    $info['mtime'] = filemtime($fn);
    $info['hmtime'] = date('Y.m.d H:i', $info['mtime']);
    $info['mediatype'] = $mime;

    $ainfo = array();
    if($mime == 'image/tiff') {
      $ainfo = self::get_image_info($fn, false);
    } else if($mime == 'video/ogg') {
      $ainfo = self::get_ogg_info($fn, false);
    }
    if($ainfo) {
      $info = self::array_merge($info, $ainfo);
    }
    return $info;
  }

  static public function human_filesize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
  }

  static public function get_file_mime($fn) {
    $mime_map = array(
                  'ogv' => 'video/ogg',
                  'tif' => 'image/tiff',
                  'tiff' => 'image/tiff',
                  'jpg' => 'image/jpeg',
                );
    $ext = pathinfo ( $fn , PATHINFO_EXTENSION );
    $ext = strtolower( $ext );
    if( array_key_exists($ext, $mime_map)) {
      return $mime_map[$ext];
    } else {
      return false;
    }
  }

  static public function get_ogg_info($fn, $getbaseinfo = true) {
    $info = array();
    if($getbaseinfo) {
      $info = self::get_file_info();
      if( empty($info) ) return $info;
    }

    require_once 'lib/ogg.class.php';
    $ff = drupal_realpath($fn);
    $video=new Ogg ($ff, NOCACHING);
    if ($video->LastError) {
      return $info;
    } else {
      $info['duration'] = $video->Streams['duration'];
      $info['hduration'] = $info['duration'].'s';
      $info['txt'] = 'Duration: '.$info['hduration'];
    }
    return $info;
  }

  static public function get_image_info($fn, $getbaseinfo = true) {
    $info = array();
    if($getbaseinfo) {
      $info = self::get_file_info();
      if( empty($info) ) return $info;
    }

    $wh = getimagesize($fn);
    if($wh) {
      $info['width'] = $wh[0];
      $info['height'] = $wh[1];
      $info['txt'] = $info['width'].'x'.$info['height'];
    }
    return $info;
  }

  static public function downloadfile($file, $fname = null) {
    if(file_exists($file)) {
      if(!$fname) $fname = $file;
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename='.basename($fname));
      header('Content-Transfer-Encoding: binary');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file));
      ob_clean();
      flush();
      readfile($file);
      exit;
    }

  }

  static public function random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
  }

  static public function str2var($str) {
    $str = strtolower($str);
    $str = preg_replace('%[^a-z0-9_]%i', '_', $str);
    return $str;
  }

  static public function string_to_array($s, $del=';', $unique = TRUE) {
    $r = explode($del, $s);
    if($unique) {
      $r = array_unique($r);
    }
    $r = array_values($r);
    $r = self::array_filter_and_trim($r);
    return $r;
  }

  static public function rawurlencode_path($path) {
    $arr = array();
    $ar = explode('/', $path);
    foreach($ar as $item) {
      $arr[] = rawurlencode($item);
    }
    return implode('/', $arr);
  }

  static public function rawurlencode($url) {
    $encoded='';
    $length=mb_strlen($url);
    for($i=0; $i<$length; $i++) {
      $encoded.='%'.wordwrap(bin2hex(mb_substr($url,$i,1)),2,'%',true);
    }
    return $encoded;
  }

  static public function create_temp_file($ext = 'temp', $path = '/tmp') {
    $fn = $path.'/'.self::random_string(8).'.'.$ext;
    return $fn;
  }

  static public function create_temp_folder($path = '/tmp', $withdate=true) {
    $fn = $path.'/';
    if($withdate) $fn .= date('Ymdhm_');
    $fn .= self::random_string(8);
    file_prepare_directory($fn, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    return $fn;
  }

  /**
   * explode
   * support multiple dels explode
   * parameters order change
   */
  static public function explode($dels, $s, &$r = array()) {
    if(!is_array($dels)) $dels = array($dels);
    $del = array_shift($dels);
    $parts = self::string_to_array($s, $del, false);
    if($dels) {
      foreach($parts as &$item) {
        $item = self::explode($item, $dels, $r);
      }
    }
    $parts = self::array_keys_multi($parts);
    return $parts;
  }

  static public function explode_get_first($del, $str) {
    $ar = explode($del, $str);
    $f = array_shift($ar);
    return $f;
  }

  static public function explode_map( $delimiter, $str, $varkeys) {
    $limit = count($varkeys);
    $ar = explode($delimiter, $str, $limit);
    $r = array();
    $pre = '';
    foreach($varkeys as $k) {
      $x = array_shift($ar);
      if(is_null($x)) {
        $x = $pre;
      }
      $pre = $x;
      if($k != '') $r[$k] = $x;
    }
    return $r;
  }

  static public function fetch_url($url) {
    $opts = array(
              'http'=>array('method'=>"GET", 'header'=>"Accept-language: en\r\n" . "Cookie: foo=bar\r\n")
            );
    $context = stream_context_create($opts);
    $data = file_get_contents($url, false, $context);
    return $data;
  }

  /**
   * Set value to array (nested or one dimension) with default value.
   *
   * @param $ar
   *   array.
   * @param $key
   *   key name
   * @param $value
   *   value
   * @param $override
   *   if @key exists, override or not
   * @return
   *   true: set.
   *   false: not set, may be key already exists when $override is false
   */
  static public function array_set(&$ar, $key, $value, $override = FALSE) {
    if($override || !array_key_exists($key, $ar)) {
      $ar[$key] = $value;
      return TRUE;
    }
    return FALSE;
  }

  static public function array_get_first($ar) {
    $f = array_shift($ar);
    return $f;
  }

  static public function array_keys_multi($array, &$vals = array()) {
    foreach ($array as $key => $value) {
      if (is_array($value)) {
        self::array_keys_multi($value,$vals);
      } else {
        $vals[] = $value;
      }
    }
    return $vals;
  }

  /**
   * extend an array with default values
   * @param $ar
   * @param $default
   * @return
   */
  static public function array_extend(&$ar, $default) {
    if(!$ar) $ar = array();
    $ar = self::array_merge($ar, $default);
  }

  /**
   * @improvement of PHP function array_merge
   * allow null parameters
   **/
  static public function array_merge($ar1, $ar2) {
    $args = func_get_args();
    foreach($args as &$arg) {
      if(!is_array($arg)) $arg = array();
    }
    return call_user_func_array('array_merge', $args);
  }

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

  /**
   * Creates an array by using the values from the keys array as keys and the values from the values array as the corresponding values.
   **/
  static public function array_combine(array $keys , array $values, $default='') {
    self::array_complete($values, count($keys), $default);
    return array_combine($keys, $values);
  }

  /**
   * complete an array with default value by
   * a. an numeric array by assign how many elements the array should have
   * b. an associate array by assign keys
   *
   * @param $ar
   *
   * @param $keys
   *
   * @param $keep
   * for associate array only
   * preserve elements which not in $keys
   * @return
   */
  static public function array_complete(&$ar, $keys, $value="", $keep=TRUE) {
    if(!is_array($ar)) $ar = array();
    if(is_int($keys)) { // numeric array
      $c = count($ar);
      if($c < $keys) {
        for($i=$c; $i<$keys; $i++) {
          $ar[$i] = $value;
        }
      }
    } else { // associate array
      $c_keys = array_keys($ar);
      $tmp = $ar;
      $ar = array();
      foreach($keys as $k) {
        if(array_key_exists($k, $tmp)) {
          $ar[$k] = $tmp[$k];
          unset($tmp[$k]);
        } else {
          $ar[$k] = $value;
        }
      }
      if($keep) {
        if(count($tmp)>0) {
          $ar = array_merge($ar, $tmp);
        }
      }
    }
    return;
  }

  /**
   * array_filter_and_trim
   * trim and remove empty element
   */
  static public function array_filter_and_trim($arr) {
    array_walk($arr, function(&$value) {
      if(is_string($value)) $value = trim($value);
    });
    return array_filter($arr);
  }

  static public function array_reverse_key_value($arr) {
    $ar = array();
    foreach($arr as $k=>$v) {
      $ar[$v] = $k;
    }
    return $ar;
  }

  static public function menu_tree_output_list($menu, &$list) {
    $d = array();
    if($menu) {
      foreach($menu as $key=>$element) {
        if(!is_numeric($key)) continue;
        $item = array();
        $item['data'] = l($element['#title'], $element['#href']);
        if($element['#below']) {
          $item['children'] = array();
          self::menu_tree_output_list($element['#below'], $item['children']);
        }
        $list[] = $item;
      }
    }
  }

  static public function progressinfo($c, $step = 10) {
    if($c % $step == 0 ) {
      $mm = memory_get_usage();
      $mm = ceil($mm/1024);
      $mm .= ' '.get_render_time();
      self::dnotice('* '.$c.' '.$mm);
    }
  }

  static public function dnotice($msg) {
    $level = E_USER_NOTICE;
    trigger_error($msg, $level);
  }
}
