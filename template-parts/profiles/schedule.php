<?php 
/** Template Part: Guest Profile Schedule 
 * @package FanX Theme 2026
 * Displays a guest's schedule from the LEAP Conventions API in list format
 * Automatically matches guest by post title against LEAP schedule data
 * 
 * //FIXME: Replace hardcoded API key with dynamic ACF field value (currently for testing)
 */

// ============================================================================
// CONDITIONAL CHECK: Only display schedule if API key is configured
// ============================================================================
$leap_api_key = get_field( 'leap_api_key', 'option' );

if ( empty( $leap_api_key ) ) {
    // API key is EMPTY - show ACF fallback message

    ?>
<!---------------------- NO EVENTS ------------------->
    <div class="guest-schedule block"> 
        <div class="guest-no-events block">
            <?php 
                $content = get_field( 'sched_guest' ) ?? ''; // Guest Schedule Content
                // Remove wrapping <p> tags from ACF field
                $content = preg_replace('/<\/?p[^>]*>/', '', $content);
                echo wp_kses_post($content); 
            ?>
        </div>
    </div><!-- END NO EVENTS -->
    <?php
    return;
}
// ============================================================================
// GET GUEST NAME FROM POST TITLE
// ============================================================================
$guest_name = get_the_title();

if ( empty( $guest_name ) ) {
    return; // Exit if no guest name is available
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

foreach ( $schedules as $event ) {
    // Check if this guest appears in the event's people array
    if ( ! empty( $event['people'] ) && is_array( $event['people'] ) ) {
        foreach ( $event['people'] as $person ) {
            // Construct person's full name
            $person_full_name = trim( ( $person['first_name'] ?? '' ) . ' ' . ( $person['last_name'] ?? '' ) );
            $person_alt_name = $person['alt_name'] ?? '';
            
            // Compare names (case-insensitive)
            if ( strtolower( $person_full_name ) === strtolower( $guest_name ) || 
                 ( ! empty( $person_alt_name ) && strtolower( $person_alt_name ) === strtolower( $guest_name ) ) ) {
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
    $time_a = strtotime( $a['start_time'] ?? 0 );
    $time_b = strtotime( $b['start_time'] ?? 0 );
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
            <div class="guest-schedule-item">
                <h4 class="guest-event-title"><?php echo esc_html( $title ); ?> - <?php echo esc_html( $location ); ?></h4>
                <div class="guest-schedule-item-details">
                    <span class="day-name"><?php echo esc_html( $event_day ); ?></span>
                    <span class="time"><?php echo esc_html( $event_time ); ?></span>
                </div><!--- END guest-schedule-item-details -->
            </div><!--- END guest-schedule-item -->
        <?php endforeach; ?>
    </div><!-- END Schedule List ------------------>
<?php else : ?>
    <div class="guest-no-events block">
        <?php 
            $content = get_field( 'sched_guest' ) ?? ''; // Guest Schedule Content
            // Remove wrapping <p> tags from ACF field
            $content = preg_replace('/<\/?p[^>]*>/', '', $content);
            echo wp_kses_post($content); 
        ?>
    </div>
    <?php endif; ?>
</div>


