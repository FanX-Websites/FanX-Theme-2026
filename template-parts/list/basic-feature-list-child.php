<?php 
/** Template Part: Basic Features/Activities List - eXperiences
 * 
 * Feature Category Page Template Part - Child Categories ONLY
 * 
 * //NOTE: Headers are not included in this template part - Add header to template part parent div. 
 * Header Div Class: 
 * 
 */
?>
<div class="featured-guest-list-section self-centered-column">

<!-------------------------- Basic Features/Activities List --------------------->
    <div class="cat-tax grid-container" id="features"> 
        <?php 
        // Query features CPT for the current xp taxonomy or category, excluding postponed xp-status
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
        
        // Build tax_query based on whether we're on a category page
        $tax_query = array();
        
        if ( is_category() ) {
            // On a category page: get parent if this is a child category
            $current_term = get_queried_object();
            $category_id = $current_term->term_id;
            
            // If this is a child category, use the parent ID
            if ( $current_term->parent !== 0 ) {
                $category_id = $current_term->parent;
            }
            
            $tax_query[] = array(
                'taxonomy' => 'category',
                'field' => 'term_id',
                'terms' => $category_id,
            );
        } else {
            // Not on a category page: filter by xp taxonomy (child terms only)
            $xp_term_id = get_queried_object_id();
            
            // Get child xp terms
            $child_terms = get_terms( array(
                'taxonomy' => 'xp',
                'parent' => $xp_term_id,
                'fields' => 'ids',
            ) );
            
            // Include only child terms
            if ( ! empty( $child_terms ) && ! is_wp_error( $child_terms ) ) {
                $tax_query[] = array(
                    'taxonomy' => 'xp',
                    'field' => 'term_id',
                    'terms' => $child_terms,
                );
            }
        }
        
        // Always exclude postponed items
        $tax_query[] = array(
            'taxonomy' => 'xp-status',
            'field' => 'slug',
            'terms' => 'postponed',
            'operator' => 'NOT IN',
        );
        
        $args = array(
            'post_type' => 'features',
            'tax_query' => $tax_query,
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
        <!------------------- Post (Feature/Activity) Block --------------------->
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