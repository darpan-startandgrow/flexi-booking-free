<?php
/**
 * PDF Templates listing for the free version.
 *
 * Displays the 3 default non-customizable PDF templates
 * (Booking Ticket, Voucher PDF, Customer Details PDF).
 * Users can only download PDFs; no customization is allowed.
 *
 * @since      1.1.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pro_url = Booking_Management_Limits::get_pro_upsell_url();
?>

<div class="wrap">
	<h1><?php esc_html_e( 'PDF Templates', 'service-booking' ); ?></h1>
	<p><?php esc_html_e( 'The following default PDF templates are available for download. Upgrade to Pro to customize fonts, colors, logos, and layouts.', 'service-booking' ); ?></p>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" style="width: 40px;">#</th>
				<th scope="col"><?php esc_html_e( 'Template Name', 'service-booking' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Description', 'service-booking' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'service-booking' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>1</td>
				<td><strong><?php esc_html_e( 'Booking Ticket PDF', 'service-booking' ); ?></strong></td>
				<td><?php esc_html_e( 'Default booking ticket template used for order confirmations.', 'service-booking' ); ?></td>
				<td><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <?php esc_html_e( 'Active', 'service-booking' ); ?></td>
			</tr>
			<tr>
				<td>2</td>
				<td><strong><?php esc_html_e( 'Voucher PDF', 'service-booking' ); ?></strong></td>
				<td><?php esc_html_e( 'Default voucher template used for gift vouchers.', 'service-booking' ); ?></td>
				<td><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <?php esc_html_e( 'Active', 'service-booking' ); ?></td>
			</tr>
			<tr>
				<td>3</td>
				<td><strong><?php esc_html_e( 'Customer Details PDF', 'service-booking' ); ?></strong></td>
				<td><?php esc_html_e( 'Default customer details template for customer information export.', 'service-booking' ); ?></td>
				<td><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <?php esc_html_e( 'Active', 'service-booking' ); ?></td>
			</tr>
		</tbody>
	</table>

	<div class="sg-pro-upsell-notice" style="margin-top: 20px; padding: 15px; background: #fff8e1; border-left: 4px solid #ffb300; border-radius: 3px;">
		<p>
			<span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
			<strong><?php esc_html_e( 'Want to customize your PDF templates?', 'service-booking' ); ?></strong>
			<?php esc_html_e( 'Upgrade to Pro for the full PDF builder with custom fonts, colors, logos, and layouts.', 'service-booking' ); ?>
			<a href="<?php echo esc_url( $pro_url ); ?>" class="button button-primary" style="margin-left: 10px;" target="_blank">
				<?php esc_html_e( 'Upgrade to Pro', 'service-booking' ); ?>
			</a>
		</p>
	</div>
</div>
