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
        <div class="event-info container" id="hours">        
            <?php get_template_part('template-parts/sections/content-blocks'); ?>
        </div><!-- END Comparison Chart Section -->

<!-------------------------- ADDRESS MAP Template Part --------------------->
    <div class="section-full-width">
        <?php get_template_part('template-parts/sections/event-address-map'); ?>
    </div>
<!---------------------------END ADDRESS MAP Template Part ---------------------> 

    <!-- END Main Content Area --> 

         

<?php
    get_footer();
?>
