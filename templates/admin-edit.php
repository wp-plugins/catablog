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
							<input type="hidden" name="image" id="image" value="" />
							<label id="select-image-button">
								<input type="file" id="new_image" name="new_image" tabindex="1" />
							</label>
							<p id="select-image-text"><small>
								Double click <em>Select Image</em> above to choose the 
								image you would like to upload for this item. You
								may upload JPEG, GIF and PNG graphic formats only.
								Every CataBlog item is required to have an image 
								and a title. 
								
							</small></p>
						<?php else: ?>
							<img src="<?php echo $this->urls['thumbnails'] . "/" . $result->getImage() ?>" id="catablog-image-preview" />
							<input type="hidden" name="image" id="image" value="<?php echo $result->getImage() ?>" />
							<label id="select-image-button">
								<input type="file" id="new_image" name="new_image" tabindex="1" />
							</label>
								
							<p id="select-image-text"><small>
								Double click <em>Select Image</em> above to choose a
								replacement image for your item. Again only
								JPEG, GIF and PNG formats are accepted.
							</small></p>
						<?php endif ?>
					</div>
					
					
					<div id="catablog-edit-main-text">
						<label for="catablog-title">Title</label>
						<input type="text" name="title" id="catablog-title" maxlength="200" tabindex="2" value="<?php echo htmlspecialchars($result->getTitle(), ENT_QUOTES, 'UTF-8') ?>" />
						<br /><br />
						<label for="catablog-description">Description [<small>excepts html formatting</small>]</label>
						<textarea name="description" id="catablog-description" tabindex="2"><?php echo htmlspecialchars($result->getDescription(), ENT_QUOTES, 'UTF-8') ?></textarea>
					</div>
					
					<div id="catablog-edit-main-save">
						<input type="hidden" id="save" name="save" value="yes" />
						<?php wp_nonce_field( 'catablog_save', '_catablog_save_nonce', false, true ) ?>
						
						<?php if (!$new_item): ?>
							<input type="hidden" id="saved_image" name="saved_image" value="<?php echo $result->getImage() ?>" />
						<?php endif ?>
						
						<input type="hidden" id="id" name="id" value="<?php echo $result->getId() ?>" />
						<input type="hidden" id="order" name="order" value="<?php echo $result->getOrder() ?>" />
						
						<?php $save_button_label = ($new_item)? 'Create CataBlog Item' : 'Save Changes' ?>
						<input type="submit" class="button-primary" tabindex="9" value="<?php echo $save_button_label ?>" />
						
						<span> or <a href="<?php echo get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog' ?>" tabindex="10">back to list</a></span>						
					</div>
				</div>
		
			</fieldset>
		</div>
		
		
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
		
		
	</form>
	
</div>

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