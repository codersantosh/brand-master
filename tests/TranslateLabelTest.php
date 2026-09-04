<?php
/**
 * Tests for brand_master_translate_default_label().
 *
 * @package Brand_Master
 */// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter


use PHPUnit\Framework\TestCase;

/**
 * Default label translation helper.
 */
class TranslateLabelTest extends TestCase {

	public function test_default_logout_translates() {
		$this->assertSame( 'Logout', brand_master_translate_default_label( 'Logout', 'Logout' ) );
	}

	public function test_default_navigations_translates() {
		$this->assertSame( 'Navigations', brand_master_translate_default_label( 'Navigations', 'Navigations' ) );
	}

	public function test_default_social_translates() {
		$this->assertSame( 'Social', brand_master_translate_default_label( 'Social', 'Social' ) );
	}

	public function test_custom_label_passes_through() {
		$this->assertSame( 'My Custom Label', brand_master_translate_default_label( 'My Custom Label', 'Navigations' ) );
	}

	public function test_unknown_fallback_passes_through() {
		$this->assertSame( 'Something Else', brand_master_translate_default_label( 'Something Else', 'UnknownDefault' ) );
	}
}
