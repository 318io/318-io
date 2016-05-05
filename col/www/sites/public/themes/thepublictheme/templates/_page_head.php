<header id="page-header" role="banner">
  <div class="container-fluid">
    <div class="site-branding">
      <h1 class="site-title">
        <a class="site-name" href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>"><?php print $site_name; ?></a>
      </h1>
    </div>
  </div>
</header>

<section id="pagina-nav">
	<div class="container">
	  <div class="row">

	    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div id="mainnav" class="mainnav">
	        <nav role="navigation" class="nav-zone">
	          <?php if (!empty($page['navigation'])): ?>
	            <?php print render($page['navigation']); ?>
	          <?php endif; ?>
	        </nav>
	      </div>
	    </div>

	  </div>
	</div>
</section>

<?php if (!empty($page['help']) || !empty($messages)): ?>
<div id="message">
  <?php print $messages; ?>
  <?php print render($page['help']); ?>
</div>
<?php endif; ?>

<a id="main-content"></a>
