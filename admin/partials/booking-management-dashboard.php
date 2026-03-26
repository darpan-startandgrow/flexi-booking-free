<?php
$dbhandler          = new BM_DBhandler();
$bmrequests         = new BM_Request();
$woocommerceservice = new WooCommerceService();
$limit              = 10;
$total              = $dbhandler->bm_count( 'BOOKING' );
$today              = gmdate( 'Y-m-d' );
$next_day           = $bmrequests->bm_add_day( $today, '+1 day' );
$week_last_day      = $bmrequests->bm_add_day( $today, '+7 day' );
$categories         = $dbhandler->get_all_result( 'CATEGORY', '*', 1, 'results', 0, false, 'cat_position', false );
$services           = $dbhandler->get_all_result( 'SERVICE', '*', array( 'is_service_front' => 1 ), 'results', 0, false, 'service_position', false );
$category_ids       = !empty( $categories ) ? wp_list_pluck( $categories, 'id' ) : array();
$service_ids        = !empty( $services ) ? wp_list_pluck( $services, 'id' ) : array();

/**if ( $woocommerceservice->is_enabled() ) {
    $order_statuses = wc_get_order_statuses();
} else {
    $order_statuses = $bmrequests->bm_fetch_order_status_key_value();
}*/

$order_statuses = $bmrequests->bm_fetch_order_status_key_value();
$timezone       = ( new BM_DBhandler() )->get_global_option_value( 'bm_booking_time_zone', 'Asia/Kolkata' );
$date           = new DateTime( 'now', new DateTimeZone( $timezone ) );
$current_year   = $date->format( 'Y' );
$months         = array(
    '01' => esc_html__( 'Jan', 'service-booking' ),
    '02' => esc_html__( 'Feb', 'service-booking' ),
    '03' => esc_html__( 'Mar', 'service-booking' ),
    '04' => esc_html__( 'Apr', 'service-booking' ),
    '05' => esc_html__( 'May', 'service-booking' ),
    '06' => esc_html__( 'June', 'service-booking' ),
    '07' => esc_html__( 'Jul', 'service-booking' ),
    '08' => esc_html__( 'Aug', 'service-booking' ),
    '09' => esc_html__( 'Sept', 'service-booking' ),
    '10' => esc_html__( 'Oct', 'service-booking' ),
    '11' => esc_html__( 'Nov', 'service-booking' ),
    '12' => esc_html__( 'Dec', 'service-booking' ),
);

?>

<div class="container">
    <div class="pagewrapper">
        <div class="widgetbar">
            <div class="widgetbox">
                <div class="leftwidget">
                    <h2><?php esc_html_e( 'Total Bookings', 'service-booking' ); ?><br />
                        <span class="total_bookings_count"></span>
                    </h2>
                    <ul class="legend0">
                        <li><span class="legend-dots bluedot get_booking-info" data-status="booked" data-type="total"></span><?php esc_html_e( 'Completed', 'service-booking' ); ?></li>
                        <li><span class="legend-dots greydot get_booking-info" data-status="pending" data-type="total"></span><?php esc_html_e( 'Pending', 'service-booking' ); ?></li>
                    </ul>
                </div>
                <div class="rightwidget">
                    <div class="dashboard_month_year_selection">
                        <select class="widgetselect total_year_analytics" data-type="total" onchange="bm_fetch_booking_counts(this)">
                            <option value=""><?php echo esc_html__( 'year', 'service-booking' ); ?></option>
                            <?php for ( $yr = $current_year - 10; $yr <= $current_year + 10; $yr++ ) { ?>
                                <option value="<?php echo esc_attr( $yr ); ?>"><?php echo esc_attr( $yr ); ?></option>
                            <?php } ?>
                        </select>
                        <select class="widgetselect total_month_analytics" data-type="total" onchange="bm_fetch_booking_counts(this)">
                            <option value=""><?php echo esc_html__( 'month', 'service-booking' ); ?></option>
                            <?php foreach ( $months as $key => $month ) { ?>
                                <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $month ); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="svg-item">
                        <svg width="100%" height="100%" viewBox="0 0 40 40" class="donut">
                            <circle class="donut-hole" cx="20" cy="20" r="15.91549430918954" fill="#fff"></circle>
                            <circle class="donut-ring" cx="20" cy="20" r="15.91549430918954" fill="transparent" stroke-width="3.5"></circle>
                            <circle class="donut-segment" cx="20" cy="20" r="15.91549430918954" fill="transparent" stroke-width="3.5" stroke-dasharray="80 20" stroke-dashoffset="25"></circle>
                            <g class="donut-text">
                                <!-- <text y="50%" transform="translate(0, 2)">
                                  <tspan x="50%" text-anchor="middle" class="donut-percent">40%</tspan>   
                                </text> -->
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="widgetbox">
                <div class="leftwidget">
                    <h2><?php esc_html_e( 'Upcoming Bookings', 'service-booking' ); ?><br />
                        <span class="upcoming_bookings_count"></span>
                    </h2>
                    <ul class="legend0">
                        <li><span class="legend-dots bluedot get_booking-info" data-status="booked" data-type="upcoming"></span><?php esc_html_e( 'Completed', 'service-booking' ); ?></li>
                        <li><span class="legend-dots greydot get_booking-info" data-status="pending" data-type="upcoming"></span><?php esc_html_e( 'Pending', 'service-booking' ); ?></li>
                    </ul>
                </div>
                <div class="rightwidget">
                    <!-- <div class="dashboard_month_year_selection">
                        <select class="widgetselect upcoming_year_analytics" data-type="upcoming" onchange="bm_fetch_booking_counts(this)">
                            <?php for ( $yr = $current_year - 10; $yr <= $current_year + 10; $yr++ ) { ?>
                                <option value="<?php echo esc_attr( $yr ); ?>" <?php echo $yr == $current_year ? 'selected' : ''; ?>><?php echo esc_attr( $yr ); ?></option>
                            <?php } ?>
                        </select>
                        <select class="widgetselect upcoming_month_analytics" data-type="upcoming" onchange="bm_fetch_booking_counts(this)">
                            <option value=""><?php echo esc_html__( 'month', 'service-booking' ); ?></option>
                            <?php foreach ( $months as $key => $month ) { ?>
                                <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $month ); ?></option>
                            <?php } ?>
                        </select>
                    </div> -->

                    <div class="svg-item">
                        <svg width="100%" height="100%" viewBox="0 0 40 40" class="donut">
                            <circle class="donut-hole" cx="20" cy="20" r="15.91549430918954" fill="#fff"></circle>
                            <circle class="donut-ring" cx="20" cy="20" r="15.91549430918954" fill="transparent" stroke-width="3.5"></circle>
                            <circle class="donut-segment" cx="20" cy="20" r="15.91549430918954" fill="transparent" stroke-width="3.5" stroke-dasharray="80 20" stroke-dashoffset="25"></circle>
                            <g class="donut-text">
                                <!-- <text y="50%" transform="translate(0, 2)">
                                  <tspan x="50%" text-anchor="middle" class="donut-percent">40%</tspan>   
                                </text> -->
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="widgetbox">
                <div class="leftwidget">
                    <h2><?php esc_html_e( 'Bookings This Week', 'service-booking' ); ?><br />
                        <span class="weekly_bookings_count"></span>
                    </h2>
                    <ul class="legend0">
                        <li><span class="legend-dots bluedot get_booking-info" data-status="booked" data-type="weekly"></span><?php esc_html_e( 'Completed', 'service-booking' ); ?></li>
                        <li><span class="legend-dots greydot get_booking-info" data-status="pending" data-type="weekly"></span><?php esc_html_e( 'Pending', 'service-booking' ); ?></li>
                    </ul>
                </div>
                <div class="rightwidget">
                    <div class="widgetselect">

                    </div>

                    <div class="svg-item">
                        <svg width="100%" height="100%" viewBox="0 0 40 40" class="donut">
                            <circle class="donut-hole" cx="20" cy="20" r="15.91549430918954" fill="#fff"></circle>
                            <circle class="donut-ring" cx="20" cy="20" r="15.91549430918954" fill="transparent" stroke-width="3.5"></circle>
                            <circle class="donut-segment" cx="20" cy="20" r="15.91549430918954" fill="transparent" stroke-width="3.5" stroke-dasharray="80 20" stroke-dashoffset="25"></circle>
                            <g class="donut-text">
                                <!-- <text y="50%" transform="translate(0, 2)">
                                  <tspan x="50%" text-anchor="middle" class="donut-percent">40%</tspan>   
                                </text> -->
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="widgetbox">
                <div class="leftwidget">
                    <h2><?php esc_html_e( 'Total Revenue', 'service-booking' ); ?><br />
                        <span class="total_bookings_revenue"></span>
                    </h2>
                    <ul class="legend0">
                        <li><span class="legend-dots bluedot get_booking-info" data-status="booked" data-type="revenue"></span><?php esc_html_e( 'Completed', 'service-booking' ); ?></li>
                        <li><span class="legend-dots greydot get_booking-info" data-status="pending" data-type="revenue"></span><?php esc_html_e( 'Pending', 'service-booking' ); ?></li>
                    </ul>
                </div>
                <div class="rightwidget">
                    <div class="dashboard_month_year_selection">
                        <select class="widgetselect revenue_year_analytics" data-type="revenue" onchange="bm_fetch_booking_counts(this)">
                            <option value=""><?php echo esc_html__( 'year', 'service-booking' ); ?></option>
                            <?php for ( $yr = $current_year - 10; $yr <= $current_year + 10; $yr++ ) { ?>
                                <option value="<?php echo esc_attr( $yr ); ?>"><?php echo esc_attr( $yr ); ?></option>
                            <?php } ?>
                        </select>
                        <select class="widgetselect revenue_month_analytics" data-type="revenue" onchange="bm_fetch_booking_counts(this)">
                            <option value=""><?php echo esc_html__( 'month', 'service-booking' ); ?></option>
                            <?php foreach ( $months as $key => $month ) { ?>
                                <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $month ); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="svg-item">
                        <svg width="100%" height="100%" viewBox="0 0 40 40" class="donut">
                            <circle class="donut-hole" cx="20" cy="20" r="15.91549430918954" fill="#fff"></circle>
                            <circle class="donut-ring" cx="20" cy="20" r="15.91549430918954" fill="transparent" stroke-width="3.5"></circle>
                            <circle class="donut-segment" cx="20" cy="20" r="15.91549430918954" fill="transparent" stroke-width="3.5" stroke-dasharray="80 20" stroke-dashoffset="25"></circle>
                            <g class="donut-text">
                                <!-- <text y="50%" transform="translate(0, 2)">
                                  <tspan x="50%" text-anchor="middle" class="donut-percent">40%</tspan>   
                                </text> -->
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div style="margin-top: 15px; padding: 12px 16px; background: #fff8e1; border-left: 4px solid #ffb300; border-radius: 3px;">
            <p style="margin: 0;">
                <span class="dashicons dashicons-lock" style="color: #ffb300;"></span>
                <strong><?php esc_html_e( 'Advanced Analytics & Reports', 'service-booking' ); ?></strong>
                <span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span><br />
                <small><?php esc_html_e( 'Upgrade to Pro for Booking Status Charts, Revenue Reports, Category-wise Analytics, Service-wise Revenue, Date-wise Revenue, Customer-wise Revenue, and more.', 'service-booking' ); ?></small>
            </p>
        </div>

<input type="hidden" id="cat_wise_orders_pagenum" value="<?php echo esc_attr( 1 ); ?>" />
<input type="hidden" id="revenue_orders_pagenum" value="<?php echo esc_attr( 1 ); ?>" />
<input type="hidden" id="datewise_revenue_orders_pagenum" value="<?php echo esc_attr( 1 ); ?>" />
<input type="hidden" id="customer_wise_revenue_orders_pagenum" value="<?php echo esc_attr( 1 ); ?>" />
<input type="hidden" name="limit_count" id="limit_count" value="<?php echo esc_attr( $limit ); ?>" />
<input type="hidden" id="total_search_status" value="booked" />
<!-- <input type="hidden" id="upcoming_search_status" value="booked" /> -->
<input type="hidden" id="revenue_search_status" value="booked" />

<div id="customer-dialog" title="<?php esc_html_e( 'Customer Details', 'service-booking' ); ?>" style="display: none;">
    <ul id="customer-list"></ul>
</div>

<div class="loader_modal"></div>
