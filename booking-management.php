<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link    https://startandgrow.in
 * @since   1.0.0
 * @package Booking_Management
 *
 * @wordpress-plugin
 * Plugin Name:       SG Flexi Booking Lite
 * Plugin URI:        https://startandgrow.in
 * Description:       A free, powerful booking engine for WordPress & WooCommerce. Create services, manage bookings, and accept payments. Upgrade to Pro for analytics, coupons, price modules, PDF builder, and more. <strong>Requires WooCommerce.</strong>
 * Version:           1.1.0
 * Requires Plugins:  woocommerce
 * Author:            Start and Grow
 * Author URI:        https://startandgrow.in
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       service-booking
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/*
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BOOKING_MANAGEMENT_VERSION', '1.1.0' );
define( 'BOOKING_MANAGEMENT_FILE', __FILE__ );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-booking-management-activator.php
 */
function activate_booking_management() {
	// Require WooCommerce to be active before activation.
	if ( ! class_exists( 'WooCommerce' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'SG Flexi Booking Lite requires WooCommerce to be installed and active. Please install WooCommerce first.', 'service-booking' ),
			esc_html__( 'Plugin Activation Error', 'service-booking' ),
			array( 'back_link' => true )
		);
	}

	include_once plugin_dir_path( __FILE__ ) . 'includes/class-booking-management-dbhandler.php';
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-booking-management-request.php';
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-booking-management-activator.php';
	$activator = new Booking_Management_Activator();
	$activator->activate();

}//end activate_booking_management()


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-booking-management-deactivator.php
 */
function deactivate_booking_management() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-booking-management-deactivator.php';
	$deactivator = new Booking_Management_Deactivator();
	$deactivator->deactivate();

}//end deactivate_booking_management()


register_activation_hook( __FILE__, 'activate_booking_management' );
register_deactivation_hook( __FILE__, 'deactivate_booking_management' );

/**
 * Check WooCommerce dependency at runtime.
 * If WooCommerce is not active, show an admin notice and bail early.
 */
function bm_check_woocommerce_dependency() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'bm_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice displayed when WooCommerce is not active.
 */
function bm_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<strong><?php esc_html_e( 'SG Flexi Booking Lite', 'service-booking' ); ?></strong>
			<?php esc_html_e( 'requires WooCommerce to be installed and active. Please install and activate WooCommerce to use this plugin.', 'service-booking' ); ?>
		</p>
	</div>
	<?php
}

/*
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-booking-management.php';

// Freemius SDK integration (when provider is 'freemius').
if ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/class-sg-freemius.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sg-freemius.php';
}

/**
 * Enqueue the deactivation feedback modal on the Plugins page.
 */
function bm_enqueue_deactivation_modal() {
	$screen = get_current_screen();
	if ( ! $screen || 'plugins' !== $screen->id ) {
		return;
	}

	$plugin_basename = plugin_basename( __FILE__ );

	wp_enqueue_style(
		'bm-deactivation-modal',
		plugin_dir_url( __FILE__ ) . 'admin/css/booking-management-deactivation.css',
		array(),
		BOOKING_MANAGEMENT_VERSION
	);

	wp_enqueue_script(
		'bm-deactivation-modal',
		plugin_dir_url( __FILE__ ) . 'admin/js/booking-management-deactivation.js',
		array( 'jquery' ),
		BOOKING_MANAGEMENT_VERSION,
		true
	);

	wp_localize_script(
		'bm-deactivation-modal',
		'bm_deactivation',
		array(
			'ajax_url'        => admin_url( 'admin-ajax.php' ),
			'nonce'           => wp_create_nonce( 'bm_deactivation_nonce' ),
			'plugin_basename' => $plugin_basename,
			'i18n'            => array(
				'title'       => __( 'Deactivate SG Flexi Booking Lite', 'service-booking' ),
				'description' => __( 'Would you like to keep or delete all plugin data (database tables, settings, and scheduled tasks)?', 'service-booking' ),
				'keep'        => __( 'Keep Data', 'service-booking' ),
				'delete'      => __( 'Delete All Data', 'service-booking' ),
				'cancel'      => __( 'Cancel', 'service-booking' ),
				'deleting'    => __( 'Deleting data…', 'service-booking' ),
				'confirm'     => __( 'Are you sure you want to permanently delete ALL plugin data? This cannot be undone.', 'service-booking' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'bm_enqueue_deactivation_modal' );

/**
 * AJAX handler: Delete all plugin data before deactivation.
 */
function bm_ajax_delete_plugin_data() {
	check_ajax_referer( 'bm_deactivation_nonce', 'nonce' );

	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json_error( 'Unauthorized' );
	}

	bm_delete_all_plugin_data();

	wp_send_json_success();
}
add_action( 'wp_ajax_bm_delete_all_plugin_data', 'bm_ajax_delete_plugin_data' );

/**
 * Delete all plugin database tables, options, and cron jobs.
 */
function bm_delete_all_plugin_data() {
	global $wpdb;

	$prefix = $wpdb->prefix . 'sgbm_';

	// 1. Drop all plugin tables.
	$tables = array(
		'services',
		'time_slots',
		'gallery',
		'service_extras',
		'categories',
		'billing_forms',
		'fields',
		'booking',
		'booking_slot_count',
		'extra_svc_booking_count',
		'saved_search',
		'email_template',
		'customers',
		'transactions',
		'booking_archive',
		'email_records',
		'vouchers',
		'checkin',
		'availability_periods',
		'global_extras',
		'service_global_extras',
		'service_category_map',
	);

	foreach ( $tables as $table ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS {$prefix}{$table}" );
	}

	// 2. Delete all plugin options (bm_* prefix).
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
			'bm\_%'
		)
	);

	// 3. Unschedule all plugin cron jobs.
	$cron_hooks = array(
		'flexibooking_check_expired_book_on_request_bookings',
		'flexibooking_check_paid_expired_processing_bookings',
		'flexibooking_check_expired_pending_bookings',
		'flexibooking_check_expired_free_bookings',
		'flexibooking_check_expired_vouchers',
		'bm_resend_missing_emails_hook',
		'sg_async_queue_cron',
	);

	foreach ( $cron_hooks as $hook ) {
		$timestamp = wp_next_scheduled( $hook );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $hook );
		}
		wp_clear_scheduled_hook( $hook );
	}

	// 4. Delete transients.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			'\_transient\_bm\_%',
			'\_transient\_timeout\_bm\_%'
		)
	);
}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_booking_management() {
	// Do not run if WooCommerce is missing.
	if ( ! bm_check_woocommerce_dependency() ) {
		return;
	}

	$plugin = new Booking_Management();
	$plugin->run();

}//end run_booking_management()


add_action( 'plugins_loaded', 'run_booking_management' );
