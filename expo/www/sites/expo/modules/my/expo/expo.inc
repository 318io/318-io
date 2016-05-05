<?php
use Drupal\wg\DT;
use Drupal\wg\WG;
use Drupal\expo\Sticky;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Url;
use Drupal\field_collection\Entity\FieldCollectionItem;

function _expo_node_view_expo_front(array &$build, \Drupal\Core\Entity\EntityInterface $entity, \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display, $view_mode) {
  $url = WG::nodeurl($entity->id(), true);
  $permalink = $url->toString();
  $build['permalink'] = array('#markup' => $permalink);

  $d = _expo_view_stickymap($entity);
  $build['stickymap'] = array('#markup' => $d, '#weight'=>-10);

  $featuredimage_uri = 'public://expo/logo.png';
  $p_link_text = SafeMarkup::format('@label<div class="litem-desc">@description</div>', ['@label' => '我們的318', '@description' => '個人策展']);

  $expomenuitems = [
                     WG::link('緣 起', _expo_ajax_url('node', 3), ['attributes' => ['class' => ['ajaxpopup', 'staticpage',],],]),
                     WG::link('回 眸', _expo_ajax_url('node', 2), ['attributes' => ['class' => ['ajaxpopup', 'staticpage',],],]),
                     WG::link('願 景', _expo_ajax_url('node', 1), ['attributes' => ['class' => ['ajaxpopup', 'staticpage',],],]),
                     WG::link($p_link_text, WG::nodeurl(150), ['attributes' => ['class' => ['our318', 'staticpage',],],]),
                   ];
  $ar = [];
  foreach($expomenuitems as $expomenuitem) {
    $ar[] = '<div class="item"><div class="text">'.$expomenuitem.'</div></div>';
  }
  $expomenu = implode('', $ar);

  $build['expomenu'] = array('#markup' => $expomenu);
  $build['featuredimage'] = WG::render_styled_image($featuredimage_uri, 'expologo', false);
}

function _expo_node_view_expo_full(array &$build, \Drupal\Core\Entity\EntityInterface $entity, \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display, $view_mode) {
  $url = WG::nodeurl($entity->id(), true);
  $permalink = $url->toString();
  $build['permalink'] = array(
                          '#markup' => $permalink,
                          '#type' => 'item',
                          '#title' => '',
                        );
  $purl = WG::nodeurl(150);
  $plink = $purl->toString();
  $build['expotool'] =[
                        '#markup' =>
                        '<div id="expotool"><div class="wrapper">'
                        .'<a href="'.$plink.'"><i class="fa fa-arrow-circle-left"></i> 我們的318</a>'
                        .'<a href="'.$permalink.'?view=stickymap"><i class="fa fa-leaf"></i> 地圖顯示</a>'
                        .'<a href="'.$permalink.'?view=list"><i class="fa fa-th-list"></i> 列表顯示</a>'
                        .'</div></div>'
                      ];

  $d = _expo_view_stickymap($entity);
  $build['stickymap'] = array('#markup' => $d, '#weight'=>-10);

  $expomenuitems = [
                     ' ',
                     ' ',
                     '',
                     '',
                   ];
  $ar = [];
  foreach($expomenuitems as $expomenuitem) {
    $ar[] = '<div class="item"><div class="text">'.$expomenuitem.'</div></div>';
  }
  $expomenu = implode('', $ar);

  $build['expomenu'] = array('#markup' => $expomenu);
  $build['featuredimage'] = _expo_node_get_featuredimage($entity);

}

function _expo_node_view_expo_teaser(array &$build, \Drupal\Core\Entity\EntityInterface $entity, \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display, $view_mode) {
  $actions = '';
  if(\Drupal::currentUser()->hasPermission('admin expo item')) {
    $form = [];
    $form['highlight'] = [
                           '#type' => 'checkbox',
                           '#title' => t('精選'),
                           '#attributes' => [
                             'class' => ['highlight-checkbox'],
                             'data-nid' => $entity->id(),
                           ],
                         ];
    if(WG::entity_get_field_value($entity, 'field_highlight') == 1) {
      $form['highlight']['#attributes']['checked'] = 'checked';
    }
    $actions = render($form);
  }
  $build['actions'] = array('#markup' => $actions);
  $build['featuredimage'] = _expo_node_get_featuredimage($entity);
}

function _expo_node_view_expo_colllist(array &$build, \Drupal\Core\Entity\EntityInterface $entity, \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display, $view_mode) {
  $url = WG::nodeurl($entity->id(), true);
  $permalink = $url->toString();
  $build['permalink'] = array(
                          '#markup' => $permalink,
                          '#type' => 'item',
                          '#title' => t('引用網址: '),
                        );

  $purl = WG::nodeurl(150);
  $plink = $purl->toString();
  $build['expotool'] =[
                        '#markup' =>
                        '<div id="expotool"><div class="wrapper">'
                        .'<a href="'.$plink.'"><i class="fa fa-arrow-circle-left"></i> 我們的318</a>'
                        .'<a href="'.$permalink.'?view=stickymap"><i class="fa fa-leaf"></i> 地圖顯示</a>'
                        .'<a href="'.$permalink.'?view=list"><i class="fa fa-th-list"></i> 列表顯示</a>'
                        .'</div></div>'
                      ];
}

function _expo_node_get_featuredimage(\Drupal\Core\Entity\EntityInterface $entity, $stylename = 'expologo', $default_uri = 'public://expo/logo.png') {
  $featuredimage_id = WG::entity_get_field_value($entity, 'field_featuredimage', 0, 'target_id');
  if(!$featuredimage_id) {
    $featuredimage_uri = $default_uri;
  } else {
    $file =  \Drupal\file\Entity\File::load($featuredimage_id);
    $featuredimage_uri = $file->getFileUri();
  }

  $r = WG::render_styled_image($featuredimage_uri, $stylename, false);
  return $r;
}

function _expo_field_collitem_view_list(array &$build, \Drupal\Core\Entity\EntityInterface $entity, \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display, $view_mode) {
  $fcitem = $entity;
  $target = WG::entity_get_field_value($fcitem, 'field_target');
  preg_match('%^([^:]+)://([0-9]+)$%', $target, $m);
  $itemtype = $m[1];
  $id = $m[2];
  $classes = array('collitem_preview');
  $r = '';
  switch($itemtype) {
    case 'public318':
      $identifier = $id;
      $text = WG::entity_get_field_formatted_text($fcitem, 'field_annotation');
      $stylename = 'medium';
      $icon_uri = _expo_public318_get_icon_uri($identifier);
      $img = WG::render_styled_image($icon_uri, $stylename);
      $url = 'aa';
      $icon_tag = _expo_coll_url($identifier, $img);

      $r .= WG::btGrid_col($icon_tag, 'collitem-icon', 4);
      $r .= WG::btGrid_col( $text,'collitem-content', 8);
      break;
    case 'storynode':
      $nid = $id;
      $story = node_load($nid);
      $v = entity_view($story, 'ajaxpage');
      $output = render($v);
      break;
    default:
      $tag = "<div class=\"sticky\" id=\"sticky_$pos\">".$itemtype.$pos."</div>";
  }

  $output = WG::btGrid($r, $classes, false);
  $build = ['#markup'=>$output];

  return;
}

function _expo_field_collitem_alter(&$element, \Drupal\Core\Form\FormStateInterface $form_state, $context) {
  $schema_base = WG::config_get('expo.env', 'schema.base');
  $schema_json = $schema_base . WG::config_get('expo.env', 'schema.json');
  $schema_icon = $schema_base . WG::config_get('expo.env', 'schema.icon');

  $id_prefix = 'expo_field_asticky_';
  $delta = $element['#delta'];

  $element['#prefix'] = '<div id ="'.$id_prefix.$delta.'">';
  $element['#suffix'] = '</div>';

  $fcid = $context['items']->get($delta)->value;
  if($fcid) {
    $fcitem = \Drupal\field_collection\Entity\FieldCollectionItem::load($fcid);

    $element['fcpreview'] = array(
                              '#markup' => _expo_field_widget_form_itemview($fcitem),
                              '#weight' => -50);
    if($fcitem) {
      $element['field_annotation']['#attributes']['style'] = 'display:none';
      $element['field_target']['#attributes']['style'] = 'display:none';
      $itemtype = WG::entity_get_field_value($fcitem, 'field_itemtype');
      $tag = WG::xmlTag('span', '編輯',
                        [
                          'class'=>'fcitemeditbtn btn btn-default',
                          'data-delta'  => $delta,
                          'data-target' => $id_prefix.$delta,
                          'data-itemtype' => $itemtype,
                        ]
                       );
      $element['actions']['edit2'] = array ('#markup' => $tag);
    }

  } else {
    $element['public318'] =
      array(
        '#type' => 'container',
        '#weight' => -50,
      );
    $element['public318']['searchtext'] =
      array(
        '#type' => 'textfield',
        '#default_value' => 'term:歌詞',
        '#size' => 60,
        '#description' => '輸入藏品識別號或搜尋字串',
        '#maxlength' => 128,
      );

    $config = \Drupal::config('expo.ns');
    $url = $config->get('public318');

    $tag = WG::xmlTag('span', '編輯',
                      [
                        'class'=>'public318search btn btn-default',
                        'data-delta'  => $delta,
                        'data-url'    => $url,
                      ]
                     );
    $element['public318']['searchbtn'] = array ('#markup' => $tag);
    $element['public318']['wrap'] =
      array(
        '#markup' => '<div id="search-result-'.$delta.'">aaa</div>',
      );

  }
}

function _expo_field_widget_form_itemview($fcitem) {
  $schema_base = WG::config_get('expo.env', 'schema.base');
  $schema_json = $schema_base . WG::config_get('expo.env', 'schema.json');
  $schema_icon = $schema_base . WG::config_get('expo.env', 'schema.icon');
  $r = '';
  $classes = array('collitem_preview');
  if($fcitem) {
    $itemtype = WG::entity_get_field_value($fcitem, 'field_itemtype');
    $classes[] = $itemtype;
    $stylename = 'thumbnail';
    switch($itemtype) {
      case 'public318':
        $identifier = WG::entity_get_field_value($fcitem, 'field_target');
        $ns = 'public318';
        $fn = $schema_json.'/'.$ns.'-'.$identifier.'.json';
        $json = file_get_contents($fn);
        $data = Json::decode($json);
        $icon_uri = $schema_icon.'/'.$identifier.'.jpg';
        $icon_url = file_create_url($icon_uri);

        $title = '識別號：'.$identifier;
        $r .= WG::btGrid_col(WG::render_styled_image($icon_uri, $stylename), 'collitem-icon', 2);
        break;
      case 'story':
        $target = WG::entity_get_field_value($fcitem, 'field_target');
        if(preg_match('%^storynode://([0-9]+)%', $target,$m)) {
          $nid = $m[1];
          $story = node_load($nid);

          $title = $story->title->value;
          $body = WG::entity_get_field_formatted_text_trim($story, 'field_body');
          $r .= WG::btGrid_col('x', 'collitem-icon', 2);
          $r .= WG::btGrid_col( "<h4>$title</h4><div>$body</div>", 'collitem-content', 10);
          break;
        } else {

        }
    }
  }
  $output = WG::btGrid($r, $classes);
  return $output;
}

function _expo_view_stickymap_sticky_link($sticky_type, $img, $pos, $fcid) {
  $url = _expo_ajax_url('fc', $fcid)->toString();
  $el = WG::xmlTag('a',
                   WG::xmlTag('span', $img, ['class'=>'sticky_wrap']),
                   [
                     'class' => 'sticky '.'sticky-'.$sticky_type.' ajaxpopup fccollitem',
                     'id' => 'sticky_'.$pos,
                     'href' => $url
                   ]
                  );
  return $el;
}

function _expo_view_stickymap($entity) {
  $schema_base = WG::config_get('expo.env', 'schema.base');
  $schema_icon = $schema_base . WG::config_get('expo.env', 'schema.icon');

  $cnt = count($entity->field_collitem);
  $elements = array();
  for($i=0; $i<$cnt; $i++) {
    if($i >= 78) break;
    $fcid = $entity->field_collitem[$i]->value;
    $fcitem = \Drupal\field_collection\Entity\FieldCollectionItem::load($fcid);
    $pos = $i+1;
    $target = WG::entity_get_field_value($fcitem, 'field_target');
    if(preg_match('%^([^:]+)://([0-9]+)$%', $target, $m)) {}
    else {
      DT::dbug($target);
    }

    $itemtype = $m[1];
    $id = $m[2];
    $sticky_type = 'image';
    switch($itemtype) {
      case 'public318':
        $identifier = $id;
        $uri = _expo_public318_get_icon_uri($identifier);
        $img = _expo_view_stickymap_image($uri, $pos);
        $element = _expo_view_stickymap_sticky_link($sticky_type, $img, $pos, $fcid);
        break;
      case 'storynode':
        $nid = $id;
        $easteregg_nid = WG::config_get('expo.settings', 'easter_egg.nid');
        if($nid == $easteregg_nid) {
          $easteregg_uri = WG::config_get('expo.settings', 'easter_egg.image');
          $img = _expo_view_stickymap_image($easteregg_uri, $pos);
          $element = _expo_view_stickymap_sticky_link($sticky_type, $img, $pos, $fcid);
        } else {
          $story = node_load($nid);
          $str = $story->title->value;
          $element = _expo_view_stickymap_story($str, $pos, $fcid);
        }
        break;
    }
    $elements[] = $element;
  }

  if($cnt < 78) {
    $easteregg_uri = WG::config_get('expo.settings', 'easter_egg.image');
    for($i=$cnt; $i<78; $i++) {
      $pos = $i+1;
      $img = _expo_view_stickymap_image($easteregg_uri, $pos);
      $element = "<div class=\"sticky\" id=\"sticky_$pos\">".$img."</div>";
      $elements[] = $element;
    }
  }
  $d = WG::xmlTag('div',
                  ''.
                  WG::xmlTag('div', '', ['id' => 'sticky_arrow_start'], false)
                  .implode('', $elements),
                  ['id' => "stickymap"]
                 );
  return $d;
}

function _expo_view_stickymap_image($uri, $pos) {
  $type = WG::config_get('expo.sticky', 'map.'.$pos);
  $dest_uri = _expo_view_stickymap_image_typed_icon($uri, $type);
  $url = file_create_url($dest_uri);
  $img = WG::xmlTag('img', '', ['alt' => $uri, 'src'=> $url]);
  return $img;
}

function _expo_view_stickymap_story($str, $pos, $fcid) {
  $type = WG::config_get('expo.sticky', 'map.'.$pos);
  $fn = 'story-'.md5($str);
  $uri = _expo_view_stickymap_story_stickyuri($fn, $type, 'png');
  $opts = WG::config_get('expo.sticky', 'type.'.$type);
  $sticky_uri = Sticky::gen($str, $uri, $opts);
  $url = file_create_url($sticky_uri);
  $img = WG::xmlTag('img', '', ['alt' => $uri, 'src'=> $url]);
  $element = _expo_view_stickymap_sticky_link('story', $img, $pos, $fcid);
  return $element;
}

function _expo_view_stickymap_story_stickyuri($fn, $type, $ext = 'png') {
  $schema_base = WG::config_get('expo.settings', 'schema.base');
  $schema_media = $schema_base . WG::config_get('expo.settings', 'schema.media');
  $schema_sticky = $schema_media.'/'.'sticky';

  $uri = $schema_sticky.'/'.$fn.'-'.$type.'.'.$ext;
  return $uri;
}

function _expo_view_stickymap_stickyuri($uri, $type, $ext = 'jpg') {
  $schema_base = WG::config_get('expo.settings', 'schema.base');
  $schema_media = $schema_base . WG::config_get('expo.settings', 'schema.media');
  $schema_sticky = $schema_media.'/'.'sticky';
  $fn = pathinfo($uri, PATHINFO_FILENAME );
  $uri = $schema_sticky.'/'.$fn.'-'.$type.'.'.$ext;
  return $uri;
}

function _expo_view_stickymap_image_typed_icon($uri, $type, $reset = false) {
  $dest_uri = _expo_view_stickymap_stickyuri($uri, $type);
  $dest = \Drupal::service('file_system')->realpath($dest_uri);
  if(!file_exists($dest)) {
    file_prepare_directory($schema_sticky, FILE_CREATE_DIRECTORY);
    $opts = WG::config_get('expo.sticky', 'type.'.$type);
    $w = $opts['w'];
    $h = $opts['h'];
    $source = \Drupal::service('file_system')->realpath($uri);
    $cmd = "convert $source -resize ${w}x${h}^ -gravity center -extent ${w}x${h} $dest";
    $rr = shell_exec($cmd);
  }
  return $dest_uri;
}

function _expo_mailhash($to, $viewurl, $editurl) {
  $mailManager = \Drupal::service('plugin.manager.mail');

  $module = 'expo';
  $key = 'mailhash';
  $params['message'][] = '感謝你使用「我們的３１８－個人策展系統」，策劃分享屬於你的視野的318公民運動故事。';
  $params['message'][] = '';
  $params['message'][] = '請使用以下資訊，進一步的分享及編輯您的個人策展。';
  $params['message'][] = '分享：'.$viewurl;
  $params['message'][] = '編輯：'.$editurl;
  $params['message'][] = '';
  $params['message'][] = '若您有任何建議，我們亦非常歡迎你的來信。請寄至expo@318.io';

  $params['title'] = '您的個人策展頁面已建置完成';
  $langcode = \Drupal::currentUser()->getPreferredLangcode();
  $send = true;

  $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
  if ($result['result'] !== true) {
    $message = '很抱歉，無法寄送電子郵件，請記住你的分享與編輯網址。';
    \Drupal::logger('expo')->error($message);
    return;
  }

  $message = t('你的分享與編輯網址已寄到你的電子郵件信箱（@email）。', array('@email' => $to,));
  drupal_set_message($message);
  \Drupal::logger('expo')->notice($message);
}
