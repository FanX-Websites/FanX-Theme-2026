<?php
/**
 * Template Name: The Backup Archives 
 */
?>
<!Doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?> 
    <?php get_header(); ?>
    
    <main class="site-main backup-archives">
        <section class="archive-section">
            <h1 class="archive-title">Backup Archives</h1>
            <div class="archive-list">
                <?php
                $args = array(
                    'type' => 'monthly',
                    'limit' => '',
                    'format' => 'html',
                    'before' => '<li>',
                    'after' => '</li>',
                    'show_post_count' => true,
                );
                wp_get_archives($args);
                ?>
            </div>
        </section>
    </main>
    <?php get_footer(); ?>
    <?php wp_footer(); ?>
</body>
</html>     