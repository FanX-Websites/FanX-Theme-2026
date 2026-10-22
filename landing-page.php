<?php
/**
 * Template Name: Landing Page Template - Basic Layout  
 **/ 
 ?>

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
    
    <!-- Site Main -->  
     <main class="site-main landing">



<!------------- Main Page Div ----------------------->
<div class="page full">
    <div class="self-centered-column fill">
    <!-- Entry Content -->
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>    
            <?php 
            $content = get_the_content();
            if ( ! empty( trim( $content ) ) ) : ?>

                <!-- Page Content ---------------------------->
                <div class="landing-page-content">
                    <?php echo wp_kses_post( get_the_content() ); ?>
                </div>
                <!-------------------- END Basic Page Content-->    
            
                <?php else : ?>
                <div class="no-posts">
                    <h2>INFO COMING SOON</h2>
                </div>
            <?php endif; ?>
        <?php endwhile; ?> 
    <?php else : ?>
        <div class="no-posts">
            <h2>INFO COMING SOON</h2>
        </div>
    <?php endif; ?>
    </div><!-- END Centered Column Div -->
</div><!------- End Main Page Div --------------------->
    </main>
    </body>
</html>