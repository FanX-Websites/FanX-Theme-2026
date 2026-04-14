<?php
/**
 * Template Name: Default Post 
 * @fanxtheme2026
 * 
 * Notes: 
 * Uses classes: profile, profile-header, profile-details, profile-img, profile-content, small-print
 * Needs: Small Print at Bottom - Not showing 
 * //FIXME: Replace current layout with CSS Grid Blocks 
 * //TODO: Guest eXperience Conditionals - ie PhotoOps 'coming soon' conditional to guest expereince status 
 */

get_header();
//END Header  
 ?>

<!-- Profile Main Div --------------------->
    <div class="profile min-90"><!-- Profile sizing, padding,  -->
        <div class="self-centered">
            <!--  Profile Header -->
            <div class="profile-header block"> 
                
                <!-- Submenu --- [Template Part] -->
                <div class="sub-menu container">
                    <?php get_template_part( 
                        'template-parts/sub-menu' 
                    ); ?>
                </div><!-- END Submenu -->

                <!-- Main Category --->
                <h1>
                    <?php 
                        $categories = get_the_category(); //Main Category 
                        if ( ! empty( $categories ) ) {
                            echo '<div class="profile-cat-header">';
                            
                            foreach ( $categories as $category ) {
                                echo '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a> ';
                            }
                            echo '</div>';
                        }
                    ?><!-- END Main Category -->
                </h1 class="profile"><!-- Profile Header Text --> 
        </div><!--END Profile Header -->
        </div><!-- END Self Centered -->

        <!-- Post Content --------------------------------------->    
        <?php if ( have_posts() ) :
            while ( have_posts() ) : the_post(); ?>

            <!-- Profile Main Section ------------------->
            <div class="profile self-centered-row"> <!-- Profile Responsive Section-->   

            <!-- Profile Image and Links ----------------------------------------->
            <?php if ( has_post_thumbnail() ) : ?>
            <div class="profile-details block">

                <!-- Profile Image -------------->
                <div class="profile-img">
                    <?php the_post_thumbnail(); //Thumbnail ?> 
                </div><!-- END profile-img -->

                <!-- Appearance Days //NOTE: Conditional on Guest/Feature remove/update when seprate post templates 
                 -->
                    <?php 
                    if ( get_post_type() === 'guests' || get_post_type() === 'features' ) {
                        get_template_part( 'template-parts/profiles/appearance-days' ); 
                    }
                    ?>
                <!-- END Appearance Days ---> 

                <!-- Guest Experiences //NOTE: Conditional on Guest/Feature remove/update when seprate post templates 
                 -->
                    <?php 
                    if ( get_post_type() === 'guests' || get_post_type() === 'features' ) {
                        get_template_part( 'template-parts/profiles/experiences' ); 
                    }
                    ?>
                <!-- END Guest Experiences -->
            
            
            </div><!--END Profile Details block --> 
            <?php endif; ?>
                <!-- END Profile Image and Links -------------->
                
                <!-- Profile Content & Galleries -->
                <div class="profile-content block">

                    <!--- Profile Title & Cats --> 
                    <!-- Profile Name & Cats -->
                    <div class="profile-header">
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
                </div>
                <!-- END Fandom Tags -->
                    
                    <!-- Profile Content -->
                    <div class="the-content">
                        <?php the_content(); //Content ?>
                    </div><!-- END Profile Content-->

                <!-- Guest Xperience Links --->
                    <!-- Featured Links Buttons --> 
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
                    </div><!-- END Featured Links--> 
                <!-- END Buttons -->
            </div><!-- END Profile Details Block -->
        </div><!-- END Profile Main Section ------------------->
        
        <?php get_template_part( 'template-parts/profiles/smallprint' ); ?>
    </div>
    <!-- END Profile Main Div --------------------->

    <!-- Latest Posts ------> 
        <get template_part( 'template-parts/sections/updates-section' ); ?>

    <!-- END Latest Posts ------>
            
            <?php
            endwhile; //Post Loop End - while
                endif; //Post Loop End - if

            get_footer(); //Footer 
            ?>


