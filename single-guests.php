<?php
/**
 * Template Name: Default Profile Pages - Guests, Features, Partners & Products. 
 * @fanxtheme2026
 * 
 * //NOTE: Blog is now its own template. Make needed changes across both templates accordingly. 
 * //TODO: Replace current layout with CSS Grid Blocks 
 */

get_header();
//END Header  
 ?>

<!-- Profile Header [Template Part] -->
    <div class="container full"><!-- Container for Submenu & Profile Header -->
        <?php get_template_part( 'template-parts/profiles/profile-header' ); ?><!-- Profile Header Template Part -->    
    </div>

<!-- Profile Main Div --------------------->

<!-- Main Profile Container -->
<div class="profile"><!-- Profile sizing, padding,  -->

    <!-- Main Profile Card/Content - Grid Container --------------------------------------->    
    <div class="profile-card grid-container layout-2col"> 
        <?php if ( have_posts() ) :
            while ( have_posts() ) : the_post(); ?>

        <!-- Profile Details - Grid Block ------------------>
        <div class="grid-block profile-details"> 
            <?php if ( has_post_thumbnail() ) : //Conditional - Profile Image or not hides column ?>
        
                <!-- Profile Image - DIV ------------->
                <div class="profile-img">
                    <?php the_post_thumbnail(); //Thumbnail ?> 
                    <?php 
                     if ( has_term( 'postponed', 'xp-status' ) ) : //Postponed Overlay ?>
                        <div class="postponed-overlay">
                            <span class="postponed-text">Postponed</span>
                        </div>
                    <?php endif;?>
                </div><!-- END profile-img ------------>

                <!-- Appearance Info [Template Part] -->
                <div class="container full"> 
                    <?php 
                    if ( get_post_type() === 'guests' || get_post_type() === 'features' ) {
                        get_template_part( 'template-parts/profiles/appearance-info' ); 
                    }
                    ?>
                </div><!-- END Appearance Days ---> 

        </div><!--END Grid Block Profile Details -->
        
        <!-- Profile Content - Grid Block ------------------>
        <div class="grid-block profile-content"><!-- Post Main Content -->
            <div><!--- Profile content header --->

                <!-- Postponed Notice -->
                    <?php if ( has_term( 'postponed', 'xp-status' ) ) : ?>
                        <div class="postponed-notice"> 
                            <?php the_field('stat_postponed', 'option'); //Postponed Message ?>
                        </div>
                    <?php endif; ?> 
                <!-- END Postponed Notice -->

                <!-- Profile Name, Subtitle, Subtext -->
                <div class="profile-name">
                    <h1><?php the_title(); ?></h1> <!-- Title --> 
                    <h2><?php the_field('heafoo_subtitle'); ?></h2> <!-- Subtitle --> 
                    <h3><?php the_field('heafoo_subtext'); ?></h3> <!--  Subtext -->  
                </div><!-- END Profile Name & Cats --> 

                <!-- Fandom Tags -->
                <div class="fandom-tags">
                    <?php
                    $fandoms = get_the_terms( get_the_ID(), 'fandoms' );
                    if ( $fandoms && ! is_wp_error( $fandoms ) ) {
                        echo '<div class="tags-list">';
                        $tags = array();
                        foreach ( $fandoms as $fandom ) {
                            $tags[] = '<span class="fandom-tag">' . esc_html( $fandom->name ) . '</span>';
                        }
                        echo implode( ' | ', $tags );
                        echo '</div>';
                    }
                    ?>
                </div><!-- END Fandom Tags -->

            </div><!-- END Profile content header --->    
                
            <!--- Profile Content - DIV -->
            <div class="the-content">
                <?php the_content(); //Content ?> 
            </div><!-- END Profile Content-->

            <!-- Buttons - Featured Links - DIV --> 
            <div class="featured-links"> 
                <?php
                // Check if the repeater field has rows of data
                if( have_rows('button') ):
                    // Loop through the rows of data
                    while ( have_rows('button') ) : the_row();
                        // Get sub field values
                        $title = get_sub_field('title');
                        $subtext = get_sub_field('subtext');
                        $url = get_sub_field('url');
                        
                        // Display the button if title and URL exist
                        if( $title && $url ):
                            ?>
                            <a href="<?php echo esc_url( $url ); ?>" class="button">
                                <?php echo esc_html( $title ); ?>
                                <?php if( $subtext ): ?>
                                    <span class="button-subtext"><?php echo esc_html( $subtext ); ?></span>
                                <?php endif; ?>
                            </a>
                            <?php
                        endif;
                    endwhile;
                endif;
                ?>
            </div><!-- END Buttons - Featured Links--> 
        
        </div><!-- Profile Content - Grid Block -->  
    </div><!--- Main Profile Card/Content - Grid Container -->    
    
    <!-- Guest eXperiences [Template Part]-->
    <div class="container full"> 
        <?php 
            if ( ( get_post_type() === 'guests' || get_post_type() === 'features' ) && ! has_term( 'postponed', 'xp-status' ) ) {
                get_template_part( 'template-parts/profiles/experiences' );
            }
        ?>
        <?php endif; ?>
    </div><!-- END - Guest Experiences -->  


    <!-- Featured Content/Links - Galleries, etc. //TODO: All Gallery Types -->

        <!-- Multi-Post Gallery [Template Part] -->
        <div class="container full">                
            <?php get_template_part( 'template-parts/profiles/multi-post-gallery' ); ?><!-- Multi-Post Gallery -->            
        </div><!-- END Multi-Post Gallery -->
        <!-- END Featured Content -->

            
    <!-- Small Print at Bottom [Template Part] -->   
    <div class="container full">
        <?php get_template_part( 'template-parts/profiles/smallprint' ); ?> 
    </div><!-- END template part container-->
    <!-- Small Print at Bottom -->

</div>
</div><!-- END Main Profile Container --------------------->
<!-- Latest Post [Template Part]------> 
<div class="container full">
    <?php get_template_part( 'template-parts/profiles/updates-section' ); ?>
</div><!-- END Template Part Container - Latest Posts ------>
            
            <?php
            endwhile; //Post Loop End - while
                endif; //Post Loop End - if 
            ?>


<?php get_footer(); //Footer ?>


