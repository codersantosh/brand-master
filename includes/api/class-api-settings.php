<?php // phpcs:ignore Class file names should be based on the class name with "class-" prepended.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class used to manage a plugin's settings via the REST API.
 *
 * @link       https://patternswp.com
 * @since      1.0.0
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Brand_Master_Api_Settings
 */

/**
 * Plugin's settings via the REST API.
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Brand_Master_Api_Settings
 * @author     codersantosh <codersantosh@gmail.com>
 *
 * @see Brand_Master_Api
 */

if ( ! class_exists( 'Brand_Master_Api_Settings' ) ) {

	/**
	 * Brand_Master_Api_Settings
	 *
	 * @package Brand_Master
	 * @since 1.0.0
	 */
	class Brand_Master_Api_Settings extends Brand_Master_Api {

		/**
		 * Initialize the class and set up actions.
		 *
		 * @access public
		 * @return void
		 */
		public function run() {
			$this->type      = 'brand_master_api_settings';
			$this->rest_base = 'settings';

			/*Custom Rest Routes*/
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}

		/**
		 * Register REST API route.
		 *
		 * @since    1.0.0
		 */
		public function register_routes() {
			$namespace = $this->namespace . $this->version;

			register_rest_route(
				$namespace,
				'/' . $this->rest_base,
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item' ),
						'args'                => array(),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
					),
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'callback'            => array( $this, 'update_item' ),
						'args'                => rest_get_endpoint_args_for_schema( $this->get_item_schema(), WP_REST_Server::EDITABLE ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);
		}

		/**
		 * Checks if a given request has access to read and manage settings.
		 *
		 * Network administrators are allowed in addition to per-site admins so
		 * the settings page and REST endpoint remain reachable on multisite
		 * network-active installs.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return bool True if the request has read access for the item, otherwise false.
		 */
		public function get_item_permissions_check( $request ) {
			return brand_master_current_user_can_manage();
		}


		/**
		 * Retrieves the settings.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return array|WP_Error Array on success, or WP_Error object on failure.
		 */
		public function get_item( $request ) {
			$response = array();

			$saved_options = brand_master_get_options();

			$schema = $this->get_registered_schema();

			$response = $this->prepare_value( $saved_options, $schema );

			return $response;
		}

		/**
		 * Prepares a value for output based off a schema array.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $value  Value to prepare.
		 * @param array $schema Schema to match.
		 * @return mixed The prepared value.
		 */
		protected function prepare_value( $value, $schema ) {

			$sanitized_value = rest_sanitize_value_from_schema( $value, $schema );

			/*
			 * Deep-sanitize saved strings. rest_sanitize_value_from_schema() handles types and
			 * format:uri fields, but plain strings pass through untouched, so everything else
			 * is normalized here by key.
			 */
			$sanitized_value = $this->deep_sanitize( $sanitized_value );

			return $sanitized_value;
		}

		/**
		 * Recursively sanitize option values by key.
		 *
		 * @since 1.0.6
		 *
		 * @param mixed $value Option value (array or scalar).
		 * @return mixed Sanitized value.
		 */
		protected function deep_sanitize( $value ) {
			if ( ! is_array( $value ) ) {
				return $value;
			}

			foreach ( $value as $key => $val ) {
				if ( is_array( $val ) ) {
					$value[ $key ] = $this->deep_sanitize( $val );
					continue;
				}
				if ( ! is_string( $val ) ) {
					continue;
				}
				if ( in_array( $key, array( 'slug', 'redirect_slug' ), true ) ) {
					$value[ $key ] = sanitize_key( $val );
				} elseif ( 'css' === $key || 'js' === $key ) {
					/*
					 * Raw code fields: only users with unfiltered_html (typically single-site
					 * admins) may store them verbatim. This is a defense-in-depth trust boundary,
					 * not an unauthenticated XSS surface (saving requires manage_options + nonce).
					 */
					if ( ! current_user_can( 'unfiltered_html' ) ) {
						$value[ $key ] = '';
					}
				} elseif ( 'svg' === $key ) {
					$value[ $key ] = brand_master_esc_svg( $val );
				} elseif ( 'url' === $key ) {
					$value[ $key ] = esc_url_raw( $val );
				} else {
					$value[ $key ] = sanitize_text_field( $val );
				}
			}

			return $value;
		}


		/**
		 * Updates settings.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 * @return array|WP_Error Array on success, or error object on failure.
		 */
		public function update_item( $request ) {
			$schema = $this->get_registered_schema();

			$params = $request->get_params();

			if ( is_wp_error( rest_validate_value_from_schema( $params, $schema ) ) ) {
				return new WP_Error(
					'rest_invalid_stored_value',
					/* translators: %s: Property name. */
					sprintf( __( 'The %s property has an invalid stored value, and cannot be updated to null.', 'brand-master' ), BRAND_MASTER_OPTION_NAME ),
					array( 'status' => 400 )
				);
			}

			/* Validate login slugs against reserved words and existing content before saving. */
			$slug_error = $this->validate_slugs( $params );
			if ( is_wp_error( $slug_error ) ) {
				return $slug_error;
			}
			$sanitized_options = $this->prepare_value( $params, $schema );
			brand_master_update_options( $sanitized_options );

			return $this->get_item( $request );
		}


		/**
		 * Validate login/redirect slugs against reserved words and existing content.
		 *
		 * @since 1.0.6
		 *
		 * @param array $params Request params.
		 * @return true|WP_Error True if valid, WP_Error otherwise.
		 */
		protected function validate_slugs( $params ) {
			/* Compare using the same normalization (sanitize_key) applied to user input. */
			$reserved = array_map( 'sanitize_key', array( 'wp-admin', 'wp-login.php', 'wp-json', 'wp-content', 'wp-includes', 'admin', 'feed', 'cgi-bin' ) );

			$slugs         = array();
			$saved_options = brand_master_get_options();

			$saved_login_slug = isset( $saved_options['login']['url']['slug'] ) ? sanitize_key( $saved_options['login']['url']['slug'] ) : '';
			$new_login_slug   = isset( $params['login']['url']['slug'] ) ? sanitize_key( $params['login']['url']['slug'] ) : '';
			if ( $new_login_slug && $new_login_slug !== $saved_login_slug ) {
				$slugs['login'] = $new_login_slug;
			}
			$saved_redirect_slug = isset( $saved_options['login']['url']['redirect_slug'] ) ? sanitize_key( $saved_options['login']['url']['redirect_slug'] ) : '';
			$new_redirect_slug   = isset( $params['login']['url']['redirect_slug'] ) ? sanitize_key( $params['login']['url']['redirect_slug'] ) : '';
			if ( $new_redirect_slug && $new_redirect_slug !== $saved_redirect_slug ) {
				$slugs['redirect'] = $new_redirect_slug;
			}

			foreach ( $slugs as $context => $slug ) {
				if ( in_array( $slug, $reserved, true ) ) {
					return new WP_Error(
						'brand_master_reserved_slug',
						sprintf( /* translators: %s: slug. */ esc_html__( 'The slug %s is reserved and cannot be used.', 'brand-master' ), '<code>' . esc_html( $slug ) . '</code>' ),
						array( 'status' => 400 )
					);
				}
				/* Existing published page with the same path? */
				$page = get_page_by_path( $slug );
				if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
					return new WP_Error(
						'brand_master_slug_in_use',
						sprintf( /* translators: %s: slug. */ esc_html__( 'The slug %s is already in use by an existing page.', 'brand-master' ), '<code>' . esc_html( $slug ) . '</code>' ),
						array( 'status' => 400 )
					);
				}
			}

			if ( isset( $slugs['login'], $slugs['redirect'] ) && $slugs['login'] === $slugs['redirect'] ) {
				return new WP_Error(
					'brand_master_slug_conflict',
					esc_html__( 'Login slug and redirect slug cannot be the same.', 'brand-master' ),
					array( 'status' => 400 )
				);
			}

			return true;
		}

		/**
		 * Retrieves all of the registered options for the Settings API.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of registered options.
		 */
		protected function get_registered_schema() {
			// Use a static variable to cache the schema.
			static $cached_schema = null;

			// If the schema is already cached, return it.
			if ( null !== $cached_schema ) {
				return $cached_schema;
			}

			// If not cached, fetch the value and cache it.
			$schema = brand_master_admin()->get_settings_schema();

			// Cache the schema in the static variable.
			$cached_schema = $schema;

			return $schema;
		}


		/**
		 * Retrieves the site setting schema, conforming to JSON Schema.
		 *
		 * @since 1.0.0
		 *
		 * @return array Item schema data.
		 */
		public function get_item_schema() {
			$schema = array(
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => $this->type,
				'type'       => 'object',
				/* Partial payloads are allowed: the endpoint deep-merges them into the stored row. */
				'properties' => $this->get_registered_schema()['properties'],
			);

			/**
			 * Filters the item's schema.
			 *
			 * @param array $schema Item schema data.
			 */
			$schema = apply_filters( "rest_{$this->type}_item_schema", $schema ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WP core convention for REST item schemas.

			$this->schema = $schema;

			return $this->add_additional_fields_schema( $this->schema );
		}

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
	}
}

/**
 * Return instance of  Brand_Master_Api_Settings class
 *
 * @since 1.0.0
 *
 * @return Brand_Master_Api_Settings
 */
function brand_master_api_settings() { //phpcs:ignore
	return Brand_Master_Api_Settings::get_instance();
}
brand_master_api_settings()->run();
