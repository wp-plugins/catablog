<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>Storage Space Full!</h2>
	
	<p>
		<strong>CataBlog</strong> can't make a new entry because your site has
		<strong class="error">run out of storage space.</strong>
	</p>
	<p>
		You are currently using <?php echo round((get_dirsize(BLOGUPLOADDIR) / 1024 / 1024), 2) ?>MB of <?php echo get_space_allowed() ?>MB of storage space.
	</p>
	<p>
		Please talk to your WordPress Administrator to
		have more space allocated to your site or delete some previous uploaded
		content.
	</p>
	<ul>
		<li><strong>Go To:</strong></li>
		<li><a href="index.php">Dashboard</a></li>
		<li><a href="upload.php">Media</a></li>
		<li><a href="admin.php?page=catablog">CataBlog</a></li>
	</ul>
</div>