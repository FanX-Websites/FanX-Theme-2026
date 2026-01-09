<?php 

/** Template Part: Content-Blocks Section - DELETE ME
 * @package FanXTheme2026 
 * 
 * Notes: Veiw Layout in Design Notebook 
 * - Uses classes: content-block-section, self-centered-row, content, block, small-print, 
 * - Pulls from ACF fields from Product Statuses & Links Options Page
 * 
*/
?>
<!-- Content Blocks Section ------------------------->
<div class="cb-section">      

    <!-- Content BLOCK ---------------------->
    <?php //Content Block - Repeater
        $term_id = get_queried_object_id();
        $blocks = get_field('cb', 'term_' . $term_id);
        
        if ($blocks) :
            $block_count = count($blocks);
            // Determine layout class based on block count
            $layout_class = 'content-blocks';
            if ($block_count === 1) {
                $layout_class .= ' layout-single';
            } elseif ($block_count === 2) {
                $layout_class .= ' layout-two-col';
            } elseif ($block_count === 3) {
                $layout_class .= ' layout-three-col';
            } else {
                $layout_class .= ' layout-grid';
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
                        <h2 class="cb outline">
                            <?php echo esc_html($block['title'] ?? ''); ?>
                        </h2>
                        <h3 class="cb">   
                            <?php echo esc_html($block['subtext'] ?? ''); ?>
                        </h3>
                        <p class="cb">   
                            <?php echo wp_kses_post($block['content'] ?? ''); ?>
                        </p>
                        <button>    
                        <?php //Content Block - Button
                            $button_text = $block['butt_txt'] ?? '';
                            $button_subtext = $block['butt_subtxt'] ?? '';
                            $button_link = $block['butt_url'] ?? '';
                            if ($button_text && $button_link) : ?>
                            <a href="<?php echo esc_url($button_link); ?>" class="button">
                                <span class="button-text"><?php echo esc_html($button_text); ?></span>
                                <?php if ($button_subtext) : ?>
                                    <span class="button-subtext"><?php echo esc_html($button_subtext); ?></span>
                                <?php endif; ?>
                            </a>
                            <?php endif;//END Content Block - Button ?></button>
                        <!-- Small Print / Disclaimer ---------------------->
                        <div class="small-print">
                            <?php echo wp_kses_post($block['small_print'] ?? ''); ?>
                        </div><!-- END Small Print / Disclaimer ----------------------->
                    </div><!-- END Individual Content Block -->
                    <?php
                endforeach;
                ?>
            </div><!-- END Content Blocks Container -->
        <?php endif; ?>
</div><!-- END Content Blocks Section ----------------------->


