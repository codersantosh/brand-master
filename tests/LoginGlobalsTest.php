<?php
/**
 * Tests for the WP 7.0 globals compatibility shim.
 *
 * Verifies that brand_master_login()::init_wp_login_globals() initializes
 * the $error and $user_login globals only when they are not already set,
 * preserving any value populated by a prior handler (lost-password POST,
 * reset-password check, registration form, error render).
 *
 * Regression: B-2 — clobbering $user_login silently broke those flows.
 *
 * @package Brand_Master
 */// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter

use PHPUnit\Framework\TestCase;

/**
 * WP 7.0 globals initialization.
 */
class LoginGlobalsTest extends TestCase {

	protected function setUp(): void {
		// Reset the legacy globals between tests.
		unset( $GLOBALS['error'], $GLOBALS['user_login'] );
	}

	public function test_init_does_not_clobber_existing_user_login() {
		// Simulate a lost-password POST: core already set $user_login to
		// the submitted value before the shim runs.
		$GLOBALS['user_login'] = 'submitted@example.com';
		$GLOBALS['error']      = 'some prior error';

		brand_master_login()->init_wp_login_globals();

		$this->assertSame( 'submitted@example.com', $GLOBALS['user_login'] );
		$this->assertSame( 'some prior error', $GLOBALS['error'] );
	}

	public function test_init_initializes_when_unset() {
		// First-time visit to /login with no prior handler: globals are
		// not yet defined; the shim must initialize them to safe empties.
		$this->assertFalse( array_key_exists( 'error', $GLOBALS ) );
		$this->assertFalse( array_key_exists( 'user_login', $GLOBALS ) );

		brand_master_login()->init_wp_login_globals();

		$this->assertSame( '', $GLOBALS['error'] );
		$this->assertSame( '', $GLOBALS['user_login'] );
	}

	public function test_init_preserves_error_overrides_user_login() {
		// Edge: a previous handler set $error but not $user_login (e.g.
		// rendering an error on a page that never had a username field).
		$GLOBALS['error'] = 'invalid_credentials';

		brand_master_login()->init_wp_login_globals();

		$this->assertSame( 'invalid_credentials', $GLOBALS['error'] );
		$this->assertSame( '', $GLOBALS['user_login'] );
	}

	public function test_init_is_hooked_to_login_head() {
		// The shim must run on every login page, not just the custom slug.
		// Without this hook, direct wp-login.php access (lost-password,
		// registration) skips initialization and triggers warnings.
		$api = brand_master_login();
		$api->run();

		$this->assertSame(
			1,
			has_action( 'login_head', array( $api, 'init_wp_login_globals' ) ),
			'init_wp_login_globals() must be hooked to login_head with priority 1.'
		);
	}
}
