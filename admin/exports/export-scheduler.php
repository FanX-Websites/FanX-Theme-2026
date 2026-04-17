<?php
/**
 * Export Scheduler - One-Time Export Scheduling via wp-cron
 * 
 * Handles scheduling and execution of one-time full site exports
 * Uses wp-cron to schedule exports at a specified time
 * 
 * HOOKS:
 * - fanx_one_time_export_cron: The actual export hook triggered by wp-cron
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register AJAX handlers for export scheduling
 */
function fanx_register_export_scheduler_ajax() {
    add_action( 'wp_ajax_fanx_schedule_export', 'fanx_ajax_schedule_export' );
    add_action( 'wp_ajax_fanx_clear_export', 'fanx_ajax_clear_export' );
}
add_action( 'admin_init', 'fanx_register_export_scheduler_ajax' );

/**
 * AJAX handler to schedule a one-time export
 * 
 * Security:
 * - Requires 'manage_options' capability (admin only)
 * - Validates nonce for CSRF protection
 * - Sanitizes all input
 * 
 * POST Parameters:
 * - nonce: Security nonce for CSRF protection
 * - scheduled_time: ISO 8601 datetime string (e.g., '2026-04-15T14:30')
 */
function fanx_ajax_schedule_export() {
    // Check nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'fanx_schedule_export' ) ) {
        wp_send_json_error( array(
            'message' => __( 'Security check failed', 'fanx-theme' ),
        ) );
    }
    
    // Check capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array(
            'message' => __( 'You do not have permission to schedule exports', 'fanx-theme' ),
        ) );
    }
    
    // Get and validate scheduled time
    $scheduled_time_str = isset( $_POST['scheduled_time'] ) ? sanitize_text_field( $_POST['scheduled_time'] ) : '';
    
    // Parse the datetime string (format: 2026-04-15T14:30)
    $scheduled_timestamp = strtotime( $scheduled_time_str );
    
    if ( ! $scheduled_timestamp ) {
        wp_send_json_error( array(
            'message' => __( 'Invalid date/time format', 'fanx-theme' ),
        ) );
    }
    
    // Validate that scheduled time is in the future (allow 30 second grace period)
    if ( $scheduled_timestamp <= time() + 30 ) {
        wp_send_json_error( array(
            'message' => __( 'Export must be scheduled for a future time (at least 30 seconds from now)', 'fanx-theme' ),
        ) );
    }
    
    // Schedule the export
    $result = fanx_schedule_one_time_export( $scheduled_timestamp );
    
    if ( $result['success'] ) {
        wp_send_json_success( $result );
    } else {
        wp_send_json_error( $result );
    }
}

/**
 * Schedule a one-time full site export via wp-cron
 * 
 * @param int $scheduled_timestamp Unix timestamp for when to run the export
 * @return array Result array with 'success' bool and 'message' string
 */
function fanx_schedule_one_time_export( $scheduled_timestamp = null ) {
    // If no timestamp provided, use 1 hour from now
    if ( ! $scheduled_timestamp ) {
        $scheduled_timestamp = time() + 3600;
    }
    
    // Check if an export is already scheduled
    $existing_scheduled = wp_next_scheduled( 'fanx_one_time_export_cron' );
    
    if ( $existing_scheduled ) {
        // Clear existing scheduled export
        wp_unschedule_event( $existing_scheduled, 'fanx_one_time_export_cron' );
    }
    
    // Schedule the export for the specified time
    $scheduled = wp_schedule_single_event( $scheduled_timestamp, 'fanx_one_time_export_cron' );
    
    if ( false === $scheduled ) {
        return array(
            'success' => false,
            'message' => __( 'Failed to schedule export. Please try again.', 'fanx-theme' ),
        );
    }
    
    // Log the scheduling
    error_log( '[EXPORT SCHEDULER] One-time export scheduled for ' . wp_date( 'Y-m-d H:i:s', $scheduled_timestamp ) );
    
    return array(
        'success' => true,
        'message' => sprintf(
            __( 'Export scheduled! It will run at %s. The export may take several minutes.', 'fanx-theme' ),
            wp_date( 'Y-m-d H:i:s', $scheduled_timestamp )
        ),
        'scheduled_time' => wp_date( 'Y-m-d H:i:s', $scheduled_timestamp ),
    );
}

/**
 * AJAX handler to clear a scheduled export
 * 
 * Security:
 * - Requires 'manage_options' capability (admin only)
 * - Validates nonce for CSRF protection
 */
function fanx_ajax_clear_export() {
    // Check nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'fanx_schedule_export' ) ) {
        wp_send_json_error( array(
            'message' => __( 'Security check failed', 'fanx-theme' ),
        ) );
    }
    
    // Check capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array(
            'message' => __( 'You do not have permission to manage exports', 'fanx-theme' ),
        ) );
    }
    
    // Clear any scheduled exports
    $scheduled_time = wp_next_scheduled( 'fanx_one_time_export_cron' );
    
    if ( $scheduled_time ) {
        wp_unschedule_event( $scheduled_time, 'fanx_one_time_export_cron' );
        error_log( '[EXPORT SCHEDULER] Cleared scheduled export that was set for ' . wp_date( 'Y-m-d H:i:s', $scheduled_time ) );
        
        wp_send_json_success( array(
            'message' => __( 'Scheduled export cleared successfully.', 'fanx-theme' ),
        ) );
    } else {
        wp_send_json_error( array(
            'message' => __( 'No scheduled exports found to clear.', 'fanx-theme' ),
        ) );
    }
}

/**
 * Register the wp-cron hook for one-time exports
 * This fires when wp-cron detects a scheduled 'fanx_one_time_export_cron' event
 */
function fanx_register_export_cron_hook() {
    add_action( 'fanx_one_time_export_cron', 'fanx_execute_one_time_export' );
}
add_action( 'init', 'fanx_register_export_cron_hook' );

/**
 * Execute a one-time full site export (Simply Static only, no backup)
 * 
 * PROCESS:
 * 1. Runs pre-export health checks
 * 2. If checks pass, runs the full static export via Simply Static
 * 
 * All steps are logged to WordPress error log
 */
function fanx_execute_one_time_export() {
    error_log( '[EXPORT CRON] Starting one-time full site export...' );
    
    // Run pre-export health checks
    if ( function_exists( 'fanx_log_pre_export_check' ) ) {
        fanx_log_pre_export_check();
    }
    
    // Check if critical issues exist
    if ( function_exists( 'fanx_pre_export_health_check' ) ) {
        $results = fanx_pre_export_health_check();
        if ( ! $results['passed'] ) {
            error_log( '[EXPORT CRON] EXPORT ABORTED: Critical issues found' );
            error_log( '[EXPORT CRON] Issues: ' . implode( ' | ', $results['errors'] ) );
            return;
        }
    }
    
    // Execute the export via Simply Static only (no backup)
    try {
        $simply_static = \Simply_Static\Plugin::instance();
        $simply_static->run_static_export();
        error_log( '[EXPORT CRON] One-time export completed successfully' );
    } catch ( Exception $e ) {
        error_log( '[EXPORT CRON] One-time export failed with exception: ' . $e->getMessage() );
    }
}
