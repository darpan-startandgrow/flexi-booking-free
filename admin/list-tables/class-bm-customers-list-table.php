<?php
/**
 * Customers List Table.
 *
 * Uses the WP_List_Table class to render the customers listing
 * with name, email, active status, and actions.
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

		// Register columns with WordPress Screen Options for column visibility.
		add_filter( 'manage_' . $this->screen->id . '_columns', array( $this, 'get_columns' ) );
	}

	/**
	 * Define columns — shows name, email, status, and actions.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'             => '<input type="checkbox" />',
			'serial'         => esc_html__( '#', 'service-booking' ),
			'customer_name'  => esc_html__( 'Name', 'service-booking' ),
			'customer_email' => esc_html__( 'Email', 'service-booking' ),
			'is_active'      => esc_html__( 'Status', 'service-booking' ),
			'actions'        => esc_html__( 'Actions', 'service-booking' ),
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
			'customer_name'  => array( 'customer_name', false ),
			'customer_email' => array( 'customer_email', false ),
		);
	}

	/**
	 * Extra table nav — search filter.
	 *
	 * @param string $which 'top' or 'bottom'.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$status_filter = isset( $_REQUEST['status_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status_filter'] ) ) : '';

		echo '<div class="alignleft actions">';
		echo '<select name="status_filter">';
		echo '<option value="">' . esc_html__( 'All Statuses', 'service-booking' ) . '</option>';
		printf( '<option value="1"%s>%s</option>', selected( $status_filter, '1', false ), esc_html__( 'Active', 'service-booking' ) );
		printf( '<option value="0"%s>%s</option>', selected( $status_filter, '0', false ), esc_html__( 'Inactive', 'service-booking' ) );
		echo '</select>';
		submit_button( __( 'Filter', 'service-booking' ), '', 'filter_action', false );
		echo '</div>';
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$per_page = $this->get_items_per_page( 'bm_list_per_page', 10 );

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// Search filter.
		$where      = 1;
		$additional = '';
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search      = '%' . $GLOBALS['wpdb']->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%';
			$additional .= $GLOBALS['wpdb']->prepare( ' AND (customer_email LIKE %s OR customer_name LIKE %s)', $search, $search );
		}

		// Status filter.
		if ( isset( $_REQUEST['status_filter'] ) && '' !== $_REQUEST['status_filter'] ) {
			$status_val  = absint( $_REQUEST['status_filter'] );
			$additional .= $GLOBALS['wpdb']->prepare( ' AND is_active = %d', $status_val );
		}

		// Sorting.
		$orderby = 'id';
		$order   = 'DESC';
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$allowed = array( 'id', 'customer_name', 'customer_email' );
			$req_orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
			if ( in_array( $req_orderby, $allowed, true ) ) {
				$orderby = $req_orderby;
			}
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			$order = ( 'asc' === strtolower( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) ) ? 'ASC' : 'DESC';
		}

		$count_results = $this->dbhandler->get_all_result( 'CUSTOMERS', 'id', $where, 'results', 0, false, 'id', 'ASC', $additional );
		$total         = is_array( $count_results ) ? count( $count_results ) : 0;
		$customers     = $this->dbhandler->get_all_result( 'CUSTOMERS', '*', $where, 'results', $offset, $per_page, $orderby, $order, $additional );

		$this->items = array();
		if ( ! empty( $customers ) ) {
			$i = 1 + $offset;
			foreach ( $customers as $customer ) {
				$this->items[] = array(
					'id'             => $customer->id,
					'serial'         => $i,
					'customer_name'  => isset( $customer->customer_name ) ? $customer->customer_name : '',
					'customer_email' => isset( $customer->customer_email ) ? $customer->customer_email : '',
					'is_active'      => isset( $customer->is_active ) ? $customer->is_active : 0,
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

			case 'customer_name':
				return esc_html( $item['customer_name'] );

			case 'customer_email':
				return sprintf(
					'<span title="%s">%s</span>',
					esc_attr( $item['customer_email'] ),
					esc_html( mb_strimwidth( $item['customer_email'], 0, 60, '...' ) )
				);

			case 'is_active':
				if ( (int) $item['is_active'] === 1 ) {
					return '<span class="bm-customer-status bm-customer-active">' . esc_html__( 'Active', 'service-booking' ) . '</span>';
				}
				return '<span class="bm-customer-status bm-customer-inactive">' . esc_html__( 'Inactive', 'service-booking' ) . '</span>';

			case 'actions':
				return sprintf(
					'<a href="admin.php?page=bm_add_customer&id=%s" class="edit-button" title="%s"><i class="fa fa-pencil" aria-hidden="true"></i></a>',
					esc_attr( $item['id'] ),
					esc_attr__( 'Edit', 'service-booking' )
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
