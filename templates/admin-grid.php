
		<ul id="catablog_items" class="catablog-grid-view">
			<?php if (count($results) < 1): ?>
				<p>No CataBlog Items Found.</p>
			<?php endif ?>
			<?php foreach ($results as $result): ?>
				<?php $edit   = get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog-edit&amp;id='.$result->getId() ?>
				<?php $remove = get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog-delete&amp;id='.$result->getId() ?>
				<li>
					<a href="<?php echo $edit ?>" rel="<?php echo $this->urls['thumbnails'] . "/" . $result->getImage() ?>" class="catablog-admin-thumbnail lazyload">
						<noscript><img src="<?php echo $this->urls['thumbnails'] . "/" . $result->getImage() ?>" class="catablog-grid-thumbnail" width="100" height="100" alt="" /></noscript>
					</a>
					
					<a href="<?php echo $edit ?>" class="catablog-title"><small>
							<?php $title = (mb_strlen($result->getTitle()) > 30)? trim(mb_substr($result->getTitle(), 0, 30)).'...' : $result->getTitle() ?>
							<?php echo htmlentities($title, ENT_QUOTES, 'UTF-8') ?>
					</small></a>
					
					<input type="checkbox" class="bulk_selection" name="bulk_action_id" value="<?php echo $result->getId() ?>" />
					
				</li>
			<?php endforeach; ?>
		</ul>