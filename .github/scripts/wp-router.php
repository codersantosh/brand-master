<?php
/**
 * Router for the WordPress runtime smoke test's PHP development server.
 *
 * Existing files are served directly. All other requests are handed to
 * WordPress so permalinks, REST routes, and the custom login slug are exercised.
 */

$root = getenv( 'BM_WORDPRESS_DIR' );
if ( ! $root ) {
	fwrite( STDERR, "BM_WORDPRESS_DIR is required.\n" );
	http_response_code( 500 );
	exit( 1 );
}

$request_path = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
$candidate    = $root . $request_path;

if ( '/' !== $request_path && is_file( $candidate ) ) {
	return false;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF']    = '/index.php';
require $root . '/index.php';
