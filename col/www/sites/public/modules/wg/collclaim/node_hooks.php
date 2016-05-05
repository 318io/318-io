<?php

/**
* Implements hook_field_extra_fields().
* add Virtual Field for display only.
*/
function collclaim_field_extra_fields() {
  $extra = array();
  $extra['node']['collection'] =
    array(
      'display' => array(
        'claim' => array(
          'label' => t('Claim'),
          'description' => t('Claim the collection.'),
          'weight' => 50,
        ),
      ),
    );
  return $extra;
}

/**
* Implements hook_node_view().
*/
function collclaim_node_view($node, $view_mode, $langcode) {

  $p = drupal_get_path('module', 'collclaim');

  drupal_add_css("$p/css/squarebutton.css"); // fix the radio box styling problem.

  // The field is showed in when using full view mode and on page node type.
  if ($view_mode == 'full') {
    switch($node->type) {
      case 'collection':
        if(has_verified_claims_of_a_collection($node->nid)) {
          $node->content['claim'] =
            array(
              '#markup' => '<div class="claim"><a class="squarebutton" href="collection/identify/' . $node->nid . '"><span>指認與授權 (已被指認)</span></a><br> <p>若您是本藏品著作人，請點選上面連結進行指認授權作業，想了解更多，請參考<a href="identification_info">相關說明</a>。</p></div>',
            );
        } else {
          $node->content['claim'] =
            array(
              '#markup' => '<div class="claim"><a class="squarebutton" href="collection/identify/' . $node->nid . '"><span>指認與授權</span></a><br> <p>若您是本藏品著作人，請點選上面連結進行指認授權作業，想了解更多，請參考<a href="identification_info">相關說明</a>。</p></div>',
            );
        }
        break;
    }
  }
  elseif($view_mode == 'list') {
    switch($node->type) {
      case 'collection':
        $node->content['claim'] =
          array(
            '#markup' => '<div class="claim"><a href="collection/identify/' . $node->nid . '">指認藏品</a></div>',
          );
        break;
    }
  }
}
