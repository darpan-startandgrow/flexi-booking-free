<?php
/**
 * Tests for BM_Sanitizer.
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/includes/class-booking-management-sanitized.php';

class SanitizedTest extends TestCase {

	/**
	 * @var BM_Sanitizer
	 */
	private $sanitizer;

	protected function setUp(): void {
		parent::setUp();
		$this->sanitizer = new BM_Sanitizer();
	}

	/**
	 * Removed pro fields should not be recognized by the booking sanitizer,
	 * falling through to the default case (sanitize_text_field).
	 *
	 * @dataProvider removedFieldsProvider
	 */
	public function test_removed_fields_are_not_specially_handled( string $field ): void {
		$raw    = '  <b>test</b>  ';
		$result = $this->sanitizer->get_sanitized_booking_field( $field, $raw );

		// These fields should hit the default branch which calls sanitize_text_field.
		$this->assertSame(
			sanitize_text_field( $raw ),
			$result,
			"Removed field '$field' should fall through to default sanitization"
		);
	}

	public function removedFieldsProvider(): array {
		return array(
			'external_price_module'       => array( 'external_price_module' ),
			'default_saleswitch'          => array( 'default_saleswitch' ),
			'default_stopsales'           => array( 'default_stopsales' ),
			'variable_svc_price_modules'  => array( 'variable_svc_price_modules' ),
			'variable_saleswitch'         => array( 'variable_saleswitch' ),
			'variable_stopsales'          => array( 'variable_stopsales' ),
		);
	}

	/**
	 * Known fields that remain in the free version should still be sanitized.
	 *
	 * @dataProvider remainingFieldsProvider
	 */
	public function test_remaining_fields_are_sanitized( string $field ): void {
		$raw    = '  <script>alert(1)</script>hello  ';
		$result = $this->sanitizer->get_sanitized_booking_field( $field, $raw );
		$this->assertIsString( $result );
		// Should not contain the raw script tag after sanitization.
		$this->assertStringNotContainsString( '<script>', $result );
	}

	public function remainingFieldsProvider(): array {
		return array(
			'newsletter'    => array( 'newsletter' ),
			'service_name'  => array( 'service_name' ),
			'order_status'  => array( 'order_status' ),
			'booking_date'  => array( 'booking_date' ),
			'total_cost'    => array( 'total_cost' ),
			'email'         => array( 'email' ),
		);
	}

	public function test_get_sanitized_service_field_sanitizes_service_name(): void {
		$raw    = '  <b>My Service</b>  ';
		$result = $this->sanitizer->get_sanitized_service_field( 'service_name', $raw );
		$this->assertSame( 'My Service', $result );
	}

	/**
	 * The top-level dispatcher should route to the correct per-identifier method.
	 */
	public function test_get_sanitized_fields_routes_to_booking(): void {
		$result = $this->sanitizer->get_sanitized_fields( 'booking', 'newsletter', '1' );
		$this->assertSame( '1', $result );
	}
}
