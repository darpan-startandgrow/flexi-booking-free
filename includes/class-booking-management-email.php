<?php
class BM_Email
{


	public function bm_send_notification_to_shop_admin( $subject, $message, $booking_id ) {
		$dbhandler           = new BM_DBhandler();
		$bmrequests          = new BM_Request();
		$from_email_address  = $this->bm_get_from_email();
		$admin_email_address = $this->bm_get_admin_email();
		$headers             = "MIME-Version: 1.0\r\n";
		$headers            .= "Content-type:text/html;charset=UTF-8\r\n";
		$headers            .= "From:$from_email_address\r\n";
		$language            = $dbhandler->get_global_option_value( 'bm_flexi_current_language', 'en' );
		$back_lang           = $dbhandler->get_global_option_value( 'bm_flexi_current_language_backend', '' );
		$language            = ! empty( $back_lang ) ? $back_lang : $language;
		$old_locale          = $bmrequests->bm_switch_locale_by_booking_reference( '', $language );

		$customer_attachment = $dbhandler->get_global_option_value( 'bm_attach_customer_data_with_admin_email_body', 0 );
		$attachment_urls     = array();

		if ( $customer_attachment == 1 ) {
			$attachment_urls[] = $bmrequests->bm_get_customer_details_attachment( $booking_id );
		}

		$attachment_urls[] = $bmrequests->bm_get_order_details_attachment( $booking_id );
		if ( $old_locale ) {
			$bmrequests->bm_restore_locale( $old_locale );
		}

		if ( is_string( $admin_email_address ) ) {
			return wp_mail( maybe_unserialize( $admin_email_address ), $subject, $message, $headers, $attachment_urls );
		} else {
			return wp_mail( $admin_email_address, $subject, $message, $headers, $attachment_urls );
		}
	}//end bm_send_notification_to_shop_admin()


	public function bm_send_email_to_customer( $subject, $message, $booking_id ) {
		$bmrequests         = new BM_Request();
		$from_email_address = $this->bm_get_from_email();
		$dbhandler          = new BM_DBhandler();
		$booking_refrence   = $dbhandler->get_value( 'BOOKING', 'booking_key', $booking_id, 'id' );
		// $old_locale         = $bmrequests->bm_switch_locale_by_booking_reference( $booking_refrence );
		$headers         = "MIME-Version: 1.0\r\n";
		$headers        .= "Content-type:text/html;charset=UTF-8\r\n";
		$headers        .= "From:$from_email_address\r\n";
		$customer_email  = $bmrequests->bm_fetch_customer_email_from_booking_form_data( $booking_id );
		$attachment_urls = array();

		$attachment_urls[] = $bmrequests->bm_get_order_details_attachment( $booking_id, true );

		// if( $old_locale ){
		// $bmrequests->bm_restore_locale( $old_locale );
		// }
		return wp_mail( $customer_email, $subject, $message, $headers, $attachment_urls );
	}//end bm_send_email_to_customer()


	public function bm_send_voucher_email_to_recipient( $subject, $message, $booking_id, $send_voucher_pdf = true ) {
		$bmrequests         = new BM_Request();
		$from_email_address = $this->bm_get_from_email();
		$dbhandler          = new BM_DBhandler();
		$booking_refrence   = $dbhandler->get_value( 'BOOKING', 'booking_key', $booking_id, 'id' );
		$old_locale         = $bmrequests->bm_switch_locale_by_booking_reference( $booking_refrence );

		$headers         = "MIME-Version: 1.0\r\n";
		$headers        .= "Content-type:text/html;charset=UTF-8\r\n";
		$headers        .= "From:$from_email_address\r\n";
		$recipient_email = $bmrequests->bm_fetch_gift_recipient_email_from_booking_form_data( (int) $booking_id );
		$attachment_urls = array();

		if ( $send_voucher_pdf ) {
			$attachment_urls[] = $bmrequests->bm_add_voucher_pdf_to_recipient( $booking_id );
		}
		if ( $old_locale ) {
			$bmrequests->bm_restore_locale( $old_locale );
		}
		return wp_mail( $recipient_email, $subject, $message, $headers, $attachment_urls );
	}//end bm_send_voucher_email_to_recipient()


	public function bm_get_template_email_content( $template_id, $booking_id, $customer = false ) {
		$dbhandler = new BM_DBhandler();
		$language  = $dbhandler->get_global_option_value( 'bm_flexi_current_language', 'en' );
		$back_lang = $dbhandler->get_global_option_value( 'bm_flexi_current_language_backend', '' );
		$language  = ! empty( $back_lang ) ? $back_lang : $language;
		if ( $customer ) {
			$order    = $dbhandler->get_row( 'BOOKING', $booking_id, 'id' );
			$trp_lang = get_option( 'trp_lang_' . $order->booking_key, false );
			$language = ! empty( $trp_lang ) ? $trp_lang : $language;
		}

		$email_body = "email_body_$language";
		$body       = $dbhandler->get_value( 'EMAIL_TMPL', $email_body, $template_id, 'id' );
		$message    = $this->bm_filter_email_content( $body, $booking_id, $customer );
		return $message;
	}//end bm_get_template_email_content()


	public function bm_get_template_email_subject( $template_id, $booking_id, $customer = false ) {
		$dbhandler = new BM_DBhandler();
		$language  = $dbhandler->get_global_option_value( 'bm_flexi_current_language', 'en' );
		$back_lang = $dbhandler->get_global_option_value( 'bm_flexi_current_language_backend', '' );
		$language  = ! empty( $back_lang ) ? $back_lang : $language;
		if ( $customer ) {
			$order    = $dbhandler->get_row( 'BOOKING', $booking_id, 'id' );
			$trp_lang = get_option( 'trp_lang_' . $order->booking_key, false );
			$language = ! empty( $trp_lang ) ? $trp_lang : $language;
		}
		$email_subject = "email_subject_$language";
		$subject       = $dbhandler->get_value( 'EMAIL_TMPL', $email_subject, $template_id, 'id' );
		return $subject;
	}//end bm_get_template_email_subject()


	public function bm_filter_email_content( $message, $booking_id, $customer = false ) {
		$matches = $this->bm_get_inbetween_strings( '{{', '}}', $message );
		$result  = $matches[1];

		if ( ! empty( $result ) ) {
			$bmrequests = new BM_Request();

			foreach ( $result as $field_name ) {
				$search  = '{{' . $field_name . '}}';
				$value   = $bmrequests->bm_replace_field_values_in_email_body( $field_name, $booking_id, $customer );
				$message = str_replace( $search, $value, $message );
			}
		}

		return $message;
	}//end bm_filter_email_content()


	public function bm_get_from_email() {
		$dbhandler     = new BM_DBhandler();
		$email_address = '';

		if ( $dbhandler->get_global_option_value( 'bm_enable_smtp' ) == 1 ) {
			$email_address = $dbhandler->get_global_option_value( 'bm_smtp_from_email_address', get_option( 'admin_email' ) );
		} else {
			$email_address = $dbhandler->get_global_option_value( 'bm_from_email_address', get_option( 'admin_email' ) );
		}

		return $email_address;
	}//end bm_get_from_email()


	public function bm_get_from_name() {
		$dbhandler = new BM_DBhandler();
		$from_name = '';

		if ( $dbhandler->get_global_option_value( 'bm_enable_smtp' ) == 1 ) {
			$from_name = $dbhandler->get_global_option_value( 'bm_smtp_from_email_name', esc_html__( 'Flexi Booking' ) );
		} else {
			$from_name = $dbhandler->get_global_option_value( 'bm_from_email_name', esc_html__( 'Flexi Booking' ) );
		}

		return $from_name;
	}//end bm_get_from_name()


	public function bm_get_inbetween_strings( $start, $end, $str ) {
		$matches = array();
		$regex   = "/$start([a-zA-Z0-9_]*)$end/";
		preg_match_all( $regex, $str, $matches );
		return $matches;
	}//end bm_get_inbetween_strings()


	public function bm_get_admin_email() {
		$dbhandler     = new BM_DBhandler();
		$email_address = array( get_option( 'admin_email' ) );
		$extra_emails  = maybe_unserialize( $dbhandler->get_global_option_value( 'bm_shop_admin_email' ) );

		if ( ! empty( $extra_emails ) && is_array( $extra_emails ) ) {
			$email_address = array_merge( $email_address, $extra_emails );
		}

		$email_address = implode( ',', $email_address );

		return $email_address;
	}//end bm_get_admin_email()


	public function bm_get_admin_username() {
		$username    = 'Unknown';
		$admin_email = get_option( 'admin_email' );
		$admin_user  = get_user_by( 'email', $admin_email );

		if ( $admin_user ) {
			$username = $admin_user->get( 'user_login' );
		}

        return $username;
    } //end bm_get_admin_username()

    public function bm_send_invoice_to_email($subject, $message, $booking_id, $email)
    {
        $bmrequests         = new BM_Request();
        $from_email_address = $this->bm_get_from_email();
        $headers            = "MIME-Version: 1.0\r\n";
        $headers           .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers           .= "From:$from_email_address\r\n";
        $attachment_urls    = array();

        $attachment_urls[] = $bmrequests->bm_get_order_details_attachment($booking_id, true);

        return wp_mail($email, $subject, $message, $headers, $attachment_urls);
    } //end bm_send_invoice_to_email()


}//end class
