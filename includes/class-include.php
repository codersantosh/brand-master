<?php // phpcs:ignore Class file names should be based on the class name with "class-" prepended.
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The common bothend functionality of the plugin.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://patternswp.com
 * @since      1.0.0
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/includes
 */

/**
 * The common bothend functionality of the plugin.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 * @package    Brand_Master
 * @subpackage Brand_Master/includes
 * @author     codersantosh <codersantosh@gmail.com>
 */
class Brand_Master_Include {

	/**
	 * Static property to store Options Settings
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    settings All settings for this plugin.
	 */
	private static $settings = null;

	/**
	 * Static property to store white label settings
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    settings All settings for this plugin.
	 */
	private static $white_label = null;

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
	 * Get the settings from the class instance.
	 *
	 * Settings are cached per-request because the rendering layer reads them
	 * many times per page. The cache is invalidated when the option is
	 * updated via the updated_option hook so a save in the same request
	 * (e.g. an admin that updates options and then previews) sees the new
	 * values.
	 *
	 * @access public
	 * @return array|null
	 */
	public function get_settings() {
		if ( null === self::$settings ) {
			self::$settings = brand_master_get_options();
		}
		return self::$settings;
	}

	/**
	 * Drop the request-scoped settings cache. Hooked to updated_option so a
	 * save followed by a same-request read picks up the new value.
	 *
	 * @since 1.0.6
	 *
	 * @param string $option Option name.
	 * @return void
	 */
	public function clear_settings_cache( $option = '' ) {
		if ( '' === $option || BRAND_MASTER_OPTION_NAME === $option ) {
			self::$settings = null;
		}
	}

	/**
	 * Get options related to white label.
	 *
	 * @access public
	 * @return array|null
	 */
	public function get_white_label() {
		if ( null === self::$white_label ) {
			self::$white_label = brand_master_get_white_label();
		}
		return self::$white_label;
	}

	/**
	 * Register scripts and styles
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return void
	 */
	public function register_scripts_and_styles() {
		/* Atomic css */
		wp_register_style( 'atomic', BRAND_MASTER_URL . 'assets/library/atomic-css/atomic.min.css', array(), BRAND_MASTER_VERSION );
	}
}

if ( ! function_exists( 'brand_master_include' ) ) {
	/**
	 * Return instance of  Brand_Master_Include class
	 *
	 * @since 1.0.0
	 *
	 * @return Brand_Master_Include
	 */
	function brand_master_include() {//phpcs:ignore
		return Brand_Master_Include::get_instance();
	}
}
