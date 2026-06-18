<?php
/*  Admin Dashboard Widget: Event Mode Toggle
*  Description: Simple dashboard widget with on/off toggle for event-mode display settings.
*  Allows admins to swap between different div layouts across the theme.
*/

// Register the Event Mode Dashboard Widget
function fanx_register_event_mode_widget() {
	wp_add_dashboard_widget(
		'widget_event_mode_toggle',
		__( 'Event Mode', 'fanx-theme' ),
		'fanx_event_mode_widget_display'
	);
}
add_action( 'wp_dashboard_setup', 'fanx_register_event_mode_widget' );

// Display the Event Mode Widget
function fanx_event_mode_widget_display() {
	// Get current event mode status
	$event_mode = get_option( 'fanx_event_mode', 'off' );
	$is_enabled = ( $event_mode === 'on' );
	
	?>
	<div class="fanx-event-mode-widget" style="padding: 12px 0;">
		<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
			<span style="font-size: 14px; font-weight: 500;">Current Status:</span>
			<span id="event-mode-status" style="padding: 4px 8px; border-radius: 3px; background: <?php echo $is_enabled ? '#46b450' : '#dc3545'; ?>; color: white; font-size: 12px; font-weight: bold;">
				<?php echo $is_enabled ? 'ON' : 'OFF'; ?>
			</span>
		</div>
		
		<div style="display: flex; gap: 8px;">
			<button type="button" id="event-mode-on" class="button <?php echo $is_enabled ? 'button-primary' : ''; ?>" 
				style="<?php echo $is_enabled ? '' : 'opacity: 0.6;'; ?>" data-state="on">
				Turn On
			</button>
			
			<button type="button" id="event-mode-off" class="button <?php echo ! $is_enabled ? 'button-primary' : ''; ?>" 
				style="<?php echo ! $is_enabled ? '' : 'opacity: 0.6;'; ?>" data-state="off">
				Turn Off
			</button>
		</div>
		
		<p style="margin-top: 12px; font-size: 12px; color: #666;">
			<strong>Modes:<strong><br>
            - Event Mode: 
            - Pre-Event Mode:
            - Post-Event Mode: Thank You see you next time - pages closed    
        
		</p>
	</div>
	
	<script>
	(function() {
		const onBtn = document.getElementById('event-mode-on');
		const offBtn = document.getElementById('event-mode-off');
		const statusSpan = document.getElementById('event-mode-status');
		
		function toggleMode(newState) {
			const data = new FormData();
			data.append('action', 'fanx_toggle_event_mode');
			data.append('state', newState);
			data.append('nonce', '<?php echo wp_create_nonce( 'fanx_event_mode_nonce' ); ?>');
			
			fetch(ajaxurl, {
				method: 'POST',
				body: data
			})
			.then(response => response.json())
			.then(result => {
				if (result.success) {
					// Update status display
					const isOn = newState === 'on';
					statusSpan.textContent = isOn ? 'ON' : 'OFF';
					statusSpan.style.background = isOn ? '#46b450' : '#dc3545';
					
					// Update button states
					onBtn.classList.toggle('button-primary', isOn);
					offBtn.classList.toggle('button-primary', !isOn);
					onBtn.style.opacity = isOn ? '1' : '0.6';
					offBtn.style.opacity = !isOn ? '1' : '0.6';
				}
			});
		}
		
		onBtn.addEventListener('click', () => toggleMode('on'));
		offBtn.addEventListener('click', () => toggleMode('off'));
	})();
	</script>
	<?php
}

// AJAX handler for toggling event mode
function fanx_toggle_event_mode_ajax() {
	check_ajax_referer( 'fanx_event_mode_nonce', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Insufficient permissions' );
	}
	
	$new_state = sanitize_text_field( $_POST['state'] );
	
	if ( in_array( $new_state, array( 'on', 'off' ), true ) ) {
		update_option( 'fanx_event_mode', $new_state );
		wp_send_json_success( array( 'state' => $new_state ) );
	} else {
		wp_send_json_error( 'Invalid state' );
	}
}
add_action( 'wp_ajax_fanx_toggle_event_mode', 'fanx_toggle_event_mode_ajax' );

// Helper function to check if event mode is enabled
if ( ! function_exists( 'fanx_is_event_mode_enabled' ) ) {
	function fanx_is_event_mode_enabled() {
		return get_option( 'fanx_event_mode', 'off' ) === 'on';
	}
}
