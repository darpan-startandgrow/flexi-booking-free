<?php
/**
 * Prices tab — save data preparation.
 *
 * Prepares the variable price data collected from the Prices calendar tab.
 * Included by booking-management-add-service.php during form processing.
 *
 * @since 1.3.0
 * @package Booking_Management
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$prices_data = array();

if ( isset( $_POST['variable_svc_prices'] ) ) {
    $prices_data['variable_svc_prices'] = filter_input( INPUT_POST, 'variable_svc_prices', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
}
