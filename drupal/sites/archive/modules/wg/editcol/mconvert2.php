<?php
/*
  Modeified from mconvert.php. Use Yihong's conversion function :

  module coll 下的 coll.inc 的 328 行 轉檔用
  _coll_make_public($identifier, $reset = false)

*/

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

if(empty($ticket)) { mylog('empty ticket', 'mconvert-bug.txt'); exit(0); }

$archives = variable_get("archives_for_convert_$ticket", array());
if(empty($archives)) { mylog('empty archives', 'mconvert-bug.txt'); variable_del("archives_for_convert_$ticket"); exit(0); }

variable_del("archives_for_convert_$ticket");

foreach($archives as $archive) {
  //$store         = $archive['store'];
  $collection_id = $archive['collection_id'];
  //$files         = $archive['files'];

  //mylog($collection_id, 'bbb.txt');

  _coll_make_public($collection_id, false); // defined at coll/coll.inc, call dt::ogg2webm defined at dt/dt.class.inc
                                            // use avconv -i in.ogv -s 480x360 -bt 700k -ab 128k -f webm -y out.webm
}
