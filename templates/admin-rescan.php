<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2><?php _e("CataBlog Rescan Original Images Results", "catablog"); ?></h2>
	
	<h3><strong><?php _e("Rescan Console", "catablog"); ?></strong></h3>

	<?php if (count($new_rows['images']) < 1): ?>
		<p>
			<?php _e("No new images where found in your originals folders.", 'catablog'); ?> <br />
			<?php _e("Please make sure that you have successfully uploaded new images via	FTP before running this command.", "catablog"); ?><br />
			<?php _e("New images should be uploaded into the following folder:", "catablog"); ?><br />
			<code><?php echo $this->directories['originals'] ?></code>
			
		</p>
	<?php else: ?>
		<noscript>
			<div class="error">
				<strong><?php _e("You must have a JavaScript enabled browser to regenerate your images.", "catablog"); ?></strong>
			</div>
		</noscript>
		
		<div id="catablog-progress-thumbnail" class="catablog-progress">
			<div class="catablog-progress-bar"></div>
			<h3 class="catablog-progress-text"><?php _e("Processing Thumbnail Images...", "catablog"); ?></h3>
		</div>

		<?php if ($this->options['lightbox-enabled']): ?>
			<div id="catablog-progress-fullsize" class="catablog-progress">
				<div class="catablog-progress-bar"></div>
				<h3 class="catablog-progress-text"><?php _e("Waiting For Thumbnail Rendering To Finish...", "catablog"); ?></h3>
			</div>
		<?php endif ?>
		
		<ul id="catablog-console">
			<?php foreach ($new_rows['titles'] as $title): ?>
				<li class="message"><?php printf(__("New Image Found, creating catalog item ", "catablog"), "<strong>$title</strong>"); ?></li>
			<?php endforeach ?>
		</ul>
	<?php endif ?>
	
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		
		discourage_leaving_page('<?php _e("Please allow the rendering to complete before leaving this page. Click cancel to go back and let the rendering complete.", "catablog"); ?>');
		
		/****************************************
		** CALCULATE NEW IMAGES
		****************************************/
		var nonce   = '<?php echo wp_create_nonce("catablog-render-images") ?>';		
		var images  = ["<?php echo implode('", "', $new_rows['images']) ?>"];
		var message = '<?php _e("Image rendering is now complete", "catablog"); ?>';

		var thumbs = images.slice(0);
		renderCataBlogItems(thumbs, 'thumbnail', nonce, function() {
			jQuery('#catablog-progress-thumbnail .catablog-progress-text').html(message);
			
			<?php if ($this->options['lightbox-enabled']): ?>
				var fullsize = images.slice(0);
				renderCataBlogItems(fullsize, 'fullsize', nonce, function() {
					jQuery('#catablog-progress-fullsize .catablog-progress-text').html(message);
					var t = setTimeout(function() {
						jQuery('#catablog-progress-thumbnail').hide('medium');
						jQuery('#catablog-progress-fullsize').hide('medium');
						jQuery('#message').hide('medium');
					}, 2000);
					$('#save_changes').attr('disabled', false);
				});
			<?php else: ?>
				var t = setTimeout(function() {
					jQuery('#catablog-progress-thumbnail').hide('medium');
					jQuery('#message').hide('medium');
				}, 2000);
				$('#save_changes').attr('disabled', false);				
			<?php endif ?>
		});
	});
</script>