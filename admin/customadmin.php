<?php 
/* 
Custom Admin Area, Dashboard, Admin Columns,etc 
*/

// --- Admin Bar ---->
    //Add Links to Admin Bar 
    function df_admin_bar_render() {
        if (!function_exists('get_field')) {
            return; // ACF is not active, exit the function
        }
        global $wp_admin_bar;

        //Event Dates - Info link         
            $eventinfoTAB = '/wp-admin/admin.php?page=event-info'; //Event-Info Page
            $wp_admin_bar->add_menu( array(
            'parent' => false,
            'id' => 'eventinfo',
            'title' => get_field('event_date', 'options'), //Event Dates
            'href' => $eventinfoTAB
            ));
        
    }   
    add_action( 'wp_before_admin_bar_render', 'df_admin_bar_render' );

    // Remove from admin bar
        //Comments        
        add_action( 'wp_before_admin_bar_render', 'df_remove_comments_admin_bar' );  
                function df_remove_comments_admin_bar() {
                    global $wp_admin_bar;
                    $wp_admin_bar->remove_menu('comments');
                }


// <---- END Admin Bar --

//--- Admin Columns ---> 
    //Custom Column Layout ---> 
    function df_custom_admin_columns($columns) {
        $columns = array(
            'cb' => $columns['cb'], //Checkbox
            'thumbnail' => __('Thumbnail'), //Thumbnail
            'title' => __('Title'), //Post Title
            'slug' => __('Slug'), //Slug
            'categories' => __('Main Cats'), //Main Category
            'tags' => __('XP Status'), //Xp Status
            'date' => __('Date'), //Date Published
            );
            return $columns;
        }
        function df_apply_columns_to_all_post_types() {
            $all_post_types = get_post_types(array('public' => true), 'names'); // Get all public post types
        
            foreach ($all_post_types as $post_type) {
                add_filter("manage_edit-{$post_type}_columns", 'df_custom_admin_columns');
            }
        }
        add_action('admin_init', 'df_apply_columns_to_all_post_types');

    // Populate custom column data
        function df_custom_admin_columns_content($column, $post_id){
            switch ($column) {
                case 'thumbnail':
                    echo get_the_post_thumbnail($post_id, array(150, 150));
                    break;
                case 'slug':
                    $post = get_post($post_id);
                    echo $post->post_name;
                    break;
        
        }
    }
    //Apply to all post types dynamically
        function df_apply_columns_content_to_all_post_types() {
            $all_post_types = get_post_types(array('public' => true), 'names'); // Get all public post types

            foreach ($all_post_types as $post_type) {
                add_action("manage_{$post_type}_posts_custom_column", 'df_custom_admin_columns_content', 10, 2);
            }
        }
        add_action('admin_init', 'df_apply_columns_content_to_all_post_types');
    
    //Title Column 
        function df_custom_column_title($column, $post_id) {
            if ($column === 'title') { 
                $post = get_post($post_id);

                // Display the title
                echo '<strong>' . get_the_title($post_id) . '</strong>';
                
                // Append the slug below the title
                echo '<br><small style="color: #888;">Slug: ' . esc_html($post->post_name) . '</small>';
            }
        }
        add_action('manage_posts_custom_column', 'df_custom_column_title', 10, 2);
        add_action('manage_pages_custom_column', 'df_custom_column_title', 10, 2);

// --- END Admin Columns <-- 



// -- Admin Menu --->
    //Remove the things
        // -- Remove Comments -->
            add_action( 'admin_menu', 'df_remove_admin_menus' ); // Remove from admin menu
            function df_remove_admin_menus() {
                remove_menu_page( 'edit-comments.php' );
            }

            add_action('init', 'df_remove_comment_support', 100);  // Remove from post and pages
            function df_remove_comment_support() {
                remove_post_type_support( 'post', 'comments' );
                remove_post_type_support( 'page', 'comments' );
            }
            
// --- END Admin Menu <--- 
            
//Use When Needed:
   // remove_action('shutdown', 'wp_ob_end_flush_all', 1);  //Flush error
  //flush_rewrite_rules(); //Flush Rules


