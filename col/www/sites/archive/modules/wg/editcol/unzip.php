<?php

$argv   = drush_get_arguments();
$srcdir = $argv[2];
$ticket = $argv[3];

if(empty($ticket)) { mylog('empty ticket', 'unzip-bug.txt'); exit(0); }
if(empty($srcdir)) { mylog('empty directory', 'unzip-bug.txt'); exit(0); }

$mapping = variable_get("mapping_for_unzip_$ticket", array());
if(empty($mapping)) { mylog('empty mapping', 'unzip-bug.txt'); variable_del("mapping_for_unzip_$ticket"); exit(0); }

variable_del("mapping_for_unzip_$ticket");

try {
  ext_unzip_zipfile_of_dir($srcdir);      // unzip the zip archive of uploaded files
  //mylog('done unzip', 'done_unzip.txt');
  multiple_upload($srcdir, $mapping);     // will move the files and fork mconvert.php, defined in editcol.upload.php
} catch(Exception $e) {
  mylog("Exception:" . $e->message, 'unzip-bug.txt');
}
