<div class="wrap">
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Options</h2>
	
	<form id="catablog-options" method="post" action="">
		
		<fieldset>
			<legend>Thumbnails</legend>
			
			<div id="thumbnail_settings">
				<p>
					<label for='image_size'>Thumbnail Size:</label>
					<input type='text' name='image_size' id='image_size' size='5' value='<?php echo $thumbnail_size ?>' />
					<span>pixels</span><br />
				
					<small id="image_size_error" class="error hidden">your thumbnail size must be a positive integer<br /></small>
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

				<?php /*<a href="#reset" class="button" id="button-recalc-thumbs">Recalculate All Thumbnails</a> */ ?>
			</div>
			
			<div id="thumbnail_preview">
				<div id='demo_box' class='demo_box' style='width:<?php echo $thumbnail_size ?>px; height:<?php echo $thumbnail_size ?>px;'>&nbsp;</div>
			</div>
			
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
		
		<input type="hidden" name="save" id="save" value="yes" />		
		<p class="submit">
			<input type="submit" id="save_changes" class="button-primary" value="<?php _e('Save Changes') ?>" />
			<span> or <a href="<?php echo get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog-edit' ?>">back to list</a></span>
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
		
		
		
		$('#image_size').bind('keydown', function(event) {
			var step = 5;
			var keycode = event.keyCode;
			
			if (keycode == 40) { this.value = parseInt(this.value) - step; }
			if (keycode == 38) { this.value = parseInt(this.value) + step; }
		});
		
		
		
		$('#image_size').bind('keyup', function(event) {
			var v = this.value;
			if (is_integer(v)) {
				$('#save_changes').attr('disabled', false);
				$('#save_changes').attr('class', 'button-primary');
				$('#image_size_error').hide();
			
				resize_box(v);
			}
			else {
				$('#save_changes').attr('disabled', true);
				$('#save_changes').attr('class', '');
				$('#image_size_error').show();
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
