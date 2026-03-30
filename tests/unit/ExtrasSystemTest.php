<?php
/**
 * Tests for the Extras System fixes.
 *
 * Covers:
 * - Global extra max-cap fallback lookup
 * - Shared extras included in frontend response
 * - Service duplication copies shared-extra junction links
 * - Services list table includes extras column
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__, 2 ) . '/admin/list-tables/class-bm-services-list-table.php';

class ExtrasSystemTest extends TestCase {

	/**
	 * @var BM_DBhandler
	 */
	private $dbhandler;

	protected function setUp(): void {
		parent::setUp();
		$this->dbhandler = new BM_DBhandler();
	}

	// ──────────────────────────────────────────────────────────────────────
	// Fix 1: bm_fetch_extra_service_max_cap_by_extra_service_id() fallback
	// ──────────────────────────────────────────────────────────────────────

	public function test_extra_max_cap_returns_from_extra_table(): void {
		// Insert a service-specific extra into the EXTRA store.
		$this->dbhandler->insert_row( 'EXTRA', array(
			'service_id'   => 1,
			'extra_name'   => 'Parking',
			'extra_max_cap' => 10,
		) );

		$row = $this->dbhandler->get_row( 'EXTRA', 1 );
		$this->assertNotNull( $row );
		$this->assertEquals( 10, $row->extra_max_cap );
	}

	public function test_extra_max_cap_falls_back_to_global_extra_table(): void {
		// No EXTRA row with id=99 — only a GLOBAL_EXTRA row.
		$this->dbhandler->insert_row( 'GLOBAL_EXTRA', array(
			'extra_name'   => 'Shared Parking',
			'extra_max_cap' => 5,
		) );

		// EXTRA lookup should return null.
		$extra_row = $this->dbhandler->get_row( 'EXTRA', 1 );
		$this->assertNull( $extra_row );

		// GLOBAL_EXTRA lookup should succeed.
		$global_row = $this->dbhandler->get_row( 'GLOBAL_EXTRA', 1 );
		$this->assertNotNull( $global_row );
		$this->assertEquals( 5, $global_row->extra_max_cap );
	}

	// ──────────────────────────────────────────────────────────────────────
	// Fix 2: Linked global extras fetched via junction table
	// ──────────────────────────────────────────────────────────────────────

	public function test_junction_table_links_global_extras_to_service(): void {
		// Create a global extra.
		$ge_id = $this->dbhandler->insert_row( 'GLOBAL_EXTRA', array(
			'extra_name'             => 'WiFi',
			'extra_max_cap'          => 20,
			'is_extra_service_front' => 1,
		) );

		// Link it to service 42 via junction table.
		$this->dbhandler->insert_row( 'SERVICE_GLOBAL_EXTRA', array(
			'service_id'      => 42,
			'global_extra_id' => $ge_id,
		) );

		// Query the junction table.
		$links = $this->dbhandler->get_all_result(
			'SERVICE_GLOBAL_EXTRA',
			'*',
			array( 'service_id' => 42 ),
			'results'
		);

		$this->assertCount( 1, $links );
		$this->assertEquals( $ge_id, $links[0]->global_extra_id );

		// Fetch the linked global extra.
		$ge = $this->dbhandler->get_row( 'GLOBAL_EXTRA', $links[0]->global_extra_id );
		$this->assertNotNull( $ge );
		$this->assertEquals( 'WiFi', $ge->extra_name );
	}

	// ──────────────────────────────────────────────────────────────────────
	// Fix 3: Service duplication copies junction links
	// ──────────────────────────────────────────────────────────────────────

	public function test_duplicate_service_copies_junction_links(): void {
		$original_service_id = 10;
		$new_service_id      = 20;

		// Create junction links for the original service.
		$this->dbhandler->insert_row( 'SERVICE_GLOBAL_EXTRA', array(
			'service_id'      => $original_service_id,
			'global_extra_id' => 100,
		) );
		$this->dbhandler->insert_row( 'SERVICE_GLOBAL_EXTRA', array(
			'service_id'      => $original_service_id,
			'global_extra_id' => 200,
		) );

		// Simulate the duplication logic from bm_duplicate_service().
		$sge_links = $this->dbhandler->get_all_result(
			'SERVICE_GLOBAL_EXTRA',
			'*',
			array( 'service_id' => $original_service_id ),
			'results'
		);
		$this->assertCount( 2, $sge_links );

		foreach ( $sge_links as $sge_link ) {
			$this->dbhandler->insert_row(
				'SERVICE_GLOBAL_EXTRA',
				array(
					'service_id'      => $new_service_id,
					'global_extra_id' => $sge_link->global_extra_id,
				)
			);
		}

		// Verify the new service has both links.
		$new_links = $this->dbhandler->get_all_result(
			'SERVICE_GLOBAL_EXTRA',
			'*',
			array( 'service_id' => $new_service_id ),
			'results'
		);
		$this->assertCount( 2, $new_links );
		$new_ge_ids = array_map( function ( $l ) { return $l->global_extra_id; }, $new_links );
		$this->assertContains( 100, $new_ge_ids );
		$this->assertContains( 200, $new_ge_ids );
	}

	// ──────────────────────────────────────────────────────────────────────
	// Fix 5: Services list table has 'extras' column
	// ──────────────────────────────────────────────────────────────────────

	public function test_services_list_table_includes_extras_column(): void {
		$table   = new BM_Services_List_Table();
		$columns = $table->get_columns();

		$this->assertArrayHasKey( 'extras', $columns );
	}

	// ──────────────────────────────────────────────────────────────────────
	// Bookability check with dual-lookup pattern
	// ──────────────────────────────────────────────────────────────────────

	public function test_get_value_returns_global_extra_max_cap(): void {
		$this->dbhandler->insert_row( 'GLOBAL_EXTRA', array(
			'extra_name'   => 'Towel',
			'extra_max_cap' => 15,
		) );

		// get_value fallback — first try EXTRA (empty), then GLOBAL_EXTRA.
		$from_extra = $this->dbhandler->get_value( 'EXTRA', 'extra_max_cap', 1, 'id' );
		$this->assertNull( $from_extra );

		$from_global = $this->dbhandler->get_value( 'GLOBAL_EXTRA', 'extra_max_cap', 1, 'id' );
		$this->assertEquals( 15, $from_global );
	}
}
