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
		
		<div id="catablog-progress">
			<div id="catablog-progress-bar"></div>
			<h3 id="catablog-progress-text">Processing...</h5>
		</div>
		
		<ul id="catablog-console">
			<?php foreach ($new_rows['titles'] as $title): ?>
				<li class="message">New Image Found, rendering images <strong><?php echo $title ?></strong></li>
			<?php endforeach ?>
		</ul>
	<?php endif ?>
	
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		
		var catablog_items = [<?php echo implode(', ', $new_rows['ids']) ?>];
		var total_count    = catablog_items.length;
		
		discourage_leaving_page();
		
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
		
	});
</script>