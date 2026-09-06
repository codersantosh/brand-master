<?php
/**
 * PHPUnit bootstrap.
 *
 * Loads minimal WordPress function/class stubs so the plugin's pure logic can
 * be unit-tested without a full WordPress install. Integration behavior is
 * covered by the CI workflow's full WordPress test suite step.
 *
 * @package Brand_Master
 * @since   1.0.6
 */
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, Squiz.Commenting.FunctionComment.Missing, Squiz.Commenting.FileComment.Missing, Generic.CodeAnalysis.UnusedFunctionParameter, Universal.NamingConventions.NoReservedKeywordParameterNames, WordPress.WP.AlternativeFunctions, Universal.Operators.DisallowShortTernary, Universal.Files.SeparateFunctionsFromOO, Squiz.PHP.EmbeddedPhp, Squiz.Commenting.VariableComment.Missing, WordPress.WP.GlobalVariablesOverride, Generic.NamingConventions.CamelCapsFunctionName, Squiz.Classes.ClassFileName, Squiz.Classes.ClassDeclaration, Generic.Files.OneObjectStructurePerFile, WordPress.NamingConventions.ValidFunctionName


define( 'ABSPATH', sys_get_temp_dir() . '/' );
define( 'BRAND_MASTER_PATH', dirname( __DIR__ ) . '/' );
define( 'BRAND_MASTER_URL', 'https://example.com/wp-content/plugins/brand-master/' );
define( 'BRAND_MASTER_VERSION', 'test' );
define( 'BRAND_MASTER_PLUGIN_NAME', 'brand-master' );
define( 'BRAND_MASTER_OPTION_NAME', 'brand_master_options' );
define( 'OBJECT', 'OBJECT' );
define( 'ARRAY_A', 'ARRAY_A' );
define( 'ARRAY_N', 'ARRAY_N' );


// phpcs:disable Squiz.Commenting.FunctionComment.Missing -- lightweight test stubs.

$GLOBALS['__bm_options'] = array();

function get_option( $name, $default = false ) {
	return isset( $GLOBALS['__bm_options'][ $name ] ) ? $GLOBALS['__bm_options'][ $name ] : $default;
}
function update_option( $name, $value ) {
	$old = isset( $GLOBALS['__bm_options'][ $name ] ) ? $GLOBALS['__bm_options'][ $name ] : false;
	// Emulate WP core: update_option() applies the pre_update_option_{$option}
	// filter before writing, so the plugin's deep-merge filter is exercised.
	$value                            = apply_filters( 'pre_update_option_' . $name, $value, $name, $old );
	$GLOBALS['__bm_options'][ $name ] = $value;
	if ( function_exists( 'do_action' ) ) {
		do_action( 'updated_option', $name, $old, $value );
	}
	return true;
}
function delete_option( $name ) {
	unset( $GLOBALS['__bm_options'][ $name ] );
	return true;
}
function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	$GLOBALS['__bm_filters'][ $tag ][ $priority ][] = $callback;
	return true;
}
function apply_filters( $tag, $value, ...$args ) {
	if ( empty( $GLOBALS['__bm_filters'][ $tag ] ) ) {
		return $value;
	}
	ksort( $GLOBALS['__bm_filters'][ $tag ] );
	foreach ( $GLOBALS['__bm_filters'][ $tag ] as $callbacks ) {
		foreach ( $callbacks as $cb ) {
			$value = call_user_func_array( $cb, array_merge( array( $value ), $args ) );
		}
	}
	return $value;
}
function remove_all_filters( $tag ) {
	unset( $GLOBALS['__bm_filters'][ $tag ] );
}
function __( $text, $domain = 'default' ) {
	return $text;
}
function esc_html__( $text, $domain = 'default' ) {
	return $text;
}
function esc_html( $text ) {
	return $text;
}
function WP_Filesystem() {
	return true;
}
function wp_kses_post( $string ) {
	return $string;
}

function esc_url_raw( $url ) {
	$url = trim( (string) $url );
	return filter_var( $url, FILTER_SANITIZE_URL ) ? $url : '';
}
function esc_url( $url ) {
	return esc_url_raw( $url );
}
function wp_strip_all_tags( $string, $remove_breaks = false ) {
	$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $string );
	$string = strip_tags( $string );
	if ( $remove_breaks ) {
		$string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
	}
	return trim( $string );
}
function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
}
function sanitize_text_field( $str ) {
	return trim( preg_replace( '/<[^>]*>/', '', (string) $str ) );
}
function home_url( $path = '' ) {
	return 'https://example.com/' . ltrim( (string) $path, '/' );
}
function wp_validate_redirect( $location, $fallback = '' ) {
	// Simplified core behavior: allow only same-host URLs.
	$location_host = wp_parse_url( $location, PHP_URL_HOST );
	$home_host     = wp_parse_url( home_url(), PHP_URL_HOST );
	if ( null === $location_host || $location_host === $home_host ) {
		return $location;
	}
	return $fallback;
}
function wp_parse_url( $url, $component = -1 ) {
	if ( PHP_URL_HOST === $component ) {
		$host = parse_url( $url, PHP_URL_HOST );
		return $host ? $host : null;
	}
	return parse_url( $url, $component );
}
function current_user_can( $cap, ...$args ) {
	return ! empty( $GLOBALS['__bm_caps'][ $cap ] );
}
function is_multisite() {
	return ! empty( $GLOBALS['__bm_ms'] );
}
function is_super_admin( $user_id = false ) {
	return ! empty( $GLOBALS['__bm_super'] );
}
function wp_unslash( $value ) {
	return is_string( $value ) ? stripslashes( $value ) : $value;
}
function brand_master_esc_svg( $svg ) {
	return $svg; // Not under test in the sanitization suite; real kses behavior is covered elsewhere.
}
function wp_get_referer() {
	return isset( $GLOBALS['__bm_referer'] ) ? $GLOBALS['__bm_referer'] : false;
}
function add_query_arg( $args, $url ) {
	if ( is_array( $args ) ) {
		$query = http_build_query( $args );
		$glue  = ( false === strpos( $url, '?' ) ) ? '?' : '&';
		return $url . $glue . $query;
	}
	return $url;
}
function is_admin() {
	return ! empty( $GLOBALS['__bm_is_admin'] );
}
function is_user_logged_in() {
	return ! empty( $GLOBALS['__bm_is_logged_in'] );
}
function wp_get_current_user() {
	return isset( $GLOBALS['__bm_current_user'] ) ? $GLOBALS['__bm_current_user'] : (object) array( 'roles' => array() );
}
function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	$GLOBALS['__bm_actions'][ $tag ][ $priority ][] = $callback;
	return true;
}
function has_action( $tag, $callback = false ) {
	if ( empty( $GLOBALS['__bm_actions'][ $tag ] ) ) {
		return false;
	}
	// Check for any callback on this tag.
	if ( false === $callback ) {
		return true;
	}
	// Check each priority for the specific callback.
	foreach ( $GLOBALS['__bm_actions'][ $tag ] as $priority => $callbacks ) {
		foreach ( $callbacks as $cb ) {
			if ( $cb === $callback ) {
				return $priority;
			}
		}
	}
	return false;
}
function do_action( $tag, ...$args ) {
	if ( empty( $GLOBALS['__bm_actions'][ $tag ] ) ) {
		return;
	}
	ksort( $GLOBALS['__bm_actions'][ $tag ] );
	foreach ( $GLOBALS['__bm_actions'][ $tag ] as $callbacks ) {
		foreach ( $callbacks as $cb ) {
			call_user_func_array( $cb, array_slice( $args, 0, 99 ) );
		}
	}
}
function rest_api_init() {}
function __return_false() {
	return false;
}
function __return_true() {
	return true;
}

// Minimal WP test doubles.
class WP_Error {
	public $errors = array();
	public function __construct( $code = '', $message = '' ) {
		$this->errors[ $code ][] = $message;
	}
}
class WP_REST_Request {
	private $params = array();
	public function __construct( $params = array() ) {
		$this->params = $params;
	}
	public function get_params() {
		return $this->params;
	}
}
class WP_Post {
	public $ID;
	public $post_status;
}
function get_page_by_path( $page_path, $output = OBJECT, $post_type = 'page' ) {
	return isset( $GLOBALS['__bm_page_by_path'] ) ? $GLOBALS['__bm_page_by_path'] : false;
}
function is_wp_error( $thing ) {
	return $thing instanceof WP_Error;
}
function rest_sanitize_value_from_schema( $value, $schema ) {
	return $value;
}
function rest_validate_value_from_schema( $value, $schema ) {
	return $value;
}
function register_rest_route( ...$args ) {
	return true;
}

$GLOBALS['wp_filesystem'] = new class() {
	public function is_readable( $file ) {
		return file_exists( $file );
	}
	public function get_contents( $file ) {
		return file_get_contents( $file );
	}
};

// Minimal parent controller so the API classes can be loaded.
class WP_REST_Controller {
	public function add_additional_fields_schema( $schema ) {
		return $schema;
	}
}

require BRAND_MASTER_PATH . 'includes/functions.php';
require BRAND_MASTER_PATH . 'includes/api/class-api.php';
require BRAND_MASTER_PATH . 'includes/api/class-api-settings.php';
require BRAND_MASTER_PATH . 'admin/class-admin.php';

// Minimal include stub so Brand_Master_Login can be loaded and tested.
require BRAND_MASTER_PATH . 'public/class-login.php';
if ( ! function_exists( 'brand_master_include' ) ) {
	function brand_master_include() {
		return new class() {
			public function get_settings() {
				return brand_master_get_options();
			}
		};
	}
}
