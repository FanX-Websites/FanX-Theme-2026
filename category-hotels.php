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
    <div class="page-header container">
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
            'meta_key' => 'info_display_order',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
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
                    <div class="post-thumbnail no-frame">
                        <a href="<?php echo esc_url( $button_url ); ?>" target="_blank">
                            <?php the_post_thumbnail( 'medium' ); ?>
                        </a>
                        <?php if ( has_term( 'rooms-booked', 'xp-status', get_the_ID() ) ) : ?>
                            <div class="postponed-overlay">
                                <span class="postponed-text cat">ROOMS BOOKED</span>
                            </div>
                        <?php endif; ?>
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

                <!-- Subtitle -->
                <?php 
                $heafoo = get_field( 'heafoo' );
                $subtitle = isset( $heafoo['subtitle'] ) ? $heafoo['subtitle'] : '';
                if ( $subtitle ) : 
                ?>
                    <div class="entry-subtitle"> 
                        <?php echo wp_kses_post( $subtitle ); ?>
                    </div>
                <?php endif; ?>
                <!-- END Subtitle -->

                 <!-- Post Excerpt -->
                <div class="entry-summary">
                    <p><?php the_content(); ?><p>
                </div><!-- END entry-summary -->
                <!-- END Post Excerpt -->

                <!-- Read More Button/ Footer -->
                
                <footer class="entry-footer">
                    <?php if ( $button_title && $button_url && ! has_term( 'rooms-booked', 'xp-status', get_the_ID() ) ) : ?>
                        <a href="<?php echo esc_url( $button_url ); ?>" class="button" target="_blank">
                            <?php echo esc_html( $button_title ); ?>
                        </a>
                    <?php endif; ?>
                </footer><!-- END Read More Button/ Footer -->
                
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

    <!----- END Main Content Area ----------------->
    </div><!-- END cat-tax grid-container -->
    
    <?php
    if ( ! $query->have_posts() ) :
        get_template_part( 'template-parts/coming-soon' );
    endif;
    ?>
     <!--- Latest Updates ----> 
    
     <div class="container full">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
    <!--- END Latest Updates ---->

<?php
get_footer();
?>
