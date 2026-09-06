<?php
/**
 * Tests for the WP globals compatibility shim.
 *
 * Verifies that brand_master_login()::init_wp_login_globals() initializes
 * the $error and $user_login globals only when they are not already set,
 * preserving any value populated by a prior handler (lost-password POST,
 * reset-password check, registration form, error render).
 *
 * Regression: B-2 — clobbering $user_login silently broke those flows.
 * Regression: method-scope include — wp-login.php runs inside
 * load_login_page()'s local symbol table, so bare $user_login / $error reads
 * warn unless the method aliases them to $GLOBALS via `global`.
 *
 * @package Brand_Master
 */// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter, Generic.Files.OneObjectStructurePerFile

use PHPUnit\Framework\TestCase;

/**
 * Helper whose method scope simulates load_login_page(): an include inside
 * a method inherits the method's local symbol table, not global scope.
 */
class LoginGlobalsScopeHelper {
	/**
	 * Include the scope fixture with the same global binding as load_login_page().
	 *
	 * @param string $fixture Absolute path to the fixture file.
	 * @return array Rendered values read from the fixture.
	 */
	public function load_fixture( $fixture ) {
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited -- mirrors load_login_page() scope binding.
		global $error, $interim_login, $action, $user_login;
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

		brand_master_login()->init_wp_login_globals();

		include $fixture;

		return array(
			'rendered_user' => $rendered_user,
			'can_autofocus' => $can_autofocus,
		);
	}
}

/**
 * WP globals initialization.
 */
class LoginGlobalsTest extends TestCase {

	protected function setUp(): void {
		// Reset the legacy globals between tests.
		unset( $GLOBALS['error'], $GLOBALS['user_login'], $GLOBALS['action'], $GLOBALS['interim_login'] );
		// Reset stub hook registries so run() does not accumulate duplicates.
		unset( $GLOBALS['__bm_actions'], $GLOBALS['__bm_filters'] );
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

	public function test_init_is_hooked_to_login_init() {
		// The shim must run on every login page, not just the custom slug.
		// Without this hook, direct wp-login.php access (lost-password,
		// registration) skips initialization and triggers warnings.
		$api = brand_master_login();
		$api->run();

		$this->assertSame(
			1,
			has_action( 'login_init', array( $api, 'init_wp_login_globals' ) ),
			'init_wp_login_globals() must be hooked to login_init with priority 1.'
		);
	}

	public function test_method_scope_include_emits_no_warnings() {
		// Simulate wp-login.php running inside a method symbol table: bare
		// $user_login / $error reads must resolve via the global binding
		// without E_WARNING, and $action / $interim_login writes must reach
		// $GLOBALS for login_header() / login_footer().
		$warnings = array();
		// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- intentional test-only warning capture, restored in finally.
		set_error_handler(
			function ( $errno, $errstr ) use ( &$warnings ) {
				if ( E_WARNING === $errno ) {
					$warnings[] = $errstr;
				}
				return true;
			},
			E_WARNING
		);
		// phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler

		try {
			$helper = new LoginGlobalsScopeHelper();
			$result = $helper->load_fixture( __DIR__ . '/fixtures/login-scope-fixture.php' );
		} finally {
			restore_error_handler();
		}

		$this->assertCount( 0, $warnings, 'Method-scope include must not emit E_WARNING.' );
		$this->assertSame( '', $result['rendered_user'] );
		$this->assertTrue( $result['can_autofocus'] );
		$this->assertSame( 'custom_action', $GLOBALS['action'] );
		$this->assertSame( 'success', $GLOBALS['interim_login'] );
	}
}
