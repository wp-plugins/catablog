<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Rescan Original Images Results</h2>
	
	<h3><strong>Rescan Console</strong></h3>

	<?php if (count($new_rows['ids']) < 1): ?>
		<p>
			No new images where found in your originals folders. <br />
			Please make sure that you have successfully uploaded new images via	FTP before running this command.<br />
			New images should be uploaded into the following folder:<br />
			<code><?php echo $this->directories['originals'] ?></code>
			
		</p>
	<?php else: ?>
		<noscript>
			<div class="error">
				<strong>You must have a JavaScript enabled browser to regenerate your images.</strong>
			</div>
		</noscript>
		
		<div id="catablog-progress-thumbnail" class="catablog-progress">
			<div class="catablog-progress-bar"></div>
			<h3 class="catablog-progress-text">Processing Thumbnail Images...</h3>
		</div>

		<?php if ($this->options['lightbox-enabled']): ?>
			<div id="catablog-progress-fullsize" class="catablog-progress">
				<div class="catablog-progress-bar"></div>
				<h3 class="catablog-progress-text">Waiting For Thumbnail Rendering To Finish...</h3>
			</div>
		<?php endif ?>
		
		<ul id="catablog-console">
			<?php foreach ($new_rows['titles'] as $title): ?>
				<li class="message">New Image Found, creating catalog item <strong><?php echo $title ?></strong></li>
			<?php endforeach ?>
		</ul>
	<?php endif ?>
	
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		
		discourage_leaving_page();
		
		/****************************************
		** CALCULATE NEW IMAGES
		****************************************/

		var nonce   = '<?php echo wp_create_nonce("catablog-render-images") ?>';		
		var images  = ["<?php echo implode('", "', $new_rows['image']) ?>"];
		var message = "Image rendering is now complete";

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

		
		
		<?php /*
		var catablog_items = [<?php echo implode(', ', $new_rows['ids']) ?>];
		var total_count    = catablog_items.length;
		
		
		
		function renderCataBlogItem(id) {
			$('#catablog-progress-text').text('Processing ' + (total_count - catablog_items.length) + ' of ' + total_count + ' Items');
			
			var params = {
				'id':       id,
				'action':   'catablog_render_images',
				'security': '<?php echo wp_create_nonce("catablog-render-images") ?>'
			}
			
			$.post(ajaxurl, params, function(data) {
				try {
					data = eval(data);
					if (data.success == false) {
						$('#catablog-console').append('<li class="error">' + data.error + '</li>')
					}
					
				}
				catch(e) {
					$('#catablog-console').append('<li class="error">' + e + '</li>')
				}
				
				if (catablog_items.length > 0) {
					percent_complete = 100 - ((catablog_items.length / total_count) * 100);
					$('#catablog-progress-bar').css('width', percent_complete + '%');
					renderCataBlogItem(catablog_items.shift());
				}
				else {
					$('#catablog-progress-bar').css('width', '100%');
					$('#catablog-progress-text').text('Processing Complete');
					unbind_discourage_leaving_page();
				}
			});
		}
		
		renderCataBlogItem(catablog_items.shift());
		*/ ?>
	});
</script>