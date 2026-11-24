<?php
/**
 * Tests for login URL building and rewriting logic.
 *
 * @package Brand_Master
 */// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter


use PHPUnit\Framework\TestCase;

/**
 * Login URL building and rewriting.
 */
class UrlRewriteTest extends TestCase {

	/**
	 * Helper: set the plugin option.
	 *
	 * @param array $options Plugin options.
	 */
	private function set_options( array $options ) {
		update_option( BRAND_MASTER_OPTION_NAME, $options );
	}

	/**
	 * Helper: set permalink structure.
	 *
	 * @param string $structure Permalink structure.
	 */
	private function set_permalink( $structure ) {
		update_option( 'permalink_structure', $structure );
	}


	public function test_login_slug_disabled_returns_empty() {
		$this->set_options( array( 'login' => array( 'url' => array( 'on' => false ) ) ) );
		$login = brand_master_login();
		$this->assertSame( '', $login->get_login_slug() );
	}

	public function test_login_slug_default_when_on_and_empty() {
		$this->set_options(
			array(
				'login' => array(
					'url' => array(
						'on'   => true,
						'slug' => '',
					),
				),
			)
		);
		$login = brand_master_login();
		$this->assertSame( 'login', $login->get_login_slug() );
	}

	public function test_login_slug_sanitized() {
		$this->set_options(
			array(
				'login' => array(
					'url' => array(
						'on'   => true,
						'slug' => 'My Login!',
					),
				),
			)
		);
		$login = brand_master_login();
		$this->assertSame( 'mylogin', $login->get_login_slug() );
	}


	public function test_login_url_pretty_permalinks() {
		$this->set_permalink( '/%postname%/' );
		$this->set_options(
			array(
				'login' => array(
					'url' => array(
						'on'   => true,
						'slug' => 'sign-in',
					),
				),
			)
		);
		$login = brand_master_login();
		$this->assertSame( 'https://example.com/sign-in', $login->get_login_url() );
	}

	public function test_login_url_no_permalinks() {
		$this->set_permalink( '' );
			$this->set_options(
				array(
					'login' => array(
						'url' => array(
							'on'   => true,
							'slug' => 'sign-in',
						),
					),
				)
			);
		$login = brand_master_login();
		$this->assertSame( 'https://example.com/?sign-in=', $login->get_login_url() );
	}

	public function test_login_url_disabled_returns_empty() {
		$this->set_options( array( 'login' => array( 'url' => array( 'on' => false ) ) ) );
		$login = brand_master_login();
		$this->assertSame( '', $login->get_login_url() );
	}


	public function test_updated_login_url_rewrites_wp_login() {
		$this->set_permalink( '/%postname%/' );
		$this->set_options(
			array(
				'login' => array(
					'url' => array(
						'on'   => true,
						'slug' => 'sign-in',
					),
				),
			)
		);
		$login = brand_master_login();
		$input = 'https://example.com/wp-login.php?action=register';
		$this->assertSame( 'https://example.com/sign-in?action=register', $login->get_updated_login_url( $input ) );
	}

	public function test_updated_login_url_preserves_referer_login() {
		$this->set_permalink( '/%postname%/' );
		$this->set_options(
			array(
				'login' => array(
					'url' => array(
						'on'   => true,
						'slug' => 'sign-in',
					),
				),
			)
		);
		$GLOBALS['__bm_referer'] = 'https://example.com/wp-login.php';
		$login                   = brand_master_login();
		$input                   = 'https://example.com/wp-login.php';
		$this->assertSame( $input, $login->get_updated_login_url( $input ) );
		unset( $GLOBALS['__bm_referer'] );
	}

	public function test_updated_login_url_rewrites_when_referer_absent() {
		$this->set_permalink( '/%postname%/' );
		$this->set_options(
			array(
				'login' => array(
					'url' => array(
						'on'   => true,
						'slug' => 'sign-in',
					),
				),
			)
		);
		// No Referer header (first-visit GETs, privacy-stripped referrers):
		// wp_get_referer() returns false and the rewrite must still happen.
		unset( $GLOBALS['__bm_referer'] );
		$login = brand_master_login();
		$input = 'https://example.com/wp-login.php';
		$this->assertSame( 'https://example.com/sign-in', $login->get_updated_login_url( $input ) );
	}

	public function test_updated_login_url_rewrites_with_unrelated_referer() {
		$this->set_permalink( '/%postname%/' );
		$this->set_options(
			array(
				'login' => array(
					'url' => array(
						'on'   => true,
						'slug' => 'sign-in',
					),
				),
			)
		);
		$GLOBALS['__bm_referer'] = 'https://example.com/some-page';
		$login                   = brand_master_login();
		$input                   = 'https://example.com/wp-login.php';
		$this->assertSame( 'https://example.com/sign-in', $login->get_updated_login_url( $input ) );
		unset( $GLOBALS['__bm_referer'] );
	}

	public function test_updated_login_url_returns_unchanged_when_disabled() {
		$this->set_options( array( 'login' => array( 'url' => array( 'on' => false ) ) ) );
		$login = brand_master_login();
		$input = 'https://example.com/wp-login.php';
		$this->assertSame( $input, $login->get_updated_login_url( $input ) );
	}


	public function test_redirect_slug_default_404() {
		$this->set_options(
			array(
				'login' => array(
					'url' => array(
						'on'            => true,
						'redirect_slug' => '',
					),
				),
			)
		);
		$login = brand_master_login();
		$this->assertSame( '404', $login->get_redirect_slug() );
	}

	public function test_redirect_url_pretty_permalinks() {
		$this->set_permalink( '/%postname%/' );
		$this->set_options(
			array(
				'login' => array(
					'url' => array(
						'on'            => true,
						'redirect_slug' => 'home',
					),
				),
			)
		);
		$login = brand_master_login();
		$this->assertSame( 'https://example.com/home', $login->get_redirect_url() );
	}


	public function test_update_site_url_rewrites_login_path() {
		$this->set_permalink( '/%postname%/' );
		$this->set_options(
			array(
				'login' => array(
					'url' => array(
						'on'   => true,
						'slug' => 'sign-in',
					),
				),
			)
		);
		$login  = brand_master_login();
		$result = $login->update_site_url( 'https://example.com/wp-login.php', 'wp-login.php', 'login', null );
		$this->assertSame( 'https://example.com/sign-in', $result );
	}

	public function test_update_site_url_ignores_non_login_path() {
		$this->set_permalink( '/%postname%/' );
		$this->set_options(
			array(
				'login' => array(
					'url' => array(
						'on'   => true,
						'slug' => 'sign-in',
					),
				),
			)
		);
		$login  = brand_master_login();
		$input  = 'https://example.com/sample-page';
		$result = $login->update_site_url( $input, 'sample-page', null, null );
		$this->assertSame( $input, $result );
	}

	public function test_update_site_url_handles_logout_action_path() {
		$this->set_permalink( '/%postname%/' );
		$this->set_options(
			array(
				'login' => array(
					'url' => array(
						'on'   => true,
						'slug' => 'sign-in',
					),
				),
			)
		);
		$login  = brand_master_login();
		$result = $login->update_site_url( 'https://example.com/wp-login.php?action=logout', 'wp-login.php?action=logout', 'login', null );
		$this->assertSame( 'https://example.com/sign-in?action=logout', $result );
	}


	public function test_admin_access_denied_for_frontend() {
		$GLOBALS['__bm_is_admin']     = false;
		$GLOBALS['__bm_is_logged_in'] = false;
		$login                        = brand_master_login();
		$this->assertTrue( $login->has_wp_admin_access() );
	}

	public function test_admin_access_denied_when_not_logged_in_in_admin() {
		$GLOBALS['__bm_is_admin']     = true;
		$GLOBALS['__bm_is_logged_in'] = false;
		$login                        = brand_master_login();
		$this->assertFalse( $login->has_wp_admin_access() );
	}

	public function test_admin_access_allowed_when_logged_in() {
		$GLOBALS['__bm_is_admin']     = true;
		$GLOBALS['__bm_is_logged_in'] = true;
		$login                        = brand_master_login();
		$this->assertTrue( $login->has_wp_admin_access() );
	}
}
