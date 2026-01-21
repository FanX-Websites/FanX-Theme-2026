<?php 

// This is the Block pulls from the Event Info Details from the ACF Options Page
?> 


    <div class="event-details-fp-block fill">
        <div class="event-details-content">
     <div class="fp-event-name"><h1>
        <?php echo get_field('event_name', 'option'); ?></h1>  
    </div> 
    <div class="fp-event-date"><h2>
        <?php echo get_field('event_date', 'option'); ?></h2>  
    </div>
    <div class="fp-event-location"><h3>
        <?php 
        $venue_id = get_field('event_venue', 'option');
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

<style>
.event-details-fp-block{ /** The Block size & positioning in Partent Div*/
    color: var(--color_fnt_wht);
    display: flex;
    text-align: left;
    display: flex;
    align-items: center;
    padding: 3% 5%;
    margin: 0;
    max-width: 600px;
} 

.fp-event-name h1{
    font-size: 2rem;
    line-height: 2.25rem;
    font-weight: 700;  
}
.fp-event-date h2{
    font-size: 1.5rem;
    line-height: 2rem;
    font-weight: 500;  
}
.fp-event-location h3{
    font-size: 1.2rem;
    line-height: 1.75rem;
    font-weight: 400;  
}

</style>