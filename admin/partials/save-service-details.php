<?php
/**
 * Service Details tab — save data preparation.
 *
 * Prepares the core service fields collected from the Service Details tab.
 * Included by booking-management-add-service.php during form processing.
 *
 * @since 1.3.0
 * @package Booking_Management
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract the first selected category for the legacy column (supports array from multi-select).
$raw_category = isset( $_POST['service_category'] )
    ? ( is_array( $_POST['service_category'] ) ? reset( $_POST['service_category'] ) : $_POST['service_category'] )
    : null;

$service_details_data = array(
    'service_name'           => isset( $_POST['service_name'] ) ? ucfirst( filter_input( INPUT_POST, 'service_name' ) ) : '',
    'service_calendar_title' => isset( $_POST['service_calendar_title'] ) ? ucfirst( filter_input( INPUT_POST, 'service_calendar_title' ) ) : '',
    'service_category'       => null !== $raw_category ? absint( $raw_category ) : null,
    'service_duration'       => isset( $_POST['service_duration'] ) ? filter_input( INPUT_POST, 'service_duration' ) : null,
    'service_operation'      => isset( $_POST['service_operation'] ) ? filter_input( INPUT_POST, 'service_operation' ) : null,
    'default_max_cap'        => ! empty( $_POST['default_max_cap'] ) ? filter_input( INPUT_POST, 'default_max_cap' ) : 1,
    'is_service_front'       => isset( $_POST['is_service_front'] ) ? 1 : 0,
    'service_short_desc'     => isset( $_POST['service_short_desc'] ) ? filter_input( INPUT_POST, 'service_short_desc' ) : null,
    'service_desc'           => isset( $_POST['service_desc'] ) ? filter_input( INPUT_POST, 'service_desc' ) : null,
    'default_price'          => isset( $_POST['default_price'] ) ? filter_input( INPUT_POST, 'default_price' ) : null,
    'service_image_guid'     => isset( $_POST['svc_image_id'] ) ? filter_input( INPUT_POST, 'svc_image_id' ) : 0,
    'is_linked_wc_product'   => isset( $_POST['is_linked_wc_product'] ) ? 1 : 0,
    'wc_product'             => isset( $_POST['is_linked_wc_product'] ) ? filter_input( INPUT_POST, 'wc_product' ) : null,
    'service_settings'       => isset( $_POST['service_settings'] ) ? filter_input( INPUT_POST, 'service_settings', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) : null,
);
