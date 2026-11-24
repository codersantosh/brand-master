<?php
/**
 * Validate a Brand Master WordPress.org release package.
 *
 * Usage: php .github/scripts/validate-release.php VERSION BUNDLE_DIR [PROJECT_ROOT]
 *
 * @package Brand_Master
 */

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "This validator must be run from the CLI.\n" );
	exit( 1 );
}

if ( $argc < 3 || $argc > 4 ) {
	fwrite( STDERR, "Usage: validate-release.php VERSION BUNDLE_DIR [PROJECT_ROOT]\n" );
	exit( 1 );
}

$expected_version   = $argv[1];
$expected_php       = '8.3';
$expected_wordpress = '7.0';
$expected_tested    = '7.1';
$bundle             = rtrim( $argv[2], '/\\' );
$project_root       = rtrim( $argv[3] ?? getcwd(), '/\\' );
$errors             = array();
$checks             = 0;

/**
 * Record a validation check.
 *
 * @param bool   $condition Pass/fail condition.
 * @param string $message   Failure message.
 * @param array  $errors    Error collector.
 * @param int    $checks    Check counter.
 */
function bm_release_check( $condition, $message, array &$errors, &$checks ) {
	++$checks;
	if ( ! $condition ) {
		$errors[] = $message;
	}
}

/**
 * Read a file when it exists.
 *
 * @param string $path File path.
 * @return string|false
 */
function bm_release_read( $path ) {
	return is_file( $path ) ? file_get_contents( $path ) : false;
}

/**
 * Read a colon-delimited metadata field.
 *
 * @param string $contents File contents.
 * @param string $field    Field name.
 * @return string
 */
function bm_release_field( $contents, $field ) {
	if ( preg_match( '/^[\s\/*#@]*' . preg_quote( $field, '/' ) . ':\h*(.+?)\h*$/mi', $contents, $matches ) ) {
		return trim( $matches[1] );
	}
	return '';
}

/**
 * Extract one version entry from a WordPress-style changelog.
 *
 * @param string $contents File contents.
 * @param string $version  Release version.
 * @return string
 */
function bm_release_changelog_entry( $contents, $version ) {
	$pattern = '/^=\h*' . preg_quote( $version, '/' ) . '\h*=\h*\R(.*?)(?=^=\h*[0-9]+\.[0-9]+(?:\.[0-9]+)*\h*=\h*$|\z)/ms';
	return preg_match( $pattern, $contents, $matches ) ? trim( $matches[1] ) : '';
}

bm_release_check(
	(bool) preg_match( '/^[0-9]+\.[0-9]+(?:\.[0-9]+)*$/', $expected_version ),
	"Invalid release version '$expected_version'.",
	$errors,
	$checks
);
bm_release_check( is_dir( $bundle ), "Bundle directory does not exist: $bundle", $errors, $checks );
bm_release_check( is_dir( $project_root ), "Project root does not exist: $project_root", $errors, $checks );

if ( ! is_dir( $bundle ) || ! is_dir( $project_root ) ) {
	foreach ( $errors as $error ) {
		fwrite( STDERR, "ERROR: $error\n" );
	}
	exit( 1 );
}

$required_files = array(
	'brand-master.php',
	'readme.txt',
	'changelog.txt',
	'LICENSE',
	'uninstall.php',
	'includes/json/patterns.json',
	'languages/brand-master.pot',
	'assets/library/atomic-css/atomic.min.css',
	'assets/library/fonts/open-sans.css',
	'assets/img/logo-20-20.png',
	'assets/img/logo.png',
	'assets/img/featured-image.png',
	'build/admin/admin.js',
	'build/admin/styles.css',
	'build/admin/admin.asset.php',
	'build/public/dashboard.js',
	'build/public/dashboard.css',
	'build/public/dashboard.asset.php',
);

foreach ( $required_files as $file ) {
	bm_release_check( is_file( "$bundle/$file" ), "Missing required runtime file: $file", $errors, $checks );
}

$main     = bm_release_read( "$bundle/brand-master.php" );
$readme   = bm_release_read( "$bundle/readme.txt" );
$changelog = bm_release_read( "$bundle/changelog.txt" );

if ( false === $main || false === $readme || false === $changelog ) {
	$errors[] = 'Could not read brand-master.php, readme.txt, or changelog.txt.';
} else {
	$required_headers = array(
		'Plugin Name',
		'Version',
		'Requires at least',
		'Requires PHP',
		'License',
		'License URI',
		'Text Domain',
		'Domain Path',
	);

	foreach ( $required_headers as $header ) {
		bm_release_check( '' !== bm_release_field( $main, $header ), "Missing plugin header: $header", $errors, $checks );
	}

	bm_release_check(
		'' === bm_release_field( $main, 'Tested up to' ),
		'Tested up to must not be declared in the plugin header; keep it in readme.txt only.',
		$errors,
		$checks
	);

	bm_release_check(
		$expected_version === bm_release_field( $main, 'Version' ),
		"Plugin header Version must be $expected_version.",
		$errors,
		$checks
	);

	$constant_pattern = '/define\(\s*[\'"]BRAND_MASTER_VERSION[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\)/';
	$constant_version = preg_match( $constant_pattern, $main, $constant_matches ) ? $constant_matches[1] : '';

	bm_release_check(
		$expected_version === $constant_version,
		"BRAND_MASTER_VERSION must be $expected_version.",
		$errors,
		$checks
	);
	bm_release_check(
		'brand-master' === bm_release_field( $main, 'Text Domain' ),
		'Plugin Text Domain must be brand-master.',
		$errors,
		$checks
	);
	bm_release_check(
		'/languages' === bm_release_field( $main, 'Domain Path' ),
		'Plugin Domain Path must be /languages.',
		$errors,
		$checks
	);

	$required_readme_fields = array(
		'Contributors',
		'Tags',
		'Requires at least',
		'Tested up to',
		'Requires PHP',
		'Stable tag',
		'License',
		'License URI',
	);

	foreach ( $required_readme_fields as $field ) {
		bm_release_check( '' !== bm_release_field( $readme, $field ), "Missing readme.txt field: $field", $errors, $checks );
	}

	bm_release_check(
		$expected_version === bm_release_field( $readme, 'Stable tag' ),
		"readme.txt Stable tag must be $expected_version.",
		$errors,
		$checks
	);

	foreach ( array( 'Requires at least', 'Requires PHP' ) as $field ) {
		bm_release_check(
			bm_release_field( $main, $field ) === bm_release_field( $readme, $field ),
			"Plugin header and readme.txt disagree for '$field'.",
			$errors,
			$checks
		);
	}

	bm_release_check(
		$expected_php === bm_release_field( $main, 'Requires PHP' ),
		"Plugin header Requires PHP must be $expected_php.",
		$errors,
		$checks
	);
	bm_release_check(
		$expected_php === bm_release_field( $readme, 'Requires PHP' ),
		"readme.txt Requires PHP must be $expected_php.",
		$errors,
		$checks
	);
	bm_release_check(
		$expected_wordpress === bm_release_field( $main, 'Requires at least' ),
		"Plugin header Requires at least must be $expected_wordpress.",
		$errors,
		$checks
	);
	bm_release_check(
		$expected_wordpress === bm_release_field( $readme, 'Requires at least' ),
		"readme.txt Requires at least must be $expected_wordpress.",
		$errors,
		$checks
	);
	bm_release_check(
		$expected_tested === bm_release_field( $readme, 'Tested up to' ),
		"readme.txt Tested up to must be $expected_tested.",
		$errors,
		$checks
	);

	bm_release_check(
		stripos( bm_release_field( $main, 'License URI' ), 'gnu.org/licenses/gpl-2.0' ) !== false
		&& stripos( bm_release_field( $readme, 'License URI' ), 'gnu.org/licenses/gpl-2.0' ) !== false,
		'Plugin header and readme.txt must link to the GPLv2 license.',
		$errors,
		$checks
	);

	$tags = array_filter( array_map( 'trim', explode( ',', bm_release_field( $readme, 'Tags' ) ) ) );
	bm_release_check( count( $tags ) <= 5, 'readme.txt must contain no more than five tags.', $errors, $checks );
	bm_release_check(
		(bool) preg_match( '/^==\h*Changelog\h*==\h*$/mi', $readme ),
		'readme.txt is missing the Changelog section.',
		$errors,
		$checks
	);
	bm_release_check(
		(bool) preg_match( '/^=\h*' . preg_quote( $expected_version, '/' ) . '\h*=\h*$/m', $readme ),
		"readme.txt is missing changelog heading '= $expected_version ='.",
		$errors,
		$checks
	);
	bm_release_check(
		1 === preg_match_all( '/^=\h*[0-9]+\.[0-9]+(?:\.[0-9]+)*\h*=\h*$/m', $readme ),
		'readme.txt must contain only the current release changelog; move older entries to changelog.txt.',
		$errors,
		$checks
	);
	bm_release_check(
		(bool) preg_match( '/^==\h*Changelog\h*==\h*$/mi', $changelog )
		&& (bool) preg_match( '/^=\h*' . preg_quote( $expected_version, '/' ) . '\h*=\h*$/m', $changelog ),
		"changelog.txt must contain the Changelog section and '= $expected_version =' heading.",
		$errors,
		$checks
	);
	bm_release_check(
		strpos( $readme, 'Full changelog: changelog.txt' ) !== false,
		'readme.txt must link to changelog.txt.',
		$errors,
		$checks
	);
	$readme_entry    = bm_release_changelog_entry( $readme, $expected_version );
	$changelog_entry = bm_release_changelog_entry( $changelog, $expected_version );
	bm_release_check(
		'' !== $readme_entry && $readme_entry === $changelog_entry,
		"The $expected_version changelog entry in readme.txt and changelog.txt must match.",
		$errors,
		$checks
	);
	bm_release_check(
		stripos( bm_release_field( $readme, 'License' ), 'GPL' ) !== false,
		'readme.txt must declare a GPL-compatible license.',
		$errors,
		$checks
	);
	bm_release_check(
		strpos( $readme, 'https://github.com/codersantosh/brand-master' ) !== false,
		'readme.txt must link to the public source repository.',
		$errors,
		$checks
	);

	foreach ( array( 'brand-master.php', 'readme.txt', 'changelog.txt', 'LICENSE', 'uninstall.php' ) as $file ) {
		$source_file = "$project_root/$file";
		$same        = is_file( $source_file )
			&& is_file( "$bundle/$file" )
			&& hash_file( 'sha256', $source_file ) === hash_file( 'sha256', "$bundle/$file" );

		bm_release_check( $same, "Packaged $file differs from the reviewed source file.", $errors, $checks );
	}
}

$package = json_decode( (string) bm_release_read( "$project_root/package.json" ), true );

bm_release_check( is_array( $package ), 'package.json could not be parsed.', $errors, $checks );
bm_release_check(
	is_array( $package ) && 'brand-master' === ( $package['name'] ?? '' ),
	'package.json name must be brand-master.',
	$errors,
	$checks
);
bm_release_check(
	is_array( $package ) && $expected_version === ( $package['version'] ?? '' ),
	"package.json version must be $expected_version.",
	$errors,
	$checks
);

$composer = json_decode( (string) bm_release_read( "$project_root/composer.json" ), true );

bm_release_check( is_array( $composer ), 'composer.json could not be parsed.', $errors, $checks );
bm_release_check(
	is_array( $composer ) && '^8.3' === ( $composer['require']['php'] ?? '' ),
	'composer.json must require PHP ^8.3.',
	$errors,
	$checks
);
bm_release_check(
	is_array( $composer ) && '8.3.0' === ( $composer['config']['platform']['php'] ?? '' ),
	'composer.json must set config.platform.php to 8.3.0.',
	$errors,
	$checks
);

$composer_lock = json_decode( (string) bm_release_read( "$project_root/composer.lock" ), true );
bm_release_check(
	is_array( $composer_lock ) && ! empty( $composer_lock['packages-dev'] ) && isset( $composer_lock['content-hash'] ),
	'composer.lock must be present and contain locked development dependencies.',
	$errors,
	$checks
);

$forbidden_directories = array( '.git', '.svn', '.github', '.wordpress-org', '.bin', 'node_modules', 'vendor', 'src', 'tests', 'deploy' );
$forbidden_files       = array(
	'.gitignore',
	'.gitattributes',
	'.distignore',
	'.babelrc',
	'.env',
	'.phpunit.result.cache',
	'composer.json',
	'composer.lock',
	'package.json',
	'package-lock.json',
	'phpcs.xml.dist',
	'phpunit.xml.dist',
	'readme.md',
	'js.pot',
	'translation-js.php',
);
$forbidden_extensions  = array( 'zip', 'tar', 'gz', 'map', 'md' );
$bundle_realpath       = realpath( $bundle );
$iterator              = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $bundle, FilesystemIterator::SKIP_DOTS )
);

foreach ( $iterator as $file ) {
	// @var SplFileInfo $file
	$relative  = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $bundle ) + 1 ) );
	$segments  = explode( '/', $relative );
	$basename  = $file->getBasename();
	$extension = strtolower( $file->getExtension() );

	bm_release_check( ! $file->isLink(), "Symlinks are not allowed in the package: $relative", $errors, $checks );
	bm_release_check( ! in_array( $basename, $forbidden_files, true ), "Development file found in package: $relative", $errors, $checks );
	bm_release_check( ! in_array( $extension, $forbidden_extensions, true ), "Forbidden file type found in package: $relative", $errors, $checks );

	foreach ( $segments as $segment ) {
		bm_release_check( ! in_array( $segment, $forbidden_directories, true ), "Development directory found in package: $relative", $errors, $checks );
	}

	if ( in_array( $extension, array( 'js', 'css', 'php' ), true ) ) {
		$contents = (string) file_get_contents( $file->getPathname() );
		foreach ( array( 'atrc-text-domain-atrc', 'atrc_prefix_atrc', 'atrc-prefix-atrc', 'ATRC_PREFIX_ATRC', 'ATRC-PREFIX-ATRC', 'Atrc-Prefix-Atrc', 'Atrc_Prefix_Atrc' ) as $placeholder ) {
			bm_release_check(
				false === stripos( $contents, $placeholder ),
				"Unreplaced ATRC placeholder '$placeholder' in $relative.",
				$errors,
				$checks
			);
		}
	}

	if ( 'php' === $extension ) {
		$contents = (string) file_get_contents( $file->getPathname() );

		if ( preg_match_all( '/BRAND_MASTER_(?:PATH|URL)\s*\.\s*[\'"]([^\'"]+)[\'"]/', $contents, $matches ) ) {
			foreach ( $matches[1] as $reference ) {
				$target = realpath( "$bundle/$reference" );
				bm_release_check(
					false !== $target && 0 === strpos( $target, $bundle_realpath . DIRECTORY_SEPARATOR ),
					"Missing runtime reference '$reference' from $relative.",
					$errors,
					$checks
				);
			}
		}
	}

	if ( 'css' === $extension && preg_match_all( '/url\(\s*[\'"]?([^\'")]+)[\'"]?\s*\)/i', (string) file_get_contents( $file->getPathname() ), $matches ) ) {
		foreach ( $matches[1] as $reference ) {
			if ( preg_match( '/^(?:data:|https?:|\/\/|#|\/)/i', $reference ) ) {
				continue;
			}

			$reference = preg_replace( '#/+#', '/', $reference );
			$target    = realpath( dirname( $file->getPathname() ) . '/' . $reference );
			bm_release_check(
				false !== $target && 0 === strpos( $target, $bundle_realpath . DIRECTORY_SEPARATOR ),
				"Missing CSS asset '$reference' from $relative.",
				$errors,
				$checks
			);
		}
	}
}

if ( $errors ) {
	foreach ( $errors as $error ) {
		fwrite( STDERR, "ERROR: $error\n" );
	}
	fwrite( STDERR, sprintf( "\nRelease validation failed with %d error(s).\n", count( $errors ) ) );
	exit( 1 );
}

printf( "Release validation passed for Brand Master %s (%d checks).\n", $expected_version, $checks );
