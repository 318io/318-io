<?php require '_page_head.php';?>

<div id="breadcrumb" role="banner">
  <div class="container">
    <?php if (!empty($breadcrumb)): print $breadcrumb; endif;?>
  </div>
</div>

<div id="page-content" class="main-container">
  <div class="container">
      <?php if (!empty($action_links)): ?>
        <ul class="action-links"><?php print render($action_links); ?></ul>
      <?php endif; ?>
      <?php print render($page['content']); ?>
  </div>
</div>

<?php require '_page_foot.php';?>