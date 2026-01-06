<?php
/**
 * Template Name: Default Page Template - Basic Layout 
 */

get_header(); 
//END Header ?>
    
<!--------------- Page Header Container [Template Part] ----------------------->
<div class="page-header container">
    <?php get_template_part('template-parts/page-header'); ?>
</div>
<!------------ END Page Header Container -------------------->


<!------------- Main Page Div ----------------------->
<div class="page full">
    <div class="self-centered-column framed-900 min-80">
    <!-- Entry Content -->
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>    
            <?php 
            $content = get_the_content();
            if ( ! empty( trim( $content ) ) ) : ?>
                <!-- Page Content -->
                <div class="basic-page-content">
                    <?php the_content(); ?>
                </div><!-- END Basic Page Content-->    
            <?php else : ?>
                <div class="no-posts">
                    <h2>INFO COMING SOON</h2>
                </div>
            <?php endif; ?>
        <?php endwhile; ?> 
    <?php else : ?>
        <div class="no-posts">
            <h2>INFO COMING SOON</h2>
        </div>
    <?php endif; ?>
    </div><!-- END Centered Column Div -->
</div><!------- End Main Page Div --------------------->


<?php get_footer(); ?>
