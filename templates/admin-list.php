	<form method="post" action="">	
	<table class="widefat post" cellspacing="0">
		<thead>
			<tr>
				<th class="manage-column column-cb check-column"><input type="checkbox" /></th>
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
				<th class="manage-column column-cb check-column"><input type="checkbox" /></th>
				<th class="manage-column cb_icon_column">Image</th>
				<th class="manage-column">Title</th>
				<th class="manage-column">Link</th>
				<th class="manage-column">Description</th>
				<th class="manage-column">Categories</th>
				<th class="manage-column">Price</th>
				<th class="manage-column">Product Code</th>
			</tr>
		</tfoot>
		
		<tbody id="catablog_items">
			<?php if (count($results) < 1): ?>
				<tr>
					<td colspan='5'>No CataBlog Items</td>
				</tr>
			<?php endif ?>
			<?php foreach ($results as $result): ?>
				<?php $edit   = get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog-edit&amp;id='.$result->getId() ?>
				<?php $remove = get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog-delete&amp;id='.$result->getId() ?>
				
				<tr>
					<th class="check-column"><input type="checkbox" class="bulk_selection" name="bulk_action_id" value="<?php echo $result->getId() ?>" /></th>
					<td class="cb_icon_column">
						<a href="<?php echo $edit ?>"><img src="<?php echo $this->urls['thumbnails'] . "/" . $result->getImage() ?>" class="cb_item_icon" width="50" height="50" alt="" /></a>
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