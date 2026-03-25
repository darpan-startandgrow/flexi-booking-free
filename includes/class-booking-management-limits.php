<?php
/**
 * The Core Gatekeeper & Limitation Engine for SG Flexi Booking Lite.
 *
 * This class provides centralized helper methods that check limits
 * based on the freemium rules. All checks rely on the central filter:
 *   $is_pro_active = apply_filters( 'sg_booking_is_pro_active', false );
 *
 * If Pro is active (filter returns true), all limits are bypassed.
 * If Pro is NOT active, free-tier limits are enforced.
 *
 * @since      1.1.0
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Booking_Management_Limits {

	/**
	 * Maximum number of vouchers allowed in the free version.
	 */
	const FREE_VOUCHER_LIMIT = 20;

	/**
	 * Maximum number of active notification processes in the free version.
	 */
	const FREE_NOTIFICATION_PROCESS_LIMIT = 2;

	/**
	 * Maximum number of mail templates in the free version.
	 *
	 * Default templates: New Order (admin + customer), Failed Order (admin + customer),
	 * Cancelled Order (admin + customer), and Voucher Redemption.
	 */
	const FREE_MAIL_TEMPLATE_LIMIT = 7;

	/**
	 * Maximum number of gift orders in the free version.
	 */
	const FREE_GIFT_ORDER_LIMIT = 20;

	/**
	 * Default billing form fields for the free version.
	 */
	const FREE_DEFAULT_FIELD_NAMES = array(
		'billing_first_name',
		'billing_last_name',
		'billing_email',
		'billing_contact',
		'billing_address',
		'billing_state',
		'billing_country',
		'customer_order_note',
	);

	/**
	 * Hardcoded order listing columns for the free version.
	 */
	const FREE_ORDER_COLUMNS = array(
		'order_id',
		'service_name',
		'booking_created_at',
		'booking_date',
		'first_name',
		'email',
		'service_cost',
		'extra_svc_cost',
		'disount_amount',
		'total_cost',
		'order_status',
		'payment_status',
		'actions',
	);

	/**
	 * Check if the Pro add-on is currently active.
	 *
	 * The Pro plugin hooks into this filter and returns true.
	 * Without Pro, this always returns false.
	 *
	 * @return bool
	 */
	public static function is_pro_active() {
		return (bool) apply_filters( 'sg_booking_is_pro_active', false );
	}

	/**
	 * Check if a new voucher can be created.
	 *
	 * Free version: limited to FREE_VOUCHER_LIMIT total vouchers.
	 * Pro version: unlimited.
	 *
	 * @return bool
	 */
	public static function can_create_voucher() {
		if ( self::is_pro_active() ) {
			return true;
		}

		$dbhandler = new BM_DBhandler();
		$count     = $dbhandler->bm_count( 'VOUCHERS' );

		return ( (int) $count < self::FREE_VOUCHER_LIMIT );
	}

	/**
	 * Get the remaining voucher count for the free version.
	 *
	 * @return int Remaining vouchers allowed (-1 if unlimited/Pro).
	 */
	public static function get_remaining_vouchers() {
		if ( self::is_pro_active() ) {
			return -1;
		}

		$dbhandler = new BM_DBhandler();
		$count     = (int) $dbhandler->bm_count( 'VOUCHERS' );
		$remaining = self::FREE_VOUCHER_LIMIT - $count;

		return max( 0, $remaining );
	}

	/**
	 * Check if a new custom field can be added.
	 *
	 * Free version: cannot add new custom fields (only edit defaults).
	 * Pro version: can create new fields.
	 *
	 * @return bool
	 */
	public static function can_add_custom_field() {
		return self::is_pro_active();
	}

	/**
	 * Check if a default field can be deleted.
	 *
	 * Free version: default fields cannot be deleted.
	 * Pro version: all fields can be deleted.
	 *
	 * @param string $field_name The field name to check.
	 * @return bool
	 */
	public static function can_delete_field( $field_name = '' ) {
		if ( self::is_pro_active() ) {
			return true;
		}
		return ! in_array( $field_name, self::FREE_DEFAULT_FIELD_NAMES, true );
	}

	/**
	 * Check if new forms can be created.
	 *
	 * Free version: only the default billing form is available.
	 * Pro version: unlimited forms.
	 *
	 * @return bool
	 */
	public static function can_create_form() {
		return self::is_pro_active();
	}

	/**
	 * Check if a new notification process can be created.
	 *
	 * Free version: limited to FREE_NOTIFICATION_PROCESS_LIMIT active processes.
	 * Pro version: unlimited.
	 *
	 * @return bool
	 */
	public static function can_create_notification_process() {
		if ( self::is_pro_active() ) {
			return true;
		}

		$dbhandler = new BM_DBhandler();
		$count     = $dbhandler->bm_count( 'EVENTNOTIFICATION' );

		return ( (int) $count < self::FREE_NOTIFICATION_PROCESS_LIMIT );
	}

	/**
	 * Get the remaining notification process count for the free version.
	 *
	 * @return int Remaining processes allowed (-1 if unlimited/Pro).
	 */
	public static function get_remaining_notification_processes() {
		if ( self::is_pro_active() ) {
			return -1;
		}

		$dbhandler = new BM_DBhandler();
		$count     = (int) $dbhandler->bm_count( 'EVENTNOTIFICATION' );
		$remaining = self::FREE_NOTIFICATION_PROCESS_LIMIT - $count;

		return max( 0, $remaining );
	}

	/**
	 * Check if a new mail template can be created.
	 *
	 * Free version: limited to FREE_MAIL_TEMPLATE_LIMIT templates total.
	 * Pro version: unlimited templates of all types.
	 *
	 * @param string $template_type Optional. Unused — kept for backward compatibility.
	 * @return bool
	 */
	public static function can_create_mail_template( $template_type = '' ) {
		if ( self::is_pro_active() ) {
			return true;
		}

		$dbhandler = new BM_DBhandler();
		$count     = $dbhandler->bm_count( 'EMAIL_TMPL' );

		return ( (int) $count < self::FREE_MAIL_TEMPLATE_LIMIT );
	}

	/**
	 * Get the remaining mail template count for the free version.
	 *
	 * @return int Remaining templates allowed (-1 if unlimited/Pro).
	 */
	public static function get_remaining_mail_templates() {
		if ( self::is_pro_active() ) {
			return -1;
		}

		$dbhandler = new BM_DBhandler();
		$count     = (int) $dbhandler->bm_count( 'EMAIL_TMPL' );
		$remaining = self::FREE_MAIL_TEMPLATE_LIMIT - $count;

		return max( 0, $remaining );
	}

	/**
	 * Check if QR code scanning is available.
	 *
	 * Free version: only manual check-ins (button click).
	 * Pro version: QR code scanning and automated processing.
	 *
	 * @return bool
	 */
	public static function can_use_qr_scanning() {
		return self::is_pro_active();
	}

	/**
	 * Check if the PDF customization builder is available.
	 *
	 * Free version: standard default PDF templates only.
	 * Pro version: drag-and-drop PDF builder.
	 *
	 * @return bool
	 */
	public static function can_use_pdf_customizer() {
		return self::is_pro_active();
	}

	/**
	 * Check if Price Modules (Dynamic/Conditional Pricing) are available.
	 *
	 * Free version: standard WooCommerce pricing only.
	 * Pro version: full Price Modules access.
	 *
	 * @return bool
	 */
	public static function can_use_price_modules() {
		return self::is_pro_active();
	}

	/**
	 * Check if the custom Booking Coupons system is available.
	 *
	 * Free version: coupons are completely removed.
	 * Pro version: custom Booking Coupons system.
	 *
	 * @return bool
	 */
	public static function can_use_coupons() {
		return self::is_pro_active();
	}

	/**
	 * Check if the full Analytics page is available.
	 *
	 * Free version: basic limited dashboard analytics only.
	 * Pro version: dedicated full Analytics page.
	 *
	 * @return bool
	 */
	public static function can_use_full_analytics() {
		return self::is_pro_active();
	}

	/**
	 * Check if Manage Columns (column visibility toggles) is available.
	 *
	 * Free version: hidden on all admin DataTables.
	 * Pro version: fully available.
	 *
	 * @return bool
	 */
	public static function can_manage_columns() {
		return self::is_pro_active();
	}

	/**
	 * Check if advanced time slot calendar configurations are available.
	 *
	 * Free version: basic service creation and standard settings.
	 * Pro version: advanced time slot calendar inside service settings.
	 *
	 * @return bool
	 */
	public static function can_use_advanced_time_slots() {
		return self::is_pro_active();
	}

	/**
	 * Check if advanced global settings tabs are available.
	 *
	 * Free version: basic global settings tabs only.
	 * Pro version: all advanced global settings tabs.
	 *
	 * @return bool
	 */
	public static function can_use_advanced_global_settings() {
		return self::is_pro_active();
	}

	/**
	 * Check if Stop-Sales feature is available.
	 *
	 * Free version: not available.
	 * Pro version: full stop-sales functionality.
	 *
	 * @return bool
	 */
	public static function can_use_stop_sales() {
		return self::is_pro_active();
	}

	/**
	 * Check if Saleswitch feature is available.
	 *
	 * Free version: not available.
	 * Pro version: full saleswitch functionality.
	 *
	 * @return bool
	 */
	public static function can_use_saleswitch() {
		return self::is_pro_active();
	}

	/**
	 * Check if Max Capacity management is available.
	 *
	 * Free version: not available.
	 * Pro version: configurable max capacity per service.
	 *
	 * @return bool
	 */
	public static function can_edit_max_capacity() {
		return self::is_pro_active();
	}

	/**
	 * Check if Age Settings (age-based pricing) is available.
	 *
	 * Free version: not available.
	 * Pro version: age-based pricing rules.
	 *
	 * @return bool
	 */
	public static function can_use_age_settings() {
		return self::is_pro_active();
	}

	/**
	 * Check if new customers can be created from admin.
	 *
	 * Free version: customer creation is blocked, only email listing.
	 * Pro version: full customer management.
	 *
	 * @return bool
	 */
	public static function can_create_customer() {
		return self::is_pro_active();
	}

	/**
	 * Check if emails can be resent.
	 *
	 * Free version: no resend button.
	 * Pro version: full email resend capability.
	 *
	 * @return bool
	 */
	public static function can_resend_email() {
		return self::is_pro_active();
	}

	/**
	 * Check if ticket scanning is available.
	 *
	 * Free version: manual check-in only.
	 * Pro version: ticket scanner + resend ticket.
	 *
	 * @return bool
	 */
	public static function can_use_ticket_scanner() {
		return self::is_pro_active();
	}

	/**
	 * Check if tickets can be resent.
	 *
	 * Free version: no resend ticket button.
	 * Pro version: resend ticket capability.
	 *
	 * @return bool
	 */
	public static function can_resend_ticket() {
		return self::is_pro_active();
	}

	/**
	 * Check if payment logs are available.
	 *
	 * Free version: payment logs not available.
	 * Pro version: full payment logging.
	 *
	 * @return bool
	 */
	public static function can_use_payment_logs() {
		return self::is_pro_active();
	}

	/**
	 * Check if SMTP settings are available.
	 *
	 * Free version: basic mail settings only (no SMTP).
	 * Pro version: full SMTP configuration.
	 *
	 * @return bool
	 */
	public static function can_use_smtp() {
		return self::is_pro_active();
	}

	/**
	 * Check if the advanced dashboard is available.
	 *
	 * Free version: simple dashboard with basic metrics.
	 * Pro version: full analytics dashboard.
	 *
	 * @return bool
	 */
	public static function can_use_advanced_dashboard() {
		return self::is_pro_active();
	}

	/**
	 * Check if voucher redemption is available.
	 *
	 * Free version: voucher listing only.
	 * Pro version: full voucher redemption.
	 *
	 * @return bool
	 */
	public static function can_redeem_voucher() {
		return self::is_pro_active();
	}

	/**
	 * Get the upsell URL for the Pro version.
	 *
	 * @return string
	 */
	public static function get_pro_upsell_url() {
		return apply_filters( 'sg_booking_pro_upsell_url', 'https://developer.startandgrow.in/sg-flexi-booking-pro/' );
	}

	/**
	 * Get a human-readable label for a feature limit.
	 *
	 * @param string $feature Feature key identifier.
	 * @return string
	 */
	public static function get_limit_message( $feature ) {
		$messages = array(
			'vouchers'               => sprintf(
				/* translators: %d: Maximum number of vouchers allowed */
				__( 'You have reached the free limit of %d vouchers. Upgrade to Pro for unlimited voucher generation.', 'service-booking' ),
				self::FREE_VOUCHER_LIMIT
			),
			'custom_fields'          => __( 'Adding new custom fields is a Pro feature. You can edit the default fields in the free version.', 'service-booking' ),
			'notification_processes' => sprintf(
				/* translators: %d: Maximum number of notification processes allowed */
				__( 'You have reached the free limit of %d notification processes. Upgrade to Pro for unlimited processes.', 'service-booking' ),
				self::FREE_NOTIFICATION_PROCESS_LIMIT
			),
			'mail_templates'         => sprintf(
				/* translators: %d: Maximum number of mail templates allowed */
				__( 'You have reached the free limit of %d mail templates. Upgrade to Pro for unlimited templates and all template types.', 'service-booking' ),
				self::FREE_MAIL_TEMPLATE_LIMIT
			),
			'qr_scanning'            => __( 'QR code scanning is a Pro feature. Manual check-ins are available in the free version.', 'service-booking' ),
			'pdf_customizer'         => __( 'The PDF customization builder is a Pro feature. Standard PDF templates are available in the free version.', 'service-booking' ),
			'price_modules'          => __( 'Price Modules (Dynamic/Conditional Pricing) are a Pro feature. Standard WooCommerce pricing is available.', 'service-booking' ),
			'coupons'                => __( 'The Booking Coupons system is a Pro feature. Upgrade to Pro for coupon support.', 'service-booking' ),
			'analytics'              => __( 'The full Analytics page is a Pro feature. A basic dashboard overview is available in the free version.', 'service-booking' ),
			'manage_columns'         => __( 'Column visibility management is a Pro feature.', 'service-booking' ),
			'stop_sales'             => __( 'Stop-Sales is a Pro feature. Upgrade to Pro to stop sales for specific services.', 'service-booking' ),
			'saleswitch'             => __( 'Saleswitch is a Pro feature. Upgrade to Pro to switch between sales modes.', 'service-booking' ),
			'max_capacity'           => __( 'Max Capacity management is a Pro feature. Upgrade to Pro for capacity control.', 'service-booking' ),
			'age_settings'           => __( 'Age-based pricing is a Pro feature. Upgrade to Pro for age settings.', 'service-booking' ),
			'customer_creation'      => __( 'Customer creation is a Pro feature. The free version shows customer email listings only.', 'service-booking' ),
			'email_resend'           => __( 'Email resend is a Pro feature. Upgrade to Pro for email resend capability.', 'service-booking' ),
			'ticket_scanner'         => __( 'Ticket scanning is a Pro feature. Manual check-ins are available in the free version.', 'service-booking' ),
			'payment_logs'           => __( 'Payment logs are a Pro feature. Upgrade to Pro for payment logging.', 'service-booking' ),
			'smtp'                   => __( 'SMTP configuration is a Pro feature. Basic mail settings are available in the free version.', 'service-booking' ),
			'voucher_redemption'     => __( 'Voucher redemption is a Pro feature. The free version shows voucher listings only.', 'service-booking' ),
		);

		return isset( $messages[ $feature ] ) ? $messages[ $feature ] : __( 'This feature requires SG Flexi Booking Pro.', 'service-booking' );
	}
}
