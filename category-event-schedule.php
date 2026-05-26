<?php
/**
 * Leap Conventions Schedule Template
 * Displays events from the Leap API
 * 
 * //TODO: Closeout Events as the time passes. 
 * //TODO: Add to calendar functionality (ICS file or Google Calendar link)
 * //FIXME: Replace hardcoded API key with dynamic ACF field value (currently for testing)
 */

get_header(); /** body- main-site */

// ============================================================================
// CONDITIONAL CHECK: Only display schedule if API key is configured
// ============================================================================
// This checks the ACF field "leap_api_key" to see if it has a value
$leap_api_key = get_field( 'leap_api_key', 'option' );

if ( empty( $leap_api_key ) ) {
    // API key is EMPTY - show "Coming soon" placeholder
    echo '<div class="schedule-wrapper">';
    echo '<h2>Schedule</h2>';
    echo '<p><em>Coming soon...</em></p>';
    echo '</div>';
} else {
    // API key EXISTS - proceed with fetching and displaying schedule

?>
    <!--------------- Page Header Container [Template Part] ----------------------->
    <div class="container full">
        <?php get_template_part('template-parts/page-header'); ?>
    </div>
    <!------------ END Page Header Container -------------------->    
<?php
/** ========================================================================
 * LEAP SCHEDULE DATA API - DO NOT CHANGE UNLESS UPDATING ENDPOINT OR DATA FIELDS
 * https://conventions.leapevent.tech/Api/docs#
 * This section: Fetches the schedule data from Leap API and parses the response
 * ======================================================================== */

// API URL - replace with actual values as needed
$api_url = 'https://conventions.leapevent.tech/api/schedules?key=3fafe43a-11c0-4c3b-8694-3b7792a80c3d';

// Fetch data from API
$response = wp_remote_get( $api_url, array(
    'timeout'   => 10,
    'headers'   => array( 'accept' => 'application/json' )
) );

// Check for errors
if ( is_wp_error( $response ) ) {
    echo '<p>Error fetching schedule: ' . esc_html( $response->get_error_message() ) . '</p>';
} else {

// Parse JSON - convert response to PHP array
$body = wp_remote_retrieve_body( $response );
$data = json_decode( $body, true );

if ( ! $data || ! isset( $data['schedules'] ) ) {
    echo '<p>No schedule data found.</p>';
} else {

$schedules = $data['schedules'];

if ( empty( $schedules ) ) {
    echo '<p>No events available.</p>';
} else {

// Display schedules - organize by day and time
echo '<div class="schedule-wrapper">';

// Dynamic day title (will be updated by JavaScript)
echo '<h2 class="schedule-day-title" id="schedule-day-title"><span id="schedule-tag-filter-display"></span></h2>';

// Create a container for clear filter button
echo '<div class="clear-filter-container hidden"><button class="clear-filter-btn">Clear filter</button></div>';

// Create a container for day filter tabs
echo '<div class="schedule-day-filters"></div>';

// Group events by day and time (needs to happen before output)
$events_by_day = array();
foreach ( $schedules as $event ) {
    $event_date = substr( $event['start_time'], 0, 10 );
    $event_day = date( 'l', strtotime( $event_date ) ); // No timezone conversion
    $event_time = date( 'g:i a', strtotime( $event['start_time'] ) ); // No timezone conversion
    
    if ( ! isset( $events_by_day[ $event_day ] ) ) {
        $events_by_day[ $event_day ] = array();
    }
    
    if ( ! isset( $events_by_day[ $event_day ][ $event_time ] ) ) {
        $events_by_day[ $event_day ][ $event_time ] = array();
    }
    
    $events_by_day[ $event_day ][ $event_time ][] = $event;
}

// Sort days in order
$days_order = array( 'Friday', 'Saturday', 'Sunday' );
$sorted_days = array();
foreach ( $days_order as $day ) {
    if ( isset( $events_by_day[ $day ] ) ) {
        $sorted_days[ $day ] = $events_by_day[ $day ];
    }
}

// Day headers removed - now using dynamic title above buttons

// Output each day
foreach ( $sorted_days as $day => $time_slots ) {
    echo '<div class="schedule-day" data-day="' . esc_attr( $day ) . '">';
    
    // Sort times chronologically
    uksort( $time_slots, function( $a, $b ) {
        return strtotime( $a ) - strtotime( $b );
    });
    
    // Output each time slot
    foreach ( $time_slots as $time => $events ) {
        echo '<div class="time-slot" data-time="' . esc_attr( $time ) . '">';
        echo '<h3 class="time-header" role="button" tabindex="0">' . esc_html( $time ) . '</h3>';
        echo '<div class=" grid-container">';
        
        foreach ( $events as $event ) {
            $event_date = substr( $event['start_time'], 0, 10 );
            $event_date_short = date( 'M j', strtotime( $event_date ) ); // No timezone conversion
            
            // Extract end time
            $end_time = '';
            if ( ! empty( $event['end_time'] ) ) {
                $end_time = date( 'g:i A', strtotime( $event['end_time'] ) ); // No timezone conversion
            }
            
            echo '<div class="event-card grid-block">';
            echo '<div class="event-header">';
            
            // Display time range
            $time_display = esc_html( $time );
            if ( $end_time ) {
                $time_display .= ' - ' . esc_html( $end_time );
            }
            echo '<p class="event-datetime">' . esc_html( $day ) . ', ' . esc_html( $event_date_short ) . ' at ' . $time_display . '</p>';
            echo '<h4 class="event-title">' . esc_html( $event['title'] ) . '</h4>';
            
            // Show guest/person names if they exist (up to 3)
            if ( ! empty( $event['people'] ) && is_array( $event['people'] ) ) {
                $people_count = count( $event['people'] );
                $people_display = array_slice( $event['people'], 0, 3 );
                $people_names = array_map( function( $person ) {
                    $name = trim( $person['first_name'] . ' ' . $person['last_name'] );
                    return ! empty( $name ) ? $name : ( $person['alt_name'] ?? '' );
                }, $people_display );
                $people_names = array_filter( $people_names );
                
                if ( ! empty( $people_names ) ) {
                    echo '<p class="event-people">';
                    echo 'With: ' . esc_html( implode( ', ', $people_names ) );
                    if ( $people_count > 3 ) {
                        echo ' (...)';
                    }
                    echo '</p>';
                }
            }
            if ( ! empty( $event['location'] ) ) {
                echo '<p class="event-room">' . esc_html( $event['location'] ) . '</p>';
            }
            echo '</div>';
            
            echo '<div class="event-details">';
            
            if ( ! empty( $event['description'] ) ) {
                echo '<p class="event-description">' . wp_kses_post( $event['description'] ) . '</p>';
                echo '<button class="read-more-btn" aria-expanded="false">Read more</button>';
            }
            
            // Show tags if they exist
            if ( ! empty( $event['schedule_tags'] ) && is_array( $event['schedule_tags'] ) ) {
                echo '<div class="event-tags">';
                foreach ( $event['schedule_tags'] as $tag ) {
                    $tag_slug = sanitize_title( $tag['tag'] );
                    echo '<button class="event-tag" data-tag="' . esc_attr( $tag_slug ) . '">' . esc_html( $tag['tag'] ) . '</button>';
                }
                echo '</div>';
            }
            
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>'; // Close grid-container
        echo '</div>'; // Close time-slot
    }
    
    echo '</div>'; // Close schedule-day
}

echo '</div>'; // Close schedule-wrapper

// ============================================================================
// JAVASCRIPT: Extract unique days and build filter tabs, handle filtering
// ============================================================================
?>
<script>
(function() {
    // Get all day containers and filter container
    const dayContainers = document.querySelectorAll('.schedule-day');
    const filterContainer = document.querySelector('.schedule-day-filters');
    const timeHeaders = document.querySelectorAll('.time-header');
    
    if (!dayContainers.length) return;
    
    // ===== ACCORDION FUNCTIONALITY =====
    timeHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const timeSlot = this.closest('.time-slot');
            timeSlot.classList.toggle('collapsed');
        });
        
        // Allow keyboard navigation (Enter key)
        header.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const timeSlot = this.closest('.time-slot');
                timeSlot.classList.toggle('collapsed');
            }
        });
    });
    
    // ===== DAY FILTER TABS =====
    // Extract unique days in order (Fri, Sat, Sun)
    const daysOrder = ['Friday', 'Saturday', 'Sunday'];
    const uniqueDays = [];
    
    dayContainers.forEach(container => {
        const day = container.dataset.day;
        if (!uniqueDays.includes(day)) {
            uniqueDays.push(day);
        }
    });
    
    // Create filter tabs container
    const tabsWrapper = document.createElement('div');
    tabsWrapper.className = 'button-group';
    
    // Create filter tabs in correct day order
    let firstTab = true;
    
    daysOrder.forEach(day => {
        if (uniqueDays.includes(day)) {
            const button = document.createElement('button');
            button.className = `button day-tab${firstTab ? ' active' : ''}`;
            button.dataset.day = day;
            button.textContent = day;
            if (firstTab) button.classList.add('button');
            tabsWrapper.appendChild(button);
            firstTab = false;
        }
    });
    
    filterContainer.appendChild(tabsWrapper);
    
    // Get the newly created tabs
    const dayTabs = document.querySelectorAll('.day-tab');
    const dayTitleElement = document.getElementById('schedule-day-title');
    
    // Function to filter by day
    function filterByDay(selectedDay) {
        dayContainers.forEach(container => {
            if (container.dataset.day === selectedDay) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        });
        
        // Update dynamic day title (set day name, preserve tag display span)
        if (dayTitleElement) {
            // Get current tag display content if it exists
            const currentTagDisplay = dayTitleElement.querySelector('#schedule-tag-filter-display')?.innerHTML || '';
            dayTitleElement.innerHTML = selectedDay + '<span id="schedule-tag-filter-display">' + currentTagDisplay + '</span>';
        }
    }
    
    // Show first day by default
    if (dayTabs.length > 0) {
        const firstDay = dayTabs[0].dataset.day;
        filterByDay(firstDay);
    }
    
    // Add click handlers to tabs
    dayTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            dayTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filter by day
            filterByDay(this.dataset.day);
        });
    });
    
    // ===== READ MORE/LESS FUNCTIONALITY =====
    const readMoreBtns = document.querySelectorAll('.read-more-btn');
    
    readMoreBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const description = this.previousElementSibling;
            const isExpanded = description.classList.contains('expanded');
            
            if (isExpanded) {
                description.classList.remove('expanded');
                this.textContent = 'Read more';
                this.setAttribute('aria-expanded', 'false');
            } else {
                description.classList.add('expanded');
                this.textContent = 'Read less';
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });
    
    // ===== TAG FILTER =====
    const tagButtons = document.querySelectorAll('.event-tag');
    const eventCards = document.querySelectorAll('.event-card');
    const clearFilterContainer = document.querySelector('.clear-filter-container');
    const clearFilterBtn = document.querySelector('.clear-filter-btn');
    let selectedTag = null;
    
    // Helper function to update filter state
    function updateFilterState(tag, tagText, tagElement) {
        // Update selected tag
        selectedTag = tag;
        
        // Update active classes
        if (tag) {
            if (tagElement) tagElement.classList.add('active');
        }
        
        // Update tag display in the day title
        const tagFilterDisplay = document.getElementById('schedule-tag-filter-display');
        if (tagFilterDisplay) {
            if (tag) {
                tagFilterDisplay.innerHTML = ` | ${tagText}`;
            } else {
                tagFilterDisplay.innerHTML = '';
            }
        }
        
        // Show/hide clear filter button
        if (clearFilterContainer) {
            if (tag) {
                clearFilterContainer.classList.remove('hidden');
            } else {
                clearFilterContainer.classList.add('hidden');
            }
        }
        
        // Filter events
        eventCards.forEach(card => {
            const cardTags = Array.from(card.querySelectorAll('.event-tag')).map(btn => btn.dataset.tag);
            
            if (!tag) {
                // No filter - show all
                card.classList.remove('hidden');
            } else {
                // Show if card has the selected tag
                if (cardTags.includes(tag)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            }
        });
        
        // Scroll to day title to show the filter indication
        if (tag) {
            const dayTitleElement = document.getElementById('schedule-day-title');
            if (dayTitleElement) {
                dayTitleElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }
    
    tagButtons.forEach(tagBtn => {
        tagBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const tag = this.dataset.tag;
            const tagText = this.textContent;
            
            if (selectedTag === tag) {
                // Deselect
                this.classList.remove('active');
                updateFilterState(null, '', null);
            } else {
                // Remove active class from previously selected tag
                if (selectedTag) {
                    document.querySelector(`.event-tag[data-tag="${selectedTag}"]`).classList.remove('active');
                }
                // Select new tag
                updateFilterState(tag, tagText, this);
            }
        });
    });
    
    // ===== CLEAR FILTER BUTTON =====
    if (clearFilterBtn) {
        clearFilterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from currently selected tag
            if (selectedTag) {
                document.querySelector(`.event-tag[data-tag="${selectedTag}"]`).classList.remove('active');
            }
            
            // Clear the filter
            updateFilterState(null, '', null);
        });
    }})();
</script>
<?php
} // END: if schedules not empty
} // END: if data exists
} // END: if not wp_error
} // END: if api_key exists (closes the main conditional - returns to showing "Coming soon" above)

/** END LEAP SCHEDULE DISPLAY */

// ============================================================================
// FILE STRUCTURE SUMMARY:
// ============================================================================
// 1. CONDITIONAL CHECK (lines ~8-18): Checks if ACF "leap_api_key" exists
//    - If EMPTY: Shows "Coming soon" placeholder
//    - If EXISTS: Runs the API code below
//
// 2. API FETCH (lines ~20-35): Gets data from Leap Conventions API
//    - Makes HTTP request with the API key
//    - Checks for connection errors
//
// 3. DATA PARSE (lines ~36-46): Processes the JSON response
//    - Converts JSON to PHP array
//    - Checks if schedules exist
//
// 4. DISPLAY LOOP (lines ~47-80): Outputs HTML for each event
//    - Creates event cards with title, time, location, description
//    - Uses CSS classes for styling
//
// 5. CLOSING BRACKETS: Closes all the conditional blocks (must match opening)
// ============================================================================


    get_footer();
?>
