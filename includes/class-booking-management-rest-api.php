<?php
/**
 * Core REST API registry for SG Flexi Booking Lite.
 *
 * Registers essential v1 endpoints consumed by the Lite plugin's own
 * frontend (shortcodes, calendar, checkout). This is separate from the
 * All admin and public endpoints are registered here.
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

		// --- Service Management REST Endpoints ---

		register_rest_route(
			self::NAMESPACE,
			'/services',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_services' ),
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
					'search'   => array(
						'required'          => false,
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'category_id' => array(
						'required'          => false,
						'default'           => 0,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/services/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_service' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/services/(?P<id>\d+)',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_service' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id'           => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'service_name' => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'service_desc' => array(
						'required'          => false,
						'sanitize_callback' => 'wp_kses_post',
					),
					'service_category' => array(
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
					'service_duration' => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'service_price' => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/services/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_service' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// --- Fields Management REST Endpoints ---

		register_rest_route(
			self::NAMESPACE,
			'/fields',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_fields' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'form_id' => array(
						'required'          => false,
						'default'           => 1,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/fields/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_field' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/fields/(?P<id>\d+)',
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_field' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id'          => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'field_label' => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'placeholder' => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'is_required' => array(
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
					'visible'     => array(
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/fields/reorder',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'reorder_fields' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'field_id'  => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'direction' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function ( $param ) {
							return in_array( $param, array( 'up', 'down' ), true );
						},
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/fields/preview',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'preview_fields' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'form_id' => array(
						'required'          => false,
						'default'           => 1,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// --- Billing Forms REST Endpoints ---

		register_rest_route(
			self::NAMESPACE,
			'/forms',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_forms' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// --- Voucher Listing REST Endpoint ---

		register_rest_route(
			self::NAMESPACE,
			'/vouchers',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_vouchers' ),
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
				),
			)
		);

		// --- Customer Listing REST Endpoint ---

		register_rest_route(
			self::NAMESPACE,
			'/customers',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_customers' ),
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
					'search'   => array(
						'required'          => false,
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// --- Check-In REST Endpoints ---

		register_rest_route(
			self::NAMESPACE,
			'/checkins',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_checkins' ),
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

		register_rest_route(
			self::NAMESPACE,
			'/checkins/(?P<id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'manual_checkin' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// --- Email Listing REST Endpoint ---

		register_rest_route(
			self::NAMESPACE,
			'/emails',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_emails' ),
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
				),
			)
		);

		// --- Email Records REST Endpoint (simplified listing) ---

		register_rest_route(
			self::NAMESPACE,
			'/email-records',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_email_records' ),
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
					'search'   => array(
						'required'          => false,
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// --- Dashboard REST Endpoint ---

		register_rest_route(
			self::NAMESPACE,
			'/dashboard',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_dashboard' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/dashboard/counts',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_dashboard_counts' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/dashboard/status-chart',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_dashboard_status_chart' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// --- Action endpoints (replacing AJAX handlers) ---

		register_rest_route(
			self::NAMESPACE,
			'/services/(?P<id>\d+)/visibility',
			array(
				'methods'             => 'PATCH',
				'callback'            => array( $this, 'toggle_service_visibility' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'visible' => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return in_array( (int) $param, array( 0, 1 ), true );
						},
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/categories/(?P<id>\d+)/visibility',
			array(
				'methods'             => 'PATCH',
				'callback'            => array( $this, 'toggle_category_visibility' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'visible' => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return in_array( (int) $param, array( 0, 1 ), true );
						},
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/categories',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_categories' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/categories/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_category' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/services/reorder',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'reorder_services' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/categories/reorder',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'reorder_categories' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/orders/(?P<id>\d+)/status',
			array(
				'methods'             => 'PATCH',
				'callback'            => array( $this, 'update_order_status' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'status' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/orders/(?P<id>\d+)/archive',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'archive_order' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/orders/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_order' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/templates/(?P<id>\d+)/visibility',
			array(
				'methods'             => 'PATCH',
				'callback'            => array( $this, 'toggle_template_visibility' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'visible' => array(
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return in_array( (int) $param, array( 0, 1 ), true );
						},
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/templates/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_template' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// --- Check-in REST Endpoints ---

		register_rest_route(
			self::NAMESPACE,
			'/checkins/process',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_manual_checkin_process' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'search_type'  => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'search_value' => array(
						'required' => true,
					),
					'booking_ids'  => array(
						'required' => false,
						'default'  => array(),
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/checkins/search',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_manual_checkin_check' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'search_type'  => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'search_value' => array(
						'required' => true,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/checkins/status',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_update_checkin_status' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'checkin_id'  => array(
						'required'          => false,
						'default'           => 0,
						'sanitize_callback' => 'absint',
					),
					'booking_id'  => array(
						'required'          => false,
						'default'           => 0,
						'sanitize_callback' => 'absint',
					),
					'new_status'  => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/checkins/details/(?P<booking_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_manual_checkin_view_details' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'booking_id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		/**
		 * Fires after all Lite REST routes are registered.
		 *
		 * Use this hook to register additional custom REST routes
		 * under the same namespace or to modify existing route behaviour.
		 *
		 * @since 1.1.0
		 *
		 * @param string $namespace The REST namespace ('sg-booking/v1').
		 */
		do_action( 'sg_booking_rest_routes_registered', self::NAMESPACE );
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

		// Check cache — use SG_Cache_Manager if available, otherwise fall back to transients.
		$cache_key = 'ts_' . $service_id . '_' . $booking_date;
		if ( class_exists( 'SG_Cache_Manager' ) ) {
			$cached = SG_Cache_Manager::get_instance()->get( $cache_key );
		} else {
			$transient_key = 'sg_ts_' . $service_id . '_' . $booking_date;
			$cached        = get_transient( $transient_key );
		}

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

		// Table names from get_db_table_name() are hardcoded in the activator class — not user input.
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

		// Cache for 5 minutes — use SG_Cache_Manager if available.
		if ( class_exists( 'SG_Cache_Manager' ) ) {
			SG_Cache_Manager::get_instance()->set( $cache_key, $timeslots, 5 * MINUTE_IN_SECONDS );
		} else {
			set_transient( $transient_key, $timeslots, 5 * MINUTE_IN_SECONDS );
		}

		/**
		 * Filters the timeslots response before sending to client.
		 *
		 * @since 1.1.0
		 *
		 * @param array $timeslots    The timeslot data array.
		 * @param int   $service_id   The service ID.
		 * @param string $booking_date The booking date.
		 */
		$timeslots = apply_filters( 'sg_booking_rest_timeslots', $timeslots, $service_id, $booking_date );

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

			/**
			 * Filters the booking data before it is saved to the database.
			 *
			 * @since 1.1.0
			 *
			 * @param array $booking_data The booking record data.
			 * @param array $customer     The raw customer data from the request.
			 * @param int   $slot_id      The selected time slot ID.
			 */
			$booking_data = apply_filters( 'sg_booking_before_save', $booking_data, $customer, $slot_id );

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

			// Invalidate services/categories API cache.
			if ( class_exists( 'SG_Cache_Manager' ) ) {
				$cache = SG_Cache_Manager::get_instance();
				$cache->delete( 'api_categories' );
			}

			/**
			 * Fires after a booking has been successfully saved.
			 *
			 * @since 1.1.0
			 *
			 * @param int   $booking_id The new booking ID.
			 * @param array $booking_data The booking data that was inserted.
			 */
			do_action( 'bm_after_booking_saved', $booking_id, $booking_data );

			// Event-driven dispatch for REST API booking creation.
			if ( class_exists( 'SG_Event_Dispatcher' ) ) {
				SG_Event_Dispatcher::dispatch( 'booking.confirmed', array(
					'booking_id'   => $booking_id,
					'service_id'   => $service_id,
					'booking_date' => $booking_date,
					'slot_id'      => $slot_id,
					'quantity'     => $quantity,
					'source'       => 'rest_api',
				) );
			}

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

		// Free version: return only essential columns.
		$select_cols = 'id, service_name, booking_created_at, booking_date, service_cost, extra_svc_cost, disount_amount, total_cost, order_status, booking_type, field_values';

		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $status ) ) {
			$where[]  = 'order_status = %s';
			$values[] = $status;
		}

		if ( ! empty( $search ) ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '( service_name LIKE %s OR order_status LIKE %s )';
			$values[] = $like;
			$values[] = $like;
		}

		$where_clause = implode( ' AND ', $where );
		$offset       = ( $page - 1 ) * $per_page;

		// Table names from get_db_table_name() are hardcoded in the activator class — not user input.
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
				"SELECT {$select_cols} FROM {$book_table} WHERE {$where_clause} ORDER BY id DESC LIMIT %d OFFSET %d",
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
	// Service Management Handlers
	// ------------------------------------------------------------------

	/**
	 * GET /services — Retrieve a list of services.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_services( $request ) {
		global $wpdb;
		$activator = new Booking_Management_Activator();
		$table     = $activator->get_db_table_name( 'SERVICE' );

		$page     = $request->get_param( 'page' );
		$per_page = $request->get_param( 'per_page' );
		$search   = $request->get_param( 'search' );
		$cat_id   = $request->get_param( 'category_id' );

		// Check cache for listing requests.
		if ( class_exists( 'SG_Cache_Manager' ) ) {
			$cache     = SG_Cache_Manager::get_instance();
			$cache_key = 'rest_services_' . md5( wp_json_encode( array( $page, $per_page, $search, $cat_id ) ) );
			$cached    = $cache->get( $cache_key );
			if ( false !== $cached ) {
				return new WP_REST_Response( $cached, 200 );
			}
		}

		$offset = ( $page - 1 ) * $per_page;

		$where = 'WHERE 1=1';
		$args  = array();

		if ( ! empty( $search ) ) {
			$where .= ' AND service_name LIKE %s';
			$args[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		if ( ! empty( $cat_id ) ) {
			$activator = new Booking_Management_Activator();
			$map_table = $activator->get_db_table_name( 'SERVICE_CATEGORY_MAP' );
			$where    .= " AND (service_category = %d OR id IN (SELECT service_id FROM `{$map_table}` WHERE category_id = %d))";
			$args[]    = $cat_id;
			$args[]    = $cat_id;
		}

		$args[] = $per_page;
		$args[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from get_db_table_name() is hardcoded
		$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} {$where}", array_slice( $args, 0, -2 ) ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from get_db_table_name() is hardcoded
		$services = $wpdb->get_results(
			$wpdb->prepare( "SELECT id, service_name, service_desc, service_category, service_duration, service_price, is_service_front, service_position FROM {$table} {$where} ORDER BY service_position ASC LIMIT %d OFFSET %d", $args )
		);

		$items = array();
		if ( ! empty( $services ) ) {
			foreach ( $services as $svc ) {
				$items[] = array(
					'id'               => (int) $svc->id,
					'service_name'     => $svc->service_name,
					'service_desc'     => $svc->service_desc,
					'service_category' => (int) $svc->service_category,
					'service_duration' => $svc->service_duration,
					'service_price'    => $svc->service_price,
					'is_service_front' => (int) $svc->is_service_front,
					'service_position' => (int) $svc->service_position,
				);
			}
		}

		/**
		 * Filters the services list returned by the REST API.
		 *
		 * @since 1.2.0
		 * @param array           $items   The service items array.
		 * @param int             $total   Total number of services matching the query.
		 * @param WP_REST_Request $request The original request object.
		 */
		$items = apply_filters( 'sg_booking_rest_services', $items, $total, $request );

		$response_data = array(
			'items' => $items,
			'total' => $total,
			'page'  => $page,
			'pages' => ceil( $total / max( 1, $per_page ) ),
		);

		// Cache for 5 minutes.
		if ( class_exists( 'SG_Cache_Manager' ) ) {
			$cache->set( $cache_key, $response_data, 5 * MINUTE_IN_SECONDS );
		}

		return new WP_REST_Response( $response_data, 200 );
	}

	/**
	 * GET /services/{id} — Retrieve a single service.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_service( $request ) {
		$dbhandler = new BM_DBhandler();
		$id        = $request->get_param( 'id' );
		$service   = $dbhandler->get_row( 'SERVICE', $id );

		if ( empty( $service ) ) {
			return new WP_REST_Response( array( 'message' => esc_html__( 'Service not found.', 'service-booking' ) ), 404 );
		}

		// For free version, return only essential fields.
		$data = array(
			'id'               => (int) $service->id,
			'service_name'     => $service->service_name,
			'service_desc'     => isset( $service->service_desc ) ? $service->service_desc : '',
			'service_short_desc' => isset( $service->service_short_desc ) ? $service->service_short_desc : '',
			'service_category' => (int) $service->service_category,
			'service_duration' => $service->service_duration,
			'service_price'    => $service->service_price,
			'is_service_front' => (int) $service->is_service_front,
			'service_position' => (int) $service->service_position,
		);

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * PUT /services/{id} — Update a service (basic fields only in free version).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function update_service( $request ) {
		$dbhandler = new BM_DBhandler();
		$id        = $request->get_param( 'id' );
		$service   = $dbhandler->get_row( 'SERVICE', $id );

		if ( empty( $service ) ) {
			return new WP_REST_Response( array( 'message' => esc_html__( 'Service not found.', 'service-booking' ) ), 404 );
		}

		$update_data = array();
		$update_args = array();

		// Only allow updating basic service fields.
		$allowed_fields = array(
			'service_name'     => '%s',
			'service_desc'     => '%s',
			'service_category' => '%d',
			'service_duration' => '%s',
			'service_price'    => '%s',
		);

		foreach ( $allowed_fields as $field => $format ) {
			$value = $request->get_param( $field );
			if ( null !== $value ) {
				$update_data[ $field ] = $value;
				$update_args[]         = $format;
			}
		}

		if ( empty( $update_data ) ) {
			return new WP_REST_Response( array( 'message' => esc_html__( 'No fields to update.', 'service-booking' ) ), 400 );
		}

		$result = $dbhandler->update_row( 'SERVICE', $update_data, array( 'id' => $id ), $update_args, array( '%d' ) );

		if ( false === $result ) {
			return new WP_REST_Response( array( 'message' => esc_html__( 'Failed to update service.', 'service-booking' ) ), 500 );
		}

		return new WP_REST_Response( array( 'message' => esc_html__( 'Service updated.', 'service-booking' ) ), 200 );
	}

	/**
	 * DELETE /services/{id} — Delete a service.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function delete_service( $request ) {
		$dbhandler = new BM_DBhandler();
		$id        = $request->get_param( 'id' );
		$service   = $dbhandler->get_row( 'SERVICE', $id );

		if ( empty( $service ) ) {
			return new WP_REST_Response( array( 'message' => esc_html__( 'Service not found.', 'service-booking' ) ), 404 );
		}

		$deleted = $dbhandler->remove_row( 'SERVICE', 'id', $id, '%d' );

		if ( ! $deleted ) {
			return new WP_REST_Response( array( 'message' => esc_html__( 'Failed to delete service.', 'service-booking' ) ), 500 );
		}

		return new WP_REST_Response( array( 'message' => esc_html__( 'Service deleted.', 'service-booking' ) ), 200 );
	}

	/**
	 * GET /fields — Retrieve all fields for a form.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_fields( $request ) {
		global $wpdb;
		$activator = new Booking_Management_Activator();
		$table     = $activator->get_db_table_name( 'FIELDS' );
		$form_id   = $request->get_param( 'form_id' );

		if ( empty( $table ) ) {
			return rest_ensure_response( array( 'fields' => array() ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from get_db_table_name() is hardcoded
		$columns = $wpdb->get_col( "DESCRIBE {$table}", 0 );
		$has_form_id = in_array( 'form_id', $columns, true );

		if ( $has_form_id ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$fields = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE form_id = %d ORDER BY ordering ASC",
					$form_id
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$fields = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY ordering ASC" );
		}

		return rest_ensure_response( array(
			'fields'  => $fields ? $fields : array(),
			'form_id' => $form_id,
		) );
	}

	/**
	 * GET /fields/{id} — Retrieve a single field.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_field( $request ) {
		global $wpdb;
		$activator = new Booking_Management_Activator();
		$table     = $activator->get_db_table_name( 'FIELDS' );
		$id        = $request->get_param( 'id' );

		if ( empty( $table ) ) {
			return new WP_Error( 'db_error', esc_html__( 'Database tables not found.', 'service-booking' ), array( 'status' => 500 ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$field = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id )
		);

		if ( ! $field ) {
			return new WP_Error( 'not_found', esc_html__( 'Field not found.', 'service-booking' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $field );
	}

	/**
	 * PUT /fields/{id} — Update a field (label, placeholder, required, visible).
	 *
	 * In the free version, only editing of default fields is allowed.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_field( $request ) {
		global $wpdb;
		$activator = new Booking_Management_Activator();
		$table     = $activator->get_db_table_name( 'FIELDS' );
		$id        = $request->get_param( 'id' );

		if ( empty( $table ) ) {
			return new WP_Error( 'db_error', esc_html__( 'Database tables not found.', 'service-booking' ), array( 'status' => 500 ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$field = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id )
		);

		if ( ! $field ) {
			return new WP_Error( 'not_found', esc_html__( 'Field not found.', 'service-booking' ), array( 'status' => 404 ) );
		}

		$update_data   = array();
		$update_format = array();

		// Update label.
		$label = $request->get_param( 'field_label' );
		if ( null !== $label ) {
			$update_data['field_label'] = sanitize_text_field( $label );
			$update_format[]            = '%s';
		}

		// Update field_options for placeholder.
		$placeholder = $request->get_param( 'placeholder' );
		if ( null !== $placeholder ) {
			$options = maybe_unserialize( $field->field_options );
			if ( ! is_array( $options ) ) {
				$options = array();
			}
			$options['placeholder']     = sanitize_text_field( $placeholder );
			$update_data['field_options'] = maybe_serialize( $options );
			$update_format[]            = '%s';
		}

		// Update required status.
		$is_required = $request->get_param( 'is_required' );
		if ( null !== $is_required ) {
			$update_data['is_required'] = absint( $is_required ) ? 1 : 0;
			$update_format[]            = '%d';
		}

		// Update visibility.
		$visible = $request->get_param( 'visible' );
		if ( null !== $visible ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from get_db_table_name() is hardcoded
		$columns = $wpdb->get_col( "DESCRIBE {$table}", 0 );
			if ( in_array( 'visible', $columns, true ) ) {
				$update_data['visible'] = absint( $visible ) ? 1 : 0;
				$update_format[]        = '%d';
			}
		}

		if ( empty( $update_data ) ) {
			return new WP_Error( 'no_data', esc_html__( 'No data to update.', 'service-booking' ), array( 'status' => 400 ) );
		}

		$wpdb->update( $table, $update_data, array( 'id' => $id ), $update_format, array( '%d' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$updated_field = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id )
		);

		return rest_ensure_response( array(
			'success' => true,
			'field'   => $updated_field,
		) );
	}

	/**
	 * POST /fields/reorder — Reorder a field up or down.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function reorder_fields( $request ) {
		global $wpdb;
		$activator = new Booking_Management_Activator();
		$table     = $activator->get_db_table_name( 'FIELDS' );
		$field_id  = $request->get_param( 'field_id' );
		$direction = $request->get_param( 'direction' );

		if ( empty( $table ) ) {
			return new WP_Error( 'db_error', esc_html__( 'Database tables not found.', 'service-booking' ), array( 'status' => 500 ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$current = $wpdb->get_row(
			$wpdb->prepare( "SELECT id, ordering FROM {$table} WHERE id = %d", $field_id )
		);

		if ( ! $current ) {
			return new WP_Error( 'not_found', esc_html__( 'Field not found.', 'service-booking' ), array( 'status' => 404 ) );
		}

		$current_order = (int) $current->ordering;

		if ( 'up' === $direction ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$swap = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT id, ordering FROM {$table} WHERE ordering < %d ORDER BY ordering DESC LIMIT 1",
					$current_order
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$swap = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT id, ordering FROM {$table} WHERE ordering > %d ORDER BY ordering ASC LIMIT 1",
					$current_order
				)
			);
		}

		if ( ! $swap ) {
			return rest_ensure_response( array(
				'success' => false,
				'message' => esc_html__( 'Cannot move field further in this direction.', 'service-booking' ),
			) );
		}

		// Swap the ordering values.
		$wpdb->update( $table, array( 'ordering' => (int) $swap->ordering ), array( 'id' => $field_id ), array( '%d' ), array( '%d' ) );
		$wpdb->update( $table, array( 'ordering' => $current_order ), array( 'id' => (int) $swap->id ), array( '%d' ), array( '%d' ) );

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Field reordered successfully.', 'service-booking' ),
		) );
	}

	/**
	 * GET /fields/preview — Preview how the form will render.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function preview_fields( $request ) {
		global $wpdb;
		$activator = new Booking_Management_Activator();
		$table     = $activator->get_db_table_name( 'FIELDS' );
		$form_id   = $request->get_param( 'form_id' );

		if ( empty( $table ) ) {
			return rest_ensure_response( array( 'html' => '' ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from get_db_table_name() is hardcoded
		$columns = $wpdb->get_col( "DESCRIBE {$table}", 0 );
		$has_visible = in_array( 'visible', $columns, true );
		$has_form_id = in_array( 'form_id', $columns, true );

		$where = '1=1';
		$values = array();

		if ( $has_visible ) {
			$where .= ' AND visible = 1';
		}
		if ( $has_form_id ) {
			$where .= ' AND form_id = %d';
			$values[] = $form_id;
		}

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$fields = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE {$where} ORDER BY ordering ASC",
					$values
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$fields = $wpdb->get_results( "SELECT * FROM {$table} WHERE {$where} ORDER BY ordering ASC" );
		}

		$html = '<div class="sg-booking-preview-form">';
		if ( $fields ) {
			foreach ( $fields as $field ) {
				$options     = maybe_unserialize( $field->field_options );
				$placeholder = is_array( $options ) && isset( $options['placeholder'] ) ? esc_attr( $options['placeholder'] ) : '';
				$required    = $field->is_required ? ' <span class="required">*</span>' : '';
				$field_width = is_array( $options ) && isset( $options['field_width'] ) ? esc_attr( $options['field_width'] ) : 'full';

				$html .= '<div class="sg-field-row sg-field-width-' . $field_width . '">';
				$html .= '<label>' . esc_html( $field->field_label ) . $required . '</label>';

				switch ( $field->field_type ) {
					case 'textarea':
						$html .= '<textarea placeholder="' . $placeholder . '" disabled></textarea>';
						break;
					case 'select':
						$html .= '<select disabled><option>' . esc_html( $placeholder ) . '</option></select>';
						break;
					case 'tel':
						$html .= '<input type="tel" placeholder="' . $placeholder . '" disabled />';
						break;
					default:
						$html .= '<input type="' . esc_attr( $field->field_type ) . '" placeholder="' . $placeholder . '" disabled />';
						break;
				}

				$html .= '</div>';
			}
		}
		$html .= '</div>';

		return rest_ensure_response( array(
			'html'   => $html,
			'fields' => $fields ? $fields : array(),
		) );
	}

	/**
	 * GET /forms — Retrieve all billing forms.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_forms( $request ) {
		global $wpdb;
		$activator = new Booking_Management_Activator();
		$table     = $activator->get_db_table_name( 'BILLING_FORMS' );

		if ( empty( $table ) ) {
			return rest_ensure_response( array( 'forms' => array() ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$forms = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id ASC" );

		return rest_ensure_response( array(
			'forms' => $forms ? $forms : array(),
		) );
	}

	// ------------------------------------------------------------------
	// Voucher Listing Handler
	// ------------------------------------------------------------------

	/**
	 * GET /vouchers — Retrieve voucher listing.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_vouchers( $request ) {
		global $wpdb;
		$activator     = new Booking_Management_Activator();
		$table         = $activator->get_db_table_name( 'VOUCHERS' );
		$booking_table = $activator->get_db_table_name( 'BOOKING' );
		$page          = max( 1, $request->get_param( 'page' ) );
		$per_page      = min( 100, max( 1, $request->get_param( 'per_page' ) ) );
		$status        = $request->get_param( 'status' );

		if ( empty( $table ) ) {
			return rest_ensure_response( array( 'vouchers' => array(), 'total' => 0 ) );
		}

		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $status ) ) {
			$where[]  = 'v.status = %d';
			$values[] = absint( $status );
		}

		$where_clause = implode( ' AND ', $where );
		$offset       = ( $page - 1 ) * $per_page;

		$select_cols = 'v.id, v.code, v.booking_id, v.status, v.created_at, b.service_name';
		$join_sql    = "LEFT JOIN {$booking_table} b ON v.booking_id = b.id";

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names from get_db_table_name() are hardcoded
			$total = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$table} v {$join_sql} WHERE {$where_clause}", $values )
			);
			$values[] = $per_page;
			$values[] = $offset;
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results(
				$wpdb->prepare( "SELECT {$select_cols} FROM {$table} v {$join_sql} WHERE {$where_clause} ORDER BY v.id DESC LIMIT %d OFFSET %d", $values )
			);
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} v" );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results(
				$wpdb->prepare( "SELECT {$select_cols} FROM {$table} v {$join_sql} ORDER BY v.id DESC LIMIT %d OFFSET %d", $per_page, $offset )
			);
		}

		return rest_ensure_response( array(
			'vouchers' => $rows ? $rows : array(),
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per_page,
		) );
	}

	// ------------------------------------------------------------------
	// Customer Listing Handler
	// ------------------------------------------------------------------

	/**
	 * GET /customers — Retrieve customer email listing.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_customers( $request ) {
		global $wpdb;
		$activator = new Booking_Management_Activator();
		$table     = $activator->get_db_table_name( 'CUSTOMERS' );
		$page      = max( 1, $request->get_param( 'page' ) );
		$per_page  = min( 100, max( 1, $request->get_param( 'per_page' ) ) );
		$search    = $request->get_param( 'search' );

		if ( empty( $table ) ) {
			return rest_ensure_response( array( 'customers' => array(), 'total' => 0 ) );
		}

		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $search ) ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = 'customer_email LIKE %s';
			$values[] = $like;
		}

		$where_clause = implode( ' AND ', $where );
		$offset       = ( $page - 1 ) * $per_page;

		// Free version: only show email column.
		$select_cols = 'id, customer_email';

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where_clause}", $values )
			);
			$values[] = $per_page;
			$values[] = $offset;
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results(
				$wpdb->prepare( "SELECT {$select_cols} FROM {$table} WHERE {$where_clause} ORDER BY id DESC LIMIT %d OFFSET %d", $values )
			);
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results(
				$wpdb->prepare( "SELECT {$select_cols} FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset )
			);
		}

		return rest_ensure_response( array(
			'customers' => $rows ? $rows : array(),
			'total'     => $total,
			'page'      => $page,
			'per_page'  => $per_page,
		) );
	}

	// ------------------------------------------------------------------
	// Check-In Handlers
	// ------------------------------------------------------------------

	/**
	 * GET /checkins — Retrieve check-in listing.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_checkins( $request ) {
		global $wpdb;
		$activator = new Booking_Management_Activator();
		$table     = $activator->get_db_table_name( 'CHECKIN' );
		$page      = max( 1, $request->get_param( 'page' ) );
		$per_page  = min( 100, max( 1, $request->get_param( 'per_page' ) ) );
		$status    = $request->get_param( 'status' );
		$search    = $request->get_param( 'search' );

		if ( empty( $table ) ) {
			return rest_ensure_response( array( 'checkins' => array(), 'total' => 0 ) );
		}

		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $status ) ) {
			$where[]  = 'status = %s';
			$values[] = $status;
		}

		$where_clause = implode( ' AND ', $where );
		$offset       = ( $page - 1 ) * $per_page;

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where_clause}", $values )
			);
			$values[] = $per_page;
			$values[] = $offset;
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$table} WHERE {$where_clause} ORDER BY id DESC LIMIT %d OFFSET %d", $values )
			);
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset )
			);
		}

		return rest_ensure_response( array(
			'checkins' => $rows ? $rows : array(),
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per_page,
		) );
	}

	/**
	 * POST /checkins/{id} — Manual check-in for a booking.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function manual_checkin( $request ) {
		global $wpdb;
		$activator = new Booking_Management_Activator();
		$table     = $activator->get_db_table_name( 'CHECKIN' );
		$id        = $request->get_param( 'id' );

		if ( empty( $table ) ) {
			return new WP_Error( 'db_error', esc_html__( 'Database tables not found.', 'service-booking' ), array( 'status' => 500 ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$checkin = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id )
		);

		if ( ! $checkin ) {
			return new WP_Error( 'not_found', esc_html__( 'Check-in record not found.', 'service-booking' ), array( 'status' => 404 ) );
		}

		$wpdb->update(
			$table,
			array(
				'status'       => 'checked_in',
				'checkin_time' => current_time( 'mysql' ),
				'updated_at'   => current_time( 'mysql' ),
			),
			array( 'id' => $id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Check-in completed successfully.', 'service-booking' ),
		) );
	}

	// ------------------------------------------------------------------
	// Email Listing Handler
	// ------------------------------------------------------------------

	/**
	 * GET /emails — Retrieve email listing.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_emails( $request ) {
		global $wpdb;
		$activator = new Booking_Management_Activator();
		$table     = $activator->get_db_table_name( 'EMAILS' );
		$page      = max( 1, $request->get_param( 'page' ) );
		$per_page  = min( 100, max( 1, $request->get_param( 'per_page' ) ) );

		if ( empty( $table ) ) {
			return rest_ensure_response( array( 'emails' => array(), 'total' => 0 ) );
		}

		$offset = ( $page - 1 ) * $per_page;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, mail_to, mail_sub, created_at, status FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);

		return rest_ensure_response( array(
			'emails'   => $rows ? $rows : array(),
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per_page,
		) );
	}

	// ------------------------------------------------------------------
	// Email Records Handler (simplified read-only listing)
	// ------------------------------------------------------------------

	/**
	 * GET /email-records — Retrieve simplified email records listing.
	 *
	 * Returns: Recipient, Subject, Date, Status.
	 * No resend capability — that is Pro-only.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_email_records( $request ) {
		global $wpdb;
		$activator = new Booking_Management_Activator();
		$table     = $activator->get_db_table_name( 'EMAILS' );
		$page      = max( 1, $request->get_param( 'page' ) );
		$per_page  = min( 100, max( 1, $request->get_param( 'per_page' ) ) );
		$search    = $request->get_param( 'search' );

		if ( empty( $table ) ) {
			return rest_ensure_response( array( 'records' => array(), 'total' => 0 ) );
		}

		$offset = ( $page - 1 ) * $per_page;
		$where  = '';

		if ( ! empty( $search ) ) {
			$like   = '%' . $wpdb->esc_like( $search ) . '%';
			$where .= $wpdb->prepare( ' WHERE (mail_to LIKE %s OR mail_sub LIKE %s)', $like, $like );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}{$where}" );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, mail_to, mail_sub, created_at, status FROM {$table}{$where} ORDER BY id DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);

		return rest_ensure_response( array(
			'records'  => $rows ? $rows : array(),
			'total'    => $total,
			'page'     => $page,
			'per_page' => $per_page,
		) );
	}

	// ------------------------------------------------------------------
	// Dashboard Handler
	// ------------------------------------------------------------------

	/**
	 * GET /dashboard — Retrieve simplified dashboard metrics.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_dashboard( $request ) {
		global $wpdb;
		$activator    = new Booking_Management_Activator();
		$book_table   = $activator->get_db_table_name( 'BOOKING' );
		$cust_table   = $activator->get_db_table_name( 'CUSTOMERS' );

		$data = array(
			'total_bookings'    => 0,
			'total_customers'   => 0,
			'upcoming_bookings' => 0,
			'recent_orders'     => array(),
			'revenue'           => 0,
		);

		if ( ! empty( $book_table ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$data['total_bookings'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$book_table} WHERE is_active = 1" );

			$seven_days_later = gmdate( 'Y-m-d', strtotime( '+7 days' ) );
			$today            = gmdate( 'Y-m-d' );

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$data['upcoming_bookings'] = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$book_table} WHERE is_active = 1 AND booking_date >= %s AND booking_date <= %s",
					$today,
					$seven_days_later
				)
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$data['recent_orders'] = $wpdb->get_results(
				"SELECT id, service_name, booking_date, order_status, total_cost, booking_created_at FROM {$book_table} WHERE is_active = 1 ORDER BY id DESC LIMIT 10"
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$data['revenue'] = (float) $wpdb->get_var(
				"SELECT COALESCE(SUM(total_cost), 0) FROM {$book_table} WHERE is_active = 1 AND order_status IN ('booked', 'completed')"
			);
		}

		if ( ! empty( $cust_table ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$data['total_customers'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$cust_table}" );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * GET /dashboard/counts — Retrieve booking KPI counts.
	 *
	 * Accepts optional query params: year, month, type, status.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_dashboard_counts( $request ) {
		$bmrequests = new BM_Request();

		$allowed_types    = array( '', 'total', 'upcoming', 'revenue', 'weekly' );
		$allowed_statuses = array( 'booked', 'completed', 'cancelled', 'pending', '' );

		$type   = $request->get_param( 'type' ) ? sanitize_text_field( $request->get_param( 'type' ) ) : '';
		$year   = $request->get_param( 'year' ) ? absint( $request->get_param( 'year' ) ) : '';
		$month  = $request->get_param( 'month' ) ? absint( $request->get_param( 'month' ) ) : '';
		$status = $request->get_param( 'status' ) ? sanitize_text_field( $request->get_param( 'status' ) ) : '';

		if ( ! in_array( $type, $allowed_types, true ) ) {
			$type = '';
		}
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			$status = '';
		}

		$data = array();

		if ( '' === $type ) {
			$data['total_bookings_count']    = $bmrequests->bm_fetch_total_bookings_count( $year, $month, $status );
			$data['upcoming_bookings_count'] = $bmrequests->bm_fetch_upcoming_bookings_count( $year, $month, $status );
			$data['weekly_bookings_count']   = $bmrequests->bm_fetch_weekly_bookings_count( $status );
			$data['total_bookings_revenue']  = $bmrequests->bm_fetch_total_bookings_revenue( $year, $month, $status );
		} elseif ( 'total' === $type ) {
			$data['total_bookings_count'] = $bmrequests->bm_fetch_total_bookings_count( $year, $month, $status );
		} elseif ( 'upcoming' === $type ) {
			$data['upcoming_bookings_count'] = $bmrequests->bm_fetch_upcoming_bookings_count( $year, $month, $status );
		} elseif ( 'revenue' === $type ) {
			$data['total_bookings_revenue'] = $bmrequests->bm_fetch_total_bookings_revenue( $year, $month, $status );
		} elseif ( 'weekly' === $type ) {
			$data['weekly_bookings_count'] = $bmrequests->bm_fetch_weekly_bookings_count( $status );
		}

		$data['booking_type'] = $type;

		return rest_ensure_response( $data );
	}

	/**
	 * GET /dashboard/status-chart — Retrieve booking status chart data.
	 *
	 * Accepts optional query params: from, to.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_dashboard_status_chart( $request ) {
		$bmrequests = new BM_Request();

		$from = $request->get_param( 'from' ) ? sanitize_text_field( $request->get_param( 'from' ) ) : '';
		$to   = $request->get_param( 'to' ) ? sanitize_text_field( $request->get_param( 'to' ) ) : '';

		// Validate date format (DD/MM/YY as sent by the JS).
		$date_pattern = '/^\d{2}\/\d{2}\/\d{2}$/';
		if ( '' !== $from && ! preg_match( $date_pattern, $from ) ) {
			$from = '';
		}
		if ( '' !== $to && ! preg_match( $date_pattern, $to ) ) {
			$to = '';
		}

		$post = array(
			'type'   => 'monthly',
			'status' => 'order_status',
			'from'   => $from,
			'to'     => $to,
		);

		$status_data = $bmrequests->bm_fetch_booking_status_count( $post );

		$data = array(
			'labels' => isset( $status_data['labels'] ) ? $status_data['labels'] : array(),
			'data'   => isset( $status_data['data'] ) ? $status_data['data'] : array(),
		);

		return rest_ensure_response( $data );
	}

	// ------------------------------------------------------------------
	// Action Endpoint Handlers
	// ------------------------------------------------------------------

	/**
	 * PATCH /services/{id}/visibility — Toggle service frontend visibility.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function toggle_service_visibility( $request ) {
		$id      = $request->get_param( 'id' );
		$visible = $request->get_param( 'visible' );

		$dbhandler = new BM_DBhandler();
		$result    = $dbhandler->update_row( 'SERVICE', array( 'is_service_front' => $visible ), array( 'id' => $id ) );

		if ( false === $result ) {
			return new WP_Error( 'update_failed', esc_html__( 'Failed to update service visibility.', 'service-booking' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Service visibility updated.', 'service-booking' ),
		) );
	}

	/**
	 * PATCH /categories/{id}/visibility — Toggle category frontend visibility.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function toggle_category_visibility( $request ) {
		$id      = $request->get_param( 'id' );
		$visible = $request->get_param( 'visible' );

		$dbhandler = new BM_DBhandler();
		$result    = $dbhandler->update_row( 'CATEGORY', array( 'cat_in_front' => $visible ), array( 'id' => $id ) );

		if ( false === $result ) {
			return new WP_Error( 'update_failed', esc_html__( 'Failed to update category visibility.', 'service-booking' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Category visibility updated.', 'service-booking' ),
		) );
	}

	/**
	 * GET /categories — Retrieve categories listing.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_categories( $request ) {
		// Check cache for categories listing.
		if ( class_exists( 'SG_Cache_Manager' ) ) {
			$cache     = SG_Cache_Manager::get_instance();
			$cache_key = 'rest_v1_categories';
			$cached    = $cache->get( $cache_key );
			if ( false !== $cached ) {
				return rest_ensure_response( $cached );
			}
		}

		$dbhandler  = new BM_DBhandler();
		$categories = $dbhandler->get_all_result( 'CATEGORY', '*', 1, 'results', 0, false, 'cat_position', 'ASC' );

		$data = array();
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $cat ) {
				$data[] = array(
					'id'           => (int) $cat->id,
					'cat_name'     => $cat->cat_name,
					'cat_in_front' => (int) $cat->cat_in_front,
					'cat_position' => (int) $cat->cat_position,
				);
			}
		}

		/**
		 * Filters the categories list returned by the REST API.
		 *
		 * @since 1.2.0
		 * @param array           $data    The category items array.
		 * @param WP_REST_Request $request The original request object.
		 */
		$data = apply_filters( 'sg_booking_rest_categories', $data, $request );

		// Cache for 5 minutes.
		if ( class_exists( 'SG_Cache_Manager' ) ) {
			$cache->set( $cache_key, $data, 5 * MINUTE_IN_SECONDS );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * DELETE /categories/{id} — Delete a category.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_category( $request ) {
		$id = $request->get_param( 'id' );

		$dbhandler = new BM_DBhandler();
		$result    = $dbhandler->delete_row( 'CATEGORY', array( 'id' => $id ) );

		if ( false === $result ) {
			return new WP_Error( 'delete_failed', esc_html__( 'Failed to delete category.', 'service-booking' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Category deleted.', 'service-booking' ),
		) );
	}

	/**
	 * POST /services/reorder — Reorder services (drag-drop sort).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function reorder_services( $request ) {
		$items = $request->get_param( 'items' );

		if ( empty( $items ) || ! is_array( $items ) ) {
			return new WP_Error( 'invalid_data', esc_html__( 'No items provided.', 'service-booking' ), array( 'status' => 400 ) );
		}

		$dbhandler = new BM_DBhandler();
		foreach ( $items as $item ) {
			$service_id = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
			$position   = isset( $item['position'] ) ? absint( $item['position'] ) : 0;
			if ( $service_id > 0 ) {
				$dbhandler->update_row( 'SERVICE', array( 'service_position' => $position ), array( 'id' => $service_id ) );
			}
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Services reordered.', 'service-booking' ),
		) );
	}

	/**
	 * POST /categories/reorder — Reorder categories (drag-drop sort).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function reorder_categories( $request ) {
		$items = $request->get_param( 'items' );

		if ( empty( $items ) || ! is_array( $items ) ) {
			return new WP_Error( 'invalid_data', esc_html__( 'No items provided.', 'service-booking' ), array( 'status' => 400 ) );
		}

		$dbhandler = new BM_DBhandler();
		foreach ( $items as $item ) {
			$cat_id   = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
			$position = isset( $item['position'] ) ? absint( $item['position'] ) : 0;
			if ( $cat_id > 0 ) {
				$dbhandler->update_row( 'CATEGORY', array( 'cat_position' => $position ), array( 'id' => $cat_id ) );
			}
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Categories reordered.', 'service-booking' ),
		) );
	}

	/**
	 * PATCH /orders/{id}/status — Update order status.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_order_status( $request ) {
		$id     = $request->get_param( 'id' );
		$status = $request->get_param( 'status' );

		$allowed_statuses = array( 'booked', 'confirmed', 'completed', 'cancelled', 'pending', 'no_show' );
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			return new WP_Error( 'invalid_status', esc_html__( 'Invalid order status.', 'service-booking' ), array( 'status' => 400 ) );
		}

		$dbhandler = new BM_DBhandler();
		$result    = $dbhandler->update_row( 'BOOKING', array( 'order_status' => $status ), array( 'id' => $id ) );

		if ( false === $result ) {
			return new WP_Error( 'update_failed', esc_html__( 'Failed to update order status.', 'service-booking' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Order status updated.', 'service-booking' ),
		) );
	}

	/**
	 * POST /orders/{id}/archive — Archive an order.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function archive_order( $request ) {
		global $wpdb;
		$id        = $request->get_param( 'id' );
		$activator = new Booking_Management_Activator();
		$book_table    = $activator->get_db_table_name( 'BOOKING' );
		$archive_table = $activator->get_db_table_name( 'BOOKING_ARCHIVE' );

		if ( empty( $book_table ) || empty( $archive_table ) ) {
			return new WP_Error( 'db_error', esc_html__( 'Database tables not found.', 'service-booking' ), array( 'status' => 500 ) );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$book_table} WHERE id = %d", $id ), ARRAY_A );

		if ( ! $order ) {
			return new WP_Error( 'not_found', esc_html__( 'Order not found.', 'service-booking' ), array( 'status' => 404 ) );
		}

		unset( $order['id'] );
		$wpdb->insert( $archive_table, $order );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->delete( $book_table, array( 'id' => $id ) );

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Order archived.', 'service-booking' ),
		) );
	}

	/**
	 * DELETE /orders/{id} — Delete an order permanently.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_order( $request ) {
		$id = $request->get_param( 'id' );

		$dbhandler = new BM_DBhandler();
		$result    = $dbhandler->delete_row( 'BOOKING', array( 'id' => $id ) );

		if ( false === $result ) {
			return new WP_Error( 'delete_failed', esc_html__( 'Failed to delete order.', 'service-booking' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Order deleted.', 'service-booking' ),
		) );
	}

	/**
	 * PATCH /templates/{id}/visibility — Toggle email template status.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function toggle_template_visibility( $request ) {
		$id      = $request->get_param( 'id' );
		$visible = $request->get_param( 'visible' );

		$dbhandler = new BM_DBhandler();
		$result    = $dbhandler->update_row( 'EMAIL_TMPL', array( 'status' => $visible ), array( 'id' => $id ) );

		if ( false === $result ) {
			return new WP_Error( 'update_failed', esc_html__( 'Failed to update template visibility.', 'service-booking' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Template visibility updated.', 'service-booking' ),
		) );
	}

	/**
	 * DELETE /templates/{id} — Delete an email template.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_template( $request ) {
		$id = $request->get_param( 'id' );

		$dbhandler = new BM_DBhandler();
		$result    = $dbhandler->delete_row( 'EMAIL_TMPL', array( 'id' => $id ) );

		if ( false === $result ) {
			return new WP_Error( 'delete_failed', esc_html__( 'Failed to delete template.', 'service-booking' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => esc_html__( 'Template deleted.', 'service-booking' ),
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

	// ------------------------------------------------------------------
	// Check-in REST handlers
	// ------------------------------------------------------------------

	/**
	 * POST /checkins/process — Manual check-in for one or more bookings.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function rest_manual_checkin_process( $request ) {
		$search_type = $request->get_param( 'search_type' );
		$raw_value   = $request->get_param( 'search_value' );

		if ( is_array( $raw_value ) ) {
			$search_value = array_map( 'sanitize_text_field', $raw_value );
		} else {
			$search_value = sanitize_text_field( $raw_value );
		}

		$booking_ids = $request->get_param( 'booking_ids' );
		if ( ! is_array( $booking_ids ) ) {
			$booking_ids = array();
		}

		$db = new BM_DBhandler();

		if ( 'reference' === $search_type ) {
			$booking_id = $db->get_value( 'BOOKING', 'id', $search_value, 'booking_key' );
			$is_active  = $db->get_value( 'BOOKING', 'is_active', $search_value, 'booking_key' );

			if ( 1 != $is_active ) {
				return new WP_Error( 'invalid_booking', __( 'Can not check in cancelled or refunded orders', 'service-booking' ), array( 'status' => 400 ) );
			}

			if ( ! $booking_id ) {
				return new WP_Error( 'not_found', __( 'Booking not found', 'service-booking' ), array( 'status' => 404 ) );
			}

			$success = $this->mark_booking_checked_in( (int) $booking_id, $db );
			if ( ! $success ) {
				return new WP_Error( 'already_checked', __( 'Already checked in or expired.', 'service-booking' ), array( 'status' => 400 ) );
			}

			return rest_ensure_response( array( 'message' => __( 'Booking successfully checked in.', 'service-booking' ) ) );
		}

		if ( empty( $booking_ids ) ) {
			return new WP_Error( 'no_selection', __( 'No bookings selected.', 'service-booking' ), array( 'status' => 400 ) );
		}

		$count = 0;
		foreach ( $booking_ids as $id ) {
			$is_active = $db->get_value( 'BOOKING', 'is_active', $id, 'id' );
			if ( 1 != $is_active ) {
				continue;
			}
			if ( $this->mark_booking_checked_in( (int) $id, $db ) ) {
				++$count;
			}
		}

		if ( 0 === $count ) {
			return new WP_Error( 'none_checked', __( 'No valid bookings were checked in.', 'service-booking' ), array( 'status' => 400 ) );
		}

		return rest_ensure_response(
			array(
				/* translators: %d: number of bookings */
				'message' => sprintf( __( '%d bookings successfully checked in.', 'service-booking' ), $count ),
			)
		);
	}

	/**
	 * POST /checkins/search — Search bookings for manual check-in modal.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function rest_manual_checkin_check( $request ) {
		$search_type = $request->get_param( 'search_type' );
		$raw_value   = $request->get_param( 'search_value' );

		if ( is_array( $raw_value ) ) {
			$search_value = array_map( 'sanitize_text_field', $raw_value );
		} else {
			$search_value = sanitize_text_field( $raw_value );
		}

		if ( empty( $search_type ) || empty( $search_value ) ) {
			return new WP_Error( 'invalid_params', __( 'Invalid search parameters', 'service-booking' ), array( 'status' => 400 ) );
		}

		$db         = new BM_DBhandler();
		$bmrequests = new BM_Request();

		if ( 'reference' === $search_type ) {
			$booking_id = $db->get_value( 'BOOKING', 'id', $search_value, 'booking_key' );
			if ( ! $booking_id ) {
				return new WP_Error( 'not_found', __( 'Booking not found', 'service-booking' ), array( 'status' => 404 ) );
			}

			$html = $bmrequests->bm_get_order_details_attachment( (int) $booking_id, false, false );
			if ( empty( $html ) ) {
				return new WP_Error( 'no_data', __( 'Booking data not found', 'service-booking' ), array( 'status' => 404 ) );
			}

			return rest_ensure_response( array( 'html' => $html ) );
		}

		$joins = array(
			array(
				'table' => 'CUSTOMERS',
				'alias' => 'c',
				'on'    => 'c.id = b.customer_id',
				'type'  => 'LEFT',
			),
			array(
				'table' => 'CHECKIN',
				'alias' => 'ch',
				'on'    => 'ch.booking_id = b.id',
				'type'  => 'LEFT',
			),
		);

		if ( 'email' === $search_type ) {
			$where = array( 'c.customer_email' => array( '=' => $search_value ) );
		} elseif ( 'service' === $search_type ) {
			$where = array( 'b.service_id' => array( 'IN' => $search_value ) );
		} else {
			$where = array(
				'c.customer_name' => array(
					'LIKE' => '%' . $search_value,
				),
			);
		}

		$results = $db->get_results_with_join(
			array( 'BOOKING', 'b' ),
			'b.id, b.service_id, b.service_name, b.total_svc_slots as svc_participants, b.total_ext_svc_slots as ex_svc_participants, b.booking_key, c.customer_email, c.billing_details, ch.qr_scanned, ch.checkin_time',
			$joins,
			$where,
			'results'
		);

		if ( ! $results || count( $results ) === 0 ) {
			return new WP_Error( 'no_bookings', __( 'No bookings found', 'service-booking' ), array( 'status' => 404 ) );
		}

		ob_start();
		?>
		<div class="bm-bookings-list">
			<table class="manual_checkin_records_table widefat striped">
				<thead>
					<tr>
						<th><input type="checkbox" id="bm-checkall"></th>
						<th><?php esc_html_e( 'Booking Key', 'service-booking' ); ?></th>
						<th><?php esc_html_e( 'Service Name', 'service-booking' ); ?></th>
						<?php if ( 'email' === $search_type ) : ?>
							<th><?php esc_html_e( 'Email', 'service-booking' ); ?></th>
						<?php else : ?>
							<th><?php esc_html_e( 'First Name', 'service-booking' ); ?></th>
							<th><?php esc_html_e( 'Last Name', 'service-booking' ); ?></th>
						<?php endif; ?>
						<th><?php esc_html_e( 'Service Participants', 'service-booking' ); ?></th>
						<th><?php esc_html_e( 'Extra Service Participants', 'service-booking' ); ?></th>
						<th><?php esc_html_e( 'Check-in Status', 'service-booking' ); ?></th>
						<th><?php esc_html_e( 'Check-in Date', 'service-booking' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'service-booking' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $results as $row ) :
					$first_name = '';
					$last_name  = '';
					if ( ! empty( $row->billing_details ) ) {
						$details = maybe_unserialize( $row->billing_details );
						if ( is_array( $details ) ) {
							$first_name = esc_html( $details['billing_first_name'] ?? '' );
							$last_name  = esc_html( $details['billing_last_name'] ?? '' );
						}
					}
					$status = ( 1 == $row->qr_scanned ) ? __( 'Checked-in', 'service-booking' ) : __( 'Pending', 'service-booking' );
					$date   = ! empty( $row->checkin_time ) ? $bmrequests->bm_convert_date_format( $row->checkin_time, 'Y-m-d H:i:s', 'd/m/y H:i' ) : '-';
					?>
					<tr>
						<td><input type="checkbox" class="bm-booking-select" value="<?php echo esc_attr( $row->id ); ?>"></td>
						<td><?php echo esc_html( $row->booking_key ); ?></td>
						<td><?php echo esc_html( $row->service_name ); ?></td>
						<?php if ( 'email' === $search_type ) : ?>
							<td><?php echo esc_html( $row->customer_email ); ?></td>
						<?php else : ?>
							<td><?php echo esc_html( $first_name ); ?></td>
							<td><?php echo esc_html( $last_name ); ?></td>
						<?php endif; ?>
						<td><?php echo esc_html( $row->svc_participants ); ?></td>
						<td><?php echo esc_html( $row->ex_svc_participants ); ?></td>
						<td><?php echo esc_html( $status ); ?></td>
						<td><?php echo esc_html( $date ); ?></td>
						<td>
							<div class="bm-view-details" data-id="<?php echo esc_attr( $row->id ); ?>">
								<i class="fa fa-eye"></i> <?php esc_html_e( 'View', 'service-booking' ); ?>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
		$html = ob_get_clean();

		return rest_ensure_response( array( 'html' => $html ) );
	}

	/**
	 * POST /checkins/status — Update or create a check-in record status.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function rest_update_checkin_status( $request ) {
		$checkin_id = $request->get_param( 'checkin_id' );
		$status     = $request->get_param( 'new_status' );
		$booking_id = $request->get_param( 'booking_id' );

		$dbhandler = new BM_DBhandler();
		$checkin   = $checkin_id ? $dbhandler->get_row( 'CHECKIN', $checkin_id, 'id' ) : null;

		$data = array(
			'status'     => $status,
			'updated_at' => current_time( 'mysql' ),
		);

		if ( 'checked_in' === $status ) {
			$data['checkin_time'] = current_time( 'mysql' );
		} else {
			$data['checkin_time'] = null;
		}

		if ( $checkin ) {
			$updated = $dbhandler->update_row( 'CHECKIN', 'id', $checkin_id, $data );
		} else {
			if ( ! $booking_id ) {
				return new WP_Error( 'missing_booking', esc_html__( 'Booking ID required to create checkin record.', 'service-booking' ), array( 'status' => 400 ) );
			}

			$data['booking_id'] = $booking_id;
			$data['qr_token']   = $dbhandler->get_value( 'BOOKING', 'booking_key', $booking_id, 'id' );
			$data['qr_scanned'] = ( 'checked_in' === $status ) ? 1 : 0;
			$data['created_at'] = current_time( 'mysql' );

			$updated = $dbhandler->insert_row( 'CHECKIN', $data );
		}

		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Unable to update or create checkin.', 'service-booking' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * GET /checkins/details/{booking_id} — View order details for check-in.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function rest_manual_checkin_view_details( $request ) {
		$booking_id = $request->get_param( 'booking_id' );

		if ( ! $booking_id ) {
			return new WP_Error( 'invalid_id', __( 'Invalid booking ID', 'service-booking' ), array( 'status' => 400 ) );
		}

		$html = ( new BM_Request() )->bm_get_order_details_attachment( (int) $booking_id, false, false );

		if ( empty( $html ) ) {
			return new WP_Error( 'no_data', __( 'Booking data not found', 'service-booking' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( array( 'html' => $html ) );
	}

	/**
	 * Mark a booking as checked in.
	 *
	 * @param int          $booking_id Booking ID.
	 * @param BM_DBhandler $db         DB handler instance.
	 * @return bool
	 */
	private function mark_booking_checked_in( int $booking_id, BM_DBhandler $db ): bool {
		$now = ( new BM_Request() )->bm_fetch_current_wordpress_datetime_stamp();

		$data = array(
			'qr_scanned'   => 1,
			'status'       => 'checked_in',
			'qr_token'     => $db->get_value( 'BOOKING', 'booking_key', $booking_id, 'id' ),
			'booking_id'   => $booking_id,
			'checkin_time' => $now,
			'updated_at'   => $now,
		);

		$existing = $db->get_value( 'CHECKIN', 'id', $booking_id, 'booking_id' );

		if ( $existing ) {
			return $db->update_row( 'CHECKIN', 'booking_id', $booking_id, $data );
		} else {
			return $db->insert_row( 'CHECKIN', $data );
		}
	}
}

// Bootstrap the REST API.
new Booking_Management_Rest_API();
