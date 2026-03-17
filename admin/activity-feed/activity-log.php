<?php
/**
 * Admin Activity Logger
 * 
 * Tracks all admin/user activities and displays them in a dashboard widget.
 * Stores logs in a custom database table with 30-day retention policy.
 */

class FanX_Activity_Logger {
    private $table_name;
    private $retention_days = 30;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'fanx_activity_log';
        
        // Initialize on theme activation
        add_action('wp_loaded', array($this, 'init'));
    }

    /**
     * Initialize the activity logger
     */
    public function init() {
        // Create table if it doesn't exist
        $this->create_table();
        
        // Hook into various admin actions
        $this->register_hooks();
        
        // Schedule cleanup of old logs
        if (!wp_next_scheduled('fanx_cleanup_activity_logs')) {
            wp_schedule_event(time(), 'daily', 'fanx_cleanup_activity_logs');
        }
    }

    /**
     * Create the custom activity log table
     */
    private function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            user_login varchar(60) NOT NULL,
            action varchar(100) NOT NULL,
            object_type varchar(50),
            object_id bigint(20) UNSIGNED,
            object_title varchar(255),
            details longtext,
            ip_address varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY created_at (created_at),
            KEY object_type (object_type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Register action hooks to capture admin activities
     */
    private function register_hooks() {
        // Post/Page actions
        add_action('save_post', array($this, 'log_post_activity'), 10, 2);
        add_action('delete_post', array($this, 'log_post_delete'), 10, 2);
        add_action('post_stuck', array($this, 'log_post_stuck'), 10, 2);
        add_action('post_unstuck', array($this, 'log_post_unstuck'), 10, 2);

        // User actions
        add_action('user_register', array($this, 'log_user_register'), 10, 1);
        add_action('delete_user', array($this, 'log_user_delete'), 10, 1);
        add_action('profile_update', array($this, 'log_profile_update'), 10, 2);
        add_action('wp_login', array($this, 'log_user_login'), 10, 2);
        add_action('wp_logout', array($this, 'log_user_logout'));

        // Taxonomy/Categories
        add_action('create_category', array($this, 'log_taxonomy_create'), 10, 1);
        add_action('edit_category', array($this, 'log_taxonomy_edit'), 10, 1);
        add_action('delete_category', array($this, 'log_taxonomy_delete'), 10, 2);

        // Cleanup old logs
        add_action('fanx_cleanup_activity_logs', array($this, 'cleanup_old_logs'));
    }

    /**
     * Log post create/update
     */
    public function log_post_activity($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ($post->post_type === 'revision') return;
        
        $user_id = get_current_user_id();
        if (!$user_id) return;

        // Determine if this is new or update
        $is_new = !get_post_meta($post_id, '_fanx_logged', true);
        
        // Create post-type-specific action names
        $type_prefix = $this->get_post_type_label($post->post_type);
        $action = $is_new ? $type_prefix . '_created' : $type_prefix . '_updated';

        if ($is_new) {
            update_post_meta($post_id, '_fanx_logged', true);
        }

        // Determine object type (handle ACF post types specially)
        $object_type = $type_prefix;

        $this->log_activity(
            $action,
            $object_type,
            $post_id,
            $post->post_title,
            json_encode([
                'post_type' => $post->post_type,
                'status' => $post->post_status,
            ])
        );
    }

    /**
     * Log post deletion
     */
    public function log_post_delete($post_id, $post) {
        $user_id = get_current_user_id();
        if (!$user_id) return;

        // Determine object type and create post-type-specific action name
        $type_prefix = $this->get_post_type_label($post->post_type);
        $action = $type_prefix . '_deleted';

        $this->log_activity(
            $action,
            $type_prefix,
            $post_id,
            $post->post_title,
            json_encode(['post_type' => $post->post_type])
        );
    }

    /**
     * Log post sticky status change
     */
    public function log_post_stuck($post_id, $post) {
        $type_prefix = $this->get_post_type_label($post->post_type);
        $this->log_activity($type_prefix . '_stuck', $type_prefix, $post_id, $post->post_title);
    }

    public function log_post_unstuck($post_id, $post) {
        $type_prefix = $this->get_post_type_label($post->post_type);
        $this->log_activity($type_prefix . '_unstuck', $type_prefix, $post_id, $post->post_title);
    }

    /**
     * Log user registration
     */
    public function log_user_register($user_id) {
        $user = get_userdata($user_id);
        $this->log_activity(
            'user_registered',
            'user',
            $user_id,
            $user->user_login,
            json_encode(['email' => $user->user_email])
        );
    }

    /**
     * Log user deletion
     */
    public function log_user_delete($user_id) {
        $user = get_userdata($user_id);
        $this->log_activity(
            'user_deleted',
            'user',
            $user_id,
            $user->user_login
        );
    }

    /**
     * Log profile updates
     */
    public function log_profile_update($user_id, $old_userdata) {
        $user = get_userdata($user_id);
        $this->log_activity(
            'profile_updated',
            'user',
            $user_id,
            $user->user_login
        );
    }

    /**
     * Log user login
     */
    public function log_user_login($user_login, $user) {
        $this->log_activity(
            'user_login',
            'user',
            $user->ID,
            $user_login,
            json_encode(['email' => $user->user_email])
        );
    }

    /**
     * Log user logout
     */
    public function log_user_logout() {
        $user_id = get_current_user_id();
        if ($user_id) {
            $user = get_userdata($user_id);
            $this->log_activity('user_logout', 'user', $user_id, $user->user_login);
        }
    }

    /**
     * Log taxonomy actions
     */
    public function log_taxonomy_create($term_id) {
        $term = get_term($term_id, 'category');
        $this->log_activity('category_created', 'category', $term_id, $term->name);
    }

    public function log_taxonomy_edit($term_id) {
        $term = get_term($term_id, 'category');
        $this->log_activity('category_updated', 'category', $term_id, $term->name);
    }

    public function log_taxonomy_delete($term_id, $tt_id) {
        // Note: get_term fails after deletion, so we capture before
        $this->log_activity('category_deleted', 'category', $term_id);
    }

    /**
     * Core logging function
     */
    private function log_activity($action, $object_type = null, $object_id = null, $object_title = null, $details = null) {
        global $wpdb;
        
        $user_id = apply_filters('fanx_activity_logger_user_id', get_current_user_id());
        if (!$user_id) return;

        $user = get_userdata($user_id);
        
        $wpdb->insert(
            $this->table_name,
            array(
                'user_id' => $user_id,
                'user_login' => $user->user_login,
                'action' => $action,
                'object_type' => $object_type,
                'object_id' => $object_id,
                'object_title' => $object_title,
                'details' => $details,
                'ip_address' => $this->get_user_ip(),
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ),
            array('%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Determine proper object type label for post types
     */
    private function get_post_type_label($post_type) {
        // Map ACF and other special post types
        $type_map = array(
            'acf-field-group' => 'acf_field_group',
            'post' => 'post',
            'page' => 'page',
        );

        return isset($type_map[$post_type]) ? $type_map[$post_type] : $post_type;
    }

    /**
     * Get user IP address
     */
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        }
        return sanitize_text_field($ip);
    }

    /**
     * Clean up logs older than 30 days
     */
    public function cleanup_old_logs() {
        global $wpdb;
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$this->retention_days} days"));
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE created_at < %s",
            $cutoff_date
        ));
    }

    /**
     * Get recent activity logs
     */
    public function get_recent_logs($limit = 20) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT %d",
            $limit
        ));
    }

    /**
     * Get logs by filters
     */
    public function get_logs($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'action' => null,
            'user_id' => null,
            'object_type' => null,
            'from_date' => null,
            'to_date' => null,
            'limit' => 50,
            'offset' => 0,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $values = array();
        
        if ($args['action']) {
            $where[] = 'action = %s';
            $values[] = $args['action'];
        }
        
        if ($args['user_id']) {
            $where[] = 'user_id = %d';
            $values[] = $args['user_id'];
        }
        
        if ($args['object_type']) {
            $where[] = 'object_type = %s';
            $values[] = $args['object_type'];
        }
        
        if ($args['from_date']) {
            $where[] = 'created_at >= %s';
            $values[] = $args['from_date'];
        }
        
        if ($args['to_date']) {
            $where[] = 'created_at <= %s';
            $values[] = $args['to_date'];
        }
        
        $where_clause = implode(' AND ', $where);
        $sql = "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $values[] = $args['limit'];
        $values[] = $args['offset'];
        
        return $wpdb->get_results($wpdb->prepare($sql, $values));
    }

    /**
     * Get total log count
     */
    public function get_log_count($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'action' => null,
            'user_id' => null,
            'object_type' => null,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $values = array();
        
        if ($args['action']) {
            $where[] = 'action = %s';
            $values[] = $args['action'];
        }
        
        if ($args['user_id']) {
            $where[] = 'user_id = %d';
            $values[] = $args['user_id'];
        }
        
        if ($args['object_type']) {
            $where[] = 'object_type = %s';
            $values[] = $args['object_type'];
        }
        
        $where_clause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";
        
        return $wpdb->get_var($wpdb->prepare($sql, $values));
    }
}

// Initialize the activity logger
new FanX_Activity_Logger();
