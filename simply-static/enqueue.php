<?php
/**
 * Enqueue ACF Export Scheduler Scripts
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue ACF export action scripts in admin
 * Uses ACF's recommended hook for input-related scripts
 */
function ssp_enqueue_acf_export_scripts() {
	wp_enqueue_script(
		'acf-export-scheduler',
		get_template_directory_uri() . '/simply-static/acf-export-actions.js',
		array( 'jquery', 'acf-input' ),
		'1.0.0',
		true
	);
}
add_action( 'acf/input/admin_enqueue_scripts', 'ssp_enqueue_acf_export_scripts' );
