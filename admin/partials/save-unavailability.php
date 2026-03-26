<?php
/**
 * Unavailability & Settings tab — save data preparation.
 *
 * Prepares the unavailability and other settings data collected from the
 * Unavailability and Other Settings tab.
 * Included by booking-management-add-service.php during form processing.
 *
 * @since 1.3.0
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
