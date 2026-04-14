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
            //NOTE: Removed until template part fixed --> 
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
                    <h2 class="page-subtitle">
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
                                    echo $subtitle;
                                } 
                        ?>
                    </h2><!-- END Subtitle -->
                
                <!-- Subtext -->
                    <p class="page-subtext">  
                        <?php 
                            $subtext = get_field('heafoo_subtext', $field_key);
                                if ( $subtext ) {
                                    echo $subtext;
                                } 
                        ?>
                    </p><!-- END Subtext -->
                
                <!--Taxonomy Description -->    
                    <div class="description block">
                        <p class="description">
                            <?php the_archive_description(); ?>
                        </p><!-- END Taxonomy Description Text -->
                    </div><!-- END description block -->

                    <!-- END Taxonomy Description -->
        </div><!-- END Page Title Block -->
    </div><!-- END Page Header Block -->
</div><!-- END Page Header Section -->
<!--------- END Page Header ----------------------->