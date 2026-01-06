<?php 
/*
Tweaks to Categories, Tags, Custom Taxonomies, etc
*/

//Add Support ----------------------------------->
    //support for Categories & Tags
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

// -----All Post types in Category Pages ------------------->
    function df_include_all_post_types_in_category_archive($query) {
        if ($query->is_category() && $query->is_main_query() && !is_admin()) {
            $query->set('post_type', 'any'); 
        }
    }
    add_action('pre_get_posts', 'df_include_all_post_types_in_category_archive');


//Templates - Child Copy Catts the Parents  ------------------->
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

//XP Status (Tag) Rebranding ------------------->
    function df_rename_tags_taxonomy() {
        global $wp_taxonomies;
 
        if (isset($wp_taxonomies['post_tag'])) {
            $wp_taxonomies['post_tag']->labels = (object) array_merge((array) $wp_taxonomies['post_tag']->labels, array(
                'name' => 'XPstatus',
                'singular_name' => 'XPstatus',
                'menu_name' => 'XPstatus',
                'search_items' => 'Search XPstatus',
                'popular_items' => 'Popular XPstatus',
                'all_items' => 'All XPstatus',
                'edit_item' => 'Edit XPstatus',
                'view_item' => 'View XPstatus',
                'update_item' => 'Update XPstatus',
                'add_new_item' => 'Add New XPstatus',
                'new_item_name' => 'New XPstatus Name',
                'separate_items_with_commas' => 'Separate XPstatus with commas',
                'add_or_remove_items' => 'Add or remove XPstatus',
                'choose_from_most_used' => 'Choose from the most used XPstatus',
                'not_found' => 'No XPstatus found.',
            ));
        }
    }
    add_action('init', 'df_rename_tags_taxonomy', 11);
    //END XP Status (Tag) Tweaks

    //Archive Page Settings - Remove Archive Prefix
    add_filter( 'get_the_archive_title', function( $title ) {
        return preg_replace( '/^[^:]+:\s*/', '', $title );
    } );

    //Remove the Slugs 
    

