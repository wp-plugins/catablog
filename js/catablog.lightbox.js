jQuery(function($) {
	jQuery.fn.catablogLightbox = function(config) {
		// PlugIn Variables
		var size     = this.size();
		var settings = {'size': size};
		if (config) jQuery.extend(settings, config);
		
		var timeout = null;
		
		var hold_click = false;
		
		// PlugIn Construction applied across each selected jQuery object
		this.each(function(i) {
			jQuery(this).bind('click', function(event) {
				
				// remove selection class from possible previous elements
				jQuery('.catablog-selected').removeClass('catablog-selected');
				
				// set row to the clicked element
				var row = this;
				
				// if row has a src attribtue and is likely an <img /> element
				if (this.src != undefined) {
					row = jQuery(this).closest('.catablog-row').get(0);
				}
				
				// select the current row and open the lightbox
				jQuery(this).addClass('catablog-selected');
				open_lightbox(row);
				
				// do not register the click
				return false;
			});
		});
		
		
		
		
		
		
		
		
		// Private Functions
		function open_lightbox(row) {
			// get the image inside the row
			
			var img = jQuery(row).find('img.catablog-selected').get(0);
			
			var support_fixed   = supportPositionFixed();
			var curtain_density = 0.85;
			var fadein_speed    = 0;
			var page_top        = jQuery(document).scrollTop() + 30;
			
			// add the curtain div into the DOM
			jQuery('body').append("<div id='catablog-curtain'>&nbsp;</div>");
			var curtain = jQuery('#catablog-curtain');

			// bind the curtain click and fade the curtain into view
			curtain.bind('click', function() {
				close_lightbox();
			});
			curtain.css('opacity', curtain_density);
			
			if (!support_fixed) {
				var window_height   = jQuery(window).height();
				var document_height = jQuery(document).height();
				curtain.css('position', 'absolute');
				curtain.height(document_height);
			}
			
			// if (supportPositionFixed() != true) {
			// 	jQuery('#catablog-curtain').height(jQuery(document).height());
			// }


			// add the lightbox div into the DOM
			jQuery('body').append("<div id='catablog-lightbox'><div id='catablog-whiteboard'></div></div>");
			var lightbox = jQuery('#catablog-lightbox');
			lightbox.css('top', page_top);	

			
			// MAKE NOTE HERE
			lightbox.bind('click', function() {
				close_lightbox();
			});
			jQuery('#catablog-whiteboard').bind('click', function(event) {
				event.stopPropagation();
				// do not return false, break a:links in whiteboard 
			});
			
			
			lightbox.show();


			// load the full size picture and expand the lightbox to fit the images dimensions
			var fullsize_pic = new Image();
			fullsize_pic.onload = function() {
				var meta = calculateMeta(row);
				expand_lightbox(this, meta);
			}
			
			fullsize_pic.src = img.src.replace("/catablog/thumbnails", "/catablog/fullsize");
			
		}
		
		
		function expand_lightbox(img, meta) {
			
			var lightbox = jQuery('#catablog-whiteboard');
			
			var w = img.width;
			var h = img.height;
			var s = img.src;
			
			
			var title       = "<h4 class='catablog-lightbox-title'>" + meta.title + "</h4>";
			var description = "<p class='catablog-lightbox-desc'>" + meta.description + "</p>";
			var nav         = meta.nav;
			var close       = meta.close
			
			// attach image and navigation
			jQuery(lightbox).append("<div id='catablog-lightbox-image' />");
			jQuery('#catablog-lightbox-image').height(h);
			
			if (!jQuery('#catablog-lightbox-image').append("<img src='"+s+"' />")) {
				alert('failed appending image to html dom');
			};
			jQuery('#catablog-lightbox-image').append(nav);
			jQuery('#catablog-lightbox-image a').height(h);
			
			// attach meta data below image
			jQuery(lightbox).append("<div id='catablog-lightbox-meta' />");
			jQuery('#catablog-lightbox-meta').append(title);
			jQuery('#catablog-lightbox-meta').append(description);
			
			jQuery('#catablog-whiteboard').append(close);
			
			
			lightbox.animate({width:w, height:h}, 400, function() {
				var full_height = h + jQuery('#catablog-lightbox-meta').outerHeight();
				
				jQuery(this).children('#catablog-lightbox-meta').show();
				jQuery(this).animate({height:full_height}, 400, function() {
					hold_click = false;
					bindNavigationControls();
				})
				
				jQuery('#catablog-lightbox-image').fadeIn(400, function() {
					
				});
			});
		}
		
		
		function change_lightbox(img) {
			var row   = jQuery(img).closest('.catablog-row').get(0);
			var speed = 150;
			
			jQuery('#catablog-lightbox-meta').fadeOut(speed, function() {
				jQuery(this).remove();
			});
			jQuery('#catablog-lightbox-image').fadeOut(speed, function() {
				jQuery(this).remove();
				
				var fullsize_pic = new Image();
				fullsize_pic.onload = function() {
					var meta = calculateMeta(row);
					expand_lightbox(this, meta);
				};
				
				fullsize_pic.src = img.src.replace("/catablog/thumbnails", "/catablog/fullsize");
				
				
			});			
		}
		
		
		function navigate_lightbox(direction) {
			if (hold_click) {
				return false;
			}
			
			hold_click = true;
			unbindNavigationControls();
			
			
			var selected = jQuery('.catablog-selected');
			var new_image  = null;
			
			if (direction == 'next') {
				new_image = selected.next('img.catablog-image');
				if (new_image.size() < 1) {
					new_image = jQuery('img.catablog-image:first');
				}
			}
			else if (direction == 'prev') {
				new_image = selected.prev('img.catablog-image');
				if (new_image.size() < 1) {
					new_image = jQuery('img.catablog-image:last');
				}
			}
			
			new_thumbnail = new_image.get(0);
			
			selected.removeClass('catablog-selected');
			new_image.addClass('catablog-selected');
			
			change_lightbox(new_thumbnail);
		}
		
		
		function close_lightbox() {
			unbindNavigationControls();
			
			var fadeout_speed = 300;
			
			jQuery('#catablog-curtain').fadeOut(fadeout_speed, function() {
				jQuery(this).remove();
			});
			jQuery('#catablog-lightbox').fadeOut(fadeout_speed, function() {
				jQuery(this).remove();
			});
			jQuery('.catablog-selected').removeClass('catablog-selected');
		}
		
		
		
		function calculateMeta(row) {
			var row          = jQuery(row);
			var prev_tip     = "You may also press P or the left arrow on your keyboard";
			var next_tip     = "You may also press N or the right arrow on your keyboard";
			var prev_button  = "<a href='#prev' id='catablog-lightbox-prev' class='catablog-nav' title='"+prev_tip+"'><span class='catablog-lightbox-nav-label'>PREV</span></a>";
			var next_button  = "<a href='#next' id='catablog-lightbox-next' class='catablog-nav' title='"+next_tip+"'><span class='catablog-lightbox-nav-label'>NEXT</span></a>";
			var close_button = "<a href='#close' id='catablog-lightbox-close' class='catablog-nav' title='Close LightBox Now'>CLOSE</a>";
			
			var meta = {};
			
			meta.title       = row.find('.catablog-title').html();
			meta.description = row.find('.catablog-description').html();
			meta.buynow      = "";
			meta.close       = close_button;
			
			meta.nav   = "";
			if (jQuery('.catablog-selected').prev('.catablog-image').size() > 0) {
				meta.nav += prev_button;
			}
			if (jQuery('.catablog-selected').next('.catablog-image').size() > 0) {
				meta.nav += next_button;
			}
			
			
			return meta;
		}
		
		
		
		
		
		/******************************
		**   SUPPORT METHODS
		******************************/
		function supportPositionFixed() {
			var isSupported = null;
			if (document.createElement) {
				var el = document.createElement('div');
				if (el && el.style) {
					el.style.position = 'fixed';
					el.style.top = '10px';
					var root = document.body;
					if (root && root.appendChild && root.removeChild) {
						root.appendChild(el);
						isSupported = (el.offsetTop === 10);
						root.removeChild(el);
					}
				}
			}
			return isSupported;
		}
		
		function bindNavigationControls() {
			
			// bind next and previous buttons
			jQuery('#catablog-lightbox-prev').bind('click', function(event) {
				navigate_lightbox('prev');
				return false;
			});

			jQuery('#catablog-lightbox-next').bind('click', function(event) {
				navigate_lightbox('next');
				return false;
			});
			
			
			// bind close button
			jQuery('#catablog-lightbox-close').bind('click', function(event) {
				close_lightbox();
				return false;
			});
			jQuery('#catablog-lightbox-close').bind('mouseenter', function(event) {
				jQuery(this).addClass('catablog-lightbox-close-hover');
				return false;
			});
			jQuery('#catablog-lightbox-close').bind('mouseleave', function(event) {
				jQuery(this).removeClass('catablog-lightbox-close-hover');
				return false;
			});
			jQuery(document).bind('mousemove', function(event) {
				var close_button = jQuery('#catablog-lightbox-close');
				
				if (close_button.is(':hidden')) {
					close_button.css('zIndex', 10800);
					close_button.fadeIn(50);
				}
				else {
					hideCloseButtonTimer(close_button);
				}
				
				
				
			});
			
			// bind keyboard shortcuts
			jQuery(document).bind('keyup', function(event) {
				var key_code = (event.keyCode ? event.keyCode : event.which);
				
				var forward_keycodes = [39, 78];
				var back_keycodes    = [37, 80];
				var escape_keycodes  = [27];
				
				if (in_array(key_code, forward_keycodes)) {
					jQuery('#catablog-lightbox-next').click();
				}
				if (in_array(key_code, back_keycodes)) {
					jQuery('#catablog-lightbox-prev').click();
				}
				if (in_array(key_code, escape_keycodes)) {
					close_lightbox();
				}
			});
		}
		
		function unbindNavigationControls() {
			jQuery('#catablog-lightbox-prev').unbind('click');
			jQuery('#catablog-lightbox-next').unbind('click');
			jQuery('#catablog-lightbox-close').unbind('click');
			jQuery(document).unbind('mousemove');
			jQuery(document).unbind('keyup');
			
			jQuery('#catablog-lightbox-close').fadeOut(200);
		}
		
		function hideCloseButtonTimer(obj) {
			clearTimeout(timeout);
			timeout = setTimeout(function() {
				if (obj.hasClass('catablog-lightbox-close-hover')) {
					hideCloseButtonTimer(obj);
				}
				else {
					obj.fadeOut(200);							
				}
			}, 1500);
		}
		
		function in_array (needle, haystack, argStrict) {
		    var key = '', strict = !!argStrict;

		    if (strict) {
		        for (key in haystack) {
		            if (haystack[key] === needle) {
		                return true;
		            }
		        }
		    } else {
		        for (key in haystack) {
		            if (haystack[key] == needle) {
		                return true;
		            }
		        }
		    }

		    return false;
		}
		
		
		
		
		return this;
	};
});



