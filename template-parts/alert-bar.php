<?php 
/** Template Part: Alert Bar 
 * Top of the Website. Located in the header 
 * 
 * Notes:
 * Uses classes: alert-bar gradi alert-slide 
 * To Do: 
 * - Post slider 
 * 
 */

?>



<?php
// Alert Bar - ACF Repeater Field
if( have_rows('alert', 'option') ) {
    $alerts = array();
    
    // Collect all alerts
    while( have_rows('alert', 'option') ) {
        the_row();
        $message = get_sub_field('message');
        $link = get_sub_field('url'); 
        if( $message ) {
            $alerts[] = array(
                'message' => $message,
                'link' => $link
            );
        }
    }
    
    // Display alerts as slides
    if( !empty($alerts) ) {
        echo '<div class="alert-bar gradi">';// Alert Bar Container --------->       
       foreach( $alerts as $alert ) {
    if( is_array($alert['link']) && !empty($alert['link']['url']) ) {
        echo '<a href="' . esc_url($alert['link']['url']) . '"class="alert-slide">' . esc_html($alert['message']) . '</a>'; 
    } else { //Alert Slide ------------------------>
        echo '<div class="alert-slide">' . esc_html($alert['message']) . '</div>';
        }//END link   
    }// End foreach alert
        echo '</div><!-- END Alert Bar -->';
    } //END Display as Slides
} //END Alert Bar
?>

