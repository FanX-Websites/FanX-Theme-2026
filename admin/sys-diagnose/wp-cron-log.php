<?php
/**
 * System Diagnostics Widget: WP Cron Tab
 *
 * Displays scheduled WordPress cron events and their next run times.
 */

function df_display_wp_cron_tab() {
    $cron_array = _get_cron_array();

    echo '<div style="padding: 10px 12px; margin-bottom: 14px; font-size: 12px; font-weight: 300; color: #333;">';
    echo '<strong>WP Cron is managed by the system cron.</strong> <code>DISABLE_WP_CRON</code> is enabled on this server — WordPress does not trigger cron automatically on page load. Instead, a system cron job runs <code>run-wp-cron-events.php</code> every 10 minutes to execute any due events.';
    echo '</div>';

    if ( empty( $cron_array ) ) {
        echo '<p style="color: #999; margin-top: 12px;"><em>No scheduled cron events found.</em></p>';
        return;
    }

    // Flatten cron array into list of events
    $events = array();
    foreach ( $cron_array as $timestamp => $hooks ) {
        foreach ( $hooks as $hook => $hook_events ) {
            foreach ( $hook_events as $event ) {
                $events[] = array(
                    'timestamp' => $timestamp,
                    'hook'      => $hook,
                    'schedule'  => isset( $event['schedule'] ) ? $event['schedule'] : 'single',
                    'interval'  => isset( $event['interval'] ) ? $event['interval'] : 0,
                );
            }
        }
    }

    // Sort by next run time
    usort( $events, function( $a, $b ) {
        return $a['timestamp'] - $b['timestamp'];
    } );

    $now = time();

    echo '<div style="margin-top: 12px; max-height: 500px; overflow-y: auto;">';
    echo '<table style="width: 100%; border-collapse: collapse; font-size: 12px;">';
    echo '<thead><tr style="border-bottom: 2px solid #e5e5e5;">';
    echo '<th style="text-align: left; padding: 6px 8px; color: #555;">Hook</th>';
    echo '<th style="text-align: left; padding: 6px 8px; color: #555;">Schedule</th>';
    echo '<th style="text-align: left; padding: 6px 8px; color: #555;">Next Run</th>';
    echo '<th style="text-align: left; padding: 6px 8px; color: #555;">Status</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ( $events as $event ) {
        $overdue      = $event['timestamp'] < $now;
        $next_run     = wp_date( 'M j, Y g:i a', $event['timestamp'] );
        $status_color = $overdue ? '#d32f2f' : '#2e7d32';
        $status_label = $overdue ? 'Overdue' : 'Pending';
        $row_bg       = $overdue ? '#fff5f5' : 'transparent';

        echo '<tr style="border-bottom: 1px solid #f0f0f0; background: ' . esc_attr( $row_bg ) . ';">';
        echo '<td style="padding: 6px 8px; font-family: monospace; word-break: break-all;">' . esc_html( $event['hook'] ) . '</td>';
        echo '<td style="padding: 6px 8px; color: #666;">' . esc_html( $event['schedule'] ) . '</td>';
        echo '<td style="padding: 6px 8px; color: #444;">' . esc_html( $next_run ) . '</td>';
        echo '<td style="padding: 6px 8px; color: ' . esc_attr( $status_color ) . '; font-weight: 500;">' . esc_html( $status_label ) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';

    echo '<div style="margin-top: 12px; font-size: 12px; color: #999;">';
    echo intval( count( $events ) ) . ' scheduled event(s) total';
    echo '</div>';
}
