<div class="wrap">
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>Manage CataBlog <a href="admin.php?page=catablog-new" class="button add-new-h2">Add New</a></h2>
	
	<div id="message" class="updated hide">
		<strong>&nbsp;</strong>
	</div>
	
	<form method="post" action="">	
	<table id="catablog_items" class="widefat post" cellspacing="0">
		<thead>
			<tr>
				<?php /*<th class="manage-column column-cb check-column"><input type="checkbox" /></th>*/?>
				<th class="manage-column cb_order_column"></th>
				<th class="manage-column cb_icon_column">Image</th>
				<th class="manage-column">Title</th>
				<th class="manage-column">Link</th>
				<th class="manage-column">Description</th>
				<th class="manage-column">Tags</th>
				<?php if (mb_strlen($this->options['paypal-email']) > 0): ?>
					<th class="manage-column">Price</th>
					<th class="manage-column">Product Code</th>
				<?php endif ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<?php /*><th class="manage-column column-cb check-column"><input type="checkbox" /></th>*/?>
				<th class="manage-column cb_order_column"></th>
				<th class="manage-column cb_icon_column">Image</th>
				<th class="manage-column">Title</th>
				<th class="manage-column">Link</th>
				<th class="manage-column">Description</th>
				<th class="manage-column">Tags</th>
				<?php if (mb_strlen($this->options['paypal-email']) > 0): ?>
					<th class="manage-column">Price</th>
					<th class="manage-column">Product Code</th>
				<?php endif ?>
			</tr>
		</tfoot>
		
		<tbody>
			<?php if (count($results) < 1): ?>
				<tr>
					<td colspan='5'>No CataBlog Items</td>
				</tr>
			<?php endif ?>
			<?php foreach ($results as $key => $result): ?>
				<?php $edit   = get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog-new&amp;action=edit&amp;id='.$result->id ?>
				<?php $remove = get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog-new&amp;action=remove&amp;id='.$result->id ?>
				<tr>
					<?php /*><th class="check-column"><input type="checkbox" /></th>*/?>
					<td class="cb_order_column">
						<span class="cb_item_handle" title="Drag To Reorder Items">&nbsp;</span>
						<input type="hidden" name="catablog-item-id" value="<?php echo $result->id ?>" />
					</td>
					<td class="cb_icon_column">
						<img src="<?php echo get_bloginfo('wpurl').'/wp-content/uploads/catablog/thumbnails/'.$result->image ?>" class="cb_item_icon" width="50" height="50" />
					</td>
					<td>
						<strong><a href="<?php echo $edit ?>" title="Edit CataBlog Item"><?php echo htmlspecialchars($result->title, ENT_QUOTES, 'UTF-8') ?></a></strong>
						<div class="row-actions">
							<span><a href="<?php echo $edit ?>">Edit</a></span>
							<span> | </span>
							<span class="trash"><a href="<?php echo $remove ?>" class="remove_link">Trash</a></span>
						</div>
					</td>
					<td><?php echo htmlspecialchars($result->link, ENT_QUOTES, 'UTF-8') ?></td>
					<td><?php echo nl2br(htmlspecialchars($result->description, ENT_QUOTES, 'UTF-8')) ?></td>
					<td><?php echo htmlspecialchars($result->tags, ENT_QUOTES, 'UTF-8') ?></td>
					<?php if (mb_strlen($this->options['paypal-email']) > 0): ?>
						<td><?php echo htmlspecialchars($result->price, ENT_QUOTES, 'UTF-8') ?></td>
						<td><?php echo htmlspecialchars($result->product_code, ENT_QUOTES, 'UTF-8') ?></td>
					<?php endif ?>
				</tr>
			<?php endforeach; ?>
		</tbody>

	</table>
	</form>
</div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$("#catablog_items tbody").sortable({
			forcePlaceholderSize: true,
			axis: 'y',
			handle: 'span.cb_item_handle',
			opacity: 0.7,
			update: function(event, ui) {
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
					setTimeout(function() {
						$('#message').hide(1000);
					}, 4000);
				});
				
			}
		});
		$("#catablog_items tbody").disableSelection();
	});
</script>