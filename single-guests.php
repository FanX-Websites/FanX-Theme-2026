<?php
/**
 * Template Name: Default Profile Pages - Guests, Features, Partners & Products. 
 * @fanxtheme2026
 * 
 * //TODO: Replace current layout with CSS Grid Blocks 
 * //TODO: Create seperate templates for every CPT
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
<div class="profile page"><!-- Profile sizing, padding,  -->

    <!-- Main Profile Card/Content - Grid Container --------------------------------------->    
    <div class="profile-card grid-container <?php echo ( has_post_thumbnail() && ! has_term( 'alumni', 'category' ) ) ? 'layout-2col' : 'layout-1col'; ?>"> 
        <?php if ( have_posts() ) :
            while ( have_posts() ) : the_post(); ?>
        
            <?php 
                if ( has_term( 'postponed', 'xp-status' ) ) : //Postponed Overlay ?>
                <div class="profile-card postponed-overlay">
                    <span class="postponed-text">Postponed</span>
                </div>
            <?php endif;?>

        <!-- Profile Details - Grid Block ------------------>
        <?php if ( has_post_thumbnail() && ! has_term( 'alumni', 'category' ) ) : //Conditional - Profile Image or not hides column (also hidden for alumni) ?>
        <div class="grid-block profile-details"> 
        
                <!-- Profile Image - DIV ------------->
                <div class="profile-img">
                    <?php the_post_thumbnail(); //Thumbnail ?> 
                    
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
        <?php endif; ?>
        
        <!-- Profile Content - Grid Block ------------------>
        <div class="grid-block profile-content"><!-- Post Main Content -->
            <div class="profile-content-header"><!--- Profile content header --->


                <!-- Profile Name, Subtitle, Subtext -->
                <div class="profile-name">
                    <h1><?php the_title(); ?></h1> <!-- Title --> 
                    <h2><?php the_field('heafoo_subtitle'); ?></h2> <!-- Subtitle --> 
                    <h3><?php the_field('heafoo_subtext'); ?></h3> <!--  Subtext -->  
                </div><!-- END Profile Name & Cats --> 

                <!-- Guest List Tags (Alumni only) -->
                <?php if ( has_term( 'alumni', 'category' ) ) : ?>
                <div class="guest-list-tags">
                    <?php
                    $guest_list_terms = get_the_terms( get_the_ID(), 'guest-list' );
                    if ( $guest_list_terms && ! is_wp_error( $guest_list_terms ) ) {
                        echo '<div class="tags-list"><strong>Past Events:</strong> ';
                        $tags = array();
                        foreach ( $guest_list_terms as $gl_term ) {
                            $term_url = get_term_link( $gl_term );
                            $tags[] = '<a href="' . esc_url( $term_url ) . '" class="fandom-tag">' . esc_html( $gl_term->name ) . '</a>';
                        }
                        echo implode( ' | ', $tags );
                        echo '</div>';
                    }
                    ?>
                </div>
                <?php endif; ?>
                <!-- END Guest List Tags -->

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
                
            <!-- Guest Profile Info -->
            <div class="guest tab-section block"><!-- Guest Tabs --------------->

                <?php $is_tv_film = has_term( 'tv-film-guests', 'category' ); //Default to Guest eXperiences tab for TV/Film guests ?>

                <?php if ( ( get_post_type() === 'guests' || get_post_type() === 'features' ) && ! has_term( 'alumni', 'category' ) ) : ?>
                <div class="guest tab-top-bar block"><!-- Tab Top Bar -->
                    <button class="guest-tab-button" onclick="openTab('guest-bio')">Guest Bio</button>
                    <button class="guest-tab-button" onclick="openTab('guest-schedule')">Guest Schedule</button>
                    <button class="guest-tab-button" onclick="openTab('guest-xp')">Guest eXperiences</button>
                </div><!-- END Tab Top Bar -->
                <?php endif; ?>

                <div class="profile the-content-block">

                    <!--- Guest Bio Tab - Tab 1 ---------------------->
                    <div class="guest-bio tab" id="guest-bio" <?php echo $is_tv_film ? 'style="display:none"' : ''; ?>>
                        <!--- Profile Content - DIV -->
                            <div class="profile the-content">
                               <?php 
                                    if ( ! empty( trim( get_the_content() ) ) ) {
                                        the_content(); // Display content if it exists
                                    } else {
                                        echo 'More info about ' . esc_html( get_the_title() ) . ' coming soon'; //More Info Message when Empty
                                    }
                                ?>
                            </div><!-- END Profile Content-->   
                        <!-- END Profile Content - DIV -->
                    </div><!-- END Guest Bio Tab --------------------->

                    <!-- Guest Schedule Tab - Tab 2 -----------------------------> 
                        <?php if ( get_post_type() === 'guests' || get_post_type() === 'features' ) : ?>
                    <div class="guest-schedule tab" id="guest-schedule" style="display:none">                  
                        <div class="profile the-content">
                            <!-- Guest Schedule [Template Part] -->
                            <div class="container full"> 
                                <?php get_template_part( 'template-parts/profiles/schedule' ); ?>
                            </div><!--- END Guest Schedule Template Part -->
                        </div><!-- END Guest Schedule Template Part -->
                    </div>
                        <?php endif; ?>
                    <!-- END Guest Schedule Tab -->
                    
                    <!--- Guest eXperiences Tab - Tab 3 ------------------------>
                    <div class="guest-xp tab" id="guest-xp" <?php echo $is_tv_film ? '' : 'style="display:none"'; ?>>
                       <div class="profile the-content">
                            <!-- Guest eXperiences [Template Part]-->
                            <div class="container full"> 
                                <?php 
                                    if ( ( get_post_type() === 'guests' || get_post_type() === 'features' ) && ! has_term( 'postponed', 'xp-status' ) ) {
                                        get_template_part( 'template-parts/profiles/experiences' );
                                    }
                                ?>
                            </div><!-- END - Guest Experiences -->  
                       </div> 
                    </div>
                    <!---- END Gues eXperiences Tab ---------------------->
<!-- Buttons - Featured Links - DIV --> 
            <div class="featured-links"> 
                <?php
                // Auto-button: Photo Ops (products CPT + 'photo-ops' xp term)
                if ( get_post_type() === 'products' && has_term( 'photo-ops', 'xp' ) ) :
                    if ( has_term( 'photo-ops-coming-soon', 'xp-status' ) ) :
                        // Coming Soon state — non-clickable
                        ?>
                        <span class="button button-coming-soon">Photo Ops Coming Soon</span>
                        <?php
                    else :
                        $ded_link     = get_field( 'ded_prod_cat' ); // Post-level link field (overrides options)
                        $auto_btn_url = ! empty( $ded_link['url'] )
                            ? $ded_link['url']
                            : get_field( 'celeb_grp_ops_url', 'option' );
                        if ( $auto_btn_url ) :
                            ?>
                            <a href="<?php echo esc_url( $auto_btn_url ); ?>" class="button">Buy Photo Ops NOW</a>
                            <?php
                        endif;
                    endif;
                endif;
                ?>
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
            
                </div><!-- END Profile Content BLOCK -->
            </div><!-- END Guest Tab-Section Block -->
            <!-- END Guest Profile Info -->
        
        </div><!-- Profile Content - Grid Block -->  
    </div><!--- Main Profile Card/Content - Grid Container -->    
    

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
