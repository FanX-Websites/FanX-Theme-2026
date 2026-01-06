<?php
/**
 * Template Name: Front Page
 */
get_header(); ?>

<!-- Front-Page Layout -->
<div class="front-page cover">

    <!-- Featured Info Blocks -->


        <!-- Featured Video ---------------------------->
        <div class="container">
            <?php get_template_part( 
                'template-parts/fp-blocks/feat-video'
            ); ?>
        </div><!-- END Featured Video  -->

        <!-- Updates Feed -------------------------------->
        <div class="container">
            <?php get_template_part( 
                'template-parts/fp-blocks/updates-feed'
            ); ?>
        </div><!-- END Updates Feed -->

       <!-- Event Info ---------------------------------->
            <div class="container">
                <?php get_template_part( 
                    'template-parts/fp-blocks/event-info'
                ); ?>
            </div><!-- END Event Info -->

            <!-- Countdown ----------------------------------->
        <div class="container">
            <?php get_template_part( 
                'template-parts/fp-blocks/countdown'
            ); ?>
        </div><!--END Countdown -->

        <!-- Participate Links --------------------------->
            <div class="container">
                <?php get_template_part( 
                    'template-parts/fp-blocks/participate'
                ); ?>
            </div><!-- Participate Links -->
 


</div><!-- END Page Cover -->
<!-- END Front-Page Layout -->

<?php get_footer(); ?>

<style>


</style>