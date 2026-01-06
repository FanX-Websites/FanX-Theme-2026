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
                'posts_per_page' => -1,  
            );
            
            $query = new WP_Query($args);

            if( $query->have_posts() ) {
                while( $query->have_posts() ) {
                    $query->the_post();
                    // Content Output
                    $image = get_the_post_thumbnail_url();
                        if( $image ) {
                        echo '<img src="' . esc_url($image) 
                        . '" alt="'. get_the_title() .'">'; //Image
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