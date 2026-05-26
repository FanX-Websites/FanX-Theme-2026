<?php
/**
 * Venue Taxonomy Template
 * 
 * 
 */

get_header(); //Body - Main Site
?>

<!--------------- Page Header Container [Template Part] ----------------------->
    <div class="container">
        <?php get_template_part('template-parts/page-header'); ?> 
    </div>
<!------------ END Page Header Container -------------------->

<!-------------------------- ADDRESS MAP Template Part --------------------->
    <div class="section-full-width">
        <?php get_template_part('template-parts/sections/address-map'); ?>
    </div>
<!---------------------------END ADDRESS MAP Template Part --------------------->

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
            $total_posts = $query->found_posts;
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
    if ( ! $query->have_posts() ) :
        get_template_part( 'template-parts/coming-soon' );
    endif;
    ?>

<?php
get_footer();
?>

