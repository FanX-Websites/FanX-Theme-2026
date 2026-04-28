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








