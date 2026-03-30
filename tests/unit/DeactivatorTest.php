<?php
/**
 * Tests for Booking_Management_Deactivator.
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/includes/class-booking-management-deactivator.php';

class DeactivatorTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		global $wp_test_scheduled_hooks, $wp_test_cleared_hooks;
		$wp_test_scheduled_hooks = array();
		$wp_test_cleared_hooks   = array();
	}

	/**
	 * Verify that all 6 plugin cron hooks are cleared on deactivation.
	 */
	public function test_deactivate_clears_all_plugin_cron_hooks(): void {
		global $wp_test_cleared_hooks;

		Booking_Management_Deactivator::deactivate();

		$expected_hooks = array(
			'flexibooking_check_expired_book_on_request_bookings',
			'flexibooking_check_paid_expired_processing_bookings',
			'flexibooking_check_expired_pending_bookings',
			'flexibooking_check_expired_free_bookings',
			'flexibooking_check_expired_vouchers',
			'bm_resend_missing_emails_hook',
		);

		foreach ( $expected_hooks as $hook ) {
			$this->assertContains(
				$hook,
				$wp_test_cleared_hooks,
				"Expected wp_clear_scheduled_hook to be called for '$hook'"
			);
		}
	}

	/**
	 * Verify that the deactivation hook fires the sg_booking_deactivated action.
	 */
	public function test_deactivate_fires_deactivated_action(): void {
		global $wp_test_actions;
		$wp_test_actions = array();

		Booking_Management_Deactivator::deactivate();

		$this->assertArrayHasKey(
			'sg_booking_deactivated',
			$wp_test_actions,
			"Expected 'sg_booking_deactivated' action to be triggered"
		);
	}
}
