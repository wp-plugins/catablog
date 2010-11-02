<div class="wrap">
	
	<div id="icon-catablog" class="icon32"><br /></div>
	<h2>CataBlog Import Results</h2>
	
	<h3><strong>Import Console</strong></h3>

	<?php if ($error): ?>
		<p>
			You must select a XML file to be used for the import, 
			please press back on your browser and resend the form
			with a XML file.
		</p>
	<?php endif ?>

	<ul id="catablog-import-messages">
		<?php if ($error === false): ?>
			<?php $this->load_xml_to_database($xml_object) ?>
		<?php endif ?>
	</ul>	
	
	
</div>