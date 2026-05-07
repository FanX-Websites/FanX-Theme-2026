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

<style>

/** FP BLOCK - UPDATES FEED ----------------- */
.updates-feed-fp-block{  
    padding: 20px;
    margin: 0;
    font-family: inherit;
    overflow: scroll; 
}

.updates-layout{
    padding: 3%;
}

.updates-feed-post-container{
    border-bottom: solid 1px var(--color_acc_lght);
    border-top: solid 1px var(--color_acc_lght);
    border-radius: 0px; 
    padding: 20px;
}
article.updates-feed{
    margin-bottom: 1%; 
}
/** Post Title */
article.updates-feed > .updates-feed-post-content h3 a{
    font-size: clamp(1rem, 1.5vw, 1.25rem);
    font-weight: 400;
    text-align: center;
    color: var(--color_prim_lght);
    text-decoration: none;
}

div.updates-feed-entry-summary{
    font-size: 1em;
    line-height: 1.5em;
    color: var(--color_lght);
    font-weight: 100;
    margin: 0;
    padding: 1%;
  }

/* .updates-feed-entry-summary{
    color: var(--color_lght); 
    margin: 0; 
    padding: 10px; 
    font-size: 1.15em;
} */

.updates-feed-entry-footer{
    display: flex;
    justify-content: center;
    margin: 0;
    padding: 0;
}

.updates-feed-entry-footer .button{
    margin-top: 10px;
    padding: 1px;
    font-size: 1rem;
    max-width: 150px;  
}

/** The Updates Div Title */
h3.updates-feed{
    color: var(--color_fnt_wht);
    margin-bottom: 10px;
    text-align: center; 
    text-transform: uppercase;
}

/** Restricted Height/Scroll for Large Desktop */
@media (min-width: 1300px) {
    .updates-feed.container{
    display: flex;
    align-items: start; 
    max-height: 55vh;
}

}
/** Restricted height/scroll for smaller desktop/tablets */
@media (min-width: 1045px) and (max-width: 1300px) {
    .updates-feed.container{
    display: flex;
    align-items: start; 
    max-height: 40vh;
}

}
/** END FP BLOCK - UPDATES FEED ----------------- */

</style>