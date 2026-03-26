<?php
/**
 * Modular License Management System for SG Flexi Booking.
 *
 * Handles license validation, activation, deactivation, and caching.
 * Provides concrete EDD Software Licensing API and Freemius SDK support,
 * while remaining fully abstracted for future custom license servers.
 *
 * The Lite plugin ships this class but **never** returns true from the
 * central gatekeeper filter `sg_booking_is_pro_active` (implemented in
 * class-booking-management-feature-control.php and
 * class-booking-management-limits.php, not here). The Pro add-on hooks
 * that filter to return true only when a valid license is detected.
 *
 * Architecture:
 *   - `sg_license_validation_method` filter: choose 'edd', 'freemius',
 *     or 'custom'. Default is 'edd'.
 *   - The validate/activate/deactivate methods delegate to the chosen
 *     provider automatically.
 *   - Results are cached in transients for 24 hours.
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
	 * WordPress option key for the stored license expiry date.
	 */
	const OPTION_LICENSE_EXPIRY = 'sg_booking_license_expiry';

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
		add_action( 'admin_menu', array( $this, 'register_license_page' ), 99 );
		add_action( 'admin_init', array( $this, 'register_license_settings' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		// Register the built-in EDD and Freemius validation handlers.
		add_filter( 'sg_license_validate', array( $this, 'validate_via_provider' ), 10, 3 );
	}

	// ------------------------------------------------------------------
	// Provider Configuration
	// ------------------------------------------------------------------

	/**
	 * Get the active license validation provider.
	 *
	 * Defaults to 'edd'. Can be overridden by defining
	 * `SG_BOOKING_LICENSE_PROVIDER` or via the
	 * `sg_license_validation_method` filter.
	 *
	 * @return string 'edd', 'freemius', or 'custom'.
	 */
	public function get_provider() {
		$default = defined( 'SG_BOOKING_LICENSE_PROVIDER' )
			? SG_BOOKING_LICENSE_PROVIDER
			: 'edd';

		/**
		 * Filter the license validation provider.
		 *
		 * @since 1.1.0
		 * @param string $provider 'edd', 'freemius', or 'custom'.
		 */
		return apply_filters( 'sg_license_validation_method', $default );
	}

	/**
	 * Get the EDD store URL.
	 *
	 * @return string
	 */
	private function get_edd_store_url() {
		return defined( 'SG_BOOKING_EDD_STORE_URL' )
			? SG_BOOKING_EDD_STORE_URL
			: 'https://startandgrow.in';
	}

	/**
	 * Get the EDD item ID (download ID).
	 *
	 * @return int
	 */
	private function get_edd_item_id() {
		return defined( 'SG_BOOKING_EDD_ITEM_ID' )
			? (int) SG_BOOKING_EDD_ITEM_ID
			: 0;
	}

	/**
	 * Get the EDD item name.
	 *
	 * @return string
	 */
	private function get_edd_item_name() {
		return defined( 'SG_BOOKING_EDD_ITEM_NAME' )
			? SG_BOOKING_EDD_ITEM_NAME
			: 'SG Flexi Booking Pro';
	}

	// ------------------------------------------------------------------
	// License Validation
	// ------------------------------------------------------------------

	/**
	 * Validate a license key.
	 *
	 * Checks the transient cache first, then delegates to the configured
	 * provider (EDD / Freemius / custom).
	 *
	 * @param string $license_key The license key to validate.
	 * @param string $item_id     Optional. The product / plan ID.
	 * @return bool True if valid.
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
		 * Validate the license via the configured provider.
		 *
		 * @since 1.1.0
		 *
		 * @param bool   $is_valid    Default false.
		 * @param string $license_key The license key.
		 * @param string $item_id     The product / plan ID.
		 */
		$is_valid = (bool) apply_filters( 'sg_license_validate', false, $license_key, $item_id );

		// Cache the result for 24 hours.
		set_transient( self::TRANSIENT_KEY, $is_valid ? 1 : 0, self::CACHE_DURATION );

		return $is_valid;
	}

	/**
	 * Central dispatch for license validation.
	 *
	 * Called via the `sg_license_validate` filter. Routes to the
	 * appropriate provider method based on `get_provider()`.
	 *
	 * @param bool   $is_valid    Current validation state.
	 * @param string $license_key The license key.
	 * @param string $item_id     The product / plan ID.
	 * @return bool
	 */
	public function validate_via_provider( $is_valid, $license_key, $item_id ) {
		$provider = $this->get_provider();

		switch ( $provider ) {
			case 'edd':
				return $this->edd_check_license( $license_key );

			case 'freemius':
				return $this->freemius_check_license( $license_key );

			case 'custom':
				// Custom providers hook into `sg_license_validate` at a
				// higher priority and return their own result.
				return $is_valid;

			default:
				return $is_valid;
		}
	}

	// ------------------------------------------------------------------
	// EDD Software Licensing API
	// ------------------------------------------------------------------

	/**
	 * Validate a license key against the EDD Software Licensing API.
	 *
	 * @param string $license_key The license key.
	 * @return bool True if active and not expired.
	 */
	private function edd_check_license( $license_key ) {
		$response = $this->edd_api_request( 'check_license', $license_key );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $data ) || ! isset( $data['license'] ) ) {
			return false;
		}

		// Store the expiry date if available.
		if ( ! empty( $data['expires'] ) && 'lifetime' !== $data['expires'] ) {
			update_option( self::OPTION_LICENSE_EXPIRY, sanitize_text_field( $data['expires'] ) );
		} elseif ( 'lifetime' === ( $data['expires'] ?? '' ) ) {
			update_option( self::OPTION_LICENSE_EXPIRY, 'lifetime' );
		}

		return 'valid' === $data['license'];
	}

	/**
	 * Activate a license key via the EDD API.
	 *
	 * @param string $license_key The license key.
	 * @return bool True on success.
	 */
	private function edd_activate( $license_key ) {
		$response = $this->edd_api_request( 'activate_license', $license_key );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $data ) || ! isset( $data['license'] ) ) {
			return false;
		}

		return 'valid' === $data['license'];
	}

	/**
	 * Deactivate a license key via the EDD API.
	 *
	 * @param string $license_key The license key.
	 * @return bool True on success.
	 */
	private function edd_deactivate( $license_key ) {
		$response = $this->edd_api_request( 'deactivate_license', $license_key );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		return is_array( $data ) && 'deactivated' === ( $data['license'] ?? '' );
	}

	/**
	 * Send a request to the EDD Software Licensing API.
	 *
	 * @param string $action      The EDD SL action (activate_license, deactivate_license, check_license).
	 * @param string $license_key The license key.
	 * @return array|WP_Error The raw wp_remote_get() response.
	 */
	private function edd_api_request( $action, $license_key ) {
		$api_params = array(
			'edd_action' => $action,
			'license'    => sanitize_text_field( $license_key ),
			'url'        => home_url(),
		);

		$item_id = $this->get_edd_item_id();
		if ( $item_id > 0 ) {
			$api_params['item_id'] = $item_id;
		} else {
			$api_params['item_name'] = rawurlencode( $this->get_edd_item_name() );
		}

		return wp_remote_get(
			add_query_arg( $api_params, $this->get_edd_store_url() ),
			array(
				'timeout'   => 15,
				'sslverify' => true,
			)
		);
	}

	// ------------------------------------------------------------------
	// Freemius SDK Support
	// ------------------------------------------------------------------

	/**
	 * Validate a license key against the Freemius SDK.
	 *
	 * Requires the Freemius SDK to be loaded (typically by the Pro add-on).
	 * If Freemius is not available, returns false.
	 *
	 * @param string $license_key The license key.
	 * @return bool
	 */
	private function freemius_check_license( $license_key ) {
		// The Freemius SDK global instance is typically set up by the
		// Pro plugin via `fs_dynamic_init()`. If it's not available,
		// the license cannot be validated.
		if ( ! function_exists( 'sg_booking_fs' ) ) {
			return false;
		}

		$fs = sg_booking_fs();

		if ( ! is_object( $fs ) || ! method_exists( $fs, 'is_paying' ) ) {
			return false;
		}

		return $fs->is_paying();
	}

	// ------------------------------------------------------------------
	// Activation / Deactivation
	// ------------------------------------------------------------------

	/**
	 * Activate a license key.
	 *
	 * @param string $license_key The key to activate.
	 * @param string $item_id     The product / plan ID.
	 * @return bool True on success.
	 */
	public function activate_license( $license_key, $item_id = '' ) {
		$license_key = sanitize_text_field( $license_key );

		/**
		 * Fires before a license activation attempt.
		 *
		 * @since 1.2.0
		 *
		 * @param string $license_key The license key being activated.
		 * @param string $item_id     The product/plan ID.
		 */
		do_action( 'sg_license_before_activation', $license_key, $item_id );

		update_option( self::OPTION_LICENSE_KEY, $license_key );
		delete_transient( self::TRANSIENT_KEY );

		$provider = $this->get_provider();
		$valid    = false;

		if ( 'edd' === $provider ) {
			$valid = $this->edd_activate( $license_key );
		} elseif ( 'freemius' === $provider ) {
			$valid = $this->freemius_check_license( $license_key );
		} else {
			$valid = $this->validate_license( $license_key, $item_id );
		}

		$status = $valid ? 'active' : 'invalid';
		update_option( self::OPTION_LICENSE_STATUS, $status );

		// Cache the activation result.
		set_transient( self::TRANSIENT_KEY, $valid ? 1 : 0, self::CACHE_DURATION );

		/**
		 * Fires after a license activation attempt completes.
		 *
		 * @since 1.2.0
		 *
		 * @param string $license_key The license key.
		 * @param bool   $valid       Whether activation succeeded.
		 * @param string $status      The new license status ('active' or 'invalid').
		 * @param string $provider    The license provider used ('edd', 'freemius', 'custom').
		 */
		do_action( 'sg_license_after_activation', $license_key, $valid, $status, $provider );

		return $valid;
	}

	/**
	 * Deactivate the current license.
	 *
	 * @return void
	 */
	public function deactivate_license() {
		$license_key = $this->get_license_key();
		$provider    = $this->get_provider();

		/**
		 * Fires before a license deactivation attempt.
		 *
		 * @since 1.2.0
		 *
		 * @param string $license_key The license key being deactivated.
		 * @param string $provider    The license provider ('edd', 'freemius', 'custom').
		 */
		do_action( 'sg_license_before_deactivation', $license_key, $provider );

		// Tell the remote server to release this site.
		if ( ! empty( $license_key ) && 'edd' === $provider ) {
			$this->edd_deactivate( $license_key );
		}

		delete_option( self::OPTION_LICENSE_KEY );
		delete_option( self::OPTION_LICENSE_EXPIRY );
		update_option( self::OPTION_LICENSE_STATUS, 'inactive' );
		delete_transient( self::TRANSIENT_KEY );

		/**
		 * Fires after a license is deactivated.
		 *
		 * @since 1.2.0
		 *
		 * @param string $license_key The license key that was deactivated.
		 * @param string $provider    The license provider.
		 */
		do_action( 'sg_license_after_deactivation', $license_key, $provider );
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
	 * Get the stored license expiry date.
	 *
	 * @return string ISO-8601 date string, 'lifetime', or empty.
	 */
	public function get_license_expiry() {
		return (string) get_option( self::OPTION_LICENSE_EXPIRY, '' );
	}

	/**
	 * Whether the license is currently active.
	 *
	 * @return bool
	 */
	public function is_license_active() {
		$is_active = ( 'active' === $this->get_license_status() );

		/**
		 * Filter the license active status.
		 *
		 * Allows external plugins to override the license check
		 * (e.g., during testing or custom license integration).
		 *
		 * @since 1.2.0
		 *
		 * @param bool   $is_active   Whether the license is active.
		 * @param string $license_key The stored license key.
		 * @param string $status      The raw status string.
		 */
		return (bool) apply_filters( 'sg_license_is_active', $is_active, $this->get_license_key(), $this->get_license_status() );
	}

	// ------------------------------------------------------------------
	// Admin UI
	// ------------------------------------------------------------------

	/**
	 * Register the license settings page under the FlexiBooking menu.
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
		$license_expiry = $this->get_license_expiry();
		$status_label   = ucfirst( $license_status );
		$provider       = $this->get_provider();

		if ( 'active' === $license_status ) {
			$status_class = 'sg-license-active';
			$status_color = '#46b450';
		} elseif ( 'expired' === $license_status ) {
			$status_class = 'sg-license-expired';
			$status_color = '#dc3232';
		} else {
			$status_class = 'sg-license-inactive';
			$status_color = '#999';
		}

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
							<span class="<?php echo esc_attr( $status_class ); ?>" style="font-weight:600;color:<?php echo esc_attr( $status_color ); ?>">
								<?php echo esc_html( $status_label ); ?>
							</span>
						</td>
					</tr>
					<?php if ( ! empty( $license_expiry ) ) : ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Expires', 'service-booking' ); ?></th>
						<td>
							<?php
							if ( 'lifetime' === $license_expiry ) {
								esc_html_e( 'Lifetime', 'service-booking' );
							} else {
								echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $license_expiry ) ) );
							}
							?>
						</td>
					</tr>
					<?php endif; ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Provider', 'service-booking' ); ?></th>
						<td>
							<code><?php echo esc_html( strtoupper( $provider ) ); ?></code>
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

			<hr />
			<p class="description">
				<?php esc_html_e( 'Your license key was provided with your SG Flexi Booking Pro purchase. Enter it above to unlock all Pro features.', 'service-booking' ); ?>
			</p>
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

		/**
		 * Filter whether to display the license notice.
		 *
		 * @since 1.2.0
		 *
		 * @param bool   $show   Whether to show the notice. Default true.
		 * @param string $status The current license status.
		 * @param string $screen The current admin screen ID.
		 */
		$show = apply_filters( 'sg_license_show_notice', true, $status, $screen->id );
		if ( ! $show ) {
			return;
		}

		/**
		 * Fires before the license admin notice is displayed.
		 *
		 * @since 1.2.0
		 *
		 * @param string $status The license status ('invalid' or 'expired').
		 */
		do_action( 'sg_license_before_notice', $status );

		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %1$s: status, %2$s: link open, %3$s: link close */
					esc_html__( 'SG Flexi Booking Pro: Your license is %1$s. Please %2$senter a valid license key%3$s to continue using Pro features.', 'service-booking' ),
					'<strong>' . esc_html( $status ) . '</strong>',
					'<a href="' . esc_url( admin_url( 'admin.php?page=sg_booking_license' ) ) . '">',
					'</a>'
				);
				?>
			</p>
		</div>
		<?php

		/**
		 * Fires after the license admin notice is displayed.
		 *
		 * @since 1.2.0
		 *
		 * @param string $status The license status.
		 */
		do_action( 'sg_license_after_notice', $status );
	}
}

// Initialize the license manager.
SG_License_Manager::get_instance();
