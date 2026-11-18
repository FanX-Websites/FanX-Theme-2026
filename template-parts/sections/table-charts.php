<?php 

/** Template Part: Table-Charts Section
 * @package FanXTheme2026 
 * 
 * Notes: 
 * - Classes used: 
 * - Pages using this template part: Exhibitor Info 
 * - CSS Wireframes shared with table-charts in style.css, Styling in FanX.css
 * //TODO: Use as replacement for Comparison & Pricing Charts - Ticketing, Exhibitor Packages, etc.
*/
?>

<?php
// Check if any ACF fields have content
$queried_object = get_queried_object();
$field_key = '';

// Determine correct ACF field key based on object type
if ($queried_object && isset($queried_object->taxonomy)) {
    // It's a term/taxonomy
    $field_key = 'term_' . $queried_object->term_id;
} else {
    // It's a post/page
    $field_key = get_the_ID();
}

$section_title = get_field( 'tc_section_title', $field_key );
$section_sub = get_field( 'tc_section_sub', $field_key );
$section_txt = get_field( 'tc_section_txt', $field_key );
$blocks = get_field('tc', $field_key );

// Hide entire section if no content exists
if ( !$section_title && !$section_sub && !$section_txt && !$blocks ) {
    return;
}
?>

<!-- Table Charts Section ------------------------->
<div class="tc-section" id="table-charts">
    <div class="tc-section-inner">  
     
    <!-- Table Chart Section HEADER -------->
        <h2 class="tc-section-title">
        <?php echo wp_kses_post( $section_title ?? '' ); ?></h2>
        <h3 class="tc-section-sub">
        <?php echo wp_kses_post( $section_sub ?? '' ); ?></h3>
        <p class="tc-section-txt">
        <?php echo wp_kses_post( $section_txt ?? '' ); ?></p>

    <!-- END Table Chart Section Header -->

    <!-- Table Chart BLOCK ---------------------->
    <?php //Table Chart - Repeater
     // Check if blocks exist and have any content
        $has_content = false;
        if ($blocks) {
            foreach ($blocks as $block) {
                if (!empty($block['title']) || !empty($block['subtext']) || !empty($block['content']) || !empty($block['list']) || !empty($block['butt_txt']) || !empty($block['small_print'])) {
                    $has_content = true;
                    break;
                }
            }
        }
        
        if ($has_content) :
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
                $layout_class .= ' ' . $page_slug . '-charts'; //Template Specific Class > .{page-slug}-blocks
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
                        // Table Chart - LIST (nested repeater: list > item)
                            $list_items = $block['list'] ?? [];
                            if (!empty($list_items)) : ?>
                                <ul class="table-chart-list">
                                    <?php foreach ($list_items as $list_item) :
                                        if (empty($list_item['item'])) continue; // skip empty rows
                                    ?>
                                        <li class="list-item">
                                            <?php echo wp_kses_post($list_item['item']); ?>
                                            <?php if (!empty($list_item['details'])) : ?>
                                                <span class="list-item-details"><?php echo wp_kses_post($list_item['details']); ?></span> 
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        
                        <!-- END Table Chart - LIST -->



                        <?php
                        // Table Chart - Button
                        $button_text    = $block['butt_txt'] ?? '';    // Button Text > .butt_txt
                        $button_subtext = $block['butt_subtxt'] ?? ''; // Button Subtext > .butt_subtxt
                        $button_link    = $block['butt_url'] ?? '';    // Button Link > .butt_url
                        $button_target  = '_self';
                        $button_class   = 'button';

                        // Support ACF Advanced Link field (returns array with url/title/target)
                        if ( is_array( $button_link ) ) {
                            $button_target = ! empty( $button_link['target'] ) ? $button_link['target'] : '_self';
                            $button_link   = $button_link['url'] ?? '';
                        } elseif ( is_string( $button_link ) && $button_link !== '' ) {
                            // Support ACF shortcodes, e.g. [acf field="my_link" link_format="url"]
                            $button_link = trim( do_shortcode( $button_link ) );

                            // If the shortcode returned a full <a> tag (default link_format="html"),
                            // pull out the href and target attributes
                            if ( stripos( $button_link, '<a ' ) !== false ) {
                                if ( preg_match( '/href=["\']([^"\']+)["\']/i', $button_link, $href_match ) ) {
                                    if ( preg_match( '/target=["\']([^"\']+)["\']/i', $button_link, $target_match ) ) {
                                        $button_target = $target_match[1];
                                    }
                                    $button_link = $href_match[1];
                                } else {
                                    $button_link = '';
                                }
                            }
                        }
                        
                        if (!$button_text || !$button_link) {
                            $button_class .= ' button--hidden';
                        }
                        
                        if ($button_subtext) {
                            $button_class .= ' button--has-subtext';
                        }
                        ?>
                        <a href="<?php echo esc_url($button_link); ?>" target="<?php echo esc_attr($button_target); ?>" class="<?php echo esc_attr($button_class); ?>">
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