<?php
require_once 'coll.page.block.inc';

function _coll_node_view_license(&$node) {
  $field = &$node->content['field_license'];
  $tid = DT::array_get($field, array('#items', 0, 'tid'));
  $t = taxonomy_term_load($tid);
  $map =  variable_get('license_link_map', array());
  $key = strtolower($t->name);
  $it = DT::array_get($map, $key);
  if($it) {
    $options = array(
                 'external' =>true,
                 'attributes'=> array(
                   'class' => array('external')
                 ),
                 'html' => true,
               );
    $out = l($t->name, $it, $options);
    $field[0] = array ('#markup' =>$out);
  }

}

function _coll_node_view_rightgranted(&$node) {
  $field = &$node->content['field_rightgranted'];
  $tid = DT::array_get($field, array('#items', 0, 'tid'));
  $t = taxonomy_term_load($tid);
  $fragments = array(
                 '權利狀態不明'   => 'R01',
                 '權利人聲明保留' => 'R02',
                 '已採用公眾授權' => 'R03',
                 '宣告為公眾領域' => 'R04',
               );
  $fragment = DT::array_get($fragments, $t->name);
  if($fragment) {
    $options = array(
                 'attributes'=> array(
                   'class' => array('linktopage')
                 ),
                 'fragment' => $fragment,
                 'html' => true,
               );
    $out = l($t->name.' '._wg_bt_icon('info-sign'), 'about-rightgranted', $options);
    $field[0] = array ('#markup' =>$out);
  }
}

function _coll_set_breadcrumb($qs, $ext = '') {
  $crumbs = array();
  $crumbs[] = l(t('Home'), '<front>');
  if(!$qs) {
    $crumbs[] = l('All', 'search').$ext;
  } else {
    $crumbs[] = '找 '.l($qs, 'search', array('query'=> array('qs'=>$qs))).$ext;
  }
  drupal_set_breadcrumb($crumbs);
}

function _coll_node_view_teaser(&$node, $qs, $row, $identifier) {
  $linkurl = _coll_url($identifier, $qs, $row);
  $node->linkurl = $linkurl;

  $feature_image = _coll_get_feature_image($identifier, 'medium', $linkurl);
  $node->feature_image = array('#markup' => $feature_image,);
}

function _coll_node_view_grid(&$node, $qs, $row, $identifier) {
  $linkurl = _coll_url($identifier, $qs, $row);
  $node->linkurl = $linkurl;

  $feature_image = _coll_get_feature_image($identifier, 'medium', $linkurl);
  $node->feature_image = array('#markup' => $feature_image,);
}

function _coll_node_view_list(&$node, $qs, $row, $identifier) {
  $linkurl = _coll_url($identifier, $qs, $row);
  $node->linkurl = $linkurl;

  $feature_image = _coll_get_feature_image($identifier, 'thumbnail', $linkurl);
  $node->feature_image = array('#markup' => $feature_image,);
}

function _coll_node_view_full(&$node, $qs, $row, $identifier) {
  $rowperpage = _coll_get_rowperpage();
  $ret = _coll_search($qs, $row, $rowperpage);
  $count = $ret['count'];
  if(!$qs) {
    $bread = '';
  } else {
    $bread = $qs;
  }

  _coll_set_breadcrumb($bread);
  _coll_node_view_full_relcol($node, $qs, $row, $identifier);
  _coll_node_thenav($node, $qs, $row, $count);

  $feature_image = _coll_get_full_files($identifier);
  $node->feature_image = array('#markup' => $feature_image,);
  global $base_url;
  $node->collection_url = $base_url.'/'.$identifier;
}

function _coll_node_view_full_relcol(&$node, $qs, $row, $identifier) {
  $d = _coll_relcol_with_inverse_tabbed($node, $qs, $row, $identifier);
  if(!$d) {
    unset($node->content['relcol']);
  } else {
    $out = '<div class="field field-type-text field-label-inline clearfix">';
    $out .= '<div class="field-label">關係藏品:&nbsp;</div>';
    $out .= $d;
    $out .= ' <script>
            $(function () {
            $(\'#field_relcol_tab a:first\').tab(\'show\')
          })
            </script>
            ';
    $out .= '</div>';
    $weight = $node->content['field_relcol_partof']['#weight'];
    $node->content['relcol'] = array(
                                 '#markup'=>$out,
                                 '#weight' => $weight,
                               );
  }
}

function _coll_relcol_with_inverse_tabbed(&$node, $qs, $row, $identifier) {
  $r = '';

  $tab = array();
  //part of
  $field_name = 'field_relcol_partof';
  $field_partof = &$node->content[$field_name];
  $identifiers = _coll_node_view_get_field_values($field_partof);
  $identifiers = _coll_filter_not_exists($identifiers);

  _coll_relcol_view_tabbed($identifiers, '整體', 'partof', $tab);
  hide($field_partof);
  $sql = 'select entity_id as nid from field_data_'.$field_name.' where '.$field_name.'_value= :identifier';
  $identifiers = db_query($sql, array(':identifier'=>$identifier))->fetchCol();
  $identifiers = _coll_filter_not_exists($identifiers);
  if($identifiers) {
    _coll_relcol_view_tabbed($identifiers, '部份', 'part', $tab);
  }

  //relatedto
  $field_name = 'field_relcol_relatedto';
  $field_relatedto = &$node->content[$field_name];
  $identifiers = _coll_node_view_get_field_values($field_relatedto);
  $identifiers = _coll_filter_not_exists($identifiers);

  $sql = 'select entity_id as nid from field_data_'.$field_name.' where '.$field_name.'_value= :identifier';
  $nids = db_query($sql, array(':identifier'=>$identifier))->fetchCol();
  $nids = _coll_filter_not_exists($nids);

  $identifiers = DT::array_merge($identifiers, $nids);
  $identifiers = array_unique($identifiers);

  _coll_relcol_view_tabbed($identifiers, '關聯', 'relatedto', $tab);
  hide($field_relatedto);
  if($tab['tab']) {

    $r .= '<ul class="nav nav-tabs" role="tablist" id="field_relcol_tab">';
    $r .= implode('',$tab['tab']);
    $r .= '</ul>';

    $r .= '<div class="tab-content">';
    $r .= implode('',$tab['content']);
    $r .='</div>';
  }
  return $r;

}

function _coll_relcol_view_tabbed($identifiers, $label, $tabid, &$tab) {
  if($identifiers) {
    $ar2 = array();
    foreach($identifiers as $identifier) {
      $image = _coll_get_feature_image($identifier, 'icon', _coll_url($identifier), true);
      if($image === false) {
        $ar2[] = _coll_l($identifier, $identifier);
      } else {
        $ar2[] = $image;
      }
    }
    $tabid = 'tab_'.$tabid;
    $tab['tab'][] = '<li role="presentation"><a href="#'.$tabid.'" aria-controls="'.$tabid.'" role="tab" data-toggle="tab">'.$label.'</a></li>';
    $tab['content'][] = '<div role="tabpanel" class="tab-pane active field-relcol-group" id="'.$tabid.'">'.theme('item_list', array('items'=>$ar2)).'</div>';
  }
}

function _coll_relcol_with_inverse(&$node, $qs, $row, $identifier) {
  $r = '';

  //part of
  $field_name = 'field_relcol_partof';
  $field_partof = &$node->content[$field_name];
  $identifiers = _coll_node_view_get_field_values($field_partof);
  $identifiers = _coll_filter_not_exists($identifiers);

  $r .= _coll_relcol_view($identifiers, '整體');
  hide($field_partof);
  $sql = 'select entity_id as nid from field_data_'.$field_name.' where '.$field_name.'_value= :identifier';
  $nids = db_query($sql, array(':identifier'=>$identifier))->fetchCol();
  $nids = _coll_filter_not_exists($nids);
  if($nids) {
    $r .= _coll_relcol_view($nids, '部份');
  }

  //relatedto
  $field_name = 'field_relcol_relatedto';
  $field_relatedto = &$node->content[$field_name];
  $identifiers = _coll_node_view_get_field_values($field_relatedto);
  $identifiers = _coll_filter_not_exists($identifiers);

  $sql = 'select entity_id as nid from field_data_'.$field_name.' where '.$field_name.'_value= :identifier';
  $nids = db_query($sql, array(':identifier'=>$identifier))->fetchCol();
  $nids = _coll_filter_not_exists($nids);

  $identifiers = DT::array_merge($identifiers, $nids);
  $identifiers = array_unique($identifiers);

  $r .= _coll_relcol_view($identifiers, '關聯');
  hide($field_relatedto);

  return $r;
}

function _coll_relcol_view($identifiers, $label) {
  $r = '';
  if($identifiers) {
    $ar2 = array();
    foreach($identifiers as $identifier) {
      $image = _coll_get_feature_image($identifier, 'icon', _coll_url($identifier), true);
      if($image === false) {
        $ar2[] = _coll_l($identifier, $identifier);
      } else {
        $ar2[] = $image;
      }
    }
    $r = '<div class="field-relcol-group">'.'<br/><h4>'.$label.'</h4>'.theme('item_list', array('items'=>$ar2)).'</div>';
  }
  return $r;
}


function _coll_node_view_get_field_values($field) {
  $items = $field['#items'];
  $values = array();
  if($items) {
    foreach($items as $item) {
      if($item['value']) {
        $ar = explode(';', $item['value']);
        if($ar) {
          foreach($ar as $i) {
            $i = (int) $i;
            if($i) {
              $values[] = $i;
            }
          }
        }
      }
    }
  }
  return $values;
}

function _coll_relcol_items_link(&$field) {
  $values = _coll_node_view_get_field_values($field);
  if($values) {
    $keys = array_keys($field);
    foreach($keys as $key) {
      if(is_numeric($key)) {
        unset($field[$key]);
      }
    }
    $ar2 = array();
    $idx = 0;

    foreach($values as $identifier) {
      $image = _coll_get_feature_image($identifier, 'icon', _coll_url($identifier), true);
      if($image === false) {
        $ar2[] = _coll_l($identifier, $identifier);
      } else {
        $ar2[] = $image;
      }
    }
    $field[0] = array('#markup' => implode(' ', $ar2));
  }
}

function _coll_node_thenav(&$node, $qs, $row, $count) {
  $ar = array();

  $viewmode = $_GET['viewmode'];
  if(!$viewmode) $viewmode='grid';

  $node->collopts = array(
                      'row'=>$row,
                      'qs' => $qs,
                    );
  $node->collopts['nexturl'] = _coll_single_next_prev_url('next', $qs, $row, $node->nid);
  $node->collopts['prevurl'] = _coll_single_next_prev_url('prev', $qs, $row, $node->nid);

  $classes_base = array('btn', 'btn-primary', 'btn-sm');
  $collopts = $node->collopts;

  $txt = _wg_bt_icon('th-list'). ' 瀏覽';
  $classes_browse = array_merge($classes_base, array('browse'));
  $ar[] = _coll_search_page_pagination_link($txt, 'search', $qs, $row, $classes_browse, array('query' => array('viewmode'=>$viewmode)));

  $txt = _wg_bt_icon('chevron-left'). ' 上一筆';
  $classes_prev = array_merge($classes_base, array('prev'));
  if(!$collopts['prevurl']) {
    $classes_prev[] = 'disabled';
  }
  $ar[] = _coll_node_thenav_link($txt, $collopts['prevurl'], $classes_prev);

  $txt = '下一筆 '._wg_bt_icon('chevron-right');
  $classes_next = array_merge($classes_base, array('next'));
  if(!$collopts['nexturl']) {
    $classes_next[] = 'disabled';
  }
  $ar[] = _coll_node_thenav_link($txt, $collopts['nexturl'], $classes_next);

  $ar[] = ' (共'.$count.'筆，本頁顯示第'.($row+1).'筆)';

  $node->thenav = '<div id="thenav">'.implode(' ', $ar). '</div>';
}

function _coll_node_thenav_link($txt, $path, $classes) {
  $options = array(
               'attributes'=>array('class'=>$classes  ),
               'html' => true,
               'external' => true,
             );
  $r = l($txt, $path, $options);
  return $r;
}

function _coll_search_page() {
  $rowperpage = _coll_get_rowperpage();

  $path = $_GET['q'];
  $mode = DT::array_get($_GET, 'mode', 'full');
  $row = DT::array_get($_GET, 'row', 0);
  $row = (int) $row;
  $qs = $_GET['qs'];
  $qs = str_replace(array('　'), ' ', $qs);
  if(array_key_exists('page', $_GET)) {
    $page = (int) $_GET['page'];
    $row = $rowperpage * ($page - 1);
    drupal_goto($path,array('query'=>array('qs'=>$qs, 'row'=>$row) ,));
  }

  if($row%$rowperpage > 0) {
    $row = floor($row/$rowperpage) * $rowperpage;
    drupal_goto($path,array('query'=>array('qs'=>$qs, 'row'=>$row) ,));
  }

  $ret = _coll_search($qs, $row, $rowperpage);
  $count = $ret['count'];
  if($count <= 0) {
    drupal_set_message(t('沒有符合<b>:qs</b>的資料，請重新搜尋', array(':qs'=>$qs)), 'error');
    drupal_goto('');
  }
  if(!$qs) {
    $bread = '';
  } else {
    $bread = $qs;
  }

  _coll_set_breadcrumb($bread);

  $maxrow = $count-1;

  if($row > $maxrow) {
    $row = $maxrow;
    drupal_goto($path,array('query'=>array('qs'=>$qs, 'row'=>$row) ,));
  } else if($row < 0) {
    $row = 0;
    drupal_goto($path,array('query'=>array('qs'=>$qs, 'row'=>$row) ,));
  }

  $nids = $ret['identifier'];

  $r = array();
  $crow = $row;
  foreach ($nids as $nid) {
    $node = node_load($nid);
    if(!$node) {
      //dbug_message('error '.$nid);
      continue;
    }
    $node->collopts = array(
                        'row'=>$crow,
                        'qs' => $qs,
                      );
    $viewmode = DT::array_get($_GET, 'viewmode', 'grid');
    $d = node_view($node, $viewmode, 'und');
    $r[] = $d;
    $crow++;
  }

  $nav = _coll_search_page_pagination($path, $qs, $count, $rowperpage, $row);

  $r[] = array('#markup' => $nav, );

  if($mode== 'ajax') {
    $ret = array('content' => render($r));
    print drupal_json_output($ret);
    exit();
  }
  return $r;
}

function _coll_search_page_pagination($path, $qs, $count, $rowperpage, $row) {
  $ar = array();

  $num = 10;

  $classes_base = array('btn', 'btn-primary', 'btn-sm');

  $maxpage = floor(($count-1)/$rowperpage);
  $cpage = floor($row / $rowperpage);
  $min = $cpage - ($num/2);
  if($min < 0) $min = 0;
  $max = $min + ($num -1);
  if($max > $maxpage) {
    $max = $maxpage;
    $min = $max - ($num -1);
    if($min < 0) $min = 0;
  }

  $classes_first = array_merge($classes_base, array('first'));
  $classes_prev = array_merge($classes_base, array('prev'));
  if($row <= 0) {
    $prevrow = 0;
    $classes_prev[] = 'disabled';
    $classes_first[] = 'disabled';
  } else {
    $prevrow= $row - $rowperpage;
    if($prevrow <0) $prevrow = 0;
  }
  $viewmode = DT::array_get($_GET, 'viewmode', 'grid');

  $txt = _wg_bt_icon('th'). '';
  $classes = array_merge($classes_base, array('viewmode-grid'));
  if($viewmode == 'grid') $classes = array_merge($classes_base, array('viewmode-grid', 'disabled'));
  else $classes = array_merge($classes_base, array('viewmode-grid'));
  $ar[] = _coll_search_page_pagination_link($txt, $path, $qs, $row, $classes, array('query' => array('viewmode'=>'grid')));

  $txt = _wg_bt_icon('th-list'). '';
  if($viewmode == 'list') $classes = array_merge($classes_base, array('viewmode-list', 'disabled'));
  else $classes = array_merge($classes_base, array('viewmode-list'));
  $ar[] = _coll_search_page_pagination_link($txt, $path, $qs, $row, $classes, array('query' => array('viewmode'=>'list')));

  $txt = _wg_bt_icon('home'). ' 首頁';
  $classes = array_merge($classes_base, array('home'));
  $ar[] = _coll_search_page_pagination_link($txt, '<front>', '', 0, $classes);

  $txt = _wg_bt_icon('chevron-left')._wg_bt_icon('chevron-left');
  $ar[] = _coll_search_page_pagination_link($txt, $path, $qs, 0, $classes_first, array('query' => array('viewmode'=>$viewmode)));

  $txt = _wg_bt_icon('chevron-left');
  $ar[] = _coll_search_page_pagination_link($txt, $path, $qs, $prevrow, $classes_prev, array('query' => array('viewmode'=>$viewmode)));

  for($i = $min; $i<=$max; $i++) {
    $classes = array_merge($classes_base, array('pagina'));
    if($i == $cpage) {
      $classes[] = 'disabled';
    }
    $txt = $i + 1;
    $prow = $i * $rowperpage;
    $options = array(
                 'query'=>array('qs'=>$qs, 'row'=>$prow, 'viewmode'=>$viewmode) ,
                 'attributes'=>array('class'=>$classes  ),
                 'html' => true,
               );
    $ar[] = l($txt, $path, $options);
  }

  $classes_last = array_merge($classes_base, array('last'));
  $classes_next = array_merge($classes_base, array('next'));

  $maxrow = $count-1;

  if($row > ($maxrow - $rowperpage)) {
    $nextrow = $maxrow;
    $classes_next[] = 'disabled';
    $classes_last[] = 'disabled';
  } else {
    $nextrow= $row + $rowperpage;
  }

  $txt = _wg_bt_icon('chevron-right');
  $ar[] = _coll_search_page_pagination_link($txt, $path, $qs, $nextrow, $classes_next, array('query' => array('viewmode'=>$viewmode)));

  $txt = _wg_bt_icon('chevron-right')._wg_bt_icon('chevron-right');
  $maxrow = $maxpage*$rowperpage;
  $ar[] = _coll_search_page_pagination_link($txt, $path, $qs, $maxrow, $classes_last, array('query' => array('viewmode'=>$viewmode)));

  $gotol = '<form class="goto-form goto-page-form" action="search">
           <input name="qs" type="hidden" value="'.$qs.'"/>
           <input name="page" type="text" size="3" placeholder="到X頁"/>
           <input class="submit" type="submit" value="到"/>
           </form>';

  $rowstart = $row;
  $rowend = $rowstart+$rowperpage-1;
  if($rowend > $count) $rowend = $count-1;
  $nav = '<div id="thenav">'.implode(' ', $ar). ' '.$gotol. ' (共'.($maxpage+1).'頁，'.$count.'筆，本頁顯示'.($rowstart+1).'-'.($rowend+1).'筆)'.'</div>';

  return $nav;
}


function _coll_search_page_pagination_link($txt, $path, $qs='', $row=0, $classes=array(), $options = array()) {
  if($qs) $options['query']['qs'] = $qs;
  if($row) $options['query']['row'] = $row;
  if($classes) $options['attributes']['class'] = $classes;
  $options['html'] = true;
  $r = l($txt, $path, $options);
  return $r;
}

function _coll_get_rowperpage() {
  $r = DT::array_get($_GET, 'rowperpage', 20);
  return $r;
}

function _coll_get_row() {
  $r = DT::array_get($_GET, 'row', 1);
  return $r;
}

function _coll_filter_not_exists($identifiers0) {
  $identifiers = array();
  if($identifiers0) {
    foreach($identifiers0 as $identifier) {
      $node = node_load($identifier);
      if(!$node) {
        if(user_access('update collection')) {
          drupal_set_message('col: '.$identifier.' not exists!', 'warning');
        }
        continue;
      }
      $identifiers[] = $identifier;
    }
  }
  return $identifiers;
}
