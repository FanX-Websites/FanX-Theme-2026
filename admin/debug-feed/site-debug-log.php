<?php 
/*  Admin Dashboard Widget: Debug Feed
*  Description: A simple widget to display the latest posts from the site's RSS feed for debugging
*/

function df_reg_debug_widget() {
	global $wp_meta_boxes;

	wp_add_dashboard_widget('widget_debug_feed', __('The Debug Log', 'df'), 'df_create_debug_log_box');
}
add_action('wp_dashboard_setup', 'df_reg_debug_widget');

function df_create_debug_log_box() {
    echo '<p><i>A list of recent debug log entries from the site.</i></p>';
    
    // Refresh Button - and - Timestamp
    echo '<div style="margin-bottom: 15px;">';
    echo '<button class="button button-secondary" onclick="location.reload();">↻ Refresh Log</button>';
    echo '<span style="margin-left: 10px; color: #20848f; font-size: 12px;">Last loaded: ';
    echo wp_kses_post( current_time( 'Y-m-d H:i:s' ) );
    echo '</span>';
    echo '</div>';
    
    // Display the latest debug log entries
    $log_file = WP_CONTENT_DIR . '/debug.log';
    if ( file_exists( $log_file ) ) {
        $log_contents = file_get_contents( $log_file );
        $log_entries = explode( "\n", trim( $log_contents ) );
        $recent_entries = array_slice( $log_entries, -12 ); // Show last 12 entries

        echo '<ul style="max-height: 400px; overflow-y: auto; color: #5bc851; background: #000000; padding: 15px; border-radius: 3px;">';
        foreach ( $recent_entries as $entry ) {
            if ( ! empty( $entry ) ) {
                // Highlight only lines with [error] tag in ORANGE
                $highlight_class = ( stripos( $entry, '[error]' ) !== false ) ? 'style="color: #c38500; font-weight: bold;"' : '';
                echo '<li ' . $highlight_class . '>' . wp_kses_post( $entry ) . '</li>'; //HTML test styling
            }
        }
        echo '</ul>';
    } else {
        echo '<p style="color: #999;"><em>No debug log found.</em></p>';
    }
}

?>