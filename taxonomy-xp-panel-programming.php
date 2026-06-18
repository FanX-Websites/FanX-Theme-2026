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


 <!---- Programming Tabs Section ------------------------->
    <!--- Tab 1: Guest List --->
        <!--------------- #Guest List Section [Template Part] ----------------------->
        <div class="container full space">
        <!----- Guest List Header ---------->
            <div class="section-header">
                <h2>Featured Panelists</h2>
                <p>Choose Guest to View Schedule</p>
            </div><!---- END Guest List Header ---------->
        <!---- END Guest List Header ---------->
            <?php get_template_part('template-parts/list/basic-guest-list'); ?>
        </div><!-- END #Guest List Section ----------------------------------------->
    <!--- END Tab 1: Guest List --->
    
      <!-- Floor #Maps & Room List Section --->
     <?php get_template_part('template-parts/sections/floor-maps'); ?>
    <!-- END Floor #Maps & Room List Section -->  

    <!--- Tab 2: Schedule ---->
        <!----Panel Programming Schedule Section -------------->
        <div class="container full space">
        <!----- Panel Programming Schedule Header ---------->
            <div class="section-header" id="sched">
                <h2>Panel Programming Schedule</h2>
                <p>Pick a Day and/or Panel Room</p>
            </div><!---- END Panel Programming Schedule Header ---------->
        <!---- END Panel Programming Schedule Header ---------->
            <?php get_template_part('template-parts/schedules/panel-schedule'); ?>
        <!----- END Panel Programming Schedule Section ----------->
    <!--- END Tab 2: Schedule ---->
<!---END Programming Tabs Section ------------------------->

   <!------------------- Latest #News Post Block --------------------->
    <div class="container full">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
<!----------- END Latest # News Post Block -->

<?php
get_footer();
?>