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
 * @link       https://github.com/codersantosh
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
			/* Query only once */
			self::$settings    = brand_master_get_options();
			self::$white_label = brand_master_get_white_label();

			$instance = new self();
		}

		// Always return the instance.
		return $instance;
	}

	/**
	 * Get the settings from the class instance.
	 *
	 * @access public
	 * @return array|null
	 */
	public function get_settings() {
		return self::$settings;
	}

	/**
	 * Get options related to white label.
	 *
	 * @access public
	 * @return array|null
	 */
	public function get_white_label() {
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
	function brand_master_include() {
		return Brand_Master_Include::get_instance();
	}
}
