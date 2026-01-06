<?php 

// This is the Block for The Announcements & Updates Category

?> 


    <div class="updates-block"><!-- Updates Block -->
            <div class="updates-layout"><!-- Updates Inner Div -->
               
    <!-------------- Posts Loop --------------------->
    <div class="ufeed-posts-loop"> 

        <!-------------- Single Post Container --------------------->
        <div class="ufeed-post-container">

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
                        'posts_per_page' => 1,
                    );

                    $query = new WP_Query( $args );

                    if ( $query->have_posts() ) : 
                        while ( $query->have_posts() ) : $query->the_post(); 
                    ?>

                <!--------- Article Section - Updates ---------->
                <article class="updates self-centered-row" 
                    id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <!-------------- Post Thumbnail Container--------------------->
                    <div class="ufeed-post-thumbnail-container">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="ufeed-post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'medium' ); ?>
                                </a>
                            </div><!-- END Post Thumbnail -->
                        <?php endif; ?>
                    </div>
                    <!------------- END Post Thumbnail Container ------------>
                

                    <!-------------- Post Content --------------------->
                    <div class="ufeed-post-content">
                        <header class="ufeed-entry-header"><!-- Post Title Header -->
                            <h3 class="ufeed-entry-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                        </header><!-- END Post Title Header -->
                        
                        <!-------------- Post Excerpt --------------------->
                        <div class="ufeed-entry-summary">
                            <?php the_excerpt(); ?>
                        </div>
                    
                        <!-------------- Call to Action Button --------------------->
                        <footer class="ufeed-entry-footer">
                            <a href="<?php the_permalink(); ?>">Read More</a>
                        </footer>   

                    </div><!-- END Post Content -->
                </article><!-- END News & Updates Post Layout -->
            <?php 
                endwhile;
                wp_reset_postdata();
            ?>
            
            <!----------- No Posts Message -------------->
            <?php else : ?>
                <div class="ufeed-no-posts-message">
                    <h3>No updates available</h3>
                    <p>Check back soon for the latest updates.</p>
                </div>
            <?php endif; ?>
            <!-- END No Posts Message -->

        </div><!-- END Posts Container -->  
    </div><!-- END Posts Loop -->

            </div><!-- END Updates Layout column -->
    </div><!-- END Updates Block -->



<style>
.updates-block{ /** The Block size & positioning in Parent Div*/
    width: 60%; 
    height: 35%;
    top: 50%;
    left: 0;
    position: absolute;

} 
.updates-layout{/*Inner Div*/
    width: 100%; 
    height: 100%;
    padding: 0; 
    margin: 0;  
    object-fit: contain; 
    color: var(--color_fnt_wht); 
    background-color: var(--color_drk);


}
.ufeed-header{/** Inner Div Header */
    text-align: center; 
    align-content: center; 
    top: 0;
    margin: 0; 
    padding: 2%; 
    width: 100%; 
    height: 15%; 
}
.ufeed-header h1{
    font-size: 2em;
    font-weight: 900;
    font-family: sans-serif;
    color: white; 
    margin: 0; 
    padding: 0;
}



</style>