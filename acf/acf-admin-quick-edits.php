<?php
/**
 * ACF Quick Edit & Bulk Edit Support
 * 
 * Provides quick edit and bulk edit functionality for ACF fields in the admin posts list.
 */

/**
 * Add an ACF field to quick edit and bulk edit forms
 *
 * @param string $post_type    The post type to add the field to (e.g., 'guest')
 * @param string $acf_field    The ACF field key or name
 * @param string $field_label  The label to display in the edit form
 */
function add_acf_field_to_quick_edit( $post_type, $acf_field, $field_label ) {
	
	// Add to Quick Edit form
	add_action( 'quick_edit_custom_box', function( $column_name, $post_type_name ) use ( $post_type, $acf_field, $field_label ) {
		if ( $post_type_name !== $post_type || $column_name !== $acf_field ) {
			return;
		}
		
		// Get the post ID - try multiple methods
		$post_id = 0;
		global $post;
		
		if ( isset( $post->ID ) && $post->ID > 0 ) {
			$post_id = $post->ID;
		} elseif ( isset( $_GET['post'] ) ) {
			$post_id = intval( $_GET['post'] );
		}
		
		if ( $post_id > 0 ) {
			acf_render_quick_edit_field( $post_type, $acf_field, $field_label, $post_id );
		}
	}, 10, 2 );

	// Add to Bulk Edit form
	add_action( 'bulk_edit_custom_box', function( $column_name, $post_type_name ) use ( $post_type, $acf_field, $field_label ) {
		if ( $post_type_name !== $post_type || $column_name !== $acf_field ) {
			return;
		}
		acf_render_bulk_edit_field( $post_type, $acf_field, $field_label );
	}, 10, 2 );

	// Hook into save_post to handle quick-edit/bulk-edit field saves
	add_action( 'save_post', function( $post_id ) use ( $post_type, $acf_field ) {
		// Skip during autosave or if not the right post type
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== $post_type ) {
			return;
		}

		// Check if the field value is in POST (quick edit/bulk edit context)
		if ( isset( $_POST[ $acf_field ] ) && $_POST[ $acf_field ] !== '' ) {
			if ( current_user_can( 'edit_post', $post_id ) ) {
				// Directly update post meta without ACF processing
				// This bypasses ACF's form validation which isn't present in quick-edit
				$value = wp_unslash( $_POST[ $acf_field ] );
				update_post_meta( $post_id, $acf_field, $value );
			}
		}
	}, 20 );
}

/**
 * Render an ACF field in the Quick Edit form
 */
function acf_render_quick_edit_field( $post_type, $acf_field, $field_label, $post_id = 0 ) {
	$field_obj = get_field_object( $acf_field );
	$field_type = isset( $field_obj['type'] ) ? $field_obj['type'] : 'text';
	$current_value = $post_id ? get_field( $acf_field, $post_id ) : '';
	?>
	<fieldset class="inline-edit-col-left">
		<div class="inline-edit-group">
			<label class="inline-edit-label">
				<span class="title"><?php echo esc_html( $field_label ); ?></span>
				<?php acf_render_quick_edit_input( $acf_field, $field_type, $current_value ); ?>
			</label>
		</div>
	</fieldset>
	<?php
}

/**
 * Render an ACF field in the Bulk Edit form
 */
function acf_render_bulk_edit_field( $post_type, $acf_field, $field_label ) {
	$field_obj = get_field_object( $acf_field );
	$field_type = isset( $field_obj['type'] ) ? $field_obj['type'] : 'text';
	?>
	<div class="inline-edit-group">
		<label class="inline-edit-label">
			<span class="title"><?php echo esc_html( $field_label ); ?></span>
			<?php acf_render_bulk_edit_input( $acf_field, $field_type ); ?>
		</label>
	</div>
	<?php
}

/**
 * Render input for quick edit based on field type
 */
function acf_render_quick_edit_input( $field_name, $field_type, $current_value = '' ) {
	switch ( $field_type ) {
		case 'number':
			?>
			<input type="number" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $current_value ); ?>" />
			<?php
			break;

		case 'textarea':
			?>
			<textarea name="<?php echo esc_attr( $field_name ); ?>"><?php echo esc_textarea( $current_value ); ?></textarea>
			<?php
			break;

		case 'true_false':
		case 'checkbox':
			?>
			<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="1" <?php checked( $current_value, 1 ); ?> />
			<?php
			break;

		case 'select':
			$field_obj = get_field_object( $field_name );
			$choices = isset( $field_obj['choices'] ) ? $field_obj['choices'] : [];
			?>
			<select name="<?php echo esc_attr( $field_name ); ?>">
				<option value="">— Select —</option>
				<?php foreach ( $choices as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_value, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php
			break;

		default:
			// Text input for text and other basic types
			?>
			<input type="text" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $current_value ); ?>" />
			<?php
			break;
	}
}

/**
 * Render input for bulk edit based on field type
 */
function acf_render_bulk_edit_input( $field_name, $field_type ) {
	switch ( $field_type ) {
		case 'number':
			?>
			<input type="number" name="<?php echo esc_attr( $field_name ); ?>" placeholder="Leave empty to skip" />
			<?php
			break;

		case 'textarea':
			?>
			<textarea name="<?php echo esc_attr( $field_name ); ?>"></textarea>
			<?php
			break;

		case 'true_false':
		case 'checkbox':
			?>
			<div style="margin: 5px 0;">
				<label><input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="-" checked /> No change</label><br>
				<label><input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="1" /> Yes</label><br>
				<label><input type="radio" name="<?php echo esc_attr( $field_name ); ?>" value="0" /> No</label>
			</div>
			<?php
			break;

		case 'select':
			$field_obj = get_field_object( $field_name );
			$choices = isset( $field_obj['choices'] ) ? $field_obj['choices'] : [];
			?>
			<select name="<?php echo esc_attr( $field_name ); ?>">
				<option value="">— No change —</option>
				<?php foreach ( $choices as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php
			break;

		default:
			// Text input for text and other basic types
			?>
			<input type="text" name="<?php echo esc_attr( $field_name ); ?>" />
			<?php
			break;
	}
}

/**
 * Initialize ACF Quick Edit Fields
 * 
 * Define which ACF fields should appear in quick edit and bulk edit.
 */
add_action( 'init', function() {
	$acf_quick_edit_fields = array(
        'guests' => array(
            array( 'field' => 'info_display_order', 'label' => 'Post Order' ),
        ),
	);

	// Register all configured quick edit fields
	foreach ( $acf_quick_edit_fields as $post_type => $fields ) {
		foreach ( $fields as $field_config ) {
			add_acf_field_to_quick_edit( 
				$post_type, 
				$field_config['field'], 
				$field_config['label'] 
			);
		}
	}
});

// Enqueue the JavaScript file for quick edit functionality
add_action( 'admin_enqueue_scripts', function( $hook ) {
	// Only load on the edit posts page
	if ( $hook !== 'edit.php' ) {
		return;
	}
	
	$script_path = get_template_directory() . '/acf/js/acf-quick-edit.js';
	$script_url = get_template_directory_uri() . '/acf/js/acf-quick-edit.js';
	
	if ( file_exists( $script_path ) ) {
		wp_enqueue_script( 
			'acf-quick-edit', 
			$script_url, 
			array( 'jquery' ), 
			filemtime( $script_path ), 
			true 
		);
	}
});

?>
