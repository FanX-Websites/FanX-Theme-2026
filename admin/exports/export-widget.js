(function($) {
    'use strict';
    
    /**
     * Initialize export widget functionality
     */
    $(document).ready(function() {
        initializeExportTabs();
        initializeExportScheduling();
    });
    
    /**
     * Initialize tab switching functionality
     */
    function initializeExportTabs() {
        $(document).on('click', '.fanx-export-tab-button', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const tabName = $button.data('tab');
            
            // Remove active class from all buttons and panes
            $('.fanx-export-tab-button').removeClass('active');
            $('.fanx-export-tab-pane').removeClass('active');
            
            // Add active class to clicked button and corresponding pane
            $button.addClass('active');
            $('.fanx-export-tab-pane[data-tab="' + tabName + '"]').addClass('active');
        });
    }
    
    /**
     * Initialize export scheduling functionality
     */
    function initializeExportScheduling() {
        // Handle clear button
        $(document).on('click', '#fanx-clear-export-btn', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const nonce = $button.data('nonce');
            
            console.log('Clear button clicked. Nonce:', nonce);
            
            if ( ! confirm('Are you sure you want to clear the scheduled export?') ) {
                console.log('User cancelled clear action');
                return;
            }
            
            console.log('User confirmed. Sending AJAX request...');
            
            // Disable button and show loading
            $button.prop('disabled', true);
            $('#fanx-export-loading').show();
            $('#fanx-export-message').empty();
            
            // Make AJAX request
            $.ajax({
                url: fanxExportWidget.ajaxurl,
                type: 'POST',
                data: {
                    action: 'fanx_clear_export',
                    nonce: nonce,
                },
                success: function(response) {
                    console.log('AJAX success response:', response);
                    if (response.success) {
                        showSuccessMessage(response.data.message);
                        // Reload the widget after 2 seconds
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $button.prop('disabled', false);
                        console.error('AJAX error:', response.data.message);
                        showErrorMessage(response.data.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $button.prop('disabled', false);
                    console.error('AJAX error:', textStatus, errorThrown, jqXHR);
                    showErrorMessage('Failed to clear export. Check console for details.');
                },
                complete: function() {
                    $('#fanx-export-loading').hide();
                }
            });
        });
        
        // Handle schedule button
        $(document).on('click', '#fanx-schedule-export-btn', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const nonce = $button.data('nonce');
            const scheduledTimeInput = $('#fanx-export-datetime').val();
            
            // Validate datetime input
            if ( ! scheduledTimeInput ) {
                showErrorMessage('Please select a date and time for the export');
                return;
            }
            
            console.log('Scheduling export for:', scheduledTimeInput);
            
            // Disable button and show loading
            $button.prop('disabled', true);
            $('#fanx-export-loading').show();
            $('#fanx-export-message').empty();
            
            // Make AJAX request with datetime string
            $.ajax({
                url: fanxExportWidget.ajaxurl,
                type: 'POST',
                data: {
                    action: 'fanx_schedule_export',
                    nonce: nonce,
                    scheduled_time: scheduledTimeInput,
                },
                success: function(response) {
                    if (response.success) {
                        showSuccessMessage(response.data.message);
                        // Reload the widget after 2 seconds to show updated status
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showErrorMessage(response.data.message);
                        $button.prop('disabled', false);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Export scheduling error:', textStatus, errorThrown);
                    showErrorMessage('An error occurred while scheduling the export. Please try again.');
                    $button.prop('disabled', false);
                },
                complete: function() {
                    $('#fanx-export-loading').hide();
                }
            });
        });
    }
    
    /**
     * Display success message
     */
    function showSuccessMessage(message) {
        const $messageDiv = $('#fanx-export-message');
        $messageDiv.html('<div class="fanx-export-message-success">✓ ' + message + '</div>');
    }
    
    /**
     * Display error message
     */
    function showErrorMessage(message) {
        const $messageDiv = $('#fanx-export-message');
        $messageDiv.html('<div class="fanx-export-message-error">✗ ' + message + '</div>');
    }
    
})(jQuery);
