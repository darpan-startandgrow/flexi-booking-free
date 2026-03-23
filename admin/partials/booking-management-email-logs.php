<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class BM_Email_Logs_Table extends WP_List_Table {

    private $dbhandler;
    private $bm_request;

    public function __construct() {
        parent::__construct(
            array(
				'singular' => 'email',
				'plural'   => 'emails',
				'ajax'     => false,
            )
        );
        $this->dbhandler  = new BM_DBhandler();
        $this->bm_request = new BM_Request();
    }

    public function get_columns() {
        return array(
            'cb'            => '<input type="checkbox" />',
            'id'            => __( 'ID', 'service-booking' ),
            'booking_id'    => __( 'Booking ID', 'service-booking' ),
            'service_name'  => __( 'Service', 'service-booking' ),
            'mail_type'     => __( 'Email Type', 'service-booking' ),
            'mail_to'       => __( 'Recipient', 'service-booking' ),
            'mail_sub'      => __( 'Subject', 'service-booking' ),
            'mail_lang'     => __( 'Language', 'service-booking' ),
            'status'        => __( 'Status', 'service-booking' ),
            'error_message' => __( 'Error', 'service-booking' ),
            'created_at'    => __( 'Date', 'service-booking' ),
        );
    }

    protected function column_default( $item, $column_name ) {
        return isset( $item->$column_name ) ? esc_html( $item->$column_name ) : '';
    }

    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="email[]" value="%d" />', $item->id );
    }

    protected function column_booking_id( $item ) {
        if ( empty( $item->module_id ) ) {
			return '—';
        }
        $url = admin_url( 'admin.php?page=bm_single_order&booking_id=' . $item->module_id );
        return '<a href="' . esc_url( $url ) . '">' . $item->module_id . '</a>';
    }

    protected function column_service_name( $item ) {
        return ! empty( $item->service_name ) ? esc_html( $item->service_name ) : '—';
    }

    protected function column_mail_type( $item ) {
        // Use bm_fetch_email_type to get human‑readable name
        return $this->bm_request->bm_fetch_email_type( $item->mail_type );
    }

    protected function column_status( $item ) {
        if ( $item->status == 1 ) {
            return '<span style="color:green;">' . __( 'Success', 'service-booking' ) . '</span>';
        } else {
            return '<span style="color:red;">' . __( 'Failed', 'service-booking' ) . '</span>';
        }
    }

    protected function column_error_message( $item ) {
        return ! empty( $item->error_message ) ? esc_html( $item->error_message ) : '—';
    }

    public function prepare_items() {
		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$where      = array();        // for simple equality conditions
		$additional = '';             // for raw SQL conditions (LIKE, YEAR, MONTH, OR)

		// Search across mail_to and mail_sub (OR logic)
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search      = '%' . $this->dbhandler->get_global_db()->esc_like( $_REQUEST['s'] ) . '%';
			$additional .= $this->dbhandler->get_global_db()->prepare(
                ' AND (e.mail_to LIKE %s OR e.mail_sub LIKE %s)',
                $search,
                $search
			);
		}

		// Filter by email status (exact match)
		if ( ! empty( $_REQUEST['status'] ) && $_REQUEST['status'] !== 'all' ) {
			$where['e.status'] = array( '=' => intval( $_REQUEST['status'] ) );
		}

		// Filter by booking ID (exact match)
		if ( ! empty( $_REQUEST['booking_id'] ) ) {
			$where['e.module_id'] = array( '=' => intval( $_REQUEST['booking_id'] ) );
		}

		// Filter by month (using YEAR() and MONTH() functions)
		if ( ! empty( $_REQUEST['m'] ) ) {
			$yearmonth   = $_REQUEST['m'];
			$year        = substr( $yearmonth, 0, 4 );
			$month       = substr( $yearmonth, 4, 2 );
			$additional .= $this->dbhandler->get_global_db()->prepare(
                ' AND YEAR(e.created_at) = %d AND MONTH(e.created_at) = %d',
                $year,
                $month
			);
		}

		// Joins
		$joins = array(
			array(
				'table' => 'BOOKING',
				'alias' => 'b',
				'on'    => 'e.module_id = b.id',
				'type'  => 'LEFT',
			),
		);

		// Columns for the main query
		$columns = 'e.*, b.service_name';

		// Get paginated results
		$results = $this->dbhandler->get_results_with_join(
            array( 'EMAILS', 'e' ),
            $columns,
            $joins,
            $where,
            'results',
            $offset,
            $per_page,
            'e.created_at',
            true,                    // descending
            $additional,
            false,
            10000,
            OBJECT
		);

		$this->items = $results ?: array();

		// Get total count (same query but COUNT(*) and no limit)
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
            )
        );

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
	}

    protected function extra_tablenav( $which ) {
        if ( $which !== 'top' ) {
			return;
        }
        ?>
        <div class="alignleft actions">
            <select name="status">
                <option value="all"><?php _e( 'All statuses', 'service-booking' ); ?></option>
                <option value="1" <?php selected( $_REQUEST['status'] ?? '', '1' ); ?>><?php _e( 'Success', 'service-booking' ); ?></option>
                <option value="0" <?php selected( $_REQUEST['status'] ?? '', '0' ); ?>><?php _e( 'Failed', 'service-booking' ); ?></option>
            </select>
            <input type="text" name="booking_id" placeholder="<?php esc_attr_e( 'Booking ID', 'service-booking' ); ?>" value="<?php echo esc_attr( $_REQUEST['booking_id'] ?? '' ); ?>" size="5" />
            <?php submit_button( __( 'Filter', 'service-booking' ), '', 'filter_action', false ); ?>
        </div>
        <?php
    }
}

// Display the page
$table = new BM_Email_Logs_Table();
$table->prepare_items();
?>
<div class="wrap">
    <h1><?php _e( 'Email Logs', 'service-booking' ); ?></h1>
    <form method="get">
        <input type="hidden" name="page" value="bm_email_logs" />
        <?php $table->search_box( __( 'Search Recipient/Subject', 'service-booking' ), 'search_id' ); ?>
        <?php $table->display(); ?>
    </form>
</div>
