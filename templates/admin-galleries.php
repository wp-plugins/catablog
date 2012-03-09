<div class="wrap">

		<div id="icon-catablog" class="icon32"><br /></div>
		<h2>
			<span><?php _e("CataBlog Galleries", 'catablog'); ?></span>
			<a href="#catablog-gallery-create-form" class="button add-new-h2" id="catablog-new-gallery-button"><?php _e("Add New", 'catablog'); ?></a>
			
		</h2>
		
		<?php $this->render_catablog_admin_message() ?>
		
		<div class="tablenav">&nbsp;</div>
		
		<table class="widefat post" cellspacing="0">
			<thead>
				<tr>
					<th class="column-cb check-column"><input type="checkbox" /></th>
					
					<?php $css_sort = ($sort=='title')? "sorted" : "sortable" ?>
					<?php $sort_url = ($order=='asc')? "&amp;order=desc" : "&amp;order=asc" ?>
					<?php $cat_url  = (isset($_GET['category']))? "&amp;category=".$_GET['category'] : "" ?>
					<th class="<?php echo "$css_sort $order" ?>" style="width:120px;">
						<a href="admin.php?page=catablog-galleries&amp;sort=title<?php echo $sort_url . $cat_url ?>">
							<span><?php _e("Title", "catablog"); ?></span>
							<span class="sorting-indicator">&nbsp;</span>
						</a>
					</th>
					
					<th class="column-description"><?php _e("Description", "catablog"); ?></th>
					<th><?php _e("Size", "catablog"); ?></th>
					<th><?php _e("Shortcode", "catablog"); ?></th>
					
					<?php $css_sort = ($sort=='date')? "sorted" : "sortable" ?>
					<?php $sort_url = ($order=='asc')? "&amp;order=desc" : "&amp;order=asc" ?>
					<?php $cat_url  = (isset($_GET['category']))? "&amp;category=".$_GET['category'] : "" ?>
					<th class="column-date <?php echo "$css_sort $order" ?> <?php echo $date_col_class ?>" style="width:100px;">
						<a href="admin.php?page=catablog-galleries&amp;sort=date<?php echo $sort_url . $cat_url ?>">
							<span><?php _e("Date", "catablog"); ?></span>
							<span class="sorting-indicator">&nbsp;</span>
						</a>
					</th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th class="column-cb check-column"><input type="checkbox" /></th>
					<th class=""><?php _e("Title", "catablog"); ?></th>
					<th class="column-description"><?php _e("Description", "catablog"); ?></th>
					<th><?php _e("Size", "catablog"); ?></th>
					<th><?php _e("Shortcode", "catablog"); ?></th>
					<th class="column-date <?php echo $date_col_class ?>"><?php _e("Date", "catablog"); ?></th>
				</tr>
			</tfoot>

			<tbody id="catablog_items">

				<?php if (count($galleries) < 1): ?>
					<tr>
						<td colspan='8'><p>
							<p><?php _e("No CataBlog Galleries found", 'catablog'); ?></p>
						</td>
					</tr>
				<?php endif ?>

				<?php foreach ($galleries as $gallery): ?>
					<?php $edit   = 'admin.php?page=catablog-gallery-edit&amp;id='.$gallery->getId() ?>
					<?php $remove = wp_nonce_url(('admin.php?page=catablog-gallery-delete&amp;id='.$gallery->getId()), "catablog-gallery-delete") ?>
					
					<tr>
						<th class="check-column">
							<input type="checkbox" class="bulk_selection" name="bulk_action_id" value="<?php echo $gallery->getId() ?>" />
						</th>
						<td>
							<strong><a href="<?php echo $edit ?>" title="Edit CataBlog Item"><?php echo ($gallery->getTitle()) ?></a></strong>
							<div class="row-actions">
								<span><a href="<?php echo $edit ?>"><?php _e("Edit", "catablog"); ?></a></span>
								<span> | </span>
								<span class="trash"><a href="<?php echo $remove ?>" class="remove_link"><?php _e("Delete", "catablog"); ?></a></span>
							</div>
						</td>
						<td class="column-description <?php echo $description_col_class ?>"><?php echo $gallery->getDescription() ?></td>
						<td><?php echo count($gallery->getItemIds()); ?></td>
						<td><input type="text" value='[catablog_gallery id="<?php echo $gallery->getId(); ?>"]' readonly="readonly" /></td>
						<td class="column-date <?php echo $date_col_class ?>">
							<span><?php echo str_replace('-', '/', substr($gallery->getDate(), 0, 10)) ?></span>
							<br />
							<span><?php echo substr($gallery->getDate(), 11) ?></span>
						</td>
					</tr>
				<?php endforeach; ?>

			</tbody>
			
		</table>
		
		<form id="catablog-gallery-create-form" action="admin.php?page=catablog-gallery-create" method="post">
			<h3><?php _e("Create New Gallery", "catablog"); ?></h3>
			<p>
				<label for="catablog-gallery-title"><?php _e("Title", "catablog"); ?></label><br />
				<input type="text" id="catablog-gallery-title" name="title" value="" />
			</p>
			
			<p>
				<label for="catablog-gallery-description"><?php _e("Description", "catablog"); ?></label><br />
				<input type="text" id="catablog-gallery-description" name="description" value="" />
			</p>
			
			<?php wp_nonce_field( 'catablog_create_gallery', '_catablog_create_gallery_nonce', false, true ) ?>
			<input type="submit" id="catablog-gallery-create" name="save" class="button-primary" value="<?php _e('Create Gallery', 'catablog') ?>" />
		</form>
</div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		
		// BIND TRASH CATALOG ITEM WARNING
		$('.remove_link').bind('click', function(event) {
			return (confirm('<?php _e("Are you sure you want to permanently delete this gallery?", "catablog"); ?>'));
		});
		
		// BIND AUTO SELECT FOR SHORTCODE VALUE
		$('#catablog_items input').click(function(event) {
			this.focus();
			this.select();
		});
		
		// FLASH THE NEW FORM RED WHEN YOU CLICK THE NEW BUTTON
		$('#catablog-gallery-create-form').append('<div class="catablog-red-curtain">&nbsp;</div>');
		$('#catablog-new-gallery-button').click(function(event) {
			$('#catablog-gallery-create-form .catablog-red-curtain').css({display:'block'}).fadeOut(800);
		});
	});
</script>