<?php 
// Template for Posts Loop  

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
        <h5 class="ctax-list">
            <?php 
            $fandoms = get_the_term_list($post->ID, 'fandom', '', ', ', '');
            $days = get_the_term_list($post->ID, 'day', '', ', ', '');
            $experiences = get_the_term_list($post->ID, 'experience', '', ', ', '');
            $terms = array_filter([$fandoms, $days, $experiences]);
            echo implode(', ', $terms);
            ?>
        </h5>
    </header>
    
    <div class="entry-summary">
        <?php the_excerpt(); ?>
    </div>
    
    <footer class="entry-footer">
        <a href="<?php the_permalink(); ?>">Read More</a>
    </footer>
</article>