<div class="wrap">
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Options</h2>
	
	<form id="catablog-options" class="catablog-form" method="post" action="">
		
		<fieldset>
			<legend>Thumbnails</legend>
			
			<div id="thumbnail_settings">
				<p>
					<label for='thumbnail_size'>Thumbnail Size:</label>
					<input type='text' name='thumbnail_size' id='thumbnail_size' class='arrow_edit' size='5' value='<?php echo $thumbnail_size ?>' />
					<span>pixels</span><br />
				
					<small class="error hidden">your thumbnail size must be a positive integer<br /></small>
					<small>this will change the display size of all images, images you uploaded previously may look pixelated due to poor resolution.</small>
				</p>
				<p>
					<?php $checked = ($keep_aspect_ratio)? "checked='checked'" : "" ?>
					<label for="keep_aspect_ratio">Keep Aspect Ratio:</label>
					<input type="checkbox" name="keep_aspect_ratio" id="keep_aspect_ratio" <?php echo $checked ?> />
					<br />
					<small>this will keep the aspect ratio of the original image in your thumbnails, using the background color to fill in the empty space.</small>
				</p>
				<p>
					<label>Thumbnail Background Color:</label>
					<input type="text" name="bg_color" id="bg_color" size="7" maxlength="6" value="<?php echo $background_color ?>" />
					<span id="red"></span>
					<span id="green"></span>
					<span id="blue"></span>
				</p>
			</div>
			
			<div id="thumbnail_preview">
				<div id='demo_box' class='demo_box' style='width:<?php echo $thumbnail_size ?>px; height:<?php echo $thumbnail_size ?>px;'>&nbsp;</div>
			</div>
			
		</fieldset>
		
		<fieldset>
			<legend>Full Size LightBox</legend>
			
			<p>
				<?php $checked = ($lightbox_enabled)? "checked='checked'" : "" ?>
				<label for="lightbox_enabled">Enable LightBox Feature:</label>
				<input type="checkbox" name="lightbox_enabled" id="lightbox_enabled" <?php echo $checked ?> /><br/>
				<small>this will allow people to enlarge an image thumbnail with a lightbox style javascript effect.</small>
			</p>
			
			<p>
				<label for='lightbox_image_size'>LightBox Size:</label>
				<input type='text' name='lightbox_image_size' id='lightbox_image_size' class='arrow_edit' size='5' value='<?php echo $lightbox_size ?>' />
				<span>pixels</span><br />
				<small class="error hidden">your lightbox size must be a positive integer<br /></small>
				<small>This is the maximum length of either the height or width, depending on whichever is longer in the original uploaded image.</small>
			</p>
		</fieldset>
		
		<fieldset>
			<legend>Link Settings</legend>
			
			<label for="link_target">Link Target:</label>
			<select id="link_target" name="link_target">
				<option value="_blank" <?php echo ($link_target == "_blank")? 'selected="selected"' : '' ?>>_blank</option>
				<option value="_top" <?php echo ($link_target == "_top")? 'selected="selected"' : '' ?>>_top</option>
				<option value="" <?php echo ($link_target == '')? 'selected="selected"' : '' ?>>_none</option>
			</select>
			
			<p><small>
				The link target setting will set the target attribute of all the catalog links.
			</small></p>
		</fieldset>
		
		<fieldset>
			<legend>PayPal Settings</legend>
			
			<label for="paypal_email">Account Email Address:</label>
			<input type="text" name="paypal_email" id="paypal_email" size="50" value="<?php echo $paypal_email ?>" />
			
			<p><small>
				Enter in an email address here that has been registered with <a href="http://www.paypal.com">PayPal</a> and
				you will enable the <strong>Store Front</strong> mode of CataBlog. This will allow you to give an item a price and product code.
				When an item has a price above zero a "Add to Cart" button appear under the description of that CataBlog item.
			</small></p>
		</fieldset>
		
		<?php wp_nonce_field( 'catablog_options', '_catablog_options_nonce', false, true ) ?>
		<input type="hidden" name="save" id="save" value="yes" />		
		<p class="submit">
			<input type="submit" id="save_changes" class="button-primary" value="<?php _e('Save Changes') ?>" />
			<span> or <a href="<?php echo get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog' ?>">back to list</a></span>
		</p>
	</form>
	
	<?php //print_r($this->options)?>
	
</div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		
		function refreshSwatch() {
			var red = $("#red").slider("value")
				,green = $("#green").slider("value")
				,blue = $("#blue").slider("value")
				,hex = hexFromRGB(red, green, blue);
			$("#demo_box").css("background-color", "#" + hex);
			$('#bg_color').val(hex);
		}
		
		
		function setSliders(hex) {
			$("#red").slider("value", HexToR(hex));
			$("#green").slider("value", HexToG(hex));
			$("#blue").slider("value", HexToB(hex));
			refreshSwatch();
		}
		
		
		
		$("#red, #green, #blue").slider({
			orientation: 'horizontal',
			range: "min",
			max: 255,
			value: 127,
			slide: refreshSwatch
		});
		setSliders('<?php echo $background_color ?>')
		
		
		
		$('#bg_color').bind('blur', function(event) {
			if (this.value.length != 6) {
				alert('Please make sure to enter a full 6 character hexadecimal color code.')
				this.value = "000000";
			}
			setSliders(this.value)
		});
		
		
		
		var size = <?php echo get_option('catablog_image_size') ?> - 1;
		$('#demo_box').css({width:size, height:size});
		
		
		
		$('input.arrow_edit').bind('keydown', function(event) {
			var step = 5;
			var keycode = event.keyCode;
			
			if (keycode == 40) { this.value = parseInt(this.value) - step; }
			if (keycode == 38) { this.value = parseInt(this.value) + step; }
		});
		
		
		
		$('input.arrow_edit').bind('keyup', function(event) {
			var v = this.value;
			if (is_integer(v) && (v > 0)) {
				$(this).siblings('small.error').hide();
				if ($(this).attr('id') == 'thumbnail_size') {
					resize_box(v);
				}
			}
			else {
				$(this).siblings('small.error').show();
			}
			
			possibly_disable_save_button();
		});
		
		function possibly_disable_save_button() {
			if ($('small.error:visible').size() == 0) {
				$('#save_changes').attr('disabled', false);
				$('#save_changes').attr('class', 'button-primary');
			}
			else {
				$('#save_changes').attr('disabled', true);
				$('#save_changes').attr('class', 'button-disabled');
			}
		}
		
		
		var lightbox_button   = $('#lightbox_enabled');
		var lightbox_fieldset = lightbox_button.parent().parent();
		
		if (lightbox_button.attr('checked') == false) {
			$('input.arrow_edit', lightbox_fieldset).attr('readonly', true);
			lightbox_fieldset.addClass('disabled');
		}
		
		lightbox_button.bind('click', function(event) {
			if (this.checked) {
				// enable lightbox
				if (!confirm('Enabling the lightbox feature may take some time because it must generate full size versions for all your pictures. Are you sure you want to do this?')) {
					$(this).attr('checked', false);
					return false;
				}
				
				show_load();
				
				lightbox_fieldset.removeClass('disabled');
				$('input.arrow_edit', lightbox_fieldset).attr('readonly', false);
				
				var params = { 'action':'catablog_render_fullsize', 'security':'<?php echo wp_create_nonce("catablog-render-fullsize") ?>' }
				$.post(ajaxurl, params, function(data) {
					hide_load();
				});
			}
			else {
				// disable lightbox
				if (!confirm('Disabling the lightbox feature will also clear your full size images directory. Are you sure you want to do this?')) {
					$(this).attr('checked', true);
					return false;
				}
				
				show_load();
				
				lightbox_fieldset.addClass('disabled');
				$('input.arrow_edit', lightbox_fieldset).attr('readonly', true);
				
				var params = { 'action':'catablog_flush_fullsize', 'security':'<?php echo wp_create_nonce("catablog-flush-fullsize") ?>' }
				$.post(ajaxurl, params, function(data) {
					hide_load();
				});
			}
		});
	});
	
	function is_integer(s) {
		return (s.toString().search(/^[0-9]+$/) == 0);
	}
	
	function resize_box(num) {
		var speed = 100;
		jQuery('#demo_box').animate({width:(num-1), height:(num-1)}, speed);
	}
	
	
	
</script>
