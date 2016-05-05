<?php
namespace Drupal\expo;

use Drupal\wg\DT;
use Drupal\wg\WG;
use Drupal\Component\Serialization\Json;

use Drupal\node\Entity\Node;
use Drupal\Core\Url;

use Drupal\field_collection\Entity\FieldCollection;
use Drupal\field_collection\Entity\FieldCollectionItem;

class ExpoCrud {
  private static function _gen_hash($field = '') {
    do {
      $hash = DT::random_string(6, true);
      $entity_ids = \Drupal::entityQuery('node')
                    ->condition($field.'.value', $hash, '=')
                    ->execute();
    } while($entity_ids);
    return $hash;
  }

  public static function saveExpo($d) {
    $defaults = [
                  'title' => '無題',
                  'email' => '',
                  'author' => '匿名者',
                  'public' => 1,
                  'body' => '',
                  'showinfront' => 0,
                  'hide' => 0,
                  'highlight' => 0,
                  'weight' => 0,
                  'featuredimage' => null,
                ];
    $d = array_merge($defaults, $d);
    $nid = $d['nid'];
    unset($d['nid']);
    if(!$nid) return self::saveExpo_new($d);
    else return self::saveExpo_exist($d, $nid);
  }

  public static function saveExpo_new($d) {
    $viewhash = self::_gen_hash('field_viewhash');
    $edithash = self::_gen_hash('field_edithash');
    //$node->field_image->setValue([
    //'target_id' => $file->id(),
    //]);
    $node_values =
      array(
        'type' => 'expo',
        'title' => $d['title'],
        'langcode' => 'und',
        'uid' => '1',
        'status' => 1,
        'field_edithash' => ['value' => $edithash,],
        'field_viewhash' => ['value' => $viewhash,],

        'field_authoremail' => ['value' => $d['email'],],
        'field_author_plain' => ['value' => $d['author'],],
        'field_public' => ['value' => $d['public'],],
        //tag 	field_tag 	Entity reference

        'field_showinfront' => ['value' => $d['showinfront'],],
        'field_hide' => ['value' => $d['hide'],],
        'field_highlight' => ['value' => $d['highlight'],],
        'field_weight' => ['value' => $d['weight'],],

        'path' => '/p/'.$viewhash,
      );
    if(is_array($d['body'])) $node_values['field_body'] = $d['body'];
    else $node_values['field_body'] = ['format'=>'rich_html_base', 'value'=>$d['body']];
    if($d['featuredimage']) {
      if(is_array($d['featuredimage'])) $node_values['field_featuredimage'] = $d['featuredimage'];
    }

    $node = Node::create($node_values);
    $node->save();

    foreach($d['collitems'] as $item) {
      $collitem = ['field_name' => 'field_collitem',];
      $collitem['field_target'] = array('value' => $item['target']);
      if($item['annotation']) $collitem['field_annotation'] = $item['annotation'];
      $fcitem = FieldCollectionItem::create($collitem);
      $fcitem->setHostEntity($node, false);
      $node-> {$fcitem->bundle()} [] = array('field_collection_item' => $fcitem);
      $fcitem->save(true);
    }
    $node->save();
    return $node;
  }

  public static function saveExpo_exist($d, $nid) {
    $node = node_load($nid);
    $node->setTitle($d['title']);
    $node->field_authoremail->setValue($d['email']);
    $node->field_author_plain->setValue($d['author']);
    $node->field_public->setValue($d['public']);
    //tag 	field_tag 	Entity reference

    if(is_array($d['body'])) $body_value = ['format'=>'rich_html_base', 'value'=>$d['body']['value']];
    else $body_value = ['format'=>'rich_html_base', 'value'=>$d['body']];
    //if(is_array($d['body'])) $body_value = $d['body']['value'];
    //else $body_value = $d['body'];

    $node->field_body->setValue($body_value);
    if($d['featuredimage']) {
      if(is_array($d['featuredimage'])) {
        $target_id =   $d['featuredimage']['target_id'];
        if($node->field_featuredimage) {
          $node->field_featuredimage->setValue(['target_id' => $target_id,]);
        }
      }
    }

    $node->save();

    $fcbundle = 'field_collitem';

    $cnt = count($node->$fcbundle);
    for($i=0; $i<$cnt; $i++) {
      $fcid = $node->field_collitem[$i]->value;
      $fcitem = \Drupal\field_collection\Entity\FieldCollectionItem::load($fcid);
      $fcitem->delete();
    }
    $node-> {$fcbundle} = [];
    //foreach ($node->{$fcbundle} as $key => $value) {
    //    unset($node->{$fcbundle}[$key]);
    //}
    //$node->save();

    foreach($d['collitems'] as $item) {
      $collitem = ['field_name' => 'field_collitem',];
      $collitem['field_target'] = array('value' => $item['target']);
      if($item['annotation']) $collitem['field_annotation'] = $item['annotation'];
      $fcitem = FieldCollectionItem::create($collitem);
      $fcitem->setHostEntity($node, false);
      $node-> {$fcitem->bundle()} [] = array('field_collection_item' => $fcitem);

      $fcitem->save(true);
    }
    $node->save();

    return $node;
  }

}
