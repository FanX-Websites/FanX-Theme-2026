<?php
/**
 * Taxonomy Template - Blog Category Taxonomy
 */

// Hook to modify the query before it runs
add_filter( 'pre_get_posts', function( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_tax( 'blog-category' ) ) {
        $query->set( 'nopaging', true );
    }
    return $query;
} );

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

<?php
get_footer();
?>