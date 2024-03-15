<?php // phpcs:ignore Class file names should be based on the class name with "class-" prepended.
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add design WordPress Gutenberg Block patterns.
 *
 * A class definition that includes attributes and functions used for adding block patterns.
 *
 * @link       https://github.com/codersantosh
 * @since      1.0.0
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/patterns
 */

/**
 * Add design WordPress Gutenberg Block patterns.
 *
 * A class definition that includes attributes and functions used for adding block patterns.
 *
 * @since      1.0.0
 * @package    Brand_Master
 * @subpackage Brand_Master/patterns
 * @author     codersantosh <codersantosh@gmail.com>
 */
class Brand_Master_Patterns {

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

		add_action( 'admin_init', array( $this, 'register_block_pattern_category' ) );
		add_action( 'admin_init', array( $this, 'register_patterns' ) );
	}

	/**
	 * Register pattern category.
	 *
	 * @access public
	 * @return void
	 */
	public function register_block_pattern_category() {
		register_block_pattern_category(
			'brand-master-pattern-cat',
			array(
				'label' => esc_html__( 'Dashboard', 'brand-master' ),
			)
		);
	}

	/**
	 * Get patterns data.
	 *
	 * @access public
	 * @return void
	 */
	public function register_patterns() {
		$pattern_api_url = BRAND_MASTER_URL . 'includes/json/patterns.json';
		$response        = wp_remote_get( $pattern_api_url );
		if ( ! is_wp_error( $response ) ) {
			$patterns = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( $patterns ) {
				foreach ( $patterns as $pattern ) {
					$this->register_block_pattern( $pattern );
				}
			}
		}
	}

	/**
	 * Register an individual block pattern
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array $pattern Single block pattern.
	 * @return void
	 */
	private function register_block_pattern( $pattern ) {
		register_block_pattern(
			sanitize_title( 'bm-' . $pattern['slug'] ),
			array(
				'title'      => $pattern['title']['rendered'],
				'content'    => $pattern['pattern_content'],
				'categories' => array( 'brand-master-pattern-cat' ),
			)
		);
	}
}

if ( ! function_exists( 'brand_master_patterns' ) ) {
	/**
	 * Return instance of  Brand_Master_Patterns class
	 *
	 * @since 1.0.0
	 *
	 * @return Brand_Master_Patterns
	 */
	function brand_master_patterns() {
		return Brand_Master_Patterns::get_instance();
	}
}

brand_master_patterns()->run();
