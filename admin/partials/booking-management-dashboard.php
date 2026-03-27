<?php
/**
 * Dashboard — Free Version.
 *
 * Provides KPI cards, booking status chart, and booking tables
 * with basic filters (date range, status, search).
 *
 * @since      1.3.0
 * @package    Booking_Management
 * @subpackage Booking_Management/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dbhandler  = new BM_DBhandler();
$bmrequests = new BM_Request();
$timezone   = $dbhandler->get_global_option_value( 'bm_booking_time_zone', 'Asia/Kolkata' );

try {
	$tz = new DateTimeZone( $timezone );
} catch ( Exception $e ) {
	$tz = new DateTimeZone( 'Asia/Kolkata' );
}
$date         = new DateTime( 'now', $tz );
$current_year = $date->format( 'Y' );
$months       = array(
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

// Prepare list tables.
require_once plugin_dir_path( __DIR__ ) . 'list-tables/class-bm-dashboard-bookings-list-table.php';

$upcoming_table = new BM_Dashboard_Bookings_List_Table( 'upcoming' );
$upcoming_table->prepare_items();

$all_table = new BM_Dashboard_Bookings_List_Table( 'all' );
$all_table->prepare_items();

$customer_count = $all_table->get_customer_count();
?>

<div class="container bm-dashboard-container">
	<div class="pagewrapper">

		<!-- ============ HEADER ============ -->
		<div class="bm-dash-header">
			<h1 class="bm-dash-title">
				<span class="dashicons dashicons-dashboard"></span>
				<?php esc_html_e( 'Booking Dashboard', 'service-booking' ); ?>
			</h1>
		</div>

		<!-- ============ KPI CARDS ============ -->
		<div class="bm-kpi-row">

			<!-- Total Bookings -->
			<div class="bm-kpi-card">
				<div class="bm-kpi-icon bm-kpi-icon--bookings"><span class="dashicons dashicons-calendar-alt"></span></div>
				<div class="bm-kpi-body">
					<span class="bm-kpi-label"><?php esc_html_e( 'Total Bookings', 'service-booking' ); ?></span>
					<span class="bm-kpi-value total_bookings_count">&mdash;</span>
					<div class="bm-kpi-legend">
						<span class="legend-dots bluedot get_booking-info" data-status="booked" data-type="total"></span>
						<small><?php esc_html_e( 'Completed', 'service-booking' ); ?></small>
						<span class="legend-dots greydot get_booking-info" data-status="pending" data-type="total"></span>
						<small><?php esc_html_e( 'Pending', 'service-booking' ); ?></small>
					</div>
				</div>
				<div class="bm-kpi-filter">
					<select class="bm-kpi-select total_year_analytics" data-type="total" onchange="bm_fetch_booking_counts(this)">
						<option value=""><?php esc_html_e( 'Year', 'service-booking' ); ?></option>
						<?php for ( $yr = $current_year - 5; $yr <= $current_year + 2; $yr++ ) : ?>
							<option value="<?php echo esc_attr( $yr ); ?>"><?php echo esc_html( $yr ); ?></option>
						<?php endfor; ?>
					</select>
					<select class="bm-kpi-select total_month_analytics" data-type="total" onchange="bm_fetch_booking_counts(this)">
						<option value=""><?php esc_html_e( 'Month', 'service-booking' ); ?></option>
						<?php foreach ( $months as $key => $month ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $month ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<!-- Upcoming Bookings -->
			<div class="bm-kpi-card">
				<div class="bm-kpi-icon bm-kpi-icon--upcoming"><span class="dashicons dashicons-clock"></span></div>
				<div class="bm-kpi-body">
					<span class="bm-kpi-label"><?php esc_html_e( 'Upcoming Bookings', 'service-booking' ); ?></span>
					<span class="bm-kpi-value upcoming_bookings_count">&mdash;</span>
					<div class="bm-kpi-legend">
						<span class="legend-dots bluedot get_booking-info" data-status="booked" data-type="upcoming"></span>
						<small><?php esc_html_e( 'Completed', 'service-booking' ); ?></small>
						<span class="legend-dots greydot get_booking-info" data-status="pending" data-type="upcoming"></span>
						<small><?php esc_html_e( 'Pending', 'service-booking' ); ?></small>
					</div>
				</div>
			</div>

			<!-- Bookings This Week -->
			<div class="bm-kpi-card">
				<div class="bm-kpi-icon bm-kpi-icon--weekly"><span class="dashicons dashicons-chart-bar"></span></div>
				<div class="bm-kpi-body">
					<span class="bm-kpi-label"><?php esc_html_e( 'Bookings This Week', 'service-booking' ); ?></span>
					<span class="bm-kpi-value weekly_bookings_count">&mdash;</span>
					<div class="bm-kpi-legend">
						<span class="legend-dots bluedot get_booking-info" data-status="booked" data-type="weekly"></span>
						<small><?php esc_html_e( 'Completed', 'service-booking' ); ?></small>
						<span class="legend-dots greydot get_booking-info" data-status="pending" data-type="weekly"></span>
						<small><?php esc_html_e( 'Pending', 'service-booking' ); ?></small>
					</div>
				</div>
			</div>

			<!-- Total Revenue -->
			<div class="bm-kpi-card">
				<div class="bm-kpi-icon bm-kpi-icon--revenue"><span class="dashicons dashicons-money-alt"></span></div>
				<div class="bm-kpi-body">
					<span class="bm-kpi-label"><?php esc_html_e( 'Total Revenue', 'service-booking' ); ?></span>
					<span class="bm-kpi-value total_bookings_revenue">&mdash;</span>
					<div class="bm-kpi-legend">
						<span class="legend-dots bluedot get_booking-info" data-status="booked" data-type="revenue"></span>
						<small><?php esc_html_e( 'Completed', 'service-booking' ); ?></small>
						<span class="legend-dots greydot get_booking-info" data-status="pending" data-type="revenue"></span>
						<small><?php esc_html_e( 'Pending', 'service-booking' ); ?></small>
					</div>
				</div>
				<div class="bm-kpi-filter">
					<select class="bm-kpi-select revenue_year_analytics" data-type="revenue" onchange="bm_fetch_booking_counts(this)">
						<option value=""><?php esc_html_e( 'Year', 'service-booking' ); ?></option>
						<?php for ( $yr = $current_year - 5; $yr <= $current_year + 2; $yr++ ) : ?>
							<option value="<?php echo esc_attr( $yr ); ?>"><?php echo esc_html( $yr ); ?></option>
						<?php endfor; ?>
					</select>
					<select class="bm-kpi-select revenue_month_analytics" data-type="revenue" onchange="bm_fetch_booking_counts(this)">
						<option value=""><?php esc_html_e( 'Month', 'service-booking' ); ?></option>
						<?php foreach ( $months as $key => $month ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $month ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

		</div><!-- .bm-kpi-row -->

		<!-- ============ ANALYTICS ROW ============ -->
		<div class="bm-analytics-row">

			<!-- Booking Status Chart -->
			<div class="bm-chart-card">
				<div class="bm-card-header">
					<h3><?php esc_html_e( 'Booking Status', 'service-booking' ); ?></h3>
					<div class="bm-chart-filters">
						<input type="date" id="bm_status_chart_from" class="bm-input-date" />
						<input type="date" id="bm_status_chart_to" class="bm-input-date" />
						<button type="button" id="bm_status_chart_filter" class="button button-primary bm-btn-sm">
							<?php esc_html_e( 'Apply', 'service-booking' ); ?>
						</button>
					</div>
				</div>
				<div class="bm-chart-body">
					<canvas id="bm_status_chart" height="260"></canvas>
				</div>
			</div>

			<!-- Quick Stats -->
			<div class="bm-stats-card">
				<div class="bm-card-header">
					<h3><?php esc_html_e( 'Quick Stats', 'service-booking' ); ?></h3>
				</div>
				<div class="bm-stats-body">
					<div class="bm-stat-item">
						<span class="dashicons dashicons-groups"></span>
						<div>
							<span class="bm-stat-number"><?php echo esc_html( $customer_count ); ?></span>
							<span class="bm-stat-label"><?php esc_html_e( 'Total Customers', 'service-booking' ); ?></span>
						</div>
					</div>
				</div>
			</div>

		</div><!-- .bm-analytics-row -->

		<!-- ============ BOOKING TABLES (Tabs) ============ -->
		<div class="bm-tables-card">
			<div class="bm-card-header bm-tabs-header">
				<button type="button" class="bm-tab-btn bm-tab-active" data-tab="upcoming-bookings-tab">
					<?php esc_html_e( 'Upcoming Bookings', 'service-booking' ); ?>
				</button>
				<button type="button" class="bm-tab-btn" data-tab="all-bookings-tab">
					<?php esc_html_e( 'All Bookings', 'service-booking' ); ?>
				</button>
			</div>

			<!-- Upcoming Bookings Tab -->
			<div id="upcoming-bookings-tab" class="bm-tab-panel bm-tab-panel-active">
				<form method="get">
					<input type="hidden" name="page" value="bm_home" />
					<input type="hidden" name="tab" value="upcoming" />
					<?php $upcoming_table->display(); ?>
				</form>
			</div>

			<!-- All Bookings Tab -->
			<div id="all-bookings-tab" class="bm-tab-panel">
				<form method="get">
					<input type="hidden" name="page" value="bm_home" />
					<input type="hidden" name="tab" value="all" />
					<?php $all_table->display(); ?>
				</form>
			</div>
		</div><!-- .bm-tables-card -->

		<!-- ============ PRO FEATURES TEASER ============ -->
		<div class="bm-pro-teaser-section">
			<div class="bm-pro-teaser-header">
				<span class="dashicons dashicons-lock"></span>
				<strong><?php esc_html_e( 'Advanced Analytics & Reports', 'service-booking' ); ?></strong>
				<span class="sg-pro-badge"><?php esc_html_e( 'PRO', 'service-booking' ); ?></span>
			</div>
			<p class="bm-pro-teaser-desc">
				<?php esc_html_e( 'Upgrade to Pro for advanced analytics with deep insights.', 'service-booking' ); ?>
			</p>

			<div class="bm-pro-features-grid">
				<div class="bm-pro-feature-item sg-pro-locked">
					<span class="dashicons dashicons-chart-area"></span>
					<span><?php esc_html_e( 'Booking Trends (daily/monthly)', 'service-booking' ); ?></span>
				</div>
				<div class="bm-pro-feature-item sg-pro-locked">
					<span class="dashicons dashicons-chart-line"></span>
					<span><?php esc_html_e( 'Revenue Per Service', 'service-booking' ); ?></span>
				</div>
				<div class="bm-pro-feature-item sg-pro-locked">
					<span class="dashicons dashicons-category"></span>
					<span><?php esc_html_e( 'Category-wise Bookings', 'service-booking' ); ?></span>
				</div>
				<div class="bm-pro-feature-item sg-pro-locked">
					<span class="dashicons dashicons-businessman"></span>
					<span><?php esc_html_e( 'Customer Analytics', 'service-booking' ); ?></span>
				</div>
				<div class="bm-pro-feature-item sg-pro-locked">
					<span class="dashicons dashicons-admin-users"></span>
					<span><?php esc_html_e( 'Returning vs New Customers', 'service-booking' ); ?></span>
				</div>
				<div class="bm-pro-feature-item sg-pro-locked">
					<span class="dashicons dashicons-filter"></span>
					<span><?php esc_html_e( 'Multi-select Filters', 'service-booking' ); ?></span>
				</div>
				<div class="bm-pro-feature-item sg-pro-locked">
					<span class="dashicons dashicons-leftright"></span>
					<span><?php esc_html_e( 'Comparative Analytics', 'service-booking' ); ?></span>
				</div>
				<div class="bm-pro-feature-item sg-pro-locked">
					<span class="dashicons dashicons-arrow-up-alt"></span>
					<span><?php esc_html_e( 'Trend Indicators (↑ ↓)', 'service-booking' ); ?></span>
				</div>
				<div class="bm-pro-feature-item sg-pro-locked">
					<span class="dashicons dashicons-dismiss"></span>
					<span><?php esc_html_e( 'Cancellation Rate', 'service-booking' ); ?></span>
				</div>
				<div class="bm-pro-feature-item sg-pro-locked">
					<span class="dashicons dashicons-performance"></span>
					<span><?php esc_html_e( 'Booking Growth %', 'service-booking' ); ?></span>
				</div>
				<div class="bm-pro-feature-item sg-pro-locked">
					<span class="dashicons dashicons-star-filled"></span>
					<span><?php esc_html_e( 'Popular Services Ranking', 'service-booking' ); ?></span>
				</div>
				<div class="bm-pro-feature-item sg-pro-locked">
					<span class="dashicons dashicons-calendar"></span>
					<span><?php esc_html_e( 'Date-wise Revenue Analysis', 'service-booking' ); ?></span>
				</div>
			</div>
		</div><!-- .bm-pro-teaser-section -->

	</div><!-- .pagewrapper -->
</div><!-- .bm-dashboard-container -->

<!-- Hidden fields for legacy JS compatibility -->
<input type="hidden" id="total_search_status" value="booked" />
<input type="hidden" id="revenue_search_status" value="booked" />
