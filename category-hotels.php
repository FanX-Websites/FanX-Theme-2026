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
    <div class="self-centered-inside framed-1300"> 
        <?php if ( have_posts() ) : ?>
        <?php
        while ( have_posts() ) : the_post();
            ?>
        <!------------------- Post Block --------------------->
        <div class="post-block-5 block">

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <!-- -- Post Thumbnail -->
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-thumbnail">
                        <a href="<?php echo esc_url( get_field( 'butt_feat_url' ) ); ?>" target="_blank">
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

                 <!-- Post Excerpt -->
                <div class="entry-summary">
                    <?php the_excerpt(); ?>
                </div><!-- END entry-summary -->
                <!-- END Post Excerpt -->

                <!-- Read More Button/ Footer -->
                
                <footer class="entry-footer">
                    <?php
                    $button_link = get_field( 'butt_feat_url' );
                    $button_text = get_field( 'butt_feat_label' );
                    
                    if ( $button_link && $button_text ) :
                    ?>
                        <button class="read-more-button" onclick="window.open('<?php echo esc_url( $button_link ); ?>', '_blank');">
                            <?php echo esc_html( $button_text ); ?>
                        </button>
                    <?php endif; ?>
                </footer><!-- END Read More Button/ Footer -->
                
            </article>
        </div>
        <!-- END Post Block -------------------->

        <!-- No Posts Message -->
        <?php
            endwhile;
            the_posts_pagination( array(
                'prev_text' => '← Previous',
                'next_text' => 'Next →',
            ) );
        else :
            ?>
            <h3>Coming Soon</h3>
            <p></p>
            <?php
        endif;
        ?><!-- END No Posts Message --> 

    <!----- END Main Content Area ----------------->
    </div><!-- END self-centered-inside -->

<?php
get_footer();
?>
