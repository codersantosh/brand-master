<?php
/**
 * Tests for Brand_Master_Api_Settings::validate_slugs().
 *
 * @package Brand_Master
 */
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter, Squiz.Commenting.ClassComment
use PHPUnit\Framework\TestCase;

/**
 * Login/redirect slug validation.
 */
class SlugsTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['__bm_options']      = array();
		$GLOBALS['__bm_page_by_path'] = false;
	}

	/**
	 * Invoke the protected validate_slugs() method.
	 *
	 * @param array $params Request params.
	 * @return mixed
	 */
	private function validate( $params ) {
		$api = Brand_Master_Api_Settings::get_instance();
		$ref = new ReflectionMethod( 'Brand_Master_Api_Settings', 'validate_slugs' );
		$ref->setAccessible( true );
		return $ref->invoke( $api, $params );
	}

	public function test_returns_true_when_slugs_unchanged_from_saved() {
		update_option(
			BRAND_MASTER_OPTION_NAME,
			array(
				'login' => array(
					'url' => array(
						'slug'          => 'member',
						'redirect_slug' => '404',
					),
				),
			)
		);
		$params = array(
			'login' => array(
				'url' => array(
					'slug'          => 'member',
					'redirect_slug' => '404',
				),
			),
		);
		$this->assertTrue( $this->validate( $params ) );
	}

	public function test_reserved_slug_rejected() {
		$params = array(
			'login' => array(
				'url' => array(
					'slug'          => 'wp-admin',
					'redirect_slug' => '',
				),
			),
		);
		$result = $this->validate( $params );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertSame( 'brand_master_reserved_slug', $result->errors ? array_key_first( $result->errors ) : '' );
	}

	public function test_reserved_slug_with_php_extension_normalized() {
		// sanitize_key('wp-login.php') => 'wp-loginphp'; must still match the reserved list.
		$params = array(
			'login' => array(
				'url' => array(
					'slug'          => 'wp-login.php',
					'redirect_slug' => '',
				),
			),
		);
		$result = $this->validate( $params );
		$this->assertInstanceOf( 'WP_Error', $result );
	}

	public function test_slug_in_use_by_page_rejected() {
		$page                         = new WP_Post();
		$page->post_status            = 'publish';
		$GLOBALS['__bm_page_by_path'] = $page;
		$params                       = array(
			'login' => array(
				'url' => array(
					'slug'          => 'about',
					'redirect_slug' => '',
				),
			),
		);
		$result                       = $this->validate( $params );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertSame( 'brand_master_slug_in_use', array_key_first( $result->errors ) );
	}

	public function test_login_and_redirect_same_slug_rejected() {
		$params = array(
			'login' => array(
				'url' => array(
					'slug'          => 'member',
					'redirect_slug' => 'member',
				),
			),
		);
		$result = $this->validate( $params );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertSame( 'brand_master_slug_conflict', array_key_first( $result->errors ) );
	}

	public function test_valid_new_slug_accepted() {
		$params = array(
			'login' => array(
				'url' => array(
					'slug'          => 'member',
					'redirect_slug' => '404',
				),
			),
		);
		$this->assertTrue( $this->validate( $params ) );
	}

	public function test_empty_payload_accepted() {
		$this->assertTrue( $this->validate( array() ) );
	}
}
