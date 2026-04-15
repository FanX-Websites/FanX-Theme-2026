<?php
/**
 * Function: ACF Custom Fields Quick Edit/Columns Support 
 * 
 * Provides a generalized helper function to easily add ACF fields to admin columns.
 */

/**
 * Add an ACF field to the admin columns for a specific post type
 *
 * @param string $post_type    The post type to add the column to (e.g., 'post', 'page', 'guest')
 * @param string $acf_field    The ACF field key or name to display
 * @param string $column_label The label to display in the column header
 * @param int    $position     Optional. Position to insert column (0 = first, null = last)
 */

// Adds an ACF field to the admin columns for a specified post type
function add_acf_field_to_admin_columns( $post_type, $acf_field, $column_label, $position = null ) {

	// Register the column header
	add_filter( "manage_{$post_type}_posts_columns", function( $columns ) use ( $acf_field, $column_label, $position ) {
		if ( $position === null ) {
			// Add at the end (default)
			$columns[ $acf_field ] = $column_label;
		} else {
			// Insert at specific position
			$column_array = array_values( $columns );
			$column_keys = array_keys( $columns );
			array_splice( $column_keys, $position, 0, $acf_field );
			$new_columns = [];
			foreach ( $column_keys as $key ) {
				if ( $key === $acf_field ) {
					$new_columns[ $acf_field ] = $column_label;
				} else {
					$new_columns[ $key ] = $columns[ $key ];
				}
			}
			$columns = $new_columns;
		}
		return $columns;
	});

	// Populate the column with ACF field values
	add_action( "manage_{$post_type}_posts_custom_column", function( $column, $post_id ) use ( $acf_field ) {
		if ( $column === $acf_field ) {
			echo wp_kses_post( acf_render_admin_column_value( $post_id, $acf_field ) );
		}
	}, 10, 2 );

	// Make the column sortable
	add_filter( "manage_edit-{$post_type}_sortable_columns", function( $columns ) use ( $acf_field ) {
		$columns[ $acf_field ] = $acf_field;
		return $columns;
	});

	// Handle sorting by ACF field value
	add_filter( 'pre_get_posts', function( $query ) use ( $post_type, $acf_field ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->get( 'post_type' ) !== $post_type ) {
			return;
		}

		$orderby = $query->get( 'orderby' );
		if ( $orderby !== $acf_field ) {
			return;
		}

		// Get field type to determine sort behavior
		$field_obj = get_field_object( $acf_field );
		$field_type = isset( $field_obj['type'] ) ? $field_obj['type'] : 'text';

		// Set up meta query for sorting
		$query->set( 'meta_key', $acf_field );
		
		// Force numeric sorting for number fields
		if ( $field_type === 'number' ) {
			$query->set( 'orderby', 'meta_value_num' );
		} else {
			$query->set( 'orderby', 'meta_value' );
		}

		// Preserve the order direction (ASC/DESC) when clicking column header
		$order = $query->get( 'order' );
		if ( ! empty( $order ) ) {
			$query->set( 'order', strtoupper( $order ) );
		}
	});

	// Add to Screen Options (make it hideable/showable)
	add_filter( "default_hidden_columns", function( $hidden ) use ( $acf_field ) {
		// Column is shown by default (not hidden). Change to array_push to hide by default.
		return $hidden;
	}, 10, 2 );
}

/**
 * Render an ACF field value for display in admin columns
 * Handles different field types appropriately
 *
 * @param int    $post_id     The post ID
 * @param string $acf_field   The ACF field key or name
 * @return string             The formatted field value for display
 */
function acf_render_admin_column_value( $post_id, $acf_field ) {
	$value = get_field( $acf_field, $post_id );

	if ( empty( $value ) ) {
		return '<span style="color: #999;">—</span>';
	}

	// Get field object to determine type
	$field_obj = get_field_object( $acf_field, $post_id );
	$field_type = isset( $field_obj['type'] ) ? $field_obj['type'] : 'text';

	// Handle different field types
	switch ( $field_type ) {
		case 'image':
			return acf_render_image_column( $value );

		case 'relationship':
		case 'post_object':
			return acf_render_relationship_column( $value );

		case 'taxonomy':
			return acf_render_taxonomy_column( $value );

		case 'date_picker':
		case 'date_time_picker':
			return acf_render_date_column( $value, $field_type );

		case 'true_false':
			return acf_render_boolean_column( $value );

		case 'color_picker':
			return acf_render_color_column( $value );

		default:
			// For text, textarea, select, and other basic types
			return '<span title="' . esc_attr( wp_strip_all_tags( $value ) ) . '">' . 
				   wp_kses_post( wp_trim_words( $value, 10 ) ) . 
				   '</span>';
	}
}

/**
 * Render image field for admin column
 */
function acf_render_image_column( $image ) {
	if ( is_array( $image ) ) {
		$image_id = $image['ID'];
		$image_url = $image['url'];
	} else {
		$image_id = $image;
		$image_url = wp_get_attachment_url( $image_id );
	}

	if ( ! $image_url ) {
		return '<span style="color: #999;">No image</span>';
	}

	return '<img src="' . esc_url( $image_url ) . '" style="max-width: 50px; height: auto; border-radius: 3px;" alt="Thumbnail">';
}

/**
 * Render relationship/post_object field for admin column
 */
function acf_render_relationship_column( $value ) {
	if ( empty( $value ) ) {
		return '<span style="color: #999;">—</span>';
	}

	if ( ! is_array( $value ) ) {
		$value = array( $value );
	}

	$links = array();
	foreach ( $value as $post_id ) {
		if ( is_object( $post_id ) ) {
			$post_id = $post_id->ID;
		}
		$title = get_the_title( $post_id );
		$edit_url = get_edit_post_link( $post_id );
		$links[] = '<a href="' . esc_url( $edit_url ) . '">' . esc_html( $title ) . '</a>';
	}

	return count( $links ) > 1 
		? count( $links ) . ' items (' . implode( ', ', $links ) . ')'
		: implode( '', $links );
}

/**
 * Render taxonomy field for admin column
 */
function acf_render_taxonomy_column( $value ) {
	if ( empty( $value ) ) {
		return '<span style="color: #999;">—</span>';
	}

	if ( ! is_array( $value ) ) {
		$value = array( $value );
	}

	$terms = array();
	foreach ( $value as $term ) {
		if ( is_object( $term ) ) {
			$terms[] = esc_html( $term->name );
		} else {
			$terms[] = esc_html( $term );
		}
	}

	return implode( ', ', $terms );
}

/**
 * Render date field for admin column
 */
function acf_render_date_column( $value, $field_type ) {
	if ( $field_type === 'date_time_picker' ) {
		return esc_html( wp_date( 'M d, Y g:i A', strtotime( $value ) ) );
	} else {
		return esc_html( wp_date( 'M d, Y', strtotime( $value ) ) );
	}
}

/**
 * Render boolean field for admin column
 */
function acf_render_boolean_column( $value ) {
	return $value ? '<span style="color: green; font-weight: bold;">✓ Yes</span>' : '<span style="color: #999;">✗ No</span>';
}

/**
 * Render color field for admin column
 */
function acf_render_color_column( $value ) {
	return '<div style="display: inline-block; width: 30px; height: 30px; background-color: ' . 
		   esc_attr( $value ) . '; border: 1px solid #ddd; border-radius: 3px;" title="' . 
		   esc_attr( $value ) . '"></div>';
}

/**
 * Initialize ACF Admin Columns
 * 
 * Define your ACF field columns here. Edit the $acf_columns array below
 * to add or remove columns for any post type.
 * 
 * Optional: Add 'position' to control column placement:
 *   - position: 0 = first (after checkbox)
 *   - position: 2 = after title/date
 *   - position: null or omit = last (default)
 */
add_action( 'init', function() {
	$acf_columns = array(
		'guests' => array(
			array ( 'field'=> 'info_display_order', 'label' => 'Post Order', 'position' => 2),
		)
	);

	// Register all configured columns
	foreach ( $acf_columns as $post_type => $fields ) {
		foreach ( $fields as $field_config ) {
			$position = isset( $field_config['position'] ) ? $field_config['position'] : null;
			add_acf_field_to_admin_columns( 
				$post_type, 
				$field_config['field'], 
				$field_config['label'],
				$position
			);
		}
	}
});
?>
