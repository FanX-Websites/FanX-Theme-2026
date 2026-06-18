<?php 
/** Template Part: Countdown Block - Front Page 
 * 
 * Classes used: countdown, countdown-layout, countdown-container, countdown-timer, countdown-item, countdown-value, countdown-label
 * 
 * Pulls from ACF Options Page fields: event_name, event_timer
 * 
 * Notes: 
 * //WARNING: Temp Event Specific Messaging - Make Dynamic
*/

?> 
<!-- Countdown-block -->
<div class="countdown-fp-block fill"><!-- Countdown Block styling within parent div ---> 
    <div class="countdown-layout"> <!-- Countdown Inner Layout w/Fill-->
        
        <!-- Countdown Title --> 
            <?php if ( fanx_is_event_mode_enabled() ) : ?> 
                <p>ICC26 is happening NOW</p>
            <?php else : ?>
                <p>See You in...</p>
            <?php endif; ?>
        <!-- END Countdown Title -->

    <!-- Count Down Timer Section -->
    <?php $event_date = get_field( 'event_timer', 'option' ); //Event Date ?>

    <div class="countdown-container" data-target-date="
        <?php echo esc_attr( $event_date ); ?>">

        <!-- Countdown Timer -->
        <?php if ( fanx_is_event_mode_enabled() ) : ?>
            <div class="cd-live-event">
                <p>Come Join Us!</p>
                <?php
                    $ticket_url = get_field( 'tkt_url', 'option' );
                    if ( $ticket_url ) :
                        // Handle ACF link field (returns array) or string URL
                        $link_url = is_array( $ticket_url ) ? $ticket_url['url'] : $ticket_url;
                        $link_target = is_array( $ticket_url ) ? $ticket_url['target'] : '_self';
                        if ( $link_url ) :
                            ?>
                            <div class="button-group">
                                <a href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>" class="button button-left">Grab Tickets Now</a>
                                <a href="<?php echo esc_url( home_url( '/app' ) ); ?>" class="button button-right">Download App</a>
                            </div>
                            <?php
                        endif;
                    endif;
                ?>
            </div>
        <?php else : ?>
            <div class="countdown-timer self-centered-inside">
                <div class="countdown-item"><!-- DAYS -->
                    <div class="countdown-value" id="days">00</div>
                    <div class="countdown-label">Days</div>
                </div><!-- END Countdown DAYS-->
                <div class="countdown-separator">:</div>
                <div class="countdown-item"><!-- HOURS -->
                    <div class="countdown-value" id="hours">00</div>
                    <div class="countdown-label">Hours</div>
                </div><!-- END Countdown HOURS-->
                <div class="countdown-separator">:</div>
                <div class="countdown-item"><!--MINUTES -->
                    <div class="countdown-value" id="minutes">00</div>
                    <div class="countdown-label">Minutes</div>
                </div><!-- END Countdown MINUTES-->
                <div class="countdown-separator">:</div>
                <div class="countdown-item"><!-- SECONDS -->
                    <div class="countdown-value" id="seconds">00</div>
                    <div class="countdown-label">Seconds</div>
                </div><!-- END Countdown SECONDS-->
            </div>
        <?php endif; ?>
        <!-- END Countdown Timer -->
    
    </div><!-- END Countdown Container -->

    </div><!-- END Countdown Inner Layout w/Fill-->
</div><!-- END Countdown-block -->



<style>
/** The Block size & positioning in Partent Div*/
.countdown-fp-block{ 
    display: flex; 
    align-items: center;
    justify-content: center;
} 
/** Inner Div - builds on .fill */
.countdown-layout p{
    color: var(--color_fnt_wht);
    display: flex; 
    align-items: center;
    justify-content: center;
    flex-direction: column;
    font-size: 1.75rem; 
    font-weight: 600;
    line-height: 2.75rem;
    text-transform: uppercase;
    text-align: center; 
    width: 100%;
    padding: 0;
    margin: 0;
}
/** Countdown Timer */
.countdown-container{
    text-align: center; 
    margin: 0; 
    padding: 10px; 
 
}
 .countdown-timer{
    gap: 10px;
    align-items: flex-start;
}
.countdown-item{
    transition: transform 0.3s ease;
}
 .countdown-value { /** The Numberes */
    color: var(--color_fnt_wht);
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 100;
    line-height: 3rem;
}
.countdown-separator { 
    color: var(--color_fnt_wht);
    font-size: 2.5rem;
    font-weight: 100;
    line-height: 2.5rem;
    margin: 0; 
    padding: 0;
}
.countdown-label { /** The Days & Minutes */
    font-size: 1em;
    line-height: 2rem;
    color: var(--color_fnt_wht);
    text-transform: lowercase;
    letter-spacing: 1px;
}

</style>