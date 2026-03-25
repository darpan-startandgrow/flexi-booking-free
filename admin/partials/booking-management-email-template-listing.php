<?php
$templates_table = new BM_Email_Templates_List_Table();
$templates_table->prepare_items();
$can_add = Booking_Management_Limits::can_create_mail_template();
?>


<div class="sg-admin-main-box">
<!-- Templates -->
<div class="wrap listing_table" id="templates_records_listing">
	<div class="row">
		<div style="float:left;">
			<h2 class="title" style="font-weight: bold;"><?php esc_html_e( 'Email Templates', 'service-booking' ); ?></h2>
			<?php if ( $can_add ) : ?>
				<a href="admin.php?page=bm_add_template" class="button button-primary" style="margin-bottom:10px;" title="<?php esc_html_e( 'Add Template', 'service-booking' ); ?>"><?php esc_html_e( 'Add Template', 'service-booking' ); ?>&nbsp;<i class="fa fa-plus" aria-hidden="true"></i></a>
			<?php else : ?>
				<button class="button" disabled title="<?php esc_attr_e( 'Template limit reached', 'service-booking' ); ?>"><?php esc_html_e( 'Add Template (Limit Reached)', 'service-booking' ); ?></button>
			<?php endif; ?>
		</div>
	</div>
	<form method="get">
		<input type="hidden" name="page" value="<?php echo esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '' ); ?>" />
		<?php $templates_table->display(); ?>
	</form>
</div>

<div class="popup-message-overlay" id="popup-message-overlay"></div>
<div class="popup-message-container animate__animated animate__jackInTheBox" id="popup-message-container">
	<span id="popup-message"></span>
	<button class="close-popup-message" id="close-popup-message" title="<?php esc_html_e( 'Close', 'service-booking' ); ?>"><?php echo esc_html( '✕' ); ?></button>
</div>

<div class="loader_modal"></div>
</div>


