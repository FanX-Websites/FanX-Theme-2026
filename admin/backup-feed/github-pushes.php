<?php
/**
 * GitHub Pushes Feed for Backup Widget
 * Displays latest commits from the FanX Theme Github repository
 * 
 */

// Display GitHub repository feed
function df_display_github_pushes_feed() {
	echo '<p><i>Latest commits to the FanX Theme repository. Updates shown here may not reflect the live site status.</i></p>';
	
	// Get RSS Feed(s)
	include_once( ABSPATH . WPINC . '/feed.php' );

	// Repository Feeds list
	$git_feeds = array(
		'https://github.com/FanX-Websites/FanX-Theme-2026/commits.atom'
	);

	// Loop through Feeds
	foreach ( $git_feeds as $feed ) :

		// Get a SimplePie feed object from the specified feed source.
		$rss = fetch_feed( $feed );
		if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly
			// Figure out how many total items there are, and choose a limit
			$maxitems = $rss->get_item_quantity( 5 );

			// Build an array of all the items, starting with element 0 (first element).
			$rss_items = $rss->get_items( 0, $maxitems );

			// Get RSS title
		$rss_title = '<a href="' . esc_url( $rss->get_permalink() ) . '" target="_blank" style="text-decoration: none; color: #27ae60; font-weight: 500;">' . strtoupper( esc_html( $rss->get_title() ) ) . '</a>';
		endif;

		// Display repository link
		echo '<div style="background: #f9f9f9; 
						padding: 12px; 
						border-radius: 4px; 
						margin-bottom: 16px; 
						border-left: 4px solid #2271b1;
						">';
		echo '<strong>' . $rss_title . '</strong>';
		echo '</div>';

		// Scrollable container for feed items
		echo '<div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 8px;">';

		echo '<ul style="margin: 0; padding-left: 20px;">';

		// Check items
		if ( $maxitems == 0 ) {
			echo '<li>' . __( 'No items', 'df' ) . '.</li>';
		} else {
			// Loop through each feed item and display each item as a hyperlink.
			foreach ( $rss_items as $item ) :
				// Get human date
				$item_date = human_time_diff( $item->get_date( 'U' ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'df' );

				// Start displaying item content within a <li> tag
				echo '<li style="margin-bottom: 8px;">';
				// create item link
				echo '<a href="' . esc_url( $item->get_permalink() ) . '" target="_blank" title="' . esc_attr( $item_date ) . '" style="text-decoration: none; color: #2271b1;">';
				// Get item title
				echo '<strong>' . esc_html( $item->get_title() ) . '</strong>';
				echo '</a>';
				// Display date
				echo ' <span style="font-size: 12px; color: #999;">' . esc_html( $item_date ) . '</span><br />';
				// Get item content
				$content = $item->get_content();
				// Shorten content
				$content = wp_html_excerpt( $content, 150 ) . '...';
				// Display content
				echo '<small style="color: #666;">' . wp_kses_post( $content ) . '</small>';
				// End <li> tag
				echo '</li>';
			endforeach;
		}
		// End <ul> tag
		echo '</ul>';
		// End scrollable container
		echo '</div>';

	endforeach; // End foreach feed
}
