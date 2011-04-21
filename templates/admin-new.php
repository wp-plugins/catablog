<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2><?php _e("Add New CataBlog Entry", "catablog") ?></h2>
		
	<form id="catablog-create" class="catablog-form clear_float" method="post" action="admin.php?page=catablog-create" enctype="multipart/form-data">
		
		<h3>
			<strong><?php _e("Upload An Image To Create A New Catalog Item", "catablog"); ?></strong>
		</h3>
		<div>
			<input type="file" id="new_image" name="new_image"  />
			<span class="nonessential"> | </span>

			<?php wp_nonce_field( 'catablog_create', '_catablog_create_nonce', false, true ) ?>
			<input type="submit" name="save" value="<?php _e("Upload", "catablog") ?>" class="button-primary" />
			
			<p><?php printf(__("Maximum upload file size: %sB", "catablog"), ini_get('upload_max_filesize')); ?></p>
			
			<p><small>
				<?php _e("Select an image on your computer to upload and then use to create a new catalog item.", "catablog"); ?><br />
				<?php _e("You may upload JPEG, GIF and PNG graphic formats only.", "catablog"); ?><br />
				<strong><?php _e("No animated GIFs please.", "catablog"); ?></strong>
			</small></p>
		</div>
	</form>
</div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		
	});
</script>