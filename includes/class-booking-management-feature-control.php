<?php
/**
 * Feature control and free-tier restriction engine.
 *
 * This class uses the central gatekeeper filter `sg_booking_is_pro_active`
 * to determine whether the Pro add-on is active and applies free-tier
 * restrictions accordingly.
 *
 * @since      1.0.0
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Booking_Management_Feature_Control {

	/**
	 * Check if the Pro add-on is active.
	 *
	 * Uses the central gatekeeper filter. The Pro plugin hooks into this
	 * filter and returns true. Without Pro, this always returns false.
	 *
	 * @return bool
	 */
	public static function is_pro() {
		return (bool) apply_filters( 'sg_booking_is_pro_active', false );
	}

	/**
	 * Initialize the feature control.
	 *
	 * Applies free restrictions if Pro is not active.
	 */
	public function init() {
		if ( ! self::is_pro() ) {
			$this->apply_free_restrictions();
		}
	}

	/**
	 * Apply free-tier restrictions via WordPress filters.
	 */
	private function apply_free_restrictions() {
		// 1. Limit Services to 20.
		add_filter( 'bm_can_add_service', array( $this, 'bm_check_service_limit' ) );

		// 2. Restrict to WooCommerce Payments Only.
		add_filter( 'bm_allow_custom_gateway', '__return_false' );

		// 3. Disable Bulk Updates.
		add_filter( 'bm_allow_bulk_calendar_update', '__return_false' );

		// 4. Disable "Book on Request".
		add_filter( 'bm_allow_book_on_request', '__return_false' );

		// 5. Disable Email Automation.
		add_filter( 'bm_allow_email_automation', '__return_false' );

		// 6. Restrict Order Editing.
		add_filter( 'bm_allow_backend_order_edit', '__return_false' );

		// 7. Limit Categories (Single vs Multiple).
		add_filter( 'bm_allow_multiple_categories', '__return_false' );

		// 8. Filter Settings Tabs.
		add_filter( 'bm_settings_tabs', array( $this, 'bm_filter_free_tabs' ) );

		// 9. Add body class for free version CSS hooks.
		add_filter( 'admin_body_class', array( $this, 'bm_add_free_body_class' ) );

		// 10. Block stop-sales feature.
		add_filter( 'bm_allow_stop_sales', '__return_false' );

		// 11. Block saleswitch feature.
		add_filter( 'bm_allow_saleswitch', '__return_false' );

		// 12. Block max capacity editing.
		add_filter( 'bm_allow_max_capacity', '__return_false' );

		// 13. Block timeslot calendar.
		add_filter( 'bm_allow_timeslot_calendar', '__return_false' );

		// 14. Block age settings.
		add_filter( 'bm_allow_age_settings', '__return_false' );

		// 15. Block customer creation.
		add_filter( 'bm_allow_customer_creation', '__return_false' );

		// 16. Block email resend.
		add_filter( 'bm_allow_email_resend', '__return_false' );

		// 17. Block ticket scanner.
		add_filter( 'bm_allow_ticket_scanner', '__return_false' );

		// 18. Block ticket resend.
		add_filter( 'bm_allow_ticket_resend', '__return_false' );

		// 19. Block payment logs.
		add_filter( 'bm_allow_payment_logs', '__return_false' );

		// 20. Block SMTP.
		add_filter( 'bm_allow_smtp', '__return_false' );

		// 21. Block coupon feature.
		add_filter( 'bm_allow_coupons', '__return_false' );

		// 22. Block voucher redemption.
		add_filter( 'bm_allow_voucher_redemption', '__return_false' );

		// 23. Block manage columns.
		add_filter( 'bm_allow_manage_columns', '__return_false' );

		// 24. Block PDF customization.
		add_filter( 'bm_allow_pdf_customization', '__return_false' );

		// 25. Block price modules.
		add_filter( 'bm_allow_price_modules', '__return_false' );

		// 26. Block advanced dashboard.
		add_filter( 'bm_allow_advanced_dashboard', '__return_false' );

		// 27. Block custom field addition.
		add_filter( 'bm_allow_custom_fields', '__return_false' );

		// 28. Block field deletion for default fields.
		add_filter( 'bm_allow_field_deletion', '__return_false' );

		// 29. Block form creation.
		add_filter( 'bm_allow_form_creation', '__return_false' );
	}

	/**
	 * Check service limit for the free tier.
	 *
	 * @param bool $can_add Whether a new service can be added.
	 * @return bool
	 */
	public function bm_check_service_limit( $can_add ) {
		$count = ( new BM_DBhandler() )->bm_count( 'SERVICE' );
		return ( $count < 20 );
	}

	/**
	 * Filter settings tabs for the free tier.
	 *
	 * Only allow: general, format, timezone, language, upload, fields, mail (basic).
	 *
	 * @param array $tabs Available settings tabs.
	 * @return array Filtered tabs.
	 */
	public function bm_filter_free_tabs( $tabs ) {
		$allowed = array( 'general', 'format', 'timezone', 'language', 'upload', 'fields', 'mail' );
		return array_intersect_key( $tabs, array_flip( $allowed ) );
	}

	/**
	 * Add a CSS body class for the free version.
	 *
	 * This class is used by CSS to hide Pro-only UI elements like
	 * the "Manage Columns" button, "Add New Field" button, stop-sales,
	 * saleswitch, max capacity, coupon, and other Pro features.
	 *
	 * @param string $classes Existing body classes.
	 * @return string Modified body classes.
	 */
	public function bm_add_free_body_class( $classes ) {
		$classes .= ' sg-free-version';
		return $classes;
	}
}

// Initialize the controller.
$bm_feature_control = new Booking_Management_Feature_Control();
$bm_feature_control->init();
