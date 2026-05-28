$(document).ready(function(e) {
    
	//iFrame
	$("a[rel='iframe']").fancybox({
		'width' : 1200,
		'height' : 800,
		'autoScale' : false,
		'padding' : 0,
		'margin' : 0,
		'transitionIn' : 'none',
		'transitionOut' : 'none',
		'type' : 'iframe'
	});
	
	//Video
	$("a[rel='video']").click(function() {
		$.fancybox({
			'padding'		: 0,
			'autoScale'		: false,
			'transitionIn'	: 'none',
			'transitionOut'	: 'none',
			'title'			: this.title,
			'width'			: 640,
			'height'		: 385,
			'href'			: this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
			'type'			: 'swf',
			'swf'			: {
			'wmode'				: 'transparent',
			'allowfullscreen'	: 'true'
			}
		});

		return false;
	});
	
	//Image
	$("a[rel='fancybox']").fancybox();
	$("a.fancybox-image").fancybox();
});