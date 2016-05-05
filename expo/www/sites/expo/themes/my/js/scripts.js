jQuery(document).ready(function($) {

  $(window).load(function() {
      $('.highlight-checkbox').click(function(){
        var el=$(this);
        var c = el.prop('checked');
        var nid = el.data('nid');
        var url = '/p/edithighlight/'+nid+'/';
        if(c) url += '1';
        else url += '0';
    $.get(url, {}).done(function(data) {
        //console.log(data);
    });

      })

    $('a.ajaxpopup').colorbox({
      width: '50%',
      height: '90%',

      transition: 'none',
      scalePhotos: false,

    });
    $('a.ajaxpopup-on-start').click();
    $(".inlinepopup").colorbox({inline:true, width:"50%"});

    scrolltotop();

    $('.sticker').hover(
      function() {
        var el = $(this);
        var pos = el.position();
        el.css({
          "left": (pos.left - 2) + "px",
          "top": (pos.top - 2) + 'px'
        });
      },
      function() {
        var el = $(this);
        var pos = el.position();
        el.css({
          "left": (pos.left + 2) + "px",
          "top": (pos.top + 2) + 'px'
        });
      }

    )
  });

  function scrolltotop() {
    $(window).scroll(function() {
      if ($(this).scrollTop() != 0) {
        $("#toTop").fadeIn();
      } else {
        $("#toTop").fadeOut();
      }
    });

    $("#toTop").click(function() {
      $("body,html").animate({
        scrollTop: 0
      }, 800);
    });
  }

});
