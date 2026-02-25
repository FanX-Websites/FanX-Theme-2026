<?php
/*  Admin Dashboard Widget: Static Backups
*  Description: A simple widget to display the latest static site backups 
//INFO: File location: /wp-content/static-backups
//NOTE: Static Backups are generated as part of the export process (see schedule.php) 
*/

function df_reg_backup_widget() {
	global $wp_meta_boxes;

	wp_add_dashboard_widget('widget_static_backups', __('Static Site Backup Feed', 'df'), 'df_create_static_backup_box');
}
add_action('wp_dashboard_setup', 'df_reg_backup_widget');

function df_create_static_backup_box() {
	echo '<p><i>Monitor Static Site Backups Here. The Web Dev can find these backups in the /wp-content/static-backups directory.</i></p>';
	
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
	
	echo '<ul style="margin: 0; padding-left: 20px;">';
	
	foreach ( $backups as $backup ) {
		$backup_path = $backup_dir . $backup;
		$size = df_format_size_to_human( df_get_directory_size( $backup_path ) );
		$modified = filemtime( $backup_path );
		$date = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $modified );
		
		echo '<li style="margin-bottom: 8px;">';
		echo '<strong>' . esc_html( $backup ) . '</strong><br />';
		echo '<small style="color: #666;">' . esc_html( $size ) . ' • ' . esc_html( $date ) . '</small>';
		echo '</li>';
	}
	
	echo '</ul>';
}

/**
 * Get the size of a directory recursively
 */
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

/**
 * Convert bytes to human readable format
 */
function df_format_size_to_human( $bytes ) {
	$units = array( 'B', 'KB', 'MB', 'GB' );
	$bytes = max( $bytes, 0 );
	$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
	$pow = min( $pow, count( $units ) - 1 );
	$bytes /= ( 1 << ( 10 * $pow ) );
	
	return round( $bytes, 2 ) . ' ' . $units[ $pow ];
}

?>