
<footer id="footer" class="footer  text-right">
  <div class="container">
    <div class="sitemap col-sm-12 col-md-6 col-lg-6">
      <?php if($page['sitemap']):?><?php print render($page['sitemap']); ?><?php endif;?>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-6">
      <?php if($page['footer']):?><?php print render($page['footer']); ?><?php endif;?>
    </div>
  </div>
</footer>
