<?php
/**
 * Custom ACF support for Quick Edit and Bulk Edit
 * Handles additional fields in WordPress admin list editing
 * //FIXME: its not working/conecting/showing in the quick edit or bulk edit screens 
 */

// Add celeb_op_url field to quick edit (guests post type)
add_action( 'quick_edit_custom_box', 'acf_quick_edit_celeb_op_url', 10, 2 );
function acf_quick_edit_celeb_op_url( $column_name, $post_type ) {
	if ( 'guests' !== $post_type ) {
		return;
	}

	$post_id = isset( $_REQUEST['post'] ) ? intval( $_REQUEST['post'] ) : 0;
	$celeb_op_url = get_field( 'celeb_op_url', $post_id );
	?>
	<fieldset class="inline-edit-col-right">
		<div class="inline-edit-group wp-clearfix">
			<label class="inline-edit-label">
				<span class="title">Celeb Op URL</span>
				<span class="input-text-wrap">
					<input type="text" name="acf_celeb_op_url" value="<?php echo esc_attr( $celeb_op_url ); ?>" />
				</span>
			</label>
		</div>
	</fieldset>
	<?php
}

// Save celeb_op_url field from quick edit
add_action( 'save_post_guests', 'acf_save_quick_edit_celeb_op_url', 10, 1 );
function acf_save_quick_edit_celeb_op_url( $post_id ) {
	if ( ! isset( $_REQUEST['acf_celeb_op_url'] ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$celeb_op_url = isset( $_REQUEST['acf_celeb_op_url'] ) ? sanitize_text_field( $_REQUEST['acf_celeb_op_url'] ) : '';
	update_field( 'celeb_op_url', $celeb_op_url, $post_id );
}

// Add celeb_op_url field to bulk edit
add_action( 'bulk_edit_custom_box', 'acf_bulk_edit_celeb_op_url', 10, 2 );
function acf_bulk_edit_celeb_op_url( $column_name, $post_type ) {
	if ( 'guests' !== $post_type ) {
		return;
	}
	?>
	<fieldset class="inline-edit-col-right">
		<div class="inline-edit-group wp-clearfix">
			<label class="inline-edit-label">
				<span class="title">Celeb Op URL</span>
				<span class="input-text-wrap">
					<input type="text" name="acf_celeb_op_url_bulk" value="" placeholder="Leave blank to skip" />
				</span>
			</label>
		</div>
	</fieldset>
	<?php
}

// Save celeb_op_url field from bulk edit
add_action( 'save_post_guests', 'acf_save_bulk_edit_celeb_op_url', 10, 1 );
function acf_save_bulk_edit_celeb_op_url( $post_id ) {
	if ( ! isset( $_REQUEST['acf_celeb_op_url_bulk'] ) || '' === $_REQUEST['acf_celeb_op_url_bulk'] ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$celeb_op_url = sanitize_text_field( $_REQUEST['acf_celeb_op_url_bulk'] );
	update_field( 'celeb_op_url', $celeb_op_url, $post_id );
}
