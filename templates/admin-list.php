	<table class="widefat post" cellspacing="0">
		<thead>
			<tr>
				<th class="manage-column column-cb check-column"><input type="checkbox" /></th>
				<th class="manage-column cb_icon_column"><?php _e("Image", "catablog"); ?></th>
				<?php $css_sort = ($sort=='title')? "sorted" : "sortable" ?>
				<?php $sort_url = ($order=='asc')? "&amp;order=desc" : "&amp;order=asc" ?>
				<?php $cat_url  = (isset($_GET['category']))? "&amp;category=".$_GET['category'] : "" ?>
				<th class="manage-column <?php echo "$css_sort $order" ?>" style="width:120px;">
					<a href="admin.php?page=catablog&amp;sort=title<?php echo $sort_url . $cat_url ?>">
						<span><?php _e("Title", "catablog"); ?></span>
						<span class="sorting-indicator">&nbsp;</span>
					</a>
				</th>
				<?php /*<th class="manage-column"><?php _e("Link", "catablog"); ?></th>*/ ?>
				<th class="manage-column"><?php _e("Description", "catablog"); ?></th>
				<th class="manage-column"><?php _e("Categories", "catablog"); ?></th>
				<?php /*<th class="manage-column"><?php _e("Price", "catablog"); ?></th>*/ ?>
				<?php /*<th class="manage-column"><?php _e("Product Code", "catablog"); ?></th>*/ ?>
				
				<?php $css_sort = ($sort=='menu_order')? "sorted" : "sortable" ?>
				<?php $sort_url = ($order=='asc')? "&amp;order=desc" : "&amp;order=asc" ?>
				<?php $cat_url  = (isset($_GET['category']))? "&amp;category=".$_GET['category'] : "" ?>
				<th class="manage-column <?php echo "$css_sort $order" ?>" style="width:80px;">
					<a href="admin.php?page=catablog&amp;sort=menu_order<?php echo $sort_url . $cat_url ?>">
						<span><?php _e("Order", "catablog"); ?></span>
						<span class="sorting-indicator">&nbsp;</span>
					</a>
				</th>
				
				<?php $css_sort = ($sort=='date')? "sorted" : "sortable" ?>
				<?php $sort_url = ($order=='asc')? "&amp;order=desc" : "&amp;order=asc" ?>
				<?php $cat_url  = (isset($_GET['category']))? "&amp;category=".$_GET['category'] : "" ?>
				<th class="manage-column <?php echo "$css_sort $order" ?>" style="width:100px;">
					<a href="admin.php?page=catablog&amp;sort=date<?php echo $sort_url . $cat_url ?>">
						<span><?php _e("Date", "catablog"); ?></span>
						<span class="sorting-indicator">&nbsp;</span>
					</a>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="manage-column column-cb check-column"><input type="checkbox" /></th>
				<th class="manage-column cb_icon_column"><?php _e("Image", "catablog"); ?></th>
				<th class="manage-column"><?php _e("Title", "catablog"); ?></th>
				<?php /*<th class="manage-column"><?php _e("Link", "catablog"); ?></th>*/ ?>
				<th class="manage-column"><?php _e("Description", "catablog"); ?></th>
				<th class="manage-column"><?php _e("Categories", "catablog"); ?></th>
				<?php /*<th class="manage-column"><?php _e("Price", "catablog"); ?></th> */?>
				<?php /*<th class="manage-column"><?php _e("Product Code", "catablog"); ?></th> */?>
				<th class="manage-column"><?php _e("Order", "catablog"); ?></th>
				<th class="manage-column"><?php _e("Date", "catablog"); ?></th>
			</tr>
		</tfoot>
		
		<tbody id="catablog_items">
			
			<?php if (count($results) < 1): ?>
				<tr>
					<td colspan='8'><p>
						<p><?php _e("No catalog items found", 'catablog'); ?></p>

						<?php if ($selected_term !== false): ?>
							<p><?php _e("Use the category drop down above to switch category views.", 'catablog'); ?></p>
						<?php endif ?>
					</p></td>
				</tr>
			<?php endif ?>
			
			<?php foreach ($results as $result): ?>
				<?php $edit   = 'admin.php?page=catablog&amp;id='.$result->getId() ?>
				<?php $remove = 'admin.php?page=catablog-delete&amp;id='.$result->getId() ?>
				
				<tr>
					<th class="check-column">
						<span>&nbsp;<?php //echo $result->getOrder() ?></span>
						<input type="checkbox" class="bulk_selection" name="bulk_action_id" value="<?php echo $result->getId() ?>" />
					</th>
					<td class="cb_icon_column">
						<a href="<?php echo $edit ?>" class="lazyload" rel="<?php echo $this->urls['thumbnails'] . "/" . $result->getImage() ?>">
							<noscript><img src="<?php echo $this->urls['thumbnails'] . "/" . $result->getImage() ?>" class="cb_item_icon" width="50" height="50" alt="" /></noscript>
						</a>
					</td>
					<td>
						<strong><a href="<?php echo $edit ?>" title="Edit CataBlog Item"><?php echo ($result->getTitle()) ?></a></strong>
						<div class="row-actions">
							<span><a href="<?php echo $edit ?>"><?php _e("Edit", "catablog"); ?></a></span>
							<span> | </span>
							<span class="trash"><a href="<?php echo $remove ?>" class="remove_link"><?php _e("Trash", "catablog"); ?></a></span>
						</div>
					</td>
					
					<?php /*<td><?php echo htmlspecialchars($result->getLink(), ENT_QUOTES, 'UTF-8') ?>&nbsp;</td>*/ ?>
					
					<?php $remove_returns = str_replace(array("\r", "\n", "\r\n"), ' ', ($result->getDescription())) ?>
					<?php $description = substr($remove_returns, 0, 120) ?>
					<?php $description .= (mb_strlen($remove_returns) > 120)? '...' : ''; ?>
					
					<td><div class="catablog-list-description"><?php echo $description ?></div></td>
					
					<td><?php echo implode(', ', $result->getCategories())?></td>
					
					<?php /*
					<?php $currency = "" ?>
					<td><?php echo (((float) $result->getPrice()) > 0)? $currency. number_format($result->getPrice(), 2) : "" ?>&nbsp;</td>
					<td><?php echo htmlspecialchars($result->getProductCode(), ENT_QUOTES, 'UTF-8') ?>&nbsp;</td>
					*/ ?>
					
					<td>&nbsp;&nbsp;<?php echo htmlspecialchars($result->getOrder(), ENT_QUOTES, 'UTF-8') ?></td>
					
					<td>
						<span><?php echo str_replace('-', '/', substr($result->getDate(), 0, 10)) ?></span>
						<br />
						<span><?php echo substr($result->getDate(), 11) ?></span>
					</td>
				</tr>
			<?php endforeach; ?>

		</tbody>
	</table>