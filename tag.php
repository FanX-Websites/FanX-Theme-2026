<?php
/**
 * Template Name: Guest eXperience Status Taxonomy Archive
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php if ( have_posts() ) : ?>

            <header class="page-header">
                <h1 class="page-title"><?php single_term_title(); ?></h1>
                <?php
                $term_description = term_description();
                if ( ! empty( $term_description ) ) :
                    echo '<div class="taxonomy-description">' . $term_description . '</div>';
                endif;
                ?>
            </header>

            <?php
            while ( have_posts() ) : the_post();
                ?>
                
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="post-thumbnail">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'medium' ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <header class="entry-header">
                        <h2 class="entry-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                    </header>
                    
                    <div class="entry-summary">
                        <?php the_excerpt(); ?>
                    </div>
                    
                    <footer class="entry-footer">
                        <a href="<?php the_permalink(); ?>">Read More</a>
                    </footer>
                    
                </article>

                <?php
            endwhile;

            the_posts_pagination( array(
                'prev_text' => '← Previous',
                'next_text' => 'Next →',
            ) );

        else :
            ?>
            <p>No posts found.</p>
            <?php
        endif;
        ?>

    </main>
</div>

<?php
get_footer();
?>