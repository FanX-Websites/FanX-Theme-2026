<?php
/**
 * Admin Page - Detailed Activity Logs
 * 
 * Full activity log pagination with filters and search
 */

function fanx_register_activity_log_menu() {
    add_submenu_page(
        'tools.php',
        'Activity Logs',
        'Activity Logs',
        'manage_options',
        'fanx-activity-logs',
        'fanx_activity_logs_page'
    );
}

add_action('admin_menu', 'fanx_register_activity_log_menu');

function fanx_activity_logs_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fanx_activity_log';
    
    // Get filter values
    $action_filter = isset($_GET['action_filter']) ? sanitize_text_field($_GET['action_filter']) : '';
    $user_filter = isset($_GET['user_filter']) ? intval($_GET['user_filter']) : 0;
    $object_type_filter = isset($_GET['object_type_filter']) ? sanitize_text_field($_GET['object_type_filter']) : '';
    $from_date = isset($_GET['from_date']) ? sanitize_text_field($_GET['from_date']) : '';
    $to_date = isset($_GET['to_date']) ? sanitize_text_field($_GET['to_date']) : '';
    $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    $per_page = 25;
    $offset = ($paged - 1) * $per_page;
    
    // Build WHERE clause
    $where_clauses = array();
    $where_values = array();
    
    if ($action_filter) {
        $where_clauses[] = 'action = %s';
        $where_values[] = $action_filter;
    }
    
    if ($user_filter) {
        $where_clauses[] = 'user_id = %d';
        $where_values[] = $user_filter;
    }
    
    if ($object_type_filter) {
        $where_clauses[] = 'object_type = %s';
        $where_values[] = $object_type_filter;
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
    
    // Get unique actions for filter dropdown
    $actions = $wpdb->get_col("SELECT DISTINCT action FROM {$table_name} ORDER BY action");
    
    // Get unique object types for filter dropdown
    $object_types = $wpdb->get_col("SELECT DISTINCT object_type FROM {$table_name} WHERE object_type IS NOT NULL ORDER BY object_type");
    
    // Get users for filter dropdown
    $users = get_users(array(
        'orderby' => 'display_name',
        'order' => 'ASC',
        'number' => 100,
    ));
    
    ?>
    <div class="wrap">
        <h1>Activity Logs</h1>
        <p style="color: #666; margin-bottom: 20px;">View and filter all admin activities on your site. Logs are retained for 30 days.</p>
        
        <!-- Filters -->
        <div style="background: #f5f5f5; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <form method="get" action="">
                <input type="hidden" name="page" value="fanx-activity-logs" />
                
                <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                    <div>
                        <label for="action_filter" style="display: block; margin-bottom: 5px; font-weight: bold;">Action:</label>
                        <select name="action_filter" id="action_filter" style="padding: 5px;">
                            <option value="">-- All Actions --</option>
                            <?php foreach ($actions as $action) : ?>
                                <option value="<?php echo esc_attr($action); ?>" <?php selected($action_filter, $action); ?>>
                                    <?php echo esc_html(fanx_get_activity_label($action)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="user_filter" style="display: block; margin-bottom: 5px; font-weight: bold;">User:</label>
                        <select name="user_filter" id="user_filter" style="padding: 5px;">
                            <option value="">-- All Users --</option>
                            <?php foreach ($users as $user) : ?>
                                <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($user_filter, $user->ID); ?>>
                                    <?php echo esc_html($user->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="object_type_filter" style="display: block; margin-bottom: 5px; font-weight: bold;">Type:</label>
                        <select name="object_type_filter" id="object_type_filter" style="padding: 5px;">
                            <option value="">-- All Types --</option>
                            <?php foreach ($object_types as $type) : ?>
                                <option value="<?php echo esc_attr($type); ?>" <?php selected($object_type_filter, $type); ?>>
                                    <?php echo esc_html(fanx_get_object_type_label($type)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                    <th style="width: 20%;">Object</th>
                    <th style="width: 20%;">IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)) : ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px; color: #999;">
                            No activity logs found.
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
                                    <?php echo esc_html(wp_date('M d, Y H:i', strtotime($log->created_at))); ?>
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
