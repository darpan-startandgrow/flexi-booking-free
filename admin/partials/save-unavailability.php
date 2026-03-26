<?php
/**
 * Availability & Settings tab — save data preparation.
 *
 * Prepares the weekly unavailability data (inverted: unchecked = unavailable)
 * and processes availability period add/remove operations.
 * Included by booking-management-add-service.php during form processing.
 *
 * @since 1.4.0
 * @package Booking_Management
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$raw_service_options = isset( $_POST['service_options'] )
    ? filter_input( INPUT_POST, 'service_options', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY )
    : null;

$unavailability_data = array(
    'service_unavailability' => isset( $_POST['service_unavailability'] ) ? filter_input( INPUT_POST, 'service_unavailability', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) : null,
    'service_options'        => ( $raw_service_options && array_filter( $raw_service_options ) ) ? $raw_service_options : null,
);

// Availability periods — collected separately, saved to dedicated table after service insert/update.
$availability_periods_new = isset( $_POST['availability_periods_new'] )
    ? filter_input( INPUT_POST, 'availability_periods_new', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY )
    : array();

$availability_periods_existing = isset( $_POST['availability_periods'] ) && isset( $_POST['availability_periods']['existing'] )
    ? array_map( 'absint', $_POST['availability_periods']['existing'] )
    : array();
