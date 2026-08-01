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
        <?php get_template_part('template-parts/page-header'); // Page Header ?>
    </div><!-- END page-header Container -->
    <!------------ END Page Header Container -------------------->


<!---- Programming Tabs Section ------------------------->
    <!--- GUEST LIST ------------------------------------->
        <!--------------- #Guest List Section [Template Part] ----------------------->
    <div class="section">
        <!----- Guest List Header ---------->
            <div class="section-header">
                <h2>Cosplay Judges & Host</h2>
                <p>Choose Guest to View their Profile & Schedule</p>
            </div><!---- END Guest List Header ---------->
        <!---- END Guest List Header ---------->
        <div class="container full space">
            <?php get_template_part('template-parts/list/basic-guest-list'); //Guest List [Template-Part] ?>
        </div>
    </div><!-- END #Guest List Section ----------------------------------------->
    <!--- Guest List --->

   <!------------------- Latest #News Post Block --------------------->
    <div class="container full">
        <?php get_template_part('template-parts/sections/updates-section'); //Updates Section [Template-Part] ?>
    </div>
<!----------- END Latest # News Post Block -->

<?php
get_footer();
?>