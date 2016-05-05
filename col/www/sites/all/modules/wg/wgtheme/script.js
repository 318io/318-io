(function ($) {
  Drupal.behaviors.wgtheme = {
    attach: function(context, settings) {
      linkblock();
      ajaxload();
    }
  };

  function ajaxload(){
    $('.ajaxload').each(function( index ) {
      var el = $(this);
      var url = el.attr('href');
      url += '&ajax=html';
      el.attr('href', url);
    });

    $('body').on('click', '.ajaxload', function(e){
      var el = $(this);
      e.preventDefault();
      var tgt = el.data('ajaxtarget');
      var url = el.attr('href');
      $(tgt).html('<i class="fa fa-circle-o-notch fa-spin fa-4x"></i>');
      $.get( url, function( data ) {
        var d = $(data);
        $(tgt).replaceWith( d.hide());
        d.slideDown(1000, 'linear');
      });
    })
  }

  function linkblock() {
    $('body').on('click', '.linkableblock', function(e){
      var t = $(this);
      if (t.hasClass('link-disabled')) return;
      var url = t.data('linkurl');
      window.location = url;
    });
  }

})(jQuery);
