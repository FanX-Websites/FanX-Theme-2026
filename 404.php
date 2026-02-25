<?php
/**
 * Template Name: 404 Page Not Found
 * Description: Handles 404 errors with custom theme styling
 */

get_header(); 
// END Header ?>
    
<!-------- Page Header Container [Template Part] ----------->
<div class="page-header container">
    <div class="page-title-section">
        <h1 class="page-title">Page Not Found</h1>
    </div>
</div>
<!-------- END Page Header Container ----------->


<!-------- Main Page Div ----------->
<div class="page full">
    <div class="self-centered-column framed-900 min-80">
        <!-- 404 Content -->
        <div class="error-404-content">
            <h2 class="error-code">404</h2>
            <p class="error-message">Sorry, the page you are looking for could not be found.</p>
            
            <p class="error-description">
                The page you requested might have been removed, had its name changed, or is temporarily unavailable.
            </p>

            <!-- Search Form -->
            <div class="error-search">
                <?php
                if ( function_exists( 'get_search_form' ) ) {
                    get_search_form();
                }
                ?>
            </div>

            <!-- Back to home link -->
            <div class="error-actions">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">
                    Back to Home
                </a>
            </div>
        </div><!-- END 404 Content -->
    </div><!-- END Centered Column Div -->
</div><!------- END Main Page Div ----->


<?php get_footer(); ?>
