<?php 
/**  Admin Dashboard Widget: Debug Feed
 * Description: A simple widget to display the latest posts from the site's RSS feed for debugging 
 * Dash Board Widget: Lists Errors - PHP 
*/


//Convert UTC timestamp in log entry to site's local timezone
function df_convert_log_timestamp_to_local( $entry ) {
    // Match pattern like [03-Mar-2026 01:48:52 UTC]
    if ( preg_match( '/\[(\d{2})-([A-Za-z]{3})-(\d{4})\s+(\d{2}):(\d{2}):(\d{2})\s+UTC\]/', $entry, $matches ) ) {
        $day = $matches[1];
        $month = $matches[2];
        $year = $matches[3];
        $hour = $matches[4];
        $minute = $matches[5];
        $second = $matches[6];
        
        // Create timestamp from UTC time
        $utc_time = strtotime( "$year-$month-$day $hour:$minute:$second", 0 );
        
        // Convert to site's timezone
        $local_time = wp_date( 'M j, Y g:i:s a', $utc_time );
        
        // Replace UTC timestamp with bold local time
        $entry = preg_replace(
            '/\[\d{2}-[A-Za-z]{3}-\d{4}\s+\d{2}:\d{2}:\d{2}\s+UTC\]/',
            "[<strong>$local_time</strong>]",
            $entry
        );
    }
    
    return $entry;
}

// Check if a log entry is within the last week.
function df_is_entry_within_expiration( $entry, $days = 7 ) {
    // Match pattern like [17-Jun-2026 21:50:02 America/Denver]
    if ( preg_match( '/\[(\d{2})-([A-Za-z]{3})-(\d{4})\s+(\d{2}):(\d{2}):(\d{2})/', $entry, $matches ) ) {
        $day = $matches[1];
        $month = $matches[2];
        $year = $matches[3];
        $hour = $matches[4];
        $minute = $matches[5];
        $second = $matches[6];
        
        // Create timestamp from log entry
        $entry_time = strtotime( "$year-$month-$day $hour:$minute:$second" );
        
        // Get current time
        $current_time = current_time( 'timestamp' );
        
        // Calculate cutoff time (14 days ago)
        $cutoff_time = $current_time - ( $days * 86400 );
        
        return $entry_time >= $cutoff_time;
    }
    
    return false; // If we can't parse the timestamp, exclude it
}

function df_reg_debug_widget() {
	wp_add_dashboard_widget( 'widget_sys_diagnose', __( 'System Diagnostics', 'df' ), 'df_create_sys_diagnose_widget' );
}
add_action( 'wp_dashboard_setup', 'df_reg_debug_widget' );

// Main tabbed widget
function df_create_sys_diagnose_widget() {
	echo '<div class="df-sysdiag-widget-container">';

	// Tab Navigation
	echo '<div class="df-sysdiag-tabs" style="border-bottom: 2px solid #e5e5e5; margin-bottom: 16px; display: flex; gap: 0;">';

	$tabs = array(
		'debuglog'    => 'Debug Log',
		'sysinfo'     => 'System Info',
		'wpcron'      => 'WP Cron',
		'exporthealth' => 'Export Health',
	);

	foreach ( $tabs as $tab_id => $tab_label ) {
		$active = ( $tab_id === 'debuglog' ) ? 'active' : '';
		echo '<button class="df-sysdiag-tab-btn df-tab-' . esc_attr( $tab_id ) . ' ' . esc_attr( $active ) . '"
			style="padding: 10px 16px; border: none; background: none; cursor: pointer; font-weight: 500;
			border-bottom: 3px solid transparent; transition: all 0.2s;" data-tab="' . esc_attr( $tab_id ) . '">';
		echo esc_html( $tab_label );
		echo '</button>';
	}

	echo '</div>';

	// Tab Content
	echo '<div class="df-sysdiag-content">';

	echo '<div id="df-sysdiag-tab-debuglog" class="df-sysdiag-tab-content active">';
	df_display_debug_log_tab();
	echo '</div>';

	echo '<div id="df-sysdiag-tab-sysinfo" class="df-sysdiag-tab-content" style="display: none;">';
	df_display_system_info_tab();
	echo '</div>';

	echo '<div id="df-sysdiag-tab-wpcron" class="df-sysdiag-tab-content" style="display: none;">';
	if ( function_exists( 'df_display_wp_cron_tab' ) ) {
		df_display_wp_cron_tab();
	}
	echo '</div>';

	echo '<div id="df-sysdiag-tab-exporthealth" class="df-sysdiag-tab-content" style="display: none;">';
	if ( function_exists( 'fanx_render_export_health_check_tab' ) ) {
		fanx_render_export_health_check_tab();
	}
	echo '</div>';

	echo '</div>'; // End tab content
	echo '</div>'; // End container

	?>
	<script>
	(function() {
		const buttons = document.querySelectorAll('.df-sysdiag-tab-btn');
		buttons.forEach(btn => {
			btn.addEventListener('click', function() {
				const tabId = this.getAttribute('data-tab');

				document.querySelectorAll('.df-sysdiag-tab-content').forEach(content => {
					content.style.display = 'none';
				});

				buttons.forEach(b => b.classList.remove('active'));

				document.getElementById('df-sysdiag-tab-' + tabId).style.display = 'block';
				this.classList.add('active');

				buttons.forEach(b => {
					if (b === this) {
						b.style.borderBottomColor = '#2271b1';
						b.style.color = '#2271b1';
					} else {
						b.style.borderBottomColor = 'transparent';
						b.style.color = 'inherit';
					}
				});
			});
		});

		document.querySelector('.df-sysdiag-tab-btn.active').style.borderBottomColor = '#2271b1';
		document.querySelector('.df-sysdiag-tab-btn.active').style.color = '#2271b1';
	})();
	</script>
	<?php
}

// Debug Log tab content
function df_display_debug_log_tab() {
    echo '<span style="color: #20848f; font-size: 12px;"><strong>Last loaded:</strong> ';
    echo wp_kses_post( wp_date( 'F j, Y g:i a' ) );
    echo '</span>';

    // Inline JavaScript for clearing log with confirmation
    echo '<script>';
    echo 'function df_clear_log_confirm() {';
    echo '  if ( confirm("Are you sure you want to clear the debug log? This action cannot be undone.") ) {';
    echo '    var nonce = "' . wp_create_nonce( 'df_clear_log_nonce' ) . '";';
    echo '    fetch(ajaxurl, {';
    echo '      method: "POST",';
    echo '      headers: { "Content-Type": "application/x-www-form-urlencoded" },';
    echo '      body: "action=df_clear_debug_log&nonce=" + encodeURIComponent(nonce)';
    echo '    }).then(response => response.json()).then(data => {';
    echo '      if (data.success) {';
    echo '        alert("Debug log cleared successfully!");';
    echo '        location.reload();';
    echo '      } else {';
    echo '        alert("Failed to clear log: " + data.data.message);';
    echo '      }';
    echo '    }).catch(error => alert("Error: " + error));';
    echo '  }';
    echo '}';
    echo '</script>';

    $log_file = WP_CONTENT_DIR . '/debug.log';
    if ( file_exists( $log_file ) ) {
        $file_size    = filesize( $log_file );
        $log_contents = file_get_contents( $log_file );
        $log_entries  = array_filter( explode( "\n", trim( $log_contents ) ) );

        $log_entries = array_filter( $log_entries, function( $entry ) {
            return df_is_entry_within_expiration( $entry, 14 );
        } );

        $total_entries  = count( $log_entries );
        $recent_entries = array_reverse( array_slice( $log_entries, -5 ) );

        echo '<div style="margin: 8px 0 10px; font-size: 12px; color: #999;">';
        echo 'Total entries: <strong>' . intval( $total_entries ) . '</strong> | Log size: <strong>' . size_format( $file_size ) . '</strong>';
        echo '</div>';

        echo '<ul style="max-height: 500px; overflow-y: auto; overflow-x: auto; color: #5bc851; background: #000000;
                        padding: 5%; border-bottom: solid 15px #5bc851; border-radius: 3; list-style: none; margin: 0;
                        font-family: monospace; font-size: 12px; line-height: 1.6; word-wrap: break-word; white-space: pre-wrap;">';

        foreach ( $recent_entries as $entry ) {
            $entry    = df_convert_log_timestamp_to_local( $entry );
            $is_error = ( stripos( $entry, '[error]' ) !== false );
            $style    = $is_error ? 'color: #ff6b00; font-weight: bold;' : '';
            echo '<li style="' . esc_attr( $style ) . ' margin-bottom: 15px;">' . esc_html( $entry ) . '</li>';
        }
        echo '</ul>';

        echo '<div style="margin-top: 15px;">';
        echo '<button class="button button-secondary" onclick="location.reload();">↻ Refresh Log</button>';
        echo '<button class="button button-secondary" style="margin-left: 5px;" onclick="df_clear_log_confirm();">🗑️ Clear Log</button>';
        echo '<a href="' . esc_url( admin_url( 'tools.php?page=df_full_log' ) ) . '" class="button button-secondary" style="margin-left: 5px;">📄 View Full Log</a>';
        echo '</div>';
    } else {
        echo '<p style="color: #999;"><em>No debug log found.</em></p>';
    }
}

// Legacy alias so the old widget ID still resolves if cached
function df_create_debug_log_box() {
    df_display_debug_log_tab();
}

// AJAX handler to clear the debug log
function df_clear_debug_log() {
    // Verify nonce for security
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'df_clear_log_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Security verification failed.' ), 403 );
    }

    // Check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
    }

    $log_file = WP_CONTENT_DIR . '/debug.log';
    
    // Clear the log file
    if ( file_exists( $log_file ) ) {
        if ( file_put_contents( $log_file, '' ) !== false ) {
            wp_send_json_success( array( 'message' => 'Debug log cleared.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Could not write to log file.' ) );
        }
    } else {
        wp_send_json_success( array( 'message' => 'Debug log does not exist or is already empty.' ) );
    }
}
add_action( 'wp_ajax_df_clear_debug_log', 'df_clear_debug_log' );

// Register the full log admin page
function df_register_full_log_page() {
    add_submenu_page(
        'tools.php', // Parent menu: Tools
        'Full Debug Log',
        'Full Debug Log',
        'manage_options',
        'df_full_log',
        'df_display_full_log_page'
    );
}
add_action( 'admin_menu', 'df_register_full_log_page' );

// Display the full debug log page
function df_display_full_log_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Sorry, you are not allowed to access this page.' );
    }

    $log_file = WP_CONTENT_DIR . '/debug.log';
    $log_contents = '';
    $file_size = 0;
    $total_entries = 0;

    if ( file_exists( $log_file ) ) {
        $log_contents = file_get_contents( $log_file );
        $file_size = filesize( $log_file );
        $log_entries = array_filter( explode( "\n", trim( $log_contents ) ) );
        $total_entries = count( $log_entries );
    }

    ?>
    <div class="wrap">
        <h1>Full Debug Log</h1>
        <p><em>Complete debug log for this site.</em></p>

        <div style="margin-bottom: 15px; 
                    background: #f1f1f1; 
                    padding: 10px; 
                    border-radius: 3px;">

            <strong>Log Stats:</strong> 
            <span style="margin-left: 20px;">Total entries: <strong><?php echo intval( $total_entries ); ?></strong></span>
            <span style="margin-left: 20px;">File size: <strong><?php echo size_format( $file_size ); ?></strong></span>
        </div>

        <div style="margin-bottom: 15px;">
            <button class="button button-secondary" onclick="location.reload();">↻ Refresh</button>
            <button class="button button-secondary" style="margin-left: 5px;" onclick="df_full_clear_log_confirm();">🗑️ Clear Log</button>
        </div>
        <script>
        function df_full_clear_log_confirm() {
            if ( confirm('Are you sure you want to clear the entire debug log? This cannot be undone.') ) {
                var nonce = '<?php echo wp_create_nonce( 'df_clear_log_nonce' ); ?>';
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=df_clear_debug_log&nonce=' + encodeURIComponent(nonce)
                }).then(response => response.json()).then(data => {
                    if (data.success) {
                        alert('Debug log cleared successfully!');
                        location.reload();
                    } else {
                        alert('Failed to clear log: ' + data.data.message);
                    }
                }).catch(error => alert('Error: ' + error));
            }
        }
        </script>

        <?php if ( ! empty( $log_contents ) ) : ?>
            <div style="background: #000000; 
                        color: #5bc851; 
                        padding: 15px; 
                        border-radius: 3px; 
                        font-family: monospace; 
                        font-size: 12px; 
                        line-height: 1.6; 
                        white-space: pre-wrap; 
                        word-wrap: break-word; 
                        overflow: auto; 
                        max-height: 600px;">
                <?php
                $lines = explode( "\n", trim( $log_contents ) );
                
                // Filter to only show last 14 days
                $lines = array_filter( $lines, function( $line ) {
                    return df_is_entry_within_expiration( $line, 14 );
                } );
                
                // Reverse to show newest first
                $lines = array_reverse( $lines );
                
                foreach ( $lines as $line ) {
                    if ( ! empty( $line ) ) {
                        // Convert UTC timestamp to site's local timezone
                        $line = df_convert_log_timestamp_to_local( $line );
                        
                        $is_error = ( stripos( $line, '[error]' ) !== false );
                        if ( $is_error ) {
                            echo '<div style="color: #ff6b00; 
                            font-weight: bold; 
                            margin-bottom: 3px;">' . esc_html( $line ) . '</div>';
                        } else {
                            echo '<div style="margin-bottom: 3px;">' . esc_html( $line ) . '</div>';
                        }
                    }
                }
                ?>
            </div>
        <?php else : ?>
            <p style="color: #999;"><em>No debug log entries found.</em></p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Display System Information Tab
 * Shows PHP version, WordPress version, Theme, and Theme Compatibility status
 */
function df_display_system_info_tab() {
    echo '<div style="padding: 15px;">';
    
    $php_version = phpversion();
    $wp_version = get_bloginfo( 'version' );
    $theme = wp_get_theme();
    $theme_version = $theme->get( 'Version' );
    
    // Get compatibility check results
    $compat_issues = get_option( 'fanx_compatibility_issues', array() );
    $compat_status = empty( $compat_issues ) ? 'passing' : 'warning';
    $compat_count = count( $compat_issues );
    
    // System Info Box
    echo '<div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; border-radius: 4px; margin-bottom: 20px;">';
    
    // PHP Version
    echo '<div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">';
    echo '<strong style="min-width: 200px;">PHP Version:</strong> ';
    echo '<code style="background: #e7e7e7; padding: 4px 8px; border-radius: 3px; font-size: 13px;">' . esc_html( $php_version ) . '</code>';
    echo '</div>';
    
    // WordPress Version
    echo '<div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">';
    echo '<strong style="min-width: 200px;">WordPress Version:</strong> ';
    echo '<code style="background: #e7e7e7; padding: 4px 8px; border-radius: 3px; font-size: 13px;">' . esc_html( $wp_version ) . '</code>';
    echo '</div>';
    
    // Theme & Version
    echo '<div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">';
    echo '<strong style="min-width: 200px;">Active Theme:</strong> ';
    echo '<code style="background: #e7e7e7; padding: 4px 8px; border-radius: 3px; font-size: 13px;">' . esc_html( $theme->get( 'Name' ) ) . ' v' . esc_html( $theme_version ) . '</code>';
    echo '</div>';
    
    // Theme Compatibility
    echo '<div style="margin-bottom: 0; display: flex; justify-content: space-between; align-items: center;">';
    echo '<strong style="min-width: 200px;">Theme Compatibility:</strong> ';
    
    if ( $compat_status === 'passing' ) {
        echo '<span style="background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">✓ PASSING</span>';
    } else {
        echo '<span style="background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">⚠ ' . intval( $compat_count ) . ' ISSUE(S)</span>';
    }
    
    echo '</div>';
    echo '</div>';
    
    // Compatibility Details
    if ( ! empty( $compat_issues ) ) {
        echo '<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px; margin-bottom: 20px;">';
        echo '<h4 style="margin-top: 0; color: #856404;">Compatibility Issues Found (' . intval( $compat_count ) . ')</h4>';
        echo '<ul style="margin: 10px 0; padding-left: 20px;">';
        foreach ( $compat_issues as $issue ) {
            echo '<li style="margin-bottom: 8px;">' . esc_html( $issue ) . '</li>';
        }
        echo '</ul>';
        echo '<div style="margin-top: 12px;">';
        echo '<a href="' . esc_url( admin_url( 'tools.php?page=fanx-compatibility-check' ) ) . '" class="button button-secondary">View Full Report</a>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
}

?>