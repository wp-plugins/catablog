jQuery(function($) {
	jQuery.fn.catablogLightbox = function(config) {
		// PlugIn Variables
		var size     = this.size();
		var settings = {'size': size};
		if (config) jQuery.extend(settings, config);
		
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
					row = jQuery(this).parent().get(0);
				}
				
				// select the current row and open the lightbox
				jQuery(row).addClass('catablog-selected');
				open_lightbox(row);
				
				// do not register the click
				return false;
			});
		});
		
		
		
		
		
		
		
		
		// Private Functions
		function open_lightbox(row) {
			// get the image inside the row
			
			var img = jQuery(row).find('img.catablog-image').get(0);
			
			var support_fixed   = supportPositionFixed();
			var curtain_density = 0.85;
			var fadein_speed    = 0;
			var page_top        = jQuery(document).scrollTop() + 30;
			
			// add the curtain div into the DOM
			jQuery('body').append("<div id='catablog-curtain'>&nbsp;</div>");
			var curtain = jQuery('#catablog-curtain');
			
			// alert(curtain.css);

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
			
			
			if (!support_fixed) {
				lightbox.css('top', page_top);	
			}
			
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
			
			
			var title = "<h4 class='catablog-lightbox-title'>" + meta.title + "</h4>";
			var description = "<p class='catablog-lightbox-desc'>" + meta.description + "</p>";
			var nav =  meta.nav;
			
			// attach image and navigation
			jQuery(lightbox).append("<div id='catablog-lightbox-image' />");
			if (!jQuery('#catablog-lightbox-image').append("<img src='"+s+"' />")) {
				alert('fail appending')
			};
			jQuery('#catablog-lightbox-image').append(nav);
			jQuery('#catablog-lightbox-image a').height(h);
			
			// attach meta data below image
			jQuery(lightbox).append("<div id='catablog-lightbox-meta' />");
			jQuery('#catablog-lightbox-meta').append(title);
			jQuery('#catablog-lightbox-meta').append(description);
			
			
			
			
			lightbox.animate({width:w, height:h}, 400, function() {
				var full_height = h + jQuery('#catablog-lightbox-meta').outerHeight();
				
				jQuery(this).children('#catablog-lightbox-meta').show();
				jQuery(this).animate({height:full_height}, 400, function() {
					hold_click = false;
					listenForKeyStroke();
				})
				
				jQuery('#catablog-lightbox-image').fadeIn(400, function() {
					
				});
				
				
				
				/************
				**  Bind next and previous photo buttons
				************/
				jQuery('#catablog-lightbox-prev').bind('click', function(event) {
					navigate_lightbox('prev');
					return false;
				});

				jQuery('#catablog-lightbox-next').bind('click', function(event) {
					navigate_lightbox('next');
					return false;
				});
				


			});
		}
		
		
		function change_lightbox(img) {
			var row   = jQuery(img).parent().get(0);
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
				
				// alert(img.src.replace("/catablog/thumbnails", "/catablog/fullsize"));
				fullsize_pic.src = img.src.replace("/catablog/thumbnails", "/catablog/fullsize");
				
				
			});			
		}
		
		
		function navigate_lightbox(direction) {
			if (hold_click) {
				return false;
			}
			
			hold_click = true;
			unlistenForKeyStroke();
			
			
			var selected = jQuery('.catablog-selected');
			var new_row  = null;
			
			if (direction == 'next') {
				new_row = selected.next('.catablog-row');
				if (new_row.size() < 1) {
					new_row = jQuery('.catablog-row:first');
				}
			}
			else if (direction == 'prev') {
				new_row = selected.prev('.catablog-row');
				if (new_row.size() < 1) {
					new_row = jQuery('.catablog-row:last');
				}
			}
			
			new_thumbnail = new_row.find('.catablog-image').get(0);
			
			selected.removeClass('catablog-selected');
			new_row.addClass('catablog-selected');
			
			change_lightbox(new_thumbnail);
		}
		
		
		function close_lightbox() {
			unlistenForKeyStroke();
			
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
			var prev_button  = "<a href='#prev' id='catablog-lightbox-prev'><span class='catablog-lightbox-nav-label'>PREV</span></a>";
			var next_button  = "<a href='#next' id='catablog-lightbox-next'><span class='catablog-lightbox-nav-label'>NEXT</span></a>";
			
			var meta = {};
			
			meta.title       = row.find('.catablog-title').html();
			meta.description = row.find('.catablog-description').html();
			meta.buynow = "";
			
			meta.nav   = "";
			if (row.prev('.catablog-row').size() > 0) {
				meta.nav += prev_button;
			}
			if (row.next('.catablog-row').size() > 0) {
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
		
		function listenForKeyStroke() {
			jQuery(document).bind('keyup', function(event) {
				var key_code = (event.keyCode ? event.keyCode : event.which);
				if (key_code == 39) {
					// forward arrow pressed
					jQuery('#catablog-lightbox-next').click();
				}
				if (key_code == 37) {
					// backwards arrow pressed
					jQuery('#catablog-lightbox-prev').click();
				}
			});
		}
		
		function unlistenForKeyStroke() {
			jQuery(document).unbind('keyup');
		}
		
		
		
		
		
		
		
		return this;
	};
});



