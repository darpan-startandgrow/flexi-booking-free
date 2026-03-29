<?php
/**
 * Tests for BM_Orders_List_Table.
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/admin/list-tables/class-bm-orders-list-table.php';

class OrdersListTableTest extends TestCase {

	/**
	 * @var BM_Orders_List_Table
	 */
	private $table;

	protected function setUp(): void {
		parent::setUp();
		$this->table = new BM_Orders_List_Table();
	}

	public function test_get_columns_includes_newsletter(): void {
		$columns = $this->table->get_columns();
		$this->assertArrayHasKey( 'newsletter', $columns );
	}

	public function test_get_columns_returns_all_expected_keys(): void {
		$columns  = $this->table->get_columns();
		$expected = array(
			'cb',
			'order_id',
			'service_name',
			'booking_created_at',
			'booking_date',
			'first_name',
			'email',
			'service_cost',
			'extra_svc_cost',
			'disount_amount',
			'total_cost',
			'newsletter',
			'order_status',
			'payment_status',
			'actions',
		);

		foreach ( $expected as $key ) {
			$this->assertArrayHasKey( $key, $columns, "Column '$key' should be present" );
		}
	}

	public function test_column_default_newsletter_subscribed(): void {
		$item   = array( 'newsletter' => 1 );
		$output = $this->table->column_default( $item, 'newsletter' );
		$this->assertStringContainsString( '&#10003;', $output );
		$this->assertStringContainsString( 'sg-newsletter-yes', $output );
	}

	public function test_column_default_newsletter_unsubscribed(): void {
		$item   = array( 'newsletter' => 0 );
		$output = $this->table->column_default( $item, 'newsletter' );
		$this->assertStringContainsString( '&mdash;', $output );
		$this->assertStringContainsString( 'sg-newsletter-no', $output );
	}
}
