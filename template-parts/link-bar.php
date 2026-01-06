<?php
/**
 * Template Name: Link Bar 
 * Link bar on the bottom of the screen with product links and zendesk help bubble
 * @package FanxTheme2026
 * 
 * 
 */
?>

<!-- Link Bar ---------------------------------> 
<div class="link-bar">
    <div class="self-centered-scrunch">      
        <!-- TICKETS BLOCK ----------------------->   
        <div class="tickets-block block">
            <?php 
                $link = get_field( 'tkt_url', 'option' );
                $link_text = get_field( 'tkt_stat', 'option' );                  

                if ( $link ) : ?>
                    <a href="<?php echo esc_url( $link['url'] ); ?>" 
                    target="<?php echo esc_attr( $link['target'] ); ?>">
                        <?php echo esc_html( $link_text ); ?>
                    </a>
            <?php endif; ?>
        </div><!--END TICKETS BLOCK  ------------------->

        <!--- PHOTO OPS BLOCK --------------------->
        <div class="ops-block block">
            <?php 
                $link = get_field( 'celeb_op_fri_url', 'option' );
                    $link_text = get_field( 'celeb_ops_stat', 'option' );                  

                if ( $link ) : ?>
                    <a href="<?php echo esc_url( $link['url'] ); ?>" 
                    target="<?php echo esc_attr( $link['target'] ); ?>">
                        <?php echo esc_html( $link_text ); ?>
                    </a>
            <?php endif; ?>
        </div><!--END PHOTO OPS BLOCK-----------------> 
        
    <!-- Mobile Icons Block --------------------->
        <div class="mobile-icons block">
            <?php // Photo Ops Icon
                $link = get_field( 'celeb_op_fri_url', 'option' );
                $icon = get_field( 'celeb_ops_ico', 'option' );

                if ( $link && $icon) : ?>
                    <a href="<?php echo esc_url( $link['url'] ); ?>" 
                    target="<?php echo esc_attr( $link['target'] ); ?>">
                        <?php echo $icon; ?>
                    </a>
            <?php endif; ?>

            <?php // Tickets Icon
                $link = get_field( 'tkt_url', 'option' );
                $icon = get_field( 'tkt_ico', 'option' );

                if ( $link && $icon) : ?>
                    <a href="<?php echo esc_url( $link['url'] ); ?>" 
                    target="<?php echo esc_attr( $link['target'] ); ?>">
                        <?php echo $icon; ?>
                    </a>
            <?php endif; ?>
        </div><!--- End mobile-icons block -->
    </div><!-- END Self-Centered Scrunch ----------------->



    </div><!--END self-centered-scrunch -->
</div><!--- END Link Bar -->


<style>


</style>
