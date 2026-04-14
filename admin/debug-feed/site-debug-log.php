<?php 
/*  Admin Dashboard Widget: Debug Feed
*  Description: A simple widget to display the latest posts from the site's RSS feed for debugging
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

function df_reg_debug_widget() {
	global $wp_meta_boxes;

	wp_add_dashboard_widget('widget_debug_feed', __('Debug Log & Activity Feed', 'df'), 'df_create_debug_log_box');
}
add_action('wp_dashboard_setup', 'df_reg_debug_widget');

function df_create_debug_log_box() {
    echo '<p><i>A list of recent debug log entries from the site.</i></p>';
    
    // Timestamp at top
    echo '<div style="margin-bottom: 15px;">';
    echo '<span style="color: #20848f; font-size: 12px;"><strong>Last loaded:</strong> '; //Load Timestamp
    echo wp_kses_post( wp_date( 'F j, Y g:i a' ) );
    echo '</span>';
    echo '</div>';
    
    // Inline JavaScript for clearing log with confirmation
    echo '<script>';
    echo 'function df_clear_log_confirm() {';
    echo '  if ( confirm("Are you sure you want to clear the debug log? This action cannot be undone.") ) {';
    echo '    var nonce = "' . wp_create_nonce('df_clear_log_nonce') . '";';
    echo '    fetch(ajaxurl, {';
    echo '      method: "POST",';
    echo '      headers: { "Content-Type": "application/x-www-form-urlencoded" },';
    echo '      body: "action=df_clear_debug_log&nonce=" + encodeURIComponent(nonce)';
    echo '    }).then(response => response.json()).then(data => {';
    echo '      if (data.success) {';
    echo '        alert("Debug log cleared successfully!");';
    echo '        location.reload();';
    echo '      } else {';
    echo '        alert("Failed to clear log: " + data.message);';
    echo '      }';
    echo '    }).catch(error => alert("Error: " + error));';
    echo '  }';
    echo '}';
    echo '</script>';
    
    // Display the latest debug log entries
    $log_file = WP_CONTENT_DIR . '/debug.log';
    if ( file_exists( $log_file ) ) {
        $file_size = filesize( $log_file );
        $log_contents = file_get_contents( $log_file );
        $log_entries = array_filter( explode( "\n", trim( $log_contents ) ) );
        $total_entries = count( $log_entries );
        $recent_entries = array_slice( $log_entries, -20 ); // Show last 20 entries

        echo '<div style="margin-bottom: 10px; font-size: 12px; color: #999;">';
        echo 'Total entries: <strong>' . intval( $total_entries ) . '</strong> | Log size: <strong>' . size_format( $file_size ) . '</strong>';
        echo '</div>';

        echo '<ul style="max-height: 500px; 
                        overflow-y: auto; 
                        overflow-x: auto; 
                        color: #5bc851; 
                        background: #000000; 
                        padding: 5%;
                        border-bottom: solid 15px #5bc851; 
                        border-radius: 3; 
                        list-style: none;
                        margin: 0; 
                        font-family: monospace; 
                        font-size: 12px; 
                        line-height: 1.6; 
                        word-wrap: break-word; 
                        white-space: pre-wrap;">';

        foreach ( $recent_entries as $entry ) {
            // Skip cron event logs to keep feed clean
            if ( stripos( $entry, '[WP_CRON]' ) !== false ) {
                continue;
            }
            
            // Convert UTC timestamp to site's local timezone
            $entry = df_convert_log_timestamp_to_local( $entry );
            
            // Highlight only lines with [error] tag in ORANGE
            $is_error = ( stripos( $entry, '[error]' ) !== false );
            $highlight_style = $is_error ? 'color: #ff6b00; font-weight: bold;' : '';
            echo '<li style="' . esc_attr( $highlight_style ) . ' margin-bottom: 15px;">' . wp_kses_post( $entry ) . '</li>';
        }
        echo '</ul>';
        
        // Buttons at bottom
        echo '<div style="margin-top: 15px;">';
        echo '<button class="button button-secondary" onclick="location.reload();">↻ Refresh Log</button>';
        echo '<button class="button button-secondary" style="margin-left: 5px;" onclick="df_clear_log_confirm();">🗑️ Clear Log</button>';
        echo '<a href="' . esc_url( admin_url( 'tools.php?page=df_full_log' ) ) . '" class="button button-secondary" style="margin-left: 5px;">📄 View Full Log</a>';
        echo '</div>';
       
    } else {
        echo '<p style="color: #999;"><em>No debug log found.</em></p>';
    }
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
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=df_clear_debug_log' ), 'df_clear_log_nonce' ) ); ?>" class="button button-secondary" onclick="return confirm('Are you sure you want to clear the entire debug log? This cannot be undone.');">🗑️ Clear Log</a>
        </div>

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
                foreach ( $lines as $line ) {
                    if ( ! empty( $line ) ) {
                        // Skip cron event logs to keep log cleaner
                        if ( stripos( $line, '[WP_CRON]' ) !== false ) {
                            continue;
                        }
                        
                        // Convert UTC timestamp to site's local timezone
                        $line = df_convert_log_timestamp_to_local( $line );
                        
                        $is_error = ( stripos( $line, '[error]' ) !== false );
                        if ( $is_error ) {
                            echo '<div style="color: #ff6b00; 
                            font-weight: bold; 
                            margin-bottom: 3px;">' . wp_kses_post( $line ) . '</div>';
                        } else {
                            echo '<div style="margin-bottom: 3px;">' . wp_kses_post( $line ) . '</div>';
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

?>