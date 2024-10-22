<?php // phpcs:ignore Class file names should be based on the class name with "class-" prepended.
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The login-specific functionality of the plugin.
 *
 * @link       https://patternswp.com
 * @since      1.0.0
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Login
 */

/**
 * The login-specific functionality of the plugin.
 *
 * Define and execute the hooks for overall functionalities of customization of the login page provided by this plugin..
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Login
 * @author     codersantosh <codersantosh@gmail.com>
 */
class Brand_Master_Login {

	/**
	 * Gets an instance of this object.
	 * Prevents duplicate instances which avoid artefacts and improves performance.
	 *
	 * @static
	 * @access public
	 * @return object
	 * @since 1.0.0
	 */
	public static function get_instance() {
		// Store the instance locally to avoid private static replication.
		static $instance = null;

		// Only run these methods if they haven't been ran previously.
		if ( null === $instance ) {

			$instance = new self();
		}

		// Always return the instance.
		return $instance;
	}

	/**
	 * Initialize the class and set up actions.
	 *
	 * @access public
	 * @return void
	 */
	public function run() {

		$login_settings = brand_master_include()->get_settings()['login'];

		if ( isset( $login_settings['logo']['on'] ) && $login_settings['logo']['on'] ) {

			/*Update logo url*/
			add_filter( 'login_headerurl', array( $this, 'update_login_headerurl' ), 99 );

			/*Update logo text*/
			add_filter( 'login_headertext', array( $this, 'update_login_headertext' ), 99 );

		}

		/* Update login url */
		add_filter( 'login_url', array( $this, 'update_login_url' ), 9999, 3 );
		add_action( 'template_redirect', array( $this, 'load_login_page' ), 99 );
		add_filter( 'site_url', array( $this, 'update_site_url' ), 99, 4 );
		add_action( 'wp_loaded', array( $this, 'redirect_login_and_wp_admin' ) );

		/* Update page title */
		add_filter( 'login_title', array( $this, 'update_login_title' ), 99 );

		/* Add body class */
		add_filter( 'login_body_class', array( $this, 'add_login_body_class' ), 99 );

		/*CSS/JS*/
		add_action( 'login_head', array( $this, 'add_login_css' ), 99 );
		add_action( 'login_footer', array( $this, 'add_login_js' ), 99 );

		/* Disable admin bar */
		add_action( 'after_setup_theme', array( $this, 'disable_admin_bar' ), 99 );

		/* Not allow admin end */
		add_action( 'admin_init', array( $this, 'redirect_admin' ), 99 );

		/* After login redirect */
		add_filter( 'login_redirect', array( $this, 'login_redirect' ), 99, 3 );

		/* logout redirect */
		add_filter( 'logout_redirect', array( $this, 'logout_redirect' ), 99, 3 );

		/* lost password redirect */
		add_filter( 'lostpassword_redirect', array( $this, 'lostpassword_redirect' ), 99 );

		/* Registeration redirect */
		add_filter( 'registration_redirect', array( $this, 'registration_redirect' ), 99 );
	}

	/**
	 * Get login slug
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Login slug.
	 */
	public function get_login_slug() {
		$login_settings = brand_master_include()->get_settings()['login'];

		if ( isset( $login_settings['url']['on'] ) && $login_settings['url']['on'] ) {
			if ( $login_settings['url']['slug'] ) {
				return sanitize_key( $login_settings['url']['slug'] );
			}
			return 'login';
		}
		return '';
	}


	/**
	 * Get login url set by user.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Login url.
	 */
	public function get_login_url() {

		$login_url = '';
		if ( $this->get_login_slug() ) {
			$login_url = home_url( '/' );
			if ( get_option( 'permalink_structure' ) ) {
				$login_url = $login_url . $this->get_login_slug();

			} else {
				$login_url = add_query_arg(
					array(
						$this->get_login_slug() => '',
					),
					$login_url
				);
			}
		}

		return apply_filters( 'brand_master_get_login_url', $login_url );
	}

	/**
	 * Get updated login URL.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $url URL.
	 * @return string updated URL
	 */
	public function get_updated_login_url( $url ) {
		if ( ! $this->get_login_url() ) {
			return $url;
		}

		if ( strpos( $url, 'wp-login.php' ) !== false && strpos( wp_get_referer(), 'wp-login.php' ) === false ) {
			$args = explode( '?', $url );

			/* specially for action param */
			if ( isset( $args[1] ) ) {
				parse_str( $args[1], $params );
				$url = add_query_arg( $params, $this->get_login_url() );
			} else {
				$url = $this->get_login_url();
			}
		}

		return $url;
	}

	/**
	 * Update login URL.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $login_url    The login URL. Not HTML-encoded.
	 * @param string $redirect     The path to redirect to on login, if supplied.
	 * @param bool   $force_reauth Whether to force reauthorization, even if a cookie is present.
	 *
	 * @return string updated login URL.
	 */
	public function update_login_url( $login_url, $redirect, $force_reauth ) {
		return $this->get_updated_login_url( $login_url );
	}

	/**
	 * Load login page, if the condition meets.
	 * Conditions:
	 *  must have login slug,
	 *  must of REQUEST_URI
	 *  if login url has params
	 *      request uri should have atleast one param of login
	 *  else
	 *      the the last words of login url should equal to the  request_uri
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void.
	 */
	public function load_login_page() {

		if ( $this->get_login_slug() ) {

			$login_url   = $this->get_login_url();
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			if ( $login_url && $request_uri ) {

				$is_load_page = false;

				// Check if '?' exists in the URL.
				$args_login = explode( '?', $login_url );
				if ( isset( $args_login[1] ) ) {
					parse_str( $args_login[1], $params_login );

					$params_login = array_keys( $params_login );

					$args_uri = explode( '?', $request_uri );
					if ( isset( $args_uri[1] ) ) {
						parse_str( $args_uri[1], $params_uri );

						$params_uri = array_keys( $params_uri );

						if ( array_intersect( $params_login, $params_uri ) ) {
							$is_load_page = true;
						}
					}
				} else {
					// Check if '?' exists in the URL.
					$has_request_uri_param = strpos( $request_uri, '?' );

					// Get the substring before '?' or use the entire URL if '?' is not found.
					$request_uri = false !== $has_request_uri_param ? substr( $request_uri, 0, $has_request_uri_param ) : $request_uri;

					/* replace `/` */
					$login_url   = preg_replace( '/(^\/+|\/+$)/', '', $login_url );
					$request_uri = preg_replace( '/(^\/+|\/+$)/', '', $request_uri );

					// Get the length of the requrest uri.
					$substring_length = strlen( $request_uri );

					// Extract the end of the full string with the same length as the substring.
					$end_of_full_string = substr( $login_url, -$substring_length );
					if ( $end_of_full_string === $request_uri ) {
						$is_load_page = true;
					}
				}

				// Check if the extracted part matches the substring.
				if ( $is_load_page ) {
					require_once ABSPATH . 'wp-login.php';
					exit;
				}
			}
		}
	}

	/**
	 * Update site url for wp-login.php
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string      $url     The complete site URL including scheme and path.
	 * @param string      $path    Path relative to the site URL. Blank string if no path is specified.
	 * @param string|null $scheme  Scheme to give the site URL context. Accepts 'http', 'https', 'login',
	 *                             'login_post', 'admin', 'relative' or null.
	 * @param int|null    $blog_id Site ID, or null for the current site.
	 * @return string updated sire url for wp-login.php.
	 */
	public function update_site_url( $url, $path, $scheme, $blog_id ) {
		return $this->get_updated_login_url( $url );
	}


	/**
	 * Get redirect slug
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string redirect slug.
	 */
	public function get_redirect_slug() {
		$login_settings = brand_master_include()->get_settings()['login'];
		if ( isset( $login_settings['url']['on'] ) && $login_settings['url']['on'] ) {
			if ( $login_settings['url']['redirect_slug'] ) {
				return sanitize_key( $login_settings['url']['redirect_slug'] );
			}
			return '404';
		}
		return '';
	}

	/**
	 * Get redirect url set by user.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string redirect url.
	 */
	public function get_redirect_url() {

		$redirect_url = '';
		if ( $this->get_redirect_slug() ) {
			$redirect_url = home_url( '/' );
			if ( get_option( 'permalink_structure' ) ) {
				$redirect_url = $redirect_url . $this->get_redirect_slug();

			} else {
				$redirect_url = add_query_arg(
					array(
						$this->get_redirect_slug() => '',
					),
					$redirect_url
				);
			}
		}

		return apply_filters( 'brand_master_get_redirect_url', $redirect_url );
	}


	/**
	 * Has WordPress admin acess.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return boolean has access or not.
	 */
	public function has_wp_admin_access() {

		/* WordPress has its own function to access wp-admin, this is for this plugin only */
		$has_access = true;

		if ( is_admin() && // Check if the current page is in the admin area.
		! is_user_logged_in() && // Check if the user is not logged in.
		! defined( 'WP_CLI' ) && // Check if WP_CLI constant is not defined (WP-CLI command line interface).
		! defined( 'DOING_AJAX' ) && // Check if it's not an AJAX request.
		! defined( 'DOING_CRON' ) ) {
			$has_access = false;
		}

		return apply_filters( 'brand_master_has_wp_admin_access', $has_access );
	}

	/**
	 * Redirect login page or wp-admin dir to redirection url.
	 * Adjust wp-admin page again
	 *
	 * @since 1.0.0
	 * @access public

	 * @return void.
	 */
	public function redirect_login_and_wp_admin() {
		if ( $this->get_redirect_slug() ) {
			global $pagenow;

			/* Login page, no user has this page access */
			if ( 'wp-login.php' === $pagenow ) {
				wp_safe_redirect( esc_url( $this->get_redirect_url() ) );
				exit;
			}

			/* wp-admin, login user can access this */
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			if ( strpos( $request_uri, '/wp-admin' ) !== false && ! $this->has_wp_admin_access() ) {

				$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

				// Check if '?' exists in the URL.
				$has_request_uri_param = strpos( $request_uri, '?' );

				// Get the substring before '?' or use the entire URL if '?' is not found.
				$request_uri = false !== $has_request_uri_param ? substr( $request_uri, 0, $has_request_uri_param ) : $request_uri;

				/* replace `/` */
				$request_uri = preg_replace( '/(^\/+|\/+$)/', '', $request_uri );

				if ( strpos( $request_uri, '/wp-admin' ) !== false ) {
					wp_safe_redirect( esc_url( $this->get_redirect_url() ) );
					exit;
				}
			}
		}
	}

	/**
	 * Update login title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $login_title login title.
	 * @return string updated login title.
	 */
	public function update_login_title( $login_title ) {

		$login_settings = brand_master_include()->get_settings()['login'];
		if ( $login_settings['title'] ) {
			$login_title = esc_html( $login_settings['title'] );
		}

		return $login_title;
	}

	/**
	 * Update login logo url.
	 * Link on logo.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $login_header_url login logo url.
	 * @return string updated login logo url.
	 */
	public function update_login_headerurl( $login_header_url ) {

		$login_settings = brand_master_include()->get_settings()['login'];
		if ( $login_settings['logo']['url'] ) {
			$login_header_url = esc_url( $login_settings['logo']['url'] );
		}

		return $login_header_url;
	}

	/**
	 * Update login logo text.
	 * Text on logo.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $login_header_text login logo text.
	 * @return string updated login logo text.
	 */
	public function update_login_headertext( $login_header_text ) {

		$login_settings = brand_master_include()->get_settings()['login'];
		if ( $login_settings['logo']['url'] ) {
			$login_header_text = esc_html( $login_settings['logo']['text'] );
		}

		return $login_header_text;
	}

	/**
	 * Add our class on login body.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $classes login body classes.
	 * @return array updated login body classes.
	 */
	public function add_login_body_class( $classes ) {

		$classes[] = 'brand-master-login';

		return $classes;
	}

	/**
	 * Add css to login page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void.
	 */
	public function add_login_css() {

		$login_settings = brand_master_include()->get_settings()['login'];
		$custom_css     = '';
		if ( isset( $login_settings['logo']['on'] ) && $login_settings['logo']['on'] ) {
			if ( isset( $login_settings['logo']['img']['url'] ) && $login_settings['logo']['img']['url'] ) {
				$logo_image_url = esc_url( $login_settings['logo']['img']['url'] );
				$custom_css    .= ".brand-master-login #login h1 a { 
										background-image: url('$logo_image_url');
									}";
			}
		}

		if ( $login_settings['css'] ) {
			$custom_css .= $login_settings['css'];
		}
		if ( $custom_css ) {
			/* phpcs:ignore*/
			echo '<style>' . wp_strip_all_tags( $custom_css ) . '</style>';
		}
	}

	/**
	 * Add JS to login page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void.
	 */
	public function add_login_js() {

		$login_settings = brand_master_include()->get_settings()['login'];
		$custom_js      = '';
		if ( $login_settings['js'] ) {
			$custom_js .= wp_strip_all_tags( $login_settings['js'] );
		}
		if ( $custom_js ) {
			/* phpcs:ignore*/
			echo '<script>' . wp_strip_all_tags( $custom_js ) . '</script>';
		}
	}

	/**
	 * Hide admin bar on frontend.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void.
	 */
	public function disable_admin_bar() {
		$hide_admin_bar = brand_master_include()->get_settings()['hideAdminBar'];
		if ( isset( $hide_admin_bar['on'] ) && $hide_admin_bar['on'] ) {

			if ( 'roles' !== $hide_admin_bar['hide'] ) {
				add_filter( 'show_admin_bar', '__return_false' );
			} else {
				$hide_roles   = isset( $hide_admin_bar['useRoles'] ) && $hide_admin_bar['useRoles'] ? $hide_admin_bar['useRoles'] : array();
				$current_user = wp_get_current_user();

				if ( $hide_roles && array_intersect( $hide_roles, $current_user->roles ) ) {
					add_filter( 'show_admin_bar', '__return_false' );
				}
			}
		}
	}

	/**
	 * Redirect admin.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void.
	 */
	public function redirect_admin() {
		$redirect_dashboard = brand_master_include()->get_settings()['redirectAdminDashboard'];

		if ( isset( $redirect_dashboard['on'] ) && $redirect_dashboard['on'] && $redirect_dashboard['url'] ) {

			$disallowed_roles = isset( $redirect_dashboard['useRoles'] ) && $redirect_dashboard['useRoles'] ? $redirect_dashboard['useRoles'] : array( 'subscriber' );
			$disallowed_roles = array_diff( $disallowed_roles, array( 'administrator' ) );

			$current_user = wp_get_current_user();

			if ( $disallowed_roles && array_intersect( $disallowed_roles, $current_user->roles ) ) {
				wp_safe_redirect( esc_url( $redirect_dashboard['url'] ) );
				exit;
			}
		}
	}

	/**
	 * Redirect after login.
	 *
	 * @param string           $redirect_to           The redirect destination URL.
	 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
	 * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
	 * @return string redirect to url.
	 */
	public function login_redirect( $redirect_to, $requested_redirect_to, $user ) {

		$redirect_login = brand_master_include()->get_settings()['redirectLogin'];
		if ( isset( $redirect_login['on'] ) && $redirect_login['on'] && $redirect_login['url'] ) {
			$redirect_to = esc_url( $redirect_login['url'] );
		}
		return $redirect_to;
	}

	/**
	 * Redirect after logout.
	 *
	 * @param string           $redirect_to           The redirect destination URL.
	 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
	 * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
	 * @return string redirect to url.
	 */
	public function logout_redirect( $redirect_to, $requested_redirect_to, $user ) {

		$redirect_logout = brand_master_include()->get_settings()['redirectLogout'];

		if ( isset( $redirect_logout['on'] ) && $redirect_logout['on'] && $redirect_logout['url'] ) {
			$redirect_to = esc_url( $redirect_logout['url'] );
		}

		return $redirect_to;
	}


	/**
	 * Redirect after lost password.
	 *
	 * @param string $redirect_to           The redirect destination URL.
	 * @return string redirect to url.
	 */
	public function lostpassword_redirect( $redirect_to ) {

		$redirect_lost_password = brand_master_include()->get_settings()['redirectLostPassword'];

		if ( isset( $redirect_lost_password['on'] ) && $redirect_lost_password['on'] && $redirect_lost_password['url'] ) {
			$redirect_to = esc_url( $redirect_lost_password['url'] );
		}

		return $redirect_to;
	}

	/**
	 * Redirect after registration.
	 *
	 * @param string $redirect_to           The redirect destination URL.
	 * @return string redirect to url.
	 */
	public function registration_redirect( $redirect_to ) {

		$redirect_registration = brand_master_include()->get_settings()['redirectRegistration'];

		if ( isset( $redirect_registration['on'] ) && $redirect_registration['on'] && $redirect_registration['url'] ) {
			$redirect_to = esc_url( $redirect_registration['url'] );
		}

		return $redirect_to;
	}
}

if ( ! function_exists( 'brand_master_login' ) ) {
	/**
	 * Return instance of  Brand_Master_Login class
	 *
	 * @since 1.0.0
	 *
	 * @return Brand_Master_Login
	 */
	function brand_master_login() {//phpcs:ignore
		return Brand_Master_Login::get_instance();
	}
}
brand_master_login()->run();
