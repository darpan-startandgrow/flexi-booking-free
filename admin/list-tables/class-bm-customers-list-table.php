<?php
/**
 * Customers List Table.
 *
 * Uses the WP_List_Table class to render the customers listing.
 * Free version shows only email; Pro shows full customer management.
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

class BM_Customers_List_Table extends WP_List_Table {

	/**
	 * DB handler instance.
	 *
	 * @var BM_DBhandler
	 */
	private $dbhandler;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'customer',
				'plural'   => 'customers',
				'ajax'     => false,
			)
		);
		$this->dbhandler = new BM_DBhandler();
	}

	/**
	 * Define columns — free version shows essential columns only.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'             => '<input type="checkbox" />',
			'serial'         => esc_html__( '#', 'service-booking' ),
			'customer_email' => esc_html__( 'Email', 'service-booking' ),
		);
	}

	/**
	 * Checkbox column for bulk actions.
	 *
	 * @param array $item Row data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="customer_ids[]" value="%s" />', esc_attr( $item['id'] ) );
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

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-customers' ) ) {
			return;
		}

		$ids = isset( $_REQUEST['customer_ids'] ) ? array_map( 'absint', (array) $_REQUEST['customer_ids'] ) : array();
		if ( empty( $ids ) ) {
			return;
		}

		foreach ( $ids as $id ) {
			if ( $id > 0 ) {
				$this->dbhandler->remove_row( 'CUSTOMERS', 'id', $id, '%d' );
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
			'customer_email' => array( 'customer_email', false ),
		);
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$per_page = ! empty( $_REQUEST['per_page'] )
			? absint( $_REQUEST['per_page'] )
			: ( ! empty( $this->dbhandler->get_global_option_value( 'bm_customers_per_page' ) )
				? absint( $this->dbhandler->get_global_option_value( 'bm_customers_per_page' ) )
				: 10 );

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// Search filter.
		$where      = 1;
		$additional = '';
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search      = '%' . $GLOBALS['wpdb']->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%';
			$additional .= $GLOBALS['wpdb']->prepare( ' AND customer_email LIKE %s', $search );
		}

		$count_results = $this->dbhandler->get_all_result( 'CUSTOMERS', 'id', $where, 'results', 0, false, 'id', 'ASC', $additional );
		$total         = is_array( $count_results ) ? count( $count_results ) : 0;
		$customers     = $this->dbhandler->get_all_result( 'CUSTOMERS', '*', $where, 'results', $offset, $per_page, 'id', 'Desc', $additional );

		$this->items = array();
		if ( ! empty( $customers ) ) {
			$i = 1 + $offset;
			foreach ( $customers as $customer ) {
				$this->items[] = array(
					'id'             => $customer->id,
					'serial'         => $i,
					'customer_email' => isset( $customer->customer_email ) ? $customer->customer_email : '',
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

			case 'customer_email':
				return sprintf(
					'<span title="%s">%s</span>',
					esc_attr( $item['customer_email'] ),
					esc_html( mb_strimwidth( $item['customer_email'], 0, 60, '...' ) )
				);

			default:
				return '';
		}
	}

	/**
	 * Message when no items are found.
	 */
	public function no_items() {
		esc_html_e( 'No Customers Found', 'service-booking' );
	}
}
