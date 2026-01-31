<?php
/**
 * Template Name: Basic Category Archive
 * @author FanXTheme2026
 * 
 * Notes: 
 * Classes used: tax-cat-page, container, page-header, updates, event-hours
 */

get_header(); /** body- main-site */
?>
<!-- Tax Cat Page --> 

     
    <!--------------- Page Header Container [Template Part] ----------------------->
    <div class="page-header container">
        <?php get_template_part('template-parts/page-header'); ?>
    </div>
    <!------------ END Page Header Container -------------------->

    <!-- Main Content Area -->
        <!-- Content Blocks - Event Hours -[Template Part] -->
        <div class="event-info container">        
            <?php get_template_part('template-parts/sections/content-blocks'); ?>
        </div><!-- END Comparison Chart Section -->
    <!-- END Main Content Area --> 

    <?Php /* Hidden Until Styling issues resolved
    <!-- Latest News Post Block Section -->
        <div class="updates container">
            <?php get_template_part('template-parts/sections/updates-section'); ?>
        </div><!-- END Latest News Post Block -->
     <!-- END Latest News Post Block --> */ ?>
         

<?php
    get_footer();
?>
