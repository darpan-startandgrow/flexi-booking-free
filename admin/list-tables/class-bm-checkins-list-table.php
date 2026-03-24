<?php
/**
 * Check-ins List Table.
 *
 * Uses the WP_List_Table class to render the check-ins listing
 * with server-side pagination and manual check-in support.
 * Free version: manual check-in only (no QR scanner, no resend ticket, no manage columns).
 *
 * @since      1.2.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/list-tables
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class BM_Checkins_List_Table extends WP_List_Table {

	/**
	 * DB handler instance.
	 *
	 * @var BM_DBhandler
	 */
	private $dbhandler;

	/**
	 * Request helper instance.
	 *
	 * @var BM_Request
	 */
	private $bmrequests;

	/**
	 * Whether Pro is active.
	 *
	 * @var bool
	 */
	private $is_pro;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'checkin',
				'plural'   => 'checkins',
				'ajax'     => false,
			)
		);
		$this->dbhandler  = new BM_DBhandler();
		$this->bmrequests = new BM_Request();
		$this->is_pro     = Booking_Management_Limits::is_pro_active();
	}

	/**
	 * Define columns — simplified for free version.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'serial'         => esc_html__( '#', 'service-booking' ),
			'service_name'   => esc_html__( 'Ordered Service', 'service-booking' ),
			'booking_date'   => esc_html__( 'Service Date', 'service-booking' ),
			'first_name'     => esc_html__( 'Attendee First Name', 'service-booking' ),
			'last_name'      => esc_html__( 'Attendee Last Name', 'service-booking' ),
			'email'          => esc_html__( 'Attendee Email', 'service-booking' ),
			'checkin_time'   => esc_html__( 'Check-in Time', 'service-booking' ),
			'checkin_status' => esc_html__( 'Check-in Status', 'service-booking' ),
			'total_cost'     => esc_html__( 'Order Cost', 'service-booking' ),
			'actions'        => esc_html__( 'Actions', 'service-booking' ),
		);

		return $columns;
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'service_name'   => array( 'service_name', false ),
			'booking_date'   => array( 'booking_date', false ),
			'checkin_time'   => array( 'checkin_time', true ),
			'checkin_status' => array( 'checkin_status', false ),
		);
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$per_page = ! empty( $this->dbhandler->get_global_option_value( 'bm_checkins_per_page' ) )
			? absint( $this->dbhandler->get_global_option_value( 'bm_checkins_per_page' ) )
			: 10;

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// Fetch all check-ins via the existing helper method.
		$all_checkins = $this->bmrequests->bm_fetch_all_order_checkins();
		$total        = ! empty( $all_checkins ) && is_array( $all_checkins ) ? count( $all_checkins ) : 0;

		// Apply search filter if provided.
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		if ( ! empty( $search ) && ! empty( $all_checkins ) ) {
			$all_checkins = array_filter(
				$all_checkins,
				function ( $checkin ) use ( $search ) {
					$searchable = implode(
						' ',
						array(
							isset( $checkin['service_name'] ) ? $checkin['service_name'] : '',
							isset( $checkin['first_name'] ) ? $checkin['first_name'] : '',
							isset( $checkin['last_name'] ) ? $checkin['last_name'] : '',
							isset( $checkin['email'] ) ? $checkin['email'] : '',
						)
					);
					return stripos( $searchable, $search ) !== false;
				}
			);
			$total = count( $all_checkins );
		}

		// Paginate.
		$paged_checkins = array_slice( $all_checkins, $offset, $per_page );

		$this->items = array();
		if ( ! empty( $paged_checkins ) ) {
			$i = 1 + $offset;
			foreach ( $paged_checkins as $checkin ) {
				$this->items[] = array(
					'id'             => isset( $checkin['checkin_id'] ) ? $checkin['checkin_id'] : ( isset( $checkin['id'] ) ? $checkin['id'] : 0 ),
					'booking_id'     => isset( $checkin['booking_id'] ) ? $checkin['booking_id'] : 0,
					'serial'         => $i,
					'service_name'   => isset( $checkin['service_name'] ) ? $checkin['service_name'] : '',
					'booking_date'   => isset( $checkin['booking_date'] ) ? $checkin['booking_date'] : '',
					'first_name'     => isset( $checkin['first_name'] ) ? $checkin['first_name'] : '',
					'last_name'      => isset( $checkin['last_name'] ) ? $checkin['last_name'] : '',
					'email'          => isset( $checkin['email'] ) ? $checkin['email'] : '',
					'checkin_time'   => isset( $checkin['checkin_time'] ) ? $checkin['checkin_time'] : '',
					'checkin_status' => isset( $checkin['checkin_status'] ) ? $checkin['checkin_status'] : 'pending',
					'total_cost'     => isset( $checkin['total_cost'] ) ? $checkin['total_cost'] : '0',
				);
				$i++;
			}
		}

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total / max( 1, $per_page ) ),
			)
		);

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);
	}

	/**
	 * Default column renderer.
	 *
	 * @param array  $item        Row data.
	 * @param string $column_name Column key.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'serial':
				return esc_html( $item['serial'] );

			case 'service_name':
				return sprintf(
					'<span title="%s">%s</span>',
					esc_attr( $item['service_name'] ),
					esc_html( mb_strimwidth( $item['service_name'], 0, 30, '...' ) )
				);

			case 'booking_date':
				return ! empty( $item['booking_date'] )
					? esc_html( $this->bmrequests->bm_convert_date_format( $item['booking_date'], 'Y-m-d', 'd/m/Y' ) )
					: '';

			case 'first_name':
				return esc_html( $item['first_name'] );

			case 'last_name':
				return esc_html( $item['last_name'] );

			case 'email':
				return esc_html( $item['email'] );

			case 'checkin_time':
				return ! empty( $item['checkin_time'] )
					? esc_html( $this->bmrequests->bm_convert_date_format( $item['checkin_time'], 'Y-m-d H:i:s', 'd/m/Y H:i' ) )
					: '—';

			case 'checkin_status':
				$status = $item['checkin_status'];
				$label  = ucfirst( str_replace( '_', ' ', $status ) );
				$class  = ( 'checked_in' === $status ) ? 'color: green;' : 'color: #999;';
				return sprintf( '<span style="%s font-weight:600;">%s</span>', esc_attr( $class ), esc_html( $label ) );

			case 'total_cost':
				return esc_html( $item['total_cost'] );

			case 'actions':
				$checkin_btn = '';
				if ( 'checked_in' !== $item['checkin_status'] ) {
					$checkin_btn = sprintf(
						'<button type="button" class="button button-small bm-checkin-action" data-id="%s" title="%s"><span class="dashicons dashicons-yes" style="vertical-align:middle;"></span></button>',
						esc_attr( $item['id'] ),
						esc_attr__( 'Check In', 'service-booking' )
					);
				}
				return $checkin_btn;

			default:
				return '';
		}
	}

	/**
	 * Message when no items are found.
	 */
	public function no_items() {
		esc_html_e( 'No Check-ins Found', 'service-booking' );
	}
}
