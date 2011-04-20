<div class="wrap">

		<div id="icon-catablog" class="icon32"><br /></div>
		<h2>
			<span><?php _e("Manage CataBlog", 'catablog'); ?></span>
			<a href="admin.php?page=catablog-new" class="button add-new-h2"><?php _e("Add New", 'catablog'); ?></a>
			
		</h2>
		<div id="message" class="updated hide">
			<strong>&nbsp;</strong>
		</div>

		<noscript>
			<div class="error">
				<strong><?php _e("You must have a JavaScript enabled browser for bulk actions and to change the order of your items.", 'catablog'); ?>
				<a href="http://www.google.com/search?q=what+is+javascript"><?php _e("Learn More", 'catablog'); ?></a></strong>
			</div>
		</noscript>


		<div class="tablenav">
			
			<form id="catablog-bulk-action-form" method="post" action="admin.php?page=catablog-bulkedit" class="alignleft actions hide">
				<input type="hidden" name="page" value="catablog-bulkedit" />
				<?php wp_nonce_field( 'catablog_bulkedit', '_catablog_bulkedit_nonce', false, true ) ?>
				
				<select id="bulk-action" name="bulk-action">
					<option value=""><?php _e("Bulk Actions", 'catablog'); ?></option>
					<option value="delete"><?php _e("Delete", 'catablog'); ?></option>
				</select>
				
				<input type="submit" value="<?php _e("Apply", 'catablog'); ?>" class="button-secondary" />
				<small>|</small>
			</form>
			
			<form method="get" action="admin.php?page=catablog" class="alignleft actions">
				<label for="cat">View:</label>
				<input type="hidden" name="page" value="catablog" />
				<select id="cat" name="category" class="postform">
					<option value="-1">- <?php _e("All Categories", 'catablog'); ?></option>
					<?php $categories = $this->get_terms() ?>
					<?php foreach ($categories as $category): ?>
						<?php $selected = ($category->term_id == $selected_term->term_id)? 'selected="selected"' : '' ?>
						<option value="<?php echo $category->term_id ?>" <?php echo $selected ?> ><?php echo $category->name ?></option>
					<?php endforeach ?>
				</select>
				<input type="submit" value="Filter" id="catablog-submit-filter" class="button-secondary" />
				
				<?php /*
				<small>|</small>
				
				<?php $disabled = (!isset($_GET['category']) || $_GET['category'] > 0)? '' : 'disabled="disabled"' ?>
				<a href="#sort" id="enable_sort" <?php echo $disabled ?> class="button"><?php _e("Change Order", 'catablog'); ?></a>
				*/ ?>
			</form>
			
			<div id="catablog-view-switch" class="view-switch">
				<?php $list_class = ($view == 'list')? 'class="current"' : 'class=""' ?>
				<?php $grid_class = ($view == 'grid')? 'class="current"' : 'class=""' ?>
				<?php $meta       = 'width="20" height="20" border="0"'; ?>
				<?php $current_cat = (isset($_GET['category']))? '&amp;category='.$_GET['category'] : '' ?>
				<?php $current_page = (isset($_GET['offset']))? '&amp;offset='.$_GET['offset'] : ''?>
				
				<a href="admin.php?page=catablog<?php echo $current_page ?><?php echo $current_cat ?>&amp;view=list">
					<img src="<?php echo $this->urls['images'] ?>/blank.gif" id="view-switch-list" <?php echo "$list_class $meta" ?> title="<?php _e("List View", 'catablog'); ?>" alt="<?php _e("List View", 'catablog'); ?>"/>
				</a>
				<a href="admin.php?page=catablog<?php echo $current_page ?><?php echo $current_cat ?>&amp;view=grid">
					<img src="<?php echo $this->urls['images'] ?>/blank.gif" id="view-switch-excerpt" <?php echo "$grid_class $meta" ?> title="<?php _e("Grid View", 'catablog'); ?>" alt="<?php _e("Grid View", 'catablog'); ?>"/>
				</a>
			</div>
		</div>
		
		<?php
		
		if ($view == 'grid') {
			include_once($this->directories['template'] . '/admin-grid.php');
		}
		else {
			include_once($this->directories['template'] . '/admin-list.php');
		}
				
		?>
	
</div>

<script type="text/javascript">
	var timer = null;
	
	jQuery(document).ready(function($) {
		
		/************************************************************************************
		** quick form bindings that should happen first
		*************************************************************************************/
		// show the bulk actions form and bind form submission;
		$('#catablog-bulk-action-form').show().bind('submit', function(event) {
			var self = this;
			
			if ($('#bulk-action').val().length < 1) {
				alert('<?php _e("Please select a bulk action to apply.", "catablog"); ?>');
				return false;
			}
			
			if ($('#bulk-action').val() == 'delete') {
				if (!confirm('<?php _e("Are you sure you want to delete multiple items?", "catablog"); ?>')) {
					return false;
				}
			}
			
			$('#catablog_items input.bulk_selection:checked').each(function() {
				$(self).append("<input type='hidden' name='bulk_selection[]' value='"+this.value+"' />");
			});
			
		});
		
		// hide the filter button and bind live category switching
		$('#catablog-submit-filter').hide();
		$('#cat.postform').bind('change', function(event) {
			$(this).closest('form').submit();
		});
		
		
		/************************************************************************************
		** quick form modifications that should happen first
		*************************************************************************************/
		// lazy load the images
		calculate_lazy_loads();
		$(window).bind('scroll resize', function(event) {
			calculate_lazy_loads();
		});
		
		
		/*
		// initialize the sortables
		var catablog_items_path = "#catablog_items";
		$(catablog_items_path).sortable({
			disabled: true,
			forcePlaceholderSize: true,
			opacity: 0.7, 
			<?php echo ($view == 'list')? "axis: 'y'" : "" ?>
		});
		
		
		$('#enable_sort').bind('click', function(event) {
			if ($(this).attr('disabled')) {
				alert('<?php _e("This feature only works when viewing a single category.", "catablog"); ?>');
				return false;
			}
			
			var items = $(catablog_items_path);
			if ($(this).hasClass('button-primary')) {
				
				// disable sortable and save order using ajax
				items.sortable('option', 'disabled', true);
				ajax_save_order();
				
				// remove disable link classes and show bulk selection
				items.find('a').removeClass('cb_disabled_link');
				items.find('input.bulk_selection').show();
				unbind_discourage_leaving_page();
				
				// enable selection of text and remove sort enabled class
				items.enableSelection();
				items.removeClass('sort_enabled');
				
				// swap button to original state
				$(this).html('Change Order').removeClass('button-primary');
			}
			else {
				
				// disable links, hide bulk selection and discourage leaving page
				items.find('a').addClass('cb_disabled_link');
				items.find('input.bulk_selection').hide();
				
				discourage_leaving_page('<?php _e("You have not saved your order. If you leave now you will loose your changes. Are you sure you want to continue leaving this page?", "catablog"); ?>');
				
				// disable selection of text and add sort enabled class
				items.disableSelection();
				items.addClass('sort_enabled');
				
				// enable sortable items
				items.sortable('option', 'disabled', false);
				
				// display helpful message to user
				var help_message = '<?php _e("Drag the items below to rearrange their order.", "catablog"); ?>';
				$('#message strong').html(help_message);
				$('#message').show();
				
				// swap button to active state
				$(this).html('Save Order').addClass('button-primary');
			}
			
			return false;
		});
		
		function ajax_save_order() {
			var ids = [];
			$('#catablog_items input.bulk_selection').each(function(i) {
				var id = $(this).attr('value');
				ids.push(id);
			});
			
			var params = {
				'action':   'catablog_reorder',
				'security': '<?php echo wp_create_nonce("catablog-reorder") ?>',
				'ids[]':    ids
			}
			
			$('#message strong').html('<?php _e("Saving new catalog order...", "catablog"); ?>');			
			$.post(ajaxurl, params, function(data) {
				$('#message strong').html('<?php _e("Your catalog items have been rearranged successfully.", "catablog"); ?>');
			});
		}
		*/
		
	});
</script>