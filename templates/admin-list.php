<div class="wrap">
		
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>
		<span>Manage CataBlog</span>
		<a href="admin.php?page=catablog-new" class="button add-new-h2">Add New</a>
		<a href="#sort" id="enable_sort" class="button add-new-h2">Change Order</a>
	</h2>
	
	<div id="message" class="updated hide">
		<strong>&nbsp;</strong>
	</div>
	
	<noscript>
		<div class="error">
			<strong>You must have a JavaScript enabled browser to change the order of your items on this page.</strong>
		</div>
	</noscript>
	
	<form method="post" action="">	
	<table id="catablog_items" class="widefat post" cellspacing="0">
		<thead>
			<tr>
				<?php /*<th class="manage-column column-cb check-column"><input type="checkbox" /></th>*/?>
				<?php /*<th class="manage-column cb_order_column"></th>*/?>
				<th class="manage-column cb_icon_column">Image</th>
				<th class="manage-column">Title</th>
				<th class="manage-column">Link</th>
				<th class="manage-column">Description</th>
				<th class="manage-column">Categories</th>
				<th class="manage-column">Price</th>
				<th class="manage-column">Product Code</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<?php /*><th class="manage-column column-cb check-column"><input type="checkbox" /></th>*/?>
				<?php /*<th class="manage-column cb_order_column"></th>*/?>
				<th class="manage-column cb_icon_column">Image</th>
				<th class="manage-column">Title</th>
				<th class="manage-column">Link</th>
				<th class="manage-column">Description</th>
				<th class="manage-column">Categories</th>
				<th class="manage-column">Price</th>
				<th class="manage-column">Product Code</th>
			</tr>
		</tfoot>
		
		<tbody>
			<?php if (count($results) < 1): ?>
				<tr>
					<td colspan='5'>No CataBlog Items</td>
				</tr>
			<?php endif ?>
			<?php foreach ($results as $result): ?>
				<?php $edit   = get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog-edit&amp;id='.$result->getId() ?>
				<?php $remove = get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog-delete&amp;id='.$result->getId() ?>
				
				<tr>
					<?php /*><th class="check-column"><input type="checkbox" /></th>*/?>
					<?php /*
					<td class="cb_order_column">
						<span class="cb_item_handle" title="Drag To Reorder Items">&nbsp;</span>
					</td>
					*/?>
					<td class="cb_icon_column">
						<input type="hidden" name="catablog-item-id" value="<?php echo $result->getId() ?>" />
						<a href="<?php echo $edit ?>"><img src="<?php echo $this->urls['thumbnails'] . "/" . $result->getImage() ?>" class="cb_item_icon" width="50" height="50" /></a>
					</td>
					<td>
						<strong><a href="<?php echo $edit ?>" title="Edit CataBlog Item"><?php echo htmlentities($result->getTitle(), ENT_QUOTES, 'UTF-8') ?></a></strong>
						<div class="row-actions">
							<span><a href="<?php echo $edit ?>">Edit</a></span>
							<span> | </span>
							<span class="trash"><a href="<?php echo $remove ?>" class="remove_link">Trash</a></span>
						</div>
					</td>
					<td><?php echo htmlspecialchars($result->getLink(), ENT_QUOTES, 'UTF-8') ?>&nbsp;</td>
					<td><?php echo nl2br(htmlentities($result->getDescription(), ENT_QUOTES, 'UTF-8')) ?>&nbsp;</td>
					<td><?php echo htmlspecialchars(implode(', ', $result->getCategories()), ENT_QUOTES, 'UTF-8') ?>&nbsp;</td>
					<?php $currency = "" ?>
					<td><?php echo (((float) $result->getPrice()) > 0)? $currency. number_format($result->getPrice(), 2) : "" ?>&nbsp;</td>
					<td><?php echo htmlspecialchars($result->getProductCode(), ENT_QUOTES, 'UTF-8') ?>&nbsp;</td>
				</tr>
			<?php endforeach; ?>
		</tbody>

	</table>
	</form>
</div>

<script type="text/javascript">
	var timer = null;
	jQuery(document).ready(function($) {
		
		$('a.remove_link').bind('click', function(e) {
			if (confirm('Are you sure you want to delete this catablog item?')) {
				return true;
			}
			return false;
		});
		
		
		$("#catablog_items tbody").sortable({
			disabled: true,
			forcePlaceholderSize: true,
			axis: 'y',
			opacity: 0.7
		});
		
		
		$('#enable_sort').bind('click', function(event) {
			var tbody = $('#catablog_items tbody');
			if ($(this).hasClass('button-primary')) {
				// lock the order
				tbody.enableSelection();
				tbody.removeClass('sort_enabled');
				tbody.sortable('option', 'disabled', true);
				
				ajax_save_order();
				
				$(this).html('Change Order').removeClass('button-primary');
			}
			else {
				// enable DnD order changing
				tbody.disableSelection();
				tbody.addClass('sort_enabled')
				tbody.sortable('option', 'disabled', false);

				$(this).html('Save Order').addClass('button-primary');
			}
		});
		
		function ajax_save_order() {
			var ids = [];
			$('#catablog_items tbody tr').each(function(i) {
				var id = $('td:first input', this).attr('value');
				ids.push(id);
			});
			
			var params = {
				'action':   'catablog_reorder',
				'security': '<?php echo wp_create_nonce("catablog-reorder") ?>',
				'ids[]':    ids
			}
			
			$('#message').show();
			$('#message strong').html('Saving New Order...');
			$.post(ajaxurl, params, function(data) {
				$('#message strong').html('New Order Saved');
				timer = setTimeout(function() {
					$('#message').hide(500);
				}, 8000);
			});
		}
		
	});
</script>