<?php
/**
 * Template Name: Basic Category Archive
 * @author FanXTheme2026
 * 
 * Notes: 
 */

get_header(); /** body- main-site */
?>

    <!--------------- Page Header Container [Template Part] ----------------------->
    <div class="page-header container">
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
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'medium' ); ?>
                        </a>
                        <?php if ( has_term( 'postponed', 'xp-status' ) ) : ?>
                            <div class="postponed-overlay">
                                <span class="postponed-text">Postponed</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <!-- END Post Thumbnail -->
                
                <!-- Post Header -->
                <header class="entry-header">
                    <h2 class="entry-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                </header>
                <!-- END Post Header -->

                <!-- Post Excerpt -->
                <div class="entry-summary">
                    <?php the_excerpt(); ?>
                </div>
                <!-- END Post Excerpt -->

                <!-- Read More Button/ Footer -->
                <footer class="entry-footer">
                <a href="<?php the_permalink(); ?>" class="button">Read More</a>
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

    <!----- END Main Content Area----------------->
    </div><!-- END cat-tax grid-container -->

    <?php
    if ( ! $wp_query->have_posts() ) :
        get_template_part( 'template-parts/coming-soon' );
    endif;
    ?>

<!------------------- Latest News Post Block --------------------->
    <div class="container full">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
<!----------- END Latest News Post Block -->

<?php
get_footer();
?>