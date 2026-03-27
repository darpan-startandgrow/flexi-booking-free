<?php
$dbhandler    = new BM_DBhandler();
$path         = plugin_dir_url( __FILE__ );
$fields_table = new BM_Fields_List_Table();
$fields_table->prepare_items();

$total_fields   = (int) $dbhandler->bm_count( 'FIELDS' );
$default_fields = Booking_Management_Limits::FREE_DEFAULT_FIELD_NAMES;
$default_count  = count( $default_fields );
?>

<!-- Fields Listing -->
<div class="wrap" id="user_form" style="display:flex; gap:20px;">
    <div style="flex:1;" id="field_section">
        <div class="field_tab">
            <button class="field_tablinks active" id="listing_button" onclick="fieldTabs(event, 'field_listing')"><?php esc_html_e( 'Fields', 'service-booking' ); ?></button>
            <button class="field_tablinks" id="settings_button" onclick="fieldTabs(event, 'field_settings')"><?php esc_html_e( 'Settings', 'service-booking' ); ?></button>
        </div>

        <!-- Tab: Fields listing -->
        <div id="field_listing" class="field_tabcontent">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
                <div>
                    <h2 class="title" style="font-weight: bold; margin:0;"><?php esc_html_e( 'Default Billing Form', 'service-booking' ); ?></h2>
                    <p class="description" style="margin:4px 0 0;">
                        <?php
                        printf(
                            /* translators: %d: total field count */
                            esc_html__( '%d fields', 'service-booking' ),
                            $total_fields
                        );
                        ?>
                        &mdash; <?php esc_html_e( 'Edit labels, placeholders, visibility, and required status.', 'service-booking' ); ?>
                    </p>
                </div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <button type="button" class="preview_button button" style="display:inline-flex;align-items:center;gap:4px;">
                        <span class="dashicons dashicons-visibility" style="font-size:16px;width:16px;height:16px;"></span>
                        <?php esc_html_e( 'Preview', 'service-booking' ); ?>
                    </button>
                    <button class="button" disabled aria-disabled="true" title="<?php echo esc_attr( Booking_Management_Limits::get_limit_message( 'custom_fields' ) ); ?>" style="opacity:0.65;cursor:not-allowed;">
                        <?php esc_html_e( 'Add Field', 'service-booking' ); ?>&nbsp;<span class="sg-pro-badge"><?php esc_html_e( 'Pro', 'service-booking' ); ?></span>
                    </button>
                    <button class="button" disabled aria-disabled="true" title="<?php esc_html_e( 'Creating new forms is a Pro feature.', 'service-booking' ); ?>" style="opacity:0.65;cursor:not-allowed;">
                        <?php esc_html_e( 'New Form', 'service-booking' ); ?>&nbsp;<span class="sg-pro-badge"><?php esc_html_e( 'Pro', 'service-booking' ); ?></span>
                    </button>
                </div>
            </div>

            <div style="margin-bottom:15px; padding:12px 16px; background:#fff8e1; border-left:4px solid #ffb300; border-radius:3px;">
                <p style="margin:0;">
                    <span class="dashicons dashicons-lock" style="color:#ffb300;"></span>
                    <strong><?php esc_html_e( 'Custom Fields & Multi-Step Forms', 'service-booking' ); ?></strong>
                    <span class="sg-pro-badge"><?php esc_html_e( 'Pro', 'service-booking' ); ?></span><br/>
                    <small><?php esc_html_e( 'Upgrade to Pro to add custom fields, drag-and-drop reordering, conditional fields, multi-step forms, and more.', 'service-booking' ); ?></small>
                    <a href="<?php echo esc_url( Booking_Management_Limits::get_pro_upsell_url() ); ?>" target="_blank" style="font-size: 12px;"><?php esc_html_e( 'Upgrade to Pro →', 'service-booking' ); ?></a>
                </p>
            </div>

            <?php $fields_table->display(); ?>
        </div>

        <!-- Tab: Field settings (populated by JS when editing) -->
        <div id="field_settings" class="field_tabcontent">
            <p style="text-align: center;"><?php esc_html_e( 'Select a field first', 'service-booking' ); ?></p>
        </div>
    </div>

    <!-- Right panel: legacy JS field cards + edit form -->
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

<!-- Primary Email Modal -->
<div id="primary_email_modal" class="modaloverlay">
    <div class="modal primary_mail_custom_class">
        <span class="close">&times;</span>
        <h4 style="font-size:16px; margin-left:10px;"><?php esc_html_e( 'Select Primary Email', 'service-booking' ); ?></h4>
        <div class="modalcontentbox modal-body" id="active_emails_details"></div>
    </div>
</div>

<!-- Preview Form Modal -->
<div id="preview_form_modal" class="modaloverlay">
    <div class="modal animate__animated animate__fadeInDown">
        <span class="close" onclick="closeModal('preview_form_modal')">&times;</span>
        <h2 style="background:#5EA8ED ; margin:0px; padding:12px;color:#fff;font-size:18px;text-align: center;"><?php esc_html_e( 'Preview', 'service-booking' ); ?></h2>
        <div class="modalcontentbox2 modal-body" id="preview_form"></div>
    </div>
</div>

<!-- Popup Messages -->
<div class="popup-message-overlay" id="popup-message-overlay"></div>
<div class="popup-message-container animate__animated animate__shakeY" id="popup-message-container">
    <span id="popup-message"></span>
    <button class="close-popup-message" id="close-popup-message" title="<?php esc_html_e( 'Close', 'service-booking' ); ?>"><?php echo esc_html( '✕' ); ?></button>
</div>

<div class="loader_modal"></div>

