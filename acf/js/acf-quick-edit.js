(function($) {
	'use strict';
	
	$(function() {
		// When quick edit form opens, populate ACF fields from the table column
		if (typeof inlineEditPost !== 'undefined') {
			var originalEdit = inlineEditPost.edit;
			inlineEditPost.edit = function(id) {
				// Call the original edit - this creates the form
				var result = originalEdit.apply(this, arguments);
				
				// Wait a bit for the form to be rendered, then populate our fields
				setTimeout(function() {
					try {
						var postId = parseInt(id);
						var $row = $('#post-' + postId);
						var $editRow = $('#edit-' + postId);
						
						if ($row.length && $editRow.length) {
							// Try to get the value from the table column
							var columnSelector = '.column-info_display_order';
							var $column = $row.find(columnSelector);
							
							if ($column.length) {
								var columnValue = $.trim($column.text());
								
								// Skip if it's just a dash or empty
								if (columnValue && columnValue !== '—' && columnValue !== '-') {
									// Set the value in the edit form
									var $field = $editRow.find('[name="info_display_order"]');
									if ($field.length) {
										$field.val(columnValue);
										console.log('Populated field with value: ' + columnValue);
									}
								}
							}
						}
					} catch (e) {
						console.error('Error populating quick edit field:', e);
					}
				}, 100);
				
				return result;
			};
		}
	});
})(jQuery);
