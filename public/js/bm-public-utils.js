/**
 * BMPublicUtils - Common utility methods for public area.
 * @since 1.1.0
 */
var slideIndex = 1;

class BMPublicUtils {
    static bmPublicRestRequest(action, data, successCallback) {
        return jQuery.ajax({
            url: bm_ajax_object.rest_url + 'public-action/' + action,
            method: 'POST',
            data: data,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', bm_ajax_object.rest_nonce);
            },
            success: successCallback
        });
    }

    /**
     * Safely parse a response that may already be an object (auto-parsed by jQuery)
     * or still a JSON string.
     */
    static bmSafeParse(response) {
        if ( typeof response === 'string' ) {
            try { return JSON.parse(response); } catch(e) { return response; }
        }
        return response;
    }

    static getUrlParameter(sParam) {
        var sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
            }
        }
        return false;
    }

    static getUrlVars(string = '') {
        var vars = [], hash;
        if (string != '') {
            var hashes = string.slice(string.indexOf('?') + 1).split('&');
        } else {
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        }

        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }

    static strict_encode(string = '') {
        return btoa(encodeURIComponent(string));
    }

    static strict_decode(string = '') {
        return decodeURIComponent(atob(string));
    }

    static padWithZeros(number) {
        var lengthOfNumber = (parseInt(number) + '').length;
        if (lengthOfNumber == 2) return number;
        else if (lengthOfNumber == 1) return '0' + number;
        else if (lengthOfNumber == 0) return '00';
        else return false;
    }

    static changePriceFormat(price) {
        price = !isNaN(parseFloat(price)) ? parseFloat(price) : 0.00;
        var formatLocale = bm_normal_object.price_format ? bm_normal_object.price_format : 'it-IT';
        formatLocale = formatLocale.replace('_', '-');
        var currency = bm_normal_object.currency_type ? bm_normal_object.currency_type : 'EUR';

        const formattedPrice = new Intl.NumberFormat(formatLocale, {
            // style: 'currency',
            // currency: currency,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(price);

        return formattedPrice;
    }

    static closeModal(id) {
        // jQuery('#' + id).removeClass('active-slot');

        var modal = jQuery('#' + id);

        modal.animate({ top: "-=100px" }, 300, function () {
            modal.css({ top: "" });
            modal.removeClass('active-slot');
        });
    }

    static getFormData(formId) {
        var formData = {};
        var inputs = jQuery('#' + formId).serializeArray();

        jQuery.each(inputs, function (i, input) {
            formData[input.name] = input.value;
        });

        return formData;
    }

    static mobileFilter() {
        var x = document.getElementById("leftbar-modal");
        if (x.style.display == "block") {
            x.style.display = "none";
        } else {
            x.style.display = "block";
        }
    }

    static showGridOrList($instance) {
        if ($instance == 'gridview') {
            jQuery('.gridview').show();
            jQuery('.listview').hide();
        } else {
            jQuery('.gridview').hide();
            jQuery('.listview').show();
        }
    }

    static galleryPlusSlides(n) {
        BMPublicUtils.showGallerySlides(slideIndex += n);
    }

    static galleryCurrentSlide(n) {
        BMPublicUtils.showGallerySlides(slideIndex = n);
    }

    static showGallerySlides(n) {
        var i;
        var slides = document.getElementsByClassName("gallery_slides");
        var dots = document.getElementsByClassName("gallery_single_image");
        // var captionText = document.getElementById("caption");
        if (n > slides.length) { slideIndex = 1 }
        if (n < 1) { slideIndex = slides.length }
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" gallery_active", "");
        }
        slides[slideIndex - 1].style.display = "block";
        dots[slideIndex - 1].className += " gallery_active";
        // captionText.innerHTML = dots[slideIndex - 1].alt;
    }

    static initializeMultiselect(a) {
        jQuery('#' + a).multiselect('reload');
        jQuery('#' + a).multiselect({
            columns: 1,
            texts: {
                placeholder: a == 'search_by_service' || a == 'search_fullcalendar_by_service' || a == 'search_timeslot_fullcalendar_by_service' ? bm_normal_object.filter_service : bm_normal_object.filter_category,
                search: bm_normal_object.search_here,
                selectAll: bm_normal_object.select_all
            },
            search: true,
            selectAll: true,
            onOptionClick: function (element, option) {
                var maxSelect = 1000;

                // too many selected, deselect this option
                if (jQuery(element).val().length > maxSelect) {
                    if (jQuery(option).is(':checked')) {
                        var thisVals = jQuery(element).val();

                        thisVals.splice(
                            thisVals.indexOf(jQuery(option).val()), 1
                        );

                        jQuery(element).val(thisVals);

                        jQuery(option).prop('checked', false).closest('li')
                            .toggleClass('selected');
                    }
                }
            }
        });
    }

    static change_flexi_language($this) {
        var lang_code = jQuery($this).val();
        sessionStorage.setItem("flexi_current_lang", lang_code);

        var post = {
            'flexi_lang_code': lang_code,
        }

        var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
        bmPublicRestRequest('bm_flexi_set_frontend_lang', data, function (response) {
            var jsondata = bmSafeParse(response);
            var status = jsondata.status ? jsondata.status : '';

            if (status == true) {
                location.reload();
            } else {
                // Display an error message
                return false;
            }
        });
    }

    static bm_open_close_tab(a) {
        if (jQuery('#' + a).is(':visible')) {
            jQuery('#' + a).hide();
        } else {
            jQuery('#' + a).show();

            if (a == 'shipping_fields') {
                const shipping_state = jQuery.trim(jQuery('select[id="shipping_state"]').val());

                if (!shipping_state) {
                    const country_code = jQuery.trim(jQuery('select[id="shipping_country"]').val());

                    if (country_code) {
                        bm_get_state_of_country(country_code, jQuery('select[id="shipping_state"]'));
                    }
                }

                jQuery([document.documentElement, document.body]).animate({
                    scrollTop: jQuery("#shipping_fields").offset().top
                }, 2000);
            }
        }
    }

    static bm_slide_up_down(a) {
        if (jQuery('#' + a).is(':visible')) {
            jQuery('#' + a).slideUp("slow");
        } else {
            jQuery('#' + a).slideDown("slow");

            if (a == 'gift_fields') {
                const recipient_state = jQuery.trim(jQuery('select[id="recipient_state"]').val());

                if (!recipient_state) {
                    const country_code = jQuery.trim(jQuery('select[id="recipient_country"]').val());

                    if (country_code) {
                        bm_get_state_of_country(country_code, jQuery('select[id="recipient_state"]'));
                    }
                }
            }
        }
    }
}

window.BMPublic = window.BMPublic || {};
window.BMPublic.Utils = BMPublicUtils;

// Global aliases
window.bmPublicRestRequest = BMPublicUtils.bmPublicRestRequest;
window.bmSafeParse = BMPublicUtils.bmSafeParse;
window.getUrlParameter = BMPublicUtils.getUrlParameter;
window.getUrlVars = BMPublicUtils.getUrlVars;
window.strict_encode = BMPublicUtils.strict_encode;
window.strict_decode = BMPublicUtils.strict_decode;
window.padWithZeros = BMPublicUtils.padWithZeros;
window.changePriceFormat = BMPublicUtils.changePriceFormat;
window.closeModal = BMPublicUtils.closeModal;
window.getFormData = BMPublicUtils.getFormData;
window.mobileFilter = BMPublicUtils.mobileFilter;
window.showGridOrList = BMPublicUtils.showGridOrList;
window.galleryPlusSlides = BMPublicUtils.galleryPlusSlides;
window.galleryCurrentSlide = BMPublicUtils.galleryCurrentSlide;
window.showGallerySlides = BMPublicUtils.showGallerySlides;
window.initializeMultiselect = BMPublicUtils.initializeMultiselect;
window.change_flexi_language = BMPublicUtils.change_flexi_language;
window.bm_open_close_tab = BMPublicUtils.bm_open_close_tab;
window.bm_slide_up_down = BMPublicUtils.bm_slide_up_down;
