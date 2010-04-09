<div class="wrap">
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>Manage CataBlog</h2>

	<form method="post" action="">	
	<table id="catablog_items" class="widefat post fixed" cellspacing="0">
		<thead>
			<tr>
				<?php /*<th class="manage-column column-cb check-column"><input type="checkbox" /></th>*/?>
				<th class="manage-column cb_icon_column">Order</th>
				<th class="manage-column cb_icon_column">Image</th>
				<th class="manage-column">Title</th>
				<th class="manage-column">Link</th>
				<th class="manage-column">Description</th>
				<th class="manage-column column-rm">Delete</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<?php /*><th class="manage-column column-cb check-column"><input type="checkbox" /></th>*/?>
				<th class="manage-column cb_icon_column">Order</th>
				<th class="manage-column cb_icon_column">Image</th>
				<th class="manage-column">Title</th>
				<th class="manage-column">Link</th>
				<th class="manage-column">Description</th>
				<th class="manage-column">Delete</th>
			</tr>
		</tfoot>
		
		<tbody>
			<?php if (count($results) < 1): ?>
				<tr>
					<td colspan='5'>No CataBlog Items</td>
				</tr>
			<?php endif ?>
			<?php foreach ($results as $key => $result): ?>
				<?php $edit   = get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog_edit&amp;action=edit&amp;id='.$result->id ?>
				<?php $remove = get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog_edit&amp;action=remove&amp;id='.$result->id ?>
				<tr>
					<?php /*><th class="check-column"><input type="checkbox" /></th>*/?>
					<td><?php echo $result->order ?></td>
					<td class="cb_icon_column">
						<img src="<?php echo get_bloginfo('wpurl').'/wp-content/catablog/'.$result->image ?>" class="cb_item_icon" width="50" height="50" />
					</td>
					<td><a href="<?php echo $edit ?>" title="Edit CataBlog Item"><?php echo htmlspecialchars($result->title, ENT_QUOTES, 'UTF-8') ?></a></td>
					<td><?php echo htmlspecialchars($result->link, ENT_QUOTES, 'UTF-8') ?></td>
					<td><?php echo nl2br(htmlspecialchars($result->description, ENT_QUOTES, 'UTF-8')) ?></td>
					<td><a href="<?php echo $remove ?>" class="remove_link" title="Remove CataBlog Item">remove</td>
				</tr>
			<?php endforeach; ?>
		</tbody>

	</table>
	</form>
</div>