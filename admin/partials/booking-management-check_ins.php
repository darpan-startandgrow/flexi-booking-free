<?php
$is_pro         = Booking_Management_Limits::is_pro_active();
$checkins_table = new BM_Checkins_List_Table();
$checkins_table->prepare_items();

$bmrequests      = new BM_Request();
$dbhandler       = new BM_DBhandler();
$all_checkins    = $bmrequests->bm_fetch_all_order_checkins();
$unique_services = array();
$added_ids       = array();
if ( ! empty( $all_checkins ) && is_array( $all_checkins ) ) {
	foreach ( $all_checkins as $checkin ) {
		if ( ! in_array( $checkin['service_id'], $added_ids, true ) ) {
			$unique_services[] = array(
				'service_id'   => $checkin['service_id'],
				'service_name' => $checkin['service_name'],
			);
			$added_ids[] = $checkin['service_id'];
		}
	}
}
$plugin_path = plugin_dir_url( __FILE__ );
?>

<!-- Check ins -->
<div class="sg-admin-main-box checkin-listing-admin-main-box">
<div class="wrap listing_table">
    <div class="checkin_listing_top">
        <h2 class="title" style="font-weight: bold;"><?php esc_html_e( 'Check Ins', 'service-booking' ); ?></h2>

        <button id="manual-checkin-btn" class="button button-primary">
            <span class="dashicons dashicons-yes-alt" style="vertical-align: middle;"></span>
            <?php esc_html_e( 'Manual Check-in', 'service-booking' ); ?>
        </button>

        <?php if ( $is_pro ) : ?>
            <button id="ticket-scanner-btn" class="button button-primary">
                <span class="dashicons dashicons-scanner"></span><?php esc_html_e( 'Ticket Scanner', 'service-booking' ); ?>
            </button>
        <?php else : ?>
            <button class="button" disabled title="<?php esc_attr_e( 'QR Ticket Scanner — Pro Feature', 'service-booking' ); ?>">
                <span class="dashicons dashicons-lock" style="vertical-align: middle; color: #ffb300;"></span>
                <?php esc_html_e( 'Ticket Scanner', 'service-booking' ); ?>
                <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span>
            </button>
        <?php endif; ?>
    </div>

    <form method="get">
        <input type="hidden" name="page" value="<?php echo esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '' ); ?>" />
        <?php $checkins_table->search_box( __( 'Search Check-ins', 'service-booking' ), 'checkin_search' ); ?>
        <?php $checkins_table->display(); ?>
    </form>
</div>

<!-- Manual Check-in Modal -->
<div id="manual_checkin-modal" class="checkin-default-modal" style="display:none;">
    <div class="modal-content animate__animated animate__flipInX">
        <div class="fx-modal-header">
            <span class="close">&times;</span>
            <h2 class="fx-manual_checkin-heading"><?php esc_html_e( 'Manual Check-in', 'service-booking' ); ?></h2>
        </div>
        <div class="manual_checkin-container">
            <select id="manual_checkin_type">
                <option value="last_name" selected><?php esc_html_e( 'Search by Last Name', 'service-booking' ); ?></option>
                <option value="email"><?php esc_html_e( 'Search by Email', 'service-booking' ); ?></option>
                <option value="reference"><?php esc_html_e( 'Search by Reference Number', 'service-booking' ); ?></option>
                <option value="service"><?php esc_html_e( 'Search by Service', 'service-booking' ); ?></option>
            </select>

            <input type="text" id="manual_checkin_lastname" class="checkin-input" placeholder="<?php esc_html_e( 'Enter Last Name', 'service-booking' ); ?>" autocomplete="off" />
            <input type="email" id="manual_checkin_email" class="checkin-input hidden" placeholder="<?php esc_html_e( 'Enter Email', 'service-booking' ); ?>" autocomplete="off" />
            <input type="text" id="manual_checkin_reference" class="checkin-input hidden" placeholder="<?php esc_html_e( 'Enter Booking Reference Number', 'service-booking' ); ?>" autocomplete="off" />
            <span id="manual_checkin_service_span" class="select-checkin-input hidden">
                <select id="manual_checkin_service" multiple="multiple">
                    <?php
                    foreach ( $unique_services as $svc ) {
                        echo '<option value="' . esc_attr( $svc['service_id'] ) . '">' . esc_html( $svc['service_name'] ) . '</option>';
                    }
                    ?>
                </select>
            </span>

            <button id="manual-checkin-search" class="button-primary"><?php esc_html_e( 'Search', 'service-booking' ); ?></button>
            <div id="manual_checkin-error"></div>
            <div id="manual_checkin-result"></div>
        </div>
        <div class="manual-cherckin-buttons hidden">
            <button type="button" class="button-primary manual-checkin-button" id="manual-checkin-button" onclick="bm_checkin_manually()"><?php esc_html_e( 'Check In', 'service-booking' ); ?></button>
            <div id="resendProcess" class="hidden">
                <img id="resend_loader" src="<?php echo esc_url( $plugin_path . 'images/ajax-loader.gif' ); ?>">
            </div>
            <button type="button" class="button manual-cancel-button" id="manual-cancel-button"><?php esc_html_e( 'Cancel', 'service-booking' ); ?></button>
        </div>
    </div>
</div>

<?php if ( $is_pro ) : ?>
<!-- Ticket Scanner Modal — Pro Only -->
<div id="scanner-modal" class="checkin-default-modal" style="display:none;">
    <div class="modal-content animate__animated animate__flipInX">
        <span class="close">&times;</span>
        <h2><?php esc_html_e( 'Ticket Scanner', 'service-booking' ); ?></h2>
        <div class="scanner-container">
            <video id="scanner-video" width="100%" playsinline></video>
            <canvas id="scanner-canvas" style="display:none;"></canvas>
            <div id="scanner-result"></div>
        </div>
        <div class="scanner-controls">
            <button id="start-scan" class="button button-primary"><?php esc_html_e( 'Start Scan', 'service-booking' ); ?></button>
            <button id="stop-scan" class="button"><?php esc_html_e( 'Stop Scan', 'service-booking' ); ?></button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ( ! $is_pro ) : ?>
<div style="margin-top: 15px; padding: 12px 16px; background: #fff8e1; border-left: 4px solid #ffb300; border-radius: 3px;">
    <p style="margin: 0;">
        <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
        <strong><?php esc_html_e( 'Advanced Check-in Features', 'service-booking' ); ?></strong>
        <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span><br />
        <small><?php esc_html_e( 'Upgrade to Pro for QR Ticket Scanner, Resend Ticket Email, Manage Columns, Advanced Search Filters, and CSV Export.', 'service-booking' ); ?></small>
    </p>
</div>
<?php endif; ?>

<div class="popup-message-overlay" id="popup-message-overlay"></div>
<div class="popup-message-container animate__animated animate__flipInX" id="popup-message-container">
    <span id="popup-message"></span>
    <button class="close-popup-message" id="close-popup-message" title="<?php esc_html_e( 'Close', 'service-booking' ); ?>"><?php echo esc_html( '✕' ); ?></button>
</div>

<div class="loader_modal"></div>
</div>

