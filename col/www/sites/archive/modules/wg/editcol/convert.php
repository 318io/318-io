<?php

/*
Designed to be called by drush.

$module_path = DRUPAL_ROOT . DIRECTORY_SEPARATOR . drupal_get_path('module','editcol');
$script = $module_path . DIRECTORY_SEPARATOR . convert.php;
$cmd = "drush -q -l $base_url -r DRUPAL_ROOT php-script $script $args";
*/

global $base_url; // drupal url
$drupal_root = DRUPAL_ROOT;
$drupal_url  = $base_url;

$argv = drush_get_arguments();
$argc = count($argv);

$store_dir = $argv[2];
$src_path = drupal_realpath('public://') . DIRECTORY_SEPARATOR . 'digicoll' . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . $store_dir;
$target_path = drupal_realpath('public://') . DIRECTORY_SEPARATOR . 'digicoll' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $store_dir;

if(!file_exists($target_path)) mkdir($target_path); // make sure the target path

$image_format = array(
  'jpg'  => NULL,
  'jpeg' => NULL,
  'png'  => NULL,
  'tif'  => NULL,
  'tiff' => NULL,
  'bmp'  => NULL,
  'gif'  => NULL,
);

$video_format = array(
  'mpg' => NULL, 'mpeg' => NULL, 'mp4' => NULL, 'm4p' => NULL, 'w4v' => NULL, 'mp2' => NULL, 'mpe' => NULL, 'mpv' => NULL, 'm2v' => NULL, 'm4v' => NULL,
  'webm' => NULL, 'mkv' => NULL, 'flv' => NULL, 'ogv' => NULL, 'ogg' => NULL,
  'mov' => NULL, 'qt' => NULL, 'avi' => NULL, 'wmv' => NULL, 'asf' => NULL,
  'rm' => NULL, 'rmvb' => NULL,
  '3gp' => NULL, '3g2' => NULL,
);

$result = array();

for($i = 3; $i < $argc; $i++) {
  $file_name = $argv[$i];
  $ext = strtolower(extract_file_ext($file_name));
  $file_name_no_ext = filename_without_ext($file_name);

  $src = $src_path . DIRECTORY_SEPARATOR . $file_name;

  if(array_key_exists($ext, $video_format)) {
    $target = $target_path . DIRECTORY_SEPARATOR . $file_name_no_ext . '.ogv';
    $cmd = "/usr/bin/avconv -y -i $src -f ogg -q:v 5 -acodec libvorbis -aq 60 $target 2> /dev/null";
  } else if(array_key_exists($ext, $image_format)) {
    $target = $target_path . DIRECTORY_SEPARATOR . $file_name_no_ext . '.jpg';
    $cmd = "/usr/bin/convert $src $target 2> /dev/null";
  } else {
    $cmd = NULL;
  }

  if(!empty($cmd)) {
    exec($cmd, $output, $status);
    $result[$file_name]['output'] = $output;
    $result[$file_name]['status'] = ($status == 0) ? 'success' : 'failure';
  } else {
    $result[$file_name]['output'] = 'unsupported file format.';
    $result[$file_name]['status'] = 'failure';
  }
}

mylog(print_r($result, true), 'convert_log.txt');
