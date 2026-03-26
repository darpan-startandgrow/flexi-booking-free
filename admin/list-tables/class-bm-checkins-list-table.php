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
	}

	/**
	 * Define columns — simplified for free version.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />',
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
	 * Checkbox column for bulk actions.
	 *
	 * @param array $item Row data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="checkin_ids[]" value="%s" />', esc_attr( $item['id'] ) );
	}

	/**
	 * Bulk actions available in the dropdown.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'bulk-checkin' => esc_html__( 'Check In', 'service-booking' ),
		);
	}

	/**
	 * Process bulk actions.
	 */
	public function process_bulk_action() {
		if ( 'bulk-checkin' !== $this->current_action() ) {
			return;
		}

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-checkins' ) ) {
			return;
		}

		$ids = isset( $_REQUEST['checkin_ids'] ) ? array_map( 'absint', (array) $_REQUEST['checkin_ids'] ) : array();
		if ( empty( $ids ) ) {
			return;
		}

		foreach ( $ids as $id ) {
			if ( $id > 0 ) {
				$this->dbhandler->update_row( 'CHECKIN', 'id', $id, array( 'status' => 'checked_in' ), null, '%d' );
			}
		}
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
	 * Extra table nav with filters.
	 *
	 * @param string $which 'top' or 'bottom'.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$service_filter = isset( $_REQUEST['service_filter'] ) ? absint( $_REQUEST['service_filter'] ) : '';
		$status_filter  = isset( $_REQUEST['checkin_status_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['checkin_status_filter'] ) ) : '';

		// Fetch unique services from check-in data.
		$all_checkins    = $this->bmrequests->bm_fetch_all_order_checkins();
		$unique_services = array();
		$added_ids       = array();
		if ( ! empty( $all_checkins ) && is_array( $all_checkins ) ) {
			foreach ( $all_checkins as $checkin ) {
				if ( ! empty( $checkin['service_id'] ) && ! in_array( $checkin['service_id'], $added_ids, true ) ) {
					$unique_services[] = array(
						'service_id'   => $checkin['service_id'],
						'service_name' => $checkin['service_name'],
					);
					$added_ids[] = $checkin['service_id'];
				}
			}
		}

		echo '<div class="alignleft actions">';

		// Service filter.
		echo '<select name="service_filter">';
		echo '<option value="">' . esc_html__( 'All Services', 'service-booking' ) . '</option>';
		foreach ( $unique_services as $svc ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $svc['service_id'] ),
				selected( $service_filter, $svc['service_id'], false ),
				esc_html( $svc['service_name'] )
			);
		}
		echo '</select>';

		// Status filter.
		echo '<select name="checkin_status_filter">';
		echo '<option value="">' . esc_html__( 'All Statuses', 'service-booking' ) . '</option>';
		printf( '<option value="checked_in"%s>%s</option>', selected( $status_filter, 'checked_in', false ), esc_html__( 'Checked In', 'service-booking' ) );
		printf( '<option value="pending"%s>%s</option>', selected( $status_filter, 'pending', false ), esc_html__( 'Pending', 'service-booking' ) );
		echo '</select>';

		submit_button( __( 'Filter', 'service-booking' ), '', 'filter_action', false );
		echo '</div>';
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$per_page = ! empty( $_REQUEST['per_page'] )
			? absint( $_REQUEST['per_page'] )
			: ( ! empty( $this->dbhandler->get_global_option_value( 'bm_checkins_per_page' ) )
				? absint( $this->dbhandler->get_global_option_value( 'bm_checkins_per_page' ) )
				: 10 );

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
							isset( $checkin['email_address'] ) ? $checkin['email_address'] : '',
						)
					);
					return stripos( $searchable, $search ) !== false;
				}
			);
			$total = count( $all_checkins );
		}

		// Apply service filter.
		if ( isset( $_REQUEST['service_filter'] ) && '' !== $_REQUEST['service_filter'] ) {
			$svc_id = absint( $_REQUEST['service_filter'] );
			$all_checkins = array_filter(
				$all_checkins,
				function ( $checkin ) use ( $svc_id ) {
					return isset( $checkin['service_id'] ) && (int) $checkin['service_id'] === $svc_id;
				}
			);
			$total = count( $all_checkins );
		}

		// Apply status filter.
		if ( isset( $_REQUEST['checkin_status_filter'] ) && '' !== $_REQUEST['checkin_status_filter'] ) {
			$status_val = sanitize_text_field( wp_unslash( $_REQUEST['checkin_status_filter'] ) );
			$all_checkins = array_filter(
				$all_checkins,
				function ( $checkin ) use ( $status_val ) {
					return isset( $checkin['checkin_status'] ) && $checkin['checkin_status'] === $status_val;
				}
			);
			$total = count( $all_checkins );
		}

		// Re-index after filtering.
		$all_checkins = array_values( $all_checkins );

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
					'email'          => isset( $checkin['email_address'] ) ? $checkin['email_address'] : '',
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
