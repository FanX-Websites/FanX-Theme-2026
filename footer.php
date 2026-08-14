
<?php
//Footer Template: Including Bottom Link Bar 
 
 ?>  
  
</main><!-- END Site Main from Header-->  

<!-- Footer Injection Code -->
<?php echo get_field('theme_footer', 'option') ?> 

<!-- Remove search strings from url -->
<script>
if (window.history.replaceState) {
    const cleanUrl = window.location.pathname;
    window.history.replaceState({}, document.title, cleanUrl);
}
</script> 

<!-- END Footer Injection Code --->

<!--- FOOTER --->
<div class="footer">
    
    <!--SPONSOR BAR --------->
    <div class="sponsor-bar container">
        <?php get_template_part('template-parts/sponsor-bar'); ?>
    </div><!-- END Sponsor Bar Container-->

    <?php /* Temp Hidden
    <!-- FOOTER MAIN: CONTACT SECTION ------>    
    <div class="Contact Us"class="container">
        <?php get_template_part('template-parts/connection'); ?>
    </div><!-- END Footer Main/Contact Section -->*/ ?>

    <!--- Socket Bar -->
    <div class="container">
        <?php 
        // Fetch socials from ACF Options page
        $socials = get_field('socials', 'option');
        // Pass socials array to the 'socket' 
        set_query_var('socials', $socials);
        //Template Part - Socket 
        get_template_part('template-parts/socket'); ?>
    </div>

    <!-- Bottom Link Bar ----------> 
    <div class="container">
        <?php get_template_part( //Sticky Bottom Link Bar
            'template-parts/link-bar'
        ); ?>
    </div><!-- END Container -->
    <!-- END Bottom Link Bar -->
    
</div><!-- END Footer-->    



<!-- Customer Service Chatbot -->
<?php echo get_field('int_bubble', 'option')?>
<!-- END Customer Service Chatbot -->

<?php wp_footer(); ?>

<!-- Website Closing Tags -->
    </body>
</html>
<!-- END Body and HTML --->

