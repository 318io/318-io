
<footer id="footer" class="footer  text-right">
  <div class="container">
    <div class="col-sm-12 col-md-12 col-lg-12">
      <?php if($usernav):?><?php print $usernav;?><?php endif;?>
      <?php if($page['footer']):?><?php print render($page['footer']); ?><?php endif;?>
    </div>
  </div>
</footer>
