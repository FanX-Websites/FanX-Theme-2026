<?php
/**
 * Template Part: News & Updates Section
 * @package FanxTheme2026
*/
?>
<!-- News & Updates Post Layout (Post)  -->
<div class="updates-section grid-bkgrnd "><!-- News & Updates Section Container -->     
    
    <!-------------- Posts Loop --------------------->
    <div class="updates-posts-loop"> 

        <!-------------- Single Post Container --------------------->
        <div class="updates-post self-centered">
            <div class="updates-section-title"><!----------------------------- Updates Section Title -->
                <h3>Announcements & Updates</h3>
            </div><!-- END Updates Title -->

            <!-------------- Posts Begin --------------------->
            <?php 
                // Query only blog posts (post type 'post') from the current category/taxonomy
                // Exclude posts that use community-events term
                $term = get_queried_object();
                $args = array(
                    'post_type' => 'post',
                    'posts_per_page' => 3,
                    'tax_query' => array(
                        'relation' => 'AND',
                        array(
                            'taxonomy' => $term->taxonomy,
                            'field' => 'term_id',
                            'terms' => $term->term_id,
                        ),
                        array(
                            'taxonomy' => 'blog',
                            'field' => 'slug',
                            'terms' => 'community-events',
                            'operator' => 'NOT IN',
                        ),
                    ),
                );
                $updates_query = new WP_Query( $args );
            ?>
            
            <?php if ( $updates_query->have_posts() ) : ?>
                <?php while ( $updates_query->have_posts() ) : $updates_query->the_post(); ?>

                    <!--------- Article Section - Updates ---------->
                    <article class="updates self-centered-row" 
                        id="post-<?php the_ID(); ?>" 
                            <?php post_class(); ?>>

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
                            <header class="updates-entry-title"><!----------------- Post Title Header -->
                                <h4>
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h4>
                            </header><!-- END Post Title Header -->
                            
                            <!-------------- Post Excerpt --------------------->
                            <div class="updates-entry-summary">
                                <?php echo wp_trim_words(apply_filters('the_excerpt', get_the_excerpt()), 55); ?>
                            </div>
                        
                           <!-- Read More Button/ Footer -->
                <footer class="entry-footer">
                <a href="<?php the_permalink(); ?>" class="button">Read More</a>
                </footer>
                <!-- END Read More Button/ Footer -->          

                    </div><!-- END Post Content -->
                </article><!-- END News & Updates Post Layout -->
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        <!-- Posts End -->
            
            <!----------- No Posts Message -------------->
            <?php else : ?>
                <div class="updates-no-posts-message">
                   No updates Yet
                        <?php echo wp_kses_post(get_field('news_message', get_option('page_on_front')) ?? ''); ?>
                        <?php 
                            $news_link = get_field('news_url', get_option('page_on_front'));
                            if ($news_link) {
                                $url = $news_link['url'];
                                $title = isset($news_link['title']) ? $news_link['title'] : 'sign up for our newsletter.';
                                echo ' <a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
                            }
                        ?>
                </div>
            <?php endif; ?>
            <!-- END No Posts Message -->

        </div><!-- END Posts Container -->  
    </div><!-- END Posts Loop -->

</div><!-- END News & Updates Section -->   
    
        