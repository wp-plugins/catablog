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




function is_integer(s) {
	return (s.toString().search(/^[0-9]+$/) == 0);
}





function possibly_disable_save_button() {
	if (jQuery('small.error:visible').size() == 0) {
		jQuery('#save_changes').attr('disabled', false);
		jQuery('#save_changes').attr('class', 'button-primary');
	}
	else {
		jQuery('#save_changes').attr('disabled', true);
		jQuery('#save_changes').attr('class', 'button-disabled');
	}
}





function replaceSelection (input, replaceString) {
	if (input.setSelectionRange) {
		var selectionStart = input.selectionStart;
		var selectionEnd = input.selectionEnd;
		input.value = input.value.substring(0, selectionStart)+ replaceString + input.value.substring(selectionEnd);

		if (selectionStart != selectionEnd){ 
			setSelectionRange(input, selectionStart, selectionStart + 	replaceString.length);
		}else{
			setSelectionRange(input, selectionStart + replaceString.length, selectionStart + replaceString.length);
		}

	}else if (document.selection) {
		var range = document.selection.createRange();

		if (range.parentElement() == input) {
			var isCollapsed = range.text == '';
			range.text = replaceString;

			 if (!isCollapsed)  {
				range.moveStart('character', -replaceString.length);
				range.select();
			}
		}
	}
}
function setSelectionRange(input, selectionStart, selectionEnd) {
  if (input.setSelectionRange) {
    input.focus();
    input.setSelectionRange(selectionStart, selectionEnd);
  }
  else if (input.createTextRange) {
    var range = input.createTextRange();
    range.collapse(true);
    range.moveEnd('character', selectionEnd);
    range.moveStart('character', selectionStart);
    range.select();
  }
}