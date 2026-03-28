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
14. [Payment Processing Hooks](#payment-processing-hooks)
15. [Event-Driven Booking Events](#event-driven-booking-events)
16. [License Management Hooks](#license-management-hooks)
17. [Freemius SDK Integration](#freemius-sdk-integration)
18. [Event Dispatcher Hooks](#event-dispatcher-hooks)
19. [Async Queue Hooks](#async-queue-hooks)
20. [Deactivation Hooks](#deactivation-hooks)
21. [Hybrid Architecture Overview](#hybrid-architecture-overview)
22. [Hooks Audit Report](#hooks-audit-report)

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

### `sg_booking_rest_services` *(filter)*

Filters the services list returned by the REST API v1 `GET /services` endpoint.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$items` | `array` | The service items array. |
| `$total` | `int` | Total number of services matching the query. |
| `$request` | `WP_REST_Request` | The original request object. |

**Return:** `array`

```php
add_filter( 'sg_booking_rest_services', function ( $items, $total, $request ) {
    // Add a computed field to each service.
    foreach ( $items as &$item ) {
        $item['is_popular'] = $item['service_position'] <= 3;
    }
    return $items;
}, 10, 3 );
```

### `sg_booking_rest_categories` *(filter)*

Filters the categories list returned by the REST API v1 `GET /categories` endpoint.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | The category items array. |
| `$request` | `WP_REST_Request` | The original request object. |

**Return:** `array`

```php
add_filter( 'sg_booking_rest_categories', function ( $data, $request ) {
    // Remove hidden categories from the API response.
    return array_filter( $data, function ( $cat ) {
        return $cat['cat_in_front'] === 1;
    } );
}, 10, 2 );
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

## Field Management Hooks

### `bm_flexibooking_before_updating_field_ordering` *(filter)*

Fired before field ordering is saved. Allows modification of the ordering array.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ordering` | `array` | Array of field ordering values. |
| `$total_field_records` | `int` | Total number of fields in the database. |

**Return:** `array`

---

### `bm_flexibooking_after_updating_field_ordering` *(action)*

Fired after field ordering has been saved to the database.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ordering` | `array` | Array of field ordering values that were saved. |

---

### `bm_flexibooking_before_fetching_fields` *(filter)*

Fired before field labels are returned. Allows modification of the fields array before `is_default` is added.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$fields` | `array` | Array of field objects from the database. |

**Return:** `array`

---

### `bm_flexibooking_after_fetching_fields` *(filter)*

Fired after field labels have been fetched and `is_default` has been set on each field.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$fields` | `array` | Array of field objects with `is_default` property set. |

**Return:** `array`

---

### `bm_flexibooking_field_labels_response` *(filter)*

Fired just before the field labels response is JSON-encoded and returned.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$fields` | `array` | Final array of field objects. |

**Return:** `array`

---

### `bm_flexibooking_before_fetching_last_row_id` *(filter)*

Fired before the last row ID is used to calculate new field ordering.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$lastrow_id` | `int` | The ID of the last field row. |

**Return:** `int`

---

### `bm_flexibooking_before_fetching_field_key` *(filter)*

Fired before the field key is used for a new field.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$field_key` | `string` | The generated field key (e.g., `sgbm_field_9`). |

**Return:** `string`

---

### `bm_flexibooking_primary_mail_filed_key` *(filter)*

Fired before the primary email field key is returned.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$primary_mail_key` | `string` | The field key of the primary email field. |

**Return:** `string`

---

### `bm_flexibooking_after_fetching_field_key_and_ordering` *(action)*

Fired after the field key and ordering have been calculated for a new field.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$field_key` | `string` | The generated field key. |
| `$ordering` | `int` | The next ordering number. |

---

### `bm_flexibooking_fieldkey_and_order_response` *(filter)*

Fired before the field key and order response is JSON-encoded and returned.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | The response data with type, ordering, field_key, and primary_mail_key. |

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

### `sg_booking_before_wc_add_to_cart` *(action)*

Fires before FlexiBooking adds items to the WooCommerce cart.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | Cart data (service_id, booking_date, etc.). |
| `$flexi_order_key` | `string` | The FlexiBooking order key. |

```php
add_action( 'sg_booking_before_wc_add_to_cart', function ( $data, $flexi_order_key ) {
    // Log WooCommerce cart additions.
    error_log( "Adding booking {$flexi_order_key} to WooCommerce cart." );
}, 10, 2 );
```

### `sg_booking_wc_cart_item_data` *(filter)*

Filters the WooCommerce cart item data for a FlexiBooking order.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$cart_item_data` | `array` | Cart item data array. |
| `$data` | `array` | The original booking data. |
| `$flexi_order_key` | `string` | The FlexiBooking order key. |

**Return:** `array`

```php
add_filter( 'sg_booking_wc_cart_item_data', function ( $cart_item_data, $data, $flexi_order_key ) {
    // Add custom metadata to the WooCommerce cart item.
    $cart_item_data['custom_addon_ref'] = 'my_addon_v1';
    return $cart_item_data;
}, 10, 3 );
```

### `sg_booking_after_wc_add_to_cart` *(action)*

Fires after FlexiBooking successfully adds items to the WooCommerce cart.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$data` | `array` | The booking data. |
| `$flexi_order_key` | `string` | The FlexiBooking order key. |
| `$service_id` | `int` | The service ID. |

```php
add_action( 'sg_booking_after_wc_add_to_cart', function ( $data, $flexi_order_key, $service_id ) {
    // Trigger analytics event after WooCommerce cart addition.
    do_action( 'my_analytics_cart_add', $service_id );
}, 10, 3 );
```

---

## Payment Processing Hooks

### `sg_booking_before_payment_processing` *(action)*

Fires before payment processing begins.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$booking_key` | `string` | The booking key. |
| `$checkout_key` | `string` | The checkout key. |
| `$method_id` | `string` | The payment method ID. |
| `$gift` | `bool` | Whether the booking is a gift. |

```php
add_action( 'sg_booking_before_payment_processing', function ( $booking_key, $checkout_key, $method_id, $gift ) {
    // Log payment attempt.
    error_log( "Payment processing started for booking {$booking_key}, method: {$method_id}" );
}, 10, 4 );
```

### `sg_booking_after_payment_processing` *(action)*

Fires after payment processing completes.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$process_status` | `string` | The result status. |
| `$booking_key` | `string` | The booking key. |
| `$checkout_key` | `string` | The checkout key. |

```php
add_action( 'sg_booking_after_payment_processing', function ( $process_status, $booking_key, $checkout_key ) {
    if ( $process_status === 'success' ) {
        // Trigger post-payment workflows.
    }
}, 10, 3 );
```

---

## Event-Driven Booking Events

The following events are dispatched via `SG_Event_Dispatcher::dispatch()` alongside the legacy action hooks. Use `SG_Event_Dispatcher::listen()` or the `sg_booking_event_{name}` WordPress actions to respond.

| Event Name | When Dispatched | Payload Keys |
|-----------|-----------------|--------------|
| `booking.confirmed` | After a direct booking is successfully saved | `booking_id`, `booking_type`, `service_id`, `booking_date`, `slot_id`, `quantity`, `source` |
| `booking.request_created` | After a book-on-request is created | `booking_id`, `booking_type` |
| `booking.cancelled` | After a booking is cancelled | `booking_id` |
| `booking.approved` | After a book-on-request is approved | `booking_id` |
| `booking.failed` | After payment processing fails | `booking_id`, `customer_id` |
| `booking.failed_refund` | After a failed order is refunded | `booking_key`, `transaction_id`, `refund_id` |
| `payment.received` | After successful payment | `booking_id`, `transaction_id`, `paid_amount` |
| `payment.refunded` | After a booking refund is processed | `booking_id`, `refund_id` |

```php
// Listen for booking confirmation events.
SG_Event_Dispatcher::listen( 'booking.confirmed', function ( $payload ) {
    // Send to external analytics, trigger webhook, etc.
    wp_remote_post( 'https://example.com/webhook', array(
        'body' => wp_json_encode( $payload ),
    ) );
} );

// Or use WordPress actions (dot replaced with underscore).
add_action( 'sg_booking_event_booking_confirmed', function ( $payload ) {
    // Same as above, using WordPress hook system.
} );
```

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

---

## License Management Hooks

### `sg_license_before_activation` *(action)*

Fires before a license activation attempt.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$license_key` | `string` | The license key being activated. |
| `$item_id` | `string` | The product/plan ID. |

```php
add_action( 'sg_license_before_activation', function ( $license_key, $item_id ) {
    error_log( 'Attempting to activate license: ' . substr( $license_key, 0, 8 ) . '...' );
}, 10, 2 );
```

### `sg_license_after_activation` *(action)*

Fires after a license activation attempt completes.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$license_key` | `string` | The license key. |
| `$valid` | `bool` | Whether activation succeeded. |
| `$status` | `string` | The new license status (`'active'` or `'invalid'`). |
| `$provider` | `string` | The license provider used (`'edd'`, `'freemius'`, `'custom'`). |

```php
add_action( 'sg_license_after_activation', function ( $license_key, $valid, $status, $provider ) {
    if ( $valid ) {
        // Enable Pro features, clear caches, etc.
    }
}, 10, 4 );
```

### `sg_license_before_deactivation` *(action)*

Fires before a license deactivation attempt.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$license_key` | `string` | The license key being deactivated. |
| `$provider` | `string` | The license provider. |

### `sg_license_after_deactivation` *(action)*

Fires after a license is deactivated.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$license_key` | `string` | The license key that was deactivated. |
| `$provider` | `string` | The license provider. |

### `sg_license_is_active` *(filter)*

Filters the license active status. Allows external plugins to override the license check.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$is_active` | `bool` | Whether the license is active. |
| `$license_key` | `string` | The stored license key. |
| `$status` | `string` | The raw status string. |

**Return:** `bool`

```php
add_filter( 'sg_license_is_active', function ( $is_active, $license_key, $status ) {
    // Override during testing.
    if ( defined( 'SG_BOOKING_DEV_MODE' ) && SG_BOOKING_DEV_MODE ) {
        return true;
    }
    return $is_active;
}, 10, 3 );
```

### `sg_license_show_notice` *(filter)*

Filters whether to display the license admin notice.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$show` | `bool` | Whether to show the notice. Default `true`. |
| `$status` | `string` | The current license status. |
| `$screen_id` | `string` | The current admin screen ID. |

**Return:** `bool`

### `sg_license_before_notice` *(action)*

Fires before the license admin notice is displayed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$status` | `string` | The license status (`'invalid'` or `'expired'`). |

### `sg_license_after_notice` *(action)*

Fires after the license admin notice is displayed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$status` | `string` | The license status. |

### Freemius SDK Integration

The plugin supports [Freemius](https://freemius.com/) as an alternative licensing and distribution platform. When enabled, Freemius handles license validation, automatic updates, deactivation tracking, and upsell flows.

#### Architecture

```
Lite Plugin (free, wp.org)
  └── includes/class-sg-freemius.php    → sg_booking_fs() initialisation
  └── includes/class-sg-license-manager.php → provider-based validation
  └── includes/class-booking-management-feature-control.php → is_pro() gate

Pro Plugin (separate add-on, distributed via Freemius)
  └── Hooks sg_booking_is_pro_active filter → returns true when valid
```

**Key principle:** The Lite plugin contains zero Pro code. Pro features are delivered exclusively through a separate add-on plugin. Even if someone cracks the Lite plugin, they find no Pro code to unlock.

#### Enabling Freemius

1. **Define credentials** in `wp-config.php` (or a mu-plugin):

```php
define( 'SG_BOOKING_LICENSE_PROVIDER', 'freemius' );
define( 'SG_BOOKING_FS_ID', 12345 );              // Your Freemius product ID.
define( 'SG_BOOKING_FS_PUBLIC_KEY', 'pk_abc...' ); // Your Freemius public key.
define( 'SG_BOOKING_FS_SLUG', 'sg-flexi-booking' ); // Optional, defaults to 'sg-flexi-booking'.
```

2. **Bundle the Freemius SDK** in the plugin root at `freemius/start.php`. The SDK is loaded automatically when the provider is `'freemius'` and valid credentials are configured.

3. The `sg_booking_fs()` global function returns the Freemius SDK instance (or `null` when inactive).

#### How It Works

| Step | Description |
|------|-------------|
| 1 | Plugin loads `includes/class-sg-freemius.php`. |
| 2 | `sg_booking_fs()` checks `SG_BOOKING_LICENSE_PROVIDER` via the `sg_license_validation_method` filter. |
| 3 | If provider is `'freemius'` and credentials are set, `fs_dynamic_init()` initialises the SDK. |
| 4 | `Booking_Management_Feature_Control::is_pro()` checks `sg_booking_fs()->is_paying()`. |
| 5 | If the user has a valid Freemius license, Pro features are unlocked. |
| 6 | The License admin page (`admin.php?page=sg_booking_license`) shows Freemius account info. |

#### Validation Flow

```
sg_booking_fs()->is_paying()
  ├── true  → is_pro() returns true → free restrictions skipped
  └── false → is_pro() falls back to sg_booking_is_pro_active filter (default false)
```

#### Constants Reference

| Constant | Default | Description |
|----------|---------|-------------|
| `SG_BOOKING_LICENSE_PROVIDER` | `'edd'` | License provider: `'edd'`, `'freemius'`, or `'custom'`. |
| `SG_BOOKING_FS_ID` | `0` | Freemius product/plugin ID. |
| `SG_BOOKING_FS_PUBLIC_KEY` | `''` | Freemius public key for your product. |
| `SG_BOOKING_FS_SLUG` | `'sg-flexi-booking'` | Freemius product slug. |
| `SG_BOOKING_EDD_STORE_URL` | `'https://startandgrow.in'` | EDD store URL (when provider is `'edd'`). |
| `SG_BOOKING_EDD_ITEM_ID` | `0` | EDD download/item ID. |
| `SG_BOOKING_EDD_ITEM_NAME` | `'SG Flexi Booking Pro'` | EDD item name (fallback when item ID is 0). |

#### Filter: `sg_license_validation_method`

Controls which license provider is used.

```php
// Switch to Freemius at runtime.
add_filter( 'sg_license_validation_method', function () {
    return 'freemius';
} );
```

#### Uninstall Cleanup

When the plugin is uninstalled via Freemius, the `sg_booking_fs_uninstall_cleanup` callback removes:
- `sg_booking_license_key` option
- `sg_booking_license_status` option
- `sg_booking_license_expiry` option
- `sg_booking_license_cache` transient

#### Files

| File | Purpose |
|------|---------|
| `includes/class-sg-freemius.php` | `sg_booking_fs()` initialisation and uninstall hook. |
| `includes/class-sg-license-manager.php` | Provider-based license validation, activation, deactivation. Admin UI. |
| `includes/class-booking-management-feature-control.php` | Central `is_pro()` gate. Checks Freemius first. |

---

## Event Dispatcher Hooks

### `sg_booking_event_payload` *(filter)*

Filters the event payload before dispatching.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$payload` | `array` | Event data. |
| `$event` | `string` | Event name. |

**Return:** `array`

```php
add_filter( 'sg_booking_event_payload', function ( $payload, $event ) {
    if ( 'booking.confirmed' === $event ) {
        $payload['custom_tracking_id'] = uniqid( 'trk_' );
    }
    return $payload;
}, 10, 2 );
```

### `sg_booking_event_{event_name}` *(action)*

Dynamic action for each dispatched event. The event name has dots replaced with underscores.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$payload` | `array` | Event data. |

**Examples:**
- `sg_booking_event_booking_confirmed` — Booking confirmed
- `sg_booking_event_booking_cancelled` — Booking cancelled
- `sg_booking_event_payment_received` — Payment received
- `sg_booking_event_email_admin_notification` — Admin email sent
- `sg_booking_event_email_customer_notification` — Customer email sent
- `sg_booking_event_pdf_generate` — PDF generation requested
- `sg_booking_event_webhook_send` — Webhook delivery

```php
add_action( 'sg_booking_event_booking_confirmed', function ( $payload ) {
    // Send a webhook to an external CRM.
    wp_remote_post( 'https://crm.example.com/webhook', array(
        'body' => wp_json_encode( $payload ),
    ) );
} );
```

### `sg_booking_event_dispatched` *(action)*

Fires after any event is dispatched (catch-all).

| Parameter | Type | Description |
|-----------|------|-------------|
| `$event` | `string` | Event name. |
| `$payload` | `array` | Event data. |

### `sg_booking_async_events` *(filter)*

Filters the list of events processed asynchronously via the queue.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$async_events` | `array` | Default async event list. |

**Return:** `array`

```php
add_filter( 'sg_booking_async_events', function ( $events ) {
    // Add custom event to async processing.
    $events[] = 'crm.sync';
    return $events;
} );
```

### `sg_booking_event_system_init` *(action)*

Fires after the event system is initialized. Register custom listeners or async events here.

| Parameter | Type | Description |
|-----------|------|-------------|
| *(none)* | — | — |

```php
add_action( 'sg_booking_event_system_init', function () {
    SG_Event_Dispatcher::listen( 'booking.confirmed', 'my_crm_sync_callback' );
    SG_Event_Dispatcher::register_async( 'crm.sync' );
} );
```

---

## Async Queue Hooks

### `sg_booking_should_queue_job` *(filter)*

Filters whether a job should be queued or processed immediately.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$should_queue` | `bool` | Default `true`. |
| `$event` | `string` | Job event name. |
| `$payload` | `array` | Job data. |

**Return:** `bool`

```php
// Process all email jobs immediately (skip queue).
add_filter( 'sg_booking_should_queue_job', function ( $should_queue, $event ) {
    if ( str_starts_with( $event, 'email.' ) ) {
        return false;
    }
    return $should_queue;
}, 10, 2 );
```

### `sg_booking_job_queued` *(action)*

Fires when a job is added to the queue.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$job` | `array` | Job data (`id`, `event`, `payload`, `created_at`). |
| `$event` | `string` | The event name. |

### `sg_booking_job_processed` *(action)*

Fires after a queued job is processed successfully.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$job` | `array` | The processed job data. |

### `sg_booking_job_failed` *(action)*

Fires when a queued job fails after all retries (3 attempts).

| Parameter | Type | Description |
|-----------|------|-------------|
| `$job` | `array` | The failed job data. |
| `$exception` | `\Exception` | The exception that caused the failure. |

```php
add_action( 'sg_booking_job_failed', function ( $job, $exception ) {
    error_log( 'FlexiBooking queue job failed: ' . $job['event'] . ' — ' . $exception->getMessage() );
}, 10, 2 );
```

### `sg_booking_queue_batch_complete` *(action)*

Fires after a queue batch is processed.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$processed` | `int` | Number of successfully processed jobs. |
| `$remaining` | `array` | Jobs remaining in the queue. |
| `$failed` | `array` | Jobs that failed after all retries. |

### `sg_booking_queue_processor` *(filter)*

Filters the job processor for custom queue backends (Redis Queue, RabbitMQ, etc.).

| Parameter | Type | Description |
|-----------|------|-------------|
| `$processor` | `callable\|null` | Custom job processor. Default `null`. |
| `$event` | `string` | Job event name. |
| `$payload` | `array` | Job data. |

**Return:** `callable|null`

```php
// Route all jobs to a custom Redis-based processor.
add_filter( 'sg_booking_queue_processor', function ( $processor, $event, $payload ) {
    return function ( $event, $payload ) {
        MyRedisQueue::dispatch( $event, $payload );
    };
}, 10, 3 );
```

---

## Deactivation Hooks

### `sg_booking_deactivated` *(action)*

Fires during plugin deactivation, after queue cleanup.

| Parameter | Type | Description |
|-----------|------|-------------|
| *(none)* | — | — |

```php
add_action( 'sg_booking_deactivated', function () {
    // Clean up custom tables or options.
    delete_option( 'my_addon_settings' );
} );
```

---

## Hybrid Architecture Overview

### Scalability Tiers

| Traffic Level | Architecture | Components |
|---------------|-------------|------------|
| 10K–100K users/day | Optimized modular plugin + caching | `SG_Cache_Manager` (transients), WP-Cron queue |
| 100K–1M users/day | Add async processing + external services | `SG_Async_Queue` + `SG_Event_Dispatcher` (async), Redis object cache |
| 1M+ users/day | Headless + microservices | Custom `sg_booking_queue_processor`, external queue (Redis/RabbitMQ), CDN caching |

### Cache Manager Usage

```php
$cache = SG_Cache_Manager::get_instance();

// Simple get/set.
$cache->set( 'service_42', $service_data, 600 );
$data = $cache->get( 'service_42' );

// Remember pattern (get or compute).
$timeslots = $cache->remember( 'ts_42_2024-01-15', function () {
    return compute_timeslots( 42, '2024-01-15' );
}, 300 );

// API response caching.
$response = $cache->cache_api_response( '/timeslots', $params, function () use ( $params ) {
    return fetch_timeslots_from_db( $params );
}, 300 );
```

### Event Dispatcher Usage

```php
// Dispatch a booking event.
SG_Event_Dispatcher::dispatch( 'booking.confirmed', [
    'booking_id' => 123,
    'service_id' => 42,
    'customer'   => 'user@example.com',
] );

// Listen for events.
SG_Event_Dispatcher::listen( 'booking.confirmed', function ( $payload ) {
    // Send webhook, update analytics, etc.
} );
```

### Async Queue Usage

```php
$queue = SG_Async_Queue::get_instance();

// Push a job for background processing.
$queue->push( 'email.send', [
    'to'      => 'user@example.com',
    'subject' => 'Booking Confirmed',
    'body'    => $email_html,
] );
```

---

## Email Records Hooks

### `sg_booking_before_email_records_page` *(action)*

Fired before the email records admin page renders.

**Usage:**

```php
add_action( 'sg_booking_before_email_records_page', function () {
    // Track page views, load additional resources, etc.
} );
```

---

### `sg_booking_after_email_records_page` *(action)*

Fired after the email records admin page renders.

**Usage:**

```php
add_action( 'sg_booking_after_email_records_page', function () {
    // Add custom footer content, scripts, etc.
} );
```

---

### `sg_booking_before_email_records_bulk_delete` *(action)*

Fired before email records are bulk-deleted. Allows archiving or logging before deletion.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ids` | `array` | Array of email record IDs about to be deleted. |

**Usage:**

```php
add_action( 'sg_booking_before_email_records_bulk_delete', function ( $ids ) {
    // Archive email records before deletion.
    foreach ( $ids as $id ) {
        my_archive_email_record( $id );
    }
} );
```

---

### `sg_booking_after_email_records_bulk_delete` *(action)*

Fired after email records have been bulk-deleted.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$ids` | `array` | Array of email record IDs that were deleted. |

**Usage:**

```php
add_action( 'sg_booking_after_email_records_bulk_delete', function ( $ids ) {
    // Log the deletion event.
    error_log( 'Deleted email records: ' . implode( ', ', $ids ) );
} );
```

---

### `sg_booking_email_records_query_additional` *(filter)*

Filters the additional WHERE clause for the email records list table SQL query. Allows adding custom conditions.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$additional` | `string` | Raw SQL additional WHERE conditions. |

**Return:** `string`

**Usage:**

```php
add_filter( 'sg_booking_email_records_query_additional', function ( $additional ) {
    // Only show emails from the last 30 days.
    global $wpdb;
    $additional .= $wpdb->prepare( ' AND e.created_at >= %s', gmdate( 'Y-m-d', strtotime( '-30 days' ) ) );
    return $additional;
} );
```

---

### `sg_booking_email_records_items` *(filter)*

Filters the email records items array before they are displayed in the list table.

| Parameter | Type | Description |
|-----------|------|-------------|
| `$items` | `array` | Array of email record row data (each item is an associative array). |

**Return:** `array`

**Usage:**

```php
add_filter( 'sg_booking_email_records_items', function ( $items ) {
    // Add custom data to each item.
    foreach ( $items as &$item ) {
        $item['custom_note'] = get_custom_note_for_email( $item['id'] );
    }
    return $items;
} );
```

---

## Hooks Audit Report

This section documents the audit of all admin hooks registered in `define_admin_hooks()` in `class-booking-management.php` and their associated callback functions in `class-booking-management-admin.php`.

### 1. Hooks Summary

#### Admin UI & Title Hooks

| Hook | Type | Callback | Priority | Args | Status |
|------|------|----------|----------|------|--------|
| `admin_notices` | action | `bm_disable_admin_notices_on_specific_pages` | 1 | 1 | ✅ Correct |
| `admin_title` | filter | `bm_ensure_admin_title` | 1 | 1 | ✅ Correct |
| `parent_file` | filter | `bm_fix_menu_highlight` | 10 | 1 | ✅ Correct |
| `set-screen-option` | filter | `bm_set_screen_option` | 10 | 3 | ✅ Correct |
| `admin_notices` | action | `bm_flexi_admin_notice` | 10 | 1 | ✅ Correct |
| `admin_bar_menu` | action | `bm_add_flexibooking_language_switcher_in_admin_bar` | 999 | 1 | ✅ Correct — late priority ensures other menu items are already registered |
| `wp_footer` | action | `bm_add_flexibooking_language_switcher_in_footer` | 10 | 1 | ✅ Correct |

#### Timezone Hooks

| Hook | Type | Callback | Priority | Args | Status |
|------|------|----------|----------|------|--------|
| `init` | action | `bm_set_timezone` | 10 | 1 | ✅ Refactored — now skips DB write when timezone is unchanged |
| `update_option_timezone_string` | action | `bm_update_plugin_timezone_on_wp_change` | 10 | 2 | ✅ Correct |
| `update_option_gmt_offset` | action | `bm_update_plugin_timezone_on_gmt_offset_change` | 10 | 2 | ✅ Refactored — early return pattern |

#### Initialisation Hooks

| Hook | Type | Callback | Priority | Args | Status |
|------|------|----------|----------|------|--------|
| `init` | action | `bm_register_shortcodes` | 10 | 1 | ✅ Correct |
| `init` | action | `bm_set_installed_languages` | 10 | 1 | ✅ Refactored — skips download when Italian already installed |
| `init` | action | `bm_multilingual_email` | 10 | 1 | ✅ Correct |

#### Cron Schedule Hooks

| Hook | Type | Callback | Priority | Args | Status |
|------|------|----------|----------|------|--------|
| `cron_schedules` | filter | `bm_custom_cron_schedule` | 10 | 1 | ✅ Correct — adds `per_minute` and `per_5_minute` schedules |
| `init` | action | `bm_check_booking_requests` | 10 | 1 | ✅ Correct — schedules `flexibooking_check_expired_book_on_request_bookings` |
| `init` | action | `bm_check_falied_emails_and_resend_pdfs` | 10 | 1 | ⚠️ Typo in method name (`falied` should be `failed`) — kept for backward compatibility |
| `init` | action | `bm_mark_flexi_paid_processing_bookings_as_completed` | 10 | 1 | ✅ Correct |
| `init` | action | `bm_mark_pending_bookings_as_cancelled` | 10 | 1 | ✅ Correct |
| `init` | action | `bm_mark_expired_free_bookings_as_completed` | 10 | 1 | ✅ Correct |
| `init` | action | `bm_check_expired_vouchers` | 10 | 1 | ✅ Correct |

#### Cron Callback Hooks

| Hook | Type | Callback | Priority | Args | Status |
|------|------|----------|----------|------|--------|
| `flexibooking_check_expired_book_on_request_bookings` | action | `flexibooking_check_expired_book_on_request_bookings_callback` | 10 | 1 | ✅ Refactored — timezone moved outside loop, uses helper methods |
| `bm_resend_missing_emails_hook` | action | `bm_resend_missing_emails_cron` | 10 | 1 | ✅ Correct |
| `flexibooking_check_paid_expired_processing_bookings` | action | `flexibooking_check_paid_expired_processing_bookings_callback` | 10 | 1 | ✅ Refactored — uses `bm_is_booking_service_expired()` and `bm_expire_woocommerce_order_for_booking()` |
| `flexibooking_check_expired_pending_bookings` | action | `flexibooking_check_expired_pending_bookings_callback` | 10 | 1 | ✅ Refactored |
| `flexibooking_check_expired_free_bookings` | action | `flexibooking_check_expired_free_bookings_callback` | 10 | 1 | ✅ Refactored |
| `flexibooking_check_expired_vouchers` | action | `flexibooking_check_expired_vouchers_callback` | 10 | 1 | ✅ Correct |

#### Booking Status Update Filters

| Hook | Type | Callback | Priority | Args | Status |
|------|------|----------|----------|------|--------|
| `flexibooking_cancel_booking` | filter | `bm_flexibooking_cancel_booking` | 10 | 1 | ✅ Refactored — uses `bm_update_all_booking_tables()` |
| `flexibooking_update_status_as_refunded` | filter | `bm_flexibooking_update_status_as_refunded` | 10 | 2 | ✅ Refactored |
| `flexibooking_update_status_as_completed` | filter | `bm_flexibooking_update_status_as_completed` | 10 | 1 | ✅ Refactored |
| `flexibooking_update_status_as_processing` | filter | `bm_flexibooking_update_status_as_processing` | 10 | 1 | ✅ Refactored |
| `flexibooking_update_status_as_on_hold` | filter | `bm_flexibooking_update_status_as_on_hold` | 10 | 1 | ✅ Refactored |
| `flexibooking_mark_processing_orders_as_complete` | filter | `bm_flexibooking_mark_processing_orders_as_complete` | 10 | 1 | ✅ Correct |
| `bm_mark_free_orders_as_complete` | filter | `bm_mark_free_orders_as_complete` | 10 | 1 | ✅ Refactored — removed unused `$wc_order_id` |

#### Notification Process Hooks

| Hook | Type | Callback | Priority | Args | Status |
|------|------|----------|----------|------|--------|
| `flexibooking_set_process_approved_order` | action | `bm_flexibooking_set_process_approved_order_callback` | 10 | 1 | ✅ Refactored — uses `bm_schedule_notification_for_event()` |
| `flexibooking_mail_approved_order` | action | `bm_flexibooking_mail_on_approved_order_callback` | 10 | 3 | ✅ Correct — thin wrapper |
| `flexibooking_set_process_cancel_order` | action | `bm_flexibooking_set_process_cancel_order_callback` | 10 | 1 | ✅ Refactored |
| `flexibooking_mail_cancel_order` | action | `bm_flexibooking_mail_on_cancel_order_callback` | 10 | 3 | ✅ Correct — thin wrapper |
| `flexibooking_set_process_failed_order` | action | `bm_flexibooking_set_process_failed_order_callback` | 10 | 1 | ✅ Refactored — fixed `maybe_serialize` → `maybe_unserialize` bug |
| `flexibooking_mail_failed_order` | action | `bm_flexibooking_mail_on_failed_order_callback` | 10 | 3 | ✅ Correct — thin wrapper |
| `flexibooking_refund_cancelled_order` | filter | `bm_flexibooking_refund_cancelled_order` | 10 | 1 | ✅ Correct |
| `flexibooking_set_process_order_refund` | action | `bm_flexibooking_set_process_order_refund_callback` | 10 | 2 | ✅ Refactored |
| `flexibooking_mail_order_refund` | action | `bm_flexibooking_mail_on_order_refund_callback` | 10 | 3 | ✅ Correct — thin wrapper |

#### Transaction Data Hooks

| Hook | Type | Callback | Priority | Args | Status |
|------|------|----------|----------|------|--------|
| `flexibooking_fetch_order_transaction_data` | filter | `bm_flexibooking_fetch_order_transaction_data` | 10 | 1 | ✅ Fixed — empty array guard added |
| `flexibooking_fetch_html_with_transaction_data` | filter | `bm_flexibooking_fetch_html_with_transaction_data` | 10 | 1 | ✅ Correct |
| `flexibooking_save_order_transaction_data` | filter | `bm_flexibooking_save_order_transaction_data` | 10 | 5 | ✅ Correct — orchestrates full transaction save pipeline |
| `flexibooking_save_existing_transaction_data_before_update` | action | `bm_flexibooking_save_existing_transaction_data_before_update` | 10 | 1 | ✅ Correct |
| `flexibooking_verify_if_valid_transaction_id` | filter | `bm_flexibooking_verify_if_valid_transaction_id` | 10 | 3 | ✅ Correct |
| `flexibooking_verify_if_paid_transaction_id` | filter | `bm_flexibooking_verify_if_paid_transaction_id` | 10 | 1 | ✅ Correct |
| `flexibooking_paid_transaction_statuses` | filter | `bm_flexibooking_paid_transaction_statuses` | 10 | 1 | ✅ Correct — extensibility hook |
| `flexibooking_verify_if_pending_transaction_id` | filter | `bm_flexibooking_verify_if_pending_transaction_id` | 10 | 1 | ✅ Fixed — variable naming |
| `flexibooking_pending_transaction_statuses` | filter | `bm_flexibooking_pending_transaction_statuses` | 10 | 1 | ✅ Correct — extensibility hook |
| `flexibooking_verify_if_cancelled_transaction_id` | filter | `bm_flexibooking_verify_if_cancelled_transaction_id` | 10 | 1 | ✅ Correct |
| `flexibooking_verify_transaction_for_free_payment_status` | filter | `bm_flexibooking_verify_transaction_for_free_payment_status` | 10 | 1 | ✅ Correct |
| `flexibooking_verify_if_refunded_transaction_id` | filter | `bm_flexibooking_verify_if_refunded_transaction_id` | 10 | 1 | ✅ Correct |
| `flexibooking_update_transaction_data` | filter | `bm_flexibooking_update_transaction_data` | 10 | 2 | ✅ Correct |
| `flexibooking_update_booking_data_before_marking_transaction_failed` | filter | `bm_flexibooking_update_booking_data_before_marking_transaction_failed` | 10 | 1 | ✅ Correct |
| `flexibooking_add_data_to_failed_transaction_table` | filter | `bm_flexibooking_add_data_to_failed_transaction_table` | 10 | 2 | ✅ Fixed — return `$status` on empty booking_id |
| `flexibooking_update_booking_data_after_transaction_update` | filter | `bm_flexibooking_update_booking_data_after_transaction_update` | 10 | 2 | ✅ Correct |
| `flexibooking_check_and_remove_duplicate_record_in_failed_transaction_table` | filter | `bm_flexibooking_check_and_remove_duplicate_record_in_failed_transaction_table` | 10 | 1 | ✅ Correct |
| `flexibooking_revert_transaction_update` | filter | `bm_flexibooking_revert_transaction_update` | 10 | 1 | ✅ Correct |

#### WooCommerce Integration Hooks

| Hook | Type | Callback | Priority | Args | Status |
|------|------|----------|----------|------|--------|
| `woocommerce_admin_order_data_after_order_details` | action | `bm_display_service_date_in_admin` | 10 | 1 | ✅ Fixed — text domain typo corrected, uses `esc_html__()` |
| `before_delete_post` | action | `bm_remove_flexi_order_if_woocommerce_order_is_permanently_deleted` | 10 | 1 | ✅ Correct |
| `wp_trash_post` | action | `bm_modify_flexi_plugin_order_on_woocommerce_order_trash` | 10 | 1 | ✅ Correct |
| `untrash_post` | action | `bm_schedule_woocommerce_order_status_check_on_untrash` | 10 | 1 | ✅ Correct |
| `bm_update_flexi_order_as_woocommerce_order_is_restored` | action | `bm_modify_flexi_plugin_order_on_woocommerce_order_untrash` | 10 | 1 | ✅ Fixed — order validity check added |
| `woocommerce_hidden_order_itemmeta` | filter | `bm_hide_flexi_order_itemmeta` | 10 | 1 | ✅ Correct |
| `pre_post_update` | action | `bm_prevent_expired_woocommerce_order_updates` | 10 | 2 | ✅ Correct |

### 2. Bugs Fixed

| Bug | Location | Fix |
|-----|----------|-----|
| Text domain typo `'servuice-booking'` | `bm_display_service_date_in_admin` | Changed to `'service-booking'` |
| `maybe_serialize` instead of `maybe_unserialize` | `bm_flexibooking_set_process_failed_order_callback` | Fixed to `maybe_unserialize` |
| Unchecked array access `$transaction_data[0]` | `bm_flexibooking_fetch_order_transaction_data` | Added empty/is_array guard |
| Bare `return;` instead of `return $status;` | `bm_flexibooking_add_data_to_failed_transaction_table` | Fixed return value |
| Missing `$order` validity check | `bm_modify_flexi_plugin_order_on_woocommerce_order_untrash` | Added `! $order` guard |
| Uninitialized `$failed_customer_data` | `bm_flexibooking_add_data_to_failed_transaction_table` | Initialized to empty array |

### 3. Performance Improvements

| Improvement | Method | Impact |
|-------------|--------|--------|
| Skip DB write when timezone unchanged | `bm_set_timezone` | Eliminates 2 DB writes per page load when timezone hasn't changed |
| Skip language pack download when already installed | `bm_set_installed_languages` | Eliminates filesystem access and HTTP request per page load |
| Move timezone fetch outside loop | `flexibooking_check_expired_book_on_request_bookings_callback` | Reduces from N DB queries to 1 for timezone |
| Cache timestamp in single variable | `bm_update_all_booking_tables` | Reduces redundant `bm_fetch_current_wordpress_datetime_stamp()` calls from 5-7 to 1 |

### 4. DRY Refactoring Summary

| Helper Method | Replaces | Lines Saved |
|---------------|----------|-------------|
| `bm_update_all_booking_tables()` | 5 status-update methods (cancel, refunded, completed, processing, on_hold) | ~200 lines |
| `bm_schedule_notification_for_event()` | 4 notification callbacks (approved, cancel, failed, refund) | ~400 lines |
| `bm_evaluate_notification_conditions()` | Condition evaluation logic duplicated 4 times | Extracted into reusable method |
| `bm_calculate_notification_delay()` | Time offset calculation duplicated 4 times | Extracted into reusable method |
| `bm_expire_woocommerce_order_for_booking()` | WC order expiry logic in 4 cron callbacks | ~60 lines |
| `bm_is_booking_service_expired()` | Service date comparison in 3 cron callbacks | ~30 lines |
| `bm_get_current_plugin_datetime()` | Timezone + DateTime construction in 4 callbacks | ~20 lines |

### 5. Critical Hooks for Connectivity

The following hooks are essential for core functionality and third-party integration:

#### Core Booking Lifecycle
- `flexibooking_cancel_booking` — Central cancellation point, deactivates all booking tables
- `flexibooking_update_status_as_completed` — Marks bookings as succeeded
- `flexibooking_update_status_as_processing` — Marks bookings as processing
- `flexibooking_update_status_as_refunded` — Handles refund status updates with Stripe refund ID
- `flexibooking_save_order_transaction_data` — Orchestrates the full transaction save pipeline

#### WooCommerce Integration
- `woocommerce_admin_order_data_after_order_details` — Displays service date in WC admin
- `before_delete_post` / `wp_trash_post` / `untrash_post` — Syncs FlexiBooking status with WC order lifecycle
- `woocommerce_hidden_order_itemmeta` — Hides internal meta from WC order admin
- `pre_post_update` — Prevents updates to expired WC orders

#### Cron & Scheduled Tasks
- `cron_schedules` — Registers per-minute and per-5-minute schedules
- `flexibooking_check_expired_book_on_request_bookings` — Cancels expired book-on-request orders
- `flexibooking_check_paid_expired_processing_bookings` — Completes expired processing orders
- `flexibooking_check_expired_pending_bookings` — Cancels expired pending orders
- `flexibooking_check_expired_free_bookings` — Completes expired free orders
- `bm_resend_missing_emails_hook` — Resends failed emails for upcoming bookings

#### Notification System
- `flexibooking_set_process_approved_order` — Triggers approved order email workflow
- `flexibooking_set_process_cancel_order` — Triggers cancellation email workflow
- `flexibooking_set_process_failed_order` — Triggers failed order email workflow
- `flexibooking_set_process_order_refund` — Triggers refund email workflow

#### Internationalisation
- `admin_bar_menu` → `bm_add_flexibooking_language_switcher_in_admin_bar` — Admin bar language switcher
- `wp_footer` → `bm_add_flexibooking_language_switcher_in_footer` — Frontend language switcher

### 6. Coding Standards Improvements

| Issue | Fix |
|-------|-----|
| Loose comparisons (`==`) | Changed to strict (`===`) or `absint()` where DB values are string type |
| Inconsistent variable naming (`$pending_payment_Statuses`) | Fixed to `$pending_payment_statuses` |
| Unused variables | Removed `$wc_order_id` in `bm_mark_free_orders_as_complete` |
| Missing PHPDoc blocks | Added `@since`, `@param`, `@return` to all refactored methods |
| Non-descriptive variable names (`$one`, `$two`, `$three`, `$four`) | Replaced with `$txn_result`, `$booking_result`, `$slotcount_result` in helper |
| Deeply nested conditionals | Replaced with early returns and guard clauses |
| Misleading variable name (`$show_admin_bar` in footer method) | Renamed to `$show_in_footer` |
