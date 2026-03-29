<?php
/**
 * Tests for BM_Request stub methods.
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/includes/class-booking-management-request.php';

class RequestTest extends TestCase {

	/**
	 * @var BM_Request
	 */
	private $request;

	protected function setUp(): void {
		parent::setUp();
		$this->request = new BM_Request();
	}

	public function test_fetch_external_service_price_module_returns_zero(): void {
		$result = $this->request->bm_fetch_external_service_price_module_by_service_id_and_date( 1, '2024-01-01' );
		$this->assertSame( 0, $result );
	}

	public function test_fetch_external_service_price_module_returns_zero_with_defaults(): void {
		$result = $this->request->bm_fetch_external_service_price_module_by_service_id_and_date();
		$this->assertSame( 0, $result );
	}

	public function test_fetch_external_service_price_module_age_ranges_returns_empty_array(): void {
		$result = $this->request->bm_fetch_external_service_price_module_age_ranges( 1, 1 );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	public function test_fetch_price_module_data_for_order_returns_empty_array(): void {
		$result = $this->request->bm_fetch_price_module_data_for_order( 'test-key' );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}
}
