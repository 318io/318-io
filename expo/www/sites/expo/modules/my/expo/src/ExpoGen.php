<?php
namespace Drupal\expo;

use Drupal\wg\DT;
use Drupal\wg\WG;
use Drupal\Component\Serialization\Json;

use Drupal\node\Entity\Node;
use Drupal\Core\Url;

use Drupal\field_collection\Entity\FieldCollection;
use Drupal\field_collection\Entity\FieldCollectionItem;

class ExpoGen {

  public static function genRandomExpoUser($nodenums=1, $stickymax = 78) {
    $ns = 'public318';

    $fetchurl = _expo_coll_fetchurl($ns);
    $json_fn = $ns.'-all';
    $cache_fn_all = _expo_get_json_fn($json_fn);
    $json = WG::fetch_url($fetchurl, $cache_fn_all);

    $ret = Json::decode($json);
    $datas = $ret['results'];
    $min = 0;
    $max = count($datas)-1;
    $max = 1000;

    for($i=1; $i<=$nodenums; $i++) {
      $d = [];
      $d['title'] = '標題expo-auto-user-'.date('ymdhi');
      $d['public'] = 1;
      $d['showinfront'] = 0;
      $d['email'] = 'aa@bb.cc';
      $d['author'] = 'i, the author';
      $d['body'] = "這是描述\nsecond paragraph\n很常的第三段：最著名和最明確的倡議是讓-保羅·沙特的格言：「存在先於本質」（法語：l'existence précède l'essence）。他的意思是說，除了人的生存之外沒有天經地義的道德或靈魂。道德和靈魂都是人在生存中創造出來的。人沒有義務遵守某個道德標準或宗教信仰，卻有選擇的自由。要評價一個人，要評價他的所作所為，而不是評價他是個什麼人物，因為一個人是由他的行動來定義的。";

      $items = self::_getRandomItems(0, $stickymax, $min, $max);

      $pos = 0;
      foreach($items as $item) {
        $collitem = [];
        $collitem['weight'] = $pos;
        $pos++;
        DT::dnotice($pos.'-'.$item['itemtype'].':'.$item['id']);
        switch($item['itemtype']) {
          case 'story':
            $collitem['target'] = 'storynode://'.$item['id'];
            $collitem['annotation'] = [];
            break;
          case 'public318':
            $id = $item['id'];
            $entity = $datas[$id];
            self::_fetch_public318($entity);
            $collitem['target'] = 'public318://'.$entity['identifier'];
            //$text = $entity['text'];
            //$text = str_replace(';;;', "\n", $text);
            $text = 'user input annotation of '.$id;
            $collitem['annotation'] = ['format'=>'rich_html_base', 'value'=>$text];
            break;
        }
        $d['collitems'][] = $collitem;
      }
      \Drupal\expo\ExpoCrud::saveExpo($d);
    }
  }


  public static function genRandomExpoFront($nodenums=1, $stickymax = 78, $storymax = 10 ) {
    $ns = 'public318';

    $fetchurl = _expo_coll_fetchurl($ns);
    $json_fn = $ns.'-all';
    $cache_fn_all = _expo_get_json_fn($json_fn);
    $json = WG::fetch_url($fetchurl, $cache_fn_all);

    $ret = Json::decode($json);
    $datas0 = $ret['results'];

    $public318_ids = [
                       10478, 11857, 11861, 11897, 11902, 11914, 11933, 11940, 11941, 11956,
                       11960, 11962, 12377, 12378, 12382, 12395, 12578, 12663, 12669, 12681,
                       12684, 13035, 13049, 13050, 13051, 13064, 13065, 13066, 13067, 13070,
                       13076, 13077, 13078, 13100, 13121, 13122, 13130, 13135, 13136, 13138,
                       13140, 13142, 13144, 13145, 13149, 13173, 13182, 13202, 13225, 13253,
                       13255, 13258, 13259, 13260, 13264, 13265, 13266, 13267, 13292, 13305,
                       13313, 13325, 13328, 13329, 13343, 13348, 13349, 13350, 13351, 13352,
                       13353, 13354, 13356, 13377, 13391, 13394, 13395, 13396, 13413, 13416,
                       13428, 14944, 14945, 14946, 14948, 14951, 14952, 14953, 14954, 14955,
                       14956, 14957, 14959, 14960, 14961, 14962, 14963, 14967, 14968, 14969,
                       14970, 14971, 14972,
                     ];
    $datas = [];
    foreach($datas0 as $data0) {
      $id = $data0['identifier'];
      if(in_array($id, $public318_ids)) {
        //echo $id.' ';
        $datas[$id] = $data0;
      }
    }

    for($i=1; $i<=$nodenums; $i++) {
      $d = [];
      $d['title'] = 'expo-autofront-'.date('ymdhi');
      $d['public'] = 0;
      $d['showinfront'] = 1;

      $items = self::_getRandomItems2($storymax-1, $stickymax-1, $public318_ids);

      $easteregg_index = rand(0, $stickymax-1);
      $el = ['itemtype'=>'story', 'id'=>WG::config_get('expo.settings', 'easter_egg.nid')];

      $items = DT::array_insert_element($items, $easteregg_index, $el);
      //print_r($items);
      //die();
      $pos = 0;
      foreach($items as $item) {
        $collitem = [];
        $collitem['weight'] = $pos;
        $pos++;
        DT::dnotice($pos.'-'.$item['itemtype'].':'.$item['id']);
        switch($item['itemtype']) {
          case 'story':
            $collitem['target'] = 'storynode://'.$item['id'];
            $collitem['annotation'] = [];
            break;
          case 'public318':
            $id = $item['id'];
            //echo $id.' ';
            $entity = $datas[$id];

            self::_fetch_public318($entity);
            $collitem['target'] = 'public318://'.$entity['identifier'];
            $text = $entity['text'];
            $text = str_replace(';;;', "\n", $text);
            $collitem['annotation'] = ['format'=>'rich_html_base', 'value'=>$text];
            break;
        }
        $d['collitems'][] = $collitem;
      }
      \Drupal\expo\ExpoCrud::saveExpo($d);
    }
  }

  private static function _getRandomItems2($storymax, $stickymax, $public318_ids) {
    $story_cc = DT::random_num_list(4, 35, $storymax);
    $min = 0;
    $max = count($public318_ids)-1;
    $cc = DT::random_num_list($min, $max, $stickymax - $storymax);
    //print_r($cc);
    //die();
    $ins_cc = DT::random_num_list(0, $stickymax-1, $storymax);
    sort($ins_cc);
    $sss = array();
    $k = 0;
    for($j = 0; $j<$stickymax; $j++) {
      if(in_array($j, $ins_cc)) {
        $id = array_shift($story_cc);
        $sss[] = array(
                   'itemtype' => 'story',
                   'id' => $id,
                 );
      } else {
        $id = array_shift($cc);
        $sss[] = array(
                   'itemtype' => 'public318',
                   'id' => $public318_ids[$id],
                 );
      }
    }
    return $sss;
  }
  private static function _getRandomItems($storymax, $stickymax, $min, $max) {
    $story_cc = DT::random_num_list(4, 35, $storymax);
    $cc = DT::random_num_list($min, $max, $stickymax - $storymax);
    $ins_cc = DT::random_num_list(0, $stickymax-1, $storymax);
    sort($ins_cc);
    $sss = array();
    $k = 0;
    for($j = 0; $j<$stickymax; $j++) {
      if(in_array($j, $ins_cc)) {
        $id = array_shift($story_cc);
        $sss[] = array(
                   'itemtype' => 'story',
                   'id' => $id,
                 );
      } else {
        $id = array_shift($cc);
        $sss[] = array(
                   'itemtype' => 'public318',
                   'id' => $id,
                 );
      }
    }
    return $sss;
  }

  private static function _fetch_public318($entity) {
    $ns = 'public318';
    $ext = 'jpg';

    $col = array(
             'identifier' => $entity['identifier'],
             'icon' => $entity['icon'],
             'body' => $entity['text'],
           );

    $identifier = $entity['identifier'];

    if($entity['icon']) {
      $icon_realpath = _expo_public318_get_icon_by_id($identifier, $entity['icon_info']);
    }
    return true;
  }

}
