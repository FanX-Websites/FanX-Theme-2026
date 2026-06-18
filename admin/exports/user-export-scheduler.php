<?php
/**
 * Export Scheduler - One-Time Export Scheduling via System Cron
 * 
 * Handles scheduling and execution of one-time full site exports
 * Uses system cron (10-minute cycle) instead of WordPress cron
 * 
 * QUEUE STORAGE:
 * - wp_option 'fanx_scheduled_user_export': timestamp of when to execute
 * - wp_option 'fanx_last_user_export': tracks execution status (pending/success/failed)
 * 
 * EXECUTION:
 * - Called by run-wp-cron-events.php via fanx_execute_due_user_exports()
 * - Runs on 10-minute cycle, same as post exports
 * 
 * AJAX ENDPOINTS:
 * - fanx_ajax_schedule_export: Schedule a new export
 * - fanx_ajax_clear_export: Clear a scheduled export
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
 * Schedule a one-time full site export via system cron
 * 
 * Stores the scheduled timestamp in wp_options for system cron to pick up
 * on the 10-minute cycle. The actual execution happens in run-wp-cron-events.php
 * which calls fanx_execute_due_user_exports().
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
    $existing_scheduled = get_option( 'fanx_scheduled_user_export' );
    
    if ( $existing_scheduled ) {
        // Log that we're replacing existing schedule
        error_log( '[EXPORT SCHEDULER] Replacing existing export scheduled for ' . wp_date( 'Y-m-d H:i:s', $existing_scheduled ) );
    }
    
    // Queue the export for system cron to execute (store timestamp in wp_options)
    update_option( 'fanx_scheduled_user_export', $scheduled_timestamp );
    
    // Save export state to wp_options for widget tracking
    $export_state = array(
        'scheduled_time' => wp_date( 'Y-m-d H:i:s', $scheduled_timestamp ),
        'scheduled_timestamp' => $scheduled_timestamp,
        'status' => 'pending',
        'executed_at' => null,
    );
    update_option( 'fanx_last_user_export', $export_state );
    
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
    $scheduled_timestamp = get_option( 'fanx_scheduled_user_export' );
    
    if ( $scheduled_timestamp ) {
        delete_option( 'fanx_scheduled_user_export' );
        error_log( '[EXPORT SCHEDULER] Cleared scheduled export that was set for ' . wp_date( 'Y-m-d H:i:s', $scheduled_timestamp ) );
        
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
 * Execute a one-time full site export (Simply Static only, no backup)
 * 
 * NOTE: This function is no longer called by WordPress cron.
 * Execution is now handled by system cron via /bin/run-wp-cron-events.php
 * calling fanx_execute_due_user_exports() every 10 minutes.
 * 
 * This function is kept for backward compatibility but should not be used.
 * 
 * @deprecated Use fanx_execute_due_user_exports() instead
 */
function fanx_execute_one_time_export() {
    error_log( '[EXPORT CRON] WARNING: fanx_execute_one_time_export() called directly but should use system cron instead' );
    return;
}

/**
 * Check for and execute due user-scheduled exports (System Cron)
 * 
 * Called by /bin/run-wp-cron-events.php on 10-minute cycle.
 * 
 * PROCESS:
 * 1. Checks wp_option 'fanx_scheduled_user_export' for scheduled timestamp
 * 2. If current time >= scheduled time, executes the export
 * 3. Runs pre-export health checks before executing
 * 4. Updates status in 'fanx_last_user_export' wp_option (pending/success/failed)
 * 5. Logs all actions to WordPress debug log
 * 
 * @return void
 */
function fanx_execute_due_user_exports() {
	// Get scheduled export timestamp
	$scheduled_timestamp = get_option( 'fanx_scheduled_user_export' );
	
	// No export scheduled, nothing to do
	if ( ! $scheduled_timestamp ) {
		return;
	}
	
	// Export not due yet
	$current_time = current_time( 'timestamp' );
	if ( $current_time < $scheduled_timestamp ) {
		return;
	}
	
	// Time has arrived, execute the export
	error_log( '[USER EXPORT] Executing scheduled user export' );
	
	// Log pre-export check results
	if ( function_exists( 'fanx_log_pre_export_check' ) ) {
		fanx_log_pre_export_check();
	}
	
	// Check if critical issues exist
	if ( function_exists( 'fanx_pre_export_health_check' ) ) {
		$results = fanx_pre_export_health_check();
		if ( ! $results['passed'] ) {
			error_log( '[USER EXPORT] EXPORT ABORTED: Critical issues found during health check.' );
			error_log( '[USER EXPORT] Issues: ' . implode( ' | ', $results['errors'] ) );
			
			// Update export state to failed
			$export_state = get_option( 'fanx_last_user_export', array() );
			$export_state['status'] = 'failed';
			$export_state['executed_at'] = wp_date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
			$export_state['reason'] = 'Health check failed: ' . implode( ' | ', $results['errors'] );
			update_option( 'fanx_last_user_export', $export_state );
			
			// Clear the scheduled timestamp
			delete_option( 'fanx_scheduled_user_export' );
			
			return;
		}
	}
	
	// Execute the export
	try {
		$simply_static = \Simply_Static\Plugin::instance();
		$simply_static->run_static_export();
		
		error_log( '[USER EXPORT] User-scheduled export completed successfully' );
		
		// Update export state to success
		$export_state = get_option( 'fanx_last_user_export', array() );
		$export_state['status'] = 'success';
		$export_state['executed_at'] = wp_date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
		update_option( 'fanx_last_user_export', $export_state );
	} catch ( Exception $e ) {
		error_log( '[USER EXPORT] User-scheduled export failed with exception: ' . $e->getMessage() );
		
		// Update export state to failed
		$export_state = get_option( 'fanx_last_user_export', array() );
		$export_state['status'] = 'failed';
		$export_state['executed_at'] = wp_date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
		$export_state['reason'] = 'Export failed: ' . $e->getMessage();
		update_option( 'fanx_last_user_export', $export_state );
	}
	
	// Clear the scheduled timestamp (export is done, whether success or failed)
	delete_option( 'fanx_scheduled_user_export' );
}
