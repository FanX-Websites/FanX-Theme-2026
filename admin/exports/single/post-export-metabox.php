<?php
/**
 * Post Export Metabox - Single Post/Page Export Scheduling
 * 
 * Renders a metabox on post/page edit screens allowing users to:
 * - Schedule a single post/page export at a specific time
 * - View scheduled export status
 * - Cancel scheduled exports
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the post export metabox on all public post types and taxonomies
 */
function fanx_register_post_export_metabox() {
    // Get all public post types
    $post_types = get_post_types( array( 'public' => true ), 'objects' );
    
    foreach ( $post_types as $post_type ) {
        add_meta_box(
            'fanx_post_export_metabox',
            __( 'Schedule Export', 'fanx-theme' ),
            'fanx_render_post_export_metabox',
            $post_type->name,
            'side',
            'high'
        );
    }
    
    // Also register for taxonomies (categories, tags, custom taxonomies)
    $taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
    
    foreach ( $taxonomies as $taxonomy ) {
        add_meta_box(
            'fanx_post_export_metabox',
            __( 'Schedule Export', 'fanx-theme' ),
            'fanx_render_post_export_metabox',
            $taxonomy->name,
            'side',
            'high'
        );
    }
}
add_action( 'add_meta_boxes', 'fanx_register_post_export_metabox' );

/**
 * Render the post export metabox
 * 
 * @param WP_Post $post The current post object
 */
function fanx_render_post_export_metabox( $post ) {
    // Enqueue scripts/styles for post export
    wp_enqueue_script( 'fanx-post-export-script' );
    wp_enqueue_style( 'fanx-post-export-styles' );
    
    // Get current post/term ID
    $post_id = is_object( $post ) ? $post->ID : $post;
    $scheduled_time = fanx_get_post_export_scheduled_time( $post_id );
    
    echo '<div class="fanx-post-export-metabox">';
    
    // Status section
    if ( $scheduled_time ) {
        echo '<div class="fanx-post-export-status scheduled">';
        echo '<strong style="color: #1976d2;">📅 Export Scheduled</strong><br>';
        echo 'Scheduled for: ' . esc_html( wp_date( 'M d, Y g:i A', $scheduled_time ) ) . '<br>';
        echo '</div>';
        
        // Cancel button
        echo '<button class="button button-secondary fanx-cancel-post-export" data-post-id="' . absint( $post_id ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'fanx_schedule_post_export' ) ) . '" style="width: 100%; margin-top: 10px;">';
        echo '✕ Cancel Export';
        echo '</button>';
    } else {
        echo '<div class="fanx-post-export-status not-scheduled">';
        echo '<strong>No export scheduled</strong>';
        echo '</div>';
        
        // Schedule form
        echo '<div style="margin-top: 15px;">';
        echo '<label for="fanx-post-export-datetime" style="display: block; margin-bottom: 8px; font-weight: bold; font-size: 12px;">Schedule For:</label>';
        echo '<input type="datetime-local" id="fanx-post-export-datetime" class="fanx-post-export-datetime" data-post-id="' . absint( $post_id ) . '" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; font-size: 12px;" value="' . esc_attr( wp_date( 'Y-m-d\TH:i', time() + 3600 ) ) . '">';
        echo '<small style="display: block; color: #666; margin-top: 4px; font-size: 11px;">Defaults to 1 hour from now</small>';
        echo '</div>';
        
        // Schedule button
        echo '<button class="button button-primary fanx-schedule-post-export" data-post-id="' . absint( $post_id ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'fanx_schedule_post_export' ) ) . '" style="width: 100%; margin-top: 10px;">';
        echo '⏱️ Schedule Export';
        echo '</button>';
    }
    
    // Message area
    echo '<div id="fanx-post-export-message" class="fanx-post-export-message" style="margin-top: 10px;"></div>';
    
    echo '</div>';
}

/**
 * Get scheduled export time for a post
 * 
 * Queries the custom database table instead of wp-cron
 * 
 * @param int $post_id The post ID
 * @return int|false Unix timestamp if scheduled, false otherwise
 */
function fanx_get_post_export_scheduled_time( $post_id ) {
    // Check custom table for pending scheduled export
    $export = fanx_get_next_scheduled_post_export( $post_id );
    
    if ( $export ) {
        // Convert database datetime to Unix timestamp
        return strtotime( $export->scheduled_time );
    }
    
    return false;
}

/**
 * Enqueue post export metabox styles and scripts
 */
function fanx_enqueue_post_export_metabox_assets() {
    $screen = get_current_screen();
    if ( ! $screen ) {
        return;
    }
    
    // Load on all post type edit screens and taxonomy edit screens
    $is_post_edit = $screen->base === 'post';
    $is_term_edit = $screen->base === 'term';
    
    if ( ! $is_post_edit && ! $is_term_edit ) {
        return;
    }
    
    // Register and enqueue styles
    wp_register_style(
        'fanx-post-export-styles',
        get_template_directory_uri() . '/admin/exports/single/post-export.css',
        array(),
        '1.0.0'
    );
    wp_enqueue_style( 'fanx-post-export-styles' );
    
    // Register and enqueue scripts
    wp_register_script(
        'fanx-post-export-script',
        get_template_directory_uri() . '/admin/exports/single/post-export.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );
    wp_enqueue_script( 'fanx-post-export-script' );
    
    // Localize script with AJAX data
    wp_localize_script( 'fanx-post-export-script', 'fanxPostExport', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'fanx_enqueue_post_export_metabox_assets' );
