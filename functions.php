<?php
// Exit if accessed directly
	if (!defined('ABSPATH')) {
		exit;
	}

// === TIMEZONE FIX ===
// Force PHP to use WordPress's configured timezone instead of UTC
// This fixes strtotime() and date functions to work with the correct timezone
// See: https://core.trac.wordpress.org/ticket/24730
if ( function_exists( 'get_option' ) ) {
	$wp_timezone = get_option( 'timezone_string' );
	if ( $wp_timezone ) {
		date_default_timezone_set( $wp_timezone );
	} elseif ( get_option( 'gmt_offset' ) ) {
		// Fallback to GMT offset if timezone_string not set
		$gmt_offset = get_option( 'gmt_offset' );
		$offset_hours = intval( $gmt_offset );
		$offset_mins = ( $gmt_offset - $offset_hours ) * 60;
		$offset_string = sprintf( '%+03d:%02d', $offset_hours, abs( $offset_mins ) );
		// Create timezone string from offset
		$timezone = timezone_name_from_abbr( '', intval( $gmt_offset ) * 3600, 0 );
		if ( $timezone ) {
			date_default_timezone_set( $timezone );
		}
	}
}

// === SIMPLY STATIC WIDGET TIMEZONE FIX ===
// The Simply Static widget uses strtotime() on stored UTC timestamps
// date_default_timezone_set() causes strtotime() to interpret them in local timezone (wrong)
// Fix: Hook into dashboard rendering and temporarily revert timezone for the widget
function fanx_simply_static_widget_fix() {
	// Check if we're on the dashboard and Simply Static is active
	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( $screen && 'dashboard' === $screen->base && class_exists( '\Simply_Static\Admin_Dashboard_Widget' ) ) {
			// On dashboard page, temporarily reset timezone while widgets render
			$current_tz = ini_get( 'date.timezone' );
			if ( $current_tz && 'UTC' !== $current_tz ) {
				ini_set( 'date.timezone', 'UTC' );
				// Restore after dashboard loads
				add_action( 'admin_footer', function() use ( $current_tz ) {
					ini_set( 'date.timezone', $current_tz );
				}, 1 );
			}
		}
	}
}
add_action( 'admin_init', 'fanx_simply_static_widget_fix' );

//--- Theme Support -->
	function fanx_theme_setup() {	
		add_theme_support('post-thumbnails'); //Thumbnails	
		add_theme_support('sticky-posts'); //Sticky Posts
	
	//--- Menu Registration -->
		register_nav_menus(array( 
			'footer' => __('Footer Menu', 'fanx-theme'), //Footer Menu
			'about' => __('About Menu', 'fanx-theme'), //About Menu
			'guests' => __('Guests Menu', 'fanx-theme'), //Guests Menu
			'features' => __('Features/Activities Menu', 'fanx-theme'), //Features Menu
			'products' => __('Purchase Menu', 'fanx-theme'), //Products Menu
			'experiences' => __('eXperiences Menu', 'fanx-theme'), //Experiences Menu
			'participate' => __('Participate Menu', 'fanx-theme'), //Participate Menu
			'updates' => __('Updates Menu', 'fanx-theme'), //Updates Menu
			'hoteltravel' => __('Hotel/Travel Menu', 'fanx-theme'), //Hotel/Travel Menu
			'socket' => __('Socket Menu', 'fanx-theme'), //Socket Menu
			'event-info' => __('Event Info Menu', 'fanx-theme'), //Event Info Menu
		
		)); //Menu Registration End <---
	}//Add Support End <---

	add_action('after_setup_theme', 'fanx_theme_setup'); //FanX Theme Setup

//--- END Theme Support Setup <--

//Theme Styles Support 
	//Enqueue Styles and Scripts --->
		function fanx_enqueue_assets() {

		//Main Stylesheet
			wp_enqueue_style('fanx-style', get_template_directory_uri() . '/style.css', array(), wp_get_theme()->get('Version')); //Stylesheet 

		//Branding Stylesheets 
			wp_enqueue_style('fanx-branding', get_template_directory_uri() . '/branding/styles/fanx.css', array(), wp_get_theme()->get('Version')); //Branding CSS - 2026

			wp_enqueue_style('template-parts', get_template_directory_uri() . '/template-parts/template-parts.css', array(), wp_get_theme()->get('Version')); //Template Parts CSS - 2026		
		}
		add_action('wp_enqueue_scripts', 'fanx_enqueue_assets');

	//Front end JavaScript
		function fanx_enqueue_scripts(){
			wp_enqueue_script('fanx-scripts', get_template_directory_uri() . '/js/scripts.js', array(), '1.0.2', true);
		}
		add_action('wp_enqueue_scripts', 'fanx_enqueue_scripts');

	//ADMIN JavaScript
		function fanx_enqueue_admin_scripts() {
			wp_enqueue_script('fanx-admin-scripts', get_template_directory_uri() . '/js/admin-scripts.js', array(), '1.0.0', true);
		}
		add_action('admin_enqueue_scripts', 'fanx_enqueue_admin_scripts');
	
	//End Enqueue Styles and Scripts <---

	//Font Awesome ---->

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
});

	//Color Palette Support - Dev Tools Form (ACF)
	add_action( 'wp_head', function() {
    ?>
    <style>
    :root {
	/** Dark Mode */
        --color_drk: <?php echo esc_attr( get_field( 'color_drk', 'option' ) ); ?>;
        --color_base: <?php echo esc_attr( get_field( 'color_base', 'option' ) ); ?>;
        --color_prim: <?php echo esc_attr( get_field( 'color_prim', 'option' ) ); ?>;
        --color_acc: <?php echo esc_attr( get_field( 'color_acc', 'option' ) ); ?>;

	/** Light Mode */
        --color_lght: <?php echo esc_attr( get_field( 'color_lght', 'option' ) ); ?>;
        --color_base_lght: <?php echo esc_attr( get_field( 'color_base_lght', 'option' ) ); ?>;
        --color_prim_lght: <?php echo esc_attr( get_field( 'color_prim_lght', 'option' ) ); ?>;
        --color_acc_lght: <?php echo esc_attr( get_field( 'color_acc_lght', 'option' ) ); ?>;
	
	/** Light Mode /Contrast */
        --color_brht: <?php echo esc_attr( get_field( 'color_brht', 'option' ) ); ?>;
        --color_base_brht: <?php echo esc_attr( get_field( 'color_base_brht', 'option' ) ); ?>;
        --color_prim_brht: <?php echo esc_attr( get_field( 'color_prim_brht', 'option' ) ); ?>;
        --color_acc_brht: <?php echo esc_attr( get_field( 'color_acc_brht', 'option' ) ); ?>;
    
	/**Text */
		--color_fnt_wht: <?php echo esc_attr( get_field( 'color_fnt_wht', 'option' ) ); ?>;
        --color_fnt_blk: <?php echo esc_attr( get_field( 'color_fnt_blk', 'option' ) ); ?>;
	
	
	}
    </style>

    <?php
} ); //END Color Palette <--- 

//Include Additional Theme Files --->
	$theme_files = [
		//Functions - Front End
			'functions/shortcode.php',
			'functions/tag-cats.php',
			'functions/sitemap.php',
		//Plugins - Third Party	
			'acf/tweaks.php',
			'acf/acf-admin-columns.php', //ACF Admin Columns Manager
			'acf/acf-admin-quick-edits.php', //ACF Admin Quick Edits
			'seo/yoast-tweaks.php',
			'simply-static/system-level-exports.php', //System-level scheduled exports & backups
			'simply-static/schedule.php',
			'simply-static/enqueue.php',
			'simply-static/sitemap-integration.php',
		//Admin Area 	
			'admin/white-label.php', //Admin White Labeling
			'admin/customadmin.php', //Custom Admin Features	
			'admin/custommenu.php', //Menu Customizations & Support
			'admin/post-rules.php', //Post Editor Locking System
			'admin/backup-feed/static-backups.php', //Static Backups Feed
			'admin/backup-feed/wordpress-backups.php', //WordPress Backups Feed
			'admin/backup-feed/github-pushes.php', //GitHub Pushes Feed
			'admin/backup-feed/backup-widget.php', //Consolidated Backup & Repository Widget with Tabs
			'admin/debug-feed/site-debug-log.php', //Debug Log Dashboard Widget
			'admin/exports/pre-export-checker.php', //Pre-Export Health Check
			'admin/exports/export-health-widget.php', //Export Manager Dashboard Widget
			'admin/exports/export-scheduler.php', //Export Scheduler - One-Time Exports via wp-cron
		'admin/exports/single/post-export-table.php', //Post Export Database Table Manager
		'admin/exports/single/post-export-metabox.php', //Post Export Metabox
		'admin/exports/single/post-export-scheduler.php', //Post Export Scheduler - Single Post Exports via Custom Table
			'admin/activity-feed/index.php', //Activity Logging & Dashboard Widget
	];
//END Theme Files <---

	foreach ($theme_files as $file) {
		$filepath = get_parent_theme_file_path($file);
		if (file_exists($filepath)) {
			include $filepath;
		}
	}

//PROTECT THE THINGS --->
	
	/*** Block User Enumeration */
	function df_block_user_enum_attempt() {
		// Only run on frontend
		if ( is_admin() ) {
			return;
		}
		
		// Block ?author=123 style enumeration attempts
		if ( isset( $_GET['author'] ) && is_numeric( $_GET['author'] ) ) {
			wp_safe_redirect( home_url(), 301 );
			exit;
		}
		
		// Block /author/username/ archive pages
		if ( is_author() ) {
			wp_safe_redirect( home_url(), 301 );
			exit;
		}
	}
	add_action( 'template_redirect', 'df_block_user_enum_attempt' );

//End protect the things <---


//REMOVE THE THINGS --->
	/* ---Remove Gutenberg Block Library CSS from loading on the frontend -->*/
		function smartwp_remove_wp_block_library_css(){
			wp_dequeue_style( 'wp-block-library' ); 
			wp_dequeue_style( 'wp-block-library-theme' ); 
			wp_dequeue_style( 'wc-block-style' ); // Remove WooCommerce block CSS
		}
		add_action( 'wp_enqueue_scripts', 'smartwp_remove_wp_block_library_css', 100 );

		add_filter( 'use_block_editor_for_post_type', '__return_false', 10 );
		
	/** End Gutenberg - */	


//FLUSH THE THINGS --->
	//remove_action('shutdown', 'wp_ob_end_flush_all', 1);  //Flush error
	//flush_rewrite_rules(); //Flush Rules
// Prevent non-production sites from being indexed - DO NOT SYNC TO LIVE
if ( 'production' !== wp_get_environment_type() ) {
    add_filter( 'wpseo_robots', function() {
        return 'noindex, nofollow';
    });
    add_action( 'wp_head', function() {
        echo '<meta name="robots" content="noindex, nofollow">' . "\n";
    }, 1 );
}

// Increase media upload size limits
add_filter( 'upload_size_limit', function() {
    return 512 * 1024 * 1024; // 512MB limit
}, 20 );

add_filter( 'wp_max_upload_size', function() {
    return 512 * 1024 * 1024; // 512MB limit
}, 20 );

add_filter( 'wp_import_post_data_raw', function( $post_data ) {
    return $post_data;
} );

