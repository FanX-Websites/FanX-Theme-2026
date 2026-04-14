<?php 
/**
 * Template Part: Guest Appearance Days 
 * @author FanXTheme2026
 */
?>

<?php 
                    $days_cats = get_the_terms( get_the_ID(), 'days' );
                    
                    if ( ! empty( $days_cats ) && ! is_wp_error( $days_cats ) ) {
                        //Sort by Day Name for correct Appearance Order
                        $order = ['thursday' => 1, 'friday' => 2, 'saturday' => 3, 'sunday' => 4];
                        usort($days_cats, fn($a, $b) => ($order[$a->slug] ?? 99) - ($order[$b->slug] ?? 99));
                        echo '<div class="days guest-xp">';
                        echo '<strong>Appearing:</strong> ';
                        $links = array();
                        foreach ( $days_cats as $cat ) {
                            $links[] = '<a href="' . esc_url( get_term_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a>';
                        }
                        echo implode( ' | ', $links ) . '*';
                        echo '</div>';
                    }
                    ?> 