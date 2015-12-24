<?php
function _wg_bt_carosel($id, $photos, $style_name) {
  $out = '';
  $out .= '<div class="'.$id.'-wrap"><div id="'.$id.'-generic" class="carousel slide">';
  $out .= '<div class="carousel-inner" role="listbox">';

  $active = true;
  foreach($photos as $photo) {
    $i = array(
           'style_name' => $style_name,
           'path' => $photo['uri'],
           'width' => '',
           'height' => '',
           'alt' => '',
           'title' => '',
         );
    if($active) {
      $out .= '<div class="item active">';
      $active = false;
    } else {
      $out .= '<div class="item">';
    }
    $out .= theme('image_style',$i);
    if($photo['title']) {
      $out .= '<div class="carousel-caption"><p>'.$photo['title'].'</p></div>';
    }
    $out .= '</div>';
  }
  $out .= '</div>';
  if(count($photos)>1) {
    $out .= '
            <a class="left carousel-control" href="#'.$id.'-generic" role="button" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
            </a>
            <a class="right carousel-control" href="#'.$id.'-generic" role="button" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
            </a>';
  }
  $out .= '</div></div>';
  return $out;
}

function _wg_bt_sronly($t) {
  $r = '<span class="sr-only">'.$t.'</span>';
  return $r;
}

function _wg_bt_icon($t) {
  $r = '<span class="glyphicon glyphicon-'.$t.'" aria-hidden="true"></span>';
  return $r;
}

function _wg_fa_icon($t, $etraCl='') {
  $r = '<i class="fa fa-'.$t.' '.$etraCl.'"></i>';
  return $r;
}

function _wg_date_with_week($t0) {
  $weeks_tw = array('日','一','二','三','四','五','六');
  $format = "%Y-%m-%d";
  $t = strtotime($t0);
  $w = date('w', $t);
  $s = date('Y.m.d', $t) . ' ('.$weeks_tw[$w].')';
  return $s;
}

function _wg_nodepath_get_link($path) {
  $l = false;
  $p2 = drupal_lookup_path('source', $path);
  if(preg_match('%^node/([0-9]+)$%', $p2, $m)) {
    $n = node_load($m[1]);
    $l = l($n->title, $p2);
  }
  return $l;
}

function _wg_theme_breadcrumb($breadcrumbs, $seperator = '') {
  if($seperator == '') $seperator = _wg_fa_icon('angle-double-right', 'breadcrumb-sep');
  $out = '';
  $ar = array();
  foreach ($breadcrumbs as $key => $breadcrumb) {
    if(is_array($breadcrumb) && array_key_exists('data', $breadcrumb)) {
      $item = $breadcrumb['data'];
    } else {
      $item = $breadcrumb;
    }
    $ar[] =  '<span class="breadcrumb-item">'.$item.'</span>';
  }
  $out = implode($seperator, $ar);
  $out = '<div class="breadcrumb">'.$out.'</div>';
  return $out;
}

function _wg_helper_field_custom($label, $value, $class='') {
  $r  = '<div class="field field-custom-field field-label-inline clearfix '.$class.'">';
  if($label) $r .= '<div class="field-label">'.$label.':&nbsp;</div>';
  $r .= '<div class="field-items"><div class="field-item">'.$value.'</div></div>';
  $r .= '</div>';
  return $r;
}

function _wg_helper_nodecontent_custom(&$node, $key, $val) {
  $node->content[$key] = array('#markup' => $val);
}

function _wg_helper_get_bilingual_title($type, $entity, $tw_fieldname='field_nametw', $prefix = ' (', $suffix = ')') {
  $n = '';
  switch($type) {
    case 'term':
      $n = $entity->name;
      break;
  }

  $twname = DTEF2::get_field_text($entity, $tw_fieldname);
  if($twname) $n .= $prefix.$twname.$suffix;
  return $n;
}

function _wg_html_wrap($c, $class='', $id = '', $type='div') {
  $r = '<'.$type;
  if($class) $r .= ' class="'.$class.'"';
  if($id) $r .= ' id="'.$id.'"';
  $r .= '>';
  $r .= $c;
  $r .= '</'.$type.'>';
  return $r;
}

function _wg_image_style($style_name, $path, $title = '') {
  $i = array(
         'style_name' => $style_name,
         'path' => $path,
         'width' => '',
         'height' => '',
         'alt' => $title,
         'title' => $title,
       );
  return theme('image_style',$i);
}
