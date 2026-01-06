<?php
/**
 * Template Name: Basic Category Archive
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
    <div class="self-centered-inside"> 
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

                <!-- Post Excerpt -->
                <div class="entry-summary">
                    <?php the_excerpt(); ?>
                </div>
                <!-- END Post Excerpt -->

                <!-- Read More Button/ Footer -->
                <footer class="entry-footer">
                <button class="read-more-button" onclick="window.location.href='<?php the_permalink(); ?>';">Read More</button>
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
            <h3>COMING SOON</h3>
            <p></p>
            <?php
        endif;
        ?><!-- END No Posts Message --> 

    <!----- END Main Content Area ----------------->
    </div><!-- END self-centered-inside -->

<?php
get_footer();
?>