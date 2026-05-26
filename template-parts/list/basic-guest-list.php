<?php 
/** Template Part: Basic Guest List - eXperiences
 * 
 * Guest Posts with Featured Guests Header 
 * 
 */
?>
<div class="featured-guest-list-section self-centered-column">
<!----- Featured Guest List Header ---------->
    <div class="feat-guest-header">
        <h2> Featured Guests</h2>
    </div><!---- END Featured Guest List Header ---------->
<!---- END Featured Guest List Header ---------->

<!-------------------------- Basic Guest list --------------------->
    <div class="cat-tax grid-container" id="guests"> 
        <?php 
        // Query guests CPT for the current xp taxonomy, excluding postponed xp-status
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
        $args = array(
            'post_type' => 'guests',
            'tax_query' => array(
                array(
                    'taxonomy' => 'xp', //Filter by current XP taxonomy term
                    'field' => 'term_id',
                    'terms' => get_queried_object_id(),
                ),
                array(
                    'taxonomy' => 'xp-status',
                    'field' => 'slug',
                    'terms' => 'postponed', //Excluded Terms 
                    'operator' => 'NOT IN',
                ),
            ),
            'nopaging' => true,
            'meta_key' => 'info_display_order', 
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
        );
        $query = new WP_Query( $args );
        if ( $query->have_posts() ) : ?>
        <?php
        while ( $query->have_posts() ) : $query->the_post();
            ?>
        <!------------------- Post (Guest) Block --------------------->
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

                <!-- Fandom Tags -->
                <div class="fandom-tags">
                    <?php
                    $fandoms = get_the_terms( get_the_ID(), 'fandoms' );
                    if ( $fandoms && ! is_wp_error( $fandoms ) ) {
                        echo '<div class="tags-list">';
                        $tags = array();
                        foreach ( $fandoms as $fandom ) {
                            $tags[] = '<span class="fandom-tag">' . esc_html( $fandom->name ) . '</span>';
                        }
                        echo implode( ' | ', $tags );
                        echo '</div>';
                    }
                    ?>
                </div>
                <!-- END Fandom Tags -->

            </article>
        </div>
        <!-- END Post Block -------------------->

          <!-- No Posts Message  & filler posts -->
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
        ?>
        <!-- END No Posts Message -->


    </div><!-- END cat-tax grid-container -->
    
    <?php
    if ( ! $query->have_posts() ) :
        get_template_part( 'template-parts/coming-soon' );
    endif;
    ?>
    <?php get_template_part( 'template-parts/profiles/smallprint' ); ?>
    <!----- END Guest List ----------------->
</div><!----- END Featured Guest List Section ---------->