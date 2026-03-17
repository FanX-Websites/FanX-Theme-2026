<?php
/**
 * Admin Dashboard Widget - Activity Feed
 * 
 * Displays recent admin activities in the WordPress dashboard
 */

function fanx_activity_log_dashboard_widget() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fanx_activity_log';
    
    $logs = $wpdb->get_results("
        SELECT * FROM {$table_name} 
        ORDER BY created_at DESC 
        LIMIT 15
    ");
    
    echo '<div class="fanx-activity-feed" style="max-height: 500px; overflow-y: auto;">';
    
    if (empty($logs)) {
        echo '<p style="padding: 10px; color: #999;">No recent activity recorded.</p>';
    } else {
        echo '<ul style="list-style: none; padding: 0; margin: 0;">';
        
        foreach ($logs as $log) {
            $user = get_userdata($log->user_id);
            $user_name = $user ? $user->display_name : 'Unknown User';
            $action_label = fanx_get_activity_label($log->action);
            $time_ago = human_time_diff(strtotime($log->created_at), current_time('timestamp')) . ' ago';
            
            echo '<li style="padding: 10px; border-bottom: 1px solid #eee; font-size: 13px;">';
            echo '<strong>' . esc_html($user_name) . '</strong> ';
            echo esc_html($action_label);
            
            if ($log->object_title) {
                $edit_url = fanx_get_object_edit_url($log->object_type, $log->object_id);
                if ($edit_url) {
                    echo ' <a href="' . esc_url($edit_url) . '" style="color: #666; text-decoration: none;">' . esc_html($log->object_title) . '</a>';
                } else {
                    echo ' <em style="color: #666;">' . esc_html($log->object_title) . '</em>';
                }
            }
            
            echo '<br /><span style="color: #999; font-size: 11px;">' . esc_html($time_ago) . '</span>';
            echo '</li>';
        }
        
        echo '</ul>';
        echo '<p style="padding: 10px; margin: 10px 0 0 0; border-top: 1px solid #eee; text-align: center;">';
        echo '<a href="' . esc_url(admin_url('admin.php?page=fanx-activity-logs')) . '" style="color: #0073aa; text-decoration: none;">View All Activity →</a>';
        echo '</p>';
    }
    
    echo '</div>';
}

function fanx_add_activity_log_widget() {
    wp_add_dashboard_widget(
        'fanx_activity_log_widget',
        'Site Activity Feed',
        'fanx_activity_log_dashboard_widget'
    );
}

add_action('wp_dashboard_setup', 'fanx_add_activity_log_widget');

/**
 * Get human-readable activity labels
 */
function fanx_get_activity_label($action) {
    // Static labels for non-post-type actions
    $static_labels = array(
        'user_registered' => 'registered a new user',
        'user_deleted' => 'deleted a user',
        'user_login' => 'logged in',
        'user_logout' => 'logged out',
        'profile_updated' => 'updated their profile',
        'category_created' => 'created a category',
        'category_updated' => 'updated a category',
        'category_deleted' => 'deleted a category',
        'editor_lock_request' => 'requested edit access',
        'editor_lock_approved' => 'approved edit access request',
        'editor_lock_denied' => 'denied edit access request',
    );
    
    // Check static labels first
    if (isset($static_labels[$action])) {
        return $static_labels[$action];
    }
    
    // Parse post-type-based actions dynamically
    // Format: {post_type}_{action_verb}
    $parts = explode('_', $action);
    if (count($parts) >= 2) {
        $action_verb = array_pop($parts); // Get last part (created, updated, deleted, stuck, unstuck)
        $post_type = implode('_', $parts); // Rejoin remaining parts as post type
        
        $post_type_label = fanx_get_object_type_label($post_type);
        $action_verbs = array(
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'deleted',
            'stuck' => 'pinned',
            'unstuck' => 'unpinned',
        );
        
        if (isset($action_verbs[$action_verb])) {
            $verb = $action_verbs[$action_verb];
            $article = in_array(strtolower($post_type_label[0]), ['a','e','i','o','u']) ? 'an' : 'a';
            return $verb . ' ' . $article . ' ' . strtolower($post_type_label);
        }
    }
    
    // Fallback to generic label
    return ucwords(str_replace('_', ' ', $action));
}

/**
 * Get edit URL for an object based on type and ID
 */
function fanx_get_object_edit_url($object_type, $object_id) {
    if (!$object_id) return false;
    
    switch ($object_type) {
        case 'post':
        case 'page':
            return get_edit_post_link($object_id);
        case 'acf_field_group':
            // ACF field group edit URL
            return 'post.php?post=' . $object_id . '&action=edit';
        case 'user':
            return get_edit_user_link($object_id);
        case 'category':
            return get_edit_term_link($object_id, 'category');
        default:
            // Try generic post edit
            return get_edit_post_link($object_id);
    }
}

/**
 * Get human-readable object type labels
 */
function fanx_get_object_type_label($object_type) {
    $labels = array(
        'acf_field_group' => 'ACF Field Group',
        'post' => 'Post',
        'page' => 'Page',
        'user' => 'User',
        'category' => 'Category',
    );
    
    return isset($labels[$object_type]) ? $labels[$object_type] : ucfirst(str_replace('_', ' ', $object_type));
}
