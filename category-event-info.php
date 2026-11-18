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
        <!-- Content Blocks - Event Hours -[Template Part] #HOURS -->
        <div class="event-info container" id="hours">        
            <?php get_template_part('template-parts/sections/content-blocks'); ?>
        </div>
    <!-- END Comparison Chart Section -->

    <!-- Floor Maps & Room List Section --->
     <div class="" id="maps">
        <?php get_template_part('template-parts/sections/floor-maps'); ?>
    </div>
    <!-- END Floor Maps & Room List Section -->     
     
<!------------------- Latest News Post Block --------------------->
    <div class="container full" id="updates">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
<!----------- END Latest News Post Block -->

<!-------------------------- ADDRESS MAP Template Part --------------------->
    <div class="section-full-width" id="location">
        <?php get_template_part('template-parts/sections/event-address-map'); ?>
    </div>
<!---------------------------END ADDRESS MAP Template Part ---------------------> 



    <!-- END Main Content Area --> 

         

<?php
    get_footer();
?>
