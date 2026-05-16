<?php
/**
 * Template Name: Blog Category Page
 * @author FanXTheme2026
 * 
 * Notes: 
 * Uses classes: self-centered, self-centered-row, post-block, tax-cat,
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
        // Query partners CPT filtered by current category
        $term = get_queried_object();
        $args = array(
            'post_type' => 'partner',
            'nopaging' => true,
            'tax_query' => array(
                array(
                    'taxonomy' => $term->taxonomy,
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                ),
            ),
        );
        $query = new WP_Query( $args );
        if ( $query->have_posts() ) : ?>
        <?php
        while ( $query->have_posts() ) : $query->the_post();
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
        else :
            ?>
            <div class="no-posts-container">
                <h3>Coming Soon</h3>
                <p>
                    <?php 
                        $news_link = get_field('news_url', 'option');
                        $news_message = get_field('news_message', 'option') ?? '';
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

    <!----- END Main Content Area ----------------->
    </div><!-- END cat-tax grid-container -->

<!------------------- Latest News Post Block --------------------->
    <div class="container full">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
<!----------- END Latest News Post Block -->

<?php
get_footer();
?>
