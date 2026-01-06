<?php 

/** Template Part: Content-Blocks Section - Default 
 * @package FanXTheme2026 
 * 
 * Notes: Veiw Layout in Design Notebook 
 * - Uses classes: content-block-section, self-centered-row, content, block, small-print, 
 * - Pulls from ACF fields from Product Statuses & Links Options Page
 * 
*/
?>
<!-- Content Blocks Section ------------------------->
<div class="event-info self-centered">      
    <div class="content-blocks-sec self-centered-stretch"><!-- Section Background -->   

        <!-- Content Block 1 ---------------------->
        <div class="content-blocks block">
            <h2 class="cb outline">
            <?php //Content Block 1 - Title
                $term_id = get_queried_object_id();
                echo get_field( 'feat_con_title_1', 'term_' . $term_id );
                ?></h2>
            <h3 class="cb">   
            <?php //Content Block 1 - Subtext
                $term_id = get_queried_object_id();
                echo get_field('feat_con_subtext_1', 'term_' . $term_id);
                ?></h3>
            <p class="cb">   
            <?php //Content Block 1 - Content Text
                $term_id = get_queried_object_id();
                echo get_field( 'feat_con_content_1', 'term_' . $term_id);
                ?></p>
            <button>    
            <?php //Content Block 1 - Button
                $term_id = get_queried_object_id();
                $button_text = get_field( 'feat_con_butt_txt_1', 'term_' . $term_id );
                $button_subtext = get_field( 'feat_con_butt_subtxt_1', 'term_' . $term_id );
                $button_link = get_field( 'feat_con_butt_url_1', 'term_' . $term_id );
                if ( $button_text && $button_link ) : ?>
                <a href="<?php echo esc_url( $button_link ); ?>" class="button">
                    <span class="button-text"><?php echo esc_html( $button_text ); ?></span>
                    <?php if ( $button_subtext ) : ?>
                        <span class="button-subtext"><?php echo esc_html( $button_subtext ); ?></span>
                    <?php endif; ?>
                </a>
                <?php endif;//END Content Block 1 - Button ?></button>
        </div><!-- END Content Block 1 -------------------------->

        <!--- Content Block 2 ---------------------->
        <div class=" content-blocks block">
            <h2 class="cb outline">
            <?php //Content Block 2 - Title
                $term_id = get_queried_object_id();
                echo get_field('feat_con_title_2', 'term_' . $term_id); 
                    ?></h1>
            <h3 class="cb">
            <?php //Content Block 2 - Subtext
                $term_id = get_queried_object_id();
                echo get_field('feat_con_subtext_2', 'term_' . $term_id)
                    ?></h3>
            <p class="cb">
            <?php //Content Block 2 - Content Text
                $term_id = get_queried_object_id();
                echo get_field('feat_con_content_2', 'term_' . $term_id)
                    ?></p>
            <button>
            <?php //Content Block 1 - Button
                $term_id = get_queried_object_id();
                $button_text = get_field( 'feat_con_butt_txt_2', 'term_' . $term_id );
                $button_subtext = get_field( 'feat_con_butt_subtxt_2', 'term_' . $term_id );
                $button_link = get_field( 'feat_con_butt_url_2', 'term_' . $term_id );

                if ( $button_text && $button_link ) : ?>
                <a href="<?php echo esc_url( $button_link ); ?>" class="button">
                    <span class="button-text"><?php echo esc_html( $button_text ); ?></span>
                    <?php if ( $button_subtext ) : ?>
                        <span class="button-subtext"><?php echo esc_html( $button_subtext ); ?></span>
                    <?php endif; ?>
                </a>
                <?php endif;//END Content Block 1 - Button ?></button>
        </div><!-- END Content Block 2 ----------------------->

        <!--- Content Block 3 ---------------------->
        <div class=" content-blocks block">
            <h2 class="cb outline">
            <?php //Content Block 3 - Title
                $term_id = get_queried_object_id();
                echo get_field('feat_con_title_3', 'term_' . $term_id); 
                ?></h2>
            <h3 class="cb">
            <?php //Content Block 3 - Subtext
                $term_id = get_queried_object_id();
                echo get_field('feat_con_subtext_3', 'term_' . $term_id);
                ?></h3>
            <p class="cb">
            <?php //Content Block 3 - Content Text
                $term_id = get_queried_object_id();
                echo get_field('feat_con_content_3', 'term_' . $term_id)
                ?></p>
            <button>
            <?php //Content Block 1 - Button
                $term_id = get_queried_object_id();
                $button_text = get_field( 'feat_con_butt_txt_3', 'term_' . $term_id );
                $button_subtext = get_field( 'feat_con_butt_subtxt_3', 'term_' . $term_id );
                $button_link = get_field( 'feat_con_butt_url_3', 'term_' . $term_id );

                if ( $button_text && $button_link ) : ?>
                <a href="<?php echo esc_url( $button_link ); ?>" class="button">
                    <span class="button-text"><?php echo esc_html( $button_text ); ?></span>
                    <?php if ( $button_subtext ) : ?>
                        <span class="button-subtext"><?php echo esc_html( $button_subtext ); ?></span>
                    <?php endif; ?>
                </a>
                <?php endif;//END Content Block 1 - Button ?>
            </button>
        </div><!-- END Content Block 3 ----------------------->
    </div><!-- END Comparison Chart Section ----------------------->
        
        <!-- Small Print / Disclaimer ---------------------->
        <div class="cb-small-print">
            <?php //Small Print / Disclaimer Text
                $term_id = get_queried_object_id();
                echo get_field('feat_con_small_print', 'term_' . $term_id);
                ?>
        </div><!-- END Small Print / Disclaimer ----------------------->
    
    </div><!-- END Content Blocks Section ----------------------->
</div><!-- END Section Background -->

