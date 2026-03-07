<?php
/**
 * Template Part Name: Page Header 
 * @author FanXTheme2026
 * 
 * Notes: 
 * 
 * 
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
            <!-- Category/Page Title -->
                <div class="page-title block">    
                    <h1 class="page-title">
                        <?php 
                        if ( is_tax() || is_category() || is_tag() ) { //Category as Page Title 
                            single_term_title();
                        } elseif ( is_archive() ) {
                            the_archive_title( '', '' );
                        } else {
                            the_title(); //Page Title if Not Category/Archive
                        }
                        ?>
                    </h1>
                    
            <!-- Subtitle -->        
                <?php 
                if ( is_category() || is_tax() ) {
                    $term_id = get_queried_object_id();
                    $subtitle = get_field('subtitle', 'term_' . $term_id);
                } else {
                    $subtitle = get_field('heafoo_subtitle');
                }
                if ( $subtitle ) {
                    printf( '<h4 class="cat-subtitle">%s</h4>', esc_html( $subtitle ) );
                } 
                ?>
            
            <!-- Subtext -->
                <?php 
                if ( is_category() || is_tax() ) {
                    $term = get_queried_object();
                    $subtext = get_field('subtext', 'term_' . $term->term_id);
                } else {
                    $subtext = get_field('heafoo_subtext');
                }
                if ( $subtext ) {
                    printf( '<p class="cat-subtext">%s</p>', esc_html( $subtext ) );
                } 
                ?>
        
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