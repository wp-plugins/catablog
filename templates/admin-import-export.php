<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Import/Export</h2>
	
	<form id="catablog-import-export" class="catablog-form" method="post" action="admin.php?page=catablog-import" enctype="multipart/form-data">
		<fieldset>
			<?php $function_exists = true;//function_exists('simplexml_load_file') ?>
			<?php $disabled = ($function_exists)? '' : 'disabled="disabled"'?>
			
				<legend>DataBase Import</legend>

				<div id="import-controls" class="controls">
				
					<p><input type="file" name="catablog_data" id="catablog_data" <?php echo $disabled ?> /></p><br />
					
					<p style="margin-bottom:5px;">&nbsp;
						<input type="checkbox" name="catablog_clear_db" id="catablog_clear_db" value="true" <?php echo $disabled ?> />
						<label for="catablog_clear_db">Replace All Data:</label>
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
			<legend>DataBase Export</legend>
			
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
		
		<fieldset>
			<legend>Upload Folder Permissions</legend>
			
			<div class="controls">
				<?php $permissions = substr(sprintf('%o', fileperms($this->directories['uploads'])), -4) ?>
				<?php if ($permissions == '0777'): ?>
					<p>CataBlog Upload Folder is <strong>Unlocked</strong></p>
				<?php elseif ($permissions == '0755'): ?>
					<p>CataBlog Upload Folder is <strong>Locked</strong></p>
				<?php else: ?>
					<p>Error: You may be on a windows server...</p>
				<?php endif ?>
				<br />
				<a href="admin.php?page=catablog-lock-folders" class="button">Lock Folders</a>
				<a href="admin.php?page=catablog-unlock-folders" class="button">Unlock Folders</a>				
			</div>
			<div class="advice">
				<p><small>
					You may lock and unlock your <em>catablog</em> folders with 
					these controls. The idea is to unlock the folders, use your FTP client to 
					upload your original files and then lock the folders to protect them from hackers.
					After unlocking your directories please upload the original files directly
					into the <strong>wp-content/uploads/catablog/originals/</strong> folder without replacing it.
					<strong>Do not replace any of the CataBlog created folders</strong>.
					You should then regenerate all your thumbnail and lightbox pictures below.
					These controls may not work on a Windows server, it depends on your
					servers PHP settings and if the chmod command is supported. 
				</small></p>
			</div>
			
		</fieldset>
		
		<fieldset>
			<legend>Regenerate Images</legend>
			
			<div class="controls">
				<br />
				<a href="admin.php?page=catablog-regenerate-images" class="button">Regenerate Now</a>
			</div>
			<div class="advice">
				<p><small>
					Click the <em>Regenerate Now</em> button to recreate all the
					thumbnail and lightbox images that CataBlog has generated over
					the time you have used it. This is also useful when restoring exported
					data from another version of CataBlog. after you have uploaded your
					original images you must regenerate your images so they display properly.
				</small></p>
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