<?php
/**
 * Modular License Management System for SG Flexi Booking Pro.
 *
 * Handles license validation, activation, deactivation, and caching.
 * Abstracted for EDD/Freemius integration and future custom license servers.
 *
 * The Lite plugin ships this class but **never** returns true from the
 * central gatekeeper filter `sg_booking_is_pro_active` (implemented in
 * class-booking-management-feature-control.php and
 * class-booking-management-limits.php, not here). The Pro add-on hooks
 * that filter to return true only when a valid license is detected.
 *
 * @since      1.1.0
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SG_License_Manager {

	/**
	 * WordPress option key for the stored license key.
	 */
	const OPTION_LICENSE_KEY = 'sg_booking_license_key';

	/**
	 * WordPress option key for the stored license status.
	 */
	const OPTION_LICENSE_STATUS = 'sg_booking_license_status';

	/**
	 * Transient key used to cache the license validation result.
	 */
	const TRANSIENT_KEY = 'sg_booking_license_cache';

	/**
	 * Cache duration in seconds (24 hours).
	 */
	const CACHE_DURATION = DAY_IN_SECONDS;

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — use get_instance().
	 */
	private function __construct() {
		// Register the admin settings page.
		add_action( 'admin_menu', array( $this, 'register_license_page' ), 99 );
		add_action( 'admin_init', array( $this, 'register_license_settings' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	// ------------------------------------------------------------------
	// License Validation
	// ------------------------------------------------------------------

	/**
	 * Validate a license key against the configured provider.
	 *
	 * Uses a filter `sg_license_validation_method` so the validation
	 * backend (EDD, Freemius, or custom) can be swapped without
	 * changing this class.
	 *
	 * Results are cached for CACHE_DURATION seconds.
	 *
	 * @param string $license_key The license key to validate.
	 * @param string $item_id     The EDD download / Freemius plan ID.
	 * @return bool True if valid, false otherwise.
	 */
	public function validate_license( $license_key, $item_id = '' ) {
		if ( empty( $license_key ) ) {
			return false;
		}

		// Check the cache first.
		$cached = get_transient( self::TRANSIENT_KEY );
		if ( false !== $cached ) {
			return (bool) $cached;
		}

		/**
		 * Filters the license validation result.
		 *
		 * Third-party providers (EDD, Freemius, custom server) hook here
		 * and return true/false based on their own validation logic.
		 *
		 * @since 1.1.0
		 *
		 * @param bool   $is_valid   Default false.
		 * @param string $license_key The license key.
		 * @param string $item_id     The product / plan ID.
		 */
		$is_valid = (bool) apply_filters( 'sg_license_validation_method', false, $license_key, $item_id );

		// Cache the result.
		set_transient( self::TRANSIENT_KEY, $is_valid ? 1 : 0, self::CACHE_DURATION );

		return $is_valid;
	}

	// ------------------------------------------------------------------
	// Activation / Deactivation
	// ------------------------------------------------------------------

	/**
	 * Activate a license key.
	 *
	 * Stores the key, validates it, and updates the status option.
	 *
	 * @param string $license_key The key to activate.
	 * @param string $item_id     The product / plan ID.
	 * @return bool True on success.
	 */
	public function activate_license( $license_key, $item_id = '' ) {
		$license_key = sanitize_text_field( $license_key );

		update_option( self::OPTION_LICENSE_KEY, $license_key );
		delete_transient( self::TRANSIENT_KEY );

		$valid = $this->validate_license( $license_key, $item_id );

		update_option( self::OPTION_LICENSE_STATUS, $valid ? 'active' : 'invalid' );

		return $valid;
	}

	/**
	 * Deactivate the current license.
	 *
	 * Clears the stored key, status, and cached validation.
	 *
	 * @return void
	 */
	public function deactivate_license() {
		delete_option( self::OPTION_LICENSE_KEY );
		update_option( self::OPTION_LICENSE_STATUS, 'inactive' );
		delete_transient( self::TRANSIENT_KEY );
	}

	// ------------------------------------------------------------------
	// Status helpers
	// ------------------------------------------------------------------

	/**
	 * Get the stored license key.
	 *
	 * @return string
	 */
	public function get_license_key() {
		return (string) get_option( self::OPTION_LICENSE_KEY, '' );
	}

	/**
	 * Get the current license status.
	 *
	 * @return string One of 'active', 'inactive', 'invalid', 'expired'.
	 */
	public function get_license_status() {
		return (string) get_option( self::OPTION_LICENSE_STATUS, 'inactive' );
	}

	/**
	 * Whether the license is currently active.
	 *
	 * @return bool
	 */
	public function is_license_active() {
		return 'active' === $this->get_license_status();
	}

	// ------------------------------------------------------------------
	// Admin UI
	// ------------------------------------------------------------------

	/**
	 * Register the license settings page as a hidden sub-page under
	 * the FlexiBooking admin menu.
	 */
	public function register_license_page() {
		add_submenu_page(
			'bm_home',
			__( 'License', 'service-booking' ),
			__( 'License', 'service-booking' ),
			'manage_options',
			'sg_booking_license',
			array( $this, 'render_license_page' )
		);
	}

	/**
	 * Register settings handled by the Settings API.
	 */
	public function register_license_settings() {
		register_setting( 'sg_booking_license_group', self::OPTION_LICENSE_KEY, array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		) );
	}

	/**
	 * Render the license settings page.
	 */
	public function render_license_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle activation / deactivation actions.
		if ( isset( $_POST['sg_license_action'] ) && check_admin_referer( 'sg_booking_license_nonce', 'sg_booking_license_nonce_field' ) ) {
			$action = sanitize_text_field( wp_unslash( $_POST['sg_license_action'] ) );

			if ( 'activate' === $action ) {
				$key = isset( $_POST[ self::OPTION_LICENSE_KEY ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::OPTION_LICENSE_KEY ] ) ) : '';
				$this->activate_license( $key );
			} elseif ( 'deactivate' === $action ) {
				$this->deactivate_license();
			}
		}

		$license_key    = $this->get_license_key();
		$license_status = $this->get_license_status();
		$status_label   = ucfirst( $license_status );
		$status_class   = 'active' === $license_status ? 'sg-license-active' : 'sg-license-inactive';

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'SG Flexi Booking — License', 'service-booking' ); ?></h1>

			<form method="post" action="">
				<?php wp_nonce_field( 'sg_booking_license_nonce', 'sg_booking_license_nonce_field' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( self::OPTION_LICENSE_KEY ); ?>">
								<?php esc_html_e( 'License Key', 'service-booking' ); ?>
							</label>
						</th>
						<td>
							<input type="password"
								id="<?php echo esc_attr( self::OPTION_LICENSE_KEY ); ?>"
								name="<?php echo esc_attr( self::OPTION_LICENSE_KEY ); ?>"
								class="regular-text"
								value="<?php echo esc_attr( $license_key ); ?>"
								autocomplete="off" />
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Status', 'service-booking' ); ?></th>
						<td>
							<span class="<?php echo esc_attr( $status_class ); ?>">
								<?php echo esc_html( $status_label ); ?>
							</span>
						</td>
					</tr>
				</table>

				<?php if ( 'active' === $license_status ) : ?>
					<input type="hidden" name="sg_license_action" value="deactivate" />
					<?php submit_button( __( 'Deactivate License', 'service-booking' ) ); ?>
				<?php else : ?>
					<input type="hidden" name="sg_license_action" value="activate" />
					<?php submit_button( __( 'Activate License', 'service-booking' ) ); ?>
				<?php endif; ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Show admin notice when the license is invalid or expired.
	 */
	public function admin_notices() {
		$status = $this->get_license_status();

		if ( 'invalid' !== $status && 'expired' !== $status ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %s: link to license page */
					esc_html__( 'SG Flexi Booking Pro: Your license is %1$s. Please %2$senter a valid license key%3$s to continue using Pro features.', 'service-booking' ),
					'<strong>' . esc_html( $status ) . '</strong>',
					'<a href="' . esc_url( admin_url( 'admin.php?page=sg_booking_license' ) ) . '">',
					'</a>'
				);
				?>
			</p>
		</div>
		<?php
	}
}

// Initialize the license manager.
SG_License_Manager::get_instance();
