<?php
/**
 * Tests for brand_master_parse_changelog() line handling.
 *
 * @package Brand_Master
 */
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter, Squiz.Commenting.ClassComment
use PHPUnit\Framework\TestCase;

/**
 * Changelog parsing.
 */
class ChangelogTest extends TestCase {

	/**
	 * Changelog extracted from a CRLF-delimited readme.txt.
	 */
	public function test_parses_crlf_lined_changelog(): void {
		$file = BRAND_MASTER_PATH . 'tests/fixtures/changelog-crlf.txt';
		$res  = $this->run_parse( $file );
		$this->assertStringContainsString( 'First line', $res );
		$this->assertStringContainsString( 'Second line', $res );
	}

	/**
	 * Changelog extracted from a LF-delimited readme.txt.
	 */
	public function test_parses_lf_lined_changelog(): void {
		$file = BRAND_MASTER_PATH . 'tests/fixtures/changelog-lf.txt';
		$res  = $this->run_parse( $file );
		$this->assertStringContainsString( 'First line', $res );
		$this->assertStringContainsString( 'Second line', $res );
	}

	/**
	 * The full changelog is read from changelog.txt, not the WordPress.org readme.
	 */
	public function test_uses_full_changelog_by_default(): void {
		remove_all_filters( 'brand_master_changelog_file' );
		$default_file = null;
		add_filter(
			'brand_master_changelog_file',
			function ( $file ) use ( &$default_file ) {
				$default_file = $file;
				return BRAND_MASTER_PATH . 'tests/fixtures/changelog-lf.txt';
			}
		);

		$this->assertStringContainsString( 'First line', brand_master_parse_changelog() );
		$this->assertSame( BRAND_MASTER_PATH . 'changelog.txt', $default_file );
	}

	/**
	 * Run the changelog parser against a fixture by temporarily swapping the filter.
	 *
	 * @param string $file Fixture path.
	 * @return string
	 */
	private function run_parse( $file ) {
		remove_all_filters( 'brand_master_changelog_file' );
		add_filter(
			'brand_master_changelog_file',
			function () use ( $file ) {
				return $file;
			}
		);
		return brand_master_parse_changelog();
	}
}
