<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2><?php _e('Add New CataBlog Entry') ?></h2>
		
	<form id="catablog-create" class="catablog-form clear_float" method="post" action="admin.php?page=catablog-create" enctype="multipart/form-data">
		
		<h3>
			<strong>Upload An Image To Create A New Catalog Item</strong>
		</h3>
		<div>
			<input type="file" id="new_image" name="new_image"  />
			<span class="nonessential"> | </span>

			<?php wp_nonce_field( 'catablog_create', '_catablog_create_nonce', false, true ) ?>
			<input type="submit" name="save" value="Submit" class="button-primary" />
			
			<p>Maximum upload file size: <?php echo ini_get('upload_max_filesize') ?>B</p>
			
			<p><small>
				Select an image on your computer to upload and then use to create a new catalog item.<br />
				You	may upload JPEG, GIF and PNG graphic formats only.<br />
				<strong>No animated GIFs please.</strong><br />
			</small></p>
		</div>
	</form>
</div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		
	});
</script>