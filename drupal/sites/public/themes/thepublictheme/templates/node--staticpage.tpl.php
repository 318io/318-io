<div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> clearfix"<?php print $attributes; ?>>

  <?php print render($title_prefix); ?>
    <h2<?php print $title_attributes; ?>><?php print $title; ?></h2>
  <?php print render($title_suffix); ?>
  <?php if(user_is_logged_in() && node_access('update', $node)):?>
    <div class="node_tools">
      <a href="/node/<?php print $node->nid;?>/edit">Edit</a>
    </div>
  <?php endif;?>

  <div class="content"<?php print $content_attributes; ?>>
    <?php
      hide($content['comments']);
      hide($content['links']);
      print render($content);
    ?>
  </div>

</div>
