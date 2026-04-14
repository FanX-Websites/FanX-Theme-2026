<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * System-Level Scheduled Exports & Backups for Simply Static
 * 
 * Handles automatic, recurring export of the dynamic WordPress site to static HTML,
 * CSS, and JavaScript. Each export is immediately backed up for safekeeping.
 * 
 * SCHEDULE:
 * - FanX:      12:00 AM (midnight)
 * - ICC/TBCC:  12:00 AM (midnight)
 * 
 * EXECUTION FLOW:
 * 1. System cron calls /bin/run-scheduled-backups.php at midnight
 * 2. Script loads WordPress and calls ssp_run_static_backup_cron_cli()
 * 3. Simply Static exports the dynamic site to static files (~step 1: EXPORT)
 * 4. Exported files are backed up to wp-content/static-backups/<timestamp>/ (~step 2: BACKUP)
 * 5. Old backup archives are cleaned up automatically (~step 3: CLEANUP)
 * 
 * SETUP:
 * - DISABLE_WP_CRON = true in wp-config.php
 * - System cron configured (see SYSTEM_CRON_SETUP.md)
 * 
 * LOGS:
 * - WordPress: wp-content/debug.log
 * - System cron: /var/log/wp-backups.log
 */

// Full Static Export & Backup - CLI Callable Version
/**
 * CLI callable version of full static export & backup for system cron
 * 
 * PROCESS:
 * 1. Runs health checks before export (finds critical issues)
 * 2. Exports the WordPress site to static HTML/CSS/JS via Simply Static
 * 3. Backs up the exported files to wp-content/static-backups/<timestamp>/
 * 4. Cleans up old backup archives to save disk space
 * 
 * This function is called by /bin/run-scheduled-backups.php
 * 
 * @param string $trigger Source of trigger: 'cli' or 'cron'
 * @return bool True if export & backup succeeded, false otherwise
 */
function ssp_run_static_backup_cron_cli( $trigger = 'cli' ) {
	// Log pre-export check results
	if ( function_exists( 'fanx_log_pre_export_check' ) ) {
		fanx_log_pre_export_check();
	}
	
	// Check if critical issues exist
	if ( function_exists( 'fanx_pre_export_health_check' ) ) {
		$results = fanx_pre_export_health_check();
		if ( ! $results['passed'] ) {
			error_log( '[' . strtoupper( $trigger ) . '] EXPORT ABORTED: Critical issues found during health check.' );
			error_log( '[' . strtoupper( $trigger ) . '] Issues: ' . implode( ' | ', $results['errors'] ) );
			return false;
		}
	}
	
	try {
		$simply_static = Simply_Static\Plugin::instance();
		$simply_static->run_static_export();
		
		// Create backup of the exported files
		df_backup_static_export();
		df_cleanup_old_backups();
		
		error_log( '[' . strtoupper( $trigger ) . '] Full static export & backup completed' );
		return true;
	} catch ( Exception $e ) {
		error_log( '[' . strtoupper( $trigger ) . '] Full static export & backup failed: ' . $e->getMessage() );
		return false;
	}
}

// Update Export & Backup
/**
 * Legacy wp-cron handler for update exports - kept for backward compatibility
 * Not used when DISABLE_WP_CRON is true
 */
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
	
	// Create backup of the exported files
	df_backup_static_export();
	df_cleanup_old_backups();
}
add_action( 'update_export_event', 'ssp_run_update_export_cron' );

// Update Export & Backup - CLI Callable Version
/**
 * CLI callable version of update export & backup for system cron
 * This function is called by /bin/run-scheduled-exports.php
 * 
 * @param string $trigger Source of trigger: 'cli' or 'cron'
 * @return bool True if export & backup succeeded, false otherwise
 */
function ssp_run_update_export_cron_cli( $trigger = 'cli' ) {
	// Log pre-export check results
	if ( function_exists( 'fanx_log_pre_export_check' ) ) {
		fanx_log_pre_export_check();
	}
	
	// Check if critical issues exist
	if ( function_exists( 'fanx_pre_export_health_check' ) ) {
		$results = fanx_pre_export_health_check();
		if ( ! $results['passed'] ) {
			error_log( '[' . strtoupper( $trigger ) . '] UPDATE EXPORT ABORTED: Critical issues found during health check.' );
			error_log( '[' . strtoupper( $trigger ) . '] Issues: ' . implode( ' | ', $results['errors'] ) );
			return false;
		}
	}
	
	try {
		$simply_static = Simply_Static\Plugin::instance();
		$simply_static->run_static_export( 0, 'update' );
		
		// Create backup of the exported files
		df_backup_static_export();
		df_cleanup_old_backups();
		
		error_log( '[' . strtoupper( $trigger ) . '] Update export & backup completed' );
		return true;
	} catch ( Exception $e ) {
		error_log( '[' . strtoupper( $trigger ) . '] Update export & backup failed: ' . $e->getMessage() );
		return false;
	}
}

// Backup Static Site (Post Export)
/**
 * Create a backup of the static export directory
 * 
 * WHAT IT DOES:
 * - Copies all files from the Simply Static export directory
 * - Creates a timestamped archive in wp-content/static-backups/
 * - Sets proper file permissions for security
 * 
 * CALLED AFTER: Every successful export (both full and update exports)
 */
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
	
	$backup_dir = WP_CONTENT_DIR . '/static-backups/' . wp_date( 'Y-m-d_H-i-s' ) . '/';

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
				if ( ! chmod( $dest, 0750 ) ) {
					error_log( 'Backup: Failed to chmod directory ' . $dest );
				}
			} else {
				if ( ! copy( $file, $dest ) ) {
					error_log( 'Backup: Failed to copy file ' . $file . ' to ' . $dest );
					continue;
				}
				if ( ! chmod( $dest, 0640 ) ) {
					error_log( 'Backup: Failed to chmod file ' . $dest );
				}
				$file_count++;
			}
		}
		error_log( "Backup complete: $file_count files backed up to $backup_dir" );
		
		// Mark this as a scheduled backup by creating a marker file
		$marker_file = $backup_dir . '.scheduled';
		if ( ! file_put_contents( $marker_file, 'scheduled backup' ) ) {
			error_log( 'Backup: Failed to create marker file ' . $marker_file );
		} elseif ( ! chmod( $marker_file, 0640 ) ) {
			error_log( 'Backup: Failed to chmod marker file ' . $marker_file );
		}
	} catch ( Exception $e ) {
		error_log( 'Backup error: ' . $e->getMessage() );
	}
}

// Backup Cleanup & Retention
/**
 * Clean up old backup directories, keeping only the 12 most recent
 * Called after each successful system-level export
 */
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
