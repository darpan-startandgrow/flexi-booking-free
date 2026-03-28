/**
 * SG Flexi Booking — Dashboard JS (Free Version).
 *
 * Handles KPI count fetching, booking-status chart, and tab switching.
 *
 * @since 1.3.0
 */

/* global jQuery, bm_ajax_object, bm_normal_object, bm_error_object, Chart */

// ---------- REST helper ----------
/**
 * Helper: make a REST API request.
 *
 * @param {string} endpoint  REST path relative to sg-booking/v1/ (e.g. 'dashboard/counts').
 * @param {string} method    HTTP method (GET, POST, etc.).
 * @param {object} data      Request body / query params.
 * @return {jQuery.jqXHR}
 */
function bmDashRestRequest(endpoint, method, data) {
	var url = bm_ajax_object.rest_url + endpoint;

	var settings = {
		url: url,
		method: method,
		dataType: 'json',
		beforeSend: function (xhr) {
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

// ---------- Tab switching ----------
jQuery(document).ready(function ($) {
	$('.bm-tab-btn').on('click', function () {
		var target = $(this).data('tab');
		$('.bm-tab-btn').removeClass('bm-tab-active');
		$(this).addClass('bm-tab-active');
		$('.bm-tab-panel').removeClass('bm-tab-panel-active');
		$('#' + target).addClass('bm-tab-panel-active');
	});

	// Initial KPI load.
	bm_fetch_booking_counts(null);

	// Initial status chart load.
	bm_load_status_chart();

	// Chart filter button.
	$('#bm_status_chart_filter').on('click', function () {
		bm_load_status_chart();
	});
});

// ---------- KPI Counts ----------
function bm_fetch_booking_counts($this) {
	var currency_symbol = (typeof bm_normal_object !== 'undefined') ? bm_normal_object.currency_symbol : '';
	var currency_position = (typeof bm_normal_object !== 'undefined') ? bm_normal_object.currency_position : 'before';
	var year_value = null;
	var month_value = null;
	var type = '';
	var status = 'booked';

	if ($this !== null) {
		type = jQuery($this).data('type');
		year_value = jQuery('.' + type + '_year_analytics').length ? jQuery('.' + type + '_year_analytics').val() : year_value;
		month_value = jQuery('.' + type + '_month_analytics').length ? jQuery('.' + type + '_month_analytics').val() : month_value;
		status = jQuery('#' + type + '_search_status').length ? jQuery('#' + type + '_search_status').val() : jQuery($this).data('status');
	}

	var data = {
		'year': year_value,
		'month': month_value,
		'type': type,
		'status': status,
	};

	bmDashRestRequest('dashboard/counts', 'GET', data).done(function (jsondata) {
		if (typeof jsondata.booking_type !== 'undefined') {
			if (jsondata.booking_type === '') {
				jQuery('.total_bookings_count').text(jsondata.total_bookings_count ? jsondata.total_bookings_count : '0');
				jQuery('.upcoming_bookings_count').text(jsondata.upcoming_bookings_count ? jsondata.upcoming_bookings_count : '0');
				jQuery('.weekly_bookings_count').text(jsondata.weekly_bookings_count ? jsondata.weekly_bookings_count : '0');
				var rev = jsondata.total_bookings_revenue ? changePriceFormat(jsondata.total_bookings_revenue) : '0';
				if (currency_position === 'before') {
					jQuery('.total_bookings_revenue').text(currency_symbol + rev);
				} else {
					jQuery('.total_bookings_revenue').text(rev + currency_symbol);
				}
			} else if (jsondata.booking_type === 'total') {
				jQuery('.total_bookings_count').text(jsondata.total_bookings_count ? jsondata.total_bookings_count : '0');
			} else if (jsondata.booking_type === 'upcoming') {
				jQuery('.upcoming_bookings_count').text(jsondata.upcoming_bookings_count ? jsondata.upcoming_bookings_count : '0');
			} else if (jsondata.booking_type === 'revenue') {
				var revVal = jsondata.total_bookings_revenue ? changePriceFormat(jsondata.total_bookings_revenue) : '0';
				if (currency_position === 'before') {
					jQuery('.total_bookings_revenue').text(currency_symbol + revVal);
				} else {
					jQuery('.total_bookings_revenue').text(revVal + currency_symbol);
				}
			} else if (jsondata.booking_type === 'weekly') {
				jQuery('.weekly_bookings_count').text(jsondata.weekly_bookings_count ? jsondata.weekly_bookings_count : '0');
			}
		}
	});
}

// ---------- Legend dot click handler ----------
jQuery(document).on('click', 'span.get_booking-info', function (e) {
	e.preventDefault();
	var type = jQuery(this).data('type');
	var status = jQuery(this).data('status');
	jQuery('#' + type + '_search_status').val(status);
	jQuery(this).closest('.bm-kpi-legend').find('span.get_booking-info').removeClass('bluedot').addClass('greydot');
	jQuery(this).removeClass('greydot').addClass('bluedot');
	bm_fetch_booking_counts(this);
});

// ---------- Booking Status Chart ----------
var bmDashboardStatusChart = null;

function bm_load_status_chart() {
	var from = jQuery('#bm_status_chart_from').val() || '';
	var to = jQuery('#bm_status_chart_to').val() || '';

	var data = {
		'from': from ? bm_format_date_dmy(from) : '',
		'to': to ? bm_format_date_dmy(to) : '',
	};

	bmDashRestRequest('dashboard/status-chart', 'GET', data).done(function (jsondata) {
		bm_render_status_chart(jsondata.labels || [], jsondata.data || []);
	}).fail(function () {
		bm_render_status_chart([], []);
	});
}

function bm_format_date_dmy(dateStr) {
	// Convert YYYY-MM-DD to DD/MM/YY
	var parts = dateStr.split('-');
	if (parts.length === 3) {
		return parts[2] + '/' + parts[1] + '/' + parts[0].slice(2);
	}
	return dateStr;
}

function bm_render_status_chart(labels, chartData) {
	var canvas = document.getElementById('bm_status_chart');
	if (!canvas) return;
	var ctx = canvas.getContext('2d');

	if (bmDashboardStatusChart) {
		bmDashboardStatusChart.destroy();
	}

	var bookingsLabel = (typeof bm_normal_object !== 'undefined' && bm_normal_object.bookings) ? bm_normal_object.bookings : 'Bookings';
	var noDataLabel = (typeof bm_normal_object !== 'undefined' && bm_normal_object.no_data_to_show) ? bm_normal_object.no_data_to_show : 'No data to show';

	bmDashboardStatusChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: labels,
			datasets: [{
				label: bookingsLabel,
				data: chartData,
				fill: true,
				borderRadius: 6,
				borderColor: '#818cf8',
				backgroundColor: '#818cf8',
				barPercentage: 0.5,
				datalabels: { display: false },
			}],
		},
		options: {
			scales: {
				x: {
					grid: { display: false },
					ticks: { color: '#64748b', font: { size: 11 } }
				},
				y: {
					suggestedMin: 0,
					ticks: { precision: 0, color: '#64748b', font: { size: 11 } },
					grid: { color: '#f1f5f9' }
				}
			},
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: {
					display: true,
					labels: { color: '#1e293b', font: { size: 12 } }
				},
				tooltip: {
					enabled: true,
					callbacks: {
						label: function (tooltipItem) {
							return bookingsLabel + ': ' + tooltipItem.raw;
						}
					}
				}
			}
		},
		plugins: [{
			id: 'noDataMessage',
			afterDraw: function (chart) {
				var data = chart.data.datasets[0].data;
				if (!data.length || data.every(function (v) { return v === 0; })) {
					var ctxDraw = chart.ctx;
					var w = chart.width;
					var h = chart.height;
					ctxDraw.save();
					ctxDraw.font = "600 16px -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
					ctxDraw.fillStyle = '#94a3b8';
					ctxDraw.textAlign = 'center';
					ctxDraw.textBaseline = 'middle';
					ctxDraw.fillText(noDataLabel, w / 2, h / 2);
					ctxDraw.restore();
				}
			}
		}]
	});
}