<?php 

/** Template Part: Table-Charts Section
 * @package FanXTheme2026 
 * 
 * Notes: 
 * - Classes used: 
 * - Pages using this template part: Exhibitor Info 
 * - CSS Wireframes shared with content-blocks in style.css, Styling in FanX.css
 * //TODO: Use as replacement for Comparison & Pricing Charts - Ticketing, Exhibitor Packages, etc.
*/
?>

<?php
// Check if any ACF fields have content
$term_id = get_queried_object_id();
$section_title = get_field( 'tc_section_title', 'term_' . $term_id );
$section_sub = get_field( 'tc_section_sub', 'term_' . $term_id );
$section_txt = get_field( 'tc_section_txt', 'term_' . $term_id );
$blocks = get_field('tc', 'term_' . $term_id );

// Hide entire section if no content exists
if ( !$section_title && !$section_sub && !$section_txt && !$blocks ) {
    return;
}
?>

<!-- Table Charts Section ------------------------->
<div class="tc-section">
    <div class="tc-section-inner">  
     
    <!-- Table Chart Section Header -->
        <h2 class="tc-section-title">
        <?php echo wp_kses_post( $section_title ); ?></h2>
        <h3 class="tc-section-sub">
        <?php echo wp_kses_post( $section_sub ); ?></h3>
        <p class="tc-section-txt">
        <?php echo wp_kses_post( $section_txt ); ?></p>

    <!-- END Table Chart Section Header -->

    <!-- Table Chart BLOCK ---------------------->
    <?php //Content Block - Repeater
        if ($blocks) :
            $block_count = count($blocks);
            // Determine layout class based on block count
            $layout_class = 'content-blocks'; // container class > .content-blocks
            
            // Add context-based class for page-specific styling
            // Priority: args > queried object (for archives) > current page
            $page_slug = '';
            
            if (isset($args['page_class'])) {
                // If passed as argument to template part
                $page_slug = $args['page_class'];
            } else {
                // First check if we're on a taxonomy/category archive
                $queried_object = get_queried_object();
                if ($queried_object && isset($queried_object->taxonomy)) {
                    // We're on a taxonomy/category archive
                    $page_slug = $queried_object->slug;
                } elseif ($queried_object && isset($queried_object->post_name)) {
                    // It's a page/post
                    $page_slug = $queried_object->post_name;
                } else {
                    // Fallback to current page
                    $current_page_id = get_the_ID();
                    if ($current_page_id && is_page()) {
                        $page_slug = get_post_field('post_name', $current_page_id);
                    }
                }
            }
            
            if ($page_slug) {
                $layout_class .= ' ' . $page_slug . '-blocks'; //Template Specific Class > .{page-slug}-blocks
            }
            
            if ($block_count === 1) {
                $layout_class .= ' layout-single'; // single block layout > .layout-single
            } elseif ($block_count === 2) {
                $layout_class .= ' layout-two-col'; // two column layout > .layout-two-col
            } elseif ($block_count === 3) {
                $layout_class .= ' layout-three-col'; // three column layout > .layout-three-col
            } else {
                $layout_class .= ' layout-grid'; // grid layout for more than three blocks > .layout-grid
            }
            ?>
            <div class="<?php echo esc_attr($layout_class); ?>">
                <?php
                foreach ($blocks as $index => $block) :
                    $block_class = 'table-chart';
                    // Add position class for styling purposes
                    $block_class .= ' chart-' . ($index + 1);
                    ?>
      
                    <div class="<?php echo esc_attr($block_class); ?>">
                        <h2 class="table-chart-title">
                            <?php echo wp_kses_post($block['title'] ?? ''); //Title ?> 
                        </h2>
                        <h3 class="table-chart-subtext">   
                            <?php echo wp_kses_post($block['subtext'] ?? ''); //Subtext ?>
                        </h3>
                        <p class="table-chart-text">   
                            <?php echo wp_kses_post($block['content'] ?? ''); //Content ?>
                        </p>
                        <?php
                        // Content Block - Button
                        $button_text    = $block['butt_txt'] ?? '';    // Button Text > .butt_txt
                        $button_subtext = $block['butt_subtxt'] ?? ''; // Button Subtext > .butt_subtxt
                        $button_link    = $block['butt_url'] ?? '';    // Button Link > .butt_url
                        $button_class   = 'button';
                        
                        if (!$button_text || !$button_link) {
                            $button_class .= ' button--hidden';
                        }
                        
                        if ($button_subtext) {
                            $button_class .= ' button--has-subtext';
                        }
                        ?>
                        <a href="<?php echo esc_url($button_link); ?>" class="<?php echo esc_attr($button_class); ?>">
                            <span class="button-text"><?php echo esc_html($button_text); ?></span>
                            <span class="button-subtext"><?php echo esc_html($button_subtext); ?></span>
                        </a>
                        <!-- Small Print / Disclaimer ---------------------->
                        <div class="table-chart-small-print">
                            <?php echo wp_kses_post($block['small_print'] ?? ''); ?>
                        </div><!-- END Small Print / Disclaimer ----------------------->
     
                    </div><!-- END Individual Table Chart -->
                    <?php
                endforeach;
                ?>
            </div><!-- END Table Charts Container -->
        <?php endif; ?>
    </div><!-- END Table Charts Section - Inner ---------------------->
</div><!-- END Table Charts Section ----------------------->