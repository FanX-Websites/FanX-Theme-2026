<?php
/**
 * Taxonomy Template: eXperiences (XP) Category/Archive Pages
 * @author FanXTheme2026
 * Default template for XP categories. 
 * //TODO: Create Sections (template-parts) w/Headers for guests, latest updates, features, events, etc. (as needed)
 */
get_header(); /** body- main-site */
?>
<!-- Category Page Body -->

    <!--------------- Page Header Container [Template Part] ----------------------->
    <div class="page-header container">
        <?php get_template_part('template-parts/page-header'); ?>
    </div><!-- END page-header Container -->
    <!------------ END Page Header Container -------------------->




        <!--------------- #Guest List Section [Template Part] ----------------------->
        <div class="container full">
            <?php get_template_part('template-parts/list/basic-guest-list'); ?>
        </div><!-- END #Guest List Section ----------------------------------------->


    <!-- Floor #Maps & Room List Section --->
        <?php get_template_part('template-parts/sections/sched-maps'); ?>
    <!-- END Floor #Maps & Room List Section -->  

   <!------------------- Latest #News Post Block --------------------->
    <div class="container full">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
<!----------- END Latest # News Post Block -->

<?php
get_footer();
?>