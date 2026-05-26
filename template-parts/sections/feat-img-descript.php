<?php 
/** 
 * Template Part: Featured Image + Description Section
 * 
 * Displays featured image (left column) + term description (right column)
 * Layout: 1fr 2fr ratio using layout-1-2col grid
 * 
 * //NOTE: This template triggers on any template that uses the description field
 * 
 * //FIXME: The Description field in Cat/Tax Pages aren't working 
 * 
 * Usage: get_template_part( 'template-parts/sections/feat-img-descript' );
 */

$term = get_queried_object();

if ( ! $term ) {
    return;
}

$featured_image = get_field( 'heafoo_feat_img', $term );  // Pass term object directly instead of string
$description = term_description( $term->term_id );

// Only display if description exists
if ( ! $description ) {
    return;
}

// Get page/term slug for template-specific styling
$page_slug = '';
$queried_object = get_queried_object();

if ( $queried_object && isset( $queried_object->taxonomy ) ) {
    // We're on a taxonomy/category archive
    $page_slug = $queried_object->slug;
} elseif ( $queried_object && isset( $queried_object->post_name ) ) {
    // It's a page/post
    $page_slug = $queried_object->post_name;
} else {
    // Fallback to current page
    $current_page_id = get_the_ID();
    if ( $current_page_id && is_page() ) {
        $page_slug = get_post_field( 'post_name', $current_page_id );
    }
}

$section_class = '';
if ( $page_slug ) {
    $section_class = $page_slug . '-blocks'; // Template Specific Class > .{page-slug}-blocks
}
?>
<div class="feat-desc-section<?php echo esc_attr( $section_class ); ?>">
    <div class="feat-desc grid-container framed-1300 <?php echo ! empty( $featured_image ) ? 'layout-1-2col' : 'layout-single'; ?>">
        
        <!-- Featured Image: 1fr (narrow left) - Only show if image exists -->
        <?php if ( ! empty( $featured_image ) ) : ?>
        <div class="feat-img grid-block outlined"">
            <div class="feat-img img-container">
                <?php 
                $img_url = $featured_image['url'] ?? '';
                $img_alt = $featured_image['alt'] ?? 'Featured image for ' . $term->name;
                
                if ( $img_url ) {
                    echo '<img src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $img_alt ) . '" />';
                }
                ?>
            </div><!-- END img-container -->
        </div><!-- END Featured Image Grid Block -->
        <?php endif; ?>

        <!-- Description: 2fr (wide right) or full width if no image -->
        <div class="desc grid-block outlined gradi">
            <div class="feat-desc term-description">
                <?php echo wp_kses_post( $description ); ?>
            </div><!-- END term-description -->
        </div><!-- END Description Grid Block -->

    </div><!-- END feat-desc grid-container -->
</div><!-- END feat-desc-section -->
