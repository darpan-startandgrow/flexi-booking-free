<?php
/**
 * Fields List Table.
 *
 * Uses the WP_List_Table class to render the billing form fields
 * listing with server-side pagination in the free version.
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

class BM_Fields_List_Table extends WP_List_Table {

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
				'singular' => 'field',
				'plural'   => 'fields',
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
			'serial'     => esc_html__( '#', 'service-booking' ),
			'label'      => esc_html__( 'Label', 'service-booking' ),
			'field_type' => esc_html__( 'Type', 'service-booking' ),
			'required'   => esc_html__( 'Required', 'service-booking' ),
			'visible'    => esc_html__( 'Visible', 'service-booking' ),
			'actions'    => esc_html__( 'Actions', 'service-booking' ),
		);
	}

	/**
	 * No bulk actions in free version (fields cannot be deleted).
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array();
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
		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$count_results = $this->dbhandler->get_all_result( 'FIELDS', 'id', 1, 'results', 0, false, 'field_position', 'ASC' );
		$total         = is_array( $count_results ) ? count( $count_results ) : 0;
		$fields        = $this->dbhandler->get_all_result( 'FIELDS', '*', 1, 'results', $offset, $per_page, 'field_position', 'ASC' );

		$this->items = array();
		if ( ! empty( $fields ) ) {
			$i = 1 + $offset;
			foreach ( $fields as $field ) {
				$field_options = isset( $field->field_options ) ? maybe_unserialize( $field->field_options ) : array();
				$is_visible    = isset( $field_options['is_visible'] ) ? (int) $field_options['is_visible'] : 1;
				$is_default    = isset( $field_options['is_default'] ) ? (int) $field_options['is_default'] : 0;

				$this->items[] = array(
					'id'         => $field->id,
					'serial'     => $i,
					'label'      => isset( $field->field_label ) ? $field->field_label : '',
					'field_type' => isset( $field->field_type ) ? $field->field_type : '',
					'required'   => isset( $field->is_required ) ? (int) $field->is_required : 0,
					'visible'    => $is_visible,
					'is_default' => $is_default,
					'field_name' => isset( $field->field_name ) ? $field->field_name : '',
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

			case 'label':
				$label = esc_html( $item['label'] );
				if ( $item['is_default'] ) {
					$label .= ' <span class="bm-default-badge" style="background:#e8f5e9;color:#2e7d32;padding:2px 6px;border-radius:3px;font-size:11px;font-weight:600;">' . esc_html__( 'Default', 'service-booking' ) . '</span>';
				}
				return $label;

			case 'field_type':
				return sprintf(
					'<code style="background:#f0f0f1;padding:2px 6px;border-radius:3px;">%s</code>',
					esc_html( ucfirst( $item['field_type'] ) )
				);

			case 'required':
				if ( $item['required'] ) {
					return '<span class="dashicons dashicons-yes-alt" style="color:#2e7d32;" title="' . esc_attr__( 'Required', 'service-booking' ) . '"></span>';
				}
				return '<span class="dashicons dashicons-minus" style="color:#999;" title="' . esc_attr__( 'Optional', 'service-booking' ) . '"></span>';

			case 'visible':
				if ( $item['visible'] ) {
					return '<span class="dashicons dashicons-visibility" style="color:#2e7d32;" title="' . esc_attr__( 'Visible', 'service-booking' ) . '"></span>';
				}
				return '<span class="dashicons dashicons-hidden" style="color:#999;" title="' . esc_attr__( 'Hidden', 'service-booking' ) . '"></span>';

			case 'actions':
				return sprintf(
					'<button type="button" class="edit-button edit_field" title="%1$s" id="%2$s" onclick="get_field_Settings(this.id)"><i class="fa fa-edit" aria-hidden="true"></i></button>',
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
		esc_html_e( 'No Fields Found', 'service-booking' );
	}
}
