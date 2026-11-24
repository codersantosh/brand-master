<?php
/**
 * Tests for Brand_Master_Api_Settings::deep_sanitize() via reflection.
 *
 * @package Brand_Master
 */// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter


use PHPUnit\Framework\TestCase;

/**
 * Deep sanitization of saved settings.
 */
class DeepSanitizeTest extends TestCase {

	/**
	 * Invoke the protected deep_sanitize() method.
	 *
	 * @param mixed $value Value to sanitize.
	 * @return mixed
	 */
	private function sanitize( $value ) {
		$api = Brand_Master_Api_Settings::get_instance();
		$ref = new ReflectionMethod( 'Brand_Master_Api_Settings', 'deep_sanitize' );
		$ref->setAccessible( true );
		return $ref->invoke( $api, $value );
	}

	public function test_strings_are_sanitized() {
		$result = $this->sanitize( array( 'label' => '<script>alert(1)</script>Hello' ) );
		$this->assertSame( 'alert(1)Hello', $result['label'] );
	}

	public function test_slugs_are_key_sanitized() {
		$this->assertSame( 'mylogin', $this->sanitize( array( 'slug' => 'My Login!' ) )['slug'] );
	}

	public function test_urls_are_raw_escaped() {
		$this->assertSame( 'https://example.com/a?b=1', $this->sanitize( array( 'url' => ' https://example.com/a?b=1 ' ) )['url'] );
	}

	public function test_css_blocked_without_unfiltered_html() {
		$GLOBALS['__bm_caps']['unfiltered_html'] = false;
		$this->assertSame( array( 'css' => '' ), $this->sanitize( array( 'css' => 'body{color:red}' ) ) );
	}

	public function test_css_allowed_with_unfiltered_html() {
		$GLOBALS['__bm_caps']['unfiltered_html'] = true;
		$this->assertSame( array( 'css' => 'body{color:red}' ), $this->sanitize( array( 'css' => 'body{color:red}' ) ) );
		$GLOBALS['__bm_caps']['unfiltered_html'] = false;
	}

	public function test_non_strings_pass_through() {
		$this->assertSame(
			array(
				'on'    => true,
				'count' => 3,
			),
			$this->sanitize(
				array(
					'on'    => true,
					'count' => 3,
				)
			)
		);
	}

	public function test_nested_arrays_are_sanitized_recursively() {
		$input  = array(
			'menu' => array(
				'items' => array(
					array(
						'label' => '<b>Hi</b>',
						'slug'  => 'A B',
					),
				),
			),
		);
		$result = $this->sanitize( $input );
		$this->assertSame( 'Hi', $result['menu']['items'][0]['label'] );
		$this->assertSame( 'ab', $result['menu']['items'][0]['slug'] );
	}
}
