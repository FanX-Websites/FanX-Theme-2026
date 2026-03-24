<?php
/*  WordPress Backups Feed for Backup Widget
*  Description: Displays list of WordPress installation backups from /wordpress-backups/
*  Created by: Daily backup script (backup-wordpress-daily.sh)
*/

// Display WordPress backups for current site only
function df_display_wordpress_backups_feed() {
	echo '<p><i>Daily WordPress installation backups for this site. Kept for 7 days.</i></p>';
	
	// Display next scheduled backup
	df_display_next_wordpress_backup_info();
	
	// Detect current site from WordPress path
	$current_site = df_get_current_site_name();
	
	if ( ! $current_site ) {
		echo '<p style="color: #999;"><em>Could not determine site name.</em></p>';
		return;
	}
	
	$backup_base = '/home/ashelizmoore/wordpress-backups';
	$site_backup_dir = $backup_base . '/' . $current_site;
	
	if ( ! is_dir( $site_backup_dir ) ) {
		echo '<p style="color: #999;"><em>No backups found for this site.</em></p>';
		return;
	}
	
	// Get backups for current site
	$backups = glob( $site_backup_dir . '/wordpress-' . $current_site . '-*.tar.gz' );
	
	if ( empty( $backups ) ) {
		echo '<p style="color: #999;"><em>No backup files found.</em></p>';
		return;
	}
	
	// Sort by modification time, most recent first
	usort( $backups, function( $a, $b ) {
		return filemtime( $b ) - filemtime( $a );
	});
	
	// Display backup list
	echo '<div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 8px;">';
	echo '<ul style="margin: 0; padding-left: 20px;">';
	
	foreach ( $backups as $backup ) {
		$size = df_format_size_to_human( filesize( $backup ) );
		$modified = filemtime( $backup );
		$date = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $modified );
		$filename = basename( $backup );
		
		echo '<li style="margin-bottom: 8px;">';
		echo '<strong>' . esc_html( $date ) . '</strong><br />';
		echo '<small style="color: #666;">' . esc_html( $filename ) . ' • ' . esc_html( $size ) . '</small>';
		echo '</li>';
	}
	
	echo '</ul>';
	echo '</div>';
}

// Display next scheduled WordPress backup time and status
function df_display_next_wordpress_backup_info() {
	echo '<div style="background: #f9f9f9; 
					padding: 12px; 
					border-radius: 4px; 
					margin-bottom: 16px; 
					border-left: 4px solid #2271b1;">';
	
	// Calculate next backup time (2:00 AM daily)
	$next_backup = df_calculate_next_backup_time();
	$next_date = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_backup );
	
	// Check if backup completed successfully today
	$current_site = df_get_current_site_name();
	$backup_status = df_check_backup_status( $current_site );
	
	// Display next backup time
	echo '<div style="margin-bottom: 10px; font-size: 13px;">';
	echo '<strong>Next Scheduled Backup:</strong> <span style="color: #666;">' . esc_html( $next_date ) . '</span>';
	echo '</div>';
	
	// Display backup status
	echo '<div style="font-size: 13px;">';
	if ( $backup_status['success'] ) {
		echo '<strong style="color: #27ae60;">✓ Last Backup Successful</strong><br />';
		echo '<small style="color: #666;">Completed: ' . esc_html( $backup_status['last_backup_date'] ) . '</small>';
	} else {
		echo '<strong style="color: #e74c3c;">✗ No Recent Backup</strong><br />';
		echo '<small style="color: #666;">Last backup may have failed or is pending.</small>';
	}
	echo '</div>';
	
	echo '</div>';
}

// Calculate next WordPress backup time (2:00 AM daily)
function df_calculate_next_backup_time() {
	$backup_hour = 2; // 2:00 AM
	$backup_minute = 0;
	
	// Get current time
	$current_time = current_time( 'timestamp' );
	$today_backup = mktime( $backup_hour, $backup_minute, 0, date( 'm', $current_time ), date( 'd', $current_time ), date( 'Y', $current_time ) );
	
	// If backup time has already passed today, schedule for tomorrow
	if ( $current_time >= $today_backup ) {
		$next_backup = $today_backup + ( 24 * 60 * 60 );
	} else {
		$next_backup = $today_backup;
	}
	
	return $next_backup;
}

// Check if backup completed successfully today
function df_check_backup_status( $site_name ) {
	if ( ! $site_name ) {
		return array( 'success' => false, 'last_backup_date' => 'Unknown' );
	}
	
	$backup_base = '/home/ashelizmoore/wordpress-backups';
	$site_backup_dir = $backup_base . '/' . $site_name;
	
	if ( ! is_dir( $site_backup_dir ) ) {
		return array( 'success' => false, 'last_backup_date' => 'Never' );
	}
	
	// Get latest backup
	$backups = glob( $site_backup_dir . '/wordpress-' . $site_name . '-*.tar.gz' );
	
	if ( empty( $backups ) ) {
		return array( 'success' => false, 'last_backup_date' => 'Never' );
	}
	
	// Sort by modification time
	usort( $backups, function( $a, $b ) {
		return filemtime( $b ) - filemtime( $a );
	});
	
	$latest = $backups[0];
	$modified = filemtime( $latest );
	$size = filesize( $latest );
	
	// Check if backup is from today and has reasonable size
	$today_start = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );
	$is_recent = $modified >= $today_start;
	$has_size = $size > 1000; // At least 1KB
	
	$success = $is_recent && $has_size;
	$last_backup_date = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $modified );
	
	return array( 'success' => $success, 'last_backup_date' => $last_backup_date );
}

// Detect current WordPress site name from installation path
function df_get_current_site_name() {
	// Get the WordPress installation base path
	$wp_path = rtrim( ABSPATH, '/' );
	
	// Map of site paths to site names
	$site_mapping = array(
		'/home/ashelizmoore/fillory/fanx' => 'fanx',
		'/home/ashelizmoore/fillory/TBCC' => 'tbcc',
		'/home/ashelizmoore/fillory/ICC' => 'icc',
		'/home/ashelizmoore/fillory/ATL' => 'atl',
		'/home/ashelizmoore/fillory/Hub' => 'hub',
		'/home/ashelizmoore/fillory/dev' => 'dev',
	);
	
	// Check if current path matches any known site
	foreach ( $site_mapping as $path => $site_name ) {
		if ( strpos( $wp_path, $path ) === 0 ) {
			return $site_name;
		}
	}
	
	return null;
}

