<?php
/**
 * Email Records List Table.
 *
 * Uses the WP_List_Table class to render the email records listing
 * with server-side pagination, search, date/status/type filters, and bulk actions.
 * Columns: ID, Type, Recipient, Order Details, Mail Body, Total Mails Sent, Actions.
 * Free version: read-only listing (resend is Pro-only with teaser UI).
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

class BM_Email_Records_List_Table extends WP_List_Table {

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
				'singular' => 'email_record',
				'plural'   => 'email_records',
				'ajax'     => false,
			)
		);
		$this->dbhandler  = new BM_DBhandler();
		$this->bmrequests = new BM_Request();
	}

	/**
	 * Define columns.
	 *
	 * Columns: ID, Type, Recipient, Order Details, Mail Body, Total Mails Sent, Actions.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'              => '<input type="checkbox" />',
			'id'              => esc_html__( 'ID', 'service-booking' ),
			'mail_type'       => esc_html__( 'Type', 'service-booking' ),
			'mail_to'         => esc_html__( 'Recipient', 'service-booking' ),
			'order_details'   => esc_html__( 'Order Details', 'service-booking' ),
			'mail_body'       => esc_html__( 'Mail Body', 'service-booking' ),
			'total_sent'      => esc_html__( 'Total Mails Sent', 'service-booking' ),
			'status'          => esc_html__( 'Status', 'service-booking' ),
			'created_at'      => esc_html__( 'Date', 'service-booking' ),
			'actions'         => esc_html__( 'Actions', 'service-booking' ),
		);
	}

	/**
	 * Checkbox column for bulk actions.
	 *
	 * @param array $item Row data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="email_ids[]" value="%d" />', absint( $item['id'] ) );
	}

	/**
	 * Bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'bulk_delete' => esc_html__( 'Delete', 'service-booking' ),
		);
	}

	/**
	 * Process bulk actions.
	 */
	public function process_bulk_action() {
		if ( 'bulk_delete' !== $this->current_action() ) {
			return;
		}

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-email_records' ) ) {
			return;
		}

		$ids = isset( $_REQUEST['email_ids'] ) ? array_map( 'absint', (array) $_REQUEST['email_ids'] ) : array();

		/**
		 * Fires before bulk-deleting email records.
		 *
		 * @since 1.2.0
		 * @param array $ids Email record IDs to delete.
		 */
		do_action( 'sg_booking_before_email_records_bulk_delete', $ids );

		foreach ( $ids as $id ) {
			if ( $id > 0 ) {
				$this->dbhandler->remove_row( 'EMAILS', 'id', $id, '%d' );
			}
		}

		/**
		 * Fires after bulk-deleting email records.
		 *
		 * @since 1.2.0
		 * @param array $ids Email record IDs that were deleted.
		 */
		do_action( 'sg_booking_after_email_records_bulk_delete', $ids );
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'id'         => array( 'e.id', false ),
			'created_at' => array( 'e.created_at', true ),
			'mail_to'    => array( 'e.mail_to', false ),
		);
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$per_page = $this->get_items_per_page( 'bm_list_per_page', 20 );

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$where      = array();
		$additional = '';

		// Search across mail_to and mail_sub.
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search      = '%' . $GLOBALS['wpdb']->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%';
			$additional .= $GLOBALS['wpdb']->prepare(
				' AND (e.mail_to LIKE %s OR e.mail_sub LIKE %s)',
				$search,
				$search
			);
		}

		// Filter by date range (year-month).
		if ( ! empty( $_REQUEST['m'] ) ) {
			$yearmonth   = sanitize_text_field( wp_unslash( $_REQUEST['m'] ) );
			$year        = absint( substr( $yearmonth, 0, 4 ) );
			$month       = absint( substr( $yearmonth, 4, 2 ) );
			if ( $year > 0 && $month > 0 ) {
				$additional .= $GLOBALS['wpdb']->prepare(
					' AND YEAR(e.created_at) = %d AND MONTH(e.created_at) = %d',
					$year,
					$month
				);
			}
		}

		// Filter by status.
		if ( isset( $_REQUEST['status_filter'] ) && '' !== $_REQUEST['status_filter'] ) {
			$status_val  = absint( $_REQUEST['status_filter'] );
			$additional .= $GLOBALS['wpdb']->prepare( ' AND e.status = %d', $status_val );
		}

		// Filter by email type.
		if ( ! empty( $_REQUEST['type_filter'] ) ) {
			$type_val    = sanitize_text_field( wp_unslash( $_REQUEST['type_filter'] ) );
			$additional .= $GLOBALS['wpdb']->prepare( ' AND e.mail_type = %s', $type_val );
		}

		// Filter by booking/order ID.
		if ( ! empty( $_REQUEST['booking_id'] ) ) {
			$additional .= $GLOBALS['wpdb']->prepare( ' AND e.module_id = %d', absint( $_REQUEST['booking_id'] ) );
		}

		/**
		 * Filters the additional WHERE clause for the email records list table query.
		 *
		 * @since 1.2.0
		 * @param string $additional Raw SQL additional conditions.
		 */
		$additional = apply_filters( 'sg_booking_email_records_query_additional', $additional );

		// Joins.
		$joins = array(
			array(
				'table' => 'BOOKING',
				'alias' => 'b',
				'on'    => 'e.module_id = b.id',
				'type'  => 'LEFT',
			),
		);

		$columns = 'e.id, e.module_id, e.mail_type, e.mail_to, e.mail_sub, e.mail_body, e.is_resent, e.status, e.created_at, b.service_name';

		// Determine sort column.
		$orderby = 'e.created_at';
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$allowed = array( 'e.id', 'e.created_at', 'e.mail_to' );
			$req_ob  = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
			if ( in_array( $req_ob, $allowed, true ) ) {
				$orderby = $req_ob;
			}
		}
		$desc = true;
		if ( ! empty( $_REQUEST['order'] ) && 'asc' === strtolower( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) ) {
			$desc = false;
		}

		// Paginated results.
		$results = $this->dbhandler->get_results_with_join(
			array( 'EMAILS', 'e' ),
			$columns,
			$joins,
			$where,
			'results',
			$offset,
			$per_page,
			$orderby,
			$desc,
			$additional,
			false,
			10000,
			OBJECT
		);

		// Build a map of total emails sent per module_id+mail_to+mail_type for the "Total Mails Sent" column.
		$sent_counts = array();
		if ( ! empty( $results ) ) {
			$module_ids = array();
			foreach ( $results as $row ) {
				if ( ! empty( $row->module_id ) ) {
					$module_ids[] = absint( $row->module_id );
				}
			}
			if ( ! empty( $module_ids ) ) {
				$module_ids   = array_unique( $module_ids );
				$placeholders = implode( ', ', array_fill( 0, count( $module_ids ), '%d' ) );
				$activator    = new Booking_Management_Activator();
				$emails_table = esc_sql( $activator->get_db_table_name( 'EMAILS' ) );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- placeholders are safe.
				$count_sql    = $GLOBALS['wpdb']->prepare(
					"SELECT module_id, mail_to, mail_type, COUNT(*) as cnt FROM $emails_table WHERE module_id IN ($placeholders) GROUP BY module_id, mail_to, mail_type",
					...$module_ids
				);
				$count_rows   = $GLOBALS['wpdb']->get_results( $count_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				if ( ! empty( $count_rows ) ) {
					foreach ( $count_rows as $cr ) {
						$key                = $cr->module_id . '|' . $cr->mail_to . '|' . $cr->mail_type;
						$sent_counts[ $key ] = (int) $cr->cnt;
					}
				}
			}
		}

		$this->items = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				$count_key = ( isset( $row->module_id ) ? $row->module_id : '0' ) . '|' . ( isset( $row->mail_to ) ? $row->mail_to : '' ) . '|' . ( isset( $row->mail_type ) ? $row->mail_type : '' );

				$this->items[] = array(
					'id'            => isset( $row->id ) ? (int) $row->id : 0,
					'module_id'     => isset( $row->module_id ) ? (int) $row->module_id : 0,
					'mail_type'     => isset( $row->mail_type ) ? $row->mail_type : '',
					'mail_to'       => isset( $row->mail_to ) ? $row->mail_to : '',
					'mail_sub'      => isset( $row->mail_sub ) ? $row->mail_sub : '',
					'mail_body'     => isset( $row->mail_body ) ? $row->mail_body : '',
					'service_name'  => isset( $row->service_name ) ? $row->service_name : '',
					'total_sent'    => isset( $sent_counts[ $count_key ] ) ? $sent_counts[ $count_key ] : 1,
					'status'        => isset( $row->status ) ? (int) $row->status : 0,
					'created_at'    => isset( $row->created_at ) ? $row->created_at : '',
				);
			}
		}

		/**
		 * Filters the email records items before they are displayed.
		 *
		 * @since 1.2.0
		 * @param array $items Array of email record row data.
		 */
		$this->items = apply_filters( 'sg_booking_email_records_items', $this->items );

		// Total count.
		$total = $this->dbhandler->get_results_with_join(
			array( 'EMAILS', 'e' ),
			'COUNT(*) as total',
			$joins,
			$where,
			'var',
			0,
			false,
			null,
			false,
			$additional,
			false,
			10000,
			OBJECT
		);

		$this->set_pagination_args(
			array(
				'total_items' => intval( $total ),
				'per_page'    => $per_page,
				'total_pages' => ceil( intval( $total ) / max( 1, $per_page ) ),
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
			case 'id':
				return esc_html( $item['id'] );

			case 'mail_type':
				$type_label = $this->bmrequests->bm_fetch_email_type( $item['mail_type'] );
				if ( empty( $type_label ) ) {
					$type_label = ucfirst( str_replace( '_', ' ', $item['mail_type'] ) );
				}
				return sprintf(
					'<span class="bm-email-type-badge" style="background:#e3f2fd;color:#1565c0;padding:2px 8px;border-radius:3px;font-size:12px;white-space:nowrap;">%s</span>',
					esc_html( $type_label )
				);

			case 'mail_to':
				return sprintf( '<a href="mailto:%1$s">%1$s</a>', esc_html( $item['mail_to'] ) );

			case 'order_details':
				if ( empty( $item['module_id'] ) || 0 === $item['module_id'] ) {
					return '—';
				}
				$order_url = admin_url( 'admin.php?page=bm_single_order&booking_id=' . $item['module_id'] );
				$svc_name  = ! empty( $item['service_name'] ) ? esc_html( $item['service_name'] ) : '';
				$output    = '<a href="' . esc_url( $order_url ) . '">#' . esc_html( $item['module_id'] ) . '</a>';
				if ( $svc_name ) {
					$output .= '<br/><small style="color:#666;">' . $svc_name . '</small>';
				}
				return $output;

			case 'mail_body':
				if ( empty( $item['mail_body'] ) ) {
					return '—';
				}
				$stripped = wp_strip_all_tags( $item['mail_body'] );
				$preview  = mb_strimwidth( $stripped, 0, 80, '…' );
				return sprintf(
					'<span title="%s" style="cursor:help;">%s</span>',
					esc_attr( mb_strimwidth( $stripped, 0, 300, '…' ) ),
					esc_html( $preview )
				);

			case 'total_sent':
				return '<span style="font-weight:600;">' . esc_html( $item['total_sent'] ) . '</span>';

			case 'status':
				if ( 1 === $item['status'] ) {
					return '<span style="color:#2e7d32;font-weight:600;">' . esc_html__( 'Sent', 'service-booking' ) . '</span>';
				}
				return '<span style="color:#c62828;font-weight:600;">' . esc_html__( 'Failed', 'service-booking' ) . '</span>';

			case 'created_at':
				return ! empty( $item['created_at'] )
					? esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item['created_at'] ) ) )
					: '—';

			case 'actions':
				// Pro-only Resend teaser.
				return sprintf(
					'<span class="bm-pro-resend-teaser" style="display:inline-flex;align-items:center;gap:4px;opacity:0.65;cursor:not-allowed;" title="%s">'
					. '<span class="dashicons dashicons-lock" style="font-size:14px;width:14px;height:14px;color:#7b1fa2;"></span>'
					. '<span style="color:#999;font-size:12px;">%s</span>'
					. '&nbsp;<span class="sg-pro-badge" style="font-size:10px;">%s</span>'
					. '</span>',
					esc_attr__( 'Resend email is a Pro feature. Upgrade to unlock.', 'service-booking' ),
					esc_html__( 'Resend', 'service-booking' ),
					esc_html__( 'Pro', 'service-booking' )
				);

			default:
				return '';
		}
	}

	/**
	 * Extra filter controls above the table.
	 *
	 * @param string $which Top or bottom.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$status_filter = isset( $_REQUEST['status_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['status_filter'] ) ) : '';
		$type_filter   = isset( $_REQUEST['type_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type_filter'] ) ) : '';
		$booking_id    = isset( $_REQUEST['booking_id'] ) ? absint( $_REQUEST['booking_id'] ) : '';

		echo '<div class="alignleft actions">';

		// Month dropdown.
		$this->months_dropdown( 'email_record' );

		// Status filter.
		echo '<select name="status_filter">';
		echo '<option value="">' . esc_html__( 'All Statuses', 'service-booking' ) . '</option>';
		printf( '<option value="1"%s>%s</option>', selected( $status_filter, '1', false ), esc_html__( 'Sent', 'service-booking' ) );
		printf( '<option value="0"%s>%s</option>', selected( $status_filter, '0', false ), esc_html__( 'Failed', 'service-booking' ) );
		echo '</select>';

		// Type filter.
		$types = array(
			'new_order'      => __( 'New Order', 'service-booking' ),
			'cancel_order'   => __( 'Cancel Order', 'service-booking' ),
			'failed_order'   => __( 'Failed Order', 'service-booking' ),
			'approved_order' => __( 'Approved Order', 'service-booking' ),
			'refund_order'   => __( 'Refund Order', 'service-booking' ),
			'gift_voucher'   => __( 'Gift Voucher', 'service-booking' ),
			'new_request'    => __( 'New Request', 'service-booking' ),
			'voucher_redeem' => __( 'Voucher Redeem', 'service-booking' ),
		);

		echo '<select name="type_filter">';
		echo '<option value="">' . esc_html__( 'All Types', 'service-booking' ) . '</option>';
		foreach ( $types as $key => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $key ),
				selected( $type_filter, $key, false ),
				esc_html( $label )
			);
		}
		echo '</select>';

		// Booking ID filter.
		printf(
			'<input type="text" name="booking_id" placeholder="%s" value="%s" size="8" style="height:30px;" />',
			esc_attr__( 'Order ID', 'service-booking' ),
			esc_attr( $booking_id )
		);

		submit_button( __( 'Filter', 'service-booking' ), '', 'filter_action', false );
		echo '</div>';
	}

	/**
	 * Message when no items are found.
	 */
	public function no_items() {
		esc_html_e( 'No Email Records Found', 'service-booking' );
	}
}
