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

//Shortcodes in ACF 
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
//Custom ACF Shortcode Attributes 
    remove_shortcode('acf');
    add_shortcode('acf', function($atts) {
        $atts = shortcode_atts([
            'field'        => '',
            'post_id'      => null,
            'format_value' => true,
            'format'       => 'raw', // change default format/output of shortcode
            'url'          => '',
            'link_format'  => 'html', // 'html' for full <a> tag, 'url' for just the URL
            'orderby'      => '', //See below for option settings


        ], $atts);
    //order elements by Date, Title, ID, Rand(dom), Menu Order, Name (slug), etc.
        $allowed = ['date', 'title', 'ID', 'rand', 'menu_order', 'name', ]; 
        $orderby = in_array($atts['orderby'], $allowed) ? $atts['orderby'] : 'date';

        $value = get_field($atts['field'], $atts['post_id']);

            if (is_array($value) && isset($value['url']) && isset($value['title'])) { //Replace Array with String (Dynamic Links Fix) - only for link fields
                // If link_format is 'url', return just the URL for use in href attributes
                if ($atts['link_format'] === 'url') {
                    return esc_url($value['url']);
                }
                // Otherwise return full HTML link tag
                return '<a href="' . esc_url($value['url']) . '" target="' . esc_attr($value['target']) . '">' . esc_html($value['title']) . '</a>';
            }

            if ($atts['format'] === 'name' && is_numeric($value)) { //Term ID > Term Name (Cateogory Name as Content Fix)
                $term = get_term((int) $value);
                return (!is_wp_error($term) && $term) ? $term->name : $value;
            }

        return $value;
    });

/* API Keys & other 3rd Party Hooks*/