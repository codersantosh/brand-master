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
}
