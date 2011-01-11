<?php // variables for database checks below ?>
<?php $table = $this->db_table ?>
<?php global $wpdb ?>
<?php $old_database_present = ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) ?>

<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Options</h2>
	
	<noscript>
		<div class="error">
			<strong>You must have a JavaScript enabled browser to change the CataBlog options.</strong>
		</div>
	</noscript>
	
	<?php if ($recalculate): ?>
		<div id="catablog-progress">
			<div id="catablog-progress-bar"></div>
			<h3 id="catablog-progress-text">Processing...</h5>
		</div>
	<?php endif ?>
	
	<form action="admin.php?page=catablog-options" id="catablog-options" class="catablog-form" method="post">
		
		<ul id="catablog-options-menu">
			<li><a href="#thumbnails">Thumbnails</a></li>
			<li><a href="#lightbox">LightBox</a></li>
			<li><a href="#title">Title</a></li>
			<li><a href="#description">Description</a></li>
			<li><a href="#template">Template</a></li>
			<li><a href="#store">Store</a></li>
			<li><a id="catablog-options-menu-export" href="#export">Export</a></li>
			<li><a href="#import">Import</a></li>
			<li><a id="catablog-options-menu-system" href="#system">Systems</a></li>
		</ul>
		
		
		<?php /*  THUMBNAIL SETTINGS PANEL */ ?>
		<div id="catablog-options-thumbnails" class="catablog-options-panel">
			<p>
				<label for='thumbnail_size'>Thumbnail Size:</label>
				<input type='text' name='thumbnail_size' id='thumbnail_size' class='arrow_edit' size='5' value='<?php echo $thumbnail_size ?>' />
				<span>pixels</span><br />
			
				<small class="error hidden">your thumbnail size must be a positive integer<br /></small>
				<small>this will change the thumbnail size of all your catalog items.</small>
			</p>
			<p>
				<?php $checked = ($keep_aspect_ratio)? "checked='checked'" : "" ?>
				<label for="keep_aspect_ratio">Keep Aspect Ratio:</label>
				<input type="checkbox" name="keep_aspect_ratio" id="keep_aspect_ratio" <?php echo $checked ?> />
				<br />
				<small>this will keep the aspect ratio of the original image in your thumbnails, using the background color to fill in the empty space.</small>
			</p>
			<p>
				<label>Thumbnail Background Color:</label>
				<input type="text" name="bg_color" id="bg_color" size="7" maxlength="6" value="<?php echo $background_color ?>" />
				<span id="red"></span>
				<span id="green"></span>
				<span id="blue"></span>
			</p>
			<hr />
			<div>
				<label>Thumbnail Preview</label>
				<p id="thumbnail_preview">
					<span id='demo_box' class='demo_box' style='width:<?php echo $thumbnail_size - 1 ?>px; height:<?php echo $thumbnail_size - 1 ?>px;'>&nbsp;</span>
				</p>
			</div>
		</div>
		
		
		
		<?php /*  LIGHTBOX SETTINGS PANEL  */ ?>
		<div id="catablog-options-lightbox" class="catablog-options-panel hide">
			<p>
				<?php $checked = ($lightbox_enabled)? "checked='checked'" : "" ?>
				<label for="lightbox_enabled">Enable LightBox Feature:</label>
				<input type="checkbox" name="lightbox_enabled" id="lightbox_enabled" <?php echo $checked ?> /><br/>
				<small>this will allow people to enlarge an image thumbnail with a lightbox style javascript effect.</small>
			</p>
			
			<p>
				<label for='lightbox_image_size'>LightBox Size:</label>
				<input type='text' name='lightbox_image_size' id='lightbox_image_size' class='arrow_edit' size='5' value='<?php echo $lightbox_size ?>' />
				<span>pixels</span><br />
				<small class="error hidden">your lightbox size must be a positive integer<br /></small>
				<small>This is the maximum length of either the height or width, depending on whichever is longer in the original uploaded image.</small>
			</p>
		</div>
		
		
		<?php /*  TITLE SETTINGS PANEL  */ ?>
		<div id="catablog-options-title" class="catablog-options-panel hide">
			<p>
				<label for="link_target">Link Target:</label>
				<select id="link_target" name="link_target">
					<option value="_blank" <?php echo ($link_target == "_blank")? 'selected="selected"' : '' ?>>_blank</option>
					<option value="_top" <?php echo ($link_target == "_top")? 'selected="selected"' : '' ?>>_top</option>
					<option value="" <?php echo ($link_target == '')? 'selected="selected"' : '' ?>>_none</option>
				</select>
				<br />
				<small>
					The link target setting will set the <strong>target</strong> attribute of all the catalog title links.
				</small>
			</p>
			<?php /*
			<p>
				<label for="link_reference">Link Reference:</label>
				<input type="text" id="link_reference" name="link_reference" value="" />
				<br />
				<small>
					The link reference setting will set the <strong>ref</strong> attribute of all the catalog links.
				</small>
			</p>
			*/ ?>
		</div>
		
		
		<?php /*  DESCRIPTION SETTINGS PANEL */ ?>
		<div id="catablog-options-description" class="catablog-options-panel hide">
			<p>
				<?php $checked = ($wp_filters_enabled)? "checked='checked'" : "" ?>
				<label for="catablog-filters-enabled">Enable WordPress Filters:</label>
				<input type="checkbox" name="wp-filters-enabled" id="catablog-filters-enabled" <?php echo $checked ?> /><br/>
				<small>
					This will filter your catalog item's description through the standard WordPress filters. 
					Be careful with this option turned on, [shortcodes] will be rendered and you can break
					your web site by putting [catablog] in any item's description.
				</small>
			</p>
			
			<p>
				<?php $checked = ($nl2br_enabled)? "checked='checked'" : "" ?>
				<label for="catablog-nl2br-enabled">Enable Hard Returns:</label>
				<input type="checkbox" name="nl2br-enabled" id="catablog-nl2br-enabled" <?php echo $checked ?> /><br/>
				<small>
					This will filter your catalog item's description through the standard PHP 
					function nl2br(). This will turn all hard returns in your description into HTML
					line break tags (&lt;br /&gt;). Turn this off if you want complete control over 
					your descriptions HTML code.
				</small>
			</p>
		</div>
		
		
		<?php /*  TEMPLATE SETTINGS PANEL  */ ?>
		<div id="catablog-options-template" class="catablog-options-panel hide">
			<p>
				<?php $views = array('- templates', 'default', 'gallery') ?>
				<select id="catablog-template-view-menu" name="view">
					<?php foreach($views as $key => $view): ?>
						<?php echo "<option value='$view'>$view</option>" ?>
					<?php endforeach ?>
				</select>
				<a href="#set-view" id="catablog-view-set-template" class="catablog-load-code button add-new-h2">Load Template</a>
			</p>
			<p>
				<textarea name="view-code-template" id="catablog-view-set-template-code" class="catablog-code" rows="10" cols="30"><?php echo $this->options['view-theme'] ?></textarea>
				
				<small>
					You may change the html code rendered by <strong>CataBlog</strong> here, this
					allows you to make fundamental changes to how catalogs will appear
					in your posts. You may choose a template from the drop down menu to
					the left and then click <em>Load Template</em> to load it into the
					template code below. If you want to setup a photo gallery I would 
					recommend that you use the <em>Gallery template</em> and then modify 
					your css accordingly. To setup a shopping cart you should load the 
					<em>Default Template</em> code and then load a buy now template. 
					Don't forget to click <strong>Save Changes</strong>
					at the bottom of the page to set your new view and finalize any changes.
				</small>
			</p>
		</div>
		
		
		
		<?php /* BUY NOW TEMPLATE SETTINGS PANEL */ ?>
		<div id="catablog-options-store" class="catablog-options-panel hide">
			<p>
				<label for="paypal_email">PayPal Account Email Address:</label>
				<input type="text" name="paypal_email" id="paypal_email" size="50" value="<?php echo $paypal_email ?>" />
			</p>
			
			<p><small>
				Enter in an email address here that has been registered with <a href="http://www.paypal.com" target="_blank">PayPal</a> and
				choose a <em>Buy Now Template</em> below to setup a store. You may then give items a price and product code.
				When an item has a price above zero a "Buy Now" button will appear under the description of that CataBlog item.
			</small></p>
			
			<hr />
			
			<p>
				<?php $views = array('- templates', 'paypal') ?>
				<select id="catablog-template-store-menu" name="view">
					<?php foreach($views as $key => $view): ?>
						<?php echo "<option value='$view'>$view</option>" ?>
					<?php endforeach ?>
				</select>
				<a href="#set-view" id="catablog-view-set-buynow" class="catablog-load-code button add-new-h2">Load Template</a>
			</p>
			<p>
				<textarea name="view-code-buynow" id="catablog-view-set-buynow-code" class="catablog-code" rows="10" cols="30"><?php echo $this->options['view-buynow'] ?></textarea>
				<small>
					You may change the html code rendered for the <strong>Buy Now</strong> button here.
					All value tokens are available here too, so place the title, description or any other
					values you may want to use from the current catalog item in this code as well.					
				</small>
			</p>
			<p style="clear:both;">&nbsp;</p>
			
			<input type="hidden" name="save" id="save" value="yes" />
			<?php wp_nonce_field( 'catablog_options', '_catablog_options_nonce', false, true ) ?>
			<input id="catablog-options-submit" type="submit" class="hide" value="<?php _e('Save Changes') ?>" />
			
		</div>
	</form>	
	
	
	
	
	<?php /*  EXPORT SETTINGS PANEL  */ ?>
	<div id="catablog-options-export" class="catablog-options-panel hide">
		<p>
			You may export your CataBlog data to a XML file which may be used to backup 
			and protect your work. The XML file is a simple transfer of the database information
			itself and the <strong>images are not included in this backup</strong>. To backup 
			your images follow the directions below.
		</p>
		
		<?php if ($old_database_present): ?>
		<p class="error">
			<strong>Important Upgrade Tip:</strong><br />
			Your backups will be made from the old CataBlog database table as long as it is present.<br />
			You should backup your old database...<br />
			verify the XML data looks correct...<br />
			and then delete	the old database in the <a href="#system" onclick="jQuery('#catablog-options-menu-system').click();">Systems Panel</a>.
		</p>
		<?php endif ?>
		
		<p>&nbsp;</p>
		<p><a href="admin.php?page=catablog-export" class="button">Save XML BackUp File</a></p>
		<p>&nbsp;</p>
		
		<p>
				<strong>Backing Up Images:</strong><br />
				Please copy the <em>catablog</em> directory to a secure location.<br />
				This directory can be located at the following path:<br />
				<strong><?php echo $this->directories['uploads'] ?></strong>
		</p>
	</div>
	
	



	<?php /*  IMPORT SETTINGS PANEL  */ ?>
	<div id="catablog-options-import" class="catablog-options-panel hide">
		<form action="admin.php?page=catablog-import" method="post" enctype="multipart/form-data">
			<?php $function_exists = function_exists('simplexml_load_file') ?>
			<?php $disabled = ($function_exists)? '' : 'disabled="disabled"'?>
			
			<p><input type="file" name="catablog_data" id="catablog_data" <?php echo $disabled ?> /></p><br />
			
			<p style="margin-bottom:5px;">&nbsp;
				<input type="checkbox" name="catablog_clear_db" id="catablog_clear_db" value="true" <?php echo $disabled ?> />
				<label for="catablog_clear_db">Replace All Data:</label>
			</p>
			
			<p><input type="submit" class="button" value="<?php _e('Import CataBlog Data') ?>" /></p>
			
			<?php if ($function_exists): ?>
				<p><small>
					To import data previously saved from CataBlog simpley select the XML file 
					you downloaded on your hard drive and click the <em>Import CataBlog Data</em>
					button. You may choose to completely erase all your data before importing
					by checking the <em>Replace All Data</em> checkbox. Keep in mind, this 
					<strong>does not import images</strong>, to do that simply replace all images
					inside the <em>originals</em> directory in
					<em><?php echo $this->directories['uploads'] ?></em>. Once you load the
					XML file and replace the <em>originals</em> directory content everything 
					should be back to the way it was before after clicking <em>Regenerate All Images</em> in
					the systems tab.
				</small></p>
			<?php else: ?>
				<p class="error"><small>
					You must have the <strong>Simple XML Library</strong> installed on your web server's version of PHP
					for XML imports to work. Please contact your server administrator for more information 
					regarding this error.
				</small></p>
			<?php endif ?>
		</form>
	</div>
	
	
	
	<?php /* SYSTEM SETTINGS PANEL */ ?>
	<div id="catablog-options-system" class="catablog-options-panel hide">
		<p>
		<?php $permissions = substr(sprintf('%o', fileperms($this->directories['uploads'])), -4) ?>
		<?php if ($permissions == '0777'): ?>
			<span>CataBlog Upload Folders are <strong>Unlocked</strong></span>
		<?php elseif ($permissions == '0755'): ?>
			<span>CataBlog Upload Folders are <strong>Locked</strong></span>
		<?php else: ?>
			<span>Error: You may be on a windows server...</span>
		<?php endif ?>
		</p>
		
		<p>
		<a href="admin.php?page=catablog-lock-folders" class="button">Lock Folders</a>
		<a href="admin.php?page=catablog-unlock-folders" class="button">Unlock Folders</a>				
		</p>
		
		<p><small>
				You may lock and unlock your <em>catablog</em> folders with 
				these controls. The idea is to unlock the folders, use your FTP client to 
				upload your original files and then lock the folders to protect them from hackers.
				After unlocking your directories please upload the original files directly
				into the <strong><?php echo $this->directories['originals'] ?></strong> folder without replacing it.
				<strong>Do not replace any of the CataBlog created folders</strong>.
				You should then regenerate all your thumbnail and lightbox pictures below.
				These controls may not work on a Windows server, it depends on your
				servers PHP settings and if the chmod command is supported. 
		</small></p>
		
		<hr />
		
		<p><label>Rescan Original Image Folder</label></p>
		<p><a href="admin.php?page=catablog-rescan-images" class="button">Rescan Original Images Folder Now</a></p>
		<p><small>
			Click the <em>Rescan Now</em> button to rescan the original catablog images
			folder and automatically import any new jpeg, gif or png images. It works simply
			by making a list of all the image names in the database and then compares each file's
			name in the originals folder against the list of image names in the database. Any newly
			discovered images will automatically be made into a new catalog item. You should Regenerate Images
			after running this command.
		</small></p>
		
		<hr />
		
		<p><label>Regenerate Images</label></p>
		<p><a href="admin.php?page=catablog-regenerate-images" class="button">Regenerate All Images Now</a></p>
		<p><small>
				Click the <em>Regenerate Now</em> button to recreate all the
				thumbnail and lightbox images that CataBlog has generated over
				the time you have used it. This is also useful when restoring exported
				data from another version of CataBlog. after you have uploaded your
				original images you must regenerate your images so they display properly.
		</small></p>
		
		<hr />
		
		<p><label>Reset CataBlog</label></p>
		<p><a href="admin.php?page=catablog-reset" class="button" id="button-reset">Reset All CataBlog Data</a></p>
		<p><small>
			Reset your entire catalog, deleting all photos and custom data permanently. 
			Sometimes you can use this to fix an improper install.
		</small></p>
		
		
		<?php if ($old_database_present): ?>
			<hr />
			
				<p><label>Clear Legacy Database Information</label></p>
				<p><a href="admin.php?page=catablog-clear-old-data" class="button">Clear Old Database Table</a></p>
				<p class="error"><small>
					You have a database table from a version of CataBlog prior to version 0.9.5.
					The <strong><a href="#export" onclick="jQuery('#catablog-options-menu-export').click();">Export</a></strong> feature 
					will read from this old database table so you may back it up before clearing it. 
					Once you no longer need the old table please click the 
					<a href="admin.php?page=catablog-clear-old-data">Clear Old Data</a> button above.
				</small></p>
		<?php endif ?>
		
		
	</div>
	
	
	
	<?php /*  SUBMIT FORM BUTTON  */ ?>
	<p class="submit" style="margin-left:100px;">
		<input type="button" id="save_changes" class="button-primary" value="<?php _e('Save Changes') ?>" />
		<span> or <a href="<?php echo get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog' ?>">back to list</a></span>
	</p>
	
</div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		
		/****************************************
		** BIND SAVE CHANGES BUTTON
		****************************************/
		$('#save_changes').bind('click', function(event) {
			var form_action = $('#catablog-options').attr('action');
			var active_tab  = $('#catablog-options-menu li a.selected').attr('href');
			
			$('#catablog-options').attr('action', (form_action+active_tab))
			$('#catablog-options').submit();
		});
		
		
		/****************************************
		** BIND OPTION PANEL TABS AND CLICK ONE
		****************************************/		
		$('#catablog-options-menu li a').bind('click', function(event) {
			$('.catablog-options-panel:visible').hide();
			$('#catablog-options-menu li a.selected').removeClass('selected');
			
			$(this).addClass('selected');
			var panel = '#catablog-options-' + $(this).attr('href').substring(1);
			$(panel).show();
		});
		
		var path = '#catablog-options-menu li:first a';
		var hash = window.location.hash;
		if (hash.length > 0) {
			path = '#catablog-options-menu li a[href='+hash+']';
		}
		$(path).click();
		

		
		/****************************************
		** BIND LOAD TEMPLATE BUTTONS
		****************************************/
		$('.catablog-load-code').bind('click', function(event) {
			var id       = this.id;
			var selected = $(this).siblings('select').val();
			if (selected == '- templates') {
				alert('please select a template from the drop down menu');
			}
			else {
				var url = "<?php echo $this->urls['plugin'] ?>/templates/views/" + selected + ".htm";
				$.get(url, function(data) {
					$('#' + id + '-code').val(data);
				});				
			}

			return false;
		});
				
		
		
		
		<?php if ($recalculate): ?>
		/****************************************
		** START RECALCULATING IMAGES
		****************************************/
		$('#save_changes').attr('disabled', true);
		
		discourage_leaving_page();
		
		var catablog_items = [<?php echo implode(', ', $item_ids) ?>];
		var total_count    = catablog_items.length;
		
		function renderCataBlogItem(id) {
			$('#catablog-progress-text').text('Rendering Image ' + (total_count - catablog_items.length) + ' of ' + total_count);
			
			var params = {
				'id':       id,
				'action':   'catablog_render_images',
				'security': '<?php echo wp_create_nonce("catablog-render-images") ?>'
			}
			
			$.post(ajaxurl, params, function(data) {
				if (catablog_items.length > 0) {
					percent_complete = 100 - ((catablog_items.length / total_count) * 100);
					$('#catablog-progress-bar').css('width', percent_complete + '%');
					renderCataBlogItem(catablog_items.shift());
				}
				else {
					$('#catablog-progress-bar').css('width', '100%');
					$('#catablog-progress-text').text('Rendering Complete');
					
					$('#save_changes').attr('disabled', false);
					unbind_discourage_leaving_page();
					
					var time1 = setTimeout(function() {
						$('#catablog-progress').hide('slow');
					}, 3000);
				}
			});
		}
		
		renderCataBlogItem(catablog_items.shift());
		
		<?php endif ?>
		
		
		

		
		
		$("#red, #green, #blue").slider({
			orientation: 'horizontal',
			range: "min",
			max: 255,
			value: 127,
			slide: refreshSwatch
		});
		setSliders('<?php echo $background_color ?>');
		
		function refreshSwatch() {
			var red = $("#red").slider("value")
				,green = $("#green").slider("value")
				,blue = $("#blue").slider("value")
				,hex = hexFromRGB(red, green, blue);
			$("#demo_box").css("background-color", "#" + hex);
			$('#bg_color').val(hex);
		}
		
		
		function setSliders(hex) {
			$("#red").slider("value", HexToR(hex));
			$("#green").slider("value", HexToG(hex));
			$("#blue").slider("value", HexToB(hex));
			refreshSwatch();
		}
		
		
		
		
		
		
		
		
		
		$('#bg_color').bind('blur', function(event) {
			if (this.value.length != 6) {
				alert('Please make sure to enter a full 6 character hexadecimal color code.')
				this.value = "000000";
			}
			setSliders(this.value)
		});
		
		
		
		$('input.arrow_edit').bind('keydown', function(event) {
			var step = 5;
			var keycode = event.keyCode;
			
			if (keycode == 40) { this.value = parseInt(this.value) - step; }
			if (keycode == 38) { this.value = parseInt(this.value) + step; }
		});
		
		
		
		$('input.arrow_edit').bind('keyup', function(event) {
			var v = this.value;
			if (is_integer(v) && (v > 0)) {
				$(this).siblings('small.error').hide();
				if ($(this).attr('id') == 'thumbnail_size') {
					resize_box(v);
				}
			}
			else {
				$(this).siblings('small.error').show();
			}
			
			possibly_disable_save_button();
		});
		
		function possibly_disable_save_button() {
			if ($('small.error:visible').size() == 0) {
				$('#save_changes').attr('disabled', false);
				$('#save_changes').attr('class', 'button-primary');
			}
			else {
				$('#save_changes').attr('disabled', true);
				$('#save_changes').attr('class', 'button-disabled');
			}
		}
		
		
		
		var lightbox_button   = $('#lightbox_enabled');
		var lightbox_fieldset = lightbox_button.parent().parent();
		
		if (lightbox_button.attr('checked') == false) {
			$('input.arrow_edit', lightbox_fieldset).attr('readonly', true);
			lightbox_fieldset.addClass('disabled');
		}
		
		lightbox_button.bind('click', function(event) {
			if (this.checked) {
				$('input.arrow_edit', lightbox_fieldset).attr('readonly', false);
				lightbox_fieldset.removeClass('disabled');
			}
			else {
				$('input.arrow_edit', lightbox_fieldset).attr('readonly', true);
				lightbox_fieldset.addClass('disabled');
			}
		});
		
		
		function is_integer(s) {
			return (s.toString().search(/^[0-9]+$/) == 0);
		}

		function resize_box(num) {
			var speed = 100;
			jQuery('#demo_box').animate({width:(num-1), height:(num-1)}, speed);
		}
		
	});
	

	
	
	
</script>