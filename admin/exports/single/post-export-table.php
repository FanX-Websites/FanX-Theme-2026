<?php
/**
 * Post Export Database Table Manager
 * 
 * Manages the custom database table for storing scheduled single post exports
 * Replaces wp-cron dependency with direct database storage + system cron executor
 * 
 * Table: {prefix}fanx_scheduled_post_exports
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Create or upgrade the scheduled post exports table
 * Called during theme initialization
 */
function fanx_create_scheduled_post_exports_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'fanx_scheduled_post_exports';
    $charset_collate = $wpdb->get_charset_collate();
    
    // Check if table already exists
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
        return true;
    }
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        post_title varchar(255) NOT NULL,
        post_type varchar(50) NOT NULL,
        scheduled_time datetime NOT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        executed_at datetime NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        error_message longtext NULL,
        PRIMARY KEY (id),
        KEY post_id (post_id),
        KEY scheduled_time (scheduled_time),
        KEY status (status)
    ) $charset_collate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    
    error_log( '[POST EXPORT TABLE] Created scheduled post exports table' );
    return true;
}

/**
 * Insert or update a scheduled post export
 * 
 * @param int $post_id The post ID to export
 * @param int $scheduled_timestamp Unix timestamp for when to run
 * @return array Result with success flag and message
 */
function fanx_insert_scheduled_post_export( $post_id, $scheduled_timestamp ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'fanx_scheduled_post_exports';
    $post = get_post( $post_id );
    
    if ( ! $post ) {
        return array(
            'success' => false,
            'message' => 'Post not found',
        );
    }
    
    // Check if an export is already scheduled for this post
    $existing = $wpdb->get_row( $wpdb->prepare(
        "SELECT id FROM $table_name WHERE post_id = %d AND status = 'pending'",
        $post_id
    ) );
    
    if ( $existing ) {
        // Update existing scheduled export
        $result = $wpdb->update(
            $table_name,
            array(
                'scheduled_time' => wp_date( 'Y-m-d H:i:s', $scheduled_timestamp ),
                'created_at' => wp_date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
            ),
            array( 'id' => $existing->id ),
            array( '%s', '%s' ),
            array( '%d' )
        );
        
        if ( false === $result ) {
            error_log( '[POST EXPORT TABLE] Failed to update scheduled export for post ' . $post_id );
            return array(
                'success' => false,
                'message' => 'Failed to update scheduled export',
            );
        }
        
        error_log( '[POST EXPORT TABLE] Updated scheduled post export: "' . $post->post_title . '" (ID: ' . $post_id . ') for ' . wp_date( 'Y-m-d H:i:s', $scheduled_timestamp ) );
    } else {
        // Insert new scheduled export
        $result = $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'post_title' => $post->post_title,
                'post_type' => $post->post_type,
                'scheduled_time' => wp_date( 'Y-m-d H:i:s', $scheduled_timestamp ),
                'created_at' => wp_date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
                'status' => 'pending',
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s' )
        );
        
        if ( false === $result ) {
            error_log( '[POST EXPORT TABLE] Failed to insert scheduled export for post ' . $post_id );
            return array(
                'success' => false,
                'message' => 'Failed to schedule export',
            );
        }
        
        error_log( '[POST EXPORT TABLE] Scheduled post export: "' . $post->post_title . '" (ID: ' . $post_id . ') for ' . wp_date( 'Y-m-d H:i:s', $scheduled_timestamp ) );
    }
    
    return array(
        'success' => true,
        'message' => 'Export scheduled successfully',
        'scheduled_time' => wp_date( 'Y-m-d H:i:s', $scheduled_timestamp ),
    );
}

/**
 * Get the next scheduled post export for a specific post
 * 
 * @param int $post_id The post ID to check
 * @return object|null The scheduled export record or null if none found
 */
function fanx_get_next_scheduled_post_export( $post_id ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'fanx_scheduled_post_exports';
    
    $export = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM $table_name WHERE post_id = %d AND status = 'pending' ORDER BY scheduled_time ASC LIMIT 1",
        $post_id
    ) );
    
    return $export;
}

/**
 * Cancel a scheduled post export
 * 
 * @param int $post_id The post ID to cancel
 * @return array Result with success flag and message
 */
function fanx_cancel_scheduled_post_export( $post_id ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'fanx_scheduled_post_exports';
    
    $export = fanx_get_next_scheduled_post_export( $post_id );
    
    if ( ! $export ) {
        return array(
            'success' => false,
            'message' => 'No scheduled export found for this post',
        );
    }
    
    $result = $wpdb->update(
        $table_name,
        array( 'status' => 'cancelled' ),
        array( 'id' => $export->id ),
        array( '%s' ),
        array( '%d' )
    );
    
    if ( false === $result ) {
        error_log( '[POST EXPORT TABLE] Failed to cancel scheduled export (ID: ' . $export->id . ')' );
        return array(
            'success' => false,
            'message' => 'Failed to cancel export',
        );
    }
    
    error_log( '[POST EXPORT TABLE] Cancelled scheduled post export (ID: ' . $export->id . ')' );
    
    return array(
        'success' => true,
        'message' => 'Export cancelled successfully',
    );
}

/**
 * Get all due scheduled post exports (current time >= scheduled_time)
 * 
 * @return array Array of pending export records
 */
function fanx_get_due_scheduled_post_exports() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'fanx_scheduled_post_exports';
    $current_time = wp_date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
    
    $exports = $wpdb->get_results(
        "SELECT * FROM $table_name WHERE status = 'pending' AND scheduled_time <= '$current_time' ORDER BY scheduled_time ASC"
    );
    
    return $exports ? $exports : array();
}

/**
 * Mark a scheduled export as executed
 * 
 * @param int $export_id The export table record ID
 * @param bool $success Whether the export succeeded
 * @param string $error_message Optional error message if export failed
 * @return bool True if update successful
 */
function fanx_mark_post_export_executed( $export_id, $success = true, $error_message = '' ) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'fanx_scheduled_post_exports';
    
    $update_data = array(
        'executed_at' => wp_date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
        'status' => $success ? 'completed' : 'failed',
    );
    
    if ( $error_message ) {
        $update_data['error_message'] = $error_message;
    }
    
    $result = $wpdb->update(
        $table_name,
        $update_data,
        array( 'id' => $export_id ),
        array( '%s', '%s', '%s' ),
        array( '%d' )
    );
    
    return false !== $result;
}

/**
 * Cleanup old scheduled export records (older than 30 days)
 * 
 * @return int Number of records deleted
 */
function fanx_cleanup_old_scheduled_post_exports() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'fanx_scheduled_post_exports';
    $cutoff_date = wp_date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );
    
    $deleted = $wpdb->query( $wpdb->prepare(
        "DELETE FROM $table_name WHERE executed_at IS NOT NULL AND executed_at < %s",
        $cutoff_date
    ) );
    
    if ( $deleted > 0 ) {
        error_log( '[POST EXPORT TABLE] Cleaned up ' . $deleted . ' old scheduled post export records' );
    }
    
    return $deleted;
}

// Create table on theme activation/update
add_action( 'after_switch_theme', 'fanx_create_scheduled_post_exports_table' );

// Also try to create on admin_init as a fallback
add_action( 'admin_init', function() {
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return; // Skip on AJAX to avoid overhead
    }
    static $table_created = false;
    if ( ! $table_created ) {
        fanx_create_scheduled_post_exports_table();
        $table_created = true;
    }
}, 999 );
