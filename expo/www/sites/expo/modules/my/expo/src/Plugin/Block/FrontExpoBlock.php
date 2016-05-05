<?php
/**
 * @file
 * Contains \Drupal\yourmodule\Plugin\Block\YourBlockName.
 */
namespace Drupal\expo\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\wg\DT;

/**
 * Provides custom block.
 *
 * @Block(
 *   id = "front_expo_block",
 *   admin_label = @Translation("Front Expo Block"),
 *   category = @Translation("WG")
 * )
 */
class FrontExpoBlock extends BlockBase {

  /**
     * {@inheritdoc}
     */
  public function build() {
    $nid = self::_gen_random_nid();
    $node = node_load($nid);
    $v = entity_view($node, 'full');
    $build = [
               '#markup' => render($v),
               '#cache' => ['max-age'=>0],
             ];
    return $build;
  }

  private static function _gen_random_nid() {
    $nids = \Drupal::entityQuery('node')
            ->condition('field_showinfront.value', 1, '=')
            ->execute();
    $nid = DT::random_in_list($nids);
    return $nid;
  }

}
