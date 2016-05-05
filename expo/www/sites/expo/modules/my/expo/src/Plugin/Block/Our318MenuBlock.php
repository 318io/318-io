<?php
namespace Drupal\expo\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\wg\DT;

/**
 * Provides custom block.
 *
 * @Block(
 *   id = "our318_menu_block",
 *   admin_label = @Translation("Our3l8 Menu Block"),
 *   category = @Translation("WG")
 * )
 */
class Our318MenuBlock extends BlockBase {

  /**
     * {@inheritdoc}
     */
  public function build() {

    $p_visited = DT::array_get($_COOKIE, 'p_visited', false);
    setcookie('p_visited', true);

    $menu = '
            <nav id="block-our318"><div id="expotool"><div class="wrapper"><ul class="menu nav">
            <li><a href="/"><i class="fa fa-home"></i> 首頁</a></li>
            <li><a href="/p/highlight"><i class="fa fa-star"></i> 精選</a></li>
            <li><a href="/ajax-page/node/247" class="ajaxpopup staticpage'.(!$p_visited?' ajaxpopup-on-start':'').'"><i class="fa fa-info-circle"></i> 關於</a></li>
            <li><a href="/ajax-page/node/166" class="ajaxpopup staticpage"><i class="fa fa-info-circle"></i> 使用</a></li>
            <li><a href="/ajax-page/node/165" class="ajaxpopup staticpage"><i class="fa fa-info-circle"></i> 免責</a></li>
            <li><a href="/p/add"><i class="fa fa-plus-circle"></i> 新增你的個人策展</a></li>
            </ul></div></div></nav>
            ';
    $build = [
               '#markup' => $menu,
               '#cache' => ['max-age'=>0],
             ];
    return $build;
  }

}
