<?php
/**
 * Coming Soon Template Part
 * 
 * Displays "Coming Soon" notice when no posts are found.
 * Optionally displays newsletter/news message if configured in ACF options.
 * 
 * //TODO: Coming Soon Mode: Connect to ACF Field - Overrides ALL Cat/Tax Page Content except the header with Coming Soon message. 
 * 
 * @package FanXTheme2026
 */
?>
<div class="no-posts-container ">
    <!--- Coming Soon Message -->
    <span>More info about <?php echo single_term_title('', false); ?></span>
    <h3>COMING SOON</h3>
    <?php
        $news_link = get_field('news_url', 'option');
        $news_message = get_field('news_message', 'option') ?? '';
        if ($news_message) :
    ?>
        <p>
            <?php
                if ($news_link && isset($news_link['url'])) {
                    echo '<a href="' . esc_url($news_link['url']) . '">' . wp_kses_post($news_message) . '</a>';
                } else {
                    echo wp_kses_post($news_message);
                }
            ?>
        </p>
    <?php endif; ?>
</div>
