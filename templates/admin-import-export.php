<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Import/Export</h2>
	
	<form id="catablog-import-export" class="catablog-form" method="post" action="admin.php?page=catablog-import" enctype="multipart/form-data">
		<fieldset>
			<?php $function_exists = true;//function_exists('simplexml_load_file') ?>
			<?php $disabled = ($function_exists)? '' : 'disabled="disabled"'?>
			
				<legend>Import</legend>

				<div id="import-controls" class="controls">
				
					<p><input type="file" name="catablog_data" id="catablog_data" <?php echo $disabled ?> /></p>
				
					<p>
						<label for="catablog_clear_db">Replace All Data:</label>
						<input type="checkbox" name="catablog_clear_db" id="catablog_clear_db" value="true" <?php echo $disabled ?> />
					</p>
				
					<input type="submit" class="button" value="Load XML BackUp File" <?php echo $disabled ?> />
				</div>
				<div id="import-advice" class="advice">
					<?php if ($function_exists): ?>
						<p><small>
							To import data previously saved from CataBlog simple select the XML file 
							you downloaded on your hard drive and click the "Load XML BackUp File"
							button. You may choose to completely erase all your data before importing
							by checking the "Replace All Data" checkbox. Keep in mind, this 
							<strong>does not import images</strong>, to do that simply replace
							the <em>catablog</em> directory in <em>wp-content</em>. Once you load the
							XML file and replace the <em>catablog</em> directory everything should be back
							to the way it was before.
						</small></p>
					<?php else: ?>
						<p class="error"><small>
							You must have the Simple XML library installed in your server's version of PHP
							for XML import to work. Please contact your server administrator for more information 
							regarding this error.
						</small></p>
					<?php endif ?>
				</div>
		</fieldset>
		
		<fieldset>
			<legend>Export</legend>
			
			<div id="export-controls" class="controls">
				<br />
				<a href="admin.php?page=catablog-export" class="button">Save XML BackUp File</a>
			</div>
			<div id="export-advice" class="advice">
				<small>
					You may export your CataBlog data to a XML file which may be used to backup 
					and protect your work. The XML file is a simple transfer of the database table
					itself and the <strong>images are not included in this backup</strong>. 
					To backup your images, simple copy the <em>catablog</em> directory in 
					<em>wp-content</em> to a secure location.
				</small>
			</div>
		</fieldset>
	</form>
</div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#catablog-import-export').bind('submit', function(event) {
			if ($('#catablog_clear_db').attr('checked') == true) {
				if (!confirm('Are you sure you want to replace your CataBlog items with your newly imported data?')) {
					return false;
				}
			}
		});
	});
</script>