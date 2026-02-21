<?php
/**
 * Template Name: Default Post 
 * @fanxtheme2026
 * 
 * Notes: 
 * Uses classes: profile, profile-header, profile-details, profile-img, profile-content, small-print
 * Needs: Small Print at Bottom - Not showing 
 * //FIXME: Replace current layout with CSS BLocks 
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
                </h1><!-- Profile Header Text --> 
        </div><!--END Profile Header -->
        </div><!-- END Self Centered -->

        <!-- Post Content --------------------------------------->    
        <?php if ( have_posts() ) :
            while ( have_posts() ) : the_post(); ?>

            <!-- Profile Main Section ------------------->
            <div class="profile self-centered-row"> <!-- Profile Responsive Section-->   

            <!-- Profile Image and Links -------------->
            <?php if ( has_post_thumbnail() ) : ?>
            <div class="profile-details block">

                <!-- Profile Image -------------->
                <div class="profile-img">
                    <?php the_post_thumbnail(); //Thumbnail ?> 
                </div><!-- END profile-img -->

                 <!-- Appearance Days -->
                    <?php 
                    $days_cats = get_the_terms( get_the_ID(), 'days' );
                    echo '<div class="days guest-xp">';
                    echo '<strong>Appearing:</strong> ';
                    
                    if ( ! empty( $days_cats ) && ! is_wp_error( $days_cats ) ) {
                        $links = array();
                        foreach ( $days_cats as $cat ) {
                            $links[] = '<a href="' . esc_url( get_term_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a>';
                        }
                        echo implode( ' | ', $links ) . '*';
                    } else {
                        echo 'More info soon*';
                      }
                    echo '</div>';
                    ?> 
                <!-- END Appearance Days ---> 
            
                <!-- Photo Ops -------------------------------------------------------->
                <div class="guest-op-info guest-xp">
                    <?php 
                    $op_price = get_field('xp')['op_price'] ?? '';
                    $op_url = get_field('xp')['op_url'] ?? '';
                    $xp_terms = get_the_terms( get_the_ID(), 'xp' );
                    $xp_status_terms = get_the_terms( get_the_ID(), 'xp-status' );
                    $has_photo_ops = false;
                    $is_coming_soon = false;
                    
                    // Photo Op XP Category Trigger
                    if ( $xp_terms && ! is_wp_error( $xp_terms ) ) {
                        foreach ( $xp_terms as $term ) {
                            if ( $term->slug === 'photo-ops' ) {
                                $has_photo_ops = true;
                                break;
                            }
                        }
                    }
                    
                    // Check for coming soon status
                    if ( $xp_status_terms && ! is_wp_error( $xp_status_terms ) ) {
                        foreach ( $xp_status_terms as $term ) {
                            if ( $term->slug === 'photo-ops-coming-soon' ) { //XP Status - Coming Soon Trigger
                                $is_coming_soon = true;
                                break;
                            }
                        }
                    }
                    
                    // Photo Op Status Messages
                    if ( $has_photo_ops ) : ?>
                        <div class="guest-ops-price">
                            <strong>Photo Ops:</strong> 
                            <?php 
                                if ( $op_price ) {
                                    echo esc_html($op_price);
                                    if ( $is_coming_soon ) {
                                        echo ' - Coming Soon'; //COMING SOON
                                    } elseif ( $op_url ) {
                                        echo ' - <a href="' . esc_url($op_url) . '">Buy Photo Ops NOW**</a>'; //BUY NOW
                                    }
                                } else {
                                    echo 'More Info Coming Soon*'; //NO PRICE - Coming Soon
                                }
                            ?> 
                        </div>
                    <?php endif; ?>
                </div>
                <!-- END Photo Ops <------------------------------------------->

            <!-- Autographs -->
                    <?php 
                    $auto_price = get_field('xp')['auto_price'] ?? '';
                    if ($auto_price) : ?>
                        <div class="auto-price guest-xp">
                            <strong>Autographs:</strong> <?php echo esc_html($auto_price); ?> - Available at Event***
                        </div>
                    <?php endif; ?><!-- END Autographs -->
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
        
        <!--- SMALL PRINT -->
        <div class="small-print">
            <p>
                <?php the_field('heafoo_small_print'); //Small Print ?>
            </p>
            <p>
                <?php the_field('heafoo_celeb_small_print'); //Small Print ?>
            </p>
        </div>
        <!-- END Small Print -->
    </div>
    <!-- END Profile Main Div --------------------->
            
            <?php
            endwhile; //Post Loop End - while
                endif; //Post Loop End - if

            get_footer(); //Footer 
            ?>


