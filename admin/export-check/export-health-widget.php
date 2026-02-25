<?php
/**
 * Admin Dashboard Widget: Pre-Export Health Check
 * 
 * Shows status and warnings before exporting static site
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function fanx_register_export_check_widget() {
    wp_add_dashboard_widget(
        'fanx_export_health_widget',
        __( 'Static Export Health Check', 'fanx-theme' ),
        'fanx_render_export_health_widget'
    );
}
add_action( 'wp_dashboard_setup', 'fanx_register_export_check_widget' );

function fanx_render_export_health_widget() {
    echo '<div style="padding: 10px;">';
    
    // Status Badge
    echo '<p style="margin: 0 0 15px 0;">';
    echo 'Overall Export Status: ';
    echo fanx_get_export_status_badge();
    echo '</p>';
    
    // Check Results
    echo fanx_get_export_check_html();
    
    // Action Button
    echo '<p style="margin-top: 15px;">';
    echo '<button class="button button-primary" onclick="location.reload();">↻ Refresh Check</button>&nbsp;';
    echo '<a href="' . admin_url( 'tools.php?page=simply-static' ) . '" class="button button-secondary" target="_blank">Go to Simply Static</a>';
    echo '</p>';
    
    // Last Check Time
    echo '<p style="font-size: 11px; color: #999; margin-top: 10px;">';
    echo 'Last checked: ' . esc_html( current_time( 'Y-m-d H:i:s' ) );
    echo '</p>';
    
    echo '</div>';
}
