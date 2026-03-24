<?php
$is_pro       = Booking_Management_Limits::is_pro_active();
$dbhandler    = new BM_DBhandler();
$bmrequests   = new BM_Request();
$activator    = new Booking_Management_Activator();
$pagenum      = filter_input( INPUT_GET, 'pagenum' );
$pagenum      = isset( $pagenum ) ? absint( $pagenum ) : 1;
$limit        = ! empty( $dbhandler->get_global_option_value( 'bm_voucher_records_per_page' ) ) ? absint( $dbhandler->get_global_option_value( 'bm_voucher_records_per_page' ) ) : 10;
$offset       = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;
$i            = ( 1 + $offset );

// Status filter from URL.
$filter_status = filter_input( INPUT_GET, 'status' );
$filter_status = ( $filter_status !== null && $filter_status !== '' ) ? absint( $filter_status ) : '';

global $wpdb;
$voucher_table = $activator->get_db_table_name( 'VOUCHERS' );
$booking_table = $activator->get_db_table_name( 'BOOKING' );

// Build WHERE clause for status filter.
$where_sql  = '';
$where_args = array();
if ( $filter_status !== '' ) {
	$where_sql    = 'WHERE v.status = %d';
	$where_args[] = $filter_status;
}

// Count total.
if ( ! empty( $where_args ) ) {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names from get_db_table_name() are hardcoded
	$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$voucher_table} v {$where_sql}", $where_args ) );
} else {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$voucher_table} v" );
}

// Fetch vouchers with service name via LEFT JOIN to BOOKING table.
$query_args = $where_args;
$query_args[] = $limit;
$query_args[] = $offset;
if ( ! empty( $where_args ) ) {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$vouchers = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT v.id, v.code, v.booking_id, v.status, v.created_at, b.service_name
			FROM {$voucher_table} v
			LEFT JOIN {$booking_table} b ON v.booking_id = b.id
			{$where_sql}
			ORDER BY v.id DESC LIMIT %d OFFSET %d",
			$query_args
		)
	);
} else {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$vouchers = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT v.id, v.code, v.booking_id, v.status, v.created_at, b.service_name
			FROM {$voucher_table} v
			LEFT JOIN {$booking_table} b ON v.booking_id = b.id
			ORDER BY v.id DESC LIMIT %d OFFSET %d",
			$limit,
			$offset
		)
	);
}

$num_of_pages = ( $limit > 0 ) ? ceil( $total / $limit ) : 1;
$pagination   = $dbhandler->bm_get_pagination( $num_of_pages, $pagenum, $bmrequests->bm_get_page_url(), 'list' );

// Current page URL for filter links.
$page_url = admin_url( 'admin.php?page=bm_voucher_records' );

?>

<div class="sg-admin-main-box">
<!-- Vouchers -->
<div class="wrap listing_table" id="vocuher_records_listing">
    <div class="row">
        <div>
            <h2 class="title" style="font-weight: bold;"><?php esc_html_e( 'Vouchers', 'service-booking' ); ?></h2>
        </div>
    </div>

    <!-- Status filter -->
    <div class="tablenav top" style="margin-bottom: 10px;">
        <div class="alignleft">
            <a href="<?php echo esc_url( $page_url ); ?>" class="button <?php echo ( $filter_status === '' ) ? 'button-primary' : ''; ?>"><?php esc_html_e( 'All', 'service-booking' ); ?></a>
            <a href="<?php echo esc_url( add_query_arg( 'status', '1', $page_url ) ); ?>" class="button <?php echo ( $filter_status === 1 ) ? 'button-primary' : ''; ?>"><?php esc_html_e( 'Active', 'service-booking' ); ?></a>
            <a href="<?php echo esc_url( add_query_arg( 'status', '0', $page_url ) ); ?>" class="button <?php echo ( $filter_status === 0 ) ? 'button-primary' : ''; ?>"><?php esc_html_e( 'Inactive', 'service-booking' ); ?></a>
        </div>
        <div class="alignright">
            <span class="displaying-num">
                <?php
                printf(
                    /* translators: %d: Total number of voucher items */
                    esc_html( _n( '%d item', '%d items', $total, 'service-booking' ) ),
                    (int) $total
                );
                ?>
            </span>
        </div>
        <br class="clear" />
    </div>

    <?php if ( ! empty( $vouchers ) ) { ?>
        <input type="hidden" name="pagenum" value="<?php echo esc_attr( $pagenum ); ?>" />
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th width="5%" style="text-align: center;font-weight: 600;"><?php esc_html_e( '#', 'service-booking' ); ?></th>
                    <th style="text-align: center;font-weight: 600;"><?php esc_html_e( 'Voucher Code', 'service-booking' ); ?></th>
                    <th style="text-align: center;font-weight: 600;"><?php esc_html_e( 'Service', 'service-booking' ); ?></th>
                    <th style="text-align: center;font-weight: 600;"><?php esc_html_e( 'Status', 'service-booking' ); ?></th>
                    <th style="text-align: center;font-weight: 600;"><?php esc_html_e( 'Created Date', 'service-booking' ); ?></th>
                </tr>
            </thead>
            <tbody class="vocuher_records">
                <?php
                foreach ( $vouchers as $voucher ) {
                    $status_label = ( (int) $voucher->status === 1 ) ? __( 'Active', 'service-booking' ) : __( 'Inactive', 'service-booking' );
                    $status_class = ( (int) $voucher->status === 1 ) ? 'color: green;' : 'color: #999;';
                    ?>
                    <tr>
                        <td style="text-align: center;"><?php echo esc_html( $i ); ?></td>
                        <td style="text-align: center;" title="<?php echo isset( $voucher->code ) ? esc_attr( $voucher->code ) : ''; ?>">
                            <?php echo isset( $voucher->code ) ? esc_html( mb_strimwidth( $voucher->code, 0, 40, '...' ) ) : ''; ?>
                        </td>
                        <td style="text-align: center;">
                            <?php echo isset( $voucher->service_name ) && ! empty( $voucher->service_name ) ? esc_html( $voucher->service_name ) : '—'; ?>
                        </td>
                        <td style="text-align: center; <?php echo esc_attr( $status_class ); ?>">
                            <?php echo esc_html( $status_label ); ?>
                        </td>
                        <td style="text-align: center;">
                            <?php echo isset( $voucher->created_at ) ? esc_html( $bmrequests->bm_convert_date_format( $voucher->created_at, 'Y-m-d H:i:s', 'd/m/Y' ) ) : ''; ?>
                        </td>
                    </tr>
                    <?php
                    $i++;
                }
                ?>
            </tbody>
        </table>
        <div class="vocuher_pagination"><?php echo wp_kses_post( $pagination ?? '' ); ?></div>
    <?php } else { ?>
        <div class="bm_no_records_message">
            <div class="Pointer">
                <p class="message"><?php esc_html_e( 'No Vouchers Found', 'service-booking' ); ?></p>
            </div>
        </div>
    <?php } ?>
</div>

<?php if ( ! $is_pro ) { ?>
<div style="margin-top: 15px; padding: 12px 16px; background: #fff8e1; border-left: 4px solid #ffb300; border-radius: 3px;">
    <p style="margin: 0;">
        <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
        <strong><?php esc_html_e( 'Voucher Redemption', 'service-booking' ); ?></strong>
        <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span><br />
        <small><?php echo esc_html( Booking_Management_Limits::get_limit_message( 'voucher_redemption' ) ); ?></small>
    </p>
</div>
<?php } ?>

</div>
