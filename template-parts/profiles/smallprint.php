<?php
// Template Part: Small Print
?>

<!--- SMALL PRINT -->
        <div class="small-print">
            <p>
                <?php the_field('foo_small_print', 'options'); //Small Print ?>
            </p>
            <?php 
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
            ?>
            <p>
                <?php the_field('foo_celeb_small_print', 'options'); //Small Print ?>
            </p>
            <?php endif; ?>
        </div>
        <!-- END Small Print -->