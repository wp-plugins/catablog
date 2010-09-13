(function($) {
	$.fn.catablogLightbox = function(config) {
		// PlugIn Variables
		var size     = this.size();
		var settings = {'size': size};
		if (config) $.extend(settings, config);
		
		
		
		// PlugIn Construction applied across each selected jQuery object
		this.each(function(i) {
			
			$(this).bind('click', function(event) {
				$('img.catablog_selected').removeClass('catablog_selected');
				$(this).addClass('catablog_selected');
				
				open_lightbox(this);
			});
			
			
		});
		
		
		
		
		
		
		
		
		// Private Functions
		function open_lightbox(obj) {
			var curtain_density = 0.85;
			var fadein_speed    = 0;
			var page_top        = jQuery(document).scrollTop() + 30;
			
			// add the curtain div into the DOM
			jQuery('body').append("<div id='catablog_curtain'>&nbsp;</div>");
			var curtain = jQuery('#catablog_curtain');
			
			// alert(curtain.css);

			// bind the curtain click and fade the curtain into view
			curtain.bind('click', function() {
				close_lightbox();
			});
			curtain.css('opacity', curtain_density);
			
			if (supportPositionFixed() != true) {
				var window_height   = jQuery(window).height();
				var document_height = jQuery(document).height();
				curtain.css('position', 'absolute');
				curtain.height(document_height);
			}


			// add the lightbox div into the DOM
			jQuery('body').append("<div id='catablog_lightbox'><div id='catablog_whiteboard'></div></div>");
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
				var meta = calculateMeta(obj);
				expand_lightbox(this, meta);
			}
			
			fullsize_pic.src = obj.src.replace("/catablog/thumbnails", "/catablog/fullsize");
			
		}
		
		
		function expand_lightbox(img, meta) {
			var lightbox = jQuery('#catablog_lightbox div');
			var speed    = 600;
			var w = img.width;
			var h = img.height;
			var s = img.src;

			lightbox.animate({width:w, height:h}, speed, function() {
				jQuery(this).append("<img src='"+s+"' />");
				// jQuery(this).css('backgroundImage', 'url()');
				jQuery(this).children('img').fadeIn(800);

				jQuery(this).animate({height:h}, speed, function() {
					
					var title = "<p class='catablog_lightbox_title'>" + meta.title + "<br />" + meta.buynow + "</p>";
					var number = "<small class='catablog_lightbox_page'>" + "</small>";
					var navigation = "<small class='catablog_lightbox_nav'>" + meta.nav + "</small>";
					var description = "<small class='catablog_lightbox_desc'>" + meta.description + "</small>";
					
					jQuery(this).append("<div id='catablog_lightbox_meta'>" + title + navigation + description +  "</div>");
					
					var h2 = h + jQuery('#catablog_lightbox_meta').outerHeight() + 10;
					// console.log(h + " : " + h2);
					jQuery(this).animate({height:h2}, 500, function() {
						
						
						jQuery(this).children('#catablog_lightbox_meta').fadeIn(800);
						if (supportPositionFixed() != true) {
							jQuery('#catablog_curtain').height(jQuery(document).height());
						}
						
						
						
						/************
						**  Lightbox Event Bindings
						************/
						jQuery('#catablog_lightbox_meta').bind('click', function(event) {
							event.stopPropagation();
						});

						jQuery('#catablog_lightbox_prev').bind('click', function(event) {
							var selected = jQuery('img.catablog_selected');
							var prev_row = jQuery('img.catablog_selected').parent().prev('.catablog_row');
							
							if (prev_row.size() > 0) {
								var new_thumbnail = prev_row.children('img.catablog_image');
								
								selected.removeClass('catablog_selected');
								new_thumbnail.addClass('catablog_selected');
								
								change_lightbox(new_thumbnail);
							}
							else {
								
							}
						});

						jQuery('#catablog_lightbox_next').bind('click', function(event) {
							var selected = jQuery('img.catablog_selected');
							var next_row = jQuery('img.catablog_selected').parent().next('.catablog_row');
							
							if (next_row.size() > 0) {
								var new_thumbnail = next_row.children('img.catablog_image');
								
								selected.removeClass('catablog_selected');
								new_thumbnail.addClass('catablog_selected');
								
								change_lightbox(next_row.children('img.catablog_image'));
							}
							else {
								
							}
						});
						
					});
				});

			});
		}
		
		function close_lightbox() {
			var fadeout_speed = 300;

			jQuery('#catablog_curtain').fadeOut(fadeout_speed, function() {
				jQuery(this).remove();
			});
			jQuery('#catablog_lightbox').fadeOut(fadeout_speed, function() {
				jQuery(this).remove();
			});
			jQuery('img.catablog_selected').removeClass('catablog_selected');
		}
		
		
		function change_lightbox(img) {
			var lightBox = jQuery('#catablog_lightbox > div');
			
			lightBox.children().fadeOut(500, function() {
				jQuery(this).remove();
			});
			
			var t = setTimeout(function() {
				//lightBox.animate({width:100, height:100}, 500, function() {
						// load the full size picture and expand the lightbox to fit the images dimensions
					var fullsize_pic = new Image();
					fullsize_pic.onload = function() {
						var prev_button  = "<a href='#prev' id='catablog_lightbox_prev'>prev</a>";
						var next_button  = "<a href='#next' id='catablog_lightbox_next'>next</a>";
						
						var meta = calculateMeta(img);
						expand_lightbox(this, meta);
					};
					
				  fullsize_pic.src = jQuery(img).attr('src').replace("/catablog/thumbnails", "/catablog/fullsize");
				  
			  //});
			}, 700);
			
			
		}
		
		
		
		function calculateMeta(obj) {
			var prev_button  = "<a href='#prev' id='catablog_lightbox_prev'>prev</a>";
			var next_button  = "<a href='#next' id='catablog_lightbox_next'>next</a>";
			
			var meta = {};
			
			meta.title       = jQuery(obj).siblings('.catablog_title').html();
			meta.description = jQuery(obj).siblings('.catablog_description').html();
			meta.buynow = "";
			meta.nav    = prev_button + " | " + next_button;
			
			return meta;
		}
		
		
		
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


		
		
		
		
		
		
		
		
		return this;
	};
})(jQuery);



