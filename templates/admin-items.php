<div class="wrap">

		<div id="icon-catablog" class="icon32"><br /></div>
		<h2>
			<span>Manage CataBlog</span>
			<a href="admin.php?page=catablog-new" class="button add-new-h2">Add New</a>
			
		</h2>


		<div id="message" class="updated hide">
			<strong>&nbsp;</strong>
		</div>

		<noscript>
			<div class="error">
				<strong>You must have a JavaScript enabled browser for bulk actions and to change the order of your items. <a href="http://www.google.com/search?q=what+is+javascript">Learn More</a>.</strong>
			</div>
		</noscript>




		<div class="tablenav">
			
			<form id="catablog-bulk-action-form" method="post" action="admin.php?page=catablog-bulkedit" class="alignleft actions hide">
				<input type="hidden" name="page" value="catablog-bulkedit" />
				<?php wp_nonce_field( 'catablog_bulkedit', '_catablog_bulkedit_nonce', false, true ) ?>
				
				<select id="bulk-action" name="bulk-action">
					<option value="">Bulk Actions</option>
					<option value="delete">Delete</option>
				</select>
				
				<input type="submit" value="Apply" class="button-secondary" />
				<small>|</small>
			</form>
			
			<form method="get" action="admin.php?page=catablog" class="alignleft actions">
				<input type="hidden" name="page" value="catablog" />
				<select id="cat" name="category" class="postform">
					<option value="-1">- All Items [slow]</option>
					<?php $categories = get_terms($this->custom_tax_name, 'hide_empty=0') ?>
					<?php foreach ($categories as $category): ?>
						<?php $selected = ($category->term_id == $selected_term_id)? 'selected="selected"' : '' ?>
						<option value="<?php echo $category->term_id ?>" <?php echo $selected ?> ><?php echo $category->name ?></option>
					<?php endforeach ?>
				</select>
				<input type="submit" value="Filter" id="catablog-submit-filter" class="button-secondary" />
				
				<small>|</small>
				
				<a href="#sort" id="enable_sort" class="button">Change Order</a>
			</form>
			
			<div id="catablog-view-switch" class="view-switch">
				<?php $list_class = ($view == 'list')? 'class="current"' : 'class=""' ?>
				<?php $grid_class = ($view == 'grid')? 'class="current"' : 'class=""' ?>
				<?php $meta       = 'width="20" height="20" border="0"'; ?>
				<?php $current_cat = (isset($_GET['category']))? '&amp;category='.$_GET['category'] : '' ?>
				<?php $current_page = (isset($_GET['offset']))? '&amp;offset='.$_GET['offset'] : ''?>
				
				<a href="admin.php?page=catablog<?php echo $current_page ?><?php echo $current_cat ?>&amp;view=list">
					<img src="<?php echo $this->urls['images'] ?>/blank.gif" id="view-switch-list" <?php echo "$list_class $meta" ?> title="List View" alt="List View"/>
				</a>
				<a href="admin.php?page=catablog<?php echo $current_page ?><?php echo $current_cat ?>&amp;view=grid">
					<img src="<?php echo $this->urls['images'] ?>/blank.gif" id="view-switch-excerpt" <?php echo "$grid_class $meta" ?> title="Grid View" alt="Grid View"/>
				</a>
			</div>
		</div>
		
		<?php /*
		<div id="catablog-view-nav">
			<?php echo $this->items_per_page ?> | 
			<?php echo $catalog_count = wp_count_posts($this->custom_post_name)->publish ?> | 
			
			
			<?php echo $pages = floor($catalog_count / $this->items_per_page) ?>
			<?php for ($i = 0; $i <= $pages; $i++): ?>
				<a href="admin.php?page=catablog&amp;offset=<?php echo $i + 1 ?>"><?php echo $i + 1 ?></a>
			<?php endfor ?>
		</div>
		*/ ?>
		
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
				
		// disable item links when drag n drop is enabled
		$('#catablog_items a').bind('click', function(e) {
			if ($(this).hasClass('cb_disabled_link')) {
				return false;
			}
		});
		
		
		// bind a warning on the delete link
		// LIST VIEW ONLY!
		$('#catablog_items a.remove_link').bind('click', function(e) {
			if (confirm('Are you sure you want to delete this catablog item?')) {
				return true;
			}			
			return false;
		});
		
		// hide exceptionaly long descriptions
		// LIST VIEW ONLY!
		// $('#catablog_items div.catablog-list-description').each(function() {
		// 	var height = $(this).height();
		// 	if (height > 90) {
		// 		$(this).height(72);
		// 		$(this).after("<em>more...</em>");
		// 	}
		// });
				
		
		
		// show the bulk actions form and bind form submission;
		$('#catablog-bulk-action-form').show().bind('submit', function(event) {
			var self = this;
			
			if ($('#bulk-action').val().length < 1) {
				alert('Please select a bulk action to apply.');
				return false;
			}
			
			if ($('#bulk-action').val() == 'delete') {
				if (!confirm('Are you sure you want to delete multiple items?')) {
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
		
		// lazy load the images
		calculate_lazy_loads();
		$(window).bind('scroll resize', function(event) {
			calculate_lazy_loads();
		});
		
		
		// initialize the sortables
		var catablog_items_path = "#catablog_items";
		$(catablog_items_path).sortable({
			disabled: true,
			forcePlaceholderSize: true,
			opacity: 0.7, 
			<?php echo ($view == 'list')? "axis: 'y'" : "" ?>
		});
		
		
		$('#enable_sort').bind('click', function(event) {
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
				discourage_leaving_page("You have not saved your order.\nIf you leave now you will loose your changes.\n Are you sure you want to continue leaving this page?");
				
				// disable selection of text and add sort enabled class
				items.disableSelection();
				items.addClass('sort_enabled');
				
				// enable sortable items
				items.sortable('option', 'disabled', false);
				
				// display helpful message to user
				var help_message = 'Drag the items below to rearrange their order.';
				$('#message strong').html(help_message);
				$('#message').show();
				
				// swap button to active state
				$(this).html('Save Order').addClass('button-primary');
			}
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
			
			$('#message strong').html('Saving new catalog order...');			
			$.post(ajaxurl, params, function(data) {
				$('#message strong').html('Your catalog items have been rearranged successfully');
			});
		}
		
	});
</script>