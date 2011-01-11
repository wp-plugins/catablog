// color conversion, hex to rgb and rgb to hex
function HexToR(h) {return parseInt((cutHex(h)).substring(0,2),16)}
function HexToG(h) {return parseInt((cutHex(h)).substring(2,4),16)}
function HexToB(h) {return parseInt((cutHex(h)).substring(4,6),16)}
function cutHex(h) {return (h.charAt(0)=="#") ? h.substring(1,7):h}
function hexFromRGB (r, g, b) {
	var hex = [
		r.toString(16),
		g.toString(16),
		b.toString(16)
	];
	jQuery.each(hex, function (nr, val) {
		if (val.length == 1) {
			hex[nr] = '0' + val;
		}
	});
	return hex.join('').toUpperCase();
}



function show_load() {
	jQuery('body').append("<div id='catablog_load_curtain' />");
	jQuery('#catablog_load_curtain').append("<div id='catablog_load_display' >processing</div>");
	
	jQuery('#catablog_load_curtain').fadeTo(200, 0.8);
}

function hide_load() {
	// $('#catablog_load_display').html('finishing');
	
	setTimeout(function() {
		jQuery('#catablog_load_curtain').fadeOut(400, function() {
			jQuery(this).remove();
		});
	}, 500);
}


function discourage_leaving_page(message) {
	var all_links = jQuery('a').filter(function() {
		return ( jQuery(this).attr('href').charAt(0) != '#' );
	}).filter(function() {
		return (jQuery(this).hasClass('cb_disabled_link') == false);
	});
	
	all_links.bind('click', function(event) {
		if (message == null) {
			message = "Image changes are still rendering, are you sure you want to leave this page?";
		}
		if(!confirm(message)) {
			return false;
		}
	});
}

function unbind_discourage_leaving_page() {
	var all_links = jQuery('a').filter(function() {
		return ( jQuery(this).attr('href').charAt(0) != '#' );
	});
	
	all_links.unbind('click');
}

