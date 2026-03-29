/**
 * BMCustomerManager - Customer management operations.
 * @since 1.1.0
 */
class BMCustomerManager {
    // Change customer visiblity
    static changeCustomerVisibility($this) {
        var id = jQuery($this).attr('id');

        if (confirm(bm_normal_object.change_cust_visibility)) {
            var customer_id = id.split('_')[3];
            var data = { 'id': customer_id, 'nonce': bm_ajax_object.nonce };
            bmRestRequest('bm_change_customer_visibility', data, function (response) {
                var jsondata = bmSafeParse(response);
                if (jsondata.status == true) {
                    showMessage(bm_success_object.status_successfully_changed, 'success');
                } else {
                    showMessage(bm_error_object.server_error, 'error')
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

    static customerFormValidation() {
        let b = 0;
        jQuery('.errortext').html('').hide();
        jQuery('.billing_field_errortext').html('').hide();

        const tel_pattern = /([0-9]{10})|(\([0-9]{3}\)\s+[0-9]{3}\-[0-9]{4})/;

        jQuery('.bm_required').each(function () {
            const input = jQuery(this).find('input, select');
            const value = jQuery.trim(input.val());

            if (!value) {
                jQuery(this).find('.errortext').html(bm_error_object.required_field).show();
                b++;
            } else if (input.attr('type') === 'email') {
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regex.test(value)) {
                    jQuery(this).find('.errortext').html(bm_error_object.invalid_email).show();
                    b++;
                }
            } else if (input.attr('id') === 'tel') {
                if (!tel_pattern.test(value)) {
                    jQuery(this).find('.errortext').html(bm_error_object.invalid_contact).show();
                    b++;
                }
            }
        });

        if (jQuery('#billing_contact').val() == '') {
            jQuery('.billing_contact_field').find('.billing_field_errortext').html(bm_error_object.required_field).show();
            b++;
        } else if (!tel_pattern.test(jQuery('#billing_contact').val())) {
            jQuery('.billing_contact_field').find('.billing_field_errortext').html(bm_error_object.invalid_contact).show();
            b++;
        }

        if (jQuery('#shipping_contact').val() == '') {
            jQuery('.shipping_contact_field').find('.billing_field_errortext').html(bm_error_object.required_field).show();
            b++;
        } else if (!tel_pattern.test(jQuery('#shipping_contact').val())) {
            jQuery('.shipping_contact_field').find('.billing_field_errortext').html(bm_error_object.invalid_contact).show();
            b++;
        }


        if (b > 0) {
            return Promise.resolve(false);
        }

        const post = {
            main_email: jQuery('#customer_email').val(),
            billing_email: jQuery('#billing_email').val(),
            shipping_email: jQuery('#shipping_email').val(),
            customer_id: getUrlParameter('id'),
        };

        const data = {
            post: post,
            nonce: bm_ajax_object.nonce,
        };

        return bmRestRequest('bm_check_if_exisiting_customer', data)
            .then(response => {
                let c = 0;

                if (response.success) {
                    if (response.data) {
                        if (response.data.main_email) {
                            jQuery('#customer_email').next('.errortext').html(bm_error_object.existing_mail).show();
                            c++;
                        }
                        if (response.data.billing_email) {
                            jQuery('#billing_email').next('.errortext').html(bm_error_object.existing_mail).show();
                            c++;
                        }
                        if (response.data.shipping_email) {
                            jQuery('#shipping_email').next('.errortext').html(bm_error_object.existing_mail).show();
                            c++;
                        }
                    }
                } else {
                    showMessage(bm_error_object.server_error, 'error');
                    c++;
                }

                return c === 0;
            })
            .catch(() => {
                showMessage(bm_error_object.server_error, 'error');
                return false;
            });
    }

    // Reset order page customer details content
    static resetCustomerDetails() {
        jQuery('.billing_details').hide();
        jQuery('.shipping_details').hide();
    }
}

// Attach to namespace
window.BMAdmin = window.BMAdmin || {};
window.BMAdmin.CustomerManager = BMCustomerManager;

// Global aliases
window.bm_change_customer_visibility = BMCustomerManager.changeCustomerVisibility;
window.customer_form_validation = BMCustomerManager.customerFormValidation;
window.resetCustomerDetails = BMCustomerManager.resetCustomerDetails;
