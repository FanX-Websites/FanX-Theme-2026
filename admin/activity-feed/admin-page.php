<?php
/**
 * Admin Page - Detailed Activity Logs
 * 
 * Full activity log pagination with filters and search
 */

function fanx_register_activity_log_menu() {
    add_submenu_page(
        'tools.php',
        'User Activity Logs',
        'User Activity Logs',
        'manage_options',
        'fanx-activity-logs',
        'fanx_activity_logs_page'
    );
}

add_action('admin_menu', 'fanx_register_activity_log_menu');

function fanx_activity_logs_page() {
    // Check permissions and determine if user can see all logs or only their own
    $is_admin = current_user_can('manage_options');
    $can_view_logs = $is_admin || current_user_can('edit_posts') || current_user_can('seo_manager');
    
    if (!$can_view_logs) {
        wp_die('You do not have permission to access this page.');
    }
    
    // Non-admin users see only their own activity
    $current_user_id = get_current_user_id();
    $view_own_only = !$is_admin;
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'fanx_activity_log';
    
    // Get filter values
    $action_filter = isset($_GET['action_filter']) ? sanitize_text_field($_GET['action_filter']) : '';
    // Non-admin users cannot filter by other users
    $user_filter = $view_own_only ? $current_user_id : (isset($_GET['user_filter']) ? intval($_GET['user_filter']) : 0);
    $object_type_filter = isset($_GET['object_type_filter']) ? sanitize_text_field($_GET['object_type_filter']) : '';
    $object_search = isset($_GET['object_search']) ? sanitize_text_field($_GET['object_search']) : '';
    $from_date = isset($_GET['from_date']) ? sanitize_text_field($_GET['from_date']) : '';
    $to_date = isset($_GET['to_date']) ? sanitize_text_field($_GET['to_date']) : '';
    $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    $per_page = 25;
    $offset = ($paged - 1) * $per_page;
    
    // Define action groups for consolidated filtering
    // Get all logged actions to populate post types dynamically
    $all_logged_actions = $wpdb->get_col("SELECT DISTINCT action FROM {$table_name} ORDER BY action");
    
    // Collect all actions by type
    $created_actions = array();
    $updated_actions = array();
    $delete_actions = array();
    $other_actions = array();
    
    foreach ($all_logged_actions as $action) {
        // Collect all created actions
        if (strpos($action, '_created') !== false || $action === 'user_registered') {
            $created_actions[] = $action;
        }
        // Collect all updated actions (including post sticky status)
        elseif (strpos($action, '_updated') !== false || $action === 'profile_updated' || $action === 'post_stuck' || $action === 'post_unstuck') {
            $updated_actions[] = $action;
        }
        // Collect all delete actions
        elseif (strpos($action, '_deleted') !== false) {
            $delete_actions[] = $action;
        }
        // Keep other actions separate (login, logout, etc.)
        else {
            $other_actions[] = $action;
        }
    }
    
    $action_groups = array(
        'created' => array(
            'label' => 'Create',
            'actions' => $created_actions,
        ),
        'updated' => array(
            'label' => 'Update',
            'actions' => $updated_actions,
        ),
        'delete' => array(
            'label' => 'Delete',
            'actions' => $delete_actions,
        ),
        'user_session' => array(
            'label' => 'User Login/Out',
            'actions' => array('user_login', 'user_logout'),
        ),
    );
    
    // Expand action_filter if it's a group
    $action_filter_values = array();
    if ($action_filter && isset($action_groups[$action_filter])) {
        $action_filter_values = $action_groups[$action_filter]['actions'];
    }
    
    // Build WHERE clause
    $where_clauses = array();
    $where_values = array();
    
    // Non-admin users always see only their own activity
    if ($view_own_only) {
        $where_clauses[] = 'user_id = %d';
        $where_values[] = $current_user_id;
    } elseif ($user_filter) {
        $where_clauses[] = 'user_id = %d';
        $where_values[] = $user_filter;
    }
    
    // Handle action filter (expanded if it's a group)
    if (!empty($action_filter_values)) {
        $action_placeholders = implode(',', array_fill(0, count($action_filter_values), '%s'));
        $where_clauses[] = "action IN ($action_placeholders)";
        $where_values = array_merge($where_values, $action_filter_values);
    }
    
    if ($object_type_filter) {
        if ($object_type_filter === 'acf') {
            // For ACF, match any object_type containing 'acf'
            $where_clauses[] = 'object_type LIKE %s';
            $where_values[] = '%acf%';
        } else {
            // For other types, exact match
            $where_clauses[] = 'object_type = %s';
            $where_values[] = $object_type_filter;
        }
    }
    
    if ($object_search) {
        $where_clauses[] = 'object_title LIKE %s';
        $where_values[] = '%' . $wpdb->esc_like($object_search) . '%';
    }
    
    if ($from_date) {
        $where_clauses[] = 'created_at >= %s';
        $where_values[] = $from_date . ' 00:00:00';
    }
    
    if ($to_date) {
        $where_clauses[] = 'created_at <= %s';
        $where_values[] = $to_date . ' 23:59:59';
    }
    
    $where = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM {$table_name} {$where}";
    if (!empty($where_clauses)) {
        $total = $wpdb->get_var($wpdb->prepare($count_sql, $where_values));
    } else {
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    }
    
    $pages = ceil($total / $per_page);
    
    // Get logs
    $logs_sql = "SELECT * FROM {$table_name} {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d";
    if (!empty($where_clauses)) {
        $where_values[] = $per_page;
        $where_values[] = $offset;
        $logs = $wpdb->get_results($wpdb->prepare($logs_sql, $where_values));
    } else {
        $logs = $wpdb->get_results($wpdb->prepare($logs_sql, $per_page, $offset));
    }
    
    // Get unique object types for filter dropdown
    $all_object_types = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT object_type FROM {$table_name} WHERE object_type IS NOT NULL ORDER BY object_type"
    ));
    
    // Consolidate ACF types into a single "acf" entry
    $object_types = array();
    $has_acf = false;
    foreach ($all_object_types as $type) {
        if (strpos($type, 'acf') !== false) {
            $has_acf = true;
        } else {
            $object_types[] = $type;
        }
    }
    if ($has_acf) {
        $object_types[] = 'acf';
    }
    sort($object_types);
    
    // Get users for filter dropdown (only for admins)
    if ($is_admin) {
        $users = get_users(array(
            'orderby' => 'display_name',
            'order' => 'ASC',
            'number' => 100,
        ));
    } else {
        $users = array();
    }
    
    ?>
    <div class="wrap">
        <h1>User Activity Logs</h1>
        <p style="color: #666; margin-bottom: 20px;">View and filter all admin activities on your site. Logs are retained for 30 days.</p>
        
        <!-- Cleanup Status (Admin Only) -->
        <?php if ($is_admin) : ?>
            <?php 
            // Get cleanup status
            $logger = new FanX_Activity_Logger();
            
            // Handle manual cleanup trigger
            if (isset($_POST['fanx_cleanup_now']) && wp_verify_nonce($_POST['fanx_cleanup_nonce'], 'fanx_cleanup_nonce')) {
                $logger->cleanup_old_logs();
                echo '<div class="notice notice-success"><p>✓ Cleanup executed successfully!</p></div>';
            }
            
            $cleanup_status = $logger->get_cleanup_status();
            $last_cleanup_ts = $cleanup_status['last_cleanup'] !== 'Never' ? strtotime($cleanup_status['last_cleanup']) : 0;
            $overdue = $last_cleanup_ts === 0 || $last_cleanup_ts < strtotime('-30 days');
            $bg_color = $overdue ? '#fff3cd' : '#e8f5e9';
            $border_color = $overdue ? '#ffc107' : '#4caf50';
            $label_color = $overdue ? '#856404' : '#2e7d32';
            $value_color = $overdue ? '#856404' : '#558b2f';
            ?>
            <div style="background: <?php echo $bg_color; ?>; border-left: 4px solid <?php echo $border_color; ?>; padding: 12px; margin-bottom: 20px; border-radius: 4px;">
                <strong style="color: <?php echo $label_color; ?>;">Cleanup Status:</strong>
                <span style="color: <?php echo $value_color; ?>; display: block; margin-top: 6px;">
                    Last cleanup: <strong><?php echo esc_html($cleanup_status['last_cleanup']); ?></strong>
                </span>
                <small style="color: #666; display: block; margin-top: 6px;">Total records: <strong><?php echo esc_html($cleanup_status['total_records']); ?></strong> | Records older than 30 days: <strong><?php echo esc_html($cleanup_status['records_older_than_30_days']); ?></strong></small>
                
                <div style="margin-top: 10px;">
                    <form method="post" style="display: inline;">
                        <?php wp_nonce_field('fanx_cleanup_nonce', 'fanx_cleanup_nonce'); ?>
                        <button type="submit" name="fanx_cleanup_now" class="button button-small" style="background: #2196F3; color: white; border: none; cursor: pointer;">Run Cleanup Now</button>
                    </form>
                    <?php if ($cleanup_status['records_older_than_30_days'] > 0) : ?>
                        <span style="color: #d32f2f; margin-left: 10px;"><strong>⚠️ <?php echo esc_html($cleanup_status['records_older_than_30_days']); ?> old records to clean up!</strong></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div style="background: #f5f5f5; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <form method="get" action="">
                <input type="hidden" name="page" value="fanx-activity-logs" />
                
                <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                    <div>
                        <label for="action_filter" style="display: block; margin-bottom: 5px; font-weight: bold;">Action:</label>
                        <select name="action_filter" id="action_filter" style="padding: 5px;">
                            <option value="">-- All Actions --</option>
                            <?php foreach ($action_groups as $group_key => $group_data) : ?>
                                <option value="<?php echo esc_attr($group_key); ?>" <?php selected($action_filter, $group_key); ?>>
                                    <?php echo esc_html($group_data['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="user_filter" style="display: block; margin-bottom: 5px; font-weight: bold;">User:</label>
                        <select name="user_filter" id="user_filter" style="padding: 5px;" <?php echo $view_own_only ? 'disabled' : ''; ?>>
                            <option value="">-- All Users --</option>
                            <?php foreach ($users as $user) : ?>
                                <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($user_filter, $user->ID); ?>>
                                    <?php echo esc_html($user->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($view_own_only) : ?>
                            <p style="font-size: 12px; color: #666; margin-top: 3px;">Viewing your activity only</p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="object_type_filter" style="display: block; margin-bottom: 5px; font-weight: bold;">Type:</label>
                        <select name="object_type_filter" id="object_type_filter" style="padding: 5px;">
                            <option value="">-- All Types --</option>
                            <?php foreach ($object_types as $type) : ?>
                                <option value="<?php echo esc_attr($type); ?>" <?php selected($object_type_filter, $type); ?>>
                                    <?php 
                                    if ($type === 'acf') {
                                        echo 'ACF';
                                    } else {
                                        echo esc_html(fanx_get_object_type_label($type));
                                    }
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="object_search" style="display: block; margin-bottom: 5px; font-weight: bold;">Search Title/Name:</label>
                        <input type="text" name="object_search" id="object_search" value="<?php echo esc_attr($object_search); ?>" placeholder="Search by name..." style="padding: 5px;" />
                    </div>
                    
                    <div>
                        <label for="from_date" style="display: block; margin-bottom: 5px; font-weight: bold;">From:</label>
                        <input type="date" name="from_date" id="from_date" value="<?php echo esc_attr($from_date); ?>" style="padding: 5px;" />
                    </div>
                    
                    <div>
                        <label for="to_date" style="display: block; margin-bottom: 5px; font-weight: bold;">To:</label>
                        <input type="date" name="to_date" id="to_date" value="<?php echo esc_attr($to_date); ?>" style="padding: 5px;" />
                    </div>
                    
                    <button type="submit" class="button button-primary" style="margin-top: 20px;">Filter</button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=fanx-activity-logs')); ?>" class="button" style="margin-top: 20px;">Clear</a>
                </div>
            </form>
        </div>
        
        <!-- Results Info -->
        <p style="color: #666; margin-bottom: 15px;">
            Showing <strong><?php echo esc_html(count($logs)); ?></strong> of <strong><?php echo esc_html($total); ?></strong> results
        </p>
        
        <!-- Table -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th style="width: 15%;">Date/Time</th>
                    <th style="width: 20%;">User</th>
                    <th style="width: 25%;">Action</th>
                    <th style="width: 20%;">Title/Name</th>
                    <th style="width: 20%;">IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)) : ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px; color: #999;">
                            No user activity logs found.
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($logs as $log) : ?>
                        <?php 
                        $user = get_userdata($log->user_id);
                        $user_name = $user ? $user->display_name : 'Unknown';
                        $user_url = $user ? add_query_arg('user_filter', $log->user_id) : '#';
                        ?>
                        <tr>
                            <td>
                                <span title="<?php echo esc_attr($log->created_at); ?>">
                                    <?php echo esc_html(wp_date('M d, Y H:i', strtotime($log->created_at . ' UTC'))); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo esc_url($user_url); ?>" style="color: #0073aa; text-decoration: none;">
                                    <?php echo esc_html($user_name); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo esc_html(fanx_get_activity_label($log->action)); ?>
                            </td>
                            <td>
                                <?php if ($log->object_title) : ?>
                                    <?php $edit_url = fanx_get_object_edit_url($log->object_type, $log->object_id); ?>
                                    <?php if ($edit_url) : ?>
                                        <a href="<?php echo esc_url($edit_url); ?>" style="color: #0073aa; text-decoration: none;"><?php echo esc_html(substr($log->object_title, 0, 50)); ?></a>
                                    <?php else : ?>
                                        <em><?php echo esc_html(substr($log->object_title, 0, 50)); ?></em>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <span style="color: #999;">--</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code style="background: #f5f5f5; padding: 2px 5px; border-radius: 3px; font-size: 11px;">
                                    <?php echo esc_html($log->ip_address); ?>
                                </code>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($pages > 1) : ?>
            <div class="tablenav bottom">
                <div class="pagination" style="margin-top: 20px;">
                    <?php 
                    $page_args = array_merge($_GET, array('paged' => 1));
                    echo '<a href="' . esc_url(add_query_arg($page_args)) . '" class="button" style="margin-right: 5px;">« First</a>';
                    
                    if ($paged > 1) {
                        $page_args['paged'] = $paged - 1;
                        echo '<a href="' . esc_url(add_query_arg($page_args)) . '" class="button" style="margin-right: 5px;">‹ Previous</a>';
                    }
                    
                    echo '<span style="margin: 0 10px;"><strong>' . esc_html($paged) . '</strong> of <strong>' . esc_html($pages) . '</strong></span>';
                    
                    if ($paged < $pages) {
                        $page_args['paged'] = $paged + 1;
                        echo '<a href="' . esc_url(add_query_arg($page_args)) . '" class="button" style="margin-left: 5px;">Next ›</a>';
                    }
                    
                    $page_args['paged'] = $pages;
                    echo '<a href="' . esc_url(add_query_arg($page_args)) . '" class="button" style="margin-left: 5px;">Last »</a>';
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
