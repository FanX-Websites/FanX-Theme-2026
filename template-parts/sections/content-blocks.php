<?php 

/** Template Part: Content-Blocks Section - DELETE ME
 * @package FanXTheme2026 
 * 
 * Notes: 
 * - Classes used: cb-section, content-blocks, layout-single, layout-two-col, layout-three-col, layout-grid, content-block, block-1, block-2, block-3, button, button-text, button-subtext, small-print
 * - Pages using this template part: Ticket-Info, Event-Info, Exhibitor-Info 
 * - CSS Wireframes in style.css, Styling in FanX.css (differs by page ^)
*/
?>
<!-- Content Blocks Section ------------------------->
<div class="cb-section">
    <div class="cb-section-inner">  

    <!-- Content BLOCK ---------------------->
    <?php //Content Block - Repeater
        $term_id = get_queried_object_id();
        $blocks = get_field('cb', 'term_' . $term_id); // ACF Repeater Field
        
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
                $layout_class .= ' ' . $page_slug . '-blocks';
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
                    $block_class = 'content-block';
                    // Add position class for styling purposes
                    $block_class .= ' block-' . ($index + 1);
                    ?>
      
                    <div class="<?php echo esc_attr($block_class); ?>">
                        <h2 class="content-block-title">
                            <?php echo wp_kses_post($block['title'] ?? ''); //Title ?> 
                        </h2>
                        <h3 class="content-block-subtext">   
                            <?php echo wp_kses_post($block['subtext'] ?? ''); //Subtext ?>
                        </h3>
                        <p class="content-block-text">   
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
                        <div class="content-block-small-print">
                            <?php echo wp_kses_post($block['small_print'] ?? ''); ?>
                        </div><!-- END Small Print / Disclaimer ----------------------->
     
                    </div><!-- END Individual Content Block -->
                    <?php
                endforeach;
                ?>
            </div><!-- END Content Blocks Container -->
        <?php endif; ?>
    </div><!-- END Content Blocks Section - Inner ---------------------->
</div><!-- END Content Blocks Section ----------------------->


