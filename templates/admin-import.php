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
			Once you have fixed your file and its format <a href="admin.php?page=catablog-options#import">please try again</a>.
		</p>
	<?php else: ?>
		<ul id="catablog-import-messages">
			<?php if (isset($_REQUEST['catablog_clear_db'])): ?>
				
				<li class="updated"><em>removing catalog items...</em></li>
				<?php $items = CataBlogItem::getItems(false, 0, -1, true) ?>
				<?php foreach ($items as $item): ?>
					<?php $item->delete(false) ?>
				<?php endforeach ?>
				<li class="updated">Success: <em>All</em> catalog items removed successfully</li>
				
				<li class="updated"><em>removing catalog categories...</em></li>
				<?php $this->remove_terms() ?>
				<li class="updated">Success: <em>All</em> catalog categories removed successfully</li>
				
				<li class="updated"><strong>DataBase Cleared Successfully</strong></li>
				<li>&nbsp;</li>
			<?php endif ?>
			
			<?php $this->load_array_to_database($data) ?>
		</ul>	
	<?php endif ?>	
	
</div>