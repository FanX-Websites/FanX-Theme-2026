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
        <!-- Section 1 - ADA Event Locations and Maps --> 
            <div class="container"></div> 
        
            <!-- Section 2 - ADA Event Services --> 
            <div class="container"></div>        
              
        <!-- Section 3 - Website Accessibility Statement --> 
        <div class="framed-900 container">        
            <?php get_template_part('template-parts/layouts/content-1-full'); ?>
        </div><!-- END Website Accessibility Statement Section -->
    <!-- END Main Content Area --> 

    <!-- Latest News Post Block Section -->
        <div class="updates full container">
            <?php get_template_part('template-parts/sections/updates-section'); ?>
        </div><!-- END Latest News Post Block -->
     <!-- END Latest News Post Block -->
         

<?php
    get_footer();
?>
