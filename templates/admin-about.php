<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2><?php _e("About CataBlog", 'catablog'); ?></h2>
	
	<p>
		<a href="http://catablog.illproductions.com/" target="_blank">CataBlog</a> is written by 
		<a href="http://catablog.illproductions.com/about-author/" target="_blank">Zachary Segal</a> in his spare time. 
		It is a cataloging tool for <a href="http://wordpress.org" target="_blank">WordPress</a>
		that allows you to easily manage a list of items with automatically generated thumbnail images.
		Use of CataBlog is completely free, even commercial sites for now, all that I ask is
		that you rate the plugin at the <a href="http://wordpress.org/extend/plugins/catablog/" target="_blank">WordPress Plugin Repository</a>.
	</p>
	
	<p>
		<strong><?php _e("CSS Modification:", 'catablog'); ?></strong><?php _e("You can always override CataBlog's CSS settings to create custom looks.
		If you make a catablog.css file in your active theme's directory it will be automatically loaded and applied.
 		This makes it easy to prepare your custom theme for CataBlog integration and will also protect your customization
		for future version to come.", 'catablog'); ?> 
	</p>
	
	<table class="catablog_stats wide" cellspacing="5">
		<thead>
			<tr><td colspan="2"><h3><strong><?php _e("Server Statistics", 'catablog'); ?></strong></h3></td></tr>
		</thead>
		<tbody>
		<?php foreach ($stats as $label => $value): ?>
			<tr>
				<td><strong><?php echo str_replace("_", " ", $label) . ":" ?></strong></td>
				<td><?php echo $value ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>

</div><?php _e("", 'catablog'); ?>