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
	 * Maximum number of active mail templates in the free version.
	 */
	const FREE_MAIL_TEMPLATE_LIMIT = 2;

	/**
	 * Allowed mail template types in the free version.
	 */
	const FREE_MAIL_TEMPLATE_TYPES = array( 'booking_confirmed', 'booking_cancelled' );

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
	 * Free version: limited to FREE_MAIL_TEMPLATE_LIMIT templates
	 * and only basic default types.
	 * Pro version: unlimited templates of all types.
	 *
	 * @param string $template_type Optional. The type of template being created.
	 * @return bool
	 */
	public static function can_create_mail_template( $template_type = '' ) {
		if ( self::is_pro_active() ) {
			return true;
		}

		// Check template type restriction.
		if ( ! empty( $template_type ) && ! in_array( $template_type, self::FREE_MAIL_TEMPLATE_TYPES, true ) ) {
			return false;
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
	 * Free version: standard WooCommerce coupons only.
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
			'coupons'                => __( 'The Booking Coupons system is a Pro feature. Standard WooCommerce coupons are available.', 'service-booking' ),
			'analytics'              => __( 'The full Analytics page is a Pro feature. A basic dashboard overview is available in the free version.', 'service-booking' ),
			'manage_columns'         => __( 'Column visibility management is a Pro feature.', 'service-booking' ),
		);

		return isset( $messages[ $feature ] ) ? $messages[ $feature ] : __( 'This feature requires SG Flexi Booking Pro.', 'service-booking' );
	}
}
