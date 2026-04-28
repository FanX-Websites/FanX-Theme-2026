<?php
/** Timezone Fix for WordPress - Simply Static Compatibility
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// === TIMEZONE FIX ===
// Force PHP to use WordPress's configured timezone instead of UTC
// This fixes strtotime() and date functions to work with the correct timezone
// See: https://core.trac.wordpress.org/ticket/24730
if ( function_exists( 'get_option' ) ) {
	$wp_timezone = get_option( 'timezone_string' );
	if ( $wp_timezone ) {
		date_default_timezone_set( $wp_timezone );
	} elseif ( get_option( 'gmt_offset' ) ) {
		// Fallback to GMT offset if timezone_string not set
		$gmt_offset = get_option( 'gmt_offset' );
		$offset_hours = intval( $gmt_offset );
		$offset_mins = ( $gmt_offset - $offset_hours ) * 60;
		$offset_string = sprintf( '%+03d:%02d', $offset_hours, abs( $offset_mins ) );
		// Create timezone string from offset
		$timezone = timezone_name_from_abbr( '', intval( $gmt_offset ) * 3600, 0 );
		if ( $timezone ) {
			date_default_timezone_set( $timezone );
		}
	}
}

// === SIMPLY STATIC WIDGET TIMEZONE FIX ===
// The Simply Static widget uses strtotime() on stored UTC timestamps
// date_default_timezone_set() causes strtotime() to interpret them in local timezone (wrong)
// Fix: Hook into dashboard rendering and temporarily revert timezone for the widget
function fanx_simply_static_widget_fix() {
	// Check if we're on the dashboard and Simply Static is active
	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( $screen && 'dashboard' === $screen->base && class_exists( '\Simply_Static\Admin_Dashboard_Widget' ) ) {
			// On dashboard page, temporarily reset timezone while widgets render
			$current_tz = ini_get( 'date.timezone' );
			if ( $current_tz && 'UTC' !== $current_tz ) {
				ini_set( 'date.timezone', 'UTC' );
				// Restore after dashboard loads
				add_action( 'admin_footer', function() use ( $current_tz ) {
					ini_set( 'date.timezone', $current_tz );
				}, 1 );
			}
		}
	}
}
add_action( 'admin_init', 'fanx_simply_static_widget_fix' );
