<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Images Rendering</h2>
	
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
		
	</ul>
	
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		
		
		var catablog_items = [<?php echo implode(', ', $item_ids) ?>];
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
					// window.location = "admin.php?page=catablog";
				}
			});
		}
		
		renderCataBlogItem(catablog_items.shift());
		
	});
</script>