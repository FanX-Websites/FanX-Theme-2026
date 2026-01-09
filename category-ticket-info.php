<?php
/**
 * Template Name: Ticket Info Category Archive
 * @author FanXTheme2026
 * 
 * Notes: 
 * Classes used: tax-cat-page, container, page-header, comparison-chart, updates, ticket-compare,
 */

get_header(); /** body- main-site */
?>
  
<!--------------- Page Header Container [Template Part] ----------------------->
<div class="page-header container">
    <?php get_template_part('template-parts/page-header'); ?>
</div>
<!------------ END Page Header Container -------------------->

<!------------------- Main Content Area -------------------->
    <!-- Content Blocks - Comparison Chart -[Template Part] -->
    <div class="comparison-chart container">        
        <?php get_template_part('template-parts/sections/content-blocks'); ?>
    </div>
    <!----------- END Comparison Chart Section -->

    <!------------------- Latest News Post Block --------------------->
    <div class="updates container">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
    <!----------- END Latest News Post Block -->

<!----------- END Main Content Area ----------------->  


<?php
    get_footer();
?>