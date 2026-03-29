/**
 * BMUtils - Common utility methods for admin area.
 * @since 1.1.0
 */
class BMUtils {
// Get Url Param
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

static padWithZeros(number) {
var lengthOfNumber = (parseInt(number) + '').length;
if (lengthOfNumber == 2) return number;
else if (lengthOfNumber == 1) return '0' + number;
else if (lengthOfNumber == 0) return '00';
else return false;
}

static strToMins(t) {
var s = t.split(":");
return Number(s[0]) * 60 + Number(s[1]);
}

static minsToStr(t) {
return BMUtils.padWithZeros(Math.trunc(t / 60)) + ':' + BMUtils.padWithZeros(('00' + t % 60)).slice(-2);
}

static timeStringToFloat(time) {
var hoursMinutes = time.split(/[.:]/);
var hours = parseInt(hoursMinutes[0], 10);
var minutes = hoursMinutes[1] ? parseInt(hoursMinutes[1], 10) : 0;
return hours + minutes / 60;
}

// Convert Number to Time
static convertNumToTime(number) {
// Check sign of given number
var sign = (number >= 0) ? 1 : -1;

// Set positive value of number of sign negative
number = number * sign;

// Separate the int from the decimal part
var hour = Math.floor(number);
var decpart = number - hour;

var min = 1 / 60;
// Round to nearest minute
decpart = min * Math.round(decpart / min);

var minute = Math.floor(decpart * 60) + '';

// Add padding if need
if (hour.toString().length < 2) {
hour = '0' + hour;
}

// Add padding if need
if (minute.length < 2) {
minute = '0' + minute;
}

// Add Sign in final result
sign = sign == 1 ? '' : '-';

// Concate hours and minutes
var time = sign + hour + ':' + minute;

return time;
}

static isFormattedDate(date) {
var splitDate = date.split(':');
if (splitDate.length == 2 && (parseInt(splitDate[0]) + '').length <= 2 && (parseInt(splitDate[1]) + '').length <= 2) return true;
else return false;
}

// Convert one date format to another format
static convertDateFormat(date, toFormat) {
var convertedDate = new Date(date);

if (!isNaN(convertedDate.getTime())) {
var formattedDate = '';

switch (toFormat) {
case 'YYYY-MM-DD':
formattedDate = convertedDate.toISOString().split('T')[0];
break;

case 'MM/DD/YYYY':
case 'DD/MM/YYYY':
case 'DD-MM-YYYY':
case 'YYYY/MM/DD':
case 'DD/MM/YY':
var day = ("0" + convertedDate.getDate()).slice(-2);
var month = ("0" + (convertedDate.getMonth() + 1)).slice(-2);
var year = convertedDate.getFullYear();
switch (toFormat) {
case 'MM/DD/YYYY':
formattedDate = month + '/' + day + '/' + year;
break;
case 'DD/MM/YYYY':
formattedDate = day + '/' + month + '/' + year;
break;
case 'DD-MM-YYYY':
formattedDate = day + '-' + month + '-' + year;
break;
case 'YYYY/MM/DD':
formattedDate = year + '/' + month + '/' + day;
break;
case 'DD/MM/YY':
formattedDate = day + '/' + month + '/' + year.toString().substr(-2);
break;
}
break;

case 'MMMM DD, YYYY':
formattedDate = convertedDate.toLocaleDateString(undefined, {
month: 'long',
day: 'numeric',
year: 'numeric'
});
break;

case 'MMM DD, YYYY':
formattedDate = convertedDate.toLocaleDateString(undefined, {
month: 'short',
day: 'numeric',
year: 'numeric'
});
break;

case 'YYYY MMMM DD':
formattedDate = convertedDate.toLocaleDateString(undefined, {
year: 'numeric',
month: 'long',
day: 'numeric'
});
break;

case 'YYYY MMM DD':
formattedDate = convertedDate.toLocaleDateString(undefined, {
year: 'numeric',
month: 'short',
day: 'numeric'
});
break;

case 'YYYY-MM-DD HH:mm:ss':
var year = convertedDate.getFullYear();
var month = ("0" + (convertedDate.getMonth() + 1)).slice(-2);
var day = ("0" + convertedDate.getDate()).slice(-2);
var hours = ("0" + convertedDate.getHours()).slice(-2);
var minutes = ("0" + convertedDate.getMinutes()).slice(-2);
var seconds = ("0" + convertedDate.getSeconds()).slice(-2);
formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
break;

case 'atTimeOnDate':
var hours = convertedDate.getHours();
var minutes = ("0" + convertedDate.getMinutes()).slice(-2);
var period = hours >= 12 ? 'PM' : 'AM';
var hour12 = hours % 12 || 12;

var dayNum = convertedDate.getDate();
var daySuffix;
if (dayNum >= 11 && dayNum <= 13) {
daySuffix = 'th';
} else {
switch (dayNum % 10) {
case 1: daySuffix = 'st'; break;
case 2: daySuffix = 'nd'; break;
case 3: daySuffix = 'rd'; break;
default: daySuffix = 'th';
}
}

var monthName = convertedDate.toLocaleString(undefined, { month: 'long' });
var year = convertedDate.getFullYear();

formattedDate = ` at ${hour12}:${minutes} ${period} on ${dayNum}${daySuffix} ${monthName} ${year}`;
break;

default:
formattedDate = convertedDate.toLocaleDateString();
break;
}

return formattedDate;
} else {
return false;
}
}

// Convert one date format to another format
static convertDateFormat_old(date, toFormat) {
var convertedDate = new Date(date);

if (!isNaN(convertedDate.getTime())) {
var formattedDate = '';

switch (toFormat) {
case 'YYYY-MM-DD':
formattedDate = convertedDate.toISOString().split('T')[0];
break;
case 'MM/DD/YYYY':
var day = ("0" + convertedDate.getDate()).slice(-2);
var month = ("0" + (convertedDate.getMonth() + 1)).slice(-2);
formattedDate = month + '/' + day + '/' + convertedDate.getFullYear();
break;
case 'DD/MM/YYYY':
var day = ("0" + convertedDate.getDate()).slice(-2);
var month = ("0" + (convertedDate.getMonth() + 1)).slice(-2);
formattedDate = day + '/' + month + '/' + convertedDate.getFullYear();
break;
case 'DD-MM-YYYY':
var day = ("0" + convertedDate.getDate()).slice(-2);
var month = ("0" + (convertedDate.getMonth() + 1)).slice(-2);
formattedDate = day + '-' + month + '-' + convertedDate.getFullYear();
break;
case 'YYYY/MM/DD':
var day = ("0" + convertedDate.getDate()).slice(-2);
var month = ("0" + (convertedDate.getMonth() + 1)).slice(-2);
formattedDate = convertedDate.getFullYear() + '/' + month + '/' + day;
break;
case 'DD/MM/YY':
var day = ("0" + convertedDate.getDate()).slice(-2);
var month = ("0" + (convertedDate.getMonth() + 1)).slice(-2);
formattedDate = day + '/' + month + '/' + convertedDate.getFullYear().toString().substr(-2);
break;
case 'MMMM DD, YYYY':
formattedDate = convertedDate.toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' });
break;
case 'MMM DD, YYYY':
formattedDate = convertedDate.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
break;
case 'YYYY MMMM DD':
formattedDate = convertedDate.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
break;
case 'YYYY MMM DD':
formattedDate = convertedDate.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
break;
default:
formattedDate = convertedDate.toLocaleDateString();
break;
}

return formattedDate;
} else {
return false;
}
}

// Change price format
static changePriceFormat(price, customLocale = '') {
price = !isNaN(parseFloat(price)) ? parseFloat(price) : 0.00;
var formatLocale = bm_normal_object.price_format ? bm_normal_object.price_format : 'it-IT';
formatLocale = formatLocale.replace('_', '-');
var currency = bm_normal_object.currency_type ? bm_normal_object.currency_type : 'EUR';

const formattedPrice = new Intl.NumberFormat((customLocale != '' ? customLocale : formatLocale), {
// style: 'currency',
// currency: currency,
minimumFractionDigits: 2,
maximumFractionDigits: 2,
}).format(price);

return formattedPrice;
}

// Pagination
static generatePagination(pageNumber, baseUrl, totalPages) {
var pagination = jQuery("<ul class='page-numbers'></ul>");

// "Previous" link
var previousPage = pageNumber - 1;
if (previousPage > 0) {
var previousLink = jQuery("<li><a href='" + baseUrl + previousPage + "'>" + bm_normal_object.previous + "</a></li>");
pagination.append(previousLink);
}

// numeric page links
for (var i = 1; i <= totalPages; i++) {
var pageLink = jQuery("<li><a href='" + baseUrl + i + "'>" + i + "</a></li>");
if (i === pageNumber) {
pageLink.addClass("active");
}
pagination.append(pageLink);
}

// "Next" link
var nextPage = pageNumber + 1;
if (nextPage <= totalPages) {
var nextLink = jQuery("<li><a href='" + baseUrl + nextPage + "'>" + bm_normal_object.next + "</a></li>");
pagination.append(nextLink);
}

return pagination;
}

// Show module pop up message
static showMessage(message, type) {
jQuery("#popup-message").text(message ? message : bm_error_object.server_error);
if (type === "success") {
jQuery("#popup-message-container").css("background-color", "#4CAF50");
} else if (type === "error") {
jQuery("#popup-message-container").css("background-color", "#2271b1");
} else {
jQuery("#popup-message-container").css("background-color", "#2271b1");
}

jQuery("#popup-message-overlay, #popup-message-container").fadeIn();
}

// Hide module pop up message
static hideMessage() {
jQuery("#popup-message-overlay, #popup-message-container").fadeOut();
}

// Read a page's or a string's GET URL variables and return them as an associative array.
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

// Check if two arrays has a common element
static hasCommonElement(arr1, arr2) {
var hasCommon = false;
if (arr1.length > 0 && arr2.length > 0) {
jQuery.each(arr2, function (index, value) {
if (jQuery.inArray(value, arr1) != -1) {
hasCommon = true;
}
if (hasCommon) {
return false;
}
});
}

return hasCommon;
}

// Array sum
static array_sum(arr) {
return arr.reduce((a, b) => a + b, 0);
}

// Handle special characters in export
static encodeValue(value) {
return `"${value.replace(/"/g, '""')}"`;
}

// Handle special characters in export
static encodeValue1(value) {
value = String(value);
if (value.includes(',')) {
return `"${value.replace(/"/g, '""')}"`;
}
return value;
}

// Copy text to clipboard
static bm_copy_text(element) {
element.select();
element.setSelectionRange(0, 99999);
navigator.clipboard.writeText(element.value);

// Update tooltip
var tooltip = element.nextElementSibling;
if (tooltip) {
tooltip.innerHTML = bm_normal_object.copied_to_clipboard;
}
}

// Copy text to clipboard message
static bm_copy_message(element) {
var tooltip = element.nextElementSibling;
if (tooltip) {
tooltip.innerHTML = bm_normal_object.copy_to_clipboard;
}
}

// Show/hide search box
static bm_show_search_box(id) {
if (jQuery("#" + id).is(':visible')) {
jQuery("#" + id).slideUp("slow");
} else {
jQuery("#" + id).slideDown("slow");
}
}

// Add/remove search box
static bm_remove_hidden_class(id) {
if (jQuery("#" + id).hasClass('hidden')) {
jQuery("#" + id).removeClass("hidden");
} else {
jQuery("#" + id).addClass("hidden");
}
}

// Get All Dates in Range
static getDaysArray(start, end) {
for (var arr = [], dt = new Date(start); dt <= new Date(end); dt.setDate(dt.getDate() + 1)) {
arr.push(new Date(dt));
}
return arr;
}

// Show To Service Date In Bulk Price/Stopsales Change
static showToDate(type = '') {
if (type == 'price') {
var date_from = jQuery('#from_bulk_price_change');
var date_to = jQuery('#to_bulk_price_change');
} else if (type == 'stopsales') {
var date_from = jQuery('#from_bulk_stopsales_change');
var date_to = jQuery('#to_bulk_stopsales_change');
} else if (type == 'saleswitch') {
var date_from = jQuery('#from_bulk_saleswitch_change');
var date_to = jQuery('#to_bulk_saleswitch_change');
} else if (type == 'capacity') {
var date_from = jQuery('#from_bulk_cap_change');
var date_to = jQuery('#to_bulk_cap_change');
}
date_to.val('');
date_to.attr('min', date_from.val());

if (date_from.val() != '') {
if (date_to.prop('readonly')) {
date_to.prop('readonly', false);
}
} else {
date_to.prop('readonly', true);
date_to.val('');
}
}

// Close Modal
static closeModal(id) {
// jQuery('#' + id).removeClass('active-modal');

var modal = jQuery('#' + id);

modal.animate({ top: "-=100px" }, 300, function () {
modal.css({ top: "" });
modal.removeClass('active-modal');
});

if (id == 'resend_email_modal') {
remove_unsent_temporary_email_attachment();
}
}
}

// Attach to namespace
window.BMAdmin = window.BMAdmin || {};
window.BMAdmin.Utils = BMUtils;

// Global aliases for backward compatibility with PHP onclick handlers
window.getUrlParameter = BMUtils.getUrlParameter;
window.padWithZeros = BMUtils.padWithZeros;
window.strToMins = BMUtils.strToMins;
window.minsToStr = BMUtils.minsToStr;
window.timeStringToFloat = BMUtils.timeStringToFloat;
window.convertNumToTime = BMUtils.convertNumToTime;
window.isFormattedDate = BMUtils.isFormattedDate;
window.convertDateFormat = BMUtils.convertDateFormat;
window.convertDateFormat_old = BMUtils.convertDateFormat_old;
window.changePriceFormat = BMUtils.changePriceFormat;
window.generatePagination = BMUtils.generatePagination;
window.showMessage = BMUtils.showMessage;
window.hideMessage = BMUtils.hideMessage;
window.getUrlVars = BMUtils.getUrlVars;
window.hasCommonElement = BMUtils.hasCommonElement;
window.array_sum = BMUtils.array_sum;
window.encodeValue = BMUtils.encodeValue;
window.encodeValue1 = BMUtils.encodeValue1;
window.bm_copy_text = BMUtils.bm_copy_text;
window.bm_copy_message = BMUtils.bm_copy_message;
window.bm_show_search_box = BMUtils.bm_show_search_box;
window.bm_remove_hidden_class = BMUtils.bm_remove_hidden_class;
window.getDaysArray = BMUtils.getDaysArray;
window.showToDate = BMUtils.showToDate;
window.closeModal = BMUtils.closeModal;

// REST API request helper
window.bmRestRequest = function(action, data, successCallback) {
    return jQuery.ajax({
        url: bm_ajax_object.rest_url + action,
        method: 'POST',
        data: data,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', bm_ajax_object.rest_nonce);
        },
        success: successCallback
    });
};

// Safe JSON parse helper
window.bmSafeParse = function(response) {
    if ( typeof response === 'string' ) {
        try { return JSON.parse(response); } catch(e) { return response; }
    }
    return response;
};
