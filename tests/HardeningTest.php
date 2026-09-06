<?php
/**
 * Tests for the H-3/H-4/H-6 hardening items and the replace+reject
 * settings contract (endpoint requires the full settings object).
 *
 * @package Brand_Master
 */// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter

use PHPUnit\Framework\TestCase;

/**
 * Hardening regressions.
 */
class HardeningTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['__bm_options'] = array();
		$GLOBALS['__bm_caps']    = array();
		unset( $GLOBALS['error'], $GLOBALS['user_login'] );
	}

	/**
	 * Invoke the protected prepare_value() method.
	 *
	 * @param mixed $value Value to prepare.
	 * @return mixed
	 */
	private function prepare( $value ) {
		$api = Brand_Master_Api_Settings::get_instance();
		$ref = new ReflectionMethod( 'Brand_Master_Api_Settings', 'prepare_value' );
		$ref->setAccessible( true );
		return $ref->invoke( $api, $value, array() );
	}

	public function test_direct_update_option_partial_deep_merges_via_filter() {
		update_option(
			BRAND_MASTER_OPTION_NAME,
			array(
				'login'     => array(
					'url'   => array(
						'on'   => true,
						'slug' => 'member',
					),
					'title' => 'My Custom Title',
				),
				'deleteAll' => true,
			)
		);
		// Bypass the helper: a direct core write of a partial payload must
		// still preserve siblings via the pre_update_option filter.
		update_option(
			BRAND_MASTER_OPTION_NAME,
			array(
				'login' => array(
					'url' => array(
						'on' => false,
					),
				),
			)
		);

		$options = brand_master_get_options();
		$this->assertFalse( $options['login']['url']['on'] );
		$this->assertSame( 'member', $options['login']['url']['slug'] );
		$this->assertSame( 'My Custom Title', $options['login']['title'] );
		$this->assertTrue( $options['deleteAll'] );
	}

	/**
	 * The REST endpoint accepts partial payloads and deep-merges them.
	 */
	public function test_update_item_accepts_partial_and_deep_merges() {
		$GLOBALS['__bm_caps']['unfiltered_html'] = true;

		update_option(
			BRAND_MASTER_OPTION_NAME,
			array(
				'login'     => array(
					'url'   => array(
						'on'   => true,
						'slug' => 'member',
					),
					'title' => 'My Custom Title',
				),
				'deleteAll' => true,
			)
		);

		$api    = Brand_Master_Api_Settings::get_instance();
		$result = $api->update_item(
			new WP_REST_Request(
				array(
					'login' => array(
						'url' => array(
							'on' => false,
						),
					),
				)
			)
		);

		$this->assertIsArray( $result );
		$this->assertFalse( $result['login']['url']['on'] );
		$this->assertSame( 'member', $result['login']['url']['slug'] );
		$this->assertSame( 'My Custom Title', $result['login']['title'] );
		$this->assertTrue( $result['deleteAll'] );
	}

	public function test_read_gates_css_js_without_unfiltered_html() {
		$GLOBALS['__bm_caps']['unfiltered_html'] = false;
		$result                                  = $this->prepare(
			array(
				'css'   => 'body{color:red}',
				'js'    => 'alert(1)',
				'label' => 'Hi',
			)
		);
		$this->assertSame( '', $result['css'] );
		$this->assertSame( '', $result['js'] );
		$this->assertSame( 'Hi', $result['label'] );
	}

	public function test_read_preserves_css_js_with_unfiltered_html() {
		$GLOBALS['__bm_caps']['unfiltered_html'] = true;
		$result                                  = $this->prepare(
			array(
				'css' => 'body{color:red}',
				'js'  => 'alert(1)',
			)
		);
		$this->assertSame( 'body{color:red}', $result['css'] );
		$this->assertSame( 'alert(1)', $result['js'] );
		$GLOBALS['__bm_caps']['unfiltered_html'] = false;
	}

	public function test_login_css_output_neutralizes_style_breakout() {
		brand_master_update_options(
			array(
				'login' => array(
					'css' => 'a{color:red}</style><script>alert(1)</script>',
				),
			)
		);
		ob_start();
		brand_master_login()->add_login_css();
		$out = ob_get_clean();
		$this->assertStringContainsString( '<style>', $out );
		// Only the single intended closing tag may remain.
		$this->assertSame( 1, substr_count( strtolower( $out ), '</style' ) );
		$this->assertStringNotContainsStringIgnoringCase( '</script', $out );
	}

	public function test_login_js_output_neutralizes_script_breakout_but_keeps_comparisons() {
		brand_master_update_options(
			array(
				'login' => array(
					'js' => 'if(a<b){c();}</script><script>alert(1)',
				),
			)
		);
		ob_start();
		brand_master_login()->add_login_js();
		$out = ob_get_clean();
		$this->assertStringContainsString( '<script>', $out );
		// Only the single intended closing tag may remain.
		$this->assertSame( 1, substr_count( strtolower( $out ), '</script' ) );
		$this->assertStringContainsString( 'if(a<b)', $out );
	}

	public function test_include_cache_clear_picks_up_new_values() {
		if ( ! class_exists( 'Brand_Master_Include' ) ) {
			require_once BRAND_MASTER_PATH . 'includes/class-include.php';
		}
		$include = Brand_Master_Include::get_instance();
		$include->clear_settings_cache();

		brand_master_update_options( array( 'deleteAll' => false ) );
		$first = $include->get_settings();
		$this->assertFalse( $first['deleteAll'] );

		// Raw write behind the cache's back: same-request read stays stale
		// until the cache is cleared (updated_option hook in production).
		$GLOBALS['__bm_options'][ BRAND_MASTER_OPTION_NAME ]['deleteAll'] = true;
		$this->assertFalse( $include->get_settings()['deleteAll'] );
		$include->clear_settings_cache( BRAND_MASTER_OPTION_NAME );
		$this->assertTrue( $include->get_settings()['deleteAll'] );

		// Unrelated option names must not clear our cache.
		$GLOBALS['__bm_options'][ BRAND_MASTER_OPTION_NAME ]['deleteAll'] = false;
		$include->clear_settings_cache( 'some_other_option' );
		$this->assertTrue( $include->get_settings()['deleteAll'] );
		$include->clear_settings_cache();
	}
}
