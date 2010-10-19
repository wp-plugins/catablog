<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>About CataBlog</h2>
	
	<p>
		<a href="http://catablog.illproductions.net/" target="_blank">CataBlog</a> is written by 
		<a href="http://catablog.illproductions.net/about-author/" target="_blank">Zachary Segal</a> in his spare time. 
		It is a cataloging tool for <a href="http://wordpress.org" target="_blank">WordPress</a>
		that allows you to easily manage a list of items with automatically generated thumbnail images.
		Use of CataBlog is completely free, even commercial sites for now, all that I ask is
		that you rate the plugin at the <a href="http://wordpress.org/extend/plugins/catablog/" target="_blank">WordPress Plugin Repository</a>.
	</p>
	
	<p>
		<strong>CSS Modification:</strong> You can always override CataBlog's CSS settings to create custom looks.
		If you make a catablog.css file in your active theme's directory it will be automatically loaded and applied.
 		This makes it easy to prepare your custom theme for CataBlog integration and will also protect your customization
		for future version to come.
	</p>
	
	<table class="catablog_stats wide" cellspacing="5">
		<thead>
			<tr><td colspan="2"><h3><strong>Server Statistics</strong></h3></td></tr>
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
	
	<br /><br />
	
	<p>
		<a href="#reset" class="button" id="button-reset">Reset</a>
		<small>Reset your entire catalog, deleting all photos and custom data permanently. Sometimes you can use this to fix an improper install.</small>
	</p>
	
</div>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#button-reset').click(function() {
			if (!confirm("Are you sure you want to reset CataBlog and delete all your data?")) {
				return false;
			}
			
			var params = { 'action':'catablog_reset', 'security':'<?php echo wp_create_nonce("catablog-reset") ?>' }
			$.post(ajaxurl, params, function(data) {
				$('#button-reset').blur();
				alert('You have successfully removed all data pertaining to CataBlog');
				window.location.reload();
			});
			
			return false;	
			
		});
	});
</script>