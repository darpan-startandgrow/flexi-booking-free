/**
 * BMPublicCore - Core event handlers and initialization for public area.
 * @since 1.1.0
 */
class BMPublicCore {
    static init($) {
        // $.datepicker.setDefaults($.datepicker.regional[bm_normal_object.current_language]);

        var is_svc_search_shortcode = bm_normal_object.is_svc_search_shortcode;

        if (is_svc_search_shortcode == 1) {
            // Get all Services on page load
            bm_fetch_all_services('');
        }

        $(document).on('click', 'div.svc_search_shortcode_content a.page-numbers', function (e) {
            e.preventDefault();
            var hrefString = $(this).attr('href');
            var pagenum = getUrlVars(hrefString)["pagenum"];
            $('#svc_search_shortcode_pagenum').val(pagenum ? pagenum : '1');
            bm_fetch_all_services(pagenum ? pagenum : '1');
        });

        //EVENT DELEGATION	
        $('#tab_nav > ul').on('click', 'a', function () {

            var aElement = $('#tab_nav > ul > li > a');
            var divContent = $('#tab_nav > div');

            /*Handle Tab Nav*/
            aElement.removeClass("selected current-view-type textblue");
            $(this).addClass("selected current-view-type textblue");

            /*Handle Tab Content*/
            var clicked_index = aElement.index(this);
            divContent.css('display', 'none');
            divContent.eq(clicked_index).css('display', 'block');

            $(this).blur();
            return false;
        });

        // Fetch service gallery images
        $(document).on('click', '.product-gallery-btn, .gallery-btn', function (e) {
            e.preventDefault();
            jQuery('#service_gallery_images_html').html('');
            jQuery('#service_gallery_modal').addClass('active-slot');
            jQuery('#service_gallery_modal').find('.loader_modal').show();
            var post = {
                'id': jQuery(this).attr('id'),
            }

            var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('bm_fetch_service_gallry_images', data, function (response) {
                jQuery('.loader_modal').hide();
                jQuery('#service_gallery_images_html').html('');
                var jsondata = bmSafeParse(response);
                if (jsondata.status == true) {
                    jQuery('#service_gallery_images_html').html(jsondata.data);
                    galleryCurrentSlide(1);
                } else {
                    jQuery('#service_gallery_images_html').html(bm_error_object.server_error);
                }
            });
        });

        // Fetch Service time slots details
        $(document).on('click', '.get_slot_details', function (e) {
            e.preventDefault();
            jQuery('#slot_details').html('');
            jQuery('#time_slot_modal').addClass('active-slot');
            jQuery('#time_slot_modal').find('.loader_modal').show();

            var $dialog = jQuery('#timeslot-capacity-dialog');
            var isDialogOpen = $dialog.length && $dialog.dialog('instance') && $dialog.dialog('isOpen');
            if (isDialogOpen) $dialog.dialog('close');
            
            var date = jQuery('#booking_date').val();
            var service_id = jQuery(this).attr('id');

            if (jQuery(this).attr('data-mobile-date')) {
                date = jQuery(this).attr('data-mobile-date');
                sessionStorage.setItem('mobile-service-date-' + service_id, date);
            }

            if (jQuery(this).attr('data-service-date')) {
                date = jQuery(this).attr('data-service-date');
                sessionStorage.setItem('service-calendar-service-date-' + service_id, date);
            }

            if (jQuery(this).attr('data-fullcalendar-id')) {
                date = jQuery(this).attr('data-fullcalendar-id');
                sessionStorage.setItem('service-fullcalendar-service-date-' + service_id, date);
            }

            if (jQuery(this).attr('data-timeslot-fullcalendar-id')) {
                date = jQuery(this).attr('data-timeslot-fullcalendar-id');
                sessionStorage.setItem('service-timeslot-fullcalendar-service-date-' + service_id, date);
            }

            var post = {
                'date': date,
                'id': service_id,
                'type': 'home_page',
            }

            jQuery('#current_service_id').val(strict_encode(jQuery(this).attr('id')));

            var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('bm_fetch_frontend_service_time_slots', data, function (response) {
                jQuery('.loader_modal').hide();
                jQuery('#slot_details').html('');
                var jsondata = bmSafeParse(response);
                var status = jsondata.status;
                var min_cap = jsondata.min_cap;
                var data = jsondata.data;
                jQuery('#total_service_booking').val(min_cap);

                if (status == false) {
                    jQuery('#slot_details').html(bm_error_object.server_error);
                } else if (data != null && data != '' && status == true) {
                    jQuery('#slot_details').html(data);
                } else {
                    jQuery('#slot_details').html(bm_error_object.server_error);
                }
            });
        });

        // Fetch Service calendar and time slots details
        $(document).on('click', '.get_calendar_and_slot_details', function (e) {
            e.preventDefault();
            jQuery('#calendar_and_slot_details').html('');
            jQuery('#slot_modal').addClass('active-slot');
            jQuery('#slot_modal').find('.loader_modal').show();

            var date = new Date(jQuery.now());
            var today = date.getFullYear() + "-" + padWithZeros((date.getMonth() + 1)) + "-" + padWithZeros(date.getDate());

            jQuery('#booking_date2').val(today);
            jQuery('#current_service_id').val(strict_encode(jQuery(this).attr('id')));

            var post = {
                'date': today,
                'id': jQuery(this).attr('id'),
                'type': 'service_by_category',
            }

            var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('bm_fetch_frontend_service_time_slots', data, function (response) {
                jQuery('#calendar_and_slot_details').html('');
                var jsondata = bmSafeParse(response);
                var status = jsondata.status;
                var min_cap = jsondata.min_cap;
                var data = jsondata.data;
                jQuery('#total_service_booking').val(min_cap);

                if (status == false) {
                    jQuery('#calendar_and_slot_details').html(bm_error_object.server_error);
                } else if (data != null && data != '' && status == true) {
                    jQuery('#calendar_and_slot_details').html(data);
                    initiateServiceCalendar('calendar_and_slot_details');
                } else {
                    jQuery('#calendar_and_slot_details').html(bm_error_object.server_error);
                }
            });
        });

        // Fetch checkout options
        $(document).on('click', '.get_checkout_options', function (e) {
            e.preventDefault();

            var no_of_persons = [];
            var i = 0;

            jQuery('[id^=extra_service_total_booking_]').each(function () {
                if (jQuery(this).val() != '' && !jQuery(this).hasClass('readonly_checkbox')) {
                    no_of_persons[i] = jQuery(this).val();
                    i++;
                }
            });

            jQuery('#no_of_persons').val(no_of_persons.join(','));
            jQuery('#service_id_for_checkout').val(strict_encode(this.id));

            jQuery('#checkout_options_html').html('');
            jQuery('#time_slot_modal').removeClass('active-slot');
            jQuery('#slot_modal').removeClass('active-slot');
            jQuery('#extra_service_modal').removeClass('active-slot');
            jQuery('#checkout_options_modal').addClass('active-slot');
            jQuery('#checkout_options_modal').find('.loader_modal').show();

            var $dialog = jQuery('#timeslot-capacity-dialog');
            var isDialogOpen = $dialog.length && $dialog.dialog('instance') && $dialog.dialog('isOpen');
            if (isDialogOpen) $dialog.dialog('close');

            var post = {
                'type': 'home_page',
            }

            var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('bm_fetch_checkout_options', data, function (response) {
                jQuery('.loader_modal').hide();
                jQuery('#checkout_options_html').html('');
                if (response.success && response.data) {
                    jQuery('#checkout_options_html').html(response.data);
                } else if (!response.success && response.data) {
                    jQuery('#checkout_options_html').html(response.data);
                } else {
                    jQuery('#checkout_options_html').html(bm_error_object.server_error);
                }
            });
        });

        // Fetch checkout options
        $(document).on('click', '.get_svc_by_cat_checkout_options', function (e) {
            e.preventDefault();

            var no_of_persons = [];
            var i = 0;

            jQuery('[id^=extra_service_total_booking_]').each(function () {
                if (jQuery(this).val() != '' && !jQuery(this).hasClass('readonly_checkbox')) {
                    no_of_persons[i] = jQuery(this).val();
                    i++;
                }
            });

            jQuery('#no_of_persons').val(no_of_persons.join(','));
            jQuery('#service_id_for_checkout').val(strict_encode(this.id));

            jQuery('#checkout_options_html').html('');
            jQuery('#time_slot_modal').removeClass('active-slot');
            jQuery('#slot_modal').removeClass('active-slot');
            jQuery('#extra_service_modal').removeClass('active-slot');
            jQuery('#checkout_options_modal').addClass('active-slot');
            jQuery('#checkout_options_modal').find('.loader_modal').show();

            var post = {
                'type': 'service_by_category',
            }

            var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('bm_fetch_checkout_options', data, function (response) {
                jQuery('.loader_modal').hide();
                jQuery('#checkout_options_html').html('');
                if (response.success && response.data) {
                    jQuery('#checkout_options_html').html(response.data);
                } else if (!response.success && response.data) {
                    jQuery('#checkout_options_html').html(response.data);
                } else {
                    jQuery('#checkout_options_html').html(bm_error_object.server_error);
                }
            });
        });

        // Fetch extra services
        $(document).on('click', '.get_extra_service', function (e) {
            e.preventDefault();
            jQuery('#extra_service_details').html('');
            jQuery('#time_slot_modal').removeClass('active-slot');
            jQuery('#extra_service_modal').addClass('active-slot');
            jQuery('#extra_service_modal').find('.loader_modal').show();
            var date = jQuery('#booking_date').val();
            var service_id = jQuery(this).attr('id');

            var $dialog = jQuery('#timeslot-capacity-dialog');
            var isDialogOpen = $dialog.length && $dialog.dialog('instance') && $dialog.dialog('isOpen');
            if (isDialogOpen) $dialog.dialog('close');

            if (sessionStorage.getItem('mobile-service-date-' + service_id) != null) {
                date = sessionStorage.getItem('mobile-service-date-' + service_id);
            }

            if (sessionStorage.getItem('service-calendar-service-date-' + service_id) != null) {
                date = sessionStorage.getItem('service-calendar-service-date-' + service_id);
            }

            if (sessionStorage.getItem('service-fullcalendar-service-date-' + service_id) != null) {
                date = sessionStorage.getItem('service-fullcalendar-service-date-' + service_id);
            }

            if (sessionStorage.getItem('service-timeslot-fullcalendar-service-date-' + service_id) != null) {
                date = sessionStorage.getItem('service-timeslot-fullcalendar-service-date-' + service_id);
            }

            if (jQuery(document).find('#timeslot_booking_date').length > 0) {
                date = jQuery(document).find('#timeslot_booking_date').val();
            }

            var post = {
                'date': date,
                'id': service_id,
                'type': 'home_page',
            }

            var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('bm_fetch_extra_service', data, function (response) {
                jQuery('.loader_modal').hide();
                jQuery('#extra_service_details').html('');
                if (response != null && response != '') {
                    jQuery('#extra_service_details').html(response);
                } else {
                    jQuery('#extra_service_details').html(bm_error_object.server_error);
                }
            });
        });

        // Fetch extra services
        $(document).on('click', '.get_svc_by_cat_extra_service', function (e) {
            e.preventDefault();
            jQuery('#extra_service_details').html('');
            jQuery('#slot_modal').removeClass('active-slot');
            jQuery('#extra_service_modal').addClass('active-slot');
            jQuery('#extra_service_modal').find('.loader_modal').show();

            var post = {
                'date': jQuery('#booking_date2').val(),
                'id': jQuery(this).attr('id'),
                'type': 'service_by_category',
            }

            var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('bm_fetch_extra_service', data, function (response) {
                jQuery('.loader_modal').hide();
                jQuery('#extra_service_details').html('');
                if (response != null && response != '') {
                    jQuery('#extra_service_details').html(response);
                } else {
                    jQuery('#extra_service_details').html(bm_error_object.server_error);
                }
            });
        });

        // Redirect to checkout form
        $(document).on('click', '.get_checkout_form', function (e) {
            e.preventDefault();

            let modal;
            if (jQuery('#time_slot_modal').hasClass('active-slot')) {
                modal = jQuery('#time_slot_modal');
            } else if (jQuery('#slot_modal').hasClass('active-slot')) {
                modal = jQuery('#slot_modal');
            } else {
                modal = jQuery('#flexi_checkout_options').length > 0 ? jQuery('#checkout_options_modal') : jQuery('#extra_service_modal');
            }

            localStorage.setItem('booking_url', location.href);

            const modal_body = modal.find('.modal-body');

            var $dialog = jQuery('#timeslot-capacity-dialog');
            var isDialogOpen = $dialog.length && $dialog.dialog('instance') && $dialog.dialog('isOpen');
            var $currentLoader = isDialogOpen 
                ? $dialog.find('.loader_modal') 
                : modal.find('.loader_modal');
            var $targetContent = isDialogOpen 
                ? $dialog.find('.timeslot-dialog-content') 
                : modal_body;

            if (isDialogOpen) $targetContent.find('.timeslot-dialog-footer').hide();

            $currentLoader.show();

            var no_of_persons = [];
            var i = 0;

            jQuery('[id^=extra_service_total_booking_]').each(function () {
                if (jQuery(this).val() != '' && !jQuery(this).hasClass('readonly_checkbox')) {
                    no_of_persons[i] = jQuery(this).val();
                    i++;
                }
            });

            var total_service_booking = 0;
            if (jQuery(document).find('#timeslot-counter').length > 0) {
                total_service_booking = jQuery('#timeslot-counter').val();
            } else {
                total_service_booking = jQuery('#total_service_booking').val();
            }

            var date = jQuery('#booking_date').val();
            var service_id = jQuery(this).attr('id') ? jQuery(this).attr('id') : 0;
            service_id = service_id ? service_id : strict_decode(jQuery('#service_id_for_checkout').val())

            if (sessionStorage.getItem('mobile-service-date-' + service_id) != null) {
                date = sessionStorage.getItem('mobile-service-date-' + service_id);
                sessionStorage.removeItem('mobile-service-date-' + service_id);
            }

            if (sessionStorage.getItem('service-calendar-service-date-' + service_id) != null) {
                date = sessionStorage.getItem('service-calendar-service-date-' + service_id);
                sessionStorage.removeItem('service-calendar-service-date-' + service_id);
            }

            if (sessionStorage.getItem('service-fullcalendar-service-date-' + service_id) != null) {
                date = sessionStorage.getItem('service-fullcalendar-service-date-' + service_id);
                sessionStorage.removeItem('service-fullcalendar-service-date-' + service_id);
            }

            if (sessionStorage.getItem('service-timeslot-fullcalendar-service-date-' + service_id) != null) {
                date = sessionStorage.getItem('service-timeslot-fullcalendar-service-date-' + service_id);
                sessionStorage.removeItem('service-timeslot-fullcalendar-service-date-' + service_id);
            }

            if (jQuery(document).find('#timeslot_booking_date').length > 0) {
                date = jQuery(document).find('#timeslot_booking_date').val();
            }

            var post = {
                'time_slot': jQuery('#selected_slot').val(),
                'date': date,
                'id': service_id,
                'total_service_booking': total_service_booking,
                'extra_svc_ids': jQuery('#selected_extra_service_ids').val(),
                'no_of_persons': no_of_persons.length != 0 ? no_of_persons.join(',') : jQuery('#no_of_persons').val(),
                'checkout_option': jQuery('#flexi_checkout_options').length > 0 ? jQuery('#flexi_checkout_options').val() : '',
                'type': 'home_page',
            }

            jQuery(modal_body).html('');

            var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('bm_fetch_order_info_and_redirect_to_checkout', data, function (response) {
                $currentLoader.hide();
                
                var jsondata = bmSafeParse(response);
                var status = jsondata.status;
                var data = jsondata.data;

                if (data != null && data != '' && status == 'error') {
                    $targetContent.html(data);
                } else if (data != null && data != '' && status == 'success') {
                    $targetContent.html('<div class="checkout-spinner-box"><div class="checkout-spinner"></div><p>' + bm_normal_object.moving_to_checkout + '</p></div>');
                    
                    // if (isDialogOpen) $dialog.dialog('close');
                    
                    window.location.href = data;
                } else {
                    $targetContent.html(bm_error_object.server_error);
                }
            });
        });

        // Redirect to checkout form
        $(document).on('click', '.get_svc_by_cat_checkout_form', function (e) {
            e.preventDefault();

            let modal;
            if (jQuery('#time_slot_modal').hasClass('active-slot')) {
                modal = jQuery('#time_slot_modal');
            } else if (jQuery('#slot_modal').hasClass('active-slot')) {
                modal = jQuery('#slot_modal');
            } else {
                modal = jQuery('#flexi_checkout_options').length > 0 ? jQuery('#checkout_options_modal') : jQuery('#extra_service_modal');
            }

            localStorage.setItem('booking_url', location.href);

            const modal_body = modal.find('.modal-body');
            modal.find('.loader_modal').show();

            var no_of_persons = [];
            var i = 0;

            jQuery('[id^=extra_service_total_booking_]').each(function () {
                if (jQuery(this).val() != '' && !jQuery(this).hasClass('readonly_checkbox')) {
                    no_of_persons[i] = jQuery(this).val();
                    i++;
                }
            });

            var post = {
                'time_slot': jQuery('#selected_slot').val(),
                'date': jQuery('#booking_date2').val(),
                'id': jQuery(this).attr('id') ? jQuery(this).attr('id') : strict_decode(jQuery('#service_id_for_checkout').val()),
                'total_service_booking': jQuery('#total_service_booking').val(),
                'extra_svc_ids': jQuery('#selected_extra_service_ids').val(),
                'no_of_persons': no_of_persons.length != 0 ? no_of_persons.join(',') : jQuery('#no_of_persons').val(),
                'checkout_option': jQuery('#flexi_checkout_options').length > 0 ? jQuery('#flexi_checkout_options').val() : '',
                'type': 'service_by_category',
            }

            jQuery(modal_body).html('');

            var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('bm_fetch_order_info_and_redirect_to_checkout', data, function (response) {
                jQuery('.loader_modal').hide();
                var jsondata = bmSafeParse(response);
                var status = jsondata.status;
                var data = jsondata.data;

                if (data != null && data != '' && status == 'error') {
                    jQuery(modal_body).html(data);
                } else if (data != null && data != '' && status == 'success') {
                    // jQuery(modal).removeClass('active-slot');
                    jQuery(modal_body).html('<div class="checkout-spinner-box"><div class="checkout-spinner"></div><p>' + bm_normal_object.moving_to_checkout + '</p></div>');
                    window.location.href = data;
                } else {
                    jQuery(modal_body).html(bm_error_object.server_error);
                }
            });
        });

        // Highlight time slot on selection
        $(document).on('click', '#slot_value', function () {
            $this = jQuery(this);
            var frontend_button_background_colur = bm_normal_object.svc_button_colour;
            var frontend_button_text_colur = bm_normal_object.svc_btn_txt_colour;

            jQuery(this).parent().children()
                .removeClass('bgcolor bordercolor textwhite')
                .css('cssText', 'background-color: initial !important; color: initial !important;');
            jQuery(this)
                .addClass('bgcolor bordercolor textwhite')
                .css('cssText', `background-color: ${frontend_button_background_colur} !important; color: ${frontend_button_text_colur} !important;`);

            var time_slot_value = jQuery(this).find('.slot_value_text').text();
            jQuery('#selected_slot').val(time_slot_value);

            jQuery(document).find('.service_selection_div').html('');
            jQuery(document).find('.service_selection_div').show();
            jQuery($this).parents('.modal').find('.loader_modal').css('top', 'initial');
            jQuery($this).parents('.modal').find('.loader_modal').show();

            var post = {
                'capacity_left': jQuery(this).find('.slot_count_text').data('capacity'),
                'mincap': jQuery(this).find('.slot_count_text').data('mincap'),
                'id': strict_decode(jQuery('#current_service_id').val()),
            };

            var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('bm_fetch_service_selection', data, function (response) {
                jQuery('.loader_modal').hide();
                jQuery(document).find('.service_selection_div').html('');

                if (response != null && response != '') {
                    jQuery(document).find('.service_selection_div').append(response);

                    if (jQuery(document).find('.service_selection_div .terms_required_errortext').length > 0) {
                        jQuery('#total_service_booking').val('0');
                        jQuery($this).parents('.slot_box_modal').find('#select_slot_button')
                            .addClass('readonly_div')
                            .removeClass('bgcolor textwhite text-center')
                            .css('cssText', 'background-color: initial !important; color: initial !important;');
                        jQuery($this).parents('.slot_box_modal').find('#select_slot_button').children().addClass('inactiveLink');
                    } else {
                        jQuery('#total_service_booking').val(jQuery(document).find('.service_total_booking').val());
                        jQuery($this).parents('.slot_box_modal').find('#select_slot_button')
                            .removeClass('readonly_div')
                            .addClass('bgcolor textwhite text-center')
                            .css('cssText', `background-color: ${frontend_button_background_colur} !important; color: ${frontend_button_text_colur} !important;`);
                        jQuery($this).parents('.slot_box_modal').find('#select_slot_button').children().removeClass('inactiveLink');
                    }
                } else {
                    jQuery(document).find('.service_selection_div').append(bm_error_object.server_error);
                }

                jQuery($this).parent('.modalcontentbox').animate({
                    scrollTop: jQuery(".service_selection_div").offset().top
                }, 2000);
            });
        });

        // Change value on service selection
        $(document).on('change', '.service_total_booking', function () {
            jQuery('#total_service_booking').val('');
            jQuery('#total_service_booking').val(jQuery(this).val());
        });

        // Highlight next button on extra service selection
        $(document).on('change', '.listed_extra_service', function () {
            var ids = [];
            var type = '';

            jQuery(".extra_services_available input:checked").each(function () {
                ids.push(jQuery(this).attr('id'));
            });

            var $cancelBtn = jQuery(this).parents('div.extra_service_results').find('.cancelbtn');

            if ($cancelBtn.data('type')) {
                type = $cancelBtn.data('type');
            } else {
                if ($cancelBtn.hasClass('get_checkout_form')) {
                    type = 'service_shortcode';
                    $cancelBtn.data('type', type);
                } else if ($cancelBtn.hasClass('get_svc_by_cat_checkout_form')) {
                    type = 'service_by_cat_shortcode';
                    $cancelBtn.data('type', type);
                } else if ($cancelBtn.hasClass('get_checkout_options')) {
                    type = 'service_options';
                    $cancelBtn.data('type', type);
                } else if ($cancelBtn.hasClass('get_svc_by_cat_checkout_options')) {
                    type = 'service_by_cat_options';
                    $cancelBtn.data('type', type);
                }
            }

            if (jQuery(this).is(':checked')) {
                jQuery(this).parents('div.extra_service_content')
                    .find('div.extra_service_booking_no').removeClass('readonly_cursor')
                    .find('.extra_service_total_booking').removeClass('readonly_checkbox');
            } else {
                jQuery(this).parents('div.extra_service_content')
                    .find('div.extra_service_booking_no').addClass('readonly_cursor')
                    .find('.extra_service_total_booking').addClass('readonly_checkbox');
            }

            var frontend_button_background_colur = bm_normal_object.svc_button_colour;
            var frontend_button_text_colur = bm_normal_object.svc_btn_txt_colour;

            if (ids.length !== 0) {
                jQuery(this).parents('div.extra_service_results')
                    .find('.bookbtn')
                    .removeClass('readonly_div')
                    .addClass('bgcolor textwhite text-center')
                    .css('cssText', `background-color: ${frontend_button_background_colur} !important; color: ${frontend_button_text_colur} !important;`)
                    .children()
                    .removeClass('inactiveLink');

                switch (type) {
                    case 'service_shortcode':
                        $cancelBtn.addClass('readonly_div').removeClass('get_checkout_form');
                        break;
                    case 'service_by_cat_shortcode':
                        $cancelBtn.addClass('readonly_div').removeClass('get_svc_by_cat_checkout_form');
                        break;
                    case 'service_options':
                        $cancelBtn.addClass('readonly_div').removeClass('get_checkout_options');
                        break;
                    case 'service_by_cat_options':
                        $cancelBtn.addClass('readonly_div').removeClass('get_svc_by_cat_checkout_options');
                        break;
                }

                $cancelBtn.css('cssText', `background-color: '' !important; color: '' !important;`);

                jQuery('#selected_extra_service_ids').val(ids.join(","));
            } else {
                jQuery(this)
                    .parents('div.extra_service_results')
                    .find('.bookbtn')
                    .removeClass('bgcolor bordercolor textwhite')
                    .addClass('readonly_div')
                    .css('cssText', 'background-color: initial !important; color: initial !important;')
                    .children()
                    .addClass('inactiveLink');

                switch (type) {
                    case 'service_shortcode':
                        $cancelBtn.removeClass('readonly_div').addClass('get_checkout_form');
                        break;
                    case 'service_by_cat_shortcode':
                        $cancelBtn.removeClass('readonly_div').addClass('get_svc_by_cat_checkout_form');
                        break;
                    case 'service_options':
                        $cancelBtn.removeClass('readonly_div').addClass('get_checkout_options');
                        break;
                    case 'service_by_cat_options':
                        $cancelBtn.removeClass('readonly_div').addClass('get_svc_by_cat_checkout_options');
                        break;
                }

                $cancelBtn.css('cssText', `background-color: ${frontend_button_background_colur} !important; color: ${frontend_button_text_colur} !important;`);

                jQuery('#selected_extra_service_ids').val('');
            }
        });

        // Cancel button on booking form
        $(document).on('click', '#cancel_booking', function (e) {
            e.preventDefault();
            closeModal('user_form_modal');
        });

        // Cancel button on checkout form
        $(document).on('click', '#booking_home', function (e) {
            location.href = localStorage.getItem('booking_url');
        });

        // Confirm order and fetch details from booking form
        $(document).on('click', '#confirm_booking', function (e) {
            e.preventDefault();
            jQuery(this).parents('#user_form_modal').find('.loader_modal').css('top', 'initial');
            jQuery(this).parents('#user_form_modal').find('.loader_modal').show();

            var formData = {};
            var fieldData = {};
            var otherData = {};

            jQuery('#booking_form :input').map(function () {
                validateFields(jQuery(this));

                var type = jQuery(this).prop("type");
                var contentFolder = jQuery(this).attr("id").startsWith('sgbm_field_') ? fieldData : otherData;

                if ((type == "checkbox")) {
                    if (this.checked) {
                        contentFolder[jQuery(this).attr('id')] = 1;
                    } else {
                        contentFolder[jQuery(this).attr('id')] = 0;
                    }
                } else if ((type == "radio")) {
                    if (this.checked) contentFolder[jQuery(this).attr('id')] = jQuery(this).val();
                } else if ((type == "tel" && jQuery(this).hasClass('intl_phone_field_input'))) {
                    var country_text = jQuery(document).find("div.iti__selected-flag div:first-child").attr('class');
                    var country_code = country_text.split('_').pop();
                    otherData['country_code'] = country_code;
                    var intl_code = jQuery(document).find(".iti__selected-dial-code").text();
                    contentFolder[jQuery(this).attr('id')] = intl_code + jQuery(this).val();
                } else {
                    contentFolder[jQuery(this).attr('id')] = jQuery(this).val();
                }
            });

            if (jQuery('#booking_form .required_errortext').length > 0 || jQuery('#booking_form .terms_required_errortext').length > 0 || jQuery('#booking_form .checkbox_required_errortext').length > 0) {
                jQuery('.loader_modal').hide();
                return false;
            } else {
                formData['field_data'] = fieldData;
                formData['other_data'] = otherData;

                var data = { 'post': formData, 'nonce': bm_ajax_object.nonce };
                bmPublicRestRequest('bm_fetch_booking_data', data, function (response) {
                    jQuery('.loader_modal').hide();
                    jQuery('#user_form_modal').removeClass('active-slot');
                    jQuery('#user_form').html('');
                    var jsondata = bmSafeParse(response);
                    var status = jsondata.status;
                    var data = jsondata.data;

                    if (data != null && data != '' && status == 'error') {
                        jQuery('#booking_detail').html('');
                        jQuery('#booking_detail').html(data);
                        jQuery('#booking_detail_modal').addClass('active-slot');
                    } else if (data != null && data != '' && status == 'success') {
                        window.location.href = data;
                    } else {
                        jQuery('#booking_detail').html('');
                        jQuery('#booking_detail').html(bm_error_object.server_error);
                        jQuery('#booking_detail_modal').addClass('active-slot');
                    }
                });
            }
        });

        // Close modal
        $(document).on('click', '#close_modal', function (e) {
            e.preventDefault();
            var modal = jQuery(this).parents('.modaloverlay');

            modal.animate({ top: "-=100px" }, 300, function () {
                modal.css({ top: "" });
                modal.removeClass('active-slot');
            });
        });

        // Close booking_details
        $(document).on('click', '#close_booking_details', function (e) {
            e.preventDefault();
            closeModal('booking_detail_modal');
        });

        // Edit slot selection
        $(document).on('click', '.edit_slot_selection', function () {
            jQuery('#selected_extra_service_ids').val('');
            jQuery(this).parents('.modaloverlay ').removeClass('active-slot');
            jQuery('#time_slot_modal').addClass('active-slot');
        });

        // Edit slot selection
        $(document).on('click', '.edit_svc_by_cat_slot_selection', function () {
            jQuery('#selected_extra_service_ids').val('');
            jQuery(this).parents('.modaloverlay ').removeClass('active-slot');
            jQuery('#slot_modal').addClass('active-slot');
        });

        // Show full service description in frontend
        $(document).on('click', '.service-desc-fa', function (e) {
            e.preventDefault();
            var svc_button_colour = bm_normal_object.svc_button_colour;
            var svc_btn_txt_colour = bm_normal_object.svc_btn_txt_colour;
            var svc_title = jQuery(this).parents('.main-parent').find('.service_full_description').data('title');

            if (!svc_title || svc_title == 'undefined') {
                svc_title = jQuery(this).parents('.main-parent').find('.fc-title').attr('title');
            }

            // full description dialog
            var dialog = jQuery(this).parents('.main-parent').find('.service_full_description').dialog({
                autoOpen: false,
                resizable: false,
                draggable: false,
                title: svc_title + ' ' + bm_normal_object.svc_full_desc,
                height: 400,
                width: 800,
                modal: true,
                show: {
                    effect: "bounce",
                    duration: 1000
                },
                hide: {
                    effect: "slide",
                    direction: 'up',
                    duration: 1000
                },
                close: function () {
                    jQuery(this).dialog("destroy");
                },
                buttons: [{
                    text: "Ok",
                    click: function() {
                        jQuery(this).dialog("destroy");
                    }
                }],
                open: function() {
                    var button = jQuery(this).parent().find('.ui-dialog-buttonset button');
                    
                    var styleId = 'dialog-button-style-' + Date.now();
                    jQuery('head').append(`
                        <style id="${styleId}">
                            .ui-dialog .ui-dialog-buttonpane button.ui-button {
                                color: ${svc_btn_txt_colour} !important;
                                background: ${svc_button_colour} !important;
                                border-color: ${svc_button_colour} !important;
                            }
                            .ui-dialog .ui-dialog-buttonpane button.ui-button:hover,
                            .ui-dialog .ui-dialog-buttonpane button.ui-button:focus {
                                background: ${svc_button_colour} !important;
                                opacity: 0.9 !important;
                            }
                            .ui-dialog .ui-dialog-buttonpane button.ui-button:active {
                                background: ${svc_button_colour} !important;
                                opacity: 0.8 !important;
                            }
                        </style>
                    `);
                    
                    // Remove the style when dialog is closed
                    jQuery(this).on('dialogclose', function() {
                        jQuery('#' + styleId).remove();
                    });
                }
            });

            // Open dialog
            dialog.dialog("open");
        });

        // Tooltip
        if (window.innerWidth >= 1025) {
            $(document).tooltip({
                position: {
                    my: "center bottom-10",
                    at: "center top",
                    using: function (position, feedback) {
                        $(this).css(position);
                        $("<div>")
                            .addClass("arrow")
                            .addClass(feedback.vertical)
                            .addClass(feedback.horizontal)
                            .appendTo(this);
                    }
                }
            });
        } else {
            // Only on mobile & tablet screens (max-width 1025px)
            $(document).tooltip({
                items: "[title]:not(.no-tooltip):not(input[type='checkbox'])",
                position: {
                    my: "center bottom-10",
                    at: "center top",
                    using: function (position, feedback) {
                        $(this).css(position);
                        $("<div>")
                            .addClass("arrow")
                            .addClass(feedback.vertical)
                            .addClass(feedback.horizontal)
                            .appendTo(this);
                    }
                }
            });
        }

        if (bm_normal_object.current_page_title == "Flexibooking Checkout") {
            setIntlInput('checkout_form');
        }

        // Search services by name
        $(document).on('keyup', '#search_service_by_name', function (e) {
            e.preventDefault();
            var $this = jQuery(this);
            var element = jQuery($this).parents('.service-by-catrgory');
            jQuery(element).find('.service_by_category_gridview').html('');
            jQuery(element).find('.service_by_category_gridview').html('<div class="loader_modal" style="top: initial;"></div>');
            jQuery(element).find('.service_by_category_gridview').find('.loader_modal').show();

            var categories = jQuery($this).parent('.inputgroup').find('#service_categories').val();
            var post = {
                'search_string': jQuery.trim($this.val()),
                'categories': categories,
            }

            var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('bm_fetch_services_by_name', data, function (response) {
                var jsondata = bmSafeParse(response);
                jQuery(element).find('.service_by_category_gridview').html('');

                if (jsondata != null && jsondata != "") {
                    if (!jQuery(jsondata).hasClass('svc_by_cat_search')) {
                        var sliders = document.querySelectorAll('.slick-initialized');

                        sliders.forEach(item => {
                            jQuery(item).slick('unslick');
                        })
                    }

                    jQuery(element).find('.service_by_category_gridview').html(jsondata);
                    activateSlick();
                } else {
                    jQuery(element).find('.service_by_category_gridview').html(bm_error_object.server_error);
                }
            });
        });

        // Add discount in checkout form
        $(document).on('click', '#check_checkout_discount', function (e) {
            e.preventDefault();
            jQuery(document).find('span.age_errortext').html('');
            var b = 0;
            var formData = {};
            var ageFromData = {};
            var ageToData = {};
            var ageTotalData = {};

            jQuery('.checkout_age_range_fields :input').map(function () {
                var type = jQuery(this).prop('type');
                var value = jQuery(this).val();
                var index = jQuery(this).attr('id').split('_')[3];
                jQuery(this).parent().find('div.required_errortext').remove();

                if (type !== 'button' && type !== 'submit' && type !== 'reset' && type !== 'search') {
                    if (value == '') {
                        jQuery(document).find('span.age_errortext').html(bm_error_object.fill_up_age_fields);
                        b++;
                    } else if ((value < 0) || (value % 1 != 0)) {
                        jQuery(document).find('span.age_errortext').html(bm_error_object.invalid_total);
                        b++;
                    } else {
                        if (jQuery(this).attr("id").match("age_group_from_")) {
                            ageFromData[index] = jQuery(this).val();
                        } else if (jQuery(this).attr("id").match("age_group_to_")) {
                            ageToData[index] = jQuery(this).val();
                        } else if (jQuery(this).attr("id").match("age_group_total_")) {
                            ageTotalData[index] = jQuery(this).val();
                        }
                    }
                }
            });

            if (b == 0) {
                jQuery('.loader_modal').show();
                formData['from_data'] = ageFromData;
                formData['to_data'] = ageToData;
                formData['total_data'] = ageTotalData;
                formData['booking_key'] = getUrlParameter('flexi_booking');

                var data = { 'post': formData, 'nonce': bm_ajax_object.nonce };
                bmPublicRestRequest('bm_check_discount', data, function (response) {
                    jQuery('.loader_modal').hide();
                    var jsondata = bmSafeParse(response);
                    var status = jsondata.status ? jsondata.status : '';
                    var data = jsondata.data ? jsondata.data : '';
                    var negative_discount = jsondata.negative_discount ? jsondata.negative_discount : 0;

                    var currency_symbol = bm_normal_object.currency_symbol;
                    var currency_position = bm_normal_object.currency_position;
                    if (status == 'success' && typeof (data.subtotal) != "undefined" && typeof (data.discount) != "undefined" && typeof (data.total) != "undefined") {
                        if (data.discount <= 0) {
                            jQuery('.discount_li').addClass('hidden')
                        } else {
                            jQuery('.discount_li').removeClass('hidden')
                        }

                        jQuery(document).find('span#checkout_subtotal').html('');
                        jQuery(document).find('span#checkout_discount').html('');
                        jQuery(document).find('span#checkout_total').html('');
                        var subtotal_text = currency_position == 'before' ? currency_symbol + changePriceFormat(parseFloat(data.subtotal).toFixed(2)) : changePriceFormat(parseFloat(data.subtotal).toFixed(2)) + currency_symbol;
                        var discount_text = currency_position == 'before' ? currency_symbol + changePriceFormat(parseFloat(data.discount).toFixed(2)) : changePriceFormat(parseFloat(data.discount).toFixed(2)) + currency_symbol;
                        var total_text = currency_position == 'before' ? currency_symbol + changePriceFormat(parseFloat(data.total).toFixed(2)) : changePriceFormat(parseFloat(data.total).toFixed(2)) + currency_symbol;
                        negative_discount == 1 ? jQuery(document).find('span#checkout_discount').removeClass('positive_discount').addClass('negative_discount') : jQuery(document).find('span#checkout_discount').removeClass('negative_discount').addClass('positive_discount');

                        jQuery(document).find('span#checkout_subtotal').html(subtotal_text);
                        jQuery(document).find('span#checkout_discount').html(discount_text);
                        jQuery(document).find('span#checkout_total').html(total_text);

                        if (data.total == 0) {
                            jQuery(document).find('div.payement_button_parent').children('div.bookbtn').attr("id", "free_booking_no_payment");
                            jQuery(document).find('div#free_booking_no_payment').html(bm_normal_object.free_book);
                        } else {
                            jQuery(document).find('div.payement_button_parent').children('div.bookbtn').attr("id", "go_to_payment_page");
                            jQuery(document).find('div#go_to_payment_page').html(bm_normal_object.pay + total_text);
                        }

                        jQuery([document.documentElement, document.body]).animate({
                            scrollTop: jQuery(".order_price_heading").offset().top
                        }, 2000);
                    } else if (status == 'excess') {
                        jQuery(document).find('span.age_errortext').html(bm_error_object.excess_order_total);
                    } else if (status == 'negative') {
                        jQuery(document).find('span.age_errortext').html(bm_error_object.discount_not_applicable);
                    } else {
                        jQuery(document).find('span.age_errortext').html(bm_error_object.server_error);
                    }
                });
            } else {
                return false;
            }
        });

        // Reset discount in checkout form
        $(document).on('click', '#reset_checkout_discount', function (e) {
            e.preventDefault();
            jQuery('.loader_modal').show();
            jQuery(document).find('span.age_errortext').html('');

            var data = { 'booking_key': getUrlParameter('flexi_booking'), 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('bm_reset_discount', data, function (response) {
                jQuery('.loader_modal').hide();
                var jsondata = bmSafeParse(response);
                var status = jsondata.status ? jsondata.status : '';
                var data = jsondata.data ? jsondata.data : '';
                var currency_symbol = bm_normal_object.currency_symbol;
                var currency_position = bm_normal_object.currency_position;
                if (status == 'success' && typeof (data.subtotal) != "undefined" && typeof (data.discount) != "undefined" && typeof (data.total) != "undefined") {
                    if (data.discount <= 0) {
                        jQuery('.discount_li').addClass('hidden');
                    } else {
                        jQuery('.discount_li').removeClass('hidden');
                    }

                    jQuery(document).find('span#checkout_subtotal').html('');
                    jQuery(document).find('span#checkout_discount').html('');
                    jQuery(document).find('span#checkout_total').html('');

                    jQuery(document).find('input#age_group_total_0').val(0);
                    jQuery(document).find('input#age_group_total_1').val(0);
                    jQuery(document).find('input#age_group_total_2').val(0);
                    jQuery(document).find('input#age_group_total_3').val(0);

                    var subtotal_text = currency_position == 'before' ? currency_symbol + changePriceFormat(parseFloat(data.subtotal).toFixed(2)) : changePriceFormat(parseFloat(data.subtotal).toFixed(2)) + currency_symbol;
                    var discount_text = currency_position == 'before' ? currency_symbol + changePriceFormat(parseFloat(data.discount).toFixed(2)) : changePriceFormat(parseFloat(data.discount).toFixed(2)) + currency_symbol;
                    var total_text = currency_position == 'before' ? currency_symbol + changePriceFormat(parseFloat(data.total).toFixed(2)) : changePriceFormat(parseFloat(data.total).toFixed(2)) + currency_symbol;
                    jQuery(document).find('span#checkout_discount').removeClass('negative_discount').addClass('positive_discount');

                    jQuery(document).find('span#checkout_subtotal').html(subtotal_text);
                    jQuery(document).find('span#checkout_discount').html(discount_text);
                    jQuery(document).find('span#checkout_total').html(total_text);

                    if (data.total == 0) {
                        jQuery(document).find('div.payement_button_parent').children('div.bookbtn').attr("id", "free_booking_no_payment");
                        jQuery(document).find('div#free_booking_no_payment').html(bm_normal_object.free_book);
                    } else {
                        jQuery(document).find('div.payement_button_parent').children('div.bookbtn').attr("id", "go_to_payment_page");
                        jQuery(document).find('div#go_to_payment_page').html(bm_normal_object.pay + total_text);
                    }

                    jQuery([document.documentElement, document.body]).animate({
                        scrollTop: jQuery(".order_price_heading").offset().top
                    }, 2000);

                } else {
                    jQuery(document).find('span.age_errortext').html(bm_error_object.server_error);
                }
            });
        });

        // Gallery previous slide
        $(document).on('click', '.gallery_prev', function (e) {
            e.preventDefault();
            galleryPlusSlides(-1);
        });

        // Gallery next slide
        $(document).on('click', '.gallery_next', function (e) {
            e.preventDefault();
            galleryPlusSlides(1);
        });

        // Initialize multiselect
        initializeMultiselect('search_by_category');
        initializeMultiselect('search_by_service');

        // Fill woocommerce states wrt country
        $('.woocommerce_gift_fields #is_gift').on('click', function () {
            bm_slide_up_down('gift_fields');
        });

        $('.woocommerce_gift_fields #gift_details\\[country\\]').change(function () {
            var country = $(this).val();
            var stateField = $('.woocommerce_gift_fields #gift_details\\[state\\]');
            var post = {
                country,
            }

            var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
            bmPublicRestRequest('fetch_woocommerce_states', data, function (response) {
                stateField.empty();
                if (response.success && response.data) {
                    $.each(response.data, function (stateCode, stateName) {
                        stateField.append(new Option(stateName, stateCode));
                    });
                } else {
                    stateField.append(new Option(bm_normal_object.no_states_available, ''));
                }
            });
        });

        $('.woocommerce_gift_fields #gift_details\\[first_name\\]').closest('.form-row').addClass('form-row-first');
        $('.woocommerce_gift_fields #gift_details\\[last_name\\]').closest('.form-row').addClass('form-row-last');
        $('.woocommerce_gift_fields #gift_details\\[email\\]').closest('.form-row').addClass('form-row-first');
        $('.woocommerce_gift_fields #gift_details\\[contact\\]').closest('.form-row').addClass('form-row-last');
        $('.woocommerce_gift_fields #gift_details\\[address\\]').closest('.form-row').addClass('form-row-wide');
        $('.woocommerce_gift_fields #gift_details\\[city\\]').closest('.form-row').addClass('form-row-first');
        $('.woocommerce_gift_fields #gift_details\\[state\\]').closest('.form-row').addClass('form-row-last');
        $('.woocommerce_gift_fields #gift_details\\[postcode\\]').closest('.form-row').addClass('form-row-first');
        $('.woocommerce_gift_fields #gift_details\\[country\\]').closest('.form-row').addClass('form-row-last');

        // Fill checkout page states wrt country
        (async function () {
            const country_code = $.trim($('select[name="billing_country"]').val());

            if (country_code) {
                await bm_get_state_of_country(country_code, $('select[name="billing_state"]'));
            }

            $('select[name="billing_country"], select[id="shipping_country"], select[id="recipient_country"]').on('change', async function () {
                const country = $(this).val();
                let stateField;

                $('.loader_modal').show();

                if ($(this).attr('name') === 'billing_country') {
                    stateField = $('select[name="billing_state"]');
                } else if ($(this).attr('id') === 'shipping_country') {
                    stateField = $('select[id="shipping_state"]');
                } else if ($(this).attr('id') === 'recipient_country') {
                    stateField = $('select[id="recipient_state"]');
                }

                if (stateField) {
                    await bm_get_state_of_country(country, stateField);
                }
            });
        })();

        // Service calendar shortcode init
        let is_svc_calendar_shortcode = bm_normal_object.is_svc_calendar_shortcode;

        if (is_svc_calendar_shortcode == 1) {
            $('.service_calendar_details').each(function (id, item) {
                const serviceId = $(this).data('service-id');
                initiateServiceCalendar('service_calendar_details', serviceId, $(this));
            });
        }

        // Hide checkout page title
        if (window.location.href.includes("flexibooking-checkout")) {
            let titleElement = document.querySelector(".entry-title");
            if (titleElement) {
                titleElement.style.display = "none";
            }
        }
    }

    static bm_get_state_of_country(country, stateField) {
        if (country) {
            const data = {
                action: 'get_states',
                country,
                nonce: bm_ajax_object.nonce
            };

            return bmPublicRestRequest('fetch_woocommerce_states', data)
                .then(function (response) {
                    stateField.empty();
                    jQuery('.no_states_message').remove();

                    if (response.success && response.data && Object.keys(response.data).length > 0) {
                        jQuery.each(response.data, function (stateCode, state) {
                            stateField.append(new Option(state.name, state.name));
                        });
                    } else {
                        stateField.after(jQuery('<div class="no_states_message">' + bm_normal_object.no_states_available + '</div>'));
                    }
                })
                .catch(function (error) {
                    alert(bm_error_object.server_error);
                })
                .always(function () {
                    jQuery('.loader_modal').hide();
                });
        }
    }
}

window.BMPublic = window.BMPublic || {};
window.BMPublic.Core = BMPublicCore;

// Global alias
window.bm_get_state_of_country = BMPublicCore.bm_get_state_of_country;

jQuery(document).ready(function ($) {
    BMPublicCore.init($);
});

// document.querySelector('.calendar-box .ui-datepicker .ui-datepicker-title').innerHTML = document.querySelector('.calendar-box .ui-datepicker-title').innerHTML.replace('年', '');


