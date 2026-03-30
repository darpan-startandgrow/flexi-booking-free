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
			'price_modules'        => array( 'price_modules' ),
			'analytics'            => array( 'analytics' ),
			'coupons'              => array( 'coupons' ),
			'stop_sales'           => array( 'stop_sales' ),
			'saleswitch'           => array( 'saleswitch' ),
			'vouchers'             => array( 'vouchers' ),
			'custom_fields'        => array( 'custom_fields' ),
			'mail_templates'       => array( 'mail_templates' ),
			'qr_scanning'          => array( 'qr_scanning' ),
			'pdf_customizer'       => array( 'pdf_customizer' ),
			'smtp'                 => array( 'smtp' ),
			'multi_page_forms'     => array( 'multi_page_forms' ),
			'payment_integration'  => array( 'payment_integration' ),
			'advanced_conditional' => array( 'advanced_conditional' ),
			'ai_fields'            => array( 'ai_fields' ),
			'captcha'              => array( 'captcha' ),
			'crm_integrations'     => array( 'crm_integrations' ),
			'workflow_automation'   => array( 'workflow_automation' ),
			'premium_templates'    => array( 'premium_templates' ),
			'offline_forms'        => array( 'offline_forms' ),
			'digital_signatures'   => array( 'digital_signatures' ),
			'surveys'              => array( 'surveys' ),
			'multi_site'           => array( 'multi_site' ),
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

	// --- Tests for new Free Field Types methods ---

	public function test_free_field_types_constant_contains_expected_types(): void {
		$free_types = Booking_Management_Limits::FREE_FIELD_TYPES;
		$this->assertIsArray( $free_types );
		$this->assertContains( 'text', $free_types );
		$this->assertContains( 'email', $free_types );
		$this->assertContains( 'tel', $free_types );
		$this->assertContains( 'textarea', $free_types );
		$this->assertContains( 'select', $free_types );
		$this->assertContains( 'checkbox', $free_types );
		$this->assertContains( 'radio', $free_types );
		$this->assertContains( 'number', $free_types );
		$this->assertContains( 'date', $free_types );
		$this->assertContains( 'time', $free_types );
		$this->assertContains( 'url', $free_types );
		$this->assertContains( 'file', $free_types );
		$this->assertContains( 'hidden', $free_types );
		$this->assertContains( 'password', $free_types );
		$this->assertContains( 'gdpr_consent', $free_types );
	}

	public function test_can_add_basic_field_returns_true_for_free_types(): void {
		$this->assertTrue( Booking_Management_Limits::can_add_basic_field( 'text' ) );
		$this->assertTrue( Booking_Management_Limits::can_add_basic_field( 'email' ) );
		$this->assertTrue( Booking_Management_Limits::can_add_basic_field( 'gdpr_consent' ) );
		$this->assertTrue( Booking_Management_Limits::can_add_basic_field( 'checkbox' ) );
		$this->assertTrue( Booking_Management_Limits::can_add_basic_field( 'date' ) );
	}

	public function test_can_add_basic_field_returns_false_for_unknown_type(): void {
		$this->assertFalse( Booking_Management_Limits::can_add_basic_field( 'custom_pro_field' ) );
		$this->assertFalse( Booking_Management_Limits::can_add_basic_field( '' ) );
	}

	public function test_get_free_field_types_returns_same_as_constant(): void {
		$this->assertSame(
			Booking_Management_Limits::FREE_FIELD_TYPES,
			Booking_Management_Limits::get_free_field_types()
		);
	}

	public function test_can_add_custom_field_returns_false_without_pro(): void {
		$this->assertFalse( Booking_Management_Limits::can_add_custom_field() );
	}
}
