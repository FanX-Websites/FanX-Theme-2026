<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export Scheduler for Simply Static
 *
 * Manages cron jobs and backups for Simply Static exports
 */

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
			} else {
				copy( $file, $dest );
				$file_count++;
			}
		}
		error_log( "Backup complete: $file_count files backed up to $backup_dir" );
	} catch ( Exception $e ) {
		error_log( 'Backup error: ' . $e->getMessage() );
	}
}
add_action( 'simply_static_finished', 'df_backup_static_export' );

// Also hook to the correct Simply Static completion hooks
add_action( 'ss_completed', function( $status, $message = '' ) {
	if ( $status === 'success' || $status === true ) {
		df_backup_static_export();
		df_cleanup_old_backups();
	}
}, 10, 2 );

// Backup Cleanup & Retention
function df_cleanup_old_backups() {
	$backup_base = WP_CONTENT_DIR . '/static-backups/';
	
	if ( ! is_dir( $backup_base ) ) {
		error_log( 'Backup directory does not exist: ' . $backup_base );
		return;
	}
	
	$backups = glob( $backup_base . '*', GLOB_ONLYDIR );
	
	if ( ! $backups || count( $backups ) <= 7 ) {
		return;
	}
	
	sort( $backups );
	$to_delete = array_slice( $backups, 0, count( $backups ) - 7 );
	
	foreach ( $to_delete as $old_backup ) {
		try {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $old_backup, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ( $files as $file ) {
				$file->isDir() ? rmdir( $file ) : unlink( $file );
			}
			rmdir( $old_backup );
			error_log( 'Deleted old backup: ' . $old_backup );
		} catch ( Exception $e ) {
			error_log( 'Failed to delete backup ' . $old_backup . ': ' . $e->getMessage() );
		}
	}
}
add_action( 'simply_static_finished', 'df_cleanup_old_backups' );
add_action( 'ss_finished_transferring_files_locally', 'df_cleanup_old_backups' );