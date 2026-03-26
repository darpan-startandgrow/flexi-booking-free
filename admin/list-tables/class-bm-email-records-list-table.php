<?php
/**
 * Email Records List Table.
 *
 * Uses the WP_List_Table class to render the email records listing
 * with server-side pagination, search, and date filters.
 * Free version: read-only listing (no resend).
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
	 * @return array
	 */
	public function get_columns() {
		return array(
			'serial'     => esc_html__( '#', 'service-booking' ),
			'mail_to'    => esc_html__( 'Recipient', 'service-booking' ),
			'mail_sub'   => esc_html__( 'Subject', 'service-booking' ),
			'created_at' => esc_html__( 'Date', 'service-booking' ),
			'status'     => esc_html__( 'Status', 'service-booking' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'created_at' => array( 'created_at', true ),
		);
	}

	/**
	 * Prepare data for the table.
	 */
	public function prepare_items() {
		$per_page = ! empty( $_REQUEST['per_page'] )
			? absint( $_REQUEST['per_page'] )
			: 20;

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
			$additional .= $GLOBALS['wpdb']->prepare(
				' AND YEAR(e.created_at) = %d AND MONTH(e.created_at) = %d',
				$year,
				$month
			);
		}

		// Filter by status.
		if ( isset( $_REQUEST['status_filter'] ) && '' !== $_REQUEST['status_filter'] ) {
			$status_val  = absint( $_REQUEST['status_filter'] );
			$additional .= $GLOBALS['wpdb']->prepare( ' AND e.status = %d', $status_val );
		}

		// Joins.
		$joins = array(
			array(
				'table' => 'BOOKING',
				'alias' => 'b',
				'on'    => 'e.module_id = b.id',
				'type'  => 'LEFT',
			),
		);

		$columns = 'e.id, e.mail_to, e.mail_sub, e.created_at, e.status';

		// Paginated results.
		$results = $this->dbhandler->get_results_with_join(
			array( 'EMAILS', 'e' ),
			$columns,
			$joins,
			$where,
			'results',
			$offset,
			$per_page,
			'e.created_at',
			true,
			$additional,
			false,
			10000,
			OBJECT
		);

		$this->items = array();
		if ( ! empty( $results ) ) {
			$i = 1 + $offset;
			foreach ( $results as $row ) {
				$this->items[] = array(
					'serial'     => $i,
					'mail_to'    => isset( $row->mail_to ) ? $row->mail_to : '',
					'mail_sub'   => isset( $row->mail_sub ) ? $row->mail_sub : '',
					'created_at' => isset( $row->created_at ) ? $row->created_at : '',
					'status'     => isset( $row->status ) ? (int) $row->status : 0,
				);
				$i++;
			}
		}

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
				'total_pages' => ceil( intval( $total ) / $per_page ),
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

			case 'mail_to':
				return esc_html( $item['mail_to'] );

			case 'mail_sub':
				return sprintf(
					'<span title="%s">%s</span>',
					esc_attr( $item['mail_sub'] ),
					esc_html( mb_strimwidth( $item['mail_sub'], 0, 50, '...' ) )
				);

			case 'created_at':
				return ! empty( $item['created_at'] )
					? esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item['created_at'] ) ) )
					: '—';

			case 'status':
				if ( 1 === $item['status'] ) {
					return '<span style="color:green;font-weight:600;">' . esc_html__( 'Sent', 'service-booking' ) . '</span>';
				}
				return '<span style="color:red;font-weight:600;">' . esc_html__( 'Failed', 'service-booking' ) . '</span>';

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

		echo '<div class="alignleft actions">';
		$this->months_dropdown( 'email_record' );

		// Status filter.
		echo '<select name="status_filter">';
		echo '<option value="">' . esc_html__( 'All Statuses', 'service-booking' ) . '</option>';
		printf( '<option value="1"%s>%s</option>', selected( $status_filter, '1', false ), esc_html__( 'Sent', 'service-booking' ) );
		printf( '<option value="0"%s>%s</option>', selected( $status_filter, '0', false ), esc_html__( 'Failed', 'service-booking' ) );
		echo '</select>';

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
