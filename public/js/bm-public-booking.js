/**
 * BMPublicBooking - Booking-related methods for public area.
 * @since 1.1.0
 */
class BMPublicBooking {
    static validateFields($this = '') {

        if ($this != '') {
            var type = jQuery($this).prop('type');
            var required = jQuery($this).prop("required");
            var visiblity = jQuery($this).parent().parent().is(':visible');

            if (type !== 'hidden' && type !== 'button' && type !== 'submit' && type !== 'reset' && type !== 'search') {
                switch (type) {

                    case 'checkbox':
                        var checked = $this.is(':checked');

                        if (jQuery($this).attr('name') == 'terms_conditions') {
                            jQuery($this).parent().find('div.terms_required_errortext').remove();
                            if (checked == false) jQuery($this).next('label').after('<div class="terms_required_errortext">' + bm_error_object.term_co + '</div>');
                        } else if (jQuery($this).attr('name') == 'terms_conditions1') {
                            jQuery($this).parent().find('div.terms_required1_errortext').remove();
                            if (checked == false) jQuery($this).next('label').after('<div class="terms_required1_errortext">' + bm_error_object.term_co + '</div>');
                        } else if (visiblity == true && required == true) {
                            jQuery($this).parents('.checkbox_and_radio_div').find('div.checkbox_required_errortext').remove();
                            if (checked == false) {
                                if (jQuery($this).parents('.checkbox_and_radio_div').find('div.checkbox_required_errortext').length == 0) {
                                    jQuery($this).parents('.checkbox_and_radio_div').append('<div class="checkbox_required_errortext">' + bm_error_object.required + '</div>');
                                }
                            }
                        }
                        break;

                    case 'radio':
                        jQuery($this).parents('.checkbox_and_radio_div').find('div.checkbox_required_errortext').remove();

                        var checked = $this.is(':checked');

                        if (visiblity == true && required == true && checked == false) {
                            if (jQuery($this).parents('.checkbox_and_radio_div').find('div.checkbox_required_errortext').length == 0) {
                                jQuery($this).parents('.checkbox_and_radio_div').append('<div class="checkbox_required_errortext">' + bm_error_object.required + '</div>');
                            }
                        }
                        break;

                    case 'select':
                        jQuery($this).parents('.checkbox_and_radio_div').find('div.checkbox_required_errortext').remove();

                        var selected = $this.is(':selected');

                        if (selected == false && visiblity == true && required == true) {
                            if (jQuery($this).parents('.checkbox_and_radio_div').find('div.checkbox_required_errortext').length == 0) {
                                jQuery($this).parents('.checkbox_and_radio_div').append('<div class="checkbox_required_errortext">' + bm_error_object.required + '</div>');
                            }
                        }
                        break;

                    case 'email':
                        jQuery($this).parent().find('div.required_errortext').remove();

                        var pattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
                        var value = jQuery($this).val();

                        if (visiblity == true) {
                            if (required == true) {
                                if (value == '') {
                                    jQuery($this).after('<div class="required_errortext">' + bm_error_object.required + '</div>');
                                } else if (!pattern.test(value)) {
                                    jQuery($this).after('<div class="required_errortext">' + bm_error_object.invalid_email + '</div>');
                                }
                            } else if (value != '' && !pattern.test(value)) {
                                jQuery($this).after('<div class="required_errortext">' + bm_error_object.invalid_email + '</div>');
                            }
                        }
                        break;

                    case 'tel':
                        jQuery($this).parent().find('div.required_errortext').remove();

                        var pattern = /([0-9]{10})|(\([0-9]{3}\)\s+[0-9]{3}\-[0-9]{4})/;
                        var value = jQuery($this).val();

                        if (visiblity == true) {
                            if (required == true) {
                                if (value == '') {
                                    jQuery($this).after('<div class="required_errortext">' + bm_error_object.required + '</div>');
                                } else if (!pattern.test(value)) {
                                    jQuery($this).after('<div class="required_errortext">' + bm_error_object.invalid_contact + '</div>');
                                }
                            } else if (value != '' && !pattern.test(value)) {
                                jQuery($this).after('<div class="required_errortext">' + bm_error_object.invalid_contact + '</div>');
                            }
                        }
                        break;

                    case 'url':
                        jQuery($this).parent().find('div.required_errortext').remove();

                        var pattern = /^((https?|ftp|smtp):\/\/)?(www.)?[a-z0-9]+\.[a-z]+(\/[a-zA-Z0-9#]+\/?)*$/;
                        var value = jQuery($this).val();

                        if (visiblity == true) {
                            if (required == true) {
                                if (value == '') {
                                    jQuery($this).after('<div class="required_errortext">' + bm_error_object.required + '</div>');
                                } else if (!pattern.test(value)) {
                                    jQuery($this).after('<div class="required_errortext">' + bm_error_object.invalid_url + '</div>');
                                }
                            } else if (value != '' && !pattern.test(value)) {
                                jQuery($this).after('<div class="required_errortext">' + bm_error_object.invalid_url + '</div>');
                            }
                        }
                        break;

                    case 'password':
                        jQuery($this).parent().find('div.required_errortext').remove();

                        var pattern = /^(?=.*[A-Z].*[A-Z])(?=.*[!@#$&*])(?=.*[0-9].*[0-9])(?=.*[a-z].*[a-z].*[a-z]).{8}$/;
                        var value = jQuery($this).val();

                        if (visiblity == true) {
                            if (required == true) {
                                if (value == '') {
                                    jQuery($this).after('<div class="required_errortext">' + bm_error_object.required + '</div>');
                                } else if (!pattern.test(value)) {
                                    jQuery($this).after('<div class="required_errortext">' + bm_error_object.invalid_password + '</div>');
                                }
                            } else if (value != '' && !pattern.test(value)) {
                                jQuery($this).after('<div class="required_errortext">' + bm_error_object.invalid_password + '</div>');
                            }
                        }
                        break;

                    case 'text':
                    case 'date':
                    case 'time':
                    case 'datetime':
                    case 'month':
                    case 'week':
                    case 'number':
                    case 'textarea':
                        jQuery($this).parent().find('div.required_errortext').remove();

                        var value = jQuery($this).val();

                        if (visiblity == true) {
                            if (required == true && value == '') {
                                jQuery($this).after('<div class="required_errortext">' + bm_error_object.required + '</div>');
                            }
                        }
                        break;

                    default:
                        jQuery($this).parent().find('div.required_errortext').remove();

                        var value = jQuery($this).val();

                        if (visiblity == true) {
                            if (required == true && value == '') {
                                jQuery($this).after('<div class="required_errortext">' + bm_error_object.required + '</div>');
                            }
                        }
                        break;
                }

            }
        }
    }

    static setIntlInput(formID) {
        jQuery('#' + formID + ' :input').map(function () {
            var type = jQuery(this).prop("type");
            var id = jQuery(this).attr("id");

            if ((type == "tel") && jQuery(this).hasClass('intl_phone_field_input')) {
                jQuery("#" + id).intlTelInput({
                    initialCountry: bm_normal_object.booking_country,
                    separateDialCode: true,
                    autoInsertDialCode: true,
                    showFlags: true,
                    utilsScript: bm_intl_script.script_url
                });
            }
        });
    }

    static bm_filter_services($this) {
        jQuery('.gridview').html('');
        jQuery('.listview').html('');
        jQuery('.loader_modal').show();
        var ids = [];

        jQuery($this).parents(".all_available_services").find("input:checked").each(function () {
            var id = jQuery(this).attr('name').split('_')[1];
            ids.push(id);
        });

        var post = {
            'pagenum': 1,
            'base': jQuery(location).attr("href"),
            'order': jQuery.trim(jQuery('#service_category_result_order').val()),
            'ids': ids,
            'limit': jQuery.trim(jQuery('#limit_count').val()),
            'date': jQuery('#booking_date').val(),
        }

        var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
        bmPublicRestRequest('bm_filter_services', data, function (response) {
            jQuery('.loader_modal').hide();
            jQuery('.gridview').html('');
            jQuery('.listview').html('');
            if (response) {
                var jsondata = bmSafeParse(response);
                jQuery('.gridview').html(jsondata.data);
                jQuery('.listview').html(jsondata.data);
                jQuery('.pagination').html(jsondata.pagination);
            } else {
                jQuery('.gridview').html(bm_error_object.server_error);
                jQuery('.listview').html(bm_error_object.server_error);
            }
        });
    }

    static bm_filter_categories($this) {
        jQuery('.gridview').html('');
        jQuery('.listview').html('');
        jQuery('.loader_modal').show();
        var cat_ids = [];
        var svc_ids = [];

        jQuery($this).parents(".all_available_categories").find("input:checked").each(function () {
            var id = jQuery(this).attr('name').split('_')[1];
            cat_ids.push(id);
        });

        jQuery("#search_by_service option:selected").each(function () {
            svc_ids.push(jQuery(this).val());
        });

        var post = {
            'pagenum': 1,
            'base': jQuery(location).attr("href"),
            'order': jQuery.trim(jQuery('#service_category_result_order').val()),
            'ids': cat_ids,
            'svc_ids': svc_ids,
            'limit': jQuery.trim(jQuery('#limit_count').val()),
            'date': jQuery('#booking_date').val(),
        }

        var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
        bmPublicRestRequest('bm_filter_categories', data, function (response) {
            jQuery('.loader_modal').hide();
            jQuery('.gridview').html('');
            jQuery('.listview').html('');
            if (response) {
                var jsondata = bmSafeParse(response);
                jQuery('.gridview').html(jsondata.data);
                jQuery('.listview').html(jsondata.data);
                jQuery('.pagination').html(jsondata.pagination);
            } else {
                jQuery('.gridview').html(bm_error_object.server_error);
                jQuery('.listview').html(bm_error_object.server_error);
            }
        });
    }

    static bm_filter_service_by_category() {
        jQuery('.gridview').html('');
        jQuery('.listview').html('');
        jQuery('.loader_modal').show();

        var categories = [];
        jQuery("#search_by_category option:selected").each(function () {
            var id = jQuery(this).val();
            categories.push(id);
        });

        var post = {
            'pagenum': 1,
            'base': jQuery(location).attr("href"),
            'ids': categories,
            'order': jQuery.trim(jQuery('#service_category_result_order').val()),
            'limit': jQuery.trim(jQuery('#limit_count').val()),
            'date': jQuery('#booking_date').val(),
        }

        var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
        bmPublicRestRequest('bm_filter_service_by_category', data, function (response) {
            jQuery('.loader_modal').hide();
            jQuery('.gridview').html('');
            jQuery('.listview').html('');
            if (response) {
                var jsondata = bmSafeParse(response);
                jQuery('.gridview').html(jsondata.data);
                jQuery('.listview').html(jsondata.data);
                jQuery('.pagination').html(jsondata.pagination);
            } else {
                jQuery('.gridview').html(bm_error_object.server_error);
                jQuery('.listview').html(bm_error_object.server_error);
            }
        });
    }

    static bm_filter_services_by_id() {
        jQuery('.gridview').html('');
        jQuery('.listview').html('');
        jQuery('.loader_modal').show();

        var svc_ids = [];
        var cat_ids = [];

        jQuery("#search_by_service option:selected").each(function () {
            var id = jQuery(this).val();
            svc_ids.push(id);
        });

        jQuery(".all_available_categories").find("input:checked").each(function () {
            var id = jQuery(this).attr('name').split('_')[1];
            cat_ids.push(id);
        });

        var post = {
            'pagenum': 1,
            'base': jQuery(location).attr("href"),
            'ids': svc_ids,
            'cat_ids': cat_ids,
            'order': jQuery.trim(jQuery('#service_category_result_order').val()),
            'limit': jQuery.trim(jQuery('#limit_count').val()),
            'date': jQuery('#booking_date').val(),
        }

        var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
        bmPublicRestRequest('bm_filter_services_by_id', data, function (response) {
            jQuery('.loader_modal').hide();
            jQuery('.gridview').html('');
            jQuery('.listview').html('');
            if (response) {
                var jsondata = bmSafeParse(response);
                jQuery('.gridview').html(jsondata.data);
                jQuery('.listview').html(jsondata.data);
                jQuery('.pagination').html(jsondata.pagination);
            } else {
                jQuery('.gridview').html(bm_error_object.server_error);
                jQuery('.listview').html(bm_error_object.server_error);
            }
        });
    }

    static bm_fetch_all_services(pagenum = '', $type = '') {
        jQuery('.gridview').html('');
        jQuery('.listview').html('');
        jQuery('.loader_modal').show();

        var booking_date = jQuery('#booking_date').val();

        if ($type == 'mobile') {
            booking_date = jQuery('#booking_date_mobile').val();
        }

        var post = {
            'pagenum': pagenum != '' ? pagenum : jQuery('#svc_search_shortcode_pagenum').val(),
            'base': jQuery(location).attr("href"),
            'order': jQuery.trim(jQuery('#service_category_result_order').val()),
            'limit': jQuery.trim(jQuery('#limit_count').val()),
            'date': booking_date,
        }

        var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
        bmPublicRestRequest('bm_fetch_all_services', data, function (response) {
            jQuery('.loader_modal').hide();
            jQuery('.gridview').html('');
            jQuery('.listview').html('');

            if (response) {
                var jsondata = bmSafeParse(response);
                var active_ids = jsondata.service_ids;

                jQuery('.gridview').html(jsondata.data);
                jQuery('.listview').html(jsondata.data);
                jQuery('.pagination').html(jsondata.pagination);

                if (active_ids.length != 0) {
                    var encoded_string = strict_encode(active_ids.join(','));
                    jQuery('#active_services').val(encoded_string);
                }
            } else {
                jQuery('.gridview').html(bm_error_object.server_error);
                jQuery('.listview').html(bm_error_object.server_error);
            }
        });
    }

    static bm_fetch_all_services_by_categories() {
        var data = { 'nonce': bm_ajax_object.nonce };
        bmPublicRestRequest('bm_fetch_all_services_by_categories', data, function (response) {
            jQuery('.service_by_category_gridview').html('');
            jQuery('.service_by_category_gridview slider1').html('');

            if (response) {
                var jsondata = bmSafeParse(response);
                jQuery('.service_by_category_gridview').html(jsondata.data);
                jQuery('.service_by_category_gridview slider1').html(jsondata.data);

            } else {
                jQuery('.service_by_category_gridview').html(bm_error_object.server_error);
                jQuery('.service_by_category_gridview slider1').html(bm_error_object.server_error);
            }
        });
    }
}

window.BMPublic = window.BMPublic || {};
window.BMPublic.Booking = BMPublicBooking;

// Global aliases
window.validateFields = BMPublicBooking.validateFields;
window.setIntlInput = BMPublicBooking.setIntlInput;
window.bm_filter_services = BMPublicBooking.bm_filter_services;
window.bm_filter_categories = BMPublicBooking.bm_filter_categories;
window.bm_filter_service_by_category = BMPublicBooking.bm_filter_service_by_category;
window.bm_filter_services_by_id = BMPublicBooking.bm_filter_services_by_id;
window.bm_fetch_all_services = BMPublicBooking.bm_fetch_all_services;
window.bm_fetch_all_services_by_categories = BMPublicBooking.bm_fetch_all_services_by_categories;
