<?php
/**
 * Categories List Table.
 *
 * Uses the WP_List_Table class to render the categories listing
 * with server-side pagination and drag-drop reordering.
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

class BM_Categories_List_Table extends WP_List_Table {

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
				'singular' => 'category',
				'plural'   => 'categories',
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
			'serial'       => esc_html__( 'Serial No', 'service-booking' ),
			'cat_name'     => esc_html__( 'Name', 'service-booking' ),
			'cat_in_front' => esc_html__( 'Show in Frontend', 'service-booking' ),
			'shortcode'    => esc_html__( 'Single category shortcode', 'service-booking' ),
			'actions'      => esc_html__( 'Actions', 'service-booking' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'cat_name' => array( 'cat_name', false ),
		);
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$per_page = ! empty( $this->dbhandler->get_global_option_value( 'bm_categories_per_page' ) )
			? absint( $this->dbhandler->get_global_option_value( 'bm_categories_per_page' ) )
			: 10;

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;
		$total        = $this->dbhandler->bm_count( 'CATEGORY' );

		$categories = $this->dbhandler->get_all_result( 'CATEGORY', '*', 1, 'results', $offset, $per_page, 'cat_position', false );

		$this->items = array();
		if ( ! empty( $categories ) ) {
			$i = 1 + $offset;
			foreach ( $categories as $category ) {
				$this->items[] = array(
					'id'           => $category->id,
					'serial'       => $i,
					'cat_name'     => isset( $category->cat_name ) ? $category->cat_name : '',
					'cat_in_front' => isset( $category->cat_in_front ) ? $category->cat_in_front : 0,
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
					'<span class="category_listing_number" data-id="%s" data-order="%s" style="cursor:move;">%s</span>',
					esc_attr( $item['id'] ),
					esc_attr( $item['serial'] ),
					esc_html( $item['serial'] )
				);

			case 'cat_name':
				return sprintf(
					'<span style="cursor:move;" title="%s">%s</span>',
					esc_attr( $item['cat_name'] ),
					esc_html( mb_strimwidth( $item['cat_name'], 0, 40, '...' ) )
				);

			case 'cat_in_front':
				$checked = checked( $item['cat_in_front'], '1', false );
				return sprintf(
					'<div class="bm-checkbox-td"><input name="bm_show_category_in_front" type="checkbox" id="bm_show_category_in_front_%1$s" class="regular-text auto-checkbox bm_toggle" %2$s onchange="bm_change_category_visibility(this)"><label for="bm_show_category_in_front_%1$s"></label></div>',
					esc_attr( $item['id'] ),
					$checked
				);

			case 'shortcode':
				$shortcode = '[sgbm_service_by_category ids="' . $item['id'] . '"]';
				return sprintf(
					'<div class="copyMessagetooltip"><input class="copytextTooltip" value="%s" id="copyInput_%s" onclick="bm_copy_text(this)" onmouseout="bm_copy_message(this)" readonly><span class="tooltiptext" id="copyTooltip_%s">%s</span></div>',
					esc_attr( $shortcode ),
					esc_attr( $item['id'] ),
					esc_attr( $item['id'] ),
					esc_html__( 'Copy to clipboard', 'service-booking' )
				);

			case 'actions':
				return sprintf(
					'<button type="button" name="editcat" class="edit-button" id="editcat" title="%1$s" value="%3$s"><i class="fa fa-edit" aria-hidden="true"></i></button>
					<button type="button" name="delcat" class="delete-button" id="delcat" title="%2$s" value="%3$s"><i class="fa fa-trash" aria-hidden="true" style="color:red"></i></button>',
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
		esc_html_e( 'No Categories Found', 'service-booking' );
	}
}
