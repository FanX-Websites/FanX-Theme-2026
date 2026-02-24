<?php
/**
 * Template Name: Exhibitor Info Page/ Category Archive
 * @author FanXTheme2026
 * 
 * Notes: 
 */

get_header(); /** body- main-site */
?>

    <!--------------- Page Header Container [Template Part] ----------------------->
    <div class="page-header container">
        <?php get_template_part('template-parts/page-header'); ?> 
    </div><!-- END page-header Container -->
    <!------------ END Page Header Container -------------------->

    <!-------------------------- CONTENT BLOCKS Template Part --------------------->
    <div class="content-blocks container">
        <?php get_template_part('template-parts/sections/content-blocks'); ?>
    </div>
    <!---------------------------END Content BLOCK Template Part --------------------->

    <!-------------------------- Table Charts Template Part --------------------->
    <div class="table-charts container">
        <?php get_template_part('template-parts/sections/table-charts'); ?>
    </div>
    <!---------------------------END Table Charts Template Part --------------------->

    <!-------------------------- Updates Section Template Part --------------------->
    <div class="section-full-width container">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
    <!---------------------------END Updates Section Template Part --------------------->
    

<?php
get_footer();
?>