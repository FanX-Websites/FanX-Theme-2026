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
<div class="countdown block"><!-- Countdown Block styling within parent div ---> 
    <div class="countdown-layout fill"> <!-- Countdown Inner Layout w/Fill-->
        
        <!-- Countdown Title -->
            <h1><?php echo get_field('event_name', 'option'); ?></h1>
            <p>is coming in:</p>
            <!--ADD conditional connected to countdown date field HERE -->
        <!-- END Countdown Title -->

    <!-- Count Down Timer Section -->
    <?php $event_date = get_field( 'event_timer', 'option' ); //Event Date ?>

    <div class="countdown-container self-centered" data-target-date="
        <?php echo esc_attr( $event_date ); ?>">

        <!-- Countdown Timer -->
        <div class="countdown-timer self-centered-inside">
            <div class="countdown-item"><!-- DAYS -->
                <div class="countdown-value" id="days">00</div>
                <div class="countdown-label">Days</div>
            </div><!-- END Countdown DAYS-->
            <div class="countdown-item"><!-- HOURS -->
                <div class="countdown-value" id="hours">00</div>
                <div class="countdown-label">Hours</div>
            </div><!-- END Countdown HOURS-->
            <div class="countdown-item"><!--MINUTES -->
                <div class="countdown-value" id="minutes">00</div>
                <div class="countdown-label">Minutes</div>
            </div><!-- END Countdown MINUTES-->
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
.countdown{ 
    background-color: var(--color_prim_brht); 
    width: 40%; 
    height: 35%;
    top: 35%; 
    right: 0;
    position: absolute; 

} 
/** Inner Div - builds on .fill */
.countdown-layout{
    color: var(--color_fnt_wht);
    display: flex; 
    align-items: center;
    jutify-content: center;
    flex-direction: column;
}
.countdown-layout h1{
    font-size: 1.5em; 
    text-align: center; 
    width: 100%;
    line-height: 1.2em;
    padding: 3% 0 0;
    margin: 0;
}
.countdown-layout p{
    font-size: 1em; 
    line-height: 1.2em;
    text-align: center; 
    width: 100%;
    padding: 1%;
    margin: 0;
}
/** Countdown Timer */
.countdown-container{
    text-align: center; 

}
 .countdown-timer{/**Combined w/Self-Centered-Inside */
      gap: 20px;
}
.countdown-item{
    transition: transform 0.3s ease;
}
 .countdown-value {
    font-size: 36px;
    font-weight: bold;
    color: inherit;
    line-height: 1;
}
.countdown-label {
    font-size: 12px;
    color: inherit;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 8px;
}

</style>