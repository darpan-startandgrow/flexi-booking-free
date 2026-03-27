<?php
/**
 * Global Extras List Table.
 *
 * Uses the WP_List_Table class to render the shared (global) extras listing
 * with server-side pagination, bulk actions and service-count badges.
 *
 * @since      1.5.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/list-tables
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class BM_Global_Extras_List_Table extends WP_List_Table {

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
				'singular' => 'global_extra',
				'plural'   => 'global_extras',
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
		$currency_symbol = $this->bmrequests->bm_get_currency_symbol( $this->dbhandler->get_global_option_value( 'bm_booking_currency', 'EUR' ) );
		return array(
			'cb'                     => '<input type="checkbox" />',
			'serial'                 => esc_html__( 'Serial No', 'service-booking' ),
			'extra_name'             => esc_html__( 'Name', 'service-booking' ),
			'extra_price'            => sprintf( esc_html__( 'Price (%s)', 'service-booking' ), esc_html( $currency_symbol ) ),
			'extra_max_cap'          => esc_html__( 'Max Capacity', 'service-booking' ),
			'is_extra_service_front' => esc_html__( 'Visible Frontend', 'service-booking' ),
			'linked_services'        => esc_html__( 'Linked Services', 'service-booking' ),
			'actions'                => esc_html__( 'Actions', 'service-booking' ),
		);
	}

	/**
	 * Checkbox column for bulk actions.
	 *
	 * @param array $item Row data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="global_extra_ids[]" value="%s" />', esc_attr( $item['id'] ) );
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
		$action = $this->current_action();
		if ( 'bulk-delete' !== $action ) {
			return;
		}

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-global_extras' ) ) {
			return;
		}

		$ids = isset( $_REQUEST['global_extra_ids'] ) ? array_map( 'absint', (array) $_REQUEST['global_extra_ids'] ) : array();
		if ( empty( $ids ) ) {
			return;
		}

		foreach ( $ids as $id ) {
			if ( $id <= 0 ) {
				continue;
			}
			// Remove junction records first, then the global extra.
			$this->dbhandler->remove_row( 'SERVICE_GLOBAL_EXTRA', 'global_extra_id', $id, '%d' );
			$this->dbhandler->remove_row( 'GLOBAL_EXTRA', 'id', $id, '%d' );
		}
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'extra_name' => array( 'extra_name', false ),
		);
	}

	/**
	 * Default column output.
	 *
	 * @param array  $item        Row data.
	 * @param string $column_name Column key.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'serial':
				return esc_html( $item['serial'] );
			case 'extra_name':
				return esc_html( $item['extra_name'] );
			case 'extra_price':
				return esc_html( $this->bmrequests->bm_fetch_price_in_global_settings_format( $item['extra_price'], true ) );
			case 'extra_max_cap':
				return esc_html( $item['extra_max_cap'] );
			case 'is_extra_service_front':
				$checked = ( (int) $item['is_extra_service_front'] === 1 ) ? 'checked' : '';
				return '<span class="bm-checkbox-td"><input type="checkbox" class="bm_toggle" ' . $checked . ' disabled /><label></label></span>';
			case 'linked_services':
				return $this->render_linked_services_badges( $item['id'] );
			case 'actions':
				return $this->render_actions( $item );
			default:
				return '';
		}
	}

	/**
	 * Render linked-services badges.
	 *
	 * @param int $global_extra_id Global extra ID.
	 * @return string HTML output.
	 */
	private function render_linked_services_badges( $global_extra_id ) {
		$links = $this->dbhandler->get_all_result( 'SERVICE_GLOBAL_EXTRA', '*', array( 'global_extra_id' => $global_extra_id ), 'results' );
		if ( empty( $links ) ) {
			return '<span class="bm-shared-badge bm-shared-badge--none">' . esc_html__( 'None', 'service-booking' ) . '</span>';
		}
		$count = count( $links );
		$names = array();
		foreach ( $links as $link ) {
			$svc = $this->dbhandler->get_row( 'SERVICE', $link->service_id );
			if ( $svc ) {
				$names[] = esc_html( $svc->service_name );
			}
		}
		$tooltip = implode( ', ', $names );
		return '<span class="bm-shared-badge bm-shared-badge--amber" title="' . esc_attr( $tooltip ) . '"><span class="dashicons dashicons-share"></span> ' . esc_html( $count ) . '</span>';
	}

	/**
	 * Render action buttons.
	 *
	 * @param array $item Row data.
	 * @return string HTML output.
	 */
	private function render_actions( $item ) {
		$edit_url   = wp_nonce_url(
			admin_url( 'admin.php?page=bm_shared_extras&action=edit&global_extra_id=' . absint( $item['id'] ) ),
			'bm_edit_global_extra_' . absint( $item['id'] )
		);
		$delete_url = wp_nonce_url(
			admin_url( 'admin.php?page=bm_shared_extras&action=delete&global_extra_id=' . absint( $item['id'] ) ),
			'bm_delete_global_extra_' . absint( $item['id'] )
		);
		$html  = '<a href="' . esc_url( $edit_url ) . '" class="edit-button" title="' . esc_attr__( 'Edit', 'service-booking' ) . '"><i class="fa fa-pencil" aria-hidden="true"></i></a> ';
		$html .= '<a href="' . esc_url( $delete_url ) . '" class="delete-button" onclick="return confirm(\'' . esc_attr__( 'Delete this shared extra?', 'service-booking' ) . '\');" title="' . esc_attr__( 'Delete', 'service-booking' ) . '"><i class="fa fa-trash" aria-hidden="true" style="color:red;"></i></a>';
		return $html;
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'bm_list_per_page', 20 );
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$count_results = $this->dbhandler->get_all_result( 'GLOBAL_EXTRA', 'id', 1, 'results' );
		$total         = is_array( $count_results ) ? count( $count_results ) : 0;

		$sort_by = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id';
		$order   = ( isset( $_REQUEST['order'] ) && 'asc' === strtolower( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) ) ? false : 'DESC';

		$extras = $this->dbhandler->get_all_result( 'GLOBAL_EXTRA', '*', 1, 'results', $offset, $per_page, $sort_by, $order );

		$this->items = array();
		if ( ! empty( $extras ) ) {
			$i = 1 + $offset;
			foreach ( $extras as $extra ) {
				$this->items[] = array(
					'id'                     => $extra->id,
					'serial'                 => $i,
					'extra_name'             => isset( $extra->extra_name ) ? $extra->extra_name : '',
					'extra_price'            => isset( $extra->extra_price ) ? $extra->extra_price : 0,
					'extra_max_cap'          => isset( $extra->extra_max_cap ) ? $extra->extra_max_cap : 1,
					'is_extra_service_front' => isset( $extra->is_extra_service_front ) ? $extra->is_extra_service_front : 0,
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
}
