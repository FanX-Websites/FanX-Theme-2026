<?php
/**
 * Theme specific shortcodes.
 * Enables Shortcode for WordPress core areas and creates custom shortcodes for use in posts, pages, widgets, and menus.
 *
 * ACF FIELD SHORTCODE CAN BE FOUND IN ACF/TWEAKS.PHP
 */

//Enable Shortcodes 
    //WP Core - Add Filter Methods 
    add_filter( 'widget_text', 'do_shortcode'); //Widget Text
    add_filter( 'the_excerpt', 'do_shortcode'); //Excerpt 
    add_filter( 'the_content', 'do_shortcode'); //Content

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
    // -- [page_title] -->
    function page_title_df( ){
    return get_the_title();
    }
    add_shortcode( 'page_title', 'page_title_df' );

    // -- [page_content] -->
    function page_content_df( ){
    return get_the_content();
    }
    add_shortcode( 'page_content', 'page_content_df' );

    // -- [post_content] -->
    function post_content_df( ){
    return get_the_content();
    }
    add_shortcode( 'post_content', 'post_content_df' );


    // --[sitemap] -->
    function sitemap_df($atts){
    return get_template_part('sitemap');
    }
    add_shortcode( 'sitemap', 'sitemap_df' );
    
    //--- [year] -->
    function year_df () {
        $year = date_i18n ('Y');
        return $year;
        }
    add_shortcode ('year', 'year_df');

    // -- [site_url] --> 
    function generate_site_url_shortcode() {
    return get_site_url();
    }
    add_shortcode( 'site_url', 'generate_site_url_shortcode' );


    // -- Content Specific Shortcodes --->>>
        // -- [br] -->
        function linebreak_df() {
            return '<br />';
        }
        add_shortcode( 'br', 'linebreak_df' );

        // -- [hr] -->
        function thembreak_df() {
            return '<hr style="width:50%; text-align:left; ; margin: 3%; border-top: 1px solid gold;">';
            return '<hr style="width:50%; text-align:left; margin: 3%; border-top: 1px solid gold;">';
        }    
        add_shortcode( 'hr', 'thembreak_df' );

// --- ACF Fields as shortcode --->
    // -- [acf field="field_name"] -->
function acf_field_shortcode( $atts ) {
    $a = shortcode_atts( array(
        'field' => '',
        'post_id' => false,
    ), $atts );

    if ( function_exists( 'get_field' ) && ! empty( $a['field'] ) ) {
        $value = get_field( $a['field'], $a['post_id'] );
        
        // If no value found and no specific post_id was set, try options page
        if ( empty( $value ) && $a['post_id'] === false ) {
            $value = get_field( $a['field'], 'option' );
        }
        
        // Handle array returns for common ACF field types
        if ( is_array( $value ) ) {
            // Image field: return image URL
            if ( isset( $value['url'] ) ) {
                return $value['url'];
            }
            // Gallery field: return comma-separated URLs
            if ( isset( $value[0] ) && is_array( $value[0] ) && isset( $value[0]['url'] ) ) {
                $urls = array_map( function( $img ) {
                    return isset( $img['url'] ) ? $img['url'] : '';
                }, $value );
                return implode( ',', array_filter( $urls ) );
            }
            // For other array types, return JSON for debugging/documentation
            return json_encode( $value );
        }
        
        return $value;
    }
    return '';
}
add_shortcode( 'acf', 'acf_field_shortcode' );


//Use When Needed:
    //remove_action('shutdown', 'wp_ob_end_flush_all', 1);  //Flush error
    //flush_rewrite_rules(); //Flush Rules