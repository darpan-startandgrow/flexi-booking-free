/**
 * BMServiceManager - Service CRUD and management operations.
 * @since 1.1.0
 */
class BMServiceManager {
    constructor() {
        jQuery(document).ready(($) => { this.init($); });
    }

    init($) {
        // Sort Service Listing
        $('.service_records').sortable({
            axis: "y",
            items: ".single_service_record",
            containment: "#service_records_listing",
            revert: true,
            scroll: true,
            cursor: "move",
            update: function () {
                var ids = {};
                var pagenum = sessionStorage.getItem("servicePagno");
                $(".service_records .single_service_record .service_listing_number").each(function (i) {
                    ids[i] = $(this).data('id');
                })
                bm_sort_service_listing(ids, pagenum != null ? pagenum : '1');
            }
        }).disableSelection();

        // Display Condition for Tabs in Service Page
        if (getUrlParameter('extra_id') || sessionStorage.getItem("extravalue") != null) {
            if ($("#service_extra").not(':visible')) $('#service_extra').show();
            if (sessionStorage.getItem("extravalue") != null) {
                $('button.tablinks.active').removeClass('active');
                $("#extra_button").addClass("active");
            }
        } else if (sessionStorage.getItem("galleryvalue") != null) {
            if ($("#service_gallery").not(':visible')) $('#service_gallery').show();
            $('button.tablinks.active').removeClass('active');
            $("#gallery_button").addClass("active");
        } else if ((sessionStorage.getItem("variableprice") != null)) {
            if ($("#price_calendar").not(':visible')) $('#price_calendar').show();
            $('button.tablinks.active').removeClass('active');
            $("#price_calendar_button").addClass("active");
        } else if ((sessionStorage.getItem("variablehour") != null)) {
            if ($("#stopsales_calendar").not(':visible')) $('#stopsales_calendar').show();
            $('button.tablinks.active').removeClass('active');
            $("#stopsales_calendar_button").addClass("active");
        } else if ((sessionStorage.getItem("variablesaleswitch") != null)) {
            if ($("#saleswitch_calendar").not(':visible')) $('#saleswitch_calendar').show();
            $('button.tablinks.active').removeClass('active');
            $("#saleswitch_calendar_button").addClass("active");
        } else if (sessionStorage.getItem("variablecapacity") != null) {
            if ($("#capacity_calendar").not(':visible')) $('#capacity_calendar').show();
            $('button.tablinks.active').removeClass('active');
            $("#capacity_calendar_button").addClass("active");
        } else if (sessionStorage.getItem("variabletimeslot") != null) {
            if ($("#time_slots_calendar").not(':visible')) $('#time_slots_calendar').show();
            $('button.tablinks.active').removeClass('active');
            $("#time_slot_button").addClass("active");
        } else if (sessionStorage.getItem("svcsettingstab") != null) {
            if ($("#svc_settings_section").not(':visible')) $('#svc_settings_section').show();
            $('button.tablinks.active').removeClass('active');
            $("#svc_settings_button").addClass("active");
        } else {
            if ($("#service_details").not(':visible')) $('#service_details').show();
        }

        // Service Image Selection
        var custom_uploader;

        $('.svc-image').click(function (e) {
            e.preventDefault();
            //If the uploader object has already been created, reopen the dialog
            if (custom_uploader) {
                custom_uploader.open();
                return;
            }
            //Extend the wp.media object
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: bm_normal_object.choose_image,
                button: {
                    text: bm_normal_object.choose_image
                },
                library: {
                    type: 'image'
                },
                multiple: false
            });

            custom_uploader.on('select', function () {
                attachment = custom_uploader.state().get('selection').first().toJSON();
                var file_size = parseInt(attachment.filesizeInBytes);
                var file_width = parseInt(attachment.sizes.full.width);
                var file_height = parseInt(attachment.sizes.full.height);

                var min_file_size = parseInt(bm_normal_object.image_min_size);
                var max_file_size = parseInt(bm_normal_object.image_max_size);
                var minimum_width = parseInt(bm_normal_object.image_min_width);
                var maximum_width = parseInt(bm_normal_object.image_max_width);
                var minimum_height = parseInt(bm_normal_object.image_min_height);
                var maximum_height = parseInt(bm_normal_object.image_max_height);

                if (attachment['type'] == 'image') {
                    if (min_file_size != 0 && file_size < min_file_size) {
                        alert(bm_error_object.file_size_less_message + min_file_size + ' bytes');
                    } else if (max_file_size != 0 && file_size > max_file_size) {
                        alert(bm_error_object.file_size_more_message + max_file_size + ' bytes');
                    } else if (minimum_width != 0 && file_width < minimum_width) {
                        alert(bm_error_object.file_width_less_message + minimum_width + ' pixels');
                    } else if (maximum_width != 0 && file_width > maximum_width) {
                        alert(bm_error_object.file_width_more_message + maximum_width + ' pixels');
                    } else if (minimum_height != 0 && file_height < minimum_height) {
                        alert(bm_error_object.file_width_less_message + minimum_height + ' pixels');
                    } else if (maximum_height != 0 && file_height > maximum_height) {
                        alert(bm_error_object.file_width_more_message + maximum_height + ' pixels');
                    } else {
                        $('#svc_image_id').val(attachment.id);
                        $('#svc_image_preview').attr('src', attachment.url);
                        $('.svc_image_container').show();
                    }
                } else {
                    alert(bm_error_object.file_type_not_supported);
                }

            });

            //Open the uploader dialog
            custom_uploader.open();
        });


        // Service Extra Add Button
        $("#add_extra").click(function (e) {
            if ($("#svc_extra_fields").not(':visible')) $("#svc_extra_fields").css('display', 'block');
            $("#if_extra_svc").val('1');
            if ($("#extraTitle").is(':visible')) $("#extraTitle").css('display', 'none');
            if ($("#existing_extra_content").is(':visible')) $("#existing_extra_content").hide();
        });


        // Service Extra Cancel Button
        $("#cancel_extra").click(function (e) {
            if ($("#svc_extra_fields").is(':visible')) $("#svc_extra_fields").css('display', 'none');
            $("#if_extra_svc").val('0');
            if ($("#extraTitle").not(':visible')) $("#extraTitle").css('display', 'block');
            if ($("#existing_extra_content").not(':visible')) $("#existing_extra_content").show();
        });

        // Service Gallery Image Selection
        var gallery_custom_uploader;
        var counter = 0;
        var crossSign = "✕";

        $('.svc-gallery-image').click(function (e) {
            e.preventDefault();
            //If the uploader object has already been created, reopen the dialog
            if (gallery_custom_uploader) {
                gallery_custom_uploader.open();
                return;
            }

            if (sessionStorage.getItem("galleryvalue") == null) sessionStorage.setItem("galleryvalue", 1);

            //Extend the wp.media object
            gallery_custom_uploader = wp.media.frames.file_frame = wp.media({
                title: bm_normal_object.choose_image,
                button: {
                    text: bm_normal_object.choose_image
                },
                library: {
                    type: 'image'
                },
                multiple: 'add'
            });


            gallery_custom_uploader.on('select', function () {
                var image_ids = $('#svc_gallery_image_id').val();
                image_ids = image_ids.length != 0 ? image_ids.split(',') : [];
                attachments = gallery_custom_uploader.state().get('selection').toJSON();

                var min_file_size = parseInt(bm_normal_object.image_min_size);
                var max_file_size = parseInt(bm_normal_object.image_max_size);
                var minimum_width = parseInt(bm_normal_object.image_min_width);
                var maximum_width = parseInt(bm_normal_object.image_max_width);
                var minimum_height = parseInt(bm_normal_object.image_min_height);
                var maximum_height = parseInt(bm_normal_object.image_max_height);

                for (var i = 0; i < attachments.length; i++) {
                    if ($.inArray(attachments[i].id.toString(), image_ids) == -1) {
                        var file_size = parseInt(attachments[i].filesizeInBytes);
                        var file_width = parseInt(attachments[i].sizes.full.width);
                        var file_height = parseInt(attachments[i].sizes.full.height);

                        if (attachments[i]['type'] == 'image') {
                            if (min_file_size != 0 && file_size < min_file_size) {
                                counter++;
                            } else if (max_file_size != 0 && file_size > max_file_size) {
                                counter++;
                            } else if (minimum_width != 0 && file_width < minimum_width) {
                                counter++;
                            } else if (maximum_width != 0 && file_width > maximum_width) {
                                counter++;
                            } else if (minimum_height != 0 && file_height < minimum_height) {
                                counter++;
                            } else if (maximum_height != 0 && file_height > maximum_height) {
                                counter++;
                            }
                        } else {
                            counter++;
                        }

                        if (counter == 0) {
                            image_ids.push(attachments[i].id);
                            $('#gallery_images').append("<span class='svc_gallery_image_container' style='position: relative;display: inline-block;' id='svc_gallery_image_container'><image src=" + attachments[i].url + " width='100' height='100' id='svc_gallery_image_preview'>" +
                                "<button type='button' class='svc_gallery_image_remove' id=" + attachments[i].id + " title='" + bm_normal_object.remove + "' onclick='svc_gallery_remove(this)'>" + crossSign + "</button></span>");
                        }
                    }
                }

                if (counter == 0) {
                    $('#svc_gallery_image_id').val(image_ids.join(','));
                    $('#gallery_images').show();
                    $('#is_gallery_image').val('1');
                } else {
                    alert(bm_error_object.file_invalid);
                }
            });

            //Open the uploader dialog
            gallery_custom_uploader.open();
        });

        // --- Weekly Availability Checkbox Logic ---
        // Checked = available (no hidden input). Unchecked = unavailable (hidden input sent).
        $('.bm-availability-weekday').on('change', function() {
            var dayVal = $(this).data('day');
            var isChecked = $(this).is(':checked');

            // Remove any existing hidden for this day
            $('input.bm-weekday-hidden[data-day="' + dayVal + '"]').remove();

            if (!isChecked) {
                // Day is unavailable: add hidden input
                $(this).closest('td').append(
                    '<input type="hidden" name="service_unavailability[weekdays][]" value="' + dayVal + '" class="bm-weekday-hidden" data-day="' + dayVal + '">'
                );
            }
        });

        // --- Availability Periods Logic ---
        // Add Period button handler
        $('#bm_add_period').on('click', function() {
            var startVal = $('#bm_period_start').val();
            var endVal = $('#bm_period_end').val();

            if (!startVal || !endVal) {
                alert('Please select both start and end dates.');
                return;
            }

            if (endVal < startVal) {
                alert('End date must be on or after start date.');
                return;
            }

            // Check for duplicates
            var rangeText = startVal + ' to ' + endVal;
            var isDuplicate = false;
            $('#availability_periods_list .bm-availability-chip').each(function() {
                if ($(this).text().replace('×', '').trim() === rangeText) {
                    isDuplicate = true;
                }
            });
            if (isDuplicate) {
                alert('This period is already added.');
                return;
            }

            var chip = '<span class="bm-availability-chip">' +
                '<span class="dashicons dashicons-calendar-alt"></span> ' +
                startVal + ' to ' + endVal +
                '<input type="hidden" name="availability_periods_new[start][]" value="' + startVal + '">' +
                '<input type="hidden" name="availability_periods_new[end][]" value="' + endVal + '">' +
                '<button type="button" class="bm-chip-remove" onclick="bm_remove_availability_period(this)" title="Remove">&times;</button>' +
                '</span>';

            $('#availability_periods_list').append(chip);
            $('#bm_period_start').val('');
            $('#bm_period_end').val('');
        });
    }

    // Ajax for sorting service listing on Page Load
    static sortServiceListing(ids = [], pagenum = 1) {
        var post = {
            'pagenum': pagenum ? pagenum : jQuery('#service_pagenum').val(),
            'base': jQuery(location).attr("href"),
            'limit': jQuery.trim(jQuery('#limit_count').val()),
            'ids': ids,
        }

        var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
        bmRestRequest('bm_sort_service_listing', data, function (response) {
            var jsondata = bmSafeParse(response);
            var status = jsondata.status ? jsondata.status : '';
            if (status == true) {
                jQuery(".service_records").html('');
                jQuery(".service_pagination").html('');
                var services = jsondata.services ? jsondata.services : [];
                var category_name = jsondata.category_name ? jsondata.category_name : '';
                var pagination = jsondata.pagination ? jsondata.pagination : '';
                var current_pagenumber = jsondata.current_pagenumber ? jsondata.current_pagenumber : '';
                var serviceListing = '';

                for (var i = 0; i < services.length; i++) {
                    serviceListing += "<tr class='single_service_record ui-sortable-handle'><form role='form' method='post'>" +
                        "<td style='text-align: center;cursor:move;' data-id='" + services[i].id + "' data-order=" + (i + 1) + " data-position='" + services[i].service_position + "' class='service_listing_number'>" + (current_pagenumber ? current_pagenumber : (i + 1)) + "</td>" +
                        "<td style='text-align: center;cursor:move;' title=" + services[i].service_name + ">" + services[i].service_name.substring(0, 40) + '...' + " </td>" +
                        "<td style='text-align: center;' title=" + (category_name[i] ? category_name[i] : '') + ">" + (category_name[i] ? category_name[i].substring(0, 40) + '...' : '') + " </td>" +
                        "<td style='text-align: center;' class='bm-checkbox-td'>" +
                        "<input name='bm_show_service_in_front' type='checkbox' id='bm_show_service_in_front_" + services[i].id + "' class='regular-text auto-checkbox bm_toggle' " + (services[i].is_service_front == 1 ? 'checked' : '') + " onchange='bm_change_service_visibility(this)'>" +
                        "<label for='bm_show_service_in_front_" + services[i].id + "'></label>" +
                        "</td>" +
                        "<td style='text-align: center;'>" +
                        "<div class='copyMessagetooltip' style='margin-bottom: 5px;'>" +
                        "<input style='cursor:pointer;border:none;width:200px;padding: 2px 2px 6px 12px;font-family:serif;' class='copytextTooltip' value='[sgbm_single_service id=\"" + services[i].id + "\"]' onclick='bm_copy_text(this)' onmouseout='bm_copy_message(this)' readonly>" +
                        "<span class='tooltiptext'>" + bm_normal_object.copy_to_clipboard + "</span>" +
                        "<button type='button' class='bm-info-button' data-shortcode='sgbm_single_service' title='" + bm_normal_object.shortcode_info + "'>i</button>" +
                        "</div>" +
                        "<div class='copyMessagetooltip'>" +
                        "<input style='cursor:pointer;border:none;width:200px;padding: 2px 2px 6px 12px;font-family:serif;' class='copytextTooltip' value='[sgbm_single_service_calendar id=\"" + services[i].id + "\"]' onclick='bm_copy_text(this)' onmouseout='bm_copy_message(this)' readonly>" +
                        "<span class='tooltiptext'>" + bm_normal_object.copy_to_clipboard + "</span>" +
                        "<button type='button' class='bm-info-button' data-shortcode='sgbm_single_service_calendar' title='" + bm_normal_object.shortcode_info + "'>i</button>" +
                        "</div>" +
                        "</td>" +
                        "<td style='text-align: center;'>" +
                        "<button type='button' name='editsvc' id='editsvc' style='margin-right:3px' title='" + bm_normal_object.edit + "' value='" + services[i].id + "'><i class='fa fa-edit' aria-hidden='true'></i></button>" +
                        "<button type='button' name='delsvc' id='delsvc' title='" + bm_normal_object.remove + "' value='" + services[i].id + "'><i class='fa fa-trash' aria-hidden='true' style='color:red'></i></button>" +
                        "</td>" +
                        "</form></tr>";
                    current_pagenumber++;
                }
                jQuery(".service_records").append(serviceListing);
                jQuery(".service_pagination").append(pagination);

                jQuery('.bm-info-button').off('click').on('click', function() {
                    var shortcode = jQuery(this).data('shortcode');
                    var info = bm_shortcode_info[shortcode];
                    
                    if (info) {
                        jQuery('#bm-shortcode-title').text(info.title);
                        jQuery('#bm-shortcode-description').text(info.description);
                        
                        var attributesBody = jQuery('#bm-shortcode-attributes tbody');
                        attributesBody.empty();
                        
                        if (info.attributes.length > 0) {
                            jQuery.each(info.attributes, function(i, attr) {
                                attributesBody.append(
                                    '<tr>' +
                                    '<td>' + attr.name + '</td>' +
                                    '<td>' + attr.description + '</td>' +
                                    '<td>' + attr.default + '</td>' +
                                    '</tr>'
                                );
                            });
                        } else {
                            attributesBody.append(
                                '<tr><td colspan="3">' + bm_normal_object.no_attributes + '</td></tr>'
                            );
                        }
                        
                        var examplesHtml = info.examples.join('\n');
                        jQuery('#bm-shortcode-examples').text(examplesHtml);
                        
                        jQuery('#bm-shortcode-info-modal').show();
                    }
                });
            }
        });
    }

    // Change service visiblity
    static changeServiceVisibility($this) {
        var id = jQuery($this).attr('id');

        if (confirm(bm_normal_object.change_svc_visibility)) {
            var service_id = id.split('_')[5];
            var data = { 'id': service_id, 'nonce': bm_ajax_object.nonce };
            bmRestRequest('bm_change_service_visibility', data, function (response) {
                var jsondata = bmSafeParse(response);
                if (jsondata.status == true) {
                    showMessage(bm_success_object.status_successfully_changed, 'success');
                } else {
                    showMessage(bm_error_object.server_error, 'error');
                }
            });
        } else {
            if (jQuery($this).is(':checked')) {
                jQuery('#' + id).prop('checked', false);
            } else {
                jQuery('#' + id).prop('checked', true);
            }
        }
    }

    static changeExtraServiceVisibility($this) {
        var id = jQuery($this).attr('id');

        if (confirm(bm_normal_object.change_svc_visibility)) {
            var extra_id = id.split('_')[6];
            var data = { 'id': extra_id, 'nonce': bm_ajax_object.nonce };
            bmRestRequest('bm_change_extra_service_visibility', data, function (response) {
                var jsondata = bmSafeParse(response);
                if (jsondata.status == true) {
                    showMessage(bm_success_object.status_successfully_changed, 'success');
                } else {
                    showMessage(bm_error_object.server_error, 'error');
                }
            });
        } else {
            if (jQuery($this).is(':checked')) {
                jQuery('#' + id).prop('checked', false);
            } else {
                jQuery('#' + id).prop('checked', true);
            }
        }
    }

    // Service Image Remove
    static svcRemoveImage() {
        jQuery('#svc_image_id').val('');
        jQuery('#svc_image_preview').attr('src', '');
        jQuery('.svc_image_container').hide();
    }

    // Service Form Tabs
    static openSection(evt, sectionName) {

        // Remove Session Value If Exists
        if (sessionStorage.getItem("extravalue") != null) sessionStorage.removeItem("extravalue");
        if (sessionStorage.getItem("galleryvalue") != null) sessionStorage.removeItem("galleryvalue");
        if (sessionStorage.getItem("variableprice") != null) sessionStorage.removeItem("variableprice");
        if (sessionStorage.getItem("variablehour") != null) sessionStorage.removeItem("variablehour");
        if (sessionStorage.getItem("variablesaleswitch") != null) sessionStorage.removeItem("variablesaleswitch");
        if (sessionStorage.getItem("variablecapacity") != null) sessionStorage.removeItem("variablecapacity");
        if (sessionStorage.getItem("variabletimeslot") != null) sessionStorage.removeItem("variabletimeslot");
        if (sessionStorage.getItem("svcsettingstab") != null) sessionStorage.removeItem("svcsettingstab");


        // Remove Success/Error Messgaes If Exists
        jQuery('.calendar_errortext').hide();
        jQuery('.stopsales_errortext').hide();
        jQuery('.saleswitch_errortext').hide();
        jQuery('.capacity_calendar_errortext').hide();
        jQuery('.price_update_successtext').hide();
        jQuery('.stopsales_update_successtext').hide();
        jQuery('.saleswitch_update_successtext').hide();
        jQuery('.capacity_update_successtext').hide();
        jQuery('.calendar_errortext').html(' ');
        jQuery('.stopsales_errortext').html(' ');
        jQuery('.saleswitch_errortext').html(' ');
        jQuery('.capacity_calendar_errortext').html('');
        jQuery('.price_update_successtext').html('');
        jQuery('.stopsales_update_successtext').html('');
        jQuery('.saleswitch_update_successtext').html('');
        jQuery('.capacity_update_successtext').html('');

        // Tab Switch
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace("active", "");
        }
        document.getElementById(sectionName).style.display = "block";
        evt.currentTarget.className += " active";
    }

    // Service Gallery Image Remove
    static svcGalleryRemove($this) {

        // Set Session Value If Doesn't Exist
        if (sessionStorage.getItem("galleryvalue") == null) sessionStorage.setItem("galleryvalue", 1);

        // Remove Image
        var id = jQuery($this).attr('id');
        var image_ids = jQuery('#svc_gallery_image_id').val();
        image_ids = image_ids.split(',');
        if (jQuery.inArray(id, image_ids) !== -1) {
            image_ids = jQuery.grep(image_ids, function (value) {
                return value != id;
            });
        }
        jQuery('#svc_gallery_image_id').val(image_ids.join(','));
        jQuery($this).find('image').attr('src', '');
        jQuery($this).parent('span').hide();
    }

    // Add Session Value on Extra Service Edit, Update, Delete
    static extraUpdate() {
        if (sessionStorage.getItem("extravalue") == null) sessionStorage.setItem("extravalue", 1);
    }

    // Form Validation
    static addFormValidation(type = '') {

        jQuery('.errortext').html('');
        jQuery('.svc_short_desc_error').html('');
        jQuery('.errortext').hide();
        jQuery('.svc_short_desc_error').hide();

        var divclass = '.bm_required';
        var b = 0;

        if (type == 'extra') {
            divclass = '.bm_ex_required';
            if (sessionStorage.getItem("extravalue") == null) sessionStorage.setItem("extravalue", 1);
        }

        // Form Validation for extras
        if (jQuery("#if_extra_svc").val() == '1') {
            jQuery('.bm_ex_required').each(
                function (index, element) {
                    var type = jQuery(this).children().prop('type');
                    var value = type == 'select-one' ? jQuery.trim(jQuery(this).children('select').val()) : jQuery.trim(jQuery(this).children('input').val());
                    if (value == "") {
                        jQuery(this).children('.errortext').html(bm_error_object.required_field);
                        jQuery(this).children('.errortext').show();
                        b++;
                    } else if (jQuery(this).children('input').attr('name') == 'svc_extra_price') {
                        var regex = /^[1-9]\d*(\.\d+)?$/;
                        if (!value.match(regex)) {
                            jQuery(this).children('.errortext').html(bm_error_object.numeric_field);
                            jQuery(this).children('.errortext').show();
                            b++;
                        }
                    } else if (jQuery(this).children('select').attr('name') == 'svc_extra_duration') {
                        if (jQuery("#svc_extra_duration").val() > jQuery("#svc_extra_operation").val()) {
                            jQuery(this).children('.errortext').html(bm_error_object.svc_duration_field);
                            jQuery(this).children('.errortext').show();
                            b++;
                        }
                    }
                }
            );
        }

        jQuery(divclass).each(
            function (index, element) {
                var type = jQuery(this).children().prop('type');
                var value = type == 'select-one' ? jQuery.trim(jQuery(this).children('select').val()) : jQuery.trim(jQuery(this).children('input').val());
                if (value == "") {
                    if (jQuery('#time_slots').html() != '') {
                        if (jQuery(this).children().attr('id').startsWith('from_') || jQuery(this).children().attr('id').startsWith('to_') || jQuery(this).children().attr('id').startsWith('min_cap_') || jQuery(this).children().attr('id').startsWith('max_cap_')) {
                            if (!jQuery(this).children('input').prop('readonly')) {
                                jQuery(this).children('.errortext').html(bm_error_object.required);
                                b++;
                            }
                        } else {
                            jQuery(this).children('.errortext').html(bm_error_object.required_field);
                            b++;
                        }
                    } else {
                        jQuery(this).children('.errortext').html(bm_error_object.required_field);
                        b++;
                    }
                    jQuery(this).children('.errortext').show();
                } else if (jQuery(this).children('input').attr('name') == 'default_price' || jQuery(this).children('input').attr('name') == 'svc_extra_price') {
                    var regex = /^[1-9]\d*(\.\d+)?$/;
                    if (!value.match(regex)) {
                        jQuery(this).children('.errortext').html(bm_error_object.numeric_field);
                        jQuery(this).children('.errortext').show();
                        b++;
                    }
                }

                if (jQuery(this).children('select').attr('name') == 'service_duration') {
                    if (jQuery("#service_duration").val() > jQuery("#service_operation").val()) {
                        jQuery(this).children('.errortext').html(bm_error_object.svc_duration_field);
                        jQuery(this).children('.errortext').show();
                        b++;
                    }
                }

                if (jQuery(this).children('select').attr('name') == 'svc_extra_duration') {
                    if (jQuery("#svc_extra_duration").val() > jQuery("#svc_extra_operation").val()) {
                        jQuery(this).children('.errortext').html(bm_error_object.svc_duration_field);
                        jQuery(this).children('.errortext').show();
                        b++;
                    }
                }
            }
        );

        var svc_shrt_desc_chr_limit = bm_normal_object.svc_shrt_dsc_lmt;

        if (typeof tinymce !== 'undefined') {
            var editor = tinymce.get('service_short_desc');
            if (editor) {
                var content = editor.getContent({ format: 'text' })
                    .replace(/\s+/g, ' ')
                    .replace(/[\u200B-\u200D\uFEFF]/g, '')
                    .replace(/\u00A0/g, ' ')
                    .replace(/\n/g, '')
                    .trim();

                if (svc_shrt_desc_chr_limit > 0 && content.length > svc_shrt_desc_chr_limit) {
                    showMessage(bm_error_object.svc_short_desc_limit, 'error');
                    b++;
                }
            }
        }

        if (b === 0) {
            return true;
        } else {
            return false;
        }
    }

    // Check validity of age values entered in service
    static checkServiceAgeValue($this) {
        var index = Number($this.id.split("_")[2]);
        var fieldId = $this.id;
        var val = parseInt($this.value);

        if (fieldId.startsWith('age_from_')) {
            var toFieldId = jQuery('#age_to_' + index);
            var toFieldValue = parseInt(jQuery(toFieldId).val());
            var preToField = jQuery('#age_to_' + Number(index - 1));
            var preToFieldValue = parseInt(jQuery(preToField).val());
            var nextfromField = jQuery('#age_from_' + Number(index + 1));
            var nextfromFieldValue = parseInt(jQuery(nextfromField).val());

            if (jQuery(preToField).length > 0) {
                if (!isNaN(preToFieldValue) && (val <= preToFieldValue)) {
                    jQuery($this).val('');
                    showMessage(bm_error_object.must_be_greater_than + preToFieldValue, 'error');
                    return false;
                }
            }

            if (jQuery(nextfromField).length > 0) {
                if (!isNaN(nextfromFieldValue) && (val >= nextfromFieldValue)) {
                    jQuery($this).val('');
                    showMessage(bm_error_object.must_be_less_than_field, 'error');
                    return false;
                }
            }

            if (!isNaN(toFieldValue) && (val >= toFieldValue)) {
                jQuery($this).val('');
                showMessage(bm_error_object.must_be_less_than + toFieldValue, 'error');
                return false;
            }

            if (isNaN(toFieldValue)) {
                jQuery(toFieldId).val((val + 1));
                jQuery(toFieldId).attr('value', (val + 1));
                jQuery(toFieldId).attr('min', (val + 1));
            }
        } else if (fieldId.startsWith('age_to_')) {
            var fromField = jQuery('#age_from_' + index);
            var fromFieldValue = parseInt(jQuery(fromField).val());
            var nextfromField = jQuery('#age_from_' + Number(index + 1));
            var nextfromFieldValue = parseInt(jQuery(nextfromField).val());
            var preToField = jQuery('#age_to_' + Number(index - 1));
            var preToFieldValue = parseInt(jQuery(preToField).val());

            if (!isNaN(fromFieldValue) && (val <= fromFieldValue)) {
                jQuery($this).val('');
                showMessage(bm_error_object.must_be_greater_than + fromFieldValue, 'error');
                return false;
            }

            if (jQuery(preToField).length > 0) {
                if (!isNaN(preToFieldValue) && (val <= preToFieldValue)) {
                    jQuery($this).val('');
                    showMessage(bm_error_object.must_be_greater_than_field, 'error');
                    return false;
                }
            }

            if (jQuery(nextfromField).length > 0) {
                if (!isNaN(nextfromFieldValue) && (val >= nextfromFieldValue)) {
                    jQuery($this).val('');
                    showMessage(bm_error_object.must_be_less_than + nextfromFieldValue, 'error');
                    return false;
                }
            }

            if (isNaN(fromFieldValue)) {
                jQuery(fromField).val((val - 1));
                jQuery(fromField).attr('value', (val - 1));
                jQuery(fromField).attr('max', (val - 1));
            }
        }
    }

    static removeAvailabilityPeriod(el) {
        if (confirm('Remove this availability period?')) {
            jQuery(el).closest('.bm-availability-chip').remove();
        }
    }

    static removeGlobalUnavailableRange(el) {
        if (confirm('Remove this date range?')) {
            jQuery(el).parent('span').remove();

            jQuery('#global_unavailable_date_ranges .date_range_span input').each(function(index) {
                const i = index + 1;
                jQuery(this).attr('id', 'global_unavailable_date_range_' + i);
                jQuery(this).attr('name', 'bm_global_unavailability[dates][' + i + ']');
            });
        }
    }
}

// Attach to namespace
window.BMAdmin = window.BMAdmin || {};
window.BMAdmin.ServiceManager = BMServiceManager;

window.bmServiceManager = new BMServiceManager();

// Global aliases
window.bm_sort_service_listing = BMServiceManager.sortServiceListing;
window.bm_change_service_visibility = BMServiceManager.changeServiceVisibility;
window.bm_change_extra_service_visibility = BMServiceManager.changeExtraServiceVisibility;
window.svc_remove_image = BMServiceManager.svcRemoveImage;
window.openSection = BMServiceManager.openSection;
window.svc_gallery_remove = BMServiceManager.svcGalleryRemove;
window.extraUpdate = BMServiceManager.extraUpdate;
window.add_form_validation = BMServiceManager.addFormValidation;
window.checkServiceAgeValue = BMServiceManager.checkServiceAgeValue;
window.bm_remove_availability_period = BMServiceManager.removeAvailabilityPeriod;
window.bm_remove_global_unavailable_range = BMServiceManager.removeGlobalUnavailableRange;
