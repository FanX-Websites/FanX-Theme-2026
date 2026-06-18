<?php 
/** Template Part: Floor Maps/Room List Section
 *  Description: Displays the floor maps and a room list for the event.
 * 
 * //TODO: Create a conditional that hides the List column when the Vendor List Status ACF Field contains a specific message ie: 'coming soon' or if not 'view now'. add directions to ACF feild for user. 
 */

// Get page slug for context-specific styling
// Priority: args > queried object (for archives) > current page
$page_slug = '';

if (isset($args['page_class'])) {
    // If passed as argument to template part
    $page_slug = $args['page_class'];
} else {
    // First check if we're on a taxonomy/category archive
    $queried_object = get_queried_object();
    if ($queried_object && isset($queried_object->taxonomy)) {
        // We're on a taxonomy/category archive
        $page_slug = $queried_object->slug;
    } elseif ($queried_object && isset($queried_object->post_name)) {
        // It's a page/post
        $page_slug = $queried_object->post_name;
    } else {
        // Fallback to current page
        $current_page_id = get_the_ID();
        if ($current_page_id && is_page()) {
            $page_slug = get_post_field('post_name', $current_page_id);
        }
    }
}

$container_class = 'grid-container layout-1-2col'; // Column layout for maps and list
if ($page_slug) {
    $container_class .= ' ' . $page_slug . '-blocks';
}

// Check if all map fields are empty - if so, don't display this template //TODO: Add Headers & Map/List Status
$map_master_list = get_field('map_master_list', 'option');
$map_vend = get_field('map_vend', 'option');
$map_misc = get_field('map_misc', 'option');

if (empty($map_master_list) && empty($map_vend) && empty($map_misc)) {
    return;
}

?>


<!--- Floor Maps & Room List Section Container -->

<div id="maps" class="<?php echo esc_attr($container_class); ?>"> 
    
    <div class="room-list grid-block" id="list"><!-- Room List Container -->
        <div class="sched list-container">    
            <div class="sched list-content">
                <div class="list-title-bar">
                    <h4>Vendor Booth List</h4>
                    <div class="scroll-indicator">
                        <span class="scroll-up"><svg xmlns="http://www.w3.org/2000/svg" height="60px" viewBox="0 -960 960 960" width="40px" fill="#1f1f1f"><path d="m296-224-56-56 240-240 240 240-56 56-184-183-184 183Zm0-240-56-56 240-240 240 240-56 56-184-183-184 183Z"/></svg></span>
                        <span class="scroll-down"><svg xmlns="http://www.w3.org/2000/svg" height="60px" viewBox="0 -960 960 960" width="40px" fill="#1f1f1f"><path d="M480-200 240-440l56-56 184 183 184-183 56 56-240 240Zm0-240L240-680l56-56 184 183 184-183 56 56-240 240Z"/></svg></span>
                    </div>
                </div>
                <?php 
                    // Fetch vendor/booth data from Leap Events API
                    $leap_api_key = get_field( 'leap_api_key', 'option' );
                    
                    if ( ! empty( $leap_api_key ) ) {
                        $api_url = 'https://conventions.leapevent.tech/api/space_orders?key=' . urlencode( $leap_api_key );
                        
                        $response = wp_remote_get( $api_url, array(
                            'timeout'   => 10,
                            'headers'   => array( 'accept' => 'application/json' )
                        ) );
                        
                        if ( is_wp_error( $response ) ) {
                            echo '<p>Error fetching vendor list: ' . esc_html( $response->get_error_message() ) . '</p>';
                        } else {
                        $body = wp_remote_retrieve_body( $response );
                        $data = json_decode( $body, true );
                        
                        if ( ! $data || ! isset( $data['space_orders'] ) ) {
                            echo '<p>No vendor data found.</p>';
                        } else {
                            $vendors = $data['space_orders'];
                            
                            // Filter vendors with booth numbers and sort alphabetically by company name
                            $booth_vendors = array_filter( $vendors, function( $vendor ) {
                                return ! empty( $vendor['booth'] );
                            });
                            
                            // Sort alphabetically by vendor company name
                            usort( $booth_vendors, function( $a, $b ) {
                                return strcasecmp( $a['company'], $b['company'] );
                            });
                            
                            if ( empty( $booth_vendors ) ) {
                                echo '<p>Vendor List Available SOON.</p>';
                            } else {
                                echo '<div class="vendor-list-wrapper">';
                                echo '<table class="vendor-list">';
                                echo '<thead><tr><th>Vendor Name</th><th>Booth Number</th></tr></thead>';
                                echo '<tbody>';
                                foreach ( $booth_vendors as $vendor ) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html( $vendor['company'] ) . '</td>';
                                    echo '<td>Booth ' . esc_html( $vendor['booth'] ) . '</td>';
                                    echo '</tr>';
                                }
                                echo '</tbody>';
                                echo '</table>';
                                echo '</div>';
                                // scroll-indicator moved above table with title
                            }
                        }
                        }
                    } else {
                        echo '<p>API key not configured.</p>';
                    }
                ?>
            </div>
        </div>
    </div>

    <div class="floor-maps grid-container layout-2col child"><!-- Floor Maps Container -->
        <div class="map-container grid-block">    
        <figure class="img-container"><!-- Vendor Map Container -->
        <?php 
                $map_vend = get_field('map_vend', 'option'); // Vendor Map 
                if($map_vend) {
                    echo '<a href="' . esc_url($map_vend['url']) . '" target="_blank" rel="noopener noreferrer">';
                    echo '<img src="' . esc_url($map_vend['url']) . '" alt="' . esc_attr($map_vend['alt']) . '">';
                    if (!empty($map_vend['caption'])) { //caption
                        echo '<figcaption>' . wp_kses_post($map_vend['caption']) . '</figcaption>';
                    }
                    echo '</a>';
                }
                ?>
        </figure><!-- End Vendor Map Container -->
        </div>
        <div class="map-container grid-block">    
        <figure class="img-container"><!-- Misc Map Container -->
            <?php 
                $map_misc = get_field('map_misc', 'option'); // Misc Map
                if($map_misc) {
                    echo '<a href="' . esc_url($map_misc['url']) . '" target="_blank" rel="noopener noreferrer">';
                    echo '<img src="' . esc_url($map_misc['url']) . '" alt="' . esc_attr($map_misc['alt']) . '">';
                    if (!empty($map_misc['caption'])) { //caption
                        echo '<figcaption>' . wp_kses_post($map_misc['caption']) . '</figcaption>';
                    }
                    echo '</a>';
                }
            ?>
        </figure><!-- End Misc Map Container -->
        </div>
    </div><!-- End Floor Grid Block -->    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scrollUpBtn = document.querySelector('.scroll-indicator .scroll-up');
    const scrollDownBtn = document.querySelector('.scroll-indicator .scroll-down');
    const vendorListWrapper = document.querySelector('.vendor-list-wrapper');
    
    if (scrollUpBtn && vendorListWrapper) {
        scrollUpBtn.style.cursor = 'pointer';
        scrollUpBtn.addEventListener('click', function() {
            vendorListWrapper.scrollBy({
                top: -150,
                behavior: 'smooth'
            });
        });
    }
    
    if (scrollDownBtn && vendorListWrapper) {
        scrollDownBtn.style.cursor = 'pointer';
        scrollDownBtn.addEventListener('click', function() {
            vendorListWrapper.scrollBy({
                top: 150,
                behavior: 'smooth'
            });
        });
    }
});
</script>

<!--- END Floor Maps & Room List Section Container -->
