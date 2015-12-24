$(function () {

  /*
  Get corrdinates
  */
  function getPosition(element) {
    var xPosition = 0;
    var yPosition = 0;
    while(element) {
      xPosition += (element.offsetLeft - element.scrollLeft + element.clientLeft);
      yPosition += (element.offsetTop - element.scrollTop + element.clientTop);
      element = element.offsetParent;
    }
    return { x: xPosition, y: yPosition };
  }


  function getClickPosition(e) {
    var parentPosition = getPosition(e.currentTarget);

    var xPosition = e.clientX - parentPosition.x;
    var yPosition = e.clientY - parentPosition.y;

    console.log('here');

    alert('X: ' + xPosition + ', ' + 'Y: ' + yPosition);
  }

  //$(".iframe").colorbox({iframe:true, width:"65%", height:"95%"});

  /*
  $(document).bind('cbox_complete', function(){
    $('.sticker[kind]').colorbox.resize();
  }); */


  $('.idoc').colorbox({opacity:0.85 , inline: true, scrolling: true, width: "713px", height: "90%" }); // internal document


  $('.sticker[data-kind="0"]').colorbox({ opacity:0.85 , inline: true, scrolling: false, innerWidth: "400px", innerHeight: "400px" }); // sticker
  $('.sticker[data-kind="1"]').colorbox({ opacity:0.85 , inline: true, scrolling: true, width: "713px", height: "90%" }); // story
  //$('.sticker[data-kind="1"]').colorbox({ opacity:0.5 , inline: true, scrolling: true, width: "713px", height: "580px" }); // story
  //$('.sticker[data-kind="2"]').colorbox({ opacity:0.85 , inline: true, scrolling: true, width: "693px", height: "510px", maxHeight: "770px", 
  $('.sticker[data-kind="2"]').colorbox({ opacity:0.85 , inline: true, scrolling: true, width: "545px",
    onOpen: function() {       
      var element = $.colorbox.element();
      var href = element.attr('href');
      //var test = $(href).find('img').attr('data-original');
      //console.log(test);
      $(href).find('img').trigger('lazy_load');
    },
    onComplete: function() {
      var href = $.colorbox.element().attr('href');
      var img = $(href).find('img');
      var width = (parseInt(img.attr('width'))) + 'px';
      var height = (parseInt(img.attr('height')) + 80) + 'px';
      //console.log('width ' + width);
      //console.log('height' + height);
      $.colorbox.resize({ 'innerWidth': width, 'innerHeight': height });
      //$.colorbox.resize({ width: "693px", height: "770px"});
    }
  }); // picture
  
  $('.sticker[data-kind="3"]').colorbox({ opacity:0.85 , inline: true, scrolling: true, width: "545px",
    onComplete: function() {
      var href = $.colorbox.element().attr('href');
      var video = $(href).find('video');
      var width = (parseInt(video.attr('width'))) + 'px';
      var height = (parseInt(video.attr('height')) + 80) + 'px';      
      $.colorbox.resize({ 'innerWidth': width, 'innerHeight': height });
      //video..videoUI();
    }
  });

  function __redirect() {
    //console.log(location);
    //document.location = location.origin;
    window.location = '/';
  }

  // the immediately open sticker will load immediately !!! no lazy load
  $('.sticker[data-kind="0"].opennow').colorbox({ open: true, opacity:0.85 , inline: true, scrolling: false, innerWidth: "400px", innerHeight: "400px", onClosed: __redirect }); 
  $('.sticker[data-kind="1"].opennow').colorbox({ open: true, opacity:0.85 , inline: true, scrolling: true, width: "713px", height: "90%", onClosed: __redirect });
  $('.sticker[data-kind="2"].opennow').colorbox({ open: true, opacity:0.85 , inline: true, scrolling: true, width: "545px",
    onOpen: function() {       
      var element = $.colorbox.element();
      var href = element.attr('href');
      //alert('opening');
      //$(href).find('img').trigger('lazy_load');
    },
    onComplete: function() {
      var href = $.colorbox.element().attr('href');
      var img = $(href).find('img');
      var width = (parseInt(img.attr('width'))) + 'px';
      var height = (parseInt(img.attr('height')) + 80) + 'px';
      //console.log('width ' + width);
      //console.log('height' + height);
      $.colorbox.resize({ 'innerWidth': width, 'innerHeight': height });
      //$.colorbox.resize({ width: "693px", height: "770px"});
    },
    onClosed: __redirect
  }); // picture
  $('.sticker[data-kind="3"].opennow').colorbox({ open: true, opacity:0.85 , inline: true, scrolling: true, width: "545px",
    onComplete: function() {
      var href = $.colorbox.element().attr('href');
      var video = $(href).find('video');
      var width = (parseInt(video.attr('width'))) + 'px';
      var height = (parseInt(video.attr('height')) + 80) + 'px';      
      $.colorbox.resize({ 'innerWidth': width, 'innerHeight': height });
      //video..videoUI();
    },
    onClosed: __redirect
  });

  //$('#trans_map').on('click', getClickPosition);

  $("img.lazy").lazyload({ event: 'lazy_load' });

  $(function () {
    $('[data-toggle="popover"]').popover()
  })

/*
  $(window).resize(function() {
     //var viewportWidth = $(window).width();
     //var viewportHeight = $(window).height();
     var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
     var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0)
     if(w <= 768) {  
       
     }
  });
*/

});
