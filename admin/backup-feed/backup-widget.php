<?php
/*  Admin Dashboard Widget: Backup Feed (Tabbed)
*  Description: Consolidated widget displaying static backups, WordPress backups, and GitHub repository pushes
*  with tabbed interface for easy switching between backup/push methods.
*/

// Shared utility: Format bytes to human-readable size
if ( ! function_exists( 'df_format_size_to_human' ) ) {
	function df_format_size_to_human( $bytes ) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		$bytes = max( $bytes, 0 );
		$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow = min( $pow, count( $units ) - 1 );
		$bytes /= ( 1 << ( 10 * $pow ) );
		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}
}

//Dashboard Widget Registration
function df_reg_backup_widget() {
	wp_add_dashboard_widget(
		'widget_consolidated_backups',
		__('Backup & Repository Feed', 'df'),
		'df_create_consolidated_backup_widget'
	);
}
add_action( 'wp_dashboard_setup', 'df_reg_backup_widget' );

//Main Widget with Tabbed Interface
function df_create_consolidated_backup_widget() {
	echo '<div class="df-backup-widget-container">';
	
	// Tab Navigation
	echo '<div class="df-backup-tabs" style="border-bottom: 2px solid #e5e5e5; margin-bottom: 16px; display: flex; gap: 0;">';
	
	$tabs = array(
		'static' => 'Static Exports',
		'wordpress' => 'WordPress Backups',
		'github' => 'GitHub Pushes'
	);
	
	foreach ( $tabs as $tab_id => $tab_label ) {
		$active = ( $tab_id === 'static' ) ? 'active' : '';
		echo '<button class="df-backup-tab-btn df-tab-' . esc_attr( $tab_id ) . ' ' . esc_attr( $active ) . '" 
			style="padding: 10px 16px; border: none; background: none; cursor: pointer; font-weight: 500; 
			border-bottom: 3px solid transparent; transition: all 0.2s;" data-tab="' . esc_attr( $tab_id ) . '">';
		echo esc_html( $tab_label );
		echo '</button>';
	}
	
	echo '</div>';
	
	// Tab Content
	echo '<div class="df-backup-content">';
	
	// Static Backups Tab
	echo '<div id="df-backup-tab-static" class="df-backup-tab-content active">';
	df_display_static_backups_feed();
	echo '</div>';
	
	// WordPress Backups Tab
	echo '<div id="df-backup-tab-wordpress" class="df-backup-tab-content" style="display: none;">';
	df_display_wordpress_backups_feed();
	echo '</div>';
	
	// GitHub Pushes Tab
	echo '<div id="df-backup-tab-github" class="df-backup-tab-content" style="display: none;">';
	df_display_github_pushes_feed();
	echo '</div>';
	
	echo '</div>'; // End tab content
	echo '</div>'; // End container
	
	// Tab switching script
	?>
	<script>
	(function() {
		const buttons = document.querySelectorAll('.df-backup-tab-btn');
		buttons.forEach(btn => {
			btn.addEventListener('click', function() {
				const tabId = this.getAttribute('data-tab');
				
				// Hide all tabs
				document.querySelectorAll('.df-backup-tab-content').forEach(content => {
					content.style.display = 'none';
				});
				
				// Remove active state from all buttons
				buttons.forEach(b => b.classList.remove('active'));
				
				// Show selected tab
				document.getElementById('df-backup-tab-' + tabId).style.display = 'block';
				this.classList.add('active');
				
				// Update button styling
				buttons.forEach(b => {
					if (b === this) {
						b.style.borderBottomColor = '#2271b1';
						b.style.color = '#2271b1';
					} else {
						b.style.borderBottomColor = 'transparent';
						b.style.color = 'inherit';
					}
				});
			});
		});
		
		// Set initial button styling
		document.querySelector('.df-backup-tab-btn.active').style.borderBottomColor = '#2271b1';
		document.querySelector('.df-backup-tab-btn.active').style.color = '#2271b1';
	})();
	</script>
	<?php
}
