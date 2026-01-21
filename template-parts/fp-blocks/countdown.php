<?php 
/** Template Part: Countdown Block - Front Page 
 * 
 * Classes used: countdown, countdown-layout, countdown-container, countdown-timer, countdown-item, countdown-value, countdown-label
 * 
 * Pulls from ACF Options Page fields: event_name, event_timer
 * 
 * Notes: 
 * Make the Countdown message change from 'is coming in' to 'is happening now' when the date is reached.
*/

?> 
<!-- Countdown-block -->
<div class="countdown-fp-block fill"><!-- Countdown Block styling within parent div ---> 
    <div class="countdown-layout"> <!-- Countdown Inner Layout w/Fill-->
        
        <!-- Countdown Title -->
            <p>See You in...</p>
            <!--ADD conditional connected to countdown date field HERE -->
        <!-- END Countdown Title -->

    <!-- Count Down Timer Section -->
    <?php $event_date = get_field( 'event_timer', 'option' ); //Event Date ?>

    <div class="countdown-container" data-target-date="
        <?php echo esc_attr( $event_date ); ?>">

        <!-- Countdown Timer -->
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