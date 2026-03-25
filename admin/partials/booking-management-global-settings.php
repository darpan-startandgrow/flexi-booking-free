<?php
if ( ! current_user_can( 'manage_options' ) ) {
    die( esc_html__( 'Forbidden', 'service-booking' ) );
}

$plugin_path = plugin_dir_url( __FILE__ );
$is_pro      = Booking_Management_Limits::is_pro_active();
?>

<div class="sg-admin-main-box">
    <div class="bm-setting-wrapper" style="float: none; margin: 0px; padding: 0px;">
    <div class="content bm_settings_option listing_table">
        <div>
            <h2 class="title" style="font-weight: bold;"> <?php esc_html_e( 'Global Settings', 'service-booking' ); ?></h2>
        </div>

        <div class="bm-setting-wrap">

            <!-- Allowed in Free: Service Shortcode Content Settings -->
            <div class="settings_row">
                <a href="admin.php?page=bm_global_general_settings">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/general.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title"><?php esc_html_e( 'Service Shortcode Content Settings', 'service-booking' ); ?></span>
                        <span class="bm-setting-description"><?php esc_html_e( 'Grid/List view, Service contents in the frontend etc.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Allowed in Free: Mail Settings (SMTP gated inside) -->
            <div class="settings_row">
                <a href="admin.php?page=bm_global_email_settings">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/mail.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title"><?php esc_html_e( 'Mail Settings', 'service-booking' ); ?></span>
                        <span class="bm-setting-description"><?php esc_html_e( 'Admin mail notification settings.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Allowed in Free: Time Zone and Country Settings -->
            <div class="settings_row">
                <a href="admin.php?page=bm_global_timezone_country_settings">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/general.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title"><?php esc_html_e( 'Time Zone and Country Settings', 'service-booking' ); ?></span>
                        <span class="bm-setting-description"><?php esc_html_e( 'time zone and country settings etc.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Allowed in Free: Image Upload Settings -->
            <div class="settings_row">
                <a href="admin.php?page=bm_upload_settings">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/upload.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title"><?php esc_html_e( 'Image Upload Settings', 'service-booking' ); ?></span>
                        <span class="bm-setting-description"><?php esc_html_e( 'image width, height, size, quality etc.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Allowed in Free: Language Settings -->
            <div class="settings_row">
                <a href="admin.php?page=bm_global_language_settings">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/general.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title"><?php esc_html_e( 'Language Settings', 'service-booking' ); ?></span>
                        <span class="bm-setting-description"><?php esc_html_e( 'language preference, switcher visibility etc.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <?php if ( $is_pro ) : ?>

            <!-- Pro only: Payment Settings -->
            <div class="settings_row">
                <a href="admin.php?page=bm_global_payment_settings">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/payment.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title"><?php esc_html_e( 'Payment Settings', 'service-booking' ); ?></span>
                        <span class="bm-setting-description"><?php esc_html_e( 'Currency, Symbol Position etc.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Pro only: Service/booking Settings -->
            <div class="settings_row">
                <a href="admin.php?page=bm_svc_booking_settings">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/stopsales.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title"><?php esc_html_e( 'Service/booking Settings', 'service-booking' ); ?></span>
                        <span class="bm-setting-description"><?php esc_html_e( 'Stopsales, Saleswicth, book on request expiry time etc.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Pro only: CSS Settings -->
            <div class="settings_row">
                <a href="admin.php?page=bm_global_css_settings">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/general.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title"><?php esc_html_e( 'Service Shortcode CSS Settings', 'service-booking' ); ?></span>
                        <span class="bm-setting-description"><?php esc_html_e( 'Font and colour settings etc.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Pro only: Format Settings -->
            <div class="settings_row">
                <a href="admin.php?page=bm_global_format_settings">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/general.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title"><?php esc_html_e( 'Format Settings', 'service-booking' ); ?></span>
                        <span class="bm-setting-description"><?php esc_html_e( 'time, price format etc.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Pro only: Integration Settings -->
            <div class="settings_row">
                <a href="admin.php?page=bm_global_integration_settings">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/general.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title"><?php esc_html_e( 'Integration Settings', 'service-booking' ); ?></span>
                        <span class="bm-setting-description"><?php esc_html_e( '3rd party and Service level integrations.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Pro only: Coupon Settings -->
            <div class="settings_row">
                <a href="admin.php?page=bm_global_coupon_settings">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/general.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title"><?php esc_html_e( 'Coupon Settings', 'service-booking' ); ?></span>
                        <span class="bm-setting-description"><?php esc_html_e( 'Coupon related global settings.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <?php else : ?>

            <!-- Pro teaser: Payment Settings -->
            <div class="settings_row bm-setting-disabled" style="opacity: 0.6; pointer-events: none; position: relative;">
                <a href="javascript:void(0);">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/payment.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title">
                            <?php esc_html_e( 'Payment Settings', 'service-booking' ); ?>
                            <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
                            <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span>
                        </span>
                        <span class="bm-setting-description"><?php esc_html_e( 'Currency, Symbol Position etc.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Pro teaser: Service/booking Settings -->
            <div class="settings_row bm-setting-disabled" style="opacity: 0.6; pointer-events: none; position: relative;">
                <a href="javascript:void(0);">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/stopsales.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title">
                            <?php esc_html_e( 'Service/booking Settings', 'service-booking' ); ?>
                            <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
                            <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span>
                        </span>
                        <span class="bm-setting-description"><?php esc_html_e( 'Stopsales, Saleswicth, book on request expiry time etc.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Pro teaser: CSS Settings -->
            <div class="settings_row bm-setting-disabled" style="opacity: 0.6; pointer-events: none; position: relative;">
                <a href="javascript:void(0);">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/general.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title">
                            <?php esc_html_e( 'Service Shortcode CSS Settings', 'service-booking' ); ?>
                            <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
                            <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span>
                        </span>
                        <span class="bm-setting-description"><?php esc_html_e( 'Font and colour settings etc.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Pro teaser: Format Settings -->
            <div class="settings_row bm-setting-disabled" style="opacity: 0.6; pointer-events: none; position: relative;">
                <a href="javascript:void(0);">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/general.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title">
                            <?php esc_html_e( 'Format Settings', 'service-booking' ); ?>
                            <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
                            <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span>
                        </span>
                        <span class="bm-setting-description"><?php esc_html_e( 'time, price format etc.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Pro teaser: Integration Settings -->
            <div class="settings_row bm-setting-disabled" style="opacity: 0.6; pointer-events: none; position: relative;">
                <a href="javascript:void(0);">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/general.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title">
                            <?php esc_html_e( 'Integration Settings', 'service-booking' ); ?>
                            <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
                            <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span>
                        </span>
                        <span class="bm-setting-description"><?php esc_html_e( '3rd party and Service level integrations.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <!-- Pro teaser: Coupon Settings -->
            <div class="settings_row bm-setting-disabled" style="opacity: 0.6; pointer-events: none; position: relative;">
                <a href="javascript:void(0);">
                    <div class="bm_setting_image">
                        <img src="<?php echo esc_url( $plugin_path . 'images/general.png' ); ?>" class="options" alt="options">
                    </div>
                    <div class="bm-setting-heading">
                        <span class="bm-setting-icon-title">
                            <?php esc_html_e( 'Coupon Settings', 'service-booking' ); ?>
                            <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
                            <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span>
                        </span>
                        <span class="bm-setting-description"><?php esc_html_e( 'Coupon related global settings.', 'service-booking' ); ?></span>
                    </div>
                </a>
            </div>

            <?php endif; ?>

            <?php do_action('flexi_booking_global_setting_list');?>
        </div>
    </div>
</div>

<div class="loader_modal"></div>
</div>

