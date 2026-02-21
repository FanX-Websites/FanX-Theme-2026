<?php 
/* Export Scheduler for Simply Static 
* Temp Setup: Cron Plugin 

//TODO: Set Up integrated Post/ Page Scheduler that triggers one-time cron job at on defined date and time. 
//TODO: Create Admin Dashboard Widget listing all Scheduled Post/ Page Cron Jobs using integrated scheduler  
*/

/* WP Crontrol Plugin Jobs - 
//NOTE: Temporary Solution & Backup Method //
*/
	/*Simply Static Provided Cron Jobs
	//INFO: Source/Reference Docs:: https://docs.simplystatic.com/article/69-how-to-schedule-exports-with-wp-crontrol
	*/
		//Full Static Export -------------------------------------->
		function ssp_run_static_export_cron() {
			// Full static export
			$simply_static = Simply_Static\Plugin::instance();
			$simply_static->run_static_export();
		}
		add_action( 'static_export_event', 'ssp_run_static_export_cron' ); //Hook: static_export_event (Export Full Site)

		//Schedule Update Export ----------------------------------->
		function ssp_run_update_export_cron() {
			// Get Simply Static instance - and - trigger an Update export
			$simply_static = Simply_Static\Plugin::instance();
			$simply_static->run_static_export( 0, 'update' ); // 'update' = Update Export
		}
		add_action( 'update_export_event', 'ssp_run_update_export_cron' ); //Hook: update_export_event (Export Updates)

	//END Simply Static Provided Cron Jobs

//Custom Cron Jobs

	//Url Scheduled Export ----------------------------------->



	//END Custom Cron Jobs <-------------------------------

//Static Website Backup - [19.02.26]

	//Backup Static Site (Post Export) ----------------------------------->
		function df_backup_static_export() {
			$export_dir = get_option( 'simply-static' )['local_dir'] ?? WP_CONTENT_DIR . '/simply-static/temp-files/';
			$backup_dir = WP_CONTENT_DIR . '/static-backups/' . date( 'Y-m-d_H-i-s' ) . '/';

			if ( ! is_dir( $backup_dir ) ) {
				wp_mkdir_p( $backup_dir );
			}

			// Recursive copy
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $export_dir, RecursiveDirectoryIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ( $iterator as $item ) {
				$dest = $backup_dir . $iterator->getSubPathname();
				if ( $item->isDir() ) {
					wp_mkdir_p( $dest );
				} else {
					copy( $item, $dest );
				}
			}
		}
		add_action( 'simply_static_finished', 'df_backup_static_export' );

	// Backup Cleanup & Retention ----------------------------------->
		$backup_base = WP_CONTENT_DIR . '/static-backups/';
		$backups = glob( $backup_base . '*', GLOB_ONLYDIR );

		if ( count( $backups ) > 7 ) { // Backup Number
			sort( $backups ); // oldest first
			$to_delete = array_slice( $backups, 0, count( $backups ) - 5 );

			foreach ( $to_delete as $old_backup ) {
				// Recursive delete
				$files = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator( $old_backup, RecursiveDirectoryIterator::SKIP_DOTS ),
					RecursiveIteratorIterator::CHILD_FIRST
				);
				foreach ( $files as $file ) {
					$file->isDir() ? rmdir( $file ) : unlink( $file );
				}
				rmdir( $old_backup );
			}
		}

/* End of WP Crontrol Plugin Jobs - Backup Method */

?>