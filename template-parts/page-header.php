<?php
/**
 * Template Part Name: Page Header 
 * @author FanXTheme2026
 * 
 * Notes: 
 * Uses classes: self-centered-top, page-header-section, page-header, sub-menu, description, page-title
 */
?>

<!--------- Page Header ----------------------->
<div class="page-header-section self-centered-top">          
    <div class="page-header block">

        <!--------- Submenu --- [Template Part] -->
        <div class="sub-menu container">
            <?php get_template_part('template-parts/sub-menu'); ?>
        </div>
        <!--------- END Submenu -------->
        
        <!--Page Title Block -------->
        <div class="page-title block">    
    <h1 class="page-title">
        <?php 
        if ( is_tax() || is_category() || is_tag() ) {
            single_term_title();
        } elseif ( is_archive() ) {
            the_archive_title( '', '' );
        } else {
            the_title();
        }
        ?>
    </h1>
</div>
        <!-- END Page Title Block -->
        
        <!--Taxonomy Description -->    
        <div class="description block">
            <p class="description">
                <?php the_archive_description(); ?>
            </p>
        </div><!-- END description block -->
        <!-- END Taxonomy Description -->

    </div><!-- END Page Header Block -->
</div><!-- END Page Header Section -->
<!--------- END Page Header ----------------------->