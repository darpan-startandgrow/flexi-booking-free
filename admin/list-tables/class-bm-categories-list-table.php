<?php
/**
 * Categories List Table.
 *
 * Uses the WP_List_Table class to render the categories listing
 * with server-side pagination, drag-drop reordering, filters, and bulk actions.
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
			'cb'           => '<input type="checkbox" />',
			'serial'       => esc_html__( 'Serial No', 'service-booking' ),
			'cat_name'     => esc_html__( 'Name', 'service-booking' ),
			'cat_in_front' => esc_html__( 'Show in Frontend', 'service-booking' ),
			'shortcode'    => esc_html__( 'Single category shortcode', 'service-booking' ),
			'actions'      => esc_html__( 'Actions', 'service-booking' ),
		);
	}

	/**
	 * Checkbox column for bulk actions.
	 *
	 * @param array $item Row data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="category_ids[]" value="%s" />',
			esc_attr( $item['id'] )
		);
	}

	/**
	 * Bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'bulk_delete'     => esc_html__( 'Delete', 'service-booking' ),
			'bulk_show_front' => esc_html__( 'Show in Frontend', 'service-booking' ),
			'bulk_hide_front' => esc_html__( 'Hide from Frontend', 'service-booking' ),
		);
	}

	/**
	 * Process bulk actions.
	 */
	public function process_bulk_action() {
		$action = $this->current_action();
		if ( ! $action ) {
			return;
		}

		$nonce = isset( $_REQUEST['_wpnonce'] ) ? wp_unslash( $_REQUEST['_wpnonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bulk-categories' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'service-booking' ) );
		}

		$category_ids = isset( $_REQUEST['category_ids'] ) ? array_map( 'absint', (array) $_REQUEST['category_ids'] ) : array();

		if ( empty( $category_ids ) ) {
			return;
		}

		switch ( $action ) {
			case 'bulk_delete':
				foreach ( $category_ids as $category_id ) {
					if ( $category_id > 0 ) {
						$this->dbhandler->remove_row( 'CATEGORY', 'id', $category_id );
					}
				}
				break;

			case 'bulk_show_front':
				foreach ( $category_ids as $category_id ) {
					if ( $category_id > 0 ) {
						$this->dbhandler->update_row( 'CATEGORY', 'id', $category_id, array( 'cat_in_front' => 1 ), '', '%d' );
					}
				}
				break;

			case 'bulk_hide_front':
				foreach ( $category_ids as $category_id ) {
					if ( $category_id > 0 ) {
						$this->dbhandler->update_row( 'CATEGORY', 'id', $category_id, array( 'cat_in_front' => 0 ), '', '%d' );
					}
				}
				break;
		}
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
	 * Extra table nav — visibility filter.
	 *
	 * @param string $which 'top' or 'bottom'.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$visibility_filter = isset( $_REQUEST['visibility_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['visibility_filter'] ) ) : '';

		echo '<div class="alignleft actions">';

		// Visibility filter.
		echo '<select name="visibility_filter">';
		echo '<option value="">' . esc_html__( 'All Visibility', 'service-booking' ) . '</option>';
		printf(
			'<option value="1"%s>%s</option>',
			selected( $visibility_filter, '1', false ),
			esc_html__( 'Visible in Frontend', 'service-booking' )
		);
		printf(
			'<option value="0"%s>%s</option>',
			selected( $visibility_filter, '0', false ),
			esc_html__( 'Hidden from Frontend', 'service-booking' )
		);
		echo '</select>';

		submit_button( __( 'Filter', 'service-booking' ), '', 'filter_action', false );
		echo '</div>';
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$per_page = ! empty( $this->dbhandler->get_global_option_value( 'bm_categories_per_page' ) )
			? absint( $this->dbhandler->get_global_option_value( 'bm_categories_per_page' ) )
			: 10;

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// Build where conditions from filters.
		$where = 1;
		$visibility_filter = isset( $_REQUEST['visibility_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['visibility_filter'] ) ) : '';

		if ( '' !== $visibility_filter ) {
			$where = array( 'cat_in_front' => absint( $visibility_filter ) );
		}

		$total = $this->dbhandler->bm_count( 'CATEGORY', $where );

		$categories = $this->dbhandler->get_all_result( 'CATEGORY', '*', $where, 'results', $offset, $per_page, 'cat_position', false );

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
					'<button type="button" name="editcat" class="edit-button" id="editcat" title="%1$s" value="%2$s"><i class="fa fa-edit" aria-hidden="true"></i></button>',
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
		esc_html_e( 'No Categories Found', 'service-booking' );
	}
}
