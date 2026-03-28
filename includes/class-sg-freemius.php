<?php
/**
 * Freemius SDK integration for SG Flexi Booking.
 *
 * Initialises the Freemius SDK when the license provider is set to
 * 'freemius'. The SDK itself is expected to be present in the
 * `freemius/` directory (bundled separately). All calls are guarded
 * so the plugin degrades gracefully when the SDK is absent.
 *
 * Configuration constants (define in wp-config.php or a mu-plugin):
 *   SG_BOOKING_FS_ID         – Freemius product ID   (default 0).
 *   SG_BOOKING_FS_PUBLIC_KEY – Freemius public key    (default '').
 *   SG_BOOKING_FS_SLUG       – Freemius product slug  (default 'sg-flexi-booking').
 *
 * @since      1.1.0
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create a helper function for easy Freemius SDK access.
 *
 * The function is only defined when the license provider is 'freemius'
 * AND the Freemius SDK is actually present.
 */
if ( ! function_exists( 'sg_booking_fs' ) ) {

	/**
	 * Determine whether Freemius integration should be active.
	 *
	 * Returns true only when the configured license provider is 'freemius'.
	 *
	 * @since  1.1.0
	 * @return bool
	 */
	function sg_booking_fs_should_activate() {
		$provider = defined( 'SG_BOOKING_LICENSE_PROVIDER' )
			? SG_BOOKING_LICENSE_PROVIDER
			: 'edd';

		/** This filter is documented in class-sg-license-manager.php */
		$provider = apply_filters( 'sg_license_validation_method', $provider );

		return ( 'freemius' === $provider );
	}

	/**
	 * Get the Freemius SDK instance for SG Flexi Booking.
	 *
	 * Initialises the SDK on first call and returns the cached instance
	 * on subsequent calls. Returns null when the SDK is not available or
	 * the provider is not 'freemius'.
	 *
	 * @since  1.1.0
	 * @return \Freemius|null
	 */
	function sg_booking_fs() {
		global $sg_booking_fs;

		if ( isset( $sg_booking_fs ) ) {
			return $sg_booking_fs;
		}

		// Only initialise when the provider is set to 'freemius'.
		if ( ! sg_booking_fs_should_activate() ) {
			return null;
		}

		// The Freemius SDK must be loaded before we can initialise.
		if ( ! function_exists( 'fs_dynamic_init' ) ) {
			// Try to load the SDK from the bundled path.
			$sdk_path = dirname( __DIR__ ) . '/freemius/start.php';
			if ( file_exists( $sdk_path ) ) {
				require_once $sdk_path;
			} else {
				return null;
			}
		}

		// Double-check after the include.
		if ( ! function_exists( 'fs_dynamic_init' ) ) {
			return null;
		}

		$fs_id         = defined( 'SG_BOOKING_FS_ID' ) ? (int) SG_BOOKING_FS_ID : 0;
		$fs_public_key = defined( 'SG_BOOKING_FS_PUBLIC_KEY' ) ? SG_BOOKING_FS_PUBLIC_KEY : '';
		$fs_slug       = defined( 'SG_BOOKING_FS_SLUG' ) ? SG_BOOKING_FS_SLUG : 'sg-flexi-booking';

		// Bail out when placeholder credentials are still in use.
		if ( 0 === $fs_id || '' === $fs_public_key ) {
			return null;
		}

		$sg_booking_fs = fs_dynamic_init( array(
			'id'                  => $fs_id,
			'slug'                => $fs_slug,
			'type'                => 'plugin',
			'public_key'          => $fs_public_key,
			'is_premium'          => false,
			'has_addons'          => false,
			'has_paid_plans'      => true,
			'menu'                => array(
				'slug'    => 'bm_home',
				'parent'  => array(
					'slug' => 'bm_home',
				),
				'support' => false,
			),
			'is_live'             => true,
		) );

		return $sg_booking_fs;
	}

	// --- Initialise Freemius early so it can hook into WordPress. ------
	$sg_fs_instance = sg_booking_fs();

	if ( is_object( $sg_fs_instance ) ) {
		/**
		 * Cleanup callback fired after the plugin is uninstalled via Freemius.
		 *
		 * @since 1.1.0
		 */
		function sg_booking_fs_uninstall_cleanup() {
			// Remove plugin-specific options.
			delete_option( 'sg_booking_license_key' );
			delete_option( 'sg_booking_license_status' );
			delete_option( 'sg_booking_license_expiry' );
			delete_transient( 'sg_booking_license_cache' );
		}

		$sg_fs_instance->add_action( 'after_uninstall', 'sg_booking_fs_uninstall_cleanup' );
	}
}
