<?php
/**
 * Core REST API registry for SG Flexi Booking Lite.
 *
 * Registers essential v1 endpoints consumed by the Lite plugin's own
 * frontend (shortcodes, calendar, checkout). This is separate from the
 * React v2 API in class-booking-api.php which is meant for the React
 * frontend.
 *
 * Endpoints:
 *   GET  /sg-booking/v1/timeslots    — Fetch available timeslots.
 *   POST /sg-booking/v1/booking      — Submit a booking / checkout.
 *   GET  /sg-booking/v1/orders       — Backend datatable retrieval.
 *
 * @since      1.1.0
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Booking_Management_Rest_API {

	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'sg-booking/v1';

	/**
	 * Initialize the REST API — hooked into `rest_api_init`.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	// ------------------------------------------------------------------
	// Route registration
	// ------------------------------------------------------------------

	/**
	 * Register all Lite-core REST routes.
	 */
	public function register_routes() {

		// --- Public endpoints (used by frontend shortcodes) ---

		register_rest_route(
			self::NAMESPACE,
			'/timeslots',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_available_timeslots' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'service_id'   => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && (int) $param > 0;
						},
						'sanitize_callback' => 'absint',
					),
					'booking_date' => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $param );
						},
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/booking',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'submit_booking' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'service_id'   => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'booking_date' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'slot_id'      => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'quantity'     => array(
						'required'          => false,
						'default'           => 1,
						'sanitize_callback' => 'absint',
					),
					'customer'     => array(
						'required'          => true,
						'type'              => 'object',
					),
				),
			)
		);

		// --- Admin-only endpoints ---

		register_rest_route(
			self::NAMESPACE,
			'/orders',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_orders' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'page'     => array(
						'required'          => false,
						'default'           => 1,
						'sanitize_callback' => 'absint',
					),
					'per_page' => array(
						'required'          => false,
						'default'           => 20,
						'sanitize_callback' => 'absint',
					),
					'status'   => array(
						'required'          => false,
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'search'   => array(
						'required'          => false,
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	// ------------------------------------------------------------------
	// Permission callbacks
	// ------------------------------------------------------------------

	/**
	 * Strict permission callback for admin-only endpoints.
	 *
	 * @return bool|WP_Error
	 */
	public function check_admin_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'You do not have permission to access this endpoint.', 'service-booking' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	// ------------------------------------------------------------------
	// Endpoint handlers
	// ------------------------------------------------------------------

	/**
	 * GET /timeslots — Fetch available timeslots for a service on a date.
	 *
	 * Uses transient caching; the cache is invalidated when a new booking
	 * is saved (see bm_invalidate_timeslot_cache).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_available_timeslots( $request ) {
		$service_id   = $request->get_param( 'service_id' );
		$booking_date = $request->get_param( 'booking_date' );

		$transient_key = 'sg_ts_' . $service_id . '_' . $booking_date;
		$cached        = get_transient( $transient_key );

		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		global $wpdb;
		$activator  = new Booking_Management_Activator();
		$slot_table = $activator->get_db_table_name( 'TIME' );
		$count_table = $activator->get_db_table_name( 'SLOTCOUNT' );

		if ( empty( $slot_table ) || empty( $count_table ) ) {
			return new WP_Error( 'db_error', esc_html__( 'Database tables not found.', 'service-booking' ), array( 'status' => 500 ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$slots = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.id AS slot_id, t.time_from, t.time_to, t.max_cap,
					COALESCE( sc.booked, 0 ) AS booked
				 FROM {$slot_table} AS t
				 LEFT JOIN (
					 SELECT slot_id, SUM( slot_count ) AS booked
					 FROM {$count_table}
					 WHERE service_id = %d AND booking_date = %s AND is_active = 1
					 GROUP BY slot_id
				 ) AS sc ON sc.slot_id = t.id
				 WHERE t.service_id = %d
				 ORDER BY t.time_from ASC",
				$service_id,
				$booking_date,
				$service_id
			)
		);

		$timeslots = array();
		if ( $slots ) {
			foreach ( $slots as $slot ) {
				$available = max( 0, (int) $slot->max_cap - (int) $slot->booked );
				$timeslots[] = array(
					'slot_id'   => (int) $slot->slot_id,
					'time_from' => $slot->time_from,
					'time_to'   => $slot->time_to,
					'max_cap'   => (int) $slot->max_cap,
					'booked'    => (int) $slot->booked,
					'available' => $available,
				);
			}
		}

		// Cache for 5 minutes.
		set_transient( $transient_key, $timeslots, 5 * MINUTE_IN_SECONDS );

		return rest_ensure_response( $timeslots );
	}

	/**
	 * POST /booking — Submit a booking / checkout.
	 *
	 * Uses ACID transactions: START TRANSACTION → row locking → COMMIT
	 * on success or ROLLBACK on failure.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function submit_booking( $request ) {
		global $wpdb;

		$service_id   = $request->get_param( 'service_id' );
		$booking_date = $request->get_param( 'booking_date' );
		$slot_id      = $request->get_param( 'slot_id' );
		$quantity     = max( 1, $request->get_param( 'quantity' ) );
		$customer     = $request->get_param( 'customer' );

		if ( ! is_array( $customer ) || empty( $customer ) ) {
			return new WP_Error( 'invalid_customer', esc_html__( 'Customer data is required.', 'service-booking' ), array( 'status' => 400 ) );
		}

		$activator   = new Booking_Management_Activator();
		$count_table = $activator->get_db_table_name( 'SLOTCOUNT' );
		$slot_table  = $activator->get_db_table_name( 'TIME' );
		$book_table  = $activator->get_db_table_name( 'BOOKING' );

		if ( empty( $count_table ) || empty( $slot_table ) || empty( $book_table ) ) {
			return new WP_Error( 'db_error', esc_html__( 'Database tables not found.', 'service-booking' ), array( 'status' => 500 ) );
		}

		// --- ACID Transaction ---
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( 'START TRANSACTION' );

		try {
			// Lock the slot count row to prevent double-booking.
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$booked = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COALESCE( SUM( slot_count ), 0 )
					 FROM {$count_table}
					 WHERE service_id = %d AND booking_date = %s AND slot_id = %d AND is_active = 1
					 FOR UPDATE",
					$service_id,
					$booking_date,
					$slot_id
				)
			);

			// Get the slot capacity.
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$max_cap = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT max_cap FROM {$slot_table} WHERE id = %d AND service_id = %d",
					$slot_id,
					$service_id
				)
			);

			if ( null === $max_cap ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( 'ROLLBACK' );
				return new WP_Error( 'invalid_slot', esc_html__( 'The selected time slot does not exist.', 'service-booking' ), array( 'status' => 404 ) );
			}

			$available = (int) $max_cap - (int) $booked;

			if ( $quantity > $available ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( 'ROLLBACK' );
				return new WP_Error(
					'no_availability',
					esc_html__( 'Not enough availability for the requested quantity.', 'service-booking' ),
					array( 'status' => 409 )
				);
			}

			// Insert the slot count record.
			$wpdb->insert(
				$count_table,
				array(
					'service_id'   => $service_id,
					'booking_date' => $booking_date,
					'slot_id'      => $slot_id,
					'slot_count'   => $quantity,
					'is_active'    => 1,
				),
				array( '%d', '%s', '%d', '%d', '%d' )
			);

			$slot_count_id = $wpdb->insert_id;

			if ( ! $slot_count_id ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( 'ROLLBACK' );
				return new WP_Error( 'insert_failed', esc_html__( 'Failed to reserve the time slot.', 'service-booking' ), array( 'status' => 500 ) );
			}

			// Insert the booking record.
			$booking_data = array(
				'service_id'   => $service_id,
				'booking_date' => $booking_date,
				'order_status' => 'pending',
				'created_at'   => current_time( 'mysql' ),
			);

			// Sanitise customer fields.
			$allowed_customer_keys = array( 'first_name', 'last_name', 'email', 'phone', 'notes' );
			foreach ( $allowed_customer_keys as $key ) {
				if ( isset( $customer[ $key ] ) ) {
					$booking_data[ $key ] = sanitize_text_field( $customer[ $key ] );
				}
			}

			$wpdb->insert( $book_table, $booking_data );
			$booking_id = $wpdb->insert_id;

			if ( ! $booking_id ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( 'ROLLBACK' );
				return new WP_Error( 'insert_failed', esc_html__( 'Failed to create the booking.', 'service-booking' ), array( 'status' => 500 ) );
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( 'COMMIT' );

			// Invalidate timeslot cache.
			$this->invalidate_timeslot_cache( $service_id, $booking_date );

			/**
			 * Fires after a booking has been successfully saved.
			 *
			 * @since 1.1.0
			 *
			 * @param int   $booking_id The new booking ID.
			 * @param array $booking_data The booking data that was inserted.
			 */
			do_action( 'bm_after_booking_saved', $booking_id, $booking_data );

			return rest_ensure_response( array(
				'success'    => true,
				'booking_id' => $booking_id,
				'message'    => esc_html__( 'Booking created successfully.', 'service-booking' ),
			) );

		} catch ( \Exception $e ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( 'ROLLBACK' );
			return new WP_Error( 'booking_error', esc_html( $e->getMessage() ), array( 'status' => 500 ) );
		}
	}

	/**
	 * GET /orders — Retrieve orders for backend datatable.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_orders( $request ) {
		global $wpdb;

		$page     = max( 1, $request->get_param( 'page' ) );
		$per_page = min( 100, max( 1, $request->get_param( 'per_page' ) ) );
		$status   = $request->get_param( 'status' );
		$search   = $request->get_param( 'search' );

		$activator  = new Booking_Management_Activator();
		$book_table = $activator->get_db_table_name( 'BOOKING' );

		if ( empty( $book_table ) ) {
			return rest_ensure_response( array(
				'orders' => array(),
				'total'  => 0,
			) );
		}

		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $status ) ) {
			$where[]  = 'order_status = %s';
			$values[] = $status;
		}

		if ( ! empty( $search ) ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '( first_name LIKE %s OR last_name LIKE %s OR email LIKE %s )';
			$values[] = $like;
			$values[] = $like;
			$values[] = $like;
		}

		$where_clause = implode( ' AND ', $where );
		$offset       = ( $page - 1 ) * $per_page;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = (int) $wpdb->get_var(
			empty( $values )
				? "SELECT COUNT(*) FROM {$book_table} WHERE {$where_clause}"
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				: $wpdb->prepare( "SELECT COUNT(*) FROM {$book_table} WHERE {$where_clause}", $values )
		);

		$values[] = $per_page;
		$values[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$book_table} WHERE {$where_clause} ORDER BY id DESC LIMIT %d OFFSET %d",
				$values
			)
		);

		return rest_ensure_response( array(
			'orders'   => $rows ? $rows : array(),
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per_page,
		) );
	}

	// ------------------------------------------------------------------
	// Cache helpers
	// ------------------------------------------------------------------

	/**
	 * Invalidate the timeslot transient cache for a given service/date.
	 *
	 * Called after a new booking is created or cancelled.
	 *
	 * @param int    $service_id   Service ID.
	 * @param string $booking_date Booking date (Y-m-d).
	 */
	public function invalidate_timeslot_cache( $service_id, $booking_date ) {
		delete_transient( 'sg_ts_' . (int) $service_id . '_' . sanitize_text_field( $booking_date ) );
	}
}

// Bootstrap the REST API.
new Booking_Management_Rest_API();
