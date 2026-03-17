/**
 * Editor Lock System - Client Side
 * 
 * Handles:
 * 1. Clearing editor locks when users close/navigate away from the editor
 * 2. Periodically touching the lock to keep it fresh while editing
 * 3. Displaying access request popups for editors
 * 4. Polling for access status when requesting permission
 */

(function($) {
	'use strict';

	// Only run in admin
	if (typeof FanXEditorLock === 'undefined') {
		return;
	}

	const lockKey = FanXEditorLock.lockKey;
	const ajaxUrl = FanXEditorLock.ajaxUrl;
	const nonce = FanXEditorLock.nonce;
	let touchInterval = null;
	let requestCheckInterval = null;
	let pendingRequestsCheckInterval = null;
	let currentRequestId = null;

	// ============================================
	// LOCK MANAGEMENT (Active Editor)
	// ============================================

	/**
	 * Touch (refresh) the editor lock to prevent expiration
	 * This updates the timestamp so the lock stays active while user is editing
	 */
	function touchLock() {
		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'fanx_touch_editor_lock',
				lockKey: lockKey,
				nonce: nonce
			},
			dataType: 'json'
		});
	}

	/**
	 * Clear the editor lock when user closes/leaves the editor
	 */
	function clearLock() {
		// Stop touching the lock
		if (touchInterval) {
			clearInterval(touchInterval);
		}

		// Stop checking for requests
		if (pendingRequestsCheckInterval) {
			clearInterval(pendingRequestsCheckInterval);
		}

		// Send clear request
		navigator.sendBeacon(ajaxUrl, new URLSearchParams({
			action: 'fanx_clear_editor_lock',
			lockKey: lockKey,
			nonce: nonce
		}).toString());
	}

	// ============================================
	// ACCESS REQUEST HANDLING
	// ============================================

	/**
	 * Show access request popup for the active editor
	 */
	function showAccessRequestPopup(requestData) {
		// Create modal overlay
		const overlay = $('<div class="fanx-editor-modal-overlay active"></div>');

		// Create modal
		const modal = $(`
			<div class="fanx-editor-approval-modal">
				<h3>📢 Access Request</h3>
				<div class="fanx-editor-approval-info">
					<strong>${escapeHtml(requestData.requesting_user_name)}</strong>
					is requesting edit access to this item.
				</div>
				<p>Allow them to edit, or deny access for now?</p>
				<div class="fanx-editor-approval-buttons">
					<button type="button" class="button fanx-allow-btn" data-request-id="${requestData.request_id}" data-action="allowed">
						✓ Allow
					</button>
					<button type="button" class="button fanx-deny-btn" data-request-id="${requestData.request_id}" data-action="denied">
						✕ Deny
					</button>
				</div>
			</div>
		`);

		$('body').append(overlay);
		$('body').append(modal);

		// Handle allow/deny buttons
		modal.on('click', 'button[data-action]', function(e) {
			e.preventDefault();
			const requestId = $(this).data('request-id');
			const action = $(this).data('action');
			respondToAccessRequest(requestId, action);
			overlay.remove();
			modal.remove();
		});

		// Allow closing by clicking overlay
		overlay.on('click', function() {
			overlay.remove();
			modal.remove();
		});
	}

	/**
	 * Respond to an access request (as the active editor)
	 */
	function respondToAccessRequest(requestId, action) {
		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'fanx_respond_access_request',
				requestId: requestId,
				action_response: action,
				nonce: nonce
			},
			dataType: 'json',
			success: function(response) {
				// Show brief feedback
				const message = action === 'allowed' 
					? 'Access granted. User can now edit.'
					: 'Access denied. User notified.';
				
				// Could add a flash message here if desired
				console.log('Access request response: ' + message);
			}
		});
	}

	/**
	 * Check for pending access requests (for the active editor)
	 */
	function checkForAccessRequests() {
		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'fanx_get_pending_requests',
				nonce: nonce
			},
			dataType: 'json',
			success: function(response) {
				if (response.success && response.data && response.data.requests) {
					response.data.requests.forEach(function(request) {
						showAccessRequestPopup(request.data);
					});
				}
			}
		});
	}

	// ============================================
	// ACCESS REQUEST STATUS CHECKING (Requester)
	// ============================================

	/**
	 * Request edit access when another user is editing
	 */
	function requestEditAccess() {
		$('#fanx-request-access-btn').prop('disabled', true).text('Requesting...');
		$('#fanx-request-status').show().text('Requesting access...').addClass('pending');

		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'fanx_request_edit_access',
				lockKey: lockKey,
				nonce: nonce
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					currentRequestId = response.data.request_id;
					startCheckingAccessStatus();
				} else {
					$('#fanx-request-status').removeClass('pending').addClass('denied').text('Request failed: ' + response.data.message);
					$('#fanx-request-access-btn').prop('disabled', false).text('Request Edit Access');
				}
			},
			error: function() {
				$('#fanx-request-status').removeClass('pending').addClass('denied').text('Error sending request');
				$('#fanx-request-access-btn').prop('disabled', false).text('Request Edit Access');
			}
		});
	}

	/**
	 * Start polling for access request status
	 */
	function startCheckingAccessStatus() {
		if (requestCheckInterval) {
			clearInterval(requestCheckInterval);
		}

		// Check immediately, then every 2 seconds
		checkAccessStatus();
		requestCheckInterval = setInterval(checkAccessStatus, 2000);

		// Auto-stop after 5 minutes (300 seconds)
		setTimeout(function() {
			if (requestCheckInterval) {
				clearInterval(requestCheckInterval);
				clearAccessStatusDisplay();
			}
		}, 300000);
	}

	/**
	 * Check the status of the access request
	 */
	function checkAccessStatus() {
		if (!currentRequestId) {
			return;
		}

		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: {
				action: 'fanx_check_access_status',
				requestId: currentRequestId,
				nonce: nonce
			},
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					const status = response.data.status;

					if (status === 'allowed') {
						if (requestCheckInterval) {
							clearInterval(requestCheckInterval);
						}
						showAccessApprovedMessage();
					} else if (status === 'denied') {
						if (requestCheckInterval) {
							clearInterval(requestCheckInterval);
						}
						showAccessDeniedMessage();
					}
					// 'pending' - keep waiting
					// 'expired' - auto-closed
				}
			}
		});
	}

	/**
	 * Show access approved modal
	 */
	function showAccessApprovedMessage() {
		const modal = $('#fanx-access-response-modal');
		$('#fanx-access-message').html('<strong>✓ Access Approved!</strong><br>The editor has approved your request. You can now edit this item.');
		$('#fanx-access-buttons').html('<button type="button" class="button button-primary" onclick="location.reload();">Refresh Page</button>');
		modal.addClass('active').show();

		// Auto-reload after 2 seconds
		setTimeout(function() {
			location.reload();
		}, 2000);
	}

	/**
	 * Show access denied modal
	 */
	function showAccessDeniedMessage() {
		const modal = $('#fanx-access-response-modal');
		$('#fanx-access-message').html('<strong>✕ Access Denied</strong><br>The editor declined your request. Please try again later.');
		$('#fanx-access-buttons').html('<button type="button" class="button" onclick="jQuery(\'#fanx-access-response-modal\').hide();">Done</button>');
		modal.addClass('active').show();

		// Reset request
		clearAccessStatusDisplay();
	}

	/**
	 * Clear access request display
	 */
	function clearAccessStatusDisplay() {
		if (requestCheckInterval) {
			clearInterval(requestCheckInterval);
		}
		$('#fanx-request-status').removeClass('pending allowed denied').hide().text('');
		$('#fanx-request-access-btn').prop('disabled', false).text('Request Edit Access');
		currentRequestId = null;
	}

	// ============================================
	// INITIALIZATION
	// ============================================

	/**
	 * Escape HTML to prevent XSS
	 */
	function escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	// Initialize lock system when document is ready
	$(document).ready(function() {
		// Touch the lock immediately (if we're the active editor)
		touchLock();

		// Re-touch the lock every 30 seconds to keep it fresh
		// (timeout is 10 minutes, so we update well before expiration)
		touchInterval = setInterval(function() {
			touchLock();
		}, 30000); // 30 seconds

		// Check for access requests every 5 seconds (for active editors)
		pendingRequestsCheckInterval = setInterval(checkForAccessRequests, 5000);

		// Clear lock when page unloads
		$(window).on('beforeunload', function() {
			clearLock();
		});

		// Handle "Request Edit Access" button click
		$(document).on('click', '#fanx-request-access-btn', function(e) {
			e.preventDefault();
			requestEditAccess();
		});

		// Monitor for form submission to keep lock even on save
		$('form').on('submit', function() {
			// Keep touching while form is submitting
			touchLock();
		});

		// Handle Gutenberg editor block updates
		if (typeof wp !== 'undefined' && wp.data) {
			// Touch lock when post content changes
			wp.data.subscribe(function() {
				touchLock();
			});
		}
	});

})(jQuery);
