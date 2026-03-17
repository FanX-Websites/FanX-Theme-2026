/**
 * ACF Export Scheduler Button Actions
 * Uses ACF Extended's built-in button AJAX hooks with click listener fallback
 */

(function($) {
	'use strict';

	console.log('✓ ACF Export Scheduler script loaded');

	// Function to show message near the button
	function showFieldMessage($el, message, type) {
		var $message = $('<div class="acf-export-message" style="margin-top: 10px; padding: 12px; border-radius: 4px; font-size: 13px; font-weight: 500;"></div>');
		
		if (type === 'success') {
			$message.css({
				'background-color': '#d4edda',
				'color': '#155724',
				'border': '1px solid #c3e6cb'
			});
		} else if (type === 'error') {
			$message.css({
				'background-color': '#f8d7da',
				'color': '#721c24',
				'border': '1px solid #f5c6cb'
			});
		}
		
		$message.text(message);
		
		// Remove any existing messages
		$el.closest('.acf-field').find('.acf-export-message').remove();
		
		// Append message below the button
		$el.after($message);
		
		console.log('✓ Message shown:', message);
		
		// Auto-remove after 4 seconds
		setTimeout(function() {
			$message.fadeOut(function() {
				$(this).remove();
			});
		}, 4000);
	}

	// FALLBACK: Direct click listeners (in case ACF Extended hooks don't fire)
	$(document).on('click', '[data-name="schedule_export"] button', function(e) {
		console.log('✓ Schedule button CLICK detected (fallback)');
		
		var $datetimeField = $('[data-name="export_datetime"]');
		var datetimeValue = $datetimeField.find('input').val();
		
		console.log('✓ Datetime value:', datetimeValue);
		
		// Validate datetime is set
		if (!datetimeValue || datetimeValue.trim() === '') {
			console.log('✗ Datetime not set');
			showFieldMessage($(this), 'Please set an export date/time before scheduling.', 'error');
			return false;
		}
		
		console.log('✓ Schedule validation passed');
	});

	$(document).on('click', '[data-name="cancel_export"] button', function(e) {
		console.log('✓ Cancel button CLICK detected (fallback)');
		
		var confirmed = confirm('Are you sure you want to cancel the scheduled export?');
		if (!confirmed) {
			console.log('✗ User cancelled');
			return false;
		}
		
		console.log('✓ Cancel confirmed');
	});

	// ACF EXTENDED HOOKS: These fire if Ajax call is enabled on the button fields
	
	// Schedule Export button - before AJAX
	acf.addAction('acfe/fields/button/before/name=schedule_export', function($el, data) {
		console.log('✓ Schedule button ACF hook fired, data:', data);
		
		// Get datetime value
		var $datetimeField = $('[data-name="export_datetime"]');
		var datetimeValue = $datetimeField.find('input').val();
		
		console.log('✓ Datetime value:', datetimeValue);
		
		// Validate datetime is set
		if (!datetimeValue || datetimeValue.trim() === '') {
			console.log('✗ Datetime not set, preventing AJAX');
			showFieldMessage($el, 'Please set an export date/time before scheduling.', 'error');
			// Prevent AJAX by returning false
			return false;
		}
		
		// Store datetime for success message
		$el.data('scheduledTime', datetimeValue);
		console.log('✓ Validation passed, proceeding with AJAX');
	});

	// Schedule Export button - success
	acf.addAction('acfe/fields/button/success/name=schedule_export', function(response, $el, data) {
		console.log('✓ Schedule export AJAX success, response:', response);
		
		var scheduledTime = $el.data('scheduledTime');
		var message = 'Export scheduled for: ' + scheduledTime;
		
		if (response.data && response.data.message) {
			message = response.data.message;
		}
		
		showFieldMessage($el, message, 'success');
	});

	// Cancel Export button - before AJAX
	acf.addAction('acfe/fields/button/before/name=cancel_export', function($el, data) {
		console.log('✓ Cancel button ACF hook fired, data:', data);
		
		// Show confirmation dialog
		var confirmed = confirm('Are you sure you want to cancel the scheduled export?');
		
		if (!confirmed) {
			console.log('✗ Cancellation cancelled by user');
			// Return false to prevent AJAX
			return false;
		}
		
		console.log('✓ User confirmed, proceeding with AJAX');
	});

	// Cancel Export button - success
	acf.addAction('acfe/fields/button/success/name=cancel_export', function(response, $el, data) {
		console.log('✓ Cancel export AJAX success, response:', response);
		
		var message = 'Export cancellation scheduled.';
		
		if (response.data && response.data.message) {
			message = response.data.message;
		}
		
		showFieldMessage($el, message, 'success');
	});

})(jQuery);

