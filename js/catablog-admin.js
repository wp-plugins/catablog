jQuery(document).ready(function() {
	
	// confirm delete links
	jQuery('a.remove_link').bind('click', function(e) {
		if (confirm('Are you sure you want to delete this catablog item?')) {
			return true;
		}
		
		return false;
	});
	
	
	// reorder items
	jQuery('#catablog_items tbody').sortable({
		containment: 'parent',
		handle: 'div.handle',
		stop: function(event, ui) {
			alert(ui);
		}
	});
	jQuery('#catablog_items tbody').disableSelection();
	
});