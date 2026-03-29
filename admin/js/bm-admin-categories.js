/**
 * BMCategoryManager - Category management operations.
 * @since 1.1.0
 */
class BMCategoryManager {
    constructor() {
        jQuery(document).ready(($) => { this.init($); });
    }

    init($) {
        // Sort Category Listing
        $('.category_records').sortable({
            axis: "y",
            items: ".single_category_record",
            containment: "#category_records_listing",
            revert: true,
            scroll: true,
            cursor: "move",
            update: function () {
                var ids = {};
                var pagenum = sessionStorage.getItem("categoryPagno");
                $(".category_records .single_category_record .category_listing_number").each(function (i) {
                    ids[i] = $(this).data('id');
                })
                bm_sort_category_listing(ids, pagenum != null ? pagenum : '1');
            }
        }).disableSelection();
    }

    static sortCategoryListing(ids = [], pagenum = 1) {
        var post = {
            'pagenum': pagenum ? pagenum : jQuery('#category_pagenum').val(),
            'base': jQuery(location).attr("href"),
            'limit': jQuery.trim(jQuery('#limit_count').val()),
            'ids': ids,
        }

        var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
        bmRestRequest('bm_sort_category_listing', data, function (response) {
            var jsondata = bmSafeParse(response);
            var status = jsondata.status ? jsondata.status : '';
            if (status == true) {
                jQuery(".category_records").html('');
                jQuery(".category_pagination").html('');
                var categories = jsondata.categories ? jsondata.categories : 0;
                var cat_ids = jsondata.cat_ids ? jsondata.cat_ids : '';
                var pagination = jsondata.pagination ? jsondata.pagination : '';
                var current_pagenumber = jsondata.current_pagenumber ? jsondata.current_pagenumber : '';
                var categoryListing = '';
                jQuery(".overallCategoryShortcode").val('[sgbm_service_by_category ids="' + cat_ids + '"]');

                for (var i = 0; i < categories.length; i++) {
                    categoryListing += "<tr class='single_category_record ui-sortable-handle'><form role='form' method='post'>" +
                        "<td style='text-align: center;cursor:move;' data-id='" + categories[i].id + "' data-order=" + (i + 1) + " data-position='" + categories[i].cat_position + "' class='category_listing_number'>" + (current_pagenumber ? current_pagenumber : (i + 1)) + "</td>" +
                        "<td style='text-align: center;cursor:move;' title=" + categories[i].cat_name + ">" + categories[i].cat_name.substring(0, 40) + '...' + " </td>" +
                        "<td style='text-align: center;' class='bm-checkbox-td'>" +
                        "<input name='bm_show_category_in_front' type='checkbox' id='bm_show_category_in_front_" + categories[i].id + "' class='regular-text auto-checkbox bm_toggle' " + (categories[i].cat_in_front == 1 ? 'checked' : '') + " onchange='bm_change_category_visibility(this)'>" +
                        "<label for='bm_show_category_in_front_" + categories[i].id + "'></label>" +
                        "</td>" +
                        "<td style='text-align: center;'>" +
                        "<div class='copyMessagetooltip'>" +
                        '<input style="cursor:pointer;border:none;width:240px;padding: 2px 2px 6px 12px;font-family:serif;" class="copytextTooltip" id="copyInput_' + categories[i].id + '" onclick="bm_copy_text(this)" onmouseout="bm_copy_message(this)" readonly>' +
                        "<span class='tooltiptext' id='copyTooltip_" + categories[i].id + "'>" + bm_normal_object.copy_to_clipboard + "</span>" +
                        "</div></td>" +
                        "<td style='text-align: center;'>" +
                        "<button type='button' name='editcat' id='editcat' style='margin-right:3px' title='" + bm_normal_object.edit + "' value='" + categories[i].id + "'><i class='fa fa-edit' aria-hidden='true'></i></button>" +
                        "<button type='button' name='delcat' id='delcat' title='" + bm_normal_object.remove + "' value='" + categories[i].id + "'><i class='fa fa-trash' aria-hidden='true' style='color:red'></i></button>" +
                        "</td>" +
                        "</form></tr>";
                    current_pagenumber++;
                }
                jQuery(".category_records").append(categoryListing);
                jQuery(".category_pagination").append(pagination);

                if (categoryListing != '') {
                    for (var i = 0; i < categories.length; i++) {
                        var id = categories[i].id.toString().trim();
                        var shortcode = '[sgbm_service_by_category ids="' + id + '"]';
                        jQuery('#copyInput_' + id).val(shortcode);
                    }
                }
            }
        });
    }

    // Change category visiblity
    static changeCategoryVisibility($this) {
        var id = jQuery($this).attr('id');

        if (confirm(bm_normal_object.change_cat_visibility)) {
            var category_id = id.split('_')[5];
            var data = { 'id': category_id, 'nonce': bm_ajax_object.nonce };
            bmRestRequest('bm_change_category_visibility', data, function (response) {
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
}

// Attach to namespace
window.BMAdmin = window.BMAdmin || {};
window.BMAdmin.CategoryManager = BMCategoryManager;

window.bmCategoryManager = new BMCategoryManager();

// Global aliases
window.bm_sort_category_listing = BMCategoryManager.sortCategoryListing;
window.bm_change_category_visibility = BMCategoryManager.changeCategoryVisibility;
