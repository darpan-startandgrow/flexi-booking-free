<?php
/**
 * Orders List Table.
 *
 * Uses the WP_List_Table class to render the orders listing
 * with server-side pagination, sorting, search, and filters.
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

class BM_Orders_List_Table extends WP_List_Table {

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
				'singular' => 'order',
				'plural'   => 'orders',
				'ajax'     => false,
			)
		);
		$this->dbhandler  = new BM_DBhandler();
		$this->bmrequests = new BM_Request();
		$this->is_pro     = Booking_Management_Limits::is_pro_active();
	}

	/**
	 * Define columns — Free version uses hardcoded essential columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'order_id'          => esc_html__( 'Order ID', 'service-booking' ),
			'service_name'      => esc_html__( 'Ordered Service', 'service-booking' ),
			'booking_created_at' => esc_html__( 'Ordered Date', 'service-booking' ),
			'booking_date'      => esc_html__( 'Service Date', 'service-booking' ),
			'first_name'        => esc_html__( 'First name', 'service-booking' ),
			'email'             => esc_html__( 'Email', 'service-booking' ),
			'service_cost'      => esc_html__( 'Service Cost', 'service-booking' ),
			'extra_svc_cost'    => esc_html__( 'Extra Service Cost', 'service-booking' ),
			'disount_amount'    => esc_html__( 'Discount', 'service-booking' ),
			'total_cost'        => esc_html__( 'Total Cost', 'service-booking' ),
			'order_status'      => esc_html__( 'Order Status', 'service-booking' ),
			'payment_status'    => esc_html__( 'Payment Status', 'service-booking' ),
			'actions'           => esc_html__( 'Actions', 'service-booking' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'order_id'           => array( 'id', true ),
			'service_name'       => array( 'service_name', false ),
			'booking_created_at' => array( 'booking_created_at', false ),
			'booking_date'       => array( 'booking_date', false ),
			'total_cost'         => array( 'total_cost', false ),
			'order_status'       => array( 'order_status', false ),
		);
	}

	/**
	 * Extra table nav (above/below table) — used for status filter and search.
	 *
	 * @param string $which 'top' or 'bottom'.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$status_filter = isset( $_REQUEST['order_status_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order_status_filter'] ) ) : '';
		$statuses      = $this->bmrequests->bm_fetch_order_status_key_value();
		unset( $statuses['failed'] );

		echo '<div class="alignleft actions">';
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
		submit_button( __( 'Filter', 'service-booking' ), '', 'filter_action', false );
		echo '</div>';
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$per_page = ! empty( $this->dbhandler->get_global_option_value( 'bm_orders_per_page' ) )
			? absint( $this->dbhandler->get_global_option_value( 'bm_orders_per_page' ) )
			: 10;

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$user_id               = get_current_user_id();
		$failed_order_option   = $this->dbhandler->get_global_option_value( "show_backend_order_page_failed_orders_{$user_id}", 0 );
		$archived_order_option = $this->dbhandler->get_global_option_value( "show_backend_order_page_archived_orders_{$user_id}", 0 );

		// Determine the table source.
		$table_key = 'BOOKING';
		if ( (int) $failed_order_option === 1 ) {
			$table_key = 'FAILED_TRANSACTIONS';
		} elseif ( (int) $archived_order_option === 1 ) {
			$table_key = 'BOOKING_ARCHIVE';
		}

		$total = $this->dbhandler->bm_count( $table_key );

		// Sorting.
		$orderby = 'id';
		$order   = 'DESC';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$allowed = array( 'id', 'service_name', 'booking_created_at', 'booking_date', 'total_cost', 'order_status' );
			$req_orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
			if ( in_array( $req_orderby, $allowed, true ) ) {
				$orderby = $req_orderby;
			}
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			$order = ( 'asc' === strtolower( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) ) ? 'ASC' : 'DESC';
		}

		$orders = $this->dbhandler->get_all_result( $table_key, '*', 1, 'results', $offset, $per_page, $orderby, $order );

		$this->items = array();
		if ( ! empty( $orders ) ) {
			foreach ( $orders as $row ) {
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
					'id'                  => $row->id,
					'order_id'            => $row->id,
					'service_name'        => isset( $row->service_name ) ? $row->service_name : '',
					'booking_created_at'  => isset( $row->booking_created_at ) ? $row->booking_created_at : '',
					'booking_date'        => isset( $row->booking_date ) ? $row->booking_date : '',
					'first_name'          => $first_name,
					'email'               => $email,
					'service_cost'        => isset( $row->service_cost ) ? $row->service_cost : '0',
					'extra_svc_cost'      => isset( $row->extra_svc_cost ) ? $row->extra_svc_cost : '0',
					'disount_amount'      => isset( $row->disount_amount ) ? $row->disount_amount : '0',
					'total_cost'          => isset( $row->total_cost ) ? $row->total_cost : '0',
					'order_status'        => isset( $row->order_status ) ? $row->order_status : '',
					'payment_status'      => isset( $row->payment_status ) ? $row->payment_status : '',
					'booking_type'        => isset( $row->booking_type ) ? $row->booking_type : '',
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

			case 'booking_created_at':
				return ! empty( $item['booking_created_at'] )
					? esc_html( $this->bmrequests->bm_convert_date_format( $item['booking_created_at'], 'Y-m-d H:i:s', 'd/m/Y H:i' ) )
					: '';

			case 'booking_date':
				return ! empty( $item['booking_date'] )
					? esc_html( $this->bmrequests->bm_convert_date_format( $item['booking_date'], 'Y-m-d', 'd/m/Y' ) )
					: '';

			case 'first_name':
				return esc_html( $item['first_name'] );

			case 'email':
				return esc_html( $item['email'] );

			case 'service_cost':
			case 'extra_svc_cost':
			case 'disount_amount':
			case 'total_cost':
				return esc_html( $item[ $column_name ] );

			case 'order_status':
				$status_label = $this->bmrequests->bm_fetch_order_status_name( $item['order_status'] );
				return sprintf(
					'<span class="sg-order-status sg-status-%s">%s</span>',
					esc_attr( $item['order_status'] ),
					esc_html( $status_label )
				);

			case 'payment_status':
				return esc_html( ucfirst( $item['payment_status'] ) );

			case 'actions':
				$view = sprintf(
					'<a href="admin.php?page=bm_single_order&id=%s" class="edit-button" title="%s"><i class="fa fa-eye" aria-hidden="true"></i></a>',
					esc_attr( $item['id'] ),
					esc_attr__( 'View', 'service-booking' )
				);
				return $view;

			default:
				return '';
		}
	}

	/**
	 * Message when no items are found.
	 */
	public function no_items() {
		esc_html_e( 'No Orders Found', 'service-booking' );
	}
}
