<?php require '_page_head.php';?>

<div id="content-search" class="mainregion">
  <div class="container">
    <div class="row">
      <?php print render($page['search']); ?>
    </div>
  </div>
</div>

<div>
  <div class="container">
    <div id="content-taxoncloud" class="col-md-12 col-lg-6">
      <?php print render($page['taxoncloud']); ?>
    </div>

    <div id="content-collhigh" class="col-md-12 col-lg-6">
      <?php print render($page['collhigh']); ?>
    </div>

  </div>
</div>
<div id="front-free-bottom">
  <?php print render($page['front_free_bottom']); ?>
</div>

<?php require '_page_foot.php';?>
