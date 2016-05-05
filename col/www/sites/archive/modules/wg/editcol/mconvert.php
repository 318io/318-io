<?php
/*
Designed to be called by drush.
    
    $uniq = get_uniq_string();

    $module_path = DRUPAL_ROOT . DIRECTORY_SEPARATOR . drupal_get_path('module','editcol');
    $script = $module_path . DIRECTORY_SEPARATOR . 'mconvert.php' . ' ' . $uniq;
    $cmd = "drush -q -l $base_url -r DRUPAL_ROOT php-script $script";
*/

/*
   $archives = array(
      [0] => array(
                'collection_id' => '10001',
                'repository_id' => 'A1000001',
                'store'         => '010',
                'files'         => array('10001_001.tif', '10001_002.tif', ...)
             ),
      [1] => array(
                'collection_id' => '10002',
                'repository_id' => 'A10000002',
                'store'         => '010',
                'files'         => array('10002_001.tif', '10002_002.tif', ...)
            ),
      ...
   );
*/

$argv   = drush_get_arguments();
$ticket = $argv[2];

//$argc = count($argv);

if(empty($ticket)) { mylog('empty ticket', 'mconvert-bug.txt'); exit(0); }

$archives = variable_get("archives_for_convert_$ticket", array());
if(empty($archives)) { mylog('empty archives', 'mconvert-bug.txt'); variable_del("archives_for_convert_$ticket"); exit(0); }

variable_del("archives_for_convert_$ticket");

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

foreach($archives as $archive) {
  $store         = $archive['store'];
  $collection_id = $archive['collection_id'];
  $files         = $archive['files'];

  $store_dir = $store;
  $src_path = drupal_realpath('public://') . DIRECTORY_SEPARATOR . 'digicoll' . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . $store_dir;
  $target_path = drupal_realpath('public://') . DIRECTORY_SEPARATOR . 'digicoll' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $store_dir;
  if(!file_exists($target_path)) mkdir($target_path); // make sure the target path

  $result = array();  
  foreach($files as $file_name) {
    $ext = strtolower(extract_file_ext($file_name));
    $file_name_no_ext = filename_without_ext($file_name);

    $src = $src_path . DIRECTORY_SEPARATOR . $file_name;

    if(array_key_exists($ext, $video_format)) {
      $target = $target_path . DIRECTORY_SEPARATOR . $file_name_no_ext . '.webm';
      $cmd = "/usr/bin/avconv -y -i $src -s '480x360' -f webm -bt 700k $target 2> /dev/null";
    } else if(array_key_exists($ext, $image_format)) {
      $target = $target_path . DIRECTORY_SEPARATOR . $file_name_no_ext . '.jpg';
      $cmd = "/usr/bin/convert $src $target 2> /dev/null";
    } else {
      $cmd = NULL;
    }

    if(!empty($cmd)) {
      exec($cmd, $output, $status);  // blocking here until finish
      $result[$file_name]['output'] = $output; 
      $result[$file_name]['status'] = ($status == 0) ? 'success' : 'failure';    
    } else {
      $result[$file_name]['output'] = 'unsupported file format.';
      $result[$file_name]['status'] = 'failure';
    }
  }
  mylog(print_r($result, true), "convert_log_$collection_id.txt");
}



/*
global $base_url;
echo $base_url . "\n";
echo drupal_realpath('public://') . "\n";

$argv = drush_get_arguments();
echo $argv[2] ;

$cmd = 'avconv -y -i beauty.mp4 -f ogg -q:v 5 -acodec libvorbis -aq 60 out.ogv 2> /dev/null';

$result = exec($cmd, $output, $status);

print_r($result);

print_r($output);

echo $status . "\n"; // 1: error, 0: success
*/

