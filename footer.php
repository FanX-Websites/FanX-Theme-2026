
<?php
//Footer Template: Including Bottom Link Bar 
 
 ?>  
  
</main><!-- END Site Main from Header-->  

<?php echo get_field( //Footer Injection Code 
            'theme_footer', 'option'
            ) ?>            

<!--- FOOTER --->
<div class="footer">
    
    <!--SPONSOR BAR --------->
    <div data-id="sponsor-bar" class="container">
        <?php get_template_part('template-parts/sponsor-bar'); ?>
    </div><!-- END Sponsor Bar Container-->

    <?php /* Temp Hidden
    <!-- FOOTER MAIN: CONTACT SECTION ------>    
    <div data-id="Contact Us"class="container">
        <?php get_template_part('template-parts/connection'); ?>
    </div><!-- END Footer Main/Contact Section -->*/ ?>

    <!--- Socket Bar -->
    <div data-id="The Small Print" class="container">
        <?php 
        // Fetch socials from ACF Options page
        $socials = get_field('socials', 'option');
        // Pass socials array to the 'socket' 
        set_query_var('socials', $socials);
        //Template Part - Socket 
        get_template_part('template-parts/socket'); ?>
    </div>
    
</div><!-- END Footer-->


<!-- Bottom Link Bar ----------> 
    <div data-id="Purchase Tickets and Photo Ops" class="container">
        <?php get_template_part( //Sticky Bottom Link Bar
            'template-parts/link-bar'
        ); ?>
    </div><!-- END Container -->
<!-- END Bottom Link Bar -->    


<!--- Website Wrap up ------->

<?php echo get_field('theme_body', 'option')?>
<?php wp_footer(); ?>
    </body>
</html>
<!-- END Body and HTML --->

