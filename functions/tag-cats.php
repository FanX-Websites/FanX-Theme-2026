<?php 
/*
Tweaks to Categories, Tags, Custom Taxonomies, etc

What this File Does: 
    1 - Adds Category & Tag Support to All Post Types
    2 - Allows Child Categories to Use Parent Category Templates
    3 - Removes "Category: " Prefix from Category Archive Titles
    4 - //TODO: Removes Parent Category Slug from Child Categories - Partners 
    5 - //TODO: REmove Category Base from URLs

*/

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

//END Add Support ----------------------------------->

//Parent & Child Category Tweaks ----------------------------------->

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
            return preg_replace( '/^[^:]+:\s*/', '', $title );
        } );
    //END 3- Archive Page Settings - Remove Archive Prefix

    //NOTE: NO IDEA WHAT or WHY THIS IS
    // //Default Category Tweaks -------------------> 
    //     function df_default_category_handler() {
    //         // Get default category from ACF field
    //         $default_cat_id = get_field( 'default_category', 'option' );
            
    //         // Get default tags from ACF field
    //         $default_tag_ids = get_field( 'default_tags', 'option' );
            
    //         if ( $default_cat_id ) {
    //             $default_category = get_category( $default_cat_id );
    //             // Hook here for default category behaviors
    //             return $default_category;
    //         }
            
    //         return false;
    //     }
    //     add_action( 'init', 'df_default_category_handler' );
    // //END Default Category Tweaks ------------------->

    // //Categories in URLS ----------------------------------------------->
       
    // //END Categories in URLS <-----------------------------------------------