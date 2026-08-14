<?php 
/*
Tweaks to Categories, Tags, Custom Taxonomies, etc

What this File Does: 
    1 - Adds Category & Tag Support to All Post Types
    2 - Allows Child Categories to Use Parent Category Templates
    3 - Removes "Category: " Prefix from Category Archive Titles
    4 - //TODO: Remove Parent Category Slug from Child Categories - Partners 
        //NOTE: Look into Hierarchies in URLS for categories/tags
*/

//URLS 


//Add Support ----------------------------------->
    // 1- support for Categories & Tags - Adds to all public post types except attachments and pages
    function df_add_taxonomies_to_all_post_types() {
        $post_types = get_post_types(['public' => true], 'names'); 

        foreach ($post_types as $post_type) {
            if ( !in_array( $post_type, array( 'attachment', 'page' ) ) ) { 
                register_taxonomy_for_object_type('category', $post_type);
                register_taxonomy_for_object_type('post_tag', $post_type);
            }
        }
    }
        add_action('init', 'df_add_taxonomies_to_all_post_types');

    // 2- Support for Tags ON Taxonomies - Adds post_tag to multiple taxonomies
    function df_register_taxonomy_for_objects() {
        $objects = apply_filters('df_tag_taxonomy_objects', array('category', 'fandoms', 'xp'));
        
        foreach ($objects as $object) {
            register_taxonomy_for_object_type('post_tag', $object);
        }
    }
    add_action('init', 'df_register_taxonomy_for_objects', 11); 

//END Add Support ----------------------------------->

//Parent & Child Category Page Tweaks ----------------------------------->

    // 2- Templates - Child Copy Cats the Parents  ------------------->
        add_filter( 'category_template', function( $template ) {
            $cat = get_queried_object();
            
            // Only apply to specific parent categories
            $allowed_parents = array( 'guests' ); //Specify which parents
            
            if ( $cat->parent !== 0 ) {
                $parent = get_term( $cat->parent, 'category' );
                
                // Check if parent is in allowed list
                if ( in_array( $parent->slug, $allowed_parents ) ) {
                    $parent_template = locate_template( 'category-' . $parent->slug . '.php' );
                    
                    if ( $parent_template ) {
                        return $parent_template;
                    }
                }
            }  
            return $template;
        } );
    //END Templates - Child Copy Cats the Parents  ------------------->


// END Parent & Child Category Tweaks ----------------------------------------------->

//Category & Taxonomy Types & Labels ----------------------------------------------->
    
    // 3- Archive Page Settings - Remove Archive Prefix on Cat/Tax Page Titles
        add_filter( 'get_the_archive_title', function( $title ) { 
            if ( empty( $title ) ) {
                return $title;
            }
            return preg_replace( '/^[^:]+:\s*/', '', $title );
        } );
    //END 3- Archive Page Settings - Remove Archive Prefix


// END Category & Taxonomy Types & Labels ----------------------------------------------->    



/** 
 * Category/Taxonomy Coming Soon Mode 
 */

add_filter('template_include', function( $template ) {
    if ( ! is_category() && ! is_tax() ) return $template;

    $term = get_queried_object();
    if ( ! $term || ! isset( $term->term_id ) ) return $template;

    if ( get_field( 'coming_soon', 'term_' . $term->term_id ) ) {
        $override = locate_template( 'coming-soon.php' ); //coming-soon.php
        if ( $override ) return $override;
    }

    return $template;
});

/** END */


/**
 * 'LOAD MORE' List Feature - AJAX Handler - ALL Category Lists 
 * Fetches next batch of posts for Load More button
 * //TODO: Finsih setting up the LOAD MORE feature for all taxonomy/category/archive pages - 
 */
function fanx_load_more_alumni_callback() {
    // Get POST parameters
    $paged = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
    $term_id = isset( $_POST['term_id'] ) ? intval( $_POST['term_id'] ) : 0;
    $taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : 'category';
    $posts_per_page = isset( $_POST['posts_per_page'] ) ? intval( $_POST['posts_per_page'] ) : 50;
    
    if ( $term_id === 0 ) {
        wp_send_json_error( 'Invalid term ID' );
    }
    
    // Build tax_query
    $tax_query = array(
        array(
            'taxonomy' => $taxonomy,
            'field'    => 'term_id',
            'terms'    => $term_id,
        )
    );
    
    // Query posts
    $args = array(
        'post_type'      => 'guests',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'tax_query'      => $tax_query,
        'orderby'        => 'meta_value_num',
        'meta_key'       => 'info_display_order',
        'order'          => 'ASC',
    );
    
    $query = new WP_Query( $args );
    
    // Generate HTML for posts
    $posts_html = '';
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            
            // Capture post block HTML using the rendering function
            ob_start();
            fanx_render_alumni_post_block();
            $posts_html .= ob_get_clean();
        }
    }
    
    wp_reset_postdata();
    
    // Check if there are more posts
    $has_more = ( $paged * $posts_per_page ) < $query->found_posts;
    
    wp_send_json_success( array(
        'posts_html' => $posts_html,
        'has_more'   => $has_more,
    ) );
}
add_action( 'wp_ajax_fanx_load_more_alumni', 'fanx_load_more_alumni_callback' );
add_action( 'wp_ajax_nopriv_fanx_load_more_alumni', 'fanx_load_more_alumni_callback' );


/**
 * Render Alumni Post Block HTML 
 * //FIXME: Remove temp solution for Alumni Post-Block Layout and refer to the current post-block layout of any given template to keep layout changes consistant across the loop 
 * Used by both category-alumni.php and AJAX handler
 * Keeps post structure in one place for consistent rendering
 */
function fanx_render_alumni_post_block() {
    $is_postponed = has_term( 'postponed', 'xp-status', get_the_ID() );
    ?>
    <!------------------- Post (Guest) Block --------------------->
    <div class="post-block block">

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            
        
            
            <!-- Post Header -->
            <header class="entry-header">
                <h2 class="entry-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
            </header>
            <!-- END Post Header -->

            <!-- Fandom Tags -->
            <div class="fandom-tags">
                <?php
                $fandoms = get_the_terms( get_the_ID(), 'guest-list' );
                if ( $fandoms && ! is_wp_error( $fandoms ) ) {
                    echo '<div class="tags-list">';
                    $tags = array();
                    foreach ( $fandoms as $fandom ) {
                        $tags[] = '<span class="fandom-tag">' . esc_html( $fandom->name ) . '</span>';
                    }
                    echo implode( ' | ', $tags );
                    echo '</div>';
                }
                ?>
            </div>
            <!-- END Fandom Tags -->

        </article>
    </div>
    <!-- END Post Block -------------------->
    <?php
}



