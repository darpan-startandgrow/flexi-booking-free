<?php
$dbhandler       = new BM_DBhandler();
$templates_table = isset( $this ) && method_exists( $this, 'get_list_table' ) ? $this->get_list_table( 'bm_email_templates' ) : null;
if ( ! $templates_table ) {
	$templates_table = new BM_Email_Templates_List_Table();
}
$templates_table->prepare_items();

$total_count = (int) $dbhandler->bm_count( 'EMAIL_TMPL' );
$limit       = Booking_Management_Limits::FREE_MAIL_TEMPLATE_LIMIT;
$remaining   = Booking_Management_Limits::get_remaining_mail_templates();
$can_create  = Booking_Management_Limits::can_create_mail_template();
?>


<!-- Templates -->
<div class="wrap listing_table" id="templates_records_listing">
	<div class="row" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
		<div>
			<h2 class="title" style="font-weight: bold; margin:0;"><?php esc_html_e( 'Email Templates', 'service-booking' ); ?></h2>
			<p class="description" style="margin:4px 0 0;">
				<?php
				printf(
					/* translators: 1: current count, 2: max limit */
					esc_html__( '%1$d of %2$d templates used', 'service-booking' ),
					$total_count,
					$limit
				);
				if ( $remaining > 0 ) {
					echo ' &mdash; ';
					printf(
						/* translators: %d: remaining template slots */
						esc_html__( '%d remaining', 'service-booking' ),
						$remaining
					);
				}
				?>
			</p>
		</div>
		<div>
			<?php if ( $can_create ) : ?>
				<a href="admin.php?page=bm_add_template" class="button button-primary" title="<?php esc_html_e( 'Add Template', 'service-booking' ); ?>"><?php esc_html_e( 'Add Template', 'service-booking' ); ?>&nbsp;<i class="fa fa-plus" aria-hidden="true"></i></a>
			<?php else : ?>
				<button class="button" disabled aria-disabled="true" title="<?php echo esc_attr( Booking_Management_Limits::get_limit_message( 'mail_templates' ) ); ?>">
					<?php esc_html_e( 'Add Template', 'service-booking' ); ?>&nbsp;<i class="fa fa-plus" aria-hidden="true"></i>
				</button>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( ! $can_create ) : ?>
	<div style="margin-bottom:15px; padding:12px 16px; background:#fff8e1; border-left:4px solid #ffb300; border-radius:3px;">
		<p style="margin:0;">
			<span class="dashicons dashicons-info" style="color:#ffb300;"></span>
			<?php echo esc_html( Booking_Management_Limits::get_limit_message( 'mail_templates' ) ); ?>
		</p>
	</div>
	<?php endif; ?>

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


