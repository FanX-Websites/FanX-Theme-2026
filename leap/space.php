<?php
/**
 * LEAP Space Orders API - Booth Location Lookup
 * @author FanXTheme2026
 */

/**
 * Retrieve booth location from Space Orders API
 * Matches title, subtitle, subtext, then content (in that priority order) against
 * company name in API, using whole-word matching to avoid partial-word false positives.
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
		// Most reliable field first; content is checked last since it's the noisiest source
		$fields_by_priority = [
			get_the_title( $post_id ),
			get_field( 'heafoo_subtitle', $post_id ),
			get_field( 'heafoo_subtext', $post_id ),
			get_post_field( 'post_content', $post_id ),
		];
		
		$api_url = 'https://conventions.leapevent.tech/api/space_orders?key=' . urlencode( $leap_api_key );
		$response = wp_remote_get( $api_url, array(
			'timeout'   => 10,
			'headers'   => array( 'accept' => 'application/json' )
		) );
		
		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );
			
			if ( isset( $data['space_orders'] ) && is_array( $data['space_orders'] ) ) {
				// Normalize to lowercase, single-spaced words (punctuation collapsed to spaces, not removed)
				// Accents/diacritics are transliterated to ASCII first so "José" and "Jose" normalize the same
				$normalize_for_match = function( $str ) {
					$str = (string) $str;
					$transliterated = iconv( 'UTF-8', 'ASCII//TRANSLIT', $str );
					if ( $transliterated !== false ) {
						$str = $transliterated;
					}
					$str = mb_strtolower( $str, 'UTF-8' );
					$str = preg_replace( '/[^a-z0-9]+/', ' ', $str );
					return trim( preg_replace( '/\s+/', ' ', $str ) );
				};
				
				// Check fields in priority order; stop at the first field that yields a match across all vendors
				foreach ( $fields_by_priority as $field_index => $field_value ) {
					if ( empty( $field_value ) ) {
						continue;
					}
					$clean_field = $normalize_for_match( $field_value );
					if ( $clean_field === '' ) {
						continue;
					}
					// Title is often just a person's name, which may only be part of a multi-name space listing
					$is_title_field = ( $field_index === 0 );
					
					foreach ( $data['space_orders'] as $vendor ) {
						$company_name = isset( $vendor['company'] ) ? $vendor['company'] : '';
						if ( empty( $company_name ) ) {
							continue;
						}
						
						$clean_company = $normalize_for_match( $company_name );
						// Skip overly short/generic names - too likely to false-positive
						if ( strlen( $clean_company ) < 4 ) {
							continue;
						}
						
						// Whole-word/phrase match only, so "art" can't match inside "smart"
						$matched = preg_match( '/\b' . preg_quote( $clean_company, '/' ) . '\b/', $clean_field );
						// For the title only, also allow the title to be a whole-word match found within the company name
						if ( ! $matched && $is_title_field && strlen( $clean_field ) >= 4 ) {
							$matched = preg_match( '/\b' . preg_quote( $clean_field, '/' ) . '\b/', $clean_company );
						}
						
						if ( $matched ) {
							$vend_booth = ! empty( $vendor['booth'] ) ? 'Booth ' . esc_html( $vendor['booth'] ) : '';
							break 2; // Break out of both loops
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
