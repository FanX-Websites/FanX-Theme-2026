<?php 
/**
* Functions for every template file. (unless otherwise noted) 
*/


//ALL TEMPLATES:: 

/* Template Default Override: if a different template is explicitly assigned, use it instead - controlled via Page Attributes Section
 Runs through template_include so it fires during template selection (not at file load, when no page is queried yet) 
 */

 
add_filter( 'template_include', function ( $template ) {
    // is_page() is true for the static front page too; core would otherwise pick front-page.php and ignore the Page Attributes choice
    if ( ! is_page() ) {
        return $template;
    }

    $assigned_template = get_page_template_slug( get_queried_object_id() );

    if ( empty( $assigned_template ) || $assigned_template === 'default' ) {
        return $template;
    }

    $located_template = locate_template( $assigned_template );

    return $located_template ? $located_template : $template;
}, 99 );


//ALL TEMPLATE PARTS::

