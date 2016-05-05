<?php
  hide($content['comments']);
  hide($content['links']);
?>

<?php if($view_mode == 'full'):?>

<article id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> clearfix"<?php print $attributes; ?>>
  <?php print $thenav;?>

  <div class="content clearfix"<?php print $content_attributes; ?>>
    <div class="content-main col-md-7 col-lg-6">
      <?php print render($feature_image); ?>
      <?php print render($content['field_description']);?>
      <?php print render($content['field_measurement']);?>
    </div>
    <div class="content-meta col-md-5 col-lg-6">
      <h2><?php print $title; ?></h2>
      <?php print render($content);?>
    </div>
  </div>
  <div class="node-comments clearfix">
    <?php print render($content['comments']);?>
  </div>

</article>
<?php elseif($view_mode == 'grid'):?>
  <?php
    $index = $node->collopts['row'] + 1;
    $classes .= (($index %2) == 0)?' index-even':' index-odd';
    $attributes .= ' data-linkurl="'.$linkurl.'"';
  ?>
  <div class="col-xs-12	col-sm-6 col-md-4 col-lg-3">
    <div class="linkableblock node-grid-wrapper"<?php print $attributes; ?>>
      <section id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> clearfix">
        <div class="content"<?php print $content_attributes; ?>>
          <div class="index"><?php print $index; ?></div>
          <div class="content-main"><?php print render($feature_image); ?></div>
          <div class="content-meta"><?php print render($content);?></div>
        </div>
      </section>
    </div>
  </div>

<?php elseif($view_mode == 'list'):?>
  <?php
    $index = $node->collopts['row'] + 1;
    $classes .= (($index %2) == 0)?' index-even':' index-odd';
    $attributes .= ' data-linkurl="'.$linkurl.'"';
  ?>
  <div class="col-xs-12	col-sm-12 col-md-12 col-lg-123">
    <div class="linkableblock node-list-wrapper"<?php print $attributes; ?>>
      <section id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> clearfix">
        <div class="content"<?php print $content_attributes; ?>>
          <div class="index"><?php print $index; ?></div>
          <div class="content-main"><?php print render($feature_image); ?></div>
          <div class="content-meta"><?php print render($content);?></div>
        </div>
      </section>
    </div>
  </div>
<?php elseif($view_mode == 'teaser'):?>
  <?php
    $index = $node->collopts['row'] + 1;
    $classes .= (($index %2) == 0)?' index-even':' index-odd';
    $attributes .= ' data-linkurl="'.$linkurl.'"';
  ?>
  <div class="col-xs-12	col-sm-6 col-md-4 col-lg-3">
    <div class="linkableblock node-teaser-wrapper"<?php print $attributes; ?>>
      <section id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> clearfix">
        <div class="content"<?php print $content_attributes; ?>>
          <div class="index"><?php print $index; ?></div>
          <div class="content-main"><?php print render($feature_image); ?></div>
          <div class="content-meta"><?php print render($content);?></div>
        </div>
      </section>
    </div>
  </div>

<?php endif;?>
