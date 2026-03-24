<?php
/*  Static Backups Feed for Backup Widget
*  Description: Displays list of static site backups from /wp-content/static-backups/
*  Location: /wp-content/static-backups
*  Note: Static Backups are generated as part of the export process (see schedule.php) 
*/

// Display next scheduled static export runs
function df_display_static_export_info() {
	echo '<div style="background: #f9f9f9; 
					padding: 12px; 
					border-radius: 4px; 
					margin-bottom: 16px; 
					border-left: 4px solid #2271b1;">';
	
	$cron_hooks = array(
		'static_export_event' => 'Next Scheduled Export'
	);
	
	$has_scheduled = false;
	
	foreach ( $cron_hooks as $hook => $label ) {
		$next_run = wp_next_scheduled( $hook );
		
		if ( $next_run ) {
			$has_scheduled = true;
			$next_date = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_run );
			echo '<div style="margin-bottom: 10px; font-size: 13px;">';
			echo '<strong>' . esc_html( $label ) . ':</strong> <span style="color: #666;">' . esc_html( $next_date ) . '</span>';
			echo '</div>';
		}
	}
	
	if ( ! $has_scheduled ) {
		echo '<span style="color: #999; font-size: 13px;"><em>No cron jobs scheduled</em></span>';
	}
	
	// Display backup status
	$backup_status = df_check_static_backup_status();
	
	echo '<div style="font-size: 13px;">';
	if ( $backup_status['success'] ) {
		echo '<strong style="color: #27ae60;">✓ Last Backup Successful</strong><br />';
		echo '<small style="color: #666;">Completed: ' . esc_html( $backup_status['last_backup_date'] ) . '</small>';
	} else {
		echo '<strong style="color: #e74c3c;">✗ No Recent Backup</strong><br />';
		echo '<small style="color: #666;">Last export may have failed or is pending.</small>';
	}
	echo '</div>';
	
	echo '</div>';
}

// Check if static backup completed successfully today
function df_check_static_backup_status() {
	$backup_dir = WP_CONTENT_DIR . '/static-backups/';
	
	if ( ! is_dir( $backup_dir ) ) {
		return array( 'success' => false, 'last_backup_date' => 'Never' );
	}
	
	$backups = array_diff( scandir( $backup_dir, SCANDIR_SORT_DESCENDING ), array( '.', '..' ) );
	
	if ( empty( $backups ) ) {
		return array( 'success' => false, 'last_backup_date' => 'Never' );
	}
	
	// Get the most recent backup
	$latest_backup = $backups[0];
	$backup_path = $backup_dir . $latest_backup;
	$modified = filemtime( $backup_path );
	$size = df_get_directory_size( $backup_path );
	
	// Check if backup is from today and has reasonable size
	$today_start = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
	$is_recent = $modified >= $today_start;
	$has_size = $size > 1000; // At least 1KB
	
	$success = $is_recent && $has_size;
	$last_backup_date = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $modified );
	
	return array( 'success' => $success, 'last_backup_date' => $last_backup_date );
}

// Display static backups list
function df_display_static_backups_feed() {
	echo '<p><i>Monitor static site backups from Simply Static exports. Alert WebDev to any issues.</i></p>';
	
	// Display next scheduled exports and status
	df_display_static_export_info();
	
	$backup_dir = WP_CONTENT_DIR . '/static-backups/';
	
	if ( ! is_dir( $backup_dir ) ) {
		echo '<p style="color: #999;"><em>No backups directory found.</em></p>';
		return;
	}
	
	$backups = array_diff( scandir( $backup_dir, SCANDIR_SORT_DESCENDING ), array( '.', '..' ) );
	
	if ( empty( $backups ) ) {
		echo '<p style="color: #999;"><em>No backup files found.</em></p>';
		return;
	}
	
	// Backup List with Date, Size, and Filename
	echo '<div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 8px;">';
	echo '<ul style="margin: 0; padding-left: 20px;">';
	
	foreach ( $backups as $backup ) {
		$backup_path = $backup_dir . $backup;
		$size = df_format_size_to_human( df_get_directory_size( $backup_path ) );
		$modified = filemtime( $backup_path );
		$date = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $modified );
		
		echo '<li style="margin-bottom: 8px;">';
		echo '<strong>' . esc_html( $date ) . '</strong><br />';
		echo '<small style="color: #666;">' . esc_html( $backup ) . ' • ' . esc_html( $size ) . '</small>';
		echo '</li>';
	}
	
	echo '</ul>';
	echo '</div>';
}

// Get the size of a directory recursively
function df_get_directory_size( $dir ) {
	$size = 0;
	
	if ( is_file( $dir ) ) {
		return filesize( $dir );
	}
	
	if ( ! is_dir( $dir ) || ! is_readable( $dir ) ) {
		return 0;
	}
	
	$files = array_diff( scandir( $dir ), array( '.', '..' ) );
	
	foreach ( $files as $file ) {
		$path = $dir . '/' . $file;
		if ( is_file( $path ) ) {
			$size += filesize( $path );
		} elseif ( is_dir( $path ) ) {
			$size += df_get_directory_size( $path );
		}
	}
	
	return $size;
}

// Convert bytes to human readable format
function df_format_size_to_human( $bytes ) {
	$units = array( 'B', 'KB', 'MB', 'GB' );
	$bytes = max( $bytes, 0 );
	$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
	$pow = min( $pow, count( $units ) - 1 );
	$bytes /= ( 1 << ( 10 * $pow ) );
	
	return round( $bytes, 2 ) . ' ' . $units[ $pow ];
}
