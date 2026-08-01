<?php
/**
 * AJAX handler: trigger due WP cron events via spawn_cron()
 */
function df_ajax_run_cron_events() {
    check_ajax_referer( 'df_run_cron', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Insufficient permissions.' );
    }

    spawn_cron( time() );
    wp_send_json_success( 'Cron triggered — page will refresh.' );
}
add_action( 'wp_ajax_df_run_cron_events', 'df_ajax_run_cron_events' );

/**
 * System Diagnostics Widget: WP Cron Tab
 *
 * Displays scheduled WordPress cron events and their next run times.
 */

function df_display_wp_cron_tab() {
    $cron_array = _get_cron_array();

    echo '<div style="padding: 10px 12px; margin-bottom: 14px; font-size: 12px; font-weight: 300; color: #333;">';
    echo '<strong>WP Cron is managed by the system cron.</strong> System runs cron every 10 minutes. Push jobs manually if overdue.'; 
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

    echo '<div style="margin-top: 12px; max-height: 400px; overflow-y: auto;">';
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

    // Run overdue events button
    $overdue_count = count( array_filter( $events, function( $e ) use ( $now ) {
        return $e['timestamp'] < $now;
    } ) );

    echo '<div style="margin-top: 14px;">';
    echo '<button class="button button-primary" id="df-run-cron-btn" data-nonce="' . esc_attr( wp_create_nonce( 'df_run_cron' ) ) . '">';
    echo '&#9654; Run Overdue Events';
    if ( $overdue_count > 0 ) {
        echo ' <span style="background: #d32f2f; color: white; border-radius: 10px; padding: 1px 7px; font-size: 11px; margin-left: 4px;">' . intval( $overdue_count ) . '</span>';
    }
    echo '</button>';
    echo '&nbsp;<button class="button button-secondary" onclick="location.reload();">&#8635; Refresh</button>';
    echo '<span id="df-run-cron-msg" style="margin-left: 10px; font-size: 12px;"></span>';
    echo '</div>';
    ?>
    <script>
    (function() {
        var btn = document.getElementById('df-run-cron-btn');
        if ( ! btn ) return;
        btn.addEventListener('click', function() {
            var msg = document.getElementById('df-run-cron-msg');
            btn.disabled = true;
            msg.style.color = '#999';
            msg.textContent = 'Running...';
            var data = new FormData();
            data.append('action', 'df_run_cron_events');
            data.append('nonce', btn.getAttribute('data-nonce'));
            fetch(ajaxurl, { method: 'POST', body: data })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if ( res.success ) {
                        msg.style.color = '#27ae60';
                        msg.textContent = res.data;
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        msg.style.color = '#d32f2f';
                        msg.textContent = res.data || 'Error running cron.';
                        btn.disabled = false;
                    }
                })
                .catch(function() {
                    msg.style.color = '#d32f2f';
                    msg.textContent = 'Request failed.';
                    btn.disabled = false;
                });
        });
    })();
    </script>
    <?php
}
