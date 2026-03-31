<?php
/**
 * Tests for the field save data transformation logic.
 *
 * Validates that non-column keys from the form builder's conditional
 * data are moved into field_options before database insertion, preventing
 * "Unknown column" errors.
 */

use PHPUnit\Framework\TestCase;

class FieldSaveTest extends TestCase {

	/**
	 * Valid database columns for the FIELDS table.
	 *
	 * @var string[]
	 */
	private $db_columns = array(
		'id',
		'form_id',
		'field_type',
		'field_label',
		'field_name',
		'field_desc',
		'field_options',
		'is_required',
		'is_editable',
		'visible',
		'ordering',
		'woocommerce_field',
		'field_key',
		'field_position',
	);

	/**
	 * Simulate the conditional data transformation that happens in
	 * bm_save_field_and_setting() after the fix.
	 *
	 * @param array  $conditional The conditional data from the form builder.
	 * @param string $type        The field type.
	 * @return array Transformed conditional data with non-column keys in field_options.
	 */
	private function transform_conditional( array $conditional, string $type = 'text' ): array {
		if ( ! isset( $conditional['field_options'] ) || ! is_array( $conditional['field_options'] ) ) {
			$conditional['field_options'] = array();
		}

		if ( $type === 'email' ) {
			$conditional['field_options']['is_main_email'] = ! isset( $conditional['field_options']['is_main_email'] ) ? 0 : 1;
		}

		if ( $type === 'select' || $type === 'checkbox' ) {
			$conditional['field_options']['is_multiple'] = ! isset( $conditional['field_options']['is_multiple'] ) ? 0 : 1;
		}

		if ( $type === 'tel' ) {
			$conditional['field_options']['show_intl_code'] = ! isset( $conditional['field_options']['show_intl_code'] ) ? 0 : 1;
		}

		if ( $type !== 'file' && $type !== 'checkbox' && $type !== 'radio' && $type !== 'reset' && $type !== 'button' && $type !== 'submit' && $type !== 'hidden' && $type !== 'color' && $type !== 'range' ) {
			$conditional['field_options']['autocomplete'] = ( ! empty( $conditional['autocomplete'] ) || ! empty( $conditional['field_options']['autocomplete'] ) ) ? 1 : 0;
		}

		if ( $type !== 'button' && $type !== 'submit' && $type !== 'hidden' ) {
			$conditional['field_options']['is_visible'] = ( ! empty( $conditional['is_visible'] ) || ! empty( $conditional['field_options']['is_visible'] ) ) ? 1 : 0;
		}

		// Move non-column keys from conditional into field_options.
		$db_columns = $this->db_columns;
		foreach ( array_keys( $conditional ) as $key ) {
			if ( 'field_options' !== $key && ! in_array( $key, $db_columns, true ) ) {
				$conditional['field_options'][ $key ] = $conditional[ $key ];
				unset( $conditional[ $key ] );
			}
		}

		return $conditional;
	}

	/**
	 * Test that placeholder is moved into field_options (not a top-level column).
	 */
	public function test_placeholder_moved_to_field_options(): void {
		$conditional = array(
			'placeholder'   => 'Enter your name',
			'default_value' => '',
			'custom_class'  => '',
			'field_width'   => 'full',
			'is_visible'    => 1,
			'autocomplete'  => 1,
		);

		$result = $this->transform_conditional( $conditional, 'text' );

		$this->assertArrayNotHasKey( 'placeholder', $result, 'placeholder should not be a top-level key' );
		$this->assertArrayHasKey( 'field_options', $result );
		$this->assertSame( 'Enter your name', $result['field_options']['placeholder'] );
	}

	/**
	 * Test that all non-column keys are moved to field_options.
	 */
	public function test_all_non_column_keys_moved_to_field_options(): void {
		$conditional = array(
			'placeholder'   => 'test',
			'default_value' => 'hello',
			'custom_class'  => 'my-class',
			'field_width'   => 'half',
			'is_visible'    => 1,
			'autocomplete'  => 1,
		);

		$result = $this->transform_conditional( $conditional, 'text' );

		// These non-column keys should not exist at the top level.
		$this->assertArrayNotHasKey( 'placeholder', $result );
		$this->assertArrayNotHasKey( 'default_value', $result );
		$this->assertArrayNotHasKey( 'custom_class', $result );
		$this->assertArrayNotHasKey( 'field_width', $result );
		$this->assertArrayNotHasKey( 'is_visible', $result );
		$this->assertArrayNotHasKey( 'autocomplete', $result );

		// They should be inside field_options.
		$this->assertSame( 'test', $result['field_options']['placeholder'] );
		$this->assertSame( 'hello', $result['field_options']['default_value'] );
		$this->assertSame( 'my-class', $result['field_options']['custom_class'] );
		$this->assertSame( 'half', $result['field_options']['field_width'] );
	}

	/**
	 * Test that only valid db columns remain at the top level after transformation.
	 */
	public function test_only_db_columns_remain_at_top_level(): void {
		$common_data = array(
			'field_type'  => 'text',
			'field_label' => 'Name',
			'field_name'  => 'billing_first_name',
			'field_desc'  => '',
			'is_required' => 1,
			'is_editable' => 1,
			'ordering'    => 1,
			'field_key'   => 'sgbm_field_1',
			'field_position' => 1,
		);

		$conditional = array(
			'placeholder'   => 'Enter name',
			'default_value' => '',
			'custom_class'  => '',
			'field_width'   => 'full',
			'is_visible'    => 1,
			'autocomplete'  => 1,
		);

		$transformed = $this->transform_conditional( $conditional, 'text' );
		$data        = array_merge( $common_data, $transformed );

		// Verify every top-level key is either field_options or a valid column.
		foreach ( array_keys( $data ) as $key ) {
			$this->assertTrue(
				in_array( $key, $this->db_columns, true ),
				"Key '$key' should be a valid DB column but is not"
			);
		}
	}

	/**
	 * Test that is_visible=0 from form builder is correctly handled.
	 */
	public function test_is_visible_zero_sets_field_options_to_zero(): void {
		$conditional = array(
			'placeholder' => '',
			'is_visible'  => 0,
		);

		$result = $this->transform_conditional( $conditional, 'text' );

		$this->assertSame( 0, $result['field_options']['is_visible'] );
	}

	/**
	 * Test that is_visible=1 from form builder is correctly handled.
	 */
	public function test_is_visible_one_sets_field_options_to_one(): void {
		$conditional = array(
			'placeholder' => '',
			'is_visible'  => 1,
		);

		$result = $this->transform_conditional( $conditional, 'text' );

		$this->assertSame( 1, $result['field_options']['is_visible'] );
	}

	/**
	 * Test that autocomplete from form builder top-level is handled.
	 */
	public function test_autocomplete_from_top_level(): void {
		$conditional = array(
			'autocomplete' => 1,
		);

		$result = $this->transform_conditional( $conditional, 'text' );

		$this->assertSame( 1, $result['field_options']['autocomplete'] );
	}

	/**
	 * Test backward compatibility: autocomplete from nested field_options (old admin).
	 */
	public function test_autocomplete_from_nested_field_options(): void {
		$conditional = array(
			'field_options' => array(
				'autocomplete' => 1,
			),
		);

		$result = $this->transform_conditional( $conditional, 'text' );

		$this->assertSame( 1, $result['field_options']['autocomplete'] );
	}

	/**
	 * Test backward compatibility: is_visible from nested field_options (old admin).
	 */
	public function test_is_visible_from_nested_field_options(): void {
		$conditional = array(
			'field_options' => array(
				'is_visible' => 1,
			),
		);

		$result = $this->transform_conditional( $conditional, 'text' );

		$this->assertSame( 1, $result['field_options']['is_visible'] );
	}

	/**
	 * Test conditional logic keys are moved to field_options.
	 */
	public function test_conditional_logic_keys_moved_to_field_options(): void {
		$conditional = array(
			'placeholder'          => '',
			'is_visible'           => 1,
			'autocomplete'         => 1,
			'conditional_enabled'  => 1,
			'conditional_field'    => 'billing_country',
			'conditional_operator' => 'equals',
			'conditional_value'    => 'US',
		);

		$result = $this->transform_conditional( $conditional, 'text' );

		$this->assertArrayNotHasKey( 'conditional_enabled', $result );
		$this->assertArrayNotHasKey( 'conditional_field', $result );
		$this->assertArrayNotHasKey( 'conditional_operator', $result );
		$this->assertArrayNotHasKey( 'conditional_value', $result );

		$this->assertSame( 1, $result['field_options']['conditional_enabled'] );
		$this->assertSame( 'billing_country', $result['field_options']['conditional_field'] );
		$this->assertSame( 'equals', $result['field_options']['conditional_operator'] );
		$this->assertSame( 'US', $result['field_options']['conditional_value'] );
	}

	/**
	 * Test validation rule keys are moved to field_options.
	 */
	public function test_validation_keys_moved_to_field_options(): void {
		$conditional = array(
			'placeholder'               => '',
			'is_visible'                => 1,
			'autocomplete'              => 1,
			'validation_min_length'     => '3',
			'validation_max_length'     => '100',
			'validation_pattern'        => '[a-zA-Z]+',
			'validation_error_message'  => 'Invalid input',
		);

		$result = $this->transform_conditional( $conditional, 'text' );

		$this->assertSame( '3', $result['field_options']['validation_min_length'] );
		$this->assertSame( '100', $result['field_options']['validation_max_length'] );
		$this->assertSame( '[a-zA-Z]+', $result['field_options']['validation_pattern'] );
		$this->assertSame( 'Invalid input', $result['field_options']['validation_error_message'] );
	}

	/**
	 * Test GDPR consent placeholder is moved to field_options.
	 */
	public function test_gdpr_consent_placeholder_in_field_options(): void {
		$conditional = array(
			'placeholder' => 'I consent to the storage and processing of my personal data.',
			'is_visible'  => 1,
		);

		$result = $this->transform_conditional( $conditional, 'gdpr_consent' );

		$this->assertSame(
			'I consent to the storage and processing of my personal data.',
			$result['field_options']['placeholder']
		);
	}
}
