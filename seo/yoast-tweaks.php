<?php 

/* Yoast SEO Plugin Tweaks for FanX Theme 2026 
*
*/


// Disable All the Things 

    //Disable Auto Redirect on Slug Change
    add_filter('Yoast\WP\SEO\post_redirect_slug_change', '__return_false' );
    add_filter('Yoast\WP\SEO\term_redirect_slug_change', '__return_false' );

    //Disable Notifications 
    add_filter('Yoast\WP\SEO\enable_notification_post_trash', '__return_false' );
    add_filter('Yoast\WP\SEO\enable_notification_post_slug_change', '__return_false' );
    add_filter('Yoast\WP\SEO\enable_notification_term_delete', '__return_false' );
    add_filter('Yoast\WP\SEO\enable_notification_term_slug_change', '__return_false' );

?>