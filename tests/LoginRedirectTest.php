<?php
/**
 * Tests for custom login page matching and wp-login.php redirect decisions.
 *
 * Covers is_login_page_request() (exact slug matching, extracted from
 * load_login_page() for testability) and should_redirect_wp_login() (the
 * allowlist that keeps logout and password-reset flows on wp-login.php).
 *
 * @package Brand_Master
 */// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter

use PHPUnit\Framework\TestCase;

/**
 * Login page matching and redirect allowlist.
 */
class LoginRedirectTest extends TestCase {

	/**
	 * Original request method, restored after each test.
	 *
	 * @var string|null
	 */
	private $orig_method;

	/**
	 * Whether $_REQUEST had an action before the test.
	 *
	 * @var bool
	 */
	private $orig_action_exists;

	/**
	 * Original $_REQUEST action, restored after each test.
	 *
	 * @var mixed
	 */
	private $orig_action;

	protected function setUp(): void {
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification -- test-harness superglobal backup/restore; no data processed or persisted.
		$this->orig_method        = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : null;
		$this->orig_action_exists = array_key_exists( 'action', $_REQUEST );
		$this->orig_action        = $this->orig_action_exists ? $_REQUEST['action'] : null;
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
		unset( $GLOBALS['__bm_actions'], $GLOBALS['__bm_filters'] );
	}

	protected function tearDown(): void {
		if ( null === $this->orig_method ) {
			unset( $_SERVER['REQUEST_METHOD'] );
		} else {
			$_SERVER['REQUEST_METHOD'] = $this->orig_method;
		}
		if ( $this->orig_action_exists ) {
			$_REQUEST['action'] = $this->orig_action;
		} else {
			unset( $_REQUEST['action'] );
		}
	}

	/**
	 * Helper: set the request context for should_redirect_wp_login().
	 *
	 * @param string|null $method HTTP method, null to leave unset.
	 * @param string|null $action Request action, null to leave unset.
	 */
	private function set_request( $method, $action ) {
		if ( null === $method ) {
			unset( $_SERVER['REQUEST_METHOD'] );
		} else {
			$_SERVER['REQUEST_METHOD'] = $method;
		}
		if ( null === $action ) {
			unset( $_REQUEST['action'] );
		} else {
			$_REQUEST['action'] = $action;
		}
	}

	public function test_slug_matches_exact_path() {
		$login = brand_master_login();
		$this->assertTrue( $login->is_login_page_request( 'https://example.com/sign-in', '/sign-in' ) );
	}

	public function test_slug_matches_with_trailing_slashes() {
		$login = brand_master_login();
		$this->assertTrue( $login->is_login_page_request( 'https://example.com/sign-in/', '/sign-in/' ) );
	}

	public function test_slug_matches_request_with_query_string() {
		$login = brand_master_login();
		$this->assertTrue( $login->is_login_page_request( 'https://example.com/sign-in', '/sign-in?foo=bar' ) );
	}

	public function test_slug_matches_subdirectory_install() {
		$login = brand_master_login();
		$this->assertTrue( $login->is_login_page_request( 'https://example.com/blog/sign-in', '/blog/sign-in' ) );
	}

	public function test_slug_rejects_tail_match() {
		// Regression: a suffix comparison matched mere tails of the slug
		// ("/ogin" matched slug "login") and loaded the login page there.
		$login = brand_master_login();
		$this->assertFalse( $login->is_login_page_request( 'https://example.com/login', '/ogin' ) );
		$this->assertFalse( $login->is_login_page_request( 'https://example.com/login', '/n' ) );
	}

	public function test_slug_rejects_unrelated_path() {
		$login = brand_master_login();
		$this->assertFalse( $login->is_login_page_request( 'https://example.com/sign-in', '/about' ) );
		$this->assertFalse( $login->is_login_page_request( 'https://example.com/sign-in', '/' ) );
	}

	public function test_plain_permalinks_match_login_param() {
		$login = brand_master_login();
		$this->assertTrue( $login->is_login_page_request( 'https://example.com/?sign-in=', '/?sign-in=' ) );
	}

	public function test_plain_permalinks_reject_other_params() {
		$login = brand_master_login();
		$this->assertFalse( $login->is_login_page_request( 'https://example.com/?sign-in=', '/?other=' ) );
		$this->assertFalse( $login->is_login_page_request( 'https://example.com/?sign-in=', '/' ) );
	}

	public function test_redirects_bare_and_login_gets() {
		$login = brand_master_login();
		$this->set_request( 'GET', null );
		$this->assertTrue( $login->should_redirect_wp_login() );
		$this->set_request( 'GET', 'login' );
		$this->assertTrue( $login->should_redirect_wp_login() );
	}

	public function test_redirects_register_and_lostpassword_gets() {
		// Generated links for these already point at the custom slug via
		// the login_url/site_url filters; direct hits stay hidden.
		$login = brand_master_login();
		$this->set_request( 'GET', 'register' );
		$this->assertTrue( $login->should_redirect_wp_login() );
		$this->set_request( 'GET', 'lostpassword' );
		$this->assertTrue( $login->should_redirect_wp_login() );
	}

	public function test_passthrough_for_logout_and_reset_key_flows() {
		$login = brand_master_login();
		$this->set_request( 'GET', 'logout' );
		$this->assertFalse( $login->should_redirect_wp_login() );
		$this->set_request( 'GET', 'rp' );
		$this->assertFalse( $login->should_redirect_wp_login() );
		$this->set_request( 'GET', 'resetpass' );
		$this->assertFalse( $login->should_redirect_wp_login() );
		$this->set_request( 'GET', 'postpass' );
		$this->assertFalse( $login->should_redirect_wp_login() );
	}

	public function test_passthrough_for_all_posts() {
		// A redirect would discard the POST payload, so no POST is redirected.
		$login = brand_master_login();
		$this->set_request( 'POST', null );
		$this->assertFalse( $login->should_redirect_wp_login() );
		$this->set_request( 'POST', 'login' );
		$this->assertFalse( $login->should_redirect_wp_login() );
		$this->set_request( 'POST', 'lostpassword' );
		$this->assertFalse( $login->should_redirect_wp_login() );
	}

	public function test_defaults_to_get_when_method_unset() {
		$login = brand_master_login();
		$this->set_request( null, null );
		$this->assertTrue( $login->should_redirect_wp_login() );
	}
}
