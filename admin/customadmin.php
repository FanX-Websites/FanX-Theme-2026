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
            'title' => '|  <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -1100 960 960" width="20px" fill="#e3e3e3"><path d="M508-267.77q-28-27.78-28-68Q480-376 507.77-404q27.78-28 68-28Q616-432 644-404.23q28 27.78 28 68Q672-296 644.23-268q-27.78 28-68 28Q536-240 508-267.77ZM216-96q-29.7 0-50.85-21.5Q144-139 144-168v-528q0-29 21.15-50.5T216-768h72v-96h72v96h240v-96h72v96h72q29.7 0 50.85 21.5Q816-725 816-696v528q0 29-21.15 50.5T744-96H216Zm0-72h528v-360H216v360Zm0-432h528v-96H216v96Zm0 0v-96 96Z"/></svg> ' . get_field('event_date', 'option'), //Event Dates
            'href' => $eventinfoTAB
            ));
        
        //Activity Log Link
            $activity_log_url = admin_url('admin.php?page=fanx-activity-logs');
            $wp_admin_bar->add_menu( array(
                'parent' => false,
                'id' => 'activity-log',
                'title' => '|  <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -1100 960 960" width="20px" fill="#e3e3e3"><path d="M566-769.89q-14-13.88-14-34Q552-824 565.89-838q13.88-14 34-14Q620-852 634-838.11q14 13.88 14 34Q648-784 634.11-770q-13.88 14-34 14Q580-756 566-769.89Zm0 648q-14-13.88-14-34Q552-176 565.89-190q13.88-14 34-14Q620-204 634-190.11q14 13.88 14 34Q648-136 634.11-122q-13.88 14-34 14Q580-108 566-121.89Zm168-504q-14-13.88-14-34Q720-680 733.89-694q13.88-14 34-14Q788-708 802-694.11q14 13.88 14 34Q816-640 802.11-626q-13.88 14-34 14Q748-612 734-625.89Zm0 360q-14-13.88-14-34Q720-320 733.89-334q13.88-14 34-14Q788-348 802-334.11q14 13.88 14 34Q816-280 802.11-266q-13.88 14-34 14Q748-252 734-265.89Zm48-180q-14-13.88-14-34Q768-500 781.89-514q13.88-14 34-14Q836-528 850-514.11q14 13.88 14 34Q864-460 850.11-446q-13.88 14-34 14Q796-432 782-445.89ZM480-96q-79.38 0-149.19-30T208.5-208.5Q156-261 126-330.96t-30-149.5Q96-560 126-629.5q30-69.5 82.5-122T330.81-834q69.81-30 149.19-30v72q-130 0-221 91t-91 221q0 130 91 221t221 91v72Zm134-214L444-480v-240h72v210l149 149-51 51Z"/></svg> Activity Log',
                'href' => $activity_log_url
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

    //Add Active User Count to Admin Bar 
        function df_add_user_count_to_admin_bar() {
            if (!current_user_can('manage_options')) {
                return; // Only show to admins
            }
        
            // Get count of users with active sessions (logged in within last 15 minutes)
            $active_users = df_get_active_users_count();
        
            global $wp_admin_bar;
            $wp_admin_bar->add_menu( array(
                'id' => 'user_count',
                'title' => '|   <svg xmlns="http://www.w3.org/2000/svg" height="18px" viewBox="0 -1150 960 960" width="20px" fill="#e3e3e3"><path d="M599-361q49-49 49-119t-49-119q-49-49-119-49t-119 49q-49 49-49 119t49 119q49 49 119 49t119-49Zm-187-51q-28-28-28-68t28-68q28-28 68-28t68 28q28 28 28 68t-28 68q-28 28-68 28t-68-28ZM220-270.5Q103-349 48-480q55-131 172-209.5T480-768q143 0 260 78.5T912-480q-55 131-172 209.5T480-192q-143 0-260-78.5ZM480-480Zm207 158q95-58 146-158-51-100-146-158t-207-58q-112 0-207 58T127-480q51 100 146 158t207 58q112 0 207-58Z"/></svg> Online Users: ' . $active_users,
                'href' => admin_url('users.php')
            ));
        }
        add_action('wp_before_admin_bar_render', 'df_add_user_count_to_admin_bar');
        
        /**
         * Get count of currently active/logged-in users
         * Checks for users with valid, non-expired session tokens
         */
        function df_get_active_users_count() {
            global $wpdb;
            
            $active_users = 0;
            $current_time = time();
            
            // Get all users with session tokens
            $user_sessions = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT user_id, meta_value FROM {$wpdb->usermeta} 
                     WHERE meta_key = %s",
                    'session_tokens'
                )
            );
            
            // Check each user's session tokens for valid/non-expired ones
            foreach ($user_sessions as $session) {
                $tokens = maybe_unserialize($session->meta_value);
                
                if (is_array($tokens) && !empty($tokens)) {
                    // Check if user has at least one valid token
                    foreach ($tokens as $token_hash => $token_data) {
                        if (is_array($token_data) && isset($token_data['expiration'])) {
                            // If token hasn't expired, count this user as active
                            if ($token_data['expiration'] > $current_time) {
                                $active_users++;
                                break; // Count user only once
                            }
                        }
                    }
                }
            }
            
            return $active_users;
        }
    //END - Add Active User Count to Admin Bar


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

// ------ ADMIN MENU LOGO --->
    function df_admin_menu_logo() {
        if (!function_exists('get_field')) {
            return; // ACF is not active, exit the function
        }
        
        $logo = get_field('event_logo', 'option');
        
        if ($logo) {
            $logo_url = $logo;
            if (is_array($logo)) {
                $logo_url = $logo['url'];
            }
            
            echo '<style>
                #adminmenu::before {
                    content: "";
                    display: block;
                    width: 100%;
                    margin: 0 auto;
                    padding: 0; 
                    background-image: url("' . esc_url($logo_url) . '");
                    background-size: contain;
                    background-repeat: no-repeat;
                    background-position: center;
                    height: auto;
                    aspect-ratio: auto;
                    min-height: 150px;
                }
            </style>';
        }
    }
    add_action('admin_head', 'df_admin_menu_logo');

// --- END ADMIN MENU LOGO <---

            
//Use When Needed:
   // remove_action('shutdown', 'wp_ob_end_flush_all', 1);  //Flush error
  //flush_rewrite_rules(); //Flush Rules


