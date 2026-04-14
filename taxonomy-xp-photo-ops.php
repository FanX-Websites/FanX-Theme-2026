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
        // Query guests CPT for the current taxonomy term, excluding postponed xp-status
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
                array(
                    'taxonomy' => 'xp-status',
                    'field' => 'slug',
                    'terms' => 'postponed',
                    'operator' => 'NOT IN',
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

                <!-- Photo Ops -->
                    <!-- Photo Ops -------------------------------------------------------->
                <div class="guest-op-info guest-xp">
                    <?php 
                    $op_price = get_field('xp')['op_price'] ?? ''; //Price
                    $op_url = get_field('xp')['op_url'] ?? ''; //Leap Link
                    $xp_terms = get_the_terms( get_the_ID(), 'xp' ); //eXperienece Category
                    $xp_status_terms = get_the_terms( get_the_ID(), 'xp-status' ); //XP Status 
                    $has_photo_ops = false;
                    $is_coming_soon = false;
                    
                    // Photo Op XP Category Trigger
                    if ( $xp_terms && ! is_wp_error( $xp_terms ) ) {
                        foreach ( $xp_terms as $term ) {
                            if ( $term->slug === 'photo-ops' ) {
                                $has_photo_ops = true;
                                break;
                            }
                        }
                    }
                    
                    // Check for coming soon status
                    if ( $xp_status_terms && ! is_wp_error( $xp_status_terms ) ) {
                        foreach ( $xp_status_terms as $term ) {
                            if ( $term->slug === 'photo-ops-coming-soon' ) { //XP Status - Coming Soon Trigger
                                $is_coming_soon = true;
                                break;
                            }
                        }
                    }
                    
                    // Photo Op Status Messages
                    if ( $has_photo_ops ) : ?>
                        <div class="guest-ops-price">
                            <strong>Photo Ops:</strong> 
                            <?php 
                                if ( $op_price ) { //Price
                                    echo esc_html($op_price);
                                    if ( $is_coming_soon ) {
                                        echo ' - Coming Soon'; //COMING SOON
                                    } else { 
                                        echo ' - <a href="' . esc_url($op_url) . '">Buy Photo Ops NOW**</a>'; //BUY NOW
                                    }
                                } else {
                                    echo 'More Info Coming Soon*'; //NO PRICE - Coming Soon
                                }
                            ?> 
                        </div>
                    <?php endif; ?>
                </div>
                <!-- END Photo Ops <------------------------------------------->
                
                <!-- END Photo Ops -->

                <!-- Appearance Days -->
                    <?php 
                    $days_cats = get_the_terms( get_the_ID(), 'days' );
                    echo '<div class="days guest-xp">';
                    
                    if ( ! empty( $days_cats ) && ! is_wp_error( $days_cats ) ) {
                        //Sort by Day Name for correct Appearance Order
                        $order = ['thursday' => 1, 'friday' => 2, 'saturday' => 3, 'sunday' => 4];
                        usort($days_cats, fn($a, $b) => ($order[$a->slug] ?? 99) - ($order[$b->slug] ?? 99));
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
    <?php get_template_part( 'template-parts/profiles/smallprint' ); ?>
    <!----- END Main Content Area----------------->
    </div><!-- END post-grid-container -->

<?php
get_footer();
?>