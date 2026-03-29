/**
 * BMPublicCalendar - Calendar-related methods for public area.
 * @since 1.1.0
 */
class BMPublicCalendar {
    static initiateServiceCalendar($container, service_id = 0, $this = '') {
        let $datepicker = jQuery('#' + $container).find('.service_by_category_calendar');

        if ($container == 'service_calendar_details') {
            $datepicker = jQuery($this).find('.service-by-id-calendar');
        }

        $datepicker.datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0,
            //---^----------- if closed by default (when you're using <input>)
            beforeShowDay: function (date) {
                var returnday = "weekday";
                return [true, returnday];
            },
            onChangeMonthYear: function () { bm_get_service_price($this, $container, service_id) },
            onSelect: function (date, inst) {
                if ($container == 'calendar_and_slot_details') {
                    jQuery(this).parents('.modal').find('.loader_modal').css('top', 'initial');
                    jQuery(this).parents('.modal').find('.loader_modal').show();
                }

                jQuery($datepicker).find('.ui-datepicker-calendar td').removeClass('custom-highlight');
                var selectedDateElement = jQuery(inst.dpDiv).find('[data-year="' + inst.selectedYear + '"][data-month="' + inst.selectedMonth + '"]').filter(function () {
                    return jQuery(this).find('a').text() == inst.selectedDay;
                });

                if (selectedDateElement.length) {
                    selectedDateElement.addClass('custom-highlight');
                }

                if (!service_id) {
                    service_id = strict_decode(jQuery('#current_service_id').val());
                }

                if ($container == 'calendar_and_slot_details') {
                    jQuery('#booking_date2').val(date);
                } else {
                    jQuery('.loader_modal').show();
                    jQuery('.calendar_shortcode_error_message').html('');
                    jQuery(this).parents('.' + $container).find('.booknowbtn').children('a').attr('data-service-date', date);
                }

                if ($container == 'calendar_and_slot_details') {
                    jQuery('#' + $container).find('div.calender-modal .booking-status .selected_date_div').text(jQuery.datepicker.formatDate('DD, MM dd, yy', new Date(date)));
                }

                var element = jQuery(inst.dpDiv).find('[data-year="' + inst.selectedYear + '"][data-month="' + inst.selectedMonth + '"]').filter(function () {
                    return jQuery(this).find('a').text() == inst.selectedDay;
                }).find("a", "span");

                if (!element.hasClass('not_available_for_booking')) {
                    var post = {
                        'date': date,
                        'id': service_id,
                        'type': $container == 'calendar_and_slot_details' ? 'service_by_category2' : '',
                    }

                    if ($container == 'calendar_and_slot_details') {
                        var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
                        bmPublicRestRequest('bm_fetch_frontend_service_time_slots', data, function (response) {
                            jQuery('.loader_modal').hide();
                            jQuery('#' + $container).find('.modalcontentbox').html('');
                            var jsondata = bmSafeParse(response);
                            var status = jsondata.status ? jsondata.status : '';
                            var data = jsondata.data ? jsondata.data : '';

                            if (status == false) {
                                jQuery('#' + $container).find('.modalcontentbox').html('<div class="no_slots_class">' + bm_error_object.server_error + '</div>');
                            } else if (data != null && data != '' && status == true) {
                                jQuery('#' + $container).find('.modalcontentbox').html(data);
                            } else {
                                jQuery('#' + $container).find('.modalcontentbox').html('<div class="no_slots_class">' + bm_error_object.server_error + '</div>');
                            }
                        });
                    } else {
                        const data = {
                            action: 'bm_fetch_service_calendar_time_slots',
                            post: post,
                            nonce: bm_ajax_object.nonce
                        };

                        const $btn = jQuery(this).parents('.' + $container).find('.booknowbtn');
                        const $link = $btn.children('a');
                        const disabledColor = '#d3d3d3';

                        const btnBgColor = bm_normal_object.svc_button_colour;

                        bmPublicRestRequest('bm_fetch_frontend_service_time_slots', data)
                            .done(function (response) {
                                jQuery('.loader_modal').hide();
                                if (response.success && response.data.is_bookable) {
                                    $btn.removeClass('readonly_div');
                                    $link.removeClass('inactiveLink');
                                    $btn.addClass('textblue bordercolor');
                                    $link.addClass('get_slot_details');
                                    $btn.attr('style', `background-color: ${btnBgColor} !important;`);
                                } else {
                                    $btn.removeClass('textblue bordercolor');
                                    $link.removeClass('get_slot_details');
                                    $btn.addClass('readonly_div');
                                    $link.addClass('inactiveLink');
                                    $btn.attr('style', `background-color: ${disabledColor} !important;`);

                                    const errorMessage = response.data.message ? response.data.message : bm_error_object.server_error;
                                    $btn.parent('.productbottombar').find('.calendar_shortcode_error_message').html(errorMessage);
                                }
                            })
                            .fail(function () {
                                jQuery('.loader_modal').hide();
                                $btn.removeClass('textblue bordercolor');
                                $link.removeClass('get_slot_details');
                                $btn.addClass('readonly_div');
                                $link.addClass('inactiveLink');
                                $btn.attr('style', `background-color: ${disabledColor} !important;`);
                                $btn.parent('.productbottombar').find('.calendar_shortcode_error_message').html(bm_error_object.server_error);
                            });
                    }
                } else {
                    jQuery('.loader_modal').hide();
                    if ($container == 'calendar_and_slot_details') {
                        jQuery('#' + $container).find('.modalcontentbox').html('<div class="no_slots_class">' + bm_error_object.service_unavailable + '</div>');
                    } else {
                        const $btn = jQuery(this).parents('.' + $container).find('.booknowbtn');
                        const $link = $btn.children('a');
                        const disabledColor = '#d3d3d3';

                        $btn.removeClass('textblue bordercolor');
                        $link.removeClass('get_slot_details');
                        $btn.addClass('readonly_div');
                        $link.addClass('inactiveLink');
                        $btn.attr('style', `background-color: ${disabledColor} !important;`);
                        $btn.parent('.productbottombar').find('.calendar_shortcode_error_message').html(bm_error_object.service_unavailable);
                    }
                }

                //Prevent the redraw.
                inst.inline = false;
            },
        });

        var today = new Date();
        var todayElement = jQuery($datepicker).find('.ui-datepicker-calendar td[data-year="' + today.getFullYear() + '"][data-month="' + today.getMonth() + '"]').filter(function () {
            return jQuery(this).find('a').text() == today.getDate();
        });

        if (todayElement.length) {
            todayElement.addClass('custom-highlight');
        }

        bm_get_service_price($this, $container, service_id); // if open by default (when you're using <div>)
    }

    static bm_get_service_price($this = '', $container, service_id = 0) {
        jQuery('#' + $container).find('.front_calendar_errortext').html('');
        var currency_symbol = bm_normal_object.currency_symbol;
        var currency_position = bm_normal_object.currency_position;

        if (!service_id) {
            service_id = strict_decode(jQuery('#current_service_id').val());
        }

        var data = {
            
            'id': service_id,
            'nonce': bm_ajax_object.nonce
        };

        bmPublicRestRequest('bm_get_frontend_service_prices', data, function (response) {
            var jsondata = bmSafeParse(response);
            var status = jsondata.status;

            if (status == true) {
                var default_price = jsondata.default_price ?? '';
                var variable_price_obj = jsondata.variable_price.price || '';
                var variable_price_date_obj = jsondata.variable_price.date || '';
                var variable_module_obj = jsondata.variable_module.module || '';
                var variable_module_date_obj = jsondata.variable_module.date || '';

                var unavailability = jsondata.unavailability || '';
                var gbl_unavailability = jsondata.gbl_unavlabilty || '';
                var unavailable_days_array = [];
                var weekdays_array = [];

                if (unavailability && typeof unavailability === 'object') {
                    if (unavailability.weekdays && unavailability.weekdays !== '') {
                        weekdays_array = Object.values(unavailability.weekdays).map(String);
                    }
                }

                if (
                    gbl_unavailability &&
                    typeof gbl_unavailability === 'object' &&
                    gbl_unavailability.dates &&
                    Object.keys(gbl_unavailability.dates).length > 0
                ) {
                    unavailable_days_array = Object.values(gbl_unavailability.dates);
                }

                // Availability periods (new system): array of {date_start, date_end}
                var availability_periods = jsondata.availability_periods || [];

                var price_array = [];
                var price_date_array = [];
                var module_date_array = [];

                if (variable_price_obj && variable_price_date_obj) {
                    price_array = Object.values(variable_price_obj);
                    price_date_array = Object.values(variable_price_date_obj);
                }

                if (variable_module_obj && variable_module_date_obj) {
                    module_date_array = Object.values(variable_module_date_obj);
                }

                var calendar = $container == 'calendar_and_slot_details'
                    ? 'service_by_category_calendar'
                    : 'service-by-id-calendar';

                setTimeout(function () {
                    var calendarElement = $this
                        ? jQuery($this).find('.' + calendar)
                        : jQuery('.' + calendar);

                    calendarElement.datepicker().find(".ui-datepicker-calendar td").filter(function () {
                        var date = jQuery(this).text();
                        return /\d/.test(date);
                    }).find('a, span').html(function (i, html) {
                        var day = jQuery(this).data('date');
                        var month = jQuery(this).parent().data('month') + 1;
                        var year = jQuery(this).parent().data('year');
                        var date = year + "-" + padWithZeros(month) + "-" + padWithZeros(day);
                        var week_date = new Date(date);
                        var isUnavailable = false;

                        for (var r = 0; r < unavailable_days_array.length; r++) {
                            var rawRange = unavailable_days_array[r];
                            if (!rawRange) continue;

                            var rangeStr = String(rawRange).trim();
                            if (!rangeStr.length) continue;

                            if (rangeStr.indexOf('to') !== -1) {
                                var parts = rangeStr.split('to');
                                var start = parts[0].trim();
                                var end = parts[1].trim();
                                if (start && end && date >= start && date <= end) {
                                    isUnavailable = true;
                                    break;
                                }
                            } else if (date === rangeStr) {
                                isUnavailable = true;
                                break;
                            }
                        }

                        if (!isUnavailable && weekdays_array.length > 0) {
                            var weekdayStr = String(week_date.getDay());
                            if (weekdays_array.indexOf(weekdayStr) !== -1) {
                                isUnavailable = true;
                            }
                        }

                        // Check availability periods: if periods exist, date must be within at least one
                        if (!isUnavailable && availability_periods.length > 0) {
                            var inPeriod = false;
                            for (var ap = 0; ap < availability_periods.length; ap++) {
                                if (date >= availability_periods[ap].date_start && date <= availability_periods[ap].date_end) {
                                    inPeriod = true;
                                    break;
                                }
                            }
                            if (!inPeriod) {
                                isUnavailable = true;
                            }
                        }

                        if (isUnavailable) {
                            jQuery(this).addClass('not_available_for_booking brightValue');
                            jQuery(this).attr('data-custom', '-');
                        } else {
                            jQuery(this).addClass('available_for_booking');

                            if (jQuery.inArray(date, price_date_array) !== -1) {
                                var price = price_array[jQuery.inArray(date, price_date_array)];
                                if (parseFloat(price) > parseFloat(default_price)) {
                                    jQuery(this).addClass('highValue');
                                } else if (parseFloat(price) < parseFloat(default_price)) {
                                    jQuery(this).addClass('lowValue');
                                }
                                var price_text = currency_position == 'before'
                                    ? currency_symbol + Math.round(parseFloat(price))
                                    : Math.round(parseFloat(price)) + currency_symbol;
                                jQuery(this).attr('data-custom', price === '' ? '-' : price_text);
                            } else if (jQuery.inArray(date, module_date_array) !== -1) {
                                jQuery(this).attr('data-custom', '#emod');
                                jQuery(this).addClass('bluetValue');
                            } else {
                                jQuery(this).addClass('brightValue');
                                var price_text = currency_position == 'before'
                                    ? currency_symbol + Math.round(parseFloat(default_price))
                                    : Math.round(parseFloat(default_price)) + currency_symbol;
                                jQuery(this).attr('data-custom', default_price === '' ? '-' : price_text);
                            }
                        }

                        if (isUnavailable && $container == 'service_calendar_details' && jQuery(this).parent().hasClass('custom-highlight')) {
                            const $btn = jQuery($this).find('.booknowbtn');
                            const $link = $btn.children('a');
                            const disabledColor = '#d3d3d3';

                            $btn.removeClass('textblue bordercolor');
                            $link.removeClass('get_slot_details');
                            $btn.addClass('readonly_div');
                            $link.addClass('inactiveLink');
                            $btn.attr('style', `background-color: ${disabledColor} !important;`);
                            $btn.parent('.productbottombar').find('.calendar_shortcode_error_message').html(bm_error_object.service_unavailable);
                        }
                    });
                });
                jQuery('.loader_modal').hide();
            } else {
                jQuery('.loader_modal').hide();
                if ($container == 'service_calendar_details') {
                    const $btn = jQuery($this).find('.booknowbtn');
                    const $link = $btn.children('a');
                    const disabledColor = '#d3d3d3';

                    $btn.removeClass('textblue bordercolor');
                    $link.removeClass('get_slot_details');
                    $btn.addClass('readonly_div');
                    $link.addClass('inactiveLink');
                    $btn.attr('style', `background-color: ${disabledColor} !important;`);
                    $btn.parent('.productbottombar').find('.calendar_shortcode_error_message').html(bm_error_object.server_error);
                } else {
                    jQuery('#slot_modal').find('.front_calendar_errortext').html(bm_error_object.server_error);
                    jQuery('#slot_modal').find('.calendar_errortext').show();
                }
            }
        });
    }
}

window.BMPublic = window.BMPublic || {};
window.BMPublic.Calendar = BMPublicCalendar;

// Global aliases
window.initiateServiceCalendar = BMPublicCalendar.initiateServiceCalendar;
window.bm_get_service_price = BMPublicCalendar.bm_get_service_price;
