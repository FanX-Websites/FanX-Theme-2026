<?php
/**
 * Template Name: Blog Category Page
 * @author FanXTheme2026
 * 
 * Notes: 
 * Uses classes: self-centered, self-centered-row, post-block, tax-cat,
 * //FIXME: This layout needs love
 */

get_header(); /** body- main-site */
?>

    <!--------------- Page Header Container [Template Part] ----------------------->
    <div class="container">
        <?php get_template_part('template-parts/page-header'); ?>
    </div><!-- END page-header Container -->
    <!------------ END Page Header Container -------------------->

    <!-------------------------- Main Content Area --------------------->
    <div class="cat-tax grid-container"> 
        <?php
        // Unlimited posts for category/taxonomy pages
        if ( is_category() || is_tax() ) {
            global $wp_query;
            $wp_query->set( 'nopaging', true ); 
            $wp_query->query( $wp_query->query_vars );
        }
        if ( have_posts() ) : ?>
        <?php
        while ( have_posts() ) : the_post();
            ?>
        <!------------------- Post Block --------------------->
        <div class="post-block block">

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <!-- -- Post Thumbnail -->
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-thumbnail">
                        <a href="<?php echo esc_url( get_field( 'butt_feat_url' ) ); ?>" target="_blank">
                            <?php the_post_thumbnail( 'medium' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <!-- END Post Thumbnail -->

                <!-- Read More Button/ Footer -->
                
                <footer class="entry-footer">
                    <?php
                    $button_link = get_field( 'butt_feat_url' );
                    $button_text = get_field( 'butt_feat_label' );
                    
                    if ( $button_link && $button_text ) :
                    ?>
                        <button class="read-more-button" onclick="window.open('<?php echo esc_url( $button_link ); ?>', '_blank');">
                            <?php echo esc_html( $button_text ); ?>
                        </button>
                    <?php endif; ?>
                </footer><!-- END Read More Button/ Footer -->
                
            </article>
        </div>
        <!-- END Post Block -------------------->

        <!-- No Posts Message -->
        <?php
            endwhile;
            wp_reset_postdata();
            
            // Add filler blocks to complete the last row dynamically
            $posts_per_row = 4; // Typical desktop column count
            $total_posts = $wp_query->found_posts;
            $remainder = $total_posts % $posts_per_row;
            if ( $remainder > 0 ) :
                $filler_count = $posts_per_row - $remainder;
                for ( $i = 0; $i < $filler_count; $i++ ) {
                    echo '<div class="post-block block"></div>';
                }
            endif;
        endif;
        ?><!-- END No Posts Message -->

    <!----- END Main Content Area ----------------->
    </div><!-- END cat-tax grid-container -->

    <?php
    if ( ! $wp_query->have_posts() ) :
        get_template_part( 'template-parts/coming-soon' );
    endif;
    ?>

<?php
get_footer();
?>

