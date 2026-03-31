<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://startandgrow.in
 * @since 1.0.0
 *
 * @package    Booking_Management
 * @subpackage Booking_Management/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Booking_Management
 * @subpackage Booking_Management/admin
 * @author     Start and Grow <laravel6@startandgrow.in>
 */
class Booking_Management_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Stores WP_List_Table instances created during load-{page} hooks.
	 *
	 * List tables must be instantiated before Screen Options renders so that
	 * columns are registered via the manage_{screen_id}_columns filter.
	 *
	 * @since  1.3.1
	 * @access private
	 * @var    array
	 */
	private $list_tables = array();


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		if ( is_user_logged_in() ) {
			$screen = get_current_screen();

			// Admin menu Pro badge CSS — always loaded on all admin screens
			// so the sidebar badges render correctly even on non-plugin pages.
			if ( ! wp_style_is( 'bm-menu-pro-badge', 'enqueued' ) ) {
				wp_register_style( 'bm-menu-pro-badge', false, array(), $this->version );
				wp_enqueue_style( 'bm-menu-pro-badge' );
				wp_add_inline_style( 'bm-menu-pro-badge', '#adminmenu .bm-menu-pro-badge { display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; font-size: 9px; font-weight: 600; padding: 2px 6px; border-radius: 100px; text-transform: uppercase; letter-spacing: 0.3px; vertical-align: middle; margin-left: 4px; line-height: 14px; }' );
			}

			// Only load plugin assets on FlexiBooking admin pages.
			if ( ! $this->is_flexi_admin_page( $screen ) ) {
				return;
			}

			wp_enqueue_style( 'jquery-ui-styles' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( 'jquery-ui', plugin_dir_url( __FILE__ ) . 'css/booking-management-jquery-ui.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'jquery-ui-smoothness', plugin_dir_url( __FILE__ ) . 'css/smoothness-jquery-ui.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'font-awesome', plugin_dir_url( __FILE__ ) . 'css/font-awesome.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'googleFonts', plugin_dir_url( __FILE__ ) . 'css/googleFonts.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'material-icon', plugin_dir_url( __FILE__ ) . 'css/material-icons.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'ui-tooltip', plugin_dir_url( __FILE__ ) . 'css/booking-management-tooltip.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'intl-tel-input', plugin_dir_url( __FILE__ ) . 'css/booking-management-intl-tel-input.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'ui-dialog-custom', plugin_dir_url( __FILE__ ) . 'css/booking-management-ui-dialog-custom.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'multiselect', plugin_dir_url( __FILE__ ) . 'css/booking-management-multiselect.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'flexi-animate', plugin_dir_url( __FILE__ ) . 'css/booking-management-animate.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/booking-management-admin.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'flexi-daterangepicker', plugin_dir_url( __FILE__ ) . 'css/booking-management-daterangepicker.css', array(), $this->version, 'all' );
            if ( $screen->base == 'toplevel_page_bm_home' ) {
                wp_enqueue_style( 'dashboard-css', plugin_dir_url( __FILE__ ) . 'css/booking-management-dashboard.css', array(), $this->version, 'all' );
                wp_enqueue_script( 'chartjs', plugin_dir_url( __FILE__ ) . 'js/booking-management-chart.js', array(), $this->version, true );
                wp_enqueue_script( 'bm-dashboard-js', plugin_dir_url( __FILE__ ) . 'js/booking-management-dashboard.js', array( 'jquery', 'chartjs' ), $this->version, true );
            }



			if ( $screen->base == 'admin_page_bm_single_order' ) {
				wp_enqueue_style( 'single-order-css', plugin_dir_url( __FILE__ ) . 'css/booking-management-single-order.css', array(), $this->version, 'all' );
			}

			// Check-ins page styles and scripts.
			if ( isset( $screen->base ) && strpos( $screen->base, 'bm_check_ins' ) !== false ) {
				wp_enqueue_style( 'bm-check-ins-css', plugin_dir_url( __FILE__ ) . 'css/booking-management-check-ins.css', array(), $this->version, 'all' );
				wp_enqueue_script( 'bm-check-ins-js', plugin_dir_url( __FILE__ ) . 'js/booking-management-check-ins.js', array( 'jquery' ), $this->version, true );
			}

			// Upsell page styles (loaded on all FlexiBooking admin pages).
			wp_enqueue_style( 'sg-upsell', plugin_dir_url( __FILE__ ) . 'css/sg-upsell.css', array(), $this->version, 'all' );

			// Form builder styles (loaded on form builder page).
			if ( isset( $screen->base ) && strpos( $screen->base, 'sg-booking-form-builder' ) !== false ) {
				wp_enqueue_style( 'bm-form-builder', plugin_dir_url( __FILE__ ) . 'css/booking-management-form-builder.css', array(), $this->version, 'all' );
			}
		} //end if
	}//end enqueue_styles()

	/**
	 * Check if the current admin screen is a FlexiBooking page.
	 *
	 * Used to conditionally load assets only on plugin pages.
	 *
	 * @since  1.1.0
	 * @param  WP_Screen|null $screen The current screen object.
	 * @return bool True if on a FlexiBooking admin page.
	 */
	private function is_flexi_admin_page( $screen ) {
		if ( ! $screen ) {
			return false;
		}

		// Check if the screen base contains our plugin's page identifiers.
		$flexi_pages = array(
			'toplevel_page_bm_home',
			'flexibooking_page_',
			'admin_page_bm_',
			'admin_page_sg-booking',
		);

		foreach ( $flexi_pages as $page_prefix ) {
			if ( strpos( $screen->base, $page_prefix ) !== false ) {
				return true;
			}
		}

		// Also check the parent_base.
		if ( isset( $screen->parent_base ) && $screen->parent_base === 'bm_home' ) {
			return true;
		}

		return false;
	}//end is_flexi_admin_page()


	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		if ( is_user_logged_in() ) {
			$screen = get_current_screen();

			// Only load plugin scripts on FlexiBooking admin pages.
			if ( ! $this->is_flexi_admin_page( $screen ) ) {
				return;
			}

			$dbhandler   = new BM_DBhandler();
			$bmrequests  = new BM_Request();
			$post_id     = get_the_ID();
			$post        = get_post( $post_id );
			$plugin_path = plugin_dir_url( __FILE__ );

			$age_groups = array(
				'0' => array(
					'name' => esc_html__( 'Infant', 'service-booking' ),
					'type' => 'infant',
					'from' => '0',
					'to'   => '2',
				),
				'1' => array(
					'name' => esc_html__( 'Children', 'service-booking' ),
					'type' => 'children',
					'from' => '3',
					'to'   => '17',
				),
				'2' => array(
					'name' => esc_html__( 'Adult', 'service-booking' ),
					'type' => 'adult',
					'from' => '18',
					'to'   => '40',
				),
				'3' => array(
					'name' => esc_html__( 'Senior', 'service-booking' ),
					'type' => 'senior',
					'from' => '41',
					'to'   => '100',
				),
			);

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_Script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-form' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'media-upload' );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_media();

			wp_enqueue_script( 'jquery-ui', plugin_dir_url( __FILE__ ) . 'js/booking-management-jquery-ui.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'intl-tel-input', plugin_dir_url( __FILE__ ) . 'js/booking-management-intl-tel-input.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'multiselect', plugin_dir_url( __FILE__ ) . 'js/booking-management-multiselect.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'bm-admin-utils', plugin_dir_url( __FILE__ ) . 'js/bm-admin-utils.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( 'bm-admin-categories', plugin_dir_url( __FILE__ ) . 'js/bm-admin-categories.js', array( 'jquery', 'bm-admin-utils' ), $this->version, false );
			wp_enqueue_script( 'bm-admin-customers', plugin_dir_url( __FILE__ ) . 'js/bm-admin-customers.js', array( 'jquery', 'bm-admin-utils' ), $this->version, false );
			wp_enqueue_script( 'bm-admin-services', plugin_dir_url( __FILE__ ) . 'js/bm-admin-services.js', array( 'jquery', 'bm-admin-utils' ), $this->version, false );
			wp_enqueue_script( 'bm-admin-orders', plugin_dir_url( __FILE__ ) . 'js/bm-admin-orders.js', array( 'jquery', 'bm-admin-utils' ), $this->version, false );
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/booking-management-admin.js', array( 'jquery', 'bm-admin-utils', 'bm-admin-categories', 'bm-admin-customers', 'bm-admin-services', 'bm-admin-orders' ), $this->version, false );
			wp_enqueue_script( 'jquery-datepicker-i18n', plugin_dir_url( __FILE__ ) . 'js/booking-management-jquery-datepicker-i18n.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'jquery-moment', plugin_dir_url( __FILE__ ) . 'js/booking-management-momentjs.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'jquery-fullcalendar', plugin_dir_url( __FILE__ ) . 'js/booking-management-jquery-fullcalendar.js', array( 'jquery', 'jquery-moment' ), $this->version, true );
			wp_enqueue_script( 'fullcalendar-moment', plugin_dir_url( __FILE__ ) . 'js/booking-management-fullcalendar-moment.js', array( 'jquery', 'jquery-fullcalendar', 'jquery-moment' ), $this->version, true );
			wp_enqueue_script( 'jquery-daterangepicker', plugin_dir_url( __FILE__ ) . 'js/booking-management-daterangepicker.js', array( 'jquery', 'jquery-fullcalendar', 'fullcalendar-moment', 'jquery-moment' ), $this->version, true );


			$bm_svc_shrt_desc_char_limit = $dbhandler->get_global_option_value( 'bm_svc_shrt_desc_char_limit', 0 );

			$error   = array();
			$success = array();
			$normal  = array();

			$error['required_field']              = __( 'This is a required field.', 'service-booking' );
			$error['required']                    = __( 'Required', 'service-booking' );
			$error['price_required']              = __( 'Price is required.', 'service-booking' );
			$error['price_module_required']       = __( 'Price module is required.', 'service-booking' );
			$error['stopsales_required']          = __( 'Stopsales is required.', 'service-booking' );
			$error['capacity_required']           = __( 'Capacity is required.', 'service-booking' );
			$error['date_required']               = __( 'Date is required.', 'service-booking' );
			$error['from_date_required']          = __( 'From date is required.', 'service-booking' );
			$error['to_date_required']            = __( 'To date is required.', 'service-booking' );
			$error['price_numeric']               = __( 'Must be numeric and > 0.', 'service-booking' );
			$error['set_price']                   = __( 'Please set a deafult price under service details section to set prices in calendar.', 'service-booking' );
			$error['set_stopsales']               = __( 'Please set a deafult stopsales under service details section to set stopsales in calendar.', 'service-booking' );
			$error['set_max_cap']                 = __( 'Please set a default max capacity under service details section to set max capacity in calendar.', 'service-booking' );
			$error['set_time_slot']               = __( 'Please set deafult time slots under service details section to set time slots in calendar.', 'service-booking' );
			$error['server_error']                = __( 'Something is Wrong.', 'service-booking' );
			$error['price_field']                 = __( 'Must be a numeric value with 2 decimal points >= zero.', 'service-booking' );
			$error['numeric_field']               = __( 'This field must have a numeric value greater than zero.', 'service-booking' );
			$error['min_cap_field']               = __( 'Minimum capacity must be lower than or equal to maximum capacity.', 'service-booking' );
			$error['min_length_field']            = __( 'Minimum length must be lower than or equal to maximum length.', 'service-booking' );
			$error['svc_duration_field']          = __( 'Service duration must be lower than or equal to total operating time.', 'service-booking' );
			$error['svc_to_date']                 = __( 'Service to date must be lower than or equal to from date.', 'service-booking' );
			$error['comma_separated_field']       = __( 'Please separate multiple values with a comma(",").', 'service-booking' );
			$error['comma_separated_emails']      = __( 'Please separate valid emails separated by a comma(",").', 'service-booking' );
			$error['bar_separated_field']         = __( 'Please separate multiple values with a bar("|").', 'service-booking' );
			$error['max_time']                    = __( 'Exceeds 24hrs.', 'service-booking' );
			$error['min_cap']                     = __( 'Exceeds default max cap.', 'service-booking' );
			$error['max_cap']                     = __( 'Choose greater than min cap.', 'service-booking' );
			$error['atleast_one_field']           = __( 'Select at least one field.', 'service-booking' );
			$error['field_label_validation']      = __( 'Words separated by spaces only (no spcl chars).', 'service-booking' );
			$error['timezone_error']              = __( 'Could not fetch timezone for selected country.', 'service-booking' );
			$error['products_error']              = __( 'Could not fetch products data.', 'service-booking' );
			$error['customer_error']              = __( 'Could not fetch customer data.', 'service-booking' );
			$error['service_error']               = __( 'Could not fetch service data.', 'service-booking' );
			$error['no_services']                 = __( 'No services found for this category.', 'service-booking' );
			$error['no_bookable_services']        = __( 'No bookable services found.', 'service-booking' );
			$error['no_time_slots']               = __( 'No bookable Time slots found.', 'service-booking' );
			$error['no_slot_capacity']            = __( 'No Capacity available for this slot.', 'service-booking' );
			$error['no_extras']                   = __( 'No bookable extras found.', 'service-booking' );
			$error['only_primary_email_field']    = __( 'Can not uncheck, this is the only primary email field.', 'service-booking' );
			$error['invalid_email']               = __( 'Please enter a valid email.', 'service-booking' );
			$error['invalid_contact']             = __( 'Please enter a valid phone no.', 'service-booking' );
			$error['invalid_url']                 = __( 'Please enter a valid URL.', 'service-booking' );
			$error['invalid_password']            = __( 'Please enter a valid password.', 'service-booking' );
			$error['existing_field_key']          = __( 'This value is taken, choose another one.', 'service-booking' );
			$error['linked_module']               = __( 'Can not delete this module as it is linked with one or more services.', 'service-booking' );
			$error['event_type_value_error']      = __( 'Could not fetch values.', 'service-booking' );
			$error['active_template_type']        = __( 'There is already an active template for this type, please deactivate the existing template.', 'service-booking' );
			$error['active_process_type']         = __( 'There is already an active process for this type, please deactivate the existing process.', 'service-booking' );
			$error['invalid_conditions']          = __( 'Invalid conditions given, please check again.', 'service-booking' );
			$error['wrong_transaction_id']        = __( 'Please double check the transaction id entered.', 'service-booking' );
			$error['transaction_id_not_required'] = __( 'For free orders, transaction id is not applicable.', 'service-booking' );
			$error['wrong_refund_id']             = __( 'Please double check the refund id entered.', 'service-booking' );
			$error['transaction_changes_revert']  = __( 'Transaction changes reverted due to some error.', 'service-booking' );
			$error['transaction_id_exists']       = __( 'Transaction id already exists in a different transaction.', 'service-booking' );
			$error['choose_correct_file_type']    = __( 'One or more files is/are invalid. Please select a valid file (PDF/DOC/DOCX/JPEG/JPG/PNG/GIF/SVG/XLSX/ZIP).', 'service-booking' );
			$error['max_files_to_be_attached']    = __( 'Maximum files can be attached at a time is ', 'service-booking' );
			$error['file_already_exists']         = __( 'The selected file already exists. ', 'service-booking' );
			$error['file_upload_failed']          = __( 'The selected files could not be uploaded. ', 'service-booking' );
			$error['attachments_clearing_failed'] = __( 'The attachments could not be cleared. ', 'service-booking' );
			$error['duplicate_attachment']        = __( 'One or more selected attachment/s is/are duplicate/s. ', 'service-booking' );
			$error['verification_failed']         = __( 'User verification failed. ', 'service-booking' );
			$error['file_size_less_message']      = __( 'File size should not be less than ', 'service-booking' );
			$error['file_size_more_message']      = __( 'File size should not be more than ', 'service-booking' );
			$error['file_width_less_message']     = __( 'File width should not be less than ', 'service-booking' );
			$error['file_width_more_message']     = __( 'File width should not be more than ', 'service-booking' );
			$error['file_height_less_message']    = __( 'File width should not be less than ', 'service-booking' );
			$error['file_height_more_message']    = __( 'File width should not be more than ', 'service-booking' );
			$error['file_type_not_supported']     = __( 'File type is not supported. ', 'service-booking' );
			$error['file_invalid']                = __( 'One of the uploaded files is invalid, try again. ', 'service-booking' );
			$error['invalid_page_numbers']        = __( 'Invalid page numbers entered.', 'service-booking' );
			$error['must_be_greater_than']        = __( 'must be greater than ', 'service-booking' );
			$error['must_be_less_than']           = __( 'must be less than ', 'service-booking' );
			$error['must_be_less_than_field']     = __( 'must be less than next from field', 'service-booking' );
			$error['must_be_greater_than_field']  = __( 'must be greater than last \'to\' field', 'service-booking' );
			$error['transaction_not_editable']    = __( 'This transaction is not editable', 'service-booking' );
			$error['fill_up_age_fields']          = __( 'Please fill up all the required fields to calculate discount.', 'service-booking' );
			$error['invalid_total']               = __( 'Invalid value of total.', 'service-booking' );
			$error['excess_order_total']          = __( 'The total number exceeds the number of people for the ordered service.', 'service-booking' );
			$error['existing_mail']               = __( 'This email is taken, choose a different one.', 'service-booking' );
			$error['coupon_frm_slot_value_error'] = __( '"To" time is required when "From" time is filled.', 'service-booking' );
			$error['coupon_to_slot_value_error']  = __( '"From" time is required when "To" time is filled.', 'service-booking' );
			$error['max_cpn_amt']                 = __( '*Selected amount should be greater than min', 'service-booking' );
			$error['coupon_time_less_error']      = __( 'To time is invalid. Please select greater value', 'service-booking' );
			$error['code_error']                  = __( 'Coupon code must be at least 4 characters.', 'service-booking' );
			$error['restore_failed']              = __( 'Failed to restore order. Please try again.', 'service-booking' );
			$error['failed_export']               = __( 'Failed to export data.', 'service-booking' );
			$error['no_services_text']            = __( 'No services found', 'service-booking' );
			$error['no_qr_code_found']            = __( 'No QR code found in cropped area.', 'service-booking' );
			$error['svc_short_desc_limit']        = sprintf( __( 'Short description cannot exceed %d characters.', 'service-booking' ), $bm_svc_shrt_desc_char_limit );

			$success['price_set']                    = __( 'Price set successfully.', 'service-booking' );
			$success['module_set']                   = __( 'Price module set successfully.', 'service-booking' );
			$success['stopsales_set']                = __( 'Stopsales set successfully.', 'service-booking' );
			$success['saleswitch_set']               = __( 'Saleswitch set successfully.', 'service-booking' );
			$success['capacity_set']                 = __( 'Capacity set successfully.', 'service-booking' );
			$success['time_slot_set']                = __( 'Time slot set successfully.', 'service-booking' );
			$success['save_success']                 = __( 'Saved successfully.', 'service-booking' );
			$success['slot_remove_success']          = __( 'Slot Removed successfully.', 'service-booking' );
			$success['field_remove_success']         = __( 'Field Removed successfully.', 'service-booking' );
			$success['remove_success']               = __( 'Removed successfully.', 'service-booking' );
			$success['order_cancel_success']         = __( 'Order Cancelled successfully.', 'service-booking' );
			$success['order_approve_success']        = __( 'Order Approved successfully.', 'service-booking' );
			$success['mail_send_success']            = __( 'Mail sent successfully.', 'service-booking' );
			$success['transaction_updated']          = __( 'Transaction data updated successfully.', 'service-booking' );
			$success['attachments_clearing_success'] = __( 'The attachments are cleared successfully.', 'service-booking' );
			$success['status_successfully_changed']  = __( 'The status has been changed successfully.', 'service-booking' );
			$success['text_copied']                  = __( 'Text copied successfully.', 'service-booking' );
			$success['checked_in_successfully']      = __( 'Checked in successfully.', 'service-booking' );

            $normal['choose_field']            = __( 'Select a field first', 'service-booking' );
            $normal['are_you_sure']            = __( 'Are You Sure ?', 'service-booking' );
            $normal['sure_remove_condition']   = __( 'Are you sure you want to remove this condition ?', 'service-booking' );
            $normal['sure_remove_attchmnt']    = __( 'Are you sure you want to remove this attachment ?', 'service-booking' );
            $normal['sure_save_transaction']   = __( 'Are you sure you want to save this order transaction ?', 'service-booking' );
            $normal['change_pro_visibility']   = __( "Are you sure you want to change this process's visibilty ?", 'service-booking' );
            $normal['change_svc_visibility']   = __( "Are you sure you want to change this service's visibilty ?", 'service-booking' );
            $normal['change_cat_visibility']   = __( "Are you sure you want to change this category's visibilty ?", 'service-booking' );
            $normal['change_cust_visibility']  = __( "Are you sure you want to change this customer's visibilty ?", 'service-booking' );
            $normal['change_tmpl_visibility']  = __( "Are you sure you want to change this template's visibilty ?", 'service-booking' );
            $normal['change_voucher_vsiblity'] = __( "Are you sure you want to change this voucher's visibilty ?", 'service-booking' );
            $normal['cancel_bor_order']        = __( 'Are you sure you want to cancel this order ? The process can not be reverted once done.', 'service-booking' );
            $normal['approve_bor_order']       = __( 'Are you sure you want to approve this order ? The process can not be reverted once done.', 'service-booking' );
            $normal['sure_remove_service']     = __( 'Are you sure you want to remove this service ? The process can not be reverted once done.', 'service-booking' );
            $normal['confirm_duplicate_service'] = __( 'Duplicate this service?', 'service-booking' );
            $normal['sure_remove_category']    = __( 'Are you sure you want to remove this category ? The process can not be reverted once done.', 'service-booking' );
            $normal['sure_remove_template']    = __( 'Are you sure you want to remove this template ? The process can not be reverted once done.', 'service-booking' );
            $normal['sure_remove_process']     = __( 'Are you sure you want to remove this notification ? The process can not be reverted once done.', 'service-booking' );
            $normal['sure_remove_prce_module'] = __( 'Are you sure you want to remove this price module ? The process can not be reverted once done.', 'service-booking' );
            $normal['sure_remove_order']       = __( 'Are you sure you want to remove this order ? The process can not be reverted once done.', 'service-booking' );
            $normal['sure_remove_timeslot']    = __( 'Are you sure you want to remove this timeslot ? The process can not be reverted once done.', 'service-booking' );
            $normal['sure_remove_field']       = __( 'Are you sure you want to remove this field ? The process can not be reverted once done.', 'service-booking' );
            $normal['sure_remove_option']      = __( 'Are you sure you want to remove this option ? The process can not be reverted once done.', 'service-booking' );
            $normal['remove_svc_unavl_date']   = __( 'Are you sure you want to remove this service unavailable date ? The process can not be reverted once done.', 'service-booking' );
            $normal['remove_extra_product']    = __( 'Are you sure you want to remove this extra service ? The process can not be reverted once done.', 'service-booking' );
            $normal['price_change']            = __( 'This will also reset the calendar prices, Are You Sure ?', 'service-booking' );
            $normal['stopsales_change']        = __( 'This will also reset the calendar stopsales, Are You Sure ?', 'service-booking' );
            $normal['saleswitch_change']       = __( 'This will also reset the calendar saleswitch, Are You Sure ?', 'service-booking' );
            $normal['max_cap_change']          = __( 'This will also reset the calendar capacities, Are You Sure ?', 'service-booking' );
            $normal['timeslot_change']         = __( 'This will also reset the calendar timeslots, Are You Sure ?', 'service-booking' );
            $normal['atleast_one_checked']     = __( 'At least 1 column should be checked.', 'service-booking' );
            $normal['choose_one']              = __( 'Please choose one option.', 'service-booking' );
            $normal['save_it']                 = __( 'Save the changes ?', 'service-booking' );
            $normal['sure_complete_order']     = __( 'Are You Sure you want to change status ? This can not be reverted.', 'service-booking' );
            $normal['sure_change_status']      = __( 'Are You Sure you want to change status ?', 'service-booking' );
            $normal['no_services']             = __( 'No Services Found', 'service-booking' );
            $normal['no_categories']           = __( 'No Categories Found', 'service-booking' );
            $normal['no_price_modules']        = __( 'No Modules Found', 'service-booking' );
            $normal['no_records']              = __( 'No Records Found', 'service-booking' );
            $normal['module_per_age_info']     = __( 'Define prices for different age groups. If this module is linked with that service, these prices will be considered for that service on top of its default price/day specific price. you can diable a group if you don\'t want the price for a specific age group to be considered', 'service-booking' );
            $normal['module_per_group_info']   = __( 'Define prices for different groups. These prices are only for adult and senior age groups and will be considered only if the booked service has persons belonging to these age groups', 'service-booking' );
            $normal['delete']                  = __( 'Delete', 'service-booking' );
            $normal['success']                 = __( 'Success', 'service-booking' );
            $normal['failure']                 = __( 'Failed', 'service-booking' );
            $normal['edit']                    = __( 'Edit', 'service-booking' );
            $normal['type']                    = __( 'Type: ', 'service-booking' );
            $normal['remove']                  = __( 'Remove', 'service-booking' );
            $normal['archive']                 = __( 'Archive', 'service-booking' );
            $normal['restore']                 = __( 'Restore', 'service-booking' );
            $normal['disable']                 = __( 'Disable ?', 'service-booking' );
            $normal['options_selected']        = __( ' options selected', 'service-booking' );
            $normal['selected']                = __( ' Selected', 'service-booking' );
            $normal['choose_option']           = __( 'Select an option', 'service-booking' );
            $normal['filter_service']          = __( 'Service', 'service-booking' );
            $normal['filter_category']         = __( 'Category', 'service-booking' );
            $normal['filter_customer']         = __( 'Customer', 'service-booking' );
            $normal['filter_email']            = __( 'Email', 'service-booking' );
            $normal['choose_order_status']     = __( 'Order statuses', 'service-booking' );
            $normal['choose_payment_status']   = __( 'Payment statuses', 'service-booking' );
            $normal['search_here']             = __( 'Search here', 'service-booking' );
            $normal['approve']                 = __( 'Approve', 'service-booking' );
            $normal['save']                    = __( 'Save', 'service-booking' );
            $normal['cancel']                  = __( 'Cancel', 'service-booking' );
            $normal['backend']                 = __( 'Backend', 'service-booking' );
            $normal['frontend']                = __( 'Frontend', 'service-booking' );
            $normal['previous']                = __( 'Previous', 'service-booking' );
            $normal['next']                    = __( 'Next', 'service-booking' );
            $normal['services_text']           = __( 'Services', 'service-booking' );
            $normal['currency_position']       = $dbhandler->get_global_option_value( 'bm_currency_position', 'before' );
            $normal['currency_symbol']         = $bmrequests->bm_get_currency_char( $dbhandler->get_global_option_value( 'bm_booking_currency', 'EUR' ) );
            $normal['currency_type']           = $dbhandler->get_global_option_value( 'bm_booking_currency', 'EUR' );
            $normal['booking_country']         = $dbhandler->get_global_option_value( 'bm_booking_country', 'IT' );
            $normal['page_slug']               = isset( $post_id ) && isset( $post ) ? $post->post_name : basename( get_permalink() );
            $normal['dashboard_global_search'] = $dbhandler->get_global_option_value( 'bm_backend_dashboard_global_search_field' );
            $normal['current_screen']          = isset( $screen->base ) ? $screen->base : '';
            $normal['insert_value']            = __( 'insert value', 'service-booking' );
            $normal['insert_key']              = __( 'insert key', 'service-booking' );
            $normal['age_price_settings']      = __( 'Age Wise Price Settings', 'service-booking' );
            $normal['cross_sign']              = '✕';
            $normal['age_groups']              = $age_groups;
            $normal['service']                 = __( 'Service', 'service-booking' );
            $normal['category']                = __( 'Category', 'service-booking' );
            $normal['order_status']            = __( 'Order status', 'service-booking' );
            $normal['payment_status']          = __( 'Payment status', 'service-booking' );
            $normal['equal_to']                = __( 'Equal to ', 'service-booking' );
            $normal['not_equal_to']            = __( 'Not equal to', 'service-booking' );
            $normal['at_least_one_condition']  = __( 'You must have at least one condition if you have checked conditions checkbox.', 'service-booking' );
            $normal['edit_transaction']        = __( 'Edit transaction', 'service-booking' );
            $normal['loading_image']           = esc_url( $plugin_path . 'partials/images/ajax-loader.gif' );
            $normal['attachment_image']        = esc_url( $plugin_path . 'partials/images/attach.png' );
            $normal['customer_data']           = __( 'Customer data', 'service-booking' );
            $normal['copy_to_clipboard']       = __( 'Copy to clipboard', 'service-booking' );
            $normal['copied_to_clipboard']     = __( 'Copied to clipboard', 'service-booking' );
            $normal['enter_admin_password']    = __( 'Enter admin password', 'service-booking' );
            $normal['username_email']          = __( 'Enter username/email', 'service-booking' );
            $normal['password']                = __( 'Password', 'service-booking' );
            $normal['enter_admin_credentials'] = __( 'Enter admin credentials', 'service-booking' );
            $normal['first_name']              = __( 'First Name', 'service-booking' );
            $normal['last_name']               = __( 'Last Name', 'service-booking' );
            $normal['email']                   = __( 'Email', 'service-booking' );
            $normal['phone']                   = __( 'Phone', 'service-booking' );
            $normal['city']                    = __( 'City', 'service-booking' );
            $normal['state']                   = __( 'State', 'service-booking' );
            $normal['country']                 = __( 'Country', 'service-booking' );
            $normal['automcomplete']           = __( 'Automcomplete', 'service-booking' );
            $normal['field_label']             = __( 'Field Label', 'service-booking' );
            $normal['field_name_attribute']    = __( 'Field Name Attribute', 'service-booking' );
            $normal['field_description']       = __( 'Field Description', 'service-booking' );
            $normal['placeholder']             = __( 'Placeholder', 'service-booking' );
            $normal['custom_class']            = __( 'Custom Class', 'service-booking' );
            $normal['field_width']             = __( 'Field Width', 'service-booking' );
            $normal['set_as_primary_email']    = __( 'Set as primary email', 'service-booking' );
            $normal['multiple']                = __( 'Multiple', 'service-booking' );
            $normal['editable']                = __( 'Editable', 'service-booking' );
            $normal['visible']                 = __( 'Visible', 'service-booking' );
            $normal['default_options']         = __( 'Default Options', 'service-booking' );
            $normal['add_option']              = __( 'Add Option', 'service-booking' );
            $normal['name']                    = __( 'name', 'service-booking' );
            $normal['from']                    = __( 'from', 'service-booking' );
            $normal['to']                      = __( 'to', 'service-booking' );
            $normal['price']                   = __( 'price', 'service-booking' );
            $normal['select_persons']          = __( 'select no of persons', 'service-booking' );
            $normal['total_price']             = __( 'Total Price', 'service-booking' );
            $normal['in']                      = __( 'in', 'service-booking' );
            $normal['select_service']          = __( 'Select Service', 'service-booking' );
            $normal['select_slot']             = __( 'select slot', 'service-booking' );
            $normal['admin_username']          = __( 'admin username', 'service-booking' );
            $normal['admin_password']          = __( 'admin password', 'service-booking' );
            $normal['minimum_capacity']        = __( 'minimum capacity', 'service-booking' );
            $normal['maximum_capacity']        = __( 'maximum capacity', 'service-booking' );
            $normal['minimum_length']          = __( 'Minimum Length', 'service-booking' );
            $normal['maximum_length']          = __( 'Maximum Length', 'service-booking' );
            $normal['rows']                    = __( 'Rows', 'service-booking' );
            $normal['columns']                 = __( 'Columns', 'service-booking' );
            $normal['field_key']               = __( 'Field Key', 'service-booking' );
            $normal['show_intl_codes']         = __( 'Show International codes', 'service-booking' );
            $normal['link_woo_field']          = __( 'Link with WooCommerce Field', 'service-booking' );
            $normal['default_value']           = __( 'Default Value', 'service-booking' );
            $normal['field']                   = __( 'Field', 'service-booking' );
            $normal['settings']                = __( 'Settings', 'service-booking' );
            $normal['add']                     = __( 'Add', 'service-booking' );
            $normal['quantity']                = __( 'Quantity', 'service-booking' );
            $normal['cap_left']                = __( 'Cap Left', 'service-booking' );
            $normal['username']                = __( 'Username', 'service-booking' );
            $normal['selected']                = __( 'Selected', 'service-booking' );
            $normal['billing_first_name']      = __( 'Billing First Name', 'service-booking' );
            $normal['billing_last_name']       = __( 'Billing Last Name', 'service-booking' );
            $normal['billing_company']         = __( 'Billing Company', 'service-booking' );
            $normal['billing_country']         = __( 'Billing Country', 'service-booking' );
            $normal['billing_address']         = __( 'Billing Address', 'service-booking' );
            $normal['billing_address_1']       = __( 'Billing Address 1', 'service-booking' );
            $normal['billing_address_2']       = __( 'Billing Address 2', 'service-booking' );
            $normal['billing_city']            = __( 'Billing City', 'service-booking' );
            $normal['billing_state']           = __( 'Billing State', 'service-booking' );
            $normal['billing_postcode']        = __( 'Billing Postcode', 'service-booking' );
            $normal['billing_phone']           = __( 'Billing Phone', 'service-booking' );
            $normal['billing_email']           = __( 'Billing Email', 'service-booking' );
            $normal['shipping_first_name']     = __( 'Shipping First Name', 'service-booking' );
            $normal['shipping_last_name']      = __( 'Shipping Last Name', 'service-booking' );
            $normal['shipping_company']        = __( 'Shipping Company', 'service-booking' );
            $normal['shipping_address']        = __( 'Shipping Address', 'service-booking' );
            $normal['shipping_address_1']      = __( 'Shipping Address 1', 'service-booking' );
            $normal['shipping_address_2']      = __( 'Shipping Address 2', 'service-booking' );
            $normal['shipping_city']           = __( 'Shipping City', 'service-booking' );
            $normal['shipping_state']          = __( 'Shipping State', 'service-booking' );
            $normal['shipping_postcode']       = __( 'Shipping Postcode', 'service-booking' );
            $normal['order_comments']          = __( 'Order Comments', 'service-booking' );
            $normal['non_woocomerce']          = __( 'Non WooCommerce Field', 'service-booking' );
            $normal['order_details']           = __( 'Order Details', 'service-booking' );
            $normal['customer_details']        = __( 'Customer Details', 'service-booking' );
            $normal['order_details_pdf']       = __( 'Order details pdf', 'service-booking' );
            $normal['order_ticket_pdf']        = __( 'Order ticket pdf', 'service-booking' );
            $normal['customer_details_pdf']    = __( 'Customer details pdf', 'service-booking' );
            $normal['no_attachments']          = __( 'No attachments found', 'service-booking' );
            $normal['no_price_module_date']    = __( 'No discountable price modules found', 'service-booking' );
            $normal['pay']                     = __( 'Pay  ', 'service-booking' );
            $normal['free_book']               = __( 'Free Booking', 'service-booking' );
            $normal['service_discount_text']   = __( 'Service discount is ', 'service-booking' );
            $normal['bookings']                = __( 'Bookings', 'service-booking' );
            $normal['no_data_to_show']         = __( 'No data to show', 'service-booking' );
            $normal['sure_remove_coupon']      = __( 'Are you sure you want to remove this Coupon ? The process can not be reverted once done.', 'service-booking' );
            $normal['sure_remove_restriction'] = __( 'Are you sure you want to remove this restriction ?', 'service-booking' );
            $normal['remove_cpn_unavl_date']   = __( 'Are you sure you want to remove this Date ?', 'service-booking' );
            $normal['remove_cpn_event_date']   = __( 'Are you sure you want to remove this event date?', 'service-booking' );
            $normal['enter_reference_key']     = __( 'Please enter the booking reference key', 'service-booking' );
            $normal['resend_ticket_mail']      = __( 'Resend ticket mail', 'service-booking' );
            $normal['sure_archive_order']      = __( 'Are you sure you want to archive this order? It can be restored later.', 'service-booking' );
            $normal['sure_restore_order']      = __( 'Are you sure you want to restore this archived order?', 'service-booking' );
            $normal['order_restored']          = __( 'Order has been successfully restored.', 'service-booking' );
            $normal['order_archived']          = __( 'Order has been successfully archived.', 'service-booking' );
            $normal['reservation_list']        = __( 'Reservation List', 'service-booking' );
            $normal['booking']                 = __( 'Booking', 'service-booking' );
            $normal['show_more']               = __( 'Show More', 'service-booking' );
            $normal['more_info']               = __( 'More Info', 'service-booking' );
            $normal['total']                   = __( 'Total', 'service-booking' );
            $normal['payment_method']          = __( 'Payment Method', 'service-booking' );
            $normal['transaction_id']          = __( 'Transaction ID', 'service-booking' );
            $normal['amount']                  = __( 'Amount', 'service-booking' );
            $normal['payment_status']          = __( 'Payment Status', 'service-booking' );
            $normal['payment_date']            = __( 'Payment Date', 'service-booking' );
            $normal['additional_information']  = __( 'Additional Information', 'service-booking' );
            $normal['billing_information']     = __( 'Billing Information', 'service-booking' );
            $normal['subject']                 = __( 'Subject', 'service-booking' );
            $normal['date_sent']               = __( 'Date Sent', 'service-booking' );
            $normal['recipient']               = __( 'Recipient', 'service-booking' );
            $normal['view_mail']               = __( 'View Mail', 'service-booking' );
            $normal['product']                 = __( 'Product', 'service-booking' );
            $normal['total_quantity']          = __( 'Total Quantity', 'service-booking' );
            $normal['revenue']                 = __( 'Revenue', 'service-booking' );
            $normal['loading']                 = __( 'Loading', 'service-booking' );
            $normal['NotAllowedError']         = __( 'Camera access denied. Please allow camera access in your browser settings.', 'service-booking' );
            $normal['NotFoundError']           = __( 'No camera found. Please check if your device has a camera.', 'service-booking' );
            $normal['NotReadableError']        = __( 'Camera is already in use by another application.', 'service-booking' );
            $normal['OverconstrainedError']    = __( 'Camera does not support the required constraints.', 'service-booking' );
            $normal['SecurityError']           = __( 'Camera access is blocked by browser security settings.', 'service-booking' );
            $normal['enter_email']             = __( 'Please enter email', 'service-booking' );
            $normal['enter_last_name']         = __( 'Please enter last name', 'service-booking' );
            $normal['enter_reference_no']      = __( 'Please enter booking reference number', 'service-booking' );
            $normal['select_a_service']        = __( 'Select a service', 'service-booking' );
            $normal['qr_code_detected']        = __( 'QR Code Detected', 'service-booking' );
            $normal['select_all']              = __( 'Select All.', 'service-booking' );
            $normal['no_mails_sent']           = __( 'No emails have been sent for this order.', 'service-booking' );
            $normal['resend_regenerate_mail']  = __( 'Resend Email & Regenerate Ticket.', 'service-booking' );
            $normal['failed_mail_load']        = __( 'Failed to load email information.', 'service-booking' );
            $normal['error_mail_load']         = __( 'Error loading email information.', 'service-booking' );
            $normal['email_sent_success']      = __( 'Email sent successfully!', 'service-booking' );

            $normal['image_min_size']     = $dbhandler->get_global_option_value( 'bm_minimum_image_size', 0 );
            $normal['image_max_size']     = $dbhandler->get_global_option_value( 'bm_maximum_image_size', 0 );
            $normal['image_min_width']    = $dbhandler->get_global_option_value( 'bm_minimum_image_width', 0 );
            $normal['image_max_width']    = $dbhandler->get_global_option_value( 'bm_maximum_image_width', 0 );
            $normal['image_min_height']   = $dbhandler->get_global_option_value( 'bm_minimum_image_height', 0 );
            $normal['image_max_height']   = $dbhandler->get_global_option_value( 'bm_maximum_image_height', 0 );
            $normal['image_quality']      = $dbhandler->get_global_option_value( 'bm_image_quality', 0 );
            $normal['price_format']       = $dbhandler->get_global_option_value( 'bm_flexi_service_price_format', 'de_DE' );
            $normal['choose_image']       = __( 'Choose Image', 'service-booking' );
            $normal['current_language']   = isset( $_COOKIE['bm_flexibooking_language'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['bm_flexibooking_language'] ) ) : esc_html( 'en' );
            $normal['svc_shrt_dsc_lmt']   = $bm_svc_shrt_desc_char_limit;
            $normal['svc_info_svg_icon']  = esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'admin/img/si_info-line.svg' );
            $primary_color                = $bmrequests->bm_get_theme_color( 'primary' ) ?? '#000000';
            $contrast                     = $bmrequests->bm_get_theme_color( 'contrast' ) ?? '#ffffff';
            $normal['svc_button_colour']  = $dbhandler->get_global_option_value( 'bm_frontend_book_button_color', $primary_color );
            $normal['svc_btn_txt_colour'] = $dbhandler->get_global_option_value( 'bm_frontend_book_button_txt_color', $contrast );
            $normal['svc_info_svg_icon']  = esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'public/img/si_info-line.svg' );
            $normal['admin_side_link']    = admin_url( 'admin.php?' );

			wp_localize_script( $this->plugin_name, 'bm_error_object', $error );
			wp_localize_script( $this->plugin_name, 'bm_success_object', $success );
			wp_localize_script( $this->plugin_name, 'bm_normal_object', $normal );

			



			if ( $screen->base == 'admin_page_bm_single_order' ) {
				wp_enqueue_script( 'single-order-js', plugin_dir_url( __FILE__ ) . 'js/booking-management-single-order.js', array( 'jquery' ), $this->version, false );
			}

			wp_localize_script(
				$this->plugin_name,
				'bm_intl_script',
				array(
					'script_url' => plugin_dir_url( __FILE__ ) . 'js/booking-management-intl-tel-input.js',
				)
			);

			wp_localize_script(
				$this->plugin_name,
				'bm_ajax_object',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'ajax-nonce' ),
					'rest_url' => esc_url_raw( rest_url( 'sg-booking/v1/' ) ),
					'rest_nonce' => wp_create_nonce( 'wp_rest' ),
				)
			);

			// Form builder script (loaded on form builder page only).
			if ( isset( $screen->base ) && strpos( $screen->base, 'sg-booking-form-builder' ) !== false ) {
				wp_enqueue_script( 'bm-form-builder', plugin_dir_url( __FILE__ ) . 'js/booking-management-form-builder.js', array( 'jquery', 'jquery-ui-sortable' ), $this->version, true );
				wp_localize_script(
					'bm-form-builder',
					'bmFbI18n',
					array(
						'label'         => __( 'Field Label', 'service-booking' ),
						'type'          => __( 'Field Type', 'service-booking' ),
						'description'   => __( 'Description', 'service-booking' ),
						'placeholder'   => __( 'Placeholder', 'service-booking' ),
						'default_value' => __( 'Default Value', 'service-booking' ),
						'css_class'     => __( 'CSS Class', 'service-booking' ),
						'css_class_desc' => __( 'Optional extra class(es) for styling.', 'service-booking' ),
						'field_width'   => __( 'Field Width', 'service-booking' ),
						'full_width'    => __( 'Full Width', 'service-booking' ),
						'half_width'    => __( 'Half Width', 'service-booking' ),
						'required'      => __( 'Required', 'service-booking' ),
						'visible'       => __( 'Visible', 'service-booking' ),
						'save_field'    => __( 'Save Field', 'service-booking' ),
						'saving'        => __( 'Saving…', 'service-booking' ),
						'field_saved'   => __( 'Field saved successfully.', 'service-booking' ),
						'save_error'    => __( 'Could not save field.', 'service-booking' ),
						'network_error' => __( 'Network error. Please try again.', 'service-booking' ),
						'loading'       => __( 'Loading…', 'service-booking' ),
						'preview_error' => __( 'Could not load preview.', 'service-booking' ),
						'field_added'   => __( 'Field added successfully.', 'service-booking' ),
						'field_removed' => __( 'Field removed successfully.', 'service-booking' ),
						'confirm_remove' => __( 'Are you sure you want to remove this field?', 'service-booking' ),
						'template_applied' => __( 'Template applied successfully.', 'service-booking' ),
						'conditional_logic' => __( 'Conditional Logic', 'service-booking' ),
						'show_field_when'   => __( 'Show this field when', 'service-booking' ),
						'select_field'      => __( 'Select a field', 'service-booking' ),
						'is_equal_to'       => __( 'is equal to', 'service-booking' ),
						'is_not_equal_to'   => __( 'is not equal to', 'service-booking' ),
						'is_empty'          => __( 'is empty', 'service-booking' ),
						'is_not_empty'      => __( 'is not empty', 'service-booking' ),
						'enter_value'       => __( 'Enter value', 'service-booking' ),
						'validation_rules'  => __( 'Validation Rules', 'service-booking' ),
						'min_length'        => __( 'Minimum Length', 'service-booking' ),
						'max_length'        => __( 'Maximum Length', 'service-booking' ),
						'pattern'           => __( 'Pattern (Regex)', 'service-booking' ),
						'custom_error'      => __( 'Custom Error Message', 'service-booking' ),
						'consent_text'      => __( 'Consent Text', 'service-booking' ),
						'gdpr_default_text' => __( 'I consent to the storage and processing of my personal data.', 'service-booking' ),
						'pro_only'          => __( 'Pro', 'service-booking' ),
						'upgrade_to_pro'    => __( 'Upgrade to Pro', 'service-booking' ),
					)
				);

				// Template data (passed via localize to avoid inline script XSS).
				wp_localize_script(
					'bm-form-builder',
					'bmFbTemplatesData',
					array(
						'contact' => array(
							'name'   => __( 'Contact Form', 'service-booking' ),
							'fields' => array(
								array( 'type' => 'text',     'label' => __( 'Full Name', 'service-booking' ),  'required' => 1, 'width' => 'full' ),
								array( 'type' => 'email',    'label' => __( 'Email Address', 'service-booking' ), 'required' => 1, 'width' => 'half' ),
								array( 'type' => 'tel',      'label' => __( 'Phone Number', 'service-booking' ), 'required' => 0, 'width' => 'half' ),
								array( 'type' => 'textarea', 'label' => __( 'Message', 'service-booking' ),     'required' => 1, 'width' => 'full' ),
								array( 'type' => 'gdpr_consent', 'label' => __( 'Privacy Consent', 'service-booking' ), 'required' => 1, 'width' => 'full' ),
							),
						),
						'booking' => array(
							'name'   => __( 'Booking Form', 'service-booking' ),
							'fields' => array(
								array( 'type' => 'text',     'label' => __( 'First Name', 'service-booking' ),  'required' => 1, 'width' => 'half' ),
								array( 'type' => 'text',     'label' => __( 'Last Name', 'service-booking' ),   'required' => 1, 'width' => 'half' ),
								array( 'type' => 'email',    'label' => __( 'Email', 'service-booking' ),        'required' => 1, 'width' => 'half' ),
								array( 'type' => 'tel',      'label' => __( 'Phone', 'service-booking' ),        'required' => 0, 'width' => 'half' ),
								array( 'type' => 'date',     'label' => __( 'Preferred Date', 'service-booking' ), 'required' => 1, 'width' => 'half' ),
								array( 'type' => 'time',     'label' => __( 'Preferred Time', 'service-booking' ), 'required' => 0, 'width' => 'half' ),
								array( 'type' => 'textarea', 'label' => __( 'Additional Notes', 'service-booking' ), 'required' => 0, 'width' => 'full' ),
								array( 'type' => 'gdpr_consent', 'label' => __( 'GDPR Consent', 'service-booking' ), 'required' => 1, 'width' => 'full' ),
							),
						),
					)
				);
			}
		} //end if
	}//end enqueue_scripts()


    public function booking_admin_menu() {

		// Main menu page — Dashboard.
		add_menu_page( __( 'FlexiBooking', 'service-booking' ), __( 'FlexiBooking', 'service-booking' ), 'manage_options', 'bm_home', array( $this, 'bm_home' ), 'dashicons-groups', 26 );

		// Dashboard.
		add_submenu_page( 'bm_home', __( 'Booking Dashboard', 'service-booking' ), __( 'Dashboard', 'service-booking' ), 'manage_options', 'bm_home', array( $this, 'bm_home' ) );

		// Analytics: Pro-only.
		add_submenu_page( 'bm_home', __( 'Analytics', 'service-booking' ), __( 'Analytics', 'service-booking' ) . ' <span class="bm-menu-pro-badge">Pro</span>', 'manage_options', 'bm_booking_analytics', array( $this, 'bm_pro_upsell_page' ) );

		// --- FREE menus (always available) ---
		$hook_orders = add_submenu_page( 'bm_home', __( 'Orders', 'service-booking' ), __( 'Orders', 'service-booking' ), 'manage_options', 'bm_all_orders', array( $this, 'bm_all_orders' ) );
		add_submenu_page( '', __( 'Add Order', 'service-booking' ), __( 'Add Order', 'service-booking' ), 'manage_options', 'bm_add_order', array( $this, 'bm_add_order' ) );

		// Service Booking Planner: Pro-only.
		add_submenu_page( 'bm_home', __( 'Service Booking Planner', 'service-booking' ), __( 'Service Booking Planner', 'service-booking' ) . ' <span class="bm-menu-pro-badge">Pro</span>', 'manage_options', 'bm_service_booking_planner', array( $this, 'bm_pro_upsell_page' ) );

		add_submenu_page( '', __( 'Single Order Page', 'service-booking' ), __( 'Single Order Page', 'service-booking' ), 'manage_options', 'bm_single_order', array( $this, 'bm_single_order' ) );

		// Single Service Booking Planner: Pro-only.
		add_submenu_page( 'bm_home', __( 'Single Service Booking Planner', 'service-booking' ), __( 'Single Service Booking Planner', 'service-booking' ) . ' <span class="bm-menu-pro-badge">Pro</span>', 'manage_options', 'bm_single_service_booking_planner', array( $this, 'bm_pro_upsell_page' ) );

		// Customers: Available in free (email-only) and pro (full management).
		$hook_customers = add_submenu_page( 'bm_home', __( 'Customers', 'service-booking' ), __( 'Customers', 'service-booking' ), 'manage_options', 'bm_all_customers', array( $this, 'bm_all_customers' ) );

		$hook_services = add_submenu_page( 'bm_home', __( 'Services', 'service-booking' ), __( 'Services', 'service-booking' ), 'manage_options', 'bm_all_services', array( $this, 'bm_all_services' ) );
		add_submenu_page( '', __( 'Add Service', 'service-booking' ), __( 'Add Service', 'service-booking' ), 'manage_options', 'bm_add_service', array( $this, 'bm_add_service' ) );
		$hook_shared_extras = add_submenu_page( 'bm_home', __( 'Shared Extras', 'service-booking' ), __( 'Shared Extras', 'service-booking' ), 'manage_options', 'bm_shared_extras', array( $this, 'bm_shared_extras' ) );
		$hook_categories = add_submenu_page( 'bm_home', __( 'Categories', 'service-booking' ), __( 'Categories', 'service-booking' ), 'manage_options', 'bm_all_categories', array( $this, 'bm_all_categories' ) );
		add_submenu_page( '', __( 'Add Category', 'service-booking' ), __( 'Add Category', 'service-booking' ), 'manage_options', 'bm_add_category', array( $this, 'bm_add_category' ) );

		// --- Pro-only menus (locked with Pro badge; unlocked by Pro add-on) ---

		// Customer Profile: Pro-only (hidden page).
		add_submenu_page( '', __( 'Customer Profile', 'service-booking' ), __( 'Customer Profile', 'service-booking' ), 'manage_options', 'bm_customer_profile', array( $this, 'bm_pro_upsell_page' ) );

		// Mail Templates: Available in free (with limits) and pro (full).
		$hook_templates = add_submenu_page( 'bm_home', __( 'Mail Templates', 'service-booking' ), __( 'Mail Templates', 'service-booking' ), 'manage_options', 'bm_email_templates', array( $this, 'bm_email_templates' ) );
		add_submenu_page( '', __( 'Add Template', 'service-booking' ), __( 'Add Template', 'service-booking' ), 'manage_options', 'bm_add_template', array( $this, 'bm_add_template' ) );

		// Booking Forms: Available in free (default billing form only) and pro (advanced).
		$hook_forms = add_submenu_page( 'bm_home', __( 'Booking Forms', 'service-booking' ), __( 'Booking Forms', 'service-booking' ), 'manage_options', 'sg-booking-forms', array( $this, 'bm_fields' ) );
		add_submenu_page( '', __( 'Form Builder', 'service-booking' ), __( 'Form Builder', 'service-booking' ), 'manage_options', 'sg-booking-form-builder', array( $this, 'bm_form_builder' ) );

		// Price Modules: Pro-only.
		add_submenu_page( 'bm_home', __( 'Price Modules', 'service-booking' ), __( 'Price Modules', 'service-booking' ) . ' <span class="bm-menu-pro-badge">Pro</span>', 'manage_options', 'bm_all_external_service_prices', array( $this, 'bm_pro_upsell_page' ) );
		add_submenu_page( '', __( 'Add Price Module', 'service-booking' ), __( 'Add Price Module', 'service-booking' ), 'manage_options', 'bm_add_external_service_price', array( $this, 'bm_pro_upsell_page' ) );

		// Email Records: Available in free (read-only listing) and pro (full with resend).
		$hook_email_records = add_submenu_page( 'bm_home', __( 'Email Records', 'service-booking' ), __( 'Email Records', 'service-booking' ), 'manage_options', 'bm_email_records', array( $this, 'bm_email_records' ) );

		// Vouchers: Available in free (listing only) and pro (full management + redemption).
		$hook_vouchers = add_submenu_page( 'bm_home', __( 'Vouchers', 'service-booking' ), __( 'Vouchers', 'service-booking' ), 'manage_options', 'bm_voucher_records', array( $this, 'bm_voucher_records' ) );

		// Check ins: Available in free (manual only) and pro (scanner + resend).
		$hook_checkins = add_submenu_page( 'bm_home', __( 'Check ins', 'service-booking' ), __( 'Check ins', 'service-booking' ), 'manage_options', 'bm_check_ins', array( $this, 'bm_check_ins' ) );

		// PDF Customization: Pro-only (free gets default non-customizable templates).
		add_submenu_page( 'bm_home', __( 'PDF Templates', 'service-booking' ), __( 'PDF Templates', 'service-booking' ), 'manage_options', 'bm_pdf_customization', array( $this, 'bm_pdf_templates_page' ) );

		// Email Logs: Pro-only.
		add_submenu_page( 'bm_home', __( 'Email Logs', 'service-booking' ), __( 'Email Logs', 'service-booking' ) . ' <span class="bm-menu-pro-badge">Pro</span>', 'manage_options', 'bm_email_logs', array( $this, 'bm_pro_upsell_page' ) );

		// Payment Logs: Pro-only.
		add_submenu_page( 'bm_home', __( 'Payment Logs', 'service-booking' ), __( 'Payment Logs', 'service-booking' ) . ' <span class="bm-menu-pro-badge">Pro</span>', 'manage_options', 'bm_payment_logs', array( $this, 'bm_pro_upsell_page' ) );

		// Coupons: Pro-only.
		add_submenu_page( 'bm_home', __( 'Coupons', 'service-booking' ), __( 'Coupons', 'service-booking' ) . ' <span class="bm-menu-pro-badge">Pro</span>', 'manage_options', 'bm_all_coupons', array( $this, 'bm_pro_upsell_page' ) );

		// --- Global Settings ---
		add_submenu_page( 'bm_home', __( 'Global Settings', 'service-booking' ), __( 'Global Settings', 'service-booking' ), 'manage_options', 'bm_global', array( $this, 'bm_global' ) );
		add_submenu_page( '', __( 'Global General Settings', 'service-booking' ), __( 'Global General Settings', 'service-booking' ), 'manage_options', 'bm_global_general_settings', array( $this, 'bm_global_general_settings' ) );
		add_submenu_page( '', __( 'Service and Booking Settings', 'service-booking' ), __( 'Service and Booking Settings', 'service-booking' ), 'manage_options', 'bm_svc_booking_settings', array( $this, 'bm_svc_booking_settings' ) );
		add_submenu_page( '', __( 'CSS Settings', 'service-booking' ), __( 'CSS Settings', 'service-booking' ), 'manage_options', 'bm_global_css_settings', array( $this, 'bm_global_css_settings' ) );
		add_submenu_page( '', __( 'Timezone And Country Settings', 'service-booking' ), __( 'Timezone And Country Settings', 'service-booking' ), 'manage_options', 'bm_global_timezone_country_settings', array( $this, 'bm_global_timezone_country_settings' ) );
		add_submenu_page( '', __( 'Language Settings', 'service-booking' ), __( 'Language Settings', 'service-booking' ), 'manage_options', 'bm_global_language_settings', array( $this, 'bm_global_language_settings' ) );
		add_submenu_page( '', __( 'Format Settings', 'service-booking' ), __( 'Format Settings', 'service-booking' ), 'manage_options', 'bm_global_format_settings', array( $this, 'bm_global_format_settings' ) );

		// Mail settings available in free (basic, no SMTP).
		add_submenu_page( '', __( 'Global Email Settings', 'service-booking' ), __( 'Global Email Settings', 'service-booking' ), 'manage_options', 'bm_global_email_settings', array( $this, 'bm_global_email_settings' ) );
		add_submenu_page( '', __( 'Upload Settings', 'service-booking' ), __( 'Upload Settings', 'service-booking' ), 'manage_options', 'bm_upload_settings', array( $this, 'bm_upload_settings' ) );

		// Pro-only settings pages.
		add_submenu_page( '', __( 'Global Payment Settings', 'service-booking' ), __( 'Global Payment Settings', 'service-booking' ), 'manage_options', 'bm_global_payment_settings', array( $this, 'bm_pro_upsell_page' ) );
		add_submenu_page( '', __( 'Integration Settings', 'service-booking' ), __( 'Integration Settings', 'service-booking' ), 'manage_options', 'bm_global_integration_settings', array( $this, 'bm_pro_upsell_page' ) );
		add_submenu_page( '', __( 'Coupon Settings', 'service-booking' ), __( 'Coupon Settings', 'service-booking' ), 'manage_options', 'bm_global_coupon_settings', array( $this, 'bm_pro_upsell_page' ) );

		/**
		 * Fires after the Lite plugin has registered all admin menus.
		 *
		 * The Pro add-on hooks here to replace locked upsell menu
		 * callbacks with real Pro page callbacks, and to register
		 * any additional Pro-only admin menus.
		 *
		 * @since 1.1.0
		 */
		do_action( 'sg_booking_register_pro_menus' );

		// Add Screen Options (per_page) to listing pages.
		$screen_option_hooks = array(
			$hook_orders,
			$hook_customers,
			$hook_services,
			$hook_categories,
			$hook_templates,
			$hook_forms,
			$hook_email_records,
			$hook_vouchers,
			$hook_checkins,
			$hook_shared_extras,
		);
		foreach ( $screen_option_hooks as $hook ) {
			if ( $hook ) {
				add_action( "load-{$hook}", array( $this, 'bm_add_screen_options' ) );
			}
		}
	} //end booking_admin_menu()

	/**
	 * Universal Pro upsell page callback.
	 *
	 * Displays a beautifully styled upsell page when a free user
	 * navigates to a Pro-only menu item.
	 */
	public function bm_pro_upsell_page() {
		include plugin_dir_path( __FILE__ ) . 'partials/upsells/pro-upsell.php';
	} //end bm_pro_upsell_page()


	public function bm_home() {
		include 'partials/booking-management-dashboard.php';
	}//end bm_home()

    // Display analytics page
    public function bm_booking_analytics() {
        $this->bm_pro_upsell_page();
    }

	/**
	 * PDF Templates page for free version — non-customizable defaults.
	 */
	public function bm_pdf_templates_page() {
		include 'partials/booking-management-pdf-templates-free.php';
	}


	public function bm_all_orders() {
		include 'partials/booking-management-order-listing.php';
	}//end bm_all_orders()



	public function bm_all_customers() {
		include 'partials/booking-management-customer-listing.php';
	}//end bm_all_customers()


	public function bm_all_services() {
		include 'partials/booking-management-service-listing.php';
	}


	/**
	 * Add Order page — Pro-only feature in the free version.
	 * Shows the Pro upsell page. The public-side booking form (shortcode) remains available.
	 */
	public function bm_add_order() {
		$this->bm_pro_upsell_page();
	}//end bm_add_order()


	public function bm_single_order() {
		include 'partials/booking-management-single-order.php';
	}//end bm_single_order()


	public function bm_service_booking_planner() {
		$this->bm_pro_upsell_page();
	}//end bm_service_booking_planner()


	public function bm_single_service_booking_planner() {
		$this->bm_pro_upsell_page();
	}//end bm_single_service_booking_planner()


	public function bm_add_customer() {
		$this->bm_pro_upsell_page();
	}//end bm_add_customer()


	public function bm_customer_profile() {
		$this->bm_pro_upsell_page();
	}//end bm_customer_profile()


	public function bm_add_service() {
		include 'partials/booking-management-add-service.php';
	}


	public function bm_all_external_service_prices() {
		$this->bm_pro_upsell_page();
	}//end bm_all_external_service_prices()


	public function bm_add_external_service_price() {
		$this->bm_pro_upsell_page();
	}//end bm_add_external_service_price()


	public function bm_all_categories() {
		include 'partials/booking-management-category-listing.php';
	}//end bm_all_categories()


	public function bm_shared_extras() {
		include 'partials/booking-management-shared-extras.php';
	}//end bm_shared_extras()


	public function bm_add_category() {
		include 'partials/booking-management-add-category.php';
	}//end bm_add_category()


	public function bm_email_records() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/booking-management-email-records.php';
	}//end bm_email_records()


	public function bm_voucher_records() {
		include plugin_dir_path( __FILE__ ) . 'partials/booking-management-voucher-records.php';
	}//end bm_voucher_records()


	public function bm_check_ins() {
		include 'partials/booking-management-check_ins.php';
	} //end bm_check_ins()


	public function bm_email_logs() {
		$this->bm_pro_upsell_page();
	} //end bm_email_logs()


	public function bm_payment_logs() {
		$this->bm_pro_upsell_page();
	} //end bm_payment_logs()


	public function bm_global() {
		include 'partials/booking-management-global-settings.php';
	}//end bm_global()


	public function bm_global_general_settings() {
		include 'partials/booking-management-global-general-settings.php';
	}//end bm_global_general_settings()


	public function bm_global_email_settings() {
		$this->bm_pro_upsell_page();
	}//end bm_global_email_settings()


	public function bm_global_payment_settings() {
		$this->bm_pro_upsell_page();
	}//end bm_global_payment_settings()


	public function bm_svc_booking_settings() {
		$this->bm_pro_upsell_page();
	}//end bm_svc_booking_settings()


	public function bm_global_css_settings() {
		$this->bm_pro_upsell_page();
	}//end bm_global_css_settings()


	public function bm_global_timezone_country_settings() {
		include 'partials/booking-management-global-timezone-country-settings.php';
	}//end bm_global_timezone_country_settings()


	public function bm_pagination_settings() {
		wp_safe_redirect( admin_url( 'admin.php?page=bm_global' ) );
		exit;
	}//end bm_pagination_settings()


	public function bm_upload_settings() {
		$this->bm_pro_upsell_page();
	} //end bm_upload_settings()


	public function bm_global_language_settings() {
		include 'partials/booking-management-global-language-settings.php';
	}//end bm_global_language_settings()


	public function bm_global_format_settings() {
		$this->bm_pro_upsell_page();
	}//end bm_global_format_settings()


	public function bm_global_integration_settings() {
		$this->bm_pro_upsell_page();
	}//end bm_global_integration_settings()

	public function bm_global_coupon_settings() {
		$this->bm_pro_upsell_page();
	}//end bm_global_coupon_settings()


	public function bm_fields() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/booking-management-form-listing.php';
	}//end bm_fields()

	public function bm_form_builder() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/booking-management-form-builder.php';
	}//end bm_form_builder()

	public function bm_email_templates() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/booking-management-email-template-listing.php';
	}//end bm_email_templates()


	public function bm_add_template() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/booking-management-add-email-template.php';
	}//end bm_add_template()

	public function bm_all_coupons() {
		$this->bm_pro_upsell_page();
	} //end bm_all_coupons

	public function bm_add_coupon() {
		$this->bm_pro_upsell_page();
	} //end bm_add_coupon

    public function bm_pdf_customization() {
        $this->bm_pro_upsell_page();
	} //end bm_pdf_customization();


	/**
	 * Multilingual email content
	 *
	 * @author Darpan
	 */
	public function bm_multilingual_email() {
		do_action( 'wpml_multilingual_options', 'bm_new_order_admin_email_subject' );
		do_action( 'wpml_multilingual_options', 'bm_new_order_admin_email_body' );
	}//end bm_multilingual_email()


	/**
	 * Synchronise the plugin timezone with the WordPress site timezone.
	 *
	 * Runs on `init`. Skips the DB write when the stored value already
	 * matches, avoiding redundant queries on every page load.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function bm_set_timezone() {
		$dbhandler   = new BM_DBhandler();
		$wp_timezone = get_option( 'timezone_string' );

		if ( empty( $wp_timezone ) ) {
			$gmt_offset  = get_option( 'gmt_offset' );
			$wp_timezone = timezone_name_from_abbr( '', $gmt_offset * 3600, 0 );
		}

		if ( empty( $wp_timezone ) ) {
			return;
		}

		$stored_timezone = $dbhandler->get_global_option_value( 'bm_booking_time_zone', '' );

		if ( $stored_timezone === $wp_timezone ) {
			return;
		}

		$bmrequests   = new BM_Request();
		$country_code = $bmrequests->bm_fetch_country_code_by_timezone( $wp_timezone );

		if ( ! empty( $country_code ) && ! empty( $bmrequests->bm_get_countries( $country_code ) ) ) {
			$dbhandler->update_global_option_value( 'bm_booking_country', $country_code );
		}

		$dbhandler->update_global_option_value( 'bm_booking_time_zone', $wp_timezone );
	}//end bm_set_timezone()


	/**
	 * Update the plugin timezone when the WordPress timezone_string option changes.
	 *
	 * Hooked to `update_option_timezone_string`.
	 *
	 * @since 1.0.0
	 * @param string $old_value Previous timezone string.
	 * @param string $new_value New timezone string.
	 * @return void
	 */
	public function bm_update_plugin_timezone_on_wp_change( $old_value, $new_value ) {
		$dbhandler    = new BM_DBhandler();
		$bmrequests   = new BM_Request();
		$country_code = $bmrequests->bm_fetch_country_code_by_timezone( $new_value );

		if ( ! empty( $country_code ) && ! empty( $bmrequests->bm_get_countries( $country_code ) ) ) {
			$dbhandler->update_global_option_value( 'bm_booking_country', $country_code );
		}

		$dbhandler->update_global_option_value( 'bm_booking_time_zone', $new_value );
	}//end bm_update_plugin_timezone_on_wp_change()


	/**
	 * Update the plugin timezone when the WordPress gmt_offset option changes.
	 *
	 * Only fires when timezone_string is empty (manual UTC offset mode).
	 * Hooked to `update_option_gmt_offset`.
	 *
	 * @since 1.0.0
	 * @param mixed $old_value Previous GMT offset.
	 * @param mixed $new_value New GMT offset.
	 * @return void
	 */
	public function bm_update_plugin_timezone_on_gmt_offset_change( $old_value, $new_value ) {
		if ( ! empty( get_option( 'timezone_string' ) ) ) {
			return;
		}

		$gmt_timezone = timezone_name_from_abbr( '', $new_value * 3600, 0 );

		if ( ! $gmt_timezone ) {
			return;
		}

		$dbhandler    = new BM_DBhandler();
		$bmrequests   = new BM_Request();
		$country_code = $bmrequests->bm_fetch_country_code_by_timezone( $gmt_timezone );

		if ( ! empty( $country_code ) && ! empty( $bmrequests->bm_get_countries( $country_code ) ) ) {
			$dbhandler->update_global_option_value( 'bm_booking_country', $country_code );
		}

		$dbhandler->update_global_option_value( 'bm_booking_time_zone', $gmt_timezone );
	}//end bm_update_plugin_timezone_on_gmt_offset_change()


	/**
	 * Register Shortcodes
	 *
	 * @author Darpan
	 */
	public function bm_register_shortcodes() {
		add_shortcode( 'sgbm_flexibooking_language_switcher', 'bm_flexibooking_language_switcher' );
		add_shortcode( 'sgbm_customer_profile', array( $this, 'bm_sgbm_customer_profile' ) );
		add_shortcode( 'sgbm_service_booking_planner', array( $this, 'bm_service_booking_planner_shortcode' ) );
		add_shortcode( 'sgbm_single_service_booking_planner', array( $this, 'bm_single_service_booking_planner_shortcode' ) );
	}//end bm_register_shortcodes()


	/**
	 * Language Switcher
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_language_switcher() {
		ob_start();
		$content = $this->flexibooking_language_switcher();
		ob_end_clean();
		return $content;

		/**ob_start();
		$this->flexibooking_language_switcher();
		return ob_get_clean();*/
	}//end bm_flexibooking_language_switcher()


	/**
	 * Customer profile by id shortcode
	 *
	 * @author Darpan
	 */
	public function bm_sgbm_customer_profile( $att ) {
		return '<p>' . esc_html__( 'Customer Profile is a Pro feature.', 'service-booking' ) . '</p>';
	}//end bm_sgbm_customer_profile()


	/**
	 * Service fullcalendar shortcode
	 *
	 * @author Darpan
	 */
	public function bm_service_booking_planner_shortcode( $atts = array() ) {
		return '<p>' . esc_html__( 'Service Booking Planner is a Pro feature.', 'service-booking' ) . '</p>';
	}//end bm_service_booking_planner_shortcode()


	/**
	 * Single service booking planner
	 *
	 * @author Darpan
	 */
	public function bm_single_service_booking_planner_shortcode( $atts = array() ) {
		return '<p>' . esc_html__( 'Single Service Booking Planner is a Pro feature.', 'service-booking' ) . '</p>';
	}//end bm_single_service_booking_planner_shortcode()


	/**
	 * Language Switcher Content
	 *
	 * @author Darpan
	 */
	public function flexibooking_language_switcher() {
		// switcher check if any other plugin active then don't show this
		$lang_plugin_active = get_option( 'lang_plugin', false );
		if ( $lang_plugin_active && ( $lang_plugin_active != 'none' || $lang_plugin_active != '' ) && ! is_admin() ) {
			return;
		}
		global $sitepress;
		if ( $sitepress && is_admin() ) {
			return;
		}
		$dbhandler        = new BM_DBhandler();
		$bmrequests       = new BM_Request();
		$html             = '';
		$languages        = $dbhandler->get_global_option_value( 'bm_flexibooking_languages', array() );
		$current_language = $dbhandler->get_global_option_value( 'bm_flexi_current_language', 'en' );

		$html .= '<select name="bm_flexibooking_language" id="bm_flexibooking_language" onchange="change_flexi_language(this)">';
		foreach ( $languages as $lang_code => $lang_name ) {
			$selected = ( $current_language === $lang_code ) ? 'selected' : '';
			$html    .= '<option value="' . esc_html( $lang_code ) . '" ' . esc_html( $selected ) . '>' . esc_html( $lang_name ) . '</option>';
		}
		$html .= '</select>';

		$html = apply_filters( 'bm_flexibooking_language_switcher_html', $html, $languages, $current_language );

		return wp_kses( $html, $bmrequests->bm_fetch_expanded_allowed_tags() );
	}//end flexibooking_language_switcher()


	/**
	 * Set installed languages
	 *
	 * @author Darpan
	 */
	public function bm_set_installed_languages_old() {
		$languages = get_option( 'available_languages', array() );
		$languages = apply_filters( 'bm_flexibooking_modify_installed_languages', $languages );

		if ( ! in_array( 'it_IT', $languages ) ) {
			$languages[] = 'it_IT';
			update_option( 'available_languages', $languages );
		}

		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		$it_translation = wp_download_language_pack( 'it_IT' );

		do_action( 'bm_flexibooking_languages_installed', $languages );
	} //end bm_set_installed_languages()


	/**
	 * Ensure Italian (it_IT) translation is installed.
	 *
	 * Runs on `init`. Skips the filesystem and download steps when the
	 * Italian language pack is already present to avoid unnecessary I/O
	 * on every page load.
	 *
	 * @since 1.0.0
	 * @return false|void False when filesystem credentials are unavailable.
	 */
	public function bm_set_installed_languages() {
		$languages = get_option( 'available_languages', array() );
		$languages = apply_filters( 'bm_flexibooking_modify_installed_languages', $languages );

		if ( in_array( 'it_IT', $languages, true ) ) {
			do_action( 'bm_flexibooking_languages_installed', $languages );
			return;
		}

		$languages[] = 'it_IT';
		update_option( 'available_languages', $languages );

		if ( ! function_exists( 'request_filesystem_credentials' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$url   = wp_nonce_url( admin_url() );
		$creds = request_filesystem_credentials( $url );

		if ( ! WP_Filesystem( $creds ) ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		wp_download_language_pack( 'it_IT' );

		do_action( 'bm_flexibooking_languages_installed', $languages );
	}
	// end bm_set_installed_languages()


	/**
	 * Force locale
	 *
	 * @author Darpan
	 */
	public function bm_load_service_booking_locale() {
		$dbhandler          = new BM_DBhandler();
		$current_locale     = $dbhandler->get_global_option_value( 'bm_flexi_current_locale', 'en_US' );
		$lang_plugin_active = get_option( 'lang_plugin', false );

		if ( $lang_plugin_active && ( $lang_plugin_active != 'none' || $lang_plugin_active != '' ) && ! is_admin() ) {
			switch_to_locale( $current_locale );
			return;
		}
		if ( function_exists( 'switch_to_locale' ) ) {
			update_option( 'WPLANG', $current_locale == 'en_US' ? '' : $current_locale );
		}

		do_action( 'bm_flexibooking_locale_loaded', $current_locale );
	}//end bm_load_service_booking_locale()


	/**
	 * Add Language Switcher In Admin Bar
	 *
	 * @since 1.0.0
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function bm_add_flexibooking_language_switcher_in_admin_bar( $wp_admin_bar ) {
		$dbhandler                      = new BM_DBhandler();
		$language_switcher_in_admin_bar = $dbhandler->get_global_option_value( 'bm_show_lng_swtchr_in_admin_bar', '0' );
		$show_admin_bar                 = apply_filters( 'flexibooking_show_lang_switchr_in_admin_bar', $language_switcher_in_admin_bar );

		if ( absint( $show_admin_bar ) !== 1 ) {
			return;
		}

		$wp_admin_bar->add_menu(
			array(
				'parent' => false,
				'id'     => 'bm_flexibooking_current_language',
				'title'  => $this->flexibooking_language_switcher(),
				'href'   => false,
			)
		);

		do_action( 'bm_flexibooking_admin_bar_language_switcher_added', $wp_admin_bar );
	}//end bm_add_flexibooking_language_switcher_in_admin_bar()


	/**
	 * Add Language Switcher In footer
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function bm_add_flexibooking_language_switcher_in_footer() {
		$dbhandler                   = new BM_DBhandler();
		$bmrequests                  = new BM_Request();
		$language_switcher_in_footer = $dbhandler->get_global_option_value( 'bm_show_lng_swtchr_in_footer', '0' );
		$show_in_footer              = apply_filters( 'flexibooking_show_lang_switchr_in_footer', $language_switcher_in_footer );

		if ( absint( $show_in_footer ) !== 1 ) {
			return;
		}

		$html  = '<div class="flexi-lang-select-box" id="bm_flexibooking_current_language">';
		$html .= $this->flexibooking_language_switcher();
		$html .= '</div>';

		$html = apply_filters( 'bm_flexibooking_language_switcher_footer_html', $html );

		echo wp_kses( $html, $bmrequests->bm_fetch_expanded_allowed_tags() );

		do_action( 'bm_flexibooking_footer_language_switcher_added', $html );
	}//end bm_add_flexibooking_language_switcher_in_footer()


	/**
	 * Set Language
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_set_language() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$post       = apply_filters( 'bm_flexibooking_set_language_post_data', $post );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$set_language = isset( $post['flexi_lang_code'] ) ? esc_html( sanitize_text_field( wp_unslash( $post['flexi_lang_code'] ) ) ) : esc_html( 'en' );

			if ( in_array( $set_language, array( 'en', 'it' ) ) ) {
				$current_locale = $set_language == 'it' ? 'it_IT' : 'en_US';
				$dbhandler->update_global_option_value( 'bm_flexi_current_language', $set_language );
				$dbhandler->update_global_option_value( 'bm_flexi_current_locale', $current_locale );
				$this->bm_flexibooking_load_locale();

				do_action( 'bm_flexibooking_language_set', $set_language, $current_locale );

				$data['status'] = true;
			}
		}

		$data = apply_filters( 'bm_flexibooking_set_language_response', $data, $set_language );

		echo wp_json_encode( $data );
		die;
	}//end bm_flexibooking_set_language()


	/**
	 * Load locale
	 *
	 * @author Darpan
	 */
	private function bm_flexibooking_load_locale() {
		$dbhandler      = new BM_DBhandler();
		$current_locale = $dbhandler->get_global_option_value( 'bm_flexi_current_locale', 'en_US' );
		$current_locale = apply_filters( 'bm_flexibooking_modify_locale', $current_locale );

		switch_to_locale( $current_locale );

		$current_locale == 'en_US' ? update_option( 'WPLANG', '' ) : update_option( 'WPLANG', $current_locale );

		do_action( 'bm_flexibooking_locale_switched', $current_locale );
	}//end bm_flexibooking_load_locale()


	/**
	 * Sort service Listing
	 *
	 * @author Darpan
	 */
	public function bm_sort_service_listing() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$post                  = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$post                  = apply_filters( 'bm_flexibooking_modify_sort_post_data', $post );
		$dbhandler             = new BM_DBhandler();
		$bmrequests            = new BM_Request();
		$total_service_records = $dbhandler->bm_count( 'SERVICE' );
		$category_name         = array();
		$data                  = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$base    = isset( $post['base'] ) ? $post['base'] : '';
			$limit   = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;
			$pagenum = isset( $post['pagenum'] ) ? absint( $post['pagenum'] ) : 1;
			$offset  = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;
			$ids     = isset( $post['ids'] ) ? $post['ids'] : array();

			if ( ! empty( $ids ) && $total_service_records > 0 ) {
				$order = ( 1 + $offset );
				for ( $i = 0; $i < $total_service_records; $i++ ) {
					if ( isset( $ids[ $i ] ) ) {
						$update_data = array(
							'service_position'   => $order,
							'service_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
						);
						$dbhandler->update_row( 'SERVICE', 'id', $ids[ $i ], $update_data, '', '%d' );
						++$order;
					}
				}
			}

			$services = $dbhandler->get_all_result( 'SERVICE', array( 'id', 'service_name', 'is_service_front', 'service_position' ), 1, 'results', $offset, $limit, 'service_position', false );
			$services = apply_filters( 'bm_flexibooking_modify_sorted_services', $services );

			if ( ! empty( $services ) && is_array( $services ) ) {
				foreach ( $services as $service ) {
					$category_name[] = $bmrequests->bm_fetch_category_name_by_service_id( $service->id ? $service->id : 0 );
				}
			}

			$category_name = apply_filters( 'bm_flexibooking_modify_category_names', $category_name, $services );

			$num_of_pages               = ( $limit > 0 ) ? ceil( $total_service_records / $limit ) : 1;
			$data['status']             = true;
			$data['pagination']         = wp_kses_post( $dbhandler->bm_get_pagination( $num_of_pages, $pagenum, $base, 'list' ) );
			$data['current_pagenumber'] = ( 1 + $offset );
			$data['services']           = $services;
			$data['category_name']      = $category_name;
		}

		$data = apply_filters( 'bm_flexibooking_modify_sort_data', $data, $post );

		echo wp_json_encode( $data );
		die;
	}//end bm_sort_service_listing()


	/**
	 * Change service visibility
	 *
	 * @author Darpan
	 */
	public function bm_change_service_visibility() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$id = apply_filters( 'bm_flexibooking_modify_service_visibility_id', $id );

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$data       = array( 'status' => false );

		if ( $id != false && $id != null ) {
			$service          = $dbhandler->get_row( 'SERVICE', $id );
			$is_service_front = isset( $service->is_service_front ) ? $service->is_service_front : 0;

			$update_data = array(
				'is_service_front'   => $is_service_front == 0 ? 1 : 0,
				'service_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
			);

			do_action( 'bm_flexibooking_before_service_visibility_update', $id, $update_data );

			$update = $dbhandler->update_row( 'SERVICE', 'id', $id, $update_data, '', '%d' );

			do_action( 'bm_flexibooking_after_service_visibility_update', $id, $service, $update );

			if ( $update ) {
				$data['status'] = true;
			}
		}

		$data = apply_filters( 'bm_flexibooking_modify_service_visibility_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_change_service_visibility()


	/**
	 * Change service visibility
	 *
	 * @author Darpan
	 */
	public function bm_change_extra_service_visibility() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$data       = array( 'status' => false );

		if ( $id != false && $id != null ) {
			$extra_service          = $dbhandler->get_row( 'EXTRA', $id );
			$is_extra_service_front = isset( $extra_service->is_extra_service_front ) ? $extra_service->is_extra_service_front : 0;

			$update_data = array(
				'is_extra_service_front' => $is_extra_service_front == 0 ? 1 : 0,
				'extras_created_at'      => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
			);

			$update = $dbhandler->update_row( 'EXTRA', 'id', $id, $update_data, '', '%d' );

			if ( $update ) {
				$data['status'] = true;
			}
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_change_extra_service_visibility()


	/**
	 * Remove a service
	 *
	 * @author Darpan
	 */
	public function bm_remove_service() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$post          = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$post          = apply_filters( 'bm_flexibooking_modify_remove_service_post', $post );
		$dbhandler     = new BM_DBhandler();
		$bmrequests    = new BM_Request();
		$category_name = array();
		$data          = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$base    = isset( $post['base'] ) ? $post['base'] : '';
			$limit   = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;
			$pagenum = isset( $post['pagenum'] ) ? absint( $post['pagenum'] ) : 1;
			$offset  = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;
			$id      = isset( $post['id'] ) ? $post['id'] : 0;

			do_action( 'bm_flexibooking_service_id_before_service_removal', $id );

			if ( ! empty( $id ) ) {
				$svc_gallery_row = $dbhandler->get_all_result(
					'GALLERY',
					'*',
					array(
						'module_type' => 'SERVICE',
						'module_id'   => $id,
					),
					'results'
				);
				$svc_gallery_row = isset( $svc_gallery_row[0] ) && ! empty( $svc_gallery_row[0] ) ? $svc_gallery_row[0] : '';
				$svc_extra_rows  = $dbhandler->get_all_result( 'EXTRA', '*', array( 'service_id' => $id ), 'results' );
				$time_row        = $dbhandler->get_row( 'TIME', $id, 'service_id' );
				$bookings        = $dbhandler->get_all_result( 'BOOKING', '*', array( 'service_id' => $id ), 'results' );

				if ( ! empty( $svc_gallery_row ) ) {
					$dbhandler->remove_row( 'GALLERY', 'id', $svc_gallery_row->id, '%d' );
				}

				if ( ! empty( $svc_extra_rows ) ) {
					$svc_gallery_deleted = array();
					foreach ( $svc_extra_rows as $extra_row ) {
						$svc_gallery_deleted[] = $dbhandler->remove_row( 'EXTRA', 'id', $extra_row->id, '%d' );
					}
				}

				if ( ! empty( $time_row ) ) {
					$dbhandler->remove_row( 'TIME', 'id', $time_row->id, '%d' );
				}

				if ( ! empty( $time_row ) ) {
					$dbhandler->remove_row( 'TIME', 'id', $time_row->id, '%d' );
				}

				$service_removed = $dbhandler->remove_row( 'SERVICE', 'id', $id, '%d' );

				do_action( 'bm_flexibooking_after_service_removal', $id, $service_removed );

				if ( $service_removed ) {
					$frontend_selected_services = $dbhandler->get_global_option_value( 'bm_service_search_selected_services' );
					$backend_selected_services  = $dbhandler->get_global_option_value( 'bm_backend_dashboard_revenue_wise_order_svc_search_ids' );

					if ( ! empty( $frontend_selected_services ) && in_array( $id, $frontend_selected_services ) ) {
						$frontend_selected_services = array_diff( $frontend_selected_services, array( $id ) );
						$dbhandler->update_global_option_value( 'bm_service_search_selected_services', $frontend_selected_services );
					}

					do_action( 'bm_flexibooking_after_frontend_selected_service_removal', $id, $frontend_selected_services );

					if ( ! empty( $backend_selected_services ) && in_array( $id, $backend_selected_services ) ) {
						$backend_selected_services = array_diff( $backend_selected_services, array( $id ) );
						$dbhandler->update_global_option_value( 'bm_service_search_selected_services', $backend_selected_services );
					}

					do_action( 'bm_flexibooking_after_backend_selected_service_removal', $id, $backend_selected_services );

					if ( ! empty( $bookings ) ) {
						foreach ( $bookings as $booking ) {
							$is_active   = isset( $booking->is_active ) ? $booking->is_active : 0;
							$transaction = $dbhandler->get_row( 'TRANSACTIONS', $booking->id, 'booking_id' );

							if ( ! empty( $transaction ) ) {
								$dbhandler->remove_row( 'TRANSACTIONS', 'id', $transaction->id, '%d' );
							}

							if ( $is_active == 1 ) {
								$bmrequests->bm_cancel_and_refund_order( $booking->id );
							}
						}
					}

					$total_service_records      = $dbhandler->bm_count( 'SERVICE' );
					$num_of_pages               = ( $limit > 0 ) ? ceil( $total_service_records / $limit ) : 1;
					$data['status']             = true;
					$data['pagination']         = wp_kses_post( $dbhandler->bm_get_pagination( $num_of_pages, $pagenum, $base, 'list' ) );
					$data['current_pagenumber'] = ( 1 + $offset );
					$data['services']           = $dbhandler->get_all_result( 'SERVICE', array( 'id', 'service_name', 'is_service_front', 'service_position' ), 1, 'results', $offset, $limit, 'service_position', false );

					if ( ! empty( $data['services'] ) ) {
						foreach ( $data['services'] as $key => $service ) {
							$category_name[ $key ] = $bmrequests->bm_fetch_category_name_by_service_id( $service->id );
						}
					}

					$data['category_name'] = $category_name;
				}
			}
		} //end if

		$data = apply_filters( 'bm_flexibooking_modify_remove_service_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_remove_service()


	/**
	 * Fetch templates
	 *
	 * @author Darpan
	 */
	public function bm_fetch_template_listing() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$post = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$post = apply_filters( 'bm_flexibooking_modify_template_listing_post', $post );

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$type_names = array();
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$base    = isset( $post['base'] ) ? $post['base'] : '';
			$limit   = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;
			$pagenum = isset( $post['pagenum'] ) ? absint( $post['pagenum'] ) : 1;
			$offset  = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;

			$language   = $dbhandler->get_global_option_value( 'bm_flexi_current_language', 'en' );
			$name_field = $language == 'it' ? 'tmpl_name_it' : 'tmpl_name_en';

			$total_template_records     = $dbhandler->bm_count( 'EMAIL_TMPL' );
			$num_of_pages               = ( $limit > 0 ) ? ceil( $total_template_records / $limit ) : 1;
			$data['status']             = true;
			$data['pagination']         = wp_kses_post( $dbhandler->bm_get_pagination( $num_of_pages, $pagenum, $base, 'list' ) );
			$data['current_pagenumber'] = ( 1 + $offset );
			$data['templates']          = $dbhandler->get_all_result( 'EMAIL_TMPL', array( 'id', $name_field, 'type', 'status' ), 1, 'results', $offset, $limit );

			if ( ! empty( $data['templates'] ) ) {
				foreach ( $data['templates'] as $key => $template ) {
					$type_names[ $key ] = $bmrequests->bm_fetch_template_type_name_by_type_id( $template->type );
				}
			}

			$data['type_name'] = $type_names;
		}

		$data = apply_filters( 'bm_flexibooking_modify_template_listing_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_template_listing()


	/**
	 * Fetch price modules
	 *
	 * @author Darpan
	 */
	public function bm_fetch_price_module_listing() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$post = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$post = apply_filters( 'bm_flexibooking_modify_price_module_listing_post', $post );

		$dbhandler  = new BM_DBhandler();
		$type_names = array();
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$base    = isset( $post['base'] ) ? $post['base'] : '';
			$limit   = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;
			$pagenum = isset( $post['pagenum'] ) ? absint( $post['pagenum'] ) : 1;
			$offset  = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;

			$data['status']             = true;
			$data['current_pagenumber'] = ( 1 + $offset );
		}

		$data = apply_filters( 'bm_flexibooking_modify_price_module_listing_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_price_module_listing()


	/**
	 * Fetch notification processes
	 *
	 * @author Darpan
	 */
	public function bm_remove_template() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$post = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$post = apply_filters( 'bm_flexibooking_modify_remove_template_post', $post );

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$type_names = array();
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$base    = isset( $post['base'] ) ? $post['base'] : '';
			$limit   = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;
			$pagenum = isset( $post['pagenum'] ) ? absint( $post['pagenum'] ) : 1;
			$offset  = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;
			$id      = isset( $post['id'] ) ? $post['id'] : 0;

			$language = $dbhandler->get_global_option_value( 'bm_flexi_current_language', 'en' );
			$template = $dbhandler->get_row( 'EMAIL_TMPL', $id );

			if ( ! empty( $template ) ) {
				do_action( 'bm_flexibooking_before_template_removal', $id, $template );

				$removed         = $dbhandler->remove_row( 'EMAIL_TMPL', 'id', $id, '%d' );
				$email_templates = $dbhandler->get_all_result( 'EMAIL_TMPL', '*', 1, 'results' );

				if ( empty( $email_templates ) ) {
					$dbhandler->update_global_option_value( 'bm_email_templates_created', '0' );
				}

				do_action( 'bm_flexibooking_after_template_removal', $id, $template, $removed );

				if ( $removed ) {
					$name_field = $language == 'it' ? 'tmpl_name_it' : 'tmpl_name_en';

					$total_template_records     = $dbhandler->bm_count( 'EMAIL_TMPL' );
					$num_of_pages               = ( $limit > 0 ) ? ceil( $total_template_records / $limit ) : 1;
					$data['status']             = true;
					$data['pagination']         = wp_kses_post( $dbhandler->bm_get_pagination( $num_of_pages, $pagenum, $base, 'list' ) );
					$data['current_pagenumber'] = ( 1 + $offset );
					$data['templates']          = $dbhandler->get_all_result( 'EMAIL_TMPL', array( 'id', $name_field, 'type', 'status' ), 1, 'results', $offset, $limit );

					if ( ! empty( $data['templates'] ) ) {
						foreach ( $data['templates'] as $key => $template ) {
							$type_names[ $key ] = $bmrequests->bm_fetch_template_type_name_by_type_id( $template->type );
						}
					}

					$data['type_name'] = $type_names;
				}
			}
		}

		$data = apply_filters( 'bm_flexibooking_modify_remove_template_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_remove_template()


	/**
	 * Change template visibility
	 *
	 * @author Darpan
	 */
	public function bm_change_email_template_visibility() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$post       = apply_filters( 'bm_flexibooking_modify_template_visiblity_post', $post );
		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$template_id  = isset( $post['id'] ) ? $post['id'] : 0;
			$input_status = isset( $post['status'] ) ? $post['status'] : -1;
			$input_type   = isset( $post['type'] ) ? $post['type'] : -1;

			if ( $template_id > 0 && $input_status != -1 && $input_type != -1 ) {
				do_action( 'bm_flexibooking_before_template_visibility_change', $template_id, $input_status, $input_type );

				$active_type = $bmrequests->bm_check_active_email_template_of_a_specific_type( $input_type );

				if ( $input_status == 1 && $active_type ) {
					$data['status'] = 'error';
				} else {
					$update_data = array(
						'status'              => $input_status,
						'template_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
					);

					$update = $dbhandler->update_row( 'EMAIL_TMPL', 'id', $template_id, $update_data, '', '%d' );

					do_action( 'bm_flexibooking_after_template_visibility_change', $template_id, $input_status, $update );

					if ( $update ) {
						$data['status'] = true;
					}
				}
			}
		}

		$data = apply_filters( 'bm_flexibooking_modify_template_visibility_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_change_email_template_visibility()


	/**
	 * Sort category Listing
	 *
	 * @author Darpan
	 */
	public function bm_sort_category_listing() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$post                   = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$post                   = apply_filters( 'bm_flexibooking_modify_sort_category_post', $post );
		$dbhandler              = new BM_DBhandler();
		$total_category_records = $dbhandler->bm_count( 'CATEGORY' );
		$data                   = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$base    = isset( $post['base'] ) ? $post['base'] : '';
			$limit   = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;
			$pagenum = isset( $post['pagenum'] ) ? absint( $post['pagenum'] ) : 1;
			$offset  = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;
			$ids     = isset( $post['ids'] ) ? $post['ids'] : array();

			do_action( 'bm_flexibooking_before_category_sort', $ids, $total_category_records );

			if ( ! empty( $ids ) && $total_category_records > 0 ) {
				$order = ( 1 + $offset );
				for ( $i = 0; $i < $total_category_records; $i++ ) {
					if ( isset( $ids[ $i ] ) ) {
						$update_data = array(
							'cat_position'   => $order,
							'cat_updated_at' => ( new BM_Request() )->bm_fetch_current_wordpress_datetime_stamp(),
						);

						$dbhandler->update_row( 'CATEGORY', 'id', $ids[ $i ], $update_data, '', '%d' );
						++$order;
					}
				}
			}

			do_action( 'bm_flexibooking_after_category_sort', $ids, $total_category_records );

			$categories                 = $dbhandler->get_all_result( 'CATEGORY', array( 'id', 'cat_name', 'cat_in_front', 'cat_position' ), 1, 'results', $offset, $limit, 'cat_position', false );
			$cat_ids                    = wp_list_pluck( $categories, 'id', 0 );
			$cat_ids                    = ! empty( $cat_ids ) && is_array( $cat_ids ) ? implode( ',', $cat_ids ) : '';
			$num_of_pages               = ( $limit > 0 ) ? ceil( $total_category_records / $limit ) : 1;
			$data['status']             = true;
			$data['pagination']         = wp_kses_post( $dbhandler->bm_get_pagination( $num_of_pages, $pagenum, $base, 'list' ) );
			$data['current_pagenumber'] = ( 1 + $offset );
			$data['cat_ids']            = $cat_ids;
			$data['categories']         = $categories;
		}

		$data = apply_filters( 'bm_flexibooking_sort_category_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_sort_category_listing()


	/**
	 * Change category visibility
	 *
	 * @author Darpan
	 */
	public function bm_change_category_visibility() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$id = apply_filters( 'bm_flexibooking_modify_category_visibility_id', $id );

		$dbhandler = new BM_DBhandler();
		$data      = array( 'status' => false );

		if ( $id != false && $id != null ) {
			$category     = $dbhandler->get_row( 'CATEGORY', $id );
			$cat_in_front = isset( $category->cat_in_front ) ? $category->cat_in_front : 0;

			$update_data = array(
				'cat_in_front'   => $cat_in_front == 0 ? 1 : 0,
				'cat_updated_at' => ( new BM_Request() )->bm_fetch_current_wordpress_datetime_stamp(),
			);

			do_action( 'bm_flexibooking_before_category_visibility_change', $id, $category, $update_data );

			$update = $dbhandler->update_row( 'CATEGORY', 'id', $id, $update_data, '', '%d' );

			do_action( 'bm_flexibooking_after_category_visibility_change', $id, $category, $update );

			if ( $update ) {
				$data['status'] = true;
			}
		}

		$data = apply_filters( 'bm_flexibooking_category_visibility_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_change_category_visibility()


	/**
	 * Change customer visibility
	 *
	 * @author Darpan
	 */
	public function bm_change_customer_visibility() {
		if ( ! Booking_Management_Limits::can_create_customer() ) {
			wp_send_json_error( __( 'Customer management is a Pro feature.', 'service-booking' ) );
			return;
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$id = apply_filters( 'bm_flexibooking_modify_customer_visibility_id', $id );

		$dbhandler = new BM_DBhandler();
		$data      = array( 'status' => false );

		if ( $id != false && $id != null ) {
			$customer  = $dbhandler->get_row( 'CUSTOMERS', $id );
			$is_active = isset( $customer->is_active ) ? $customer->is_active : 0;

			$update_data = array(
				'is_active'           => $is_active == 0 ? 1 : 0,
				'customer_updated_at' => ( new BM_Request() )->bm_fetch_current_wordpress_datetime_stamp(),
			);

			do_action( 'bm_flexibooking_before_customer_visibility_change', $id, $customer, $update_data );

			$update = $dbhandler->update_row( 'CUSTOMERS', 'id', $id, $update_data, '', '%d' );

			do_action( 'bm_flexibooking_after_customer_visibility_change', $id, $customer, $update );

			if ( $update ) {
				$data['status'] = true;
			}
		}

		$data = apply_filters( 'bm_flexibooking_customer_visibility_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_change_customer_visibility()


	/**
	 * Remove a category
	 *
	 * @author Darpan
	 */
	public function bm_remove_category() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$post      = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$post      = apply_filters( 'bm_flexibooking_modify_remove_category_post', $post );
		$dbhandler = new BM_DBhandler();
		$data      = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$base         = isset( $post['base'] ) ? $post['base'] : '';
			$limit        = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;
			$pagenum      = isset( $post['pagenum'] ) ? absint( $post['pagenum'] ) : 1;
			$offset       = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;
			$id           = isset( $post['id'] ) ? $post['id'] : 0;
			$service_rows = $dbhandler->get_all_result( 'SERVICE', '*', array( 'service_category' => $id ), 'results' );

			do_action( 'bm_flexibooking_before_unlinking_services_from_category', $id, $service_rows );

			if ( isset( $service_rows ) && ! empty( $service_rows ) ) {
				foreach ( $service_rows as $service_row ) {
					if ( ! empty( $service_row ) ) {
						$update_data = array(
							'service_category'   => '',
							'service_updated_at' => ( new BM_Request() )->bm_fetch_current_wordpress_datetime_stamp(),
						);

						$dbhandler->update_row( 'SERVICE', 'id', $service_row->id, $update_data, '', '%d' );

						do_action( 'bm_flexibooking_after_unlinking_services_from_category', $service_row->id, $id );
					}
				}
			}

			do_action( 'bm_flexibooking_before_category_removal', $id );

			$cat_removed = $dbhandler->remove_row( 'CATEGORY', 'id', $id, '%d' );

			if ( $cat_removed ) {
				$frontend_selected_categories = $dbhandler->get_global_option_value( 'bm_front_svc_search_shortcode_cat_ids' );
				$backend_selected_categories  = $dbhandler->get_global_option_value( 'bm_backend_dashboard_cat_wise_order_cat_ids' );

				if ( ! empty( $frontend_selected_categories ) && in_array( $id, $frontend_selected_categories ) ) {
					$frontend_selected_categories = array_diff( $frontend_selected_categories, array( $id ) );
					$dbhandler->update_global_option_value( 'bm_front_svc_search_shortcode_cat_ids', $frontend_selected_categories );
				}

				do_action( 'bm_flexibooking_after_frontend_selected_categories_removal', $id, $frontend_selected_categories );

				if ( ! empty( $backend_selected_categories ) && in_array( $id, $backend_selected_categories ) ) {
					$backend_selected_categories = array_diff( $backend_selected_categories, array( $id ) );
					$dbhandler->update_global_option_value( 'bm_backend_dashboard_cat_wise_order_cat_ids', $backend_selected_categories );
				}

				do_action( 'bm_flexibooking_after_backend_selected_categories_removal', $id, $backend_selected_categories );

				$total_category_records     = $dbhandler->bm_count( 'CATEGORY' );
				$categories                 = $dbhandler->get_all_result( 'CATEGORY', array( 'id', 'cat_name', 'cat_in_front', 'cat_position' ), 1, 'results', $offset, $limit, 'cat_position', false );
				$cat_ids                    = wp_list_pluck( $categories, 'id', 0 );
				$cat_ids                    = ! empty( $cat_ids ) && is_array( $cat_ids ) ? implode( ',', ( array_merge( array( 0 ), $cat_ids ) ) ) : '';
				$num_of_pages               = ( $limit > 0 ) ? ceil( $total_category_records / $limit ) : 1;
				$data['status']             = true;
				$data['pagination']         = wp_kses_post( $dbhandler->bm_get_pagination( $num_of_pages, $pagenum, $base, 'list' ) );
				$data['current_pagenumber'] = ( 1 + $offset );
				$data['cat_ids']            = $cat_ids;
				$data['categories']         = $categories;

				do_action( 'bm_flexibooking_after_category_removal', $id, $cat_removed );
			}
		}

		$data = apply_filters( 'bm_flexibooking_remove_category_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_remove_category()


	/**
	 * Remove a price module
	 *
	 * @author Darpan
	 */
	public function bm_remove_price_module() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$post = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$post = apply_filters( 'bm_flexibooking_modify_remove_price_module_post', $post );

		$dbhandler = new BM_DBhandler();
		$data      = array(
			'status'        => false,
			'is_removeable' => false,
		);

		if ( $post != false && $post != null ) {
			$base    = isset( $post['base'] ) ? $post['base'] : '';
			$limit   = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;
			$pagenum = isset( $post['pagenum'] ) ? absint( $post['pagenum'] ) : 1;
			$offset  = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;
			$id      = isset( $post['id'] ) ? $post['id'] : 0;

			$data['is_removeable'] = true;

			$data['status'] = true;
		}

		$data = apply_filters( 'bm_flexibooking_price_module_removal_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_remove_price_module()


	/**
	 * Prices in Service Calender on page load
	 *
	 * @author Darpan
	 */
	public function bm_get_service_prices() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$id = apply_filters( 'bm_flexibooking_modify_service_prices_service_id', $id );

		$dbhandler = new BM_DBhandler();
		$data      = array( 'status' => false );

		if ( $id != false && $id != null ) {
			$service = $dbhandler->get_row( 'SERVICE', $id );
			$service = apply_filters( 'bm_flexibooking_prices_service_object', $service, $id );

			$data['status']          = true;
			$data['default_price']   = ! empty( $service ) && isset( $service->default_price ) ? $service->default_price : 0;
			$data['variable_price']  = ! empty( $service ) && isset( $service->variable_svc_prices ) ? maybe_unserialize( $service->variable_svc_prices ) : array();
			$data['unavailability']  = ! empty( $service ) && isset( $service->service_unavailability ) ? maybe_unserialize( $service->service_unavailability ) : array();
			$data['gbl_unavlabilty'] = $dbhandler->get_global_option_value( 'bm_global_unavailability' );

			$bmrequests = new BM_Request();
			$data['availability_periods'] = $bmrequests->bm_get_availability_periods( $id );

			$data = apply_filters( 'bm_flexibooking_after_getting_service_prices', $data, $service, $id );
		}

		$data = apply_filters( 'bm_flexibooking_service_prices_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_get_service_prices()


	/**
	 * Stopsales in Service Calender on page load
	 *
	 * @author Darpan
	 */
	public function bm_get_serice_stopsales() {
		if ( ! Booking_Management_Limits::can_use_stop_sales() ) {
			wp_send_json_error( esc_html__( 'Stop-sales is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$id = apply_filters( 'bm_flexibooking_modify_stopsales_service_id', $id );

		$dbhandler = new BM_DBhandler();
		$data      = array( 'status' => false );

		if ( $id != false && $id != null ) {
			$service = $dbhandler->get_row( 'SERVICE', $id );
			$service = apply_filters( 'bm_flexibooking_stopsales_service_object', $service, $id );

			$data['status']             = true;
			$data['unavailability']     = ! empty( $service ) && isset( $service->service_unavailability ) ? maybe_unserialize( $service->service_unavailability ) : array();
			$data['gbl_unavlabilty']    = $dbhandler->get_global_option_value( 'bm_global_unavailability' );
		}

		$data = apply_filters( 'bm_flexibooking_service_stopsales_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_get_serice_stopsales()


	/**
	 * Saleswitch in Service Calender on page load
	 *
	 * @author Darpan
	 */
	public function bm_get_service_saleswitch() {
		if ( ! Booking_Management_Limits::can_use_saleswitch() ) {
			wp_send_json_error( esc_html__( 'Saleswitch is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$id = apply_filters( 'bm_flexibooking_modify_saleswitch_service_id', $id );

		$dbhandler = new BM_DBhandler();
		$data      = array( 'status' => false );

		if ( $id != false && $id != null ) {
			$service = $dbhandler->get_row( 'SERVICE', $id );
			$service = apply_filters( 'bm_flexibooking_saleswitch_service_object', $service, $id );

			$data['status']              = true;
			$data['unavailability']      = ! empty( $service ) && isset( $service->service_unavailability ) ? maybe_unserialize( $service->service_unavailability ) : array();
			$data['gbl_unavlabilty']     = $dbhandler->get_global_option_value( 'bm_global_unavailability' );
		}

		$data = apply_filters( 'bm_flexibooking_service_saleswitch_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_get_service_saleswitch()


	/**
	 * Service Maximum Capacity in Service Calender on page load
	 *
	 * @author Darpan
	 */
	public function bm_get_service_max_cap() {
		if ( ! Booking_Management_Limits::can_edit_max_capacity() ) {
			wp_send_json_error( esc_html__( 'Max capacity management is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$id = apply_filters( 'bm_flexibooking_modify_max_cap_service_id', $id );

		$dbhandler = new BM_DBhandler();
		$data      = array( 'status' => false );

		if ( $id != false && $id != null ) {
			$service = $dbhandler->get_row( 'SERVICE', $id );
			$service = apply_filters( 'bm_flexibooking_max_cap_service_object', $service, $id );

			$data['status']           = true;
			$data['default_max_cap']  = ! empty( $service ) && isset( $service->default_max_cap ) ? $service->default_max_cap : 0;
			$data['variable_max_cap'] = ! empty( $service ) && isset( $service->variable_max_cap ) ? maybe_unserialize( $service->variable_max_cap ) : array();
			$data['unavailability']   = ! empty( $service ) && isset( $service->service_unavailability ) ? maybe_unserialize( $service->service_unavailability ) : array();
			$data['gbl_unavlabilty']  = $dbhandler->get_global_option_value( 'bm_global_unavailability' );
		}

		$data = apply_filters( 'bm_flexibooking_service_max_cap_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_get_service_max_cap()


	/**
	 * Service Time slots in Service Calender on page load
	 *
	 * @author Darpan
	 */
	public function bm_get_service_time_slots() {
		if ( ! Booking_Management_Limits::is_pro_active() ) {
			wp_send_json_error( esc_html__( 'Variable time slots is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

		$dbhandler = new BM_DBhandler();
		$data      = array( 'status' => false );

		if ( $id != false && $id != null ) {
			$service = $dbhandler->get_row( 'SERVICE', $id );
			$service = apply_filters( 'bm_flexibooking_time_slots_service_object', $service, $id );

			$data['status']          = true;
			$variable_time_slots     = ! empty( $service ) && isset( $service->variable_time_slots ) ? maybe_unserialize( $service->variable_time_slots ) : array();
			$data['slot_ids']        = ! empty( $variable_time_slots ) ? wp_list_pluck( $variable_time_slots, 'slot_id' ) : array();
			$data['dates']           = ! empty( $variable_time_slots ) ? wp_list_pluck( $variable_time_slots, 'date' ) : array();
			$data['unavailability']  = ! empty( $service ) && isset( $service->service_unavailability ) ? maybe_unserialize( $service->service_unavailability ) : array();
			$data['gbl_unavlabilty'] = $dbhandler->get_global_option_value( 'bm_global_unavailability' );
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_get_service_time_slots()


	/**
	 * Fetch specific service time slot
	 *
	 * @author Darpan
	 */
	public function bm_get_specific_time_slot() {
		if ( ! Booking_Management_Limits::is_pro_active() ) {
			wp_send_json_error( esc_html__( 'Variable time slots is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$id = apply_filters( 'bm_flexibooking_modify_specific_time_slot_service_id', $id );

		$date      = filter_input( INPUT_POST, 'date' );
		$dbhandler = new BM_DBhandler();
		$data      = array(
			'status'    => false,
			'slot_data' => array(),
		);

		if ( $id != false && $id != null && $date != false && $date != null ) {
			$service = $dbhandler->get_row( 'SERVICE', $id );
			$service = apply_filters( 'bm_flexibooking_specific_time_slot_service_object', $service, $id );

			if ( ! empty( $service ) ) {
				$time_slots = isset( $service->variable_time_slots ) ? maybe_unserialize( $service->variable_time_slots ) : array();
				$dates      = ! empty( $time_slots ) ? wp_list_pluck( $time_slots, 'date' ) : array();
				$index      = (int) array_search( $date, $dates );

				if ( isset( $index ) && ! empty( $index ) ) {
					$data['status']    = true;
					$data['slot_data'] = $time_slots[ $index ];
				}
			}
		}

		$data = apply_filters( 'bm_flexibooking_specific_service_time_slot_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_get_specific_time_slot()


	/**
	 * Field data on page load
	 *
	 * @author Darpan
	 */
	public function bm_get_all_field_labels() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$ordering            = filter_input( INPUT_POST, 'ordering', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$dbhandler           = new BM_DBhandler();
		$total_field_records = $dbhandler->bm_count( 'FIELDS' );

		$ordering = apply_filters( 'bm_flexibooking_before_updating_field_ordering', $ordering, $total_field_records );

		if ( $ordering != false && $ordering != null ) {
			if ( ! empty( $ordering ) && $total_field_records > 0 ) {
				for ( $i = 0; $i < $total_field_records; $i++ ) {
					$dbhandler->update_row( 'FIELDS', 'ordering', $ordering[ $i ], array( 'field_position' => ( $i + 1 ) ), '', '%d' );
				}
			}

			do_action( 'bm_flexibooking_after_updating_field_ordering', $ordering );
		}

		$fields = $dbhandler->get_all_result( 'FIELDS', array( 'id', 'field_type', 'field_label', 'field_desc', 'ordering', 'field_position' ), 1, 'results', 0, false, 'field_position', false );
		$fields = apply_filters( 'bm_flexibooking_before_fetching_fields', $fields );

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$is_default         = ( new BM_Request() )->bm_check_is_default_field( $field->id );
				$field->is_default  = $is_default;
				$field->field_label = isset( $field->field_label ) ? $field->field_label : '';
				$field->field_type  = isset( $field->field_type ) ? $field->field_type : '';
			}

			$fields = apply_filters( 'bm_flexibooking_after_fetching_fields', $fields );
		}

		$fields = apply_filters( 'bm_flexibooking_field_labels_response', $fields );

		echo wp_json_encode( $fields );
		die;
	}//end bm_get_all_field_labels()


	/**
	 * Field key and order on page load
	 *
	 * @author Darpan
	 */
	public function bm_get_fieldkey_and_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$type       = filter_input( INPUT_POST, 'type' );
		$data       = array();

		$lastrow_id = $dbhandler->get_all_result( 'FIELDS', 'id', 1, 'var', 0, 1, 'id', 'DESC' );
		$lastrow_id = apply_filters( 'bm_flexibooking_before_fetching_last_row_id', $lastrow_id );

		$ordering = ( $lastrow_id + 1 );

		$field_key = $bmrequests->bm_fetch_field_key( $ordering );
		$field_key = apply_filters( 'bm_flexibooking_before_fetching_field_key', $field_key );

		$primary_mail_key = $bmrequests->bm_check_and_return_field_key_of_primary_email_in_field_data();
		$primary_mail_key = apply_filters( 'bm_flexibooking_primary_mail_filed_key', $primary_mail_key );

		do_action( 'bm_flexibooking_after_fetching_field_key_and_ordering', $field_key, $ordering );

		if ( $type != false && $type != null ) {
			if ( ! empty( $ordering ) && ! empty( $field_key ) ) {
				$data = array(
					'type'             => $type,
					'ordering'         => $ordering,
					'field_key'        => $field_key,
					'primary_mail_key' => $primary_mail_key,
				);
			}
		}

		$data = apply_filters( 'bm_flexibooking_fieldkey_and_order_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_get_fieldkey_and_order()


	/**
	 * Remove Time slot in calendar
	 *
	 * @author Darpan
	 */
	public function bm_remove_variable_time_slot() {
		if ( ! Booking_Management_Limits::is_pro_active() ) {
			wp_send_json_error( esc_html__( 'Variable time slots is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler = new BM_DBhandler();

		$id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$id = apply_filters( 'bm_flexibooking_modify_remove_variable_slot_service_id', $id );

		$date = filter_input( INPUT_POST, 'date' );
		$date = apply_filters( 'bm_flexibooking_modify_remove_variable_slot_date', $date );

		$data = array( 'status' => '' );

		if ( $id != false && $id != null && $date != false && $date != null ) {
			$service            = $dbhandler->get_row( 'SERVICE', $id );
			$variable_slot_data = ! empty( $service ) && isset( $service->variable_time_slots ) ? maybe_unserialize( $service->variable_time_slots ) : array();
			$dates              = ! empty( $variable_slot_data ) ? wp_list_pluck( $variable_slot_data, 'date' ) : array();

			if ( ! empty( $dates ) && in_array( $date, $dates, true ) ) {
				$index = (int) array_search( $date, $dates );

				do_action( 'bm_before_unsetting_variable_time_slot_index', $variable_slot_data, $date, $index );

				unset( $variable_slot_data[ $index ] );
				$update_data = array();
				$i           = 1;

				foreach ( $variable_slot_data as $key => $value ) {
					if ( isset( $variable_slot_data[ $key ] ) ) {
						$update_data[ $i ] = $value;
					}

					++$i;
				}

				$update_count = ! empty( $update_data ) ? count( $update_data ) : 0;

				if ( ! empty( $update_count ) ) {
					for ( $i = 1; $i <= $update_count; $i++ ) {
						$update_data[ $i ]['slot_id'] = $i;
					}
				}

				do_action( 'bm_before_updating_variable_time_slots', $id, $update_data );

				$update_slots = $dbhandler->update_row( 'SERVICE', 'id', $id, array( 'variable_time_slots' => maybe_serialize( $update_data ) ), '', '%d' );

				do_action( 'bm_after_updating_variable_time_slots', $id, $update_slots );

				if ( $update_slots ) {
					$service            = $dbhandler->get_row( 'SERVICE', $id );
					$variable_slot_data = ! empty( $service ) && isset( $service->variable_time_slots ) ? maybe_unserialize( $service->variable_time_slots ) : array();
					$data['status']     = true;
					$data['slot_ids']   = ! empty( $variable_slot_data ) ? wp_list_pluck( $variable_slot_data, 'slot_id' ) : array();
					$data['dates']      = ! empty( $variable_slot_data ) ? wp_list_pluck( $variable_slot_data, 'date' ) : array();

					do_action( 'bm_after_removing_variable_time_slot', $id, $data );
				}
			} //end if
		} //end if

		$data = apply_filters( 'bm_remove_variable_time_slot_response', $data );

		echo wp_json_encode( $data );
		die;
	}//end bm_remove_variable_time_slot()


	/**
	 * Remove Field data
	 *
	 * @author Darpan
	 */
	public function bm_remove_field() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler = new BM_DBhandler();
		$id        = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$data      = array( 'status' => '' );

		if ( $id != false && $id != null ) {
			// In free version, check if this is a default field that cannot be deleted.
			if ( ! Booking_Management_Limits::is_pro_active() ) {
				$field = $dbhandler->get_row( 'FIELDS', $id, 'id' );
				if ( $field && ! empty( $field->field_name ) && ! Booking_Management_Limits::can_delete_field( $field->field_name ) ) {
					$data['status'] = 'error';
					$data['message'] = esc_html__( 'Default fields cannot be deleted in the free version. Upgrade to Pro for full field management.', 'service-booking' );
					echo wp_json_encode( $data );
					die;
				}
			}

			$field_deleted = $dbhandler->remove_row( 'FIELDS', 'id', $id, '%d' );
			$fields        = $dbhandler->get_all_result( 'FIELDS', '*', 1, 'results' );

			if ( empty( $fields ) ) {
				$dbhandler->update_global_option_value( 'bm_booking_form_fields_created', '0' );
			}

			if ( $field_deleted ) {
				$data['status'] = 'deleted';
			}
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_remove_field()


	/**
	 * Save the field order from the form builder drag-and-drop.
	 *
	 * @since 1.3.0
	 */
	public function bm_save_form_field_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler   = new BM_DBhandler();
		$response    = array( 'status' => 'error', 'message' => __( 'No fields to reorder.', 'service-booking' ) );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified above via wp_verify_nonce.
		$raw_order   = isset( $_POST['field_order'] ) ? wp_unslash( $_POST['field_order'] ) : array();
		$field_order = array();
		if ( is_array( $raw_order ) ) {
			foreach ( $raw_order as $item ) {
				if ( is_array( $item ) ) {
					$field_order[] = array_map( 'sanitize_text_field', $item );
				}
			}
		}

		if ( ! empty( $field_order ) ) {
			foreach ( $field_order as $item ) {
				$field_id = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
				$position = isset( $item['position'] ) ? absint( $item['position'] ) : 0;

				if ( $field_id > 0 ) {
					$dbhandler->update_row(
						'FIELDS',
						'id',
						$field_id,
						array( 'field_position' => $position ),
						array( '%d' ),
						array( '%d' )
					);
				}
			}
			$response['status']  = 'success';
			$response['message'] = __( 'Field order saved.', 'service-booking' );
		}

		echo wp_json_encode( $response );
		die;
	}//end bm_save_form_field_order()


	/**
	 * Archive an order
	 *
	 * @author Darpan
	 */
	public function bm_archive_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( esc_html__( 'Failed security check', 'service-booking' ) );
			return;
		}

		$dbhandler = new BM_DBhandler();
		$id        = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$data      = array( 'status' => false );

		if ( $id === false || $id === null ) {
			wp_send_json_error( esc_html__( 'Invalid order ID', 'service-booking' ) );
			return;
		}

		$can_delete = apply_filters( 'bm_before_order_archive', true, $id );
		if ( $can_delete === false ) {
			wp_send_json_error( esc_html__( 'Deletion prevented by another plugin', 'service-booking' ) );
			return;
		}

		$order_data       = $dbhandler->get_row( 'BOOKING', $id, 'id' );
		$slot_data        = $dbhandler->get_row( 'SLOTCOUNT', $id, 'booking_id' );
		$extraslot_data   = $dbhandler->get_all_result( 'EXTRASLOTCOUNT', '*', array( 'booking_id' => $id ), 'results' );
		$transaction_data = $dbhandler->get_row( 'TRANSACTIONS', $id, 'booking_id' );

		$folder    = 'new-mail';
		$directory = wp_normalize_path( plugin_dir_path( __DIR__ ) . 'src/mail-attachments/' . $folder . '/order-details' );
		$pdf_path  = wp_normalize_path( $directory . '/order-details-booking-' . $id . '.pdf' );

		do_action( 'bm_before_order_archive', $id, $order_data, $slot_data, $extraslot_data, $transaction_data, $pdf_path );

		$archive_data = array(
			'original_id'      => $id,
			'booking_data'     => maybe_serialize( $order_data ),
			'slot_data'        => maybe_serialize( $slot_data ),
			'extraslot_data'   => maybe_serialize( $extraslot_data ),
			'transaction_data' => maybe_serialize( $transaction_data ),
			'pdf_path'         => $pdf_path,
			'deleted_at'       => current_time( 'mysql' ),
			'deleted_by'       => get_current_user_id(),
		);

		$archive_result = array();
		$archive_data   = ( new BM_Request() )->sanitize_request( $archive_data, 'BOOKING_ARCHIVE' );

		if ( $archive_data != false ) {
			$archive_result = $dbhandler->insert_row( 'BOOKING_ARCHIVE', $archive_data );
		}

		if ( empty( $archive_result ) ) {
			wp_send_json_error( esc_html__( 'Failed to archive order', 'service-booking' ) );
			return;
		}

		if ( ! empty( $slot_data ) ) {
			$dbhandler->remove_row( 'SLOTCOUNT', 'id', $slot_data->id, '%d' );
		}

		if ( ! empty( $extraslot_data ) ) {
			foreach ( $extraslot_data as $extraslot ) {
				$dbhandler->remove_row( 'EXTRASLOTCOUNT', 'id', $extraslot->id, '%d' );
			}
		}

		if ( ! empty( $transaction_data ) ) {
			$dbhandler->remove_row( 'TRANSACTIONS', 'id', $transaction_data->id, '%d' );
		}

		$order_deleted = $dbhandler->remove_row( 'BOOKING', 'id', $id, '%d' );

		if ( $order_deleted ) {
			do_action( 'bm_after_order_archive', $id, $archive_data );

			$archive_dir = plugin_dir_path( __DIR__ ) . 'src/mail-attachments/archive/';
			if ( ! file_exists( $archive_dir ) ) {
				wp_mkdir_p( $archive_dir );
			}

			if ( file_exists( $pdf_path ) ) {
				$new_pdf_path = $archive_dir . 'order-details-booking-' . $id . '.pdf';
				rename( $pdf_path, $new_pdf_path );
			}

			$data['status'] = true;
		}

		do_action( 'bm_order_archive_complete', $id, $data['status'] );

		wp_send_json_success( $data );
	}//end bm_archive_order()


	/**
	 * Remove an order permanaently
	 *
	 * @author Darpan
	 */
	public function bm_remove_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( esc_html__( 'Failed security check', 'service-booking' ) );
			return;
		}

		$dbhandler = new BM_DBhandler();
		$id        = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$data      = array( 'status' => false );

		if ( $id === false || $id === null ) {
			wp_send_json_error( esc_html__( 'Invalid order ID', 'service-booking' ) );
			return;
		}

		$can_delete = apply_filters( 'bm_before_order_delete', true, $id );
		if ( $can_delete === false ) {
			wp_send_json_error( esc_html__( 'Deletion prevented by another plugin', 'service-booking' ) );
			return;
		}

		$archive_data  = $dbhandler->get_row( 'BOOKING_ARCHIVE', $id );
		$order_deleted = $dbhandler->remove_row( 'BOOKING_ARCHIVE', 'id', $id, '%d' );

		if ( $order_deleted ) {
			do_action( 'bm_after_order_delete', $id, $archive_data );

			$archive_dir = plugin_dir_path( __DIR__ ) . 'src/mail-attachments/archive/';
			$file        = $archive_dir . 'order-details-booking-' . $id . '.pdf';

			if ( file_exists( $file ) ) {
				unlink( $file );
			}

			$data['status'] = true;
		}

		do_action( 'bm_order_delete_complete', $id, $data['status'] );

		wp_send_json_success( $data );
	}//end bm_remove_order()


	/**
	 * Remove a failed order
	 *
	 * @author Darpan
	 */
	public function bm_remove_failed_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( esc_html__( 'Failed security check', 'service-booking' ) );
			return;
		}

		// FAILED_TRANSACTIONS table removed in free version.
		wp_send_json_success( array( 'status' => false ) );
	}//end bm_remove_failed_order()


	/**
	 * Restore an order
	 *
	 * @author Darpan
	 */
	public function bm_restore_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( esc_html__( 'Failed security check', 'service-booking' ) );
			return;
		}

		$archive_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$data       = array( 'status' => false );

		if ( $archive_id === false || $archive_id === null ) {
			wp_send_json_error( esc_html__( 'Invalid archive ID', 'service-booking' ) );
			return;
		}

		$dbhandler = new BM_DBhandler();
		$archive   = $dbhandler->get_row( 'BOOKING_ARCHIVE', $archive_id, 'id' );

		if ( ! $archive ) {
			wp_send_json_error( esc_html__( 'Archived order not found', 'service-booking' ) );
			return;
		}

		$can_restore = apply_filters( 'bm_before_order_restore', true, $archive_id, $archive );
		if ( $can_restore === false ) {
			wp_send_json_error( esc_html__( 'Restoration prevented by another plugin', 'service-booking' ) );
			return;
		}

		do_action( 'bm_before_order_restore', $archive_id, $archive );

		$booking_data     = maybe_unserialize( $archive->booking_data );
		$slot_data        = maybe_unserialize( $archive->slot_data );
		$extraslot_data   = maybe_unserialize( $archive->extraslot_data );
		$transaction_data = maybe_unserialize( $archive->transaction_data );

		$booking_id = $dbhandler->insert_row( 'BOOKING', (array) $booking_data );

		if ( $booking_id > 0 ) {
			if ( ! empty( $slot_data ) ) {
				unset( $slot_data->id );
				$slot_data->booking_id = $booking_id;
				$dbhandler->insert_row( 'SLOTCOUNT', (array) $slot_data );
			}

			if ( ! empty( $extraslot_data ) && is_array( $extraslot_data ) ) {
				foreach ( $extraslot_data as $extraslot ) {
					unset( $extraslot->id );
					$extraslot->booking_id = $booking_id;
					$dbhandler->insert_row( 'EXTRASLOTCOUNT', (array) $extraslot );
				}
			}

			if ( ! empty( $transaction_data ) ) {
				unset( $transaction_data->id );
				$transaction_data->booking_id = $booking_id;
				$dbhandler->insert_row( 'TRANSACTION', (array) $transaction_data );
			}

			if ( ! empty( $archive->pdf_path ) ) {
				$archive_dir  = plugin_dir_path( __DIR__ ) . 'src/mail-attachments/archive/';
				$original_dir = plugin_dir_path( __DIR__ ) . 'src/mail-attachments/new-mail/order-details/';

				if ( file_exists( $archive->pdf_path ) ) {
					$new_pdf_path = $original_dir . 'order-details-booking-' . $booking_id . '.pdf';
					rename( $archive->pdf_path, $new_pdf_path );
				}
			}

			$dbhandler->remove_row( 'BOOKING_ARCHIVE', 'id', $archive_id, '%d' );

			do_action( 'bm_after_order_restore', $booking_id, $archive );

			$data['status'] = true;
			$data['new_id'] = $booking_id;
		}

		wp_send_json_success( $data );
	}//end bm_restore_order()


	/**
	 * Delete an order permanently
	 *
	 * @author Darpan
	 */
	public function bm_delete_archive_permanently() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( esc_html__( 'Failed security check', 'service-booking' ) );
			return;
		}

		$archive_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$data       = array( 'status' => false );

		if ( $archive_id === false || $archive_id === null ) {
			wp_send_json_error( esc_html__( 'Invalid archive ID', 'service-booking' ) );
			return;
		}

		$dbhandler = new BM_DBhandler();
		$archive   = $dbhandler->get_row( 'BOOKING_ARCHIVE', $archive_id, 'id' );

		if ( $archive ) {
			if ( ! empty( $archive->pdf_path ) && file_exists( $archive->pdf_path ) ) {
				unlink( $archive->pdf_path );
			}

			$deleted = $dbhandler->remove_row( 'BOOKING_ARCHIVE', 'id', $archive_id, '%d' );

			if ( $deleted ) {
				$data['status'] = true;
			}
		}

		wp_send_json_success( $data );
	}//end bm_delete_archive_permanently()


	/**
	 * Fetch field settings on page load
	 *
	 * @author Darpan
	 */
	public function bm_get_field_settings() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$id         = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$data       = array();

		if ( $id != false && $id != null ) {
			$settings = $dbhandler->get_row( 'FIELDS', $id );

			if ( ! empty( $settings ) ) {
				$data['common'] = array(
					'id'                => isset( $settings->id ) ? $settings->id : 0,
					'field_type'        => isset( $settings->field_type ) ? $settings->field_type : '',
					'field_label'       => isset( $settings->field_label ) ? $settings->field_label : '',
					'field_name'        => isset( $settings->field_name ) ? $settings->field_name : '',
					'field_desc'        => isset( $settings->field_desc ) ? $settings->field_desc : '',
					'is_required'       => isset( $settings->is_required ) ? $settings->is_required : 0,
					'is_editable'       => isset( $settings->is_editable ) ? $settings->is_editable : 0,
					'ordering'          => isset( $settings->ordering ) ? $settings->ordering : 0,
					'woocommerce_field' => isset( $settings->woocommerce_field ) ? $settings->woocommerce_field : '',
					'field_key'         => isset( $settings->field_key ) ? $settings->field_key : '',
					'primary_mail_key'  => $bmrequests->bm_check_and_return_field_key_of_primary_email_in_field_data(),
					'field_position'    => isset( $settings->field_position ) ? $settings->field_position : '',
				);

				$data['field_options'] = isset( $settings->field_options ) ? maybe_unserialize( $settings->field_options ) : array();
			}
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_get_field_settings()


	/**
	 * Check if existing field key
	 *
	 * @author Darpan
	 */
	public function bm_check_if_existing_field_key() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$post         = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$dbhandler    = new BM_DBhandler();
		$bmrequests   = new BM_Request();
		$is_existing  = 1;
		$original_key = '';
		$data         = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$field_key    = isset( $post['field_key'] ) ? $post['field_key'] : '';
			$field_id     = isset( $post['field_id'] ) ? $post['field_id'] : '';
			$original_key = $dbhandler->get_value( 'FIELDS', 'field_key', $field_id, 'id' );
			$id           = $dbhandler->get_value( 'FIELDS', 'id', $field_key, 'field_key' );

			if ( empty( $id ) ) {
				$is_existing = 0;
			} elseif ( ! empty( $id ) && ( $field_key == $original_key ) ) {
				$is_existing = 0;
			}

			$data['status'] = true;
		} //end if

		$data['is_existing']  = $is_existing;
		$data['original_key'] = $original_key;

		echo wp_json_encode( $data );
		die;
	}//end bm_check_if_existing_field_key()


	/**
	 * Set Price in Service Calender
	 *
	 * @author Darpan
	 */
	public function bm_set_serice_price() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$exclude    = array(
			'_wpnonce',
			'_wp_http_referer',
			'ajax-nonce',
		);
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$id                = isset( $post['id'] ) ? $post['id'] : '';
			$old_default_price = isset( $post['old_default_price'] ) ? $post['old_default_price'] : '';
			$default_price     = isset( $post['default_price'] ) ? $post['default_price'] : '';
			$price             = isset( $post['price'] ) ? $post['price'] : '';
			$date              = isset( $post['date'] ) ? $post['date'] : '';

			if ( ! empty( $id ) ) {
				if ( ! empty( $price ) && ! empty( $date ) ) {
					if ( ! empty( $old_default_price ) && ! empty( $default_price ) ) {
						if ( $old_default_price != $default_price ) {
							$update_data = array(
								'default_price'       => $default_price,
								'variable_svc_prices' => null,
								'service_updated_at'  => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
							);
							$dbhandler->update_row( 'SERVICE', 'id', $id, $update_data, '', '%d' );
						}
					}

					$service       = $dbhandler->get_row( 'SERVICE', $id );
					$variable_data = ! empty( $service ) && isset( $service->variable_svc_prices ) ? maybe_unserialize( $service->variable_svc_prices ) : array();

					if ( ! empty( $variable_data ) ) {
						$dates = isset( $variable_data['date'] ) ? $variable_data['date'] : array();
						if ( ! empty( $dates ) && in_array( $date, $dates, true ) ) {
							$index = (int) array_search( $date, $dates );
							if ( isset( $service->default_price ) && $price != $service->default_price ) {
								$variable_data['price'][ $index ] = $price;
							} elseif ( count( $variable_data['date'] ) == 1 && count( $variable_data['price'] ) == 1 ) {
									unset( $variable_data['date'] );
									unset( $variable_data['price'] );
							} else {
								unset( $variable_data['date'][ $index ] );
								unset( $variable_data['price'][ $index ] );
							}
						} elseif ( ! empty( $dates ) && isset( $service->default_price ) && $price != $service->default_price ) {
								$date_keys  = array_keys( $dates );
								$last_index = (int) end( $date_keys );

								$variable_data['date'][ ( $last_index + 1 ) ]  = $date;
								$variable_data['price'][ ( $last_index + 1 ) ] = $price;
						}
					} elseif ( isset( $service->default_price ) && $price != $service->default_price ) {
							$variable_data = array(
								'price' => array( '1' => $price ),
								'date'  => array( '1' => $date ),
							);
					} else {
						$variable_data = null;
					} //end if

					$variable_data = array( 'variable_svc_prices' => $variable_data );
					$service_post  = $bmrequests->sanitize_request( $variable_data, 'SERVICE', $exclude );

					if ( $service_post != false ) {
						$service_post['service_updated_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
						$dbhandler->update_row( 'SERVICE', 'id', $id, $service_post, '', '%d' );
						$data['status'] = true;
					}

				} //end if
			} //end if
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_set_serice_price()


	/**
	 * Set Bulk Price in Service Calender
	 *
	 * @author Darpan
	 */
	public function bm_set_bulk_serice_price() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$exclude    = array(
			'_wpnonce',
			'_wp_http_referer',
			'ajax-nonce',
		);
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$id                = isset( $post['id'] ) ? $post['id'] : '';
			$old_default_price = isset( $post['old_default_price'] ) ? $post['old_default_price'] : '';
			$default_price     = isset( $post['default_price'] ) ? $post['default_price'] : '';
			$price             = isset( $post['price'] ) ? $post['price'] : '';
			$from_date         = isset( $post['from_date'] ) ? $post['from_date'] : '';
			$to_date           = isset( $post['to_date'] ) ? $post['to_date'] : '';

			$period = new DatePeriod(
				new DateTime( $from_date ),
				new DateInterval( 'P1D' ),
				new DateTime( $to_date . '+1 day' )
			);

			if ( ! empty( $id ) ) {
				if ( ! empty( $price ) && ! empty( $from_date ) && ! empty( $to_date ) && ! empty( $period ) ) {
					if ( ! empty( $old_default_price ) && ! empty( $default_price ) ) {
						if ( $old_default_price != $default_price ) {
							$update_data = array(
								'default_price'       => $default_price,
								'variable_svc_prices' => null,
								'service_updated_at'  => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
							);
							$dbhandler->update_row( 'SERVICE', 'id', $id, $update_data, '', '%d' );
						}
					}

					$service       = $dbhandler->get_row( 'SERVICE', $id );
					$variable_data = ! empty( $service ) && isset( $service->variable_svc_prices ) ? maybe_unserialize( $service->variable_svc_prices ) : array();

					if ( ! empty( $variable_data ) ) {
						$dates = isset( $variable_data['date'] ) ? $variable_data['date'] : array();
						$i     = ! empty( $dates ) ? ( (int) end( array_keys( $dates ) ) + 1 ) : 0;
						foreach ( $period as $value ) {
							$date = $value->format( 'Y-m-d' );
							if ( ! empty( $dates ) && in_array( $date, $dates, true ) ) {
								$index = (int) array_search( $date, $variable_data['date'] );
								if ( isset( $service->default_price ) && $price != $service->default_price ) {
									$variable_data['price'][ $index ] = $price;
								} elseif ( count( $variable_data['date'] ) == 1 && count( $variable_data['price'] ) == 1 ) {
										unset( $variable_data['date'] );
										unset( $variable_data['price'] );
								} else {
									unset( $variable_data['date'][ $index ] );
									unset( $variable_data['price'][ $index ] );
								}
							} elseif ( isset( $service->default_price ) && $price != $service->default_price ) {
									$variable_data['date'][ $i ]  = $date;
									$variable_data['price'][ $i ] = $price;
									++$i;
							}
						} //end foreach
					} else {
						$i           = 1;
						$price_value = array();
						$date_value  = array();
						if ( $price != $service->default_price ) {
							foreach ( $period as $value ) {
								$price_value[ $i ] = $price;
								$date_value[ $i ]  = $value->format( 'Y-m-d' );
								++$i;
							}

							$variable_data = array(
								'price' => $price_value,
								'date'  => $date_value,
							);
						} else {
							$variable_data = null;
						}
					} //end if

					$variable_data = array( 'variable_svc_prices' => $variable_data );
					$service_post  = $bmrequests->sanitize_request( $variable_data, 'SERVICE', $exclude );

					if ( $service_post != false ) {
						$service_post['service_updated_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
						$dbhandler->update_row( 'SERVICE', 'id', $id, $service_post, '', '%d' );
						$service                = $dbhandler->get_row( 'SERVICE', $id );
						$data['status']         = true;
						$data['default_price']  = ! empty( $service ) && isset( $service->default_price ) ? $service->default_price : 0;
						$data['variable_price'] = ! empty( $service ) && isset( $service->variable_svc_prices ) ? maybe_unserialize( $service->variable_svc_prices ) : array();
					}
				} //end if
			} //end if
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_set_bulk_serice_price()


	/**
	 * Set Price in Service Calender
	 *
	 * @author Darpan
	 */
	public function bm_set_serice_price_module() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$exclude    = array(
			'_wpnonce',
			'_wp_http_referer',
			'ajax-nonce',
		);
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$id                = isset( $post['id'] ) ? $post['id'] : 0;
			$module_id         = isset( $post['module'] ) ? $post['module'] : 0;
			$date              = isset( $post['date'] ) ? $post['date'] : '';
			$old_default_price = isset( $post['old_default_price'] ) ? $post['old_default_price'] : '';
			$default_price     = isset( $post['default_price'] ) ? $post['default_price'] : '';

			if ( ! empty( $id ) ) {
				if ( ! empty( $module_id ) && ! empty( $date ) ) {
					if ( ! empty( $old_default_price ) && ! empty( $default_price ) ) {
						if ( $old_default_price != $default_price ) {
							$update_data = array(
								'default_price'       => $default_price,
								'variable_svc_prices' => null,
								'service_updated_at'  => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
							);
							$dbhandler->update_row( 'SERVICE', 'id', $id, $update_data, '', '%d' );
						}
					}

				} //end if
			} //end if
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_set_serice_price_module()


	/**
	 * Set bulk price module in service calender
	 *
	 * @author Darpan
	 */
	public function bm_set_bulk_serice_price_module() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$exclude    = array(
			'_wpnonce',
			'_wp_http_referer',
			'ajax-nonce',
		);
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$id                = isset( $post['id'] ) ? $post['id'] : 0;
			$module_id         = isset( $post['module'] ) ? $post['module'] : 0;
			$from_date         = isset( $post['from_date'] ) ? $post['from_date'] : '';
			$to_date           = isset( $post['to_date'] ) ? $post['to_date'] : '';
			$old_default_price = isset( $post['old_default_price'] ) ? $post['old_default_price'] : '';
			$default_price     = isset( $post['default_price'] ) ? $post['default_price'] : '';

			$period = new DatePeriod(
				new DateTime( $from_date ),
				new DateInterval( 'P1D' ),
				new DateTime( $to_date . '+1 day' )
			);

			if ( ! empty( $id ) ) {
				if ( ! empty( $module_id ) && ! empty( $from_date ) && ! empty( $to_date ) && ! empty( $period ) ) {
					if ( ! empty( $old_default_price ) && ! empty( $default_price ) ) {
						if ( $old_default_price != $default_price ) {
							$update_data = array(
								'default_price'       => $default_price,
								'variable_svc_prices' => null,
								'service_updated_at'  => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
							);
							$dbhandler->update_row( 'SERVICE', 'id', $id, $update_data, '', '%d' );
						}
					}

				} //end if
			} //end if
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_set_bulk_serice_price_module()


	/**
	 * Set Stopsales in Service Calender
	 *
	 * @author Darpan
	 */
	public function bm_set_serice_stopsales() {
		if ( ! Booking_Management_Limits::can_use_stop_sales() ) {
			wp_send_json_error( esc_html__( 'Stop-sales is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$exclude    = array(
			'_wpnonce',
			'_wp_http_referer',
			'ajax-nonce',
		);
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_set_serice_stopsales()


	/**
	 * Set Saleswitch in Service Calender
	 *
	 * @author Darpan
	 */
	public function bm_set_service_saleswitch() {
		if ( ! Booking_Management_Limits::can_use_saleswitch() ) {
			wp_send_json_error( esc_html__( 'Saleswitch is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$exclude    = array(
			'_wpnonce',
			'_wp_http_referer',
			'ajax-nonce',
		);
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_set_service_saleswitch()


	/**
	 * Set Bulk Stopsales in Service Calender
	 *
	 * @author Darpan
	 */
	public function bm_set_bulk_serice_stopsales() {
		if ( ! Booking_Management_Limits::can_use_stop_sales() ) {
			wp_send_json_error( esc_html__( 'Stop-sales is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$exclude    = array(
			'_wpnonce',
			'_wp_http_referer',
			'ajax-nonce',
		);
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_set_bulk_serice_stopsales()


	/**
	 * Set Bulk Saleswitch in Service Calender
	 *
	 * @author Darpan
	 */
	public function bm_set_bulk_service_saleswitch() {
		if ( ! Booking_Management_Limits::can_use_saleswitch() ) {
			wp_send_json_error( esc_html__( 'Saleswitch is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$exclude    = array(
			'_wpnonce',
			'_wp_http_referer',
			'ajax-nonce',
		);
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_set_bulk_service_saleswitch()


	/**
	 * Set Maximum Capacity in Service Calender
	 *
	 * @author Darpan
	 */
	public function bm_set_serice_max_cap() {
		if ( ! Booking_Management_Limits::can_edit_max_capacity() ) {
			wp_send_json_error( esc_html__( 'Max capacity management is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$exclude    = array(
			'_wpnonce',
			'_wp_http_referer',
			'ajax-nonce',
		);
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$id                  = isset( $post['id'] ) ? $post['id'] : '';
			$old_default_max_cap = isset( $post['old_default_max_cap'] ) ? $post['old_default_max_cap'] : '';
			$default_max_cap     = isset( $post['default_max_cap'] ) ? $post['default_max_cap'] : '';
			$capacity            = isset( $post['capacity'] ) ? $post['capacity'] : '';
			$date                = isset( $post['date'] ) ? $post['date'] : '';
			$time_row            = $dbhandler->get_row( 'TIME', $id );

			if ( ! empty( $id ) ) {
				if ( ! empty( $capacity ) && ! empty( $date ) ) {
					if ( ! empty( $old_default_max_cap ) && ! empty( $default_max_cap ) ) {
						if ( $old_default_max_cap != $default_max_cap ) {
							$update_data = array(
								'default_max_cap'    => $default_max_cap,
								'variable_max_cap'   => null,
								'service_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
							);
							$dbhandler->update_row( 'SERVICE', 'id', $id, $update_data, '', '%d' );

							if ( ! empty( $time_row ) ) {
								$time_slots = isset( $time_row->time_slots ) ? maybe_unserialize( $time_row->time_slots ) : array();
								if ( ! empty( $time_slots ) ) {
									$max_slot_count = isset( $time_slots['max_cap'] ) ? count( $time_slots['max_cap'] ) : 0;

									if ( ! empty( $max_slot_count ) ) {
										for ( $i = 1; $i <= $max_slot_count; $i++ ) {
											$time_slots['max_cap'][ $i ] = $default_max_cap;
										}
									}

									$min_slot_count = isset( $time_slots['min_cap'] ) ? count( $time_slots['min_cap'] ) : 0;

									if ( ! empty( $min_slot_count ) ) {
										for ( $i = 1; $i <= $min_slot_count; $i++ ) {
											if ( $time_slots['min_cap'][ $i ] > $default_max_cap ) {
												$time_slots['min_cap'][ $i ] = 1;
											}
										}
									}

									$update_data = array(
										'time_slots'      => maybe_serialize( $time_slots ),
										'time_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
									);
									$dbhandler->update_row( 'TIME', 'id', $id, $update_data, '', '%d' );
								} //end if
							} //end if
						} //end if
					} //end if

					$service       = $dbhandler->get_row( 'SERVICE', $id );
					$variable_data = ! empty( $service ) && isset( $service->variable_max_cap ) ? maybe_unserialize( $service->variable_max_cap ) : array();

					if ( ! empty( $variable_data ) ) {
						$dates = isset( $variable_data['date'] ) ? $variable_data['date'] : array();
						if ( ! empty( $dates ) && in_array( $date, $dates, true ) ) {
							$index = (int) array_search( $date, $dates );
							if ( isset( $service->default_max_cap ) && $capacity != $service->default_max_cap ) {
								$variable_data['capacity'][ $index ] = $capacity;
							} elseif ( count( $variable_data['date'] ) == 1 && count( $variable_data['capacity'] ) == 1 ) {
									unset( $variable_data['date'] );
									unset( $variable_data['capacity'] );
							} else {
								unset( $variable_data['date'][ $index ] );
								unset( $variable_data['capacity'][ $index ] );
							}
						} elseif ( ! empty( $dates ) && isset( $service->default_max_cap ) && $capacity != $service->default_max_cap ) {
								$last_index                                       = (int) end( array_keys( $dates ) );
								$variable_data['date'][ ( $last_index + 1 ) ]     = $date;
								$variable_data['capacity'][ ( $last_index + 1 ) ] = $capacity;
						}
					} elseif ( $capacity != $service->default_max_cap ) {
							$variable_data = array(
								'capacity' => array( '1' => $capacity ),
								'date'     => array( '1' => $date ),
							);
					} else {
						$variable_data = null;
					} //end if

					$variable_data = array( 'variable_max_cap' => $variable_data );
					$service_post  = $bmrequests->sanitize_request( $variable_data, 'SERVICE', $exclude );

					if ( $service_post != false ) {
						$service_post['service_updated_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
						$dbhandler->update_row( 'SERVICE', 'id', $id, $service_post, '', '%d' );
						$service                  = $dbhandler->get_row( 'SERVICE', $id );
						$data['status']           = true;
						$data['default_max_cap']  = ! empty( $service ) && isset( $service->default_max_cap ) ? $service->default_max_cap : 0;
						$data['variable_max_cap'] = ! empty( $service ) && isset( $service->variable_max_cap ) ? maybe_unserialize( $service->variable_max_cap ) : array();
					}
				} //end if
			} //end if
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_set_serice_max_cap()


	/**
	 * Set Bulk Maximum Capacity in Service Calender
	 *
	 * @author Darpan
	 */
	public function bm_set_bulk_serice_max_cap() {
		if ( ! Booking_Management_Limits::can_edit_max_capacity() ) {
			wp_send_json_error( esc_html__( 'Max capacity management is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$exclude    = array(
			'_wpnonce',
			'_wp_http_referer',
			'ajax-nonce',
		);
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$id                  = isset( $post['id'] ) ? $post['id'] : '';
			$old_default_max_cap = isset( $post['old_default_max_cap'] ) ? $post['old_default_max_cap'] : '';
			$default_max_cap     = isset( $post['default_max_cap'] ) ? $post['default_max_cap'] : '';
			$capacity            = isset( $post['capacity'] ) ? $post['capacity'] : '';
			$from_date           = isset( $post['from_date'] ) ? $post['from_date'] : '';
			$to_date             = isset( $post['to_date'] ) ? $post['to_date'] : '';
			$time_row            = $dbhandler->get_row( 'TIME', $id );

			$period = new DatePeriod(
				new DateTime( $from_date ),
				new DateInterval( 'P1D' ),
				new DateTime( $to_date . '+1 day' )
			);

			if ( ! empty( $id ) ) {
				if ( ! empty( $capacity ) && ! empty( $from_date ) && ! empty( $to_date ) && ! empty( $period ) ) {
					if ( ! empty( $old_default_max_cap ) && ! empty( $default_max_cap ) ) {
						if ( $old_default_max_cap != $default_max_cap ) {
							$update_data = array(
								'default_max_cap'    => $default_max_cap,
								'variable_max_cap'   => null,
								'service_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
							);
							$dbhandler->update_row( 'SERVICE', 'id', $id, $update_data, '', '%d' );
						}

						if ( ! empty( $time_row ) ) {
							$time_slots = isset( $time_row->time_slots ) ? maybe_unserialize( $time_row->time_slots ) : array();
							if ( ! empty( $time_slots ) ) {
								$max_slot_count = isset( $time_slots['max_cap'] ) ? count( $time_slots['max_cap'] ) : 0;

								if ( ! empty( $max_slot_count ) ) {
									for ( $i = 1; $i <= $max_slot_count; $i++ ) {
										$time_slots['max_cap'][ $i ] = $default_max_cap;
									}
								}

								$min_slot_count = isset( $time_slots['min_cap'] ) ? count( $time_slots['min_cap'] ) : 0;

								if ( ! empty( $min_slot_count ) ) {
									for ( $i = 1; $i <= $min_slot_count; $i++ ) {
										if ( $time_slots['min_cap'][ $i ] > $default_max_cap ) {
											$time_slots['min_cap'][ $i ] = 1;
										}
									}
								}

								$update_data = array(
									'time_slots'      => maybe_serialize( $time_slots ),
									'time_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
								);
								$dbhandler->update_row( 'TIME', 'id', $id, $update_data, '', '%d' );
							} //end if
						} //end if
					} //end if

					$service       = $dbhandler->get_row( 'SERVICE', $id );
					$variable_data = ! empty( $service ) && isset( $service->variable_max_cap ) ? maybe_unserialize( $service->variable_max_cap ) : array();

					if ( ! empty( $variable_data ) ) {
						$dates = isset( $variable_data['date'] ) ? $variable_data['date'] : array();
						$i     = ( (int) end( array_keys( $dates ) ) + 1 );
						foreach ( $period as $value ) {
							$date = $value->format( 'Y-m-d' );
							if ( ! empty( $dates ) && in_array( $date, $dates, true ) ) {
								$index = (int) array_search( $date, $variable_data['date'] );
								if ( isset( $service->default_max_cap ) && $capacity != $service->default_max_cap ) {
									$variable_data['capacity'][ $index ] = $capacity;
								} elseif ( count( $variable_data['date'] ) == 1 && count( $variable_data['capacity'] ) == 1 ) {
										unset( $variable_data['date'] );
										unset( $variable_data['capacity'] );
								} else {
									unset( $variable_data['date'][ $index ] );
									unset( $variable_data['capacity'][ $index ] );
								}
							} elseif ( isset( $service->default_max_cap ) && $capacity != $service->default_max_cap ) {
									$variable_data['date'][ $i ]     = $date;
									$variable_data['capacity'][ $i ] = $capacity;
									++$i;
							}
						} //end foreach
					} else {
						$i              = 1;
						$capacity_value = array();
						$date_value     = array();
						if ( isset( $service->default_max_cap ) && $capacity != $service->default_max_cap ) {
							foreach ( $period as $value ) {
								$capacity_value[ $i ] = $capacity;
								$date_value[ $i ]     = $value->format( 'Y-m-d' );
								++$i;
							}

							$variable_data = array(
								'capacity' => $capacity_value,
								'date'     => $date_value,
							);
						} else {
							$variable_data = null;
						}
					} //end if

					$variable_data = array( 'variable_max_cap' => $variable_data );
					$service_post  = $bmrequests->sanitize_request( $variable_data, 'SERVICE', $exclude );

					if ( $service_post != false ) {
						$service_post['service_updated_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
						$dbhandler->update_row( 'SERVICE', 'id', $id, $service_post, '', '%d' );
						$service                  = $dbhandler->get_row( 'SERVICE', $id );
						$data['status']           = true;
						$data['default_max_cap']  = ! empty( $service ) && isset( $service->default_max_cap ) ? $service->default_max_cap : 0;
						$data['variable_max_cap'] = ! empty( $service ) && isset( $service->variable_max_cap ) ? maybe_unserialize( $service->variable_max_cap ) : array();
					}
				} //end if
			} //end if
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_set_bulk_serice_max_cap()


	/**
	 * Set time slot in Service Calender
	 *
	 * @author Darpan
	 */
	public function bm_set_variable_time_slot() {
		if ( ! Booking_Management_Limits::is_pro_active() ) {
			wp_send_json_error( esc_html__( 'Variable time slots is a Pro feature.', 'service-booking' ) );
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$exclude    = array(
			'_wpnonce',
			'_wp_http_referer',
			'ajax-nonce',
		);
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$id                   = isset( $post['id'] ) ? $post['id'] : '';
			$old_total_time_slots = isset( $post['old_total_time_slots'] ) ? $post['old_total_time_slots'] : '';
			$service_duration     = isset( $post['service_duration'] ) ? $post['service_duration'] : '';
			$service_operation    = isset( $post['service_operation'] ) ? $post['service_operation'] : '';
			$old_default_max_cap  = isset( $post['old_default_max_cap'] ) ? $post['old_default_max_cap'] : '';
			$default_max_cap      = isset( $post['default_max_cap'] ) ? $post['default_max_cap'] : '';
			$total_time_slots     = isset( $post['total_time_slots'] ) ? $post['total_time_slots'] : '';
			$default_slot_data    = isset( $post['default_time_slots'] ) ? $post['default_time_slots'] : '';
			$slot_data            = isset( $post['time_slots_data'] ) ? $post['time_slots_data'] : '';
			$date                 = isset( $post['date'] ) ? $post['date'] : '';

			if ( ! empty( $id ) ) {
				if ( ! empty( $old_default_max_cap ) && ! empty( $default_max_cap ) && $old_default_max_cap != $default_max_cap ) {
					$cap_data = array(
						'default_max_cap'    => $default_max_cap,
						'service_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
					);

					$time_row = $dbhandler->get_row( 'TIME', $id );

					if ( ! empty( $time_row ) ) {
						$time_slots = isset( $time_row->time_slots ) ? maybe_unserialize( $time_row->time_slots ) : array();

						$max_cap_count = isset( $time_slots['max_cap'] ) ? count( $time_slots['max_cap'] ) : 0;

						if ( ! empty( $max_cap_count ) ) {
							for ( $i = 1; $i <= $max_cap_count; $i++ ) {
								$time_slots['max_cap'][ $i ] = $default_max_cap;
							}
						}

						$min_cap_count = isset( $time_slots['min_cap'] ) ? count( $time_slots['min_cap'] ) : 0;

						if ( ! empty( $min_cap_count ) ) {
							for ( $i = 1; $i <= $min_cap_count; $i++ ) {
								if ( $time_slots['min_cap'][ $i > $default_max_cap ] ) {
									$time_slots['min_cap'][ $i ] = 1;
								}
							}
						}

						$time_data = array(
							'time_slots'      => maybe_serialize( $time_slots ),
							'time_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
						);

						$dbhandler->update_row( 'TIME', 'service_id', $id, $time_data, '', '%d' );
					} //end if

					$dbhandler->update_row( 'SERVICE', 'id', $id, $cap_data, '', '%d' );
				} //end if

				if ( ! empty( $slot_data ) && ! empty( $date ) ) {
					if ( ! empty( $old_total_time_slots ) && ! empty( $total_time_slots ) && $old_total_time_slots != $total_time_slots ) {
						$update_data = array(
							'service_duration'    => $service_duration,
							'service_operation'   => $service_operation,
							'default_max_cap'     => $default_max_cap,
							'variable_time_slots' => null,
							'service_updated_at'  => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
						);

						$time_row = $dbhandler->get_row( 'TIME', $id );

						if ( ! empty( $time_row ) ) {
							$dbhandler->remove_row( 'TIME', 'service_id', $id, '%d' );

							if ( ! empty( $default_slot_data ) ) {
								$auto_time = array( 'auto_time' => $default_slot_data['autoselect_time'] );
								unset( $default_slot_data['autoselect_time'] );

								$add_time_data = array(
									'service_id'   => $id,
									'total_slots'  => $total_time_slots,
									'time_slots'   => $default_slot_data,
									'time_options' => $auto_time,
								);

								$time_post = $bmrequests->sanitize_request( $add_time_data, 'TIME' );

								if ( $time_post != false ) {
									$time_post['time_created_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
									$time_id                      = $dbhandler->insert_row( 'TIME', $time_post );
								}
							}
						} //end if

						$dbhandler->update_row( 'SERVICE', 'id', $id, $update_data, '', '%d' );
					} //end if

					$service            = $dbhandler->get_row( 'SERVICE', $id );
					$variable_slot_data = ! empty( $service ) && isset( $service->variable_time_slots ) ? maybe_unserialize( $service->variable_time_slots ) : array();

					if ( ! empty( $variable_slot_data ) ) {
						$dates = wp_list_pluck( $variable_slot_data, 'date' );
						if ( ! empty( $dates ) && in_array( $date, $dates, true ) ) {
							$index                        = (int) array_search( $date, $dates );
							$slot_data[ $index ]['date']  = $date;
							$variable_slot_data[ $index ] = $slot_data[ $index ];
						} elseif ( ! empty( $dates ) ) {
								$max_index                                = (int) max( array_keys( $dates ) );
								$slot_data[ ( $max_index + 1 ) ]['date']  = $date;
								$variable_slot_data[ ( $max_index + 1 ) ] = $slot_data[ ( $max_index + 1 ) ];
						}
					} else {
						$slot_data[1]['date']  = $date;
						$variable_slot_data[1] = $slot_data[1];
					}

					$variable_slot_data = array( 'variable_time_slots' => $variable_slot_data );
					$service_post       = $bmrequests->sanitize_request( $variable_slot_data, 'SERVICE', $exclude );

					if ( $service_post != false ) {
						$dbhandler->update_row( 'SERVICE', 'id', $id, $service_post, '', '%d' );
						$service                    = $dbhandler->get_row( 'SERVICE', $id );
						$data['status']             = true;
						$data['default_max_cap']    = ! empty( $service ) && isset( $service->default_max_cap ) ? $service->default_max_cap : 0;
						$data['variable_slot_data'] = ! empty( $service ) && isset( $service->variable_time_slots ) ? maybe_unserialize( $service->variable_time_slots ) : array();
						$data['total_time_slots']   = $total_time_slots;
					}
				} //end if
			} //end if
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_set_variable_time_slot()


	/**
	 * Save field type and setting
	 *
	 * @author Darpan
	 */
	public function bm_save_field_and_setting() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$identifier = 'FIELDS';
		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();

		$exclude  = array(
			'_wpnonce',
			'_wp_http_referer',
			'ajax-nonce',
		);
		$post     = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$response = array();

		if ( $post !== false && $post !== null ) {
			$id   = isset( $post['id'] ) ? $post['id'] : '';
			$data = isset( $post['formdata'] ) ? $post['formdata'] : array();

			$common_data = isset( $data['common_data'] ) ? $data['common_data'] : array();
			$conditional = isset( $data['conditional'] ) ? $data['conditional'] : array();

			// Support direct format: post[common_data] and post[conditional].
			if ( empty( $common_data ) && isset( $post['common_data'] ) ) {
				$common_data = $post['common_data'];
			}
			if ( empty( $conditional ) && isset( $post['conditional'] ) ) {
				$conditional = $post['conditional'];
			}
			if ( empty( $id ) && isset( $common_data['id'] ) ) {
				$id = $common_data['id'];
			}

			if ( ! empty( $common_data ) && ! empty( $conditional ) ) {
				$type = isset( $common_data['field_type'] ) ? $common_data['field_type'] : '';

				// Enforce field-type restrictions for the free version.
				if ( $id == 0 && ! Booking_Management_Limits::can_add_basic_field( $type ) ) {
					$response = array(
						'status'  => 'error',
						'message' => esc_html( Booking_Management_Limits::get_limit_message( 'custom_fields' ) ),
					);
					echo wp_json_encode( $response );
					die;
				}

				! isset( $common_data['is_required'] ) ? $common_data['is_required'] = 0 : $common_data['is_required'] = 1;
				! isset( $common_data['is_editable'] ) ? $common_data['is_editable'] = 0 : $common_data['is_editable'] = 1;

				if ( isset( $common_data['field_name'] ) ) {
					$common_data['field_name'] = $bmrequests->bm_create_slug( $common_data['field_name'], '_' );
				}

				if ( $common_data['field_key'] == '' ) {
					if ( $bmrequests->bm_fetch_default_key_type( $type ) ) {
						$common_data['field_key'] = $bmrequests->bm_fetch_field_key( $common_data['ordering'] );
					}
				}

				if ( ! isset( $conditional['field_options'] ) || ! is_array( $conditional['field_options'] ) ) {
					$conditional['field_options'] = array();
				}

				if ( $type == 'email' ) {
					$conditional['field_options']['is_main_email'] = ! isset( $conditional['field_options']['is_main_email'] ) ? 0 : 1;
				}

				if ( $type == 'select' || $type == 'checkbox' ) {
					$conditional['field_options']['is_multiple'] = ! isset( $conditional['field_options']['is_multiple'] ) ? 0 : 1;
				}

				if ( $type == 'tel' ) {
					$conditional['field_options']['show_intl_code'] = ! isset( $conditional['field_options']['show_intl_code'] ) ? 0 : 1;
				}

				if ( $type != 'file' && $type != 'checkbox' && $type != 'radio' && $type != 'reset' && $type != 'button' && $type != 'submit' && $type != 'hidden' && $type != 'color' && $type != 'range' ) {
					$conditional['field_options']['autocomplete'] = ( ! empty( $conditional['autocomplete'] ) || ! empty( $conditional['field_options']['autocomplete'] ) ) ? 1 : 0;
				}

				if ( $type != 'button' && $type != 'submit' && $type != 'hidden' ) {
					$conditional['field_options']['is_visible'] = ( ! empty( $conditional['is_visible'] ) || ! empty( $conditional['field_options']['is_visible'] ) ) ? 1 : 0;
				}

				// Move non-column keys from conditional into field_options to
				// prevent "Unknown column" database errors on insert/update.
				$db_columns = array( 'id', 'form_id', 'field_type', 'field_label', 'field_name', 'field_desc', 'field_options', 'is_required', 'is_editable', 'visible', 'ordering', 'woocommerce_field', 'field_key', 'field_position' );
				foreach ( array_keys( $conditional ) as $key ) {
					if ( 'field_options' !== $key && ! in_array( $key, $db_columns, true ) ) {
						$conditional['field_options'][ $key ] = $conditional[ $key ];
						unset( $conditional[ $key ] );
					}
				}

				$data      = array_merge( $common_data, $conditional );
				$finaldata = $bmrequests->sanitize_request( $data, $identifier, $exclude );

				if ( ( $finaldata != false && $finaldata != null ) ) {
					if ( $id == 0 ) {
						$field_id = $dbhandler->insert_row( $identifier, $finaldata );

						if ( isset( $field_id ) ) {
							$response = array(
								'status'     => 'saved',
								'data'       => $dbhandler->get_row( $identifier, $field_id ),
								'is_default' => $bmrequests->bm_check_is_default_field( $field_id ),
							);

							if ( $dbhandler->get_global_option_value( 'bm_booking_form_fields_created', '0' ) == '0' ) {
								$dbhandler->update_global_option_value( 'bm_booking_form_fields_created', '1' );
							}
						}
					} else {
						$dbhandler->update_row( $identifier, 'id', $id, $finaldata, '', '%d' );

						$response = array(
							'status' => 'updated',
							'data'   => $dbhandler->get_row( $identifier, $id ),
						);
					}
				}
			} //end if
		} //end if

		echo wp_json_encode( $response );
		die;
	}//end bm_save_field_and_setting()


	/**
	 * Fetch preview form for fields
	 *
	 * @author Darpan
	 */
	public function bm_fetch_preview_form() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( esc_html__( 'Failed security check', 'service-booking' ) );
			return;
		}

		$bmrequests      = new BM_Request();
		$resp            = '';
		$no_results_text = __( 'No Results Found', 'service-booking' );

		$resp = $bmrequests->bm_fetch_fields();

		if ( empty( $resp ) ) {
			$resp .= '<p style="text-align: center;">';
			$resp .= $no_results_text;
			$resp .= '</p>';
		}

		wp_send_json(
			array(
				'success' => true,
				'html'    => wp_kses( $resp, $bmrequests->bm_fetch_expanded_allowed_tags() ),
			)
		);
	}//end bm_fetch_preview_form()


	/**
	 * Test SMTP connection
	 *
	 * @author Darpan
	 */
	public function bm_check_smtp_connection() {
		if ( ! Booking_Management_Limits::is_pro_active() ) {
			wp_send_json_error( 'Pro feature' );
			return;
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$bm_mail    = new BM_Email();
		$identifier = 'GLOBAL';
		$exclude    = array(
			'_wpnonce',
			'_wp_http_referer',
			'save_email_global',
		);
		$post       = $bmrequests->sanitize_request( $_POST, $identifier, $exclude );

		if ( $post != false && $post != null ) {
			if ( isset( $post['bm_smtp_password'] ) && $post['bm_smtp_password'] != '' ) {
				$post['bm_smtp_password'] = $post['bm_smtp_password'];
			} else {
				unset( $post['bm_smtp_password'] );
			}

			foreach ( $post as $key => $value ) {
				$dbhandler->update_global_option_value( $key, $value );
			}
		}

		$dbhandler->update_global_option_value( 'bm_enable_smtp', 1 );
		$to                 = $dbhandler->get_global_option_value( 'bm_smtp_test_email_address' );
		$from_email_address = $bm_mail->bm_get_from_email();
		$headers            = "MIME-Version: 1.0\r\n";
		$headers           .= "Content-type:text/html;charset=UTF-8\r\n";
		$headers           .= "From:$from_email_address\r\n";
		echo esc_html( wp_mail( $to, 'Test SMTP Connection', 'Test', $headers ) );
		die;
	}//end bm_check_smtp_connection()


    /**
     * Email field list
     *
     * @author Darpan
     */
    public function bm_fields_list_for_email( $editor_id ) {
         $dbhandler = new BM_DBhandler();
        $bmrequests = new BM_Request();
        $exclude    = "field_type not in('file','password', 'button', 'hidden', 'submit', 'reset', 'search', 'textarea')";
        $fields     = $dbhandler->get_all_result( 'FIELDS', '*', 1, 'results', 0, false, 'field_position', false, $exclude );

		echo '<select name="bm_field_list" class="bm_field_list" onchange="bm_insert_field_in_email(this.value)">';
		echo '<option value="">' . esc_html__( 'Choose Fields', 'service-booking' ) . '</option>';

		echo '<optgroup label="' . esc_html__( 'Admin Fields', 'service-booking' ) . '" >';
		echo '<option value="{{admin_email}}">' . esc_html__( 'Admin Email', 'service-booking' ) . '</option>';
		echo '<option value="{{admin_name}}">' . esc_html__( 'Admin Name', 'service-booking' ) . '</option>';
		echo '</optgroup>';

		if ( isset( $fields ) && ! empty( $fields ) ) {
			echo '<optgroup label="' . esc_html__( 'Booking Form Fields', 'service-booking' ) . '">';
			foreach ( $fields as $field ) {
				if ( $bmrequests->bm_check_if_field_is_visible( $field->id ) == 1 ) {
					echo '<option value="{{' . esc_attr( $field->field_name ) . '}}">' . esc_html( $field->field_label ) . '</option>';
				}
			}

			echo '</optgroup>';
		}

		echo '<optgroup label="' . esc_html__( 'Order Related Fields', 'service-booking' ) . '" >';
		echo '<option value="{{booking_key}}">' . esc_html__( 'Order Reference', 'service-booking' ) . '</option>';
		echo '<option value="{{booking_date}}">' . esc_html__( 'Service Date', 'service-booking' ) . '</option>';
		echo '<option value="{{booking_created_at}}">' . esc_html__( 'Booked On', 'service-booking' ) . '</option>';
		echo '<option value="{{service_name}}">' . esc_html__( 'Service Name', 'service-booking' ) . '</option>';
		echo '<option value="{{booking_slots}}">' . esc_html__( 'Booked Slots', 'service-booking' ) . '</option>';
		echo '<option value="{{service_duration}}">' . esc_html__( 'Service Duration', 'service-booking' ) . '</option>';
		echo '<option value="{{total_svc_slots}}">' . esc_html__( 'Total Service slots', 'service-booking' ) . '</option>';
		echo '<option value="{{total_ext_svc_slots}}">' . esc_html__( 'Total Extra Service slots', 'service-booking' ) . '</option>';
		echo '<option value="{{total_cost}}">' . esc_html__( 'Order Total Cost', 'service-booking' ) . '</option>';
		echo '<option value="{{base_svc_price}}">' . esc_html__( 'Service Base Price', 'service-booking' ) . '</option>';
		echo '<option value="{{service_cost}}">' . esc_html__( 'Service Total Price', 'service-booking' ) . '</option>';
		echo '<option value="{{disount_amount}}">' . esc_html__( 'Discount', 'service-booking' ) . '</option>';
		echo '<option value="{{subtotal}}">' . esc_html__( 'Subtotal', 'service-booking' ) . '</option>';
		echo '<option value="{{extra_services}}">' . esc_html__( 'Extra Services', 'service-booking' ) . '</option>';
		/**echo '<option value="{{coupon_id}}">' . esc_html__( 'Coupon Id', 'service-booking' ) . '</option>';
		echo '<option value="{{wc_coupon_id}}">' . esc_html__( 'Coupon Id', 'service-booking' ) . '</option>';*/
        echo '</optgroup>';

        $language = $dbhandler->get_global_option_value( 'bm_flexi_current_language', 'en' );

        if ( in_array( $editor_id, array( "booking_pdf_$language", "voucher_pdf_$language", "customer_info_pdf_$language" ) ) ) {
            echo '<optgroup label="' . esc_html__( 'PDF Related Fields', 'service-booking' ) . '" >';
            echo '<option value="{{service_qty}}">' . esc_html__( 'Service Quantity', 'service-booking' ) . '</option>';
            echo '<option value="{{infant_count}}">' . esc_html__( 'Infant Count', 'service-booking' ) . '</option>';
            echo '<option value="{{infant_discount}}">' . esc_html__( 'Infant Discount', 'service-booking' ) . '</option>';
            echo '<option value="{{child_count}}">' . esc_html__( 'Child Count', 'service-booking' ) . '</option>';
            echo '<option value="{{child_discount}}">' . esc_html__( 'Child Discount', 'service-booking' ) . '</option>';
            echo '<option value="{{adult_count}}">' . esc_html__( 'Adult Count', 'service-booking' ) . '</option>';
            echo '<option value="{{adult_discount}}">' . esc_html__( 'Adult Discount', 'service-booking' ) . '</option>';
            echo '<option value="{{senior_count}}">' . esc_html__( 'Senior Count', 'service-booking' ) . '</option>';
            echo '<option value="{{senior_discount}}">' . esc_html__( 'Senior Discount', 'service-booking' ) . '</option>';
            echo '<option value="{{date_time}}">' . esc_html__( 'Date Time', 'service-booking' ) . '</option>';
            echo '<option value="{{date}}">' . esc_html__( 'Date', 'service-booking' ) . '</option>';
            echo '<option value="{{time}}">' . esc_html__( 'Time', 'service-booking' ) . '</option>';
            echo '<option value="{{current_year}}">' . esc_html__( 'Current Year', 'service-booking' ) . '</option>';
            echo '<option value="{{admin_phone}}">' . esc_html__( 'Admin Phone', 'service-booking' ) . '</option>';
            echo '<option value="{{redeemed_date}}">' . esc_html__( 'Redeemed Date', 'service-booking' ) . '</option>';
            echo '<option value="{{qr_code}}">' . esc_html__( 'QR Code', 'service-booking' ) . '</option>';
            echo '<option value="{{logo}}">' . esc_html__( 'Logo', 'service-booking' ) . '</option>';
            echo '<option value="{{logo_url}}">' . esc_html__( 'Logo url', 'service-booking' ) . '</option>';
            echo '<option value="{{customer_since}}">' . esc_html__( 'Customer Since', 'service-booking' ) . '</option>';
            echo '<option value="{{total_bookings}}">' . esc_html__( 'Total Bookings', 'service-booking' ) . '</option>';
        }

		echo '<optgroup label="' . esc_html__( 'Voucher Fields', 'service-booking' ) . '" >';
		echo '<option value="{{recipient_first_name}}">' . esc_html__( 'Recipient First Name', 'service-booking' ) . '</option>';
		echo '<option value="{{recipient_last_name}}">' . esc_html__( 'Recipient Last Name', 'service-booking' ) . '</option>';
		echo '<option value="{{voucher_code}}">' . esc_html__( 'Voucher Code', 'service-booking' ) . '</option>';
		echo '<option value="{{voucher_expiry_date}}">' . esc_html__( 'Voucher Expiry Date', 'service-booking' ) . '</option>';
		echo '<option value="{{voucher_redeem_page_url}}">' . esc_html__( 'Voucher Redeem Page URL', 'service-booking' ) . '</option>';
		echo '</optgroup>';

		echo '<optgroup label="' . esc_html__( 'Other Fields', 'service-booking' ) . '" >';
		echo '<option value="{{from_name}}">' . esc_html__( 'From Name', 'service-booking' ) . '</option>';
		echo '<option value="{{from_mail}}">' . esc_html__( 'From Mail', 'service-booking' ) . '</option>';
		echo '</optgroup>';

		echo '</select>';
	}//end bm_fields_list_for_email()


	/**
	 * Add email attachement
	 *
	 * @author Darpan
	 */
	public function bm_add_mail_attachment() {
		echo '<form id="email_attachment_form" enctype="multipart/form-data">';
		echo '<label for="email_attachment" class="custom-email-attachement">';
		echo esc_html__( 'Add attachment', 'service-booking' );
		echo '</label>';
		echo '<input id="email_attachment" name="email_attachment[]" type="file" multiple class="hidden" onclick="this.value = null"/>';
		echo '</div>';
		echo '<div id="fileList" style="display: none;"></div>';
		echo '<div class="progress" style="display: none;">';
		echo '<div class="progress-bar" role="progressbar" style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">' . esc_html( '0%' ) . '</div>';
		echo '<input type="hidden" id="resend_email_attachment" value="">';
		echo '<input type="hidden" id="final_files" value="">';
		echo '</form>';
	}//end bm_add_mail_attachment()


	/**
	 * Fetch timezone
	 *
	 * @author Darpan
	 */
	public function bm_fetch_timezone() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$bmrequests   = new BM_Request();
		$country_code = filter_input( INPUT_POST, 'country_code' );
		$data         = array( 'status' => false );

		if ( $country_code != false && $country_code != null ) {
			$timezones = $bmrequests->bm_fetch_timezones( $country_code );

			if ( ! empty( $timezones ) ) {
				$data['status']    = true;
				$data['timezones'] = $timezones;
			}
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_timezone()


	/**
	 * Customer details by order id
	 *
	 * @author Darpan
	 */
	public function bm_fetch_customer_data_for_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler     = new BM_DBhandler();
		$bmrequests    = new BM_Request();
		$order_id      = filter_input( INPUT_POST, 'order_id', FILTER_VALIDATE_INT );
		$data          = array( 'status' => false );
		$customer_data = array();

		if ( $order_id != false && $order_id != null ) {
			$order = $dbhandler->get_row( 'BOOKING', $order_id, 'id' );

			if ( ! empty( $order ) && isset( $order->id ) ) {
				$customer_data = $bmrequests->get_customer_info_for_order( $order->id );

				$data['status']        = true;
				$data['customer_info'] = $customer_data;
			}
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_customer_data_for_order()


	/**
	 * Customer details by order id
	 *
	 * @author Darpan
	 */
	public function bm_fetch_customer_data_for_failed_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler         = new BM_DBhandler();
		$bmrequests        = new BM_Request();
		$failed_booking_id = filter_input( INPUT_POST, 'order_id', FILTER_VALIDATE_INT );
		$data              = array( 'status' => false );

		if ( $failed_booking_id != false && $failed_booking_id != null ) {
			$customer_data = $bmrequests->get_customer_info_for_failed_order( $failed_booking_id );

			$data['status']        = true;
			$data['customer_info'] = $customer_data;
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_customer_data_for_failed_order()


	/**
	 * Customer details by order id
	 *
	 * @author Darpan
	 */
	public function bm_fetch_customer_data_for_archived_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler           = new BM_DBhandler();
		$bmrequests          = new BM_Request();
		$archived_booking_id = filter_input( INPUT_POST, 'order_id', FILTER_VALIDATE_INT );
		$data                = array( 'status' => false );

		if ( $archived_booking_id != false && $archived_booking_id != null ) {
			$customer_data = $bmrequests->get_customer_info_for_archived_order( $archived_booking_id );

			$data['status']        = true;
			$data['customer_info'] = $customer_data;
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_customer_data_for_archived_order()


	/**
	 * Attachments by order id
	 *
	 * @author Darpan
	 */
	public function bm_fetch_attachments_for_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler   = new BM_DBhandler();
		$bmrequests  = new BM_Request();
		$order_id    = filter_input( INPUT_POST, 'order_id', FILTER_VALIDATE_INT );
		$data        = array( 'status' => false );
		$attachments = array();

		if ( $order_id != false && $order_id != null ) {
			$attachments    = $bmrequests->bm_fetch_order_attachments( $order_id );
			$data['status'] = true;
		}

		$data['attachments'] = $attachments;

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_attachments_for_order()


	/**
	 * Attachments by order id
	 *
	 * @author Darpan
	 */
	public function bm_fetch_attachments_for_archived_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler   = new BM_DBhandler();
		$bmrequests  = new BM_Request();
		$order_id    = filter_input( INPUT_POST, 'order_id', FILTER_VALIDATE_INT );
		$data        = array( 'status' => false );
		$attachments = array();

		if ( $order_id != false && $order_id != null ) {
			$attachments    = $bmrequests->bm_fetch_archived_order_attachments( $order_id );
			$data['status'] = true;
		}

		$data['attachments'] = $attachments;

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_attachments_for_archived_order()


	/**
	 * Attachments by order id
	 *
	 * @author Darpan
	 */
	public function bm_fetch_attachments_for_failed_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler       = new BM_DBhandler();
		$bmrequests      = new BM_Request();
		$failed_order_id = filter_input( INPUT_POST, 'order_id', FILTER_VALIDATE_INT );
		$data            = array( 'status' => false );
		$attachments     = array();

		if ( $failed_order_id != false && $failed_order_id != null ) {
			$attachments    = $bmrequests->bm_fetch_failed_order_attachments( $failed_order_id );
			$data['status'] = true;
		}

		$data['attachments'] = $attachments;

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_attachments_for_failed_order()


	/**
	 * Services by category id
	 *
	 * @author Darpan
	 */
	public function bm_fetch_services_by_category_id() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$bmrequests  = new BM_Request();
		$category_id = filter_input( INPUT_POST, 'category_id', FILTER_VALIDATE_INT );
		$data        = array( 'status' => false );

		if ( $category_id != false && $category_id != null ) {
			$services = $bmrequests->bm_fetch_services_by_category_id( $category_id, 'booking' );

			$data['status']   = true;
			$data['services'] = $services;
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_services_by_category_id()


	/**
	 * Fetch bookable Services by category id
	 *
	 * @author Darpan
	 */
	public function bm_fetch_bookable_services_by_category_id_and_date() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$services = $bmrequests->bm_fetch_bookable_services_by_date_and_category_id( $post );

			$data['status']   = true;
			$data['services'] = $services;
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_bookable_services_by_category_id_and_date()


	/**
	 * Product details by order id
	 *
	 * @author Darpan
	 */
	public function bm_fetch_ordered_product_details() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler    = new BM_DBhandler();
		$bmrequests   = new BM_Request();
		$order_id     = filter_input( INPUT_POST, 'order_id', FILTER_VALIDATE_INT );
		$data         = array( 'status' => false );
		$product_data = array();

		if ( $order_id != false && $order_id != null ) {
			$order = $dbhandler->get_row( 'BOOKING', $order_id, 'id' );

			if ( ! empty( $order ) ) {
				$category_id                = $bmrequests->bm_fetch_category_id_by_service_id( $order->service_id );
				$product_data['service']    = $bmrequests->bm_fetch_non_woocmmerce_booked_service_info( $order->id );
				$product_data['services']   = $bmrequests->bm_fetch_services_by_category_id( $category_id );
				$product_data['categories'] = $dbhandler->get_all_result( 'CATEGORY', '*', 1, 'results', 0, false, 'cat_position', false );
				$product_data['extras']     = $bmrequests->get_booked_extra_products_info( $order->id );
			}

			$data['status']   = true;
			$data['products'] = $product_data;
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_ordered_product_details()


	/**
	 * Service details by order id
	 *
	 * @author Darpan
	 */
	public function bm_fetch_ordered_service_details() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$bmrequests = new BM_Request();
		$order_id   = filter_input( INPUT_POST, 'order_id', FILTER_VALIDATE_INT );
		$resp       = '';

		if ( $order_id != false && $order_id != null ) {
			$resp = $bmrequests->bm_fetch_ordered_service_details( $order_id );
		}

		echo wp_kses( $resp, $bmrequests->bm_fetch_expanded_allowed_tags() );
		die;
	}//end bm_fetch_ordered_service_details()


    /**
     * View pdf content
     *
     * @author Darpan
     */
    public function bm_view_pdf_content() {
        $nonce = filter_input( INPUT_POST, 'nonce' );
        if ( !$nonce || !wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
            wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
            return;
        }

        $type = sanitize_text_field( filter_input( INPUT_POST, 'type' ) ?? 'booking' );

        if ( ! class_exists( 'BM_PDF_Processor' ) ) {
            wp_send_json_error( __( 'PDF processing is not available.', 'service-booking' ) );
            return;
        }

        $pdf_processor = new BM_PDF_Processor();
        $html          = $pdf_processor->bm_get_template_pdf_content( $type, 'dummy' );

        if ( empty( $html ) ) {
            wp_send_json_error( __( 'No content found for this template.', 'service-booking' ) );
            return;
        }

        wp_send_json_success( $html );
    } //end bm_view_pdf_content()


    public function bm_handle_pdf_test_downloads() {
        if ( isset( $_GET['test_pdf_action'], $_GET['type'], $_GET['booking_id'], $_GET['page'] ) && $_GET['page'] === 'bm_pdf_customization' ) {
            $action            = sanitize_text_field( $_GET['test_pdf_action'] );
            $type              = sanitize_text_field( $_GET['type'] );
            $booking_id_or_key = sanitize_text_field( $_GET['booking_id'] );
            $nonce             = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

            if ( ! wp_verify_nonce( $nonce, 'test_pdf_action_' . $type . '_' . $booking_id_or_key ) ) {
                wp_die( esc_html__( 'Security check failed', 'service-booking' ) );
            }

            if ( ! class_exists( 'BM_PDF_Processor' ) ) {
                wp_die( esc_html__( 'PDF processing is not available.', 'service-booking' ) );
            }

            $pdf_processor = new BM_PDF_Processor();
            $pdf_path      = '';

            switch ( $type ) {
                case 'voucher':
                    $pdf_path = $pdf_processor->generate_voucher_pdf( $booking_id_or_key );
                    break;
                case 'customer_info':
                    $pdf_path = $pdf_processor->generate_customer_info_pdf( $booking_id_or_key );
                    break;
                case 'booking':
                default:
                    $pdf_path = $pdf_processor->generate_booking_pdf( $booking_id_or_key );
                    break;
            }

            if ( ! file_exists( $pdf_path ) ) {
                wp_die( esc_html__( 'PDF could not be generated', 'service-booking' ) );
            }

            if ( ob_get_level() ) {
                while ( ob_get_level() ) {
                    ob_end_clean();
                }
            }

            header( 'Content-Type: application/pdf' );
            header( 'Content-Length: ' . filesize( $pdf_path ) );
            header( 'Cache-Control: private, max-age=0, must-revalidate' );
            header( 'Pragma: public' );

            if ( $action === 'download' ) {
                header( 'Content-Disposition: attachment; filename="' . basename( $pdf_path ) . '"' );
            } else {
                header( 'Content-Disposition: inline; filename="' . basename( $pdf_path ) . '"' );
            }

            readfile( $pdf_path );
            exit;
        }
    }


	/**
	 * Fetch backend order service time slots
	 *
	 * @author Darpan
	 */
	public function bm_fetch_new_order_service_time_slots() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$slot_data  = array();

		if ( $post != false && $post != null ) {
			$id   = isset( $post['id'] ) ? $post['id'] : '';
			$date = isset( $post['date'] ) ? $post['date'] : '';

			if ( ! empty( $id ) && ! empty( $date ) ) {
				$total_time_slots = $bmrequests->bm_fetch_total_time_slots_by_service_id( $id );

				if ( $total_time_slots == 1 ) {
					$slot_data = $bmrequests->bm_fetch_backend_new_order_single_time_slot_by_service_id( $id, $date );
				} elseif ( $total_time_slots > 1 ) {
					$slot_data = $bmrequests->bm_fetch_backend_new_order_time_slot_by_service_id( $post );
				}
			}
		}

		echo wp_json_encode( $slot_data );
		die;
	}//end bm_fetch_new_order_service_time_slots()


	/**
	 * Fetch backend order extra services
	 *
	 * @author Darpan
	 */
	public function bm_fetch_service_extras_for_backend_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$extras     = array();

		if ( $post != false && $post != null ) {
			$extras = $bmrequests->bm_fetch_backend_new_order_extra_services( $post );
		}

		echo wp_json_encode( $extras );
		die;
	}//end bm_fetch_service_extras_for_backend_order()


	/**
	 * Fetch service min cap and cap left
	 *
	 * @author Darpan
	 */
	public function bm_fetch_mincap_and_cap_left() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$service_id = isset( $post['id'] ) && ! empty( $post['id'] ) ? $post['id'] : 0;
			$date       = isset( $post['date'] ) && ! empty( $post['date'] ) ? $post['date'] : '';

			if ( $service_id !== 0 && ! empty( $date ) ) {
				$svc_total_time_slots = $bmrequests->bm_fetch_total_time_slots_by_service_id( $service_id );
				$is_variable_slot     = $bmrequests->bm_check_if_variable_slot_by_service_id_and_date( $service_id, $date );

				if ( isset( $post['slots'] ) ) {
					if ( strpos( $post['slots'], ' - ' ) !== false ) {
						$booking_slots = explode( ' - ', $post['slots'] );
						$from          = $bmrequests->bm_twenty_fourhrs_format( $booking_slots[0] );
					} else {
						$from = $bmrequests->bm_twenty_fourhrs_format( $post['slots'] );
					}
				}

				$slot_info = $bmrequests->bm_fetch_slot_details( $service_id, $from, $date, $svc_total_time_slots, 0, $is_variable_slot, array( 'slot_min_cap', 'capacity_left' ) );

				if ( ! empty( $slot_info ) && isset( $slot_info['capacity_left'] ) && ( $slot_info['capacity_left'] > 0 ) ) {
					$data['status']    = true;
					$data['slot_info'] = $slot_info;
				}
			}
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_mincap_and_cap_left()


	/**
	 * Fetch service price for backend orders
	 *
	 * @author Darpan
	 */
	public function bm_fetch_service_price_for_backend_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$service_id = isset( $post['id'] ) && ! empty( $post['id'] ) ? $post['id'] : 0;
			$date       = isset( $post['date'] ) && ! empty( $post['date'] ) ? $post['date'] : '';

			$data['status'] = true;
			$data['price']  = $bmrequests->bm_fetch_new_order_service_price_by_service_id_and_date( $service_id, $date );
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_service_price_for_backend_order()


	/**
	 * Fetch service price for backend orders
	 *
	 * @author Darpan
	 */
	public function bm_fetch_price_discount_module_for_backend_order() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$service_id = isset( $post['id'] ) && ! empty( $post['id'] ) ? $post['id'] : 0;
			$date       = isset( $post['date'] ) && ! empty( $post['date'] ) ? $post['date'] : '';

			$data['status'] = true;
			$data['html']   = $bmrequests->bm_fetch_price_discount_module_box_for_backend_order( $service_id, $date );
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_price_discount_module_for_backend_order()


	/**
	 * Check discount in checkout form
	 *
	 * @author Darpan
	 */
	public function bm_fetch_age_data_and_check_backend_discount() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => 'error' );

		if ( $post != false && $post != null ) {
			$booking_key = isset( $post['booking_key'] ) ? $post['booking_key'] : '';

			if ( ! empty( $booking_key ) ) {
				$data['status']            = $bmrequests->bm_fetch_backend_age_type_booking_discounted_price( $post );
				$data['data']              = $bmrequests->bm_fetch_order_price_info_after_discount( $booking_key );
				$data['negative_discount'] = $dbhandler->get_global_option_value( 'negative_discount_' . $booking_key, 0 );
			}
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_age_data_and_check_backend_discount()


	/**
	 * Reset discount in checkout form
	 *
	 * @author Darpan
	 */
	public function bm_reset_backend_discounted_value() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler   = new BM_DBhandler();
		$bmrequests  = new BM_Request();
		$booking_key = filter_input( INPUT_POST, 'booking_key' );
		$data        = array( 'status' => 'error' );

		if ( $booking_key != false && $booking_key != null ) {
			$dbhandler->update_global_option_value( 'discount_' . $booking_key, 0 );
			$dbhandler->update_global_option_value( 'negative_discount_' . $booking_key, 0 );
			$dbhandler->bm_delete_transient( 'discounted_' . $booking_key );
			$dbhandler->bm_delete_transient( 'flexi_age_wise_discount_' . $booking_key );
			$dbhandler->bm_delete_transient( 'flexi_age_wise_total_price_' . $booking_key );
			$dbhandler->bm_delete_transient( 'flexi_total_person_discounted_' . $booking_key );
			$data['data']   = $bmrequests->bm_fetch_order_price_info_after_discount( $booking_key );
			$data['status'] = 'success';
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_reset_backend_discounted_value()


	/**
	 * Fetch change backend order status
	 *
	 * @author Darpan
	 */
	public function bm_change_order_status_to_complete_or_cancelled() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$order_id = isset( $post['id'] ) && ! empty( $post['id'] ) ? $post['id'] : 0;
			$status   = isset( $post['status'] ) && ! empty( $post['status'] ) ? $post['status'] : '';

			if ( $status == 'cancelled' ) {
				$update_data = array(
					'order_status' => $status,
					'is_active'    => 0,
				);
			} else {
				$update_data = array( 'order_status' => $status );
			}

			if ( $order_id !== 0 && ! empty( $status ) ) {
				$update_data['booking_updated_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
				$updated_status                    = $dbhandler->update_row( 'BOOKING', 'id', $order_id, $update_data, '', '%s' );

				if ( $updated_status ) {
					$data['status'] = true;
				}
			}
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_change_order_status_to_complete_or_cancelled()


	/**
	 * Fetch change backend order status
	 *
	 * @author Darpan
	 */
	public function bm_change_order_status() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$order_id = isset( $post['id'] ) && ! empty( $post['id'] ) ? $post['id'] : 0;
			$status   = isset( $post['status'] ) && ! empty( $post['status'] ) ? $post['status'] : '';

			if ( $status == 'cancelled' ) {
				$update_data = array(
					'order_status' => $status,
					'is_active'    => 0,
				);
			} else {
				$update_data = array( 'order_status' => $status );
			}

			if ( $order_id !== 0 && ! empty( $status ) ) {
				$update_data['booking_updated_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
				$updated_status                    = $dbhandler->update_row( 'BOOKING', 'id', $order_id, $update_data, '', '%s' );

				if ( $updated_status ) {
					$data['status'] = true;
				}
			}
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_change_order_status()



	/**
	 * Search order data
	 *
	 * @author Darpan
	 */
	public function bm_fetch_order_as_per_search() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$search_term  = isset( $post['search_string'] ) ? $post['search_string'] : '';
			$service_from = isset( $post['service_from'] ) ? $post['service_from'] : '';
			$service_to   = isset( $post['service_to'] ) ? $post['service_to'] : '';
			$order_from   = isset( $post['order_from'] ) ? $post['order_from'] : '';
			$order_to     = isset( $post['order_to'] ) ? $post['order_to'] : '';
			$type         = isset( $post['type'] ) ? $post['type'] : '';
			$base         = isset( $post['base'] ) ? $post['base'] : '';
			$limit        = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;
			$pagenum      = isset( $post['pagenum'] ) ? absint( $post['pagenum'] ) : 1;
			$offset       = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;
			$user_id      = get_current_user_id();

			$order_source = isset( $post['order_source'] ) ? sanitize_text_field( $post['order_source'] ) : '';
			$order_status = isset( $post['order_status'] ) ?
				( is_array( $post['order_status'] ) ? $post['order_status'] : explode( ',', $post['order_status'] ) ) :
				array();

			$payment_status = isset( $post['payment_status'] ) ?
				( is_array( $post['payment_status'] ) ? $post['payment_status'] : explode( ',', $post['payment_status'] ) ) :
				array();

			$services = isset( $post['services'] ) ?
				( is_array( $post['services'] ) ? $post['services'] : explode( ',', $post['services'] ) ) :
				array();

			$categories = isset( $post['categories'] ) ?
				( is_array( $post['categories'] ) ? $post['categories'] : explode( ',', $post['categories'] ) ) :
				array();

			$orderby = isset( $post['orderby'] ) ? sanitize_text_field( $post['orderby'] ) : 'id';
			$order   = isset( $post['order'] ) && in_array( strtolower( $post['order'] ), array( 'asc', 'desc' ) ) ? strtolower( $post['order'] ) : 'desc';

			$all_orders = $bmrequests->bm_fetch_all_orders_with_customer_data();
			$dbhandler->update_global_option_value( "show_backend_order_page_failed_orders_$user_id", 0 );
			$dbhandler->update_global_option_value( "show_backend_order_page_archived_orders_$user_id", 0 );

			$filtered_orders = $all_orders;

			if ( $type == 'save_search' ) {
				$search_data = array(
					'service_from'   => ! empty( $service_from ) ? $service_from : '',
					'service_to'     => ! empty( $service_to ) ? $service_to : '',
					'order_from'     => ! empty( $order_from ) ? $order_from : '',
					'order_to'       => ! empty( $order_to ) ? $order_to : '',
					'global_search'  => $search_term,
					'order_source'   => $order_source,
					'order_status'   => is_array( $order_status ) ? implode( ',', $order_status ) : '',
					'payment_status' => is_array( $payment_status ) ? implode( ',', $payment_status ) : '',
					'services'       => is_array( $services ) ? implode( ',', $services ) : '',
					'categories'     => is_array( $categories ) ? implode( ',', $categories ) : '',
				);

				$search_table_data = array(
					'search_data' => $search_data,
					'user_id'     => $user_id,
					'is_admin'    => current_user_can( 'manage_options' ) ? 1 : 0,
					'module'      => 'orders',
				);

				$search_final_data = $bmrequests->sanitize_request( $search_table_data, 'SAVESEARCH' );

				if ( $search_final_data != false && $search_final_data != null ) {
					$last_id = $dbhandler->get_all_result(
						'SAVESEARCH',
						'id',
						array(
							'user_id'  => $user_id,
							'module'   => 'orders',
							'is_admin' => current_user_can( 'manage_options' ) ? 1 : 0,
						),
						'var',
						0,
						1,
						'id',
						'DESC'
					);

					if ( $last_id ) {
						$dbhandler->update_row( 'SAVESEARCH', 'id', $last_id, $search_final_data, '', '%d' );
					} else {
						$search_final_data['search_created_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
						$dbhandler->insert_row( 'SAVESEARCH', $search_final_data );
					}
				}
			}

			if ( ! empty( $order_source ) ) {
				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $order_source ) {
						if ( $order_source === 'frontend' ) {
							return $order['is_frontend_booking'] == 1;
						} elseif ( $order_source === 'backend' ) {
							return $order['is_frontend_booking'] != 1;
						}
						return true;
					}
				);
			}

			if ( ! empty( $order_status ) && is_array( $order_status ) ) {
				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $order_status ) {
						return in_array( $order['order_status'], $order_status );
					}
				);
			}

			if ( ! empty( $payment_status ) && is_array( $payment_status ) ) {
				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $payment_status ) {
						return isset( $order['transaction_status'] ) && in_array( $order['transaction_status'], $payment_status );
					}
				);
			}

			if ( ! empty( $services ) && is_array( $services ) ) {
				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $services ) {
						return in_array( $order['service_id'], $services );
					}
				);
			}

			if ( ! empty( $categories ) && is_array( $categories ) ) {
				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $categories ) {
						return in_array( $order['category'], $categories );
					}
				);
			}

			if ( ! empty( $search_term ) ) {
				$search_date = DateTime::createFromFormat( 'd/m/y', $search_term );
				if ( $search_date !== false ) {
					$search_date_str = $search_date->format( 'Y-m-d' );
					$filtered_orders = array_filter(
						$filtered_orders,
						function ( $order ) use ( $search_date_str, $bmrequests ) {
							$booking_date = $bmrequests->bm_convert_date_format( $order['booking_date'], 'd/m/y H:i', 'Y-m-d' );
							$order_date   = $bmrequests->bm_convert_date_format( $order['booking_created_at'], 'd/m/y H:i', 'Y-m-d' );
							return $booking_date === $search_date_str || $order_date === $search_date_str;
						}
					);
				} else {
					$search_term_lower = strtolower( $search_term );
					$filtered_orders   = array_filter(
						$filtered_orders,
						function ( $order ) use ( $search_term_lower ) {
							$searchable_fields = array(
								'serial_no',
								'service_name',
								'booking_created_at',
								'booking_date',
								'first_name',
								'last_name',
								'contact_no',
								'email_address',
								'total_cost',
								'ordered_from',
								'order_status',
								'service_participants',
								'extra_service_participants',
								'service_cost',
								'extra_service_cost',
								'discount',
								'payment_status',
							);

							foreach ( $searchable_fields as $field ) {
								$value = $order[ $field ];
								if ( is_numeric( $value ) ) {
									$value = (string) $value;
								}
								if ( stripos( $value, $search_term_lower ) !== false ) {
									return true;
								}
							}

							if ( $order['order_status'] === $search_term_lower ) {
								return true;
							}
							return false;
						}
					);
				}
			}

			if ( ! empty( $service_from ) && ! empty( $service_to ) ) {
				$service_from = $bmrequests->bm_convert_date_format( $service_from, 'd/m/y', 'Y-m-d' );
				$service_to   = $bmrequests->bm_convert_date_format( $service_to, 'd/m/y', 'Y-m-d' );

				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $service_from, $service_to, $bmrequests ) {
						$booking_date = $bmrequests->bm_convert_date_format( $order['booking_date'], 'd/m/y H:i', 'Y-m-d' );
						return $booking_date >= $service_from && $booking_date <= $service_to;
					}
				);
			}

			if ( ! empty( $order_from ) && ! empty( $order_to ) ) {
				$order_from = $bmrequests->bm_convert_date_format( $order_from, 'd/m/y', 'Y-m-d' );
				$order_to   = $bmrequests->bm_convert_date_format( $order_to, 'd/m/y', 'Y-m-d' );
				$order_from = $order_from . ' 00:00:00';
				$order_to   = $order_to . ' 23:59:59';

				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $order_from, $order_to, $bmrequests ) {
						$order_date = $bmrequests->bm_convert_date_format( $order['booking_created_at'], 'd/m/y H:i', 'Y-m-d H:i' );
						return $order_date >= $order_from && $order_date <= $order_to;
					}
				);
			}

			if ( ! empty( $orderby ) ) {
				$filtered_orders = $bmrequests->bm_sort_array_by_key( $filtered_orders, $orderby, $order === 'desc' );
			}

			$total_records = count( $filtered_orders );
			$final_orders  = array_slice( $filtered_orders, $offset, $limit );

			$is_admin       = current_user_can( 'manage_options' ) ? 1 : 0;
			$saved_search   = $bmrequests->bm_fetch_last_saved_search_data( 'orders', $is_admin );
			$active_columns = $bmrequests->bm_fetch_active_columns( 'orders' );
			$column_values  = $bmrequests->bm_fetch_column_order_and_names( 'orders' );
			$statuses       = $bmrequests->bm_fetch_order_status_key_value();

			$num_of_pages = ( $limit > 0 ) ? ceil( $total_records / $limit ) : 1;
			$pagination   = $dbhandler->bm_get_pagination( $num_of_pages, $pagenum, $base, 'list' );

			$data['status']             = true;
			$data['bookings']           = $final_orders;
			$data['svc_prtcpants']      = array_sum( array_column( $final_orders, 'service_participants' ) );
			$data['ex_svc_prtcpants']   = array_sum( array_column( $final_orders, 'extra_service_participants' ) );
			$data['svc_cost_sum']       = $bmrequests->bm_fetch_price_in_global_settings_format( array_sum( array_column( $final_orders, 'service_cost' ) ), true );
			$data['ex_svc_cost_sum']    = $bmrequests->bm_fetch_price_in_global_settings_format( array_sum( array_column( $final_orders, 'extra_service_cost' ) ), true );
			$data['discount_sum']       = $bmrequests->bm_fetch_price_in_global_settings_format( array_sum( array_column( $final_orders, 'discount' ) ), true );
			$data['total_cost_sum']     = $bmrequests->bm_fetch_price_in_global_settings_format( array_sum( array_column( $final_orders, 'total_cost' ) ), true );
			$data['active_columns']     = $active_columns;
			$data['num_of_pages']       = $num_of_pages;
			$data['column_values']      = $column_values;
			$data['order_statuses']     = $statuses;
			$data['saved_search']       = $saved_search;
			$data['current_pagenumber'] = ( 1 + $offset );
			$data['pagination']         = ! empty( $pagination ) ? wp_kses_post( $pagination ) : '';
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_order_as_per_search()


	/**
	 * Search archived order data
	 *
	 * @author Darpan
	 */
	public function bm_fetch_archived_order_as_per_search() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$search_term  = isset( $post['search_string'] ) ? $post['search_string'] : '';
			$service_from = isset( $post['service_from'] ) ? $post['service_from'] : '';
			$service_to   = isset( $post['service_to'] ) ? $post['service_to'] : '';
			$order_from   = isset( $post['order_from'] ) ? $post['order_from'] : '';
			$order_to     = isset( $post['order_to'] ) ? $post['order_to'] : '';
			$type         = isset( $post['type'] ) ? $post['type'] : '';
			$base         = isset( $post['base'] ) ? $post['base'] : '';
			$limit        = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;
			$pagenum      = isset( $post['pagenum'] ) ? absint( $post['pagenum'] ) : 1;
			$offset       = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;
			$user_id      = get_current_user_id();

			$order_source = isset( $post['order_source'] ) ? sanitize_text_field( $post['order_source'] ) : '';
			$order_status = isset( $post['order_status'] ) ?
				( is_array( $post['order_status'] ) ? $post['order_status'] : explode( ',', $post['order_status'] ) ) :
				array();

			$payment_status = isset( $post['payment_status'] ) ?
				( is_array( $post['payment_status'] ) ? $post['payment_status'] : explode( ',', $post['payment_status'] ) ) :
				array();

			$services = isset( $post['services'] ) ?
				( is_array( $post['services'] ) ? $post['services'] : explode( ',', $post['services'] ) ) :
				array();

			$categories = isset( $post['categories'] ) ?
				( is_array( $post['categories'] ) ? $post['categories'] : explode( ',', $post['categories'] ) ) :
				array();

			$orderby = isset( $post['orderby'] ) ? sanitize_text_field( $post['orderby'] ) : 'id';
			$order   = isset( $post['order'] ) && in_array( strtolower( $post['order'] ), array( 'asc', 'desc' ) ) ? strtolower( $post['order'] ) : 'desc';

			$all_orders = $bmrequests->bm_fetch_all_archived_orders_with_customer_data();
			$dbhandler->update_global_option_value( "show_backend_order_page_failed_orders_$user_id", 0 );
			$dbhandler->update_global_option_value( "show_backend_order_page_archived_orders_$user_id", 1 );

			$filtered_orders = $all_orders;

			if ( $type == 'save_search' ) {
				$search_data = array(
					'service_from'   => ! empty( $service_from ) ? $service_from : '',
					'service_to'     => ! empty( $service_to ) ? $service_to : '',
					'order_from'     => ! empty( $order_from ) ? $order_from : '',
					'order_to'       => ! empty( $order_to ) ? $order_to : '',
					'global_search'  => $search_term,
					'order_source'   => $order_source,
					'order_status'   => is_array( $order_status ) ? implode( ',', $order_status ) : '',
					'payment_status' => is_array( $payment_status ) ? implode( ',', $payment_status ) : '',
					'services'       => is_array( $services ) ? implode( ',', $services ) : '',
					'categories'     => is_array( $categories ) ? implode( ',', $categories ) : '',
				);

				$search_table_data = array(
					'search_data' => $search_data,
					'user_id'     => $user_id,
					'is_admin'    => current_user_can( 'manage_options' ) ? 1 : 0,
					'module'      => 'archived_orders',
				);

				$search_final_data = $bmrequests->sanitize_request( $search_table_data, 'SAVESEARCH' );

				if ( $search_final_data != false && $search_final_data != null ) {
					$last_id = $dbhandler->get_all_result(
						'SAVESEARCH',
						'id',
						array(
							'user_id'  => $user_id,
							'module'   => 'archived_orders',
							'is_admin' => current_user_can( 'manage_options' ) ? 1 : 0,
						),
						'var',
						0,
						1,
						'id',
						'DESC'
					);

					if ( $last_id ) {
						$dbhandler->update_row( 'SAVESEARCH', 'id', $last_id, $search_final_data, '', '%d' );
					} else {
						$search_final_data['search_created_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
						$dbhandler->insert_row( 'SAVESEARCH', $search_final_data );
					}
				}
			}

			if ( ! empty( $order_source ) ) {
				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $order_source ) {
						if ( $order_source === 'frontend' ) {
							return $order['is_frontend_booking'] == 1;
						} elseif ( $order_source === 'backend' ) {
							return $order['is_frontend_booking'] != 1;
						}
						return true;
					}
				);
			}

			if ( ! empty( $order_status ) && is_array( $order_status ) ) {
				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $order_status ) {
						return in_array( $order['order_status'], $order_status );
					}
				);
			}

			if ( ! empty( $payment_status ) && is_array( $payment_status ) ) {
				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $payment_status ) {
						return isset( $order['transaction_status'] ) && in_array( $order['transaction_status'], $payment_status );
					}
				);
			}

			if ( ! empty( $services ) && is_array( $services ) ) {
				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $services ) {
						return in_array( $order['service_id'], $services );
					}
				);
			}

			if ( ! empty( $categories ) && is_array( $categories ) ) {
				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $categories ) {
						return in_array( $order['category'], $categories );
					}
				);
			}

			if ( ! empty( $search_term ) ) {
				$search_date = DateTime::createFromFormat( 'd/m/y', $search_term );
				if ( $search_date !== false ) {
					$search_date_str = $search_date->format( 'Y-m-d' );
					$filtered_orders = array_filter(
						$filtered_orders,
						function ( $order ) use ( $search_date_str, $bmrequests ) {
							$booking_date = $bmrequests->bm_convert_date_format( $order['booking_date'], 'd/m/y H:i', 'Y-m-d' );
							$order_date   = $bmrequests->bm_convert_date_format( $order['booking_created_at'], 'd/m/y H:i', 'Y-m-d' );
							return $booking_date === $search_date_str || $order_date === $search_date_str;
						}
					);
				} else {
					$search_term_lower = strtolower( $search_term );
					$filtered_orders   = array_filter(
						$filtered_orders,
						function ( $order ) use ( $search_term_lower ) {
							$searchable_fields = array(
								'serial_no',
								'service_name',
								'booking_created_at',
								'booking_date',
								'first_name',
								'last_name',
								'contact_no',
								'email_address',
								'total_cost',
								'ordered_from',
								'order_status',
								'service_participants',
								'extra_service_participants',
								'service_cost',
								'extra_service_cost',
								'discount',
								'payment_status',
							);

							foreach ( $searchable_fields as $field ) {
								$value = $order[ $field ];
								if ( is_numeric( $value ) ) {
									$value = (string) $value;
								}
								if ( stripos( $value, $search_term_lower ) !== false ) {
									return true;
								}
							}

							if ( $order['order_status'] === $search_term_lower ) {
								return true;
							}
							return false;
						}
					);
				}
			}

			if ( ! empty( $service_from ) && ! empty( $service_to ) ) {
				$service_from = $bmrequests->bm_convert_date_format( $service_from, 'd/m/y', 'Y-m-d' );
				$service_to   = $bmrequests->bm_convert_date_format( $service_to, 'd/m/y', 'Y-m-d' );

				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $service_from, $service_to, $bmrequests ) {
						$booking_date = $bmrequests->bm_convert_date_format( $order['booking_date'], 'd/m/y H:i', 'Y-m-d' );
						return $booking_date >= $service_from && $booking_date <= $service_to;
					}
				);
			}

			if ( ! empty( $order_from ) && ! empty( $order_to ) ) {
				$order_from = $bmrequests->bm_convert_date_format( $order_from, 'd/m/y', 'Y-m-d' );
				$order_to   = $bmrequests->bm_convert_date_format( $order_to, 'd/m/y', 'Y-m-d' );
				$order_from = $order_from . ' 00:00:00';
				$order_to   = $order_to . ' 23:59:59';

				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $order_from, $order_to, $bmrequests ) {
						$order_date = $bmrequests->bm_convert_date_format( $order['booking_created_at'], 'd/m/y H:i', 'Y-m-d H:i' );
						return $order_date >= $order_from && $order_date <= $order_to;
					}
				);
			}

			if ( ! empty( $orderby ) ) {
				$filtered_orders = $bmrequests->bm_sort_array_by_key( $filtered_orders, $orderby, $order === 'desc' );
			}

			$total_records = count( $filtered_orders );
			$final_orders  = array_slice( $filtered_orders, $offset, $limit );

			$is_admin       = current_user_can( 'manage_options' ) ? 1 : 0;
			$saved_search   = $bmrequests->bm_fetch_last_saved_search_data( 'archived_orders', $is_admin );
			$active_columns = $bmrequests->bm_fetch_active_columns( 'orders' );
			$column_values  = $bmrequests->bm_fetch_column_order_and_names( 'orders' );
			$statuses       = $bmrequests->bm_fetch_order_status_key_value();

			$num_of_pages = ( $limit > 0 ) ? ceil( $total_records / $limit ) : 1;
			$pagination   = $dbhandler->bm_get_pagination( $num_of_pages, $pagenum, $base, 'list' );

			$data['status']             = true;
			$data['bookings']           = $final_orders;
			$data['svc_prtcpants']      = array_sum( array_column( $final_orders, 'service_participants' ) );
			$data['ex_svc_prtcpants']   = array_sum( array_column( $final_orders, 'extra_service_participants' ) );
			$data['svc_cost_sum']       = $bmrequests->bm_fetch_price_in_global_settings_format( array_sum( array_column( $final_orders, 'service_cost' ) ), true );
			$data['ex_svc_cost_sum']    = $bmrequests->bm_fetch_price_in_global_settings_format( array_sum( array_column( $final_orders, 'extra_service_cost' ) ), true );
			$data['discount_sum']       = $bmrequests->bm_fetch_price_in_global_settings_format( array_sum( array_column( $final_orders, 'discount' ) ), true );
			$data['total_cost_sum']     = $bmrequests->bm_fetch_price_in_global_settings_format( array_sum( array_column( $final_orders, 'total_cost' ) ), true );
			$data['active_columns']     = $active_columns;
			$data['num_of_pages']       = $num_of_pages;
			$data['column_values']      = $column_values;
			$data['order_statuses']     = $statuses;
			$data['saved_search']       = $saved_search;
			$data['current_pagenumber'] = ( 1 + $offset );
			$data['pagination']         = ! empty( $pagination ) ? wp_kses_post( $pagination ) : '';
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_archived_order_as_per_search()


	/**
	 * Search checkin data
	 *
	 * @author Darpan
	 */
	public function bm_fetch_checkin_as_per_search() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$search_term  = isset( $post['search_string'] ) ? $post['search_string'] : '';
			$service_from = isset( $post['service_from'] ) ? $post['service_from'] : '';
			$service_to   = isset( $post['service_to'] ) ? $post['service_to'] : '';
			$checkin_from = isset( $post['checkin_from'] ) ? $post['checkin_from'] : '';
			$checkin_to   = isset( $post['checkin_to'] ) ? $post['checkin_to'] : '';
			$service_ids  = isset( $post['service_ids'] ) ? array_map( 'intval', (array) $post['service_ids'] ) : array();
			$type         = isset( $post['type'] ) ? $post['type'] : '';
			$base         = isset( $post['base'] ) ? $post['base'] : '';
			$limit        = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;
			$pagenum      = isset( $post['pagenum'] ) ? absint( $post['pagenum'] ) : 1;
			$offset       = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;
			$user_id      = get_current_user_id();
			$all_checkins = $bmrequests->bm_fetch_all_order_checkins();

			// foreach ( $all_checkins as &$checkin ) {
			// if ( $checkin['checkin_status'] === 'pending' &&
			// strtotime( $checkin['booking_date'] ) < time() ) {
			// $bmrequests->bm_update_checkin_status_as_expired( $checkin['booking_id'] );
			// $checkin['checkin_status'] = 'expired';
			// }
			// }

			$filtered_checkins = $all_checkins;

			if ( ! empty( $search_term ) ) {
				$search_date = DateTime::createFromFormat( 'd/m/y', $search_term );
				if ( $search_date !== false ) {
					$search_date_str   = $search_date->format( 'Y-m-d' );
					$filtered_checkins = array_filter(
						$filtered_checkins,
						function ( $checkin ) use ( $search_date_str ) {
							$booking_date      = $checkin['booking_date'];
							$checkin_time_date = $checkin['checkin_time'] !== '-' ? gmdate( 'Y-m-d', strtotime( $checkin['checkin_time'] ) ) : null;
							return $booking_date === $search_date_str || ( $checkin_time_date === $search_date_str );
						}
					);
				} else {
					$search_term_lower = strtolower( $search_term );
					$filtered_checkins = array_filter(
						$filtered_checkins,
						function ( $checkin ) use ( $search_term_lower ) {
							$searchable_fields = array(
								'serial_no',
								'service_name',
								'booking_date',
								'first_name',
								'last_name',
								'contact_no',
								'email_address',
								'total_cost',
								'checkin_time',
								'checkin_status',
							);
							foreach ( $searchable_fields as $field ) {
								$value = $checkin[ $field ];
								if ( $field === 'checkin_time' && $value === '-' ) {
									continue;
								}
								if ( $field === 'total_cost' || $field === 'serial_no' ) {
									$value = (string) $value;
								}
								if ( stripos( $value, $search_term_lower ) !== false ) {
									return true;
								}
							}

							if ( $checkin['checkin_status'] === $search_term_lower ) {
								return true;
							}
							return false;
						}
					);
				}
			}

			if ( ! empty( $checkin_from ) && ! empty( $checkin_to ) ) {
				$checkin_from_str = $bmrequests->bm_convert_date_format( $checkin_from, 'd/m/y', 'Y-m-d' );
				$checkin_to_str   = $bmrequests->bm_convert_date_format( $checkin_to, 'd/m/y', 'Y-m-d' );
				$checkin_from_str = $checkin_from_str . ' 00:00:00';
				$checkin_to_str   = $checkin_to_str . ' 23:59:59';

				$filtered_checkins = array_filter(
					$filtered_checkins,
					function ( $checkin ) use ( $checkin_from_str, $checkin_to_str, $bmrequests ) {
						if ( $checkin['checkin_time'] === '-' ) {
							return false;
						}
						$checkin_date = $bmrequests->bm_convert_date_format( $checkin['checkin_time'], 'd/m/y H:i', 'Y-m-d H:i' );
						return $checkin_date >= $checkin_from_str && $checkin_date <= $checkin_to_str;
					}
				);
			}

			if ( ! empty( $service_from ) && ! empty( $service_to ) ) {
				$service_from_str = $bmrequests->bm_convert_date_format( $service_from, 'd/m/y', 'Y-m-d' );
				$service_to_str   = $bmrequests->bm_convert_date_format( $service_to, 'd/m/y', 'Y-m-d' );
				$service_from_str = $service_from_str . ' 00:00:00';
				$service_to_str   = $service_to_str . ' 23:59:59';

				$filtered_checkins = array_filter(
					$filtered_checkins,
					function ( $checkin ) use ( $service_from_str, $service_to_str, $bmrequests ) {
						$booking_date = $bmrequests->bm_convert_date_format( $checkin['booking_date'], 'd/m/y H:i', 'Y-m-d H:i' );
						return $booking_date >= $service_from_str && $booking_date <= $service_to_str;
					}
				);
			}

			if ( ! empty( $service_ids ) ) {
				$filtered_checkins = array_filter(
					$filtered_checkins,
					function ( $checkin ) use ( $service_ids ) {
						return in_array( $checkin['service_id'], $service_ids );
					}
				);
			}

			$total_records  = count( $filtered_checkins );
			$final_checkins = array_slice( $filtered_checkins, $offset, $limit );
			$is_admin       = current_user_can( 'manage_options' ) ? 1 : 0;

			if ( $type == 'save_search' ) {
				$search_data = array(
					'service_from'  => $service_from ?? '',
					'service_to'    => $service_to ?? '',
					'checkin_from'  => $checkin_from ?? '',
					'checkin_to'    => $checkin_to ?? '',
					'global_search' => $search_term,
					'service_ids'   => $service_ids,
				);

				$search_table_data = array(
					'search_data' => $search_data,
					'user_id'     => $user_id,
					'is_admin'    => $is_admin,
					'module'      => 'checkin',
				);

				$search_final_data = $bmrequests->sanitize_request( $search_table_data, 'SAVESEARCH' );

				if ( $search_final_data != false && $search_final_data != null ) {
					$last_id = $dbhandler->get_all_result(
						'SAVESEARCH',
						'id',
						array(
							'user_id'  => $user_id,
							'module'   => 'checkin',
							'is_admin' => $is_admin,
						),
						'var',
						0,
						1,
						'id',
						'DESC'
					);

					if ( $last_id ) {
						$dbhandler->update_row( 'SAVESEARCH', 'id', $last_id, $search_final_data, '', '%d' );
					} else {
						$search_final_data['search_created_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
						$dbhandler->insert_row( 'SAVESEARCH', $search_final_data );
					}
				}
			}

			$saved_search   = $bmrequests->bm_fetch_last_saved_search_data( 'checkin', $is_admin );
			$active_columns = $bmrequests->bm_fetch_active_columns( 'checkin' );
			$column_values  = $bmrequests->bm_fetch_column_order_and_names( 'checkin' );
			$statuses       = $bmrequests->bm_fetch_order_status_key_value();

			$num_of_pages = ( $limit > 0 ) ? ceil( $total_records / $limit ) : 1;
			$pagination   = $dbhandler->bm_get_pagination( $num_of_pages, $pagenum, $base, 'list' );

			$data['status']             = true;
			$data['checkins']           = $final_checkins;
			$data['active_columns']     = $active_columns;
			$data['num_of_pages']       = $num_of_pages;
			$data['column_values']      = $column_values;
			$data['saved_search']       = $saved_search;
			$data['current_pagenumber'] = ( 1 + $offset );
			$data['pagination']         = wp_kses_post( is_string( $pagination ) ? $pagination : '' );
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_checkin_as_per_search()


	/**
	 * Search failed order data
	 *
	 * @author Darpan
	 */
	public function bm_fetch_failed_order_as_per_search() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post ) {
			$search_term  = isset( $post['search_string'] ) ? $post['search_string'] : '';
			$service_from = isset( $post['service_from'] ) ? $post['service_from'] : '';
			$service_to   = isset( $post['service_to'] ) ? $post['service_to'] : '';
			$order_from   = isset( $post['order_from'] ) ? $post['order_from'] : '';
			$order_to     = isset( $post['order_to'] ) ? $post['order_to'] : '';
			$type         = isset( $post['type'] ) ? $post['type'] : '';
			$base         = isset( $post['base'] ) ? $post['base'] : '';
			$limit        = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;
			$pagenum      = isset( $post['pagenum'] ) ? absint( $post['pagenum'] ) : 1;
			$offset       = ( $limit > 0 ) ? ( ( $pagenum - 1 ) * $limit ) : 0;
			$user_id      = get_current_user_id();

			$order_source = isset( $post['order_source'] ) ? sanitize_text_field( $post['order_source'] ) : '';

			$orderby = isset( $post['orderby'] ) ? sanitize_text_field( $post['orderby'] ) : 'id';
			$order   = isset( $post['order'] ) && in_array( strtolower( $post['order'] ), array( 'asc', 'desc' ) ) ? strtolower( $post['order'] ) : 'desc';

			$dbhandler->update_global_option_value( "show_backend_order_page_failed_orders_$user_id", 1 );
			$dbhandler->update_global_option_value( "show_backend_order_page_archived_orders_$user_id", 0 );

			$filtered_orders = $bmrequests->bm_fetch_all_failed_transactions_with_customer_data();

			if ( $type == 'save_search' ) {
				$search_data = array(
					'service_from'  => ! empty( $service_from ) ? $service_from : '',
					'service_to'    => ! empty( $service_to ) ? $service_to : '',
					'order_from'    => ! empty( $order_from ) ? $order_from : '',
					'order_to'      => ! empty( $order_to ) ? $order_to : '',
					'global_search' => $search_term,
					'order_source'  => $order_source,
				);

				$search_table_data = array(
					'search_data' => $search_data,
					'user_id'     => $user_id,
					'is_admin'    => current_user_can( 'manage_options' ) ? 1 : 0,
					'module'      => 'failed_orders',
				);

				$search_final_data = $bmrequests->sanitize_request( $search_table_data, 'SAVESEARCH' );

				if ( $search_final_data != false && $search_final_data != null ) {
					$last_id = $dbhandler->get_all_result(
						'SAVESEARCH',
						'id',
						array(
							'user_id'  => $user_id,
							'module'   => 'failed_orders',
							'is_admin' => current_user_can( 'manage_options' ) ? 1 : 0,
						),
						'var',
						0,
						1,
						'id',
						'DESC'
					);

					if ( $last_id ) {
						$dbhandler->update_row( 'SAVESEARCH', 'id', $last_id, $search_final_data, '', '%d' );
					} else {
						$search_final_data['search_created_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
						$dbhandler->insert_row( 'SAVESEARCH', $search_final_data );
					}
				}
			}

			if ( ! empty( $order_source ) ) {
				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $order_source ) {
						if ( $order_source === 'frontend' ) {
							return $order['is_frontend_booking'] == 1;
						} elseif ( $order_source === 'backend' ) {
							return $order['is_frontend_booking'] != 1;
						}
						return true;
					}
				);
			}

			if ( ! empty( $search_term ) ) {
				$search_date = DateTime::createFromFormat( 'd/m/y', $search_term );
				if ( $search_date !== false ) {
					$search_date_str = $search_date->format( 'Y-m-d' );
					$filtered_orders = array_filter(
						$filtered_orders,
						function ( $order ) use ( $search_date_str, $bmrequests ) {
							$booking_date = $bmrequests->bm_convert_date_format( $order['booking_date'], 'd/m/y H:i', 'Y-m-d' );
							$order_date   = $bmrequests->bm_convert_date_format( $order['booking_created_at'], 'd/m/y H:i', 'Y-m-d' );
							return $booking_date === $search_date_str || $order_date === $search_date_str;
						}
					);
				} else {
					$search_term_lower = strtolower( $search_term );
					$filtered_orders   = array_filter(
						$filtered_orders,
						function ( $order ) use ( $search_term_lower ) {
							$searchable_fields = array(
								'serial_no',
								'service_name',
								'booking_created_at',
								'booking_date',
								'first_name',
								'last_name',
								'contact_no',
								'email_address',
								'total_cost',
								'ordered_from',
								'order_status',
								'service_participants',
								'extra_service_participants',
								'service_cost',
								'extra_service_cost',
								'discount',
								'payment_status',
							);

							foreach ( $searchable_fields as $field ) {
								$value = $order[ $field ];
								if ( is_numeric( $value ) ) {
									$value = (string) $value;
								}
								if ( stripos( $value, $search_term_lower ) !== false ) {
									return true;
								}
							}

							if ( $order['order_status'] === $search_term_lower ) {
								return true;
							}
							return false;
						}
					);
				}
			}

			if ( ! empty( $service_from ) && ! empty( $service_to ) ) {
				$service_from = $bmrequests->bm_convert_date_format( $service_from, 'd/m/y', 'Y-m-d' );
				$service_to   = $bmrequests->bm_convert_date_format( $service_to, 'd/m/y', 'Y-m-d' );

				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $service_from, $service_to, $bmrequests ) {
						$booking_date = $bmrequests->bm_convert_date_format( $order['booking_date'], 'd/m/y H:i', 'Y-m-d' );
						return $booking_date >= $service_from && $booking_date <= $service_to;
					}
				);
			}

			if ( ! empty( $order_from ) && ! empty( $order_to ) ) {
				$order_from = $bmrequests->bm_convert_date_format( $order_from, 'd/m/y', 'Y-m-d' );
				$order_to   = $bmrequests->bm_convert_date_format( $order_to, 'd/m/y', 'Y-m-d' );
				$order_from = $order_from . ' 00:00:00';
				$order_to   = $order_to . ' 23:59:59';

				$filtered_orders = array_filter(
					$filtered_orders,
					function ( $order ) use ( $order_from, $order_to, $bmrequests ) {
						$order_date = $bmrequests->bm_convert_date_format( $order['booking_created_at'], 'd/m/y H:i', 'Y-m-d H:i' );
						return $order_date >= $order_from && $order_date <= $order_to;
					}
				);
			}

			if ( ! empty( $orderby ) ) {
				$filtered_orders = $bmrequests->bm_sort_array_by_key( $filtered_orders, $orderby, $order === 'desc' );
			}

			$total_records = count( $filtered_orders );
			$final_orders  = array_slice( $filtered_orders, $offset, $limit );

			$is_admin       = current_user_can( 'manage_options' ) ? 1 : 0;
			$saved_search   = $bmrequests->bm_fetch_last_saved_search_data( 'failed_orders', $is_admin );
			$active_columns = $bmrequests->bm_fetch_active_columns( 'orders' );
			$column_values  = $bmrequests->bm_fetch_column_order_and_names( 'orders' );
			$statuses       = $bmrequests->bm_fetch_order_status_key_value();

			$num_of_pages = ( $limit > 0 ) ? ceil( $total_records / $limit ) : 1;
			$pagination   = $dbhandler->bm_get_pagination( $num_of_pages, $pagenum, $base, 'list' );

			$data['status']             = true;
			$data['bookings']           = $final_orders;
			$data['svc_prtcpants']      = array_sum( array_column( $final_orders, 'service_participants' ) );
			$data['ex_svc_prtcpants']   = array_sum( array_column( $final_orders, 'extra_service_participants' ) );
			$data['svc_cost_sum']       = $bmrequests->bm_fetch_price_in_global_settings_format( array_sum( array_column( $final_orders, 'service_cost' ) ), true );
			$data['ex_svc_cost_sum']    = $bmrequests->bm_fetch_price_in_global_settings_format( array_sum( array_column( $final_orders, 'extra_service_cost' ) ), true );
			$data['discount_sum']       = $bmrequests->bm_fetch_price_in_global_settings_format( array_sum( array_column( $final_orders, 'discount' ) ), true );
			$data['total_cost_sum']     = $bmrequests->bm_fetch_price_in_global_settings_format( array_sum( array_column( $final_orders, 'total_cost' ) ), true );
			$data['active_columns']     = $active_columns;
			$data['num_of_pages']       = $num_of_pages;
			$data['column_values']      = $column_values;
			$data['order_statuses']     = $statuses;
			$data['saved_search']       = $saved_search;
			$data['current_pagenumber'] = ( 1 + $offset );
			$data['pagination']         = ! empty( $pagination ) ? wp_kses_post( $pagination ) : '';
		}

		echo wp_json_encode( $data );
		die;
	}



	/**
	 * Get customer info
	 *
	 * @author Darpan
	 */
	public function bm_get_order_personal_info() {
		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( __( 'Security check failed', 'service-booking' ) );
			return;
		}

		$booking_id = filter_input( INPUT_POST, 'booking_id', FILTER_VALIDATE_INT );

		if ( ! $booking_id ) {
			wp_send_json_error( __( 'Invalid booking ID', 'service-booking' ) );
		}

		$bmrequests = new BM_Request();
		$order_data = $bmrequests->bm_fetch_order_details_for_single_page( $booking_id );

		if ( ! $order_data ) {
			wp_send_json_error( __( 'Order not found', 'service-booking' ) );
		}

		$response = array(
			'billing_name'    => $order_data['customer_info']['first_name'] . ' ' . $order_data['customer_info']['last_name'],
			'billing_address' => $order_data['billing_details']['address'],
			'billing_city'    => $order_data['billing_details']['city'],
			'billing_country' => $order_data['billing_details']['country'],
			'billing_phone'   => $order_data['customer_info']['phone'],
			'customer_since'  => date( 'M j, Y', strtotime( $order_data['customer_info']['created_at'] ?? 'now' ) ),
			'total_orders'    => $this->bm_count_customer_orders( $order_data['customer_info']['id'] ),
			'notes'           => $order_data['billing_details']['notes'],
		);

		wp_send_json_success( $response );
	}//end bm_get_order_personal_info()


	/**
	 * Get payment details
	 *
	 * @author Darpan
	 */
	public function bm_get_order_payment_details() {
		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( __( 'Security check failed', 'service-booking' ) );
			return;
		}

		$booking_id = filter_input( INPUT_POST, 'booking_id', FILTER_VALIDATE_INT );

		if ( ! $booking_id ) {
			wp_send_json_error( __( 'Invalid booking ID', 'service-booking' ) );
		}

		$transaction = ( new BM_DBhandler() )->get_row( 'TRANSACTIONS', $booking_id, 'booking_id' );

		if ( ! $transaction ) {
			wp_send_json_error( __( 'No payment details found', 'service-booking' ) );
		}

		$response = array(
			'payment_method' => ucfirst( $transaction->payment_method ),
			'transaction_id' => $transaction->transaction_id,
			'amount'         => ( new BM_Request() )->bm_fetch_price_in_global_settings_format( $transaction->paid_amount, true ),
			'status'         => ucfirst( $transaction->payment_status ),
			'date'           => date( 'M j, Y H:i', strtotime( $transaction->transaction_created_at ) ),
		);

		wp_send_json_success( $response );
	}


	public function bm_get_order_email_info() {
		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( __( 'Security check failed', 'service-booking' ) );
			return;
		}

		$booking_id = filter_input( INPUT_POST, 'booking_id', FILTER_VALIDATE_INT );

		$emails = ( new BM_DBhandler() )->get_all_result(
			'EMAILS',
			'*',
			array(
				'module_type' => 'BOOKING',
				'module_id'   => $booking_id,
			),
			'results'
		);

		$response = array(
            'emails' => array_map(
                function( $email ) {
                    return array(
                        'id'        => $email->id ?? null,
                        'subject'   => $email->mail_sub ?? '',
                        'recipient' => $email->mail_to ?? '',
                        'status'    => $email->status ?? '',
                        'date'      => isset( $email->created_at )
                            ? ( new BM_Request() )->bm_month_year_date_format( $email->created_at )
                            : '',
                    );
                },
                $emails ?: array()
            ),
        );

        wp_send_json_success( $response );

	}

    public function bm_resend_order_email() {
        if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
            wp_send_json_error( __( 'Security check failed', 'service-booking' ) );
            return;
        }

        $booking_id = filter_input( INPUT_POST, 'booking_id', FILTER_VALIDATE_INT );
        if ( ! $booking_id ) {
            wp_send_json_error( __( 'Invalid booking ID', 'service-booking' ) );
            return;
        }

        $db_handler = new BM_DBhandler();
        $booking    = $db_handler->get_row( 'BOOKING', '*', array( 'id' => $booking_id ) );

        if ( ! $booking ) {
            wp_send_json_error( __( 'Booking not found', 'service-booking' ) );
            return;
        }

        $emails = $db_handler->get_all_result(
            'EMAILS',
            '*',
            array(
                'module_type' => 'BOOKING',
                'module_id'   => $booking_id,
            ),
            'results'
        );
        if ( ! empty( $emails ) ) {
            wp_send_json_error( __( 'Emails have already been sent for this order.', 'service-booking' ) );
            return;
        }

        $timezone        = $db_handler->get_global_option_value( 'bm_booking_time_zone', 'Asia/Kolkata' );
        $today           = new DateTime( 'now', new DateTimeZone( $timezone ) );
        $current_date    = $today->format( 'Y-m-d' );
        $current_time    = $today->format( 'H:i' );
        $currentDateTime = $current_date . ' ' . $current_time;

        $service_date  = isset( $booking->booking_date ) ? $booking->booking_date : '';
        $booking_slots = isset( $booking->booking_slots ) ? maybe_unserialize( $booking->booking_slots ) : array();
        $from_slot     = isset( $booking_slots['from'] ) ? $booking_slots['from'] : '';

        if ( ! empty( $service_date ) && ! empty( $from_slot ) ) {
            $service_date_time = $service_date . ' ' . $from_slot;
        } else {
            $service_date_time = $service_date . ' 00:00:00';
        }

        $service_timestamp = strtotime( $service_date_time );
        $current_timestamp = strtotime( $currentDateTime );

        if ( $service_timestamp <= $current_timestamp ) {
            wp_send_json_error( __( 'Cannot resend email for a past service date.', 'service-booking' ) );
            return;
        }

        $booking_type = isset( $booking->booking_type ) ? $booking->booking_type : '';
        if ( $booking_type !== 'on_request' && $booking_type !== 'direct' ) {
            wp_send_json_error( __( 'Unknown booking type.', 'service-booking' ) );
            return;
        }

        wp_send_json_success( __( 'Email resend process has been triggered.', 'service-booking' ) );
    }

	public function bm_get_order_products() {
		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( __( 'Security check failed', 'service-booking' ) );
			return;
		}

		$booking_id = filter_input( INPUT_POST, 'booking_id', FILTER_VALIDATE_INT );

		if ( ! $booking_id ) {
			wp_send_json_error( __( 'Invalid booking ID', 'service-booking' ) );
		}

		$bm_requests = new BM_Request();
		$order_data  = $bm_requests->bm_fetch_order_details_for_single_page( $booking_id );

		if ( ! $order_data ) {
			wp_send_json_error( __( 'Order not found', 'service-booking' ) );
		}

		$response = array(
			'products' => $order_data['ordered_products'],
		);

		wp_send_json_success( $response );
	}


	public function bm_get_email_content() {
		if ( ! check_ajax_referer( 'ajax-nonce', 'nonce', false ) ) {
			wp_send_json_error( __( 'Security check failed', 'service-booking' ) );
			return;
		}

		$email_id = filter_input( INPUT_POST, 'email_id', FILTER_VALIDATE_INT );

		if ( ! $email_id ) {
			wp_send_json_error( __( 'Invalid email ID', 'service-booking' ) );
		}

		$email = ( new BM_DBhandler() )->get_row( 'EMAILS', $email_id, 'id' );

		if ( ! $email ) {
			wp_send_json_error( __( 'Email not found', 'service-booking' ) );
		}

		$response = array(
			'subject'   => $email->mail_sub,
			'recipient' => $email->mail_to,
			'content'   => wp_kses_post( $email->mail_body ),
			'date'      => ( new BM_Request() )->bm_month_year_date_format( $email->created_at ),
		);

		wp_send_json_success( $response );
	}


	private function bm_count_customer_orders( $customer_id ) {
		return ( new BM_DBhandler() )->bm_count( 'BOOKING', array( 'customer_id' => $customer_id ) );
	}


	/**
	 * Fullcalendar events callbak
	 *
	 * @author Darpan
	 */
	public function bm_filter_fullcalendar_events_callback() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
			return;
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( empty( $post ) || ! isset( $post['start'], $post['end'] ) ) {
			wp_send_json_error( __( 'Invalid data', 'service-booking' ) );
			return;
		}

		$start_date = sanitize_text_field( $post['start'] );
		$end_date   = sanitize_text_field( $post['end'] );

		if ( empty( $start_date ) || empty( $end_date ) ) {
			wp_send_json_error( __( 'Invalid data', 'service-booking' ) );
			return;
		}

		$service_ids  = isset( $post['services'] ) ? array_map( 'intval', $post['services'] ) : array();
		$category_ids = isset( $post['categories'] ) ? array_map( 'intval', $post['categories'] ) : array();
		$cat_ids      = isset( $post['cat_ids'] ) ? array_map( 'intval', $post['cat_ids'] ) : array();

		$where = array(
			's.is_service_front' => array( '=' => 1 ),
			's.service_status'   => array( '=' => 1 ),
		);

		$additional = '';
		if ( ! empty( $cat_ids ) ) {
			if ( in_array( 0, $cat_ids ) ) {
				$where['s.service_category'] = array(
					'IN' => $cat_ids,
					'OR' => array( '=' => 0 ),
				);
				$additional                  = 'OR s.service_category = 0';
			} else {
				$where['s.service_category'] = array( 'IN' => $cat_ids );
				$where['c.cat_status']       = array( '=' => 1 );
			}
		} elseif ( ! empty( $category_ids ) ) {
			if ( in_array( 0, $category_ids ) ) {
				$where['s.service_category'] = array(
					'IN' => $category_ids,
					'OR' => array( '=' => 0 ),
				);
				$additional                  = 'OR s.service_category = 0';
			} else {
				$where['s.service_category'] = array( 'IN' => $category_ids );
				$where['c.cat_status']       = array( '=' => 1 );
			}
		} else {
			$where['c.cat_status'] = array( '=' => 1 );
			$additional            = 'OR s.service_category = 0';
		}

		if ( ! empty( $service_ids ) ) {
			$where['s.id'] = array( 'IN' => $service_ids );
		}

		$services = $dbhandler->get_results_with_join(
			array( 'SERVICE', 's' ),
			's.id, s.service_name, s.service_calendar_title, s.service_category, s.service_duration, s.default_price, s.service_desc, s.service_position',
			array(
				array(
					'table' => 'CATEGORY',
					'alias' => 'c',
					'on'    => 's.service_category = c.id',
					'type'  => 'LEFT',
				),
			),
			$where,
			'results',
			0,
			false,
			's.service_position',
			false,
			$additional
		);

		$timezone   = $dbhandler->get_global_option_value( 'bm_booking_time_zone', 'Asia/Kolkata' );
		$date_range = new DatePeriod(
			new DateTime( $start_date, new DateTimeZone( $timezone ) ),
			new DateInterval( 'P1D' ),
			( new DateTime( $end_date, new DateTimeZone( $timezone ) ) )->modify( '+1 day' )
		);
		$today      = ( new DateTime( 'now', new DateTimeZone( $timezone ) ) )->format( 'Y-m-d' );

		$filtered_events = array();

		if ( ! empty( $services ) && is_array( $services ) ) {
			foreach ( $services as $service ) {
				foreach ( $date_range as $date ) {
					$current_date = $date->format( 'Y-m-d' );

					$has_slots = ! empty(
						$bmrequests->bm_fetch_service_time_slot_array_by_service_id(
							array(
								'id'   => $service->id,
								'date' => $current_date,
							)
						)
					);

					$is_past_date = $current_date < $today;
					$event_class  = $is_past_date ? 'past-date-event' : '';

					if ( $bmrequests->bm_service_is_bookable( $service->id, $current_date ) && $has_slots ) {
						$category_name = $service->service_category ?
							$bmrequests->bm_fetch_category_name_by_category_id( $service->service_category ) :
							__( 'Uncategorized', 'service-booking' );

						$filtered_events[] = array(
							'id'             => $service->id ?? 0,
							'title'          => $service->service_name ?? '',
							'calendar_title' => $service->service_calendar_title ?? '',
							'start'          => $current_date ?? '',
							'allDay'         => true,
							'className'      => $event_class,
							'extendedProps'  => array(
								'duration'         => $bmrequests->bm_fetch_float_to_time_string( $service->service_duration ),
								'price'            => esc_html( $bmrequests->bm_fetch_service_price_by_service_id_and_date( $service->id, $current_date, 'global_format' ) ),
								'category'         => $service->service_category ?? 0,
								'service_position' => $service->service_position,
								'categoryName'     => $category_name,
								'full_desc'        => isset( $service->service_desc ) && ! empty( $service->service_desc ) ? wp_kses_post( wp_unslash( $service->service_desc ) ) : '',
								'image'            => esc_url( $bmrequests->bm_fetch_image_url_or_guid( $service->id, 'SERVICE', 'url' ) ),
								'date'             => $current_date,
								'serviceId'        => $service->id ?? 0,
								'isPastDate'       => $is_past_date,
							),
						);
					}
				}
			}
		}

		wp_send_json_success(
			array(
				'events'  => $filtered_events,
				'message' => sprintf( __( 'Showing %d available services', 'service-booking' ), count( $filtered_events ) ),
			)
		);
	}//end bm_filter_fullcalendar_events_callback()


    /**
     * Fetch overview, revenue, or products data.
     */
    public function bm_fetch_analytics_data_callback() {
        $nonce = filter_input( INPUT_POST, 'nonce' );
        if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
            wp_die( esc_html__( 'Failed security check', 'service-booking' ) );
        }

        $bmrequests = new BM_Request();
        $post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

        $date_from    = ! empty( $post['date_from'] ) ? $bmrequests->bm_convert_date_format( $post['date_from'], 'd/m/Y', 'Y-m-d' ) : gmdate( 'Y-m-01' );
        $date_to      = ! empty( $post['date_to'] ) ? $bmrequests->bm_convert_date_format( $post['date_to'], 'd/m/Y', 'Y-m-d' ) : gmdate( 'Y-m-t' );
        $compare_from = ! empty( $post['compare_from'] ) ? $bmrequests->bm_convert_date_format( $post['compare_from'], 'd/m/Y', 'Y-m-d' ) : '';
        $compare_to   = ! empty( $post['compare_to'] ) ? $bmrequests->bm_convert_date_format( $post['compare_to'], 'd/m/Y', 'Y-m-d' ) : '';
        $compare_type = ! empty( $post['compare_type'] ) ? sanitize_text_field( $post['compare_type'] ) : 'period';
        $action_type  = ! empty( $post['action_type'] ) ? sanitize_text_field( $post['action_type'] ) : 'overview';
        $category_id  = isset( $post['category_id'] ) && $post['category_id'] !== '' ? $post['category_id'] : '';
        $service_id   = isset( $post['service_id'] ) && $post['service_id'] !== '' ? $post['service_id'] : '';
        $metric       = isset( $post['metric'] ) ? sanitize_text_field( $post['metric'] ) : '';

        $response = array( 'status' => false );

        try {
            switch ( $action_type ) {
                case 'overview':
                    $response = $this->bm_get_overview_analytics( $date_from, $date_to, $compare_from, $compare_to, $compare_type );
                    break;
                case 'revenue':
                    $response = $this->bm_get_revenue_analytics( $date_from, $date_to, $compare_from, $compare_to, $compare_type );
                    break;
                case 'products':
                    $response = $this->bm_get_products_analytics( $date_from, $date_to, $compare_from, $compare_to, $category_id, $service_id, $compare_type );
                    break;
                case 'orders':
                    $filters  = isset( $post['filters'] ) ? (array) $post['filters'] : array();
                    $response = $this->bm_get_orders_analytics( $date_from, $date_to, $compare_from, $compare_to, $compare_type, $filters );
                    break;
                case 'metric_chart':
                    $response = $this->bm_get_metric_chart_data( $date_from, $date_to, $compare_from, $compare_to, $compare_type, $metric );
                    break;
                default:
                    $response = $this->bm_get_overview_analytics( $date_from, $date_to, $compare_from, $compare_to, $compare_type );
            }
            $response['status'] = true;
        } catch ( Exception $e ) {
            $response['error'] = $e->getMessage();
        }

        wp_send_json( $response );
    }

    /**
     * Get overview analytics (metrics, charts, leaderboards).
     */
    public function bm_get_overview_analytics( $date_from, $date_to, $compare_from = '', $compare_to = '', $compare_type = 'period' ) {
        $current_data  = $this->bm_get_analytics_data_join( $date_from, $date_to );
        $previous_data = ! empty( $compare_from ) && ! empty( $compare_to )
            ? $this->bm_get_analytics_data_join( $compare_from, $compare_to )
            : array(
                'total_sales'         => 0,
                'net_sales'           => 0,
                'total_orders'        => 0,
                'services_sold'       => 0,
                'extra_services_sold' => 0,
            );

        $response = array(
            'total_sales'                => $current_data['total_sales'],
            'total_sales_change'         => $this->bm_calculate_change( $previous_data['total_sales'], $current_data['total_sales'] ),
            'net_sales'                  => $current_data['net_sales'],
            'net_sales_change'           => $this->bm_calculate_change( $previous_data['net_sales'], $current_data['net_sales'] ),
            'total_orders'               => $current_data['total_orders'],
            'total_orders_change'        => $this->bm_calculate_change( $previous_data['total_orders'], $current_data['total_orders'] ),
            'services_sold'              => $current_data['services_sold'],
            'services_sold_change'       => $this->bm_calculate_change( $previous_data['services_sold'], $current_data['services_sold'] ),
            'extra_services_sold'        => $current_data['extra_services_sold'],
            'extra_services_sold_change' => $this->bm_calculate_change( $previous_data['extra_services_sold'], $current_data['extra_services_sold'] ),
        );

        // Charts
        $chart_data = $this->bm_get_chart_data_join( $date_from, $date_to, $compare_from, $compare_to, $compare_type );
        $response   = array_merge( $response, $chart_data );

        // Leaderboards
        $response['top_categories'] = $this->bm_get_top_categories_join( $date_from, $date_to, 5 );
        $response['top_services']   = $this->bm_get_top_services_join( $date_from, $date_to, 5 );

        return $response;
    }

    /**
     * Get revenue analytics.
     */
    public function bm_get_revenue_analytics( $date_from, $date_to, $compare_from = '', $compare_to = '', $compare_type = '' ) {
        $current_revenue  = $this->bm_get_revenue_data_join( $date_from, $date_to );
        $previous_revenue = ! empty( $compare_from ) && ! empty( $compare_to )
            ? $this->bm_get_revenue_data_join( $compare_from, $compare_to )
            : array(
                'gross_sales' => 0,
                'returns'     => 0,
                'net_sales'   => 0,
            );

        $response = array(
            'gross_sales'        => $current_revenue['gross_sales'],
            'gross_sales_change' => $this->bm_calculate_change( $previous_revenue['gross_sales'], $current_revenue['gross_sales'] ),
            'returns'            => $current_revenue['returns'],
            'returns_change'     => $this->bm_calculate_change( $previous_revenue['returns'], $current_revenue['returns'] ),
            'net_sales'          => $current_revenue['net_sales'],
            'net_sales_change'   => $this->bm_calculate_change( $previous_revenue['net_sales'], $current_revenue['net_sales'] ),
        );

        // Daily breakdown
        $response['daily_revenue'] = $this->bm_get_daily_revenue_join( $date_from, $date_to );

        // Revenue chart
        $revenue_chart_data = $this->bm_get_revenue_chart_data_join( $date_from, $date_to );
        $response           = array_merge( $response, $revenue_chart_data );

        return $response;
    }

    /**
     * Get products analytics.
     */
    public function bm_get_products_analytics( $date_from, $date_to, $compare_from = '', $compare_to = '', $category_id = '', $service_id = '', $compare_type = '' ) {
        $current_products  = $this->bm_get_products_data_join( $date_from, $date_to, $category_id, $service_id );
        $previous_products = ! empty( $compare_from ) && ! empty( $compare_to )
            ? $this->bm_get_products_data_join( $compare_from, $compare_to, $category_id, $service_id )
            : array(
                'items_sold'   => 0,
                'net_sales'    => 0,
                'total_orders' => 0,
            );

        $response = array(
            'items_sold'          => $current_products['items_sold'],
            'items_sold_change'   => $this->bm_calculate_change( $previous_products['items_sold'], $current_products['items_sold'] ),
            'net_sales'           => $current_products['net_sales'],
            'net_sales_change'    => $this->bm_calculate_change( $previous_products['net_sales'], $current_products['net_sales'] ),
            'total_orders'        => $current_products['total_orders'],
            'total_orders_change' => $this->bm_calculate_change( $previous_products['total_orders'], $current_products['total_orders'] ),
        );

        // Product list
        $response['products'] = $this->bm_get_products_performance_join( $date_from, $date_to, $category_id, $service_id );

        // Items sold chart
        $items_chart_data = $this->bm_get_items_sold_chart_data_join( $date_from, $date_to, $compare_from, $compare_to, $category_id, $service_id, $compare_type );
        $response         = array_merge( $response, $items_chart_data );

        return $response;
    }

    // ------------------------------------------------------------
    // 2. DATA JOIN HELPERS (Existing, correctly calculating net sales)
    // ------------------------------------------------------------

    /**
     * Get aggregated analytics data for a date range.
     */
    public function bm_get_analytics_data_join( $date_from, $date_to ) {
        $dbhandler = new BM_DBhandler();

        $where = array();
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $where['b.booking_date'] = array(
                '>=' => $date_from,
                '<=' => $date_to,
            );
        }
        $where['b.order_status'] = array( '=' => 'succeeded' );

        $results = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'SUM(b.total_cost) as total_sales,
             SUM(COALESCE(b.disount_amount, 0)) as total_discounts,
             SUM(b.total_cost - COALESCE(b.disount_amount, 0)) as net_before_returns,
             COUNT(DISTINCT b.id) as total_orders,
             SUM(b.total_svc_slots) as services_sold,
             SUM(b.total_ext_svc_slots) as extra_services_sold',
            array(),
            $where,
            'row'
        );

        // Returns
        $returns_where = array(
            't.payment_status' => array( '=' => 'refunded' ),
        );
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $returns_where['t.transaction_created_at'] = array(
                '>=' => $date_from . ' 00:00:00',
                '<=' => $date_to . ' 23:59:59',
            );
        }

        $returns = $dbhandler->get_results_with_join(
            array( 'TRANSACTIONS', 't' ),
            'SUM(t.paid_amount) as total_returns',
            array(),
            $returns_where,
            'row'
        );

        $total_sales     = $results ? floatval( $results->total_sales ) : 0;
        $total_discounts = $results ? floatval( $results->total_discounts ) : 0;
        $total_returns   = $returns ? floatval( $returns->total_returns ) : 0;
        $net_sales       = $total_sales - $total_discounts - $total_returns;

        return array(
            'total_sales'         => $total_sales,
            'total_discounts'     => $total_discounts,
            'total_returns'       => $total_returns,
            'net_sales'           => $net_sales,
            'total_orders'        => $results ? intval( $results->total_orders ) : 0,
            'services_sold'       => $results ? intval( $results->services_sold ) : 0,
            'extra_services_sold' => $results ? intval( $results->extra_services_sold ) : 0,
        );
    }

    /**
     * Get revenue data for a date range.
     */
    public function bm_get_revenue_data_join( $date_from, $date_to ) {
        $dbhandler = new BM_DBhandler();

        $where = array();
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $where['b.booking_date'] = array(
                '>=' => $date_from,
                '<=' => $date_to,
            );
        }
        $where['b.order_status'] = array( '=' => 'succeeded' );

        $results = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'SUM(b.total_cost) as gross_sales,
             SUM(b.total_cost - COALESCE(b.disount_amount, 0)) as net_before_returns,
             COUNT(DISTINCT b.id) as total_orders',
            array(),
            $where,
            'row'
        );

        // Returns
        $returns_where = array(
            't.payment_status' => array( '=' => 'refunded' ),
        );
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $returns_where['t.transaction_created_at'] = array(
                '>=' => $date_from . ' 00:00:00',
                '<=' => $date_to . ' 23:59:59',
            );
        }

        $returns = $dbhandler->get_results_with_join(
            array( 'TRANSACTIONS', 't' ),
            'SUM(t.paid_amount) as total_returns',
            array(),
            $returns_where,
            'row'
        );

        $gross_sales   = $results ? floatval( $results->gross_sales ) : 0;
        $total_returns = $returns ? floatval( $returns->total_returns ) : 0;
        $net_sales     = $gross_sales - $total_returns;

        return array(
            'gross_sales'  => $gross_sales,
            'returns'      => $total_returns,
            'net_sales'    => $net_sales,
            'total_orders' => $results ? intval( $results->total_orders ) : 0,
        );
    }

    /**
     * Daily revenue breakdown for a date range.
     */
    public function bm_get_daily_revenue_join( $date_from, $date_to ) {
        $dbhandler     = new BM_DBhandler();
        $date_range    = $this->bm_generate_date_range( $date_from, $date_to );
        $daily_revenue = array();

        foreach ( $date_range as $date ) {
            $where = array(
                'b.booking_date' => array( '=' => $date ),
                'b.order_status' => array( '=' => 'succeeded' ),
            );

            $results = $dbhandler->get_results_with_join(
                array( 'BOOKING', 'b' ),
                'COUNT(DISTINCT b.id) as orders,
                 SUM(b.total_cost) as gross_sales',
                array(),
                $where,
                'row'
            );

            // Returns for the day
            $returns_where = array(
                't.payment_status'         => array( '=' => 'refunded' ),
                't.transaction_created_at' => array(
                    '>=' => $date . ' 00:00:00',
                    '<=' => $date . ' 23:59:59',
                ),
            );

            $returns = $dbhandler->get_results_with_join(
                array( 'TRANSACTIONS', 't' ),
                'SUM(t.paid_amount) as total_returns',
                array(),
                $returns_where,
                'row'
            );

            $gross_sales   = $results ? floatval( $results->gross_sales ) : 0;
            $total_returns = $returns ? floatval( $returns->total_returns ) : 0;
            $net_sales     = $gross_sales - $total_returns;

            $daily_revenue[] = array(
                'date'        => gmdate( 'd/m/Y', strtotime( $date ) ),
                'orders'      => $results ? intval( $results->orders ) : 0,
                'gross_sales' => $gross_sales,
                'returns'     => $total_returns,
                'net_sales'   => $net_sales,
                'taxes'       => 0,
                'shipping'    => 0,
                'total_sales' => $net_sales,
            );
        }

        return $daily_revenue;
    }

    /**
     * Get aggregated products data (items sold, net sales, orders) with filters.
     */
    public function bm_get_products_data_join( $date_from, $date_to, $category_id = '', $service_id = '' ) {
        $dbhandler = new BM_DBhandler();

        $where = array();
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $where['b.booking_date'] = array(
                '>=' => $date_from,
                '<=' => $date_to,
            );
        }
        $where['b.order_status'] = array( '=' => 'succeeded' );

        if ( $service_id !== '' ) {
            $where['b.service_id'] = array( '=' => $service_id );
        }

        $joins = array();
        if ( $category_id !== '' ) {
            $joins[]                     = array(
                'table' => 'SERVICE',
                'alias' => 's',
                'on'    => 'b.service_id = s.id',
                'type'  => 'LEFT',
            );
            $where['s.service_category'] = array( '=' => $category_id );
        }

        $results = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'SUM(b.total_svc_slots + b.total_ext_svc_slots) as items_sold,
             SUM(b.total_cost) as gross_sales,
             SUM(COALESCE(b.disount_amount, 0)) as total_discounts,
             COUNT(DISTINCT b.id) as total_orders',
            $joins,
            $where,
            'row'
        );

        // Returns
        $returns_where = array(
            't.payment_status' => array( '=' => 'refunded' ),
        );
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $returns_where['t.transaction_created_at'] = array(
                '>=' => $date_from . ' 00:00:00',
                '<=' => $date_to . ' 23:59:59',
            );
        }

        $returns_joins = array();
        if ( $service_id !== '' ) {
            $returns_joins[]                = array(
                'table' => 'BOOKING',
                'alias' => 'b2',
                'on'    => 't.booking_id = b2.id',
                'type'  => 'INNER',
            );
            $returns_where['b2.service_id'] = array( '=' => $service_id );
        }
        if ( $category_id !== '' ) {
            $returns_joins[]                      = array(
                'table' => 'BOOKING',
                'alias' => 'b3',
                'on'    => 't.booking_id = b3.id',
                'type'  => 'INNER',
            );
            $returns_joins[]                      = array(
                'table' => 'SERVICE',
                'alias' => 's2',
                'on'    => 'b3.service_id = s2.id',
                'type'  => 'LEFT',
            );
            $returns_where['s2.service_category'] = array( '=' => $category_id );
        }

        $returns = $dbhandler->get_results_with_join(
            array( 'TRANSACTIONS', 't' ),
            'SUM(t.paid_amount) as total_returns',
            $returns_joins,
            $returns_where,
            'row'
        );

        $items_sold      = $results ? intval( $results->items_sold ) : 0;
        $gross_sales     = $results ? floatval( $results->gross_sales ) : 0;
        $total_discounts = $results ? floatval( $results->total_discounts ) : 0;
        $total_returns   = $returns ? floatval( $returns->total_returns ) : 0;
        $net_sales       = $gross_sales - $total_discounts - $total_returns;

        return array(
            'items_sold'      => $items_sold,
            'gross_sales'     => $gross_sales,
            'total_discounts' => $total_discounts,
            'total_returns'   => $total_returns,
            'net_sales'       => $net_sales,
            'total_orders'    => $results ? intval( $results->total_orders ) : 0,
        );
    }

    /**
     * Get detailed performance for each service (products table).
     */
    public function bm_get_products_performance_join( $date_from, $date_to, $category_id = '', $service_id = '' ) {
        $dbhandler = new BM_DBhandler();

        $where = array();
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $where['b.booking_date'] = array(
                '>=' => $date_from,
                '<=' => $date_to,
            );
        }
        $where['b.order_status'] = array( '=' => 'succeeded' );

        if ( $service_id !== '' ) {
            $where['b.service_id'] = array( '=' => $service_id );
        }

        $joins = array(
            array(
                'table' => 'SERVICE',
                'alias' => 's',
                'on'    => 'b.service_id = s.id',
                'type'  => 'LEFT',
            ),
            array(
                'table' => 'CATEGORY',
                'alias' => 'c',
                'on'    => 's.service_category = c.id',
                'type'  => 'LEFT',
            ),
        );

        if ( $category_id !== '' ) {
            $where['s.service_category'] = array( '=' => $category_id );
        }

        $results = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'b.service_id,
             b.service_name,
             COALESCE(c.cat_name, "Uncategorized") as category,
             SUM(b.total_svc_slots + b.total_ext_svc_slots) as items_sold,
             SUM(b.total_cost) as gross_sales,
             SUM(COALESCE(b.disount_amount, 0)) as total_discounts,
             COUNT(DISTINCT b.id) as orders',
            $joins,
            $where,
            'results',
            0,
            false,
            'items_sold DESC',
            false,
            'GROUP BY b.service_id'
        );

        $products = array();
        if ( ! empty( $results ) && is_array( $results ) ) {
            foreach ( $results as $result ) {
                // Returns per service
                $returns_where = array(
                    't.payment_status' => array( '=' => 'refunded' ),
                );
                if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
                    $returns_where['t.transaction_created_at'] = array(
                        '>=' => $date_from . ' 00:00:00',
                        '<=' => $date_to . ' 23:59:59',
                    );
                }
                $returns_joins                  = array(
                    array(
                        'table' => 'BOOKING',
                        'alias' => 'b2',
                        'on'    => 't.booking_id = b2.id',
                        'type'  => 'INNER',
                    ),
                );
                $returns_where['b2.service_id'] = array( '=' => $result->service_id );

                $returns = $dbhandler->get_results_with_join(
                    array( 'TRANSACTIONS', 't' ),
                    'SUM(t.paid_amount) as service_returns',
                    $returns_joins,
                    $returns_where,
                    'row'
                );

                $gross_sales     = floatval( $result->gross_sales );
                $total_discounts = floatval( $result->total_discounts );
                $service_returns = $returns ? floatval( $returns->service_returns ) : 0;
                $net_sales       = $gross_sales - $total_discounts - $service_returns;

                $products[] = array(
                    'id'         => $result->service_id,
                    'name'       => $result->service_name,
                    'category'   => $result->category,
                    'items_sold' => intval( $result->items_sold ),
                    'net_sales'  => $net_sales,
                    'orders'     => intval( $result->orders ),
                    'visits'     => 0,
                );
            }
        }

        return $products;
    }

    /**
     * Get top categories by services sold.
     */
    public function bm_get_top_categories_join( $date_from, $date_to, $limit = 5 ) {
        $dbhandler = new BM_DBhandler();

        $where = array();
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $where['b.booking_date'] = array(
                '>=' => $date_from,
                '<=' => $date_to,
            );
        }
        $where['b.order_status'] = array( '=' => 'succeeded' );

        $joins = array(
            array(
                'table' => 'SERVICE',
                'alias' => 's',
                'on'    => 'b.service_id = s.id',
                'type'  => 'LEFT',
            ),
            array(
                'table' => 'CATEGORY',
                'alias' => 'c',
                'on'    => 's.service_category = c.id',
                'type'  => 'LEFT',
            ),
        );

        $results = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'COALESCE(c.cat_name, "Uncategorized") as category_name,
             COALESCE(c.id, 0) as category_id,
             SUM(b.total_svc_slots) as services_sold,
             SUM(b.total_cost) as gross_sales,
             SUM(COALESCE(b.disount_amount, 0)) as total_discounts',
            $joins,
            $where,
            'results',
            0,
            $limit,
            'services_sold',
            true,
            'GROUP BY COALESCE(c.id, 0)'
        );

        $categories = array();
        if ( $results ) {
            foreach ( $results as $result ) {
                // Returns per category
                $returns_where = array(
                    't.payment_status' => array( '=' => 'refunded' ),
                );
                if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
                    $returns_where['t.transaction_created_at'] = array(
                        '>=' => $date_from . ' 00:00:00',
                        '<=' => $date_to . ' 23:59:59',
                    );
                }
                $returns_joins = array(
                    array(
                        'table' => 'BOOKING',
                        'alias' => 'b2',
                        'on'    => 't.booking_id = b2.id',
                        'type'  => 'INNER',
                    ),
                    array(
                        'table' => 'SERVICE',
                        'alias' => 's2',
                        'on'    => 'b2.service_id = s2.id',
                        'type'  => 'LEFT',
                    ),
                );
                if ( $result->category_id == 0 ) {
                    $returns_where[] = '(s2.service_category IS NULL OR s2.service_category = 0)';
                } else {
                    $returns_where['s2.service_category'] = array( '=' => $result->category_id );
                }

                $returns = $dbhandler->get_results_with_join(
                    array( 'TRANSACTIONS', 't' ),
                    'SUM(t.paid_amount) as total_returns',
                    $returns_joins,
                    $returns_where,
                    'row'
                );

                $gross_sales    = floatval( $result->gross_sales );
                $discounts      = floatval( $result->total_discounts );
                $returns_amount = $returns ? floatval( $returns->total_returns ) : 0;
                $net_sales      = $gross_sales - $discounts - $returns_amount;

                $categories[] = array(
                    'id'            => $result->category_id,
                    'name'          => $result->category_name,
                    'services_sold' => intval( $result->services_sold ),
                    'net_sales'     => $net_sales,
                );
            }
        }

        return $categories;
    }

    /**
     * Get top services by services sold.
     */
    public function bm_get_top_services_join( $date_from, $date_to, $limit = 5 ) {
        $dbhandler = new BM_DBhandler();

        $where = array();
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $where['b.booking_date'] = array(
                '>=' => $date_from,
                '<=' => $date_to,
            );
        }
        $where['b.order_status'] = array( '=' => 'succeeded' );

        $results = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'b.service_id, b.service_name,
             SUM(b.total_svc_slots) as services_sold,
             SUM(b.total_cost) as gross_sales,
             SUM(COALESCE(b.disount_amount, 0)) as total_discounts',
            array(),
            $where,
            'results',
            0,
            $limit,
            'services_sold',
            true,
            'GROUP BY b.service_id'
        );

        $services = array();
        if ( $results ) {
            foreach ( $results as $result ) {
                // Returns per service
                $returns_where = array(
                    't.payment_status' => array( '=' => 'refunded' ),
                );
                if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
                    $returns_where['t.transaction_created_at'] = array(
                        '>=' => $date_from . ' 00:00:00',
                        '<=' => $date_to . ' 23:59:59',
                    );
                }
                $returns_joins                  = array(
                    array(
                        'table' => 'BOOKING',
                        'alias' => 'b2',
                        'on'    => 't.booking_id = b2.id',
                        'type'  => 'INNER',
                    ),
                );
                $returns_where['b2.service_id'] = array( '=' => $result->service_id );

                $returns = $dbhandler->get_results_with_join(
                    array( 'TRANSACTIONS', 't' ),
                    'SUM(t.paid_amount) as total_returns',
                    $returns_joins,
                    $returns_where,
                    'row'
                );

                $gross_sales    = floatval( $result->gross_sales );
                $discounts      = floatval( $result->total_discounts );
                $returns_amount = $returns ? floatval( $returns->total_returns ) : 0;
                $net_sales      = $gross_sales - $discounts - $returns_amount;

                $services[] = array(
                    'id'            => $result->service_id,
                    'name'          => $result->service_name,
                    'services_sold' => intval( $result->services_sold ),
                    'net_sales'     => $net_sales,
                );
            }
        }

        return $services;
    }

    // ------------------------------------------------------------
    // 3. CHART HELPERS (with comparison type)
    // ------------------------------------------------------------

    /**
     * Get chart data for net sales and orders (overview).
     */
    public function bm_get_chart_data_join( $date_from, $date_to, $compare_from = '', $compare_to = '', $compare_type = 'period' ) {
        $date_range = $this->bm_generate_date_range( $date_from, $date_to );
        $response   = array(
            'chart_labels'            => array(),
            'current_net_sales_data'  => array(),
            'previous_net_sales_data' => array(),
            'current_orders_data'     => array(),
            'previous_orders_data'    => array(),
        );

        foreach ( $date_range as $date ) {
            $response['chart_labels'][] = gmdate( 'd M', strtotime( $date ) );

            $current                              = $this->bm_get_daily_data_join( $date );
            $response['current_net_sales_data'][] = $current['net_sales'];
            $response['current_orders_data'][]    = $current['orders'];

            if ( ! empty( $compare_from ) && ! empty( $compare_to ) ) {
                $prev_date                             = $this->bm_get_comparison_date( $date, $date_from, $date_to, $compare_from, $compare_to, $compare_type );
                $previous                              = $prev_date ? $this->bm_get_daily_data_join( $prev_date ) : array(
					'net_sales' => 0,
					'orders'    => 0,
				);
                $response['previous_net_sales_data'][] = $previous['net_sales'];
                $response['previous_orders_data'][]    = $previous['orders'];
            } else {
                $response['previous_net_sales_data'][] = 0;
                $response['previous_orders_data'][]    = 0;
            }
        }

        return $response;
    }

    /**
     * Get chart data for revenue trends (gross, returns, net).
     */
    public function bm_get_revenue_chart_data_join( $date_from, $date_to ) {
        $date_range = $this->bm_generate_date_range( $date_from, $date_to );
        $response   = array(
            'chart_labels'     => array(),
            'gross_sales_data' => array(),
            'returns_data'     => array(),
            'net_sales_data'   => array(),
        );

        foreach ( $date_range as $date ) {
            $response['chart_labels'][]     = gmdate( 'd M', strtotime( $date ) );
            $daily                          = $this->bm_get_daily_revenue_single_join( $date );
            $response['gross_sales_data'][] = $daily['gross_sales'];
            $response['returns_data'][]     = $daily['returns'];
            $response['net_sales_data'][]   = $daily['net_sales'];
        }

        return $response;
    }

    /**
     * Get chart data for items sold trend.
     */
    public function bm_get_items_sold_chart_data_join( $date_from, $date_to, $compare_from = '', $compare_to = '', $category_id = '', $service_id = '', $compare_type = 'period' ) {
        $date_range = $this->bm_generate_date_range( $date_from, $date_to );
        $response   = array(
            'chart_labels'             => array(),
            'current_items_sold_data'  => array(),
            'previous_items_sold_data' => array(),
        );

        foreach ( $date_range as $date ) {
            $response['chart_labels'][]            = gmdate( 'd M', strtotime( $date ) );
            $current                               = $this->bm_get_daily_items_sold_join( $date, $category_id, $service_id );
            $response['current_items_sold_data'][] = $current;

            if ( ! empty( $compare_from ) && ! empty( $compare_to ) ) {
                $prev_date                              = $this->bm_get_comparison_date( $date, $date_from, $date_to, $compare_from, $compare_to, $compare_type );
                $previous                               = $prev_date ? $this->bm_get_daily_items_sold_join( $prev_date, $category_id, $service_id ) : 0;
                $response['previous_items_sold_data'][] = $previous;
            } else {
                $response['previous_items_sold_data'][] = 0;
            }
        }

        return $response;
    }

    /**
     * Get daily data (orders, net sales) for a single date.
     */
    public function bm_get_daily_data_join( $date ) {
        $dbhandler = new BM_DBhandler();

        $where = array(
            'b.booking_date' => array( '=' => $date ),
            'b.order_status' => array( '=' => 'succeeded' ),
        );

        $results = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'COUNT(DISTINCT b.id) as orders,
             SUM(b.total_cost) as gross_sales,
             SUM(COALESCE(b.disount_amount, 0)) as discounts',
            array(),
            $where,
            'row'
        );

        $returns_where = array(
            't.payment_status'         => array( '=' => 'refunded' ),
            't.transaction_created_at' => array(
                '>=' => $date . ' 00:00:00',
                '<=' => $date . ' 23:59:59',
            ),
        );

        $returns = $dbhandler->get_results_with_join(
            array( 'TRANSACTIONS', 't' ),
            'SUM(t.paid_amount) as total_returns',
            array(),
            $returns_where,
            'row'
        );

        $gross_sales   = $results ? floatval( $results->gross_sales ) : 0;
        $discounts     = $results ? floatval( $results->discounts ) : 0;
        $total_returns = $returns ? floatval( $returns->total_returns ) : 0;
        $net_sales     = $gross_sales - $discounts - $total_returns;

        return array(
            'orders'    => $results ? intval( $results->orders ) : 0,
            'net_sales' => $net_sales,
        );
    }

    /**
     * Get daily revenue breakdown for a single date.
     */
    public function bm_get_daily_revenue_single_join( $date ) {
        $dbhandler = new BM_DBhandler();

        $where = array(
            'b.booking_date' => array( '=' => $date ),
            'b.order_status' => array( '=' => 'succeeded' ),
        );

        $results = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'SUM(b.total_cost) as gross_sales',
            array(),
            $where,
            'row'
        );

        $returns_where = array(
            't.payment_status'         => array( '=' => 'refunded' ),
            't.transaction_created_at' => array(
                '>=' => $date . ' 00:00:00',
                '<=' => $date . ' 23:59:59',
            ),
        );

        $returns = $dbhandler->get_results_with_join(
            array( 'TRANSACTIONS', 't' ),
            'SUM(t.paid_amount) as total_returns',
            array(),
            $returns_where,
            'row'
        );

        $gross_sales   = $results ? floatval( $results->gross_sales ) : 0;
        $total_returns = $returns ? floatval( $returns->total_returns ) : 0;
        $net_sales     = $gross_sales - $total_returns;

        return array(
            'gross_sales' => $gross_sales,
            'returns'     => $total_returns,
            'net_sales'   => $net_sales,
        );
    }

    /**
     * Get daily items sold for a single date, with optional filters.
     */
    public function bm_get_daily_items_sold_join( $date, $category_id = '', $service_id = '' ) {
        $dbhandler = new BM_DBhandler();

        $where = array(
            'b.booking_date' => array( '=' => $date ),
            'b.order_status' => array( '=' => 'succeeded' ),
        );

        if ( $service_id !== '' ) {
            $where['b.service_id'] = array( '=' => $service_id );
        }

        $joins = array();
        if ( $category_id != '' ) {
            $joins[]                     = array(
                'table' => 'SERVICE',
                'alias' => 's',
                'on'    => 'b.service_id = s.id',
                'type'  => 'LEFT',
            );
            $where['s.service_category'] = array( '=' => $category_id );
        }

        $results = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'SUM(b.total_svc_slots + b.total_ext_svc_slots) as items_sold',
            $joins,
            $where,
            'row'
        );

        return $results ? intval( $results->items_sold ) : 0;
    }

    // ------------------------------------------------------------
    // 4. UTILITY HELPERS
    // ------------------------------------------------------------

    /**
     * Calculate percentage change.
     */
    public function bm_calculate_change( $previous, $current ) {
        if ( $previous == 0 ) {
            return $current > 0 ? 100 : 0;
        }
        return round( ( ( $current - $previous ) / $previous ) * 100, 2 );
    }

    /**
     * Generate array of dates between start and end.
     */
    public function bm_generate_date_range( $start_date, $end_date, $format = 'Y-m-d' ) {
        $dates   = array();
        $current = strtotime( $start_date );
        $end     = strtotime( $end_date );

        while ( $current <= $end ) {
            $dates[] = gmdate( $format, $current );
            $current = strtotime( '+1 day', $current );
        }
        return $dates;
    }

    /**
     * Get comparison date for period/year over year.
     */
    public function bm_get_comparison_date( $current_date, $current_start, $current_end, $compare_start, $compare_end, $compare_type = 'period' ) {
        if ( $compare_type === 'year' ) {
            $current_ts      = strtotime( $current_date );
            $compare_ts      = strtotime( $compare_start );
            $year_diff       = date( 'Y', $current_ts ) - date( 'Y', $compare_ts );
            $compare_date_ts = strtotime( $current_date . " -$year_diff years" );
            return date( 'Y-m-d', $compare_date_ts );
        } else {
            $current_start_ts = strtotime( $current_start );
            $current_end_ts   = strtotime( $current_end );
            $current_date_ts  = strtotime( $current_date );
            $compare_start_ts = strtotime( $compare_start );
            $compare_end_ts   = strtotime( $compare_end );

            $days_from_start = ( $current_date_ts - $current_start_ts ) / DAY_IN_SECONDS;
            $compare_date_ts = $compare_start_ts + ( $days_from_start * DAY_IN_SECONDS );

            if ( $compare_date_ts >= $compare_start_ts && $compare_date_ts <= $compare_end_ts ) {
                return date( 'Y-m-d', $compare_date_ts );
            }
            return null;
        }
    }

    // ------------------------------------------------------------
    // 5. NEW DETAIL ENDPOINTS (for clickable metric cards)
    // ------------------------------------------------------------

    /**
     * AJAX callback for detail views (orders, services, extra services, sales).
     * FIXED: Always returns valid DataTables JSON object.
     */
    public function bm_fetch_analytics_detail_callback() {
        $nonce = filter_input( INPUT_POST, 'nonce' );
        if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
            // Return valid JSON even on error
            wp_send_json(
                array(
					'draw'            => 0,
					'recordsTotal'    => 0,
					'recordsFiltered' => 0,
					'data'            => array(),
					'error'           => 'Security check failed',
                )
            );
        }

        $post = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

        $draw     = intval( $post['draw'] ?? 1 );
        $start    = intval( $post['start'] ?? 0 );
        $length   = intval( $post['length'] ?? 20 );
        $per_page = $length;
        $offset   = $start;

        $bmrequests = new BM_Request();
        $metric     = sanitize_text_field( $post['metric'] ?? '' );
        $date_from  = ! empty( $post['date_from'] ) ? $bmrequests->bm_convert_date_format( $post['date_from'], 'd/m/Y', 'Y-m-d' ) : gmdate( 'Y-m-01' );
        $date_to    = ! empty( $post['date_to'] ) ? $bmrequests->bm_convert_date_format( $post['date_to'], 'd/m/Y', 'Y-m-d' ) : gmdate( 'Y-m-t' );
        $orderby    = sanitize_text_field( $post['order_col'] ?? '' );
        $order      = sanitize_text_field( $post['order_dir'] ?? 'DESC' );
        $filters    = isset( $post['filters'] ) ? (array) $post['filters'] : array();

        // Default Empty Response structure
        $response_data = array(
            'draw'            => $draw,
            'recordsTotal'    => 0,
            'recordsFiltered' => 0,
            'data'            => array(),
        );

        try {
            $data = array();

            switch ( $metric ) {
                case 'total_orders':
                case 'orders':
                    $data = $this->bm_get_orders_detail( $date_from, $date_to, $filters, $offset, $per_page, $orderby, $order );
                    break;
                case 'services_sold':
                case 'items_sold':
                    $data = $this->bm_get_services_detail( $date_from, $date_to, $filters, $offset, $per_page, $orderby, $order );
                    break;
                case 'extra_services_sold':
                    $data = $this->bm_get_extra_services_detail( $date_from, $date_to, $filters, $offset, $per_page, $orderby, $order );
                    break;
                default:
                    $data = $this->bm_get_sales_detail( $date_from, $date_to, $filters, $offset, $per_page, $orderby, $order );
                    break;
            }

            if ( ! empty( $data ) && is_array( $data ) ) {
                $response_data['recordsTotal']    = intval( $data['recordsTotal'] ?? 0 );
                $response_data['recordsFiltered'] = intval( $data['recordsFiltered'] ?? 0 );
                $response_data['data']            = is_array( $data['data'] ) ? $data['data'] : array();
            }
		} catch ( Exception $e ) {
            $response_data['error'] = $e->getMessage();
        }

        wp_send_json( $response_data );
    }

    /**
     * Detail: Orders (list of bookings).
     */
    private function bm_get_orders_detail( $date_from, $date_to, $filters, $offset, $limit, $orderby, $order ) {
        $dbhandler = new BM_DBhandler();

        // ------------------------------------------------------------------
        // 1. Base WHERE clause (date range + succeeded orders)
        // ------------------------------------------------------------------
        $where = array();
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $where['b.booking_date'] = array(
                '>=' => $date_from,
                '<=' => $date_to,
            );
        }
        $where['b.order_status'] = array( '=' => 'succeeded' );

        // ------------------------------------------------------------------
        // 2. Apply dynamic filters from frontend
        // ------------------------------------------------------------------
        if ( ! empty( $filters['order_status'] ) ) {
            $where['b.order_status'] = array( '=' => sanitize_text_field( $filters['order_status'] ) );
        }
        if ( ! empty( $filters['customer_name'] ) ) {
            // Use a raw string condition with LIKE
            $where[] = "c.customer_name LIKE '%" . esc_sql( $filters['customer_name'] ) . "%'";
        }
        if ( ! empty( $filters['service_name'] ) ) {
            $where['b.service_name'] = array( 'LIKE', '%' . $filters['service_name'] . '%' );
        }

        // ------------------------------------------------------------------
        // 3. Joins (customers table)
        // ------------------------------------------------------------------
        $joins = array(
            array(
                'table' => 'CUSTOMERS',
                'alias' => 'c',
                'on'    => 'b.customer_id = c.id',
                'type'  => 'LEFT',
            ),
        );

        // ------------------------------------------------------------------
        // 4. Get total count (without LIMIT) for DataTables recordsTotal
        // ------------------------------------------------------------------
        $count_result = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'COUNT(DISTINCT b.id) as total',
            $joins,
            $where,
            'row'
        );
        $total        = $count_result ? intval( $count_result->total ) : 0;

        // ------------------------------------------------------------------
        // 5. Sorting – map frontend column keys to database fields
        // ------------------------------------------------------------------
        $sortable_columns = array(
            'booking_date'    => 'b.booking_date',
            'order_id'        => 'b.id',
            'customer_name'   => 'c.customer_name',
            'service_name'    => 'b.service_name',
            'total_svc_slots' => 'b.total_svc_slots',
            'total_cost'      => 'b.total_cost',
            'disount_amount'  => 'b.disount_amount',
            'order_status'    => 'b.order_status',
        );

        $sort_column = isset( $sortable_columns[ $orderby ] ) ? $sortable_columns[ $orderby ] : 'b.booking_date';
        $sort_order  = ( strtoupper( $order ) === 'DESC' ) ? true : false;

        // ------------------------------------------------------------------
        // 6. Fetch paginated, sorted rows
        // ------------------------------------------------------------------
        $columns = 'b.id, 
                    b.booking_date, 
                    b.service_name, 
                    b.total_svc_slots, 
                    b.total_ext_svc_slots,
                    b.total_cost, 
                    b.disount_amount, 
                    b.order_status, 
                    b.booking_created_at,
                    c.customer_name, 
                    c.customer_email';

        $rows = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            $columns,
            $joins,
            $where,
            'results',
            $offset,
            $limit,
            $sort_column,
            $sort_order
        );

        // ------------------------------------------------------------------
        // 7. Format rows for JSON (convert to array, handle data types)
        // ------------------------------------------------------------------
        $formatted_rows = array();
        if ( $rows ) {
            foreach ( $rows as $row ) {
                $formatted_rows[] = array(
                    'booking_date'        => $row->booking_date,
                    'order_id'            => $row->id,
                    'customer_name'       => $row->customer_name,
                    'service_name'        => $row->service_name,
                    'total_svc_slots'     => intval( $row->total_svc_slots ),
                    'total_ext_svc_slots' => intval( $row->total_ext_svc_slots ),
                    'total_cost'          => floatval( $row->total_cost ),
                    'disount_amount'      => floatval( $row->disount_amount ),
                    'order_status'        => $row->order_status,
                );
            }
        }

        // ------------------------------------------------------------------
        // 8. Column definitions for DataTable and frontend
        // ------------------------------------------------------------------
        $columns_config = array(
            array(
                'key'      => 'booking_date',
                'label'    => 'Date',
                'type'     => 'date',
                'sortable' => true,
            ),
            array(
                'key'      => 'order_id',
                'label'    => 'Order #',
                'type'     => 'text',
                'sortable' => true,
            ),
            array(
                'key'      => 'customer_name',
                'label'    => 'Customer',
                'type'     => 'text',
                'sortable' => true,
            ),
            array(
                'key'      => 'service_name',
                'label'    => 'Service',
                'type'     => 'text',
                'sortable' => true,
            ),
            array(
                'key'      => 'total_svc_slots',
                'label'    => 'Items',
                'type'     => 'number',
                'sortable' => true,
            ),
            array(
                'key'      => 'total_cost',
                'label'    => 'Total',
                'type'     => 'currency',
                'sortable' => true,
            ),
            array(
                'key'      => 'disount_amount',
                'label'    => 'Discount',
                'type'     => 'currency',
                'sortable' => true,
            ),
            array(
                'key'      => 'order_status',
                'label'    => 'Status',
                'type'     => 'text',
                'sortable' => true,
            ),
        );

        // ------------------------------------------------------------------
        // 9. Filter configuration (for frontend filter bar)
        // ------------------------------------------------------------------
        $filter_config = array(
            'order_status'  => array(
                'label'   => 'Order Status',
                'type'    => 'select',
                'options' => array(
                    array(
                        'value' => 'succeeded',
                        'label' => 'Succeeded',
                    ),
                    array(
                        'value' => 'pending',
                        'label' => 'Pending',
                    ),
                    array(
                        'value' => 'cancelled',
                        'label' => 'Cancelled',
                    ),
                ),
            ),
            'customer_name' => array(
                'label' => 'Customer Name',
                'type'  => 'text',
            ),
            'service_name'  => array(
                'label' => 'Service Name',
                'type'  => 'text',
            ),
        );

        // ------------------------------------------------------------------
        // 10. Default visible columns (can be overridden by user preferences)
        // ------------------------------------------------------------------
        $visible_columns = array( 'booking_date', 'order_id', 'customer_name', 'service_name', 'total_svc_slots', 'total_cost', 'order_status' );

        // ------------------------------------------------------------------
        // 11. Return DataTables‑compatible JSON
        // ------------------------------------------------------------------
        // 'draw' is sent by DataTables – we echo it back
        $draw = isset( $_POST['draw'] ) ? intval( $_POST['draw'] ) : 1;

        return array(
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,   // same as total because filters are applied in SQL
            'data'            => $formatted_rows,
            'columns'         => $columns_config,
            'visible_columns' => $visible_columns,
            'filter_config'   => $filter_config,
            'active_filters'  => $filters,
        );
    }

    public function bm_get_orders_analytics( $date_from, $date_to, $compare_from = '', $compare_to = '', $compare_type = 'period', $filters = array() ) {
        $dbhandler = new BM_DBhandler();

        // ---- METRICS (current period) ----
        $current_metrics = $this->bm_get_orders_metrics( $date_from, $date_to, $filters );
        $total_orders    = $current_metrics['total_orders'];
        $total_revenue   = $current_metrics['total_revenue'];
        $avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;

        // ---- METRICS (previous period) ----
        $prev_metrics = ! empty( $compare_from ) && ! empty( $compare_to )
            ? $this->bm_get_orders_metrics( $compare_from, $compare_to, $filters )
            : array(
                'total_orders'  => 0,
                'total_revenue' => 0,
            );

        $response = array(
            'total_orders'           => $total_orders,
            'total_orders_change'    => $this->bm_calculate_change( $prev_metrics['total_orders'], $total_orders ),
            'total_revenue'          => $total_revenue,
            'total_revenue_change'   => $this->bm_calculate_change( $prev_metrics['total_revenue'], $total_revenue ),
            'avg_order_value'        => $avg_order_value,
            'avg_order_value_change' => $this->bm_calculate_change( $prev_metrics['total_revenue'] > 0 ? $prev_metrics['total_revenue'] / $prev_metrics['total_orders'] : 0, $avg_order_value ),
        );

        // ---- CHART DATA ----
        $chart_data = $this->bm_get_orders_chart_data( $date_from, $date_to, $compare_from, $compare_to, $compare_type, $filters );
        $response   = array_merge( $response, $chart_data );

        // ---- TABLE DATA (paginated) ----
        // For the first load we'll get the first 20 rows (pagination will be handled by DataTables via separate endpoint)
        // Here we return a limited set; full pagination will use bm_fetch_analytics_detail_callback.
        $table_data         = $this->bm_get_orders_table_data( $date_from, $date_to, $filters, 0, 20, 'booking_created_at', 'DESC' );
        $response['orders'] = $table_data['data'];

        // ---- FILTER OPTIONS ----
        $response['filters'] = $this->bm_get_orders_filter_options( $date_from, $date_to );

        $response['status'] = true;
        return $response;
    }

    private function bm_get_orders_metrics( $date_from, $date_to, $filters = array() ) {
        $dbhandler = new BM_DBhandler();
        $where     = $this->bm_build_orders_where( $date_from, $date_to, $filters );

        $joins = array(
            array(
                'table' => 'CUSTOMERS',
                'alias' => 'c',
                'on'    => 'b.customer_id = c.id',
                'type'  => 'LEFT',
            ),
            array(
                'table' => 'TRANSACTIONS',
                'alias' => 't',
                'on'    => 'b.id = t.booking_id',
                'type'  => 'LEFT',
            ),
        );

        $result = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'COUNT(DISTINCT b.id) as total_orders, COALESCE(SUM(b.total_cost),0) as total_revenue',
            $joins,
            $where,
            'row'
        );

        return array(
            'total_orders'  => $result ? intval( $result->total_orders ) : 0,
            'total_revenue' => $result ? floatval( $result->total_revenue ) : 0,
        );
    }

    private function bm_build_orders_where( $date_from, $date_to, $filters = array() ) {
        $where = array();
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $where['b.booking_date'] = array(
                '>=' => $date_from,
                '<=' => $date_to,
            );
        }
        $where['b.order_status'] = array( '=' => 'succeeded' ); // adjust as needed

        // Apply multi‑select filters
        if ( ! empty( $filters['customers'] ) && is_array( $filters['customers'] ) ) {
            $where['c.id'] = array( 'IN' => $filters['customers'] );
        }
        if ( ! empty( $filters['services'] ) && is_array( $filters['services'] ) ) {
            $where['b.service_id'] = array( 'IN' => $filters['services'] );
        }
        if ( ! empty( $filters['order_status'] ) && is_array( $filters['order_status'] ) ) {
            $where['b.order_status'] = array( 'IN' => $filters['order_status'] );
        }
        if ( ! empty( $filters['payment_status'] ) && is_array( $filters['payment_status'] ) ) {
            // Need to join transactions
            $where['t.payment_status'] = array( 'IN' => $filters['payment_status'] );
        }
        if ( ! empty( $filters['emails'] ) && is_array( $filters['emails'] ) ) {
            $where['c.customer_email'] = array( 'IN' => $filters['emails'] );
        }

        return $where;
    }

    private function bm_get_orders_chart_data( $date_from, $date_to, $compare_from = '', $compare_to = '', $compare_type = 'period', $filters = array() ) {
        $date_range = $this->bm_generate_date_range( $date_from, $date_to );
        $response   = array(
            'chart_labels'         => array(),
            'current_orders_data'  => array(),
            'previous_orders_data' => array(),
        );

        foreach ( $date_range as $date ) {
            $response['chart_labels'][]        = gmdate( 'd M', strtotime( $date ) );
            $current                           = $this->bm_get_daily_orders_count( $date, $filters );
            $response['current_orders_data'][] = $current;

            if ( ! empty( $compare_from ) && ! empty( $compare_to ) ) {
                $prev_date                          = $this->bm_get_comparison_date( $date, $date_from, $date_to, $compare_from, $compare_to, $compare_type );
                $previous                           = $prev_date ? $this->bm_get_daily_orders_count( $prev_date, $filters ) : 0;
                $response['previous_orders_data'][] = $previous;
            } else {
                $response['previous_orders_data'][] = 0;
            }
        }

        return $response;
    }

    private function bm_get_daily_orders_count( $date, $filters = array() ) {
        $dbhandler = new BM_DBhandler();
        $where     = $this->bm_build_orders_where( $date, $date, $filters );
        $joins     = array(
            array(
                'table' => 'CUSTOMERS',
                'alias' => 'c',
                'on'    => 'b.customer_id = c.id',
                'type'  => 'LEFT',
            ),
            array(
                'table' => 'TRANSACTIONS',
                'alias' => 't',
                'on'    => 'b.id = t.booking_id',
                'type'  => 'LEFT',
            ),
        );

        $result = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'COUNT(DISTINCT b.id) as cnt',
            $joins,
            $where,
            'row'
        );

        return $result ? intval( $result->cnt ) : 0;
    }

    private function bm_get_orders_table_data( $date_from, $date_to, $filters, $offset, $limit, $orderby, $order ) {
        $dbhandler = new BM_DBhandler();
        $where     = $this->bm_build_orders_where( $date_from, $date_to, $filters );

        $joins = array(
            array(
                'table' => 'CUSTOMERS',
                'alias' => 'c',
                'on'    => 'b.customer_id = c.id',
                'type'  => 'LEFT',
            ),
            array(
                'table' => 'TRANSACTIONS',
                'alias' => 't',
                'on'    => 'b.id = t.booking_id',
                'type'  => 'LEFT',
            ),
        );

        // Columns needed for the table – note: we fetch billing_details, not individual fields
        $columns = 'b.id, b.service_name, b.booking_created_at, b.booking_date,
                    c.billing_details, c.customer_email,
                    b.total_svc_slots, b.total_ext_svc_slots, b.service_cost, b.extra_svc_cost,
                    b.disount_amount, b.total_cost, b.order_status, t.payment_status';

        $sortable = array(
            'booking_created_at' => 'b.booking_created_at',
            'booking_date'       => 'b.booking_date',
            'service_name'       => 'b.service_name',
            'total_cost'         => 'b.total_cost',
            'order_status'       => 'b.order_status',
            'payment_status'     => 't.payment_status',
        );
        $sort_col = isset( $sortable[ $orderby ] ) ? $sortable[ $orderby ] : 'b.booking_created_at';
        $sort_dir = ( strtoupper( $order ) === 'DESC' ) ? true : false;

        $rows = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            $columns,
            $joins,
            $where,
            'results',
            $offset,
            $limit,
            $sort_col,
            $sort_dir
        );

        $count_result = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'COUNT(DISTINCT b.id) as total',
            $joins,
            $where,
            'row'
        );
        $total        = $count_result ? intval( $count_result->total ) : 0;

        $data = array();
        if ( $rows ) {
            foreach ( $rows as $row ) {
                // Unserialize billing details
                $billing = maybe_unserialize( $row->billing_details );
                if ( ! is_array( $billing ) ) {
                    $billing = array();
                }

                $first_name = isset( $billing['billing_first_name'] ) ? $billing['billing_first_name'] : '';
                $last_name  = isset( $billing['billing_last_name'] ) ? $billing['billing_last_name'] : '';
                $contact_no = isset( $billing['billing_contact'] ) ? $billing['billing_contact'] : '';
                $email      = ! empty( $row->customer_email ) ? $row->customer_email : ( isset( $billing['billing_email'] ) ? $billing['billing_email'] : '' );

                $data[] = array(
                    'orderId'             => $row->id,
                    'service_name'        => $row->service_name,
                    'booking_created_at'  => $row->booking_created_at,
                    'booking_date'        => $row->booking_date,
                    'first_name'          => $first_name,
                    'last_name'           => $last_name,
                    'contact_no'          => $contact_no,
                    'email_address'       => $email,
                    'total_svc_slots'     => intval( $row->total_svc_slots ),
                    'total_ext_svc_slots' => intval( $row->total_ext_svc_slots ),
                    'service_cost'        => floatval( $row->service_cost ),
                    'extra_svc_cost'      => floatval( $row->extra_svc_cost ),
                    'disount_amount'      => floatval( $row->disount_amount ),
                    'total_cost'          => floatval( $row->total_cost ),
                    'order_status'        => $row->order_status,
                    'payment_status'      => $row->payment_status,
                );
            }
        }

        return array(
            'total' => $total,
            'data'  => $data,
        );
    }

    private function bm_get_orders_filter_options( $date_from, $date_to ) {
        $dbhandler = new BM_DBhandler();
        $where     = array();
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $where['b.booking_date'] = array(
                '>=' => $date_from,
                '<=' => $date_to,
            );
        }

        // Customers
        $joins_cust = array(
			array(
				'table' => 'CUSTOMERS',
				'alias' => 'c',
				'on'    => 'b.customer_id = c.id',
				'type'  => 'INNER',
			),
		);
        $cust_rows  = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'DISTINCT c.id as value, c.customer_name as label',
            $joins_cust,
            $where,
            'results'
        );
        $customers  = array();
        if ( $cust_rows ) {
            foreach ( $cust_rows as $row ) {
                $customers[] = array(
					'value' => $row->value,
					'label' => $row->label,
				);
            }
        }

        // Services
        $service_rows = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'DISTINCT b.service_id as value, b.service_name as label',
            array(),
            $where,
            'results'
        );
        $services     = array();
        if ( $service_rows ) {
            foreach ( $service_rows as $row ) {
                $services[] = array(
					'value' => $row->value,
					'label' => $row->label,
				);
            }
        }

        // Order statuses (from bookings)
        $status_rows    = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'DISTINCT b.order_status as value',
            array(),
            $where,
            'results'
        );
        $order_statuses = array();
        if ( $status_rows ) {
            foreach ( $status_rows as $row ) {
                $order_statuses[] = array(
					'value' => $row->value,
					'label' => ucfirst( $row->value ),
				);
            }
        }

        // Payment statuses (from transactions)
        $joins_pay        = array(
			array(
				'table' => 'TRANSACTIONS',
				'alias' => 't',
				'on'    => 'b.id = t.booking_id',
				'type'  => 'INNER',
			),
		);
        $pay_rows         = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'DISTINCT t.payment_status as value',
            $joins_pay,
            $where,
            'results'
        );
        $payment_statuses = array();
        if ( $pay_rows ) {
            foreach ( $pay_rows as $row ) {
                $payment_statuses[] = array(
					'value' => $row->value,
					'label' => ucfirst( $row->value ),
				);
            }
        }

        // Emails (from customers table)
        $email_rows = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'DISTINCT c.customer_email as value',
            $joins_cust,
            $where,
            'results'
        );
        $emails     = array();
        if ( $email_rows ) {
            foreach ( $email_rows as $row ) {
                $emails[] = array(
					'value' => $row->value,
					'label' => $row->value,
				);
            }
        }

        return array(
            'customers'        => $customers,
            'services'         => $services,
            'order_statuses'   => $order_statuses,
            'payment_statuses' => $payment_statuses,
            'emails'           => $emails,
        );
    }

    private function bm_get_services_detail( $date_from, $date_to, $filters, $offset, $limit, $orderby, $order ) {
        $dbhandler = new BM_DBhandler();

        // ------------------------------------------------------------------
        // 1. Base WHERE clause (date range + succeeded orders)
        // ------------------------------------------------------------------
        $where = array();
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $where['b.booking_date'] = array(
                '>=' => $date_from,
                '<=' => $date_to,
            );
        }
        $where['b.order_status'] = array( '=' => 'succeeded' );

        // ------------------------------------------------------------------
        // 2. Apply dynamic filters
        // ------------------------------------------------------------------
        if ( ! empty( $filters['service_name'] ) ) {
            $where['b.service_name'] = array( 'LIKE', '%' . $filters['service_name'] . '%' );
        }
        if ( ! empty( $filters['category'] ) ) {
            if ( $filters['category'] === 'Uncategorized' ) {
                $where[] = '(s.service_category IS NULL OR s.service_category = 0)';
            } else {
                $where['s.service_category'] = array( '=', intval( $filters['category'] ) );
            }
        }

        // ------------------------------------------------------------------
        // 3. Joins (service and category)
        // ------------------------------------------------------------------
        $joins = array(
            array(
                'table' => 'SERVICE',
                'alias' => 's',
                'on'    => 'b.service_id = s.id',
                'type'  => 'LEFT',
            ),
            array(
                'table' => 'CATEGORY',
                'alias' => 'c',
                'on'    => 's.service_category = c.id',
                'type'  => 'LEFT',
            ),
        );

        // ------------------------------------------------------------------
        // 4. Get total distinct services (recordsTotal)
        // ------------------------------------------------------------------
        $count_result = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            'COUNT(DISTINCT b.service_id) as total',
            $joins,
            $where,
            'row'
        );
        $total        = $count_result ? intval( $count_result->total ) : 0;

        // ------------------------------------------------------------------
        // 5. Sorting – map frontend column keys to SQL expressions
        // ------------------------------------------------------------------
        $sortable_columns = array(
            'service_name' => 'b.service_name',
            'category'     => 'category',          // alias from SELECT
            'items_sold'   => 'items_sold',        // alias from SELECT
            'net_sales'    => 'net_sales',         // will be calculated
            'orders'       => 'orders',            // alias from SELECT
            'gross_sales'  => 'gross_sales',       // alias from SELECT
            'discounts'    => 'total_discounts',   // alias from SELECT
            'returns'      => 'returns',           // will be calculated
        );

        $sort_column = isset( $sortable_columns[ $orderby ] ) ? $sortable_columns[ $orderby ] : 'items_sold';
        $sort_order  = ( strtoupper( $order ) === 'DESC' ) ? true : false;

        // ------------------------------------------------------------------
        // 6. Fetch aggregated data per service (with pagination)
        // ------------------------------------------------------------------
        $columns    = 'b.service_id,
                    b.service_name,
                    COALESCE(c.cat_name, "Uncategorized") as category,
                    SUM(b.total_svc_slots + b.total_ext_svc_slots) as items_sold,
                    SUM(b.total_cost) as gross_sales,
                    SUM(COALESCE(b.disount_amount, 0)) as total_discounts,
                    COUNT(DISTINCT b.id) as orders';
        $additional = 'GROUP BY b.service_id';

        $results = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            $columns,
            $joins,
            $where,
            'results',
            $offset,
            $limit,
            $sort_column,
            $sort_order,
            $additional
        );

        // ------------------------------------------------------------------
        // 7. Format rows – calculate net sales (gross - discounts - returns)
        // ------------------------------------------------------------------
        $formatted_rows = array();
        if ( $results ) {
            foreach ( $results as $result ) {
                // Get returns for this service
                $returns_where = array(
                    't.payment_status' => array( '=', 'refunded' ),
                );
                if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
                    $returns_where['t.transaction_created_at'] = array(
                        '>=' => $date_from . ' 00:00:00',
                        '<=' => $date_to . ' 23:59:59',
                    );
                }
                $returns_joins                  = array(
                    array(
                        'table' => 'BOOKING',
                        'alias' => 'b2',
                        'on'    => 't.booking_id = b2.id',
                        'type'  => 'INNER',
                    ),
                );
                $returns_where['b2.service_id'] = array( '=', $result->service_id );

                $returns = $dbhandler->get_results_with_join(
                    array( 'TRANSACTIONS', 't' ),
                    'SUM(t.paid_amount) as service_returns',
                    $returns_joins,
                    $returns_where,
                    'row'
                );

                $gross_sales     = floatval( $result->gross_sales );
                $total_discounts = floatval( $result->total_discounts );
                $service_returns = $returns ? floatval( $returns->service_returns ) : 0;
                $net_sales       = $gross_sales - $total_discounts - $service_returns;

                $formatted_rows[] = array(
                    'service_id'   => $result->service_id,
                    'service_name' => $result->service_name,
                    'category'     => $result->category,
                    'items_sold'   => intval( $result->items_sold ),
                    'net_sales'    => $net_sales,
                    'orders'       => intval( $result->orders ),
                    'gross_sales'  => $gross_sales,
                    'discounts'    => $total_discounts,
                    'returns'      => $service_returns,
                );
            }
        }

        // ------------------------------------------------------------------
        // 8. Column definitions
        // ------------------------------------------------------------------
        $columns_config = array(
            array(
                'key'      => 'service_name',
                'label'    => 'Service',
                'type'     => 'text',
                'sortable' => true,
            ),
            array(
                'key'      => 'category',
                'label'    => 'Category',
                'type'     => 'text',
                'sortable' => true,
            ),
            array(
                'key'      => 'items_sold',
                'label'    => 'Items Sold',
                'type'     => 'number',
                'sortable' => true,
            ),
            array(
                'key'      => 'net_sales',
                'label'    => 'Net Sales',
                'type'     => 'currency',
                'sortable' => true,
            ),
            array(
                'key'      => 'orders',
                'label'    => 'Orders',
                'type'     => 'number',
                'sortable' => true,
            ),
            array(
                'key'      => 'gross_sales',
                'label'    => 'Gross Sales',
                'type'     => 'currency',
                'sortable' => true,
            ),
            array(
                'key'      => 'discounts',
                'label'    => 'Discounts',
                'type'     => 'currency',
                'sortable' => true,
            ),
            array(
                'key'      => 'returns',
                'label'    => 'Returns',
                'type'     => 'currency',
                'sortable' => true,
            ),
        );

        // ------------------------------------------------------------------
        // 9. Filter configuration
        // ------------------------------------------------------------------
        $filter_config = array(
            'service_name' => array(
                'label' => 'Service Name',
                'type'  => 'text',
            ),
            'category'     => array(
                'label'   => 'Category',
                'type'    => 'select',
                'options' => $this->bm_get_category_options(),
            ),
        );

        // ------------------------------------------------------------------
        // 10. Default visible columns
        // ------------------------------------------------------------------
        $visible_columns = array( 'service_name', 'category', 'items_sold', 'net_sales', 'orders' );

        // ------------------------------------------------------------------
        // 11. Return DataTables JSON
        // ------------------------------------------------------------------
        $draw = isset( $_POST['draw'] ) ? intval( $_POST['draw'] ) : 1;

        return array(
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $formatted_rows,
            'columns'         => $columns_config,
            'visible_columns' => $visible_columns,
            'filter_config'   => $filter_config,
            'active_filters'  => $filters,
        );
    }

    private function bm_get_extra_services_detail( $date_from, $date_to, $filters, $offset, $limit, $orderby, $order ) {
        $dbhandler = new BM_DBhandler();

        // ------------------------------------------------------------------
        // 1. Base WHERE clause (date range)
        // ------------------------------------------------------------------
        $where = array();
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $where['es.booking_date'] = array(
                '>=' => $date_from,
                '<=' => $date_to,
            );
        }

        // ------------------------------------------------------------------
        // 2. Joins (booking + extra)
        // ------------------------------------------------------------------
        $joins                   = array(
            array(
                'table' => 'BOOKING',
                'alias' => 'b',
                'on'    => 'es.booking_id = b.id',
                'type'  => 'INNER',
            ),
            array(
                'table' => 'EXTRA',
                'alias' => 'e',
                'on'    => 'es.extra_svc_id = e.id',
                'type'  => 'LEFT',
            ),
        );
        $where['b.order_status'] = array( '=', 'succeeded' );

        // ------------------------------------------------------------------
        // 3. Apply dynamic filters
        // ------------------------------------------------------------------
        if ( ! empty( $filters['extra_name'] ) ) {
            $where['e.extra_name'] = array( 'LIKE', '%' . $filters['extra_name'] . '%' );
        }
        if ( ! empty( $filters['service_name'] ) ) {
            $where['b.service_name'] = array( 'LIKE', '%' . $filters['service_name'] . '%' );
        }

        // ------------------------------------------------------------------
        // 4. Get total distinct extra services (recordsTotal)
        // ------------------------------------------------------------------
        $count_result = $dbhandler->get_results_with_join(
            array( 'EXTRASLOTCOUNT', 'es' ),
            'COUNT(DISTINCT es.extra_svc_id) as total',
            $joins,
            $where,
            'row'
        );
        $total        = $count_result ? intval( $count_result->total ) : 0;

        // ------------------------------------------------------------------
        // 5. Sorting – map frontend column keys to SQL expressions
        // ------------------------------------------------------------------
        $sortable_columns = array(
            'extra_name'    => 'e.extra_name',
            'service_name'  => 'b.service_name',
            'slots_booked'  => 'slots_booked',
            'total_revenue' => 'total_revenue',
        );

        $sort_column = isset( $sortable_columns[ $orderby ] ) ? $sortable_columns[ $orderby ] : 'slots_booked';
        $sort_order  = ( strtoupper( $order ) === 'DESC' ) ? true : false;

        // ------------------------------------------------------------------
        // 6. Fetch aggregated data per extra service
        // ------------------------------------------------------------------
        $columns    = 'e.id as extra_id,
                    e.extra_name,
                    b.service_name,
                    SUM(es.slots_booked) as slots_booked,
                    SUM(es.slots_booked * e.extra_price) as total_revenue';
        $additional = 'GROUP BY e.id';

        $results = $dbhandler->get_results_with_join(
            array( 'EXTRASLOTCOUNT', 'es' ),
            $columns,
            $joins,
            $where,
            'results',
            $offset,
            $limit,
            $sort_column,
            $sort_order,
            $additional
        );

        // ------------------------------------------------------------------
        // 7. Format rows
        // ------------------------------------------------------------------
        $formatted_rows = array();
        if ( $results ) {
            foreach ( $results as $result ) {
                $formatted_rows[] = array(
                    'extra_id'      => $result->extra_id,
                    'extra_name'    => $result->extra_name,
                    'service_name'  => $result->service_name,
                    'slots_booked'  => intval( $result->slots_booked ),
                    'total_revenue' => floatval( $result->total_revenue ),
                );
            }
        }

        // ------------------------------------------------------------------
        // 8. Column definitions
        // ------------------------------------------------------------------
        $columns_config = array(
            array(
                'key'      => 'extra_name',
                'label'    => 'Extra Service',
                'type'     => 'text',
                'sortable' => true,
            ),
            array(
                'key'      => 'service_name',
                'label'    => 'Booked with Service',
                'type'     => 'text',
                'sortable' => true,
            ),
            array(
                'key'      => 'slots_booked',
                'label'    => 'Slots Sold',
                'type'     => 'number',
                'sortable' => true,
            ),
            array(
                'key'      => 'total_revenue',
                'label'    => 'Revenue',
                'type'     => 'currency',
                'sortable' => true,
            ),
        );

        // ------------------------------------------------------------------
        // 9. Filter configuration
        // ------------------------------------------------------------------
        $filter_config = array(
            'extra_name'   => array(
                'label' => 'Extra Service Name',
                'type'  => 'text',
            ),
            'service_name' => array(
                'label' => 'Service Name',
                'type'  => 'text',
            ),
        );

        // ------------------------------------------------------------------
        // 10. Default visible columns
        // ------------------------------------------------------------------
        $visible_columns = array( 'extra_name', 'service_name', 'slots_booked', 'total_revenue' );

        // ------------------------------------------------------------------
        // 11. Return DataTables JSON
        // ------------------------------------------------------------------
        $draw = isset( $_POST['draw'] ) ? intval( $_POST['draw'] ) : 1;

        return array(
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $formatted_rows,
            'columns'         => $columns_config,
            'visible_columns' => $visible_columns,
            'filter_config'   => $filter_config,
            'active_filters'  => $filters,
        );
    }

    /**
     * Detail: Sales / Transactions
     * Optimized to return only the raw data required by the new JS implementation.
     */
    private function bm_get_sales_detail( $date_from, $date_to, $filters, $offset, $limit, $orderby, $order ) {
        $dbhandler = new BM_DBhandler();

        $where = array();
        if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
            $where['t.transaction_created_at'] = array(
                '>=' => $date_from . ' 00:00:00',
                '<=' => $date_to . ' 23:59:59',
            );
        }

        $joins = array(
            array(
				'table' => 'BOOKING',
				'alias' => 'b',
				'on'    => 't.booking_id = b.id',
				'type'  => 'INNER',
			),
            array(
				'table' => 'CUSTOMERS',
				'alias' => 'c',
				'on'    => 'c.id = b.customer_id',
				'type'  => 'INNER',
			),
        );

        if ( ! empty( $filters['payment_method'] ) ) {
            $where['t.payment_method'] = array( '=', sanitize_text_field( $filters['payment_method'] ) );
        }
        if ( ! empty( $filters['payment_status'] ) ) {
            $where['t.payment_status'] = array( '=', sanitize_text_field( $filters['payment_status'] ) );
        }

        $count_result = $dbhandler->get_results_with_join(
            array( 'TRANSACTIONS', 't' ),
            'COUNT(DISTINCT t.id) as total',
            $joins,
            $where,
            'row'
        );
        $total        = $count_result ? intval( $count_result->total ) : 0;

        // Sort mapping
        $sortable_columns = array(
            'date'           => 't.transaction_created_at',
            'transaction_id' => 't.id',
            'customer_name'  => 'c.customer_name',
            'service_name'   => 'b.service_name',
            'paid_amount'    => 't.paid_amount',
            'net_sales'      => 't.paid_amount',
            'payment_method' => 't.payment_method',
            'payment_status' => 't.payment_status',
        );

        $sort_column = isset( $sortable_columns[ $orderby ] ) ? $sortable_columns[ $orderby ] : 't.transaction_created_at';
        $is_desc     = ( strtoupper( $order ) === 'DESC' );

        $columns = 't.id as transaction_id, t.booking_id, t.paid_amount, t.payment_method, t.payment_status, t.transaction_created_at, b.service_name, c.customer_name';

        $rows = $dbhandler->get_results_with_join(
            array( 'TRANSACTIONS', 't' ),
            $columns,
            $joins,
            $where,
            'results',
            $offset,
            $limit,
            $sort_column,
            $is_desc
        );

        $formatted_rows = array();
        if ( $rows ) {
            foreach ( $rows as $row ) {
                $formatted_rows[] = array(
                    'date'           => $row->transaction_created_at,
                    'transaction_id' => $row->transaction_id,
                    'customer_name'  => $row->customer_name,
                    'service_name'   => $row->service_name,
                    'paid_amount'    => floatval( $row->paid_amount ),
                    'net_sales'      => floatval( $row->paid_amount ),
                    'payment_method' => ucfirst( $row->payment_method ),
                    'payment_status' => ucfirst( $row->payment_status ),
                    'booking_id'     => $row->booking_id,
                );
            }
        }

        return array(
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $formatted_rows,
        );
    }

    /**
     * Helper: Get category options for filter dropdown.
     */
    private function bm_get_category_options() {
        $dbhandler  = new BM_DBhandler();
        $categories = $dbhandler->get_all_result( 'CATEGORY', '*', 1, 'results', 0, false, 'cat_position', false );
        $options    = array(
			array(
				'value' => 'Uncategorized',
				'label' => 'Uncategorized',
			),
		);
        if ( $categories ) {
            foreach ( $categories as $cat ) {
                $options[] = array(
                    'value' => $cat->id,
                    'label' => $cat->cat_name,
                );
            }
        }
        return $options;
    }

    /**
     * Helper: Get distinct payment methods for filter dropdown.
     *
     * @return array Options array for select filter.
     */
    private function bm_get_payment_method_options() {
        $dbhandler = new BM_DBhandler();

        // Use get_results_with_join to fetch distinct payment_method
        $results = $dbhandler->get_results_with_join(
            array( 'TRANSACTIONS', 't' ),
            'DISTINCT t.payment_method',
            array(),
            array(),
            'results',
            0,
            false,
            null,
            false,
            '',
            true,
        );

        $options = array();
        if ( $results ) {
            foreach ( $results as $row ) {
                if ( ! empty( $row->payment_method ) ) {
                    $options[] = array(
                        'value' => $row->payment_method,
                        'label' => ucfirst( $row->payment_method ),
                    );
                }
            }
        }

        return $options;
    }

    // ------------------------------------------------------------
    // 6. CSV DOWNLOAD
    // ------------------------------------------------------------

    public function bm_download_analytics_csv_callback() {
        $nonce = filter_input( INPUT_POST, 'nonce' );
        if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
            die( esc_html__( 'Failed security check', 'service-booking' ) );
        }

        $bmrequests  = new BM_Request();
        $post        = isset( $_POST['post'] ) ? json_decode( wp_unslash( $_POST['post'] ), true ) : array();
        $action_type = ! empty( $post['action_type'] ) ? sanitize_text_field( $post['action_type'] ) : '';

        $date_from = ! empty( $post['date_from'] ) ? $bmrequests->bm_convert_date_format( $post['date_from'], 'd/m/Y', 'Y-m-d' ) : gmdate( 'Y-m-01' );
        $date_to   = ! empty( $post['date_to'] ) ? $bmrequests->bm_convert_date_format( $post['date_to'], 'd/m/Y', 'Y-m-d' ) : gmdate( 'Y-m-t' );
        $filters   = isset( $post['filters'] ) ? (array) $post['filters'] : array();

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=analytics_' . $action_type . '_' . gmdate( 'Y-m-d' ) . '.csv' );

        $output = fopen( 'php://output', 'w' );

        switch ( $action_type ) {
            case 'download_revenue_csv':
                $this->bm_generate_revenue_csv( $output, $date_from, $date_to );
                break;
            case 'download_products_csv':
                $category_id = ! empty( $post['category_id'] ) ? intval( $post['category_id'] ) : '';
                $service_id  = ! empty( $post['service_id'] ) ? intval( $post['service_id'] ) : '';
                $this->bm_generate_products_csv( $output, $date_from, $date_to, $category_id, $service_id );
                break;
            case 'download_detail_csv':
                $metric = ! empty( $post['metric'] ) ? sanitize_text_field( $post['metric'] ) : '';
                $this->bm_generate_detail_csv( $output, $date_from, $date_to, $metric, $filters );
                break;
            case 'download_orders_csv':
                $filters = isset( $post['filters'] ) ? (array) $post['filters'] : array();
                $this->bm_generate_orders_csv( $output, $date_from, $date_to, $filters );
                break;
        }

        fclose( $output );
        wp_die();
    }

    private function bm_generate_orders_csv( $output, $date_from, $date_to, $filters ) {
        $data    = $this->bm_get_orders_table_data( $date_from, $date_to, $filters, 0, 999999, '', 'ASC' );
        $headers = array( 'Ordered Service', 'Ordered Date', 'Service Date', 'First Name', 'Last Name', 'Contact', 'Email', 'Service Participants', 'Extra Participants', 'Service Cost', 'Extra Cost', 'Discount', 'Total', 'Order Status', 'Payment Status' );
        fputcsv( $output, $headers );
        foreach ( $data['data'] as $row ) {
            fputcsv(
                $output,
                array(
                    $row['service_name'],
                    $row['booking_created_at'],
                    $row['booking_date'],
                    $row['first_name'],
                    $row['last_name'],
                    $row['contact_no'],
                    $row['email_address'],
                    $row['total_svc_slots'],
                    $row['total_ext_svc_slots'],
                    $row['service_cost'],
                    $row['extra_svc_cost'],
                    $row['disount_amount'],
                    $row['total_cost'],
                    $row['order_status'],
                    $row['payment_status'],
                )
            );
        }
    }

    /**
     * Generate CSV for detail view (all records, no pagination).
     */
    private function bm_generate_detail_csv( $output, $date_from, $date_to, $metric, $filters ) {
        switch ( $metric ) {
            case 'total_orders':
            case 'orders':
                $data    = $this->bm_get_orders_detail( $date_from, $date_to, $filters, 0, 999999, '', 'ASC' );
                $rows    = $data['rows'];
                $headers = array( 'Date', 'Order ID', 'Customer', 'Service', 'Items', 'Total', 'Discount', 'Status' );
                fputcsv( $output, $headers );
                foreach ( $rows as $row ) {
                    fputcsv(
                        $output,
                        array(
							$row['booking_date'],
							$row['order_id'],
							$row['customer_name'],
							$row['service_name'],
							$row['total_svc_slots'],
							$row['total_cost'],
							$row['disount_amount'],
							$row['order_status'],
                        )
                    );
                }
                break;

            case 'services_sold':
                $data    = $this->bm_get_services_detail( $date_from, $date_to, $filters, 0, 999999, '', 'ASC' );
                $rows    = $data['rows'];
                $headers = array( 'Service', 'Category', 'Items Sold', 'Net Sales', 'Orders', 'Gross Sales', 'Discounts', 'Returns' );
                fputcsv( $output, $headers );
                foreach ( $rows as $row ) {
                    fputcsv(
                        $output,
                        array(
							$row['service_name'],
							$row['category'],
							$row['items_sold'],
							$row['net_sales'],
							$row['orders'],
							$row['gross_sales'],
							$row['discounts'],
							$row['returns'],
                        )
                    );
                }
                break;

            case 'extra_services_sold':
                $data    = $this->bm_get_extra_services_detail( $date_from, $date_to, $filters, 0, 999999, '', 'ASC' );
                $rows    = $data['rows'];
                $headers = array( 'Extra Service', 'Booked with Service', 'Slots Sold', 'Revenue' );
                fputcsv( $output, $headers );
                foreach ( $rows as $row ) {
                    fputcsv(
                        $output,
                        array(
							$row['extra_name'],
							$row['service_name'],
							$row['slots_booked'],
							$row['total_revenue'],
                        )
                    );
                }
                break;

            case 'total_sales':
            case 'net_sales':
                $data    = $this->bm_get_sales_detail( $date_from, $date_to, $filters, 0, 999999, '', 'ASC' );
                $rows    = $data['rows'];
                $headers = array( 'Date', 'Transaction ID', 'Customer', 'Service', 'Paid Amount', 'Net Sales', 'Payment Method', 'Status' );
                fputcsv( $output, $headers );
                foreach ( $rows as $row ) {
                    fputcsv(
                        $output,
                        array(
							$row['date'],
							$row['transaction_id'],
							$row['customer_name'],
							$row['service_name'],
							$row['paid_amount'],
							$row['net_sales'],
							$row['payment_method'],
							$row['payment_status'],
                        )
                    );
                }
                break;
        }
    }

    public function bm_generate_revenue_csv( $output, $date_from, $date_to ) {
        fputcsv( $output, array( 'Date', 'Orders', 'Gross Sales', 'Returns', 'Coupons', 'Net Sales', 'Taxes', 'Shipping', 'Total Sales' ) );
        $daily_revenue = $this->bm_get_daily_revenue_join( $date_from, $date_to );
        foreach ( $daily_revenue as $day ) {
            fputcsv(
                $output,
                array(
					$day['date'],
					$day['orders'],
					$day['gross_sales'],
					$day['returns'],
					$day['net_sales'],
					$day['taxes'],
					$day['shipping'],
					$day['total_sales'],
                )
            );
        }
    }

    public function bm_generate_products_csv( $output, $date_from, $date_to, $category_id = '', $service_id = '' ) {
        fputcsv( $output, array( 'Service', 'Category', 'Items Sold', 'Net Sales', 'Orders', 'Average Order Value', 'Conversion Rate' ) );
        $products = $this->bm_get_products_performance_join( $date_from, $date_to, $category_id, $service_id );
        foreach ( $products as $product ) {
            $avg_order_value = $product['orders'] > 0 ? $product['net_sales'] / $product['orders'] : 0;
            $conversion_rate = $product['visits'] > 0 ? ( $product['orders'] / $product['visits'] ) * 100 : 0;
            fputcsv(
                $output,
                array(
					$product['name'],
					$product['category'],
					$product['items_sold'],
					$product['net_sales'],
					$product['orders'],
					$avg_order_value,
					$conversion_rate . '%',
                )
            );
        }
    }

    public function bm_get_metric_chart_data( $date_from, $date_to, $compare_from, $compare_to, $compare_type, $metric ) {
        $date_range = $this->bm_generate_date_range( $date_from, $date_to );
        $response   = array(
            'chart_labels'  => array(),
            'current_data'  => array(),
            'previous_data' => array(),
        );

        foreach ( $date_range as $date ) {
            $response['chart_labels'][] = gmdate( 'd M', strtotime( $date ) );
            $current                    = $this->bm_get_daily_metric_value( $date, $metric );
            $response['current_data'][] = $current;

            if ( ! empty( $compare_from ) && ! empty( $compare_to ) ) {
                $prev_date                   = $this->bm_get_comparison_date( $date, $date_from, $date_to, $compare_from, $compare_to, $compare_type );
                $previous                    = $prev_date ? $this->bm_get_daily_metric_value( $prev_date, $metric ) : 0;
                $response['previous_data'][] = $previous;
            } else {
                $response['previous_data'][] = 0;
            }
        }

        $response['status'] = true;
        return $response;
    }

    /**
     * Get daily metric value for a specific date using the custom join handler.
     *
     * @param string $date   Date in Y-m-d format.
     * @param string $metric Metric identifier.
     * @return float
     */
    private function bm_get_daily_metric_value( $date, $metric ) {
        $dbhandler          = new BM_DBhandler();
        $activator          = new Booking_Management_Activator();
        $transactions_table = $activator->get_db_table_name( 'TRANSACTIONS' );

        $metric_map = array(
            'total_sales'         => 'SUM(b.total_cost)',
            'net_sales'           => "SUM(b.total_cost - COALESCE(b.disount_amount, 0)) - COALESCE((SELECT SUM(t.paid_amount) FROM $transactions_table t WHERE t.booking_id = b.id AND t.payment_status = 'refunded' AND DATE(t.transaction_created_at) = '__DATE__'), 0)",
            'total_orders'        => 'COUNT(DISTINCT b.id)',
            'services_sold'       => 'SUM(b.total_svc_slots)',
            'extra_services_sold' => 'SUM(b.total_ext_svc_slots)',
            'gross_sales'         => 'SUM(b.total_cost)',
            'returns'             => "COALESCE((SELECT SUM(t.paid_amount) FROM $transactions_table t WHERE t.booking_id = b.id AND t.payment_status = 'refunded' AND DATE(t.transaction_created_at) = '__DATE__'), 0)",
            'items_sold'          => 'SUM(b.total_svc_slots + b.total_ext_svc_slots)',
            'revenue_net_sales'   => "SUM(b.total_cost - COALESCE(b.disount_amount, 0)) - COALESCE((SELECT SUM(t.paid_amount) FROM $transactions_table t WHERE t.booking_id = b.id AND t.payment_status = 'refunded' AND DATE(t.transaction_created_at) = '__DATE__'), 0)",
            'products_net_sales'  => "SUM(b.total_cost - COALESCE(b.disount_amount, 0)) - COALESCE((SELECT SUM(t.paid_amount) FROM $transactions_table t WHERE t.booking_id = b.id AND t.payment_status = 'refunded' AND DATE(t.transaction_created_at) = '__DATE__'), 0)",
            'products_orders'     => 'COUNT(DISTINCT b.id)',
            'orders_revenue'      => "SUM(b.total_cost - COALESCE(b.disount_amount, 0)) - COALESCE((SELECT SUM(t.paid_amount) FROM $transactions_table t WHERE t.booking_id = b.id AND t.payment_status = 'refunded' AND DATE(t.transaction_created_at) = '__DATE__'), 0)",
            'avg_order_value'     => "COALESCE( ( SUM(b.total_cost - COALESCE(b.disount_amount, 0)) - COALESCE((SELECT SUM(t.paid_amount) FROM $transactions_table t WHERE t.booking_id = b.id AND t.payment_status = 'refunded' AND DATE(t.transaction_created_at) = '__DATE__'), 0) ) / NULLIF(COUNT(DISTINCT b.id), 0), 0)",
        );

        if ( ! isset( $metric_map[ $metric ] ) ) {
            return 0.0;
        }

        // Replace __DATE__ placeholder
        $sql_expression = str_replace( '__DATE__', $date, $metric_map[ $metric ] );

        $where = array(
            'b.booking_date' => array( '=' => $date ),
            'b.order_status' => array( '=' => 'succeeded' ),
        );

        $result = $dbhandler->get_results_with_join(
            array( 'BOOKING', 'b' ),
            $sql_expression . ' AS metric_value',
            array(),
            $where,
            'row'
        );

        if ( $result && isset( $result->metric_value ) ) {
            return floatval( $result->metric_value );
        }

        return 0.0;
    }

	/**
	 * Fetch all orders
	 *
	 * @author Darpan
	 */
	public function bm_fetch_all_orders() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler          = new BM_DBhandler();
		$bmrequests         = new BM_Request();
		$woocommerceservice = new WooCommerceService();
		$data               = array();
		$statuses           = array();

		/**if ( $woocommerceservice->is_enabled() ) {
			$order_statuses = wc_get_order_statuses();
		} else {
			$order_statuses = $bmrequests->bm_fetch_order_status_key_value();
		}
		foreach ( $order_statuses as $key => $status ) {
			$value              = $bmrequests->bm_fetch_order_status_string( $key );
			$text               = $bmrequests->bm_fetch_order_status_key_value( $value );
			$statuses[ $value ] = $text;
		}*/

		$statuses = $bmrequests->bm_fetch_order_status_key_value();

		$data['status']         = true;
		$data['bookings']       = $dbhandler->get_all_result( 'BOOKING', '*', array( 'is_active' => 1 ), 'results', 0, false, 'booking_date', 'DESC' );
		$data['active_columns'] = $bmrequests->bm_fetch_active_columns( 'orders' );
		$data['column_values']  = $bmrequests->bm_fetch_column_order_and_names( 'orders' );
		$data['order_statuses'] = $statuses;

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_all_orders()


	/**
	 * Fetch saved search for orders
	 *
	 * @author Darpan
	 */
	public function bm_fetch_saved_order_search() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$saved_search = array();
		$bmrequests   = new BM_Request();
		$module       = filter_input( INPUT_POST, 'module' );

		if ( $module != false && $module != null ) {
			$is_admin     = current_user_can( 'manage_options' ) ? 1 : 0;
			$saved_search = $bmrequests->bm_fetch_last_saved_search_data( $module, $is_admin );
		}

		echo wp_json_encode( $saved_search );
		die;
	}//end bm_fetch_saved_order_search()


	/**
	 * Fetch saved search for checkins
	 *
	 * @author Darpan
	 */
	public function bm_fetch_saved_checkin_search() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$saved_search = array();
		$bmrequests   = new BM_Request();
		$module       = filter_input( INPUT_POST, 'module' );

		if ( $module != false && $module != null ) {
			$is_admin     = current_user_can( 'manage_options' ) ? 1 : 0;
			$saved_search = $bmrequests->bm_fetch_last_saved_search_data( $module, $is_admin );
		}

		echo wp_json_encode( $saved_search );
		die;
	}//end bm_fetch_saved_checkin_search()


	/**
	 * Fetch primary email field key
	 *
	 * @author Darpan
	 */
	public function bm_get_primary_email_field_key() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$bmrequests = new BM_Request();
		$data       = array();

		$primary_email_key  = $bmrequests->bm_check_and_return_field_key_of_primary_email_in_field_data();
		$total_email_fields = $bmrequests->bm_fetch_total_number_of_email_fields_in_active_filelds();

		if ( $total_email_fields > 1 ) {
			$checkbox_html = $bmrequests->bm_fetch_available_email_fields_in_active_filelds_checkbox_html( $primary_email_key );
		}

		$data['primary_email_key']  = $primary_email_key;
		$data['total_email_fields'] = $total_email_fields;
		$data['checkbox_html']      = isset( $checkbox_html ) ? wp_kses( $checkbox_html, $bmrequests->bm_fetch_expanded_allowed_tags() ) : '';

		echo wp_json_encode( $data );
		die;
	}//end bm_get_primary_email_field_key()


	/**
	 * Save primary email field key
	 *
	 * @author Darpan
	 */
	public function bm_save_primary_email_field_key() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler = new BM_DBhandler();
		$post      = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data      = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$id           = isset( $post['id'] ) ? $post['id'] : 0;
			$email_fields = $dbhandler->get_all_result( 'FIELDS', '*', array( 'field_type' => 'email' ), 'results' );

			if ( ! empty( $id ) && ! empty( $email_fields ) && is_array( $email_fields ) ) {
				foreach ( $email_fields as $email ) {
					if ( ! empty( $email ) ) {
						$email_id      = isset( $email->id ) ? $email->id : 0;
						$email_options = isset( $email->field_options ) ? maybe_unserialize( $email->field_options ) : array();

						if ( ! empty( $email_options ) && ! empty( $email_id ) ) {
							if ( $email_id == $id ) {
								$email_options['is_main_email'] = 1;
							} elseif ( $email_id !== $id ) {
								$email_options['is_main_email'] = 0;
							}
						}

						$dbhandler->update_row( 'FIELDS', 'id', $email_id, array( 'field_options' => maybe_serialize( $email_options ) ), '', '%d' );
					}
				}

				$data['status'] = true;
			}
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_save_primary_email_field_key()


	/**
	 * Save non primary email as primary
	 *
	 * @author Darpan
	 */
	public function bm_save_non_primary_email_as_primary() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler = new BM_DBhandler();
		$field_key = filter_input( INPUT_POST, 'field_key' );
		$data      = array( 'status' => false );

		if ( $field_key != false && $field_key != null ) {
			$email_fields = $dbhandler->get_all_result( 'FIELDS', '*', array( 'field_type' => 'email' ), 'results' );

			if ( ! empty( $email_fields ) && is_array( $email_fields ) ) {
				foreach ( $email_fields as $email ) {
					if ( ! empty( $email ) ) {
						$email_field_key = isset( $email->field_key ) ? $email->field_key : '';
						$email_options   = isset( $email->field_options ) ? maybe_unserialize( $email->field_options ) : array();

						if ( ! empty( $email_options ) && ! empty( $email_field_key ) ) {
							if ( $email_field_key == $field_key ) {
								$email_options['is_main_email'] = 1;
							} elseif ( $email_field_key !== $field_key ) {
								$email_options['is_main_email'] = 0;
							}
						}

						$dbhandler->update_row( 'FIELDS', 'field_key', $email_field_key, array( 'field_options' => maybe_serialize( $email_options ) ), '', '%s' );
					}
				}

				$data['status'] = true;
			}
		} //end if

		echo wp_json_encode( $data );
		die;
	}//end bm_save_non_primary_email_as_primary()


	/**
	 * Custom cron schedule
	 *
	 * @author Darpan
	 */
	public function bm_custom_cron_schedule( $schedules ) {
		if ( ! isset( $schedules['per_minute'] ) ) {
			$schedules['per_minute'] = array(
				'interval' => 60,
				'display'  => 'Once every minute',
			);
		}

		if ( ! isset( $schedules['per_5_minute'] ) ) {
			$schedules['per_5_minute'] = array(
				'interval' => 300,
				'display'  => 'Every 5 Minutes',
			);
		}

		return $schedules;
	}//end bm_custom_cron_schedule()


	/**
	 * Check booking requests
	 *
	 * @author Darpan
	 */
	public function bm_check_booking_requests() {
		if ( ! wp_next_scheduled( 'flexibooking_check_expired_book_on_request_bookings' ) ) {
			wp_schedule_event( time(), 'per_minute', 'flexibooking_check_expired_book_on_request_bookings' );
		}
	}//end bm_check_booking_requests()


	/**
	 * Check booking requests
	 *
	 * @author Darpan
	 */
	public function bm_check_falied_emails_and_resend_pdfs() {
		if ( ! wp_next_scheduled( 'bm_resend_missing_emails_hook' ) ) {
        	wp_schedule_event( time(), 'per_5_minute', 'bm_resend_missing_emails_hook' );
    	}
	}//end bm_check_falied_emails_and_resend_pdfs()


	/**
	 * Cron job to resend missing emails for paid bookings with future service dates.
	 * Uses the custom get_results_with_join method to fetch eligible bookings.
	 */
	public function bm_resend_missing_emails_cron() {
		$dbhandler = new BM_DBhandler();

		// Get timezone from settings
		$timezone         = $dbhandler->get_global_option_value( 'bm_booking_time_zone', 'Asia/Kolkata' );
		$now              = new DateTime( 'now', new DateTimeZone( $timezone ) );
		$current_date     = $now->format( 'Y-m-d' );
		$current_time     = $now->format( 'H:i' );
		$current_datetime = $current_date . ' ' . $current_time;

		// Use get_results_with_join to fetch bookings with successful payment, mail_sent < 3, and future service date
		$results = $dbhandler->get_results_with_join(
			array( 'BOOKING', 'b' ),                                          // base table and alias
			'b.id, b.booking_type, b.mail_sent, b.booking_date, b.booking_slots, b.vouchers',
			array(                                                            // joins
				array(
					'table' => 'TRANSACTIONS',
					'alias' => 't',
					'on'    => 'b.id = t.booking_id',
					'type'  => 'INNER',
				),
			),
			array(                                                            // where conditions
				't.payment_status' => array( 'IN' => array( 'succeeded', 'free' ) ),
				'b.mail_sent'      => array( '<' => 3 ),
			),
			'results',                                                        // result type
			0,                                                                // offset
			false,                                                            // limit
			null,                                                             // sort by
			false,                                                            // descending
			'AND b.booking_date IS NOT NULL'                                  // additional raw condition
		);

		if ( empty( $results ) ) {
			return;
		}

		foreach ( $results as $booking ) {
			// Combine date and time from booking_slots
			$service_date = $booking->booking_date;
			$slots        = maybe_unserialize( $booking->booking_slots );
			$from_slot    = isset( $slots['from'] ) ? $slots['from'] : '';
			if ( ! empty( $from_slot ) ) {
				$service_datetime = $service_date . ' ' . $from_slot;
			} else {
				$service_datetime = $service_date . ' 00:00:00';
			}

			// Skip if service date/time has passed
			if ( strtotime( $service_datetime ) <= strtotime( $current_datetime ) ) {
				continue;
			}

			// 1. Trigger main email resend based on booking type (handled by pro plugin hooks).

			// 2. Handle gift/voucher emails if voucher exists and not yet sent
			if ( ! empty( $booking->vouchers ) ) {
				$voucher_emails = $dbhandler->get_all_result(
					'EMAILS',
					'*',
					array(
						'module_type' => 'BOOKING',
						'module_id'   => $booking->id,
						'mail_type'   => 'gift_voucher',
					),
					'results'
				);
			}
		}
	}


	/**
	 * Check expired book-on-request bookings and cancel them if the expiry window has passed.
	 *
	 * Cron callback for `flexibooking_check_expired_book_on_request_bookings`.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function flexibooking_check_expired_book_on_request_bookings_callback() {
		$bmrequests     = new BM_Request();
		$dbhandler      = new BM_DBhandler();
		$transactions   = $bmrequests->bm_fetch_book_on_request_transactions();
		$booking_expiry = (int) $dbhandler->get_global_option_value( 'bm_book_on_request_expiry' );

		if ( $booking_expiry <= 0 ) {
			$booking_expiry = 7;
		}

		if ( empty( $transactions ) || ! is_array( $transactions ) ) {
			return;
		}

		$timezone         = $dbhandler->get_global_option_value( 'bm_booking_time_zone', 'Asia/Kolkata' );
		$timezone_object  = new DateTimeZone( $timezone );
		$current_datetime = new DateTime( 'now', $timezone_object );

		foreach ( $transactions as $transaction ) {
			$booking_id        = isset( $transaction->booking_id ) ? $transaction->booking_id : 0;
			$creation_datetime = isset( $transaction->transaction_created_at ) ? $transaction->transaction_created_at : '';

			if ( empty( $booking_id ) || empty( $creation_datetime ) ) {
				continue;
			}

			$creation_datetime = new DateTime( $creation_datetime, $timezone_object );
			$time_to_compare   = clone $creation_datetime;
			$time_to_compare->modify( '+' . $booking_expiry . ' hours' );

			if ( $current_datetime <= $time_to_compare ) {
				continue;
			}

			$bmrequests->bm_cancel_and_refund_order( $booking_id );
			$is_cancelled = $dbhandler->get_global_option_value( 'bm_is_booking_cancelled-' . $booking_id, 0 );

			if ( absint( $is_cancelled ) === 1 ) {
				$this->bm_expire_woocommerce_order_for_booking( $dbhandler, $booking_id, 'cancelled' );
			}
		}
	}//end flexibooking_check_expired_book_on_request_bookings_callback()


	/**
	 * Mark processing bookings as completed
	 *
	 * @author Darpan
	 */
	public function bm_mark_flexi_paid_processing_bookings_as_completed() {
		if ( ! wp_next_scheduled( 'flexibooking_check_paid_expired_processing_bookings' ) ) {
			wp_schedule_event( time(), 'per_minute', 'flexibooking_check_paid_expired_processing_bookings' );
		}
	}//end bm_mark_flexi_paid_processing_bookings_as_completed()


	/**
	 * Mark backend pending bookings as cancelled
	 *
	 * @author Darpan
	 */
	public function bm_mark_pending_bookings_as_cancelled() {
		if ( ! wp_next_scheduled( 'flexibooking_check_expired_pending_bookings' ) ) {
			wp_schedule_event( time(), 'per_minute', 'flexibooking_check_expired_pending_bookings' );
		}
	}//end bm_mark_pending_bookings_as_cancelled()


	/**
	 * Mark expired free bookings as completed
	 *
	 * @author Darpan
	 */
	public function bm_mark_expired_free_bookings_as_completed() {
		if ( ! wp_next_scheduled( 'flexibooking_check_expired_free_bookings' ) ) {
			wp_schedule_event( time(), 'per_minute', 'flexibooking_check_expired_free_bookings' );
		}
	}//end bm_mark_expired_free_bookings_as_completed()


	/**
	 * Check expired vouchers
	 *
	 * @author Darpan
	 */
	public function bm_check_expired_vouchers() {
		if ( ! wp_next_scheduled( 'flexibooking_check_expired_vouchers' ) ) {
			wp_schedule_event( time(), 'per_minute', 'flexibooking_check_expired_vouchers' );
		}
	}//end bm_check_expired_vouchers()


	/**
	 * Callback of check expired vouchers
	 *
	 * @author Darpan
	 */
	public function flexibooking_check_expired_vouchers_callback() {
		$dbhandler = new BM_DBhandler();
		$vouchers  = $dbhandler->get_all_result( 'VOUCHERS', array( 'settings', 'booking_id', 'id', 'created_at' ), array( 'is_expired' => 0 ), 'results' );

		if ( empty( $vouchers ) || ! is_array( $vouchers ) ) {
			return;
		}

		$timezone            = new DateTimeZone( $dbhandler->get_global_option_value( 'bm_booking_time_zone', 'Asia/Kolkata' ) );
		$current_datetime    = new DateTime( 'now', $timezone );
		$default_expiry_days = (int) $dbhandler->get_global_option_value( 'bm_voucher_expiry', 30 );

		foreach ( $vouchers as $voucher ) {
			$expiry_date = null;

			if ( ! empty( $voucher->settings ) ) {
				$settings    = maybe_unserialize( $voucher->settings );
				$expiry_date = ! empty( $settings['expiry'] ) ? new DateTime( $settings['expiry'], $timezone ) : null;
			}

			if ( ! $expiry_date && ! empty( $voucher->created_at ) ) {
				$creation_date = new DateTime( $voucher->created_at, $timezone );
				$expiry_date   = $creation_date->add( new DateInterval( "P{$default_expiry_days}D" ) );
			}

			if ( $expiry_date && $current_datetime > $expiry_date ) {
				( new BM_Request() )->bm_mark_vouchers_expired( $voucher->id );
			}
		}
	}//end flexibooking_check_expired_vouchers_callback()



	/**
	 * Mark expired processing transactions as completed
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_mark_processing_orders_as_complete( $booking_id = 0 ) {
		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$status     = false;

		if ( ! empty( $booking_id ) ) {
			$booking_data = array(
				'order_status'       => 'succeeded',
				'booking_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
			);

			$booking_update = $dbhandler->update_row( 'BOOKING', 'id', $booking_id, $booking_data, '', '%d' );

			if ( $booking_update ) {
				$status = true;
			}
		}

		return $status;
	} // end bm_flexibooking_mark_processing_orders_as_complete()


	/**
	 * Mark expired free bookings as completed.
	 *
	 * Updates the booking order status to 'succeeded'. The WooCommerce
	 * order status is updated separately by the cron callback.
	 *
	 * @since 1.0.0
	 * @param int $booking_id Booking ID.
	 * @return bool True on successful update, false otherwise.
	 */
	public function bm_mark_free_orders_as_complete( $booking_id = 0 ) {
		if ( empty( $booking_id ) ) {
			return false;
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();

		$booking_data = array(
			'order_status'       => 'succeeded',
			'booking_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
		);

		$booking_update = $dbhandler->update_row( 'BOOKING', 'id', $booking_id, $booking_data, '', '%d' );

		return (bool) $booking_update;
	} // end bm_mark_free_orders_as_complete()


	/**
	 * Update all booking-related tables (transaction, booking, slotcount, extra-slotcount, customer)
	 * when a booking status changes.
	 *
	 * This private helper eliminates code duplication across the five public
	 * status-update filter callbacks.
	 *
	 * @since 1.3.0
	 * @param int    $booking_id       Booking ID.
	 * @param string $payment_status   Transaction payment status (e.g. 'cancelled', 'refunded', 'succeeded', 'pending').
	 * @param string $order_status     Booking order status (e.g. 'cancelled', 'refunded', 'succeeded', 'processing', 'on_hold').
	 * @param int    $is_active        Active flag (0 = inactive, 1 = active).
	 * @param array  $extra_txn_fields Additional fields merged into the transaction data array.
	 * @param bool   $deactivate_sole  Whether to deactivate the customer when they have only one transaction.
	 * @return bool True if the primary updates succeeded, false otherwise.
	 */
	private function bm_update_all_booking_tables( $booking_id, $payment_status, $order_status, $is_active, $extra_txn_fields = array(), $deactivate_sole = false ) {
		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$timestamp  = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();

		$transaction_data = array_merge(
			array(
				'payment_status'         => $payment_status,
				'is_active'              => $is_active,
				'transaction_updated_at' => $timestamp,
			),
			$extra_txn_fields
		);

		$booking_data = array(
			'order_status'       => $order_status,
			'is_active'          => $is_active,
			'booking_updated_at' => $timestamp,
		);

		$slotcount_data = array(
			'is_active'       => $is_active,
			'slot_updated_at' => $timestamp,
		);

		$extra_slotcount_data = array(
			'is_active'       => $is_active,
			'slot_updated_at' => $timestamp,
		);

		$txn_result       = $dbhandler->update_row( 'TRANSACTIONS', 'booking_id', $booking_id, $transaction_data, '', '%d' );
		$booking_result   = $dbhandler->update_row( 'BOOKING', 'id', $booking_id, $booking_data, '', '%d' );
		$slotcount_result = $dbhandler->update_row( 'SLOTCOUNT', 'booking_id', $booking_id, $slotcount_data, '', '%d' );
		$dbhandler->update_row( 'EXTRASLOTCOUNT', 'booking_id', $booking_id, $extra_slotcount_data, '', '%d' );

		$customer_id = $dbhandler->get_value( 'TRANSACTIONS', 'customer_id', $booking_id, 'booking_id' );

		if ( $deactivate_sole ) {
			$customer_count = $dbhandler->bm_count( 'TRANSACTIONS', array( 'customer_id' => $customer_id ) );

			if ( (int) $customer_count === 1 ) {
				$customer_data = array(
					'is_active'           => $is_active,
					'customer_updated_at' => $timestamp,
				);
				$dbhandler->update_row( 'CUSTOMERS', 'id', $customer_id, $customer_data, '', '%d' );
			}
		} else {
			$customer_data = array(
				'is_active'           => $is_active,
				'customer_updated_at' => $timestamp,
			);
			$dbhandler->update_row( 'CUSTOMERS', 'id', $customer_id, $customer_data, '', '%d' );
		}

		return ( false !== $txn_result && false !== $booking_result && false !== $slotcount_result );
	}//end bm_update_all_booking_tables()


	/**
	 * Cancel a booking and deactivate all related records.
	 *
	 * Filter callback for `flexibooking_cancel_booking`.
	 *
	 * @since 1.0.0
	 * @param int $booking_id Booking ID.
	 * @return bool True if cancellation succeeded, false otherwise.
	 */
	public function bm_flexibooking_cancel_booking( $booking_id = 0 ) {
		if ( $booking_id <= 0 ) {
			return false;
		}

		return $this->bm_update_all_booking_tables( $booking_id, 'cancelled', 'cancelled', 0, array(), true );
	} // end bm_flexibooking_cancel_booking()


	/**
	 * Mark a booking as refunded.
	 *
	 * Filter callback for `flexibooking_update_status_as_refunded`.
	 *
	 * @since 1.0.0
	 * @param int    $booking_id Booking ID.
	 * @param string $refund_id  Stripe refund ID.
	 * @return bool True on success, false otherwise.
	 */
	public function bm_flexibooking_update_status_as_refunded( $booking_id = 0, $refund_id = '' ) {
		if ( $booking_id <= 0 ) {
			return false;
		}

		return $this->bm_update_all_booking_tables(
			$booking_id,
			'refunded',
			'refunded',
			0,
			array( 'refund_id' => $refund_id ),
			true
		);
	} // end bm_flexibooking_update_status_as_refunded()


	/**
	 * Mark a booking as completed (succeeded).
	 *
	 * Filter callback for `flexibooking_update_status_as_completed`.
	 *
	 * @since 1.0.0
	 * @param int $booking_id Booking ID.
	 * @return bool True on success, false otherwise.
	 */
	public function bm_flexibooking_update_status_as_completed( $booking_id = 0 ) {
		if ( $booking_id <= 0 ) {
			return false;
		}

		return $this->bm_update_all_booking_tables( $booking_id, 'succeeded', 'succeeded', 1 );
	} // end bm_flexibooking_update_status_as_completed()


	/**
	 * Mark a booking as processing (pending payment).
	 *
	 * Filter callback for `flexibooking_update_status_as_processing`.
	 *
	 * @since 1.0.0
	 * @param int $booking_id Booking ID.
	 * @return bool True on success, false otherwise.
	 */
	public function bm_flexibooking_update_status_as_processing( $booking_id = 0 ) {
		if ( $booking_id <= 0 ) {
			return false;
		}

		return $this->bm_update_all_booking_tables( $booking_id, 'pending', 'processing', 1 );
	} // end bm_flexibooking_update_status_as_processing()


	/**
	 * Mark a booking as on-hold (pending payment).
	 *
	 * Filter callback for `flexibooking_update_status_as_on_hold`.
	 *
	 * @since 1.0.0
	 * @param int $booking_id Booking ID.
	 * @return bool True on success, false otherwise.
	 */
	public function bm_flexibooking_update_status_as_on_hold( $booking_id = 0 ) {
		if ( $booking_id <= 0 ) {
			return false;
		}

		return $this->bm_update_all_booking_tables( $booking_id, 'pending', 'on_hold', 1 );
	} // end bm_flexibooking_update_status_as_on_hold()


	/**
	 * Refund cancelled book on request transactions
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_refund_cancelled_order( $booking_id = 0 ) {
		$refund_id = '';

		if ( $booking_id > 0 ) {
			// Free version uses WooCommerce for payment handling; no Stripe refund. Returns empty refund_id.
		}

		return $refund_id;
	} // end bm_flexibooking_refund_cancelled_order()


	/**
	 * Expire the associated WooCommerce order for a FlexiBooking booking.
	 *
	 * Marks the WC order with the given status, flags it as expired, and
	 * redeems any attached voucher.
	 *
	 * @since 1.3.0
	 * @param BM_DBhandler $dbhandler   Database handler instance.
	 * @param int          $booking_id  FlexiBooking booking ID.
	 * @param string       $wc_status   WooCommerce order status to set (e.g., 'completed', 'cancelled').
	 * @return void
	 */
	private function bm_expire_woocommerce_order_for_booking( $dbhandler, $booking_id, $wc_status ) {
		$wc_order_id = $dbhandler->get_value( 'BOOKING', 'wc_order_id', $booking_id, 'id' );

		if ( $wc_order_id <= 0 || ! ( new WooCommerceService() )->is_enabled() ) {
			return;
		}

		$wc_order = wc_get_order( $wc_order_id );

		if ( ! $wc_order ) {
			return;
		}

		$wc_order->update_status( $wc_status, 'marked from flexi booking plugin' );
		update_post_meta( $wc_order_id, '_is_flexi_order_expired', true );

		$voucher_code = get_post_meta( $wc_order_id, '_flexi_voucher_id', true );

		if ( $voucher_code && class_exists( 'FlexiVoucherRedeem' ) ) {
			$redeemVoucher = new FlexiVoucherRedeem( $voucher_code );
			$redeemVoucher->markVoucherExpired();
		}
	}//end bm_expire_woocommerce_order_for_booking()


	/**
	 * Build the current date-time string in the plugin's configured timezone.
	 *
	 * @since 1.3.0
	 * @param BM_DBhandler $dbhandler Database handler instance.
	 * @return string Current date-time as 'Y-m-d H:i'.
	 */
	private function bm_get_current_plugin_datetime( $dbhandler ) {
		$timezone = $dbhandler->get_global_option_value( 'bm_booking_time_zone', 'Asia/Kolkata' );
		$now      = new DateTime( 'now', new DateTimeZone( $timezone ) );

		return $now->format( 'Y-m-d H:i' );
	}//end bm_get_current_plugin_datetime()


	/**
	 * Determine whether a booking's service end time has already passed.
	 *
	 * @since 1.3.0
	 * @param object $booking          Booking row object with booking_date and booking_slots.
	 * @param string $current_datetime Current date-time string for comparison.
	 * @return bool True if the booking is expired (service end time is in the past).
	 */
	private function bm_is_booking_service_expired( $booking, $current_datetime ) {
		$service_date  = isset( $booking->booking_date ) ? $booking->booking_date : '';
		$booking_slots = isset( $booking->booking_slots ) ? maybe_unserialize( $booking->booking_slots ) : array();

		if ( empty( $service_date ) || empty( $booking_slots ) || ! is_array( $booking_slots ) ) {
			return false;
		}

		$to_slot = isset( $booking_slots['to'] ) ? $booking_slots['to'] : '';

		if ( empty( $to_slot ) ) {
			return false;
		}

		$service_end_datetime = $service_date . ' ' . $to_slot;

		return strtotime( $service_end_datetime ) < strtotime( $current_datetime );
	}//end bm_is_booking_service_expired()


	/**
	 * Callback to check expired processing bookings and mark them as completed.
	 *
	 * Cron callback for `flexibooking_check_paid_expired_processing_bookings`.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function flexibooking_check_paid_expired_processing_bookings_callback() {
		$dbhandler        = new BM_DBhandler();
		$bmrequests       = new BM_Request();
		$bookings         = $bmrequests->bm_fetch_paid_bookings_with_processing_status();
		$current_datetime = $this->bm_get_current_plugin_datetime( $dbhandler );

		if ( empty( $bookings ) || ! is_array( $bookings ) ) {
			return;
		}

		foreach ( $bookings as $booking ) {
			$booking_id = isset( $booking->id ) ? $booking->id : 0;

			if ( empty( $booking_id ) || ! $this->bm_is_booking_service_expired( $booking, $current_datetime ) ) {
				continue;
			}

			$bmrequests->bm_mark_processing_orders_as_complete( $booking_id );
			$this->bm_expire_woocommerce_order_for_booking( $dbhandler, $booking_id, 'completed' );
		}
	}//end flexibooking_check_paid_expired_processing_bookings_callback()


	/**
	 * Callback to check expired pending bookings and cancel them.
	 *
	 * Cron callback for `flexibooking_check_expired_pending_bookings`.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function flexibooking_check_expired_pending_bookings_callback() {
		$dbhandler        = new BM_DBhandler();
		$bmrequests       = new BM_Request();
		$bookings         = $bmrequests->bm_fetch_unpaid_bookings_with_processing_status();
		$current_datetime = $this->bm_get_current_plugin_datetime( $dbhandler );

		if ( empty( $bookings ) || ! is_array( $bookings ) ) {
			return;
		}

		foreach ( $bookings as $booking ) {
			$booking_id = isset( $booking->id ) ? $booking->id : 0;

			if ( empty( $booking_id ) || ! $this->bm_is_booking_service_expired( $booking, $current_datetime ) ) {
				continue;
			}

			$bmrequests->bm_cancel_and_refund_order( $booking_id );
			$is_cancelled = $dbhandler->get_global_option_value( 'bm_is_booking_cancelled-' . $booking_id, 0 );

			if ( absint( $is_cancelled ) === 1 ) {
				$this->bm_expire_woocommerce_order_for_booking( $dbhandler, $booking_id, 'cancelled' );
			}
		}
	}//end flexibooking_check_expired_pending_bookings_callback()


	/**
	 * Callback to check expired free bookings and mark them as completed.
	 *
	 * Cron callback for `flexibooking_check_expired_free_bookings`.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function flexibooking_check_expired_free_bookings_callback() {
		$dbhandler        = new BM_DBhandler();
		$bmrequests       = new BM_Request();
		$bookings         = $bmrequests->bm_fetch_free_bookings();
		$current_datetime = $this->bm_get_current_plugin_datetime( $dbhandler );

		if ( empty( $bookings ) || ! is_array( $bookings ) ) {
			return;
		}

		foreach ( $bookings as $booking ) {
			$booking_id = isset( $booking->id ) ? $booking->id : 0;

			if ( empty( $booking_id ) || ! $this->bm_is_booking_service_expired( $booking, $current_datetime ) ) {
				continue;
			}

			$bmrequests->bm_mark_free_orders_as_complete( $booking_id );
			$this->bm_expire_woocommerce_order_for_booking( $dbhandler, $booking_id, 'completed' );
		}
	}//end flexibooking_check_expired_free_bookings_callback()


	/**
	 * Cancel book on request order
	 *
	 * @author Darpan
	 */
	public function bm_cancel_book_on_request_order() {
		if ( ! Booking_Management_Limits::is_pro_active() ) {
			wp_send_json_error( __( 'Book on Request is a Pro feature.', 'service-booking' ) );
			return;
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$booking_id    = isset( $post['id'] ) ? $post['id'] : 0;
			$paymentStatus = $dbhandler->get_value( 'TRANSACTIONS', 'payment_status', $booking_id, 'booking_id' );
			$is_active     = $dbhandler->get_value( 'TRANSACTIONS', 'is_active', $booking_id, 'booking_id' );

			if ( $paymentStatus == 'requires_capture' && $is_active == 1 ) {
				$bmrequests->bm_cancel_and_refund_order( $booking_id );
				$is_cancelled = $dbhandler->get_global_option_value( 'bm_is_booking_cancelled-' . $booking_id, 0 );

				if ( $is_cancelled == 1 ) {
					$data['status'] = true;
				}
			}
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_cancel_book_on_request_order()


	/**
	 * Approve book on request order
	 *
	 * @author Darpan
	 */
	public function bm_approve_book_on_request_order() {
		if ( ! Booking_Management_Limits::is_pro_active() ) {
			wp_send_json_error( __( 'Book on Request is a Pro feature.', 'service-booking' ) );
			return;
		}

		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$id = isset( $post['id'] ) ? $post['id'] : 0;
			$bmrequests->bm_approve_pending_book_on_request_order( $id );
			$is_approved = $dbhandler->get_global_option_value( 'bm_is_book_on_request_approved-' . $id, 0 );

			if ( $is_approved == 1 ) {
				$data['status'] = true;
			}
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_approve_book_on_request_order()


	/**
	 * Update order transaction data
	 *
	 * @author Darpan
	 */
	public function bm_update_order_transaction() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$booking_id        = isset( $post['id'] ) ? $post['id'] : 0;
			$is_active         = $dbhandler->get_value( 'TRANSACTIONS', 'is_active', $booking_id, 'booking_id' );
			$transaction_data  = apply_filters( 'flexibooking_fetch_order_transaction_data', $booking_id );
			$html              = apply_filters( 'flexibooking_fetch_html_with_transaction_data', $transaction_data );
			$data['html']      = wp_kses( $html, $bmrequests->bm_fetch_expanded_allowed_tags() );
			$data['is_active'] = $is_active;
			$data['status']    = true;
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_update_order_transaction()


	/**
	 * Save order transaction data
	 *
	 * @author Darpan
	 */
	public function bm_save_order_transaction() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( $post != false && $post != null ) {
			$booking_id     = isset( $post['id'] ) ? $post['id'] : 0;
			$transaction_id = isset( $post['transaction_id'] ) ? $post['transaction_id'] : '';
			$payment_status = isset( $post['payment_status'] ) ? $post['payment_status'] : '';
			$refund_id      = isset( $post['refund_id'] ) ? $post['refund_id'] : '';
			$is_active      = isset( $post['is_active'] ) ? $post['is_active'] : '';

			$status = apply_filters( 'flexibooking_save_order_transaction_data', $booking_id, $transaction_id, $refund_id, $payment_status, $is_active );
		}

		echo wp_kses_post( $status );
		die;
	}//end bm_save_order_transaction()


	/**
	 * Fetch the transaction data for a given booking.
	 *
	 * @since 1.0.0
	 * @param int $booking_id Booking ID.
	 * @return array Transaction data as an associative array, or empty array on failure.
	 */
	public function bm_flexibooking_fetch_order_transaction_data( $booking_id ) {
		$dbhandler        = new BM_DBhandler();
		$transaction_data = $dbhandler->get_all_result( 'TRANSACTIONS', '*', array( 'booking_id' => $booking_id ), 'results', 0, false, null, false, '', 'ARRAY_A' );

		if ( empty( $transaction_data ) || ! is_array( $transaction_data ) ) {
			return array();
		}

		return $transaction_data[0];
	}//end bm_flexibooking_fetch_order_transaction_data()


	/**
	 * Fetch order transaction data with html
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_fetch_html_with_transaction_data( $transaction_data ) {
		$bmrequests = new BM_Request();
		$html       = $bmrequests->bm_fetch_html_with_order_transaction( $transaction_data );
		return $html;
	}//end bm_flexibooking_fetch_html_with_transaction_data()


	/**
	 * Save order transaction data
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_save_order_transaction_data( $booking_id, $transaction_id, $refund_id, $payment_status, $is_active ) {
		$dbhandler     = new BM_DBhandler();
		$bmrequests    = new BM_Request();
		$status        = 0;
		$update_status = 1;

		if ( empty( $booking_id ) ) {
			return $status;
		}

		do_action( 'flexibooking_save_existing_transaction_data_before_update', $booking_id );

		$transaction_id_before_update = $dbhandler->bm_fetch_data_from_transient( 'transaction_id_before_update_' . $booking_id );

		if ( ! empty( $transaction_id_before_update ) && ( $transaction_id_before_update != $transaction_id ) ) {
			$status = apply_filters( 'flexibooking_verify_if_valid_transaction_id', $booking_id, $transaction_id, $payment_status );

			if ( $status != 1 ) {
				return $status;
			}
		}

		if ( $payment_status == 'succeeded' ) {
			$status = apply_filters( 'flexibooking_verify_if_paid_transaction_id', $transaction_id );

			if ( $status != 1 ) {
				return $status;
			}
		}

		if ( $payment_status == 'pending' ) {
			$status = apply_filters( 'flexibooking_verify_if_pending_transaction_id', $transaction_id );

			if ( $status != 1 ) {
				return $status;
			}
		}

		if ( $payment_status == 'cancelled' ) {
			$status = apply_filters( 'flexibooking_verify_if_cancelled_transaction_id', $transaction_id );

			if ( $status != 1 ) {
				return $status;
			}
		}

		if ( $payment_status == 'free' ) {
			$status = apply_filters( 'flexibooking_verify_transaction_for_free_payment_status', $transaction_id );

			if ( $status != 1 ) {
				return $status;
			}
		}

		if ( $payment_status == 'refunded' ) {
			$is_frontend_booking = $dbhandler->bm_fetch_data_from_transient( 'is_frontend_booking_' . $booking_id );

			if ( $is_frontend_booking == 0 && empty( $transaction_id ) ) {
				$status = 1;
			} else {
				$status = apply_filters( 'flexibooking_verify_if_refunded_transaction_id', $refund_id );
			}

			if ( $status != 1 ) {
				return $status;
			}
		}

		if ( $payment_status == 'failed' ) {
			$status = apply_filters( 'flexibooking_check_and_remove_duplicate_record_in_failed_transaction_table', $booking_id );

			if ( $status == 1 ) {
				$status = apply_filters( 'flexibooking_add_data_to_failed_transaction_table', $booking_id, $transaction_id );

				if ( $status == 1 ) {
					$status = apply_filters( 'flexibooking_update_booking_data_before_marking_transaction_failed', $booking_id );
				}
			}

			if ( $status != 1 ) {
				return $status;
			}
		}

		if ( $status == 1 ) {
			if ( $is_active == 0 || $payment_status == 'refunded' || $payment_status == 'succeeded' || $payment_status == 'free' || $payment_status == 'pending' ) {
				$status = apply_filters( 'flexibooking_update_booking_data_after_transaction_update', $booking_id, $payment_status );
			}

			if ( $payment_status != 'cancelled' ) {
				if ( $payment_status == 'refunded' ) {
					$is_active = 0;
				} elseif ( $payment_status == 'failed' ) {
					$is_active = 2;
				}

				$transaction_data = array(
					'transaction_id'         => $transaction_id,
					'payment_status'         => $payment_status,
					'refund_id'              => $payment_status == 'refunded' ? $refund_id : '',
					'is_active'              => $is_active,
					'transaction_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
				);

				$status = apply_filters( 'flexibooking_update_transaction_data', $booking_id, $transaction_data );
			}

			if ( $payment_status == 'cancelled' ) {
				$bmrequests->bm_cancel_and_refund_order( $booking_id );
			}
		}

		if ( $status == 0 || $status == 2 || $status == 3 || $status == 4 || $update_status == 0 ) {
			$status = apply_filters( 'flexibooking_revert_transaction_update', $booking_id );
		}

		return $status;
	}//end bm_flexibooking_save_order_transaction_data()


	/**
	 * Save old transaction data
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_save_existing_transaction_data_before_update( $booking_id ) {
		$dbhandler = new BM_DBhandler();

		$customer_id                             = $dbhandler->get_value( 'TRANSACTIONS', 'customer_id', $booking_id, 'booking_id' );
		$transaction_data_before_update          = $dbhandler->get_all_result( 'TRANSACTIONS', '*', array( 'booking_id' => $booking_id ), 'results', 0, false, null, false, '', 'ARRAY_A' );
		$transaction_data_before_update          = $transaction_data_before_update[0];
		$transaction_id_before_update            = isset( $transaction_data_before_update['transaction_id'] ) ? $transaction_data_before_update['transaction_id'] : '';
		$paid_amount_before_update               = isset( $transaction_data_before_update['paid_amount'] ) ? $transaction_data_before_update['paid_amount'] : '';
		$paid_currency_before_update             = isset( $transaction_data_before_update['paid_amount_currency'] ) ? $transaction_data_before_update['paid_amount_currency'] : '';
		$refund_id_before_update                 = isset( $transaction_data_before_update['refund_id'] ) ? $transaction_data_before_update['refund_id'] : '';
		$booking_order_status_before_update      = $dbhandler->get_value( 'BOOKING', 'order_status', $booking_id, 'id' );
		$is_frontend_booking                     = $dbhandler->get_value( 'BOOKING', 'is_frontend_booking', $booking_id, 'id' );
		$booking_is_active_before_update         = $dbhandler->get_value( 'BOOKING', 'is_active', $booking_id, 'id' );
		$slotcount_is_active_before_update       = $dbhandler->get_value( 'SLOTCOUNT', 'is_active', $booking_id, 'booking_id' );
		$extra_slotcount_is_active_before_update = $dbhandler->get_value( 'EXTRASLOTCOUNT', 'is_active', $booking_id, 'booking_id' );
		$customer_is_active_before_update        = $dbhandler->get_value( 'CUSTOMERS', 'is_active', $customer_id, 'id' );

		$dbhandler->bm_save_data_to_transient( 'transaction_data_before_update_' . $booking_id, $transaction_data_before_update, 1 );
		$dbhandler->bm_save_data_to_transient( 'transaction_id_before_update_' . $booking_id, $transaction_id_before_update, 1 );
		$dbhandler->bm_save_data_to_transient( 'paid_amount_before_update_' . $booking_id, $paid_amount_before_update, 1 );
		$dbhandler->bm_save_data_to_transient( 'refund_id_before_update_' . $booking_id, $refund_id_before_update, 1 );
		$dbhandler->bm_save_data_to_transient( 'booking_order_status_before_update_' . $booking_id, $booking_order_status_before_update, 1 );
		$dbhandler->bm_save_data_to_transient( 'paid_currency_before_update_' . $booking_id, $paid_currency_before_update, 1 );
		$dbhandler->bm_save_data_to_transient( 'booking_is_active_before_update_' . $booking_id, $booking_is_active_before_update, 1 );
		$dbhandler->bm_save_data_to_transient( 'slotcount_is_active_before_update_' . $booking_id, $slotcount_is_active_before_update, 1 );
		$dbhandler->bm_save_data_to_transient( 'extra_slotcount_is_active_before_update_' . $booking_id, $extra_slotcount_is_active_before_update, 1 );
		$dbhandler->bm_save_data_to_transient( 'customer_is_active_before_update_' . $booking_id, $customer_is_active_before_update, 1 );
		$dbhandler->bm_save_data_to_transient( 'is_frontend_booking_' . $booking_id, $is_frontend_booking, 1 );
	}//end bm_flexibooking_save_existing_transaction_data_before_update()


	/**
	 * Verify new transaction id
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_verify_if_valid_transaction_id( $booking_id, $transaction_id, $payment_status ) {
		$dbhandler                 = new BM_DBhandler();
		$existing_transaction      = $dbhandler->get_row( 'TRANSACTIONS', $transaction_id, 'id' );
		$existing_booking_id       = isset( $existing_transaction->booking_id ) ? $existing_transaction->booking_id : 0;
		$paid_amount_before_update = $dbhandler->bm_fetch_data_from_transient( 'paid_amount_before_update_' . $booking_id );
		$is_frontend_booking       = $dbhandler->bm_fetch_data_from_transient( 'is_frontend_booking_' . $booking_id );
		$status                    = 1;

		if ( $is_frontend_booking == 0 && empty( $transaction_id ) ) {
			return $status;
		}

		if ( ! empty( $existing_transaction ) && ( $booking_id != $existing_booking_id ) ) {
			return 6;
		}

		if ( empty( $paid_amount_before_update ) && empty( $transaction_id ) && $payment_status == 'free' ) {
			return $status;
		}

		return $status;
	}//end bm_flexibooking_verify_if_valid_transaction_id()


	/**
	 * Verify if paid transaction
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_verify_if_paid_transaction_id( $transaction_id ) {
		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$status     = 1;

		$booking_id          = $dbhandler->get_value( 'TRANSACTIONS', 'booking_id', $transaction_id, 'id' );
		$is_frontend_booking = $dbhandler->bm_fetch_data_from_transient( 'is_frontend_booking_' . $booking_id );

		if ( $is_frontend_booking == 0 && empty( $transaction_id ) ) {
			return $status;
		}

		return $status;
	}//end bm_flexibooking_verify_if_paid_transaction_id()


	/**
	 * Paid transaction statuses
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_paid_transaction_statuses( $statuses ) {
		return $statuses;
	} //end bm_bm_flexibooking_paid_transaction_statuses()


	/**
	 * Pending transaction statuses
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_pending_transaction_statuses( $statuses ) {
		return $statuses;
	}//end bm_flexibooking_pending_transaction_statuses()


	/**
	 * Verify if pending transaction
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_verify_if_pending_transaction_id( $transaction_id ) {
		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$status     = 1;

		$booking_id          = $dbhandler->get_value( 'TRANSACTIONS', 'booking_id', $transaction_id, 'id' );
		$is_frontend_booking = $dbhandler->bm_fetch_data_from_transient( 'is_frontend_booking_' . $booking_id );

		if ( $is_frontend_booking == 0 && empty( $transaction_id ) ) {
			return $status;
		}

		return $status;
	}//end bm_flexibooking_verify_if_pending_transaction_id()


	/**
	 * Verify if cancelled transaction
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_verify_if_cancelled_transaction_id( $transaction_id ) {
		$dbhandler = new BM_DBhandler();
		$status    = 1;

		$booking_id          = $dbhandler->get_value( 'TRANSACTIONS', 'booking_id', $transaction_id, 'id' );
		$is_frontend_booking = $dbhandler->bm_fetch_data_from_transient( 'is_frontend_booking_' . $booking_id );

		if ( $is_frontend_booking == 0 && empty( $transaction_id ) ) {
			return $status;
		}

		return $status;
	}//end bm_flexibooking_verify_if_cancelled_transaction_id()


	/**
	 * Verify if free transaction
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_verify_transaction_for_free_payment_status( $transaction_id ) {
		$dbhandler = new BM_DBhandler();
		$status    = 1;

		$booking_id          = $dbhandler->get_value( 'TRANSACTIONS', 'booking_id', $transaction_id, 'id' );
		$is_frontend_booking = $dbhandler->bm_fetch_data_from_transient( 'is_frontend_booking_' . $booking_id );

		if ( $is_frontend_booking == 0 && empty( $transaction_id ) ) {
			return $status;
		}

		if ( ! empty( $transaction_id ) ) {
			$status = 3;
		}

		return $status;
	}//end bm_flexibooking_verify_transaction_for_free_payment_status()


	/**
	 * Verify if refunded transaction
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_verify_if_refunded_transaction_id( $refund_id ) {
		$status = 1;

		return $status;
	}//end bm_flexibooking_verify_if_refunded_transaction_id()


	/**
	 * Update transaction data
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_update_transaction_data( $booking_id, $transaction_data ) {
		$dbhandler = new BM_DBhandler();
		$status    = 0;

		if ( empty( $booking_id ) ) {
			return $status;
		}

		$transaction_update = $dbhandler->update_row( 'TRANSACTIONS', 'booking_id', $booking_id, $transaction_data, '', '%d' );

		if ( ! is_wp_error( $transaction_update ) ) {
			$status = 1;
		}

		return $status;
	}//end bm_flexibooking_update_transaction_data()


	/**
	 * Update booking related data before updating transaction as failed
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_update_booking_data_before_marking_transaction_failed( $booking_id ) {
		$dbhandler     = new BM_DBhandler();
		$bmrequests    = new BM_Request();
		$status        = 1;
		$customer_data = array();

		if ( empty( $booking_id ) ) {
			return 0;
		}

		$customer_id    = $dbhandler->get_value( 'TRANSACTIONS', 'customer_id', $booking_id, 'booking_id' );
		$customer_count = $dbhandler->bm_count( 'TRANSACTIONS', array( 'customer_id' => $customer_id ) );

		$booking_data = array(
			'is_active'          => 2,
			'order_status'       => 'failed',
			'booking_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
		);

		$slotcount_data = array(
			'is_active'       => 2,
			'slot_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
		);

		$extra_slotcount_data = array(
			'is_active'       => 2,
			'slot_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
		);

		if ( ( $customer_count == 1 ) ) {
			$customer_data = array(
				'is_active'           => 2,
				'customer_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
			);
		}

		$booking_update = $dbhandler->update_row( 'BOOKING', 'id', $booking_id, $booking_data, '', '%d' );

		if ( is_wp_error( $booking_update ) ) {
			$status = 0;
		}

		$slotcount_update = $dbhandler->update_row( 'SLOTCOUNT', 'booking_id', $booking_id, $slotcount_data, '', '%d' );

		if ( is_wp_error( $slotcount_update ) ) {
			$status = 0;
		}

		$extra_slotcount_update = $dbhandler->update_row( 'EXTRASLOTCOUNT', 'booking_id', $booking_id, $extra_slotcount_data, '', '%d' );

		if ( is_wp_error( $extra_slotcount_update ) ) {
			$status = 0;
		}

		if ( ! empty( $customer_data ) ) {
			$customer_update = $dbhandler->update_row( 'CUSTOMERS', 'id', $customer_id, $customer_data, '', '%d' );

			if ( is_wp_error( $customer_update ) ) {
				$status = 0;
			}
		}

		return $status;
	}//end bm_flexibooking_update_booking_data_before_marking_transaction_failed()


	/**
	 * Update transaction as failed
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_add_data_to_failed_transaction_table( $booking_id, $transaction_id ) {
		// FAILED_TRANSACTIONS table removed in free version.
		return 0;
	}//end bm_flexibooking_add_data_to_failed_transaction_table()


	/**
	 * Update booking data after transaction update
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_update_booking_data_after_transaction_update( $booking_id, $payment_status ) {
		$dbhandler     = new BM_DBhandler();
		$bmrequests    = new BM_Request();
		$status        = 1;
		$customer_data = array();

		if ( empty( $booking_id ) ) {
			return 0;
		}

		$customer_id    = $dbhandler->get_value( 'TRANSACTIONS', 'customer_id', $booking_id, 'booking_id' );
		$customer_count = $dbhandler->bm_count( 'TRANSACTIONS', array( 'customer_id' => $customer_id ) );

		$booking_data = array(
			'is_active'          => $payment_status == 'refunded' ? 0 : 1,
			'order_status'       => $payment_status,
			'booking_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
		);

		$slotcount_data = array(
			'is_active'       => $payment_status == 'refunded' ? 0 : 1,
			'slot_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
		);

		$extra_slotcount_data = array(
			'is_active'       => $payment_status == 'refunded' ? 0 : 1,
			'slot_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
		);

		if ( ( $customer_count == 1 ) ) {
			$customer_data = array(
				'is_active'           => $payment_status == 'refunded' ? 0 : 1,
				'customer_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
			);
		}

		$booking_update = $dbhandler->update_row( 'BOOKING', 'id', $booking_id, $booking_data, '', '%d' );

		if ( is_wp_error( $booking_update ) ) {
			$status = 0;
		}

		$slotcount_update = $dbhandler->update_row( 'SLOTCOUNT', 'booking_id', $booking_id, $slotcount_data, '', '%d' );

		if ( is_wp_error( $slotcount_update ) ) {
			$status = 0;
		}

		$extra_slotcount_update = $dbhandler->update_row( 'EXTRASLOTCOUNT', 'booking_id', $booking_id, $extra_slotcount_data, '', '%d' );

		if ( is_wp_error( $extra_slotcount_update ) ) {
			$status = 0;
		}

		if ( ! empty( $customer_data ) ) {
			$customer_update = $dbhandler->update_row( 'CUSTOMERS', 'id', $customer_id, $customer_data, '', '%d' );

			if ( is_wp_error( $customer_update ) ) {
				$status = 0;
			}
		}

		return $status;
	}//end bm_flexibooking_update_booking_data_after_transaction_update()


	/**
	 * Remove duplicate record in failed transaction table
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_check_and_remove_duplicate_record_in_failed_transaction_table( $booking_id ) {
		// FAILED_TRANSACTIONS table removed in free version.
		return 1;
	}//end bm_flexibooking_check_and_remove_duplicate_record_in_failed_transaction_table()


	/**
	 * Revert transaction update
	 *
	 * @author Darpan
	 */
	public function bm_flexibooking_revert_transaction_update( $booking_id ) {
		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$status     = 5;

		if ( empty( $booking_id ) ) {
			return false;
		}

		$customer_id                             = $dbhandler->get_value( 'TRANSACTIONS', 'customer_id', $booking_id, 'booking_id' );
		$transaction_data_before_update          = $dbhandler->bm_fetch_data_from_transient( 'transaction_data_before_update_' . $booking_id );
		$booking_order_status_before_update      = $dbhandler->bm_fetch_data_from_transient( 'booking_order_status_before_update_' . $booking_id );
		$booking_is_active_before_update         = $dbhandler->bm_fetch_data_from_transient( 'booking_is_active_before_update_' . $booking_id );
		$slotcount_is_active_before_update       = $dbhandler->bm_fetch_data_from_transient( 'slotcount_is_active_before_update_' . $booking_id );
		$extra_slotcount_is_active_before_update = $dbhandler->bm_fetch_data_from_transient( 'extra_slotcount_is_active_before_update_' . $booking_id );
		$customer_is_active_before_update        = $dbhandler->bm_fetch_data_from_transient( 'customer_is_active_before_update_' . $booking_id );

		$transaction_update = $dbhandler->update_row( 'TRANSACTIONS', 'booking_id', $booking_id, $transaction_data_before_update, '', '%d' );

		if ( is_wp_error( $transaction_update ) ) {
			$status = 0;
		}

		$booking_data = array(
			'order_status'       => $booking_order_status_before_update,
			'is_active'          => $booking_is_active_before_update,
			'booking_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
		);

		$slotcount_data = array(
			'is_active'       => $slotcount_is_active_before_update,
			'slot_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
		);

		$extra_slotcount_data = array(
			'is_active'       => $extra_slotcount_is_active_before_update,
			'slot_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
		);

		$customer_data = array(
			'is_active'           => $customer_is_active_before_update,
			'customer_updated_at' => $bmrequests->bm_fetch_current_wordpress_datetime_stamp(),
		);

		$booking_update = $dbhandler->update_row( 'BOOKING', 'id', $booking_id, $booking_data, '', '%d' );

		if ( is_wp_error( $booking_update ) ) {
			$status = 0;
		}

		$slotcount_update = $dbhandler->update_row( 'SLOTCOUNT', 'booking_id', $booking_id, $slotcount_data, '', '%d' );

		if ( is_wp_error( $slotcount_update ) ) {
			$status = 0;
		}

		$extra_slotcount_update = $dbhandler->update_row( 'EXTRASLOTCOUNT', 'booking_id', $booking_id, $extra_slotcount_data, '', '%d' );

		if ( is_wp_error( $extra_slotcount_update ) ) {
			$status = 0;
		}

		$customer_update = $dbhandler->update_row( 'CUSTOMERS', 'id', $customer_id, $customer_data, '', '%d' );

		if ( is_wp_error( $customer_update ) ) {
			$status = 0;
		}

		return $status;
	}//end bm_flexibooking_revert_transaction_update()


	/**
	 * Get states by country
	 *
	 * @author Darpan
	 */
	public function bm_fetch_states_by_country() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
			return;
		}

		$country = trim( wp_unslash( sanitize_text_field( filter_input( INPUT_POST, 'country' ) ) ) );
		if ( empty( $country ) ) {
			wp_send_json_error( __( 'Invalid request data', 'service-booking' ) );
			return;
		}

		$states = ( new BM_Request() )->bm_get_states( $country );
		wp_send_json_success( $states );
	}//end bm_fetch_states_by_country()


	/**
	 * Fetch voucher booking info for voucher lisitng page
	 *
	 * @author Darpan
	 */
	public function bm_fetch_vocuher_booking_info() {
		$nonce = filter_input( INPUT_POST, 'nonce' );

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
			return;
		}

		$order_id = filter_input( INPUT_POST, 'order_id', FILTER_VALIDATE_INT );
		if ( empty( $order_id ) ) {
			wp_send_json_error( __( 'Invalid request data', 'service-booking' ) );
			return;
		}

		try {
			$booking = ( new BM_Request() )->bm_fetch_product_info_order_details_page( $order_id, true );
		} catch ( Exception $e ) {
			wp_send_json_error( __( 'Something went wrong.', 'service-booking' ) );
			return;
		}

		if ( empty( $booking ) ) {
			wp_send_json_error( __( 'No booking found.', 'service-booking' ) );
			return;
		}

		wp_send_json_success( $booking );
	} ///end bm_fetch_vocuher_booking_info()


	/**
	 * Fetch voucher gifter info for voucher lisitng page
	 *
	 * @author Darpan
	 */
	public function bm_fetch_vocuher_gifter_info() {
		$nonce = filter_input( INPUT_POST, 'nonce' );

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
			return;
		}

		$order_id = filter_input( INPUT_POST, 'order_id', FILTER_VALIDATE_INT );
		if ( empty( $order_id ) ) {
			wp_send_json_error( __( 'Invalid request data', 'service-booking' ) );
			return;
		}

		try {
			$customer_data = ( new BM_Request() )->get_customer_info_for_order( $order_id );
		} catch ( Exception $e ) {
			wp_send_json_error( __( 'Something went wrong.', 'service-booking' ) );
			return;
		}

		if ( empty( $customer_data ) ) {
			wp_send_json_error( __( 'No customer found.', 'service-booking' ) );
			return;
		}

		wp_send_json_success( $customer_data );
	} ///end bm_fetch_vocuher_gifter_info()


	/**
	 * Fetch voucher recipient info for voucher lisitng page
	 *
	 * @author Darpan
	 */
	public function bm_fetch_vocuher_recipient_info() {
		$nonce = filter_input( INPUT_POST, 'nonce' );

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
			return;
		}

		$code = trim( wp_unslash( sanitize_text_field( filter_input( INPUT_POST, 'code' ) ) ) );
		if ( empty( $code ) ) {
			wp_send_json_error( __( 'Invalid request data', 'service-booking' ) );
			return;
		}

		if ( ! class_exists( 'FlexiVoucherRedeem' ) ) {
			wp_send_json_error( __( 'Pro feature', 'service-booking' ) );
			return;
		}

		$redeemVoucher = new FlexiVoucherRedeem( $code );

		try {
			$voucher = $redeemVoucher->getVoucherInfo();
		} catch ( Exception $e ) {
			wp_send_json_error( __( 'Something went wrong.', 'service-booking' ) );
			return;
		}

		if ( isset( $voucher['error'] ) ) {
			wp_send_json_error( $voucher['error'] );
			return;
		}

		$voucher        = $voucher[0];
		$recipinet_data = isset( $voucher['recipient_data'] ) && ! empty( $voucher['recipient_data'] ) ? maybe_unserialize( $voucher['recipient_data'] ) : array();

		$recipinet_data['recipient_country'] = isset( $recipinet_data['recipient_country'] ) && ! empty( $recipinet_data['recipient_country'] ) ? ( new BM_Request() )->bm_get_countries( $recipinet_data['recipient_country'] ) : '';

		wp_send_json_success( $recipinet_data );
	} ///end bm_fetch_vocuher_recipient_info()


	/**
	 * Fetch voucher booking info for voucher lisitng page
	 *
	 * @author Darpan
	 */
	public function bm_change_voucher_status() {
		$nonce = filter_input( INPUT_POST, 'nonce' );

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
			return;
		}

		$post = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( empty( $post ) || empty( $post['code'] ) || ! isset( $post['status'] ) ) {
			wp_send_json_error( __( 'Invalid request data', 'service-booking' ) );
			return;
		}

		$code   = trim( wp_unslash( sanitize_text_field( $post['code'] ) ) );
		$status = trim( wp_unslash( sanitize_text_field( $post['status'] ) ) );

		if ( empty( $code ) ) {
			wp_send_json_error( __( 'Invalid request data', 'service-booking' ) );
			return;
		}

		if ( ! class_exists( 'FlexiVoucherRedeem' ) ) {
			wp_send_json_error( __( 'Pro feature', 'service-booking' ) );
			return;
		}

		$redeemVoucher = new FlexiVoucherRedeem( $code );

		try {
			$validate = $redeemVoucher->validateVoucherForStatusChange();
		} catch ( Exception $e ) {
			wp_send_json_error( __( 'Something went wrong.', 'service-booking' ) );
			return;
		}

		if ( isset( $validate['error'] ) ) {
			wp_send_json_error( $validate['error'] );
			return;
		}

		try {
			$redeemVoucher->updateVoucherInfo(
				array(
					'status'     => $status,
					'updated_at' => ( new BM_Request() )->bm_fetch_current_wordpress_datetime_stamp(),
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error( __( 'Status could not be updated.', 'service-booking' ) );
			return;
		}

		wp_send_json_success();
	}//end bm_change_voucher_status()


	/**
	 *  Handle QR code verification
	 *
	 * @author Darpan
	 */
	public function bm_handle_qr_verification() {
		$nonce = filter_input( INPUT_POST, 'nonce' );

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
			return;
		}

		$qr_data = isset( $_POST['qr_data'] ) ? filter_input( INPUT_POST, 'qr_data' ) : '';
		if ( ! $qr_data ) {
			wp_send_json_error( __( 'Invalid QR code', 'service-booking' ) );
			return;
		}

		$dbhandler = new BM_DBhandler();
		$booking   = $dbhandler->get_row( 'BOOKING', $qr_data, 'booking_key' );

		if ( ! $booking ) {
			wp_send_json_error( __( 'Booking not found', 'service-booking' ) );
			return;
		}

		$is_active = $db->get_value( 'BOOKING', 'is_active', $booking->id, 'id' );

		if ( $is_active != 1 ) {
			wp_send_json_error( __( 'Can not check in cancelled or refunded orders', 'service-booking' ) );
			return;
		}

		$checkin = $dbhandler->get_row( 'CHECKIN', $booking->id, 'booking_id' );

		if ( ! $checkin ) {
			$checkin_data = array(
				'booking_id' => $booking->id,
				'qr_token'   => $booking->booking_key,
				'status'     => 'pending',
			);
			$checkin_id   = $dbhandler->insert_row( 'CHECKIN', $checkin_data );
			$checkin      = $dbhandler->get_row( 'CHECKIN', $checkin_id );
		}

		// if ( strtotime( $booking->booking_date ) < time() ) {
		// $dbhandler->update_row(
		// 'CHECKIN',
		// 'id',
		// $checkin->id,
		// array(
		// 'status'          => 'expired',
		// 'service_expired' => 1,
		// )
		// );
		// wp_send_json_error( __( 'Service date has expired', 'service-booking' ) );
		// return;
		// }

		// if ( $checkin->status === 'checked_in' ) {
		// wp_send_json_error( __( 'Ticket already used', 'service-booking' ) );
		// return;
		// }

		$updated = $dbhandler->update_row(
			'CHECKIN',
			'id',
			$checkin->id,
			array(
				'booking_id'   => $booking->id ?? 0,
				'status'       => 'checked_in',
				'qr_scanned'   => 1,
				'qr_token'     => $qr_data,
				'checkin_time' => ( new BM_Request() )->bm_fetch_current_wordpress_datetime_stamp(),
				'updated_at'   => ( new BM_Request() )->bm_fetch_current_wordpress_datetime_stamp(),
			)
		);

		wp_send_json_success();
	} ///end bm_handle_qr_verification()


	public function bm_qr_checkin_process() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
			return;
		}

		$reference = isset( $_POST['booking_reference'] ) ? sanitize_text_field( $_POST['booking_reference'] ) : '';
		if ( ! $reference ) {
			wp_send_json_error( __( 'Invalid QR code.', 'service-booking' ) );
			return;
		}

		$db         = new BM_DBhandler();
		$booking_id = $db->get_value( 'BOOKING', 'id', $reference, 'booking_key' );
		$is_active  = $db->get_value( 'BOOKING', 'is_active', $search_value, 'booking_key' );

		if ( $is_active != 1 ) {
			wp_send_json_error( __( 'Can not check in cancelled or refunded orders', 'service-booking' ) );
			return;
		}

		if ( ! $booking_id ) {
			wp_send_json_error( __( 'Booking not found.', 'service-booking' ) );
			return;
		}

		$success = $this->bm_mark_booking_checked_in( (int) $booking_id, $db );

		if ( $success ) {
			wp_send_json_success( array( 'message' => __( 'Booking checked in successfully.', 'service-booking' ) ) );
		} else {
			wp_send_json_error( __( 'Unable to check in booking. It may already be checked in or inactive.', 'service-booking' ) );
		}
	}


	/**
	 *  Handle manual checkin
	 *
	 * @author Darpan
	 */
	public function bm_manual_checkin_process() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
		}

		$search_type = sanitize_text_field( filter_input( INPUT_POST, 'search_type' ) ?? '' );
		$raw_value   = $_POST['search_value'] ?? '';

		if ( is_array( $raw_value ) ) {
			$search_value = array_map( 'sanitize_text_field', $raw_value );
		} else {
			$search_value = sanitize_text_field( $raw_value );
		}

		$booking_ids = filter_input( INPUT_POST, 'booking_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		$db = new BM_DBhandler();

		if ( $search_type === 'reference' ) {
			$booking_id = $db->get_value( 'BOOKING', 'id', $search_value, 'booking_key' );
			$is_active  = $db->get_value( 'BOOKING', 'is_active', $search_value, 'booking_key' );

			if ( $is_active != 1 ) {
				wp_send_json_error( __( 'Can not check in cancelled or refunded orders', 'service-booking' ) );
				return;
			}

			if ( ! $booking_id ) {
				wp_send_json_error( __( 'Booking not found', 'service-booking' ) );
				return;
			}

			$success = $this->bm_mark_booking_checked_in( (int) $booking_id, $db );
			if ( ! $success ) {
				wp_send_json_error( __( 'Already checked in or expired.', 'service-booking' ) );
			}

			wp_send_json_success( array( 'message' => __( 'Booking successfully checked in.', 'service-booking' ) ) );
		}

		if ( empty( $booking_ids ) ) {
			wp_send_json_error( __( 'No bookings selected.', 'service-booking' ) );
		}

		$count = 0;
		foreach ( $booking_ids as $id ) {
			$is_active = $db->get_value( 'BOOKING', 'is_active', $id, 'id' );

			if ( $is_active != 1 ) {
				continue;
			}

			if ( $this->bm_mark_booking_checked_in( (int) $id, $db ) ) {
				++$count;
			}
		}

		if ( $count === 0 ) {
			wp_send_json_error( __( 'No valid bookings were checked in.', 'service-booking' ) );
		}

		wp_send_json_success( array( 'message' => sprintf( __( '%d bookings successfully checked in.', 'service-booking' ), $count ) ) );
	}//end bm_manual_checkin_process()


	private function bm_mark_booking_checked_in( int $booking_id, BM_DBhandler $db ): bool {
		$now = ( new BM_Request() )->bm_fetch_current_wordpress_datetime_stamp();

		$data = array(
			'qr_scanned'   => 1,
			'status'       => 'checked_in',
			'qr_token'     => $db->get_value( 'BOOKING', 'booking_key', $booking_id, 'id' ),
			'booking_id'   => $booking_id,
			'checkin_time' => $now,
			'updated_at'   => $now,
		);

		$existing = $db->get_value( 'CHECKIN', 'id', $booking_id, 'booking_id' );

		if ( $existing ) {
			return $db->update_row( 'CHECKIN', 'booking_id', $booking_id, $data );
		} else {
			return $db->insert_row( 'CHECKIN', $data );
		}
	}


	/**
	 *  Prepare google analytics data
	 *
	 * @author Darpan
	 */
	public function bm_prepare_ga_purchase_data( $booking_key ) {
		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();

		$discounted_key = $dbhandler->get_global_option_value( 'discount_' . $booking_key ) == 1 ? 'discounted_' : '';
		$order_data     = $dbhandler->bm_fetch_data_from_transient( $discounted_key . $booking_key );

		if ( empty( $order_data ) ) {
			return false;
		}

		$booking_id    = $dbhandler->get_value( 'BOOKING', 'id', $booking_key, 'booking_key' );
		$customer_data = $bmrequests->get_customer_info_for_order( $booking_id );

		$customer = array(
			'email'     => $customer_data['billing_email'] ?? '',
			'firstName' => $customer_data['billing_first_name'] ?? '',
			'lastName'  => $customer_data['billing_last_name'] ?? '',
		);

		$currency       = $dbhandler->get_global_option_value( 'bm_booking_currency', 'EUR' );
		$main_product   = $bmrequests->bm_prepare_service_data( $booking_key, $currency );
		$extra_products = $bmrequests->bm_prepare_extra_services_data( $booking_key, $currency );

		$items = array();
		$total = 0;

		if ( ! empty( $main_product ) ) {
			$price = floatval( $main_product['amount'] );
			$qty   = intval( $main_product['quantity'] ?? 1 );

			$items[] = array(
				'itemId'   => $main_product['id'],
				'itemName' => $main_product['name'],
				'price'    => $price,
				'quantity' => $qty,
			);

			$total += $price * $qty;
		}

		if ( ! empty( $extra_products ) ) {
			foreach ( $extra_products as $p ) {
				$price = floatval( $p['amount'] );
				$qty   = intval( $p['quantity'] ?? 1 );

				$items[] = array(
					'itemId'   => $p['id'],
					'itemName' => $p['name'],
					'price'    => $price,
					'quantity' => $qty,
				);

				$total += $price * $qty;
			}
		}

		return array(
			'transactionId'    => $booking_key,
			'transactionTotal' => $total,
			'tax'              => 0,
			'shipping'         => 0,
			'currency'         => $currency,
			'orderDate'        => $dbhandler->get_value( 'BOOKING', 'booking_date', $booking_key, 'booking_key' ),
			'items'            => $items,
			'customerData'     => $customer,
		);
	}


	/**
	 * Fetch booking info for check in
	 *
	 * @author Darpan
	 */
	public function bm_get_order_detail_for_check_in() {
		$nonce = filter_input( INPUT_POST, 'nonce' );

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
			return;
		}

		$booking_id = sanitize_text_field( filter_input( INPUT_POST, 'booking_id', FILTER_VALIDATE_INT ) );
		$dbhandler  = new BM_DBhandler();
		$booking    = $dbhandler->get_row( 'BOOKING', $booking_id, 'id' );

		if ( ! $booking ) {
			wp_send_json_error( esc_html__( 'Booking not found', 'service-booking' ) );
			return;
		}

		$customer_data = ( new BM_Request() )->get_customer_info_for_order( $booking->id );

		if ( ! $customer_data ) {
			wp_send_json_error( esc_html__( 'Customer not found', 'service-booking' ) );
			return;
		}

		$html  = '<div class="order-details">';
		$html  = '<div class="fx-modal-header">';
		$html .= '<h2>' . esc_html__( 'Order Details ', 'service-booking' ) . '#' . $booking->id . '</h2>';
		$html .= '</div>';
		$html .= '<table class="widefat fixed">';
		$html .= '<tr><th>' . esc_html__( 'Attendee', 'service-booking' ) . ':</th><td>' . $customer_data['billing_first_name'] ?? '' . '</td></tr>';
		$html .= '<tr><th>' . esc_html__( 'Email', 'service-booking' ) . ':</th><td>' . $customer_data['billing_email'] ?? '' . '</td></tr>';
		$html .= '<tr><th>' . esc_html__( 'Service', 'service-booking' ) . ':</th><td>' . $booking->service_name . '</td></tr>';
		$html .= '<tr><th>' . esc_html__( 'Booking Date', 'service-booking' ) . ':</th><td>' . $booking->booking_date . '</td></tr>';
		$html .= '<tr><th>' . esc_html__( 'Order Status', 'service-booking' ) . ':</th><td>' . ucfirst( $booking->order_status ) . '</td></tr>';

		$html .= '</table></div>';

		wp_send_json_success( $html );
	}//end bm_get_order_detail_for_check_in()


	/**
	 * Update checkin status
	 *
	 * @author Darpan
	 */
	public function bm_update_checkin_status() {
		$nonce = filter_input( INPUT_POST, 'nonce' );

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
			return;
		}

		$checkin_id = filter_input( INPUT_POST, 'checkin_id', FILTER_VALIDATE_INT );
		$status     = sanitize_text_field( filter_input( INPUT_POST, 'new_status' ) );
		$booking_id = filter_input( INPUT_POST, 'booking_id', FILTER_VALIDATE_INT );

		$dbhandler = new BM_DBhandler();
		$checkin   = $checkin_id ? $dbhandler->get_row( 'CHECKIN', $checkin_id, 'id' ) : null;

		$data = array(
			'status'     => $status,
			'updated_at' => current_time( 'mysql' ),
		);

		if ( $status === 'checked_in' ) {
			$data['checkin_time'] = current_time( 'mysql' );
		} else {
			$data['checkin_time'] = null;
		}

		if ( $checkin ) {
			$updated = $dbhandler->update_row( 'CHECKIN', 'id', $checkin_id, $data );
		} else {
			if ( ! $booking_id ) {
				wp_send_json_error( esc_html__( 'Booking ID required to create checkin record.', 'service-booking' ) );
				return;
			}

			$data['booking_id'] = $booking_id;
			$data['qr_token']   = $dbhandler->get_value( 'BOOKING', 'booking_key', $booking_id, 'id' );
			$data['qr_scanned'] = ( $status === 'checked_in' ) ? 1 : 0;
			$data['created_at'] = current_time( 'mysql' );

			$updated = $dbhandler->insert_row( 'CHECKIN', $data );
		}

		if ( ! $updated ) {
			wp_send_json_error( __( 'Unable to update or create checkin.', 'service-booking' ) );
		}

		wp_send_json_success();
	}



	/**
	 * Manual checkin
	 *
	 * @author Darpan
	 */
	public function bm_manual_checkin_check() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
		}

		$search_type = sanitize_text_field( filter_input( INPUT_POST, 'search_type' ) ?? '' );
		$raw_value   = $_POST['search_value'] ?? '';

		if ( is_array( $raw_value ) ) {
			$search_value = array_map( 'sanitize_text_field', $raw_value );
		} else {
			$search_value = sanitize_text_field( $raw_value );
		}

		if ( empty( $search_type ) || empty( $search_value ) ) {
			wp_send_json_error( __( 'Invalid search parameters', 'service-booking' ) );
		}

		$db = new BM_DBhandler();

		$bmrequests = new BM_Request();

		if ( $search_type === 'reference' ) {
			$booking_id = $db->get_value( 'BOOKING', 'id', $search_value, 'booking_key' );
			if ( ! $booking_id ) {
				wp_send_json_error( __( 'Booking not found', 'service-booking' ) );
			}

			$html = $bmrequests->bm_get_order_details_attachment( (int) $booking_id, false, false );
			if ( empty( $html ) ) {
				wp_send_json_error( __( 'Booking data not found', 'service-booking' ) );
			}

			wp_send_json_success( $html );
		}

		$joins = array(
			array(
				'table' => 'CUSTOMERS',
				'alias' => 'c',
				'on'    => 'c.id = b.customer_id',
				'type'  => 'LEFT',
			),
			array(
				'table' => 'CHECKIN',
				'alias' => 'ch',
				'on'    => 'ch.booking_id = b.id',
				'type'  => 'LEFT',
			),
		);

		if ( $search_type === 'email' ) {
			$where = array( 'c.customer_email' => array( '=' => $search_value ) );
		} elseif ( $search_type === 'service' ) {
			$where = array( 'b.service_id' => array( 'IN' => $search_value ) );
		} else {
			$where = array(
				'c.customer_name' => array(
					'LIKE' => '%' . $search_value,
				),
			);
		}

		$results = $db->get_results_with_join(
			array( 'BOOKING', 'b' ),
			'b.id, b.service_id, b.service_name, b.total_svc_slots as svc_participants, b.total_ext_svc_slots as ex_svc_participants, b.booking_key, c.customer_email, c.billing_details, ch.qr_scanned, ch.checkin_time',
			$joins,
			$where,
			'results'
		);

		if ( ! $results || count( $results ) === 0 ) {
			wp_send_json_error( __( 'No bookings found', 'service-booking' ) );
		}

		ob_start(); ?>
		<div class="bm-bookings-list">
			<table class="manual_checkin_records_table widefat striped">
				<thead>
					<tr>
						<th><input type="checkbox" id="bm-checkall"></th>
						<th><?php esc_html_e( 'Booking Key', 'service-booking' ); ?></th>
						<th><?php esc_html_e( 'Service Name', 'service-booking' ); ?></th>
						<?php if ( $search_type === 'email' ) : ?>
							<th><?php esc_html_e( 'Email', 'service-booking' ); ?></th>
						<?php else : ?>
							<th><?php esc_html_e( 'First Name', 'service-booking' ); ?></th>
							<th><?php esc_html_e( 'Last Name', 'service-booking' ); ?></th>
						<?php endif; ?>
						<th><?php esc_html_e( 'Service Participants', 'service-booking' ); ?></th>
						<th><?php esc_html_e( 'Extra Service Participants', 'service-booking' ); ?></th>
						<th><?php esc_html_e( 'Check-in Status', 'service-booking' ); ?></th>
						<th><?php esc_html_e( 'Check-in Date', 'service-booking' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'service-booking' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $results as $row ) :
					$first_name = $last_name = '';
					if ( ! empty( $row->billing_details ) ) {
						$details = maybe_unserialize( $row->billing_details );
						if ( is_array( $details ) ) {
							$first_name = esc_html( $details['billing_first_name'] ?? '' );
							$last_name  = esc_html( $details['billing_last_name'] ?? '' );
						}
					}
					$status = ( $row->qr_scanned == 1 ) ? __( 'Checked-in', 'service-booking' ) : __( 'Pending', 'service-booking' );
					$date   = ! empty( $row->checkin_time ) ? $bmrequests->bm_convert_date_format( $row->checkin_time, 'Y-m-d H:i:s', 'd/m/y H:i' ) : '-';
					?>
					<tr>
						<td><input type="checkbox" class="bm-booking-select" value="<?php echo esc_attr( $row->id ); ?>"></td>
						<td><?php echo esc_html( $row->booking_key ); ?></td>
						<td><?php echo esc_html( $row->service_name ); ?></td>
						<?php if ( $search_type === 'email' ) : ?>
							<td><?php echo esc_html( $row->customer_email ); ?></td>
						<?php else : ?>
							<td><?php echo esc_html( $first_name ); ?></td>
							<td><?php echo esc_html( $last_name ); ?></td>
						<?php endif; ?>
						<td><?php echo esc_html( $row->svc_participants ); ?></td>
						<td><?php echo esc_html( $row->ex_svc_participants ); ?></td>
						<td><?php echo esc_html( $status ); ?></td>
						<td><?php echo esc_html( $date ); ?></td>
						<td>
							<div class="bm-view-details" data-id="<?php echo esc_attr( $row->id ); ?>">
								<i class="fa fa-eye"></i> <?php esc_html_e( 'View', 'service-booking' ); ?>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
		$html = ob_get_clean();

		wp_send_json_success( $html );
	}//end bm_manual_checkin_check()


	/**
	 * View manual checkin order details
	 *
	 * @author Darpan
	 */
	public function bm_manual_checkin_view_details() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
			return;
		}

		$booking_id = intval( filter_input( INPUT_POST, 'booking_id' ) ?? 0 );
		if ( ! $booking_id ) {
			wp_send_json_error( __( 'Invalid booking ID', 'service-booking' ) );
			return;
		}

		$html = ( new BM_Request() )->bm_get_order_details_attachment( (int) $booking_id, false, false );
		if ( empty( $html ) ) {
			wp_send_json_error( __( 'Booking data not found', 'service-booking' ) );
			return;
		}

		wp_send_json_success( $html );
	}//end bm_manual_checkin_view_details()


	/**
	 * Fetch email details
	 *
	 * @author Darpan
	 */
	public function bm_show_mail_details() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$bmrequests    = new BM_Request();
		$post          = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$email_details = '';

		if ( $post != false && $post != null ) {
			$email_id      = isset( $post['id'] ) ? $post['id'] : 0;
			$email_details = $bmrequests->bm_fetch_mail_details( $email_id );

			if ( empty( $email_details ) ) {
				$email_details = '<div class="textcenter">' . esc_html__( 'Nothing to show', 'service-booking' ) . '</div>';
			}
		}

		echo wp_kses( $email_details, $bmrequests->bm_fetch_expanded_allowed_tags() );
		die;
	}//end bm_show_mail_details()


	/**
	 * Fetch email body
	 *
	 * @author Darpan
	 */
	public function bm_show_email_body() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$email_body = '';

		if ( $post != false && $post != null ) {
			$email_id   = isset( $post['id'] ) ? $post['id'] : 0;
			$email_body = $dbhandler->get_value( 'EMAILS', 'mail_body', $email_id, 'id' );

			if ( empty( $email_body ) ) {
				$email_body = '<div class="textcenter">' . esc_html__( 'Nothing to show', 'service-booking' ) . '</div>';
			}
		}

		echo wp_kses( $email_body, $bmrequests->bm_fetch_expanded_allowed_tags() );
		die;
	}//end bm_show_email_body()


	/**
	 * Open email body
	 *
	 * @author Darpan
	 */
	public function bm_open_email_body() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array();

		if ( $post != false && $post != null ) {
			$email_id        = isset( $post['id'] ) ? $post['id'] : 0;
			$module_type     = isset( $post['module_type'] ) ? $post['module_type'] : '';
			$email_record    = $dbhandler->get_row( 'EMAILS', $email_id );
			$email_body      = isset( $email_record->mail_body ) ? $email_record->mail_body : '';
			$mail_subject    = isset( $email_record->mail_sub ) ? $email_record->mail_sub : '';
			$to_email        = isset( $email_record->mail_to ) ? $email_record->mail_to : '';
			$mail_cc         = isset( $email_record->mail_cc ) ? $email_record->mail_cc : '';
			$mail_bcc        = isset( $email_record->mail_bcc ) ? $email_record->mail_bcc : '';
			$mail_attahments = isset( $email_record->mail_attachments ) ? $email_record->mail_attachments : '';
			$attachments     = ! empty( $mail_attahments ) ? maybe_unserialize( $mail_attahments ) : array();

			if ( empty( $mail_subject ) ) {
				$mail_subject = esc_html__( 'Resending mail', 'service-booking' );
			}

			if ( empty( $email_body ) ) {
				$email_body = '<div class="textcenter">' . esc_html__( 'Type here', 'service-booking' ) . '</div>';
			}

			if ( $module_type === 'checkin' ) {
				$order_id = $dbhandler->get_value( 'EMAILS', 'module_id', $email_id, 'id' );

				$ticket_file_path = plugin_dir_path( __DIR__ ) . 'src/mail-attachments/new-mail/order-details/order-details-booking-' . $order_id . '.pdf';
				$ticket_file_url  = plugin_dir_url( __DIR__ ) . 'src/mail-attachments/new-mail/order-details/order-details-booking-' . $order_id . '.pdf';

				$filepaths = array();
				$basenames = array();

				if ( file_exists( $ticket_file_path ) ) {
					$filepaths[] = $ticket_file_url;
					$basenames[] = __( 'Booking Ticket.pdf', 'service-booking' );
				}

				$attachments = array(
					'filepath' => $filepaths,
					'basename' => $basenames,
				);
			}

			$data['to']          = $to_email;
			$data['cc']          = $mail_cc;
			$data['bcc']         = $mail_bcc;
			$data['attachments'] = ! empty( $attachments ) && ! empty( array_filter( $attachments, fn( $v ) => ! empty( array_filter( $v ) ) ) ) ? $attachments : array();
			$data['subject']     = $mail_subject;
			$data['body']        = wp_kses( $email_body, $bmrequests->bm_fetch_expanded_allowed_tags() );
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_open_email_body()


	/**
	 * Resend email
	 *
	 * @author Darpan
	 */
	public function bm_resend_email() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler   = new BM_DBhandler();
		$bmrequests  = new BM_Request();
		$bm_mail     = new BM_Email();
		$post        = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data        = array( 'status' => false );
		$file_paths  = array();
		$attachments = array();

		if ( $post != false && $post != null ) {
			$to            = isset( $post['to'] ) ? $post['to'] : '';
			$cc            = isset( $post['cc'] ) ? $post['cc'] : '';
			$bcc           = isset( $post['bcc'] ) ? $post['bcc'] : '';
			$subject       = isset( $post['subject'] ) ? $post['subject'] : '';
			$template_body = isset( $post['body'] ) ? $post['body'] : '';
			$email_id      = isset( $post['email_id'] ) ? $post['email_id'] : 0;
			$module_id     = isset( $post['module_id'] ) ? $post['module_id'] : 0;
			$module_type   = isset( $post['module_type'] ) ? $post['module_type'] : '';
			$type          = isset( $post['type'] ) ? $post['type'] : '';
			$mail_type     = isset( $post['mail_type'] ) ? $post['mail_type'] : '';
			$template_id   = isset( $post['template_id'] ) ? $post['template_id'] : 0;
			$process_id    = isset( $post['process_id'] ) ? $post['process_id'] : 0;
			$guids         = isset( $post['guids'] ) ? $post['guids'] : array();
			$custom_files  = isset( $post['custom_files'] ) ? $post['custom_files'] : array();
			$mail_id       = 0;
			$source        = -1;
			$sent          = false;
			$copied_files  = array();
			$language      = $dbhandler->get_global_option_value( 'bm_flexi_current_language', 'en' );

			if ( ! empty( $to ) && ! empty( $subject ) && ! empty( $template_body ) && ! empty( $module_id ) && ! empty( $email_id ) ) {
				if ( $mail_type == 'failed_order' ) {
					$module_key    = $dbhandler->get_value( $module_type, 'booking_key', $module_id, 'id' );
					$template_body = $bm_mail->bm_filter_email_content( $template_body, (string) $module_key );
				} else {
					$template_body = $bm_mail->bm_filter_email_content( $template_body, (int) $module_id );
				}

				$from     = $bm_mail->bm_get_from_email();
				$headers  = "MIME-Version: 1.0\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8\r\n";
				$headers .= "From:$from\r\n";

				$mail_data = array(
					'module_type' => $module_type,
					'module_id'   => $module_id,
					'mail_type'   => $mail_type,
					'template_id' => $template_id,
					'process_id'  => $process_id,
					'mail_to'     => $to,
					'mail_cc'     => $cc,
					'mail_bcc'    => $bcc,
					'mail_sub'    => wp_kses_post( $subject ),
					'mail_body'   => ! empty( $template_body ) ? wp_kses_post( wp_unslash( $template_body ) ) : '',
					'is_resent'   => 1,
					'mail_lang'   => $language,
					'status'      => 1,
				);

				if ( ! empty( $guids ) && is_array( $guids ) && array_filter( $guids ) ) {
					$directory = plugin_dir_path( __DIR__ ) . 'src/mail-attachments/resend-mail/' . strtolower( $module_type ) . '-' . $module_id;

					if ( ! file_exists( $directory ) ) {
						mkdir( $directory, 0777, true );
					}

					foreach ( $guids as $guid ) {
						if ( ! empty( $guid ) ) {
							$file_path                 = get_attached_file( $guid );
							$filename                  = basename( $file_path );
							$file_paths[]              = $file_path;
							$attachments['guid'][]     = $guid;
							$attachments['filepath'][] = $file_path;
							$attachments['basename'][] = $filename;
							$destination               = $directory . '/' . $filename;

							if ( copy( $file_path, $destination ) ) {
								$copied_files[] = $destination;
							}
						}
					}
				}

				if ( $type == 'checkin' && ! empty( $custom_files ) && is_array( $custom_files ) ) {
					foreach ( $custom_files as $file_url ) {
						if ( filter_var( $file_url, FILTER_VALIDATE_URL ) ) {
							$file_path = str_replace( site_url() . '/', ABSPATH, $file_url );
							$file_path = wp_normalize_path( $file_path );

							if ( file_exists( $file_path ) ) {
								$file_paths[]              = $file_path;
								$attachments['filepath'][] = $file_path;
								$attachments['basename'][] = basename( $file_path );
								$attachments['guid'][]     = '';
							}
						}
					}
				}

				if ( ! empty( $cc ) ) {
					$headers .= "Cc:$cc\r\n";
				}

				if ( ! empty( $bcc ) ) {
					$headers .= "Bcc:$bcc\r\n";
				}

				ob_start();
				include plugin_dir_path( __DIR__ ) . 'admin/partials/booking-management-customer-email-layout.php';
				$template_body = ob_get_contents();
				ob_end_clean();

				$mail_data['mail_attachments'] = $attachments;

				$mail_data = $bmrequests->sanitize_request( $mail_data, 'EMAILS' );

				if ( $mail_data != false && $mail_data != null ) {
					$mail_data['created_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();
					$mail_id                 = $dbhandler->insert_row( 'EMAILS', $mail_data );
				}

				if ( ! empty( $mail_id ) ) {
					if ( ! empty( $file_paths ) ) {
						$sent = wp_mail( $to, $subject, $template_body, $headers, $file_paths );
					} else {
						$sent = wp_mail( $to, $subject, $template_body, $headers );
					}
				}

				if ( $sent ) {
					$data['status'] = true;
				} elseif ( ! empty( $copied_files ) ) {
					foreach ( $copied_files as $file ) {
						if ( file_exists( $file ) ) {
							unlink( $file );
						}
					}
				}
			}
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_resend_email()


	/**
	 * Add email attachment
	 *
	 * @author Darpan
	 */
	public function bm_add_email_attachment() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler          = new BM_DBhandler();
		$bmrequests         = new BM_Request();
		$attachments        = isset( $_FILES ) ? $_FILES : array();
		$email_id           = filter_input( INPUT_POST, 'email_id', FILTER_VALIDATE_INT );
		$existing_guids     = filter_input( INPUT_POST, 'existing_guids' );
		$data               = array( 'status' => 0 );
		$guids              = array();
		$existing_filenames = array();
		$allowFiletypes     = array( 'jpg', 'jpeg', 'png', 'gif', 'pdf', 'svg', 'zip', 'docx', 'doc', 'xlsx', 'ppt', 'csv' );

		if ( ! empty( $attachments ) && is_array( $attachments ) && ( $email_id != false && $email_id != null ) ) {
			if ( ! empty( $existing_guids ) ) {
				if ( ! is_array( $existing_guids ) ) {
					$existing_guids = explode( ',', $existing_guids );
				}

				if ( ! empty( $existing_guids ) && is_array( $existing_guids ) ) {
					foreach ( $existing_guids as $existing_guid ) {
						$existing_filenames[] = basename( get_attached_file( $existing_guid ) );
					}
				}
			}

			foreach ( $attachments as $attachment ) {
				$filename = isset( $attachment['name'] ) ? $attachment['name'] : '';

				if ( ! empty( $existing_filenames ) && is_array( $existing_filenames ) ) {
					if ( ! in_array( $filename, $existing_filenames ) ) {
						$guids[] = $bmrequests->bm_make_upload_and_get_attached_id( $attachment, $allowFiletypes );
					}
				} else {
					$guids[] = $bmrequests->bm_make_upload_and_get_attached_id( $attachment, $allowFiletypes );
				}
			}

			if ( ! empty( $guids ) && array_filter( $guids ) ) {
				$data['status'] = 1;
			}

			if ( ! empty( $existing_guids ) && is_array( $existing_guids ) ) {
				$guids = array_values( array_merge( $existing_guids, $guids ) );
			}
		}

		$data['guids'] = $guids;
		echo wp_json_encode( $data );
		die;
	}//end bm_add_email_attachment()


	/**
	 * Remove mail attachment
	 *
	 * @author Darpan
	 */
	public function bm_remove_email_attachment() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler = new BM_DBhandler();
		$post      = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data      = array( 'status' => false );
		$guids     = array();

		if ( $post != false && $post != null ) {
			$guid     = isset( $post['id'] ) ? $post['id'] : -1;
			$email_id = isset( $post['email_id'] ) ? $post['email_id'] : 0;
			$guids    = isset( $post['guids'] ) ? $post['guids'] : array();

			if ( ( $guid != -1 ) && ! empty( $guids ) && is_array( $guids ) ) {
				$file_index = (int) array_search( $guid, $guids );
				if ( isset( $guids[ $file_index ] ) ) {
					unset( $guids[ $file_index ] );

					if ( ! empty( $guids ) ) {
						$guids = array_values( $guids );
					}

					$data['status'] = true;
				}
			}
		}

		$data['guids'] = $guids;
		echo wp_json_encode( $data );
		die;
	}//end bm_remove_email_attachment()


	/**
	 * Remove saved mail attachment
	 *
	 * @author Darpan
	 */
	public function bm_remove_temporary_email_attachment() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler = new BM_DBhandler();
		$post      = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$status    = 0;
		$guids     = array();

		if ( $post != false && $post != null ) {
			$email_id    = isset( $post['email_id'] ) ? $post['email_id'] : 0;
			$attachments = maybe_unserialize( $dbhandler->get_value( 'EMAILS', 'mail_attachments', $email_id, 'id' ) );

			if ( ! empty( $attachments ) ) {
				$guids = isset( $attachments['guid'] ) ? $attachments['guid'] : array();
			}

			if ( ! empty( $guids ) ) {
				$deleted = delete_option( 'bm_resend_email_attachments-' . $email_id );

				if ( $deleted ) {
					$status = 1;
				}
			} else {
				$status = 2;
			}
		}

		echo wp_kses_post( $status );
		die;
	}//end bm_remove_temporary_email_attachment()


	/**
	 * Check admin password
	 *
	 * @author Darpan
	 */
	public function bm_check_admin_password() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler = new BM_DBhandler();
		$post      = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$status    = false;

		if ( $post != false && $post != null ) {
			$username = isset( $post['username'] ) ? $post['username'] : '';
			$password = isset( $post['password'] ) ? $post['password'] : '';

			if ( ! empty( $username ) && ! empty( $password ) ) {
				/**$user = wp_authenticate( $username, $password );
				$user = wp_authenticate_username_password( null, $username, $password );

				if ( is_a( $user, 'WP_User' ) ) {
					if ( in_array( 'administrator', (array) $user->roles ) ) {
						$status = true;
					}
				}*/

				$user = get_user_by( 'login', $username );

				if ( ! $user ) {
					$user = get_user_by( 'email', $username );
				}

				if ( $user ) {
					$result = wp_check_password( $password, $user->user_pass, $user->ID );

					if ( ! $result ) {
						$result = password_verify( $password, $user->user_pass );
					}

					if ( $result && is_a( $user, 'WP_User' ) ) {
						if ( in_array( 'administrator', (array) $user->roles ) ) {
							$status = true;
						}
					}
				}
			}
		}

		echo wp_kses_post( $status );
		die;
	}//end bm_check_admin_password()



	/**
	 * Fetch checkin export modal html
	 *
	 * @author Darpan
	 */
	public function bm_export_checkin_options_html() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$bmrequests = new BM_Request();
		$html       = $bmrequests->bm_fetch_export_html_with_options();
		$data       = array( 'status' => true );

		if ( empty( $html ) ) {
			$data['status'] = false;
			$html           = '<div class="textcenter order_export_html_result">' . esc_html__( 'Something went wrong, try again', 'service-booking' ) . '</div>';
		}

		$data['html'] = $html;

		echo wp_json_encode( $data );
		die;
	}//end bm_export_checkin_options_html()



	/**
	 * Export checkin records
	 *
	 * @author Darpan
	 */
	public function bm_fetch_export_checkin_records_as_per_type() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();
		$post       = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data       = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$type       = isset( $post['type'] ) ? $post['type'] : '';
			$start_page = isset( $post['start_page'] ) ? $post['start_page'] : 0;
			$end_page   = isset( $post['end_page'] ) ? $post['end_page'] : 0;
			$limit      = isset( $post['limit'] ) ? absint( $post['limit'] ) : 0;

			$order_column = isset( $post['order_column'] ) ? $post['order_column'] : 'id';
			$order_dir    = isset( $post['order_dir'] ) ? $post['order_dir'] : 'DESC';

			$search_term  = isset( $post['search_string'] ) ? $post['search_string'] : '';
			$service_from = isset( $post['service_from'] ) ? $post['service_from'] : '';
			$service_to   = isset( $post['service_to'] ) ? $post['service_to'] : '';
			$checkin_from = isset( $post['order_from'] ) ? $post['order_from'] : '';
			$checkin_to   = isset( $post['order_to'] ) ? $post['order_to'] : '';
			$services     = isset( $post['services'] ) ? $post['services'] : array();

			$search_params = array(
				'search_term'  => sanitize_text_field( $post['search_string'] ?? '' ),
				'service_from' => $post['service_from'] ?? '',
				'service_to'   => $post['service_to'] ?? '',
				'checkin_from' => $post['order_from'] ?? '',
				'checkin_to'   => $post['order_to'] ?? '',
				'services'     => $post['services'] ?? array(),
			);

			$filtered_checkins = $bmrequests->bm_fetch_all_order_checkins();

			if ( ! empty( $search_params['search_term'] ) ) {
				$search_term       = strtolower( $search_params['search_term'] );
				$filtered_checkins = array_filter(
					$filtered_checkins,
					function ( $checkin ) use ( $search_term ) {
						$search_fields = array(
							'id',
							'booking_id',
							'checkin_id',
							'serial_no',
							'service_id',
							'service_name',
							'booking_date',
							'first_name',
							'last_name',
							'contact_no',
							'email_address',
							'total_cost',
							'checkin_time',
							'checkin_status',
							'email_id',
						);

						foreach ( $search_fields as $field ) {
							if ( isset( $checkin[ $field ] ) && stripos( strtolower( $checkin[ $field ] ), $search_term ) !== false ) {
								return true;
							}
						}
						return false;
					}
				);
			}

			if ( ! empty( $search_params['service_from'] ) && ! empty( $search_params['service_to'] ) ) {
				$from_date = DateTime::createFromFormat( 'd/m/y', $search_params['service_from'] );
				$to_date   = DateTime::createFromFormat( 'd/m/y', $search_params['service_to'] );

				if ( $from_date && $to_date ) {
					$filtered_checkins = array_filter(
						$filtered_checkins,
						function ( $checkin ) use ( $from_date, $to_date ) {
							$booking_date = DateTime::createFromFormat( 'd/m/y H:i', $checkin['booking_date'] );
							return $booking_date >= $from_date && $booking_date <= $to_date;
						}
					);
				}
			}

			if ( ! empty( $search_params['checkin_from'] ) && ! empty( $search_params['checkin_to'] ) ) {
				$from_date = DateTime::createFromFormat( 'd/m/y', $search_params['checkin_from'] );
				$to_date   = DateTime::createFromFormat( 'd/m/y', $search_params['checkin_to'] );

				if ( $from_date && $to_date ) {
					$filtered_checkins = array_filter(
						$filtered_checkins,
						function ( $checkin ) use ( $from_date, $to_date ) {
							$order_date = DateTime::createFromFormat( 'd/m/y H:i', $checkin['checkin_time'] );
							return $order_date >= $from_date && $order_date <= $to_date;
						}
					);
				}
			}

			if ( ! empty( $search_params['services'] ) ) {
				$filtered_checkins = array_filter(
					$filtered_checkins,
					function ( $checkin ) use ( $search_params ) {
						return in_array( $checkin['service_id'], $search_params['services'], true );
					}
				);
			}

			$filtered_checkins = array_values( $filtered_checkins );

			if ( ! empty( $order_column ) ) {
				$filtered_checkins = $bmrequests->bm_sort_array_by_key( $filtered_checkins, $order_column, $order_dir === 'desc' );
			}

			switch ( $type ) {
				case 'all':
					$offset = 0;
					$limit  = 0;
					break;
				case 'current':
					break;
				case 'range':
					if ( $start_page <= 0 || $end_page <= 0 || $start_page % 1 !== 0 || $end_page % 1 !== 0 || $start_page > $end_page || empty( $total_pages ) || $end_page > $total_pages ) {
						$filtered_checkins = array();
					} else {
						$offset = ( $start_page - 1 ) * $limit;
						$limit  = ( $end_page - $start_page + 1 ) * $limit;
					}
					break;
				default:
					$filtered_checkins = array();
					break;
			}

			$exclude_columns = array(
				'ticket_pdf',
				'actions',
			);

			$column_headers = array_values( array_diff( array_values( $bmrequests->bm_fetch_active_columns( 'checkin' ) ), array( 'Ticket PDF', 'Actions', 'PDF del biglietto', 'Azioni' ) ) );
			$active_columns = array_keys( $bmrequests->bm_fetch_active_columns( 'checkin' ) );

			if ( ! empty( $filtered_checkins ) ) {
				$filtered_checkins = $dbhandler->bm_apply_offset_limit_and_sort_existing_data( $filtered_checkins, $offset, $limit );
			}

			if ( ! empty( $filtered_checkins ) && ! empty( $active_columns ) ) {
				$filtered_checkins = $dbhandler->filter_existing_data_by_columns( $filtered_checkins, $active_columns, $exclude_columns, true );
				$data['status']    = true;
			}

			$data['headers'] = ! empty( $column_headers ) && $data['status'] == true ? $column_headers : array();
			$data['keys']    = ! empty( $active_columns ) && $data['status'] == true ? array_values( array_diff( $active_columns, $exclude_columns ) ) : array();
			$data['orders']  = ! empty( $filtered_checkins ) && $data['status'] == true ? $filtered_checkins : array();
		}

		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_export_checkin_records_as_per_type()


	/**
	 * Display the service date and slot timing on the WooCommerce order details screen.
	 *
	 * Hooked to `woocommerce_admin_order_data_after_order_details`.
	 *
	 * @since 1.0.0
	 * @param WC_Order $order WooCommerce order object.
	 * @return void
	 */
	public function bm_display_service_date_in_admin( $order ) {
		$order_id     = $order->get_id();
		$service_date = get_post_meta( $order_id, '_flexi_service_date', true );
		$booked_slots = get_post_meta( $order_id, '_flexi_booked_slots', true );

		if ( $service_date ) {
			echo '<div class="order_data_column flexi-service-date">';
			echo '<h3>' . esc_html__( 'Service Date', 'service-booking' ) . '</h3>';
			echo '<p>' . esc_html( $service_date ) . '</p>';
			echo '</div>';
		}

		if ( $booked_slots ) {
			echo '<div class="order_data_column flexi-booked-slots">';
			echo '<h3>' . esc_html__( 'Slot Timing:', 'service-booking' ) . '</h3>';
			echo '<p>' . esc_html( $booked_slots ) . '</p>';
			echo '</div>';
		}
	}//end bm_display_service_date_in_admin()


	/**
	 * Delete flexi order data if woocommerce order is deleted permanently
	 *
	 * @author Darpan
	 */
	public function bm_remove_flexi_order_if_woocommerce_order_is_permanently_deleted( $post_id ) {
		if ( get_post_type( $post_id ) === 'shop_order' ) {
			$flexi_booking_id  = get_post_meta( $post_id, '_flexi_booking_id', true );
			$flexi_customer_id = get_post_meta( $post_id, '_flexi_customer_id', true );

			if ( $flexi_booking_id > 0 || $flexi_customer_id > 0 ) {
				( new BM_Request() )->bm_remove_order_data( $flexi_booking_id, $flexi_customer_id );
			}
		}
	}//end bm_remove_flexi_order_if_woocommerce_order_is_permanently_deleted()


	/**
	 * Modify flexi order data as per woocommerce order trash
	 *
	 * @author Darpan
	 */
	public function bm_modify_flexi_plugin_order_on_woocommerce_order_trash( $post_id ) {
		if ( get_post_type( $post_id ) === 'shop_order' ) {
			$flexi_booking_id   = get_post_meta( $post_id, '_flexi_booking_id', true );
			$flexi_service_date = get_post_meta( $post_id, '_flexi_service_date', true );

			if ( $flexi_booking_id > 0 && ! empty( $flexi_service_date ) ) {
				$dbhandler    = new BM_DBhandler();
				$booked_slots = $dbhandler->get_value( 'BOOKING', 'booking_slots', $flexi_booking_id, 'id' );
				$booked_slots = ! empty( $booked_slots ) ? maybe_unserialize( $booked_slots ) : array();

				if ( isset( $booked_slots['to'] ) ) {
					$flexi_service_date = $flexi_service_date . ' ' . $booked_slots['to'];

					$timezone     = $dbhandler->get_global_option_value( 'bm_booking_time_zone', 'Asia/Kolkata' );
					$today        = new DateTime( 'now', new DateTimeZone( $timezone ) );
					$current_date = $today->format( 'Y-m-d' );
					$current_time = $today->format( 'H:i' );

					$currentDateTime = $current_date . ' ' . $current_time;

					if ( strtotime( $flexi_service_date ) > strtotime( $currentDateTime ) ) {
						( new BM_Request() )->bm_cancel_flexi_order( $flexi_booking_id );
					}
				}
			}
		}
	}//end bm_modify_flexi_plugin_order_on_woocommerce_order_trash()


	/**
	 * Schedule woocommerce order status check as order untrash
	 *
	 * @author Darpan
	 */
	public function bm_schedule_woocommerce_order_status_check_on_untrash( $post_id ) {
		if ( get_post_type( $post_id ) === 'shop_order' ) {
			$flexi_booking_id   = get_post_meta( $post_id, '_flexi_booking_id', true );
			$flexi_service_date = get_post_meta( $post_id, '_flexi_service_date', true );

			if ( $flexi_booking_id > 0 && ! empty( $flexi_service_date ) ) {
				$dbhandler    = new BM_DBhandler();
				$booked_slots = $dbhandler->get_value( 'BOOKING', 'booking_slots', $flexi_booking_id, 'id' );
				$booked_slots = ! empty( $booked_slots ) ? maybe_unserialize( $booked_slots ) : array();

				if ( isset( $booked_slots['to'] ) ) {
					$flexi_service_date = $flexi_service_date . ' ' . $booked_slots['to'];

					$timezone     = $dbhandler->get_global_option_value( 'bm_booking_time_zone', 'Asia/Kolkata' );
					$today        = new DateTime( 'now', new DateTimeZone( $timezone ) );
					$current_date = $today->format( 'Y-m-d' );
					$current_time = $today->format( 'H:i' );

					$currentDateTime = $current_date . ' ' . $current_time;

					if ( strtotime( $flexi_service_date ) > strtotime( $currentDateTime ) ) {
						wp_schedule_single_event( time() + 2, 'bm_update_flexi_order_as_woocommerce_order_is_restored', array( $post_id ) );
					}
				}
			}
		}
	} //end bm_schedule_order_status_check_on_untrash()


	/**
	 * Modify flexi order data as per woocommerce order untrash
	 *
	 * @since 1.0.0
	 * @param int $post_id The post ID being untrashed.
	 * @return void
	 */
	public function bm_modify_flexi_plugin_order_on_woocommerce_order_untrash( $post_id ) {
		if ( get_post_type( $post_id ) !== 'shop_order' ) {
			return;
		}

		$bmrequests       = new BM_Request();
		$flexi_booking_id = get_post_meta( $post_id, '_flexi_booking_id', true );
		$order            = wc_get_order( $post_id );

		if ( ! $order || empty( $flexi_booking_id ) ) {
			return;
		}

		$restored_status = $order->get_status();

		if ( in_array( $restored_status, array( 'pending', 'processing' ), true ) ) {
			$bmrequests->bm_update_flexi_order_status_as_processing( $flexi_booking_id );
		} elseif ( $restored_status === 'completed' ) {
			$bmrequests->bm_update_flexi_order_status_as_completed( $flexi_booking_id );
		} elseif ( $restored_status === 'canceled' ) {
			$bmrequests->bm_cancel_flexi_order( $flexi_booking_id );
		} elseif ( $restored_status === 'refunded' ) {
			$bmrequests->bm_update_flexi_order_status_as_refunded( $flexi_booking_id );
		} elseif ( $restored_status === 'on-hold' ) {
			$bmrequests->bm_update_flexi_order_status_as_on_hold( $flexi_booking_id );
		}
	}//end bm_modify_flexi_plugin_order_on_woocommerce_order_untrash()


	/**
	 * Hide flexi order itemmeta for woocommerce orders
	 *
	 * @author Darpan
	 */
	public function bm_hide_flexi_order_itemmeta( $arr ) {
		$arr[] = '_flexi_booking_key';
		$arr[] = '_flexi_checkout_key';
		return $arr;
	}//end bm_hide_flexi_order_itemmeta()


	/**
	 * Block status update for expired woocommerce orders
	 *
	 * @author Darpan
	 */
	public function bm_prevent_expired_woocommerce_order_updates( $post_id, $data ) {
		if ( get_post_type( $post_id ) === 'shop_order' ) {
			$is_expired = get_post_meta( $post_id, '_is_flexi_order_expired', true );

			if ( $is_expired ) {
				$flexi_booking_notice = __( 'This order is expired and cannot be updated.', 'service-booking' );
				update_option( 'flexi_booking_notice', $flexi_booking_notice, 'no' );

				$current_screen = get_current_screen();
				$redirect_url   = admin_url( "post.php?post={$post_id}&action=edit" );

				if ( $current_screen && $current_screen->id === 'edit-shop_order' ) {
					$redirect_url = admin_url( 'edit.php?post_type=shop_order' );
				}

				wp_safe_redirect( $redirect_url );
				exit;
			}
		}
	}//end bm_prevent_expired_woocommerce_order_updates()


	/**
	 * Order expiry notice
	 *
	 * @author Darpan
	 */
	public function bm_flexi_admin_notice() {
		$flexi_booking_notice = get_option( 'flexi_booking_notice', false );

		if ( $flexi_booking_notice ) {
			delete_option( 'flexi_booking_notice' );
			?>
			<div class="notice notice-error">
				<p><?php echo esc_html( $flexi_booking_notice ); ?></p>
			</div>
			<?php
		}
	}//end bm_flexi_admin_notice()


	/**
	 * Order expiry notice
	 *
	 * @author Darpan
	 */
	public function bm_disable_admin_notices_on_specific_pages() {
		$screen = get_current_screen();

		$pages_to_disable = array( 'toplevel_page_bm_home', 'flexibooking_page_bm_all_orders', 'admin_page_bm_add_order', 'flexibooking_page_bm_all_customers', 'admin_page_bm_add_customer', 'admin_page_bm_customer_profile', 'flexibooking_page_bm_all_services', 'admin_page_bm_add_service', 'flexibooking_page_bm_all_categories', 'admin_page_bm_add_category', 'flexibooking_page_bm_email_templates', 'admin_page_bm_add_template', 'flexibooking_page_sg-booking-forms', 'admin_page_sg-booking-form-builder', 'flexibooking_page_bm_all_external_service_prices', 'flexibooking_page_bm_voucher_records', 'admin_page_bm_add_external_service_price', 'flexibooking_page_bm_email_records', 'flexibooking_page_bm_all_coupons', 'admin_page_bm_add_coupon', 'flexibooking_page_bm_global', 'admin_page_bm_global_general_settings', 'admin_page_bm_global_css_settings', 'admin_page_bm_global_timezone_country_settings', 'admin_page_bm_global_email_settings', 'admin_page_bm_global_payment_settings', 'admin_page_bm_svc_booking_settings', 'admin_page_bm_upload_settings', 'admin_page_bm_global_language_settings', 'admin_page_bm_global_format_settings', 'admin_page_bm_global_integration_settings', 'admin_page_bm_global_coupon_settings', 'flexibooking_page_bm_service_booking_planner', 'flexibooking_page_bm_single_service_booking_planner', 'flexibooking_page_bm_check_ins', 'flexibooking_page_bm_payment_logs', 'flexibooking_page_bm_email_logs', 'flexibooking_page_bm_pdf_customization', 'flexibooking_page_bm_shared_extras', 'flexibooking_page_bm_booking_analytics', 'admin_page_bm_single_order' );

		if ( in_array( $screen->id, $pages_to_disable ) ) {
			remove_all_actions( 'admin_notices' );
		}
	}//end bm_disable_admin_notices_on_specific_pages()


	/**
	 * Ensure admin page title is never null to prevent strip_tags() deprecation.
	 *
	 * WordPress core calls strip_tags() on the page title in admin-header.php.
	 * On PHP 8.1+ passing null triggers a deprecation warning.
	 *
	 * @since 1.3.0
	 * @param string $admin_title The admin page title.
	 * @return string
	 */
	public function bm_ensure_admin_title( $admin_title ) {
		return is_string( $admin_title ) ? $admin_title : '';
	}//end bm_ensure_admin_title()


	/**
	 * Highlight the FlexiBooking top-level menu for hidden sub-pages.
	 *
	 * Pages registered with an empty parent slug (e.g., bm_single_order,
	 * bm_add_service) don't auto-highlight the parent menu. This filter
	 * tells WordPress to treat them as children of 'bm_home'.
	 *
	 * @since 1.3.0
	 * @param string $parent_file The parent file slug.
	 * @return string
	 */
	public function bm_fix_menu_highlight( $parent_file ) {
		global $plugin_page;

		$hidden_pages = array(
			'bm_add_order',
			'bm_single_order',
			'bm_add_service',
			'bm_add_category',
			'bm_add_template',
			'bm_customer_profile',
			'bm_add_customer',
			'bm_add_external_service_price',
			'sg-booking-form-builder',
			'bm_global_general_settings',
			'bm_global_css_settings',
			'bm_global_timezone_country_settings',
			'bm_global_email_settings',
			'bm_global_payment_settings',
			'bm_global_integration_settings',
			'bm_global_coupon_settings',
			'bm_global_language_settings',
			'bm_global_format_settings',
			'bm_svc_booking_settings',
			'bm_upload_settings',
		);

		if ( isset( $plugin_page ) && in_array( $plugin_page, $hidden_pages, true ) ) {
			$parent_file = 'bm_home';
		}

		return $parent_file;
	}//end bm_fix_menu_highlight()


	/**
	 * Add Screen Options for list table pages.
	 *
	 * Adds the per_page option to the Screen Options dropdown on admin pages
	 * that display WP_List_Table listings.
	 *
	 * @since 1.3.0
	 */
	public function bm_add_screen_options() {
		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'Items per page', 'service-booking' ),
				'default' => 20,
				'option'  => 'bm_list_per_page',
			)
		);

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		$map = array(
			'bm_all_orders'     => 'BM_Orders_List_Table',
			'bm_all_customers'  => 'BM_Customers_List_Table',
			'bm_all_services'   => 'BM_Services_List_Table',
			'bm_all_categories' => 'BM_Categories_List_Table',
			'bm_email_templates' => 'BM_Email_Templates_List_Table',
			'sg-booking-forms'  => 'BM_Forms_List_Table',
			'bm_email_records'  => 'BM_Email_Records_List_Table',
			'bm_voucher_records' => 'BM_Vouchers_List_Table',
			'bm_check_ins'      => 'BM_Checkins_List_Table',
			'bm_shared_extras'  => 'BM_Global_Extras_List_Table',
		);

		if ( isset( $map[ $page ] ) && class_exists( $map[ $page ] ) ) {
			$this->list_tables[ $page ] = new $map[ $page ]();
		}
	}//end bm_add_screen_options()


	/**
	 * Retrieve a list table instance created during the load-{page} hook.
	 *
	 * @since 1.3.1
	 * @param string $page Admin page slug.
	 * @return \WP_List_Table|null
	 */
	public function get_list_table( $page ) {
		return isset( $this->list_tables[ $page ] ) ? $this->list_tables[ $page ] : null;
	}//end get_list_table()


	/**
	 * Persist per_page screen option when saved by the user.
	 *
	 * WordPress calls the set-screen-option filter when a user saves the Screen
	 * Options form. Return the sanitised value so it's stored in user meta.
	 *
	 * @since 1.3.0
	 * @param mixed  $status Current status (false by default).
	 * @param string $option The option name being saved.
	 * @param int    $value  The option value.
	 * @return mixed
	 */
	public static function bm_set_screen_option( $status, $option, $value ) {
		if ( 'bm_list_per_page' === $option ) {
			return absint( $value );
		}
		return $status;
	}//end bm_set_screen_option()


	/**
	 * Check if existing email
	 *
	 * @author Darpan
	 */
	public function bm_check_if_exisiting_customer() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			wp_send_json_error( __( 'Failed security check', 'service-booking' ) );
			return;
		}

		$post = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( empty( $post ) || empty( $post['main_email'] ) || empty( $post['billing_email'] ) || empty( $post['shipping_email'] ) || ! isset( $post['customer_id'] ) ) {
			wp_send_json_error( __( 'Invalid request data', 'service-booking' ) );
			return;
		}

		$main_email     = strtolower( $post['main_email'] );
		$billing_email  = strtolower( $post['billing_email'] );
		$shipping_email = strtolower( $post['shipping_email'] );
		$customer_id    = strtolower( $post['customer_id'] );

		$bmrequests = new BM_Request();

		$data = array(
			'main_email'     => $bmrequests->bm_is_exisiting_customer_email( $main_email, $customer_id ),
			'billing_email'  => $bmrequests->bm_is_exisiting_customer_email( $billing_email, $customer_id ),
			'shipping_email' => $bmrequests->bm_is_exisiting_customer_email( $shipping_email, $customer_id ),
		);

		wp_send_json_success( $data );
	}//end bm_check_if_exisiting_customer()


	/**
	 * Remove a coupon
	 *
	 *  @author Darpan
	 */
	public function bm_remove_coupon_function() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}
		$id        = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
		$dbhandler = new BM_DBhandler();
		$data      = array( 'status' => false );
		if ( $id != false && $id != null ) {
			$coupon = $dbhandler->get_row( 'COUPON', $id );
			if ( ! empty( $coupon ) ) {
				$code                 = $coupon->coupon_code;
				$additional_condition = "AND FIND_IN_SET('$code', coupons)";
				$Bookings             = $dbhandler->get_all_result( 'BOOKING', '*', array( 'is_active' => 1 ), 'results', 0, false, 'id', 'DESC', $additional_condition );
				if ( empty( $Bookings ) ) {
					$removed = $dbhandler->remove_row( 'COUPON', 'id', $id, '%d' );
					if ( $removed ) {
						$data['status'] = true;
					}
				} else {
					$update_data = array(
						'is_active' => '0',
					);
					$dbhandler->update_row( 'COUPON', 'id', $id, $update_data, '', '%d' );
					$data['status'] = true;
				}
			}
		}
		echo wp_json_encode( $data );
		die;
	}//end bm_remove_coupon_function()


	/**
	 * Fetch value for coupon type
	 *
	 * @author Darpan
	 */
	public function bm_fetch_value_for_coupon_type() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		if ( ! class_exists( 'BM_Coupon_validation' ) ) {
			echo wp_json_encode( array( 'status' => false ) );
			die;
		}

		$coupon_validation = new BM_Coupon_validation();
		$post              = filter_input( INPUT_POST, 'post', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data              = array( 'status' => false );

		if ( $post != false && $post != null ) {
			$type     = isset( $post['type'] ) ? $post['type'] : '';
			$response = $coupon_validation->bm_fetch_coupon_value_html( $type );
			if ( ! empty( $response ) ) {
				$data['status'] = true;
			}
			$data['value'] = $response;
		}
		echo wp_json_encode( $data );
		die;
	}//end bm_fetch_value_for_coupon_type()


	/**
	 * Duplicate a service and its related data (time slots, gallery, extras, availability periods).
	 */
	public function bm_duplicate_service() {
		$nonce = filter_input( INPUT_POST, 'nonce' );
		if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
			die( esc_html__( 'Failed security check', 'service-booking' ) );
		}

		$service_id = filter_input( INPUT_POST, 'service_id', FILTER_VALIDATE_INT );
		$data       = array( 'status' => false );

		if ( empty( $service_id ) ) {
			echo wp_json_encode( $data );
			die;
		}

		$dbhandler  = new BM_DBhandler();
		$bmrequests = new BM_Request();

		$service = $dbhandler->get_row( 'SERVICE', $service_id );
		if ( empty( $service ) ) {
			echo wp_json_encode( $data );
			die;
		}

		$now = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();

		// Determine the next position value.
		$max_position = $dbhandler->get_all_result( 'SERVICE', 'MAX(service_position) as max_pos', 1, 'var' );
		$new_position = is_numeric( $max_position ) ? ( (int) $max_position + 1 ) : 1;

		// Build new service data from the original, excluding auto-generated fields.
		$service_arr            = (array) $service;
		$exclude_fields         = array( 'id', 'service_created_at', 'service_updated_at' );
		$new_service            = array_diff_key( $service_arr, array_flip( $exclude_fields ) );
		$new_service['service_name']       = sprintf( __( '%s (Copy)', 'service-booking' ), $service->service_name );
		$new_service['service_position']   = $new_position;
		$new_service['service_created_at'] = $now;

		$new_service_id = $dbhandler->insert_row( 'SERVICE', $new_service );

		if ( empty( $new_service_id ) ) {
			echo wp_json_encode( $data );
			die;
		}

		// Duplicate TIME slots.
		$time_rows = $dbhandler->get_all_result( 'TIME', '*', array( 'service_id' => $service_id ), 'results' );
		if ( ! empty( $time_rows ) ) {
			foreach ( $time_rows as $time_row ) {
				$new_time                    = (array) $time_row;
				unset( $new_time['id'] );
				$new_time['service_id']      = $new_service_id;
				$new_time['time_created_at'] = $now;
				unset( $new_time['time_updated_at'] );
				$dbhandler->insert_row( 'TIME', $new_time );
			}
		}

		// Duplicate GALLERY.
		$gallery_rows = $dbhandler->get_all_result(
			'GALLERY',
			'*',
			array(
				'module_type' => 'SERVICE',
				'module_id'   => $service_id,
			),
			'results'
		);
		if ( ! empty( $gallery_rows ) ) {
			foreach ( $gallery_rows as $gallery_row ) {
				$new_gallery                       = (array) $gallery_row;
				unset( $new_gallery['id'] );
				$new_gallery['module_id']          = $new_service_id;
				$new_gallery['gallery_created_at'] = $now;
				unset( $new_gallery['gallery_updated_at'] );
				$dbhandler->insert_row( 'GALLERY', $new_gallery );
			}
		}

		// Duplicate EXTRA services.
		$extra_rows = $dbhandler->get_all_result( 'EXTRA', '*', array( 'service_id' => $service_id ), 'results' );
		if ( ! empty( $extra_rows ) ) {
			foreach ( $extra_rows as $extra_row ) {
				$new_extra                      = (array) $extra_row;
				unset( $new_extra['id'] );
				$new_extra['service_id']        = $new_service_id;
				$new_extra['extras_created_at'] = $now;
				unset( $new_extra['extras_updated_at'] );
				$dbhandler->insert_row( 'EXTRA', $new_extra );
			}
		}

		// Duplicate shared extras links (SERVICE_GLOBAL_EXTRA junction).
		$sge_links = $dbhandler->get_all_result( 'SERVICE_GLOBAL_EXTRA', '*', array( 'service_id' => $service_id ), 'results' );
		if ( ! empty( $sge_links ) ) {
			foreach ( $sge_links as $sge_link ) {
				$dbhandler->insert_row(
					'SERVICE_GLOBAL_EXTRA',
					array(
						'service_id'      => $new_service_id,
						'global_extra_id' => $sge_link->global_extra_id,
					)
				);
			}
		}

		// Duplicate AVAILABILITY_PERIOD entries.
		$periods = $dbhandler->get_all_result( 'AVAILABILITY_PERIOD', '*', array( 'service_id' => $service_id ), 'results' );
		if ( ! empty( $periods ) ) {
			foreach ( $periods as $period ) {
				$new_period               = (array) $period;
				unset( $new_period['id'] );
				$new_period['service_id'] = $new_service_id;
				$new_period['created_at'] = $now;
				$dbhandler->insert_row( 'AVAILABILITY_PERIOD', $new_period );
			}
		}

		// Duplicate service category mappings.
		$original_cat_ids = $bmrequests->bm_get_service_category_ids( $service_id );
		if ( ! empty( $original_cat_ids ) ) {
			$bmrequests->bm_save_service_categories( $new_service_id, $original_cat_ids );
		}

		$data['status']         = true;
		$data['new_service_id'] = $new_service_id;

		echo wp_json_encode( $data );
		die;
	}//end bm_duplicate_service()
}//end class
