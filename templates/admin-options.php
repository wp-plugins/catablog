<div class="wrap">
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Options</h2>
	<br />
		
	<form id="catablog-options" method="post" action="">
		
		<fieldset>
			<div id='demo_box' class='demo_box' style='width:<?php echo $thumbnail_size ?>px; height:<?php echo $thumbnail_size ?>px;'>&nbsp;</div>
			
			<label for='image_size'>Image Size:</label>
			<input type='text' name='image_size' id='image_size' size='5' value='<?php echo $thumbnail_size ?>' />
			<span>pixels</span><br />
		
			<small id="image_size_error" class="error hidden">your image size must be a positive integer<br /></small>
			<small>this will change the display size of all images, images you uploaded previously may look pixelated due to poor resolution.</small>
		</fieldset>
		
		<fieldset>
			<label for="background_color">Thumbnail Background Color:</label>
			<input type='hidden' name='bg_color' id='bg_color' value='<?php echo $background_color ?>' />
			<div id="picker"></div>
		</fieldset>
		
		<input type="hidden" name="save" id="save" value="yes" />
		
		<p class="submit">
			<input type="submit" id="save_changes" class="button-primary" value="<?php _e('Save Changes') ?>" />
			<span> or <a href="<?php echo get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog-edit' ?>">back to list</a></span>
		</p>
	</form>
	
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			
			$('#bg_color').jPicker({
				window: {position: {x:'right', y:'bottom'}},
				images: {clientPath:"<?php echo get_bloginfo('wpurl') ?>/wp-content/plugins/catablog/images/jPicker/"}
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
</div>