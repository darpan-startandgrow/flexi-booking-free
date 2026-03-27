<?php
/**
 * Email Templates List Table.
 *
 * Uses the WP_List_Table class to render the email templates listing
 * with server-side pagination.
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

class BM_Email_Templates_List_Table extends WP_List_Table {

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
	 * Current language code.
	 *
	 * @var string
	 */
	private $language;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'email_template',
				'plural'   => 'email_templates',
				'ajax'     => false,
			)
		);
		$this->dbhandler  = new BM_DBhandler();
		$this->bmrequests = new BM_Request();

		$language       = $this->dbhandler->get_global_option_value( 'bm_flexi_current_language', 'en' );
		$back_lang      = $this->dbhandler->get_global_option_value( 'bm_flexi_current_language_backend', '' );
		$this->language = ! empty( $back_lang ) ? $back_lang : $language;
	}

	/**
	 * Define columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'      => '<input type="checkbox" />',
			'serial'  => esc_html__( 'Serial No', 'service-booking' ),
			'name'    => esc_html__( 'Name', 'service-booking' ),
			'type'    => esc_html__( 'Type', 'service-booking' ),
			'status'  => esc_html__( 'Status', 'service-booking' ),
			'actions' => esc_html__( 'Actions', 'service-booking' ),
		);
	}

	/**
	 * Checkbox column for bulk actions.
	 *
	 * @param array $item Row data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="template_ids[]" value="%s" />', esc_attr( $item['id'] ) );
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

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-email_templates' ) ) {
			return;
		}

		$ids = isset( $_REQUEST['template_ids'] ) ? array_map( 'absint', (array) $_REQUEST['template_ids'] ) : array();
		if ( empty( $ids ) ) {
			return;
		}

		foreach ( $ids as $id ) {
			if ( $id > 0 ) {
				$this->dbhandler->remove_row( 'EMAIL_TMPL', 'id', $id, '%d' );
			}
		}
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Extra table nav with type filter.
	 *
	 * @param string $which 'top' or 'bottom'.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$type_filter = isset( $_REQUEST['type_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type_filter'] ) ) : '';

		$types = array(
			0  => __( 'New order from frontend (notify customer)', 'service-booking' ),
			3  => __( 'Cancel order (notify customer)', 'service-booking' ),
			5  => __( 'New order (notify admin)', 'service-booking' ),
			6  => __( 'Cancel order (notify admin)', 'service-booking' ),
			9  => __( 'Failed order (notify customer)', 'service-booking' ),
			10 => __( 'Failed order (notify admin)', 'service-booking' ),
			11 => __( 'Gift voucher (notify recipient)', 'service-booking' ),
			15 => __( 'Redeem voucher (notify admin)', 'service-booking' ),
			16 => __( 'Redeem voucher (notify customer)', 'service-booking' ),
		);

		echo '<div class="alignleft actions">';
		echo '<select name="type_filter">';
		echo '<option value="">' . esc_html__( 'All Types', 'service-booking' ) . '</option>';
		foreach ( $types as $key => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $key ),
				selected( $type_filter, (string) $key, false ),
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
		$this->process_bulk_action();

		$per_page = $this->get_items_per_page( 'bm_list_per_page', 10 );

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// Build WHERE clause for type filter.
		$where      = 1;
		$additional = '';

		if ( isset( $_REQUEST['type_filter'] ) && '' !== $_REQUEST['type_filter'] ) {
			$type_val    = absint( $_REQUEST['type_filter'] );
			$additional .= $GLOBALS['wpdb']->prepare( ' AND type = %d', $type_val );
		}

		$count_results   = $this->dbhandler->get_all_result( 'EMAIL_TMPL', 'id', $where, 'results', 0, false, 'id', 'ASC', $additional );
		$total           = is_array( $count_results ) ? count( $count_results ) : 0;
		$email_templates = $this->dbhandler->get_all_result( 'EMAIL_TMPL', '*', $where, 'results', $offset, $per_page, 'id', 'DESC', $additional );

		$tmpl_name_key = "tmpl_name_{$this->language}";

		$this->items = array();
		if ( ! empty( $email_templates ) ) {
			$i = 1 + $offset;
			foreach ( $email_templates as $template ) {
				$this->items[] = array(
					'id'     => $template->id,
					'serial' => $i,
					'name'   => isset( $template->$tmpl_name_key ) ? $template->$tmpl_name_key : '',
					'type'   => isset( $template->type ) ? $template->type : '',
					'status' => isset( $template->status ) ? (int) $template->status : 0,
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

			case 'name':
				return sprintf(
					'<span title="%s">%s</span>',
					esc_attr( $item['name'] ),
					esc_html( mb_strimwidth( $item['name'], 0, 40, '...' ) )
				);

			case 'type':
				$type_name = $this->bmrequests->bm_fetch_template_type_name_by_type_id( $item['type'] );
				return sprintf(
					'<span title="%s">%s</span>',
					esc_attr( $type_name ),
					esc_html( mb_strimwidth( $type_name, 0, 40, '...' ) )
				);

			case 'status':
				$checked = checked( $item['status'], 1, false );
				return sprintf(
					'<div class="bm-checkbox-td"><input name="bm_template_status" type="checkbox" id="bm_template_status_%1$s" data-type="%2$s" class="regular-text auto-checkbox bm_toggle" %3$s onchange="bm_change_template_visibility(this)"><label for="bm_template_status_%1$s"></label></div>',
					esc_attr( $item['id'] ),
					esc_attr( $item['type'] ),
					$checked
				);

			case 'actions':
				return sprintf(
					'<button type="button" name="edittemplate" class="edit-button" id="edittemplate" title="%1$s" value="%2$s"><i class="fa fa-edit" aria-hidden="true"></i></button>',
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
		esc_html_e( 'No Templates Found', 'service-booking' );
	}
}
