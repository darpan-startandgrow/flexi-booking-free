/**
 * Check-ins page JavaScript — Free version.
 *
 * Handles manual check-in modal, search, bulk check-in processing,
 * view details, and status-change dropdown in the list table.
 * Uses REST API endpoints (sg-booking/v1/checkins/*).
 * QR scanner, export, and resend-email are Pro-only features.
 *
 * @since 1.3.0
 */

/**
 * Helper: make a REST API request.
 *
 * @param {string} endpoint  REST path relative to sg-booking/v1/ (e.g. 'checkins/status').
 * @param {string} method    HTTP method (GET, POST, etc.).
 * @param {object} data      Request body / query params.
 * @return {jQuery.jqXHR}
 */
function bmRestRequest(endpoint, method, data) {
    var url = bm_ajax_object.rest_url + endpoint;

    var settings = {
        url: url,
        method: method,
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', bm_ajax_object.rest_nonce);
        }
    };

    if (method === 'GET') {
        settings.data = data;
    } else {
        settings.contentType = 'application/json';
        settings.data = JSON.stringify(data);
    }

    return jQuery.ajax(settings);
}

jQuery(document).ready(function($) {

    // ─── Close modals ──────────────────────────────────────────────────
    $('.close, .manual-cancel-button').click(function() {
        $(this).closest('.checkin-default-modal').hide();
    });

    // ─── View booking details (from list table) ────────────────────────
    $(document).on('click', '.view-details', function(e) {
        e.preventDefault();
        var bookingId = $(this).data('id');
        bmRestRequest('checkins/details/' + bookingId, 'GET', {}).done(function(response) {
            if (response && response.html) {
                $('#order-details-content').html(response.html);
                $('#order-details-modal').show();
            } else {
                showMessage('Server error', 'error');
            }
        }).fail(function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Server error';
            showMessage(msg, 'error');
        });
    });

    // ─── Status dropdown change (per-row) ──────────────────────────────
    $(document).on('change', '.checkin-status-dropdown', function() {
        var checkinId = $(this).data('checkin-id');
        var bookingId = $(this).data('booking-id');
        var newStatus = $(this).val();

        if (newStatus) {
            bmRestRequest('checkins/status', 'POST', {
                booking_id: bookingId,
                checkin_id: checkinId,
                new_status: newStatus
            }).done(function(response) {
                showMessage(typeof bm_success_object !== 'undefined' && bm_success_object.status_successfully_changed ? bm_success_object.status_successfully_changed : 'Status updated.', 'success');
                window.location.reload();
            }).fail(function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Server error';
                showMessage(msg, 'error');
                window.location.reload();
            });
        }
    });

    // ─── Quick check-in button (per-row) ───────────────────────────────
    $(document).on('click', '.bm-checkin-action', function() {
        var id = $(this).data('id');
        if (!id) return;
        bmRestRequest('checkins/status', 'POST', {
            checkin_id: id,
            booking_id: 0,
            new_status: 'checked_in'
        }).done(function() {
            window.location.reload();
        }).fail(function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error';
            showMessage(msg, 'error');
        });
    });

    // ─── Manual Check-in Modal ─────────────────────────────────────────

    // Show/hide fields based on search type selection.
    $('#manual_checkin_type').change(function() {
        $('#manual_checkin-error').html('');
        $('#manual_checkin-result').html('');
        $('.manual-cherckin-buttons').addClass('hidden');
        $('.checkin-input').addClass('hidden');
        $('.select-checkin-input').addClass('hidden');
        if ($(this).val() === 'last_name') {
            $('#manual_checkin_lastname').removeClass('hidden');
        } else if ($(this).val() === 'email') {
            $('#manual_checkin_email').removeClass('hidden');
        } else if ($(this).val() === 'service') {
            if (typeof $.fn.multiselect !== 'undefined') {
                $('#manual_checkin_service').val([]).multiselect('reload');
            }
            $('#manual_checkin_service_span').removeClass('hidden');
        } else {
            $('#manual_checkin_reference').removeClass('hidden');
        }
    });

    // Open manual check-in modal.
    $('#manual-checkin-btn').click(function() {
        $('#manual_checkin-result').html('');
        $('#manual_checkin-error').html('');
        $('.checkin-input').val('');
        if (typeof $.fn.multiselect !== 'undefined') {
            $('#manual_checkin_service').val([]).multiselect('reload');
        }
        $('.manual-cherckin-buttons').addClass('hidden');
        $('#manual_checkin_type').val('last_name').trigger('change');
        $('#manual_checkin-modal').show();
    });

    // Handle search button in modal.
    $('#manual-checkin-search').click(function(e) {
        e.preventDefault();
        $('#manual_checkin-result').html('');
        $('#manual_checkin-error').html('');
        $('.manual-cherckin-buttons').addClass('hidden');

        var searchType = $('#manual_checkin_type').val();
        var searchValue = '';

        if (searchType === 'last_name') {
            searchValue = $('#manual_checkin_lastname').val().trim();
            if (!searchValue) {
                $('#manual_checkin-error').html(typeof bm_normal_object !== 'undefined' ? bm_normal_object.enter_last_name : 'Please enter last name');
                return false;
            }
        } else if (searchType === 'email') {
            searchValue = $('#manual_checkin_email').val().trim();
            if (!searchValue) {
                $('#manual_checkin-error').html(typeof bm_normal_object !== 'undefined' ? bm_normal_object.enter_email : 'Please enter email');
                return false;
            }
            var emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,6}$/i;
            if (!emailPattern.test(searchValue)) {
                $('#manual_checkin-error').html(typeof bm_error_object !== 'undefined' ? bm_error_object.invalid_email : 'Invalid email');
                return false;
            }
        } else if (searchType === 'service') {
            searchValue = $('#manual_checkin_service').val();
            if (!searchValue) {
                $('#manual_checkin-error').html(typeof bm_normal_object !== 'undefined' ? bm_normal_object.select_a_service : 'Please select a service');
                return false;
            }
        } else {
            searchValue = $('#manual_checkin_reference').val().trim();
            if (!searchValue) {
                $('#manual_checkin-error').html(typeof bm_normal_object !== 'undefined' ? bm_normal_object.enter_reference_no : 'Please enter reference number');
                return false;
            }
        }

        bmRestRequest('checkins/search', 'POST', {
            search_type: searchType,
            search_value: searchValue
        }).done(function(response) {
            if (response && response.html) {
                $('#manual_checkin-result').html(response.html);
                if (typeof $.fn.DataTable !== 'undefined') {
                    $('.manual_checkin_records_table').DataTable();
                }
                $('.manual-cherckin-buttons').removeClass('hidden');
            } else {
                $('#manual_checkin-error').html('No results found');
                $('.manual-cherckin-buttons').addClass('hidden');
            }
        }).fail(function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Server error';
            $('#manual_checkin-error').html(msg);
            $('.manual-cherckin-buttons').addClass('hidden');
        });
    });
});


/**
 * Process manual check-in for selected bookings.
 */
function bm_checkin_manually() {
    var searchType = jQuery('#manual_checkin_type').val();
    var searchValue = '';
    var bookingIds = [];

    if (searchType === 'last_name') {
        searchValue = jQuery('#manual_checkin_lastname').val().trim();
        if (!searchValue) {
            jQuery('#manual_checkin-error').html(typeof bm_normal_object !== 'undefined' ? bm_normal_object.enter_last_name : 'Please enter last name');
            return false;
        }
        jQuery('.bm-booking-select:checked').each(function () {
            bookingIds.push(jQuery(this).val());
        });
    } else if (searchType === 'email') {
        searchValue = jQuery('#manual_checkin_email').val().trim();
        if (!searchValue) {
            jQuery('#manual_checkin-error').html(typeof bm_normal_object !== 'undefined' ? bm_normal_object.enter_email : 'Please enter email');
            return false;
        }
        var emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,6}$/i;
        if (!emailPattern.test(searchValue)) {
            jQuery('#manual_checkin-error').html(typeof bm_error_object !== 'undefined' ? bm_error_object.invalid_email : 'Invalid email');
            return false;
        }
        jQuery('.bm-booking-select:checked').each(function () {
            bookingIds.push(jQuery(this).val());
        });
    } else if (searchType === 'service') {
        searchValue = jQuery('#manual_checkin_service').val();
        if (!searchValue) {
            jQuery('#manual_checkin-error').html(typeof bm_normal_object !== 'undefined' ? bm_normal_object.select_a_service : 'Please select a service');
            return false;
        }
        jQuery('.bm-booking-select:checked').each(function () {
            bookingIds.push(jQuery(this).val());
        });
    } else {
        searchValue = jQuery('#manual_checkin_reference').val().trim();
        if (!searchValue) {
            jQuery('#manual_checkin-error').html(typeof bm_normal_object !== 'undefined' ? bm_normal_object.enter_reference_no : 'Please enter reference number');
            return false;
        }
    }

    if ((searchType === 'email' || searchType === 'last_name' || searchType === 'service') && bookingIds.length === 0) {
        jQuery('#manual_checkin-error').html(typeof bm_normal_object !== 'undefined' && bm_normal_object.no_selection ? bm_normal_object.no_selection : 'Please select at least one booking.');
        return false;
    }

    jQuery('#resendProcess').removeClass('hidden');
    jQuery('#manual-checkin-button').prop('disabled', true);

    bmRestRequest('checkins/process', 'POST', {
        search_type: searchType,
        search_value: searchValue,
        booking_ids: bookingIds
    }).done(function(response) {
        jQuery('#resendProcess').addClass('hidden');
        jQuery('.manual-cherckin-buttons').addClass('hidden');

        var message = (response && response.message) ? response.message : 'Check-in complete';
        jQuery('#manual_checkin-result').html('<p class="success">' + message + '</p>');
        setTimeout(function() {
            jQuery('#manual_checkin-modal').hide();
            window.location.reload();
        }, 2000);
    }).fail(function(xhr) {
        jQuery('#resendProcess').addClass('hidden');
        jQuery('.manual-cherckin-buttons').addClass('hidden');
        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Server error';
        jQuery('#manual_checkin-error').html(msg);
    });
}


// Handle "Check All" toggle in manual check-in modal.
jQuery(document).on('change', '#bm-checkall', function() {
    var checked = jQuery(this).is(':checked');
    jQuery('.bm-booking-select').prop('checked', checked);
});


// View details per booking (eye icon) in manual check-in modal.
jQuery(document).on('click', '.bm-view-details', function(e) {
    e.preventDefault();
    jQuery('.checkin-order-details-container').html('');
    jQuery('#checkin-order-details-modal').addClass('active-modal');
    jQuery('#loader_modal').show();

    var bookingId = jQuery(this).data('id');
    if (!bookingId) return;

    bmRestRequest('checkins/details/' + bookingId, 'GET', {}).done(function(response) {
        jQuery('#loader_modal').hide();
        if (response && response.html) {
            jQuery('.checkin-order-details-container').html(response.html);
        } else {
            jQuery('.checkin-order-details-container').html('No data found');
        }
    }).fail(function(xhr) {
        jQuery('#loader_modal').hide();
        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Server error';
        jQuery('.checkin-order-details-container').html(msg);
    });
});


/**
 * Show a popup message notification.
 */
function showMessage(message, type) {
    var $overlay = jQuery('#popup-message-overlay');
    var $container = jQuery('#popup-message-container');
    var $msg = jQuery('#popup-message');

    $msg.html(message);
    $container.removeClass('bm-msg-success bm-msg-error');
    $container.addClass(type === 'success' ? 'bm-msg-success' : 'bm-msg-error');
    $overlay.show();
    $container.show();

    jQuery('#close-popup-message').off('click').on('click', function() {
        $overlay.hide();
        $container.hide();
    });

    setTimeout(function() {
        $overlay.hide();
        $container.hide();
    }, 4000);
}
