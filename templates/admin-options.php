<div class="wrap">
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Options</h2>
	<br />
		
	<form method="post" action="">
		
		<div id='demo_box' class='demo_box' style='width:200px; height:200px;'>&nbsp;</div>
		
		<label for='image_size'>Image Size:</label>
		<input type='text' name='image_size' id='image_size' size='5' value='<?php echo get_option('image_size') ?>' >
		<span>pixels</span><br />
		
		<small id="image_size_error" class="error hidden">your image size must be a positive integer<br /></small>
		<small>this will change the display size of all images, images you uploaded previously may look pixelated due to poor resolution.</small>
		
		
		<input type="hidden" name="save" id="save" value="yes" />
		
		<p class="submit">
			<input type="submit" id="save_changes" class="button-primary" value="<?php _e('Save Changes') ?>" />
			<span> or <a href="<?php echo get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog/lib/CataBlog.class.php' ?>">back to list</a></span>
		</p>
	</form>
	
	<script type="text/javascript">
		jQuery(document).ready(function() {
			var size = <?php echo get_option('image_size') ?> - 1;
			jQuery('#demo_box').css({width:size, height:size});
		});
		
		jQuery('#image_size').bind('keyup', function() {
			var v = this.value;
			if (is_integer(v)) {
				jQuery('#save_changes').attr('disabled', false);
				jQuery('#save_changes').attr('class', 'button-primary');
				jQuery('#image_size_error').hide();
				
				resize_box(v);
			}
			else {
				jQuery('#save_changes').attr('disabled', true);
				jQuery('#save_changes').attr('class', '');
				jQuery('#image_size_error').show();
			}
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