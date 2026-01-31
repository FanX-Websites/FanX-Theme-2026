<?php 
/** Plugin Name: Simply Static - Schedule Export
 * @package FanXTheme2026
 *  
 * 
 * Notes: 
 *  - Connects to ACF Field Group: Schedule Exports 
 * 
 */

add_action('acf/save_post', 'schedule_simplystatic_export', 20);

function schedule_simplystatic_export($post_id) {
    // Skip if autosave or revision
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    
    // Check if schedule button was clicked
    if (isset($_POST['acf']['schedule_export'])) {
        $scheduled_time = get_field('export_datetime', $post_id);
        
        if ($scheduled_time) {
            $timestamp = strtotime($scheduled_time);
            $hook_name = 'run_simplystatic_export_' . $post_id;
            
            // Clear any existing schedule
            wp_clear_scheduled_hook($hook_name);
            
            // Schedule if time is in future
            if ($timestamp > time()) {
                wp_schedule_single_event($timestamp, $hook_name, array($post_id));
            }
        }
    }
    
    // Check if cancel button was clicked
    if (isset($_POST['acf']['cancel_export'])) {
        $hook_name = 'run_simplystatic_export_' . $post_id;
        wp_clear_scheduled_hook($hook_name);
        update_field('export_datetime', '', $post_id);
    }
}

add_action('run_simplystatic_export_', 'trigger_simplystatic_export', 10, 1);

function trigger_simplystatic_export($post_id) {
    do_action('simplystatic.archive_creation_job');
    update_field('export_datetime', '', $post_id);
}