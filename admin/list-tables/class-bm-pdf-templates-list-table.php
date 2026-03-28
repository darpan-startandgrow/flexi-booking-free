<?php
/**
 * PDF Templates List Table (Free Version).
 *
 * Uses the WP_List_Table class to render the default non-customizable
 * PDF templates listing for the free version.
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

class BM_PDF_Templates_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'pdf_template',
				'plural'   => 'pdf_templates',
				'ajax'     => false,
			)
		);

		// Register columns with WordPress Screen Options for column visibility.
		add_filter( 'manage_' . $this->screen->id . '_columns', array( $this, 'get_columns' ) );
	}

	/**
	 * Define columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'serial'      => '#',
			'name'        => esc_html__( 'Template Name', 'service-booking' ),
			'description' => esc_html__( 'Description', 'service-booking' ),
			'status'      => esc_html__( 'Status', 'service-booking' ),
		);
	}

	/**
	 * No sortable columns for static templates.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$this->items = array(
			array(
				'serial'      => 1,
				'name'        => __( 'Booking Ticket PDF', 'service-booking' ),
				'description' => __( 'Default booking ticket template used for order confirmations.', 'service-booking' ),
				'status'      => 'active',
			),
			array(
				'serial'      => 2,
				'name'        => __( 'Voucher PDF', 'service-booking' ),
				'description' => __( 'Default voucher template used for gift vouchers.', 'service-booking' ),
				'status'      => 'active',
			),
			array(
				'serial'      => 3,
				'name'        => __( 'Customer Details PDF', 'service-booking' ),
				'description' => __( 'Default customer details template for customer information export.', 'service-booking' ),
				'status'      => 'active',
			),
		);

		$this->set_pagination_args(
			array(
				'total_items' => 3,
				'per_page'    => 3,
				'total_pages' => 1,
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

			case 'name':
				return '<strong>' . esc_html( $item['name'] ) . '</strong>';

			case 'description':
				return esc_html( $item['description'] );

			case 'status':
				return '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . esc_html__( 'Active', 'service-booking' );

			default:
				return '';
		}
	}

	/**
	 * Message when no items are found.
	 */
	public function no_items() {
		esc_html_e( 'No PDF Templates Found', 'service-booking' );
	}
}
