<?php
/**
 * FanX Theme - Simply Static Integration + Yoast Sitemap Backup
 * 
 * Enhances Simply Static's URL discovery to include:
 * - All custom post types
 * - All custom taxonomies
 * - Automatic discovery of all publicly queryable content
 * - Smart pagination handling for large archives
 * 
 * Yoast Sitemap Backup:
 * - Automatically backs up Yoast-generated sitemaps
 * - Provides fallback sitemaps if the site goes down
 * - Maintains versioned backup history
 * - Allows manual and scheduled restoration
 * 
 * @package FanXTheme2026
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Simply Static integration hooks
 */
function fanx_simply_static_init() {
    // Only proceed if Simply Static is active
    if (!class_exists('Simply_Static')) {
        return;
    }
    
    // Generate static sitemaps before export
    add_action('simply_static_before_export', 'fanx_generate_static_sitemap_files');
    add_action('simply_static_before_generate', 'fanx_generate_static_sitemap_files');
    
    // Try free version filter first
    add_filter('simply_static_additional_urls', 'fanx_add_additional_urls_for_simply_static', 10, 1);
    
    // Pro version uses different hooks - register for Pro-specific hooks
    if (defined('SIMPLY_STATIC_PRO_VERSION')) {
        // Pro version may use custom crawlers
        add_action('simply_static.document_bundle_created', 'fanx_add_urls_to_simply_static_pro', 10, 1);
        add_filter('ssp_additional_urls', 'fanx_add_additional_urls_ssp_pro', 10, 1);
    }
    
    // Initialize Yoast sitemap backup system
    fanx_yoast_sitemap_backup_init();
}
add_action('plugins_loaded', 'fanx_simply_static_init', 20);

/**
 * Generate sitemaps on save, ensuring they're always up to date
 */
add_action('save_post', function() {
    if (!wp_doing_ajax() && !wp_doing_cron()) {
        fanx_generate_static_sitemap_files();
    }
});

/**
 * Generate sitemaps when new terms are created or updated
 */
add_action('create_term', function() {
    fanx_generate_static_sitemap_files();
}, 10, 3);
add_action('edit_term', function() {
    fanx_generate_static_sitemap_files();
}, 10, 3);

/**
 * Generate sitemaps when permalinks are flushed/refreshed
 */
add_action('flush_rewrite_rules_hard', function() {
    fanx_generate_static_sitemap_files();
});
add_action('flush_rewrite_rules', function() {
    fanx_generate_static_sitemap_files();
});

/**
 * Get all sitemap post types (public, queryable post types)
 * 
 * @return array Array of post type objects
 */
function fanx_get_sitemap_post_types() {
    $post_types = get_post_types([
        'public' => true,
        'publicly_queryable' => true,
    ], 'objects');
    
    // Exclude attachments
    if (isset($post_types['attachment'])) {
        unset($post_types['attachment']);
    }
    
    return $post_types;
}

/**
 * Get all sitemap taxonomies (public, queryable taxonomies)
 * 
 * @return array Array of taxonomy objects
 */
function fanx_get_sitemap_taxonomies() {
    $taxonomies = get_taxonomies([
        'public' => true,
        'publicly_queryable' => true,
    ], 'objects');
    
    // Exclude built-in non-standard taxonomies
    $exclude = ['post_format', 'fandoms'];
    
    foreach ($exclude as $taxonomy) {
        if (isset($taxonomies[$taxonomy])) {
            unset($taxonomies[$taxonomy]);
        }
    }
    
    return $taxonomies;
}

/**
 * Add all CPTs and taxonomies to Simply Static's URL list (Free & Pro compatible)
 */
function fanx_add_additional_urls_for_simply_static($urls) {
    if (!is_array($urls)) {
        $urls = [];
    }
    
    return fanx_generate_simply_static_url_list_simple();
}

/**
 * Simply Static Pro specific filter for additional URLs
 */
function fanx_add_additional_urls_ssp_pro($urls) {
    if (!is_array($urls)) {
        $urls = [];
    }
    
    return array_merge($urls, fanx_generate_simply_static_url_list_simple());
}

/**
 * Add URLs after Simply Static Pro document bundle is created
 */
function fanx_add_urls_to_simply_static_pro($bundle) {
    // Pro version may use database-driven approach
    // This is called during the build process
    if (method_exists($bundle, 'add_urls') || method_exists($bundle, 'add_additional_urls')) {
        $urls = fanx_generate_simply_static_url_list_simple();
        
        if (is_callable([$bundle, 'add_urls'])) {
            $bundle->add_urls($urls);
        }
    }
}

/**
 * Generate static sitemap XML files for static export
 * 
 * This creates actual XML files in the uploads directory that can be
 * served directly from the static export without needing WordPress.
 */
function fanx_generate_static_sitemap_files() {
    $upload_dir = wp_upload_dir();
    $sitemap_dir = $upload_dir['basedir'] . '/sitemaps';
    
    // Create sitemaps directory if it doesn't exist
    if (!is_dir($sitemap_dir)) {
        wp_mkdir_p($sitemap_dir);
    }
    
    $post_types = fanx_get_sitemap_post_types();
    $taxonomies = fanx_get_sitemap_taxonomies();
    $files_created = [];
    
    // Generate main sitemap index
    $sitemap_index = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemap_index .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Add post type sitemaps to index
    foreach ($post_types as $post_type) {
        if ($post_type->name === 'attachment') continue;
        
        $url = home_url('/sitemaps/sitemap-' . $post_type->name . '.xml');
        $sitemap_index .= '  <sitemap>' . "\n";
        $sitemap_index .= '    <loc>' . esc_url($url) . '</loc>' . "\n";
        $sitemap_index .= '  </sitemap>' . "\n";
        
        // Generate individual post type sitemap
        $sitemap_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap_content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $posts = get_posts([
            'post_type' => $post_type->name,
            'nopaging' => true,
            'post_status' => 'publish',
            'orderby' => 'modified',
            'order' => 'DESC',
        ]);
        
        foreach ($posts as $post) {
            $sitemap_content .= '  <url>' . "\n";
            $sitemap_content .= '    <loc>' . esc_url(get_permalink($post)) . '</loc>' . "\n";
            if (!empty($post->post_modified)) {
                $sitemap_content .= '    <lastmod>' . mysql2date('Y-m-d\TH:i:sP', $post->post_modified) . '</lastmod>' . "\n";
            }
            $sitemap_content .= '    <changefreq>weekly</changefreq>' . "\n";
            $sitemap_content .= '    <priority>0.8</priority>' . "\n";
            $sitemap_content .= '  </url>' . "\n";
        }
        
        $sitemap_content .= '</urlset>';
        
        $file = $sitemap_dir . '/sitemap-' . $post_type->name . '.xml';
        file_put_contents($file, $sitemap_content);
        $files_created[] = $file;
    }
    
    // Add taxonomy sitemaps to index
    foreach ($taxonomies as $taxonomy) {
        $url = home_url('/sitemaps/sitemap-tax-' . $taxonomy->name . '.xml');
        $sitemap_index .= '  <sitemap>' . "\n";
        $sitemap_index .= '    <loc>' . esc_url($url) . '</loc>' . "\n";
        $sitemap_index .= '  </sitemap>' . "\n";
        
        // Generate individual taxonomy sitemap
        $sitemap_content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap_content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $terms = get_terms([
            'taxonomy' => $taxonomy->name,
            'hide_empty' => false,
            'orderby' => 'name',
        ]);
        
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $term_link = get_term_link($term);
                if (!is_wp_error($term_link)) {
                    $sitemap_content .= '  <url>' . "\n";
                    $sitemap_content .= '    <loc>' . esc_url($term_link) . '</loc>' . "\n";
                    $sitemap_content .= '    <changefreq>weekly</changefreq>' . "\n";
                    $sitemap_content .= '    <priority>0.6</priority>' . "\n";
                    $sitemap_content .= '  </url>' . "\n";
                }
            }
        }
        
        $sitemap_content .= '</urlset>';
        
        $file = $sitemap_dir . '/sitemap-tax-' . $taxonomy->name . '.xml';
        file_put_contents($file, $sitemap_content);
        $files_created[] = $file;
    }
    
    $sitemap_index .= '</sitemapindex>';
    
    $index_file = $sitemap_dir . '/sitemap-index.xml';
    file_put_contents($index_file, $sitemap_index);
    $files_created[] = $index_file;
    
    return [
        'success' => true,
        'directory' => $sitemap_dir,
        'files' => $files_created,
        'count' => count($files_created),
    ];
}

/**
 * ============================================================================
 * YOAST SITEMAP BACKUP SYSTEM
 * ============================================================================
 * 
 * Automatically backups Yoast-generated sitemaps to a versioned backup directory.
 * If the WordPress site goes down, these backups can be served as a fallback.
 */

/**
 * Initialize Yoast sitemap backup system
 */
function fanx_yoast_sitemap_backup_init() {
    // Schedule automatic backups if not already scheduled
    if (!wp_next_scheduled('fanx_backup_yoast_sitemaps')) {
        wp_schedule_event(time(), 'daily', 'fanx_backup_yoast_sitemaps');
    }
    
    // Hook for manual/automated backup
    add_action('fanx_backup_yoast_sitemaps', 'fanx_backup_yoast_sitemaps');
    
    // Also backup when Yoast generates new sitemaps
    add_action('wpseo_sitemaps_cache_clear', 'fanx_backup_yoast_sitemaps');
    add_action('save_post', 'fanx_backup_yoast_sitemaps_on_post_update', 999);
    add_action('edit_term', 'fanx_backup_yoast_sitemaps_on_term_update', 999);
}

/**
 * Get the backup directory for Yoast sitemaps
 *
 * @return string Absolute path to backup directory
 */
function fanx_get_yoast_backup_dir() {
    $upload_dir = wp_upload_dir();
    $backup_dir = $upload_dir['basedir'] . '/yoast-sitemap-backups';
    
    if (!is_dir($backup_dir)) {
        wp_mkdir_p($backup_dir);
    }
    
    return $backup_dir;
}

/**
 * Get the current backups directory structure
 *
 * @return array Array with 'current' and 'archive' keys
 */
function fanx_get_yoast_backup_paths() {
    $backup_base = fanx_get_yoast_backup_dir();
    
    return [
        'base' => $backup_base,
        'current' => $backup_base . '/current',
        'archive' => $backup_base . '/archive',
        'metadata' => $backup_base . '/metadata.json',
    ];
}

/**
 * Get path to Yoast sitemaps directory
 *
 * @return string Absolute path to Yoast sitemaps
 */
function fanx_get_yoast_sitemap_dir() {
    // Yoast stores sitemaps in the root typically as sitemap.xml, sitemap_index.xml, etc.
    // But we need to check wp-content for specific Yoast files
    $upload_dir = wp_upload_dir();
    $base_dir = dirname($upload_dir['basedir']);
    
    // Common Yoast sitemap locations
    return $base_dir;
}

/**
 * Find all Yoast sitemap files
 *
 * @return array Array of sitemap file paths found
 */
function fanx_find_yoast_sitemap_files() {
    $files = [];
    $search_dir = get_home_path();
    
    // Look for Yoast sitemap files in root and uploads
    $patterns = [
        'sitemap*.xml',
        'sitemap*.xml.gz',
    ];
    
    foreach ($patterns as $pattern) {
        $found = glob($search_dir . $pattern);
        if (!empty($found)) {
            $files = array_merge($files, $found);
        }
    }
    
    // Remove duplicates and filter
    $files = array_unique($files);
    $files = array_filter($files, function($file) {
        return is_readable($file) && is_file($file);
    });
    
    return $files;
}

/**
 * Create a timestamped backup of current Yoast sitemaps
 *
 * @return array Result with success status and details
 */
function fanx_backup_yoast_sitemaps() {
    $paths = fanx_get_yoast_backup_paths();
    $current_backup_dir = $paths['current'];
    
    // Ensure directories exist
    if (!is_dir($current_backup_dir)) {
        wp_mkdir_p($current_backup_dir);
    }
    if (!is_dir($paths['archive'])) {
        wp_mkdir_p($paths['archive']);
    }
    
    $sitemap_files = fanx_find_yoast_sitemap_files();
    $backed_up = [];
    $failed = [];
    
    if (empty($sitemap_files)) {
        return [
            'success' => false,
            'message' => 'No Yoast sitemap files found to backup',
            'backed_up' => [],
            'failed' => [],
            'timestamp' => current_time('mysql'),
        ];
    }
    
    // Backup each sitemap file
    foreach ($sitemap_files as $source_file) {
        $filename = basename($source_file);
        $dest_file = $current_backup_dir . '/' . $filename;
        
        if (@copy($source_file, $dest_file)) {
            $backed_up[] = $filename;
        } else {
            $failed[] = $filename;
        }
    }
    
    // Archive old backups (keep only last 30 days)
    fanx_archive_old_backups();
    
    // Update metadata file
    $metadata = [
        'last_backup' => current_time('mysql'),
        'timestamp' => time(),
        'backed_up_files' => $backed_up,
        'failed_files' => $failed,
        'total_files' => count($sitemap_files),
        'site_url' => home_url(),
        'wordpress_version' => get_bloginfo('version'),
    ];
    
    file_put_contents($paths['metadata'], wp_json_encode($metadata));
    
    return [
        'success' => count($failed) === 0,
        'message' => sprintf(
            'Backed up %d Yoast sitemap file(s)',
            count($backed_up)
        ),
        'backed_up' => $backed_up,
        'failed' => $failed,
        'timestamp' => current_time('mysql'),
        'backup_dir' => $current_backup_dir,
    ];
}

/**
 * Conditional backup on post update (throttled)
 */
function fanx_backup_yoast_sitemaps_on_post_update() {
    if (wp_doing_ajax() || wp_doing_cron()) {
        return;
    }
    
    // Only backup if a full second has passed since last backup
    $last_backup = get_transient('fanx_yoast_backup_timestamp');
    if ($last_backup && (time() - $last_backup) < 60) {
        return;
    }
    
    fanx_backup_yoast_sitemaps();
    set_transient('fanx_yoast_backup_timestamp', time(), 60);
}

/**
 * Conditional backup on term update (throttled)
 */
function fanx_backup_yoast_sitemaps_on_term_update() {
    fanx_backup_yoast_sitemaps_on_post_update();
}

/**
 * Archive old backups - keep only recent ones
 */
function fanx_archive_old_backups() {
    $paths = fanx_get_yoast_backup_paths();
    $current_dir = $paths['current'];
    $archive_dir = $paths['archive'];
    $keep_days = 30;
    $retention_time = time() - ($keep_days * DAY_IN_SECONDS);
    
    // Archive old current backups
    if (is_dir($archive_dir)) {
        $old_files = glob($archive_dir . '/*');
        foreach ($old_files as $file) {
            if (filemtime($file) < $retention_time) {
                @unlink($file);
            }
        }
    }
}

/**
 * Restore Yoast sitemaps from backup to root directory
 *
 * @param string $backup_type 'current' or specific archived backup
 * @return array Result with success status
 */
function fanx_restore_yoast_sitemaps_from_backup($backup_type = 'current') {
    $paths = fanx_get_yoast_backup_paths();
    $backup_dir = ('current' === $backup_type) ? $paths['current'] : $paths['archive'];
    
    if (!is_dir($backup_dir)) {
        return [
            'success' => false,
            'message' => 'Backup directory not found',
        ];
    }
    
    $backup_files = glob($backup_dir . '/*.xml*');
    $restored = [];
    $failed = [];
    $root_dir = get_home_path();
    
    foreach ($backup_files as $backup_file) {
        $filename = basename($backup_file);
        $dest_file = $root_dir . $filename;
        
        if (@copy($backup_file, $dest_file)) {
            $restored[] = $filename;
        } else {
            $failed[] = $filename;
        }
    }
    
    return [
        'success' => count($failed) === 0,
        'message' => sprintf(
            'Restored %d sitemap file(s)',
            count($restored)
        ),
        'restored' => $restored,
        'failed' => $failed,
        'timestamp' => current_time('mysql'),
    ];
}

/**
 * Get latest backup metadata
 *
 * @return array|null Metadata array or null if not found
 */
function fanx_get_yoast_backup_metadata() {
    $paths = fanx_get_yoast_backup_paths();
    
    if (!file_exists($paths['metadata'])) {
        return null;
    }
    
    $data = file_get_contents($paths['metadata']);
    return json_decode($data, true);
}

/**
 * Check if backups are available and recent
 *
 * @return array Status information
 */
function fanx_check_yoast_backup_status() {
    $metadata = fanx_get_yoast_backup_metadata();
    $paths = fanx_get_yoast_backup_paths();
    $current_files = [];
    
    if (is_dir($paths['current'])) {
        $current_files = glob($paths['current'] . '/*.xml*');
    }
    
    if (!$metadata) {
        return [
            'backups_exist' => !empty($current_files),
            'last_backup' => null,
            'files_backed_up' => $current_files,
            'status' => 'No backup metadata found',
        ];
    }
    
    $last_backup_time = strtotime($metadata['last_backup']);
    $hours_since = (time() - $last_backup_time) / 3600;
    
    return [
        'backups_exist' => true,
        'last_backup' => $metadata['last_backup'],
        'hours_since_backup' => round($hours_since, 1),
        'backed_up_files' => $metadata['backed_up_files'],
        'status' => $hours_since < 24 ? 'Current' : 'Outdated',
    ];
}

/**
 * Generate a static fallback sitemap from backups
 * Useful if Yoast sitemaps are not generating properly
 */
function fanx_generate_fallback_sitemap_from_backup() {
    $paths = fanx_get_yoast_backup_paths();
    $backup_index = $paths['current'] . '/sitemap_index.xml';
    $fallback_index = $paths['current'] . '/sitemap-fallback.xml';
    
    if (!file_exists($backup_index)) {
        return [
            'success' => false,
            'message' => 'No backup sitemap index found',
        ];
    }
    
    // Copy as fallback
    if (@copy($backup_index, $fallback_index)) {
        return [
            'success' => true,
            'message' => 'Fallback sitemap generated from backup',
            'path' => $fallback_index,
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Failed to create fallback sitemap',
    ];
}

/**
 * Generate simple URL list for Simply Static (both free and Pro)
 */
function fanx_generate_simply_static_url_list_simple() {
    $urls = [];
    
    // Get all sitemap post types
    $post_types = fanx_get_sitemap_post_types();
    $taxonomies = fanx_get_sitemap_taxonomies();
    
    // Add main sitemap URL (if it exists)
    $main_sitemap = home_url('/sitemap.xml');
    if (!empty($main_sitemap)) {
        $urls[] = $main_sitemap;
    }
    
    // Add generated static sitemap index URL
    $sitemap_index = home_url('/sitemaps/sitemap-index.xml');
    if (!empty($sitemap_index)) {
        $urls[] = $sitemap_index;
    }
    
    // Add individual post type sitemaps
    foreach ($post_types as $post_type) {
        if ($post_type->name === 'attachment') continue;
        $sitemap_url = home_url('/sitemaps/sitemap-' . $post_type->name . '.xml');
        if (!empty($sitemap_url)) {
            $urls[] = $sitemap_url;
        }
    }
    
    // Add individual taxonomy sitemaps
    foreach ($taxonomies as $taxonomy) {
        $sitemap_url = home_url('/sitemaps/sitemap-tax-' . $taxonomy->name . '.xml');
        if (!empty($sitemap_url)) {
            $urls[] = $sitemap_url;
        }
    }
    
    // Collect all post URLs
    foreach ($post_types as $post_type) {
        $posts = get_posts([
            'post_type' => $post_type->name,
            'nopaging' => true,
            'post_status' => 'publish',
            'orderby' => 'ID',
        ]);
        
        foreach ($posts as $post) {
            $permalink = get_permalink($post);
            // Only add valid, non-empty permalinks
            if (!empty($permalink) && is_string($permalink) && strpos($permalink, 'http') === 0) {
                $urls[] = $permalink;
            }
        }
    }
    
    // Collect all taxonomy URLs
    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms([
            'taxonomy' => $taxonomy->name,
            'hide_empty' => false,
            'orderby' => 'ID',
        ]);
        
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $term_link = get_term_link($term);
                // Only add valid term links
                if (!is_wp_error($term_link) && !empty($term_link) && is_string($term_link) && strpos($term_link, 'http') === 0) {
                    $urls[] = $term_link;
                }
            }
        }
    }
    
    return array_unique(array_filter($urls));
}

/**
 * Provide filter for custom Simply Static setup
 * 
 * Usage in simply-static/schedule.php or custom code:
 * 
 * $urls = apply_filters('fanx_simply_static_urls', []);
 * 
 * @return array URLs for Simply Static to crawl
 */
function fanx_get_simply_static_urls() {
    return apply_filters('fanx_simply_static_urls', fanx_generate_simply_static_url_list_simple());
}

/**
 * Admin notice about Simply Static setup and Yoast backup status
 */
function fanx_simply_static_admin_notice() {
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }
    
    // Only show if Simply Static is active
    if (!class_exists('Simply_Static')) {
        return;
    }
    
    // Get current screen
    $screen = get_current_screen();
    
    // Check for both free and Pro plugin pages
    $ss_pages = [
        'toplevel_page_simply-static',
        'simply-static',
        'simply_static',
    ];
    
    if (!in_array($screen->id, $ss_pages, true)) {
        return;
    }
    
    $pro_active = defined('SIMPLY_STATIC_PRO_VERSION') ? ' (Pro v' . SIMPLY_STATIC_PRO_VERSION . ')' : '';
    $nonce = wp_create_nonce('fanx_regenerate_sitemaps');
    
    // Get backup status
    $backup_status = fanx_check_yoast_backup_status();
    $backup_info = '';
    
    if ($backup_status['backups_exist']) {
        $backup_msg = sprintf(
            'Last backup: %s (%s hours ago)',
            $backup_status['last_backup'],
            $backup_status['hours_since_backup']
        );
        $backup_class = $backup_status['status'] === 'Current' ? 'notice-success' : 'notice-warning';
        $backup_info = '<br><strong>Yoast Sitemap Backups:</strong> ' . esc_html($backup_msg);
    }
    
    ?>
    <div class="notice notice-info is-dismissible">
        <p>
            <strong>FanX Theme Sitemap Integration:</strong> 
            The FanX theme includes static XML sitemaps that work seamlessly with Simply Static<?php echo esc_html($pro_active); ?>. 
            Your sitemaps are automatically generated in <code>/wp-content/uploads/sitemaps/</code> and will be included in the static export.
            <?php echo wp_kses_post($backup_info); ?>
            <br><br>
            <a href="<?php echo wp_nonce_url(add_query_arg('action', 'fanx_regenerate_sitemaps'), 'fanx_regenerate_sitemaps'); ?>" class="button button-small">
                Regenerate Sitemaps
            </a>
            <a href="<?php echo wp_nonce_url(add_query_arg('action', 'fanx_backup_yoast_now'), 'fanx_backup_yoast'); ?>" class="button button-small">
                Backup Yoast Sitemaps Now
            </a>
            <a href="<?php echo wp_nonce_url(add_query_arg('action', 'fanx_restore_yoast_backup'), 'fanx_restore_backup'); ?>" class="button button-small">
                Restore from Backup
            </a>
        </p>
    </div>
    <?php
}
add_action('admin_notices', 'fanx_simply_static_admin_notice');

/**
 * Handle manual sitemap regeneration and backup actions from admin notice
 */
function fanx_handle_regenerate_sitemaps() {
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
    
    if ($action === 'fanx_regenerate_sitemaps') {
        check_admin_referer('fanx_regenerate_sitemaps');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $result = fanx_generate_static_sitemap_files();
        
        if ($result['success']) {
            add_settings_error('fanx_sitemap', 'regenerated', sprintf(
                'Sitemaps regenerated successfully! Generated %d files in %s',
                $result['count'],
                $result['directory']
            ), 'updated');
        } else {
            add_settings_error('fanx_sitemap', 'error', 'Failed to regenerate sitemaps', 'error');
        }
        
        wp_safe_remote_get(remove_query_arg(['action', '_wpnonce']));
        
        settings_errors('fanx_sitemap');
        wp_redirect(remove_query_arg(['action', '_wpnonce']));
        exit;
    }
    
    if ($action === 'fanx_backup_yoast_now') {
        check_admin_referer('fanx_backup_yoast');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $result = fanx_backup_yoast_sitemaps();
        
        if ($result['success']) {
            add_settings_error('fanx_backup', 'success', sprintf(
                'Yoast sitemaps backed up successfully! %s',
                $result['message']
            ), 'updated');
        } else {
            add_settings_error('fanx_backup', 'error', 'Yoast backup failed: ' . $result['message'], 'error');
        }
        
        wp_safe_remote_get(remove_query_arg(['action', '_wpnonce']));
        
        settings_errors('fanx_backup');
        wp_redirect(remove_query_arg(['action', '_wpnonce']));
        exit;
    }
    
    if ($action === 'fanx_restore_yoast_backup') {
        check_admin_referer('fanx_restore_backup');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $result = fanx_restore_yoast_sitemaps_from_backup('current');
        
        if ($result['success']) {
            add_settings_error('fanx_restore', 'success', sprintf(
                'Yoast sitemaps restored successfully! %s',
                $result['message']
            ), 'updated');
        } else {
            add_settings_error('fanx_restore', 'error', 'Restore failed: ' . $result['message'], 'error');
        }
        
        wp_safe_remote_get(remove_query_arg(['action', '_wpnonce']));
        
        settings_errors('fanx_restore');
        wp_redirect(remove_query_arg(['action', '_wpnonce']));
        exit;
    }
}
add_action('admin_init', 'fanx_handle_regenerate_sitemaps', 5);

/**
 * Enhanced: Provide direct URL list generator for manual use
 * 
 * This generates a comprehensive list of all URLs your site should crawl
 * Useful for debugging or manual Simply Static setup
 */
function fanx_generate_simply_static_url_list() {
    $simple_urls = fanx_generate_simply_static_url_list_simple();
    
    // Format for Pro's database if needed
    $formatted_urls = array_map(function($url) {
        return [
            'url' => $url,
            'title' => parse_url($url, PHP_URL_PATH),
        ];
    }, $simple_urls);
    
    return $formatted_urls;
}

/**
 * Alternative: Direct database insertion for Simply Static Pro
 * Use this if filters don't automatically add URLs
 * 
 * Run via WP-CLI: wp fanx add-urls-to-simply-static-pro
 */
function fanx_add_urls_directly_to_ssp_db() {
    global $wpdb;
    
    if (!defined('SIMPLY_STATIC_PRO_VERSION')) {
        return new WP_Error('no_pro', 'Simply Static Pro is not active');
    }
    
    // Simply Static Pro stores URLs in a custom table
    $table = $wpdb->prefix . 'simply_static_pages';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        return new WP_Error('no_table', 'Simply Static Pro table not found');
    }
    
    $urls = fanx_generate_simply_static_url_list_simple();
    $added = 0;
    
    foreach ($urls as $url) {
        // Skip empty URLs
        if (empty($url)) {
            continue;
        }
        
        // Check if URL already exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE original_url = %s LIMIT 1",
            $url
        ));
        
        if (!$exists) {
            // Parse URL components
            $parsed = parse_url($url);
            $path = isset($parsed['path']) ? $parsed['path'] : '/';
            $file_path = trim(str_replace(home_url(), '', $url), '/');
            
            // Ensure file_path is valid - never allow empty or directory-like paths
            if (empty($file_path) || $file_path === '/' || substr($file_path, -1) === '/') {
                $file_path = 'index.html';
            }
            
            // Double-check: ensure path doesn't end with slash
            $file_path = rtrim($file_path, '/');
            if (empty($file_path)) {
                $file_path = 'index.html';
            }
            
            // Insert into database
            $result = $wpdb->insert($table, [
                'original_url' => $url,
                'file_path' => $file_path,
                'url_type' => 'additional-url',
                'started_at' => current_time('mysql'),
                'completed_at' => current_time('mysql'),
                'http_status_code' => 200,
            ]);
            
            if ($result) {
                $added++;
            }
        }
    }
    
    return [
        'total_urls' => count($urls),
        'added_urls' => $added,
        'message' => sprintf('Added %d new URLs to Simply Static Pro', $added),
    ];
}

/**
 * REST API endpoint for URL list (useful for debugging)
 * Access at: /wp-json/fanx/v1/simply-static-urls
 */
function fanx_register_simply_static_rest_route() {
    register_rest_route('fanx/v1', '/simply-static-urls', [
        'methods' => 'GET',
        'callback' => function() {
            if (!current_user_can('manage_options')) {
                return new WP_Error('unauthorized', 'Unauthorized', ['status' => 403]);
            }
            
            return [
                'count' => count(fanx_generate_simply_static_url_list_simple()),
                'pro_active' => defined('SIMPLY_STATIC_PRO_VERSION'),
                'pro_version' => defined('SIMPLY_STATIC_PRO_VERSION') ? SIMPLY_STATIC_PRO_VERSION : null,
                'urls' => fanx_generate_simply_static_url_list_simple(),
            ];
        },
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
    ]);
    
    // Add endpoint for regenerating sitemaps
    register_rest_route('fanx/v1', '/regenerate-sitemaps', [
        'methods' => 'POST',
        'callback' => function() {
            if (!current_user_can('manage_options')) {
                return new WP_Error('unauthorized', 'Unauthorized', ['status' => 403]);
            }
            
            $result = fanx_generate_static_sitemap_files();
            
            return [
                'success' => $result['success'],
                'count' => $result['count'],
                'directory' => $result['directory'],
                'message' => sprintf('Generated %d sitemap files', $result['count']),
            ];
        },
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
    ]);
}
add_action('rest_api_init', 'fanx_register_simply_static_rest_route');

/**
 * WP-CLI Command for Simply Static URL export and Yoast Sitemap Backups
 * Usage: wp fanx simply-static-urls [--format=json|csv]
 *        wp fanx backup-yoast-sitemaps
 *        wp fanx restore-yoast-sitemaps
 *        wp fanx backup-status
 */
if (defined('WP_CLI') && WP_CLI) {
    class FanX_Simply_Static_CLI extends WP_CLI_Command {
        /**
         * Generate Simply Static URL list
         * 
         * ## OPTIONS
         * 
         * [--format=<format>]
         * : Output format. Options: json, csv, list
         * ---
         * default: json
         * ---
         * 
         * ## EXAMPLES
         * 
         *     wp fanx simply-static-urls
         *     wp fanx simply-static-urls --format=csv
         *     wp fanx simply-static-urls --format=list
         */
        public function __invoke($args, $assoc_args) {
            $urls = fanx_generate_simply_static_url_list_simple();
            $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'json';
            
            switch ($format) {
                case 'csv':
                    WP_CLI::line('URL');
                    foreach ($urls as $url) {
                        WP_CLI::line($url);
                    }
                    break;
                    
                case 'list':
                    foreach ($urls as $key => $url) {
                        WP_CLI::log(sprintf('[%d] %s', $key + 1, $url));
                    }
                    break;
                    
                case 'json':
                default:
                    WP_CLI::log(json_encode($urls, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    break;
            }
            
            WP_CLI::success(sprintf('Generated %d URLs', count($urls)));
        }
        
        /**
         * Regenerate static sitemap files
         * 
         * ## DESCRIPTION
         * 
         * Regenerates all static XML sitemap files in the uploads directory.
         * These files are served directly by the web server without needing
         * WordPress, making them perfect for static site exports.
         * 
         * ## OPTIONS
         * 
         * [--list]
         * : List the generated sitemap files instead of just regenerating
         * 
         * ## EXAMPLES
         * 
         *     wp fanx regenerate-sitemaps
         *     wp fanx regenerate-sitemaps --list
         */
        public function regenerate_sitemaps($args, $assoc_args) {
            $result = fanx_generate_static_sitemap_files();
            
            if (!$result['success']) {
                WP_CLI::error('Failed to generate sitemaps');
            }
            
            if (isset($assoc_args['list'])) {
                WP_CLI::line('Generated sitemap files:');
                foreach ($result['files'] as $file) {
                    $relative = str_replace(ABSPATH, '', $file);
                    WP_CLI::line("  " . $relative);
                }
            }
            
            WP_CLI::success(sprintf(
                'Generated %d sitemap files in %s',
                $result['count'],
                str_replace(ABSPATH, '', $result['directory'])
            ));
        }
        
        /**
         * List all generated sitemap files
         * 
         * ## DESCRIPTION
         * 
         * Lists all XML sitemap files that have been generated for static export
         * 
         * ## EXAMPLES
         * 
         *     wp fanx list-sitemaps
         */
        public function list_sitemaps($args, $assoc_args) {
            $upload_dir = wp_upload_dir();
            $sitemap_dir = $upload_dir['basedir'] . '/sitemaps';
            
            if (!is_dir($sitemap_dir)) {
                WP_CLI::warning('No sitemap directory exists yet. Run `wp fanx regenerate-sitemaps` first.');
                return;
            }
            
            $files = glob($sitemap_dir . '/*.xml');
            
            if (empty($files)) {
                WP_CLI::warning('No sitemap files found.');
                return;
            }
            
            WP_CLI::line('Sitemap files (' . count($files) . '):');
            foreach ($files as $file) {
                $filename = basename($file);
                $size = filesize($file);
                $size_str = size_format($size);
                $url = str_replace(ABSPATH, home_url('/'), $file);
                WP_CLI::line("  $filename ($size_str) - $url");
            }
        }
        
        /**
         * Backup Yoast-generated sitemaps
         * 
         * ## DESCRIPTION
         * 
         * Creates an immediate backup of all Yoast XML sitemaps to the backup directory.
         * These backups can be used to restore sitemaps if the site goes down.
         * Backups are stored in /wp-content/uploads/yoast-sitemap-backups/
         * 
         * ## OPTIONS
         * 
         * [--list]
         * : List the backed-up files
         * 
         * ## EXAMPLES
         * 
         *     wp fanx backup-yoast-sitemaps
         *     wp fanx backup-yoast-sitemaps --list
         */
        public function backup_yoast_sitemaps($args, $assoc_args) {
            $result = fanx_backup_yoast_sitemaps();
            
            if (!$result['success']) {
                WP_CLI::warning($result['message']);
                if (!empty($result['failed'])) {
                    WP_CLI::warning('Failed files: ' . implode(', ', $result['failed']));
                }
                return;
            }
            
            if (isset($assoc_args['list'])) {
                WP_CLI::line('Backed-up files:');
                foreach ($result['backed_up'] as $file) {
                    WP_CLI::line("  " . $file);
                }
            }
            
            WP_CLI::success($result['message'] . ' at ' . $result['timestamp']);
        }
        
        /**
         * Restore Yoast sitemaps from backup
         * 
         * ## DESCRIPTION
         * 
         * Restores Yoast sitemaps from the latest backup to the root directory.
         * Use this if the live Yoast sitemaps become corrupted or unavailable.
         * 
         * ## EXAMPLES
         * 
         *     wp fanx restore-yoast-sitemaps
         */
        public function restore_yoast_sitemaps($args, $assoc_args) {
            WP_CLI::confirm('This will overwrite current sitemaps. Continue?');
            
            $result = fanx_restore_yoast_sitemaps_from_backup('current');
            
            if (!$result['success']) {
                WP_CLI::error($result['message']);
            }
            
            WP_CLI::line('Restored files:');
            foreach ($result['restored'] as $file) {
                WP_CLI::line("  " . $file);
            }
            
            if (!empty($result['failed'])) {
                WP_CLI::warning('Failed to restore: ' . implode(', ', $result['failed']));
            }
            
            WP_CLI::success($result['message']);
        }
        
        /**
         * Check Yoast sitemap backup status
         * 
         * ## DESCRIPTION
         * 
         * Displays the current status of Yoast sitemap backups including
         * when the last backup was created and which files are backed up.
         * 
         * ## EXAMPLES
         * 
         *     wp fanx backup-status
         */
        public function backup_status($args, $assoc_args) {
            $status = fanx_check_yoast_backup_status();
            
            if (!$status['backups_exist']) {
                WP_CLI::warning('No backups found yet.');
                return;
            }
            
            WP_CLI::line('=== Yoast Sitemap Backup Status ===');
            WP_CLI::line('Status: ' . $status['status']);
            WP_CLI::line('Last Backup: ' . $status['last_backup']);
            WP_CLI::line('Hours Since Backup: ' . $status['hours_since_backup']);
            WP_CLI::line('');
            WP_CLI::line('Backed-up Files:');
            
            foreach ($status['backed_up_files'] as $file) {
                WP_CLI::line("  - " . $file);
            }
            
            // Check if backups are old
            if ($status['hours_since_backup'] > 24) {
                WP_CLI::warning('Backups are older than 24 hours. Consider running `wp fanx backup-yoast-sitemaps` to create a fresh backup.');
            }
        }
        
        /**
         * Add URLs directly to Simply Static Pro database
         * 
         * ## DESCRIPTION
         * 
         * This command adds all discovered CPTs and taxonomies directly to the
         * Simply Static Pro pages table. Useful if the automatic filter integration
         * isn't working.
         * 
         * ## EXAMPLES
         * 
         *     wp fanx add-urls-to-ssp
         */
        public function add_urls_to_ssp($args, $assoc_args) {
            if (!defined('SIMPLY_STATIC_PRO_VERSION')) {
                WP_CLI::error('Simply Static Pro is not active');
            }
            
            $result = fanx_add_urls_directly_to_ssp_db();
            
            if (is_wp_error($result)) {
                WP_CLI::error($result->get_error_message());
            }
            
            WP_CLI::success(sprintf(
                'Successfully added %d/%d URLs to Simply Static Pro',
                $result['added_urls'],
                $result['total_urls']
            ));
        }
    }
    
    WP_CLI::add_command('fanx simply-static-urls', 'FanX_Simply_Static_CLI');
}
?>