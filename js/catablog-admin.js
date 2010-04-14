jQuery(document).ready(function() {
	
	// confirm delete links
	jQuery('a.remove_link').bind('click', function(e) {
		if (confirm('Are you sure you want to delete this catablog item?')) {
			return true;
		}
		
		return false;
	});
	
	
});