<?php
$dbhandler = new BM_DBhandler();
$path      = plugin_dir_url( __FILE__ );
$fields    = array(
    'text',
    'email',
    'url',
    'password',
    'select',
    'radio',
    'textarea',
    'checkbox',
    'date',
    'time',
    'datetime',
    'month',
    'week',
    'number',
    'file',
    'button',
    'submit',
    'tel',
    'hidden',
    'color',
    'range',
    'reset',
    'search',
);
?>

<div class="sg-admin-main-box fields_listing_screen">
<!-- Services Listing-->
<div class="wrap" id="user_form" style="display:flex;">
    <div style="flex:1;margin-right :20px" id="field_section">
        <h2 class="title" style="font-weight: bold;text-align: center;margin-bottom: 20px;"><?php esc_html_e( 'Default Billing Form', 'service-booking' ); ?></h2>
        <p style="text-align: center; color: #666; margin-bottom: 20px;"><?php esc_html_e( 'Edit field labels, placeholders, visibility, and required status. Fields cannot be added or deleted in the free version.', 'service-booking' ); ?></p>
        <div class="field_tab">
            <button class="field_tablinks active" id="listing_button" onclick="fieldTabs(event, 'field_listing')"><?php esc_html_e( 'Fields', 'service-booking' ); ?></button>
            <button class="field_tablinks" id="settings_button" onclick="fieldTabs(event, 'field_settings')"><?php esc_html_e( 'Settings', 'service-booking' ); ?></button>
        </div>

        <!-- Tab content -->
        <div id="field_listing" class="field_tabcontent">
            <div class="sg-pro-upsell-notice" style="padding: 12px; background: #fff8e1; border-left: 4px solid #ffb300; border-radius: 3px; margin-bottom: 15px;">
                <p style="margin: 0;">
                    <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
                    <strong><?php esc_html_e( 'Add Custom Fields', 'service-booking' ); ?></strong>
                    <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span><br/>
                    <small><?php esc_html_e( 'Upgrade to Pro to add custom fields, drag-and-drop reordering, conditional fields, multi-step forms, and more.', 'service-booking' ); ?></small>
                    <a href="<?php echo esc_url( Booking_Management_Limits::get_pro_upsell_url() ); ?>" target="_blank" style="font-size: 12px;"><?php esc_html_e( 'Upgrade to Pro →', 'service-booking' ); ?></a>
                </p>
            </div>
        </div>

        <div id="field_settings" class="field_tabcontent">
            <p style="text-align: center;"><?php esc_html_e( 'Select a field first', 'service-booking' ); ?></p>
        </div>
    </div>
    <div style="flex:1.5;" id="content_section">
        <span class="title_and_preview">
            <h2 class="title" style="font-weight: bold;text-align: center;margin-bottom: 40px;"><?php esc_html_e( 'Content', 'service-booking' ); ?></h2>
            <button type="button" class="preview_button"><span><?php esc_html_e( 'Preview', 'service-booking' ); ?></span></button>
        </span>
        <div class="content_body"></div>
        <div class="field_successtext" style="display: none;"></div>
        <div class="field_errortext" style="display: none;"></div>
    </div>
</div>

<div id="primary_email_modal" class="modaloverlay">
    <div class="modal primary_mail_custom_class">
        <span class="close">&times;</span>
        <h4 style="font-size:16px; margin-left:10px;"><?php esc_html_e( 'Select Primary Email', 'service-booking' ); ?></h4>
        <div class="modalcontentbox modal-body" id="active_emails_details"></div>
    </div>
</div>

<div id="preview_form_modal" class="modaloverlay">
    <div class="modal animate__animated animate__fadeInDown">
        <span class="close" onclick="closeModal('preview_form_modal')">&times;</span>
        <h2 style="background:#5EA8ED ; margin:0px; padding:12px;color:#fff;font-size:18px;text-align: center;"><?php esc_html_e( 'Preview', 'service-booking' ); ?></h2>
        <div class="modalcontentbox2 modal-body" id="preview_form"></div>
    </div>
</div>

<div class="popup-message-overlay" id="popup-message-overlay"></div>
<div class="popup-message-container animate__animated animate__shakeY" id="popup-message-container">
    <span id="popup-message"></span>
    <button class="close-popup-message" id="close-popup-message" title="<?php esc_html_e( 'Close', 'service-booking' ); ?>"><?php echo esc_html( '✕' ); ?></button>
</div>

<div class="loader_modal"></div>
</div>

