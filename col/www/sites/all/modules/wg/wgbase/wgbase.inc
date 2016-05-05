<?php
function _wg_get_block_defs() {
  $items = array();
  foreach (module_implements('wgblock') as $module) {
    $module_items = call_user_func($module . '_wgblock');
    $items = array_merge($module_items, $items);
  }
  return $items;
}

function _wg_block_power_by_wg() {
  $r = '<p class="powerby">power by <a href="www.wordgleaner.com">wordgleaner</a></p>';
  return $r;
}

function _wg_form_element_taxonomy($voc, $args = array()) {
  $args_default = array(
                    'required' => false,
                    'option_none' => '--NONE--',
                    'option_none_value' => 0,
                  );
  $args = DT::array_merge($args_default, $args);
  extract($args);

  $vocabulary = DTEF2::taxonomy_get_vocabulary($voc);
  $terms = DTEF2::taxonomy_term_get_by_vocabulary($voc);
  $options = array();
  $default_value = null;

  if(!$required) {
    $options[$option_none_value] = $option_none;
    $default_value = $option_none_value;
  }

  foreach($terms as $term) {
    $options[$term->tid] = $term->name;
  }
  $el = array(
          '#type' => 'select',
          '#title' => $vocabulary->name,
          '#options' => $options ,
          '#default_value' => $default_value,
          '#description' => '',
        );
  return $el;
}

function _wg_theme_nodes_table($nodes, $fields, $args = array()) {
  $args_default = array(
                    'title_link_to_node' => true,
                  );
  $args = DT::array_merge($args_default, $args);
  extract($args);

  $nid = key($nodes);
  $n0 = $nodes[$nid];
  $nodetype = $n0->type;

  $node_type = node_type_get_type($n0);

  $header = array();
  $rows = array();

  foreach($fields as $field_name) {
    if($field_name == 'title') {
      $header[] = array("data" => $node_type->title_label);
    }
    elseif(preg_match('%^custom_%', $field_name)) {
      $header[] = array("data" => $n0-> {
        $field_name
      }['title']);
    }
    else {
      $label = DT::field_get_label($field_name, $nodetype);
      if(!array_key_exists($field_name, $header)) {
        $header[] = array("data" => $label);
      }
    }
  }
  foreach($nodes as $node) {
    $row = array();
    foreach($fields as $field_name) {
      if($field_name == 'title') {
        if($title_link_to_node) {
          $row[] = l($node->title, 'node/'.$node->nid);
        } else {
          $row[] = $node->title;
        }
      }
      elseif(preg_match('%^custom_%', $field_name)) {
        $row[] = $node-> {$field_name} ['data'];
      }
      else {

        $ff = _wg_field_view($field_name, $node);
        $row[] = render($ff);
      }
    }
    $rows[] = $row;
  }

  $r = theme('table', array('header' => $header, 'rows' => $rows));
  return $r;
}

function _wg_field_view_string($field_name, $node, $viewmode = 'list') {
  $field = _wg_field_view($field_name, $node, $viewmode);
  $r = strip_tags(render($field));
  return $r;
}

function _wg_field_view($field_name, $node, $viewmode = 'list') {
  $instance = field_info_instance('node', $field_name, $node->type);
  $display=field_get_display($instance, $viewmode, $node);
  $ff =field_view_field('node', $node, $field_name, $display);
  return $ff;
}

function _wg_form_element_to_side(&$vars, $key, $weight=10) {
  $vars['form'][$key]['#weight'] = $weight;
  $vars['side'][] = $vars['form'][$key];
  hide($vars['form'][$key]);
}

function _wg_get_nodes_view_count($args) {
  $args_default = array(
                    'type' => '',
                    'types' => array(),
                    'alias' => array(),
                    'query_cb' => null,
                    'query_cb_arg' => null,
                  );
  $args = DT::array_merge($args_default, $args);
  extract($args);

  $nids = array();
  if($alias) {
    $nids = DH::get_nid_by_alias($alias);
    $num_rows = count($nids);
  } else {
    if($type && !$types) {
      $types = array($type);
    }
    $r = array();

    $query = db_select('node', 'n');
    $query->condition('n.status', 1, '>=')
    ->condition('n.type', $types, 'IN');

    if($query_cb && is_callable($query_cb)) {
      $query_cb($query, $args, $query_cb_arg);
    }

    $count_query = $query->countQuery();
    $num_rows = $query->countQuery()->execute()->fetchField();
  }
  return $num_rows;
}

function _wg_get_nodes_view($args) {
  $args_default = array(
                    'viewmode' => 'full',
                    'prenode' => '',
                    'postnode' => '',
                    'preblock' => '',
                    'postblock' => '',
                    'type' => '',
                    'types' => array(),
                    'alias' => array(),
                    'orders' => array(),
                    'limit' => 0,
                    'offset' => 0,
                    'perpage' => 0,
                    'page' => 0,
                    'ajaxpagina' => false,
                    'ajax_scroll_auto_load' => false,
                    'query_cb' => null,
                    'query_cb_arg' => null,
                  );
  $args = DT::array_merge($args_default, $args);
  extract($args);

  $pagination = '';

  $nids = array();
  if($alias) {
    $nids = DH::get_nid_by_alias($alias);
  } else {
    if($type && !$types) {
      $types = array($type);
    }
    $r = array();

    $query = db_select('node', 'n');
    $query->condition('n.status', 1, '>=')
    ->condition('n.type', $types, 'IN');

    $query->fields('n', array('nid'));

    if($query_cb && is_callable($query_cb)) {
      $query_cb($query, $args, $query_cb_arg);
    }

    if($orders) {
      foreach($orders as $order) {
        $f = 'n.'.$order[0];
        $d = $order[1];
        $query->orderBy($f, $d);
      }
    }

    $query->orderBy('n.nid', 'ASC');

    $args['num_rows'] = $query->countQuery()->execute()->fetchField();

    if($limit) {
      $query->range($offset, $limit);
    } else if($perpage) {
      $pagination = _wg_get_nodes_view_pagination($query, $args);
    }
    $result = $query->execute();
    $nids = $result->fetchCol();
  }

  $r = _wg_get_nodes_view_view($nids, $args);

  $r= array('#markup' => $preblock. render($r) .  $pagination.$postblock);
  if(array_key_exists('ajax', $_GET)) {
    $r = render($r);
    print $r;
    exit();
    // only support ajax = 'html' now
  }
  return $r;
}

function _wg_get_nodes_view_pagination(&$query, $args) {
  extract($args);
  $pagination = '';
  if(array_key_exists('page', $_GET)) {
    $page = (int)$_GET['page'];
  } else {
    $page = 0;
  }

  $offset = $page * $perpage;
  $query->range($offset, $perpage);

  $pages = ceil($num_rows / $perpage);

  if($pages) {
    if(array_key_exists('pagination_base', $args)) {
      $path_base = $pagination_base;
    } else {
      $path_base = current_path();
    }
    if($ajaxpagina) {
      if($page >= ($pages-1)) {
      } else {
        $pagination .= '<div id="ajax_next">';
        $options = array(
                     'html'=>true,
                     'query'=>array('page' => ($page+1)),
                     'attributes' => array('data-ajaxtarget'=>'#ajax_next', 'class' => array('more-articles', 'ajaxload'),),
                   );
        if($ajax_scroll_auto_load) {
          $options['attributes']['class'][] = 'ajax_scroll_auto_load';
        }
        if(array_key_exists('ajax', $_GET)) {
          $options['query']['ajax'] = 'html';
        }
        $text = '載入更多文章...';
        $pagination .= l($text, $path_base, $options);
        $pagination .= '</div>';
      }
    } else {
      $pagination .= '<nav><ul class="pagination">';
      $classes ='prev';
      if($page <= 0) {
        $classes .= ' disabled';
      }
      $pagination .= '<li class="'.$classes.'">';
      $options = array('html'=>true,'query'=>array('page' => ($page-1)));
      $text = '<span aria-hidden="TRUE">&laquo;</span><span class="sr-only">Previous</span>';
      $pagination .= l($text, $path_base, $options);
      $pagination .= '</li>';
      for($i=0; $i<$pages; $i++) {
        $pn = $i+1;
        $class = 'pagination-item';
        if($i == $page) {
          $class .= ' active';
        }
        $pagination .= '<li class="'.$class .'">';
        $options = array('html'=>true,'query'=>array('page' => ($i)));
        $text = $pn;
        $pagination .= l($text, $path_base, $options);
        $pagination .= '</li>';
      }
      if($page >= ($pages-1)) {
        $pagination .= '<li class="disabled">';
      } else {
        $pagination .= '<li>';
      }
      $options = array('html'=>true,'query'=>array('page' => ($page+1)));
      $text = '<span aria-hidden="TRUE">&raquo;</span><span class="sr-only">Next</span></a>';
      $pagination .= l($text, $path_base, $options);
      $pagination .= '</li>';
      $url = url($path_base, array(
                   'query'=>array('page' => $page+1)
                 ));
      $pagination .= '</ul></nav>';
    }
  }
  return $pagination;
}

function _wg_get_nodes_view_view($nids, $args) {
  $args_default = array(
                    'viewmode' => 'teaser',
                    'prenode' => '',
                    'postnode' => '',
                  );
  $args = DT::array_merge($args_default, $args);

  extract($args);
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
  return $r;
}

function _wg_block_node_prevnext() {
  $node = menu_get_object();
  if(!$node) return '';
  $prev = _wg_node_get_prev_next($node, 'prev');
  $next = _wg_node_get_prev_next($node, 'next');
  $content = '';
  $attributes = array('class'=>array(''));
  if($prev) $content .= '<div class="prevnextnode prevnode">'.l('<span class="glyphicon glyphicon-circle-arrow-left" aria-hidden="true"></span> '.$prev->title, 'node/'.$prev->nid, array('html'=>true, 'attributes'=>$attributes)).'</div>';
  else {
    $attr2 = $attributes;
    $attr2['class'][] = 'disabled';
    $content .= '<div class="prevnextnode prevnode">'.'<a href="#" '.drupal_attributes($attr2).'><span class="glyphicon glyphicon-circle-arrow-left" aria-hidden="true"></span> </a>
                </div>';
  }
  if($next) $content .= '<div class="prevnextnode nextnode">'.l('<span class="glyphicon glyphicon-circle-arrow-right" aria-hidden="true"></span> '.$next->title, 'node/'.$next->nid, array('html'=>true, 'attributes'=>$attributes)).'</div>';
  else {
    $attr2 = $attributes;
    $attr2['class'][] = 'disabled';
    $content .= '<div class="prevnextnode nextnode">'.'<a href="#" '.drupal_attributes($attr2).'><span class="glyphicon glyphicon-circle-arrow-right" aria-hidden="true"></span> </a>
                </div>';
  }
  return $content;
}

function _wg_node_get_prev_next($node, $op='prev') {
  $types = array($node->type);

  $query = db_select('node', 'n');
  $query->fields('n', array('nid','title'));

  $query->condition('n.status', 1, '>=');
  $query->condition('n.type', $types, 'IN');
  $query->range(0, 1);
  if($op == 'prev') {
    $query->condition('n.created', $node->created, '<');
    $query->orderBy('n.created', 'DESC');
  } else {
    $query->condition('n.created', $node->created, '>');
    $query->orderBy('n.created', 'ASC');
  }
  $r = $query->execute()->fetch();
  return $r;
}

function _wg_template_license($license, $args) {
  extract($args);
  switch($license) {
    case 'CC BY':
      $license = l('創用CC 姓名標示 3.0 台灣版條款', 'http://creativecommons.org/licenses/by/3.0/tw/');
      $citeinfo = t('<p>本文採用 !license 授權。歡迎轉載與引用。<br/>轉載、引用本文請標示網址與作者，如：</p><p><strong>!author !title (!permlink)</strong></p>', array('!license'=>$license, '!author'=>strip_tags($author), '!title'=>$title, '!permlink'=>$permlink));
      break;
    case 'CC BY-NC':
      $license = l('創用CC 姓名標示-非商業使用 3.0 台灣版條款', 'http://creativecommons.org/licenses/by-nc/3.0/tw/');
      $citeinfo = t('<p>本文採用 !license 授權。歡迎轉載與引用。<br/>轉載、引用本文請標示網址與作者，如：</p><p><strong>!author !title (!permlink)</strong></p>', array('!license'=>$license, '!author'=>strip_tags($author), '!title'=>$title, '!permlink'=>$permlink));
      break;
    case 'CC BY-NC-ND':
      $license = l('創用CC 姓名標示-非商業使用-禁止改作 3.0 台灣版條款', 'http://creativecommons.org/licenses/by-nc-nd/3.0/tw/');
      $citeinfo = t('<p>本文採用 !license 授權。歡迎轉載與引用。<br/>轉載、引用本文請標示網址與作者，如：</p><p><strong>!author !title (!permlink)</strong></p>', array('!license'=>$license, '!author'=>strip_tags($author), '!title'=>$title, '!permlink'=>$permlink));
      break;
    case 'CC BY-NC-SA':
      $license = l('創用CC 姓名標示-非商業使用-相同方式分享 3.0 台灣版條款', 'http://creativecommons.org/licenses/by-nc-sa/3.0/tw/');
      $citeinfo = t('<p>本文採用 !license 授權。歡迎轉載與引用。<br/>轉載、引用本文請標示網址與作者，如：</p><p><strong>!author !title (!permlink)</strong></p>', array('!license'=>$license, '!author'=>strip_tags($author), '!title'=>$title, '!permlink'=>$permlink));
      break;
    case 'CC BY-ND':
      $license = l('創用CC 姓名標示-禁止改作 3.0 台灣版條款', 'http://creativecommons.org/licenses/by-nd/3.0/tw/');
      $citeinfo = t('<p>本文採用 !license 授權。歡迎轉載與引用。<br/>轉載、引用本文請標示網址與作者，如：</p><p><strong>!author !title (!permlink)</strong></p>', array('!license'=>$license, '!author'=>strip_tags($author), '!title'=>$title, '!permlink'=>$permlink));
      break;
    case 'CC BY-SA':
      $license = l('創用CC 姓名標示-相同方式分享 3.0 台灣版條款', 'http://creativecommons.org/licenses/by-sa/3.0/tw/');
      $citeinfo = t('<p>本文採用 !license 授權。歡迎轉載與引用。<br/>轉載、引用本文請標示網址與作者，如：</p><p><strong>!author !title (!permlink)</strong></p>', array('!license'=>$license, '!author'=>strip_tags($author), '!title'=>$title, '!permlink'=>$permlink));
      break;
    case 'CC0':
      $license = l('CC0 公眾領域', 'https://creativecommons.org/publicdomain/zero/1.0/');
      $citeinfo = t('<p>本文作者宣告本文採用 !license 授權。即任何人均可以不受限制使用本文。更歡迎轉載與引用。</p>', array('!license'=>$license, '!author'=>strip_tags($author), '!title'=>$title, '!permlink'=>$permlink));
      break;
  }
  return $citeinfo;
}

function _wg_get_nodes_of_term_query($bundle, $tids, $term_field_map, &$titles) {
  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'node')
  ->entityCondition('bundle', $bundle);
  if($tids) {
    foreach($tids as $tid) {
      $term = DTEF2::taxonomy_get_term($tid);
      if($term) {
        if($field = DT::array_get($term_field_map, $term->vocabulary_machine_name)) {
          $query->fieldCondition($field, 'tid', $tid);
          $titles[] = $term->name;
        }
      }
    }
  }
  return $query;
}

function _wg_get_field_textlist_options($code, $fieldname) {
  $options = &drupal_static(__FUNCTION__);
  if(!$options) {
    $sql = "select * from field_config where field_name = :fieldname";
    $row0 = db_query($sql, array(':fieldname' => $fieldname))->fetch();
    $data0 = unserialize($row0->data);
    $options = $data0['settings']["allowed_values"];
  }
  return $options[$code];
}

function _wg_l($text, $path, array $options = array()) {
  global $language_url;
  static $use_theme = NULL;

  $options += array(
                'attributes' => array(),
                'html' => FALSE,
              );

  if (isset($options['attributes']['title']) && strpos($options['attributes']['title'], '<') !== FALSE) {
    $options['attributes']['title'] = strip_tags($options['attributes']['title']);
  }

  if (!isset($use_theme) && function_exists('theme')) {
    if (variable_get('theme_link', TRUE)) {
      drupal_theme_initialize();
      $registry = theme_get_registry(FALSE);
      $use_theme = !isset($registry['link']['function']) || ($registry['link']['function'] != 'theme_link');
      $use_theme = $use_theme || !empty($registry['link']['preprocess functions']) || !empty($registry['link']['process functions']) || !empty($registry['link']['includes']);
    } else {
      $use_theme = FALSE;
    }
  }
  if ($use_theme) {
    return theme('link', array('text' => $text, 'path' => $path, 'options' => $options));
  }
  return '<a href="' . check_plain(url($path, $options)) . '"' . drupal_attributes($options['attributes']) . '>' . ($options['html'] ? $text : check_plain($text)) . '</a>';
}

function _wg_batch_chunck($rows, &$batch, $chunk_size, $op, $op_args) {
  if ($rows) {
    $rownum = 0;
    $arows = array();
    foreach($rows as $row) {
      $rownum++;
      $arows[] = $row;
      if($rownum % $chunk_size == 0) {
        $args0 = $op_args;
        array_unshift($args0, $arows);
        $batch['operations'][] = array($op, $args0);
        $arows = array();
      }
    }
    if($arows) {
      $args0 = $op_args;
      array_unshift($args0, $arows);
      $batch['operations'][] = array($op, $args0);
    }
  }
}

function _wg_flush_all_image_styles() {
  $styles = array_keys(image_styles());
  foreach($styles as $style) {
    watchdog('style', $style);
    image_style_flush($style);
  }
}
