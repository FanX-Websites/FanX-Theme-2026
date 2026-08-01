<?php
/**
 * Template Name: Bottom Link Bar 
 * Link bar on the bottom of the screen with product links and zendesk help bubble
 * @package FanxTheme2026
 * 
 */
?>

<!-- Link Bar ---------------------------------> 
<div class="link-bar">
    <div class="self-centered">      
        <!-- TICKETS BLOCK ----------------------->   
        <?php 
            $link = get_field( 'tkt_url', 'option' ) ?: array( 'url' => '#', 'target' => '' );
            $link_text = get_field( 'tkt_act', 'option' ) ?: 'Tickets';
        ?>
                <a href="<?php echo esc_url( $link['url'] ); ?>" 
                target="<?php echo esc_attr( $link['target'] ); ?>">
                    <div class="tickets-block block half"><!-- Tickets Block Container -->
                        <div class="tickets-block inner-block fill"><!-- Tickets Block Inner Container -->
                            <?php echo esc_html( $link_text ); ?>
                        </div><!-- END Tickets Block Inner Container -->
                    </div><!--END TICKETS BLOCK  ------------------->
            </a>
        
        <!--- PHOTO OPS BLOCK --------------------->
        <?php 
            $link = get_field( 'celeb_op_fri_url', 'option' ) ?: array( 'url' => '#', 'target' => '' );
            $link_text = get_field( 'celeb_ops_act', 'option' ) ?: 'Photo Ops';
        ?>
                <a href="<?php echo esc_url( $link['url'] ); ?>" 
                target="<?php echo esc_attr( $link['target'] ); ?>">
                    <div class="ops-block block half"><!-- Photo Ops Block Container -->
                        <div class="ops-block inner-block fill"><!-- Photo Ops Block Inner Container -->
                            <?php echo esc_html( $link_text ); ?>
                        </div><!-- END Photo Ops Block Inner Container -->
                </div><!--END PHOTO OPS BLOCK  ------------------->
                </a>
        
    <!-- Mobile Icons Block --------------------->
        <div class="mobile-icons two-thirds">

            <?php // Photo Ops Icon
                $link = get_field( 'celeb_op_fri_url', 'option' ) ?: array( 'url' => '#', 'target' => '' );
                $icon = get_field( 'celeb_ops_ico', 'option' ) ?: '';
            ?>
                    <a href="<?php echo esc_url( $link['url'] ); ?>" 
                    target="<?php echo esc_attr( $link['target'] ); ?>">
                        <?php echo $icon; ?>
                    </a>

            <?php // Tickets Icon
                $link = get_field( 'tkt_url', 'option' ) ?: array( 'url' => '#', 'target' => '' );
                $icon = get_field( 'tkt_ico', 'option' ) ?: '';
            ?>
                    <a href="<?php echo esc_url( $link['url'] ); ?>" 
                    target="<?php echo esc_attr( $link['target'] ); ?>">
                        <?php echo $icon; ?>
                    </a>
            <?php //App Store Download Page Icon 
                $link = array('url' => '/app', 'target'=>'');
                $icon = get_field('app_ico', 'option') ?: '';            
            ?>
                <a href="<?php echo esc_url( $link ['url']); ?>"
                target="<?php echo esc_attr( $link['target']); ?>">
                    <?php echo $icon; ?> 
                </a> 
        </div><!--- End mobile-icons block -->
    </div><!-- END Self-Centered Scrunch ----------------->
</div><!--- END Link Bar -->

