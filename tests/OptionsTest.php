<?php
/**
 * Tests for option get/update helpers.
 *
 * @package Brand_Master
 */// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter


use PHPUnit\Framework\TestCase;

/**
 * Options merge/fallback behavior.
 */
class OptionsTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['__bm_options'] = array();
	}

	public function test_returns_defaults_when_nothing_saved() {
		$options = brand_master_get_options();
		$this->assertArrayHasKey( 'login', $options );
		$this->assertFalse( $options['login']['url']['on'] );
		$this->assertSame( 'login', $options['login']['url']['slug'] );
		$this->assertFalse( $options['deleteAll'] );
	}

	public function test_saved_values_merge_over_defaults() {
		update_option( BRAND_MASTER_OPTION_NAME, array( 'deleteAll' => true ) );
		$options = brand_master_get_options();
		$this->assertTrue( $options['deleteAll'] );
		// Unset keys still fall back to defaults.
		$this->assertArrayHasKey( 'dashboard', $options );
	}

	public function test_key_lookup_returns_saved_value() {
		update_option(
			BRAND_MASTER_OPTION_NAME,
			array(
				'redirectLogout' => array(
					'on'  => true,
					'url' => 'https://example.com/bye',
				),
			)
		);
		$redirect = brand_master_get_options( 'redirectLogout' );
		$this->assertTrue( $redirect['on'] );
		$this->assertSame( 'https://example.com/bye', $redirect['url'] );
	}

	public function test_key_lookup_falls_back_to_defaults() {
		$this->assertSame( array(), brand_master_get_options( 'nonexistent_key' )['menu']['items'] ?? array() );
		$this->assertArrayHasKey( 'on', brand_master_get_options( 'hideAdminBar' ) );
	}

	public function test_key_lookup_returns_false_for_unknown_key() {
		$this->assertFalse( brand_master_get_options( 'totally_unknown' ) );
	}

	public function test_update_options_string_key() {
		brand_master_update_options( 'deleteAll', true );
		$this->assertTrue( brand_master_get_options( 'deleteAll' ) );
	}

	public function test_update_options_array() {
		brand_master_update_options(
			array(
				'redirectLogin' => array(
					'on'  => true,
					'url' => '/welcome',
				),
			)
		);
		$this->assertTrue( brand_master_get_options( 'redirectLogin' )['on'] );
	}

	/**
	 * Regression: B-1 (data loss on partial update).
	 *
	 * Pre-populate the options row with a fully populated, non-default value
	 * under one nested key, then call brand_master_update_options() with a
	 * partial nested payload. Sibling keys and previously saved non-default
	 * values must survive the write.
	 */
	public function test_partial_update_preserves_siblings() {
		brand_master_update_options(
			array(
				'login'    => array(
					'url'   => array(
						'on'   => true,
						'slug' => 'member',
					),
					'title' => 'My Custom Title',
					'css'   => 'body{color:red}',
				),
				'dashboard' => array(
					'noLoginContent' => 42,
				),
				'deleteAll' => true,
			)
		);

		brand_master_update_options(
			array(
				'login' => array(
					'url' => array(
						'on' => false,
					),
				),
			)
		);

		$options = brand_master_get_options();
		// Sibling login.* values must survive the partial update.
		$this->assertSame( 'member', $options['login']['url']['slug'] );
		$this->assertSame( 'My Custom Title', $options['login']['title'] );
		$this->assertSame( 'body{color:red}', $options['login']['css'] );
		// Dashboard and deleteAll must survive the partial update.
		$this->assertSame( 42, $options['dashboard']['noLoginContent'] );
		$this->assertTrue( $options['deleteAll'] );
		// The value we just sent must be reflected.
		$this->assertFalse( $options['login']['url']['on'] );
	}

	/**
	 * Regression: B-1 — list (numeric-indexed) arrays are replaced, not merged
	 * per index. Menu/social items collections are re-sent as full lists.
	 */
	public function test_partial_update_replaces_list_arrays_wholesale() {
		brand_master_update_options(
			array(
				'dashboard' => array(
					'menu' => array(
						'items' => array(
							array(
								'label' => 'Old',
								'slug'  => 'old',
							),
						),
					),
				),
			)
		);

		brand_master_update_options(
			array(
				'dashboard' => array(
					'menu' => array(
						'items' => array(
							array(
								'label' => 'New',
								'slug'  => 'new',
							),
						),
					),
				),
			)
		);

		$options = brand_master_get_options();
		$this->assertCount( 1, $options['dashboard']['menu']['items'] );
		$this->assertSame( 'New', $options['dashboard']['menu']['items'][0]['label'] );
	}

	/**
	 * Brand_master_deep_merge() unit checks.
	 */
	public function test_deep_merge_replaces_scalars_and_recurses_into_assoc() {
		$base   = array(
			'a' => 1,
			'b' => array(
				'x' => 'old',
				'y' => 'keep',
			),
		);
		$update = array(
			'a' => 2,
			'b' => array(
				'x' => 'new',
			),
			'c' => 3,
		);
		$result = brand_master_deep_merge( $base, $update );
		$this->assertSame( 2, $result['a'] );
		$this->assertSame( 'new', $result['b']['x'] );
		$this->assertSame( 'keep', $result['b']['y'] );
		$this->assertSame( 3, $result['c'] );
	}

	public function test_deep_merge_replaces_list_arrays() {
		$base   = array(
			'items' => array( 1, 2, 3 ),
		);
		$update = array(
			'items' => array( 9 ),
		);
		$result = brand_master_deep_merge( $base, $update );
		$this->assertSame( array( 9 ), $result['items'] );
	}
}
