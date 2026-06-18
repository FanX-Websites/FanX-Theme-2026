<?php
/**
 * Template Part Name: Group Photo Op List
 * @author FanXTheme2026
 * 
 * //TODO: Buttons: Profile | Purchase  Buttons  
 * 
 */
?>
<div class="group-photo-ops section self-centered" id="group-ops"><!-- Group Photo Ops Section -->
    <div class="section-header"><!-- Section Header -->
        <h2>Group Photo Ops</h2>
    </div> 
    <div class="cat-tax grid-container"> 
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
                // Get button URL from ACF options page
                $button_url = get_field( 'celeb_grp_ops_url', 'option' );
                ?>
                
            <!-- -- Post Thumbnail -->
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-thumbnail">
                        <a href="<?php echo esc_url( $button_url ); ?>" target="_blank">
                            <?php the_post_thumbnail( 'medium' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <!-- END Post Thumbnail -->
                
                <!-- Post Header -->
                <header class="entry-header">
                    <h2 class="entry-title">
                        <a href="<?php echo esc_url( $button_url ); ?>" target="_blank"><?php the_title(); ?></a>
                    </h2>
                </header>
                <!-- END Post Header -->

                <!-- Photo Op Price -->
                <div class="guest-op-info guest-xp">
                    <?php 
                    $op_price = get_field('xp')['op_price'] ?? ''; 
                    if ( $op_price ) {
                        echo esc_html($op_price);
                    }
                    ?>
                </div><!-- END Photo Op Price <------------------------------------------->

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
                        }                    echo '</div>';
                        ?> <!-- END Appearance Days ---> 

                            <!-- Read More Button/ Footer -->
                    
                    <footer class="entry-footer">
                        <?php if ( $button_url ) : ?>
                            <a href="<?php echo esc_url( $button_url ); ?>" class="button" target="_blank">
                                Buy Ops Now
                            </a>
                        <?php endif; ?>
                    </footer><!-- END Read More Button/ Footer -->

            </article>        </div>
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
        wp_reset_postdata();
        ?><!-- END No Posts Message -->

        </div><!-- END Profile Main Div --------------------->
    </div><!-- END cat-tax grid-container -->
</div><!-- END Group Photo Ops Section -->
