<?php
/**
 * Forms List Table.
 *
 * Uses the WP_List_Table class to render the billing forms
 * listing with bulk actions and pagination.
 *
 * @since      1.3.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/list-tables
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class BM_Forms_List_Table extends WP_List_Table {

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
				'singular' => 'form',
				'plural'   => 'forms',
				'ajax'     => false,
			)
		);
		$this->dbhandler = new BM_DBhandler();

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
			'cb'          => '<input type="checkbox" />',
			'form_name'   => esc_html__( 'Form Name', 'service-booking' ),
			'fields'      => esc_html__( 'Fields', 'service-booking' ),
			'status'      => esc_html__( 'Status', 'service-booking' ),
			'created_at'  => esc_html__( 'Date Created', 'service-booking' ),
		);
	}

	/**
	 * Bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'bulk-activate'   => esc_html__( 'Activate', 'service-booking' ),
			'bulk-deactivate' => esc_html__( 'Deactivate', 'service-booking' ),
			'bulk-delete'     => esc_html__( 'Delete', 'service-booking' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'form_name'  => array( 'form_name', false ),
			'created_at' => array( 'created_at', true ),
		);
	}

	/**
	 * Checkbox column.
	 *
	 * @param array $item Row data.
	 * @return string
	 */
	public function column_cb( $item ) {
		if ( ! empty( $item['is_default'] ) ) {
			return '<input type="checkbox" disabled title="' . esc_attr__( 'Default form cannot be selected', 'service-booking' ) . '" />';
		}
		return sprintf(
			'<input type="checkbox" name="form_ids[]" value="%d" />',
			absint( $item['id'] )
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

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-forms' ) ) {
			return;
		}

		$ids = isset( $_REQUEST['form_ids'] ) ? array_map( 'absint', (array) $_REQUEST['form_ids'] ) : array();
		if ( empty( $ids ) ) {
			return;
		}

		$activator  = new Booking_Management_Activator();

		foreach ( $ids as $id ) {
			if ( $id <= 0 ) {
				continue;
			}

			// Protect the default form from deletion.
			$form = $this->dbhandler->get_row( 'BILLING_FORMS', $id );
			if ( ! $form ) {
				continue;
			}
			$is_default = isset( $form->is_default ) ? (int) $form->is_default : 0;

			switch ( $action ) {
				case 'bulk-delete':
					if ( ! $is_default ) {
						// Remove all fields belonging to this form first.
						$GLOBALS['wpdb']->delete(
							$activator->get_db_table_name( 'FIELDS' ),
							array( 'form_id' => $id ),
							array( '%d' )
						);
						$this->dbhandler->remove_row( 'BILLING_FORMS', 'id', $id, '%d' );
					}
					break;

				case 'bulk-activate':
					$this->dbhandler->update_row(
						'BILLING_FORMS',
						'id',
						$id,
						array( 'is_active' => 1 ),
						array( '%d' ),
						array( '%d' )
					);
					break;

				case 'bulk-deactivate':
					if ( ! $is_default ) {
						$this->dbhandler->update_row(
							'BILLING_FORMS',
							'id',
							$id,
							array( 'is_active' => 0 ),
							array( '%d' ),
							array( '%d' )
						);
					}
					break;
			}
		}
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'bm_list_per_page', 20 );
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$orderby = 'id';
		$order   = 'ASC';

		if ( isset( $_REQUEST['orderby'] ) ) {
			$allowed = array( 'form_name', 'created_at' );
			$req_orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
			if ( in_array( $req_orderby, $allowed, true ) ) {
				$orderby = $req_orderby;
			}
		}
		if ( isset( $_REQUEST['order'] ) ) {
			$req_order = strtoupper( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) );
			if ( in_array( $req_order, array( 'ASC', 'DESC' ), true ) ) {
				$order = $req_order;
			}
		}

		$total = (int) $this->dbhandler->bm_count( 'BILLING_FORMS' );
		$forms = $this->dbhandler->get_all_result( 'BILLING_FORMS', '*', 1, 'results', $offset, $per_page, $orderby, $order );

		$activator = new Booking_Management_Activator();
		$fields_table = $activator->get_db_table_name( 'FIELDS' );

		$this->items = array();
		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$form_id = (int) $form->id;

				// Count fields for this form.
				$field_count = (int) $GLOBALS['wpdb']->get_var(
					$GLOBALS['wpdb']->prepare(
						"SELECT COUNT(*) FROM {$fields_table} WHERE form_id = %d",
						$form_id
					)
				);

				$this->items[] = array(
					'id'          => $form_id,
					'form_name'   => isset( $form->form_name ) ? $form->form_name : '',
					'description' => isset( $form->form_description ) ? $form->form_description : '',
					'fields'      => $field_count,
					'is_active'   => isset( $form->is_active ) ? (int) $form->is_active : 1,
					'is_default'  => isset( $form->is_default ) ? (int) $form->is_default : 0,
					'created_at'  => isset( $form->created_at ) ? $form->created_at : '',
				);
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

			case 'form_name':
				$edit_url = admin_url( 'admin.php?page=sg-booking-form-builder&form_id=' . absint( $item['id'] ) );
				$name     = '<strong><a href="' . esc_url( $edit_url ) . '">' . esc_html( $item['form_name'] ) . '</a></strong>';

				if ( $item['is_default'] ) {
					$name .= ' <span style="background:#e8f5e9;color:#2e7d32;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:600;">' . esc_html__( 'Default', 'service-booking' ) . '</span>';
				}

				if ( ! empty( $item['description'] ) ) {
					$name .= '<br><span class="description" style="color:#666;">' . esc_html( $item['description'] ) . '</span>';
				}

				// Row actions.
				$actions = array();
				$actions['edit'] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( $edit_url ),
					esc_html__( 'Edit', 'service-booking' )
				);

				if ( ! $item['is_default'] ) {
					$delete_url = wp_nonce_url(
						admin_url( 'admin.php?page=sg-booking-forms&action=delete&form_id=' . absint( $item['id'] ) ),
						'delete-form-' . absint( $item['id'] )
					);
					$actions['delete'] = sprintf(
						'<a href="%s" class="bm-delete-form" style="color:#b32d2e;" onclick="return confirm(\'%s\');">%s</a>',
						esc_url( $delete_url ),
						esc_attr__( 'Are you sure you want to delete this form and all its fields?', 'service-booking' ),
						esc_html__( 'Delete', 'service-booking' )
					);
				}

				return $name . $this->row_actions( $actions );

			case 'fields':
				return '<span style="font-weight:600;">' . absint( $item['fields'] ) . '</span> ' . esc_html__( 'fields', 'service-booking' );

			case 'status':
				if ( $item['is_active'] ) {
					return '<span style="display:inline-flex;align-items:center;gap:4px;background:#e8f5e9;color:#2e7d32;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">'
						. '<span class="dashicons dashicons-yes-alt" style="font-size:14px;width:14px;height:14px;"></span>'
						. esc_html__( 'Active', 'service-booking' )
						. '</span>';
				}
				return '<span style="display:inline-flex;align-items:center;gap:4px;background:#fce4ec;color:#c62828;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">'
					. '<span class="dashicons dashicons-minus" style="font-size:14px;width:14px;height:14px;"></span>'
					. esc_html__( 'Inactive', 'service-booking' )
					. '</span>';

			case 'created_at':
				if ( empty( $item['created_at'] ) ) {
					return '&mdash;';
				}
				return esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item['created_at'] ) ) );

			default:
				return '';
		}
	}

	/**
	 * Message when no items are found.
	 */
	public function no_items() {
		esc_html_e( 'No booking forms found.', 'service-booking' );
	}
}
