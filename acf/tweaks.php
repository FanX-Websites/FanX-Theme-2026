<?php  //Functions File for ACF Features & Tweaks

//TODO: Support for ACF Fields in WP Menu  
//TODO: Allow Encoded URL in ACF Link Fields

//Shortcodes in ACF 
add_filter('acf/format_value/type=textarea', 'do_shortcode'); //Text Area
add_filter('acf/format_value/type=text', 'do_shortcode'); //Text Field
add_filter('acf/format_value/type=message', 'do_shortcode'); //Message


//Allow Unsafe HTML 
add_filter( 'acf/shortcode/allow_unsafe_html', function( $allowed, $attributes = null, $field_type = null, $field_object = null ) {
    return true;
}, 10, 4 );

//Allow iframe Tags:  
add_filter( 'wp_kses_allowed_html', 'acf_add_allowed_iframe_tag', 10, 2 );
function acf_add_allowed_iframe_tag( $tags, $context ) {
    if ( $context === 'acf' ) {
        $tags['iframe'] = array(
            'src'             => true,
            'height'          => true,
            'width'           => true,
            'frameborder'     => true,
            'allowfullscreen' => true,
            'name'            => true,
        );
    }

    return $tags;
}

//Allow iframe Guest Schedule
add_filter( 'acf/shortcode/allow_unsafe_html', 
function ( $allowed, $atts ) {
    if ( is_array( $atts ) && isset( $atts['field'] ) && $atts['field'] === 'sched_url' ) {
        return true;
    }
    return $allowed;
}, 10, 2 );


//Allow SVG & Path Tags:
add_filter( 'wp_kses_allowed_html', 'acf_add_allowed_svg_tag', 10, 2 );
function acf_add_allowed_svg_tag( $tags, $context ) {
    if ( $context === 'acf' ) {
        $tags['svg']  = array(
            'xmlns'       => true,
            'fill'        => true,
            'viewbox'     => true,
            'role'        => true,
            'aria-hidden' => true,
            'focusable'   => true,
        );
        $tags['path'] = array(
            'd'    => true,
            'fill' => true,
        );
    }

    return $tags;
}