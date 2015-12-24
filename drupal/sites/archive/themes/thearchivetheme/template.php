<?php

function thearchivetheme_preprocess_html(&$vars) {
  drupal_add_css(path_to_theme() . '/css/ie8.css', array('group' => CSS_THEME, 'browsers' => array('IE' => '(lt IE 9)', '!IE' => FALSE), 'preprocess' => FALSE));
  drupal_add_js( path_to_theme() . '/js/animate-plus.js');
  drupal_add_css(path_to_theme() . '/css/animate.css');
}

function thearchivetheme_process_page(&$vars) {
  $usernav = '';
  if(user_is_logged_in()){
    global $user;
    $node = menu_get_object();
    $ar = array();
    if($node){
      $nid = $node->nid;
      if(user_access('update collection')) $ar[] = '<a href="collection/edit/'.$nid.'">編輯</a>';
      if(user_access('update collection')) $ar[] = '<a href="collection/update/file/'.$nid.'">更新數位檔</a>';
      if(user_access('update collection') && editcol_is_video_collection($nid)) $ar[] = '<a href="collection/upload/video_icons/'.$nid.'">自定影片圖示</a>';
      if(user_access('delete collection')) $ar[] = '<a href="collection/delete/'.$nid.'">刪除</a>';
    }
    if(user_access('control panel'))  $ar[] = '<a href="/control_panel/1">Control Panel</a>';
    $ar[] = 'Login as '.$user->name;
    $ar[] = '<a href="/user/logout">Log out</a>';
    $usernav = '<div class="row">'.implode(' | ', $ar).'</div>';
  }
  $vars['usernav'] = $usernav;
}

function thearchivetheme_breadcrumb($vars) {
  $breadcrumbs = $vars['breadcrumb'];
  $hometext = '<span class="glyphicon glyphicon-home" aria-hidden="true"></span>';
  $home = array_shift($breadcrumbs);
  $home = l($hometext, '<front>', array('html' =>true, 'attributes'=>array('class'=>array('home') ) ));
  array_unshift($breadcrumbs, $home);
  $out = _wgtheme_theme_breadcrumb($breadcrumbs, ' <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span> ');
  return $out;
}
