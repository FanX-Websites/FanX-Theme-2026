<?php 
/**
 * Template Part: Guest Appearance Days 
 * @author FanXTheme2026
 */
?>

<div class="appearance-info block">
    <div class="appear-block">
    <?php 
        $days_cats = get_the_terms( get_the_ID(), 'days' );
        
        if ( ! empty( $days_cats ) && ! is_wp_error( $days_cats ) ) {
        //Sort by Day Name for correct Appearance Order
            $order = ['thursday' => 1, 'friday' => 2, 'saturday' => 3, 'sunday' => 4];
                usort($days_cats, fn($a, $b) => ($order[$a->slug] ?? 99) - ($order[$b->slug] ?? 99));
                echo '<div class="days guest-xp">'; //days guest-xp
                echo '<strong>Appearing:</strong> ';
            $links = array();

        foreach ( $days_cats as $cat ) {
                $links[] = '<a href="' . esc_url( get_term_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a>';
            }
            echo implode( ' | ', $links ) . '*';
            echo '<div class="small-print">
        <p>*Choose a day above to see what else is happening!</p> 
    </div><!-- END Appearance smallpring --->'; //small-print
            echo '</div><!-- END days guest-xp -->';
        }
    ?> 
    </div><!-- END appear-days -->

    <!--- Appearance Location/Booth Info --->
    <div class="appear-block">
        <?php 
            // Load booth location function
            $leap_space_file = get_template_directory() . '/leap/space.php';
            if ( file_exists( $leap_space_file ) ) {
                require_once( $leap_space_file );
                $vend_booth = get_booth_location( get_the_ID() );
                
                if ( $vend_booth ) {
                    echo '<strong>Location:</strong> ';
                    echo wp_kses_post($vend_booth);
                }
            }
            ?>
    </div><!-- END appear-loca -->

</div><!-- END Appearance Info Block -->