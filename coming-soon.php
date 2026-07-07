<?php
/**
 * Template Name: Coming Soon Status Page
 * @author FanXTheme2026
 * 
 * 
 **/

get_header(); /** body- main-site */
?>
<!-- Category Page Body -->

    <!--------------- Page Header Container [Template Part] ----------------------->
    <div class="container">
        <?php get_template_part('template-parts/page-header'); ?>
    </div><!-- END page-header Container -->
    <!------------ END Page Header Container -------------------->


    <!--- No Posts/Coming Soon Message --->
    <div class="container full">
        <?php get_template_part( 'template-parts/coming-soon' );?>
    </div>
    <!--- END No Posts/Coming Soon Message-----> 


    <!-- Small Print Section -->
    <div class="container full">
        <?php get_template_part( 'template-parts/profiles/smallprint' ); ?>
    </div>
    <!--- END Small Print Section -->

<!------------------- Latest News Post Block --------------------->
    <div class="container full">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
<!----------- END Latest News Post Block -->

<?php
get_footer();
?>