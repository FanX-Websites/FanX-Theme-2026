<?php 
/**
 * Post Rules - Editor Locking System
 * 
 * Prevents multiple users from editing the same post/page/taxonomy/ACF field group
 * simultaneously by showing notifications and tracking active editors.
 */

if (!defined('ABSPATH')) {
	exit;
}

// ==========================================
// CONSTANTS & CONFIGURATION
// ==========================================

const EDITOR_LOCK_TIMEOUT = 10 * 60; // 10 minutes in seconds
const EDITOR_LOCK_OPTION_PREFIX = 'fanx_editor_lock_';
const EDITOR_ACCESS_REQUEST_PREFIX = 'fanx_access_request_';
const ACCESS_REQUEST_TIMEOUT = 5 * 60; // 5 minutes in seconds

// ==========================================
// REGISTER EDITOR LOCKS
// ==========================================

/**
 * Register when a user opens a post/page/taxonomy/ACF for editing
 */
function fanx_register_editor_lock() {
	if (!is_admin()) {
		return;
	}

	$screen = get_current_screen();
	if (!$screen) {
		return;
	}

	// Determine if we're editing something and what type
	$lock_key = null;

	// Posts and pages
	if (in_array($screen->base, ['post', 'edit'])) {
		$post_id = isset($_GET['post']) ? intval($_GET['post']) : null;
		if ($post_id) {
			$post = get_post($post_id);
			if ($post) {
				$lock_key = 'post_' . $post_id;
			}
		}
	}

	// Terms / Taxonomies
	if (in_array($screen->base, ['edit-tags', 'term'])) {
		$tag_id = isset($_GET['tag_ID']) ? intval($_GET['tag_ID']) : null;
		if ($tag_id) {
			$lock_key = 'term_' . $tag_id;
		}
	}

	// ACF Field Groups
	if (function_exists('acf_get_field_group') && $screen->base === 'acf-field-group') {
		$fg_id = isset($_GET['post']) ? intval($_GET['post']) : null;
		if ($fg_id) {
			$lock_key = 'acf_fg_' . $fg_id;
		}
	}

	// If we have a lock key, register this user as editing
	if ($lock_key) {
		fanx_set_editor_lock($lock_key);

		// Enqueue script to clear lock on page unload
		wp_enqueue_script(
			'fanx-editor-lock',
			get_template_directory_uri() . '/admin/js/editor-lock.js',
			['jquery'],
			wp_get_theme()->get('Version'),
			true
		);

		wp_localize_script('fanx-editor-lock', 'FanXEditorLock', [
			'lockKey' => $lock_key,
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('fanx_editor_lock_nonce'),
		]);
	}
}
add_action('admin_init', 'fanx_register_editor_lock');

// ==========================================
// LOCK MANAGEMENT FUNCTIONS
// ==========================================

/**
 * Set or update an editor lock
 */
function fanx_set_editor_lock($lock_key) {
	$current_user = wp_get_current_user();
	if (!$current_user->ID) {
		return false;
	}

	$option_key = EDITOR_LOCK_OPTION_PREFIX . $lock_key;
	$lock_data = [
		'user_id' => $current_user->ID,
		'user_name' => $current_user->display_name,
		'timestamp' => current_time('timestamp'),
	];

	return update_option($option_key, $lock_data);
}

/**
 * Get lock information for a resource
 */
function fanx_get_editor_lock($lock_key) {
	$option_key = EDITOR_LOCK_OPTION_PREFIX . $lock_key;
	$lock_data = get_option($option_key);

	if (!$lock_data) {
		return false;
	}

	// Check if lock has expired
	$elapsed = current_time('timestamp') - $lock_data['timestamp'];
	if ($elapsed > EDITOR_LOCK_TIMEOUT) {
		delete_option($option_key);
		return false;
	}

	return $lock_data;
}

/**
 * Clear an editor lock
 */
function fanx_clear_editor_lock($lock_key) {
	$option_key = EDITOR_LOCK_OPTION_PREFIX . $lock_key;
	return delete_option($option_key);
}

/**
 * Check if another user is editing this resource
 */
function fanx_has_other_editor($lock_key) {
	$lock_data = fanx_get_editor_lock($lock_key);

	if (!$lock_data) {
		return false;
	}

	$current_user = wp_get_current_user();
	return (int) $lock_data['user_id'] !== (int) $current_user->ID;
}

// ==========================================
// ACCESS REQUEST MANAGEMENT
// ==========================================

/**
 * Create an access request from a user trying to edit a locked resource
 */
function fanx_create_access_request($lock_key) {
	$requesting_user = wp_get_current_user();
	$lock_data = fanx_get_editor_lock($lock_key);

	if (!$lock_data) {
		return false;
	}

	$request_id = wp_hash($lock_key . $requesting_user->ID . current_time('timestamp'));
	$option_key = EDITOR_ACCESS_REQUEST_PREFIX . $request_id;

	$request_data = [
		'requesting_user_id' => $requesting_user->ID,
		'requesting_user_name' => $requesting_user->display_name,
		'lock_key' => $lock_key,
		'editor_user_id' => $lock_data['user_id'],
		'editor_user_name' => $lock_data['user_name'],
		'status' => 'pending', // pending, allowed, denied
		'created' => current_time('timestamp'),
	];

	update_option($option_key, $request_data);

	return $request_id;
}

/**
 * Get an access request
 */
function fanx_get_access_request($request_id) {
	$option_key = EDITOR_ACCESS_REQUEST_PREFIX . $request_id;
	$request_data = get_option($option_key);

	if (!$request_data) {
		return false;
	}

	// Check if request has expired
	$elapsed = current_time('timestamp') - $request_data['created'];
	if ($elapsed > ACCESS_REQUEST_TIMEOUT) {
		delete_option($option_key);
		return false;
	}

	return $request_data;
}

/**
 * Get pending access requests for the current user (as an editor)
 */
function fanx_get_pending_requests_for_user($user_id = null) {
	if ($user_id === null) {
		$user_id = get_current_user_id();
	}

	global $wpdb;
	$prefix = EDITOR_ACCESS_REQUEST_PREFIX;
	$now = current_time('timestamp');
	$timeout = ACCESS_REQUEST_TIMEOUT;

	$requests = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE %s",
			$wpdb->esc_like($prefix) . '%'
		)
	);

	$pending = [];

	if ($requests) {
		foreach ($requests as $request) {
			$request_data = maybe_unserialize($request->option_value);

			if (!is_array($request_data)) {
				continue;
			}

			// Filter for requests to this user
			if ((int) $request_data['editor_user_id'] !== (int) $user_id) {
				continue;
			}

			// Check if expired
			$elapsed = $now - $request_data['created'];
			if ($elapsed > $timeout) {
				delete_option($request->option_name);
				continue;
			}

			// Only include pending requests
			if ($request_data['status'] === 'pending') {
				$pending[] = [
					'request_id' => str_replace($prefix, '', $request->option_name),
					'data' => $request_data,
				];
			}
		}
	}

	return $pending;
}

/**
 * Respond to an access request
 */
function fanx_respond_to_access_request($request_id, $action) {
	if (!in_array($action, ['allowed', 'denied'])) {
		return false;
	}

	$option_key = EDITOR_ACCESS_REQUEST_PREFIX . $request_id;
	$request_data = get_option($option_key);

	if (!$request_data) {
		return false;
	}

	// Verify current user is the editor
	$current_user = wp_get_current_user();
	if ((int) $request_data['editor_user_id'] !== (int) $current_user->ID) {
		return false;
	}

	$request_data['status'] = $action;
	$request_data['response_time'] = current_time('timestamp');

	update_option($option_key, $request_data);

	// If allowing, clear the lock so user can edit
	if ($action === 'allowed') {
		fanx_clear_editor_lock($request_data['lock_key']);
	}

	return true;
}

/**
 * Check if user has been allowed to edit
 */
function fanx_check_access_request($request_id) {
	$request_data = fanx_get_access_request($request_id);

	if (!$request_data) {
		return null; // Expired or doesn't exist
	}

	return $request_data['status']; // 'pending', 'allowed', or 'denied'
}

// ==========================================
// ADMIN NOTICES
// ==========================================

/**
 * Display notice if another user is editing this post
 */
function fanx_show_editor_notice() {
	if (!is_admin()) {
		return;
	}

	$screen = get_current_screen();
	if (!$screen) {
		return;
	}

	$lock_key = null;
	$item_type = '';
	$item_name = '';

	// Posts and pages
	if (in_array($screen->base, ['post', 'edit'])) {
		$post_id = isset($_GET['post']) ? intval($_GET['post']) : null;
		if ($post_id) {
			$post = get_post($post_id);
			if ($post) {
				$lock_key = 'post_' . $post_id;
				$item_type = 'post';
				$item_name = $post->post_title ?: 'Untitled';
			}
		}
	}

	// Terms / Taxonomies
	if (in_array($screen->base, ['edit-tags', 'term'])) {
		$tag_id = isset($_GET['tag_ID']) ? intval($_GET['tag_ID']) : null;
		if ($tag_id) {
			$term = get_term($tag_id);
			if ($term) {
				$lock_key = 'term_' . $tag_id;
				$item_type = 'term';
				$item_name = $term->name ?: 'Untitled';
			}
		}
	}

	// ACF Field Groups
	if (function_exists('acf_get_field_group') && $screen->base === 'acf-field-group') {
		$fg_id = isset($_GET['post']) ? intval($_GET['post']) : null;
		if ($fg_id) {
			$lock_key = 'acf_fg_' . $fg_id;
			$item_type = 'ACF Field Group';
			$item_name = get_the_title($fg_id) ?: 'Untitled';
		}
	}

	if (!$lock_key) {
		return;
	}

	// Check for other editors
	if (fanx_has_other_editor($lock_key)) {
		$lock_data = fanx_get_editor_lock($lock_key);
		if ($lock_data) {
			$time_since = current_time('timestamp') - $lock_data['timestamp'];
			$time_label = fanx_format_time_diff($time_since);

			?>
			<div class="notice notice-warning is-dismissible" id="fanx-editor-conflict-notice">
				<p>
					<strong>⚠️ Active Editor Detected</strong><br>
					<strong><?php echo esc_html($lock_data['user_name']); ?></strong> is currently editing this <?php echo esc_html($item_type); ?> (for <?php echo esc_html($time_label); ?>).
				</p>
				<p>
					<button type="button" class="button button-primary" id="fanx-request-access-btn" data-lock-key="<?php echo esc_attr($lock_key); ?>">
						Request Edit Access
					</button>
					<span id="fanx-request-status" style="margin-left: 10px; display: none;"></span>
				</p>
			</div>

			<div id="fanx-access-response-modal" class="fanx-access-modal" style="display: none;">
				<div class="fanx-modal-content">
					<h3>Access Request Status</h3>
					<p id="fanx-access-message"></p>
					<div id="fanx-access-buttons" style="margin-top: 15px;"></div>
				</div>
			</div>

			<?php
		}
	}
}
add_action('admin_notices', 'fanx_show_editor_notice');

// ==========================================
// AJAX HANDLERS
// ==========================================

/**
 * AJAX endpoint to clear lock when user closes editor
 */
function fanx_clear_editor_lock_ajax() {
	check_ajax_referer('fanx_editor_lock_nonce', 'nonce');

	$lock_key = isset($_POST['lockKey']) ? sanitize_text_field($_POST['lockKey']) : '';

	if ($lock_key) {
		fanx_clear_editor_lock($lock_key);
	}

	wp_send_json_success(['cleared' => true]);
}
add_action('wp_ajax_fanx_clear_editor_lock', 'fanx_clear_editor_lock_ajax');

/**
 * AJAX endpoint to touch (update timestamp) the editor lock
 */
function fanx_touch_editor_lock_ajax() {
	check_ajax_referer('fanx_editor_lock_nonce', 'nonce');

	$lock_key = isset($_POST['lockKey']) ? sanitize_text_field($_POST['lockKey']) : '';

	if ($lock_key) {
		fanx_set_editor_lock($lock_key);
	}

	wp_send_json_success(['touched' => true]);
}
add_action('wp_ajax_fanx_touch_editor_lock', 'fanx_touch_editor_lock_ajax');

/**
 * AJAX endpoint to request edit access when another user is editing
 */
function fanx_request_edit_access_ajax() {
	check_ajax_referer('fanx_editor_lock_nonce', 'nonce');

	$lock_key = isset($_POST['lockKey']) ? sanitize_text_field($_POST['lockKey']) : '';

	if (!$lock_key) {
		wp_send_json_error(['message' => 'Invalid lock key']);
	}

	// Check if another user is actually editing
	if (!fanx_has_other_editor($lock_key)) {
		wp_send_json_error(['message' => 'No active editor']);
	}

	// Create the access request
	$request_id = fanx_create_access_request($lock_key);

	if ($request_id) {
		// Log to activity feed if available
		if (function_exists('do_action')) {
			$request_data = fanx_get_access_request($request_id);
			if ($request_data) {
				$object_info = fanx_parse_lock_key_to_object($lock_key);
				do_action('fanx_activity_logger_log', 'editor_lock_request', $object_info['type'], $object_info['id'], [
					'requester' => $request_data['requesting_user_name'],
					'editor' => $request_data['editor_user_name']
				]);
			}
		}

		wp_send_json_success([
			'request_id' => $request_id,
			'message' => 'Access request sent. Waiting for approval...'
		]);
	} else {
		wp_send_json_error(['message' => 'Could not create access request']);
	}
}
add_action('wp_ajax_fanx_request_edit_access', 'fanx_request_edit_access_ajax');

/**
 * AJAX endpoint to respond to an access request (allow/deny)
 */
function fanx_respond_access_request_ajax() {
	check_ajax_referer('fanx_editor_lock_nonce', 'nonce');

	$request_id = isset($_POST['requestId']) ? sanitize_text_field($_POST['requestId']) : '';
	$action = isset($_POST['action_response']) ? sanitize_text_field($_POST['action_response']) : '';

	if (!$request_id || !in_array($action, ['allowed', 'denied'])) {
		wp_send_json_error(['message' => 'Invalid request']);
	}

	// Get request data before responding (for logging)
	$request_data = fanx_get_access_request($request_id);

	// Respond to the request
	$result = fanx_respond_to_access_request($request_id, $action);

	if ($result) {
		// Log to activity feed if available
		if ($request_data && function_exists('do_action')) {
			$object_info = fanx_parse_lock_key_to_object($request_data['lock_key']);
			$action_label = $action === 'allowed' ? 'editor_lock_approved' : 'editor_lock_denied';
			do_action('fanx_activity_logger_log', $action_label, $object_info['type'], $object_info['id'], [
				'requester' => $request_data['requesting_user_name'],
				'editor' => $request_data['editor_user_name'],
				'decision' => $action
			]);
		}

		wp_send_json_success([
			'message' => 'Response recorded',
			'action' => $action
		]);
	} else {
		wp_send_json_error(['message' => 'Could not process response']);
	}
}
add_action('wp_ajax_fanx_respond_access_request', 'fanx_respond_access_request_ajax');

/**
 * AJAX endpoint to check access request status
 */
function fanx_check_access_status_ajax() {
	check_ajax_referer('fanx_editor_lock_nonce', 'nonce');

	$request_id = isset($_POST['requestId']) ? sanitize_text_field($_POST['requestId']) : '';

	if (!$request_id) {
		wp_send_json_error(['message' => 'Invalid request']);
	}

	$status = fanx_check_access_request($request_id);

	if ($status === null) {
		wp_send_json_success(['status' => 'expired']);
	} else {
		wp_send_json_success(['status' => $status]);
	}
}
add_action('wp_ajax_fanx_check_access_status', 'fanx_check_access_status_ajax');

/**
 * AJAX endpoint to get pending requests for the current user
 */
function fanx_get_pending_requests_ajax() {
	check_ajax_referer('fanx_editor_lock_nonce', 'nonce');

	$requests = fanx_get_pending_requests_for_user();

	wp_send_json_success([
		'requests' => $requests,
		'count' => count($requests)
	]);
}
add_action('wp_ajax_fanx_get_pending_requests', 'fanx_get_pending_requests_ajax');

/**
 * Enqueue modal styles
 */
function fanx_enqueue_editor_styles() {
	wp_enqueue_style(
		'fanx-editor-lock-styles',
		get_template_directory_uri() . '/admin/css/editor-lock.css',
		[],
		wp_get_theme()->get('Version')
	);
}
add_action('admin_enqueue_scripts', 'fanx_enqueue_editor_styles');

// ==========================================
// UTILITY FUNCTIONS
// ==========================================

/**
 * Parse lock key to extract object type and ID
 * Lock keys are formatted as: {type}_{id} (e.g., "post_123", "term_45", "acf_fg_67")
 */
function fanx_parse_lock_key_to_object($lock_key) {
	if (strpos($lock_key, 'acf_fg_') === 0) {
		// ACF Field Group
		$id = intval(substr($lock_key, 7));
		return ['type' => 'acf_fg', 'id' => $id];
	} elseif (strpos($lock_key, 'term_') === 0) {
		// Taxonomy term
		$id = intval(substr($lock_key, 5));
		return ['type' => 'term', 'id' => $id];
	} elseif (strpos($lock_key, 'post_') === 0) {
		// Post or page
		$id = intval(substr($lock_key, 5));
		return ['type' => 'post', 'id' => $id];
	}

	// Fallback
	return ['type' => 'unknown', 'id' => 0];
}

/**
 * Format time difference in human-readable format
 */
function fanx_format_time_diff($seconds) {
	if ($seconds < 60) {
		return 'less than a minute';
	} elseif ($seconds < 3600) {
		$minutes = intval($seconds / 60);
		return $minutes === 1 ? '1 minute' : $minutes . ' minutes';
	} else {
		$hours = intval($seconds / 3600);
		return $hours === 1 ? '1 hour' : $hours . ' hours';
	}
}

// ==========================================
// CLEANUP: Remove locks for logged-out users
// ==========================================

/**
 * Cleanup stale locks periodically
 */
function fanx_cleanup_stale_locks() {
	global $wpdb;

	$prefix = EDITOR_LOCK_OPTION_PREFIX;
	$timeout = EDITOR_LOCK_TIMEOUT;
	$now = current_time('timestamp');

	// Get all editor lock options
	$locks = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT option_id, option_name, option_value FROM $wpdb->options WHERE option_name LIKE %s",
			$wpdb->esc_like($prefix) . '%'
		)
	);

	if ($locks) {
		foreach ($locks as $lock) {
			$lock_data = maybe_unserialize($lock->option_value);

			if (is_array($lock_data) && isset($lock_data['timestamp'])) {
				$elapsed = $now - $lock_data['timestamp'];
				if ($elapsed > $timeout) {
					delete_option($lock->option_name);
				}
			}
		}
	}
}
add_action('admin_init', 'fanx_cleanup_stale_locks');
 

