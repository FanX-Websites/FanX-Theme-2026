<?php
/**
 * Taxonomy Template: Vendor Floor eXperience (XP) Category/Archive Page
 * @author FanXTheme2026
 */
get_header(); /** body- main-site */
?>
<!-- Category Page Body -->

    <!--------------- Page Header Container [Template Part] ----------------------->
    <div class="page-header container">
        <?php get_template_part('template-parts/page-header'); ?>
    </div><!-- END page-header Container -->
    <!------------ END Page Header Container -------------------->


    <!--------------- #Feature/Activity List Section [Template Part] ----------------------->
    <div class="section">
        <!----- Features/Activities List Header ---------->
            <div class="section-header">
                <h2>Features & Activities</h2>
                <p>on the Vendor Floor</p>
            </div><!---- END Features/Activities List Header ---------->
        <!---- END Features/Activities List Header ---------->
        <div class="container full">
            <?php get_template_part('template-parts/list/basic-feature-list'); //Basic Feature List ?>
        </div>
    </div><!-- END #Feature/Activity List Section ----------------------------------------->

    <!--------------- #Feature/Activity List Section [Template Part] ----------------------->
    <div class="section">
        <!----- Features/Activities List Header ---------->
            <div class="section-header">
                <h2>Vendor Spotlight</h2>
                <p>on the Vendor Floor</p>
            </div><!---- END Features/Activities List Header ---------->
        <!---- END Features/Activities List Header ---------->
        <div class="container full">
            <?php get_template_part('template-parts/list/basic-feature-list-child'); //Basic Feature List -  ?>
        </div>
    </div><!-- END #Feature/Activity List Section ----------------------------------------->

    <!--------------- #Feature/Activity List Section [Template Part] ----------------------->
    <div class="section">
        <!----- Guest List Header ---------->
            <div class="section-header">
                <h2>Featured Guests</h2>
                <p>on the Vendor Floor</p>
            </div><!---- END Guest List Header ---------->
        <!---- END Guest List Header ---------->
        <div class="container full">
            <?php get_template_part('template-parts/list/basic-guest-list'); ?>
        </div>
    </div><!-- END #Feature/Activity List Section ----------------------------------------->



    <!-- Floor #Maps & Room List Section --->
    <div class="vend-map full-framed section">
        <div class="section-header">
                <h2>Vendor List & Floor Maps</h2>
                <p>for <?php echo get_field('event_hashtag', 'options')  ?></p>
            </div><!---- END Guest List Header ---------->
        <div class="container full">
            <?php get_template_part('template-parts/xps/vend-maps'); ?>
        </div>
    </div><!--- END Floor Maps & Room List Section ---> 

    <!-- END Floor #Maps & Room List Section -->  


   <!------------------- Latest #News Post Block --------------------->
    <div class="container full">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
<!----------- END Latest # News Post Block -->

<?php
get_footer();
?>