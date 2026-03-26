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
     * Deactivation cleanup.
     *
     * Unschedules the async queue cron event and fires a
     * deactivation hook for extensions.
     *
     * @since 1.0.0
     */
    public static function deactivate() {
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
