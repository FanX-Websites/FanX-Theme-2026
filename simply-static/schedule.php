<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Export Scheduler for Simply Static
 * 
 * SETUP INSTRUCTIONS:
 * 
 * 1. The ACF field group "Static Export Scheduler" is defined in:
 *    /shared-themes/FanXTheme2026/acfe-php/group_static_export.php
 * 
 * 2. Fields added to all posts:
 *    - export_datetime: Date/time picker for scheduling export
 *    - schedule_export: Button to trigger scheduled export
 *    - cancel_export: Button to cancel scheduled export
 * 
 * 3. How it works:
 *    - Editor selects date/time in export_datetime field
 *    - Editor clicks the "Schedule Export" button
 *    - WordPress schedules a one-time cron event for that datetime
 *    - Editor can cancel by clicking the "Cancel Export" button
 *    - When the datetime arrives, a full static export runs automatically
 *    - No backup is created for single post exports
 * 
 * 4. Requirements:
 *    - ACF (with button field type)
 *    - WordPress cron must be enabled
 *    - Simply Static plugin must be active
 * 
 * 5. Logs:
 *    Check wp-content/debug.log for scheduling and export status messages
 */

//WP Cron jobs and backups for Simply Static exports --------------------------->

// ACF-Triggered Single Post Export
/**
 * Handle ACF button click to schedule post-specific export
 */
function ssp_handle_acf_export_trigger( $post_id ) {
	// Bail if not an ACF save
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}
}
add_action( 'acf/save_post', 'ssp_handle_acf_export_trigger', 5 );

// AJAX handlers for button actions using ACF Extended hook
/**
 * AJAX handler for schedule export button
 */
function ssp_ajax_schedule_export( $field, $post_id ) {
	error_log( 'AJAX: ssp_ajax_schedule_export called with post_id: ' . $post_id );
	
	// Validate post ID
	if ( ! $post_id ) {
		error_log( 'AJAX: No post ID provided' );
		wp_send_json_error( array( 'message' => 'No post ID provided' ) );
	}
	
	// Schedule the export
	ssp_schedule_post_export( $post_id );
	
	// Get datetime for response
	$export_datetime = get_field( 'export_datetime', $post_id );
	error_log( 'AJAX: Export scheduled for post ' . $post_id . ' at ' . $export_datetime );
	
	wp_send_json_success( array( 
		'message' => 'Export scheduled for: ' . $export_datetime 
	) );
}
add_action( 'acfe/fields/button/name=schedule_export', 'ssp_ajax_schedule_export', 10, 2 );

/**
 * AJAX handler for cancel export button
 */
function ssp_ajax_cancel_export( $field, $post_id ) {
	error_log( 'AJAX: ssp_ajax_cancel_export called with post_id: ' . $post_id );
	
	// Validate post ID
	if ( ! $post_id ) {
		error_log( 'AJAX: No post ID provided' );
		wp_send_json_error( array( 'message' => 'No post ID provided' ) );
	}
	
	// Cancel the export
	ssp_cancel_post_export( $post_id );
	error_log( 'AJAX: Cancel scheduled for post ' . $post_id );
	
	wp_send_json_success( array( 
		'message' => 'Export cancellation scheduled' 
	) );
}
add_action( 'acfe/fields/button/name=cancel_export', 'ssp_ajax_cancel_export', 10, 2 );

// Schedule post export
/**
 * Schedule a post export based on the export_datetime field
 */
function ssp_schedule_post_export( $post_id ) {
	// Get the scheduled datetime
	$export_datetime = get_field( 'export_datetime', $post_id );
	
	if ( ! $export_datetime ) {
		error_log( 'ACF Export: No datetime provided for post ' . $post_id );
		return;
	}
	
	// Convert ACF datetime to timestamp
	// ACF returns datetime in format: "2026-03-15 14:30:00"
	$timestamp = strtotime( $export_datetime );
	
	if ( ! $timestamp || $timestamp === false ) {
		error_log( 'ACF Export: Invalid datetime format for post ' . $post_id . ': ' . $export_datetime );
		return;
	}
	
	// Schedule the single-post export event
	$event = wp_schedule_single_event( $timestamp, 'post_export_event', array( $post_id ) );
	
	if ( ! $event ) {
		error_log( 'ACF Export: Failed to schedule event for post ' . $post_id );
		return;
	}
	
	error_log( 'ACF Export: Scheduled post export for post ' . $post_id . ' at ' . $export_datetime );
}

// Cancel scheduled export
/**
 * Cancel a previously scheduled post export
 */
function ssp_cancel_post_export( $post_id ) {
	// Unschedule the post_export_event for this post
	wp_unschedule_event( wp_next_scheduled( 'post_export_event', array( $post_id ) ), 'post_export_event', array( $post_id ) );
	error_log( 'ACF Export: Cancelled scheduled export for post ' . $post_id );
}

// Single Post Export Cron Handler
/**
 * Export a specific post and related pages/archives
 * Triggered by ACF button with scheduled datetime
 */
function ssp_run_post_export_cron( $post_id ) {
	error_log( 'Starting single post export for post ID: ' . $post_id );
	
	// Validate post exists
	if ( ! get_post( $post_id ) ) {
		error_log( 'Post export failed: Post ' . $post_id . ' not found.' );
		return false;
	}
	
	// Run pre-export health check
	if ( function_exists( 'fanx_log_pre_export_check' ) ) {
		fanx_log_pre_export_check();
	}
	
	if ( function_exists( 'fanx_pre_export_health_check' ) ) {
		$results = fanx_pre_export_health_check();
		if ( ! $results['passed'] ) {
			error_log( 'POST EXPORT ABORTED for post ' . $post_id . ': Critical issues found during health check.' );
			error_log( 'Issues: ' . implode( ' | ', $results['errors'] ) );
			return false;
		}
	}
	
	// For single post export, we'll run a full static export
	// (Simply Static doesn't have built-in single-page export, so we export all)
	try {
		$simply_static = Simply_Static\Plugin::instance();
		$simply_static->run_static_export();
		
		error_log( 'Single post export completed for post ID: ' . $post_id );
		return true;
	} catch ( Exception $e ) {
		error_log( 'Single post export failed for post ' . $post_id . ': ' . $e->getMessage() );
		return false;
	}
}
add_action( 'post_export_event', 'ssp_run_post_export_cron' );
// END  Single Post Exports - using ACF Post Widget  <----------------------------

// Full Static Export (with pre-export health check)
function ssp_run_static_export_cron() {
	// Log pre-export check results
	if ( function_exists( 'fanx_log_pre_export_check' ) ) {
		fanx_log_pre_export_check();
	}
	
	// Check if critical issues exist
	if ( function_exists( 'fanx_pre_export_health_check' ) ) {
		$results = fanx_pre_export_health_check();
		if ( ! $results['passed'] ) {
			error_log( 'EXPORT ABORTED: Critical issues found during health check.' );
			error_log( 'Issues: ' . implode( ' | ', $results['errors'] ) );
			return false;
		}
	}
	
	$simply_static = Simply_Static\Plugin::instance();
	$simply_static->run_static_export();
	
	// Create backup only for scheduled cron jobs
	df_backup_static_export();
	df_cleanup_old_backups();
}
add_action( 'static_export_event', 'ssp_run_static_export_cron' );

// Schedule Update Export (with pre-export health check)
function ssp_run_update_export_cron() {
	// Log pre-export check results
	if ( function_exists( 'fanx_log_pre_export_check' ) ) {
		fanx_log_pre_export_check();
	}
	
	// Check if critical issues exist
	if ( function_exists( 'fanx_pre_export_health_check' ) ) {
		$results = fanx_pre_export_health_check();
		if ( ! $results['passed'] ) {
			error_log( 'UPDATE EXPORT ABORTED: Critical issues found during health check.' );
			error_log( 'Issues: ' . implode( ' | ', $results['errors'] ) );
			return false;
		}
	}
	
	$simply_static = Simply_Static\Plugin::instance();
	$simply_static->run_static_export( 0, 'update' );
	
	// Create backup only for scheduled cron jobs
	df_backup_static_export();
	df_cleanup_old_backups();
}
add_action( 'update_export_event', 'ssp_run_update_export_cron' );

// Backup Static Site (Post Export)
function df_backup_static_export() {
	error_log( 'Backup function called: df_backup_static_export' );
	
	$simply_static_options = get_option( 'simply-static' );
	
	if ( ! $simply_static_options || ! is_array( $simply_static_options ) ) {
		error_log( 'Backup failed: No Simply Static settings found.' );
		return;
	}
	
	$export_dir = isset( $simply_static_options['local_dir'] ) ? $simply_static_options['local_dir'] : null;
	
	if ( ! $export_dir ) {
		error_log( 'Backup failed: local_dir not set in Simply Static options.' );
		return;
	}
	
	if ( ! is_dir( $export_dir ) ) {
		error_log( 'Backup failed: Export directory does not exist: ' . $export_dir );
		return;
	}
	
	$backup_dir = WP_CONTENT_DIR . '/static-backups/' . date( 'Y-m-d_H-i-s' ) . '/';

	if ( ! is_dir( $backup_dir ) ) {
		wp_mkdir_p( $backup_dir );
	}

	try {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $export_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		$file_count = 0;
		foreach ( $iterator as $file ) {
			$dest = $backup_dir . $iterator->getSubPathname();
			if ( $file->isDir() ) {
				wp_mkdir_p( $dest );
				@chmod( $dest, 0755 );
			} else {
				copy( $file, $dest );
				@chmod( $dest, 0644 );
				$file_count++;
			}
		}
		error_log( "Backup complete: $file_count files backed up to $backup_dir" );
		
		// Mark this as a scheduled backup by creating a marker file
		file_put_contents( $backup_dir . '.scheduled', 'scheduled backup' );
		@chmod( $backup_dir . '.scheduled', 0644 );
	} catch ( Exception $e ) {
		error_log( 'Backup error: ' . $e->getMessage() );
	}
}

// Backup Cleanup & Retention
function df_cleanup_old_backups() {
	$backup_base = WP_CONTENT_DIR . '/static-backups/';
	
	if ( ! is_dir( $backup_base ) ) {
		error_log( 'Backup directory does not exist: ' . $backup_base );
		return;
	}
	
	$backups = glob( $backup_base . '*', GLOB_ONLYDIR );
	
	if ( ! $backups || count( $backups ) <= 12 ) {
		return;
	}
	
	sort( $backups );
	$to_delete = array_slice( $backups, 0, count( $backups ) - 12 );
	
	foreach ( $to_delete as $old_backup ) {
		try {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $old_backup, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ( $files as $file ) {
				if ( $file->isDir() ) {
					@rmdir( $file );
				} else {
					@unlink( $file );
				}
			}
			@rmdir( $old_backup );
			error_log( 'Deleted old backup: ' . $old_backup );
		} catch ( Exception $e ) {
			error_log( 'Failed to delete backup ' . $old_backup . ': ' . $e->getMessage() );
		}
	}
}