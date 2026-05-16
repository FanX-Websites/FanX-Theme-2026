<?php 
/** Template Part: Multi-post Gallery 
 * 
 * Templates used in: Default Profiles (single.php)
 */
?> 

<div class="multi-post-gallery-section">
<?php
$post_ids = get_field('gal_multi_post_ids');

if( $post_ids ) :  //IF Gallery links to any Posts - THEN display gallery - ELSE skip entire section
?> 
<!-- Multi-Post Gallery TITLE --->
    <div class= "multi-post-gallery title">
        <?php echo get_field('gal_multi_post_title'); ?>
    </div>

<!-- Multi-Post Gallery Subtitle --->
    <div class= "multi-post-gallery subtitle">
        <?php echo  get_field('gal_multi_post_sub'); ?>
    </div>

<!-- Multi-Post Gallery Wrapper --->
    <?php
    // Get the first category for this post for block-specific styling (e.g., 'hotels-post')
    $categories = get_the_category();
    if ( $categories ) {
        $category = $categories[0];
        $block_class = $category->slug . '-post';
    } else {
        $block_class = 'default-post'; // fallback
    }
    ?>
    <div class="multi-post-gallery-wrapper <?php echo $block_class; ?>">
        <!-- Multi-Post Gallery Content -->
        <?php
        // Determine layout based on number of posts
        $post_count = count( (array)$post_ids );
        $layout_class = 'layout-4col'; // Default to 4 columns
        
        if ( $post_count === 1 ) {
            $layout_class = 'layout-1col';
        } elseif ( $post_count === 2 ) {
            $layout_class = 'layout-2col';
        } elseif ( $post_count === 3 ) {
            $layout_class = 'layout-3col';
        }
        ?>
        <div class="grid-container multi-post-gallery <?php echo $layout_class; ?>">
            <?php 
            foreach( (array)$post_ids as $post_id ) {
                // Determine the link URL based on post type
                $link_url = get_permalink( $post_id ); // default
                
                // For partners CPT, use first button URL if available
                if ( in_array( get_post_type( $post_id ), array( 'partner' ) ) ) {
                    $button_url = '';
                    if ( have_rows( 'button', $post_id ) ) {
                        while ( have_rows( 'button', $post_id ) ) {
                            the_row();
                            $button_url = get_sub_field( 'url' );
                            break; // Only get first button
                        }
                    }
                    if ( $button_url ) {
                        $link_url = $button_url;
                    }
                }
            ?>
            <div class="grid-block gallery-post">
                <a href="<?php echo esc_url( $link_url ); ?>">
                    <?php if( has_post_thumbnail( $post_id ) ) : ?>
                        <div class="gallery-post-img">
                            <?php echo get_the_post_thumbnail( $post_id, 'medium' ); ?>
                        </div>
                    <?php endif; ?>
                    <div class="gallery-post-title">
                        <?php echo esc_html( get_the_title( $post_id ) ); ?>
                    </div>
                </a>
            </div>
            <?php
        }
        ?>
        </div><!-- END Multi-Post Gallery Content -->
    </div><!-- END Multi-Post Gallery Wrapper -->
</div><!-- END Multi-Post Gallery Section -->
<?php 
endif;