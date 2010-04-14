<div class="wrap">
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2><?php echo ($update)? 'Edit CataBlog Entry' : 'Add New CataBlog Entry' ?></h2>
	
	<form method="post" action="" enctype="multipart/form-data">
		<label for="image">Image:</label><br />
		<img src="<?php echo get_bloginfo('wpurl').'/wp-content/catablog/'.$result['image'] ?>" width="150" height="150" /><br />
		<input type="file" name="image" id="image" size="20" />
		<br /><br />
		
		<label for="order">Order:</label><br />
		<input type="text" name="order" id="order" size="5" value="<?php echo htmlspecialchars($result['order'], ENT_QUOTES, 'UTF-8') ?>" />
		<br /><br />
		
		<label for="title">Title:</label><br />
		<input type="text" name="title" id="title" size="50" value="<?php echo htmlspecialchars($result['title'], ENT_QUOTES, 'UTF-8') ?>" />
		<br /><br />
		
		<label for="title">Link:</label><br />
		<input type="text" name="link" id="link" size="50" value="<?php echo htmlspecialchars($result['link'], ENT_QUOTES, 'UTF-8') ?>" />
		<br /><br />
		
		<label for="description">Description</label><br />
		<textarea name="description" id="description" cols="45" rows="6"><?php echo htmlspecialchars($result['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
		
		<input type="hidden" id="save" name="save" value="yes" />
		<?php if (isset($_REQUEST['id'])): ?>
			<input type="hidden" id="id" name="id" value="<?php echo $result['id']?>" />
		<?php endif ?>

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			<span> or <a href="<?php echo get_bloginfo('wpurl').'/wp-admin/admin.php?page=catablog/catablog.php' ?>">back to list</a></span>
		</p>
	</form>
</div>