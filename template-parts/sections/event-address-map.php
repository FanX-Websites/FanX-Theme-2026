<?php 
/** Template part: Address Map 
 * 
 * Contains Google Map & Address pulled from ACF fields
 * Same Styling as address-map template part 
*/
?>

<!-- Address Map Section -->

    <!-- Address Block -->
    <div class="add-map-section self-centered">
        <div class="address-block block">

        </div>

    <!-- END Address Block -->
    
    <!-- Map Block -->
    <div class="map-block block">
        <?php 
        $address = get_field('event_address', 'option');
        $encoded = !empty($address) ? urlencode($address) : '';
        $api_key = get_field('google_maps_api_key', 'option');
        ?>
        <iframe
            width="100%"
            height="400px"
            frameborder="0"
            style="border:0"
            src="https://www.google.com/maps/embed/v1/place?key=<?php echo esc_attr($api_key); ?>&q=<?php echo $encoded; ?>"
            allowfullscreen>
        </iframe>
    </div>
    <!-- END Map Block -->
            
    </div><!-- END Address Map Section -->