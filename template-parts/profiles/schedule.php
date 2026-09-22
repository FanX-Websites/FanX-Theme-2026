<?php 
/** Template Part: GUEST PROFILE Schedule TAB
 * @package FanX Theme 2026
 * Displays a guest's schedule from the LEAP Conventions API in list format
 * Automatically matches guest by post title against LEAP schedule data
 * 
 * For default Schedule template view the schedules folder 
 */

// ============================================================================
// GET GUEST NAME FROM POST TITLE
// ============================================================================
$guest_name = get_the_title();

// Shared fallback message, reused whenever no schedule can be shown
$no_events_message = esc_html( $guest_name ) . ' doesn\'t have a schedule posted here.<br>Check out their eXperience Tab to see what they\'re up to!';

if ( empty( $guest_name ) ) {
    return; // Exit if no guest name is available
}

// ============================================================================
// CONDITIONAL CHECK: Only display schedule if API key is configured
// ============================================================================
$leap_api_key = get_field( 'leap_api_key', 'option' );

if ( empty( $leap_api_key ) ) {
    // API key is EMPTY - show fallback message
    ?>
<!---------------------- NO EVENTS ------------------->
<div class="guest-schedule block"> 
    <div class="guest-no-events block">
        <?php echo $no_events_message; ?>
    </div><!-- END NO EVENTS -->
</div>
    <?php
    return;
}

// ============================================================================
// LEAP SCHEDULE DATA API - Filter by Guest ID
// https://conventions.leapevent.tech/Api/docs#
// ============================================================================

// API URL - uses ACF field for security
$api_url = 'https://conventions.leapevent.tech/api/schedules?key=' . urlencode( $leap_api_key );

// Fetch data from API
$response = wp_remote_get( $api_url, array(
    'timeout'   => 10,
    'headers'   => array( 'accept' => 'application/json' )
) );

// Check for errors
if ( is_wp_error( $response ) ) {
    echo '<p>Error fetching schedule: ' . esc_html( $response->get_error_message() ) . '</p>';
    return;
}

// Parse JSON - convert response to PHP array
$body = wp_remote_retrieve_body( $response );
$data = json_decode( $body, true );

if ( ! $data || ! isset( $data['schedules'] ) ) {
    echo '<p>No schedule data available.</p>';
    return;
}

$schedules = $data['schedules'];

// ============================================================================
// FILTER: Extract only events where the guest name matches
// ============================================================================
$guest_events = array();

// Normalize whitespace so stray/double spaces in either name don't break the match
$normalized_guest_name = strtolower( trim( preg_replace( '/\s+/', ' ', $guest_name ) ) );

foreach ( $schedules as $event ) {
    // Check if this guest appears in the event's people array
    if ( ! empty( $event['people'] ) && is_array( $event['people'] ) ) {
        foreach ( $event['people'] as $person ) {
            // Construct person's full name, trimming each part individually before joining
            $first_name = trim( $person['first_name'] ?? '' );
            $last_name = trim( $person['last_name'] ?? '' );
            $person_full_name = strtolower( trim( preg_replace( '/\s+/', ' ', $first_name . ' ' . $last_name ) ) );
            $person_alt_name = strtolower( trim( preg_replace( '/\s+/', ' ', $person['alt_name'] ?? '' ) ) );
            
            // Compare names (case-insensitive, whitespace-normalized)
            if ( $person_full_name === $normalized_guest_name || 
                 ( ! empty( $person_alt_name ) && $person_alt_name === $normalized_guest_name ) ) {
                $guest_events[] = $event;
                break; // Found the guest, add event and move to next event
            }
        }
    }
}

// ============================================================================
// SORT: Order events by start_time (chronologically)
// ============================================================================
usort( $guest_events, function( $a, $b ) {
    $time_a = strtotime( $a['start_time'] ?? '' );
    $time_b = strtotime( $b['start_time'] ?? '' );
    return $time_a - $time_b;
});

// ============================================================================
// DISPLAY: Show schedule in list format
// ============================================================================

?>
<!--- Guest Live LEAP Schedule ---------------------->
<div class="guest-schedule block">  
    <?php if ( ! empty( $guest_events ) ) : ?>
    <div class="schedule-list">
        <?php foreach ( $guest_events as $event ) : 
            // Parse date and time
            $start_datetime = $event['start_time'] ?? '';
            $end_time = $event['end_time'] ?? '';
            
        if ( empty( $start_datetime ) ) {
                continue; // Skip events without a start time
        }
            // Extract date components
            $event_date = substr( $start_datetime, 0, 10 );
            $event_day = date( 'l', strtotime( $event_date ) ); // No timezone conversion
            $event_time = date( 'g:i a', strtotime( $event['start_time'] ) ); // No timezone conversion
            
                $location = $event['location'] ?? 'TBA';
                $title = $event['title'] ?? 'Untitled Event';
        ?>
        <!-- Guest Schedule Content ---> 
            <div class="guest-schedule-item">
                <h4 class="guest-event-title"><?php echo esc_html( $title ); //Event Title ?></h4> 
                <?php echo esc_html( $location ); //Room Number ?>
                <div class="guest-schedule-item-details">
                    <span class="day-name"><?php echo esc_html( $event_day ); //Day ?>@</span>
                    <span class="time"><?php echo esc_html( $event_time ); //Time ?></span>
                </div><!--- END guest-schedule-item-details -->
            </div>
        <!--- END Guest Schedule Content -->
        <?php endforeach; ?>
    </div><!-- END Schedule List ------------------>
<?php else : ?>
    <div class="guest-no-events block">
        <?php echo $no_events_message; ?>
    </div>
    <?php endif; ?>

<!---- APPEARANCE MESSAGE & Apps Link ------->
<div class="message small-print">
Guest schedules are subject to change. <a href="/app">Download the App</a> for the most up to date schedule.
</div>


</div><!--- END Guest Schedule Block ---->


