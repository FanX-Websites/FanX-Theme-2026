<?php
/**
 * Template Name: Default Blog Post Template. 
 * @fanxtheme2027
 */

get_header();
//END Header  
 ?>

<!-- Page Header [Template Part] -->
    <div class="container full"><!-- Container for Submenu & Page Header -->
        <?php get_template_part( 'template-parts/profiles/profile-header' ); ?><!-- Page Header Template Part -->    
    </div>

<!-- Page Main Div --------------------->

<!-- Main Page Container -->
<div class="profile page"><!-- Page sizing, padding,  -->

    <!-- Main Page Card/Content - Grid Container --------------------------------------->    
    <div class="profile-card grid-container <?php echo ( has_post_thumbnail() && ! has_term( 'alumni', 'category' ) ) ? 'layout-2col' : 'layout-1col'; ?>"> 
        <?php if ( have_posts() ) :
            while ( have_posts() ) : the_post(); ?>
        
            <?php 
                if ( has_term( 'postponed', 'xp-status' ) ) : //Postponed Overlay ?>
                <div class="profile-card postponed-overlay">
                    <span class="postponed-text">Postponed</span>
                </div>
            <?php endif;?>

        <!-- Page Details - Grid Block ------------------>
        <?php if ( has_post_thumbnail() && ! has_term( 'alumni', 'category' ) ) : //Conditional - Page Image or not hides column (also hidden for alumni) ?>
            <div class="grid-block profile-details"> 
            
                    <!-- Page Image - DIV ------------->
                    <div class="profile-img">
                        <?php the_post_thumbnail(); //Thumbnail ?>      
                    </div><!-- END profile-img ------------>

            </div><!--END Grid Block Page Details -->
        <?php endif; ?>
        
        <!-- Page Content - Grid Block ------------------>
        <div class="grid-block profile-content"><!-- Post Main Content -->
            <div class="profile-content-header"><!--- Page content header --->
                <!-- Page Name, Subtitle, Subtext -->
                <div class="profile-name">
                    <h1><?php the_title(); ?></h1> <!-- Title --> 
                    <h2><?php the_field('heafoo_subtitle'); ?></h2> <!-- Subtitle --> 
                    <h3><?php the_field('heafoo_subtext'); ?></h3> <!--  Subtext -->  
                </div><!-- END Page Name & Cats -->             
            </div><!-- END Page content header --->    
                
            <!-- Post Info -->
                <div class="profile the-content-block">

                <!--- Page Content - DIV -->
                    <div class="profile the-content">
                        <?php 
                            if ( ! empty( trim( get_the_content() ) ) ) {
                                the_content(); // Display content if it exists
                            } else {
                                echo 'More info about ' . esc_html( get_the_title() ) . ' coming soon'; //More Info Message when Empty
                            }
                        ?>
                    </div><!-- END Page Content-->   
                <!-- END Page Content - DIV -->

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
                        $button_target = '_self';
                        
                        // Support ACF Advanced Link field (returns array with url/title/target)
                        if ( is_array( $url ) ) {
                            $button_target = ! empty( $url['target'] ) ? $url['target'] : '_self';
                            $url = $url['url'] ?? '';
                        } elseif ( is_string( $url ) && $url !== '' ) {
                            // Support ACF shortcodes, e.g. [acf field="my_link" link_format="url"]
                            $url = trim( do_shortcode( $url ) );

                            // If the shortcode returned a full <a> tag (default link_format="html"),
                            // pull out the href and target attributes
                            if ( stripos( $url, '<a ' ) !== false ) {
                                if ( preg_match( '/href=["\']([^"\']+)["\']/i', $url, $href_match ) ) {
                                    if ( preg_match( '/target=["\']([^"\']+)["\']/i', $url, $target_match ) ) {
                                        $button_target = $target_match[1];
                                    }
                                    $url = $href_match[1];
                                } else {
                                    $url = '';
                                }
                            }
                        }
                        
                        // Display the button if title and URL exist
                        if( $title && $url ):
                            ?>
                            <a href="<?php echo esc_url( $url ); ?>" target="<?php echo esc_attr($button_target); ?>" class="button">
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
            
                </div><!-- END Page Content BLOCK -->
            </div><!-- END PostSection Block -->
            <!-- END Post Info -->
        
        </div><!-- Page Content - Grid Block -->  
    </div><!--- Main Page Card/Content - Grid Container -->    
    

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

    
</div><!-- END Main Page Container --------------------->

            <?php
            endwhile; //Post Loop End - while
                endif; //Post Loop End - if 
            ?>


<?php get_footer(); //Footer ?>
