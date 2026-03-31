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
// Note: filter_input() with FILTER_REQUIRE_ARRAY does not support nested arrays
// (availability_periods_new[start][] / availability_periods_new[end][]),
// so we sanitize the nested structure manually.
$availability_periods_new = array();
if ( isset( $_POST['availability_periods_new'] ) && is_array( $_POST['availability_periods_new'] ) ) {
    $raw = $_POST['availability_periods_new']; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in parent file.
    if ( isset( $raw['start'] ) && is_array( $raw['start'] ) ) {
        $availability_periods_new['start'] = array_map( 'sanitize_text_field', $raw['start'] );
    }
    if ( isset( $raw['end'] ) && is_array( $raw['end'] ) ) {
        $availability_periods_new['end'] = array_map( 'sanitize_text_field', $raw['end'] );
    }
}

$availability_periods_existing = isset( $_POST['availability_periods'] ) && isset( $_POST['availability_periods']['existing'] )
    ? array_map( 'absint', $_POST['availability_periods']['existing'] )
    : array();
