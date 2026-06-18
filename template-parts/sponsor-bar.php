<?php
/**
 * Template Name: Sponsor Bar 
 * Sponsor Logos pulled from Partner CPT under Sponsor Category
 * 
 * Notes: 
 * Uses classes: sponsor-bar, self-centered
 */
?>

<!-- Sponsor Bar -->
<div class="sponsor-bar self-centered">
        <?php
            $args = array(
                'post_type' => 'partner',  
                'category_name' => 'sponsors',  
                'nopaging' => true,  
            );
            
            $query = new WP_Query($args);

            if( $query->have_posts() ) {
                while( $query->have_posts() ) {
                    $query->the_post();
                    // Content Output
                    $image = get_the_post_thumbnail_url();
                        if( $image ) {
                        $button_url = get_field('button')[0]['url'];
                        echo '<a href="' . esc_url($button_url) . '" target="_blank">';
                        echo '<img src="' . esc_url($image) 
                        . '" alt="'. get_the_title() .'">';
                        echo '</a>';
                    } //END $image                       
                }//END Posts 

                //Seemless Looping 

                wp_reset_postdata();
            } 
            else {
                echo '';
            }
        ?> 
</div><!-- END Sponsor Bar -->



<style>


    
</style>