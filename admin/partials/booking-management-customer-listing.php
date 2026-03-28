<?php
$customers_table = isset( $this ) && method_exists( $this, 'get_list_table' ) ? $this->get_list_table( 'bm_all_customers' ) : null;
if ( ! $customers_table ) {
	$customers_table = new BM_Customers_List_Table();
}
$customers_table->prepare_items();
?>


<!-- Customers -->
<div class="wrap listing_table" id="customer_records_listing">
    <div class="row">
        <span style="display: inline-block;width:50%;">
            <h2 class="title" style="font-weight: bold;"><?php esc_html_e( 'All Customers', 'service-booking' ); ?></h2>
            <button class="button" disabled aria-disabled="true" title="<?php esc_attr_e( 'Add Customer — Pro Feature', 'service-booking' ); ?>">
                <?php esc_html_e( 'Add Customer', 'service-booking' ); ?>&nbsp;<i class="fa fa-plus" aria-hidden="true"></i>
                <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span>
            </button>
        </span>
    </div>
    <form method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '' ); ?>" />
        <?php $customers_table->search_box( __( 'Search Customers', 'service-booking' ), 'customer_search' ); ?>
        <?php $customers_table->display(); ?>
    </form>
</div>

<div style="margin-top: 15px; padding: 12px 16px; background: #fff8e1; border-left: 4px solid #ffb300; border-radius: 3px;">
    <p style="margin: 0;">
        <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
        <strong><?php esc_html_e( 'Customer Management', 'service-booking' ); ?></strong>
        <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span><br />
        <small><?php echo esc_html( Booking_Management_Limits::get_limit_message( 'customer_creation' ) ); ?></small>
    </p>
</div>

<div class="popup-message-overlay" id="popup-message-overlay"></div>
<div class="popup-message-container animate__animated animate__swing" id="popup-message-container">
    <span id="popup-message"></span>
    <button class="close-popup-message" id="close-popup-message" title="<?php esc_html_e( 'Close', 'service-booking' ); ?>"><?php echo esc_html( '✕' ); ?></button>
</div>

<div class="loader_modal"></div>