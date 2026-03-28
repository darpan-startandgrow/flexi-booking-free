<?php
$vouchers_table = isset( $this ) && method_exists( $this, 'get_list_table' ) ? $this->get_list_table( 'bm_voucher_records' ) : null;
if ( ! $vouchers_table ) {
	$vouchers_table = new BM_Vouchers_List_Table();
}
$vouchers_table->prepare_items();
?>

<!-- Vouchers -->
<div class="wrap listing_table" id="vocuher_records_listing">
    <div class="row">
        <div>
            <h2 class="title" style="font-weight: bold;"><?php esc_html_e( 'Vouchers', 'service-booking' ); ?></h2>
        </div>
    </div>

    <form method="get">
        <input type="hidden" name="page" value="bm_voucher_records" />
        <?php
        $vouchers_table->views();
        $vouchers_table->display();
        ?>
    </form>
</div>

<div style="margin-top: 15px; padding: 12px 16px; background: #fff8e1; border-left: 4px solid #ffb300; border-radius: 3px;">
    <p style="margin: 0;">
        <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
        <strong><?php esc_html_e( 'Voucher Redemption', 'service-booking' ); ?></strong>
        <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span><br />
        <small><?php echo esc_html( Booking_Management_Limits::get_limit_message( 'voucher_redemption' ) ); ?></small>
    </p>
</div>
