/** 
For Theme Management 
*/

<?php 

if ( file_exists( get_template_directory() . '/admin/theme-update-checker/plugin-update-checker.php' ) ) {
    require get_template_directory() . '/admin/theme-update-checker/plugin-update-checker.php';
    $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
        'https://github.com/FanX-Websites/FanX-Theme-2026',
        get_template_directory() . '/style.css',
        'fanx-theme'
    );
}
