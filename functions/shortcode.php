<?php
/**
 * Theme specific shortcodes.
 * Enables Shortcode for WordPress core areas and creates custom shortcodes for use in posts, pages, widgets, and menus.
 *
 * ACF FIELD SHORTCODE CAN BE FOUND IN ACF/TWEAKS.PHP
 */

//Enable Shortcodes 
    //WP Core - Add Filter Methods 
    // Add null checks to prevent str_contains errors
    add_filter( 'widget_text', function( $value ) {
        if ( is_string( $value ) && ! empty( $value ) ) {
            return do_shortcode( $value );
        }
        return $value;
    }); //Widget Text
    
    add_filter( 'the_excerpt', function( $value ) {
        if ( is_string( $value ) && ! empty( $value ) ) {
            return do_shortcode( $value );
        }
        return $value;
    }); //Excerpt 
    
    add_filter( 'the_content', 'do_shortcode'); //Content (core function already handles null)

    //WP Menu 
    add_filter( 'wp_nav_menu_item', 'do_shortcode'); // Nav Label
    add_filter( 'nav_menu_item_title', 'do_shortcode'); // Nav Label

add_filter( 'walker_nav_menu_start_el', function( $item_output, $item, $depth, $args ) {
    if ( $item->description ) {
        $item->description = do_shortcode( $item->description );
    }
    return $item_output;
}, 10, 4 );    

       

//---CREATE SHORTCODES ---->       
    // -- PAGE TITLE -->
    function page_title_df( ){ // [page_title]
    return get_the_title();
    }
    add_shortcode( 'page_title', 'page_title_df' );

    // -- PAGE CONTENT -->
    function page_content_df( ){ // [page_content]
    return get_the_content();
    }
    add_shortcode( 'page_content', 'page_content_df' );

    // -- POST CONTENT -->
    function post_content_df( ){ // [post_content]
    return get_the_content();
    }
    add_shortcode( 'post_content', 'post_content_df' );


    // -- SITEMAP -->
    function sitemap_df($atts){ // [sitemap]
    return get_template_part('sitemap');
    }
    add_shortcode( 'sitemap', 'sitemap_df' );
    
    //--- CURRENT YEAR -->
    function year_df () { // [year]
        $year = date_i18n ('Y');
        return $year;
        }
    add_shortcode ('year', 'year_df');

    // -- SITE URL -->  
    function generate_site_url_shortcode() { // [site_url]
    return get_site_url();
    }
    add_shortcode( 'site_url', 'generate_site_url_shortcode' );


    // -- Content Specific Shortcodes --->>>
        // -- LINE BREAK -->
        function linebreak_df() { // [br]
            return '<br />';
        }
        add_shortcode( 'br', 'linebreak_df' );

        // -- HORIZONTAL RULE -->
        function thembreak_df() { // [hr]
            return '<hr style="width:50%; text-align:left; ; margin: 3%; border-top: 1px solid gold;">';
            return '<hr style="width:50%; text-align:left; margin: 3%; border-top: 1px solid gold;">';
        }    
        add_shortcode( 'hr', 'thembreak_df' );


// NOTE: ACF FIELD SHORTCODE is registered in acf/tweaks.php
// This provides a unified implementation with comprehensive field handling
// Supports: field, post_id, format_value, format, link_format attributes