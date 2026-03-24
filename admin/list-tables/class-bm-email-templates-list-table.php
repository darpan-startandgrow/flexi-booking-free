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
			'serial'  => esc_html__( 'Serial No', 'service-booking' ),
			'name'    => esc_html__( 'Name', 'service-booking' ),
			'type'    => esc_html__( 'Type', 'service-booking' ),
			'status'  => esc_html__( 'Status', 'service-booking' ),
			'actions' => esc_html__( 'Actions', 'service-booking' ),
		);
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
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$per_page = ! empty( $this->dbhandler->get_global_option_value( 'bm_templates_per_page' ) )
			? absint( $this->dbhandler->get_global_option_value( 'bm_templates_per_page' ) )
			: 10;

		$current_page    = $this->get_pagenum();
		$offset          = ( $current_page - 1 ) * $per_page;
		$total           = $this->dbhandler->bm_count( 'EMAIL_TMPL' );
		$email_templates = $this->dbhandler->get_all_result( 'EMAIL_TMPL', '*', 1, 'results', $offset, $per_page );

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
					'<button type="button" name="edittemplate" class="edit-button" id="edittemplate" title="%1$s" value="%3$s"><i class="fa fa-edit" aria-hidden="true"></i></button>
					<button type="button" name="deltemplate" class="delete-button" id="deltemplate" title="%2$s" value="%3$s"><i class="fa fa-trash" aria-hidden="true" style="color:red"></i></button>',
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
		esc_html_e( 'No Templates Found', 'service-booking' );
	}
}
