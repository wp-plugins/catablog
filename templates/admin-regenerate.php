<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2><?php _e("CataBlog Images Rendering", "catablog"); ?></h2>
	
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
		
	</ul>
	
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var images  = ["<?php echo implode('", "', $image_names) ?>"];
		var nonce   = '<?php echo wp_create_nonce("catablog-render-images") ?>';
		var message = '<?php _e("Image rendering is now complete, you may now go to any other admin panel you may want.", "catablog"); ?>';
		
		renderCataBlogItems(images, 'thumbnail', nonce, function() {
			<?php if ($this->options['lightbox-enabled']): ?>
				var images = ["<?php echo implode('", "', $image_names) ?>"];
				renderCataBlogItems(images, 'fullsize', nonce, function() {
					jQuery('#catablog-console').append('<li class="updated">'+message+'</li>');
				});
			<?php else: ?>	
				jQuery('#catablog-console').append('<li class="updated">'+message+'</li>');
			<?php endif ?>
		});
		
	});
</script>