<?php
/**
 * Template Name: Front Page
 */
get_header(); ?>

<!-- Front-Page Layout -->
<div class="front-page">

 
<!-- Front Page Grid -->
<div class="fp-grid">
    <!-- Front Page Grid Blocks -->
        <!-- Featured Video ---------------------------->
        <div class="feat-video container">
            <?php get_template_part( 
                'template-parts/fp-blocks/feat-video'
            ); ?>
        </div><!-- END Featured Video  -->

       <!-- Event Info ---------------------------------->
            <div class="event-details container">
                <?php get_template_part( 
                    'template-parts/fp-blocks/event-details'
                ); ?>
            </div><!-- END Event Info -->

        <!-- Updates Feed -------------------------------->
        <div class="updates-feed container">
            <?php get_template_part( 
                'template-parts/fp-blocks/updates-feed'
            ); ?>
        </div><!-- END Updates Feed -->

            <!-- Countdown ----------------------------------->
        <div class="countdown container">
            <?php get_template_part( 
                'template-parts/fp-blocks/countdown'
            ); ?>
        </div><!--END Countdown -->

    </div><!-- END Front Page Grid (fp-grid) -->  
 


</div><!-- END Page Cover -->
<!-- END Front-Page Layout -->

<?php get_footer(); ?>
