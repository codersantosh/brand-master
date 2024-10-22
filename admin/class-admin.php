<?php // phpcs:ignore Class file names should be based on the class name with "class-" prepended.
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://patternswp.com
 * @since      1.0.0
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Define and execute the hooks for overall functionalities of the plugin and add the admin end like loading resources and defining settings.
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Admin
 * @author     codersantosh <codersantosh@gmail.com>
 */
class Brand_Master_Admin {

	/**
	 * Menu info.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $menu_info    Admin menu information.
	 */
	private $menu_info;

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
	 * Add Admin Page Menu page.
	 *
	 * @access public
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {

		$white_label     = brand_master_include()->get_white_label();
		$this->menu_info = $white_label['admin_menu_page'];

		add_menu_page(
			$this->menu_info['page_title'],
			$this->menu_info['menu_title'],
			'manage_options',
			$this->menu_info['menu_slug'],
			array( $this, 'add_setting_root_div' ),
			$this->menu_info['icon_url'],
			$this->menu_info['position'],
		);
	}

	/**
	 * Add Root Div For React.
	 *
	 * @access public
	 *
	 * @since    1.0.0
	 */
	public function add_setting_root_div() {
		echo '<div id="' . esc_attr( BRAND_MASTER_PLUGIN_NAME ) . '"></div>';
	}

	/**
	 * Register the CSS/JavaScript Resources for the admin area.
	 *
	 * @access public
	 * Use Condition to Load it Only When it is Necessary
	 *
	 * @since    1.0.0
	 */
	public function enqueue_resources() {

		$screen              = get_current_screen();
		$admin_scripts_bases = array( 'toplevel_page_' . BRAND_MASTER_PLUGIN_NAME );
		if ( ! ( isset( $screen->base ) && in_array( $screen->base, $admin_scripts_bases, true ) ) ) {
			return;
		}

		/* Add media */
		wp_enqueue_media();

		/* Code editor*/
		wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
		wp_enqueue_code_editor( array( 'type' => 'javascript' ) );
		wp_enqueue_script( 'wp-theme-plugin-editor' );
		wp_enqueue_style( 'wp-codemirror' );

		/* Atomic CSS */
		wp_enqueue_style( 'atomic' );

		/*Scripts dependency files*/
		$deps_file = BRAND_MASTER_PATH . 'build/admin/admin.asset.php';

		/*Fallback dependency array*/
		$dependency = array();
		$version    = BRAND_MASTER_VERSION;

		/*Set dependency and version*/
		if ( file_exists( $deps_file ) ) {
			$deps_file  = require $deps_file;
			$dependency = $deps_file['dependencies'];
			$version    = $deps_file['version'];
		}

		wp_enqueue_script( BRAND_MASTER_PLUGIN_NAME, BRAND_MASTER_URL . 'build/admin/admin.js', $dependency, $version, true );

		wp_enqueue_style( 'google-fonts-open-sans', BRAND_MASTER_URL . 'assets/library/fonts/open-sans.css', '', $version );
		wp_enqueue_style( BRAND_MASTER_PLUGIN_NAME, BRAND_MASTER_URL . 'build/admin/admin.css', array( 'wp-components' ), $version );

		global $wp_roles;
		/* Localize */
		$localize = apply_filters(
			'brand_master_admin_localize',
			array(
				'version'          => $version,
				'root_id'          => BRAND_MASTER_PLUGIN_NAME,
				'nonce'            => wp_create_nonce( 'wp_rest' ),
				'store'            => 'brand-master',
				'rest_url'         => get_rest_url(),
				'base_url'         => menu_page_url( $this->menu_info['menu_slug'], false ),
				'BRAND_MASTER_URL' => BRAND_MASTER_URL,
				'white_label'      => brand_master_include()->get_white_label(),
				'userRoles'        => $wp_roles->get_names(),
				'home_url'         => home_url( '/' ),
				'login_url'        => wp_login_url(),
			)
		);

		wp_set_script_translations( BRAND_MASTER_PLUGIN_NAME, BRAND_MASTER_PLUGIN_NAME );
		wp_localize_script( BRAND_MASTER_PLUGIN_NAME, 'brandMasterLocalize', $localize );
	}

	/**
	 * Get settings schema
	 * Schema: http://json-schema.org/draft-04/schema#
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 *
	 * @return array settings schema for this plugin.
	 */
	public function get_settings_schema() {

		$setting_properties = apply_filters(
			'brand_master_setting_properties',
			array(
				'login'                  => array(
					'type'       => 'object',
					'properties' => array(
						'url'   => array(
							'type'       => 'object',
							'properties' => array(
								'on'            => array(
									'type' => 'boolean',
								),
								'slug'          => array(
									'type'              => 'string',
									'sanitize_callback' => 'sanitize_key',
								),
								'redirect_slug' => array(
									'type'              => 'string',
									'sanitize_callback' => 'sanitize_key',
								),
							),
						),

						'logo'  => array(
							'type'       => 'object',
							'properties' => array(
								'on'   => array(
									'type' => 'boolean',
								),
								'img'  => array(
									'type'       => 'object',
									'properties' => array(
										'frm' => array(
											'type' => 'string',
										),
										'id'  => array(
											'type' => 'integer',
										),
										'url' => array(
											'type'   => 'string',
											'format' => 'uri',
										),
										'alt' => array(
											'type' => 'string',
										),
										'ttl' => array(
											'type' => 'string',
										),
										'sz'  => array(
											'type' => 'string',
										),
										'w'   => array(
											'type' => 'string',
										),
										'h'   => array(
											'type' => 'string',
										),
									),
								),
								'text' => array(
									'type' => 'string',
								),
								'url'  => array(
									'type'   => 'string',
									'format' => 'uri',
								),
							),
						),
						'title' => array(
							'type' => 'string',
						),
						'css'   => array(
							'type' => 'string',
						),
						'js'    => array(
							'type' => 'string',
						),
					),
				),
				'dashboard'              => array(
					'type'       => 'object',
					'properties' => array(
						'noLoginContent' => array(
							'type' => 'integer',
						),
						'sidebarContent' => array(
							'type'       => 'object',
							'properties' => array(
								'siteIdentity' => array(
									'type' => 'boolean',
								),
								'userInfo'     => array(
									'type' => 'boolean',
								),
								'logout'       => array(
									'type' => 'boolean',
								),
								'menu'         => array(
									'type' => 'boolean',
								),
								'social'       => array(
									'type' => 'boolean',
								),
								'sort'         => array(
									'type'  => 'array',
									'items' => array(
										'type' => 'string',
										'enum' => array(
											'siteIdentity',
											'menu',
											'social',
											'userInfo',
											'logout',
										),
									),
								),
								'sepEl'        => array(
									'type' => 'string',
								),
							),
						),
						'headingContent' => array(
							'type'       => 'object',
							'properties' => array(
								'siteIdentity' => array(
									'type' => 'boolean',
								),
								'userInfo'     => array(
									'type' => 'boolean',
								),
								'logout'       => array(
									'type' => 'boolean',
								),
								'menu'         => array(
									'type' => 'boolean',
								),
								'social'       => array(
									'type' => 'boolean',
								),
								'pageTitle'    => array(
									'type' => 'boolean',
								),
								'sort'         => array(
									'type'  => 'array',
									'items' => array(
										'type' => 'string',
										'enum' => array(
											'pageTitle',
											'userInfo',
											'logout',
											'siteIdentity',
											'menu',
											'social',
										),
									),
								),
								'sepEl'        => array(
									'type' => 'string',
								),
							),
						),
						'siteIdentity'   => array(
							'type'       => 'object',
							'properties' => array(
								'logo'    => array(
									'type' => 'string',
								),
								'title'   => array(
									'type' => 'boolean',
								),
								'tagline' => array(
									'type' => 'boolean',
								),
								'sort'    => array(
									'type'  => 'array',
									'items' => array(
										'type' => 'string',
										'enum' => array(
											'title',
											'tagline',
										),
									),
								),
							),
						),
						'userInfo'       => array(
							'type'       => 'object',
							'properties' => array(
								'logo' => array(
									'type' => 'string',
								),
								'name' => array(
									'type' => 'boolean',
								),
								'desc' => array(
									'type' => 'boolean',
								),
								'sort' => array(
									'type'  => 'array',
									'items' => array(
										'type'  => 'string',
										'items' => array(
											'type' => 'string',
											'enum' => array(
												'name',
												'desc',
											),
										),
									),
								),
							),
						),
						'logout'         => array(
							'type'       => 'object',
							'properties' => array(
								'label' => array(
									'type' => 'string',
								),
								'icon'  => array(
									'type'       => 'object',
									'properties' => array(
										'id'  => array(
											'type' => 'string',
										),
										'svg' => array(
											'type' => 'string',
										),
									),
								),
							),
						),
						'menu'           => array(
							'type'       => 'object',
							'properties' => array(
								'heading'  => array(
									'type' => 'string',
								),
								'items'    => array(
									'type'  => 'array',
									'items' => array(
										'type'       => 'object',
										'properties' => array(
											'icon'   => array(
												'type' => 'object',
												'properties' => array(
													'id'  => array(
														'type' => 'string',
													),
													'svg' => array(
														'type' => 'string',
													),
												),
											),
											'label'  => array(
												'type' => 'string',
											),
											'slug'   => array(
												'type' => 'string',
												'sanitize_callback' => 'sanitize_key',
											),
											'typeId' => array(
												'type' => 'integer',
											),
										),
									),
								),
								'logout'   => array(
									'type' => 'boolean',
								),
								'redirect' => array(
									'type'       => 'object',
									'properties' => array(
										'on'  => array(
											'type' => 'boolean',
										),
										'url' => array(
											'type'   => 'string',
											'format' => 'uri',
										),
									),
								),
							),
						),
						'social'         => array(
							'type'       => 'object',
							'properties' => array(
								'heading' => array(
									'type' => 'string',
								),
								'items'   => array(
									'type'  => 'array',
									'items' => array(
										'type'       => 'object',
										'properties' => array(
											'icon'   => array(
												'type' => 'object',
												'properties' => array(
													'id'  => array(
														'type' => 'string',
													),
													'svg' => array(
														'type' => 'string',
													),
												),
											),
											'label'  => array(
												'type' => 'string',
											),
											'url'    => array(
												'type'   => 'string',
												'format' => 'uri',
											),
											'target' => array(
												'type' => 'string',
												'enum' => array(
													'',
													'_blank',
												),
											),
											'layout' => array(
												'type' => 'string',
											),
										),
									),
								),
								'layout'  => array(
									'type' => 'string',
								),
							),
						),
					),
				),

				'hideAdminBar'           => array(
					'type'       => 'object',
					'properties' => array(
						'on'       => array(
							'type' => 'boolean',
						),
						'hide'     => array(
							'type' => 'string',
							'enum' => array(
								'',
								'roles',
							),
						),
						'useRoles' => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
					),
				),
				'redirectAdminDashboard' => array(
					'type'       => 'object',
					'properties' => array(
						'on'       => array(
							'type' => 'boolean',
						),
						'url'      => array(
							'type'   => 'string',
							'format' => 'uri',
						),
						'useRoles' => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
					),
				),
				'redirectLogin'          => array(
					'type'       => 'object',
					'properties' => array(
						'on'  => array(
							'type' => 'boolean',
						),
						'url' => array(
							'type'   => 'string',
							'format' => 'uri',
						),
					),
				),
				'redirectLogout'         => array(
					'type'       => 'object',
					'properties' => array(
						'on'  => array(
							'type' => 'boolean',
						),
						'url' => array(
							'type'   => 'string',
							'format' => 'uri',
						),
					),
				),
				'redirectLostPassword'   => array(
					'type'       => 'object',
					'properties' => array(
						'on'  => array(
							'type' => 'boolean',
						),
						'url' => array(
							'type'   => 'string',
							'format' => 'uri',
						),
					),
				),
				'redirectRegistration'   => array(
					'type'       => 'object',
					'properties' => array(
						'on'  => array(
							'type' => 'boolean',
						),
						'url' => array(
							'type'   => 'string',
							'format' => 'uri',
						),
					),
				),
				'deleteAll'              => array(
					'type' => 'boolean',
				),
			),
		);

		return array(
			'type'       => 'object',
			'properties' => $setting_properties,
		);
	}

	/**
	 * Register settings.
	 * Common callback function of rest_api_init and admin_init
	 * Schema: http://json-schema.org/draft-04/schema#
	 *
	 * Add your own settings fields here
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_settings() {
		$defaults = brand_master_default_options();

		register_setting(
			'brand_master_setting_group',
			BRAND_MASTER_OPTION_NAME,
			array(
				'type'         => 'object',
				'default'      => $defaults,
				'show_in_rest' => array(
					'schema' => $this->get_settings_schema(),
				),
			)
		);
	}

	/**
	 * Add plugin menu items.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 * @param string[] $actions     An array of plugin action links. By default this can include
	 *                              'activate', 'deactivate', and 'delete'. With Multisite active
	 *                              this can also include 'network_active' and 'network_only' items.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array    $plugin_data An array of plugin data. See get_plugin_data()
	 *                              and the {@see 'plugin_row_meta'} filter for the list
	 *                              of possible values.
	 * @param string   $context     The plugin context. By default this can include 'all',
	 *                              'active', 'inactive', 'recently_activated', 'upgrade',
	 *                              'mustuse', 'dropins', and 'search'.
	 * @return array settings schema for this plugin.
	 */
	public function add_plugin_links( $actions, $plugin_file, $plugin_data, $context ) {
		$actions[] = '<a href="' . esc_url( menu_page_url( $this->menu_info['menu_slug'], false ) ) . '">' . esc_html__( 'Settings', 'brand-master' ) . '</a>';
		return $actions;
	}
}

if ( ! function_exists( 'brand_master_admin' ) ) {
	/**
	 * Return instance of  Brand_Master_Admin class
	 *
	 * @since 1.0.0
	 *
	 * @return Brand_Master_Admin
	 */
	function brand_master_admin() {//phpcs:ignore
		return Brand_Master_Admin::get_instance();
	}
}
