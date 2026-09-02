<?php
// Template Part: Small Print
?>

<!--- SMALL PRINT -->
        <div class="small-print">
            <?php $foo = get_field( 'foo', 'options' ); ?>
            <p>
                test <?php echo esc_html( $foo['small_print'] ?? '' ); //Small Print ?>
            </p>
            <?php 
                /*
                $xp_terms = get_the_terms( get_the_ID(), 'xp' );
                $has_autographs = false;
                
                if ( $xp_terms && ! is_wp_error( $xp_terms ) ) {
                    foreach ( $xp_terms as $term ) {
                        if ( $term->slug === 'autographs' ) {
                            $has_autographs = true;
                            break;
                        }
                    }
                }
                
                if ( $has_autographs ) : 
                */
            ?>
            <p>
                <?php echo wp_kses_post( $foo['celeb_small_print'] ?? '' ); //Small Print ?>
            </p>
            <?php // endif; ?>
        </div>
        <!-- END Small Print -->