<?php
/**
 * Activity Feed Module
 * 
 * Loads all activity logging functionality including:
 * - Core activity logger with custom database table
 * - Dashboard widget display
 * - Admin page with filters and pagination
 */

// Only load in admin context
if (is_admin()) {
    // Core activity logging functionality
    require_once dirname(__FILE__) . '/activity-log.php';
    
    // Dashboard widget
    require_once dirname(__FILE__) . '/dashboard-widget.php';
    
    // Admin page with detailed logs
    require_once dirname(__FILE__) . '/admin-page.php';
}
