<?php
/**
 * Template Part Name: Page Header 
 * @author FanXTheme2026
 * 
 */
?>
<!--------- Page Header ----------------------->
<div class="page-header-section self-centered-top">          
    <div class="page-header block">

        <!--------- Submenu --- [Template Part] 
            <?php get_template_part( 'template-parts/sub-menu' ); ?>
        <!--------- END Submenu -------->
        
        <!---------------------- Page Title Block ----------------------->
        <div class="page-title block self-centered-column">    
                <h1 class="page-title">
                    <?php 
                        if ( is_tax() || is_category() || is_tag() ) { //Category as Page Title 
                            single_term_title();
                            } elseif ( is_archive() ) {
                                the_archive_title( '', '' ); //Archive Title
                            } else {
                                the_title(); //Page Title if Not Category/Archive
                            }
                    ?>
                </h1><!-- END Page Title -->
            
                    
                <!-- Subtitle -->   
                        <?php 
                            $queried_object = get_queried_object();
                            $field_key = '';
                            
                            // Determine correct ACF field key based on object type
                                if ($queried_object && isset($queried_object->taxonomy)) {
                                    // For term/taxonomy:
                                    $field_key = 'term_' . $queried_object->term_id;
                                } else {
                                    // For post/page:
                                    $field_key = get_the_ID();
                                }
                            $subtitle = get_field('heafoo_subtitle', $field_key);
                                if ( $subtitle ) {
                        ?>
                    <h2 class="page-subtitle">
                        <?php echo $subtitle; ?>
                    </h2><!-- END Subtitle -->
                        <?php } ?>
                
                <!-- Subtext -->
                        <?php 
                            $subtext = get_field('heafoo_subtext', $field_key);
                                if ( $subtext ) {
                        ?>
                    <p class="page-subtext">  
                        <?php echo $subtext; ?>
                    </p><!-- END Subtext -->
                        <?php } ?>
                
                <!--Taxonomy Description -->
                        <?php 
                            $archive_description = get_the_archive_description();
                            if ( $archive_description ) {
                        ?>
                    <div class="description block">
                        <p class="description">
                            <?php echo $archive_description; ?>
                        </p><!-- END Taxonomy Description Text -->
                    </div><!-- END description block -->
                        <?php } ?>

                    <!-- END Taxonomy Description -->
        </div><!-- END Page Title Block -->
    </div><!-- END Page Header Block -->
</div><!-- END Page Header Section -->
<!--------- END Page Header ----------------------->