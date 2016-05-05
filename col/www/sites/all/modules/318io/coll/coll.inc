<?php
function _coll_get_nid_by_identifier($identifier) {
  return $identifier;
}

function _coll_single_next_prev_url($t, $qs, $row=0, $nid=null) {
  $r = false;
  if($qs == '') {
    switch($t) {
      case 'prev':
        $sql = "SELECT nid FROM {node} WHERE nid < $nid AND type = :type ORDER BY nid DESC";
        $identifier = db_query($sql, array(':type'=>'collection'))->fetchField();
        break;
      case 'next':
        $sql = "SELECT nid FROM {node} WHERE nid > $nid AND type = :type ORDER BY nid ASC";
        $identifier = db_query($sql, array(':type'=>'collection'))->fetchField();
        break;
    }
    if($identifier) {
      $r = _coll_url($identifier);
    }

  } else {
    switch($t) {
      case 'prev':
        $cr = $row - 1;
        break;
      case 'next':
        $cr = $row + 1;
        break;
    }
    if($cr < 0) return $r;
    $qr = _coll_search($qs, $cr, 1);
    $qi = $qr['identifier'];
    if($qi) {
      $identifier = array_shift($qi);
      $r = _coll_url($identifier, $qs, $cr);
    }
  }
  return $r;
}

function _coll_get_feature_image_uri($identifier) {
  $f = false;
  $files = _coll_get_digifiles($identifier, 'public', 'webm');
  if($files) {
    //webm;
    $file0 = array_shift($files);
    $f = _coll_get_video_icon($file0);
  }
  if(!$files) {
    $files = _coll_get_digifiles($identifier, 'public', 'jpg');
    if($files) $f = array_shift($files);
  }
  return $f;
}

function _coll_get_feature_image($identifier, $style_name = 'large', $linkurl=false, $retboolifnofile = false) {
  $f = false;
  $files = _coll_get_digifiles($identifier, 'public', 'webm');
  if($files) {
    //webm;
    $file0 = array_shift($files);
    $f = _coll_get_video_icon($file0);
  }
  if(!$files) {
    $files = _coll_get_digifiles($identifier, 'public', 'jpg');
    if($files) $f = array_shift($files);
  }
  if($f === false) {
    if($retboolifnofile) return false;
    $r = '<div class="featuredimage nofile"> no file</div>';
  } else {
    $icon = _wg_image_style($style_name, $f, $identifier);
    $r = '<div class="featuredimage">';

    if($linkurl) {
      $r .= '<a href="'.$linkurl.'">'.$icon.'</a>';
    } else {
      $r .= $icon;
    }
    $r .= '</div>';
  }
  return $r;
}

function _coll_url($identifier, $qs = '', $row = 0) {
  $opts = array('absolute'=>true);
  if($qs) {
    $opts['query']['in'] = $qs;
  }
  if($row) {
    $opts['query']['row'] = $row;
  }
  $viewmode = DT::array_get($_GET, 'viewmode');
  if($viewmode) $opts['query']['viewmode'] = $viewmode;

  $l = url('node/'.$identifier, $opts);
  return $l;
}

function _coll_l($text, $identifier, $qs = '', $row = 0) {
  $opts = array('absolute' => true);
  if($qs) {
    $opts['query']['in'] = $qs;
  }
  if($row) {
    $opts['query']['row'] = $row;
  }
  $l = l($text, 'node/'.$identifier, $opts);
  return $l;
}

function _coll_get_full_files($identifier) {
  $r = false;
  $t = null;

  $digifiles = _coll_get_digifiles($identifier, 'public', 'webm');
  if(!$digifiles) {
    $digifiles = _coll_get_digifiles($identifier, 'public', 'jpg');
    if($digifiles) {
      $t = 'image';
    }
  } else {
    $t = 'video';
  }

  $vars = _coll_ef_variables();
  $multiple = (count($digifiles) > 1)? true : false;

  $files = array();

  switch($t) {
    case 'image':
      foreach($digifiles as $k=>$file) {
        $p = pathinfo($file);
        $fn = $p['filename'];
        $pathid = _coll_get_pathpart($identifier);
        $fileuri_pathpart = $vars['path']['archive'].'/'.$pathid.'/'.$fn;
        $archive = $fileuri_pathpart.'.tif'; // todo: is all download tif?
        if(!file_exists($archive)) $archive = false;
        $files[$fn]['feature'] = _wg_image_style('large', $file, $identifier);
        $files[$fn]['icon'] = _wg_image_style('icon', $file, $identifier);
        if($archive) {
          $nomosaic_uri_archive = $vars['path']['archive0'].'/'.$pathid.'/'.$fn.'.tif';
          if(file_exists($nomosaic_uri_archive)) {
            $files[$fn]['nomosaic'] = file_create_url($nomosaic_uri_archive);
            $nomosaic_uri_public = $vars['path']['public0'].'/'.$pathid.'/'.$fn.'.jpg';
            $files[$fn]['nomosaic_public'] = $nomosaic_uri_public;
          } else {
            $files[$fn]['nomosaic'] = false;
            $files[$fn]['nomosaic_public'] = false;

          }

          $files[$fn]['download'] = file_create_url($archive);
        } else {
          $files[$fn]['nomosaic'] = false;
          $files[$fn]['nomosaic_public'] = false;
          $files[$fn]['download'] = false;
        }
      }
      break;
    case 'video':
      foreach($digifiles as $k=>$file) {
        $p = pathinfo($file);
        $fn = $p['filename'];
        $pathid = _coll_get_pathpart($identifier);
        $fileuri_pathpart = $vars['path']['archive'].'/'.$pathid.'/'.$fn;
        $fileicon = _coll_get_video_icon($file);
        $file = file_create_url($file);
        $fileiconurl = file_create_url($fileicon);
        $i = '<video class="" poster="'.$fileiconurl.'" controls><source src="'.$file.'" type="video/webm">Your browser does not support the video tag.</video> ';
        $files[$fn]['feature'] = $i;
        $files[$fn]['icon'] = _wg_image_style('icon', $fileicon, $identifier);
        $files[$fn]['download'] = false;
      }
      break;
  }

  $features = array();
  $icons = array();
  $dls = array();

  if($multiple) {
    $r = '<div class="feature-image multiple">';
  } else {
    $r = '<div class="feature-image">';
  }
  foreach($files as $fn=>$file) {
    $fea =  '<li data-key="'.$fn.'">'.$file['feature'];
    if($file['nomosaic_public']) {
      $nomosaic_public_large = image_style_url('large', $file['nomosaic_public']);
      $fea .= '<div class="nomosaic"><a href="'.$nomosaic_public_large.'" target="_blank">'._wg_image_style('icon', $file['nomosaic_public'], '無馬賽克檔').'</a></div>';
    }
    if($file['download']) {
      $fea .= '<p class="download"><a href="'.$file['download'].'">'._wg_bt_icon('download-alt').'下載典藏檔案'.'</a></p>';
    }
    $fea .= '</li>';
    $features[] = $fea;
    $icons[] = '<li data-key="'.$fn.'">'.$file['icon'].'</li>';
    $dls[] = '<li data-key="'.$fn.'">'.$file['download'].'</li>';
  }
  $r .= '<div class="features"><ul>'.implode("\n", $features).'</ul></div>';
  if($multiple) {
    $r .= '<div class="icons"><ul>'.implode("\n", $icons).'</ul></div>';
  }
  $r .= '</div>';

  return $r;
}

function _coll_get_video_icon($file, $reset = false) {
  $icon = str_replace('.webm', '_icon.jpg', $file);
  $f = drupal_realpath($icon);
  if($reset || !file_exists($f)) {
    DT::video_get_icon(drupal_realpath($file), drupal_realpath($icon));
  }
  return $icon;
}

function _coll_get_digifiles($identifier, $type = 'public', $ext = '.*', $ptn = '.*') {
  $vars = _coll_ef_variables();

  $files = array();

  $pathid = _coll_get_pathpart($identifier);
  $fileuri_pathpart = $vars['path'][$type].'/'.$pathid;

  $dir = drupal_realpath($fileuri_pathpart);
  $ptn0 = '(_[0-9]+)?';
  $pattern = '/^'.$identifier.$ptn0.$ptn.'\.'.$ext.'$/';
  $files = DT::ls($fileuri_pathpart, false, $pattern);
  return $files;
}

function _coll_get_pathpart($identifier) {
  $pathid = floor($identifier/1000);
  $pathid = str_pad($pathid, 3, "0", STR_PAD_LEFT);
  return $pathid;
}

/**
 * _coll_search().
 * @param:
 *    $qs: query_string, raw from get url
 *    $row: start
 *    $rowperpage
 * @return:
 *    a result array
 */
function _coll_search($qs, $row, $rowperpage) {
  $func = 'expsearch_coll_query';
  if(!is_callable($func)) {
    $func = '_coll_coll_query';
  }
  $r = $func($qs, $row, $rowperpage);
  DT::array_set($r, 'qs', $qs);
  DT::array_set($r, 'row', $row);
  DT::array_set($r, 'rowperpage', $rowperpage);
  return $r;
}

function _coll_coll_query($qs, $row, $rowperpage) {
  $query = db_select('node', 'n')
           ->fields('n', array('nid'))
           ->condition('n.type', 'collection', '=')
           ->orderBy('n.nid', 'ASC');
  $count = $query->countQuery()->execute()->fetchField();
  $result = $query->range($row, $rowperpage)->execute();
  $identifiers = $result->fetchCol();

  $r = array(
         'qsh' => $qs,
         'count'=>$count,
         'identifier' => $identifiers,
       );
  return $r;
}

function _coll_get_files($type, $pattern) {
  $vars = _coll_ef_variables();
  $files0 = DT::ls($vars['path'][$type], true, $pattern);
  $files = array();
  foreach($files0 as $f) {
    $info = array(
              'name' => $f,
              'mtime' => filemtime($f),
              'size' => filesize($f),
            );
    $files[$f] = $info;
  }
  return $files;
}

function _coll_digifiles_delete($identifier, $type) {
  $files = _coll_get_digifiles($identifier, $type);
  if($files) {
    foreach($files as $f) {
      unlink($f);
    }
  }
}

function _coll_make_public($identifier, $reset = false, $dry = false) {
  _coll_make_public_public($identifier, $reset, $dry);
  _coll_make_public_public0($identifier, $reset, $dry);
}

function _coll_make_public_public($identifier, $reset = false, $dry = false) {
  if($reset) {
    _coll_digifiles_delete($identifier, 'public');
  }

  $files = _coll_get_digifiles($identifier, 'archive', 'ogv');
  if(!$files) {
    $files = _coll_get_digifiles($identifier, 'archive', 'tif');
  }

  $vars = _coll_ef_variables();
  $path = $vars['path'];
  $path_public = $path['public'];

  foreach($files as $farchive) {
    $p = pathinfo($farchive);
    $pathid = basename(dirname($farchive));
    $fileuri_pathpart   = $path_public.'/'.$pathid;
    $x = file_prepare_directory($fileuri_pathpart, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    switch ($p['extension']) {
      case 'tif':
        $fpublic = $fileuri_pathpart.'/'.$p['filename'].'.'.'jpg';
        if(file_exists($fpublic) && !$reset) {
          watchdog('coll', 'make public, file exists! '.$fpublic.' ignored');
        } else {
          watchdog('coll', $identifier.' [tiff] converting...');;
          if(!$dry) DT::tiff2jpeg($farchive, $fpublic);
        }
        break;
      case 'ogv':
        $fpublic = $fileuri_pathpart.'/'.$p['filename'].'.'.'webm';
        if(file_exists($fpublic) && !$reset) {
          watchdog('coll', 'make public, file exists! '.$fpublic.' ignored');
          if(!$dry) DT::ogg2webm($farchive, $fpublic);
        } else {
          watchdog('coll', $identifier.' [ogv] converting...');;
          if(!$dry) DT::ogg2webm($farchive, $fpublic);
        }
        _coll_get_video_icon($fpublic, $reset);
        break;
      default:
        watchdog('coll', $identifier.' unknown file ext: '.$p['extension']);
        break;
    }
  }
  return;
}

function _coll_make_public_public0($identifier, $reset = false, $dry = false) {
  if($reset) {
    _coll_digifiles_delete($identifier, 'public0');
  }

  // only tif
  $files = _coll_get_digifiles($identifier, 'archive0', 'tif');
  if(!$files) return;

  $vars = _coll_ef_variables();
  $path = $vars['path'];
  $path_public = $path['public0'];

  foreach($files as $farchive) {
    $p = pathinfo($farchive);
    $pathid = basename(dirname($farchive));
    $fileuri_pathpart   = $path_public.'/'.$pathid;
    $x = file_prepare_directory($fileuri_pathpart, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
    switch ($p['extension']) {
      case 'tif':
        $fpublic = $fileuri_pathpart.'/'.$p['filename'].'.'.'jpg';
        if(file_exists($fpublic) && !$reset) {
          watchdog('coll', 'make public, file exists! '.$fpublic.' ignored');
        } else {
          watchdog('coll', $identifier.' [tiff] converting...');;
          if(!$dry) DT::tiff2jpeg($farchive, $fpublic);
        }
        break;
      default:
        watchdog('coll', $identifier.' unknown file ext: '.$p['extension']);
        break;
    }
  }
  return;
}

function _coll_digifile_open_nomosaic($identifier) {
  $files_nom = _coll_get_digifiles($identifier, 'archive0', 'tif');

  if(!$files_nom) return false;

  $files_m   = _coll_get_digifiles($identifier, 'archive', 'tif');

  $vars = _coll_ef_variables();
  $path = $vars['path'];
  $path_archive_mosbk = $path['archive_mosbk'];
  $path_archive = $path['archive'];
  foreach($files_nom as $f) {
    $p = pathinfo($f);
    $basename = $p['basename'];
    $pathid = basename(dirname($f));
    $farchive = $path_archive.'/'.$pathid.'/'.$basename;
    if(file_exists($farchive)) {
      $fileuri_pathpart   = $path_archive_mosbk.'/'.$pathid;
      file_prepare_directory($fileuri_pathpart, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
      $farchive_mosbk = $path_archive_mosbk.'/'.$pathid.'/'.$basename;
      file_unmanaged_copy($farchive, $farchive_mosbk, FILE_EXISTS_REPLACE);

      file_unmanaged_copy($f, $farchive, FILE_EXISTS_REPLACE);
      unlink($f);
    }
  }
  _coll_make_public($identifier, true);

}
