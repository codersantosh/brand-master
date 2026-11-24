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
 * @link       https://patternswp.com
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
		static $patterns = null;

		if ( null === $patterns ) {
			/* Read the patterns file locally. A loopback wp_remote_get() breaks on hosts with blocked HTTP requests and adds latency on every admin request. */
			$patterns      = array();
			$wp_filesystem = brand_master_file_system();
			$pattern_file  = BRAND_MASTER_PATH . 'includes/json/patterns.json';
			if ( $wp_filesystem && $wp_filesystem->is_readable( $pattern_file ) ) {
				$decoded = json_decode( (string) $wp_filesystem->get_contents( $pattern_file ), true );
				if ( is_array( $decoded ) ) {
					$patterns = $decoded;
				}
			}
			$patterns = apply_filters( 'brand_master_patterns', $patterns );
		}

		foreach ( $patterns as $pattern ) {
			if ( ! is_array( $pattern ) || empty( $pattern['slug'] ) || empty( $pattern['title']['rendered'] ) || empty( $pattern['pattern_content'] ) ) {
				continue;
			}
			$this->register_block_pattern( $pattern );
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
			sanitize_title( 'brand-master-' . $pattern['slug'] ),
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
	function brand_master_patterns() {//phpcs:ignore
		return Brand_Master_Patterns::get_instance();
	}
}

brand_master_patterns()->run();
