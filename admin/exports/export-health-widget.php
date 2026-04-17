<?php
/**
 * Admin Dashboard Widget: Export Manager
 * 
 * Provides tabbed interface for scheduling exports and viewing health checks
 * Tabs:
 * 1. Scheduler - Schedule one-time full site exports via wp-cron
 * 2. Health Check - Pre-export health status and warnings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue styles and scripts for the export widget
 */
function fanx_enqueue_export_widget_assets() {
    // Only load on dashboard
    if ( ! is_admin() ) {
        return;
    }
    
    // Register and enqueue styles
    wp_register_style(
        'fanx-export-widget-styles',
        get_template_directory_uri() . '/admin/exports/export-widget.css',
        array(),
        '1.0.0'
    );
    wp_enqueue_style( 'fanx-export-widget-styles' );
    
    // Register and enqueue scripts
    wp_register_script(
        'fanx-export-widget-script',
        get_template_directory_uri() . '/admin/exports/export-widget.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );
    wp_enqueue_script( 'fanx-export-widget-script' );
    
    // Localize script with AJAX URL and nonce
    wp_localize_script( 'fanx-export-widget-script', 'fanxExportWidget', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'fanx_schedule_export' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'fanx_enqueue_export_widget_assets' );

function fanx_register_export_check_widget() {
    wp_add_dashboard_widget(
        'fanx_export_health_widget',
        __( 'Export Manager', 'fanx-theme' ),
        'fanx_render_export_health_widget'
    );
}
add_action( 'wp_dashboard_setup', 'fanx_register_export_check_widget' );

function fanx_render_export_health_widget() {
    echo '<div class="fanx-export-widget">';
    
    // Tab Navigation
    echo '<div class="fanx-export-tabs-nav">';
    echo '<button class="fanx-export-tab-button active" data-tab="scheduler">' . esc_html__( 'Scheduler', 'fanx-theme' ) . '</button>';
    echo '<button class="fanx-export-tab-button" data-tab="health-check">' . esc_html__( 'Health Check', 'fanx-theme' ) . '</button>';
    echo '</div>';
    
    // Tab Content
    echo '<div class="fanx-export-tabs-content">';
    
    // Scheduler Tab
    echo '<div class="fanx-export-tab-pane active" id="scheduler-tab" data-tab="scheduler">';
    fanx_render_export_scheduler_tab();
    echo '</div>';
    
    // Health Check Tab
    echo '<div class="fanx-export-tab-pane" id="health-check-tab" data-tab="health-check">';
    fanx_render_export_health_check_tab();
    echo '</div>';
    
    echo '</div>';
    
    echo '</div>';
}

/**
 * Render the scheduler tab for one-time exports
 */
function fanx_render_export_scheduler_tab() {
    // Auto-clear any stuck/past exports on every page load
    $scheduled_time = wp_next_scheduled( 'fanx_one_time_export_cron' );
    if ( $scheduled_time && $scheduled_time < time() ) {
        wp_unschedule_event( $scheduled_time, 'fanx_one_time_export_cron' );
        error_log( '[EXPORT SCHEDULER] Auto-cleared past export that was scheduled for ' . wp_date( 'Y-m-d H:i:s', $scheduled_time ) );
        $scheduled_time = false; // Refresh for display
    }
    
    echo '<div style="padding: 10px;">';
    
    // Check if an export is currently scheduled
    $scheduled_time = wp_next_scheduled( 'fanx_one_time_export_cron' );
    
    if ( $scheduled_time ) {
        echo '<div style="background: #e3f2fd; border: 1px solid #2196f3; padding: 10px; margin: 10px 0; border-radius: 3px;">';
        echo '<strong style="color: #1976d2;">📅 Full Site Export Scheduled</strong><br>';
        echo 'Next export: ' . esc_html( wp_date( 'Y-m-d H:i:s', $scheduled_time ) ) . '<br>';
        echo '</div>';
        
        // Clear button for stuck exports
        echo '<p style="margin: 15px 0;">';
        echo '<button class="button button-secondary" id="fanx-clear-export-btn" data-nonce="' . esc_attr( wp_create_nonce( 'fanx_schedule_export' ) ) . '">';
        echo '✕ Clear Scheduled Export';
        echo '</button>';
        echo '</p>';
    } else {
        echo '<div style="background: #f5f5f5; border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 3px;">';
        echo '<strong>No exports scheduled</strong>';
        echo '</div>';
    }
    
    // Schedule Date/Time Input
    echo '<div style="margin: 15px 0;">';
    echo '<label for="fanx-export-datetime" style="display: block; margin-bottom: 8px; font-weight: bold;">Schedule Export For:</label>';
    echo '<input type="datetime-local" id="fanx-export-datetime" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box;" value="' . esc_attr( wp_date( 'Y-m-d\TH:i', time() + 3600 ) ) . '">';
    echo '<small style="display: block; color: #666; margin-top: 4px;">Defaults to 1 hour from now</small>';
    echo '</div>';
    
    // Schedule Button
    echo '<p style="margin: 15px 0;">';
    echo '<button class="button button-primary" id="fanx-schedule-export-btn" data-nonce="' . esc_attr( wp_create_nonce( 'fanx_schedule_export' ) ) . '">';
    echo '⏱️ Schedule Export';
    echo '</button>';
    echo '</p>';
    
    // Loading indicator
    echo '<div id="fanx-export-loading" style="display: none; margin: 10px 0;">';
    echo '<span class="spinner" style="float: none; visibility: visible;"></span> Scheduling export...';
    echo '</div>';
    
    // Status message
    echo '<div id="fanx-export-message" style="margin: 10px 0;"></div>';
    
    // Information
    echo '<small style="color: #666; display: block; margin: 15px 0; line-height: 1.6;">';
    echo 'Schedules a one-time full site export with pre-export health checks, and only proceeds if no critical issues are found.';
    echo '</small>';
    
    echo '</div>';
}

/**
 * Render the health check tab (existing functionality)
 */
function fanx_render_export_health_check_tab() {
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
