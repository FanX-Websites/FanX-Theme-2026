<?php
/**
 * Template Name: Category Page - ADA Information
 * @author FanXTheme2026
 * 
 * Notes: 
    *Stylesheet class indicators: 
 */

get_header(); /** body- main-site */
?>
<!-- Tax Cat Page --> 

     
    <!--------------- Page Header Container [Template Part] ----------------------->
    <div class="page-header container">
        <?php get_template_part('template-parts/page-header'); ?>
    </div>
    <!------------ END Page Header Container -------------------->

    <?php /* HIDDEN UNTIL TEMPLATE PART IS FINISHED --------------->
    <!-- Main Content Area --> 
        <!-- Section 1 - ADA Event Locations and Maps --> 
            <div id="maps" class="container">
                <?php // get_template_part('template-parts/sections/floor-maps'); ?>
            </div> 
    
            <!-- Section 2 - ADA Event Services --> 
            <div id="services" class="container">
                <?php // get_template_part('template-parts/sections/'); ?>
            </div>    
    <-------------------------------------------------- */ 
    ?>

              
        <!-- Section 3 - Website Accessibility Statement --> 
        <div id="web-accessibility" class="container">        
            <?php get_template_part('template-parts/sections/content-blocks'); ?>
        </div><!-- END Website Accessibility Statement Section -->
    <!-- END Main Content Area --> 

    <!-- Latest News Post Block Section -->
        <div id="updates" class="updates full container">
            <?php get_template_part('template-parts/sections/updates-section'); ?>
        </div><!-- END Latest News Post Block -->
     <!-- END Latest News Post Block -->
         

<?php
    get_footer();
?>

