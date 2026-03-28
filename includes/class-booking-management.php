<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://startandgrow.in
 * @since      1.0.0
 *
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 * @author     Start and Grow <laravel6@startandgrow.in>
 */
class Booking_Management {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Booking_Management_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The admin class instance.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      Booking_Management_Admin    $plugin_admin
	 */
	private $plugin_admin;

	/**
	 * The public class instance.
	 *
	 * @since    1.2.0
	 * @access   private
	 * @var      Booking_Management_Public    $plugin_public
	 */
	private $plugin_public;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'BOOKING_MANAGEMENT_VERSION' ) ) {
			$this->version = BOOKING_MANAGEMENT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'booking-management';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		/**
		 * SMTP and Stripe are Pro-only features.
		 * The Pro add-on initialises these via hooks.
		 */
		do_action( 'sg_booking_init_pro_connections' );

		// Initialize hybrid architecture components.
		$this->init_event_system();


		ob_start();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Booking_Management_Loader. Orchestrates the hooks of the plugin.
	 * - Booking_Management_i18n. Defines internationalization functionality.
	 * - Booking_Management_Admin. Defines all hooks for the admin area.
	 * - Booking_Management_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-booking-management-activator.php';
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-booking-management-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-booking-management-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-booking-management-admin.php';

		/**
		 * WP_List_Table implementations for admin listing pages.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-bm-orders-list-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-bm-services-list-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-bm-categories-list-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-bm-customers-list-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-bm-vouchers-list-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-bm-email-templates-list-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-bm-checkins-list-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-bm-pdf-templates-list-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-bm-email-records-list-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-bm-fields-list-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-bm-forms-list-table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/list-tables/class-bm-global-extras-list-table.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-booking-management-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-booking-management-dbhandler.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-booking-management-request.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-booking-management-woocommerce.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-booking-management-email.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-booking-management-sanitized.php';

		/**
		 * Freemium gatekeeper and feature control.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-booking-management-feature-control.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-booking-management-limits.php';

		/**
		 * Fires before core dependencies are fully loaded.
		 *
		 * The Pro add-on hooks here to load its own class files:
		 * Stripe gateway, SMTP, vouchers, coupons, PDF customizer, etc.
		 *
		 * @since 1.1.0
		 */
		do_action( 'sg_booking_load_pro_libraries' );

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-booking-validation.php';

		/**
		 * License management — always loaded.
		 * Provides the admin license page and validation helpers.
		 * The Pro plugin uses this to verify its license status.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sg-license-manager.php';

		/**
		 * Hybrid architecture classes for performance and scalability.
		 *
		 * - Event Dispatcher: Event-driven architecture for decoupled processing.
		 * - Cache Manager:    Unified caching layer (Redis/Memcached/Transients).
		 * - Async Queue:      Background job processor for heavy tasks.
		 *
		 * @since 1.2.0
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sg-event-dispatcher.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sg-cache-manager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sg-async-queue.php';

		/**
		 * Core REST API for the plugin's admin and public endpoints.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-booking-management-rest-api.php';

		/**
		 * Fires after the Lite plugin has loaded its core dependencies.
		 *
		 * The Pro add-on hooks here to load additional Pro-only class files
		 * (e.g., advanced analytics, extended coupon logic, QR scanner).
		 *
		 * @since 1.1.0
		 */
		do_action( 'sg_booking_dependencies_loaded' );

		$this->loader = new Booking_Management_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Booking_Management_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
         $plugin_i18n = new Booking_Management_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'detect_active_plugin', 20 );
        $this->loader->add_action( 'activated_plugin', $plugin_i18n, 'on_plugin_activation', 10, 1 );
        $this->loader->add_action( 'deactivated_plugin', $plugin_i18n, 'on_plugin_deactivation', 10, 1 );
        $this->loader->add_action( 'wp', $plugin_i18n, 'bm_save_frontend_language' );
        $this->loader->add_action( 'admin_init', $plugin_i18n, 'bm_save_backend_language', 1 );
		$this->loader->add_filter( 'locale', $plugin_i18n, 'bm_force_admin_locale_from_wpml', 999, 1 );
		$this->loader->add_filter( 'load_textdomain_mofile', $plugin_i18n, 'bm_redirect_textdomain_to_plugin', 10, 2 );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
         $this->plugin_admin = new Booking_Management_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $this->plugin_admin, 'booking_admin_menu' );
		$this->loader->add_action( 'admin_notices', $this->plugin_admin, 'bm_disable_admin_notices_on_specific_pages', 1 );
		$this->loader->add_filter( 'admin_title', $this->plugin_admin, 'bm_ensure_admin_title', 1 );
		$this->loader->add_filter( 'parent_file', $this->plugin_admin, 'bm_fix_menu_highlight' );
		$this->loader->add_filter( 'set-screen-option', $this->plugin_admin, 'bm_set_screen_option', 10, 3 );
		$this->loader->add_action( 'init', $this->plugin_admin, 'bm_set_timezone' );
		$this->loader->add_action( 'update_option_timezone_string', $this->plugin_admin, 'bm_update_plugin_timezone_on_wp_change', 10, 2 );
		$this->loader->add_action( 'update_option_gmt_offset', $this->plugin_admin, 'bm_update_plugin_timezone_on_gmt_offset_change', 10, 2 );
		$this->loader->add_action( 'init', $this->plugin_admin, 'bm_register_shortcodes' );
		$this->loader->add_action( 'init', $this->plugin_admin, 'bm_set_installed_languages' );
		// $this->loader->add_action( 'init', $this->plugin_admin, 'bm_load_service_booking_locale' ); //->translation issues
		$this->loader->add_action( 'init', $this->plugin_admin, 'bm_multilingual_email' );
		$this->loader->add_filter( 'cron_schedules', $this->plugin_admin, 'bm_custom_cron_schedule' );
		$this->loader->add_action( 'init', $this->plugin_admin, 'bm_check_booking_requests' );
		$this->loader->add_action( 'init', $this->plugin_admin, 'bm_check_falied_emails_and_resend_pdfs' );
		$this->loader->add_action( 'bm_resend_missing_emails_hook', $this->plugin_admin, 'bm_resend_missing_emails_cron' );
		$this->loader->add_action( 'flexibooking_check_expired_book_on_request_bookings', $this->plugin_admin, 'flexibooking_check_expired_book_on_request_bookings_callback' );
		$this->loader->add_action( 'init', $this->plugin_admin, 'bm_mark_flexi_paid_processing_bookings_as_completed' );
		$this->loader->add_action( 'flexibooking_check_paid_expired_processing_bookings', $this->plugin_admin, 'flexibooking_check_paid_expired_processing_bookings_callback' );
		$this->loader->add_action( 'init', $this->plugin_admin, 'bm_mark_pending_bookings_as_cancelled' );
		$this->loader->add_action( 'flexibooking_check_expired_pending_bookings', $this->plugin_admin, 'flexibooking_check_expired_pending_bookings_callback' );
		$this->loader->add_action( 'init', $this->plugin_admin, 'bm_mark_expired_free_bookings_as_completed' );
		$this->loader->add_action( 'flexibooking_check_expired_free_bookings', $this->plugin_admin, 'flexibooking_check_expired_free_bookings_callback' );
		$this->loader->add_action( 'init', $this->plugin_admin, 'bm_check_expired_vouchers' );
		$this->loader->add_action( 'flexibooking_check_expired_vouchers', $this->plugin_admin, 'flexibooking_check_expired_vouchers_callback' );
		$this->loader->add_action( 'admin_bar_menu', $this->plugin_admin, 'bm_add_flexibooking_language_switcher_in_admin_bar', 999 );
		$this->loader->add_action( 'wp_footer', $this->plugin_admin, 'bm_add_flexibooking_language_switcher_in_footer' );



		$this->loader->add_filter( 'flexibooking_cancel_booking', $this->plugin_admin, 'bm_flexibooking_cancel_booking', 10, 1 );
		$this->loader->add_filter( 'flexibooking_update_status_as_refunded', $this->plugin_admin, 'bm_flexibooking_update_status_as_refunded', 10, 2 );
		$this->loader->add_filter( 'flexibooking_update_status_as_completed', $this->plugin_admin, 'bm_flexibooking_update_status_as_completed', 10, 1 );
		$this->loader->add_filter( 'flexibooking_update_status_as_processing', $this->plugin_admin, 'bm_flexibooking_update_status_as_processing', 10, 1 );
		$this->loader->add_filter( 'flexibooking_update_status_as_on_hold', $this->plugin_admin, 'bm_flexibooking_update_status_as_on_hold', 10, 1 );
		$this->loader->add_filter( 'flexibooking_mark_processing_orders_as_complete', $this->plugin_admin, 'bm_flexibooking_mark_processing_orders_as_complete', 10, 1 );
		$this->loader->add_filter( 'bm_mark_free_orders_as_complete', $this->plugin_admin, 'bm_mark_free_orders_as_complete', 10, 1 );

		$this->loader->add_action( 'flexibooking_set_process_approved_order', $this->plugin_admin, 'bm_flexibooking_set_process_approved_order_callback', 10, 1 );
		$this->loader->add_action( 'flexibooking_mail_approved_order', $this->plugin_admin, 'bm_flexibooking_mail_on_approved_order_callback', 10, 3 );
		$this->loader->add_action( 'flexibooking_set_process_cancel_order', $this->plugin_admin, 'bm_flexibooking_set_process_cancel_order_callback', 10, 1 );
		$this->loader->add_action( 'flexibooking_mail_cancel_order', $this->plugin_admin, 'bm_flexibooking_mail_on_cancel_order_callback', 10, 3 );
		$this->loader->add_action( 'flexibooking_set_process_failed_order', $this->plugin_admin, 'bm_flexibooking_set_process_failed_order_callback', 10, 1 );
		$this->loader->add_action( 'flexibooking_mail_failed_order', $this->plugin_admin, 'bm_flexibooking_mail_on_failed_order_callback', 10, 3 );
		$this->loader->add_filter( 'flexibooking_refund_cancelled_order', $this->plugin_admin, 'bm_flexibooking_refund_cancelled_order', 10, 1 );
		$this->loader->add_action( 'flexibooking_set_process_order_refund', $this->plugin_admin, 'bm_flexibooking_set_process_order_refund_callback', 10, 2 );
		$this->loader->add_action( 'flexibooking_mail_order_refund', $this->plugin_admin, 'bm_flexibooking_mail_on_order_refund_callback', 10, 3 );
		// $this->loader->add_filter( 'flexibooking_google_analytics_data', $this->plugin_admin, 'bm_prepare_ga_purchase_data', 10, 1 );

		$this->loader->add_filter( 'flexibooking_fetch_order_transaction_data', $this->plugin_admin, 'bm_flexibooking_fetch_order_transaction_data', 10, 1 );
		$this->loader->add_filter( 'flexibooking_fetch_html_with_transaction_data', $this->plugin_admin, 'bm_flexibooking_fetch_html_with_transaction_data', 10, 1 );
		$this->loader->add_filter( 'flexibooking_save_order_transaction_data', $this->plugin_admin, 'bm_flexibooking_save_order_transaction_data', 10, 5 );
		$this->loader->add_action( 'flexibooking_save_existing_transaction_data_before_update', $this->plugin_admin, 'bm_flexibooking_save_existing_transaction_data_before_update', 10, 1 );
		$this->loader->add_filter( 'flexibooking_verify_if_valid_transaction_id', $this->plugin_admin, 'bm_flexibooking_verify_if_valid_transaction_id', 10, 3 );
		$this->loader->add_filter( 'flexibooking_verify_if_paid_transaction_id', $this->plugin_admin, 'bm_flexibooking_verify_if_paid_transaction_id', 10, 1 );
		$this->loader->add_filter( 'flexibooking_paid_transaction_statuses', $this->plugin_admin, 'bm_flexibooking_paid_transaction_statuses', 10, 1 );
		$this->loader->add_filter( 'flexibooking_verify_if_pending_transaction_id', $this->plugin_admin, 'bm_flexibooking_verify_if_pending_transaction_id', 10, 1 );
		$this->loader->add_filter( 'flexibooking_pending_transaction_statuses', $this->plugin_admin, 'bm_flexibooking_pending_transaction_statuses', 10, 1 );
		$this->loader->add_filter( 'flexibooking_verify_if_cancelled_transaction_id', $this->plugin_admin, 'bm_flexibooking_verify_if_cancelled_transaction_id', 10, 1 );
		$this->loader->add_filter( 'flexibooking_verify_transaction_for_free_payment_status', $this->plugin_admin, 'bm_flexibooking_verify_transaction_for_free_payment_status', 10, 1 );
		$this->loader->add_filter( 'flexibooking_verify_if_refunded_transaction_id', $this->plugin_admin, 'bm_flexibooking_verify_if_refunded_transaction_id', 10, 1 );
		$this->loader->add_filter( 'flexibooking_update_transaction_data', $this->plugin_admin, 'bm_flexibooking_update_transaction_data', 10, 2 );
		$this->loader->add_filter( 'flexibooking_update_booking_data_before_marking_transaction_failed', $this->plugin_admin, 'bm_flexibooking_update_booking_data_before_marking_transaction_failed', 10, 1 );
		$this->loader->add_filter( 'flexibooking_add_data_to_failed_transaction_table', $this->plugin_admin, 'bm_flexibooking_add_data_to_failed_transaction_table', 10, 2 );
		$this->loader->add_filter( 'flexibooking_update_booking_data_after_transaction_update', $this->plugin_admin, 'bm_flexibooking_update_booking_data_after_transaction_update', 10, 2 );
		$this->loader->add_filter( 'flexibooking_check_and_remove_duplicate_record_in_failed_transaction_table', $this->plugin_admin, 'bm_flexibooking_check_and_remove_duplicate_record_in_failed_transaction_table', 10, 1 );
		$this->loader->add_filter( 'flexibooking_revert_transaction_update', $this->plugin_admin, 'bm_flexibooking_revert_transaction_update', 10, 1 );
		$this->loader->add_action( 'woocommerce_admin_order_data_after_order_details', $this->plugin_admin, 'bm_display_service_date_in_admin', 10, 1 );
		$this->loader->add_action( 'before_delete_post', $this->plugin_admin, 'bm_remove_flexi_order_if_woocommerce_order_is_permanently_deleted' );
		$this->loader->add_action( 'wp_trash_post', $this->plugin_admin, 'bm_modify_flexi_plugin_order_on_woocommerce_order_trash', 10, 1 );
		$this->loader->add_action( 'untrash_post', $this->plugin_admin, 'bm_schedule_woocommerce_order_status_check_on_untrash', 10, 1 );
		$this->loader->add_action( 'bm_update_flexi_order_as_woocommerce_order_is_restored', $this->plugin_admin, 'bm_modify_flexi_plugin_order_on_woocommerce_order_untrash' );
		$this->loader->add_filter( 'woocommerce_hidden_order_itemmeta', $this->plugin_admin, 'bm_hide_flexi_order_itemmeta', 10, 1 );
		$this->loader->add_action( 'pre_post_update', $this->plugin_admin, 'bm_prevent_expired_woocommerce_order_updates', 10, 2 );
		$this->loader->add_action( 'admin_notices', $this->plugin_admin, 'bm_flexi_admin_notice' );

		$this->loader->add_action( 'rest_api_init', $this, 'register_admin_action_routes' );

		/**
		 * Fires after the Lite admin hooks are registered.
		 *
		 * Use this hook to register custom admin AJAX handlers,
		 * add filters, or extend admin functionality.
		 *
		 * @since 1.1.0
		 *
		 * @param Booking_Management_Loader $loader       The hook loader instance.
		 * @param Booking_Management_Admin  $plugin_admin The admin class instance.
		 */
		do_action( 'sg_booking_register_admin_hooks', $this->loader, $this->plugin_admin );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
        $this->plugin_public = new Booking_Management_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $this->plugin_public, 'bm_register_shortcodes' );

		$this->loader->add_action( 'flexibooking_set_process_new_order', $this->plugin_public, 'bm_flexibooking_set_process_new_order_callback', 10, 1 );
		$this->loader->add_action( 'flexibooking_mail_new_order', $this->plugin_public, 'bm_flexibooking_mail_on_new_order_callback', 10, 3 );
		$this->loader->add_action( 'flexibooking_set_process_voucher_redeem', $this->plugin_public, 'bm_flexibooking_set_process_voucher_redeem_callback', 10, 1 );
		$this->loader->add_action( 'flexibooking_mail_voucher_redeem', $this->plugin_public, 'bm_flexibooking_mail_on_voucher_redeem_callback', 10, 3 );
		$this->loader->add_action( 'flexibooking_set_process_new_request', $this->plugin_public, 'bm_flexibooking_set_process_new_request_callback', 10, 1 );
		$this->loader->add_action( 'flexibooking_mail_new_request', $this->plugin_public, 'bm_flexibooking_mail_new_request_callback', 10, 3 );
		$this->loader->add_action( 'flexibooking_set_process_new_order_voucher', $this->plugin_public, 'bm_flexibooking_set_process_new_order_voucher_callback', 10, 1 );
		$this->loader->add_action( 'flexibooking_voucher_mail_new_order', $this->plugin_public, 'bm_flexibooking_voucher_mail_new_order_callback', 10, 3 );
		$this->loader->add_action( 'flexibooking_set_process_failed_order_refund', $this->plugin_public, 'bm_flexibooking_set_process_failed_order_refund_callback', 10, 1 );
		$this->loader->add_action( 'flexibooking_mail_failed_order_refund', $this->plugin_public, 'bm_flexibooking_mail_on_failed_order_refund_callback', 10, 3 );
		$this->loader->add_filter( 'flexibooking_google_analytics_data', $this->plugin_public, 'bm_prepare_ga_purchase_data', 10, 1 );
		$this->loader->add_filter( 'woocommerce_checkout_get_value', $this->plugin_public, 'bm_set_checkout_form_value', 10, 2 );
		$this->loader->add_filter( 'woocommerce_cart_item_quantity', $this->plugin_public, 'bm_disable_quantity_change_for_plugin_products', 10, 3 );
		$this->loader->add_filter( 'woocommerce_cart_item_remove_link', $this->plugin_public, 'bm_disable_remove_link_for_plugin_products', 10, 2 );
		$this->loader->add_filter( 'woocommerce_email_attachments', $this->plugin_public, 'bm_add_custom_attachments_to_woocommerce_email', 99, 3 );
		$this->loader->add_action( 'woocommerce_checkout_create_order_line_item', $this->plugin_public, 'bm_save_flexibooking_order_keys_to_order_items', 10, 4 );
		$this->loader->add_action( 'woocommerce_order_status_processing', $this->plugin_public, 'bm_save_woocommerce_booking_data', 10, 1 );
		$this->loader->add_action( 'woocommerce_order_status_cancelled', $this->plugin_public, 'bm_update_flexi_booking_data_on_order_cancellation', 10, 1 );
		$this->loader->add_action( 'woocommerce_order_refunded', $this->plugin_public, 'bm_update_flexi_booking_data_on_order_refund', 10, 1 );
		$this->loader->add_action( 'woocommerce_order_status_on-hold', $this->plugin_public, 'bm_update_flexi_booking_data_on_order_on_hold', 10, 1 );
		$this->loader->add_filter( 'woocommerce_add_to_cart_validation', $this->plugin_public, 'bm_restrict_adding_products_if_added_through_flexi_plugin', 10, 3 );
		$this->loader->add_action( 'woocommerce_order_status_completed', $this->plugin_public, 'bm_set_flexibooking_order_as_completed', 10, 1 );
		$this->loader->add_action( 'woocommerce_email_before_order_table', $this->plugin_public, 'bm_add_service_date_to_email', 20, 4 );
		$this->loader->add_filter( 'woocommerce_thankyou_order_received_text', $this->plugin_public, 'bm_display_service_date_in_thank_you_page', 20, 2 );
		$this->loader->add_action( 'woocommerce_order_details_before_order_table', $this->plugin_public, 'bm_display_service_date_in_view_order', 20 );
		$this->loader->add_action( 'woocommerce_before_calculate_totals', $this->plugin_public, 'bm_adjust_cart_item_prices', 10, 1 );
		$this->loader->add_action( 'woocommerce_cart_emptied', $this->plugin_public, 'bm_clear_flexi_custom_order_keys' );
		$this->loader->add_action( 'woocommerce_before_checkout_billing_form', $this->plugin_public, 'bm_add_gift_fields_to_woocommerce_checkout' );
		$this->loader->add_action( 'woocommerce_checkout_process', $this->plugin_public, 'bm_validate_woocommerce_gift_fields' );
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $this->plugin_public, 'bm_save_gift_fields_to_woocommerce_order_meta' );
		$this->loader->add_filter( 'woocommerce_available_payment_gateways', $this->plugin_public, 'bm_restrict_cod_for_woocommerce_gift_orders' );
		$this->loader->add_filter( 'img_caption_shortcode', $this->plugin_public, 'bm_custom_img_caption_shortcode', 10, 3 );
		$this->loader->add_filter( 'the_title', $this->plugin_public, 'bm_hide_specific_page_title', 10, 2 );
		$this->loader->add_filter( 'body_class', $this->plugin_public, 'flexibooking_add_checkout_body_class_to_woocommerce_checkout' );
		/**$this->loader->add_action( 'woocommerce_payment_complete', $this->plugin_public, 'bm_mark_flexi_orders_paid', 10, 1 );
		$this->loader->add_filter( 'woocommerce_is_sold_individually', $this->plugin_public, 'bm_disable_quantity_for_plugin_added_products', 10, 2 )
		$this->loader->add_action('woocommerce_thankyou', $this->plugin_public, 'bm_redirect_after_order', 10, 1);*/
		$this->loader->add_action( 'bm_after_booking_saved', $this->plugin_public, 'bm_after_booking_saved_callback', 10, 2 );

		$this->loader->add_action( 'rest_api_init', $this, 'register_public_action_routes' );

		/**
		 * Fires after the Lite public hooks are registered.
		 * The Pro add-on registers its own coupon, voucher, Stripe,
		 * QR check-in, and PDF hooks here.
		 *
		 * @since 1.1.0
		 */
		do_action( 'sg_booking_register_pro_public_hooks', $this->loader, $this->plugin_public );
	}



	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
         $this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
         return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Booking_Management_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
         return $this->version;
	}

	/**
	 * Initialize the event-driven architecture components.
	 *
	 * Sets up the Event Dispatcher with default async events and
	 * ensures the Async Queue cron is scheduled.
	 *
	 * @since 1.2.0
	 * @access private
	 */
	private function init_event_system() {
		// Register default async events (email, PDF, analytics, webhooks).
		SG_Event_Dispatcher::register_default_events();

		// Ensure the queue processor cron is active.
		SG_Async_Queue::get_instance()->ensure_scheduled();

		/**
		 * Fires after the event system is initialized.
		 *
		 * Use this hook to register custom event listeners or
		 * mark additional events as asynchronous.
		 *
		 * @since 1.2.0
		 */
		do_action( 'sg_booking_event_system_init' );
	}

	/**
	 * Register admin REST routes that replace the old wp_ajax handlers.
	 *
	 * @since 1.2.0
	 */
	public function register_admin_action_routes() {
		$map = array(
			'bm_flexi_set_lang' => 'bm_flexibooking_set_language',
			'bm_sort_service_listing' => 'bm_sort_service_listing',
			'bm_remove_service' => 'bm_remove_service',
			'bm_duplicate_service' => 'bm_duplicate_service',
			'bm_remove_category' => 'bm_remove_category',
			'bm_sort_category_listing' => 'bm_sort_category_listing',
			'bm_get_service_prices' => 'bm_get_service_prices',
			'bm_set_serice_price' => 'bm_set_serice_price',
			'bm_set_bulk_serice_price' => 'bm_set_bulk_serice_price',
			'bm_save_field_and_setting' => 'bm_save_field_and_setting',
			'bm_get_all_field_labels' => 'bm_get_all_field_labels',
			'bm_get_field_settings' => 'bm_get_field_settings',
			'bm_get_fieldkey_and_order' => 'bm_get_fieldkey_and_order',
			'bm_remove_field' => 'bm_remove_field',
			'bm_save_form_field_order' => 'bm_save_form_field_order',
			'bm_fetch_preview_form' => 'bm_fetch_preview_form',
			'bm_fetch_timezone' => 'bm_fetch_timezone',
			'bm_fetch_ordered_product_details' => 'bm_fetch_ordered_product_details',
			'bm_fetch_ordered_service_details' => 'bm_fetch_ordered_service_details',
			'bm_fetch_customer_data_for_order' => 'bm_fetch_customer_data_for_order',
			'bm_fetch_attachments_for_order' => 'bm_fetch_attachments_for_order',
			'bm_fetch_services_by_category_id' => 'bm_fetch_services_by_category_id',
			'bm_fetch_new_order_service_time_slots' => 'bm_fetch_new_order_service_time_slots',
			'bm_fetch_service_extras_for_backend_order' => 'bm_fetch_service_extras_for_backend_order',
			'bm_fetch_mincap_and_cap_left' => 'bm_fetch_mincap_and_cap_left',
			'bm_fetch_bookable_services_by_category_id_and_date' => 'bm_fetch_bookable_services_by_category_id_and_date',
			'bm_fetch_service_price_for_backend_order' => 'bm_fetch_service_price_for_backend_order',
			'bm_change_order_status_to_complete_or_cancelled' => 'bm_change_order_status_to_complete_or_cancelled',
			'bm_change_order_status' => 'bm_change_order_status',
			'bm_fetch_order_as_per_search' => 'bm_fetch_order_as_per_search',
			'bm_fetch_all_orders' => 'bm_fetch_all_orders',
			'bm_fetch_saved_order_search' => 'bm_fetch_saved_order_search',
			'bm_get_primary_email_field_key' => 'bm_get_primary_email_field_key',
			'bm_save_primary_email_field_key' => 'bm_save_primary_email_field_key',
			'bm_save_non_primary_email_as_primary' => 'bm_save_non_primary_email_as_primary',
			'bm_change_service_visibility' => 'bm_change_service_visibility',
			'bm_change_extra_service_visibility' => 'bm_change_extra_service_visibility',
			'bm_change_category_visibility' => 'bm_change_category_visibility',
			'bm_check_if_existing_field_key' => 'bm_check_if_existing_field_key',
			'bm_fetch_event_condition_value' => 'bm_fetch_value_for_notification_event_type',
			'bm_fetch_notification_processes_listing' => 'bm_fetch_notification_processes_listing',
			'bm_remove_process' => 'bm_remove_notification_process',
			'bm_change_process_visibility' => 'bm_change_notification_process_visibility',
			'bm_update_transaction' => 'bm_update_order_transaction',
			'bm_save_order_transaction' => 'bm_save_order_transaction',
			'bm_get_order_personal_info' => 'bm_get_order_personal_info',
			'bm_get_order_payment_details' => 'bm_get_order_payment_details',
			'bm_get_order_email_info' => 'bm_get_order_email_info',
			'bm_get_order_failed_transactions' => 'bm_get_order_failed_transactions',
			'bm_get_order_products' => 'bm_get_order_products',
			'bm_get_email_content' => 'bm_get_email_content',
			'bm_check_if_exisiting_customer' => 'bm_check_if_exisiting_customer',
			'get_states' => 'bm_fetch_states_by_country',
			'bm_get_service_saleswitch' => 'bm_get_service_saleswitch',
			'bm_get_serice_stopsales' => 'bm_get_serice_stopsales',
			'bm_get_service_time_slots' => 'bm_get_service_time_slots',
			'bm_get_service_max_cap' => 'bm_get_service_max_cap',
			'bm_resend_order_email' => 'bm_resend_order_email',
		);

		foreach ( $map as $action => $method ) {
			register_rest_route(
				'sg-booking/v1',
				"/admin-action/{$action}",
				array(
					'methods'             => 'POST',
					'callback'            => function ( $request ) use ( $method ) {
						return $this->bridge_ajax_handler( $this->plugin_admin, $method, $request );
					},
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				)
			);
		}
	}

	/**
	 * Register public REST routes that replace the old wp_ajax/nopriv handlers.
	 *
	 * @since 1.2.0
	 */
	public function register_public_action_routes() {
		$map = array(
			'bm_flexi_set_frontend_lang' => 'bm_flexibooking_set_language',
			'bm_fetch_all_services' => 'bm_fetch_all_services',
			'bm_filter_services' => 'bm_filter_services',
			'bm_filter_categories' => 'bm_filter_categories',
			'bm_filter_service_by_category' => 'bm_filter_service_by_category',
			'bm_filter_services_by_id' => 'bm_filter_services_by_service_id',
			'bm_fetch_frontend_service_time_slots' => 'bm_fetch_service_time_slots',
			'bm_fetch_service_calendar_time_slots' => 'bm_fetch_service_by_id_calendar_time_slots',
			'bm_fetch_extra_service' => 'bm_fetch_extra_service',
			'bm_fetch_user_form' => 'bm_fetch_user_form',
			'bm_fetch_order_info_and_redirect_to_checkout' => 'bm_fetch_order_info_and_redirect_to_checkout',
			'bm_fetch_booking_data' => 'bm_fetch_booking_data',
			'bm_fetch_service_selection' => 'bm_fetch_service_selection',
			'bm_set_intl_input' => 'bm_set_intl_input',
			'bm_fetch_all_services_by_categories' => 'bm_fetch_all_services_by_categories',
			'bm_get_frontend_service_prices' => 'bm_get_service_prices',
			'bm_fetch_services_by_name' => 'bm_fetch_services_by_name',
			'bm_fetch_service_gallry_images' => 'bm_fetch_service_gallry_images',
			'bm_fetch_checkout_data' => 'bm_fetch_checkout_data_redirect_to_payment',
			'bm_free_checkout' => 'bm_discounted_and_free_checkout_save',
			'bm_process_payment' => 'bm_process_final_payment',
			'bm_save_payment' => 'bm_save_final_payment',
			'bm_check_for_refund' => 'bm_check_for_refund_for_failed_payment',
			'bm_check_session' => 'bm_check_if_payment_session_has_expired',
			'bm_check_discount' => 'bm_fetch_age_data_and_check_discount',
			'bm_reset_discount' => 'bm_reset_discounted_value',
			'bm_fetch_checkout_options' => 'bm_fetch_available_checkout_options',
			'fetch_woocommerce_states' => 'bm_get_woocommerce_states_by_country',
			'get_states' => 'bm_fetch_states_by_country',
			'bm_filter_fullcalendar_events' => 'bm_filter_fullcalendar_events_callback',
			'bm_filter_timeslot_fullcalendar_events' => 'bm_filter_timeslot_fullcalendar_events_callback',
			'bm_fetch_timeslot_dialog_content' => 'bm_fetch_timeslot_dialog_content',
			'bm_fetch_bookable_services_by_category_id_and_date' => 'bm_fetch_bookable_services_by_category_id_and_date',
			'bm_fetch_new_order_service_time_slots' => 'bm_fetch_new_order_service_time_slots',
			'bm_fetch_mincap_and_cap_left' => 'bm_fetch_mincap_and_cap_left',
			'bm_fetch_service_price_for_backend_order' => 'bm_fetch_service_price_for_add_order',
			'bm_fetch_service_extras_for_backend_order' => 'bm_fetch_service_extras_for_backend_order',
		);

		foreach ( $map as $action => $method ) {
			register_rest_route(
				'sg-booking/v1',
				"/public-action/{$action}",
				array(
					'methods'             => 'POST',
					'callback'            => function ( $request ) use ( $method ) {
						return $this->bridge_ajax_handler( $this->plugin_public, $method, $request );
					},
					'permission_callback' => '__return_true',
				)
			);
		}
	}

	/**
	 * Bridge an existing AJAX handler so it can be called from a REST route.
	 *
	 * Uses output buffering to capture the handler's JSON output and
	 * overrides wp_die to throw instead of terminating the process.
	 *
	 * @since  1.2.0
	 * @access private
	 *
	 * @param  object          $instance The admin or public class instance.
	 * @param  string          $method   The method name to invoke.
	 * @param  WP_REST_Request $request  The incoming REST request.
	 * @return WP_REST_Response
	 */
	private function bridge_ajax_handler( $instance, $method, WP_REST_Request $request ) {
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		ob_start();

		$throw_on_die = function () {
			return function ( $message = '', $title = '', $args = array() ) {
				throw new RuntimeException( 'wp_die_intercepted' );
			};
		};
		add_filter( 'wp_die_ajax_handler', $throw_on_die, 9999 );

		try {
			call_user_func( array( $instance, $method ) );
		} catch ( RuntimeException $e ) {
			// Expected: handler called wp_send_json -> wp_die -> our throw handler.
		}

		$output = ob_get_clean();
		remove_filter( 'wp_die_ajax_handler', $throw_on_die, 9999 );

		$data = json_decode( $output, true );
		if ( is_array( $data ) ) {
			$status = ! empty( $data['success'] ) ? 200 : 400;
			return new WP_REST_Response( $data, $status );
		}

		return new WP_REST_Response( array( 'success' => false, 'data' => 'Unknown error' ), 500 );
	}
}
