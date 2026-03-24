<?php
/**
 * Email Records listing page.
 *
 * Displays a simple read-only listing of all sent emails using WP_List_Table.
 * Columns: Recipient, Subject, Date, Status.
 * Resend email feature is Pro-only.
 *
 * @since      1.2.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/partials
 */

$is_pro = Booking_Management_Limits::is_pro_active();
$table  = new BM_Email_Records_List_Table();
$table->prepare_items();
?>

<div class="sg-admin-main-box" id="email-records-main-box">
<div class="wrap listing_table">
	<h2 class="title" style="font-weight: bold;"><?php esc_html_e( 'Email Records', 'service-booking' ); ?></h2>

	<form method="get">
		<input type="hidden" name="page" value="bm_email_records" />
		<?php $table->search_box( __( 'Search Recipient/Subject', 'service-booking' ), 'email_search' ); ?>
		<?php $table->display(); ?>
	</form>
</div>

<?php if ( ! $is_pro ) : ?>
<div style="margin-top: 15px; padding: 12px 16px; background: #fff8e1; border-left: 4px solid #ffb300; border-radius: 3px;">
	<p style="margin: 0;">
		<span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
		<strong><?php esc_html_e( 'Resend Emails', 'service-booking' ); ?></strong>
		<span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span><br />
		<small><?php esc_html_e( 'Upgrade to Pro to resend emails with CC/BCC, attachments, and full customization.', 'service-booking' ); ?></small>
	</p>
</div>
<?php endif; ?>

<div class="loader_modal"></div>
</div>
