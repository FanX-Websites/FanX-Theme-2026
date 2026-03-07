<?php
/**
 * Pre-Export Health & Conflict Checker
 * 
 * Checks for plugin conflicts and issues before exporting static site
 * Helps prevent broken static site exports
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Convert memory string (e.g., "256M") to bytes
 */
function fanx_convert_memory_to_bytes( $value ) {
    // Handle null or non-string values
    if ( empty( $value ) || ! is_string( $value ) ) {
        return 0;
    }
    
    $value = trim( $value );
    if ( is_numeric( $value ) ) {
        return intval( $value );
    }
    
    $unit = strtoupper( substr( $value, -1 ) );
    $bytes = intval( $value );
    
    switch ( $unit ) {
        case 'G':
            $bytes *= 1024 * 1024 * 1024;
            break;
        case 'M':
            $bytes *= 1024 * 1024;
            break;
        case 'K':
            $bytes *= 1024;
            break;
    }
    
    return $bytes;
}

/**
 * Check Yoast SEO status and configuration
 * Known to potentially break static exports if misconfigured
 */
function fanx_check_yoast_status() {
    $errors = array();
    $warnings = array();
    $info = array();
    
    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $yoast_active = is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' );
    
    if ( ! $yoast_active ) {
        $info[] = 'Yoast SEO is not active.';
        return compact( 'errors', 'warnings', 'info' );
    }
    
    // === Yoast is active ===
    $yoast_options = get_option( 'wpseo' );
    $yoast_premium = function_exists( 'YoastSEO' ) && defined( 'WPSEO_PREMIUM_FILE' );
    
    if ( $yoast_premium ) {
        $info[] = 'Yoast SEO Premium is active.';
    } else {
        $info[] = 'Yoast SEO (Free) is active.';
    }
    
    // Check for redirect integrations
    if ( isset( $yoast_options['enable_xml_sitemap'] ) && $yoast_options['enable_xml_sitemap'] ) {
        $info[] = 'Yoast XML sitemap generation is enabled.';
    }
    
    // Check if Yoast is managing redirects
    if ( is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) ) {
        $redirect_options = get_option( 'wpseo-redirect-manager' );
        if ( ! empty( $redirect_options ) ) {
            $warnings[] = '⚠️ Yoast redirect rules detected. These may affect static export. Review before exporting.';
        }
    }
    
    // Check Yoast database tables
    global $wpdb;
    $yoast_tables = $wpdb->get_results( "SHOW TABLES LIKE '" . $wpdb->prefix . "yoast%'" );
    if ( empty( $yoast_tables ) ) {
        $warnings[] = 'Yoast database tables not found. Plugin may not be fully initialized.';
    } else {
        $info[] = 'Yoast database tables are present.';
    }
    
    // Check for conflicting Yoast settings
    if ( isset( $yoast_options['disable_advanced_settings'] ) && $yoast_options['disable_advanced_settings'] ) {
        $info[] = 'Yoast advanced settings are disabled (standard config).';
    }
    
    // Check for link element in head
    if ( ! isset( $yoast_options['hide_rsd_link'] ) || $yoast_options['hide_rsd_link'] ) {
        $info[] = 'Yoast is managing link elements.';
    }
    
    return compact( 'errors', 'warnings', 'info' );
}

/**
 * Perform comprehensive pre-export checks
 */
function fanx_pre_export_health_check() {
    $issues = array();
    $warnings = array();
    $info = array();
    
    // === PLUGIN CONFLICT CHECKS ===
    $conflicting_plugins = array(
        'redirection/redirection.php' => 'Redirection Plugin',
        'health-check/health-check.php' => 'Health Check & Troubleshooting',
        'wordfence/wordfence.php' => 'Wordfence Security',
        'jetpack/jetpack.php' => 'Jetpack',
        'akismet/akismet.php' => 'Akismet',
        'wp-super-cache/wp-cache.php' => 'WP Super Cache',
        'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
    );
    
    $active_plugins = get_option( 'active_plugins', array() );
    $active_conflicting = array();
    
    foreach ( $conflicting_plugins as $plugin_file => $plugin_name ) {
        if ( in_array( $plugin_file, $active_plugins ) ) {
            $active_conflicting[] = $plugin_name;
        }
    }
    
    if ( ! empty( $active_conflicting ) ) {
        $warnings[] = 'Potentially conflicting plugins active: ' . implode( ', ', $active_conflicting );
    }
    
    // === YOAST SEO STATUS CHECK ===
    $yoast_status = fanx_check_yoast_status();
    if ( ! empty( $yoast_status['errors'] ) ) {
        $issues = array_merge( $issues, $yoast_status['errors'] );
    }
    if ( ! empty( $yoast_status['warnings'] ) ) {
        $warnings = array_merge( $warnings, $yoast_status['warnings'] );
    }
    if ( ! empty( $yoast_status['info'] ) ) {
        $info = array_merge( $info, $yoast_status['info'] );
    }
    
    // === DEBUG MODE CHECK ===
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            $log_file = WP_CONTENT_DIR . '/debug.log';
            if ( file_exists( $log_file ) ) {
                $file_size = filesize( $log_file );
                if ( $file_size > 1048576 ) { // > 1MB
                    $warnings[] = 'Debug log is large (' . size_format( $file_size ) . '). Consider clearing it.';
                }
            }
        }
        $warnings[] = 'WP_DEBUG is enabled. Consider disabling before export.';
    }
    
    // === SIMPLY STATIC CHECK ===
    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    if ( ! is_plugin_active( 'simply-static/simply-static.php' ) && 
         ! is_plugin_active( 'simply-static-pro/simply-static.php' ) ) {
        $issues[] = 'Simply Static plugin is not active. Cannot export.';
    } else {
        $info[] = 'Simply Static plugin is active and ready.';
    }
    
    // === FILESYSTEM CHECKS ===
    if ( ! wp_is_writable( WP_CONTENT_DIR ) ) {
        $issues[] = 'wp-content directory is not writable. Export will fail.';
    } else {
        $info[] = 'wp-content directory is writable.';
    }
    
    $export_dir = get_option( 'simply-static-local-dir' );
    if ( $export_dir && ! wp_is_writable( dirname( $export_dir ) ) ) {
        $issues[] = 'Static export directory is not writable: ' . $export_dir;
    }
    
    // === POSTS & CONTENT CHECK ===
    $post_count = wp_count_posts();
    if ( 0 === $post_count->publish + $post_count->draft ) {
        $warnings[] = 'No published or draft posts found. Static export may be empty.';
    } else {
        $info[] = 'Published posts found: ' . $post_count->publish;
    }
    
    // === REWRITE RULES CHECK ===
    $wp_rewrite = new WP_Rewrite();
    if ( empty( $wp_rewrite->permalink_structure ) ) {
        $warnings[] = 'Permalinks are set to default. Static export may have broken links.';
    } else {
        $info[] = 'Permalink structure: ' . $wp_rewrite->permalink_structure;
    }
    
    // === THEME CHECK ===
    $active_theme = wp_get_theme();
    if ( 'FanXTheme2026' === $active_theme->get_stylesheet() || 'FanXTheme2026' === $active_theme->get_template() ) {
        $info[] = 'FanXTheme2026 is active and compatible.';
    } else {
        $warnings[] = 'Active theme is: ' . $active_theme->get_name() . '. May have compatibility issues.';
    }
    
    // === MEMORY & TIMEOUT CHECK ===
    $memory_limit = WP_MEMORY_LIMIT;
    $memory_bytes = fanx_convert_memory_to_bytes( $memory_limit );
    $max_memory = 1024 * 1024 * 1024; // 1024M
    
    // Only warn if memory is low AND not already at max
    if ( $memory_bytes < 268435456 && $memory_bytes !== $max_memory ) { // < 256MB and not at 1024M
        $warnings[] = 'PHP memory limit is low: ' . $memory_limit . '. Increase to at least 256M for large exports.';
    }
    
    $timeout = intval( ini_get( 'max_execution_time' ) );
    
    // Only warn if timeout is low AND not unlimited (0)
    if ( $timeout > 0 && $timeout < 300 ) { // > 0 means limited, < 300 is low, 0 means unlimited
        $warnings[] = 'PHP timeout is low: ' . $timeout . 's. Increase to at least 300s or set to unlimited.';
    }
    
    return array(
        'errors'   => $issues,
        'warnings' => $warnings,
        'info'     => $info,
        'passed'   => empty( $issues ),
    );
}

/**
 * Get pre-export check results with HTML formatting
 */
function fanx_get_export_check_html() {
    $results = fanx_pre_export_health_check();
    $html = '';
    
    if ( ! $results['passed'] ) {
        $html .= '<div style="background: #fee; border: 1px solid #f00; padding: 10px; margin: 10px 0; border-radius: 3px;">';
        $html .= '<strong style="color: #d32f2f;">⚠️ Critical Issues Found:</strong><ul style="margin: 5px 0;">';
        foreach ( $results['errors'] as $error ) {
            $html .= '<li style="color: #d32f2f;"><strong>' . esc_html( $error ) . '</strong></li>';
        }
        $html .= '</ul></div>';
    }
    
    if ( ! empty( $results['warnings'] ) ) {
        $html .= '<div style="background: #ffeaa7; border: 1px solid #f39c12; padding: 10px; margin: 10px 0; border-radius: 3px;">';
        $html .= '<strong style="color: #f39c12;">⚡ Warnings:</strong><ul style="margin: 5px 0;">';
        foreach ( $results['warnings'] as $warning ) {
            $html .= '<li style="color: #e67e22;">' . esc_html( $warning ) . '</li>';
        }
        $html .= '</ul></div>';
    }
    
    if ( ! empty( $results['info'] ) ) {
        $html .= '<div style="background: #e8f5e9; border: 1px solid #27ae60; padding: 10px; margin: 10px 0; border-radius: 3px;">';
        $html .= '<strong style="color: #27ae60;">✓ Status:</strong><ul style="margin: 5px 0;">';
        foreach ( $results['info'] as $info ) {
            $html .= '<li style="color: #27ae60;">' . esc_html( $info ) . '</li>';
        }
        $html .= '</ul></div>';
    }
    
    return $html;
}

/**
 * Get status badge for quick visual feedback
 */
function fanx_get_export_status_badge() {
    $results = fanx_pre_export_health_check();
    
    if ( ! $results['passed'] ) {
        return '<span style="background: #d32f2f; color: white; padding: 3px 10px; border-radius: 3px; font-weight: bold;">❌ NOT READY</span>';
    } elseif ( ! empty( $results['warnings'] ) ) {
        return '<span style="background: #f39c12; color: white; padding: 3px 10px; border-radius: 3px; font-weight: bold;">⚠️ CAUTION</span>';
    } else {
        return '<span style="background: #27ae60; color: white; padding: 3px 10px; border-radius: 3px; font-weight: bold;">✓ READY</span>';
    }
}

/**
 * Log pre-export check results to debug log
 */
function fanx_log_pre_export_check() {
    $results = fanx_pre_export_health_check();
    
    error_log( '=== PRE-EXPORT HEALTH CHECK ===' );
    error_log( 'Status: ' . ( $results['passed'] ? 'PASSED' : 'FAILED' ) );
    
    if ( ! empty( $results['errors'] ) ) {
        error_log( 'Errors: ' . implode( ' | ', $results['errors'] ) );
    }
    
    if ( ! empty( $results['warnings'] ) ) {
        error_log( 'Warnings: ' . implode( ' | ', $results['warnings'] ) );
    }
    
    error_log( '=== END PRE-EXPORT CHECK ===' );
}
