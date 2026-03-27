<?php
$identifier    = 'EMAIL_TMPL';
$dbhandler     = new BM_DBhandler();
$bmrequests    = new BM_Request();
$id            = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
$language      = $dbhandler->get_global_option_value( 'bm_flexi_current_language', 'en' );
$back_lang     = $dbhandler->get_global_option_value( 'bm_flexi_current_language_backend', '' );
$language      = ! empty( $back_lang ) ? $back_lang : $language;
$tmpl_name     = "tmpl_name_$language";
$email_subject = "email_subject_$language";
$email_body    = "email_body_$language";

if ( $id == false || $id == null ) {
	$id = 0;
}

if ( ! empty( $id ) ) {
	$template = $dbhandler->get_row( $identifier, $id );
}

// ── Strict limit guard (free version: 9 templates max) ────────────────────
$can_create = Booking_Management_Limits::can_create_mail_template();
if ( empty( $id ) && ! $can_create ) {
	echo '<div class="wrap listing_table">';
	echo '<div class="bm-notice bm-error" style="margin:20px 0;">';
	echo esc_html( Booking_Management_Limits::get_limit_message( 'mail_templates' ) );
	echo '</div>';
	echo '<a href="' . esc_url( admin_url( 'admin.php?page=bm_email_templates' ) ) . '" class="button">&#8592; &nbsp;' . esc_html__( 'Back to Templates', 'service-booking' ) . '</a>';
	echo '</div>';
	return;
}

// Allowed template types for free version.
$allowed_types = Booking_Management_Limits::FREE_MAIL_TEMPLATE_TYPES;

$email_content = array(
	'wpautop'           => false,
	'media_buttons'     => true,
	'textarea_name'     => $email_body,
	'textarea_rows'     => 20,
	'tabindex'          => 4,
	'editor_height'     => 200,
	'tabfocus_elements' => ':prev,:next',
	'editor_css'        => '',
	'editor_class'      => '',
	'teeny'             => false,
	'dfw'               => false,
	'tinymce'           => true,
	'quicktags'         => true,
);

add_action( 'media_buttons', array( $this, 'bm_fields_list_for_email' ) );

if ( ( filter_input( INPUT_POST, 'savetemplate' ) ) ) {
	$retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
	if ( ! wp_verify_nonce( $retrieved_nonce, 'save_template_section' ) ) {
		die( '<div id="errorMessage" class="bm-notice bm-error">' . esc_html__( 'Failed security check', 'service-booking' ) . '</div>' );
	}

	$exclude = array(
		'_wpnonce',
		'_wp_http_referer',
		'savetemplate',
		'bm_field_list',
	);

	if ( ( filter_input( INPUT_POST, 'savetemplate' ) ) ) {
		$tmpl_data = $bmrequests->sanitize_request( $_POST, $identifier, $exclude );

		if ( $tmpl_data != false ) {
			$current_type   = isset( $tmpl_data['type'] ) ? (int) $tmpl_data['type'] : -1;
			$current_status = isset( $tmpl_data['status'] ) ? $tmpl_data['status'] : -1;

			// ── Enforce type restriction for free version ──────────────
			if ( ! in_array( $current_type, $allowed_types, true ) ) {
				echo '<div id="errorMessage" class="bm-notice bm-error">';
				echo esc_html__( 'This template type is not available in the free version.', 'service-booking' );
				echo '</div>';
			} else {

			$active_type    = $bmrequests->bm_check_active_email_template_of_a_specific_type( $current_type );

			if ( ! empty( $id ) ) {
				$active_template_id = $bmrequests->bm_fetch_active_email_template_id_of_a_specific_type( $current_type );

				if ( ( $current_status == 1 ) && $active_type && ( $active_template_id > 0 ) && ( $active_template_id != $id ) ) {
					echo ( '<div id="errorMessage" class="bm-notice bm-error">' );
					echo esc_html__( 'There is already an active template for this type, please deactivate the existing template.', 'service-booking' );
					echo ( '</div>' );
				} else {
					$tmpl_data['template_updated_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();

					$updated = $dbhandler->update_row( $identifier, 'id', $id, $tmpl_data, '', '%d' );

					if ( $updated ) {
						wp_safe_redirect( esc_url_raw( 'admin.php?page=bm_add_template&id=' . esc_attr( $id ) ) );
						exit;
					} else {
						echo ( '<div id="errorMessage" class="bm-notice bm-error">' );
						echo esc_html__( 'Template Could not be Updated !!', 'service-booking' );
						echo ( '</div>' );
					}
				}
			} elseif ( ! Booking_Management_Limits::can_create_mail_template( $current_type ) ) {
					// ── Double-check limit on server side before insert ──
					echo '<div id="errorMessage" class="bm-notice bm-error">';
					echo esc_html( Booking_Management_Limits::get_limit_message( 'mail_templates' ) );
					echo '</div>';
			} elseif ( $current_status == 1 && $active_type ) {
					echo ( '<div id="errorMessage" class="bm-notice bm-error">' );
					echo esc_html__( 'There is already an active template for this type, please deactivate the existing template.', 'service-booking' );
					echo ( '</div>' );
			} else {
				$tmpl_data['template_created_at'] = $bmrequests->bm_fetch_current_wordpress_datetime_stamp();

				$id = $dbhandler->insert_row( $identifier, $tmpl_data );

				if ( ! empty( $id ) ) {
					if ( $dbhandler->get_global_option_value( 'bm_email_templates_created', '0' ) == '0' ) {
						$dbhandler->update_global_option_value( 'bm_email_templates_created', '1' );
					}
					wp_safe_redirect( esc_url_raw( 'admin.php?page=bm_email_templates' ) );
					exit;
				} else {
					echo ( '<div id="errorMessage" class="bm-notice bm-error">' );
					echo esc_html__( 'Template Could not be Added !!', 'service-booking' );
					echo ( '</div>' );
				}
			}

			} // end type restriction check.
		} else {
			echo ( '<div id="errorMessage" class="bm-notice bm-error">' );
			echo esc_html__( 'Template Data could not be Processed !!', 'service-booking' );
			echo ( '</div>' );
		}
	}
}//end if

// Build the type label map (only free-allowed types).
$type_labels = array(
	0  => __( 'New order (frontend)', 'service-booking' ),
	3  => __( 'Order cancel', 'service-booking' ),
	5  => __( 'Admin new order notification', 'service-booking' ),
	6  => __( 'Admin order cancel notification', 'service-booking' ),
	9  => __( 'Failed Order', 'service-booking' ),
	10 => __( 'Failed order admin notification', 'service-booking' ),
	11 => __( 'Gift voucher notification', 'service-booking' ),
	15 => __( 'Redeem voucher admin notification', 'service-booking' ),
	16 => __( 'Redeem voucher notification', 'service-booking' ),
);

?>


<div class="wrap listing_table">
	<div class="row">
		<p>
		<h2 class="title" style="font-weight: bold;"><?php echo empty( $id ) ? esc_html__( 'Add Template', 'service-booking' ) : esc_html__( 'Edit Template', 'service-booking' ); ?></h2>
		</p>
	</div>
	
	<form role="form" method="post">
		<tbody>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="status"><?php esc_html_e( 'Status', 'service-booking' ); ?></label><strong class="required_asterisk"> *</strong></th>
					<td  class="bminput bm_required">
						<select name="status" id="status" class="regular-text">
							<option value="1" <?php isset( $template ) && isset( $template->status ) ? selected( $template->status, 1 ) : ''; ?>><?php esc_html_e( 'Active', 'service-booking' ); ?></option>
							<option value="0" <?php isset( $template ) && isset( $template->status ) ? selected( $template->status, 0 ) : ''; ?>><?php esc_html_e( 'Inactive', 'service-booking' ); ?></option>
						</select>
						<span> <?php esc_html_e( 'Status of this template', 'service-booking' ); ?></span>
						<div class="errortext"></div>
					</td>
					
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_html( $tmpl_name ); ?>"><?php esc_html_e( 'Template Name', 'service-booking' ); ?></label><strong class="required_asterisk"> *</strong></th>
					<td class="bminput bm_tmpl_required">
						<input name="<?php echo esc_html( $tmpl_name ); ?>" type="text" id="<?php echo esc_html( $tmpl_name ); ?>" placeholder="<?php esc_html_e( 'template name', 'service-booking' ); ?>" class="regular-text" value="<?php echo isset( $template ) && ! empty( $template->$tmpl_name ) ? esc_html( $template->$tmpl_name ) : ''; ?>" autocomplete="off">
						<div class="tmpl_errortext"></div>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="type"><?php esc_html_e( 'Template Type', 'service-booking' ); ?></label><strong class="required_asterisk"> *</strong></th>
					<td class="bminput bm_tmpl_required">
						<select name="type" id="type" class="regular-text">
							<?php foreach ( $type_labels as $type_id => $type_label ) : ?>
								<option value="<?php echo esc_attr( $type_id ); ?>" <?php isset( $template ) && isset( $template->type ) ? selected( (int) $template->type, $type_id ) : ''; ?>><?php echo esc_html( $type_label ); ?></option>
							<?php endforeach; ?>
						</select>
						<div class="tmpl_errortext"></div>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_html( $email_subject ); ?>"><?php esc_html_e( 'Email Subject', 'service-booking' ); ?></label><strong class="required_asterisk"> *</strong></th>
					<td class="bminput bm_tmpl_required">
						<input name="<?php echo esc_html( $email_subject ); ?>" type="text" placeholder="<?php esc_html_e( 'email subject', 'service-booking' ); ?>" id="<?php echo esc_html( $email_subject ); ?>" class="regular-text" value="<?php echo isset( $template ) && isset( $template->$email_subject ) ? esc_html( $template->$email_subject ) : ''; ?>" autocomplete="off">
						<div class="tmpl_errortext"></div>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_html( $email_body ); ?>"><?php esc_html_e( 'Email Body', 'service-booking' ); ?></label><strong class="required_asterisk"> *</strong></th>
					<td id="email_body_td" class="bminput">
						<div style="width: 54%;" class="sg-rg-buttom">
							<?php isset( $template ) && isset( $template->$email_body ) ? wp_editor( $template->$email_body, $email_body, $email_content ) : wp_editor( '', $email_body, $email_content ); ?>
							<div class="tmpl_errortext"></div>
						</div>
					</td>
				</tr>
			</table>
			<div class="row">
				<p class="submit">
					<?php wp_nonce_field( 'save_template_section' ); ?>
					<a href="admin.php?page=bm_email_templates" class="button">&#8592; &nbsp;<?php esc_attr_e( 'Back', 'service-booking' ); ?></a>
					<input type="submit" name="savetemplate" id="savetemplate" class="button button-primary" value="<?php empty( $id ) ? esc_attr_e( 'Save', 'service-booking' ) : esc_attr_e( 'Update', 'service-booking' ); ?>" onclick="return add_template_validation()">
				<div class="all_global_general_error_text" style="display:none;"></div>
				</p>
			</div>
		</tbody>
	</form>
</div>

<div class="loader_modal"></div>

