<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Import Results</h2>
	
	<h3><strong>Import Console</strong></h3>

	<?php if ($error): ?>
		<p>
			You must select a <strong>valid XML or CSV file</strong> to be used for import.
		</p>
		<p>
			You may also read more about 
			<a href="http://catablog.illproductions.com/documentation/importing-and-exporting-catalogs/" target="_blank">importing and exporting data from CataBlog</a>.
			<br />
			Once you have fixed your file and its format <a href="/wp-admin/admin.php?page=catablog-options#import">please try again</a>.
		</p>
	<?php endif ?>

	<ul id="catablog-import-messages">
		<?php if ($error === false): ?>
			<?php $this->load_array_to_database($data) ?>
		<?php endif ?>
	</ul>	
	
	
</div>