<?php
/*  Admin Dashboard Widget: Static Backups
*  Description: A simple widget to display the latest static site backups 
//INFO: File location: /wp-content/static-backups
//NOTE: Static Backups are generated as part of the export process (see schedule.php) 
*/


//Dashboard Feed Widget Registration
function df_reg_backup_widget() {
	global $wp_meta_boxes;

	wp_add_dashboard_widget('widget_static_backups', __('Static Site Backup Feed', 'df'), 'df_create_static_backup_box'); 
}
add_action('wp_dashboard_setup', 'df_reg_backup_widget');

//Message Text 
function df_create_static_backup_box() {
	echo '<p><i>Monitor Backups for scheduled static exports here. Alert WebDev to any issues or errors that require reverting the site to a previous version.</i></p>'; 
	
	// Display next scheduled cron runs
	df_display_next_cron_runs();
	
	$backup_dir = WP_CONTENT_DIR . '/static-backups/'; // File path: /wp-content/static-backups
	
	if ( ! is_dir( $backup_dir ) ) {
		echo '<p style="color: #999;"><em>No backups directory found.</em></p>';
		return;
	}
	
	$backups = array_diff( scandir( $backup_dir, SCANDIR_SORT_DESCENDING ), array( '.', '..' ) );
	
	if ( empty( $backups ) ) {
		echo '<p style="color: #999;"><em>No backup files found.</em></p>';
		return;
	}
	
	//Backup List with Date, Size, and Filename
	echo '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 8px;">';
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

// Display next scheduled cron run times
function df_display_next_cron_runs() {
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
			echo '<div style="margin-bottom: 6px; font-size: 13px;">';
			echo '<strong>' . esc_html( $label ) . ':</strong> <span style="color: #666;">' . esc_html( $next_date ) . '</span>';
			echo '</div>';
		}
	}
	
	if ( ! $has_scheduled ) {
		echo '<span style="color: #999; font-size: 13px;"><em>No cron jobs scheduled</em></span>';
	}
	
	echo '</div>';
}