<?php

// 2015.08.11
function num_generator($init, $max) {
   if($init > $max) throw new LogicException('num_gen(): initial value is larger than max value');
   if($init < 0 || $max < 0) throw new LogicException('num_gen(): negative value.');
   for($i= $init; $i <= $max; $i ++) yield $i;
}

function json_func_expr($json)
{
    return preg_replace_callback(
        '/(?<=:)"function\((?:(?!}").)*}"/',
        'json_strip_escape',
        $json
    );
}

function json_strip_escape($string)
{
    return str_replace(
    array('\n','\t','\"', '\\\\','\\/'),
    array('','','"','\\','/'),
    substr($string[0],1,-1)
  );
}

function random_string($length = 5) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

function get_distinct_tmp_dir($dir) {
  //$targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload" . random_string(); 
  $targetDir = $dir . random_string();
  if(file_exists($targetDir)) return get_distinct_tmp_dir($dir);
  else return $targetDir; 
}

function del_dir_tree($dir) { 
  $files = array_diff(scandir($dir), array('.','..')); 
  foreach ($files as $file) { 
    (is_dir("$dir/$file")) ? del_dir_tree("$dir/$file") : unlink("$dir/$file"); 
  } 
  return rmdir($dir); 
} 

/*  $path = 
    /test       -> test
    test/       -> throw exception
    hello/world -> world
    hello       -> hello
*/
function extract_last_path($path) {
  $r = split('/', $path);
  $size = count($r);
  $got = $r[$size-1];
  if(empty($got)) throw new Exception('extract_last_path(): empty.');
  else return $got;
}

function extract_file_ext($file_name) {
  $r = split('\.', $file_name);
  $ext = $r[count($r)-1];
  //if(empty($ext)) throw new Exception('extract_file_ext(): empty.');
  if(empty($ext)) return false;
  else return $ext; 
}

function filename_without_ext($file_name) {
  $r = split('\.', $file_name);
  $size = count($r);
  if($size == 1) return $file_name; // no extension

  switch($size) {
    case 1:
      return $file_name;
    case 2:
      return $r[0];
  }

  $first = $r[0];
  unset($r[0]);       // remove first
  unset($r[$size-1]); // remove last

  return array_reduce($r, function($acc, $item){
    return $acc . '.' . $item;
  }, $first);
}

// $i , integer
// equal $ret = sprintf('%03d', $i);
function strval3($i) {
  $s = strval($i);
  switch(strlen($s)) {
    case 0:
       $ret = false;
       break;
    case 1:
       $ret = '00' . $s;
       break;
    case 2:
       $ret = '0' . $s;
       break;
    default:
       $ret = $s;
  }
  return $ret;
}



function unzip($file) {
  // get the absolute path of $file
  $path = pathinfo(realpath($file), PATHINFO_DIRNAME);

  $zip = new ZipArchive;
  $res = $zip->open($file);
  if ($res === TRUE) {
    // extract it to the path we determined above
    $zip->extractTo($path);
    $zip->close();
    return true;
    //echo "WOOT! $file extracted to $path";
  } else {
    return false;
    //echo "Doh! I couldn't open $file";
  }  
}

// 2015.08.27 for unzip zip64 format file. only PHP5.6 can unzip zip64 file.
function ext_unzip($file) {
  // get the absolute path of $file
  $path = pathinfo(realpath($file), PATHINFO_DIRNAME);
  
  exec("/usr/bin/unzip -o $file -d $path ", $output, $status);
  
  if ($status === 0) { // success
    //echo "WOOT! $file extracted to $path";
    return true;
  } else {
    //echo "Doh! I couldn't open $file";
    return false;
  }
}  


function unzip_zipfile_of_dir($dir) {
  
  if(!is_dir($dir)) { throw new Exception('unzip_zipfile_of_dir(): not directory.'); }

  if (!$dir_handler = opendir($dir)) { throw new Exception('unzip_zipfile_of_dir(): cannot open directory.'); }

  while (($name = readdir($dir_handler)) !== false) {
    if(is_dir($name)) continue;    
    $ext = extract_file_ext($name);
    if(empty($ext)) continue;
    if($ext == 'zip' || $ext == 'ZIP') {
      $to_unzip = $dir . '/' . $name;
      if(unzip($to_unzip)) { unlink($to_unzip); }
      else { throw new Exception('unzip_zipfile_of_dir(): unzip() error.'); }
    }
  }
}

// 2015.08.27 for unzip zip64 format file. only PHP5.6 can unzip zip64 file.
function ext_unzip_zipfile_of_dir($dir) {
  
  //mylog('called', 'ext_unzip.txt');

  if(!is_dir($dir)) { throw new Exception('ext_unzip_zipfile_of_dir(): not directory.'); }

  if (!$dir_handler = opendir($dir)) { throw new Exception('ext_unzip_zipfile_of_dir(): cannot open directory.'); }

  while (($name = readdir($dir_handler)) !== false) {
    if(is_dir($name)) continue;    
    $ext = extract_file_ext($name);
    if(empty($ext)) continue;
    if($ext == 'zip' || $ext == 'ZIP') {
      $to_unzip = $dir . '/' . $name;
      if(ext_unzip($to_unzip)) { unlink($to_unzip); }
      else { throw new Exception('ext_unzip_zipfile_of_dir(): ext_unzip() error.'); }
    }
  }
}