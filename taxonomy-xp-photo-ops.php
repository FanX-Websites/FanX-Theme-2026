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
    
    <div class="cat-tax grid-container"> 
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

            <article id="post-<?php the_ID(); ?>" class="fill">
                
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
                            if ( $term->slug === 'photo-ops-coming-soon' ) { //XP Status - Coming Soon Triggers Available Soon
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
                                } else {
                                    echo 'More Info Available Soon*'; //NO PRICE - Available Soon
                                }
                            ?> 
                        </div>
                    <?php endif; ?>
                </div>
                <!-- END Photo Ops <------------------------------------------->
                
                <!-- END Photo Ops -->

                <!-- Photo Op Days/Links -->
                    <?php 
                    $days_cats = get_the_terms( get_the_ID(), 'days' );
                    echo '<div class="days guest-xp">';
                    
                    if ( ! empty( $days_cats ) && ! is_wp_error( $days_cats ) ) {
                        //Sort by Day Name for correct Appearance Order
                        $order = ['thursday' => 1, 'friday' => 2, 'saturday' => 3, 'sunday' => 4];
                        usort($days_cats, fn($a, $b) => ($order[$a->slug] ?? 99) - ($order[$b->slug] ?? 99));
                        
                        // Map day slugs to ACF field names for photo ops URLs
                        $day_url_map = [
                            'thursday' => 'celeb_op_sun_url',
                            'friday'   => 'celeb_op_fri_url',
                            'saturday' => 'celeb_op_sat_url',
                            'sunday'   => 'celeb_op_sun_url',
                        ];
                        
                        $links = array();
                        foreach ( $days_cats as $cat ) {
                            // Get the URL field for this day
                            $field_name = $day_url_map[$cat->slug] ?? null;
                            $day_url = '';
                            
                            if ( $field_name ) {
                                $day_link = get_field($field_name, 'option');
                                $day_url = is_array($day_link) ? ($day_link['url'] ?? '') : $day_link;
                            }
                            
                            // Build the link with URL if available, otherwise just the text
                            if ( $day_url ) {
                                $links[] = '<a href="' . esc_url($day_url) . '">' . esc_html($cat->name) . '</a>';
                            } else {
                                $links[] = esc_html($cat->name);
                            }
                        }
                        echo implode( ' | ', $links ) . '*';
                    } else {
                        echo 'More info soon';
                      }
                    echo '</div>';
                    ?> 
                <!-- END Photo Op Days/Links ---> 

                <!-- Footer Buttons Button Group -->
                <footer class="entry-footer">
                    <div class="button-group">
                        <!-- View Profile Button -->
                        <a href="<?php the_permalink(); ?>" class="button button-left">
                            View Profile
                        </a>
                        
                        <!-- Buy Photo Ops Button -->
                        <?php if ( $has_photo_ops && $op_price ) : ?>
                            <?php 
                                // Get Photo Ops URL from options page
                                $celeb_op_link = get_field('celeb_op_fri_url', 'option'); 
                                $photo_ops_url = is_array($celeb_op_link) ? ($celeb_op_link['url'] ?? '') : $celeb_op_link;
                                if ( $photo_ops_url ) {
                                    $button_text = $is_coming_soon ? 'Coming Soon' : 'BUY NOW';
                                    ?>
                                    <a href="<?php echo esc_url($photo_ops_url); ?>" class="button button-right" target="_blank">
                                        <div class="small-print">Photo Ops:</div>
                                        <?php echo $button_text; ?>
                                    </a>
                                    <?php
                                }
                            ?>
                        <?php endif; ?>
                    </div><!-- END Button Group -->
                </footer>
                <!-- END Footer Buttons -->

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
                <h3>COMING SOON</h3>
            </div>
            <?php
        endif;
        ?>
        <!-- END No Posts Message -->

    </div><!-- END Profile Main Div --------------------->

    <!----- END Main Content Area----------------->
    </div><!-- END cat-tax grid-container -->

    <!-- Small Print Section -->
    <div class="container">
        <?php get_template_part( 'template-parts/profiles/smallprint' ); ?>
    </div>
    <!--- END Small Print Section -->
    
<!------------------- Latest News Post Block --------------------->
    <div class="container full">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
<!----------- END Latest News Post Block -->

<?php
get_footer();
?>