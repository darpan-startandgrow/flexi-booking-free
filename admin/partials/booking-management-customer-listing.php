<?php
$is_pro          = Booking_Management_Limits::is_pro_active();
$customers_table = new BM_Customers_List_Table();
$customers_table->prepare_items();
?>


<div class="sg-admin-main-box">
<!-- Customers -->
<div class="wrap listing_table" id="customer_records_listing">
    <div class="row">
        <span style="display: inline-block;width:50%;">
            <h2 class="title" style="font-weight: bold;"><?php esc_html_e( 'All Customers', 'service-booking' ); ?></h2>
            <?php if ( $is_pro ) { ?>
                <a href="admin.php?page=bm_add_customer" class="button button-primary" style="margin-bottom:10px;" title="<?php esc_html_e( 'Add Customer', 'service-booking' ); ?>"><?php esc_html_e( 'Add Customer', 'service-booking' ); ?>&nbsp;<i class="fa fa-plus" aria-hidden="true"></i></a>
            <?php } ?>
        </span>
    </div>
    <form method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '' ); ?>" />
        <?php $customers_table->display(); ?>
    </form>
</div>

<?php if ( ! $is_pro ) { ?>
<div style="margin-top: 15px; padding: 12px 16px; background: #fff8e1; border-left: 4px solid #ffb300; border-radius: 3px;">
    <p style="margin: 0;">
        <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
        <strong><?php esc_html_e( 'Customer Management', 'service-booking' ); ?></strong>
        <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span><br />
        <small><?php echo esc_html( Booking_Management_Limits::get_limit_message( 'customer_creation' ) ); ?></small>
    </p>
</div>
<?php } ?>

<div class="popup-message-overlay" id="popup-message-overlay"></div>
<div class="popup-message-container animate__animated animate__swing" id="popup-message-container">
    <span id="popup-message"></span>
    <button class="close-popup-message" id="close-popup-message" title="<?php esc_html_e( 'Close', 'service-booking' ); ?>"><?php echo esc_html( '✕' ); ?></button>
</div>

<div class="loader_modal"></div>
</div>