<?php
/**
 * Tests for brand_master_validate_redirect().
 *
 * @package Brand_Master
 */// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter


use PHPUnit\Framework\TestCase;

/**
 * Redirect URL validation.
 */
class ValidateRedirectTest extends TestCase {

	public function test_empty_url_falls_back_to_home() {
		$this->assertSame( 'https://example.com/', brand_master_validate_redirect( '' ) );
	}

	public function test_internal_url_allowed() {
		$this->assertSame( 'https://example.com/dashboard', brand_master_validate_redirect( 'https://example.com/dashboard' ) );
	}

	public function test_relative_url_allowed() {
		$this->assertSame( '/dashboard', brand_master_validate_redirect( '/dashboard' ) );
	}

	public function test_external_url_falls_back() {
		$this->assertSame( 'https://example.com/', brand_master_validate_redirect( 'https://evil.example.net/phish' ) );
	}

	public function test_external_url_uses_custom_fallback() {
		$this->assertSame( '/fallback', brand_master_validate_redirect( 'https://evil.example.net/phish', '/fallback' ) );
	}

	public function test_open_redirect_subdomain_blocked() {
		$this->assertSame( 'https://example.com/', brand_master_validate_redirect( 'https://example.com.evil.net/x' ) );
	}
}
