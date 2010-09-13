<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Import/Export</h2>
	
	<form id="catablog-import-export" class="catablog-form" method="post" action="admin.php?page=catablog-import">
		<fieldset>
			<legend>Import</legend>
			<div id="import-controls" class="controls">
				<p><input type="file" name="catablog_data" disabled="disabled" id="catablog_data" /></p>
				<input type="button" class="button-disabled" disabled="disabled" value="Load XML BackUp File" />
			</div>
			<div id="import-advice" class="advice">
				<small>
					<strong>Coming Soon.</strong> This feature will be released in the next release, which is 
					coming very soon. For now you may backup your existing database, knowing you may safely store
					your CataBlog database elsewhere.
				</small>
			</div>
			
			
			<?php /*
			<div id="import-controls" class="controls">
				<p><input type="file" name="catablog_data" id="catablog_data" /></p>
				<input type="submit" class="button" value="Load XML BackUp File" />
			</div>
			<div id="import-advice" class="advice">
				<small>
					This feature is unlikely to work with images intact, due to server upload limits and
					PHP's default configuration. You may change this, but I recommend that you manually
					backup the catablog directory in your wp-content folder. You can easily copy the folder
					back into your wp-content directory at anytime with a FTP client. Then all you need to 
					do is import the XML file to populate the database with your backup.
				</small>
			</div>
			*/ ?>
			
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
					itself and the photos are not included in this backup. To backup your photos,
					simple copy the <em>catablog</em> directory in your wp-content folder to secure
					your photos.
				</small>
			</div>
		</fieldset>
	</form>
</div>