<?php
/**
 * Tests for the login-related plugin settings (admin bar, post-action redirects).
 *
 * Pins the runtime behavior of each setting through its filter callback so
 * future routing changes cannot silently break them. redirect_admin() itself
 * ends in wp_safe_redirect + exit and is covered by manual E2E instead.
 *
 * @package Brand_Master
 */// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter

use PHPUnit\Framework\TestCase;

/**
 * Login settings behavior.
 */
class LoginSettingsTest extends TestCase {

	protected function setUp(): void {
		unset( $GLOBALS['__bm_actions'], $GLOBALS['__bm_filters'] );
		unset( $GLOBALS['__bm_current_user'], $GLOBALS['__bm_is_admin'], $GLOBALS['__bm_is_logged_in'] );
		update_option( BRAND_MASTER_OPTION_NAME, array() );
	}

	/**
	 * Helper: set the plugin options.
	 *
	 * @param array $options Plugin options.
	 */
	private function set_options( array $options ) {
		update_option( BRAND_MASTER_OPTION_NAME, $options );
	}

	/**
	 * Helper: set the current user roles.
	 *
	 * @param array $roles User roles.
	 */
	private function set_roles( array $roles ) {
		$GLOBALS['__bm_current_user'] = (object) array( 'roles' => $roles );
	}

	public function test_admin_bar_hidden_for_all_when_not_roles_mode() {
		$this->set_options(
			array(
				'hideAdminBar' => array(
					'on'   => true,
					'hide' => 'all',
				),
			)
		);
		brand_master_login()->disable_admin_bar();
		$this->assertFalse( apply_filters( 'show_admin_bar', true ) );
	}

	public function test_admin_bar_shown_when_off() {
		$this->set_options( array( 'hideAdminBar' => array( 'on' => false ) ) );
		brand_master_login()->disable_admin_bar();
		$this->assertTrue( apply_filters( 'show_admin_bar', true ) );
	}

	public function test_admin_bar_hidden_only_for_matching_roles() {
		$this->set_options(
			array(
				'hideAdminBar' => array(
					'on'       => true,
					'hide'     => 'roles',
					'useRoles' => array( 'subscriber' ),
				),
			)
		);

		// Role membership is evaluated when disable_admin_bar() runs
		// (after_setup_theme in production, after the user is set).
		$this->set_roles( array( 'subscriber' ) );
		brand_master_login()->disable_admin_bar();
		$this->assertFalse( apply_filters( 'show_admin_bar', true ) );

		remove_all_filters( 'show_admin_bar' );
		$this->set_roles( array( 'administrator' ) );
		brand_master_login()->disable_admin_bar();
		$this->assertTrue( apply_filters( 'show_admin_bar', true ) );
	}

	public function test_login_redirect_uses_custom_url_when_on() {
		$this->set_options(
			array(
				'redirectLogin' => array(
					'on'  => true,
					'url' => 'https://example.com/welcome',
				),
			)
		);
		$result = brand_master_login()->login_redirect( 'https://example.com/wp-admin/', '', null );
		$this->assertSame( 'https://example.com/welcome', $result );
	}

	public function test_login_redirect_passthrough_when_off() {
		$this->set_options( array( 'redirectLogin' => array( 'on' => false ) ) );
		$this->assertSame( 'https://example.com/wp-admin/', brand_master_login()->login_redirect( 'https://example.com/wp-admin/', '', null ) );
	}

	public function test_login_redirect_rejects_external_url() {
		$this->set_options(
			array(
				'redirectLogin' => array(
					'on'  => true,
					'url' => 'https://evil.example/x',
				),
			)
		);
		$result = brand_master_login()->login_redirect( 'https://example.com/wp-admin/', '', null );
		$this->assertSame( 'https://example.com/wp-admin/', $result );
	}

	public function test_logout_redirect_uses_custom_url_when_on() {
		$this->set_options(
			array(
				'redirectLogout' => array(
					'on'  => true,
					'url' => 'https://example.com/goodbye',
				),
			)
		);
		$result = brand_master_login()->logout_redirect( 'https://example.com/', '', null );
		$this->assertSame( 'https://example.com/goodbye', $result );
	}

	public function test_logout_redirect_passthrough_when_off() {
		$this->set_options( array( 'redirectLogout' => array( 'on' => false ) ) );
		$this->assertSame( 'https://example.com/', brand_master_login()->logout_redirect( 'https://example.com/', '', null ) );
	}

	public function test_lostpassword_redirect_uses_custom_url_when_on() {
		$this->set_options(
			array(
				'redirectLostPassword' => array(
					'on'  => true,
					'url' => 'https://example.com/check-email',
				),
			)
		);
		$this->assertSame( 'https://example.com/check-email', brand_master_login()->lostpassword_redirect( '' ) );
	}

	public function test_lostpassword_redirect_passthrough_when_off() {
		$this->set_options( array( 'redirectLostPassword' => array( 'on' => false ) ) );
		$this->assertSame( '', brand_master_login()->lostpassword_redirect( '' ) );
	}

	public function test_registration_redirect_uses_custom_url_when_on() {
		$this->set_options(
			array(
				'redirectRegistration' => array(
					'on'  => true,
					'url' => 'https://example.com/thanks',
				),
			)
		);
		$this->assertSame( 'https://example.com/thanks', brand_master_login()->registration_redirect( '' ) );
	}

	public function test_registration_redirect_passthrough_when_off() {
		$this->set_options( array( 'redirectRegistration' => array( 'on' => false ) ) );
		$this->assertSame( '', brand_master_login()->registration_redirect( '' ) );
	}
}
