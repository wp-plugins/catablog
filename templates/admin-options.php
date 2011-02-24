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
	
	<?php if ($recalculate_thumbnails): ?>
		<div id="catablog-progress-thumbnail" class="catablog-progress">
			<div class="catablog-progress-bar"></div>
			<h3 class="catablog-progress-text">Processing Thumbnail Images...</h3>
		</div>
	<?php endif ?>
	
	<?php if ($recalculate_fullsize): ?>
		<div id="catablog-progress-fullsize" class="catablog-progress">
			<div class="catablog-progress-bar"></div>
			<h3 class="catablog-progress-text">Waiting For Thumbnail Rendering To Finish...</h3>
		</div>
	<?php endif ?>
	
	<form action="admin.php?page=catablog-options" id="catablog-options" class="catablog-form" method="post">
		
		<ul id="catablog-options-menu">
			<li><a href="#thumbnails" title="Set size and how thumbnails will be made">Thumbnails</a></li>
			<li><a href="#lightbox" title="">LightBox</a></li>
			<li><a href="#title" title="">Title</a></li>
			<li><a href="#description" title="">Description</a></li>
			<?php /*<li><a href="#public" title="">Public</a></li> */ ?>
			<li><a href="#template" title="Control how your catalog is rendered">Template</a></li>
			<li><a href="#store" title="">Store</a></li>
			<li><a id="catablog-options-menu-export" href="#export" title="">Export</a></li>
			<li><a href="#import" title="">Import</a></li>
			<li><a id="catablog-options-menu-system" href="#system" title="">Systems</a></li>
		</ul>
		
		
		<?php /*  THUMBNAIL SETTINGS PANEL */ ?>
		<div id="catablog-options-thumbnails" class="catablog-options-panel">
			<p>
				<label for='thumbnail_size'>Thumbnail Size:</label>
				<input type='text' name='thumbnail_size' id='thumbnail_size' class='integer_field' size='5' value='<?php echo $thumbnail_size ?>' />
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
				<input type="text" name="bg_color" id="bg_color" size="8" maxlength="7" value="<?php echo $background_color ?>" />
				<span class="color-swatch hide-if-no-js">&nbsp;</span>
				<small><a class="hide-if-no-js" href="#" id="pickcolor"><?php _e('Select a Color'); ?></a></small>
			</p>
			<div id="color-picker-div">&nbsp;</div>
			<hr />
			<div>
				<label>Thumbnail Preview</label>
				<p id="thumbnail_preview">
					<span id='demo_box' class='demo_box' style='width:<?php echo $thumbnail_size ?>px; height:<?php echo $thumbnail_size ?>px;'>&nbsp;</span>
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
				<input type='text' name='lightbox_image_size' id='lightbox_image_size' class='integer_field' size='5' value='<?php echo $lightbox_size ?>' />
				<span>pixels</span><br />
				<small class="error hidden">your lightbox size must be a positive integer<br /></small>
				<small>This is the maximum length of either the height or width, depending on whichever is longer in the original uploaded image.</small>
			</p>
		</div>
		
		
		<?php /*  TITLE SETTINGS PANEL  */ ?>
		<div id="catablog-options-title" class="catablog-options-panel hide">
			<p>
				<label for="link_target">Link Target:</label>
				<input type="text" id="link_target" name="link_target" value="<?php echo $link_target ?>" /><br />
				<small>
					The link target setting will set the <strong>target</strong> attribute of all the catalog title links.<br />
					<strong>examples:</strong> _blank, _top, _self.
				</small>
			</p>
			<p>
				<label for="link_target">Link Relationship:</label>
				<input type="text" id="link_relationship" name="link_relationship" value="<?php echo $link_relationship ?>" maxlength="30" /><br />
				<small>
					The link relationship will set the <strong>rel</strong> attribute of all the catalog title links.<br />
					<strong>examples:</strong> index, next, prev, glossary, chapter, bookmark, nofollow.
				</small>
			</p>
			<?php /*
			<p>
				<label for="permalink-default">Empty Links Go To Public Page:</label>
				<?php $checked = ($permalink_default)? "checked='checked'" : "" ?>
				<input type="checkbox" id="permalink-default" name="permalink-default" <?php echo $checked ?> /><br />
				<small>
					This feature only works if you are generating individual item pages.<br />
					When enabled all the catalog titles will link to the item's public page if the link value is empty.<br />
					
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
					Enable the standard WordPress filters for your catalog item's description.<br /> 
					This allows you to use shortcodes and media embeds inside your catalog item descriptions.<br />
					Please <strong>do not use the &#91;catablog&#93; shortcode</strong> inside a catalog item's description.
				</small>
			</p>
			
			<p>
				<?php $checked = ($nl2br_enabled)? "checked='checked'" : "" ?>
				<label for="catablog-nl2br-enabled">Render Line Breaks:</label>
				<input type="checkbox" name="nl2br-enabled" id="catablog-nl2br-enabled" <?php echo $checked ?> /><br/>
				<small>
					Filter your catalog item's description through the standard PHP function 
					<a href="http://php.net/manual/en/function.nl2br.php" target="_blank">nl2br()</a>.<br />
					This will insert HTML line breaks before all new lines in your catalog descriptions.<br />
					Turn this off if unwanted line breaks are being rendered on your page.
				</small>
			</p>
		</div>
		
		
		<?php /*  PUBLIC SETTINGS PANEL  */ ?>
		<?php /*
		<div id="catablog-options-public" class="catablog-options-panel hide">
			<p>
				<?php $checked = ($public_catalog_items_enabled)? "checked='checked'" : "" ?>
				<label for="public-catalog-items">Generate Public Pages For Catalog Item:</label>
				<input type="checkbox" name="public-catalog-items" id="public-catalog-items" <?php echo $checked ?> /><br/>
				<small>
					Generates a permalink for each individual CataBlog item that will display the singular item like a page.<br />
					This will put individual catalog items in your blog's search results.<br />
					Use the permalink structure below to control the permalink path to your catalog items.
				</small>
			</p>
			
			<p>
				<label for="public-catalog-items">Public Page's Permalink Path:</label>
				<input type="text" name="public-catalog-slug" id="public-catalog-slug" readonly="readonly" value="<?php echo $public_catalog_slug ?>" maxlength="200" /><br/>
				<small>
					Set the slug to be used in the individual catalog item's public page.<br />
					<?php if (mb_strlen($public_catalog_slug) > 0): ?>
						<strong>example:</strong> /<?php echo $public_catalog_slug ?>/item-name/
					<?php endif ?>
				</small>
			</p>
		</div>
		*/ ?>
		
		<?php /*  TEMPLATE SETTINGS PANEL  */ ?>
		<div id="catablog-options-template" class="catablog-options-panel hide">
			<p>
				<?php $views = new CataBlogDirectory($this->directories['views']); //('- templates', 'default', 'gallery', 'grid') ?>
				<?php if ($views->isDirectory()): ?>
					<select id="catablog-template-view-menu">
						<?php foreach($views->getFileArray() as $key => $view): ?>
							<?php echo "<option value='$view'>$view</option>" ?>
						<?php endforeach ?>
					</select>
					<a href="<?php echo $this->urls['views'] ?>" id="catablog-view-set-template" class="catablog-load-code button add-new-h2">Load Template</a>
				<?php else: ?>
					<p class="error">Could not locate the views directory. Please reinstall CataBlog.</p>
				<?php endif ?>
				
				
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
				<?php $views = new CataBlogDirectory($this->directories['buttons']) ?>
				<?php if ($views->isDirectory()): ?>
					<select id="catablog-template-store-menu">
						<?php foreach($views->getFileArray() as $key => $view): ?>
							<?php echo "<option value='$view'>$view</option>" ?>
						<?php endforeach ?>
					</select>
					<a href="<?php echo $this->urls['buttons'] ?>" id="catablog-view-set-buynow" class="catablog-load-code button add-new-h2">Load Template</a>
				<?php else: ?>
					<p class="error">Could not locate the views directory. Please reinstall CataBlog.</p>
				<?php endif ?>
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
		<?php $function_exists = function_exists('fputcsv') ?>
		
		<p>
			You may export your CataBlog data to a XML or CSV file which may be used to backup 
			and protect your work. The XML or CSV file is a simple transfer of the database information
			itself and the <strong>images are not included in this backup</strong>. To backup 
			your images follow the directions at the bottom of the page.
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
		
		<p>
			<a href="admin.php?page=catablog-export&amp;format=xml" class="button">Save XML BackUp File</a>
			<?php if ($function_exists): ?>
				<span> | </span>
				<a href="admin.php?page=catablog-export&amp;format=csv" class="button">Save CSV BackUp File</a>
			<?php endif ?>
		</p>
		
		<p>&nbsp;</p>
		
			
		<?php if (!$function_exists): ?>
			<p class="error"><small>
				You must have the function 
				<strong><a href="http://php.net/manual/en/function.fputcsv.php" target="_blank">fputcsv()</a></strong> 
				available on your web server's version of PHP for CSV export to work.
				Please contact your server administrator for more information regarding this error.
			</small></p>
		<?php endif ?>
		
		<p>
			<strong>Backing Up Images:</strong><br />
			Please copy the <em>catablog</em> directory to a secure location.<br />
			The directory for this WordPress blog can be located on your web server at:<br />
			<small><em><?php echo $this->directories['uploads'] ?></em></small>
		</p>
	</div>
	
	



	<?php /*  IMPORT SETTINGS PANEL  */ ?>
	<div id="catablog-options-import" class="catablog-options-panel hide">
		<label>Import XML/CSV Data</label>
		<form action="admin.php?page=catablog-import" method="post" enctype="multipart/form-data">
			<?php $function_exists = function_exists('simplexml_load_file') ?>
			
			<p><input type="file" name="catablog_data" id="catablog_data" /></p><br />
			
			<p style="margin-bottom:5px;">&nbsp;
				<input type="checkbox" id="catablog_clear_db" name="catablog_clear_db" value="true" />
				<label for="catablog_clear_db">Replace All Data:</label>
			</p>
			
			<p><input type="submit" class="button" value="<?php _e('Import CataBlog Data') ?>" /></p>
			
			<?php if (!$function_exists): ?>
				<p class="error"><small>
					You must have the <strong>Simple XML Library</strong> installed on your web server's version of PHP
					for XML imports to work. Please contact your server administrator for more information 
					regarding this error.
				</small></p>
			<?php endif ?>
			
			<p><small>
				To import data into your catalog you simply select a XML or CVS file 
				on your hard drive and click the <em>Import CataBlog Data</em> button.
				You may choose to completely erase all your data before importing
				by checking the <em>Replace All Data</em> checkbox. Keep in mind, this 
				<strong>does not import or delete images</strong>. You should replace all images
				inside the <em>originals</em> directory in
				<em><?php echo $this->directories['uploads'] ?></em>. Once you load the
				XML or CVS file and replace the <em>originals</em> directory content everything 
				should be set after you 
				<a href="admin.php?page=catablog-regenerate-images" class="js-warn">Regenerate All Images</a> 
				in the systems tab.
			</small></p>
			
			<p><small>
				You may view XML and CSV examples in the 
				<a href="http://catablog.illproductions.com/documentation/importing-and-exporting-catalogs/" target="_blank">import/export documentation</a>.
			</small></p>
			

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
		<a href="admin.php?page=catablog-lock-folders#system" class="button">Lock Folders</a>
		<a href="admin.php?page=catablog-unlock-folders#system" class="button">Unlock Folders</a>				
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
		<p><a href="admin.php?page=catablog-rescan-images" class="button js-warn">Rescan Original Images Folder Now</a></p>
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
		<p><a href="admin.php?page=catablog-regenerate-images" class="button js-warn">Regenerate All Images Now</a></p>
		<p><small>
				Click the <em>Regenerate Now</em> button to recreate all the
				thumbnail and lightbox images that CataBlog has generated over
				the time you have used it. This is also useful when restoring exported
				data from another version of CataBlog. after you have uploaded your
				original images you must regenerate your images so they display properly.
		</small></p>
		
		<hr />
		
		<p><label>Reset CataBlog</label></p>
		<p><a href="admin.php?page=catablog-reset" class="button js-warn" id="button-reset">Reset All CataBlog Data</a></p>
		<p><small>
			Reset your entire catalog, deleting all photos and custom data permanently. 
			Sometimes you can use this to fix an improper install.
		</small></p>
		
		
		<?php if ($old_database_present): ?>
			<hr />
			
				<p><label>Clear Legacy Database Information</label></p>
				<p><a href="admin.php?page=catablog-clear-old-data" class="button js-warn">Clear Old Database Table</a></p>
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
		<span> or <a href="<?php echo 'admin.php?page=catablog-options' ?>">reset options</a></span>
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
		** THUMBNAILS PANEL
		****************************************/
		// update the thumbnail preview size dynamically
		$('#thumbnail_size').bind('keyup', function(event) {
			var v = this.value;
			if (is_integer(v) && (v > 0)) {
				$(this).siblings('small.error').hide();
				if ($(this).attr('id') == 'thumbnail_size') {
					jQuery('#demo_box').animate({width:(v-1), height:(v-1)}, 100);
				}
			}
		});
		
		
		// update thumbnail preview for keep aspect ratio option
		if ($('#keep_aspect_ratio').attr('checked') == false) {
			$('#demo_box').addClass('crop');
		}
		$('#keep_aspect_ratio').bind('change', function(event) {
			if (this.checked) {
				$('#demo_box').removeClass('crop');
			}
			else {
				$('#demo_box').addClass('crop');
			}
		});
		
		// load image for thumbnail preview
		var thumbnail_preview = new Image;
		thumbnail_preview.onload = function() {
			var preview = '<img src="'+this.src+'" />';
			$('#demo_box').append(preview);
		}
		thumbnail_preview.src = "<?php echo $this->urls['images'] ?>/catablog-thumbnail-preview.jpg";
		
		
		
		
		/****************************************
		** LIGHTBOX PANEL
		****************************************/
		// disable lightbox size field if the lightbox is off
		$('#lightbox_image_size').attr('readonly', !$('#lightbox_enabled').attr('checked'));
		$('#lightbox_enabled').bind('click', function(event) {
			if (this.checked) {
				$('#lightbox_image_size').attr('readonly', false);
			}
			else {
				$('#lightbox_image_size').attr('readonly', true);
			}
		});
		
		
		
		
		
		/****************************************
		** PUBLIC PANEL
		****************************************/
		$('#public-catalog-slug').attr('readonly', !$('#public-catalog-items').attr('checked'));
		$('#public-catalog-items').bind('click', function(event) {
			if (this.checked) {
				$('#public-catalog-slug').attr('readonly', false);
			}
			else {
				$('#public-catalog-slug').attr('readonly', true);
			}
		});	
		
		
		
		
		
		/****************************************
		** TEMPLATE & BUY BUTTON PANELS
		****************************************/
		// bind the load buttons for the template drop down menus
		$('.catablog-load-code').bind('click', function(event) {
			var id       = this.id;
			var selected = $(this).siblings('select').val();
			var url = this.href + "/" + selected;
			
			$.get(url, function(data) {
				$('#' + id + '-code').val(data);
			});				
			
			return false;
		});
				
		// bind the textareas in the catablog options form to accept tabs
		$('#catablog-options textarea').bind('keydown', function(event) {
			var item = this;
			if(navigator.userAgent.match("Gecko")){
				c = event.which;
			}else{
				c = event.keyCode;
			}
			if(c == 9){
				replaceSelection(item,String.fromCharCode(9));
				$("#"+item.id).focus();	
				return false;
			}
		});
		
		
		
		
		
		
		
		
		
		/****************************************
		** GENERAL FORM BINDINGS
		****************************************/
		// enter key submits form
		$('#catablog-options input').bind('keydown', function(event) {
			if(event.keyCode == 13){
				$('#save_changes').click();
				return false;
			}
		});
		
		// up and down arrow keys for changing integer values
		$('#catablog-options input.integer_field').bind('keydown', function(event) {
			var step = 5;
			var keycode = event.keyCode;
			
			if (keycode == 40) { this.value = parseInt(this.value) - step; }
			if (keycode == 38) { this.value = parseInt(this.value) + step; }
		});
		
		// bind showing an error message when an integer value is incorrect
		$('#catablog-options input.integer_field').bind('keyup', function(event) {
			var v = this.value;
			
			if (is_integer(v) && (v > 0)) {
				// do nothing
			}
			else {
				$(this).siblings('small.error').show();
			}
			
			possibly_disable_save_button();
		});
		
		// confirm with javascript that the user wants to complete the action
		$('div.catablog-options-panel a.js-warn').click(function(event) {
			var message = $(this).html() + "?";
			return confirm(message);
		})
		
		
		
		
		
		
		

		/****************************************
		** BIND COLOR PICKERS
		****************************************/
		var farbtastic;
		function pickColor(a) {
			farbtastic.setColor(a);
			jQuery("#bg_color").val(a);
			jQuery("#demo_box").css("background-color",a)
			jQuery('.color-swatch').css("background-color",a);
		}
		jQuery("#pickcolor").click(function() {
			
			jQuery(this).addClass('selected');
			
			var color_picker = jQuery("#color-picker-div");
			color_picker.css('top', jQuery('#bg_color').offset().top + 21);
			color_picker.css('left', jQuery(this).offset().left);
			color_picker.show();
			
			return false;
		});
		jQuery("#bg_color").keyup(function() {
			var b = jQuery(this).val();
			a = b;
			
			if (a[0]!="#") {
				a = "#"+a;
			}
			
			a = a.replace(/[^#a-fA-F0-9]+/,"");
			if (a != b) {
				jQuery("#bg_color").val(a)
			}
			
			if (a.length == 4 || a.length == 7){
				pickColor(a)
			}
		});	
				
		farbtastic = jQuery.farbtastic("#color-picker-div",function(a){
			pickColor(a)
		});
		pickColor(jQuery("#bg_color").val());
		
		jQuery(document).mousedown(function(){
			jQuery("#color-picker-div").each(function(){
				var a = jQuery(this).css("display");
				if (a == "block") {
					jQuery('#pickcolor.selected').removeClass('selected');
					jQuery(this).fadeOut(2);
				}
			});
		});
		
		
		
		
		
		
		
		/****************************************
		** RECALCULATE IMAGES IF NECESSARY
		****************************************/
			
	<?php if ($recalculate_thumbnails || $recalculate_fullsize): ?>
		$('#save_changes').attr('disabled', true);
		var nonce   = '<?php echo wp_create_nonce("catablog-render-images") ?>';		
		var images  = ["<?php echo implode('", "', $image_names) ?>"];
		var message = "Image rendering is now complete";
	<?php endif ?>
		
	<?php if ($recalculate_thumbnails): ?>
		var thumbs = images.slice(0);
		renderCataBlogItems(thumbs, 'thumbnail', nonce, function() {
			jQuery('#catablog-progress-thumbnail .catablog-progress-text').html(message);
			
			<?php if ($recalculate_fullsize): ?>
				var fullsize = images.slice(0);
				renderCataBlogItems(fullsize, 'fullsize', nonce, function() {
					jQuery('#catablog-progress-fullsize .catablog-progress-text').html(message);
					var t = setTimeout(function() {
						jQuery('#catablog-progress-thumbnail').hide('medium');
						jQuery('#catablog-progress-fullsize').hide('medium');
						jQuery('#message').hide('medium');
					}, 2000);
					$('#save_changes').attr('disabled', false);
				});
			<?php else: ?>
				var t = setTimeout(function() {
					jQuery('#catablog-progress-thumbnail').hide('medium');
					jQuery('#message').hide('medium');
				}, 2000);
				
				$('#save_changes').attr('disabled', false);
			<?php endif ?>
			
			
			
			
		});
	<?php endif ?>
	

			
			
			
		
	}); // end onReady method
	

	
	
	
</script>