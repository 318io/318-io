<?php
function _thesite_block_theuser() {
  $r = '';
  if(user_is_logged_in()) {
    global $user;
    $ar = array();
    $ar[] = '登入為/Login As '.$user->name;
    $ar[] = '<a href="/user/logout">登出/Log out</a>';
    $r = implode(' | ', $ar);
  }
  return $r;
}

function _thesite_block_main_menu() {
  $menu_items = menu_tree_page_data('main-menu');
  foreach($menu_items as $key => $m) {
    $menu_items [$key]['below'] = array();
  }
  $menu = menu_tree_output($menu_items);
  $list = array();
  DT::menu_tree_output_list($menu, $list);
  $r = theme('item_list', array('items'=> $list));
  return $r;
}

function _thesite_block_random_collection() {
  $args = array(
            'viewmode' => 'front',
            'prenode' => '',
            'postnode' => '',
            'preblock' => '',
            'postblock' => '',
            'type' => 'collection',
            'types' => array(),
            'alias' => array(),
            'orders' => array(),
            'limit' => 4,
            'offset' => 0,
            'perpage' => 0,
            'page' => 0,
          );

  extract($args);

  $r = array();
  $nids = array();
  if($alias) {
    $nids = DH::get_nid_by_alias($alias);
  } else {
    if($type && !$types) {
      $types = array($type);
    }
    $query = db_select('node', 'n');
    $query->condition('n.status', 1, '>=')
    ->condition('n.type', $types, 'IN');
    $query->fields('n', array('nid'));
    $query->orderRandom();
    $num_rows = $query->countQuery()->execute()->fetchField();
    $query->range($offset, $limit);
    $result = $query->execute();
    $nids = $result->fetchCol();
  }

  $rowindex = 0;
  foreach($nids as $nid) {
    $rowindex ++;
    $node = node_load($nid);
    $node->rowindex = $rowindex;
    $n = node_view($node, $viewmode);
    $n['#prefix'] = $prenode;
    $n['#suffix'] = $postnode;
    $r[] = $n;
  }

  $r= array('#markup' => $preblock. render($r) . $postblock);
  return $r;
}

function _thesite_block_taxoncloud($reset = false) {
  return _coll_get_taxoncloud(false);
}
