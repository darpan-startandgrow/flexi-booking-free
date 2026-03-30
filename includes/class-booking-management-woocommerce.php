<?php

/**
 * WooCommerce integration service for FlexiBooking.
 *
 * Handles adding booking items to the WooCommerce cart,
 * creating WC products from services/extras, and managing
 * product pricing.
 *
 * @since      1.0.0
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 */
class WooCommerceService {


    /**
     * Get WooCommerce Cart instance.
     *
     * @since 1.0.0
     * @return WC_Cart|null Cart object or null when unavailable.
     */
    public function bm_get_woo_commerce_cart() {
        return function_exists( 'wc' ) && wc()->cart ? wc()->cart : null;
    }//end bm_get_woo_commerce_cart()


    /**
     * Check whether WooCommerce is active.
     *
     * @since 1.0.0
     * @return bool
     */
    public function is_enabled() {
        return class_exists( 'WooCommerce' );
    }//end is_enabled()


    /**
     * Get WooCommerce Cart URL.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_woo_commerce_cart_url() {
        return function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '';
    }//end get_woo_commerce_cart_url()


    /**
     * Get WooCommerce Checkout URL.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_woo_commerce_checkout_url() {
        return function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '';
    }//end get_woo_commerce_checkout_url()


    /**
     * Add a service booking to the WooCommerce cart.
     *
     * Clears the existing cart, then adds the main service product
     * and any extra-service products based on the booking data.
     *
     * @since 1.0.0
     * @param array  $data             Booking data array (service_id, booking_date, etc.).
     * @param string $flexi_order_key  The FlexiBooking order key.
     * @return bool True on success, false on failure.
     */
    public function add_to_cart( $data = array(), $flexi_order_key = '' ) {
        $wooCommerceCart = $this->bm_get_woo_commerce_cart();

        if ( ! $wooCommerceCart ) {
            return false;
        }

        /**
         * Fires before FlexiBooking adds items to the WooCommerce cart.
         *
         * @since 1.2.0
         * @param array  $data             Cart data (service_id, booking_date, etc.).
         * @param string $flexi_order_key  The FlexiBooking order key.
         */
        do_action( 'sg_booking_before_wc_add_to_cart', $data, $flexi_order_key );

        try {
            // Clear existing cart items.
            foreach ( $wooCommerceCart->get_cart() as $wc_key => $wc_item ) {
                $wooCommerceCart->remove_cart_item( $wc_key );
            }

            if ( empty( $data ) || empty( $flexi_order_key ) ) {
                return false;
            }

            $dbhandler  = new BM_DBhandler();
            $service_id = isset( $data['service_id'] ) ? (int) $data['service_id'] : 0;
            $date       = isset( $data['booking_date'] ) ? $data['booking_date'] : '';
            $product_id = (int) $dbhandler->get_value( 'SERVICE', 'wc_product', $service_id, 'id' );
            $svc_price  = isset( $data['base_svc_price'] ) ? $data['base_svc_price'] : -1;

            if ( $service_id <= 0 || empty( $date ) || $product_id <= 0 ) {
                return false;
            }

            $total_service_booked = isset( $data['total_service_booking'] ) ? (int) $data['total_service_booking'] : 0;
            $extra_service_ids    = isset( $data['extra_svc_booked'] ) ? $data['extra_svc_booked'] : '';
            $extra_slots_booked   = isset( $data['total_extra_slots_booked'] ) ? $data['total_extra_slots_booked'] : 0;

            /**
             * Filters the WooCommerce cart item data for a FlexiBooking order.
             *
             * @since 1.2.0
             * @param array  $cart_item_data  Cart item data array.
             * @param array  $data            The original booking data.
             * @param string $flexi_order_key The FlexiBooking order key.
             */
            $cart_item_data = apply_filters( 'sg_booking_wc_cart_item_data', array(
                'added_by_flexibooking' => true,
                'flexi_booking_key'     => $flexi_order_key,
                'flexi_checkout_key'    => ( new BM_Request() )->bm_generate_unique_code( '', 'FLEXIC', 15 ),
            ), $data, $flexi_order_key );

            if ( $svc_price != -1 ) {
                $cart_item_data['flexi_svc_price'] = $svc_price;
            }

            $wooCommerceCart->add_to_cart( $product_id, $total_service_booked, 0, array(), $cart_item_data );

            // Add extra services to the cart.
            if ( ! empty( $extra_service_ids ) && ! empty( $extra_slots_booked ) ) {
                $extra_slots_booked = explode( ',', $extra_slots_booked );
                $safe_ids           = implode( ',', array_map( 'absint', explode( ',', $extra_service_ids ) ) );
                $additional         = 'id in(' . $safe_ids . ')';
                $extras             = $dbhandler->get_all_result( 'EXTRA', '*', 1, 'results', 0, false, null, false, $additional );

                if ( ! empty( $extras ) ) {
                    foreach ( $extras as $key => $extra ) {
                        $extra_product_id = isset( $extra->svcextra_wc_product ) ? (int) $extra->svcextra_wc_product : 0;
                        $extra_svc_price  = isset( $extra->extra_price ) ? $extra->extra_price : 0;

                        $cart_item_data['flexi_extra_svc_price'][ $key ] = $extra_svc_price;

                        if ( $extra_product_id > 0 ) {
                            $wooCommerceCart->add_to_cart( $extra_product_id, $extra_slots_booked[ $key ], 0, array(), $cart_item_data );
                        }
                    }
                }
            }//end if

            /**
             * Fires after FlexiBooking successfully adds items to the WooCommerce cart.
             *
             * @since 1.2.0
             * @param array  $data            The booking data.
             * @param string $flexi_order_key The FlexiBooking order key.
             * @param int    $service_id      The service ID.
             */
            do_action( 'sg_booking_after_wc_add_to_cart', $data, $flexi_order_key, $service_id );
            return true;
        } catch ( Exception $e ) {
            error_log( 'FlexiBooking WC add_to_cart error: ' . $e->getMessage() );
            return false;
        }
    }//end add_to_cart()


    /**
     * Create a new WooCommerce product for a service or extra.
     *
     * @since 1.0.0
     * @param int    $id   Service or extra ID.
     * @param string $date Booking date.
     * @param string $type 'service' or 'extra'.
     * @return int Product ID on success, 0 on failure.
     */
    private static function create_woo_product( $id = 0, $date = '', $type = 'service' ) {
        if ( empty( $id ) || empty( $date ) ) {
            return 0;
        }

        $dbhandler  = new BM_DBhandler();
        $bmrequests = new BM_Request();
        $data       = array();

        switch ( $type ) {
            case 'service':
                $service = $dbhandler->get_row( 'SERVICE', $id );
                if ( empty( $service ) ) {
                    return 0;
                }
                $category_id    = ! empty( $service->service_category ) ? (int) $service->service_category : 0;
                $category_title = $bmrequests->bm_fetch_category_name_by_service_id( $id );
                $data['image']         = ! empty( $service->service_image_guid ) ? (int) $service->service_image_guid : 0;
                $data['name']          = ! empty( $service->service_name ) ? esc_html( $service->service_name ) : '';
                $data['slug']          = ! empty( $service->service_name ) ? $bmrequests->bm_create_slug( $service->service_name ) : '';
                $data['long_desc']     = ! empty( $service->service_desc ) ? wp_kses_post( stripslashes( $service->service_desc ) ) : '';
                $data['price']         = str_replace( '&euro;', '', $bmrequests->bm_fetch_service_price_by_service_id_and_date( $service->id, $date, 'global_format' ) );
                $data['default_price'] = ! empty( $service->default_price ) ? (float) $service->default_price : 0;
                $data['category']      = ! empty( $category_title ) ? self::get_wc_product_category_id_by_title( $category_title ) : -1;

                if ( 0 === $data['category'] && $category_id > 0 ) {
                    $data['category'] = self::create_woo_product_category( $category_id );
                }
                break;

            case 'extra':
                $extra = $dbhandler->get_row( 'EXTRA', $id );
                if ( empty( $extra ) ) {
                    return 0;
                }
                $data['image']     = 0;
                $data['name']      = ! empty( $extra->extra_name ) ? esc_html( $extra->extra_name ) : '';
                $data['slug']      = ! empty( $extra->extra_name ) ? $bmrequests->bm_create_slug( $extra->extra_name ) : '';
                $data['long_desc'] = ! empty( $extra->extra_desc ) ? wp_kses_post( stripslashes( $extra->extra_desc ) ) : '';
                $data['price']     = ! empty( $extra->extra_price ) ? (float) $extra->extra_price : 0;
                $data['category']  = 0;
                break;

            default:
                return 0;
        }//end switch

        if ( empty( $data ) || empty( $data['name'] ) ) {
            return 0;
        }

        $product = new WC_Product_Simple();
        $product->set_name( $data['name'] );
        $product->set_slug( $data['slug'] );

        if ( isset( $data['default_price'] ) && $data['price'] < $data['default_price'] ) {
            $product->set_regular_price( $data['default_price'] );
            $product->set_sale_price( $data['price'] );
        } else {
            $product->set_regular_price( $data['price'] );
        }

        $product->set_description( $data['long_desc'] );
        $product->set_image_id( $data['image'] );
        if ( ! empty( $data['category'] ) && $data['category'] > 0 ) {
            $product->set_category_ids( array( $data['category'] ) );
        }
        $product->set_stock_status( 'instock' );
        $product->save();

        $product_id = $product->get_id();

        if ( ! empty( $product_id ) ) {
            $update_data = ( 'service' === $type )
                ? array( 'is_linked_wc_product' => 1, 'wc_product' => $product_id )
                : array( 'is_linked_wc_extrasvc' => 1, 'svcextra_wc_product' => $product_id );
            $table = ( 'service' === $type ) ? 'SERVICE' : 'EXTRA';
            $dbhandler->update_row( $table, 'id', $id, $update_data, '', '%d' );
        }

        return $product_id;
    }//end create_woo_product()


    /**
     * Get all WooCommerce products.
     *
     * @since 1.0.0
     * @param array $params WP_Query args override.
     * @return array Array of arrays with 'id' and 'name' keys.
     */
    private static function get_all_products( $params = array() ) {
        $params = array_merge(
            array(
                'post_type'      => 'product',
                'posts_per_page' => -1,
            ),
            $params
        );

        $products = array();

        foreach ( get_posts( $params ) as $product ) {
            $products[] = array(
                'id'   => $product->ID,
                'name' => $product->post_title,
            );
        }

        return $products;
    }//end get_all_products()


    /**
     * Get all WooCommerce product categories.
     *
     * @since 1.0.0
     * @param array $params get_categories() args override.
     * @return array Array of arrays with 'id' and 'name' keys.
     */
    private static function get_all_product_categories( $params = array() ) {
        $params = array_merge( array( 'taxonomy' => 'product_cat' ), $params );

        $categories = array();

        foreach ( get_categories( $params ) as $category ) {
            $categories[] = array(
                'id'   => $category->term_id,
                'name' => $category->name,
            );
        }

        return $categories;
    }//end get_all_product_categories()


    /**
     * Create a WooCommerce product category from a booking category.
     *
     * @since 1.0.0
     * @param int $category_id Booking category ID.
     * @return int WC term ID on success, 0 on failure.
     */
    private static function create_woo_product_category( $category_id = 0 ) {
        if ( empty( $category_id ) ) {
            return 0;
        }

        $dbhandler  = new BM_DBhandler();
        $bmrequests = new BM_Request();
        $category   = $dbhandler->get_row( 'CATEGORY', $category_id );

        if ( empty( $category ) ) {
            return 0;
        }

        $name = ! empty( $category->cat_name ) ? esc_html( $category->cat_name ) : '';
        $slug = ! empty( $name ) ? $bmrequests->bm_create_slug( $name ) : '';

        $result = wp_insert_term(
            $name,
            'product_cat',
            array(
                'description' => '',
                'slug'        => $slug,
            )
        );

        if ( is_wp_error( $result ) ) {
            // Term may already exist; look it up.
            $existing = get_term_by( 'slug', $slug, 'product_cat' );
            return $existing ? $existing->term_id : 0;
        }

        return isset( $result['term_id'] ) ? (int) $result['term_id'] : 0;
    }//end create_woo_product_category()


    /**
     * Get a WooCommerce product ID by its title.
     *
     * @since 1.0.0
     * @param string $title Product title.
     * @return int Product ID on success, 0 on failure.
     */
    private static function get_wc_product_id_by_title( $title = '' ) {
        if ( empty( $title ) ) {
            return 0;
        }

        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = %s LIMIT 1",
                $title,
                'product'
            )
        );

        if ( empty( $result ) ) {
            return 0;
        }

        $product = wc_get_product( (int) $result );
        return ! empty( $product ) ? $product->get_id() : 0;
    }//end get_wc_product_id_by_title()


    /**
     * Get a WooCommerce product category ID by its name.
     *
     * @since 1.0.0
     * @param string $title Category name.
     * @return int Term ID on success, 0 on failure.
     */
    private static function get_wc_product_category_id_by_title( $title = '' ) {
        if ( empty( $title ) ) {
            return 0;
        }

        $result = get_term_by( 'name', $title, 'product_cat' );
        return ! empty( $result ) ? $result->term_id : 0;
    }//end get_wc_product_category_id_by_title()


    /**
     * Set a WooCommerce product's regular price.
     *
     * @since 1.0.0
     * @param int   $product_id WC product ID.
     * @param float $price      New regular price.
     */
    public function set_wc_product_regular_price( $product_id, $price ) {
        if ( empty( $product_id ) ) {
            return;
        }

        $product = wc_get_product( $product_id );

        if ( $product ) {
            $product->set_regular_price( $price );
            $product->set_price( $price );
            $product->save();
        }
    }//end set_wc_product_regular_price()


    /**
     * Set a WooCommerce product's sale price.
     *
     * @since 1.0.0
     * @param int   $product_id WC product ID.
     * @param float $price      New sale price.
     */
    public function set_wc_product_sale_price( $product_id, $price ) {
        if ( empty( $product_id ) ) {
            return;
        }

        $product = wc_get_product( $product_id );

        if ( $product ) {
            $product->set_sale_price( $price );
            $product->set_price( $price );
            $product->save();
        }
    }//end set_wc_product_sale_price()


}//end class
