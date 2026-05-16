<?php 
/** Template Part: Floor Maps/Room List Section
 *  Description: Displays the floor maps and a room list for the event.
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
    
    <div class="room-list grid-block"><!-- Room List Container -->
        <div class="list-container">    
            <div class="list-content">
                <?php 
                    // Determine which room list field based on category page
                    $room_list_field = 'map_master_list'; // Default for event-info - Master Room List
                    
                    switch ($page_slug) {
                        case 'panel-programming':
                            $room_list_field = 'map_prog_room_list'; // Programming Room List
                            break;
                        case 'ada':
                            $room_list_field = 'cs_ada_room_list'; // ADA Room List
                            break;
                    }
                    
                    the_field($room_list_field, 'option');
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
<!--- END Floor Maps & Room List Section Container -->
