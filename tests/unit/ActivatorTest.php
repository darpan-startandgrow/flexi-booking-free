<?php
/**
 * Tests for Booking_Management_Activator.
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/includes/class-booking-management-activator.php';

class ActivatorTest extends TestCase {

	/**
	 * @var Booking_Management_Activator
	 */
	private $activator;

	protected function setUp(): void {
		parent::setUp();
		global $wp_test_options;
		$wp_test_options   = array();
		$this->activator   = new Booking_Management_Activator();
	}

	// ── get_db_table_name ────────────────────────────────────────────────

	/**
	 * @dataProvider validTableIdentifiersProvider
	 */
	public function test_get_db_table_name_returns_string_for_valid_identifiers( string $identifier ): void {
		$result = $this->activator->get_db_table_name( $identifier );
		$this->assertIsString( $result, "Expected a table name string for identifier '$identifier'" );
		$this->assertNotEmpty( $result );
	}

	public function validTableIdentifiersProvider(): array {
		return array(
			'SERVICE'              => array( 'SERVICE' ),
			'BOOKING'              => array( 'BOOKING' ),
			'CATEGORY'             => array( 'CATEGORY' ),
			'TIME'                 => array( 'TIME' ),
			'GALLERY'              => array( 'GALLERY' ),
			'EXTRA'                => array( 'EXTRA' ),
			'FIELDS'               => array( 'FIELDS' ),
			'BILLING_FORMS'        => array( 'BILLING_FORMS' ),
			'SLOTCOUNT'            => array( 'SLOTCOUNT' ),
			'EXTRASLOTCOUNT'       => array( 'EXTRASLOTCOUNT' ),
			'EMAIL_TMPL'           => array( 'EMAIL_TMPL' ),
			'SAVESEARCH'           => array( 'SAVESEARCH' ),
			'CUSTOMERS'            => array( 'CUSTOMERS' ),
			'TRANSACTIONS'         => array( 'TRANSACTIONS' ),
			'BOOKING_ARCHIVE'      => array( 'BOOKING_ARCHIVE' ),
			'EMAILS'               => array( 'EMAILS' ),
			'VOUCHERS'             => array( 'VOUCHERS' ),
			'CHECKIN'              => array( 'CHECKIN' ),
			'AVAILABILITY_PERIOD'  => array( 'AVAILABILITY_PERIOD' ),
			'GLOBAL_EXTRA'         => array( 'GLOBAL_EXTRA' ),
			'SERVICE_GLOBAL_EXTRA' => array( 'SERVICE_GLOBAL_EXTRA' ),
			'GLOBAL'               => array( 'GLOBAL' ),
		);
	}

	/**
	 * @dataProvider removedTableIdentifiersProvider
	 */
	public function test_get_db_table_name_returns_false_for_removed_pro_identifiers( string $identifier ): void {
		$result = $this->activator->get_db_table_name( $identifier );
		$this->assertFalse( $result, "Expected false for removed pro identifier '$identifier'" );
	}

	public function removedTableIdentifiersProvider(): array {
		return array(
			'EXTERNAL_SERVICE_PRICE_MODULE' => array( 'EXTERNAL_SERVICE_PRICE_MODULE' ),
			'COUPON'                        => array( 'COUPON' ),
			'SERVICE_CATEGORY_MAP'          => array( 'SERVICE_CATEGORY_MAP' ),
			'MANAGECOLUMNS'                 => array( 'MANAGECOLUMNS' ),
			'PDF_CUSTOMIZATION'             => array( 'PDF_CUSTOMIZATION' ),
		);
	}

	// ── get_db_table_unique_field_name ────────────────────────────────────

	/**
	 * @dataProvider removedUniqueFieldIdentifiersProvider
	 */
	public function test_get_db_table_unique_field_name_returns_false_for_removed_pro_identifiers( string $identifier ): void {
		$result = $this->activator->get_db_table_unique_field_name( $identifier );
		$this->assertFalse( $result, "Expected false for removed pro identifier '$identifier'" );
	}

	public function removedUniqueFieldIdentifiersProvider(): array {
		return array(
			'EXTERNAL_SERVICE_PRICE_MODULE' => array( 'EXTERNAL_SERVICE_PRICE_MODULE' ),
			'COUPON'                        => array( 'COUPON' ),
		);
	}

	// ── add_default_options ──────────────────────────────────────────────

	public function test_add_default_options_does_not_set_removed_options(): void {
		$this->activator->add_default_options();

		$removed = array(
			'bm_price_modules_per_page',
			'bm_allowed_stopsales',
			'bm_allowed_saleswitch',
		);

		foreach ( $removed as $option ) {
			$this->assertFalse(
				get_option( $option ),
				"Removed option '$option' should not be set by add_default_options()"
			);
		}
	}

	public function test_add_default_options_sets_expected_options(): void {
		$this->activator->add_default_options();

		$expected = array(
			'bm_show_frontend_progress_bar' => '1',
			'bm_booking_currency'           => 'EUR',
			'bm_orders_per_page'            => '10',
			'bm_services_per_page'          => '10',
			'bm_categories_per_page'        => '10',
			'bm_currency_position'          => 'before',
			'bm_booking_country'            => 'IT',
		);

		foreach ( $expected as $option => $value ) {
			$this->assertSame(
				$value,
				get_option( $option ),
				"Option '$option' should be set to '$value'"
			);
		}
	}
}
