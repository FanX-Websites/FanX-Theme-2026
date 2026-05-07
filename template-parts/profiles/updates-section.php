<?php
/**
 * Template Part: News & Updates Section
 * @package FanxTheme2026
*/
?>
<!-- News & Updates Post Layout (Post)  -->
<?php 
    // Check if ACF field has content before rendering
    $info_group = get_field('info');
    $featured_updates = isset($info_group['feat_updates']) ? $info_group['feat_updates'] : null;
    
    // Handle both single post object and array of post objects
    $posts_to_display = array();
    if ($featured_updates) {
        if (is_array($featured_updates) && !empty($featured_updates)) {
            // Array of post objects
            $posts_to_display = $featured_updates;
        } elseif (is_numeric($featured_updates)) {
            // Single post object (post ID)
            $posts_to_display = array($featured_updates);
        }
    }
    
    // Only render if there are posts to display
    if (empty($posts_to_display)) {
        return;
    }
?>
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
                // Loop through featured updates
            ?>
            
            <?php if ( !empty($posts_to_display) ) : ?>
                <?php foreach ( $posts_to_display as $post_item ) : 
                    // Handle both post object and post ID
                    $post_id = is_object($post_item) ? $post_item->ID : $post_item;
                    $post = get_post($post_id);
                    
                    if (!$post) continue;
                ?>

                    <!--------- Article Section - Updates ---------->
                    <article class="updates self-centered-row" 
                        id="post-<?php echo esc_attr($post->ID); ?>" 
                            <?php echo get_post_class('', $post->ID); ?>>

                        <!-------------- Post Thumbnail Container--------------------->
                        <div class="updates-post-thumbnail-container">
                            <?php if ( has_post_thumbnail($post->ID) ) : ?>
                                <div class="updates-post-thumbnail">
                                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                                        <?php echo get_the_post_thumbnail($post->ID, 'medium'); ?>
                                    </a>
                                </div><!-- END Post Thumbnail -->
                            <?php endif; ?>
                        </div>
                        <!------------- END Post Thumbnail Container ------------>
                    

                        <!-------------- Post Content --------------------->
                        <div class="updates-post-content">
                            <header class="updates-entry-title"><!----------------- Post Title Header -->
                                <h4>
                                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                                        <?php echo esc_html($post->post_title); ?></a>
                                </h4>
                            </header><!-- END Post Title Header -->
                            
                            <!-------------- Post Excerpt --------------------->
                            <div class="updates-entry-summary">
                                <?php echo wp_trim_words(apply_filters('the_excerpt', $post->post_excerpt ?: $post->post_content), 55); ?>
                            </div>
                        
                           <!-- Read More Button/ Footer -->
                <footer class="entry-footer"><!----------------- Read More Button/ Footer -->
                <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" class="button">Read More</a>
                </footer>
                <!-- END Read More Button/ Footer -->          

                    </div><!-- END Post Content -->
                </article><!-- END News & Updates Post Layout -->
                <?php endforeach; ?>
            <?php endif; ?>
            <!-- Posts End -->

        </div><!-- END Posts Container -->  
    </div><!-- END Posts Loop -->

</div><!-- END News & Updates Section -->   