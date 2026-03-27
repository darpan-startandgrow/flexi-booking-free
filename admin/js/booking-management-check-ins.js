/**
 * Check-ins page JavaScript — Free version.
 *
 * Handles manual check-in modal, search, bulk check-in processing,
 * view details, and status-change dropdown in the list table.
 * QR scanner, export, and resend-email are Pro-only features.
 *
 * @since 1.3.0
 */
jQuery(document).ready(function($) {

    // ─── Close modals ──────────────────────────────────────────────────
    $('.close, .manual-cancel-button').click(function() {
        $(this).closest('.checkin-default-modal').hide();
    });

    // ─── View booking details (from list table) ────────────────────────
    $(document).on('click', '.view-details', function(e) {
        e.preventDefault();
        var bookingId = $(this).data('id');
        $.post(bm_ajax_object.ajax_url, {
            action: 'manual_checkin_view_details',
            booking_id: bookingId,
            nonce: bm_ajax_object.nonce
        }, function(response) {
            if (response.success) {
                $('#order-details-content').html(response.data);
                $('#order-details-modal').show();
            } else {
                showMessage(response.data ? response.data : 'Server error', 'error');
            }
        });
    });

    // ─── Status dropdown change (per-row) ──────────────────────────────
    $(document).on('change', '.checkin-status-dropdown', function() {
        var checkinId = $(this).data('checkin-id');
        var bookingId = $(this).data('booking-id');
        var newStatus = $(this).val();

        if (newStatus) {
            $.post(bm_ajax_object.ajax_url, {
                action: 'update_checkin_status',
                booking_id: bookingId,
                checkin_id: checkinId,
                new_status: newStatus,
                nonce: bm_ajax_object.nonce
            }, function(response) {
                if (response.success) {
                    showMessage(typeof bm_success_object !== 'undefined' && bm_success_object.status_successfully_changed ? bm_success_object.status_successfully_changed : 'Status updated.', 'success');
                    window.location.reload();
                } else {
                    showMessage(response.data ? response.data : 'Server error', 'error');
                    window.location.reload();
                }
            });
        }
    });

    // ─── Quick check-in button (per-row) ───────────────────────────────
    $(document).on('click', '.bm-checkin-action', function() {
        var id = $(this).data('id');
        if (!id) return;
        $.post(bm_ajax_object.ajax_url, {
            action: 'update_checkin_status',
            checkin_id: id,
            booking_id: 0,
            new_status: 'checked_in',
            nonce: bm_ajax_object.nonce
        }, function(response) {
            if (response.success) {
                window.location.reload();
            } else {
                showMessage(response.data ? response.data : 'Error', 'error');
            }
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

        $.post(bm_ajax_object.ajax_url, {
            action: 'manual_checkin_check',
            search_type: searchType,
            search_value: searchValue,
            nonce: bm_ajax_object.nonce
        }, function(response) {
            if (response.success) {
                $('#manual_checkin-result').html(response.data);
                if (typeof $.fn.DataTable !== 'undefined') {
                    jQuery('.manual_checkin_records_table').DataTable();
                }
                $('.manual-cherckin-buttons').removeClass('hidden');
            } else {
                $('#manual_checkin-error').html(response.data ? response.data : 'Server error');
                $('.manual-cherckin-buttons').addClass('hidden');
            }
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

    jQuery.post(bm_ajax_object.ajax_url, {
        action: 'manual_checkin_process',
        search_type: searchType,
        search_value: searchValue,
        booking_ids: bookingIds,
        nonce: bm_ajax_object.nonce
    }, function(response) {
        jQuery('#resendProcess').addClass('hidden');
        jQuery('.manual-cherckin-buttons').addClass('hidden');

        if (response.success) {
            jQuery('#manual_checkin-result').html('<p class="success">' + response.data.message + '</p>');
            setTimeout(function() {
                jQuery('#manual_checkin-modal').hide();
                window.location.reload();
            }, 2000);
        } else {
            jQuery('#manual_checkin-error').html(response.data ? response.data : 'Server error');
        }
    }).fail(function() {
        jQuery('#resendProcess').addClass('hidden');
        jQuery('.manual-cherckin-buttons').addClass('hidden');
        jQuery('#manual_checkin-error').html(typeof bm_error_object !== 'undefined' ? bm_error_object.server_error : 'Server error');
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

    jQuery.post(bm_ajax_object.ajax_url, {
        action: 'manual_checkin_view_details',
        booking_id: bookingId,
        nonce: bm_ajax_object.nonce
    }, function(response) {
        jQuery('#loader_modal').hide();
        if (response.success) {
            jQuery('.checkin-order-details-container').html(response.data);
        } else {
            jQuery('.checkin-order-details-container').html(response.data ? response.data : 'Server error');
        }
    }).fail(function() {
        jQuery('#loader_modal').hide();
        jQuery('.checkin-order-details-container').html(typeof bm_error_object !== 'undefined' ? bm_error_object.server_error : 'Server error');
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
