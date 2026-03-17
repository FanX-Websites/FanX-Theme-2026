<?php
/**
 * Template Name: Guest Category/Archive Pages 
 * @author FanXTheme2026
 * 
 * //TODO: Buttons: Profile | Purchase  Buttons  
 * 
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
        // Query Products CPT for the current taxonomy term
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
        $term = get_queried_object();
        $args = array(
            'post_type' => 'products', //Post Type - Products
            'tax_query' => array(
                array(
                    'taxonomy' => $term->taxonomy,
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                ),
            ),
            'paged' => $paged,
            'posts_per_page' => -1, //UNLIMITED POSTS 
        );
        $query = new WP_Query( $args );
        if ( $query->have_posts() ) : ?>
        <?php
        while ( $query->have_posts() ) : $query->the_post();
        ?>

        <!------------------- Post Block --------------------->
        <div class="post-block block">

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <?php
                // Get button data for use throughout the post
                $button_url = '';
                $button_title = '';
                if ( have_rows( 'button' ) ) :
                    while ( have_rows( 'button' ) ) :
                        the_row();
                        $button_url = get_sub_field( 'url' );
                        $button_title = get_sub_field( 'title' );
                        break; // Only get first button
                    endwhile;
                endif;
                ?>
                
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

                <!-- Photo Op Price -->
                <div class="guest-op-info guest-xp">
                    <?php echo the_field('price'); ?>
                </div><!-- END Photo Op Price <------------------------------------------->

               <!-- Appearance Days -->
                    <?php 
                    $days_cats = get_the_terms( get_the_ID(), 'days' );
                    echo '<div class="days guest-xp">';
                    
                    //Sort by Day Name for correct Appearance Order
                    $order = ['thursday' => 1, 'friday' => 2, 'saturday' => 3, 'sunday' => 4];
                    usort($days_cats, fn($a, $b) => ($order[$a->slug] ?? 99) - ($order[$b->slug] ?? 99));
                    
                    if ( ! empty( $days_cats ) && ! is_wp_error( $days_cats ) ) {
                        $links = array();
                        foreach ( $days_cats as $cat ) {
                            $links[] = esc_html( $cat->name );
                        }
                        echo implode( ' | ', $links ) . '*';
                    } else {
                        echo 'More info soon';
                      }
                    echo '</div>';
                    ?> <!-- END Appearance Days ---> 

                        <!-- Read More Button/ Footer -->
                
                <footer class="entry-footer">
                    <?php if ( $button_title && $button_url ) : ?>
                        <a href="<?php echo esc_url( $button_url ); ?>" class="button" target="_blank">
                            <?php echo esc_html( $button_title ); ?>
                        </a>
                    <?php endif; ?>
                </footer><!-- END Read More Button/ Footer -->

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
        wp_reset_postdata();
        ?><!-- END No Posts Message -->

    </div><!-- END Profile Main Div --------------------->
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

    <!----- END Main Content Area----------------->
    </div><!-- END post-grid-container -->

<?php
get_footer();
?>