<?php
/**
 * Post Export Scheduler - Single Post/Page Export via Custom Table
 * 
 * Handles scheduling and execution of single post/page exports
 * Uses custom database table (fanx_scheduled_post_exports) instead of wp-cron
 * Executor: Runs automatically every 30 minutes when wp-cron.php is called
 * 
 * DATABASE:
 * - Table: {prefix}fanx_scheduled_post_exports
 * - Stores: post_id, post_title, post_type, scheduled_time, status, execution_time
 * 
 * EXECUTION:
 * - When WordPress loads during system cron (wp-cron.php calls), init hook fires
 * - fanx_check_post_exports_on_cron() checks for due exports
 * - No separate cron entries needed—reuses existing 30-minute wp-cron schedule
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register AJAX handlers for post export scheduling
 */
function fanx_register_post_export_scheduler_ajax() {
    add_action( 'wp_ajax_fanx_schedule_post_export', 'fanx_ajax_schedule_post_export' );
    add_action( 'wp_ajax_fanx_cancel_post_export', 'fanx_ajax_cancel_post_export' );
}
add_action( 'admin_init', 'fanx_register_post_export_scheduler_ajax' );

/**
 * AJAX handler to schedule a single post export
 * 
 * Security:
 * - Requires 'edit_posts' capability
 * - Validates nonce for CSRF protection
 * - Sanitizes all input
 * 
 * POST Parameters:
 * - nonce: Security nonce for CSRF protection
 * - post_id: The post ID to export
 * - scheduled_time: ISO 8601 datetime string (e.g., '2026-04-15T14:30')
 */
function fanx_ajax_schedule_post_export() {
    // Check nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'fanx_schedule_post_export' ) ) {
        wp_send_json_error( array(
            'message' => __( 'Security check failed', 'fanx-theme' ),
        ) );
    }
    
    // Check capabilities
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array(
            'message' => __( 'You do not have permission to schedule exports', 'fanx-theme' ),
        ) );
    }
    
    // Get and validate post ID
    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    error_log( '[POST EXPORT SCHEDULER] AJAX received post_id: ' . $post_id );
    
    if ( ! $post_id || ! get_post( $post_id ) ) {
        error_log( '[POST EXPORT SCHEDULER] Invalid post_id or post not found' );
        wp_send_json_error( array(
            'message' => __( 'Invalid post ID', 'fanx-theme' ),
        ) );
    }
    
    // Verify user can edit this post
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( array(
            'message' => __( 'You do not have permission to edit this post', 'fanx-theme' ),
        ) );
    }
    
    // Get and validate scheduled time
    $scheduled_time_str = isset( $_POST['scheduled_time'] ) ? sanitize_text_field( $_POST['scheduled_time'] ) : '';
    error_log( '[POST EXPORT SCHEDULER] Scheduled time string: ' . $scheduled_time_str );
    
    // Parse the datetime string (format: 2026-04-15T14:30)
    $scheduled_timestamp = strtotime( $scheduled_time_str );
    error_log( '[POST EXPORT SCHEDULER] Parsed timestamp: ' . $scheduled_timestamp );
    
    if ( ! $scheduled_timestamp ) {
        error_log( '[POST EXPORT SCHEDULER] Failed to parse datetime' );
        wp_send_json_error( array(
            'message' => __( 'Invalid date/time format', 'fanx-theme' ),
        ) );
    }
    
    // Validate that scheduled time is in the future (allow 30 second grace period)
    if ( $scheduled_timestamp <= time() + 30 ) {
        error_log( '[POST EXPORT SCHEDULER] Time is in the past or too soon: ' . $scheduled_timestamp . ' vs ' . (time() + 30) );
        wp_send_json_error( array(
            'message' => __( 'Export must be scheduled for a future time (at least 30 seconds from now)', 'fanx-theme' ),
        ) );
    }
    
    // Schedule the export
    $result = fanx_schedule_one_time_post_export( $post_id, $scheduled_timestamp );
    
    if ( $result['success'] ) {
        wp_send_json_success( $result );
    } else {
        wp_send_json_error( $result );
    }
}

/**
 * Schedule a single post export via custom database table
 * 
 * @param int $post_id The post ID to export
 * @param int $scheduled_timestamp Unix timestamp for when to run the export
 * @return array Result array with 'success' bool and 'message' string
 */
function fanx_schedule_one_time_post_export( $post_id, $scheduled_timestamp = null ) {
    error_log( '[POST EXPORT SCHEDULER] fanx_schedule_one_time_post_export called with post_id: ' . $post_id . ', timestamp: ' . $scheduled_timestamp );
    
    if ( ! $post_id ) {
        error_log( '[POST EXPORT SCHEDULER] Invalid post_id: ' . $post_id );
        return array(
            'success' => false,
            'message' => __( 'Invalid post ID', 'fanx-theme' ),
        );
    }
    
    // If no timestamp provided, use 1 hour from now
    if ( ! $scheduled_timestamp ) {
        $scheduled_timestamp = time() + 3600;
    }
    
    // Insert into custom table (replaces wp-cron)
    $result = fanx_insert_scheduled_post_export( $post_id, $scheduled_timestamp );
    
    if ( ! $result['success'] ) {
        error_log( '[POST EXPORT SCHEDULER] Failed to schedule export: ' . $result['message'] );
        return $result;
    }
    
    // Log the scheduling
    $post_title = get_the_title( $post_id );
    error_log( '[POST EXPORT SCHEDULER] Post export scheduled: "' . $post_title . '" (ID: ' . $post_id . ') for ' . wp_date( 'Y-m-d H:i:s', $scheduled_timestamp ) );
    
    return array(
        'success' => true,
        'message' => sprintf(
            __( 'Export scheduled! "%s" will be exported at %s.', 'fanx-theme' ),
            esc_html( $post_title ),
            wp_date( 'M d, Y g:i A', $scheduled_timestamp )
        ),
        'scheduled_time' => wp_date( 'Y-m-d H:i:s', $scheduled_timestamp ),
    );
}

/**
 * AJAX handler to cancel a scheduled post export
 * 
 * Security:
 * - Requires 'edit_posts' capability
 * - Validates nonce for CSRF protection
 */
function fanx_ajax_cancel_post_export() {
    // Check nonce
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'fanx_schedule_post_export' ) ) {
        wp_send_json_error( array(
            'message' => __( 'Security check failed', 'fanx-theme' ),
        ) );
    }
    
    // Check capabilities
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array(
            'message' => __( 'You do not have permission to manage exports', 'fanx-theme' ),
        ) );
    }
    
    // Get and validate post ID
    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    if ( ! $post_id ) {
        wp_send_json_error( array(
            'message' => __( 'Invalid post ID', 'fanx-theme' ),
        ) );
    }
    
    // Verify user can edit this post
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( array(
            'message' => __( 'You do not have permission to edit this post', 'fanx-theme' ),
        ) );
    }
    
    // Cancel scheduled export via custom table (replaces wp-cron)
    $result = fanx_cancel_scheduled_post_export( $post_id );
    
    if ( $result['success'] ) {
        wp_send_json_success( $result );
    } else {
        wp_send_json_error( $result );
    }
}

/**
 * Execute all due scheduled post exports (called by system cron)
 * 
 * This replaces the wp-cron hook approach and is called directly from
 * /bin/run-wp-cron-events.php which is triggered by the system crontab
 * 
 * PROCESS:
 * 1. Query the custom table for all pending exports with scheduled_time <= now
 * 2. For each due export, export the post and mark the record as completed
 * 3. Log results to error log
 */
function fanx_execute_due_post_exports() {
    // Get all due scheduled post exports
    $due_exports = fanx_get_due_scheduled_post_exports();
    
    if ( empty( $due_exports ) ) {
        error_log( '[POST EXPORT CRON] No due scheduled post exports to execute' );
        return;
    }
    
    error_log( '[POST EXPORT CRON] Found ' . count( $due_exports ) . ' due scheduled post export(s)' );
    
    foreach ( $due_exports as $export ) {
        $post_id = absint( $export->post_id );
        $export_id = absint( $export->id );
        $post_title = $export->post_title;
        
        error_log( '[POST EXPORT CRON] Executing export for post: "' . $post_title . '" (ID: ' . $post_id . ')' );
        
        try {
            // Validate post still exists
            if ( ! get_post( $post_id ) ) {
                error_log( '[POST EXPORT CRON] Post no longer exists (ID: ' . $post_id . ')' );
                fanx_mark_post_export_executed( $export_id, false, 'Post no longer exists' );
                continue;
            }
            
            // Export single post via Simply Static
            if ( class_exists( '\Simply_Static\Plugin' ) ) {
                $simply_static = \Simply_Static\Plugin::instance();
                $simply_static->run_static_export();
                
                error_log( '[POST EXPORT CRON] Successfully exported post "' . $post_title . '" (ID: ' . $post_id . ')' );
                fanx_mark_post_export_executed( $export_id, true );
            } else {
                error_log( '[POST EXPORT CRON] Simply Static plugin not available' );
                fanx_mark_post_export_executed( $export_id, false, 'Simply Static plugin not available' );
            }
        } catch ( Exception $e ) {
            error_log( '[POST EXPORT CRON] Post export failed for "' . $post_title . '": ' . $e->getMessage() );
            fanx_mark_post_export_executed( $export_id, false, $e->getMessage() );
        }
    }
    
    // Clean up old records
    fanx_cleanup_old_scheduled_post_exports();
}
