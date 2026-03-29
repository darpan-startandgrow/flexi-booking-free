<?php
/**
 * Gallery tab — save data preparation.
 *
 * Prepares the gallery image data collected from the Gallery tab.
 * Included by booking-management-add-service.php during form processing.
 *
 * @since 1.3.0
 * @package Booking_Management
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// $svc_identifier is defined by the including file (booking-management-add-service.php).
$gallery_data = array(
    'module_type' => isset( $svc_identifier ) ? $svc_identifier : '',
    'image_guid'  => isset( $_POST['svc_gallery_image_id'] ) ? filter_input( INPUT_POST, 'svc_gallery_image_id' ) : null,
);
