/**
 * BMOrderManager - Order management operations.
 * @since 1.1.0
 */
class BMOrderManager {
	// Reset order page
	static resetOrderPage() {
		jQuery('#service_id').prop('disabled', true);
		jQuery('#service_id').html('');
		resetNoOfServiceSelection();
		resetTimeSlots();
		resetOrderPageServicePrice();
	}

	// Reset order page service price content
	static resetOrderPageServicePrice() {
		jQuery('#base_svc_price').val('');
		jQuery('#service_cost').val('');
		jQuery('#service_discount').val(0);
		jQuery('#base_svc_price').prop('disabled', true);
		jQuery('#service_cost').prop('disabled', true);
		jQuery('#base_svc_price').prop('readonly', false);
		jQuery('#service_cost').prop('readonly', false);
		jQuery('.service_price_tr').hide();
		jQuery('.service_total_price_tr').hide();
		jQuery('.order_details').addClass('hidden');
		resetExtraContent();
		resetCustomerDetails();
	}

	// Validate Order Page form
	static order_form_validation() {
		jQuery('.order_field_errortext').html('');
		jQuery('.order_field_errortext').hide();
		jQuery('.all_order_error_text').html('');

		var tel_pattern = /([0-9]{10})|(\([0-9]{3}\)\s+[0-9]{3}\-[0-9]{4})/;

		jQuery('.bm_order_field_required').each(
			function (index, element) {
				var value = jQuery(this).children('select').length != 0 ? jQuery.trim(jQuery(this).children('select').val()) : jQuery.trim(jQuery(this).children('input').val());

				if (jQuery(this).closest('table').attr('id') == 'billing_details' || jQuery(this).closest('table').attr('id') == 'shipping_details') {
					if (jQuery(this).closest('table').is(':visible')) {
						var type = jQuery(this).children('input').attr('type');

						if (type == 'email') {
							var pattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;

							if (value == "") {
								jQuery(this).children('.order_field_errortext').html(bm_error_object.required_field);
								jQuery(this).children('.order_field_errortext').show();
							} else if (!pattern.test(value)) {
								jQuery(this).children('.order_field_errortext').html(bm_error_object.invalid_email);
								jQuery(this).children('.order_field_errortext').show();
							}

						} else if (type == 'tel') {

							if (value == "") {
								jQuery(this).children('.order_field_errortext').html(bm_error_object.required_field);
								jQuery(this).children('.order_field_errortext').show();
							} else if (!tel_pattern.test(value)) {
								jQuery(this).children('.order_field_errortext').html(bm_error_object.invalid_contact);
								jQuery(this).children('.order_field_errortext').show();
							}

						} else if (value == "") {
							jQuery(this).children('.order_field_errortext').html(bm_error_object.required_field);
							jQuery(this).children('.order_field_errortext').show();
						}
					}
				} else {
					if (value == "") {
						jQuery(this).children('.order_field_errortext').html(bm_error_object.required_field);
						jQuery(this).children('.order_field_errortext').show();
					}
				}
			}
		);

		if (jQuery(document).find('#billing_contact').val() == '') {
			jQuery('td.order_billing_tel_input').find('.order_field_errortext').html(bm_error_object.required_field);
			jQuery('td.order_billing_tel_input').find('.order_field_errortext').show();
		} else if (!tel_pattern.test(jQuery(document).find('#billing_contact').val())) {
			jQuery('td.order_billing_tel_input').find('.order_field_errortext').html(bm_error_object.invalid_contact);
			jQuery('td.order_billing_tel_input').find('.order_field_errortext').show();
		}

		if (jQuery(document).find('#shipping_contact').val() == '') {
			jQuery('td.order_shipping_tel_input').find('.order_field_errortext').html(bm_error_object.required_field);
			jQuery('td.order_shipping_tel_input').find('.order_field_errortext').show();
		} else if (!tel_pattern.test(jQuery(document).find('#billing_contact').val())) {
			jQuery('td.order_shipping_tel_input').find('.order_field_errortext').html(bm_error_object.invalid_contact);
			jQuery('td.order_shipping_tel_input').find('.order_field_errortext').show();
		}

		var b = '';
		b = jQuery('.order_field_errortext').each(
			function () {
				var a = jQuery(this).html();
				b = a + b;
				jQuery('.all_order_error_text').html(b);
			}
		);

		var error = jQuery('.all_order_error_text').html();

		if (error == '') {
			return true;
		} else {
			return false;
		}

	}

	// Event handler for checking additional number of persons for a service order
	static check_for_more_persons($this) {
		if (jQuery($this).is(':checked')) {
			jQuery('.add_more_person_section').show();
			jQuery('#add_more_persons').prop('disabled', false);
		} else {
			jQuery('.add_more_person_section').hide();
			jQuery('#add_more_persons').prop('disabled', true);
		}
	}

	//International tel input for phone form fields for backend order
	static setIntlInputForBackendOrder() {
		jQuery('#order_form :input').map(function () {
			var type = jQuery(this).prop("type");
			var id = jQuery(this).attr("id");

			if ((type == "tel")) {
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

	// Change backend order status
	static bm_change_order_status_to_complete_or_cancelled($this) {

		if (jQuery($this).val() == 'completed' || jQuery($this).val() == 'cancelled') {
			if (confirm(bm_normal_object.sure_complete_order)) {

				var post = {
					'status': jQuery($this).val(),
					'id': jQuery($this).attr('id'),
				}

				var data = { 'post': post, 'nonce': bm_ajax_object.nonce };

				bmRestRequest('bm_change_order_status_to_complete_or_cancelled', data, function (response) {
					var jsondata = bmSafeParse(response);
					if (jsondata.status == true) {
						location.reload();
					} else {
						alert(bm_error_object.service_error);
					}
				});
			} else {
				jQuery($this).val('on_hold');
			}
		}
	}

	// Change frontend order status
	static bm_change_order_status($this) {
		if (confirm(bm_normal_object.sure_change_status)) {

			var post = {
				'status': jQuery($this).val(),
				'id': jQuery($this).attr('id'),
			}

			var data = { 'post': post, 'nonce': bm_ajax_object.nonce };

			bmRestRequest('bm_change_order_status', data, function (response) {
				var jsondata = bmSafeParse(response);
				if (jsondata.status == true) {
					location.reload();
				} else {
					alert(bm_error_object.service_error);
				}
			});
		}
	}

	// Search order data
	static bm_search_order_data(type = '') {
		var urlParams = new URLSearchParams(window.location.search);
	    var orderby = urlParams.get('orderby') || 'id';
	    var order = urlParams.get('order') || 'desc';

		var post = {
	        'pagenum': jQuery.trim(jQuery('#pagenum').val()),
	        'base': jQuery(location).attr("href"),
	        'limit': jQuery.trim(jQuery('#limit_count').val()),
	        'service_from': jQuery('#service_from').val(),
	        'service_to': jQuery('#service_to').val(),
	        'order_from': jQuery('#order_from').val(),
	        'order_to': jQuery('#order_to').val(),
	        'search_string': jQuery.trim(jQuery('#global_search').val()),
	        'order_source': jQuery('#order_source_filter').val(),
	        'order_status': jQuery('#order_status_filter').val() || [],
	    	'payment_status': jQuery('#payment_status_filter').val() || [],
			'services': jQuery('#service_filter').val() || [],
	    	'categories': jQuery('#category_filter').val() || [],
	        'type': type,
	        'orderby': orderby,
	        'order': order,
	    }

		var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
		bmRestRequest('bm_fetch_order_as_per_search', data, function (response) {
			var jsondata = bmSafeParse(response);
			if (jsondata.status == true) {
				jQuery(".order_records").html('');
				jQuery("#order_pagination").html('');
				var currency_symbol = bm_normal_object.currency_symbol;
				var currency_position = bm_normal_object.currency_position;
				var num_of_pages = jsondata.num_of_pages ? jsondata.num_of_pages : 0;
				jQuery(document).find("#total_pages").val(num_of_pages);

				if (typeof (jsondata.bookings) != "undefined" && typeof (jsondata.active_columns) != "undefined" && typeof (jsondata.column_values) != "undefined" && typeof (jsondata.order_statuses) != "undefined" && typeof (jsondata.current_pagenumber) != "undefined" && typeof (jsondata.pagination) != "undefined" && typeof (jsondata.saved_search) != "undefined") {
					var bookings = jsondata.bookings;
					var status_keys = jQuery.map(jsondata.order_statuses, function (value, key) {
						return key;
					});
					var status_values = jQuery.map(jsondata.order_statuses, function (value, key) {
						return value;
					});
					var active_columns = jQuery.map(jsondata.active_columns, function (value, key) {
						return value;
					});
					var column_value_keys = jQuery.map(jsondata.column_values, function (value, key) {
						return key;
					});
					var column_values = jQuery.map(jsondata.column_values, function (value, key) {
						return value;
					});
					var pagination = jsondata.pagination;
					var saved_search = jsondata.saved_search;

					if (saved_search != '' && saved_search != null) {
						jQuery('#global_search').val(typeof(saved_search.global_search) != "undefined" ? saved_search.global_search : '');
						jQuery('#service_from').val(typeof(saved_search.service_from) != "undefined" ? saved_search.service_from : '');
						jQuery('#service_to').val(typeof(saved_search.service_to) != "undefined" ? saved_search.service_to : '');
						jQuery('#order_from').val(typeof(saved_search.order_from) != "undefined" ? saved_search.order_from : '');
						jQuery('#order_to').val(typeof(saved_search.order_to) != "undefined" ? saved_search.order_to : '');
						if (typeof(saved_search.order_source) != "undefined" && saved_search.order_source != '') {
							jQuery('#order_source_filter').val(saved_search.order_source).trigger('change');
						}
						if (typeof(saved_search.order_status) != "undefined" && saved_search.order_status != '') {
							var orderStatusArray = saved_search.order_status.split(',');
							jQuery('#order_status_filter').val(orderStatusArray);
							jQuery('#order_status_filter').multiselect('reload');
						}
						if (typeof(saved_search.payment_status) != "undefined" && saved_search.payment_status != '') {
							var paymentStatusArray = saved_search.payment_status.split(',');
							jQuery('#payment_status_filter').val(paymentStatusArray);
							jQuery('#payment_status_filter').multiselect('reload');
						}
						if (typeof(saved_search.services) != "undefined" && saved_search.services != '') {
							var servicesArray = saved_search.services.split(',');
							jQuery('#service_filter').val(servicesArray);
							jQuery('#service_filter').multiselect('reload');
						}
						if (typeof(saved_search.categories) != "undefined" && saved_search.categories != '') {
							var categoriesArray = saved_search.categories.split(',');
							jQuery('#category_filter').val(categoriesArray);
							jQuery('#category_filter').multiselect('reload');
						}

						if (saved_search.service_from != '' || saved_search.service_to != '' || saved_search.order_from != '' || saved_search.order_to != '' || 
							saved_search.order_source != '' || saved_search.order_status != '' || saved_search.payment_status != '' || saved_search.services != '' || saved_search.categories != '') {
							jQuery("#order_advanced_search_box").slideDown("slow");
						}
					}

					var orderListing = '';
					var current_pagenumber = jsondata.current_pagenumber;

					if (bookings != null && bookings.length != 0) {
						for (var i = 0; i < bookings.length; i++) {
							orderListing += "<tr><form role='form' method='post'>";
							for (var j = 0; j < column_values.length; j++) {
								if (active_columns != null && jQuery.inArray(column_value_keys[j], active_columns) == -1) {
									continue;
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'serial_no') {
									orderListing += "<td style='text-align: center;'>" + (current_pagenumber ? current_pagenumber : i + 1) + "</td>";
								}
								if (
									typeof column_values[j].column !== "undefined" &&
									column_values[j].column === 'service_name'
								) {
									orderListing +=
										"<td style='text-align:center;width:140px;' title='" + bookings[i].service_name + "'>" +
											"<a href='" + bm_normal_object.admin_side_link +
											"page=bm_single_order&booking_id=" + bookings[i].id + "'>" +
												bookings[i].service_name +
											"</a>" +
										"</td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'booking_created_at') {
									orderListing += "<td style='text-align: center;'>" + bookings[i].booking_created_at + " </td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'booking_date') {
									orderListing += "<td style='text-align: center;'>" + bookings[i].booking_date + " </td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'first_name') {
									orderListing += "<td style='text-align: center;'>" + bookings[i].first_name + " </td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'last_name') {
									orderListing += "<td style='text-align: center;'>" + bookings[i].last_name + " </td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'contact_no') {
									orderListing += "<td style='text-align: center;'>" + bookings[i].contact_no + " </td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'email_address') {
									orderListing += "<td style='text-align: center;'>" + bookings[i].email_address + " </td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'service_participants') {
									orderListing += "<td style='text-align: center;'>" + bookings[i].service_participants + " </td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'extra_service_participants') {
									orderListing += "<td style='text-align: center;'>" + bookings[i].extra_service_participants + " </td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'service_cost') {
									orderListing += "<td style='text-align: center;'>";
									if (currency_position == 'before') {
										orderListing += currency_symbol + changePriceFormat(bookings[i].service_cost);
									} else {
										orderListing += changePriceFormat(bookings[i].service_cost) + currency_symbol;
									}
									orderListing += "</td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'extra_service_cost') {
									orderListing += "<td style='text-align: center;'>";
									if (currency_position == 'before') {
										orderListing += currency_symbol + changePriceFormat(bookings[i].extra_service_cost);
									} else {
										orderListing += changePriceFormat(bookings[i].extra_service_cost) + currency_symbol;
									}
									orderListing += "</td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'discount') {
									orderListing += "<td style='text-align: center;'>";
									if (currency_position == 'before') {
										orderListing += currency_symbol + changePriceFormat(bookings[i].discount);
									} else {
										orderListing += changePriceFormat(bookings[i].discount) + currency_symbol;
									}
									orderListing += "</td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'total_cost') {
									orderListing += "<td style='text-align: center;'>";
									if (currency_position == 'before') {
										orderListing += currency_symbol + changePriceFormat(bookings[i].total_cost);
									} else {
										orderListing += changePriceFormat(bookings[i].total_cost) + currency_symbol;
									}
									var payment_info = bookings[i].payment_status + '' + ( bookings[i].updated_paid_at != '' ? convertDateFormat(bookings[i].updated_paid_at, 'atTimeOnDate') : convertDateFormat(bookings[i].paid_at, 'atTimeOnDate') );
									orderListing += "&nbsp;&nbsp;<i class='fa fa-info-circle' aria-hidden='true' title='" + payment_info + "' style='cursor:pointer;'></i>";
									orderListing += "</td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'customer_data') {
									orderListing += "<td style='text-align: center;'><div class='show-customer-dialog linkText' style='cursor:pointer;font-size:16px;' id=" + bookings[i].id + "><i class='fa fa-file' aria-hidden='true'></i></div></td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'ordered_from') {
									orderListing += "<td style='text-align: center;'>" + (bookings[i].is_frontend_booking == 0 ? bm_normal_object.backend : bm_normal_object.frontend) + " </td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'order_status') {
									orderListing += "<td style='text-align: center;'>";
									orderListing += status_values[jQuery.inArray(bookings[i].order_status, status_keys)];
									orderListing += "</td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'payment_status') {
									orderListing += "<td style='text-align: center;'>" + bookings[i].payment_status + " </td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'order_attachments') {
									orderListing += "<td style='text-align: center;'><div class='show-order-attachments' style='cursor:pointer;font-size:16px;' id=" + bookings[i].id + "><i class='fa fa-paperclip' aria-hidden='true'></i></div></td>";
								}
								if (typeof (column_values[j].column) != "undefined" && column_values[j].column == 'actions') {
									orderListing += "<td style='text-align: center;width:84px;'>";

									orderListing += "<button type='button' name='edittransaction' id='" + (bookings[i].is_frontend_booking == 0 ? 'edittransaction' : '') + "' title=" + (bookings[i].is_frontend_booking == 1 ? bm_error_object.transaction_not_editable : bm_normal_object.edit_transaction) + " value=" + bookings[i].id + " onclick='bm_update_transaction(this)' " + (bookings[i].is_frontend_booking == 1 ? 'disabled' : '') + "><i class='fa fa-exchange' aria-hidden='true' style='cursor:pointer;'></i></button>&nbsp;&nbsp;";
									// orderListing += "<button type='button' name='editorder' id='editorder' title="+bm_normal_object.edit+" value="+bookings[i].id+"><i class='fa fa-edit' aria-hidden='true' style='cursor:pointer;'></i></button>";
									orderListing += "<button type='button' name='archiveorder' id='archiveorder' title="+bm_normal_object.archive+" value="+bookings[i].id+"><i class='fa fa-archive' aria-hidden='true' style='color:red;cursor:pointer;'></i></button>";
									orderListing += "</td>";
								}
							}
							orderListing += "</form></tr>";
							current_pagenumber++;
						}
						jQuery(".order_records").append(orderListing);
						jQuery("#order_pagination").append(pagination);

						let prefix = bm_normal_object.total + " = ";

						var totalsRow = "<tr class='totals-row' style='font-weight:bold; background:#f9f9f9;'>";
						for (var j = 0; j < column_values.length; j++) {
							if (active_columns != null && jQuery.inArray(column_value_keys[j], active_columns) == -1) {
								continue;
							}

							if (typeof(column_values[j].column) != "undefined") {
								let col = column_values[j].column;
								if (col == 'service_participants') {
									totalsRow += "<td style='text-align: center;'>" + prefix + jsondata.svc_prtcpants + "</td>";
								} else if (col == 'extra_service_participants') {
									totalsRow += "<td style='text-align: center;'>" + prefix + jsondata.ex_svc_prtcpants + "</td>";
								} else if (col == 'service_cost') {
									totalsRow += "<td style='text-align: center;'>" + prefix + jsondata.svc_cost_sum + "</td>";
								} else if (col == 'extra_service_cost') {
									totalsRow += "<td style='text-align: center;'>" + prefix + jsondata.ex_svc_cost_sum + "</td>";
								} else if (col == 'discount') {
									totalsRow += "<td style='text-align: center;'>" + prefix + jsondata.discount_sum + "</td>";
								} else if (col == 'total_cost') {
									totalsRow += "<td style='text-align: center;'>" + prefix + jsondata.total_cost_sum + "</td>";
								} else {
									totalsRow += "<td></td>";
								}
							}
						}
						totalsRow += "</tr>";
						jQuery(".order_records").append(totalsRow);
					} else {
						jQuery(".order_records").append('<div class="no_records_class">' + bm_normal_object.no_records + '</div>');
					}
				}
			} else {
				alert(bm_error_object.server_error);
			}
		});
	}

	// Sort results
	static bm_sort_orders(column, direction) {
	    var url = new URL(window.location.href);
	    url.searchParams.set('orderby', column);
	    url.searchParams.set('order', direction);
	    url.searchParams.set('pagenum', 1);
	    window.location.href = url.toString();
		bm_search_order_data('save_search');
	}

	// Show/hide respective orders
	static bm_show_hide_respective_orders($this) {
		jQuery('.status_search_span').show();
		jQuery('.payment_status_search_span').show();
		jQuery('.service_search_span').show();
		jQuery('.category_search_span').show();
		bm_search_order_data('save_search');
	}

	// Export table content
	static bm_export_to_csv_old(tableId, filename, excludedColumns = [], includeColumnNames = true) {
		var table = document.getElementById(tableId);
		var rows = Array.from(table.querySelectorAll('tr'));

		var columnNames = Array.from(table.querySelectorAll('th')).map(th => th.innerText);
		var excludedIndices = excludedColumns.map(col => columnNames.indexOf(col));
		columnNames = columnNames.filter(column => !excludedColumns.includes(column))

		var data = rows.map(row => {
			var cells = Array.from(row.querySelectorAll('td'));
			return cells.map((cell, index) => {
				var select = cell.querySelector('select');
				var cellValue = select ? select.options[select.selectedIndex].text : cell.innerText;
				return cellValue.trim();
			}).filter((_, index) => !excludedIndices.includes(index));
		});

		if (includeColumnNames) {
			data.unshift(columnNames);
		}

		var filteredData = data.filter(row => row.some(cell => cell.trim() !== ''));

		var csvContent = '\uFEFF';
		csvContent += filteredData.map(row => row.map(encodeValue).join(',')).join('\n');

		var csvData = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });

		var link = document.createElement('a');
		link.setAttribute('href', URL.createObjectURL(csvData));
		link.setAttribute('download', filename);

		link.click();
	}

	// Fetch Export Data
	static fetchAndExportData(moduleType, type, startPage = 0, endPage = 0) {
	    const urlParams = new URLSearchParams(window.location.search);
	    const orderby = urlParams.get('orderby') || 'id';
	    const order = urlParams.get('order') || 'desc';

	    const post = {
	        pagenum: jQuery.trim(jQuery('#pagenum').val()),
	        limit: jQuery.trim(jQuery('#limit_count').val()),
	        total_pages: jQuery.trim(jQuery('#total_pages').val()),
	        service_from: moduleType === 'orders' ? jQuery('#service_from').val() : jQuery('#checkin_service_from').val(),
	        service_to: moduleType === 'orders' ? jQuery('#service_to').val() : jQuery('#checkin_service_to').val(),
	        order_from: moduleType === 'orders' ? jQuery('#order_from').val() : jQuery('#checkin_from').val(),
	        order_to: moduleType === 'orders' ? jQuery('#order_to').val() : jQuery('#checkin_to').val(),
	        search_string: moduleType === 'orders' ? jQuery.trim(jQuery('#global_search').val()) : jQuery.trim(jQuery('#checkin_global_search').val()),
	        order_source: moduleType === 'orders' ? jQuery('#order_source_filter').val() : null,
	        order_status: moduleType === 'orders' ? jQuery('#order_status_filter').val() : null,
	        payment_status: moduleType === 'orders' ? jQuery('#payment_status_filter').val() : null,
			services_filter: moduleType === 'orders' ? jQuery('#service_filter').val() : null,
	        categories_filter: moduleType === 'orders' ? jQuery('#category_filter').val() : null,
			services: moduleType === 'orders' ? null : jQuery('#checkin_service_advanced_filter').val(),
	        type: type,
	        start_page: startPage,
	        end_page: endPage,
	        order_column: orderby,
	        order_dir: order
	    };

	    const ajaxAction = moduleType === 'orders' ? 'bm_fetch_export_order_records' : 'bm_fetch_export_checkin_records';
	    const filename = moduleType === 'orders' ? 'orders.csv' : 'checkins.csv';

	    const data = {
	        post: post,
	        nonce: bm_ajax_object.nonce
	    };

	    bmRestRequest(ajaxAction, data, function(response) {
	        jQuery('#order_export_modal, #checkin_export_modal').removeClass('active-modal');
			var response = bmSafeParse(response);

	        const status = response.status || false;
	        const orders = response.orders || [];
	        const headers = response.headers || [];
	        const keys = response.keys || [];

	        if (status && orders.length > 0 && headers.length > 0 && keys.length > 0 && headers.length === keys.length) {
	            exportToCSV(orders, headers, keys, filename);
	        } else {
	            showMessage(bm_error_object.server_error || bm_error_object.failed_export, 'error');
	        }
	    }).fail(function(jqXHR, textStatus, errorThrown) {
	        showMessage(bm_error_object.server_error);
	    });
	}

	// Export to csv
	static exportToCSV(data, headers, headerToKey, filename) {
		var csvContent = '\uFEFF';

		// Add column headers to CSV content
		csvContent += headers.map(encodeValue1).join(',') + '\n';

		// Add data rows to CSV content
		data.forEach(row => {
			let rowArray = headers.map((header, index) => {
				let key = headerToKey[index];
				let value = key && row[key] !== undefined ? row[key] : '';
				return encodeValue1(value);
			});
			csvContent += rowArray.join(',') + '\n';
		});

		var csvData = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });

		var link = document.createElement('a');
		link.setAttribute('href', URL.createObjectURL(csvData));
		link.setAttribute('download', filename);

		link.click();
	}

	// Global search order listing
	static bm_global_search_order_data(value) {
		var value = jQuery.trim(value.toLowerCase());
		var currentPage = jQuery.trim(jQuery('#pagenum').val());
		var baseURL = jQuery(location).attr("href");
		var rowsPerPage = jQuery.trim(jQuery('#limit_count').val());
		var startRow = (currentPage - 1) * rowsPerPage;
		var endRow = startRow + rowsPerPage - 1;
		var total = 0;

		jQuery("#dashboard_all_orders tbody tr").each(function (index) {
			var isVisible = index >= startRow && index <= endRow;
			jQuery(this).toggle(isVisible && jQuery(this).text().toLowerCase().indexOf(value) > -1);
			if (isVisible && jQuery(this).text().toLowerCase().indexOf(value) > -1) total++;
		});

		var totalPages = Math.ceil(total / rowsPerPage);

		var pagination = generatePagination(currentPage, baseURL, totalPages);
		jQuery("#dashboard_all_orders_pagination").html('');
		jQuery("#dashboard_all_orders_pagination").html(pagination);
	}

	// Update order transaction data
	static bm_update_transaction($this) {
		jQuery(document).find('.edit_transactions_errortext').html('');
		jQuery(document).find('.edit_transactions_errortext').hide();
		jQuery(document).find('#save_trans_button').prop('disabled', false);
		jQuery('#save_trans_button').show();
		jQuery('#resendProcess').hide();
		var id = jQuery($this).val();

		var post = {
			'id': id,
		}

		var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
		bmRestRequest('bm_update_transaction', data, function (response) {
			jQuery('#edit_transaction').html('');
			var jsondata = bmSafeParse(response);
			var status = jsondata.status ? jsondata.status : '';
			var is_active = jsondata.is_active ? jsondata.is_active : 0;
			var html = jsondata.html ? jsondata.html : '';

			if (is_active == 0 || is_active == 2) {
				jQuery(document).find('#save_trans_button').prop('disabled', true);
				jQuery(document).find('.edit_transactions_errortext').html(bm_error_object.transaction_not_editable);
				jQuery(document).find('.edit_transactions_errortext').show();
			}

			if (status == true) {
				jQuery('#edit_transaction').html(html);
				jQuery('#edit_transactions_modal').addClass('active-modal');
			} else if (status == false) {
				jQuery('#edit_transaction').html(bm_error_object.server_error);
				jQuery('#edit_transactions_modal').addClass('active-modal');
			}
		});
	}

	// Update order transaction
	static bm_save_order_transaction() {
		jQuery(document).find('.edit_transactions_errortext').html('');
		jQuery(document).find('.edit_transactions_errortext').hide();
		jQuery(document).find('#refund_id').attr('placeholder', '');
		jQuery(document).find('#refund_id').removeClass('red');
		var is_active = jQuery('#is_active').val();

		var post = {
			'id': jQuery('#booking_id').val(),
			// 'paid_amount': jQuery('#paid_amount').val(),
			// 'paid_amount_currency': jQuery('#paid_amount_currency').val(),
			'transaction_id': jQuery('#transaction_id').length > 0 ? jQuery('#transaction_id').val() : '',
			// 'payment_method': jQuery('#payment_method').val(),
			'payment_status': jQuery('#payment_status').val(),
			'refund_id': jQuery('#refund_id').val(),
			'is_active': is_active,
		}

		if (is_active == 0) {
			jQuery(document).find('.edit_transactions_errortext').html(bm_error_object.transaction_not_editable);
			jQuery(document).find('.edit_transactions_errortext').show();
		} else if (jQuery('#refund_id_input').is(':visible') && jQuery(document).find('#refund_id').val() == '') {
			jQuery(document).find('#refund_id').attr('placeholder', bm_error_object.required_field);
			jQuery(document).find('#refund_id').addClass('red');
		} else if (confirm(bm_normal_object.sure_save_transaction)) {
			jQuery('#save_trans_button').hide();
			jQuery('#resendProcess').show();
			var data = { 'post': post, 'nonce': bm_ajax_object.nonce };
			bmRestRequest('bm_save_order_transaction', data, function (status) {
				jQuery('#edit_transactions_modal').removeClass('active-modal');
				if (status == 1) {
					showMessage(bm_success_object.transaction_updated, 'success');
					location.reload();
				} else if (status == 2) {
					showMessage(bm_error_object.wrong_transaction_id, 'error');
				} else if (status == 3) {
					showMessage(bm_error_object.transaction_id_not_required, 'error');
				} else if (status == 4) {
					showMessage(bm_error_object.wrong_refund_id, 'error');
				} else if (status == 5) {
					showMessage(bm_error_object.transaction_changes_revert, 'error');
				} else if (status == 6) {
					showMessage(bm_error_object.transaction_id_exists, 'error');
				} else if (status == 0) {
					showMessage(bm_error_object.server_error, 'error');
				} else {
					showMessage(bm_error_object.server_error, 'error');
				}
			});
		}
	}

	// Check payment status
	static check_payment_status($this) {
		var payment_status = jQuery($this).val();

		jQuery('#is_active').addClass('readonly_checkbox');
		jQuery('#is_active').parent().addClass('readonly_cursor');

		if (payment_status == 'refunded') {
			jQuery(document).find('#refund_id_input').removeClass('hidden');
		} else {
			jQuery(document).find('#refund_id_input').addClass('hidden');
		}

		if (payment_status == 'pending' || payment_status == 'succeeded' || payment_status == 'free') {
			jQuery('#is_active').removeClass('readonly_checkbox');
			jQuery('#is_active').parent().removeClass('readonly_cursor');
		}
	}

	// Change voucher status
	static bm_change_voucher_status($this) {
		let inputStatus = jQuery($this).is(':checked') ? 1 : 0;

		if (confirm(bm_normal_object.change_voucher_vsiblity)) {
			const post = {
				'code': $this.id.split('_')[3],
				'status': inputStatus,
			}

			const data = {
				post,
				nonce: bm_ajax_object.nonce
			};

			jQuery('.loader_modal').show();

			bmRestRequest('bm_change_voucher_status', data)
				.done(function (response) {
					if (response.success) {
						showMessage(bm_success_object.status_successfully_changed, 'success');
						location.reload();
					} else {
						inputStatus == 1 ? jQuery('#' + $this.id).prop('checked', false) : jQuery('#' + $this.id).prop('checked', true);
						showMessage(response.data ? response.data : bm_error_object.server_error, 'error');
					}
				})
				.fail(function (jqXHR, textStatus, errorThrown) {
					showMessage(bm_error_object.server_error, 'error');
				})
				.always(function () {
					jQuery('.loader_modal').hide();
				});
		} else {
			inputStatus == 1 ? jQuery('#' + $this.id).prop('checked', false) : jQuery('#' + $this.id).prop('checked', true);
		}
	}
}

// Attach to namespace
window.BMAdmin = window.BMAdmin || {};
window.BMAdmin.OrderManager = BMOrderManager;

// Global aliases (for onclick= handlers in HTML)
window.resetOrderPage = BMOrderManager.resetOrderPage;
window.resetOrderPageServicePrice = BMOrderManager.resetOrderPageServicePrice;
window.order_form_validation = BMOrderManager.order_form_validation;
window.check_for_more_persons = BMOrderManager.check_for_more_persons;
window.setIntlInputForBackendOrder = BMOrderManager.setIntlInputForBackendOrder;
window.bm_change_order_status_to_complete_or_cancelled = BMOrderManager.bm_change_order_status_to_complete_or_cancelled;
window.bm_change_order_status = BMOrderManager.bm_change_order_status;
window.bm_search_order_data = BMOrderManager.bm_search_order_data;
window.bm_sort_orders = BMOrderManager.bm_sort_orders;
window.bm_show_hide_respective_orders = BMOrderManager.bm_show_hide_respective_orders;
window.bm_export_to_csv_old = BMOrderManager.bm_export_to_csv_old;
window.fetchAndExportData = BMOrderManager.fetchAndExportData;
window.exportToCSV = BMOrderManager.exportToCSV;
window.bm_global_search_order_data = BMOrderManager.bm_global_search_order_data;
window.bm_update_transaction = BMOrderManager.bm_update_transaction;
window.bm_save_order_transaction = BMOrderManager.bm_save_order_transaction;
window.check_payment_status = BMOrderManager.check_payment_status;
window.bm_change_voucher_status = BMOrderManager.bm_change_voucher_status;
