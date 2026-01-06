<?php 

// This is the Block for The Event-Info Category (see taemplate)

?> 


    <div class="event-info">
        <div class="event-info-content self-centered-column">
     <?php echo get_field('event_block', 'option'); ?>    
        </div>
    </div><!-- END Event Info-->

<style>
.event-info{ /**  */
    background-color: var(--color_acc);
    width: 40%; 
    height: 35%;
    top: 0; 
    right: 0;
    position: absolute;

.event-info-content{    

    
}

} 
</style>