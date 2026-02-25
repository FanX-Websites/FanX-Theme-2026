<?php
/**
 * Venue Taxonomy Template
 * 
 * 
 */

get_header(); //Body - Main Site
?>

<!--------------- Page Header Container [Template Part] ----------------------->
    <div class="page-header container">
        <?php get_template_part('template-parts/page-header'); ?> 
    </div>
<!------------ END Page Header Container -------------------->

<!-------------------------- ADDRESS MAP Template Part --------------------->
    <div class="section-full-width">
        <?php get_template_part('template-parts/sections/address-map'); ?>
    </div>
<!---------------------------END ADDRESS MAP Template Part --------------------->

<!-------------------------- Main Content Area --------------------->
    <div class="post-grid-container"> 
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
        else :
            ?>
            <div class="no-posts-container">
                <h3> EVENTS COMING SOON</h3>
                <p>
                    <?php 
                        $news_link = get_field('news_url', 'option');
                        $news_message = get_field('news_message', 'option');
                        if ($news_link && isset($news_link['url'])) {
                            echo '<a href="' . esc_url($news_link['url']) . '">' . wp_kses_post($news_message) . '</a>';
                        } else {
                            echo wp_kses_post($news_message);
                        }
                    ?>
                </p>
            </div>
            <?php
        endif;
        ?><!-- END No Posts Message -->

    <!----- END Main Content Area----------------->
    </div><!-- END post-grid-container -->

<?php
get_footer();
?>

