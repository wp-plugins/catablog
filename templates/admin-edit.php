<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2><?php echo ($new_item)? 'Add New CataBlog Entry' : 'Edit CataBlog Entry' ?></h2>
		
	<form id="catablog-edit" class="catablog-form clear_float" method="post" action="admin.php?page=catablog-save" enctype="multipart/form-data">
		
		
		<div id="catablog-edit-params">
			<fieldset>
				<h3><label for="link">Link</label></h3>
				<div>
					<input type="text" name="link" id="link" class="text-field" tabindex="3" value="<?php echo htmlspecialchars($result->getLink(), ENT_QUOTES, 'UTF-8') ?>" />
					<p><small>
						Enter a web address to turn this item's title into a hyperlink.
					</small></p>
				</div>
			</fieldset>
			
			<fieldset>
				<h3>Categories</h3>
				<div id="catablog-category" class="tabs-panel">
										
					<ul id="catablog-category-checklist" class="list:category categorychecklist form-no-clear">
						
						<?php $categories = get_terms($this->custom_tax_name, 'hide_empty=0') ?>						
						
						<?php if (count($categories) < 1): ?>
							<li><span>You currently have no categories.</span></li>
						<?php endif ?>
						
						<?php foreach ($categories as $category): ?>
						<li>
							<label class="catablog-category-row">
								<?php $checked = (in_array($category->term_id, array_keys($result->getCategories())))? 'checked="checked"' : '' ?>
								<input id="in-category-<?php echo $category->term_id ?>" type="checkbox" <?php echo $checked ?> name="categories[]"  tabindex="4" value="<?php echo $category->term_id ?>" />
								<span><?php echo $category->name ?></span>
								<a href="#delete" class="catablog-category-delete hide"><small>[DELETE]</small></a>
							</label>
						</li>
						<?php endforeach ?>
					</ul>
					
					<div id="catablog-new-category">
						<noscript>
							<div class="error">
								<strong><small>You must have a JavaScript enabled browser to create new categories.</small></strong>
							</div>
						</noscript>
						
						<span class="hide">
							<input id="catablog-new-category-input" type="text" tabindex="5" value="" />
							<a href="#new-category" id="catablog-new-category-submit" class="button" tabindex="6">New</a>
							<img src="<?php echo $this->urls['images'] ?>/ajax-loader-small.gif" id="catablog-new-category-load" class="hide" />
						</span>
					</div>
					<p><small>
						Put your items into categories to easily display subsets of your catalog on different pages.<br />
						<strong>ex:</strong> <em>[catablog category="dogs and cats"]</em>
					</small></p>
				</div>
			</fieldset>
			
			<fieldset>
				<h3>Shopping Cart</h3>
				<div>
					<p>
						<label for="price">Item Price</label><br />
						<input type="text" name="price" id="price" class="text-field" tabindex="7" value="<?php echo $result->getPrice() ?>">
					</p>
					
					<p>
						<label for="product_code">Product Code</label><br />
						<input type="text" name="product_code" id="product_code" class="text-field" tabindex="8" value="<?php echo htmlspecialchars($result->getProductCode(), ENT_QUOTES, 'UTF-8') ?>">
					</p>

					<p><small>
						If you want to make a shopping cart you should make sure you are 
						using a <a href="admin.php?page=catablog-options#store">Store Template</a> that uses these values.
					</small></p>
				</div>
			</fieldset>
		</div>
		
		

		<div id="catablog-edit-main">
			<fieldset>
				<h3>
					<?php if (!$new_item): ?>
						<span class="catablog-edit-navigation" style="float:right">
							<?php if ($prev_item != false): ?>
								<a href="admin.php?page=catablog-edit&id=<?php echo $prev_item->getId() ?>">&larr; <?php echo $prev_item->getTitle() ?></a>
							<?php else: ?>
								<span class="nonessential">no previous item</span>
							<?php endif ?>
							<span> | </span>
							<?php if ($next_item != false): ?>
								<a href="admin.php?page=catablog-edit&id=<?php echo $next_item->getId() ?>"><?php echo $next_item->getTitle() ?> &rarr;</a>
							<?php else: ?>
								<span class="nonessential">no next item</span>
							<?php endif?>
						</span>
					<?php endif ?>
					<span>Main</span>
				</h3>
				<div>
					<div id="catablog-edit-main-image">
						<?php if ($new_item): ?>
							<p id="no-image-icon">No Image!</p>
							<input type="hidden" name="image" id="image" value="" />
							<label id="select-image-button">
								<input type="file" id="new_image" name="new_image" tabindex="1" />
							</label>
							<p id="select-image-text"><small>
								Double click <em>Select Image</em> above to choose the 
								image you would like to upload as the main image for this item.
								You	may upload JPEG, GIF and PNG graphic formats only.
								Every CataBlog item is required to have a main image and title. 
								
							</small></p>
						<?php else: ?>
							<label>Images</label>
							<div id="catablog-edit-images-column">
								
								<img src="<?php echo $this->urls['thumbnails'] . "/" . $result->getImage() ?>" id="catablog-image-preview" />
								<input type="hidden" name="image" id="image" value="<?php echo $result->getImage() ?>" />
								
								<hr />
								
								<a href="#replace-main-image" id="show-image-window"><small style="font-size:10px;">Replace Main Image</small></a>
								<a href="#add-subimage" id="show-subimage-window"><small style="font-size:10px;">[+] Add Sub Image</small></a>
								
								<hr />
								
								<?php if (count($result->getSubImages()) > 0): ?>
									<ul id="catablog-sub-images">
										<?php foreach ($result->getSubImages() as $sub_image): ?>
											<li>
												<img src="<?php echo $this->urls['thumbnails'] . "/$sub_image" ?>" class="catablog-image-preview" />
												<input type="hidden" name="sub_images[]" class="sub-image" value="<?php echo $sub_image ?>" />
												<a href="#delete" class="catablog-delete-subimage" title="Delete this sub image permanently.">X</a>
											</li>
										<?php endforeach ?>
										<li class="clear">&nbsp;</li>
									</ul>
								<?php else: ?>
									<p><small class="nonessential">No Sub Images</small></p>
								<?php endif ?>
								
								
							</div>	
						<?php endif ?>
						
						
						
						
						
					</div>
					
					
					<div id="catablog-edit-main-text">
						<label for="catablog-title">Title</label>
						<input type="text" name="title" id="catablog-title" maxlength="200" tabindex="2" value="<?php echo htmlspecialchars($result->getTitle(), ENT_QUOTES, 'UTF-8') ?>" />
						<br /><br />
						<label for="catablog-description">Description [<small>accepts html formatting</small>]</label>
						<textarea name="description" id="catablog-description" tabindex="2"><?php echo htmlspecialchars($result->getDescription(), ENT_QUOTES, 'UTF-8') ?></textarea>
					</div>
					
					<div id="catablog-edit-main-save" class="clear_float">
						<input type="hidden" id="save" name="save" value="yes" />
						<?php wp_nonce_field( 'catablog_save', '_catablog_save_nonce', false, true ) ?>
						
						<?php if (!$new_item): ?>
							<input type="hidden" id="saved_image" name="saved_image" value="<?php echo $result->getImage() ?>" />
						<?php endif ?>
						
						<input type="hidden" id="id" name="id" value="<?php echo $result->getId() ?>" />
						<input type="hidden" id="order" name="order" value="<?php echo ($new_item)? wp_count_posts($this->custom_post_name)->publish : $result->getOrder() ?>" />
						
						<?php $save_button_label = ($new_item)? 'Create CataBlog Item' : 'Save Changes' ?>
						<input type="submit" class="button-primary" id="save_changes" tabindex="9" value="<?php echo $save_button_label ?>" />
						
						<?php if ($this->options['public-catalog-items']): ?>
						<span> or </span>
						<a href="<?php echo $result->getPermalink() ?>" target="_blank" class="button">View Catalog Item</a>
						<?php endif ?>
						
						<span> or <a href="<?php echo get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog' ?>" tabindex="10">back to list</a></span>						
					</div>
					
				</div>
		
			</fieldset>
		</div>
		
		
		
		
		
	</form>
	
</div>

<?php if (!$new_item): ?>

<div id='catablog_load_curtain'>&nbsp;</div>

<div id="add-subimage-window" class="catablog-modal">
	<form id="catablog-add-subimage" class="catablog-form" method="post" action="admin.php?page=catablog-add-subimage" enctype="multipart/form-data">
		<h3>
			<span style="float:right;"><a href="#" class="hide-modal-window">[close]</a></span>
			<strong>Upload A New Sub Image</strong>
		</h3>
		<div>
			<input type="file" id="new_sub_image" name="new_sub_image"  />
			<span class="nonessential"> | </span>
			<input type="hidden" name="id" value="<?php echo $result->getId() ?>" >

			<?php wp_nonce_field( 'catablog_add_subimage', '_catablog_add_subimage_nonce', false, true ) ?>
			<input type="submit" name="save" value="Submit" class="button-primary" />
			<p><small>
				Select an image on your computer to upload and then add to this item as a sub image.
				You	may upload JPEG, GIF and PNG graphic formats only.
				You will be adding a sub image, this upload will not replace this item's main image.
			</small></p>			
		</div>
	</form>
</div>

<div id="replace-image-window" class="catablog-modal">
	<form id="catablog-replace-image" class="catablog-form" method="post" action="admin.php?page=catablog-replace-image" enctype="multipart/form-data">
		<h3>
			<span style="float:right;"><a href="#" class="hide-modal-window">[close]</a></span>
			<strong>Replace The Main Image</strong>
		</h3>
		<div>
			<input type="file" id="new_image" name="new_image"  />
			<span class="nonessential"> | </span>
			<input type="hidden" name="id" value="<?php echo $result->getId() ?>" >

			<?php wp_nonce_field( 'catablog_replace_image', '_catablog_replace_image_nonce', false, true ) ?>
			<input type="submit" name="save" value="Submit" class="button-primary" />
			<p><small>
				Select an image on your computer to upload and then replace this item's main image with.
				You	may upload JPEG, GIF and PNG graphic formats only.
				You will be replacing the main image for this item, this upload will not add a new sub image.
			</small></p>			
		</div>
	</form>
</div>

<?php /*
<form id="catablog-remove-subimages" class="catablog-form" method="post" action="admin.php?page=catablog-remove-subimages">
	<label>Remove Sub Images:</label>
	
	<input type="hidden" name="id" value="<?php echo $result->getId() ?>" />
	<?php wp_nonce_field( 'catablog_remove_subimages', '_catablog_remove_subimages_nonce', false, true ) ?>
	
	<input type="submit" class="button" value="Remove All Sub Images Now" />
	<p><small>
		Click this button to remove all sub images from this catalog item.<br />
		All thumbnail, full size and original image files will be <strong>permanently deleted</strong>.<br />
		This will not remove or replace the main image from this catalog item.
	</small></p>
</form>
*/ ?>

<?php endif ?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		
		// FOCUS ON THE TITLE INPUT BOX
		$('#catablog-title').focus();
		
		// SHOW THE NEW CATEGORY FORM IF JAVASCRIPT IS ENABLED
		$('#catablog-new-category span').show();
		
		
		
		// BIND SELECT NEW IMAGE HOVERS
		$('#new_image').bind('mouseover focus', function(event) {
			$('#select-image-button').css('background-position', '0px -31px');
		});
		$('#new_image').bind('mouseout blur', function(event) {
			$('#select-image-button').css('background-position', '0px 0px');
		});
		
		
		
		// UPDATE IMAGE DESCRIPTION AFTER SELECTING A NEW IMAGE
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
		
		
		
		// BIND SORTABLE IMAGES
		$('#catablog-sub-images').sortable({
			cursor: 'crosshair',
			forcePlaceholderSize: true,
			opacity: 0.7,
			revert: 200
		})
		
		
		
		
		// BIND CATEGORY LIST HOVERS
		$('#catablog-category-checklist li label').live('mouseover', function(event) {
			$(this).addClass('hover');
			if (!catablog_category_is_loading()) {
				$('a.catablog-category-delete', this).show();
			}
		});
		$('#catablog-category-checklist li label').live('mouseout', function(event) {
			$(this).removeClass('hover');
			$('a.catablog-category-delete', this).hide();
		});
		
		
		
		// BIND DELETE CATEGORY LINKS
		$('#catablog-category-checklist li label a.catablog-category-delete').live('click', function(event) {
			// stop javascript event propagation and set this variable
			event.stopPropagation();
			var object = this;
			
			// make sure category changes aren't still loading
			if (catablog_category_is_loading()) {
				return false;
			}
			
			// confirm the deletion of the category
			if (!confirm('Are you sure you want to delete this category? You can not undo this.')) {
				return false;
			}
			
			// show the load indicator and disable new category button
			catablog_category_show_load();
			
			// setup AJAX params
			var term_id = $(this).siblings('input').val();
			var params  = {
				'action':   'catablog_delete_category',
				'security': '<?php echo wp_create_nonce("catablog-delete-category") ?>',
				'term_id':  term_id
			}
			
			// make AJAX call
			$.post(ajaxurl, params, function(data) {
				try {
					var json = eval(data);
					if (json.success == false) {
						alert(json.error);
					}
					else {
						var category = $(object).parent().parent();
						$(category).animate({'opacity':0, 'height':0, 'padding':0, 'margin':0}, 500, function() {
							$(category).remove();
						});
					}
				}
				catch(error) {
					alert(error);
				}
				
				// hide load indicator and enable new category button
				catablog_category_hide_load();
			});
			
			return false;
		});
		
		
		
		// BIND NEW CATEGORY TEXT INPUT BOX
		$('#catablog-new-category-input').bind('keypress', function(event) {
			var key_code = (event.keyCode ? event.keyCode : event.which);
			if (key_code == 13) {
				$('#catablog-new-category-submit').click();
				return false;
			}
		});
		
		
		// BIND NEW CATEGORY FORM
		$('#catablog-new-category-submit').bind('click', function(event) {
			// if button disabled don't do anything
			if (catablog_category_is_loading()) {
				return false;
			}
			
			// check if category name is set
			var category_name = $('#catablog-new-category-input').val();
			if (category_name == '') {
				alert('Please make sure to enter a category name');
				return false;
			}
			
			// show load indicators and disable button
			catablog_category_show_load();
			
			// set AJAX params
			var params = {
				'action':   'catablog_new_category',
				'security': '<?php echo wp_create_nonce("catablog-new-category") ?>',
				'name':     category_name
			}
			
			// make AJAX call
			$.post(ajaxurl, params, function(data) {
				try {
					var json = eval(data);
					if (json.success == false) {
						alert(json.error);
					}
					else {
						var html = '<li><label class="catablog-category-row">';
						html    += ' <input id="in-category-'+json.id+'" type="checkbox" checked="checked" name="categories[]" value="'+json.id+'" /> ';
						html    += ' <span>'+json.name+'</span> ';
						html    += ' <a href="#delete" class="catablog-category-delete hide"><small>[DELETE]</small></a>';
						html    += '</label></li>';
						
						$('#catablog-category-checklist').append(html);
						$('#catablog-new-category-input').val('');
					}
				}
				catch(error) {
					alert(error);
				}
				
				// hide load indicators and enable button
				catablog_category_hide_load();
			});
			
			return false;
		});
		
		// BIND
		$('#show-image-window').bind('click', function(event) {
			jQuery('#replace-image-window').show();
			jQuery('#catablog_load_curtain').fadeTo(200, 0.8);
			return false;
		});
		
		// BIND ADD SUB IMAGE MODAL WINDOW
		$('#show-subimage-window').bind('click', function(event) {
			jQuery('#add-subimage-window').show();
			jQuery('#catablog_load_curtain').fadeTo(200, 0.8);
			return false;
		});
		
		
		$('.hide-modal-window').bind('click', function(event) {
			jQuery('.catablog-modal:visible').hide();
			jQuery('#catablog_load_curtain').fadeOut(200);
			return false;
		});
		
		// BIND DELETE SUB IMAGE
		$('#catablog-sub-images .catablog-delete-subimage').bind('click', function(event) {
			if (!confirm('Are you sure you want to permanently delete this image?')) {
				return false;
			}
			
			var self  = this;
			var id    = $('#id').val();
			var image = $(this).siblings('input').val();
			
			var params = {
				'action':   'catablog_delete_subimage',
				'security': '<?php echo wp_create_nonce("catablog-delete-subimage") ?>',
				'id':       id,
				'image':    image
			}
			
			disable_save_button();
			
			// make AJAX call
			$.post(ajaxurl, params, function(data) {
				try {
					var json = eval(data);
					if (json.success == false) {
						alert(json.error);
					}
					else {
						$(self).parent().animate({opacity:0, height:0, margin:0}, 800, function() {
							$(this).remove();
							enable_save_button();
						});
					}
				}
				catch(error) {
					alert(error);
				}
				
			});
		});
		
		
		
		function catablog_category_show_load() {
			$('#catablog-new-category-load').show();
			$('#catablog-new-category-submit').addClass('disabled');
		}
		
		function catablog_category_hide_load() {
			$('#catablog-new-category-load').hide();
			$('#catablog-new-category-submit').removeClass('disabled');
		}
		
		function catablog_category_is_loading() {
			return $('#catablog-new-category-submit').hasClass('disabled');
		}
		
		
	});
</script>