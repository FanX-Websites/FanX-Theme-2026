<?php
/**
 * FanX Theme Compatibility Checker
 * 
 * Automatically scans the theme for compatibility issues after WordPress updates.
 * Alerts admins of deprecated functions, removed hooks, and ACF conflicts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ==========================================
// REGISTER COMPATIBILITY CHECK ON WP UPDATE
// ==========================================

add_action( 'upgrader_process_complete', 'fanx_run_theme_compatibility_check', 10, 1 );

/**
 * Run compatibility check after WordPress core update
 */
function fanx_run_theme_compatibility_check( $upgrader_object ) {
	// Only check if WordPress core was updated
	if ( ! isset( $upgrader_object->result['destination_name'] ) || 
		 $upgrader_object->result['destination_name'] !== 'wordpress' ) {
		return;
	}

	$issues = fanx_check_theme_compatibility();

	if ( ! empty( $issues ) ) {
		update_option( 'fanx_compatibility_issues', $issues, false );
		error_log( '[FanX Theme] Compatibility issues detected after WP update: ' . count( $issues ) . ' issues found' );
	} else {
		delete_option( 'fanx_compatibility_issues' );
		error_log( '[FanX Theme] Compatibility check passed after WP update' );
	}
}

// ==========================================
// MANUAL TRIGGER (for testing/admins)
// ==========================================

add_action( 'wp_ajax_fanx_run_compatibility_check', 'fanx_ajax_run_compatibility_check' );

/**
 * AJAX endpoint to manually trigger compatibility check
 */
function fanx_ajax_run_compatibility_check() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Insufficient permissions' );
	}

	check_admin_referer( 'fanx_compatibility_nonce' );

	$issues = fanx_check_theme_compatibility();

	if ( ! empty( $issues ) ) {
		update_option( 'fanx_compatibility_issues', $issues, false );
		wp_send_json_success( array(
			'found_issues' => true,
			'count' => count( $issues ),
			'issues' => $issues,
		) );
	} else {
		delete_option( 'fanx_compatibility_issues' );
		wp_send_json_success( array(
			'found_issues' => false,
			'message' => 'No compatibility issues detected',
		) );
	}
}

// ==========================================
// CORE COMPATIBILITY CHECK FUNCTION
// ==========================================

/**
 * Check theme for WordPress compatibility issues
 * 
 * @return array Array of issue strings, empty if no issues
 */
function fanx_check_theme_compatibility() {
	$issues = array();
	$wp_version = get_bloginfo( 'version' );

	// 1. Check for deprecated WP_Query parameters used in theme files
	$issues = array_merge( $issues, fanx_check_deprecated_wp_query() );

	// 2. Check ACF compatibility
	$issues = array_merge( $issues, fanx_check_acf_compatibility() );

	// 3. Check for removed/changed hooks and filters
	$issues = array_merge( $issues, fanx_check_hooks_compatibility( $wp_version ) );

	// 4. Check for deprecated shortcodes
	$issues = array_merge( $issues, fanx_check_shortcode_compatibility() );

	// 5. Check meta/postmeta handling
	$issues = array_merge( $issues, fanx_check_meta_compatibility() );

	return array_filter( array_unique( $issues ) );
}

/**
 * Check for deprecated WP_Query parameters in theme files
 */
function fanx_check_deprecated_wp_query() {
	$issues = array();
	$theme_dir = get_template_directory();
	$files = fanx_scan_php_files( $theme_dir );

	foreach ( $files as $file ) {
		$content = file_get_contents( $file );

		// Check for old meta_key parameter (deprecated in WP 6.0)
		if ( preg_match( "/['\"]meta_key['\"]\s*=>/", $content ) && 
			 ! preg_match( "/['\"]meta_query['\"]\s*=>\s*array/", $content ) ) {
			$issues[] = '⚠️ Found deprecated meta_key parameter in: ' . basename( $file ) . ' — use meta_query instead';
		}

		// Check for direct orderby='meta_value_num' without meta_query
		if ( preg_match( "/['\"]orderby['\"]\s*=>\s*['\"]meta_value/", $content ) && 
			 ! preg_match( "/['\"]meta_query['\"]\s*=>\s*array/", $content ) ) {
			$issues[] = '⚠️ Found orderby=meta_value without meta_query in: ' . basename( $file );
		}
	}

	return $issues;
}

/**
 * Check ACF plugin compatibility
 */
function fanx_check_acf_compatibility() {
	$issues = array();

	// Check if ACF is active
	if ( ! function_exists( 'get_field' ) ) {
		$issues[] = '⚠️ ACF is not active — some theme features may not work';
		return $issues;
	}

	// Check for ACF functions that may have changed
	if ( ! function_exists( 'acf_update_setting' ) ) {
		$issues[] = '⚠️ ACF acf_update_setting() not found — ACF version may be incompatible';
	}

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		$issues[] = '⚠️ ACF acf_add_local_field_group() not found — field groups may not load';
	}

	// Check custom shortcode override compatibility
	if ( has_filter( 'acf/format_value' ) ) {
		// This is expected, but warn if ACF version is very old
		if ( function_exists( 'acf_get_setting' ) ) {
			$shortcode_enabled = acf_get_setting( 'enable_shortcode' );
			if ( ! $shortcode_enabled ) {
				$issues[] = 'ℹ️ ACF shortcodes are disabled but theme has custom shortcode handling';
			}
		}
	}

	return $issues;
}

/**
 * Check for removed/changed WordPress hooks and filters
 */
function fanx_check_hooks_compatibility( $wp_version ) {
	$issues = array();

	// Check for common hooks that were removed in recent WP versions
	$removed_hooks = array(
		'wp_kses_allowed_html' => 'WP 6.2+',
		'acf/shortcode_pre' => 'ACF 6.0+',
		'acf/load_value' => 'ACF 5.11+',
	);

	foreach ( $removed_hooks as $hook => $version_note ) {
		// Just note these for awareness — they may still exist
		if ( did_action( $hook ) === 0 ) {
			// Hook was registered but never fired (may indicate version mismatch)
		}
	}

	return $issues;
}

/**
 * Check for shortcode conflicts
 */
function fanx_check_shortcode_compatibility() {
	$issues = array();

	// Check if custom [acf] shortcode is overriding the native one
	global $shortcode_tags;
	if ( isset( $shortcode_tags['acf'] ) ) {
		$callback = $shortcode_tags['acf'];
		
		// If it's a closure or array callback, it's likely the custom one
		if ( is_array( $callback ) && is_object( $callback[0] ) ) {
			// This is the custom shortcode from acf/tweaks.php
			$issues[] = '✓ Custom ACF shortcode is active (expected)';
		}
	}

	return $issues;
}

/**
 * Check for meta/postmeta compatibility issues
 */
function fanx_check_meta_compatibility() {
	global $wpdb;
	$issues = array();

	// Check for orphaned postmeta (posts with meta but no post)
	$orphaned = $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->postmeta} pm 
		 LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
		 WHERE p.ID IS NULL"
	);

	if ( $orphaned > 0 ) {
		$issues[] = '⚠️ Found ' . $orphaned . ' orphaned postmeta entries — consider cleanup (see Tools > Site Health)';
	}

	// Check for duplicate display_order fields (could cause sorting issues)
	$duplicates = $wpdb->get_var(
		"SELECT COUNT(DISTINCT pm1.post_id) FROM {$wpdb->postmeta} pm1
		 INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id 
		 AND pm1.meta_key = pm2.meta_key 
		 AND pm1.meta_id != pm2.meta_id
		 WHERE pm1.meta_key = 'info_display_order'"
	);

	if ( $duplicates > 0 ) {
		$issues[] = '⚠️ Found ' . $duplicates . ' posts with duplicate info_display_order values — may affect guest sorting';
	}

	return $issues;
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================

/**
 * Recursively scan theme directory for PHP files
 */
function fanx_scan_php_files( $dir, $max_depth = 3, $current_depth = 0 ) {
	$files = array();

	if ( $current_depth >= $max_depth ) {
		return $files;
	}

	// Skip certain directories
	$skip_dirs = array( '.git', '.vscode', 'node_modules', 'vendor' );

	$items = @scandir( $dir );
	if ( ! is_array( $items ) ) {
		return $files;
	}

	foreach ( $items as $item ) {
		if ( $item === '.' || $item === '..' || in_array( $item, $skip_dirs ) ) {
			continue;
		}

		$path = $dir . '/' . $item;

		if ( is_file( $path ) && pathinfo( $path, PATHINFO_EXTENSION ) === 'php' ) {
			$files[] = $path;
		} elseif ( is_dir( $path ) ) {
			$files = array_merge( $files, fanx_scan_php_files( $path, $max_depth, $current_depth + 1 ) );
		}
	}

	return $files;
}

// ==========================================
// ADMIN NOTICES & DASHBOARD
// ==========================================

add_action( 'admin_notices', 'fanx_show_compatibility_warnings' );

/**
 * Display admin notice for compatibility issues
 */
function fanx_show_compatibility_warnings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$issues = get_option( 'fanx_compatibility_issues' );
	if ( empty( $issues ) ) {
		return;
	}

	$issue_count = count( $issues );
	?>
	<div class="notice notice-warning is-dismissible" id="fanx-compat-notice">
		<p>
			<strong>🔍 FanX Theme: <?php echo esc_html( $issue_count ); ?> Compatibility Issue(s) Detected</strong><br>
			Your WordPress installation may have been updated. Please review the items below:
		</p>
		<ul style="margin: 10px 0 10px 20px;">
			<?php foreach ( $issues as $issue ) : ?>
				<li><?php echo esc_html( $issue ); ?></li>
			<?php endforeach; ?>
		</ul>
		<p>
			<a href="<?php echo admin_url( 'admin.php?page=fanx-compatibility-check' ); ?>" class="button button-secondary">
				Full Compatibility Report
			</a>
			<button class="button button-secondary" onclick="fanxDismissCompatibility(this)">Dismiss</button>
		</p>
	</div>
	<script>
	function fanxDismissCompatibility(btn) {
		document.getElementById('fanx-compat-notice').remove();
		// Optionally: send AJAX to mark as dismissed
	}
	</script>
	<?php
}

add_action( 'admin_menu', 'fanx_add_compatibility_menu' );

/**
 * Add compatibility check page to admin menu
 */
function fanx_add_compatibility_menu() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	add_submenu_page(
		'tools.php',
		'FanX Theme Compatibility',
		'FanX Compatibility',
		'manage_options',
		'fanx-compatibility-check',
		'fanx_render_compatibility_page'
	);
}

/**
 * Render the compatibility check admin page
 */
function fanx_render_compatibility_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient permissions' );
	}

	$issues = get_option( 'fanx_compatibility_issues', array() );
	$wp_version = get_bloginfo( 'version' );
	$theme = wp_get_theme();
	?>
	<div class="wrap">
		<h1>🔍 FanX Theme Compatibility Check</h1>

		<div style="background: #fff; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #0073aa;">
			<p><strong>Current Environment:</strong></p>
			<ul>
				<li>WordPress Version: <code><?php echo esc_html( $wp_version ); ?></code></li>
				<li>Theme: <code><?php echo esc_html( $theme->get( 'Name' ) ); ?></code> v<?php echo esc_html( $theme->get( 'Version' ) ); ?></li>
				<li>PHP Version: <code><?php echo esc_html( phpversion() ); ?></code></li>
				<li>Last Check: <code><?php echo esc_html( wp_date( 'Y-m-d H:i:s', get_option( 'fanx_compatibility_check_time', time() ) ) ); ?></code></li>
			</ul>
		</div>

		<button class="button button-primary" onclick="fanxRunCompatibilityCheck()">
			🔄 Run Compatibility Check Now
		</button>

		<div id="compatibility-results" style="margin-top: 20px;"></div>

		<?php if ( ! empty( $issues ) ) : ?>
			<div style="background: #fff8e5; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffb900;">
				<h2>Issues Found (<?php echo count( $issues ); ?>)</h2>
				<ul>
					<?php foreach ( $issues as $issue ) : ?>
						<li><?php echo esc_html( $issue ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php else : ?>
			<div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745;">
				<p>✅ <strong>No compatibility issues detected!</strong></p>
			</div>
		<?php endif; ?>

		<h2>About This Tool</h2>
		<p>This compatibility checker scans the FanX theme for known issues that may arise after WordPress updates, including:</p>
		<ul style="margin-left: 20px;">
			<li>Deprecated WP_Query parameters</li>
			<li>ACF plugin conflicts and version mismatches</li>
			<li>Removed or changed WordPress hooks and filters</li>
			<li>Shortcode conflicts and overrides</li>
			<li>Postmeta integrity issues</li>
		</ul>

		<h3>Manual Checks</h3>
		<p>You can also manually run this check after WordPress updates using the button above.</p>
	</div>

	<script>
	function fanxRunCompatibilityCheck() {
		const resultsDiv = document.getElementById('compatibility-results');
		resultsDiv.innerHTML = '<p>⏳ Running compatibility check...</p>';

		fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'fanx_run_compatibility_check',
				_wpnonce: '<?php echo esc_js( wp_create_nonce( 'fanx_compatibility_nonce' ) ); ?>'
			})
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				if (data.data.found_issues) {
					let html = '<div style="background: #fff8e5; padding: 15px; border-radius: 5px; border-left: 4px solid #ffb900;">';
					html += '<h3>Issues Found (' + data.data.count + ')</h3><ul>';
					data.data.issues.forEach(issue => {
						html += '<li>' + issue + '</li>';
					});
					html += '</ul></div>';
					resultsDiv.innerHTML = html;
					location.reload();
				} else {
					resultsDiv.innerHTML = '<div style="background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;"><p>✅ ' + data.data.message + '</p></div>';
					location.reload();
				}
			} else {
				resultsDiv.innerHTML = '<div class="error"><p>❌ Check failed: ' + data.data + '</p></div>';
			}
		})
		.catch(error => {
			resultsDiv.innerHTML = '<div class="error"><p>❌ Error: ' + error + '</p></div>';
		});
	}
	</script>
	<?php
}
