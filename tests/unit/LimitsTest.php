<?php
/**
 * Tests for Booking_Management_Limits.
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/includes/class-booking-management-limits.php';

class LimitsTest extends TestCase {

	/**
	 * Pro feature limit messages should exist for expected feature keys.
	 *
	 * @dataProvider proFeatureKeysProvider
	 */
	public function test_limit_message_exists_for_expected_features( string $feature ): void {
		$message = Booking_Management_Limits::get_limit_message( $feature );
		$this->assertIsString( $message );
		$this->assertNotEmpty( $message );
		// Should NOT be the generic fallback.
		$this->assertStringNotContainsString(
			'This feature requires SG Flexi Booking Pro.',
			$message,
			"Feature '$feature' should have a specific limit message, not the generic fallback"
		);
	}

	public function proFeatureKeysProvider(): array {
		return array(
			'price_modules'     => array( 'price_modules' ),
			'analytics'         => array( 'analytics' ),
			'coupons'           => array( 'coupons' ),
			'stop_sales'        => array( 'stop_sales' ),
			'saleswitch'        => array( 'saleswitch' ),
			'vouchers'          => array( 'vouchers' ),
			'custom_fields'     => array( 'custom_fields' ),
			'mail_templates'    => array( 'mail_templates' ),
			'qr_scanning'       => array( 'qr_scanning' ),
			'pdf_customizer'    => array( 'pdf_customizer' ),
			'smtp'              => array( 'smtp' ),
		);
	}

	public function test_unknown_feature_returns_generic_fallback(): void {
		$message = Booking_Management_Limits::get_limit_message( 'nonexistent_feature_xyz' );
		$this->assertSame( 'This feature requires SG Flexi Booking Pro.', $message );
	}

	public function test_is_pro_active_returns_false_by_default(): void {
		// apply_filters stub returns the passed value (false).
		$this->assertFalse( Booking_Management_Limits::is_pro_active() );
	}

	public function test_can_use_price_modules_returns_false_without_pro(): void {
		$this->assertFalse( Booking_Management_Limits::can_use_price_modules() );
	}

	public function test_can_use_full_analytics_returns_false_without_pro(): void {
		$this->assertFalse( Booking_Management_Limits::can_use_full_analytics() );
	}

	public function test_can_use_coupons_returns_false_without_pro(): void {
		$this->assertFalse( Booking_Management_Limits::can_use_coupons() );
	}
}
