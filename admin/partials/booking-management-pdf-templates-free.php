<?php
/**
 * PDF Templates listing for the free version.
 *
 * Displays the 3 default non-customizable PDF templates
 * (Booking Ticket, Voucher PDF, Customer Details PDF)
 * using WP_List_Table.
 *
 * @since      1.1.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pro_url         = Booking_Management_Limits::get_pro_upsell_url();
$pdf_table       = new BM_PDF_Templates_List_Table();
$pdf_table->prepare_items();
?>

<div class="wrap">
	<h1><?php esc_html_e( 'PDF Templates', 'service-booking' ); ?></h1>
	<p><?php esc_html_e( 'The following default PDF templates are available for download. Upgrade to Pro to customize fonts, colors, logos, and layouts.', 'service-booking' ); ?></p>

	<?php $pdf_table->display(); ?>

	<div class="sg-pro-upsell-notice" style="margin-top: 20px; padding: 15px; background: #fff8e1; border-left: 4px solid #ffb300; border-radius: 3px;">
		<p>
			<span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
			<strong><?php esc_html_e( 'Want to customize your PDF templates?', 'service-booking' ); ?></strong>
			<span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span>
			<?php esc_html_e( 'Upgrade to Pro for the full PDF builder with custom fonts, colors, logos, and layouts.', 'service-booking' ); ?>
			<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary" style="margin-left: 10px;" target="_blank">
				<?php esc_html_e( 'Upgrade to Pro', 'service-booking' ); ?>
			</a>
		</p>
	</div>
</div>
