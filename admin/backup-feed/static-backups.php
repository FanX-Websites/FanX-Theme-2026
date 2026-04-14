<?php
/**
 * Static Site Export & Backup Feed Widget - Server Cron Edition
 * 
 * Displays:
 * - Next scheduled export & backup time (calculated from server cron schedule)
 * - Last export & backup status for today
 * - List of completed export & backup archives with dates and sizes
 * 
 * Workflow:
 * 1. At scheduled time: run-scheduled-backups.php triggers export
 * 2. Simply Static exports the dynamic WordPress site to static HTML/CSS/JS
 * 3. The exported files are then backed up to wp-content/static-backups/
 * 4. This widget displays the schedule and backup archive history
 * 
 * Data flows: Server cron schedule → run-scheduled-backups.php → Simply Static export → backup → widget displays
 */

// Load export & backup schedules from the central schedules file (single source of truth)
if ( ! function_exists( 'df_get_backup_schedules' ) ) {
	function df_get_backup_schedules() {
		static $schedules = null;
		
		if ( $schedules === null ) {
			$schedules = array();
			// Load the export & backup schedule (controls when Simply Static export occurs)
			$schedules_file = '/home/ashelizmoore/bin/backup-schedules.php';
			
			if ( file_exists( $schedules_file ) ) {
				require_once( $schedules_file );
				if ( isset( $backup_schedules ) ) {
					$schedules = $backup_schedules;
				}
			}
		}
		
		return $schedules;
	}
}

// Get current site key from ABSPATH folder (no hardcoding needed)
if ( ! function_exists( 'df_get_site_key' ) ) {
	function df_get_site_key() {
		$wp_path = rtrim( ABSPATH, '/' );
		
		// Extract site folder from ABSPATH
		// e.g., /home/ashelizmoore/fillory/fanx/ → 'fanx'
		if ( preg_match( '#/fillory/([^/]+)/?$#', $wp_path, $matches ) ) {
			return strtolower( $matches[1] );
		}
		
		return null;
	}
}

// Calculate next scheduled export & backup using server time
if ( ! function_exists( 'df_get_next_backup_time' ) ) {
	function df_get_next_backup_time() {
		$schedules = df_get_backup_schedules();
		$site_key = df_get_site_key();
		
		if ( empty( $schedules ) || ! isset( $schedules[ $site_key ] ) ) {
			return null;
		}
		
		$times = $schedules[ $site_key ]['times'];
		$current_time = current_time( 'timestamp' );
		$current_date = wp_date( 'Y-m-d', $current_time );
		
		// Check each time today
		foreach ( $times as $time ) {
			$export_backup_timestamp = strtotime( $current_date . ' ' . $time );
			if ( $export_backup_timestamp && $export_backup_timestamp > $current_time ) {
				return $export_backup_timestamp;
			}
		}
		
		// All times passed today, return first time tomorrow
		$tomorrow = wp_date( 'Y-m-d', $current_time + DAY_IN_SECONDS );
		$first_time = $times[0];
		return strtotime( $tomorrow . ' ' . $first_time );
	}
}

// Check if export & backup ran today
if ( ! function_exists( 'df_get_backup_status' ) ) {
	function df_get_backup_status() {
		// Backup directory contains the archived static file exports
		$backup_dir = WP_CONTENT_DIR . '/static-backups/';
		
		if ( ! is_dir( $backup_dir ) ) {
			return array( 'success' => false, 'date' => null );
		}
		
		$backups = array_diff( scandir( $backup_dir, SCANDIR_SORT_DESCENDING ), array( '.', '..' ) );
		
		if ( empty( $backups ) ) {
			return array( 'success' => false, 'date' => null );
		}
		
		$latest = $backup_dir . $backups[0];
		$modified = filemtime( $latest );
		$size = df_get_dir_size( $latest );
		$today_start = strtotime( wp_date( 'Y-m-d 00:00:00', current_time( 'timestamp' ) ) );
		
		// Success = archive modified today AND contains actual files (> 1000 bytes)
		$success = ( $modified >= $today_start ) && ( $size > 1000 );
		$date_str = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $modified );
		
		return array( 'success' => $success, 'date' => $date_str );
	}
}

// Main widget display
if ( ! function_exists( 'df_display_static_backups_feed' ) ) {
	function df_display_static_backups_feed() {
		$next_time = df_get_next_backup_time();
		$status = df_get_backup_status();
		$backup_dir = WP_CONTENT_DIR . '/static-backups/';
		?>
		<div style="background: #f9f9f9; padding: 12px; border-radius: 4px; margin-bottom: 16px; border-left: 4px solid #2271b1;">
			
			<?php if ( $next_time ) : ?>
				<div style="margin-bottom: 10px; font-size: 13px;">
					<strong>Next Scheduled Export & Backup:</strong>
					<span style="color: #666;"><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_time ) ); ?></span>
					<br />
					<small style="color: #999;">Exports static version then archives a backup copy</small>
				</div>
			<?php else : ?>
				<span style="color: #999; font-size: 13px;"><em>No scheduled export & backup found</em></span>
			<?php endif; ?>
			
			<div style="font-size: 13px;">
				<?php if ( $status['success'] ) : ?>
					<strong style="color: #27ae60;">✓ Last Export & Backup Successful</strong><br />
					<small style="color: #666;">Completed: <?php echo esc_html( $status['date'] ); ?></small>
				<?php else : ?>
					<strong style="color: #e74c3c;">✗ No Recent Export & Backup</strong><br />
					<small style="color: #666;">Last export may have failed or is pending.</small>
				<?php endif; ?>
			</div>
			
		</div>
		
		<?php if ( is_dir( $backup_dir ) ) : ?>
			<?php $backups = array_diff( scandir( $backup_dir, SCANDIR_SORT_DESCENDING ), array( '.', '..' ) ); ?>
			
			<?php if ( ! empty( $backups ) ) : ?>
				<div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 8px;">
					<p style="margin: 0 0 8px 0; font-size: 12px; color: #666; padding: 0 20px;"><strong>Export & Backup Archive History:</strong></p>
					<ul style="margin: 0; padding-left: 20px;">
						<?php foreach ( $backups as $backup ) : ?>
							<?php
								$path = $backup_dir . $backup;
								$size = df_format_bytes( df_get_dir_size( $path ) );
								$date = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), filemtime( $path ) );
							?>
							<li style="margin-bottom: 8px;">
								<strong><?php echo esc_html( $date ); ?></strong><br />
								<small style="color: #666;">Archive: <?php echo esc_html( $backup ); ?> • Size: <?php echo esc_html( $size ); ?></small>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php else : ?>
				<p style="color: #999;"><em>No export & backup archives found yet.</em></p>
			<?php endif; ?>
		<?php else : ?>
			<p style="color: #999;"><em>No export & backup directory found.</em></p>
		<?php endif; ?>
		<?php
	}
}

// Recursive directory size
if ( ! function_exists( 'df_get_dir_size' ) ) {
	function df_get_dir_size( $path ) {
		if ( is_file( $path ) ) {
			return filesize( $path );
		}
		
		if ( ! is_dir( $path ) || ! is_readable( $path ) ) {
			return 0;
		}
		
		$size = 0;
		foreach ( array_diff( scandir( $path ), array( '.', '..' ) ) as $item ) {
			$size += df_get_dir_size( $path . '/' . $item );
		}
		return $size;
	}
}

// Format bytes human-readable
if ( ! function_exists( 'df_format_bytes' ) ) {
	function df_format_bytes( $bytes ) {
		$units = array( 'B', 'KB', 'MB', 'GB' );
		$bytes = max( $bytes, 0 );
		$pow = min( floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) ), count( $units ) - 1 );
		$bytes /= ( 1 << ( 10 * $pow ) );
		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}
}
