<?php
/**
 * @file
 * Contains \Drupal\expo\Controller\ExpoController.
 */

namespace Drupal\expo\Controller;

use Drupal\Core\Controller\ControllerBase;

use Drupal\wg\WG;
use Drupal\wg\DT;
use Drupal\expo\ExpoItemListBuilder;

use Drupal\field_collection\Entity\FieldCollectionItem;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Theme\ThemeAccessCheck;
use Drupal\Core\Url;
use Drupal\system\SystemManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\node\NodeInterface;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Serialization\Json;

class ExpoController extends ControllerBase {
  public function content_ajax_page($type, $id) {
    switch($type) {
      case 'node':
        $node = node_load($id);
        $v = entity_view($node, 'ajaxpage');
        break;
      case 'fc':
        $v = $this->content_ajax_page_fc($id);
        break;
    }
    $output = render($v);
    echo $output;
    die();
  }

  private function content_ajax_page_fc($fcid) {
    $fcitem = \Drupal\field_collection\Entity\FieldCollectionItem::load($fcid);
    $target = WG::entity_get_field_value($fcitem, 'field_target');
    preg_match('%^([^:]+)://([0-9]+)$%', $target, $m);
    $itemtype = $m[1];
    $id = $m[2];
    switch($itemtype) {
      case 'public318':
        $identifier = $id;
        $text = WG::entity_get_field_formatted_text($fcitem, 'field_annotation');
        $stylename = 'large';
        $icon_uri = _expo_public318_get_icon_uri($identifier);
        $icontag = WG::render_styled_image($icon_uri, $stylename);
        $output = '<div class="sticky-fc-public318">'.
                  '<div class="collicon">'._expo_coll_url($identifier, $icontag).'</div>'.
                  '<div class="colltext">'.$text.'</div>'.
                  '</div>';
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

    $build = ['#markup'=>$output];

    return $build;
  }

}
