<?php
/**
 * Taxonomy Template: eXperiences (XP) Category/Archive Pages
 * @author FanXTheme2026
 * Default template for XP categories. 
 * //TODO: Create Sections (template-parts) w/Headers for guests, latest updates, features, events, etc. (as needed)
 */
get_header(); /** body- main-site */
?>
<!-- Category Page Body -->

    <!--------------- Page Header Container [Template Part] ----------------------->
    <div class="page-header container">
        <?php get_template_part('template-parts/page-header'); ?>
    </div><!-- END page-header Container -->
    <!------------ END Page Header Container -------------------->

    <!-------------------------- Basic Guest list --------------------->
    <div class="cat-tax grid-container"> 
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
        else :
            ?>
            <div class="no-posts-container">
                <h3>COMING SOON</h3>
            </div>
            <?php
        endif;
        ?>
        <!-- END No Posts Message -->


    </div><!-- END cat-tax grid-container -->
    <?php get_template_part( 'template-parts/profiles/smallprint' ); ?>
    <!----- END Guest List ----------------->

    <!-- Floor Maps & Room List Section --->
     <?php get_template_part('template-parts/sections/floor-maps'); ?>
    <!-- END Floor Maps & Room List Section -->  

   <!------------------- Latest News Post Block --------------------->
    <div class="container full">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
<!----------- END Latest News Post Block -->

<?php
get_footer();
?>