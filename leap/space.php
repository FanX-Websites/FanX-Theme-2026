<?php
/**
 * LEAP Space Orders API - Booth Location Lookup
 * @author FanXTheme2026
 */

/**
 * Retrieve booth location from Space Orders API
 * Matches post title, subtitle, and content against company name in API
 * Falls back to ACF field if no API match found
 * 
 * @param int $post_id The post ID to look up
 * @return string Booth information (e.g., "Booth 42") or empty string
 */
function get_booth_location( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	
	$vend_booth = '';
	
	// Try to get location from Space Orders API first
	$leap_api_key = get_field( 'leap_api_key', 'option' );
	if ( ! empty( $leap_api_key ) ) {
		$post_title = get_the_title( $post_id );
		$post_subtitle = get_field( 'heafoo_subtitle', $post_id );
		$post_content = get_post_field( 'post_content', $post_id );
		
		// Build search terms for matching
		$search_terms = array_filter( [
			$post_title,
			$post_subtitle,
			$post_content
		] );
		
		if ( ! empty( $search_terms ) ) {
			$api_url = 'https://conventions.leapevent.tech/api/space_orders?key=' . urlencode( $leap_api_key );
			$response = wp_remote_get( $api_url, array(
				'timeout'   => 10,
				'headers'   => array( 'accept' => 'application/json' )
			) );
			
			if ( ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				
				if ( isset( $data['space_orders'] ) && is_array( $data['space_orders'] ) ) {
					// Helper function to remove spaces and punctuation for matching
					$sanitize_for_match = function( $str ) {
						return strtolower( preg_replace( '/[^a-z0-9]/i', '', $str ) );
					};
					
					// Search for a vendor match
					foreach ( $data['space_orders'] as $vendor ) {
						$company_name = isset( $vendor['company'] ) ? $vendor['company'] : '';
						
						if ( ! empty( $company_name ) ) {
							$clean_company = $sanitize_for_match( $company_name );
							
							// Check if company name is contained in any search term
							foreach ( $search_terms as $term ) {
								$clean_term = $sanitize_for_match( $term );
								if ( strpos( $clean_term, $clean_company ) !== false ) {
									// Match found - get booth number
									$vend_booth = isset( $vendor['booth'] ) ? 'Booth ' . esc_html( $vendor['booth'] ) : '';
									break 2; // Break out of both loops
								}
							}
						}
					}
				}
			}
		}
	}
	
	// Fall back to ACF field if no API match found
	if ( empty( $vend_booth ) ) {
		$sched = get_field('sched', $post_id);
		$vend_booth = is_array($sched) ? ($sched['room_booth'] ?? '') : '';
	}
	
	return $vend_booth;
}
