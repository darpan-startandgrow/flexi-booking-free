<?php
/**
 * Services List Table.
 *
 * Uses the WP_List_Table class to render the services listing
 * with server-side pagination, sorting, and search.
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

class BM_Services_List_Table extends WP_List_Table {

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
				'singular' => 'service',
				'plural'   => 'services',
				'ajax'     => false,
			)
		);
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
			'serial'            => esc_html__( 'Serial No', 'service-booking' ),
			'service_name'      => esc_html__( 'Name', 'service-booking' ),
			'category'          => esc_html__( 'Category', 'service-booking' ),
			'is_service_front'  => esc_html__( 'Show in frontend', 'service-booking' ),
			'shortcodes'        => esc_html__( 'Service Shortcodes', 'service-booking' ),
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
			'service_name' => array( 'service_name', false ),
		);
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$per_page = ! empty( $this->dbhandler->get_global_option_value( 'bm_services_per_page' ) )
			? absint( $this->dbhandler->get_global_option_value( 'bm_services_per_page' ) )
			: 10;

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;
		$total        = $this->dbhandler->bm_count( 'SERVICE' );

		$orderby = 'service_position';
		$order   = 'ASC';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$allowed = array( 'service_name' );
			$req_orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
			if ( in_array( $req_orderby, $allowed, true ) ) {
				$orderby = $req_orderby;
			}
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			$order = ( 'desc' === strtolower( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) ) ? 'DESC' : 'ASC';
		}

		$services = $this->dbhandler->get_all_result( 'SERVICE', '*', 1, 'results', $offset, $per_page, $orderby, $order );

		$this->items = array();
		if ( ! empty( $services ) ) {
			$i = 1 + $offset;
			foreach ( $services as $service ) {
				$this->items[] = array(
					'id'               => $service->id,
					'serial'           => $i,
					'service_name'     => isset( $service->service_name ) ? $service->service_name : '',
					'category'         => $this->bmrequests->bm_fetch_category_name_by_service_id( $service->id ),
					'is_service_front' => isset( $service->is_service_front ) ? $service->is_service_front : 0,
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
				return sprintf(
					'<span class="service_listing_number" data-id="%s" data-order="%s" style="cursor:move;">%s</span>',
					esc_attr( $item['id'] ),
					esc_attr( $item['serial'] ),
					esc_html( $item['serial'] )
				);

			case 'service_name':
				return sprintf(
					'<span style="cursor:move;" title="%s">%s</span>',
					esc_attr( $item['service_name'] ),
					esc_html( mb_strimwidth( $item['service_name'], 0, 40, '...' ) )
				);

			case 'category':
				return sprintf(
					'<span title="%s">%s</span>',
					esc_attr( $item['category'] ),
					esc_html( mb_strimwidth( $item['category'], 0, 40, '...' ) )
				);

			case 'is_service_front':
				$checked = checked( $item['is_service_front'], '1', false );
				return sprintf(
					'<div class="bm-checkbox-td"><input name="bm_show_service_in_front" type="checkbox" id="bm_show_service_in_front_%1$s" class="regular-text auto-checkbox bm_toggle" %2$s onchange="bm_change_service_visibility(this)"><label for="bm_show_service_in_front_%1$s"></label></div>',
					esc_attr( $item['id'] ),
					$checked
				);

			case 'shortcodes':
				$sc1 = '[sgbm_single_service id="' . $item['id'] . '"]';
				$sc2 = '[sgbm_single_service_calendar id="' . $item['id'] . '"]';
				return sprintf(
					'<div class="copyMessagetooltip" style="margin-bottom: 5px;">
						<input class="copytextTooltip" value="%1$s" onclick="bm_copy_text(this)" onmouseout="bm_copy_message(this)" readonly>
						<span class="tooltiptext">%3$s</span>
						<button type="button" class="bm-info-button" data-shortcode="sgbm_single_service" title="%4$s">i</button>
					</div>
					<div class="copyMessagetooltip">
						<input class="copytextTooltip" value="%2$s" onclick="bm_copy_text(this)" onmouseout="bm_copy_message(this)" readonly>
						<span class="tooltiptext">%3$s</span>
						<button type="button" class="bm-info-button" data-shortcode="sgbm_single_service_calendar" title="%4$s">i</button>
					</div>',
					esc_attr( $sc1 ),
					esc_attr( $sc2 ),
					esc_html__( 'Copy to clipboard', 'service-booking' ),
					esc_html__( 'Shortcode Info', 'service-booking' )
				);

			case 'actions':
				return sprintf(
					'<button type="button" name="editsvc" class="edit-button" id="editsvc" title="%1$s" value="%3$s"><i class="fa fa-edit" aria-hidden="true"></i></button>
					<button type="button" name="delsvc" class="delete-button" id="delsvc" title="%2$s" value="%3$s"><i class="fa fa-trash" aria-hidden="true" style="color:red"></i></button>',
					esc_attr__( 'Edit', 'service-booking' ),
					esc_attr__( 'Delete', 'service-booking' ),
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
		esc_html_e( 'No Services Found', 'service-booking' );
	}
}
