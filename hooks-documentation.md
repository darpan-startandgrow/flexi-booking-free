# FlexiBooking Hooks Documentation

This document lists all available WordPress action hooks and filter hooks provided by the **SG Flexi Booking** plugin. Developers can use these hooks to extend, customise, and integrate with the plugin.

---

## Table of Contents

1. [Plugin Lifecycle Hooks](#plugin-lifecycle-hooks)
2. [Email Hooks](#email-hooks)
3. [Database Hooks](#database-hooks)
4. [REST API Hooks](#rest-api-hooks)
5. [Booking Lifecycle Hooks](#booking-lifecycle-hooks)
6. [Service Management Hooks](#service-management-hooks)
7. [Category Management Hooks](#category-management-hooks)
8. [Email Template Hooks](#email-template-hooks)
9. [Notification Process Hooks](#notification-process-hooks)
10. [Transaction Hooks](#transaction-hooks)
11. [Admin Menu & UI Hooks](#admin-menu--ui-hooks)
12. [Internationalisation Hooks](#internationalisation-hooks)
13. [WooCommerce Integration Hooks](#woocommerce-integration-hooks)

---

## Plugin Lifecycle Hooks

### `sg_booking_activated` *(action)*

Fires after plugin activation and database table creation.

| Parameter | Type | Description |
|-----------|------|-------------|
| *(none)* | — | — |

```php
add_action( 'sg_booking_activated', function () {
    // Seed default data or create custom tables.
    update_option( 'my_addon_version', '1.0.0' );
} );
```

### `sg_booking_load_pro_libraries` *(action)*

Fires before core dependencies are fully loaded. Load additional class files here.

| Parameter | Type | Description |
|-----------|------|-------------|
| *(none)* | — | — |

```php
add_action( 'sg_booking_load_pro_libraries', function () {
    require_once __DIR__ . '/my-custom-class.php';
} );
```

### `sg_booking_dependencies_loaded` *(action)*

Fires after the Lite plugin has loaded all its core dependencies.

| Parameter | Type | Description |
|-----------|------|-------------|
| *(none)* | — | — |

```php
add_action( 'sg_booking_dependencies_loaded', function () {
    // All core classes are now available.
} );
```

### `sg_booking_init_pro_connections` *(action)*

Fires during constructor — SMTP and Stripe initialisation point.

| Parameter | Type | Description |
|-----------|------|-------------|
| *(none)* | — | — |

### `sg_booking_is_pro_active` *(filter)*

Central gatekeeper filter for Pro feature detection.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$is_pro` | `bool` | Whether Pro is active. Default `false`. |

**Return:** `bool`

```php
add_filter( 'sg_booking_is_pro_active', '__return_true' );
```

### `sg_booking_register_admin_hooks` *(action)*

Fires after all Lite admin hooks are registered. Register custom AJAX handlers or filters.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$loader` | `Booking_Management_Loader` | The hook loader instance. |
| `$plugin_admin` | `Booking_Management_Admin` | The admin class instance. |

```php
add_action( 'sg_booking_register_admin_hooks', function ( $loader, $plugin_admin ) {
    $loader->add_action( 'wp_ajax_my_custom_action', $plugin_admin, 'my_callback' );
}, 10, 2 );
```

### `sg_booking_register_pro_public_hooks` *(action)*

Fires after the Lite public hooks are registered. The Pro add-on registers coupon, voucher, Stripe, QR check-in, and PDF hooks here.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$loader` | `Booking_Management_Loader` | The hook loader instance. |
| `$plugin_public` | `Booking_Management_Public` | The public class instance. |

### `sg_booking_register_pro_menus` *(action)*

Fires after admin menus are registered. Replace upsell callbacks with real Pro pages.

| Parameter | Type | Description |
|-----------|------|-------------|
| *(none)* | — | — |

---

## Email Hooks

### `sg_booking_admin_email_subject` *(filter)*

Filters the admin notification email subject.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$subject` | `string` | The email subject. |
| `$booking_id` | `int` | The booking ID. |

**Return:** `string`

```php
add_filter( 'sg_booking_admin_email_subject', function ( $subject, $booking_id ) {
    return '[MyBrand] ' . $subject;
}, 10, 2 );
```

### `sg_booking_admin_email_content` *(filter)*

Filters the admin notification email body.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | `string` | The email HTML body. |
| `$booking_id` | `int` | The booking ID. |

**Return:** `string`

### `sg_booking_admin_email_headers` *(filter)*

Filters the admin notification email headers.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$headers` | `string` | The email headers string. |
| `$booking_id` | `int` | The booking ID. |

**Return:** `string`

```php
add_filter( 'sg_booking_admin_email_headers', function ( $headers, $booking_id ) {
    $headers .= "Reply-To: support@example.com\r\n";
    return $headers;
}, 10, 2 );
```

### `sg_booking_admin_email_attachments` *(filter)*

Filters the admin notification email attachments.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$attachment_urls` | `array` | Array of attachment file paths. |
| `$booking_id` | `int` | The booking ID. |

**Return:** `array`

### `sg_booking_before_admin_email` *(action)*

Fires before the admin notification email is sent.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$admin_email` | `string` | The recipient email address. |
| `$subject` | `string` | The email subject. |
| `$message` | `string` | The email body. |
| `$booking_id` | `int` | The booking ID. |

### `sg_booking_after_admin_email` *(action)*

Fires after the admin notification email is sent.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$result` | `bool` | Whether `wp_mail()` succeeded. |
| `$subject` | `string` | The email subject. |
| `$booking_id` | `int` | The booking ID. |

```php
add_action( 'sg_booking_after_admin_email', function ( $result, $subject, $booking_id ) {
    if ( ! $result ) {
        error_log( "FlexiBooking: Failed to send admin email for booking #{$booking_id}" );
    }
}, 10, 3 );
```

### `sg_booking_customer_email_subject` *(filter)*

Filters the customer email subject.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$subject` | `string` | The email subject. |
| `$booking_id` | `int` | The booking ID. |

**Return:** `string`

### `sg_booking_customer_email_content` *(filter)*

Filters the customer email body.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | `string` | The email HTML body. |
| `$booking_id` | `int` | The booking ID. |

**Return:** `string`

### `sg_booking_customer_email_headers` *(filter)*

Filters the customer email headers.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$headers` | `string` | The email headers string. |
| `$booking_id` | `int` | The booking ID. |

**Return:** `string`

### `sg_booking_customer_email_attachments` *(filter)*

Filters the customer email attachments.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$attachment_urls` | `array` | Array of attachment file paths. |
| `$booking_id` | `int` | The booking ID. |

**Return:** `array`

### `sg_booking_before_customer_email` *(action)*

Fires before the customer email is sent.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$customer_email` | `string` | The customer email address. |
| `$subject` | `string` | The email subject. |
| `$message` | `string` | The email body. |
| `$booking_id` | `int` | The booking ID. |

### `sg_booking_after_customer_email` *(action)*

Fires after the customer email is sent.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$result` | `bool` | Whether `wp_mail()` succeeded. |
| `$subject` | `string` | The email subject. |
| `$booking_id` | `int` | The booking ID. |

### `sg_booking_email_content_filtered` *(filter)*

Filters the final email content after all `{{placeholder}}` replacements.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | `string` | The processed email body. |
| `$booking_id` | `int` | The booking ID. |
| `$customer` | `bool` | Whether this is a customer email. |

**Return:** `string`

```php
add_filter( 'sg_booking_email_content_filtered', function ( $message, $booking_id, $customer ) {
    // Add a custom footer to all emails.
    $message .= '<p style="color:#888;">Powered by MyPlugin</p>';
    return $message;
}, 10, 3 );
```

---

## Database Hooks

### `sg_booking_before_insert` *(filter)*

Filters the data before inserting a row into any plugin table.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | The row data to insert. |
| `$identifier` | `string` | The table identifier (e.g. `'SERVICE'`, `'BOOKING'`). |
| `$format` | `array\|null` | The data format array. |

**Return:** `array`

```php
add_filter( 'sg_booking_before_insert', function ( $data, $identifier, $format ) {
    if ( $identifier === 'BOOKING' ) {
        $data['custom_field'] = 'custom_value';
    }
    return $data;
}, 10, 3 );
```

### `sg_booking_after_insert` *(action)*

Fires after a row is successfully inserted.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$insert_id` | `int` | The new row ID. |
| `$identifier` | `string` | The table identifier. |
| `$data` | `array` | The inserted data. |

```php
add_action( 'sg_booking_after_insert', function ( $insert_id, $identifier, $data ) {
    if ( $identifier === 'SERVICE' ) {
        // Sync service to external CRM.
        my_crm_sync( $insert_id, $data );
    }
}, 10, 3 );
```

### `sg_booking_before_update` *(filter)*

Filters the data before updating a row.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | The data to update. |
| `$identifier` | `string` | The table identifier. |
| `$unique_field_value` | `mixed` | The row's unique field value. |

**Return:** `array`

### `sg_booking_after_update` *(action)*

Fires after a row is updated.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$identifier` | `string` | The table identifier. |
| `$unique_field_value` | `mixed` | The row's unique field value. |
| `$data` | `array` | The updated data. |

### `sg_booking_before_delete` *(action)*

Fires before a row is deleted.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$identifier` | `string` | The table identifier. |
| `$unique_field_value` | `mixed` | The row's unique field value. |
| `$result` | `object` | The row data before deletion. |

```php
add_action( 'sg_booking_before_delete', function ( $identifier, $unique_field_value, $result ) {
    if ( $identifier === 'SERVICE' ) {
        // Archive service data before deletion.
        my_archive_service( $unique_field_value, $result );
    }
}, 10, 3 );
```

### `sg_booking_after_delete` *(action)*

Fires after a row is deleted.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$identifier` | `string` | The table identifier. |
| `$unique_field_value` | `mixed` | The row's unique field value. |
| `$result` | `object` | The row data that was deleted. |

---

## REST API Hooks

### `sg_booking_rest_routes_registered` *(action)*

Fires after all Lite REST routes are registered. Use to add custom endpoints.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$namespace` | `string` | The REST namespace (`'sg-booking/v1'`). |

```php
add_action( 'sg_booking_rest_routes_registered', function ( $namespace ) {
    register_rest_route( $namespace, '/my-endpoint', array(
        'methods'             => 'GET',
        'callback'            => 'my_custom_callback',
        'permission_callback' => '__return_true',
    ) );
} );
```

### `sg_booking_rest_timeslots` *(filter)*

Filters the timeslots response before returning to the client.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$timeslots` | `array` | The timeslot data array. |
| `$service_id` | `int` | The service ID. |
| `$booking_date` | `string` | The booking date (YYYY-MM-DD). |

**Return:** `array`

```php
add_filter( 'sg_booking_rest_timeslots', function ( $timeslots, $service_id, $booking_date ) {
    // Add custom availability info to each timeslot.
    foreach ( $timeslots as &$slot ) {
        $slot['custom_label'] = 'Available';
    }
    return $timeslots;
}, 10, 3 );
```

### `sg_booking_before_save` *(filter)*

Filters the booking data before it is saved to the database via the REST API.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_data` | `array` | The booking record data. |
| `$customer` | `array` | The raw customer data from the request. |
| `$slot_id` | `int` | The selected time slot ID. |

**Return:** `array`

```php
add_filter( 'sg_booking_before_save', function ( $booking_data, $customer, $slot_id ) {
    // Add a referral source to every booking.
    $booking_data['referral_source'] = sanitize_text_field( $_COOKIE['ref'] ?? 'direct' );
    return $booking_data;
}, 10, 3 );
```

---

## Booking Lifecycle Hooks

### `bm_after_booking_saved` *(action)*

Fires after a booking has been successfully saved via the REST API.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_id` | `int` | The new booking ID. |
| `$booking_data` | `array` | The booking data that was inserted. |

```php
add_action( 'bm_after_booking_saved', function ( $booking_id, $booking_data ) {
    // Send a webhook notification.
    wp_remote_post( 'https://hooks.example.com/booking', array(
        'body' => wp_json_encode( array( 'booking_id' => $booking_id ) ),
    ) );
}, 10, 2 );
```

### `flexibooking_set_process_new_order` *(action)*

Fires when a new order booking is processed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_id` | `int` | The booking ID. |

### `flexibooking_set_process_new_request` *(action)*

Fires when a new booking request is processed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_id` | `int` | The booking ID. |

### `flexibooking_set_process_approved_order` *(action)*

Fires when a booking is approved.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_id` | `int` | The booking ID. |

### `flexibooking_set_process_cancel_order` *(action)*

Fires when a booking is cancelled.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_id` | `int` | The booking ID. |

### `flexibooking_set_process_failed_order` *(action)*

Fires when a booking fails.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_key` | `string` | The booking key. |

### `flexibooking_cancel_booking` *(filter)*

Filters whether a booking should be cancelled.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_id` | `int` | The booking ID. |

**Return:** `mixed`

### `flexibooking_update_status_as_completed` *(filter)*

Filters booking status update to completed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_id` | `int` | The booking ID. |

**Return:** `mixed`

### `flexibooking_update_status_as_processing` *(filter)*

Filters booking status update to processing.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_id` | `int` | The booking ID. |

**Return:** `mixed`

### `flexibooking_update_status_as_refunded` *(filter)*

Filters booking status update to refunded.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_id` | `int` | The booking ID. |
| `$refund_data` | `mixed` | Refund details. |

**Return:** `mixed`

---

## Service Management Hooks

### `bm_flexibooking_before_service_visibility_update` *(action)*

Fires before a service visibility is updated.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | `int` | The service ID. |
| `$update_data` | `array` | The visibility data to update. |

### `bm_flexibooking_after_service_visibility_update` *(action)*

Fires after a service visibility is updated.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | `int` | The service ID. |
| `$service` | `object` | The service data. |
| `$update` | `mixed` | The update result. |

### `bm_flexibooking_modify_service_visibility_id` *(filter)*

Filters the service ID before visibility change.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | `int` | The service ID. |

**Return:** `int`

### `bm_flexibooking_modify_service_visibility_response` *(filter)*

Filters the service visibility AJAX response data.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | The response data. |

**Return:** `array`

### `bm_flexibooking_service_id_before_service_removal` *(action)*

Fires before a service is removed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | `int` | The service ID. |

### `bm_flexibooking_after_service_removal` *(action)*

Fires after a service is removed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | `int` | The service ID. |
| `$service_removed` | `mixed` | The removal result. |

### `bm_flexibooking_modify_sorted_services` *(filter)*

Filters the sorted services list.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$services` | `array` | The sorted services. |

**Return:** `array`

### `bm_flexibooking_modify_sort_data` *(filter)*

Filters the service sort response data.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | The response data. |
| `$post` | `array` | The POST data. |

**Return:** `array`

---

## Category Management Hooks

### `bm_flexibooking_before_category_sort` *(action)*

Fires before categories are sorted.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ids` | `array` | The category IDs in new order. |
| `$total` | `int` | Total category records. |

### `bm_flexibooking_after_category_sort` *(action)*

Fires after categories are sorted.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ids` | `array` | The category IDs in new order. |
| `$total` | `int` | Total category records. |

### `bm_flexibooking_before_category_visibility_change` *(action)*

Fires before a category visibility is changed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | `int` | The category ID. |
| `$category` | `object` | The category data. |
| `$update_data` | `array` | The visibility data. |

### `bm_flexibooking_after_category_visibility_change` *(action)*

Fires after a category visibility is changed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | `int` | The category ID. |
| `$category` | `object` | The category data. |
| `$update` | `mixed` | The update result. |

### `bm_flexibooking_category_visibility_response` *(filter)*

Filters the category visibility AJAX response.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | The response data. |

**Return:** `array`

---

## Email Template Hooks

### `bm_flexibooking_before_template_removal` *(action)*

Fires before an email template is removed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | `int` | The template ID. |
| `$template` | `object` | The template data. |

### `bm_flexibooking_after_template_removal` *(action)*

Fires after an email template is removed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | `int` | The template ID. |
| `$template` | `object` | The template data. |
| `$removed` | `mixed` | The removal result. |

### `bm_flexibooking_before_template_visibility_change` *(action)*

Fires before a template visibility is changed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$template_id` | `int` | The template ID. |
| `$input_status` | `int` | The new visibility status. |
| `$input_type` | `string` | The template type. |

### `bm_flexibooking_after_template_visibility_change` *(action)*

Fires after a template visibility is changed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$template_id` | `int` | The template ID. |
| `$input_status` | `int` | The new visibility status. |
| `$update` | `mixed` | The update result. |

### `bm_flexibooking_modify_template_listing_post` *(filter)*

Filters POST data for template listing.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$post` | `array` | The POST data. |

**Return:** `array`

### `bm_flexibooking_modify_template_listing_response` *(filter)*

Filters the template listing response.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | The response data. |

**Return:** `array`

---

## Notification Process Hooks

### `bm_flexibooking_before_process_visibility_change` *(action)*

Fires before a notification process visibility is changed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$process_id` | `int` | The process ID. |
| `$input_status` | `int` | The new visibility status. |
| `$input_type` | `string` | The process type. |

### `bm_flexibooking_after_process_visibility_change` *(action)*

Fires after a notification process visibility is changed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$process_id` | `int` | The process ID. |
| `$input_status` | `int` | The new visibility status. |
| `$update` | `mixed` | The update result. |

### `bm_flexibooking_modify_notification_process_listing_post` *(filter)*

Filters POST data for notification process listing.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$post` | `array` | The POST data. |

**Return:** `array`

### `bm_flexibooking_modify_notification_process_listing_response` *(filter)*

Filters the notification process listing response.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | The response data. |

**Return:** `array`

---

## Transaction Hooks

### `flexibooking_fetch_order_transaction_data` *(filter)*

Filters transaction data when fetching for an order.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_id` | `int` | The booking ID. |

**Return:** `mixed`

### `flexibooking_fetch_html_with_transaction_data` *(filter)*

Filters the HTML output for transaction data display.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$transaction_data` | `mixed` | The transaction data. |

**Return:** `string`

### `flexibooking_save_order_transaction_data` *(filter)*

Filters when saving transaction data.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_id` | `int` | The booking ID. |
| `$transaction_id` | `string` | The transaction ID. |
| `$refund_id` | `string` | The refund ID. |
| `$payment_status` | `string` | The payment status. |
| `$is_active` | `int` | Whether the transaction is active. |

**Return:** `mixed`

### `flexibooking_update_transaction_data` *(filter)*

Filters when updating transaction data.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_id` | `int` | The booking ID. |
| `$transaction_data` | `mixed` | The transaction data. |

**Return:** `mixed`

### `flexibooking_paid_transaction_statuses` *(filter)*

Filters the list of paid transaction statuses.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$statuses` | `array` | The paid status list. |

**Return:** `array`

### `flexibooking_pending_transaction_statuses` *(filter)*

Filters the list of pending transaction statuses.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$statuses` | `array` | The pending status list. |

**Return:** `array`

---

## Admin Menu & UI Hooks

### `sg_booking_register_pro_menus` *(action)*

Fires after admin menus are registered. The Pro add-on hooks here to replace locked upsell menu callbacks with real Pro page callbacks.

| Parameter | Type | Description |
|-----------|------|-------------|
| *(none)* | — | — |

```php
add_action( 'sg_booking_register_pro_menus', function () {
    // Replace an upsell page with a real feature page.
    remove_submenu_page( 'bm_home', 'bm_booking_analytics' );
    add_submenu_page( 'bm_home', 'Analytics', 'Analytics', 'manage_options', 'bm_booking_analytics', 'my_analytics_page' );
} );
```

---

## Internationalisation Hooks

### `bm_flexibooking_language_switcher_html` *(filter)*

Filters the language switcher HTML in the admin bar.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$html` | `string` | The switcher HTML. |
| `$languages` | `array` | Available languages. |
| `$current_language` | `string` | The current language code. |

**Return:** `string`

### `bm_flexibooking_modify_installed_languages` *(filter)*

Filters the list of installed languages.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$languages` | `array` | The language list. |

**Return:** `array`

### `bm_flexibooking_languages_installed` *(action)*

Fires after languages are installed/loaded.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$languages` | `array` | The installed languages. |

### `bm_flexibooking_set_language_post_data` *(filter)*

Filters POST data before setting a language.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$post` | `array` | The POST data. |

**Return:** `array`

### `bm_flexibooking_language_set` *(action)*

Fires after a language is set.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$set_language` | `string` | The language code that was set. |
| `$current_locale` | `string` | The current locale. |

### `bm_flexibooking_set_language_response` *(filter)*

Filters the language-set AJAX response.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | The response data. |
| `$set_language` | `string` | The language code. |

**Return:** `array`

### `flexibooking_show_lang_switchr_in_admin_bar` *(filter)*

Filters whether to show the language switcher in the admin bar.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$show` | `bool` | Whether to show the switcher. |

**Return:** `bool`

### `flexibooking_show_lang_switchr_in_footer` *(filter)*

Filters whether to show the language switcher in the footer.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$show` | `bool` | Whether to show the switcher. |

**Return:** `bool`

---

## WooCommerce Integration Hooks

### `flexibooking_google_analytics_data` *(filter)*

Filters Google Analytics purchase data for bookings.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | The GA purchase data. |

**Return:** `array`

---

## Table Identifiers Reference

The following table identifiers are used with the database hooks (`sg_booking_before_insert`, `sg_booking_after_update`, etc.):

| Identifier | Description |
|------------|-------------|
| `SERVICE` | Services table |
| `TIME` | Time slots table |
| `GALLERY` | Gallery images table |
| `EXTRA` | Extra services table |
| `BOOKING` | Bookings/orders table |
| `SLOTCOUNT` | Slot count tracking table |
| `EMAIL_TMPL` | Email templates table |
| `FIELD` | Form fields table |
| `CATEGORY` | Service categories table |
| `FORM` | Billing forms table |
| `VOUCHER` | Vouchers table |
| `CUSTOMER` | Customers table |
| `COUPON` | Coupons table |
| `NOTIFICATION` | Notification processes table |

---

## Notes for Developers

1. **Hook Prefix Convention**: New hooks use the `sg_booking_` prefix. Legacy hooks use `bm_flexibooking_` or `flexibooking_` prefixes.
2. **Pro Detection**: Use `Booking_Management_Feature_Control::is_pro()` or the `sg_booking_is_pro_active` filter to check Pro status.
3. **Freemium Architecture**: The free version uses CSS-only teasers for Pro features. Pro-only admin menus use the `bm_pro_upsell_page` callback with `<span class="bm-menu-pro-badge">Pro</span>` badges.
4. **REST API Namespace**: All endpoints are under `sg-booking/v1`.
5. **Database Operations**: Always use `BM_DBhandler` methods (`insert_row`, `update_row`, `remove_row`) instead of direct `$wpdb` calls to ensure hooks fire correctly.
