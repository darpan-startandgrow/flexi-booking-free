<?php
/**
 * Universal Pro upsell page template.
 *
 * This page is shown when a free user tries to access a Pro-only feature.
 * It provides a beautifully styled upsell notice with feature highlights.
 *
 * @since      1.1.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/partials/upsells
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$upsell_url = Booking_Management_Limits::get_pro_upsell_url();

// Determine which feature the user was trying to access.
$raw_page_slug = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

$feature_map = array(
	'bm_home'                       => array(
		'title'       => __( 'Booking Dashboard', 'service-booking' ),
		'description' => __( 'Unlock the full interactive dashboard with booking statistics, revenue overview, and real-time activity feed.', 'service-booking' ),
		'icon'        => 'dashicons-dashboard',
	),
	'bm_booking_analytics'          => array(
		'title'       => __( 'Advanced Analytics', 'service-booking' ),
		'description' => __( 'Unlock the full dedicated Analytics page with detailed booking insights, revenue breakdowns, service performance metrics, and exportable reports.', 'service-booking' ),
		'icon'        => 'dashicons-chart-area',
	),
	'bm_all_coupons'                => array(
		'title'       => __( 'Booking Coupons', 'service-booking' ),
		'description' => __( 'Create and manage custom booking coupons with advanced rules, usage limits, and targeted promotions to boost your bookings.', 'service-booking' ),
		'icon'        => 'dashicons-tickets-alt',
	),
	'bm_add_coupon'                 => array(
		'title'       => __( 'Booking Coupons', 'service-booking' ),
		'description' => __( 'Create and manage custom booking coupons with advanced rules, usage limits, and targeted promotions to boost your bookings.', 'service-booking' ),
		'icon'        => 'dashicons-tickets-alt',
	),
	'bm_all_external_service_prices' => array(
		'title'       => __( 'Price Modules', 'service-booking' ),
		'description' => __( 'Unlock dynamic and conditional pricing with Price Modules. Set age-based pricing, group discounts, seasonal rates, and more.', 'service-booking' ),
		'icon'        => 'dashicons-money-alt',
	),
	'bm_add_external_service_price' => array(
		'title'       => __( 'Price Modules', 'service-booking' ),
		'description' => __( 'Unlock dynamic and conditional pricing with Price Modules. Set age-based pricing, group discounts, seasonal rates, and more.', 'service-booking' ),
		'icon'        => 'dashicons-money-alt',
	),
	'bm_pdf_customization'          => array(
		'title'       => __( 'PDF Customization Builder', 'service-booking' ),
		'description' => __( 'Design stunning booking tickets and vouchers with the drag-and-drop PDF builder. Customize layouts, add your branding, and create professional documents.', 'service-booking' ),
		'icon'        => 'dashicons-media-document',
	),
	'bm_global_coupon_settings'     => array(
		'title'       => __( 'Coupon Settings', 'service-booking' ),
		'description' => __( 'Configure advanced coupon settings and defaults for your booking coupon system.', 'service-booking' ),
		'icon'        => 'dashicons-admin-settings',
	),
	'bm_email_templates'            => array(
		'title'       => __( 'Mail Templates', 'service-booking' ),
		'description' => __( 'Design custom mail templates for booking confirmations, cancellations, reminders, and more.', 'service-booking' ),
		'icon'        => 'dashicons-email',
	),
	'sg-booking-forms'              => array(
		'title'       => __( 'Custom Fields', 'service-booking' ),
		'description' => __( 'Create entirely new custom booking fields to collect additional information from your customers.', 'service-booking' ),
		'icon'        => 'dashicons-forms',
	),
	'bm_all_notification_processes' => array(
		'title'       => __( 'Notification Processes', 'service-booking' ),
		'description' => __( 'Set up automated notification workflows with unlimited processes and templates.', 'service-booking' ),
		'icon'        => 'dashicons-bell',
	),
	'bm_email_records'              => array(
		'title'       => __( 'Email Records', 'service-booking' ),
		'description' => __( 'View and manage all email communication records with detailed logs and resend capabilities.', 'service-booking' ),
		'icon'        => 'dashicons-email-alt2',
	),
	'bm_voucher_records'            => array(
		'title'       => __( 'Vouchers', 'service-booking' ),
		'description' => __( 'Create, manage, and track booking vouchers with advanced redemption rules and PDF generation.', 'service-booking' ),
		'icon'        => 'dashicons-tag',
	),
	'bm_check_ins'                  => array(
		'title'       => __( 'Check-ins & QR Scanning', 'service-booking' ),
		'description' => __( 'Manage customer check-ins with QR code scanning, manual check-in processing, and real-time attendance tracking.', 'service-booking' ),
		'icon'        => 'dashicons-camera',
	),
	'bm_customer_profile'           => array(
		'title'       => __( 'Customer Profiles', 'service-booking' ),
		'description' => __( 'View detailed customer profiles with booking history, payment records, and customer analytics.', 'service-booking' ),
		'icon'        => 'dashicons-admin-users',
	),
	'bm_global_email_settings'      => array(
		'title'       => __( 'Email Settings', 'service-booking' ),
		'description' => __( 'Configure custom SMTP, email sender details, and advanced email delivery settings.', 'service-booking' ),
		'icon'        => 'dashicons-admin-settings',
	),
	'bm_global_payment_settings'    => array(
		'title'       => __( 'Payment Settings', 'service-booking' ),
		'description' => __( 'Configure Stripe, payment gateways, and advanced payment processing options.', 'service-booking' ),
		'icon'        => 'dashicons-admin-settings',
	),
	'bm_upload_settings'            => array(
		'title'       => __( 'Upload Settings', 'service-booking' ),
		'description' => __( 'Configure file upload settings for service images and booking attachments.', 'service-booking' ),
		'icon'        => 'dashicons-admin-settings',
	),
	'bm_global_integration_settings' => array(
		'title'       => __( 'Integration Settings', 'service-booking' ),
		'description' => __( 'Connect with third-party services and configure advanced integrations.', 'service-booking' ),
		'icon'        => 'dashicons-admin-settings',
	),
);

// Validate page slug against the allowlist of known feature pages.
$page_slug = array_key_exists( $raw_page_slug, $feature_map ) ? $raw_page_slug : '';

$feature = isset( $feature_map[ $page_slug ] ) ? $feature_map[ $page_slug ] : array(
	'title'       => __( 'Pro Feature', 'service-booking' ),
	'description' => __( 'This feature is available exclusively in SG Flexi Booking Pro. Upgrade to unlock the full power of FlexiBooking.', 'service-booking' ),
	'icon'        => 'dashicons-lock',
);

$pro_features = array(
	array(
		'icon'  => 'dashicons-chart-area',
		'title' => __( 'Advanced Analytics', 'service-booking' ),
		'desc'  => __( 'Full analytics dashboard with detailed reports', 'service-booking' ),
	),
	array(
		'icon'  => 'dashicons-money-alt',
		'title' => __( 'Price Modules', 'service-booking' ),
		'desc'  => __( 'Dynamic and conditional pricing rules', 'service-booking' ),
	),
	array(
		'icon'  => 'dashicons-tickets-alt',
		'title' => __( 'Booking Coupons', 'service-booking' ),
		'desc'  => __( 'Custom coupon system beyond WooCommerce', 'service-booking' ),
	),
	array(
		'icon'  => 'dashicons-media-document',
		'title' => __( 'PDF Builder', 'service-booking' ),
		'desc'  => __( 'Drag-and-drop PDF ticket customization', 'service-booking' ),
	),
	array(
		'icon'  => 'dashicons-camera',
		'title' => __( 'QR Code Scanning', 'service-booking' ),
		'desc'  => __( 'Automated QR code check-in processing', 'service-booking' ),
	),
	array(
		'icon'  => 'dashicons-email-alt',
		'title' => __( 'Unlimited Notifications', 'service-booking' ),
		'desc'  => __( 'Unlimited notification processes and templates', 'service-booking' ),
	),
	array(
		'icon'  => 'dashicons-admin-generic',
		'title' => __( 'Manage Columns', 'service-booking' ),
		'desc'  => __( 'Column visibility toggles on all tables', 'service-booking' ),
	),
	array(
		'icon'  => 'dashicons-forms',
		'title' => __( 'Custom Fields', 'service-booking' ),
		'desc'  => __( 'Create entirely new custom booking fields', 'service-booking' ),
	),
);
?>

<div class="wrap sg-upsell-wrap">
	<div class="sg-upsell-container">
		<!-- Hero Section -->
		<div class="sg-upsell-hero">
			<div class="sg-upsell-hero-icon">
				<span class="dashicons <?php echo esc_attr( $feature['icon'] ); ?>"></span>
			</div>
			<h1 class="sg-upsell-title">
				<?php echo esc_html( $feature['title'] ); ?>
			</h1>
			<p class="sg-upsell-description">
				<?php echo esc_html( $feature['description'] ); ?>
			</p>
			<div class="sg-upsell-badge">
				<span class="dashicons dashicons-star-filled"></span>
				<?php esc_html_e( 'PRO FEATURE', 'service-booking' ); ?>
			</div>
		</div>

		<!-- CTA Section -->
		<div class="sg-upsell-cta">
			<a href="<?php echo esc_url( $upsell_url ); ?>" class="sg-upsell-button sg-upsell-button-primary" target="_blank" rel="noopener noreferrer">
				<span class="dashicons dashicons-unlock"></span>
				<?php esc_html_e( 'Upgrade to Pro', 'service-booking' ); ?>
			</a>
			<p class="sg-upsell-cta-note">
				<?php esc_html_e( 'Instant activation. Works alongside your existing free version.', 'service-booking' ); ?>
			</p>
		</div>

		<!-- Features Grid -->
		<div class="sg-upsell-features-section">
			<h2 class="sg-upsell-features-heading">
				<?php esc_html_e( 'Everything included in Pro', 'service-booking' ); ?>
			</h2>
			<div class="sg-upsell-features-grid">
				<?php foreach ( $pro_features as $pro_feature ) : ?>
					<div class="sg-upsell-feature-card">
						<div class="sg-upsell-feature-icon">
							<span class="dashicons <?php echo esc_attr( $pro_feature['icon'] ); ?>"></span>
						</div>
						<h3><?php echo esc_html( $pro_feature['title'] ); ?></h3>
						<p><?php echo esc_html( $pro_feature['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
