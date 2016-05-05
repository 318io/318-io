jQuery(document).ready(function($) {

	$(window).load(function() {
		$('.feature-image.multiple .icons li').click(function(){
			var data = $(this).data('key');
			var t = $(".feature-image.multiple .features ul").find("[data-key='" + data + "']");
			var w = t.width();
			var index = t.index();
			var l = -1*index*w;
			$(".feature-image.multiple .features ul").animate({left: l,}, 1000);

		})
	});
});
