<?php
/**Template Title: Website Socket 
 * Copyright 
 * Socket Menu 
 * Social Links
 * 
 * Classes used: resp-section, copyright, block, socket, socket-menu, socket-socials,  hor-nav, 
 */


?>

<div class="socket self-centered-row">
    
    <!--- Copyright ----------------------------------->
    <div class="copyright block">
        Copyright <?php echo get_field('event_name', 'options')?>  
        <?php echo do_shortcode( '[year]' ); ?>
    </div><!--- END Copyright -->
    
    <!-- Socket Menu ----------------------------------->
    
    <menu class="socket-menu hor-nav block">
    <!-- Builds on socket self-centered-row -->
        <?php
        wp_nav_menu( array(
            'theme_location' => 'socket', //Purchase  
            'menu_class' => '',
            'fallback_cb' => false, 
        )); ?>
    </menu><!--- END Socket Menu -->
    
    <!-- Social Links ----------------------------------->
    <div class="socket-socials hor-nav block">
        <?php
            $socials = get_query_var('socials');
            if (!empty($socials)) {
                echo '<ul class="social-links">';
                foreach ($socials as $social) {
                    $link = esc_url($social['url']);
                    echo '<li class="social-link">';
                    if ($link) {
                        echo '<a href="' . $link . '" target="_blank" rel="noopener">';
                    }
                echo $social['logo'];
                    if ($link) {
                        echo '</a>';
                    }
                    echo '</li>';
                    }   
                echo '</ul>';
                }
            ?>
    </div><!-- END Social Links --> 

</div><!-- END Socket Section -->

<style>
</style>