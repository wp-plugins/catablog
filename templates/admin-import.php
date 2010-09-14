<div class="wrap">
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Import</h2>
	
	<form class="catablog-form">
		<fieldset>
			<legend>Import Console</legend>
			<ul id="catablog-import-messages">
				<?php $this->load_xml_to_database($xml_object) ?>
			</ul>
		</fieldset>
	</form>
	
	
	
</div>