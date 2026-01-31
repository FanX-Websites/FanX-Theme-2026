<?php
/**
 * Days Taxonomy Template - 
 * @author FanXTheme2026
 * 
 * Notes: 
 * Uses classes: 
 * ACF Fields used: 
 * Post Types: Guests, Features
 */

get_header(); /** body- main-site */
?>
<!-- Category Page Body -->

    <!--------------- Page Header Container [Template Part] ----------------------->
    <div class="page-header container">
        <?php get_template_part('template-parts/page-header'); ?>
    </div><!-- END page-header Container -->
    <!------------ END Page Header Container -------------------->

    <!-------------------------- Main Content Area --------------------->
    <div class="post-grid-container"> 
        <?php
        // Query guests CPT for the current taxonomy term
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
        $term = get_queried_object();
        $args = array(
            'post_type' => 'guests',
            'tax_query' => array(
                array(
                    'taxonomy' => $term->taxonomy,
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                ),
            ),
            'paged' => $paged,
            'posts_per_page' => get_option( 'posts_per_page' ),
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

                 <!-- Autographs  -->
                    <?php 
                    $auto_price = get_field('xp')['auto_price'] ?? '';
                    if ($auto_price) : ?>
                        <div class="auto-price guest-xp">
                            <strong>Autographs:</strong> <?php echo esc_html($auto_price); ?>
                        </div>
                    <?php endif; ?><!-- END Autographs -->

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
                        echo implode( ' | ', $links );
                    } else {
                        echo 'More info soon';
                      }
                    echo '</div>';
                    ?> <!-- END Appearance Days ---> 


            </article>
        </div>
        <!-- END Post Block -------------------->

        <!-- No Posts Message -->
        <?php
            endwhile;
            wp_reset_postdata();
        else :
            ?>
            <div class="no-posts-container">
                <h3>COMING SOON</h3>
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
        wp_reset_postdata();
        ?><!-- END No Posts Message -->

    <!----- END Main Content Area----------------->
    </div><!-- END post-grid-container -->

<?php
get_footer();
?>