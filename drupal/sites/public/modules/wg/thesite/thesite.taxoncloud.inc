<?php
function _coll_get_taxoncloud($reset = false) {
  $output = '';
  $vocs = array();
  foreach (taxonomy_get_vocabularies(NULL) as $vocabulary) {
    $vocs[] = $vocabulary->vid;
  }

  if($vocs) {
    $reset = true;
    $tags = _wg_tagclouds_get_tags($vocs, 8, 100, $reset);
    $tags = tagclouds_sort_tags($tags);
    $output = theme_tagclouds_weighted(array('terms' => $tags));
    $output = '<div class="wrapper tagclouds animate-plus" data-animations="pulse" data-animation-when-visible="true">'.$output.'</div>';
  }

  return $output;
}

function _wg_term_get_childs($parent, &$childs) {
  $tids = db_query('select tid from taxonomy_term_hierarchy where parent = :par', array(':par'=>$parent))->fetchCol();
  if(empty($tids)) return;
  foreach($tids as $tid) {
    $childs[$tid] = $tid;
    _wg_term_get_childs($tid, $childs);
  }
}

/**
 * modified from tagcloud.
 */
function _wg_tagclouds_get_tags($vids, $steps = 6, $size = 60, $reset = false) {
  $options = implode('_', $vids) . '_' . $steps . '_' . $size;
  $cache_name = 'tagclouds_cache_' . $options;
  $cache = cache_get($cache_name, 'cache_page');
  if(!$cache) $cache = new stdClass();
  if($reset) $cache->data = null;

  $tags = array();
  if (isset($cache->data)) {
    $tags = $cache->data;
  } else {
    if (!is_array($vids) || count($vids) == 0) {
      return array();
    }
    $sql = "select d.tid
           from taxonomy_term_data d
           left join taxonomy_term_hierarchy h on h.tid = d.tid
           where d.vid in (:vids)
           and (h.parent = 0 or h.parent is null)
           ";
    $result = db_query($sql, array(':vids'=>$vids));

    $tags = array();

    $names = array();

    foreach ($result as $row) {
      $term = taxonomy_term_load($row->tid);
      if(in_array($term->name, $names)) continue;
      $names[] = $term->name;
      if(in_array($term->name, array('#N/A'))) continue;
      $childs = array($row->tid => $row->tid);
      _wg_term_get_childs($row->tid, $childs);
      $c = db_query('select count(distinct nid) from taxonomy_index where tid in (:tids)', array(':tids' => $childs))->fetchField();
      if($c >0) {
        $term->count = $c;
        $tags[$row->tid] = $term;
      }

    }

    function cmp($a, $b) {
      return $a->count < $b->count;
    }

    usort($tags, "cmp");

    if ($size > 0) {
      $tags = array_slice($tags, 0, $size);
    }
    $tags = tagclouds_build_weighted_tags($tags, $steps);

    cache_set($cache_name, $tags, 'cache_page', CACHE_TEMPORARY);
  }

  return $tags;
}

function tagclouds_build_weighted_tags($tags, $steps = 6) {
  $tags_tmp = array();
  $min = 1e9;
  $max = -1e9;
  foreach ($tags as $id => $tag) {
    $tag->number_of_posts = $tag->count;
    $tag->weightcount = log($tag->count);
    $min = min($min, $tag->weightcount);
    $max = max($max, $tag->weightcount);
    $tags_tmp[$id] = $tag;
  }
  $range = max(.01, $max - $min) * 1.0001;
  foreach ($tags_tmp as $key => $value) {
    $tags[$key]->weight = 1 + floor($steps * ($value->weightcount - $min) / $range);
  }
  return $tags;
}

function tagclouds_sort_tags($tags, $sort = NULL) {
  if ($sort == NULL) {
    $sort = 'title,asc';
    list($sort, $order) = explode(',', variable_get('tagclouds_sort_order', $sort));
  } else {
    list($sort, $order) = explode(',', $sort);
  }

  switch ($sort) {
    case 'title':
      usort($tags, "_tagclouds_sort_by_title");
      break;
    case 'count':
      usort($tags, "_tagclouds_sort_by_count");
      break;
    case 'random':
      shuffle($tags);
      break;
  }
  if ($order == 'desc') {
    $tags = array_reverse($tags);
  }
  return $tags;
}

function _tagclouds_sort_by_title($a, $b) {
  return strnatcasecmp($a->name, $b->name);
}

function _tagclouds_sort_by_count($a, $b) {
  return $a->count > $b->count;
}

function theme_tagclouds_weighted(array $vars) {
  $terms = $vars['terms'];

  $output = '';

  foreach ($terms as $term) {
    $term_name = $term->name;
    $term_desc = $term->description;

    $output .= tagclouds_display_term_link_weight($term_name, $term->tid, $term->weight, $term_desc);
  }

  return $output;
}

function tagclouds_display_term_link_weight($name, $tid, $weight, $description) {
  if ($term = taxonomy_term_load($tid)) {
    $uri = entity_uri('taxonomy_term', $term);
    $uri['options']['attributes']['class'][] = 'tagclouds';
    $uri['options']['attributes']['class'][] = 'level' . $weight;
    $uri['options']['attributes']['title'] = $description;
    $uri['options']['query'] = array('qs'=>'term:'.$term->name);
    $link = l($name, 'search', $uri['options']);
    return "<span class='tagclouds-term'>" . $link . "</span>\n";
  }
}
