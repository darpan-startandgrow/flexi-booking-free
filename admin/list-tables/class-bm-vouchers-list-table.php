<?php
/**
 * Vouchers List Table.
 *
 * Uses the WP_List_Table class to render the voucher listing
 * with server-side pagination and status filtering.
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

class BM_Vouchers_List_Table extends WP_List_Table {

	/**
	 * Request helper instance.
	 *
	 * @var BM_Request
	 */
	private $bmrequests;

	/**
	 * Status filter value.
	 *
	 * @var string|int
	 */
	private $filter_status;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'voucher',
				'plural'   => 'vouchers',
				'ajax'     => false,
			)
		);
		$this->bmrequests = new BM_Request();

		$status = filter_input( INPUT_GET, 'status' );
		$this->filter_status = ( $status !== null && $status !== '' ) ? absint( $status ) : '';
	}

	/**
	 * Define columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'serial'      => esc_html__( '#', 'service-booking' ),
			'code'        => esc_html__( 'Voucher Code', 'service-booking' ),
			'service'     => esc_html__( 'Service', 'service-booking' ),
			'status'      => esc_html__( 'Status', 'service-booking' ),
			'created_at'  => esc_html__( 'Created Date', 'service-booking' ),
		);
	}

	/**
	 * Checkbox column for bulk actions.
	 *
	 * @param array $item Row data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="voucher_ids[]" value="%s" />', esc_attr( $item['id'] ) );
	}

	/**
	 * Bulk actions available in the dropdown.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'bulk-delete' => esc_html__( 'Delete', 'service-booking' ),
		);
	}

	/**
	 * Process bulk actions.
	 */
	public function process_bulk_action() {
		if ( 'bulk-delete' !== $this->current_action() ) {
			return;
		}

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-vouchers' ) ) {
			return;
		}

		$ids = isset( $_REQUEST['voucher_ids'] ) ? array_map( 'absint', (array) $_REQUEST['voucher_ids'] ) : array();
		if ( empty( $ids ) ) {
			return;
		}

		$dbhandler = new BM_DBhandler();
		foreach ( $ids as $id ) {
			if ( $id > 0 ) {
				$dbhandler->remove_row( 'VOUCHERS', 'id', $id, '%d' );
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
			'code'       => array( 'code', false ),
			'created_at' => array( 'created_at', true ),
		);
	}

	/**
	 * Get available filter views (status tabs).
	 *
	 * @return array
	 */
	protected function get_views() {
		$page_url = admin_url( 'admin.php?page=bm_voucher_records' );
		$current  = $this->filter_status;

		$views = array();
		$views['all']      = sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( $page_url ),
			( $current === '' ) ? 'current' : '',
			esc_html__( 'All', 'service-booking' )
		);
		$views['active']   = sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( add_query_arg( 'status', '1', $page_url ) ),
			( $current === 1 ) ? 'current' : '',
			esc_html__( 'Active', 'service-booking' )
		);
		$views['inactive'] = sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( add_query_arg( 'status', '0', $page_url ) ),
			( $current === 0 ) ? 'current' : '',
			esc_html__( 'Inactive', 'service-booking' )
		);

		return $views;
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$dbhandler = new BM_DBhandler();

		$per_page = $this->get_items_per_page( 'bm_list_per_page', 10 );

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// Build WHERE conditions for status filter.
		$where = array();
		if ( $this->filter_status !== '' ) {
			$where = array( 'v.status' => array( '=' => (int) $this->filter_status ) );
		}

		// Count total.
		$count_result = $dbhandler->get_results_with_join(
			array( 'VOUCHERS', 'v' ),
			'COUNT(*) AS total',
			array(),
			$where,
			'row'
		);
		$total = $count_result ? (int) $count_result->total : 0;

		// Sorting.
		$orderby = 'v.id';
		$order   = 'DESC';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$allowed = array( 'code' => 'v.code', 'created_at' => 'v.created_at' );
			$req_orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
			if ( isset( $allowed[ $req_orderby ] ) ) {
				$orderby = $allowed[ $req_orderby ];
			}
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			$order = ( 'asc' === strtolower( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) ) ? 'ASC' : 'DESC';
		}

		// Fetch vouchers.
		$joins = array(
			array( 'type' => 'LEFT', 'table' => 'BOOKING', 'alias' => 'b', 'on' => 'v.booking_id = b.id' ),
		);
		$vouchers = $dbhandler->get_results_with_join(
			array( 'VOUCHERS', 'v' ),
			'v.id, v.code, v.booking_id, v.status, v.created_at, b.service_name',
			$joins,
			$where,
			'results',
			$offset,
			$per_page,
			$orderby,
			( 'DESC' === $order )
		);

		$this->items = array();
		if ( ! empty( $vouchers ) ) {
			$i = 1 + $offset;
			foreach ( $vouchers as $voucher ) {
				$this->items[] = array(
					'id'           => $voucher->id,
					'serial'       => $i,
					'code'         => isset( $voucher->code ) ? $voucher->code : '',
					'service_name' => isset( $voucher->service_name ) ? $voucher->service_name : '',
					'status'       => isset( $voucher->status ) ? (int) $voucher->status : 0,
					'created_at'   => isset( $voucher->created_at ) ? $voucher->created_at : '',
				);
				$i++;
			}
		}

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => ( $per_page > 0 ) ? ceil( $total / $per_page ) : 1,
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

			case 'code':
				return sprintf(
					'<span title="%s">%s</span>',
					esc_attr( $item['code'] ),
					esc_html( mb_strimwidth( $item['code'], 0, 40, '...' ) )
				);

			case 'service':
				return ! empty( $item['service_name'] ) ? esc_html( $item['service_name'] ) : '—';

			case 'status':
				$label = ( $item['status'] === 1 ) ? __( 'Active', 'service-booking' ) : __( 'Inactive', 'service-booking' );
				$color = ( $item['status'] === 1 ) ? 'color: green;' : 'color: #999;';
				return sprintf( '<span style="%s">%s</span>', esc_attr( $color ), esc_html( $label ) );

			case 'created_at':
				return ! empty( $item['created_at'] )
					? esc_html( $this->bmrequests->bm_convert_date_format( $item['created_at'], 'Y-m-d H:i:s', 'd/m/Y' ) )
					: '';

			default:
				return '';
		}
	}

	/**
	 * Message when no items are found.
	 */
	public function no_items() {
		esc_html_e( 'No Vouchers Found', 'service-booking' );
	}
}
