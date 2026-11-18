<?php 
/** 
 * Template Part: Basic Layout Section
 * 
 * Full Page Template that pulls from the native Wordpress content editor
 * 
 * Usage: get_template_part( 'template-parts/sections/basic-layout' );
 */
?>

<?php if ( have_posts() ) : ?>
    <?php while ( have_posts() ) : the_post(); ?>
        <?php
        $content = get_the_content();
        if ( ! empty( trim( $content ) ) ) : ?>

            <!------------- Basic Content Div ----------------------->
            <div class="section full basic-content">
                <div class="self-centered-column framed-900 min-80">

                    <!-- Page Content -->
                    <div class="basic-page-content">
                        
                    </div><!-- END Basic Page Content-->

                </div><!-- END Centered Column Div -->
            </div><!------- End Basic Content Div --------------------->

        <?php endif; ?>
    <?php endwhile; ?>
<?php endif; ?>