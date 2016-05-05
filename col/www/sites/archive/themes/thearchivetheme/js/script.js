jQuery(document).ready(function($) {

	$(window).load(function() {

		var n = $('.node-nav');
		n.appendTo($('#toolbar-fixedbottom .middletbar'));
		
		$('.feature-image.multiple .icons li').click(function(){
			var data = $(this).data('key');
			var t = $(".feature-image.multiple .features ul").find("[data-key='" + data + "']");
			var w = t.width();
			var index = t.index();
			var l = -1*index*w;
			$(".feature-image.multiple .features ul").animate({left: l,}, 1000);

		})

		$('.page-search .node-collection.node-teaser').click(function(){
			var el = $(this);
			var a = $('a', el).first();
			var h = a.attr('href');
			window.location = h;
		})

	});
});
