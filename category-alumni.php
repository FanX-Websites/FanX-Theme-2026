<?php
/**
 * Template Name: Guest Category/Archive Pages 
 * @author FanXTheme2026
 * 
 * Notes: 
 * Uses classes: self-centered, self-centered-row, post-block, tax-cat,
 * //TODO: Rankings, Latest News Block, 
 */

get_header(); /** body- main-site */
?>
<!-- Category Page Body -->

    <!--------------- Page Header Container [Template Part] ----------------------->
    <div class="container">
        <?php get_template_part('template-parts/page-header'); ?>
    </div><!-- END page-header Container -->
    <!------------ END Page Header Container -------------------->

    <!-------------------------- Main Content Area --------------------->
    <div class="cat-tax grid-container">
    
        <?php
        // Query guests CPT for the current taxonomy term (works for category and guest-list archives)
        $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
        $term = get_queried_object();
        $args = array(
            'post_type' => 'guests',
            'tax_query' => array(
                array(
                    'taxonomy' => $term->taxonomy,
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                ),
            ),
            'posts_per_page' => 50,
            'paged' => $paged,
            'meta_query' => array(
                array(
                    'key' => 'info_display_order',
                    'compare' => 'EXISTS',
                    'type' => 'NUMERIC',
                ),
            ),
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
        );
        $query = new WP_Query( $args );
        
        if ( $query->have_posts() ) : ?>
        <?php
        while ( $query->have_posts() ) : $query->the_post();
            // Render post block using shared function (also used by AJAX handler)
            fanx_render_alumni_post_block();
            ?>
        <?php
            endwhile;
            wp_reset_postdata();
            
            // Add filler blocks to complete the last row dynamically
            $posts_per_row = 4; // Typical desktop column count
            $total_posts = $query->found_posts;
            $remainder = $total_posts % $posts_per_row;
            if ( $remainder > 0 ) :
                $filler_count = $posts_per_row - $remainder;
                for ( $i = 0; $i < $filler_count; $i++ ) {
                    echo '<div class="post-block block"></div>';
                }
            endif;
        endif;
        ?>

    </div><!-- END cat-tax grid-container -->
    
    <?php
    // Load More Button (if there are more posts) - MOVED OUTSIDE GRID
    if ( $query->have_posts() ) {
        $current_posts_shown = $paged * 50;
        if ( $current_posts_shown < $query->found_posts ) : ?>
            <div class="load-more-container" style="text-align: center; padding: 40px 0; width: 100%;">
                <button 
                    id="fanx-load-more-alumni" 
                    class="load-more-btn"
                    data-paged="<?php echo esc_attr( $paged ); ?>"
                    data-term-id="<?php echo esc_attr( $term->term_id ); ?>"
                    data-taxonomy="<?php echo esc_attr( $term->taxonomy ); ?>"
                    data-posts-per-page="50"
                    style="padding: 12px 30px; font-size: 16px; background: #333; color: #fff; border: none; cursor: pointer; border-radius: 4px;">
                    Load More
                </button>
                <div id="fanx-loading-alumni" class="loading-spinner" style="display: none; margin-top: 10px;">
                    <span style="display: inline-block; width: 20px; height: 20px; border: 3px solid #f3f3f3; border-top: 3px solid #333; border-radius: 50%; animation: spin 1s linear infinite;"></span>
                </div>
            </div>
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        <?php endif;
    }
    ?>
    
    <!--- No Posts/Coming Soon Message --->
    <div class="container full">
        <?php
        if ( ! $query->have_posts() ) :
            get_template_part( 'template-parts/coming-soon' );
        endif;
        ?>
    </div>
    <!--- END No Posts/Coming Soon Message-----> 

    <!----- END Main Content Area----------------->

    <!-- Load More AJAX Handler -->
    <script>
    (function() {
        const loadMoreBtn = document.getElementById('fanx-load-more-alumni');
        const loadingSpinner = document.getElementById('fanx-loading-alumni');
        const gridContainer = document.querySelector('.cat-tax.grid-container');
        
        if (!loadMoreBtn) return; // Exit if button doesn't exist
        
        loadMoreBtn.addEventListener('click', function() {
            // Get button data
            const currentPage = parseInt(loadMoreBtn.getAttribute('data-paged')) || 1;
            const nextPage = currentPage + 1;
            const termId = loadMoreBtn.getAttribute('data-term-id');
            const taxonomy = loadMoreBtn.getAttribute('data-taxonomy');
            const postsPerPage = 50;
            
            // Show loading spinner, hide button
            loadingSpinner.style.display = 'block';
            loadMoreBtn.disabled = true;
            
            // Make AJAX request
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'fanx_load_more_alumni',
                    paged: nextPage,
                    term_id: termId,
                    taxonomy: taxonomy,
                    posts_per_page: postsPerPage
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Append new posts to grid
                    gridContainer.insertAdjacentHTML('beforeend', data.data.posts_html);
                    
                    // Update button page number
                    loadMoreBtn.setAttribute('data-paged', nextPage);
                    
                    // Check if there are more posts
                    if (data.data.has_more) {
                        loadingSpinner.style.display = 'none';
                        loadMoreBtn.disabled = false;
                    } else {
                        // Hide button if no more posts
                        loadMoreBtn.style.display = 'none';
                        loadingSpinner.style.display = 'none';
                    }
                } else {
                    console.error('Load More failed:', data.data.message);
                    loadMoreBtn.disabled = false;
                    loadingSpinner.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('AJAX error:', error);
                loadMoreBtn.disabled = false;
                loadingSpinner.style.display = 'none';
            });
        });
    })();
    </script>
    <!-- END Load More AJAX Handler -->

    <!-- Small Print Section -->
    <div class="container full">
        <?php get_template_part( 'template-parts/profiles/smallprint' ); ?>
    </div>
    <!--- END Small Print Section -->

<!------------------- Latest News Post Block --------------------->
    <div class="container full">
        <?php get_template_part('template-parts/sections/updates-section'); ?>
    </div>
<!----------- END Latest News Post Block -->

<?php
get_footer();
?>