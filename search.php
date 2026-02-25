<?php
/**
 * Template Name: Search Results
 * Description: Displays search results for user queries
 */

get_header(); 
// END Header ?>

<!-------- Page Header Container [Template Part] ----------->
<div class="page-header container">
    <div class="page-title-section">
        <h1 class="page-title">Search Results</h1>
        <p class="page-subtitle">
            <?php 
            /* translators: %s is the search query */
            printf( esc_html__( 'Results for: %s', 'fanxtheme' ), '<strong>' . esc_html( get_search_query() ) . '</strong>' ); 
            ?>
        </p>
    </div>    
    <!-- Search Bar -->
    <div class="page-search-form" style="display: flex; align-items: center; justify-content: center; color: white; width: 100%; margin: 20px 0; padding: 0 20px; box-sizing: border-box;">
        <?php get_search_form(); ?>
    </div></div>
<!-------- END Page Header Container ----------->

<!-------- Main Page Div ----------->
<div class="page full">
    <div class="self-centered-column framed-900 min-80">
        
        <?php
        if ( have_posts() ) {
            echo '<div class="search-results-list">';
            
            while ( have_posts() ) {
                the_post();
                ?>
                <article class="search-result-item">
                    <h3>
                        <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: var(--color_fnt_wht);">
                            <?php the_title(); ?>
                        </a>
                    </h3>
                    
                    <div class="result-meta small-print">
                        <span class="result-date">
                            <?php echo esc_html( get_the_date() ); ?>
                        </span>
                        <?php if ( get_post_type() === 'post' ) { ?>
                            <span class="result-category">
                                <?php the_category( ', ' ); ?>
                            </span>
                        <?php } ?>
                    </div>
                    
                    <div class="result-excerpt" style="margin: 15px 0; padding: 0;">
                        <?php the_excerpt(); ?>
                    </div>
                    
                    <a href="<?php the_permalink(); ?>" class="button" style="width: auto; padding: 1.5% 3%; margin: 10px 0;">
                        <?php esc_html_e( 'Read More', 'fanxtheme' ); ?>
                    </a>
                </article>
                <?php
            }
            
            echo '</div>'; // END search-results-list
            
            // Pagination
            ?>
            <div style="display: flex; justify-content: center; width: 100%; margin: 30px 0;">
                <?php
                the_posts_pagination( array(
                    'prev_text' => esc_html__( '← Previous', 'fanxtheme' ),
                    'next_text' => esc_html__( 'Next →', 'fanxtheme' ),
                ) );
                ?>
            </div>
            <?php
            
        } else {
            // No results found
            ?>
            <div class="search-no-results">
                <h2><?php esc_html_e( 'No Results Found', 'fanxtheme' ); ?></h2>
                
                <p><?php esc_html_e( 'Sorry, no posts matched your search criteria.', 'fanxtheme' ); ?></p>
                
                <p><?php esc_html_e( 'Try using different keywords or explore our categories below.', 'fanxtheme' ); ?></p>
                
                <!-- Search Form -->
                <div class="search-form-wrapper" style="margin: 20px 0;">
                    <?php get_search_form(); ?>
                </div>
            </div>
            <?php
        }
        ?>
        
    </div><!-- END Centered Column Div -->
</div><!------- END Main Page Div ----->

<?php
get_footer();
?>