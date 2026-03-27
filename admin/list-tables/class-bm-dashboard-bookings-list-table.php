<?php
/**
 * Dashboard Bookings List Table.
 *
 * Uses the WP_List_Table class to render dashboard bookings
 * with server-side pagination, sorting, search, and filters.
 *
 * @since      1.3.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/list-tables
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class BM_Dashboard_Bookings_List_Table extends WP_List_Table {

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
	 * Table display mode: 'all' or 'upcoming'.
	 *
	 * @var string
	 */
	private $mode;

	/**
	 * Unique customer count from the current filtered dataset.
	 *
	 * @var int
	 */
	private $customer_count = 0;

	/**
	 * Constructor.
	 *
	 * @param string $mode Display mode — 'all' or 'upcoming'.
	 */
	public function __construct( $mode = 'all' ) {
		parent::__construct(
			array(
				'singular' => 'dashboard-booking',
				'plural'   => 'dashboard-bookings',
				'ajax'     => false,
			)
		);
		$this->mode       = in_array( $mode, array( 'all', 'upcoming' ), true ) ? $mode : 'all';
		$this->dbhandler  = new BM_DBhandler();
		$this->bmrequests = new BM_Request();
	}

	/**
	 * Define columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'order_id'     => esc_html__( 'Order ID', 'service-booking' ),
			'service_name' => esc_html__( 'Service', 'service-booking' ),
			'first_name'   => esc_html__( 'Customer', 'service-booking' ),
			'email'        => esc_html__( 'Email', 'service-booking' ),
			'booking_date' => esc_html__( 'Booking Date', 'service-booking' ),
			'total_cost'   => esc_html__( 'Total Cost', 'service-booking' ),
			'order_status' => esc_html__( 'Status', 'service-booking' ),
			'actions'      => esc_html__( 'Actions', 'service-booking' ),
		);
	}

	/**
	 * No bulk actions — read-only dashboard view.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'order_id'     => array( 'id', true ),
			'booking_date' => array( 'booking_date', false ),
			'total_cost'   => array( 'total_cost', false ),
			'order_status' => array( 'order_status', false ),
		);
	}

	/**
	 * Extra table nav — status dropdown, date range, and search.
	 *
	 * @param string $which 'top' or 'bottom'.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$status_filter = isset( $_REQUEST['order_status_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order_status_filter'] ) ) : '';
		$date_from     = isset( $_REQUEST['date_from'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['date_from'] ) ) : '';
		$date_to       = isset( $_REQUEST['date_to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['date_to'] ) ) : '';
		$search        = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		$statuses      = $this->bmrequests->bm_fetch_order_status_key_value();
		unset( $statuses['failed'] );

		echo '<div class="alignleft actions">';

		// Status filter.
		echo '<select name="order_status_filter">';
		echo '<option value="">' . esc_html__( 'All Statuses', 'service-booking' ) . '</option>';
		foreach ( $statuses as $key => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $key ),
				selected( $status_filter, $key, false ),
				esc_html( $label )
			);
		}
		echo '</select>';

		// Date range filters.
		printf(
			'<input type="date" name="date_from" value="%s" placeholder="%s" />',
			esc_attr( $date_from ),
			esc_attr__( 'From', 'service-booking' )
		);
		printf(
			'<input type="date" name="date_to" value="%s" placeholder="%s" />',
			esc_attr( $date_to ),
			esc_attr__( 'To', 'service-booking' )
		);

		// Search input.
		printf(
			'<input type="search" name="s" value="%s" placeholder="%s" />',
			esc_attr( $search ),
			esc_attr__( 'Search...', 'service-booking' )
		);

		submit_button( __( 'Search', 'service-booking' ), '', 'filter_action', false );
		echo '</div>';
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$per_page     = $this->get_items_per_page( 'bm_list_per_page', 10 );
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$table_key  = 'BOOKING';
		$where      = 1;
		$additional = '';

		// Upcoming mode: restrict to bookings from today onwards.
		if ( 'upcoming' === $this->mode ) {
			$timezone = $this->dbhandler->get_global_option_value( 'bm_booking_time_zone', 'Asia/Kolkata' );
			try {
				$tz = new DateTimeZone( $timezone );
			} catch ( Exception $e ) {
				$tz = new DateTimeZone( 'Asia/Kolkata' );
			}
			$today      = ( new DateTime( 'now', $tz ) )->format( 'Y-m-d' );
			$additional .= $GLOBALS['wpdb']->prepare( ' AND booking_date >= %s', $today );
		}

		// Status filter.
		if ( ! empty( $_REQUEST['order_status_filter'] ) ) {
			$status_val  = sanitize_text_field( wp_unslash( $_REQUEST['order_status_filter'] ) );
			$additional .= $GLOBALS['wpdb']->prepare( ' AND order_status = %s', $status_val );
		}

		// Date range filter on booking_date.
		if ( ! empty( $_REQUEST['date_from'] ) ) {
			$date_from   = sanitize_text_field( wp_unslash( $_REQUEST['date_from'] ) );
			$additional .= $GLOBALS['wpdb']->prepare( ' AND booking_date >= %s', $date_from );
		}
		if ( ! empty( $_REQUEST['date_to'] ) ) {
			$date_to     = sanitize_text_field( wp_unslash( $_REQUEST['date_to'] ) );
			$additional .= $GLOBALS['wpdb']->prepare( ' AND booking_date <= %s', $date_to );
		}

		// Search across service_name and field_values.
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search      = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
			$like        = '%' . $GLOBALS['wpdb']->esc_like( $search ) . '%';
			$additional .= $GLOBALS['wpdb']->prepare( ' AND (service_name LIKE %s OR field_values LIKE %s)', $like, $like );
		}

		// Count with filters.
		$count_results = $this->dbhandler->get_all_result( $table_key, 'id, field_values', $where, 'results', 0, false, 'id', 'ASC', $additional );
		$total         = is_array( $count_results ) ? count( $count_results ) : 0;

		// Compute unique customer count from the filtered dataset.
		$unique_emails = array();
		if ( is_array( $count_results ) ) {
			foreach ( $count_results as $row ) {
				if ( ! empty( $row->field_values ) ) {
					$values = json_decode( $row->field_values, true );
					if ( is_array( $values ) ) {
						foreach ( $values as $field ) {
							if ( isset( $field['field_key'] ) && ( strpos( $field['field_key'], 'email' ) !== false || 'sgbm_field_3' === $field['field_key'] ) ) {
								$val = isset( $field['field_value'] ) ? strtolower( trim( $field['field_value'] ) ) : '';
								if ( '' !== $val ) {
									$unique_emails[ $val ] = true;
								}
							}
						}
					}
				}
			}
		}
		$this->customer_count = count( $unique_emails );

		// Sorting.
		$default_order = ( 'upcoming' === $this->mode ) ? 'ASC' : 'DESC';
		$orderby       = 'booking_date';
		$order         = $default_order;

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$allowed     = array( 'id', 'booking_date', 'total_cost', 'order_status' );
			$req_orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
			if ( in_array( $req_orderby, $allowed, true ) ) {
				$orderby = $req_orderby;
			}
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			$order = ( 'asc' === strtolower( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) ) ? 'ASC' : 'DESC';
		}

		$bookings = $this->dbhandler->get_all_result( $table_key, '*', $where, 'results', $offset, $per_page, $orderby, $order, $additional );

		$this->items = array();
		if ( ! empty( $bookings ) ) {
			foreach ( $bookings as $row ) {
				// Extract first name and email from field_values.
				$first_name = '';
				$email      = '';
				if ( ! empty( $row->field_values ) ) {
					$values = json_decode( $row->field_values, true );
					if ( is_array( $values ) ) {
						foreach ( $values as $field ) {
							if ( isset( $field['field_key'] ) ) {
								if ( strpos( $field['field_key'], 'first_name' ) !== false || $field['field_key'] === 'sgbm_field_1' ) {
									$first_name = isset( $field['field_value'] ) ? $field['field_value'] : '';
								}
								if ( strpos( $field['field_key'], 'email' ) !== false || $field['field_key'] === 'sgbm_field_3' ) {
									$email = isset( $field['field_value'] ) ? $field['field_value'] : '';
								}
							}
						}
					}
				}

				$this->items[] = array(
					'id'           => $row->id,
					'order_id'     => $row->id,
					'service_name' => isset( $row->service_name ) ? $row->service_name : '',
					'booking_date' => isset( $row->booking_date ) ? $row->booking_date : '',
					'first_name'   => $first_name,
					'email'        => $email,
					'total_cost'   => isset( $row->total_cost ) ? $row->total_cost : '0',
					'order_status' => isset( $row->order_status ) ? $row->order_status : '',
				);
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
			case 'order_id':
				return sprintf(
					'<a href="admin.php?page=bm_single_order&id=%s" title="%s">%s</a>',
					esc_attr( $item['id'] ),
					esc_attr__( 'View Order', 'service-booking' ),
					esc_html( $item['id'] )
				);

			case 'service_name':
				return sprintf(
					'<span title="%s">%s</span>',
					esc_attr( $item['service_name'] ),
					esc_html( mb_strimwidth( $item['service_name'], 0, 30, '...' ) )
				);

			case 'first_name':
				return esc_html( $item['first_name'] );

			case 'email':
				return esc_html( $item['email'] );

			case 'booking_date':
				return ! empty( $item['booking_date'] )
					? esc_html( $this->bmrequests->bm_convert_date_format( $item['booking_date'], 'Y-m-d', 'd/m/Y' ) )
					: '';

			case 'total_cost':
				return esc_html( $item['total_cost'] );

			case 'order_status':
				$status_label = $this->bmrequests->bm_fetch_order_status_name( $item['order_status'] );
				return sprintf(
					'<span class="sg-order-status sg-status-%s">%s</span>',
					esc_attr( $item['order_status'] ),
					esc_html( $status_label )
				);

			case 'actions':
				return sprintf(
					'<a href="admin.php?page=bm_single_order&id=%s" class="edit-button bm-action-view" title="%s"><i class="fa fa-eye" aria-hidden="true"></i></a>',
					esc_attr( $item['id'] ),
					esc_attr__( 'View', 'service-booking' )
				);

			default:
				return '';
		}
	}

	/**
	 * Return unique customer count from the current filtered dataset.
	 *
	 * @return int
	 */
	public function get_customer_count() {
		return $this->customer_count;
	}

	/**
	 * Message when no items are found.
	 */
	public function no_items() {
		if ( 'upcoming' === $this->mode ) {
			esc_html_e( 'No Upcoming Bookings Found', 'service-booking' );
		} else {
			esc_html_e( 'No Bookings Found', 'service-booking' );
		}
	}
}
