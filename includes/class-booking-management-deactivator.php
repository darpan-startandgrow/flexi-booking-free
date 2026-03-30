<?php

/**
 * Fired during plugin deactivation
 *
 * @link  https://startandgrow.in
 * @since 1.0.0
 *
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 * @author     Start and Grow <laravel6@startandgrow.in>
 */
class Booking_Management_Deactivator {



    /**
     * All plugin cron hooks that must be removed on deactivation.
     *
     * @since 1.3.0
     * @var string[]
     */
    private static $cron_hooks = array(
        'flexibooking_check_expired_book_on_request_bookings',
        'flexibooking_check_paid_expired_processing_bookings',
        'flexibooking_check_expired_pending_bookings',
        'flexibooking_check_expired_free_bookings',
        'flexibooking_check_expired_vouchers',
        'bm_resend_missing_emails_hook',
    );

    /**
     * Deactivation cleanup.
     *
     * Unschedules all plugin cron events (including the async queue)
     * so that WordPress does not attempt to fire callbacks or query
     * plugin tables after the plugin is deactivated.
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        // Unschedule all plugin cron hooks to prevent reschedule errors
        // and database queries against tables that may no longer exist.
        foreach ( self::$cron_hooks as $hook ) {
            $timestamp = wp_next_scheduled( $hook );
            if ( $timestamp ) {
                wp_unschedule_event( $timestamp, $hook );
            }
            wp_clear_scheduled_hook( $hook );
        }

        // Unschedule the async queue processor.
        if ( class_exists( 'SG_Async_Queue' ) ) {
            SG_Async_Queue::unschedule();
        }

        /**
         * Fires during plugin deactivation.
         *
         * @since 1.2.0
         */
        do_action( 'sg_booking_deactivated' );
    }/**end deactivate()*/


}/**end class*/
