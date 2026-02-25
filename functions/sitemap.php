<?php
/**
 * FanX Theme - Basic XML Sitemap Test
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register rewrite rule
function fanx_sitemap_rewrite_rules() {
    add_rewrite_rule('^sitemap\.xml$', 'index.php?fanx_sitemap=1', 'top');
}
add_action('init', 'fanx_sitemap_rewrite_rules');

// Register query variable
function fanx_sitemap_query_vars($vars) {
    $vars[] = 'fanx_sitemap';
    return $vars;
}
add_filter('query_vars', 'fanx_sitemap_query_vars');

// Handle sitemap requests
function fanx_sitemap_template_include($template) {
    if (get_query_var('fanx_sitemap')) {
        header('Content-Type: application/xml; charset=UTF-8');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        echo "\n";
        
        // Add homepage
        echo '  <url>';
        echo "\n";
        echo '    <loc>' . esc_url(home_url('/')) . '</loc>';
        echo "\n";
        echo '  </url>';
        echo "\n";
        
        // Get all public post types
        $post_types = get_post_types(array('public' => true), 'names');
        
        // Get all published posts across all post types
        foreach ($post_types as $post_type) {
            if ($post_type === 'attachment') continue;
            
            $posts = get_posts(array(
                'post_type' => $post_type,
                'nopaging' => true,
                'post_status' => 'publish',
                'orderby' => 'ID',
            ));
            
            foreach ($posts as $post) {
                echo '  <url>';
                echo "\n";
                echo '    <loc>' . esc_url(get_permalink($post)) . '</loc>';
                echo "\n";
                echo '  </url>';
                echo "\n";
            }
        }
        
        // Add default WordPress categories
        $categories = get_terms(array(
            'taxonomy' => 'category',
            'hide_empty' => false, // Include empty categories
        ));
        
        if (!is_wp_error($categories) && !empty($categories)) {
            foreach ($categories as $category) {
                echo '  <url>';
                echo "\n";
                echo '    <loc>' . esc_url(get_category_link($category->term_id)) . '</loc>';
                echo "\n";
                echo '  </url>';
                echo "\n";
            }
        }
        
        // Add tags
        $tags = get_terms(array(
            'taxonomy' => 'post_tag',
            'hide_empty' => false,
        ));
        
        if (!is_wp_error($tags) && !empty($tags)) {
            foreach ($tags as $tag) {
                echo '  <url>';
                echo "\n";
                echo '    <loc>' . esc_url(get_tag_link($tag->term_id)) . '</loc>';
                echo "\n";
                echo '  </url>';
                echo "\n";
            }
        }
        
        // Add custom taxonomies
        $taxonomies = get_taxonomies(array('public' => true), 'names');
        
        //Excluded Taxonomies from Custom Add
        foreach ($taxonomies as $taxonomy) {
            if (in_array($taxonomy, array('post_format', 'category', 'post_tag'), true)) {
                continue;
            }
            
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ));
            
            if (!is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $term) {
                    echo '  <url>';
                    echo "\n";
                    echo '    <loc>' . esc_url(get_term_link($term, $taxonomy)) . '</loc>';
                    echo "\n";
                    echo '  </url>';
                    echo "\n";
                }
            }
        }
        
        echo '</urlset>';
        exit;
    }
    return $template;
}
add_filter('template_include', 'fanx_sitemap_template_include');

// Flush rewrite rules on theme activation
function fanx_sitemap_flush_rewrite() {
    fanx_sitemap_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'fanx_sitemap_flush_rewrite');
