<?php
/**
 * Tests for the manage capability helper.
 *
 * Verifies that brand_master_current_user_can_manage() accepts both
 * per-site admins and (multisite) network administrators.
 *
 * Regression: B-6 — settings page and REST endpoint were unreachable for
 * network admins on network-active multisite installs.
 *
 * @package Brand_Master
 */// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter

use PHPUnit\Framework\TestCase;

/**
 * Capability check helper.
 */
class ManageCapabilityTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['__bm_caps']  = array();
		$GLOBALS['__bm_super'] = false;
		$GLOBALS['__bm_ms']    = false;
	}

	public function test_admin_granted() {
		$GLOBALS['__bm_caps']['manage_options'] = true;
		$this->assertTrue( brand_master_current_user_can_manage() );
	}

	public function test_subscriber_denied() {
		$this->assertFalse( brand_master_current_user_can_manage() );
	}

	public function test_network_admin_granted_on_multisite() {
		$GLOBALS['__bm_ms']    = true;
		$GLOBALS['__bm_super'] = true;
		$this->assertTrue( brand_master_current_user_can_manage() );
	}

	public function test_super_admin_on_single_site_denied() {
		// is_super_admin() on a single-site install does not imply
		// manage_options for a non-admin user, so we must not grant.
		$GLOBALS['__bm_ms']    = false;
		$GLOBALS['__bm_super'] = true;
		$this->assertFalse( brand_master_current_user_can_manage() );
	}
}
