//JS Library
jQuery(document).ready(function($) {
	$('div.catablog_row img.catablog_image').bind('click', function(event) {
		var fullsize_url = this.src.replace("/catablog/thumbnails", "/catablog/fullsize")
		open_lightbox(fullsize_url);
	});
});


function open_lightbox(pic_url) {
	var curtain_density = 0.85;
	var fadein_speed    = 0;
	var page_top        = jQuery(document).scrollTop() + 30;
	
	// add the curtain div into the DOM
	jQuery('body').append("<div id='catablog_curtain'>&nbsp;</div>");
	var curtain = jQuery('#catablog_curtain');
	
	// bind the curtain click and fade the curtain into view
	curtain.bind('click', function() {
		close_lightbox();
	});
	curtain.fadeTo(fadein_speed, curtain_density);
	
	
	// add the lightbox div into the DOM
	jQuery('body').append("<div id='catablog_lightbox'><div></div></div>");
	var lightbox = jQuery('#catablog_lightbox');
	
	// bind the lightbox click and fade the load indicator into view
	lightbox.bind('click', function() {
		close_lightbox();
	})
	lightbox.css('top', page_top);
	lightbox.fadeIn(fadein_speed);
	
	
	// load the full size picture and expand the lightbox to fit the images dimensions
	var fullsize_pic = new Image();
	fullsize_pic.onload = function() {
		expand_lightbox(this);
	}
	fullsize_pic.src = pic_url;
	
	
}




function close_lightbox() {
	var fadeout_speed = 300;
	
	jQuery('#catablog_curtain').fadeOut(fadeout_speed, function() {
		jQuery(this).remove();
	});
	jQuery('#catablog_lightbox').fadeOut(fadeout_speed, function() {
		jQuery(this).remove();
	});
}



function expand_lightbox(img) {
	var lightbox = jQuery('#catablog_lightbox div');
	var speed    = 600;
	
	var w = img.width;
	var h = img.height;
	var s = img.src;

	lightbox.animate({width:w, height:h}, speed, function() {
		jQuery(this).append("<img src='"+s+"' />");
		jQuery(this).css('backgroundImage', 'url()');
		jQuery(this).children('img').fadeIn(800);
	});
}