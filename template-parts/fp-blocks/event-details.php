<?php 

// This is the Block pulls from the Event Info Details from the ACF Options Page
?> 


    <div class="event-details-fp-block fill">
        <div class="event-details-content">
     <div class="fp-event-name">
        <h1><?php echo get_field('event_name', 'option'); //EVENT NAME ?></h1>
    </div> 
    <div class="fp-event-date"><h2>
        <?php echo get_field('event_date', 'option'); //EVENT DATE ?></h2>  
    </div>
    <div class="fp-event-location"><h3>
        <?php 
        $venue_id = get_field('event_venue', 'option'); //EVENT VENUE
        if ( $venue_id ) {
            $term = get_term($venue_id);
            if ( $term && !is_wp_error($term) ) {
                echo $term->name;
            }
        }
        ?></h3>  
    </div>    
        </div>
    </div><!-- END Event Details-->

