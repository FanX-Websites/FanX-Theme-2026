<?php 

// This is the Block for The partcipate Category (see taemplate)

?> 


    <div class="participate-block">
        <div class="participate-content self-centered-column fill">
           <h3>Participate/Industry</h3>
            <div class= "icon-row self-centered-row">
                <a href="/partners/exhibitor-info"><ico class="pb fa-solid fa-shopping-bag fa-4x"></ico>
                <span class="ico-subtitle">Exhibitors</span>
                </a>
                <a href="/partners/programming-info"><ico class="pb fa-solid fa-microphone fa-4x"></ico>
                <span class="ico-subtitle">Programming</span>
                </a>
                <a href="/xp/cosplay-contest"><ico class="pb fa-solid fa-trophy fa-4x"></ico>
                <span class="ico-subtitle">Cosplay Contest</span>
                </a>
            </div><!-- END Icon Row -->
        </div>

    </div><!-- END Participate-->



<style>
.participate-block{ /**  */
    background-color: var(--color_base);
        width: 40%; 
        height: 30%;
        top: 70%;
        right: 0;
        position: absolute;
    }
.participate-content{
    width: 100%;
    height: 100%;
    text-align: center;

    
}
.pb{
    margin: 0 20px;
    color: var(--color_base);
    background-color: var(--color_base_brht);
    padding: 20px;
    border-radius: 10px;
}
.ico-subtitle{
    display: block;
    line-height: 1.7em;
    font-weight: 500;
    font-size: 1.2em;
    color: var(--color_base_brht);
}
</style>