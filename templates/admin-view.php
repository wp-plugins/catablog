<div class="wrap">
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>Edit CataBlog View</h2>
	
	<?php if (!isset($_REQUEST['save'])): ?>
		<p>&nbsp;</p>
	<?php endif ?>
	
	<form id="catablog-view" class="catablog-form" method="post" action="">
		
		
		
		<fieldset>
			<legend>Template Code</legend>
			
			<div style="float:left; width:100px">
				<?php $views = array('- templates', 'default', 'gallery') ?>
				<select id="catablog-view-menu" name="view">
					<?php foreach($views as $key => $view): ?>
						<?php echo "<option value='$view'>$view</option>" ?>
					<?php endforeach ?>
				</select>
				<a href="#set-view" id="catablog-view-set-template" class="catablog-load-code button add-new-h2">Load Template</a>
			</div>
			
			<p style="margin-left:120px;">
				You may change the html code rendered by <strong>CataBlog</strong> here, this
				allows you to make fundamental changes to how catalogs will appear
				in your posts. You may choose a template from the drop down menu to
				the left and then click <em>Set View</em> to load it into the
				template code below. If you want to setup a photo gallery I would 
				recommend that you use the <em>Gallery template</em> and then modify 
				your css accordingly. To setup a shopping cart you should load the 
				<em>PayPal template</em> code and edit it to match your needs, such 
				as changing the currency. Don't forget to click <em>Save Changes</em>
				at the bottom of the page to use your new view and finalize any changes.
			</p>
			<p style="clear:both;">&nbsp;</p>
			
			<textarea name="view-code-template" id="catablog-view-set-template-code" class="catablog-code"><?php echo $this->options['view-theme'] ?></textarea>
		</fieldset>
		
		
		
		
		
		<fieldset>
			<legend>Buy Now Button Code</legend>
			
			<div style="float:left; width:100px">
				<?php $views = array('- templates', 'paypal') ?>
				<select id="catablog-view-menu" name="view">
					<?php foreach($views as $key => $view): ?>
						<?php echo "<option value='$view'>$view</option>" ?>
					<?php endforeach ?>
				</select>
				<a href="#set-view" id="catablog-view-set-buynow" class="catablog-load-code button add-new-h2">Load Template</a>
			</div>
			
			<p style="margin-left:120px;">
				You may change the html code rendered for the <strong>Buy Now</strong> button here.
				All value tokens are available here too, so place the title, description or any other
				values you may want to use from the current catalog item in this code as well.
			</p>
			<p style="clear:both;">&nbsp;</p>
			
			<textarea name="view-code-buynow" id="catablog-view-set-buynow-code" class="catablog-code"><?php echo $this->options['view-buynow'] ?></textarea>
		</fieldset>
		
		
		
		
		
		<?php wp_nonce_field( 'catablog_options', '_catablog_options_nonce', false, true ) ?>
		<input type="hidden" name="save" id="save" value="yes" />		
		<p class="submit">
			<input type="submit" id="save_changes" class="button-primary" value="<?php _e('Save Changes') ?>" />
			<span> or <a href="<?php echo get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog' ?>">back to list</a></span>
		</p>
	</form>
</div>
		
<script type="text/javascript">
	
	jQuery(document).ready(function($) {
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
	});
	
</script>