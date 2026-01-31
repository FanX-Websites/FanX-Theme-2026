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
    <div class="post-grid-container"> 
        <?php if ( have_posts() ) : ?>
        <?php
        while ( have_posts() ) : the_post();
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

                 <!-- Post Excerpt -->
                <div class="entry-summary">
                    <?php the_excerpt(); ?>
                </div><!-- END entry-summary -->
                <!-- END Post Excerpt -->

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
        else :
            ?>
            <div class="no-posts-container">
                <h3>Coming Soon</h3>
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
        ?><!-- END No Posts Message -->

    <!----- END Main Content Area ----------------->
    </div><!-- END post-grid-container -->

<?php
get_footer();
?>
