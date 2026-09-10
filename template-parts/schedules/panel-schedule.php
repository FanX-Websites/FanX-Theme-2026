<?php 
/** 
 * Template Part: Panel Schedule
 * Displays events from the Leap API filtered by specific venue locations
 */

// Get the Leap API key from ACF options
$leap_api_key = get_field( 'leap_api_key', 'option' );

if ( empty( $leap_api_key ) ) {
    echo '<div class="schedule-wrapper">';
    echo '<p><em>Schedule coming soon...</em></p>';
    echo '</div>';
    return;
}

// Fetch data from Leap API
$api_url = 'https://conventions.leapevent.tech/api/schedules?key=' . urlencode( $leap_api_key );

$response = wp_remote_get( $api_url, array(
    'timeout'   => 10,
    'headers'   => array( 'accept' => 'application/json' )
) );

if ( is_wp_error( $response ) ) {
    echo '<p>Error fetching schedule: ' . esc_html( $response->get_error_message() ) . '</p>';
    return;
}

$body = wp_remote_retrieve_body( $response );
$data = json_decode( $body, true );

if ( ! $data || ! isset( $data['schedules'] ) || empty( $data['schedules'] ) ) {
    echo '<p>No schedule data available.</p>';
    return;
}

$schedules = $data['schedules'];

// Group events by day and time
// Days are derived from the actual event dates so any combination/order returned by the API is handled
$events_by_day = array();
$earliest_date_for_day = array();
foreach ( $schedules as $event ) {
    $event_date = substr( $event['start_time'], 0, 10 );
    $event_day = date( 'l', strtotime( $event_date ) );
    $event_time = date( 'g:i a', strtotime( $event['start_time'] ) );
    
    if ( ! isset( $events_by_day[ $event_day ] ) ) {
        $events_by_day[ $event_day ] = array();
    }
    
    if ( ! isset( $events_by_day[ $event_day ][ $event_time ] ) ) {
        $events_by_day[ $event_day ][ $event_time ] = array();
    }
    
    $events_by_day[ $event_day ][ $event_time ][] = $event;
    
    if ( ! isset( $earliest_date_for_day[ $event_day ] ) || $event_date < $earliest_date_for_day[ $event_day ] ) {
        $earliest_date_for_day[ $event_day ] = $event_date;
    }
}

// Sort days chronologically by each day's earliest actual date
uksort( $events_by_day, function( $a, $b ) use ( $earliest_date_for_day ) {
    return strcmp( $earliest_date_for_day[ $a ], $earliest_date_for_day[ $b ] );
} );
$sorted_days = $events_by_day;

?>

<div class="schedule-wrapper">
    <div class="schedule-day-filters"></div>
    
<div class="panel-schedule-tag-filter self-centered-column">
    <div class="panel schedule-tag-filter-info hidden">
        <span id="schedule-tag-filter-display"></span>
        <button class="clear-filter-btn">Clear filter</button>
    </div>
</div><!--- END Panel Schedule tag Filter -->

    <?php foreach ( $sorted_days as $day => $time_slots ) : ?>
        <div class="schedule-day" data-day="<?php echo esc_attr( $day ); ?>">
            <?php 
            // Sort times chronologically
            uksort( $time_slots, function( $a, $b ) {
                return strtotime( $a ) - strtotime( $b );
            });
            ?>
            
            <?php foreach ( $time_slots as $time => $events ) : ?>
                <div class="time-slot" data-time="<?php echo esc_attr( $time ); ?>">
                    <h3 class="time-header" role="button" tabindex="0"><?php echo esc_html( $time ); ?></h3>
                    <div class=" grid-container">
                        
                        <?php foreach ( $events as $event ) : ?>
                            <?php
                            $event_date = substr( $event['start_time'], 0, 10 );
                            $event_date_short = date( 'M j', strtotime( $event_date ) );
                            
                            // Extract end time
                            $end_time = '';
                            if ( ! empty( $event['end_time'] ) ) {
                                $end_time = date( 'g:i A', strtotime( $event['end_time'] ) );
                            }
                            
                            ?>
                            
                            <div class="event-card grid-block">
                                <div class="event-header">
                                    <?php
                                    // Display time range
                                    $time_display = esc_html( $time );
                                    if ( $end_time ) {
                                        $time_display .= ' - ' . esc_html( $end_time );
                                    }
                                    ?>
                                    <p class="event-datetime"><?php echo esc_html( $day ) . ', ' . esc_html( $event_date_short ) . ' at ' . $time_display; ?></p>
                                    <h4 class="event-title"><?php echo esc_html( $event['title'] ); ?></h4>
                                    
                                    <?php if ( ! empty( $event['people'] ) && is_array( $event['people'] ) ) : ?>
                                        <?php
                                        $people_count = count( $event['people'] );
                                        $people_display = array_slice( $event['people'], 0, 3 );
                                        $people_names = array_map( function( $person ) {
                                            $name = trim( $person['first_name'] . ' ' . $person['last_name'] );
                                            return ! empty( $name ) ? $name : ( $person['alt_name'] ?? '' );
                                        }, $people_display );
                                        $people_names = array_filter( $people_names );
                                        ?>
                                        <?php if ( ! empty( $people_names ) ) : ?>
                                            <p class="event-people">
                                                With: <?php echo esc_html( implode( ', ', $people_names ) ); ?>
                                                <?php if ( $people_count > 3 ) echo ' (...)'; ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if ( ! empty( $event['location'] ) ) : ?>
                                        <p class="event-room"><?php echo esc_html( $event['location'] ); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="event-details">
                                    <?php if ( ! empty( $event['description'] ) ) : ?>
                                        <p class="event-description" style="display: none;"><?php echo wp_kses_post( $event['description'] ); ?></p>
                                        <button class="read-more-btn" aria-expanded="false">Read more</button>
                                    <?php endif; ?>
                                    
                                    <?php if ( ! empty( $event['schedule_tags'] ) && is_array( $event['schedule_tags'] ) ) : ?>
                                        <div class="event-tags">
                                            <?php foreach ( $event['schedule_tags'] as $tag ) : ?>
                                                <?php $tag_slug = sanitize_title( $tag['tag'] ); ?>
                                                <button class="event-tag" data-tag="<?php echo esc_attr( $tag_slug ); ?>">
                                                    <?php echo esc_html( $tag['tag'] ); ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
(function() {
    const dayContainers = document.querySelectorAll('.schedule-day');
    const filterContainer = document.querySelector('.schedule-day-filters');
    const timeHeaders = document.querySelectorAll('.time-header');
    
    if (!dayContainers.length) return;
    
    // Accordion functionality
    timeHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const timeSlot = this.closest('.time-slot');
            timeSlot.classList.toggle('collapsed');
        });
        
        header.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const timeSlot = this.closest('.time-slot');
                timeSlot.classList.toggle('collapsed');
            }
        });
    });
    
    // Day filter tabs - days come from the DOM in the order PHP already sorted them (chronological by actual date)
    const uniqueDays = [];
    
    dayContainers.forEach(container => {
        const day = container.dataset.day;
        if (!uniqueDays.includes(day)) {
            uniqueDays.push(day);
        }
    });
    
    const tabsWrapper = document.createElement('div');
    tabsWrapper.className = 'button-group';
    
    let firstTab = true;
    uniqueDays.forEach(day => {
        const button = document.createElement('button');
        button.className = `button day-tab${firstTab ? ' active' : ''}`;
        button.dataset.day = day;
        button.textContent = day;
        tabsWrapper.appendChild(button);
        firstTab = false;
    });
    
    filterContainer.appendChild(tabsWrapper);
    
    const dayTabs = document.querySelectorAll('.day-tab');
    
    function filterByDay(selectedDay) {
        dayContainers.forEach(container => {
            if (container.dataset.day === selectedDay) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        });
    }
    
    if (dayTabs.length > 0) {
        const firstDay = dayTabs[0].dataset.day;
        filterByDay(firstDay);
    }
    
    dayTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            dayTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            filterByDay(this.dataset.day);
        });
    });
    
    // ===== READ MORE FUNCTIONALITY =====
    const readMoreButtons = document.querySelectorAll('.read-more-btn');
    readMoreButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const eventDetails = this.closest('.event-details');
            const description = eventDetails.querySelector('.event-description');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                description.style.display = 'none';
                this.setAttribute('aria-expanded', 'false');
                this.textContent = 'Read more';
            } else {
                description.style.display = 'block';
                this.setAttribute('aria-expanded', 'true');
                this.textContent = 'Read less';
            }
        });
    });
    
    // ===== TAG FILTER =====
    const tagButtons = document.querySelectorAll('.event-tag');
    const eventCards = document.querySelectorAll('.event-card');
    const tagFilterInfo = document.querySelector('.schedule-tag-filter-info');
    const clearFilterBtn = document.querySelector('.clear-filter-btn');
    let selectedTag = null;
    
    function updateFilterState(tag, tagText, tagElement) {
        selectedTag = tag;
        if (tag && tagElement) tagElement.classList.add('active');
        const tagFilterDisplay = document.getElementById('schedule-tag-filter-display');
        if (tagFilterDisplay) {
            tagFilterDisplay.innerHTML = tag ? tagText : '';
        }
        if (tagFilterInfo) {
            tag ? tagFilterInfo.classList.remove('hidden') : tagFilterInfo.classList.add('hidden');
        }
        
        applyFilters();
    }
    
    function applyFilters() {
        eventCards.forEach(card => {
            const cardTags = Array.from(card.querySelectorAll('.event-tag')).map(btn => btn.dataset.tag);
            const tagMatches = (!selectedTag || cardTags.includes(selectedTag));
            
            if (tagMatches) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }
    
    tagButtons.forEach(tagBtn => {
        tagBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const tag = this.dataset.tag;
            const tagText = this.textContent;
            if (selectedTag === tag) {
                this.classList.remove('active');
                updateFilterState(null, '', null);
            } else {
                if (selectedTag) {
                    document.querySelector(`.event-tag[data-tag="${selectedTag}"]`).classList.remove('active');
                }
                updateFilterState(tag, tagText, this);
            }
        });
    });
    
    if (clearFilterBtn) {
        clearFilterBtn.addEventListener('click', function() {
            if (selectedTag) {
                document.querySelector(`.event-tag[data-tag="${selectedTag}"]`).classList.remove('active');
            }
            updateFilterState(null, '', null);
        });
    }
})();
</script>


