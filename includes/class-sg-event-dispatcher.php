<?php
/**
 * Event Dispatcher — Event-driven architecture for FlexiBooking.
 *
 * Provides a lightweight event bus that decouples heavy processing from
 * the request cycle. Events can be dispatched synchronously (default) or
 * asynchronously via the SG_Async_Queue class.
 *
 * Usage:
 *   SG_Event_Dispatcher::dispatch( 'booking.confirmed', [ 'booking_id' => 123 ] );
 *   SG_Event_Dispatcher::listen( 'booking.confirmed', function( $payload ) { ... } );
 *
 * All events also fire a WordPress action `sg_booking_event_{name}` so standard
 * WordPress hooks can be used as listeners.
 *
 * @since      1.2.0
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SG_Event_Dispatcher {

	/**
	 * Registered event listeners.
	 *
	 * @var array<string, array<callable>>
	 */
	private static $listeners = array();

	/**
	 * Events that should be processed asynchronously.
	 *
	 * @var array<string>
	 */
	private static $async_events = array();

	/**
	 * Register a listener for an event.
	 *
	 * @param string   $event    Event name (e.g. 'booking.confirmed').
	 * @param callable $callback Listener callback receiving ( array $payload ).
	 * @param int      $priority Optional. Lower = earlier. Default 10.
	 */
	public static function listen( $event, $callback, $priority = 10 ) {
		self::$listeners[ $event ][ $priority ][] = $callback;
	}

	/**
	 * Register an event as asynchronous.
	 *
	 * Async events are queued via SG_Async_Queue instead of being
	 * dispatched inline during the current request.
	 *
	 * @param string $event Event name.
	 */
	public static function register_async( $event ) {
		self::$async_events[] = $event;
	}

	/**
	 * Dispatch an event.
	 *
	 * If the event is registered as async and the queue is available,
	 * the payload is enqueued for background processing. Otherwise,
	 * all registered listeners are called synchronously.
	 *
	 * @param string $event   Event name.
	 * @param array  $payload Associative array of event data.
	 */
	public static function dispatch( $event, array $payload = array() ) {
		$payload['_event']     = $event;
		$payload['_timestamp'] = current_time( 'mysql' );

		/**
		 * Filter the event payload before dispatching.
		 *
		 * @since 1.2.0
		 *
		 * @param array  $payload Event data.
		 * @param string $event   Event name.
		 */
		$payload = apply_filters( 'sg_booking_event_payload', $payload, $event );

		// Async processing — enqueue and return immediately.
		if ( in_array( $event, self::$async_events, true ) && class_exists( 'SG_Async_Queue' ) ) {
			SG_Async_Queue::get_instance()->push( $event, $payload );
			return;
		}

		// Synchronous processing — call registered listeners.
		self::fire( $event, $payload );
	}

	/**
	 * Fire all listeners for an event synchronously.
	 *
	 * @param string $event   Event name.
	 * @param array  $payload Event data.
	 */
	private static function fire( $event, array $payload ) {
		// Invoke class-level listeners.
		if ( ! empty( self::$listeners[ $event ] ) ) {
			ksort( self::$listeners[ $event ] );
			foreach ( self::$listeners[ $event ] as $priority_group ) {
				foreach ( $priority_group as $callback ) {
					call_user_func( $callback, $payload );
				}
			}
		}

		// Also fire a WordPress action so standard hooks work.
		$hook_name = 'sg_booking_event_' . str_replace( '.', '_', $event );

		/**
		 * Dynamic action for each dispatched event.
		 *
		 * Hook name: `sg_booking_event_{event_name}` (dots replaced with underscores).
		 *
		 * @since 1.2.0
		 *
		 * @param array $payload Event data.
		 */
		do_action( $hook_name, $payload );

		/**
		 * Fires after any event is dispatched (catch-all).
		 *
		 * @since 1.2.0
		 *
		 * @param string $event   Event name.
		 * @param array  $payload Event data.
		 */
		do_action( 'sg_booking_event_dispatched', $event, $payload );
	}

	/**
	 * Process a queued event (called by the async queue processor).
	 *
	 * @param string $event   Event name.
	 * @param array  $payload Event data.
	 */
	public static function process_queued( $event, array $payload ) {
		self::fire( $event, $payload );
	}

	/**
	 * Get all registered event names.
	 *
	 * @return array
	 */
	public static function get_registered_events() {
		return array_keys( self::$listeners );
	}

	/**
	 * Core booking events provided by the plugin.
	 *
	 * Call during plugin init to register default async events.
	 */
	public static function register_default_events() {
		$async_events = array(
			'email.admin_notification',
			'email.customer_notification',
			'pdf.generate',
			'analytics.track',
			'webhook.send',
		);

		/**
		 * Filter the list of events that should be processed asynchronously.
		 *
		 * @since 1.2.0
		 *
		 * @param array $async_events Default async event list.
		 */
		$async_events = apply_filters( 'sg_booking_async_events', $async_events );

		foreach ( $async_events as $event ) {
			self::register_async( $event );
		}
	}
}
