<?php 

// This is the Block for The Announcements & Updates Category

?> 
    <div class="updates-feed-fp-block"><!-- Updates Block -->
        <div class="updates-layout"><!-- Updates Inner Div -->
               
       <h3 class="updates-feed">
            Event Updates:
       </h3> 
        <!-------------- Posts Loop --------------------->
        <div class="updates-feed-posts-loop"> 

            <!-------------- Single Post Container --------------------->
            <div class="updates-feed-post-container">

                <!-------------- Posts Begin --------------------->
                <?php 
                $args = array(
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'blog',
                            'field' => 'slug',
                            'terms' => 'event-updates-announcements',
                        ),
                    ),
                    'posts_per_page' => 3,
                );

                $query = new WP_Query( $args );

                if ( $query->have_posts() ) : 
                    while ( $query->have_posts() ) : $query->the_post(); 
                ?>

                    <!--------- Article Section - Updates ---------->
                    <article class="updates-feed" 
                        id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                        <!-------------- Post Content --------------------->
                        <div class="updates-feed-post-content">
                            <!-- Post Title Header -->
                                <h3><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h3>
                            <!-- END Post Title Header -->
                            
                            <!-------------- Post Excerpt --------------------->
                            <div class="updates-feed-entry-summary">
                                <?php echo wp_kses_post( get_the_excerpt() ?? '' ); ?>
                            </div>
                        
                            <!-------------- Call to Action Button --------------------->
                            <footer class="updates-feed-entry-footer">
                            <a href="<?php the_permalink(); ?>" class="button">Read More</a>
                            </footer>   
                        </div><!-- END Post Content -->
                        <hr>
                    </article><!-- END News & Updates Post Layout -->
                    
                <?php 
                    endwhile;
                    wp_reset_postdata();
                ?>
                
                <!----------- No Posts Message -------------->
                <?php else : ?>
                    <div class="no-posts-message"> 
                        <h3>More Updates Soon</h3>
                        <p>
                            <!-- Newsletter Sign-up Message -->
                            <?php echo wp_kses_post(get_field('news_message') ?? ''); ?>
                            <?php 
                                $news_link = get_field('news_url');
                                if ($news_link) {
                                    $url = $news_link['url'];
                                    $title = isset($news_link['title']) ? $news_link['title'] : 'sign up for our newsletter.';
                                    echo ' <a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
                                }
                            ?><!-- END Newsletter Sign-up Message -->
                        </p>
                    </div>
                <?php endif; ?>
                <!-- END No Posts Message -->

            </div><!-- END Posts Container -->  
        </div><!-- END Posts Loop -->

            </div><!-- END Updates Layout column -->
    </div><!-- END Updates Block -->