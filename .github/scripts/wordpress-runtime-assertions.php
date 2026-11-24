<?php
/**
 * Runtime assertions loaded by WP-CLI inside the disposable smoke-test site.
 *
 * @package Brand_Master
 */

$phase = getenv( 'BM_SMOKE_PHASE' );

if ( 'initial' === $phase ) {
	$failures = array();

	if ( ! in_array( 'brand-master/brand-master.php', get_option( 'active_plugins', array() ), true ) ) {
		$failures[] = 'Plugin is not active';
	}
	if ( BRAND_MASTER_VERSION !== getenv( 'BM_EXPECTED_VERSION' ) ) {
		$failures[] = 'Plugin version constant is incorrect';
	}
	if ( ! is_array( brand_master_get_options() ) ) {
		$failures[] = 'Settings defaults are unavailable';
	}
	if ( false === strpos( brand_master_parse_changelog(), 'Raised minimum PHP requirement to 8.3' ) ) {
		$failures[] = 'Full changelog is unavailable to the admin reader';
	}
	if ( ! shortcode_exists( 'brand_master_dashboard' ) || ! shortcode_exists( 'brand_master_login' ) ) {
		$failures[] = 'Shortcodes are not registered';
	}
	if ( false === strpos( do_shortcode( '[brand_master_dashboard /]' ), 'bm-dashboard' ) ) {
		$failures[] = 'Dashboard shortcode did not render';
	}
	if ( false === strpos( do_shortcode( '[brand_master_login /]' ), 'login-action-login' ) ) {
		$failures[] = 'Login shortcode did not render';
	}

	do_action( 'rest_api_init' );
	$routes = rest_get_server()->get_routes();
	if ( ! isset( $routes['/brand-master/v1/settings'] ) ) {
		$failures[] = 'Missing REST route /brand-master/v1/settings';
	}

	if ( $failures ) {
		throw new RuntimeException( implode( '; ', $failures ) );
	}

	echo "WordPress runtime assertions passed.\n";
} elseif ( 'persistence' === $phase ) {
	$options = brand_master_get_options();
	if ( 'Brand Master Runtime Smoke' !== $options['login']['title'] || empty( $options['login']['url']['on'] ) ) {
		throw new RuntimeException(
			'Settings were not preserved across reactivation: ' . wp_json_encode(
				array(
					'title'      => $options['login']['title'] ?? null,
					'login_url'  => $options['login']['url'] ?? null,
					'delete_all' => $options['deleteAll'] ?? null,
				)
			)
		);
	}

	echo "Settings persistence assertions passed.\n";
} else {
	throw new RuntimeException( 'BM_SMOKE_PHASE must be initial or persistence.' );
}
