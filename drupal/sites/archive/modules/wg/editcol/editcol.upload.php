<?php

function get_archive_file_name($collection_id, $count, $src_file_name) {
  // normalize file name
  $ext = strtolower(extract_file_ext($src_file_name));
  if(!$ext) throw new Exception('get_archive_file_name(): invalid file name.');
  $archive_file_name = $collection_id . "_" . strval3($count) . "." . $ext;
  return $archive_file_name;
}

/*
 * 檢查檔名是否為 foo_mosaic.tif
 */
function is_mosaic_file_pattern($src_file_name) {
  $name = filename_without_ext($src_file_name); // strip file extension
  $name_arr = split('_', $name);
  $length = count($name_arr);
  if($length > 1) {
    $last = $name_arr[$length-1];
    if(strcmp($last, 'mosaic') === 0) return true;
    else                              return false;
  }
  // not mosaic file
  return false;
}

/*
 * 檢查這個檔名是否是這個 collection 的合法檔名。
 *  for example,
 *  filename 10001.tif, 10001_001.tif, 10001_002.tif, 10001_003.ogv, 10001_002_icon.jpg
 *  are all files of collection 10001
 */
function is_file_of_collection($filename, $nid) {
  $snid = strval($nid);
  $name = filename_without_ext($filename);
  $name_arr = split('_', $name);
  $length = count($name_arr);
  if($length > 0) {
    $first = $name_arr[0];
    if(strcmp($snid, $first) === 0) return true;
    else                            return false;
  }
  return false;
}

/*
 *  傳回的是 public:// 類型的路徑，需要真實路徑需再使用 drupal_realpath
 *
 *  若 nid = 21030, $store_dir = 021
 *  return Array(
 *    $archive_path,       // [0] public://digicoll/archive/021
 *    $archive0_path,      // [1] public://digicoll/archive0/021
 *    $archive_mosbk_path, // [2] public;//digicoll/archive_mosbk
 *    $public_path,        // [3] public://digicoll/public/021
 *    $public0_path,       // [4] public://digicoll/public0/021
 *    $meta_path,          // [5] public://digicoll/meta/021
 *  )
 *
 */
function get_store_of_collection($nid) {

  if(!is_integer($nid)) $nid = intval($nid); // try to convert $nid to integer

  $store_dir = strval3(floor($nid / 1000));

  // archive
  $archive_store_path = 'public://digicoll' . DIRECTORY_SEPARATOR . 'archive';
  $archive_path = $archive_store_path . DIRECTORY_SEPARATOR . $store_dir;
  if (!file_exists($archive_path)) { drupal_mkdir($archive_path); }

  // archive0
  $archive0_store_path = 'public://digicoll' . DIRECTORY_SEPARATOR . 'archive0';
  $archive0_path = $archive0_store_path . DIRECTORY_SEPARATOR . $store_dir;
  if (!file_exists($archive0_path)) { drupal_mkdir($archive0_path); }

  // archive_mosbk
  $archive_mosbk_store_path = 'public://digicoll' . DIRECTORY_SEPARATOR . 'archive_mosbk';
  $archive_mosbk_path = $archive_mosbk_store_path . DIRECTORY_SEPARATOR . $store_dir;
  if (!file_exists($archive_mosbk_path)) { drupal_mkdir($archive_mosbk_path); }

  // public
  $public_store_path = 'public://digicoll' . DIRECTORY_SEPARATOR . 'public';
  $public_path = $public_store_path . DIRECTORY_SEPARATOR . $store_dir;
  if (!file_exists($public_path)) { drupal_mkdir($public_path); }

  // public0
  $public0_store_path = 'public://digicoll' . DIRECTORY_SEPARATOR . 'public0';
  $public0_path = $public0_store_path . DIRECTORY_SEPARATOR . $store_dir;
  if (!file_exists($public0_path)) { drupal_mkdir($public0_path); }

  // meta
  $meta_store_path = 'public://digicoll' . DIRECTORY_SEPARATOR . 'meta';
  $meta_path = $meta_store_path . DIRECTORY_SEPARATOR . $store_dir;
  if (!file_exists($meta_path)) { drupal_mkdir($meta_path); }

  return array($archive_path, $archive0_path, $archive_mosbk_path, $public_path, $public0_path, $meta_path);
}

/*
 * 取得某一個 collection 的所有檔案名稱
 */
function get_collection_files($nid) {
  $archives      = get_store_of_collection($nid);
  $archive_path  = drupal_realpath($archives[0]);
  $archive0_path = drupal_realpath($archives[1]);
  $archive_mosbk_path = drupal_realpath($archives[2]);
  $public_path   = drupal_realpath($archives[3]);
  $public0_path  = drupal_realpath($archives[4]);
  $meta_path     = drupal_realpath($archives[5]);

  $files = array('archive' => array(), 'archive0' => array());

  //-----------------------------------------------------------
  if (!is_dir($archive_path) || !$archive_path_handler = opendir($archive_path)) {
    throw new Exception("get_collection_files(): cannot read $archive_path.");
  }
  $files['archive'] = array();
  while (($fname = readdir($archive_path_handler)) !== false) {
    if(is_file_of_collection($fname, $nid)) { $files['archive'][] = $fname; }
  }

  //-----------------------------------------------------------
  if (!is_dir($archive0_path) || !$archive0_path_handler = opendir($archive0_path)) {
    throw new Exception("get_collection_files(): cannot read $archive0_path.");
  }
  $files['archive0'] = array();
  while (($fname = readdir($archive0_path_handler)) !== false) {
    if(is_file_of_collection($fname, $nid)) { $files['archive0'][] = $fname; }
  }

  //-----------------------------------------------------------
  if (!is_dir($archive_mosbk_path) || !$archive_mosbk_path_handler = opendir($archive_mosbk_path)) {
    throw new Exception("get_collection_files(): cannot read $archive_mosbk_path.");
  }
  $files['archive_mosbk'] = array();
  while (($fname = readdir($archive_mosbk_path_handler)) !== false) {
    if(is_file_of_collection($fname, $nid)) { $files['archive_mosbk'][] = $fname; }
  }

  //-----------------------------------------------------------
  if (!is_dir($public_path) || !$public_path_handler = opendir($public_path)) {
    throw new Exception("get_collection_files(): cannot read $public_path.");
  }
  $files['public'] = array();
  while (($fname = readdir($public_path_handler)) !== false) {
    if(is_file_of_collection($fname, $nid)) { $files['public'][] = $fname; }
  }

  //-----------------------------------------------------------
  if (!is_dir($public0_path) || !$public0_path_handler = opendir($public0_path)) {
    throw new Exception("get_collection_files(): cannot read $public0_path.");
  }
  $files['public0'] = array();
  while (($fname = readdir($public0_path_handler)) !== false) {
    if(is_file_of_collection($fname, $nid)) { $files['public0'][] = $fname; }
  }

  //-----------------------------------------------------------
  if (!is_dir($meta_path) || !$meta_path_handler = opendir($meta_path)) {
    throw new Exception("get_collection_files(): cannot read $meta_path.");
  }
  $files['meta'] = array();
  while (($fname = readdir($meta_path_handler)) !== false) {
    if(is_file_of_collection($fname, $nid)) { $files['meta'][] = $fname; }
  }

  return $files;
}

/*
 * Only work for 'tif' files.
 *
 * for example collection 10001
 *
 * make thumbnail in
 *   - public://digicoll/archive/010/thumb/10001_001.jpg
 *   - public://digicoll/archive0/010/thumb/10001_001.jpg
 */
function make_thumbnail_of_collection($nid) {
  $targets = get_store_of_collection($nid);
  $target_path       = drupal_realpath($targets[0]);
  $target_thumb_path = $target_path . '/thumb';
  $target_zero_path = drupal_realpath($targets[1]);
  $target_zero_thumb_path = $target_zero_path . '/thumb';

  if(!file_exists($target_thumb_path))      mkdir($target_thumb_path);
  if(!file_exists($target_zero_thumb_path)) mkdir($target_zero_thumb_path);

  //-----------------------------------------------------------------------------
  if (!is_dir($target_path) || !$target_path_handler = opendir($target_path)) {
    throw new Exception("make_thumbnail_of_collection(): cannot read $target_path.");
  }
  while (($fname = readdir($target_path_handler)) !== false) {
    if(is_file_of_collection($fname, $nid)) {
      $ext = extract_file_ext($fname);
      if($ext !== 'tif') continue;
      $file_in = $target_path . '/' . $fname;
      $file_out = $target_thumb_path . '/' . filename_without_ext($fname) . '.jpg';
      $cmd = "/usr/bin/convert $file_in -resize 400x600 $file_out";
      $ret_code = proc_close(proc_open($cmd, array(), $foo));
      if($ret_code == -1) { drupal_set_message("make_thumbnail_of_collection(): external command error!!"); }
    }
  }
  //-----------------------------------------------------------------------------
  if (!is_dir($target_zero_path) || !$target_zero_path_handler = opendir($target_zero_path)) {
    throw new Exception("make_thumbnail_of_collection(): cannot read $target_zero_path.");
  }
  while (($fname = readdir($target_zero_path_handler)) !== false) {
    if(is_file_of_collection($fname, $nid)) {
      $ext = extract_file_ext($fname);
      if($ext !== 'tif') continue;
      $file_in = $target_zero_path . '/' . $fname;
      $file_out = $target_zero_thumb_path . '/' . filename_without_ext($fname) . '.jpg';
      $cmd = "/usr/bin/convert $file_in -resize 400x600 $file_out";
      $ret_code = proc_close(proc_open($cmd, array(), $foo));
      if($ret_code == -1) { drupal_set_message("make_thumbnail_of_collection(): external command error!!"); }
    }
  }
}

function deprecated_delete_thumbnail_of_collection($nid) {
  $targets = get_store_of_collection($nid);
  $target_path       = drupal_realpath($targets[0]);
  $target_thumb_path = $target_path . '/thumb';
  $target_zero_path = drupal_realpath($targets[1]);
  $target_zero_thumb_path = $target_zero_path . '/thumb';

  // delete in archive/thumb
  if(file_exists($target_thumb_path)) {
    if (!is_dir($target_thumb_path) || !$target_thumb_path_handler = opendir($target_thumb_path)) {
      throw new Exception("delete_thumbnail_of_collection(): cannot read $target_thumb_path.");
    }
    while (($fname = readdir($target_thumb_path_handler)) !== false) {
      if(is_file_of_collection($fname, $nid)) { unlink($target_thumb_path . '/' . $fname); }
    }
  }

  // delete in archive0/thumb
  if(file_exists($target_zero_thumb_path)) {
    if (!is_dir($target_zero_thumb_path) || !$target_zero_thumb_path_handler = opendir($target_zero_thumb_path)) {
      throw new Exception("delete_thumbnail_of_collection(): cannot read $target_zero_thumb_path.");
    }
    while (($fname = readdir($target_zero_thumb_path_handler)) !== false) {
      if(is_file_of_collection($fname, $nid)) { unlink($target_zero_thumb_path . '/' . $fname); }
    }
  }
}

/*
 *
 */
function delete_thumbnail_of_collection($nid, $image_style) {
  $targets  = get_store_of_collection($nid);
  $public   = $targets[3];
  $public0  = $targets[4];
  $the_public_thumb = drupal_realpath(image_style_path($image_style, $public));   // thumbnail path
  $the_public0_thumb = drupal_realpath(image_style_path($image_style, $public0)); // thumbnail path

  // delete thumbs of public/$store
  if(file_exists($the_public_thumb)) {
    if (!is_dir($the_public_thumb) || !$the_public_thumb_handler = opendir($the_public_thumb)) {
      throw new Exception("delete_thumbnail_of_collection(): cannot read $the_public_thumb.");
    }
    while (($fname = readdir($the_public_thumb_handler)) !== false) {
      if(is_file_of_collection($fname, $nid)) { unlink($the_public_thumb . '/' . $fname); }
    }
  }

  // delete thumbs of public0/$store
  if(file_exists($the_public0_thumb)) {
    if (!is_dir($the_public0_thumb) || !$the_public0_thumb_handler = opendir($the_public0_thumb)) {
      throw new Exception("delete_thumbnail_of_collection(): cannot read $the_public0_thumb.");
    }
    while (($fname = readdir($the_public0_thumb_handler)) !== false) {
      if(is_file_of_collection($fname, $nid)) { unlink($the_public0_thumb . '/' . $fname); }
    }
  }
}

function delete_all_thumbnail_of_collection($nid) {
  $all_styles = array_keys(image_styles());
  foreach($all_styles as $style) { delete_thumbnail_of_collection($nid, $style); }
}

function deprecated_rebuild_thumbnail_of_collection($nid) {
  delete_thumbnail_of_collection($nid);
  make_thumbnail_of_collection($nid);
}

/*
 *  public 內的 file , 若是 .webm 則會有相對應的 icon 檔
 *  如, 20567_001.webm 會有 20567_001_icon.jpg
 *  這些 icon 應該也要一併 reorder
 */
function __reorder_files($nid, $filename_array, $location_path) {
  $count = 0;
  $tmp_prefix = uniqid();
  $tmp_files = array();

  if(empty($filename_array)) return;

  // rename to a temp name to avoid conflict.
  foreach($filename_array as $fname) {
    $count++;
    $tmp_name = $tmp_prefix . $fname;
    $src = $location_path . '/' . $fname;
    $dst = $location_path . '/' . $tmp_name;
    if(!rename($src, $dst)) {
      throw new Exception("__reorder_files(): rename from $src to $dst error.");
    }
    $tmp_files[] = $tmp_name;
  }

  // rename temp files
  $count = 0;
  foreach($tmp_files as $fname) {
    $count++;
    $new_name = get_archive_file_name($nid, $count, $fname); // $fname 只是用來取得 file extension 用的, 如 file.jpg => jpg
    $src = $location_path . '/' . $fname;
    $dst = $location_path . '/' . $new_name;
    if(!rename($src, $dst)) {
      throw new Exception("__reorder_files(): rename from $src to $dst error.");
    }
  }
}

/*
 * 重新編排一個 collection 的所有檔案的順序號。
 */
function reorder_collection_files($nid) {
  $targets  = get_store_of_collection($nid);
  $archive  = drupal_realpath($targets[0]);
  $archive0 = drupal_realpath($targets[1]);
  $archive_mosbk = drupal_realpath($targets[2]);
  $public   = drupal_realpath($targets[3]);
  $public0  = drupal_realpath($targets[4]);

  $all_files = get_collection_files($nid);

  $archive_files   = $all_files['archive'];
  $archive0_files  = $all_files['archive0'];
  $archive_mosbk_files = $all_files['archive_mosbk'];
  $public_files    = $all_files['public'];
  $public0_files   = $all_files['public0'];

  try {
    __reorder_files($nid, $archive_files,  $archive);
    __reorder_files($nid, $archive0_files, $archive0);
    __reorder_files($nid, $archive_mosbk_files, $archive_mosbk);
    __reorder_files($nid, $public_files,   $public);
    __reorder_files($nid, $public0_files,  $public0);
  } catch(Exception $e) {
    dbug_message($e);
  }
}

/*
 * delete all files of a collection
 */
function delete_collection_files($nid) {

  $targets  = get_store_of_collection($nid);
  $archive  = drupal_realpath($targets[0]);
  $archive0 = drupal_realpath($targets[1]);
  $archive_mosbk = drupal_realpath($targets[2]);
  $public   = drupal_realpath($targets[3]);
  $public0  = drupal_realpath($targets[4]);
  $meta     = drupal_realpath($targets[5]);

  $all_files = get_collection_files($nid);

  $archive_files  = $all_files['archive'];
  $archive0_files = $all_files['archive0'];
  $archive_mosbk_files = $all_files['archive_mosbk'];
  $public_files   = $all_files['public'];
  $public0_files  = $all_files['public0'];
  $meta_files     = $all_files['meta'];    // 只會有一個檔。

  foreach($archive_files as $fname)  { unlink($archive  . '/' . $fname); }
  foreach($archive0_files as $fname) { unlink($archive0 . '/' . $fname); }
  foreach($archive_mosbk_files as $fname) { unlink($archive_mosbk . '/' . $fname); }
  foreach($public_files as $fname)   { unlink($public   . '/' . $fname); }
  foreach($public0_files as $fname)  { unlink($public0  . '/' . $fname); }
  foreach($meta_files as $fname)     { unlink($meta     . '/' . $fname); }

  //delete_thumbnail_of_collection($nid, '200_300');
  delete_all_thumbnail_of_collection($nid);
}

/*
 * 刪除藏品的某個檔名的所有相關檔案(馬賽克與非馬賽克)
 *
 * 若是影片檔 archive 內為 ogv, public 為 webm, ex 20655_001.webm, 20656_001_icon.jpg (TODO)
 */
function delete_collection_a_file($nid, $file_name) {
  $targets  = get_store_of_collection($nid);
  $archive  = drupal_realpath($targets[0]);
  $archive0 = drupal_realpath($targets[1]);
  $archive_mosbk = drupal_realpath($targets[2]);
  $public   = drupal_realpath($targets[3]);
  $public0  = drupal_realpath($targets[4]);

  $name = filename_without_ext($file_name); // 去掉副檔名

  $archive_file  = $archive  . '/' . $name . '.tif';
  $archive0_file = $archive0 . '/' . $name . '.tif';
  $public_file   = $public   . '/' . $name . '.jpg';
  $public0_file  = $public0  . '/' . $name . '.jpg';
  $archive_mosbk_file = $archive_mosbk . '/' . $name . '.tif';

  if(file_exists($archive_file))  { unlink($archive_file); }
  if(file_exists($archive0_file)) { unlink($archive0_file); }
  if(file_exists($public_file))   { unlink($public_file); }
  if(file_exists($public0_file))  { unlink($public0_file); }
  if(file_exists($archive_mosbk_file))  { unlink($archive_mosbk_file); }
}

/*
 * 若一個在 archive 內的檔案有一個相對的檔案(同檔名)在 archive0, 則這個在 archive 內的檔為馬賽克檔。
 * 相對的，archive0 內的為非馬賽克檔。
 *
 * Note: archive0 內永遠為非馬賽克檔。
 */
function has_file_in_archive0($nid, $file_name) {
  $targets  = get_store_of_collection($nid);
  //$archive   = drupal_realpath($targets[0]);
  $archive0 = drupal_realpath($targets[1]);
  $the_file = $archive0 . '/' . $file_name;
  return file_exists($the_file);
}

/*
 * 檢查此 collection 在 archive 內是否有檔案存在。
 */
function is_empty_collection($nid) {
  $files = get_collection_files($nid);
  return empty($files['archive']);
}

/*
 * 檢查某個在 archive 內的檔案是否為馬賽克檔。
 */
function is_mosaic_file_in_archive($nid, $file_name) {
  if(has_file_in_archive0($nid, $file_name)) return true;
  else                                       return false;
}

/*
 * 刪除某個藏品的某個檔案
 *   -) $mosaic = false, 刪除非馬賽克檔, 則若有馬賽克檔需一併刪除
 *   -) $mosaic = true, 刪除馬賽克檔，刪除完後需將非馬賽克檔搬回 archive 內
 *
 * Note:
 *   -) public, public0 內的相關檔案 也需一併刪除
 *   -) 最後需重新 re-order 檔案編號。
 */
function delete_archive_file_of_collection($nid, $file_name_to_delete, $mosaic = false) {
  $targets  = get_store_of_collection($nid);
  $archive  = drupal_realpath($targets[0]);
  $archive0 = drupal_realpath($targets[1]);
  $archive_mosbk = drupal_realpath($targets[2]);
  $public   = drupal_realpath($targets[3]);
  $public0  = drupal_realpath($targets[4]);

  // 檢查檔案是否存在
  if(!has_file_in_archive0($nid, $file_name_to_delete)) {   // archive0 內有檔的話，archive 一定有檔
    if(!file_exists($archive . '/' . $file_name_to_delete)) {
      throw new Exception("update_archive_file_of_collection(): file $file_name_to_delete does not exist.");
    }
  }

  if($mosaic) {  // 刪除馬賽克檔
    $name = filename_without_ext($file_name_to_delete); // 去掉副檔名
    $the_mosaic_file        = $archive  . '/' . $file_name_to_delete;
    $the_normal_file        = $archive0 . '/' . $file_name_to_delete;
    $the_public_mosaic_file = $public   . '/' . $name . '.jpg';
    $the_public_normal_file = $public0  . '/' . $name . '.jpg';

    // 刪除 archive & public 內的馬賽克檔
    if(!unlink($the_mosaic_file) || !unlink($the_public_mosaic_file)) {
      drupal_set_message("delete_archive_file_of_collection(): delete file error.");
      return false;
    }
    // 把非馬賽克檔搬到 archive & public 內
    if(!rename($the_normal_file, $the_mosaic_file) || !rename($the_public_normal_file, $the_public_mosaic_file)) {
      drupal_set_message("delete_archive_file_of_collection(): move file error.");
      return false;
    }
  } else {  // 刪除非馬賽克檔, 若有馬賽克檔存在，應一併刪除
    delete_collection_a_file($nid, $file_name_to_delete);
  }
  reorder_collection_files($nid);
  //delete_thumbnail_of_collection($nid, '200_300');
  delete_all_thumbnail_of_collection($nid);
  return true;
}

/*
 * 更新某個藏品的數位檔
 *   -) $mosaic = false, 更新非馬賽克檔
 *   -) $mosaic = true, 更新馬賽克檔
 *
 * Note:
 *   -) $upload_src_dir 是準備用來更新的檔案所在的目錄，此目錄應該只存在一個檔案。
 *   -) 最後需執行 _coll_make_public($collection_id, false); // defined at coll/coll.inc
 *
 */
function update_archive_file_of_collection($nid, $upload_src_dir, $file_name_to_update, $mosaic = false) {

  $targets  = get_store_of_collection($nid);
  $archive  = drupal_realpath($targets[0]);
  $archive0 = drupal_realpath($targets[1]);


  // 檢查檔案是否存在
  if(!has_file_in_archive0($nid, $file_name_to_update)) {      // archive0 內有檔的話，archive 一定有檔
    if(!file_exists($archive . '/' . $file_name_to_update)) {
      throw new Exception("update_archive_file_of_collection(): file $file_name_to_update does not exist.");
    }
  }

  if (!is_dir($upload_src_dir) || !$src_dir_handler = opendir($upload_src_dir)) {
    throw new Exception("update_archive_file_of_collection(): no upload directory: $upload_src_dir .");
  }

  // 此迴圈應只會執行三次，有兩次是 . 和 .. 這兩個目錄，另一個是上傳檔
  while (($fname = readdir($src_dir_handler)) !== false) {
    if(is_dir($fname)) continue; // ignore sub directory

    $the_new_file = $upload_src_dir . '/' . $fname;
    $the_old_file = $archive . '/' . $file_name_to_update;
    if(!$mosaic && has_file_in_archive0($nid, $file_name_to_update)) {
      $the_old_file = $archive0 . '/' . $file_name_to_update;
    }
    /* 本程式碼為上面程式的邏輯，
      if($mosaic) { // 更新馬賽克檔, 馬賽克檔永遠在 archive 內
        $the_old_file = $archive . '/' . $file_name_to_update;
      } else {      // 更新非馬賽克檔
        if(has_file_in_archive0($nid, $file_name_to_update)) { // archive0 內為非馬賽克檔
          $the_old_file = $archive0 . '/' . $file_name_to_update;
        } else {                                               // archive 內為非馬賽克檔
          $the_old_file = $archive . '/' . $file_name_to_update;
        }
      }
    */
    rename($the_new_file, $the_old_file);
  }
  rmdir($upload_src_dir);         // delete containing directory.
  _coll_make_public($nid, true);  // defined at coll/coll.inc
  //delete_thumbnail_of_collection($nid, '200_300');
  delete_all_thumbnail_of_collection($nid);
}

/*
 * 新增馬賽克檔到藏品的某個檔案
 *  -) 若藏品本身就有馬賽克檔，改為更新。
 *
 * Note:
 *   -) $upload_src_dir 是準備用來新增的馬賽克檔案所在的目錄，此目錄應該只存在一個檔案。
 *   -) 最後需執行 _coll_make_public($collection_id, false); // defined at coll/coll.inc
 *
 */
function add_mosaic_file_of_collection($nid, $upload_src_dir, $file_name_for_adding_mosaic) {

  $targets  = get_store_of_collection($nid);
  $archive  = drupal_realpath($targets[0]);
  $archive0 = drupal_realpath($targets[1]);

  // 檢查檔案是否存在
  if(!has_file_in_archive0($nid, $file_name_for_adding_mosaic)) {      // archive0 內有檔的話，archive 一定有檔
    if(!file_exists($archive . '/' . $file_name_for_adding_mosaic)) {
      throw new Exception("add_mosaic_file_of_collection(): file $file_name_for_adding_mosaic does not exist.");
    }
  }

  if(!is_mosaic_file_in_archive($nid, $file_name_for_adding_mosaic)) {

    // 1) 把 archive 內的非馬賽克移到 archive0 內
    rename($archive . '/' . $file_name_for_adding_mosaic, $archive0 . '/' . $file_name_for_adding_mosaic);

    // 2) 把 upload 內的檔案移到 archive 內
    if (!is_dir($upload_src_dir) || !$src_dir_handler = opendir($upload_src_dir)) {
      throw new Exception("update_archive_file_of_collection(): no upload directory: $upload_src_dir .");
    }

    // 此迴圈應只會執行三次，有兩次是 . 和 .. 這兩個目錄，另一個是上傳檔
    while (($fname = readdir($src_dir_handler)) !== false) {
      if(is_dir($fname)) continue; // ignore sub directory
      $the_new_file = $upload_src_dir . '/' . $fname;
      rename($the_new_file, $archive . '/' . $file_name_for_adding_mosaic);
    }
    rmdir($upload_src_dir);         // delete containing directory.
    _coll_make_public($nid, true); // defined at coll/coll.inc
    //delete_thumbnail_of_collection($nid, '200_300');
    delete_all_thumbnail_of_collection($nid);
  } else { // 已經有馬賽克檔
    //throw new Exception("add_mosaic_file_of_collection():");
    drupal_set_message('此檔已經存在馬賽克檔，已直接進行更新。');
    update_archive_file_of_collection($nid, $upload_src_dir, $file_name_for_adding_mosaic, false);
  }
}

/*
 * 檢查 $src_dir 下的 $src_file_name 是否是 foo_mosaic.tif 這種 pattern 的檔案。
 *
 * if mosaic file exist, return the full path plus file name string of this file
 * otherwise, return null
 */
function has_mosaic_file($src_dir, $src_file_name) {
  $ext = extract_file_ext($src_file_name);
  if(!$ext) throw new Exception('has_mosaic_file(): invalid file name.');
  $mosaic_file = $src_dir . DIRECTORY_SEPARATOR . filename_without_ext($src_file_name) . '_mosaic.' . $ext ;
  if(file_exists($mosaic_file)) return $mosaic_file;
  else                          return null;
}

/*
   $src_file_or_dir could be single file or a directory

   Note: This function create a NEW collection node for those files.

   return array(
      'collection_id' => '10001',
      'repository_id' => '',
      'store' => '010',
      'files' => array('10001_001.tif', '10001_002.tif', ...)
   )
 */
function move_to_archive($src_file_or_dir, $node = null) {

  $new_id = 0;

  try {
    // 1. create a node with new ID or use a existed node
    if(empty($node)) {
      $empty_node = new_empty_node('', 'collection');
      $new_id     = $empty_node->nid;
    } else {
      $empty_node = $node;
      $new_id = $node->nid;
    }

    // 2. move to archive directory
    $targets = get_store_of_collection($new_id);
    $target_path = $targets[0];        //digicoll/archive/$store
    $target_zero_path = $targets[1];   //digicoll/archive0/$store


    // 3. do the moving
    $count = 1;
    $repository_id = extract_last_path($src_file_or_dir);
    $collection_files = array();

    if(!is_dir($src_file_or_dir) && is_file($src_file_or_dir)) { // file

      $target_file_name = get_archive_file_name($new_id, $count, $src_file_or_dir);
      $target = $target_path . DIRECTORY_SEPARATOR . $target_file_name;
      $real_target = drupal_realpath($target);

      drupal_set_message("move from $src_file_or_dir to $real_target");

      if(!rename($src_file_or_dir, $real_target)) {
        drupal_set_message("move_to_archive():[file] cannot move from $src_file_or_dir to $real_target");
        throw new Exception("move_to_archive():[file] cannot move from $src_file_or_dir to $real_target");
      }

      $repository_id = filename_without_ext(extract_last_path($src_file_or_dir));

      $collection_files[] = $target_file_name;

    } else { // directory

      if (!is_dir($src_file_or_dir) || !$src_dir_handler = opendir($src_file_or_dir)) {
        throw new Exception('move_to_archive(): cannot read source directory.');
      }

      /*
       * 只要有馬賽克檔，一定有一個非馬賽克檔。
       * 有非馬賽克檔，不一定有馬賽克檔。
       */
      while (($src_file_name = readdir($src_dir_handler)) !== false) {
        if(is_dir($src_file_name)) continue; // ignore sub directory
        if(is_mosaic_file_pattern($src_file_name)) continue; // ignore mosaic file,
                                                             // it will be handled while it's non-mosaic file is processed later.

        $target_file_name = get_archive_file_name($new_id, $count, $src_file_name);
        $count++;

        $src_mosaic = has_mosaic_file($src_file_or_dir, $src_file_name);       // source mosaic file name
        $src        = $src_file_or_dir . DIRECTORY_SEPARATOR . $src_file_name; // source file name

        $target      = $target_path      . DIRECTORY_SEPARATOR . $target_file_name;
        $target_zero = $target_zero_path . DIRECTORY_SEPARATOR . $target_file_name;

        $real_target      = drupal_realpath($target);          // archive folder
        $real_target_zero = drupal_realpath($target_zero);     // archive0 folder

        if(empty($src_mosaic)) { // has no mosaic file
          // put non-mosaic file to archive
          if(!rename($src, $real_target)) {
            throw new Exception("move_to_archive():[dir] cannot move from $src to $real_target");
          }
        } else {                 // has mosaic file
          // put non-mosaic file to archive0
          if(!rename($src, $real_target_zero)) {
            throw new Exception("move_to_archive():[dir] cannot move from $src to $real_target_zero");
          }

          // put mosaic file to archive
          if(!rename($src_mosaic, $real_target)) {
            throw new Exception("move_to_archive():[dir] cannot move from $src_mosaic to $real_target");
          }
        }

        $collection_files[] = $target_file_name;
      }

      rmdir($src_file_or_dir); // delete containing directory.
    }

    // save identifier and repository_id to node
    $empty_node->title = strval($new_id);
    $empty_node->field_repository_id[LANGUAGE_NONE][0]['value'] = $repository_id;
    $empty_node->field_identifier[LANGUAGE_NONE][0]['value']    = strval($new_id);
    node_save($empty_node);

    return array(
      'collection_id' => $new_id,
      'repository_id' => $repository_id,
      'store'         => $store_dir,
      'files'         => $collection_files,
    );

  } catch(Exception $e) {
    if(empty($node)) node_delete($new_id);
    header("HTTP/1.0 500 $e->message");
  }
}


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
function bg_mconvert($archives, $ticket) {
  global $base_url;
  $module_path = DRUPAL_ROOT . DIRECTORY_SEPARATOR . drupal_get_path('module','editcol');
  $script = $module_path . DIRECTORY_SEPARATOR . 'mconvert2.php' . ' ' . $ticket;
  $drupal_dir = DRUPAL_ROOT;
  $cmd = "/usr/bin/drush -q -l $base_url -r $drupal_dir php-script $script &";
  variable_set("archives_for_convert_$ticket", $archives); // invoke mconvert.php at background, it will get this variable to do the conversion
  $ret_code = proc_close(proc_open($cmd, array(), $foo));              // no blocking, run at background
  if($ret_code == -1) { drupal_set_message("bg_mconvert(): external command error!!"); }
}


/*
   repository_id to node id mapping

   $mapping = Array(
      'A00001' => '10001',
      ....
   )
*/
function bg_unzip_move_and_mconvert($mapping, $dir, $ticket) {
  global $base_url;
  $module_path = DRUPAL_ROOT . DIRECTORY_SEPARATOR . drupal_get_path('module','editcol');
  $script = $module_path . DIRECTORY_SEPARATOR . 'unzip.php' . ' ' . $dir . ' ' . $ticket;
  $drupal_dir = DRUPAL_ROOT;
  $cmd = "/usr/bin/drush -q -l $base_url -r $drupal_dir php-script $script &";
  mylog($cmd, 'cmd.txt');
  variable_set("mapping_for_unzip_$ticket", $mapping); // invoke unzip.php at background, it will get this variable to do the unzip

  $ret_code = proc_close(proc_open($cmd, array(), $foo));          // no blocking, run at background
  if($ret_code == -1) { drupal_set_message("bg_unzip_move_and_mconvert(): external command error!!"); }
}


function bg_indexer() {
  $cmd = "/usr/bin/indexer --all --rotate &";
  $ret_code = proc_close(proc_open($cmd, array(), $foo));
  if($ret_code == -1) { drupal_set_message("bg_indexer(): external command error!!"); }
}

/*
    $source_dir must be a directory that contain
      1) file must be named with repository ID
      2) directory must be named with repository ID, the files of this directory can be named arbitrary.

    we use file name or directory name as repository_id for collection
*/
function multiple_upload($source_dir, $repository_id_node_mapping = null, $delete_source_dir = true) {

  if(!is_dir($source_dir) || !$handler = opendir($source_dir)) {
    drupal_set_message('multiple_upload(): cannot read source directory.');
    throw new Exception('multiple_upload(): cannot read source directory.');
  }

  $archives = array();

  while (($file_or_dir = readdir($handler)) !== false) {
    if($file_or_dir == '.' || $file_or_dir == '..') continue;

    $repository_id = filename_without_ext($file_or_dir);

    $node = null;
    if(!empty($repository_id_node_mapping)) {
      $nid = $repository_id_node_mapping[$repository_id];
      delete_collection_files($nid);  //20151217 add, if there are files of this collection, delete them first.
      $node = node_load($nid);
    }
    $archives[] = move_to_archive($source_dir . DIRECTORY_SEPARATOR . $file_or_dir, $node);  // if $node is empty, a new node is created in move_to_archive
  }

  if($delete_source_dir) del_dir_tree($source_dir);

  //mylog(print_r($archives, true), 'archives.txt');

  bg_mconvert($archives, uniqid());
}


/*
   create nodes from the multiple upload directory.
   * every single file is a new node
   * every direcoty that contain arbitray files is a new node

   The file name and directory name must be named using repository_id.

   return Array(
      'repository_id' => $node_id
   )
*/
function create_multiple_upload_nodes($dir) {

  if(!is_dir($dir)) { throw new Exception('create_multiple_upload_nodes(): not directory.'); }

  if (!$dir_handler = opendir($dir)) { throw new Exception('create_multiple_upload_nodes(): cannot open directory.'); }

  $nodes = array();

  while (($name = readdir($dir_handler)) !== false) {
    if($name == '.' || $name == '..') continue;   // should ignore all .xxx
    $repository_id = filename_without_ext($name);

    // ft_table was inserted in new_empty_node();
    $node = new_empty_node('', 'collection', Array('repository_id' => $repository_id));  // defined in easier.drupal.php

    $nodes[$repository_id] = $node->nid;
  }

  return $nodes;
}

/*
 * 20151217
 * 用在大量更新數位檔介面
 *
 * field_repository_id => field_identifier
 */
function get_node_id_from_repository_id($repository_id) {

  $query = db_select('field_data_field_repository_id', 'rid');
  $query->condition('field_repository_id_value', $repository_id, '=');
  $query->fields('rid', array('entity_id'));
  $result = $query->execute();

  // https://api.drupal.org/api/drupal/includes!database!database.inc/function/DatabaseStatementInterface%3A%3AfetchCol/7
  // An indexed array, or an empty array if there is no result set.
  $id_array = $result->fetchCol();
  //print_r($id_array);
  $size = count($id_array);
  if($size > 1 || $size == 0) return 0;
  else                        return $id_array[0]; // return the first and only one id;
}

/*
 * 20151217
 * 用在大量更新數位檔介面
 *
 * $dir 內的每個檔案,
 *   1. 都必需是 zip
 *   2. 檔名必需是原件典藏編號 repository_id
 *   3. 不可重複
 *
 */
function get_nodes_from_zipfiles_in_dir($dir) {

  if(!is_dir($dir)) { throw new Exception('get_nodes_from_zipfiles_in_dir(): not directory.'); }

  if (!$dir_handler = opendir($dir)) { throw new Exception('get_nodes_from_zipfiles_in_dir(): cannot open directory.'); }

  $nodes = array();

  while (($name = readdir($dir_handler)) !== false) {
    if($name == '.' || $name == '..') continue;   // should ignore all .xxx
    if(extract_file_ext($name) != 'zip') continue;
    $repository_id = filename_without_ext($name);
    $node_id       = get_node_id_from_repository_id($repository_id);
    if($node_id == 0) watchdog('editcol', "get_node_id_from_repository_id($repository_id) fail");
    else $nodes[$repository_id] = $node_id;
  }
  return $nodes;
}
