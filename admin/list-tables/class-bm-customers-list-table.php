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
				'singular' => 'customer',
				'plural'   => 'customers',
				'ajax'     => false,
			)
		);
		$this->dbhandler = new BM_DBhandler();
		$this->is_pro    = Booking_Management_Limits::is_pro_active();
	}

	/**
	 * Define columns — different for Free vs Pro.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'serial'         => esc_html__( '#', 'service-booking' ),
		);

		if ( $this->is_pro ) {
			$columns['customer_name'] = esc_html__( 'Name', 'service-booking' );
		}

		$columns['customer_email'] = esc_html__( 'Email', 'service-booking' );

		if ( $this->is_pro ) {
			$columns['profile']   = esc_html__( 'Profile', 'service-booking' );
			$columns['is_active'] = esc_html__( 'Status', 'service-booking' );
			$columns['actions']   = esc_html__( 'Actions', 'service-booking' );
		}

		return $columns;
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
		$per_page = ! empty( $this->dbhandler->get_global_option_value( 'bm_customers_per_page' ) )
			? absint( $this->dbhandler->get_global_option_value( 'bm_customers_per_page' ) )
			: 10;

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;
		$total        = $this->dbhandler->bm_count( 'CUSTOMERS' );
		$customers    = $this->dbhandler->get_all_result( 'CUSTOMERS', '*', 1, 'results', $offset, $per_page, 'id', 'Desc' );

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
				'total_pages' => ceil( $total / $per_page ),
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

			case 'customer_name':
				return sprintf(
					'<span title="%s">%s</span>',
					esc_attr( $item['customer_name'] ),
					esc_html( mb_strimwidth( $item['customer_name'], 0, 60, '...' ) )
				);

			case 'customer_email':
				return sprintf(
					'<span title="%s">%s</span>',
					esc_attr( $item['customer_email'] ),
					esc_html( mb_strimwidth( $item['customer_email'], 0, 60, '...' ) )
				);

			case 'profile':
				return sprintf(
					'<a href="admin.php?page=bm_customer_profile&id=%s" title="%s"><i class="fa fa-user-circle-o" style="font-size:18px;vertical-align: middle;"></i></a>',
					esc_attr( $item['id'] ),
					esc_attr__( 'Check profile', 'service-booking' )
				);

			case 'is_active':
				$checked = checked( $item['is_active'], '1', false );
				return sprintf(
					'<div class="bm-checkbox-td"><input name="customer_is_active" type="checkbox" id="customer_is_active_%1$s" class="regular-text auto-checkbox bm_toggle" %2$s onchange="bm_change_customer_visibility(this)"><label for="customer_is_active_%1$s"></label></div>',
					esc_attr( $item['id'] ),
					$checked
				);

			case 'actions':
				return sprintf(
					'<button type="button" name="editcust" class="edit-button" id="editcust" title="%s" value="%s"><i class="fa fa-edit" aria-hidden="true"></i></button>',
					esc_attr__( 'Edit', 'service-booking' ),
					esc_attr( $item['id'] )
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
