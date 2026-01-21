<?php
/**
 * Template Name: Default Post 
 * @fanxtheme2026
 * 
 * Notes: 
 * Uses classes: profile, profile-header, profile-details, profile-img, profile-content, small-print
 * Needs: Small Print at Bottom - Not showing 
 */

get_header();
//END Header  
 ?>

<!-- Profile Main Div --------------------->
    <div class="profile min-90"><!-- Profile sizing, padding,  -->
        <div class="self-centered">
            <!--  Profile Header --------------->
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
                </h1><!-- Profile Header Text --> 
        </div><!--END Profile Header ------------------>
    </div><!-- END Self Centered -->

    <!-- Post Content --------------------------------------->    
    <?php if ( have_posts() ) :
        while ( have_posts() ) : the_post(); ?>
        <!-- Profile Main Section ------------------->
        <div class="self-centered-row"> <!-- Profile Responsive Section-->   

            <!-- Profile Image and Links -------------->
            <div class="profile-details block">
                <!-- Profile Image -------------->
                <div class="profile-img">
                    <?php the_post_thumbnail(); //Thumbnail ?> 
                </div><!-- END profile-img -->
                
            </div><!--END Profile Details block --> 
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

                    <!-- Fandoms -->
                    <?php 
                    $fandom_cats = get_the_terms( get_the_ID(), 'fandom' );
                    if ( ! empty( $fandom_cats ) && ! is_wp_error( $fandom_cats ) ) {
                        echo '<div class="fandoms">';
                        $links = array();
                        
                        foreach ( $fandom_cats as $cat ) {
                            $links[] = '<a href="' . esc_url( get_term_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a>';
                        }
                        echo implode( ', ', $links );
                        echo '</div>';
                    } 
                    ?> <!-- END Fandoms ---> 
                    
                    <!-- Profile Content -->
                    <div class="profile-content">
                        <?php the_content(); //Content ?>
                    </div><!-- END Profile Content-->
                    
                    <!-- Buttons from ACF Repeater -->
                    <?php 
                    if( have_rows('button') ) {
                        echo '<div class="button-container">';
                        while( have_rows('button') ) {
                            the_row();
                            $button_title = get_sub_field('title');
                            $button_subtitle = get_sub_field('subtitle');
                            $button_link = get_sub_field('url');
                            
                            if( $button_title && $button_link ) {
                                echo '<a href="' . esc_url($button_link) . '" class="button">';
                                echo '<span class="button-title">' . esc_html($button_title) . '</span>';
                                if( $button_subtitle ) {
                                    echo '<span class="button-subtitle">' . esc_html($button_subtitle) . '</span>';
                                }
                                echo '</a>';
                            }
                        }
                        echo '</div><!-- END button-container -->';
                    }
                    ?> <!-- END Buttons -->
                    <!-- Guest Xperience Links --->
                     
                      

            </div><!-- END Profile Details Block -->
        </div><!-- END Profile Responsive Section-->

       

    </div><!-- END Profile Main Div --------------------->
            
            <?php
            endwhile; //Post Loop End - while
                endif; //Post Loop End - if

            get_footer(); //Footer 
            ?>


