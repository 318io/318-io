<?php
function thepublictheme_preprocess_html(&$variables) {
  drupal_add_css(path_to_theme() . '/css/ie8.css', array('group' => CSS_THEME, 'browsers' => array('IE' => '(lt IE 9)', '!IE' => FALSE), 'preprocess' => FALSE));
  drupal_add_js( path_to_theme() . '/js/animate-plus.js');
  drupal_add_css(path_to_theme() . '/css/animate.css');
}

function thepublictheme_preprocess_node(&$vars) {
  if($vars['type'] == 'collection') {
    $vars['thenav'] = $vars['node']->thenav;
  }
}

function thepublictheme_breadcrumb($vars) {
  $breadcrumbs = $vars['breadcrumb'];
  $hometext = _wg_bt_icon('home');
  $home = array_shift($breadcrumbs);
  $home = l($hometext, '<front>', array('html' =>true, 'attributes'=>array('class'=>array('home') ) ));
  array_unshift($breadcrumbs, $home);
  $out = _wgtheme_theme_breadcrumb($breadcrumbs, ' '._wg_bt_icon('chevron-right').' ');
  return $out;
}
