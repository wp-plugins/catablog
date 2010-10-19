<?php $new_item = (isset($_REQUEST['id']) === false) ?>
<div class="wrap">
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2><?php echo ($new_item)? 'Add New CataBlog Entry' : 'Edit CataBlog Entry' ?></h2>
	
	<form id="catablog-edit" class="catablog-form" method="post" action="<?php echo get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog-save' ?>" enctype="multipart/form-data">
		
		
		<div id="catablog-edit-main">
			<fieldset>
				<h3>Main</h3>
				<div>
					<div id="catablog-edit-main-image">
						<?php if ($new_item): ?>
							<p id="no-image-icon">No Image!</p>
							<label id="select-image-button"><input type="file" id="new_image" name="new_image" /></label>
							<p id="select-image-text"><small>
								Double click <em>Select Image</em> above to choose the 
								image you would like to upload for this item. You
								may upload JPEG, GIF and PNG graphic formats only.
								Every CataBlog item is required to have an image 
								and a title. 
								
							</small></p>
						<?php else: ?>
							<img src="<?php echo get_bloginfo('wpurl').'/wp-content/uploads/catablog/thumbnails/'.$result['image'] ?>" id="catablog-image-preview" />
							<input type="hidden" name="image" id="image" value="<?php echo $result['image'] ?>" />
							<label id="select-image-button"><input type="file" id="new_image" name="new_image" /></label>
								
							<p id="select-image-text"><small>
								Click <em>Select Image</em> above to choose a
								replacement image for your item. Again only
								JPEG, GIF and PNG formats are accepted.
							</small></p>
						<?php endif ?>
					</div>
					
					
					<div id="catablog-edit-main-text">
						<label for="catablog-title">Title</label>
						<input type="text" name="title" id="catablog-title" maxlength="200" value="<?php echo htmlspecialchars($result['title'], ENT_QUOTES, 'UTF-8') ?>" />
						<br /><br />
						<label for="catablog-description">Description [<small>excepts html formatting</small>]</label>
						<textarea name="description" id="catablog-description" cols="45" rows="20"><?php echo htmlspecialchars($result['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
					</div>
					
					<div id="catablog-edit-main-save">
						<input type="hidden" id="save" name="save" value="yes" />
						<?php wp_nonce_field( 'catablog_save', '_catablog_save_nonce', false, true ) ?>
						
						<?php if (!$new_item): ?>
							<input type="hidden" id="id" name="id" value="<?php echo $result['id']?>" />
							<input type="hidden" id="saved_image" name="saved_image" value="<?php echo $result['image'] ?>" />
						<?php endif ?>
							<?php $save_button_label = ($new_item)? 'Create CataBlog Item' : 'Save Changes' ?>
							<input type="submit" class="button-primary" value="<?php echo $save_button_label ?>" />
							<span> or <a href="<?php echo get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog' ?>">back to list</a></span>						
					</div>
				</div>



				
				
				
				
				
		
			</fieldset>
		</div>
		
		
		<div id="catablog-edit-params">
			<fieldset>
				<h3><label for="link">Link</label></h3>
				<div>
					<input type="text" name="link" id="link" class="text-field" value="<?php echo htmlspecialchars($result['link'], ENT_QUOTES, 'UTF-8') ?>" />
					<p><small>
						Enter a web address to turn this item's title into a hyperlink.
					</small></p>
				</div>
			</fieldset>
			<fieldset>
				<h3><label for="tags">Tags</label></h3>
				<div>
					<input type="text" name="tags" id="tags" class="text-field" value="<?php echo htmlspecialchars($result['tags'], ENT_QUOTES, 'UTF-8') ?>">
					<p><small>
						Enter tags to filter your catalog on different pages. Tags must be
						single words separated by a single space. Simply add a tag parameter 
						to your CataBlog shortcode and only items with that tag will show up.<br />
						example: <em>[catablog tag=books]</em>
					</small></p>
				</div>
			</fieldset>
			<fieldset>
				<h3>Shopping Cart</h3>
				<div>
					<p>
						<label for="price">Item Price</label><br />
						<input type="text" name="price" id="price" class="text-field" value="<?php echo (((float) $result['price']) > 0)? number_format($result['price'], 2) : "" ?>">
					</p>
					
					<p>
						<label for="product_code">Product Code</label><br />
						<input type="text" name="product_code" id="product_code" class="text-field" value="<?php echo htmlspecialchars($result['product_code'], ENT_QUOTES, 'UTF-8') ?>">
					</p>

					<p><small>
						If you want to make a shopping cart you should make sure you are 
						using a <a href="#">view option</a> that uses these values.
					</small></p>
				</div>
			</fieldset>
		</div>
		
		
	</form>
</div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#title').focus();
		
		$('#new_image').change(function(event) {
			var filename = $(this).val();
			if ($.browser.msie) {
				var start    = $(this).val().lastIndexOf('\\');
				var filename = $(this).val().slice((start + 1))
			}
			
			var s = "You have selected:<br /><strong>" + filename + "</strong> as your upload image.";
			$('#select-image-text small').html(s);
			
			$('#catablog-title').focus();
		});
	});
</script>