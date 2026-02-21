<!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <!-- HEAD -->     
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
            <!-- Head Injection Code -->
            <?php echo get_field('theme_head', 'option')?>

            <?php wp_head(); ?>

        </head><!-- END HEAD -->     

    <!-- Body -->
        <body <?php body_class(); ?>>
        <?php wp_body_open(); ?>
        
        <!-- Body Injection Code -->
        <?php echo get_field('theme_body', 'option'); ?>
    
        <!-- Header --->
            <header data-id="site-header" class="container">
                <!-- Header Injection Code -->

                
                    <!-- Alert Bar Container -->
                    <div data-id="alert-bar" class="container"> 
                        <?php get_template_part('template-parts/alert-bar');?>
                    </div><!-- END alert-bar -->
                    
                    <!--Main Menu -->
                    <div data-id="main-menu" class="container">  
                            <?php get_template_part('template-parts/main-menu'); ?>
                    </div><!-- END main-menu -->         
                 
            </header><!--END Header --> 

    <!-- Site Main -->  
     <main class="site-main">