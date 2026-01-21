<?php
/**
 * Template Part: News & Updates Section
 * @package FanxTheme2026
*/
?>
<!-- News & Updates Post Layout (Post)  -->
<div class="updates-section">           
    
    <!-------------- Posts Loop --------------------->
    <div class="updates-posts-loop"> 

        <!-------------- Single Post Container --------------------->
        <div class="updates-post-container">
            <div class="updates-title"><!-- Updates Title Container -->
                <h2> Latest About <?php single_term_title(); ?> </h2>
            </div><!-- END Updates Title -->

            <!-------------- Posts Begin --------------------->
            <?php if ( have_posts() ) : ?>
                <?php $count = 0; ?>
                <?php while ( have_posts() && $count < 1 ) : the_post(); //Limit Posts ?>
                    <?php $count++; ?>

                    <!--------- Article Section - Updates ---------->
                    <article class="updates self-centered-row" 
                        id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                        <!-------------- Post Thumbnail Container--------------------->
                        <div class="updates-post-thumbnail-container">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="updates-post-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail( 'medium' ); ?>
                                    </a>
                                </div><!-- END Post Thumbnail -->
                            <?php endif; ?>
                        </div>
                        <!------------- END Post Thumbnail Container ------------>
                    

                        <!-------------- Post Content --------------------->
                        <div class="updates-post-content">
                            <header class="updates-entry-header"><!-- Post Title Header -->
                                <h3 class="updates-entry-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                            </header><!-- END Post Title Header -->
                            
                            <!-------------- Post Excerpt --------------------->
                            <div class="updates-entry-summary">
                                <?php the_excerpt(); ?>
                            </div>
                        
                            <!-------------- Call to Action Button --------------------->
                            <footer class="updates-entry-footer">
                                <a href="<?php the_permalink(); ?>">Read More</a>
                            </footer>   

                    </div><!-- END Post Content -->
                </article><!-- END News & Updates Post Layout -->
            <?php endwhile; ?>
        <!-- Posts End -->
            
            <!----------- No Posts Message -------------->
            <?php else : ?>
                <div class="updates-no-posts-message">
                    <h3>No updates Yet</h3>
                    <p>
                        <?php echo wp_kses_post(get_field('news_message', get_option('page_on_front'))); ?>
                        <?php 
                            $news_link = get_field('news_url', get_option('page_on_front'));
                            if ($news_link) {
                                $url = $news_link['url'];
                                $title = isset($news_link['title']) ? $news_link['title'] : 'sign up for our newsletter.';
                                echo ' <a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
                            }
                        ?>
                    </p>
                </div>
            <?php endif; ?>
            <!-- END No Posts Message -->

        </div><!-- END Posts Container -->  
    </div><!-- END Posts Loop -->

</div><!-- END News & Updates Section -->   
    
        