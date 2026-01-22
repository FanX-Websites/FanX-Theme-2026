<?php
// Exit if accessed directly
	if (!defined('ABSPATH')) {
		exit;
	}

//--- Theme Support -->
	function fanx_theme_setup() {	
		add_theme_support('post-thumbnails'); //Thumbnails	
	
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
				wp_enqueue_style('fanx-branding', get_template_directory_uri() . '/branding/styles/fanx.css', array(), wp_get_theme()->get('Version')); //Branding CSS
			
		}
		add_action('wp_enqueue_scripts', 'fanx_enqueue_assets');

	//Front end JavaScript
		function fanx_enqueue_scripts(){
			wp_enqueue_script('fanx-scripts', get_template_directory_uri() . '/js/scripts.js', array(), '1.0.2', true);
		}
		add_action('wp_enqueue_scripts', 'fanx_enqueue_scripts');

	//Admin JavaScript
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
		//Plugins - Thrird Party	
			'acf/tweaks.php',
			'seo/yoast.php',
			'simply-static/schedule.php',
		//Admin Area 	
			'admin/updates/dashboard.php',
			'admin/white-label.php',
			'admin/customadmin.php',	
			'admin/custommenu.php',
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
