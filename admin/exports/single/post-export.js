/**
 * Post Export Metabox - Frontend JavaScript
 * 
 * Handles:
 * - Schedule button clicks
 * - Cancel button clicks
 * - AJAX communication with backend
 * - UI status updates
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        initializePostExportScheduling();
    });

    /**
     * Initialize post export scheduling functionality
     */
    function initializePostExportScheduling() {
        // Schedule button
        $(document).on('click', '.fanx-schedule-post-export', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var postId = $btn.data('post-id');
            var nonce = $btn.data('nonce');
            var $datetime = $('.fanx-post-export-datetime[data-post-id="' + postId + '"]');
            var scheduledTime = $datetime.val();

            // Validate datetime input
            if (!scheduledTime) {
                showPostExportMessage(postId, 'Please select a date and time', 'error');
                return;
            }

            // Show loading state
            $btn.prop('disabled', true);
            $btn.text('Scheduling...');

            // Send AJAX request
            $.ajax({
                url: fanxPostExport.ajaxurl,
                type: 'POST',
                data: {
                    action: 'fanx_schedule_post_export',
                    post_id: postId,
                    scheduled_time: scheduledTime,
                    nonce: nonce,
                },
                success: function (response) {
                    if (response.success) {
                        showPostExportMessage(postId, response.data.message, 'success');
                        // Reload metabox to show updated status
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    } else {
                        showPostExportMessage(postId, response.data.message, 'error');
                        $btn.prop('disabled', false);
                        $btn.text('⏱️ Schedule Export');
                    }
                },
                error: function () {
                    showPostExportMessage(postId, 'An error occurred. Please try again.', 'error');
                    $btn.prop('disabled', false);
                    $btn.text('⏱️ Schedule Export');
                },
            });
        });

        // Cancel button
        $(document).on('click', '.fanx-cancel-post-export', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var postId = $btn.data('post-id');
            var nonce = $btn.data('nonce');

            // Confirm cancellation
            if (!confirm('Are you sure you want to cancel this scheduled export?')) {
                return;
            }

            // Show loading state
            $btn.prop('disabled', true);
            $btn.text('Cancelling...');

            // Send AJAX request
            $.ajax({
                url: fanxPostExport.ajaxurl,
                type: 'POST',
                data: {
                    action: 'fanx_cancel_post_export',
                    post_id: postId,
                    nonce: nonce,
                },
                success: function (response) {
                    if (response.success) {
                        showPostExportMessage(postId, response.data.message, 'success');
                        // Reload metabox to show updated status
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    } else {
                        showPostExportMessage(postId, response.data.message, 'error');
                        $btn.prop('disabled', false);
                        $btn.text('✕ Cancel Export');
                    }
                },
                error: function () {
                    showPostExportMessage(postId, 'An error occurred. Please try again.', 'error');
                    $btn.prop('disabled', false);
                    $btn.text('✕ Cancel Export');
                },
            });
        });
    }

    /**
     * Display a message in the post export message area
     *
     * @param {number} postId The post ID
     * @param {string} message The message text
     * @param {string} type 'success' or 'error'
     */
    function showPostExportMessage(postId, message, type) {
        var $messageBox = $('#fanx-post-export-message');
        var className = type === 'success' ? 'fanx-export-success' : 'fanx-export-error';
        var bgColor = type === 'success' ? '#d4edda' : '#f8d7da';
        var borderColor = type === 'success' ? '#28a745' : '#dc3545';
        var textColor = type === 'success' ? '#155724' : '#721c24';

        $messageBox.html(
            '<div style="background: ' +
            bgColor +
            '; border: 1px solid ' +
            borderColor +
            '; padding: 8px; border-radius: 3px; color: ' +
            textColor +
            '; font-size: 12px;">' +
            message +
            '</div>'
        );

        // Auto-clear success message after 3 seconds
        if (type === 'success') {
            setTimeout(function () {
                $messageBox.fadeOut(function () {
                    $messageBox.html('').show();
                });
            }, 3000);
        }
    }
})(jQuery);
