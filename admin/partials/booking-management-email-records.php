<?php
/**
 * Email Records listing page.
 *
 * Displays a full listing of all sent emails using WP_List_Table.
 * Columns: ID, Type, Recipient, Order Details, Mail Body, Total Mails Sent, Status, Date, Actions.
 * Filters: date, status, email type, booking ID. Bulk actions: delete.
 * Resend email feature is Pro-only with teaser UI.
 *
 * @since      1.2.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/partials
 */

/**
 * Fires before the email records page renders.
 *
 * @since 1.2.0
 */
do_action( 'sg_booking_before_email_records_page' );

$table = new BM_Email_Records_List_Table();
$table->prepare_items();

$total_count = $table->get_pagination_arg( 'total_items' );
?>

<div class="wrap listing_table">
	<div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
		<div>
			<h2 class="title" style="font-weight: bold; margin:0;"><?php esc_html_e( 'Email Records', 'service-booking' ); ?></h2>
			<p class="description" style="margin:4px 0 0;">
				<?php
				printf(
					/* translators: %d: total email record count */
					esc_html__( '%d email records', 'service-booking' ),
					(int) $total_count
				);
				?>
			</p>
		</div>
	</div>

	<div style="margin-bottom:15px; padding:12px 16px; background:#fff8e1; border-left:4px solid #ffb300; border-radius:3px;">
		<p style="margin:0;">
			<span class="dashicons dashicons-lock" style="color:#ffb300;"></span>
			<strong><?php esc_html_e( 'Resend Emails', 'service-booking' ); ?></strong>
			<span class="sg-pro-badge"><?php esc_html_e( 'Pro', 'service-booking' ); ?></span><br />
			<small><?php esc_html_e( 'Upgrade to Pro to resend emails with CC/BCC, attachments, and full customization.', 'service-booking' ); ?></small>
			<?php if ( method_exists( 'Booking_Management_Limits', 'get_pro_upsell_url' ) ) : ?>
				<a href="<?php echo esc_url( Booking_Management_Limits::get_pro_upsell_url() ); ?>" target="_blank" style="font-size:12px;"><?php esc_html_e( 'Upgrade to Pro →', 'service-booking' ); ?></a>
			<?php endif; ?>
		</p>
	</div>

	<form method="get">
		<input type="hidden" name="page" value="bm_email_records" />
		<?php $table->search_box( __( 'Search Recipient / Subject', 'service-booking' ), 'email_search' ); ?>
		<?php $table->display(); ?>
	</form>
</div>

<div class="popup-message-overlay" id="popup-message-overlay"></div>
<div class="popup-message-container animate__animated animate__jackInTheBox" id="popup-message-container">
	<span id="popup-message"></span>
	<button class="close-popup-message" id="close-popup-message" title="<?php esc_html_e( 'Close', 'service-booking' ); ?>"><?php echo esc_html( '✕' ); ?></button>
</div>

<div class="loader_modal"></div>

<?php
/**
 * Fires after the email records page renders.
 *
 * @since 1.2.0
 */
do_action( 'sg_booking_after_email_records_page' );
?>
