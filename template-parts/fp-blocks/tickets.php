<?php 

// This is the Block for The tickets Category (see taemplate)

?> 

<div class="block-mos"><!--All Mosaic Blocks-->
    <div class="ticket-block"><!-- Ticket Block Position -->
        <div class="self-centered-inside"><!-- Style --->
            <div class="ticket-layout self-centered-column"><!-- Style -->
                <h1>[TICKET STATUS]</h1>
                <!-- Ticket Links --->
                <nav class="tickets">
                    <a href="">[Ticket Info Page]</a>  |  
                    <a href="">[Ticket Action]</a>
                </nav><!-- Ticket Links --> 
            </div><!-- END Ticket Layout w/self-centered column -->
        </div><!--END Self Centered Div-->
    </div><!-- END Tickets Block-->
</div><!-- End Block -->


<style>
.ticket-block{ /** The Block size & positioning in Partent Div*/
    background-color: var(--color_base_lght); 
    width: 35%; 
    height: 20%; 
    position: absolute;

}

.ticket-layout{/** Inner Div - combined with self-centered-column */
    width: 90%;
    height: 80%; 
    text-align: center; 
    font-size: 1em; 
    line-height: 1.5em; 
    color: var(--color_base);
    border: 2px solid var(--color_base);
}
.ticket-layout h1{
    font-size: 2em;
    font-weight: 900;
    font-family: sans-serif;
}
.ticket-layout a{
    color: var(--color_base);
    text-decoration: none; 
}

.tickets{ /** Nav Links */
    width: 100%;
}
a.tickets{
    padding: 0 2%; 
}
/** END Nav Links */


 
</style>

