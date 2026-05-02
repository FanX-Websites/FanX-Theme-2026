<?php  //Functions File for ACF Features & Tweaks

//FIXME: ACF Shortcode in ACF Fields Not Working  
//TODO: Support for ACF Fields in WP Menu  
//TODO: Allow Encoded URL in ACF Link Fields

/*THEY'RE ON THE LIST:  
Allow Shortcodes in ACF Fields & Allow Unsafe HTML in Shortcodes (for iframes, svg, etc.)*/

add_action( 'acf/init', 'set_acf_settings' );
function set_acf_settings() {
    acf_update_setting( 'enable_shortcode', true ); //Enable Shortcodes in ACF Fields
}

//Safe Shortcodes in ACF 
// Wrap do_shortcode to prevent null value errors
$safe_shortcode = function( $value ) {
    if ( is_string( $value ) && ! empty( $value ) ) {
        return do_shortcode( $value );
    }
    return $value;
};

add_filter( 'acf/format_value/type=textarea', $safe_shortcode ); //Text Area
add_filter( 'acf/format_value/type=text', $safe_shortcode ); //Text Field
add_filter( 'acf/format_value/type=message', $safe_shortcode ); //Message

//Allow Unsafe HTML 
add_filter( 'acf/shortcode/allow_unsafe_html', function( $allowed, $attributes = null, $field_type = null, $field_object = null ) {
    return true;
}, 10, 4 );

// UNTESTED: Abandoned ACF iframe and guest schedule integration
// Commented out - no known use case. Uncomment only if needed for iframe/guest schedule features.
// 
// // Allow iframe Tags:  
// add_filter( 'wp_kses_allowed_html', 'acf_add_allowed_iframe_tag', 10, 2 );
// function acf_add_allowed_iframe_tag( $tags, $context ) {
//     if ( $context === 'acf' ) {
//         $tags['iframe'] = array(
//             'src'             => true,
//             'height'          => true,
//             'width'           => true,
//             'frameborder'     => true,
//             'allowfullscreen' => true,
//             'name'            => true,
//         );
//     }
//     return $tags;
// }

// // Allow iframe Guest Schedule
// add_filter( 'acf/shortcode/allow_unsafe_html', 
// function ( $allowed, $atts ) {
//     if ( is_array( $atts ) && isset( $atts['field'] ) && $atts['field'] === 'sched_url' ) {
//         return true;
//     }
//     return $allowed;
// }, 10, 2 );


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


//Custom ACF Shortcode - Unified Implementation
// Combines features from both tweaks.php and shortcode.php for comprehensive field output
remove_shortcode('acf');
add_shortcode('acf', function($atts) {
    $atts = shortcode_atts([
        'field'        => '',                      // Field name (required)
        'post_id'      => null,                    // Post ID (defaults to current post)
        'format_value' => true,                    // Format the value
        'format'       => 'raw',                   // Output format: raw, name, url, html
        'link_format'  => 'html',                  // Link output: html (full <a> tag) or url (just URL)
        'orderby'      => '',                      // Sort by: date, title, ID, rand, menu_order, name
    ], $atts);

    if ( ! function_exists( 'get_field' ) || empty( $atts['field'] ) ) {
        return '';
    }

    $value = get_field( $atts['field'], $atts['post_id'] );

    // If no value found and no specific post_id was set, try options page
    if ( empty( $value ) && $atts['post_id'] === null ) {
        $value = get_field( $atts['field'], 'option' );
    }

    if ( empty( $value ) ) {
        return '';
    }

    // Handle ACF Link Fields (Advanced Link Field)
    // Returns array with url, title, target keys
    if ( is_array( $value ) && isset( $value['url'] ) && isset( $value['title'] ) ) {
        if ( $atts['link_format'] === 'url' ) {
            return esc_url( $value['url'] );
        }
        return '<a href="' . esc_url( $value['url'] ) . '" target="' . esc_attr( $value['target'] ?? '_self' ) . '">' . esc_html( $value['title'] ) . '</a>';
    }

    // Handle Image Field (returns array with url, id, alt, etc.)
    if ( is_array( $value ) && isset( $value['url'] ) && isset( $value['id'] ) ) {
        return esc_url( $value['url'] );
    }

    // Handle Gallery Field (returns array of image arrays)
    if ( is_array( $value ) && isset( $value[0] ) && is_array( $value[0] ) && isset( $value[0]['url'] ) ) {
        $urls = array_map( function( $img ) {
            return isset( $img['url'] ) ? esc_url( $img['url'] ) : '';
        }, $value );
        return implode( ',', array_filter( $urls ) );
    }

    // Handle Term ID to Term Name conversion (format="name")
    if ( $atts['format'] === 'name' && is_numeric( $value ) ) {
        $term = get_term( (int) $value );
        return ( ! is_wp_error( $term ) && $term ) ? esc_html( $term->name ) : $value;
    }

    // For other array types, return JSON for debugging
    if ( is_array( $value ) ) {
        return wp_json_encode( $value );
    }

    return $value;
});

/* API Keys & other 3rd Party Hooks*/