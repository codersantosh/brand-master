<?php // phpcs:ignore Class file names should be based on the class name with "class-" prepended.
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site identity.
 *
 * @link       https://github.com/codersantosh
 * @since      1.0.0
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Dashboard/Site_Identity
 */

/**
 * Site identity.
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Dashboard/Site_Identity
 * @author     codersantosh <codersantosh@gmail.com>
 */
class Brand_Master_Site_Identity {

	/**
	 * Site identity settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var     array settings.
	 */
	public $settings;

	/**
	 * Initialize the class with storing settings for site identity.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$dashboard_settings = brand_master_include()->get_settings()['dashboard'];
		$this->settings     = $dashboard_settings['siteIdentity'];
	}

	/**
	 * Initialize the class and set up actions.
	 *
	 * @access public
	 * @return string Logo
	 */
	public function get_logo() {
		return '<div class="bm-logo">' . get_custom_logo() . '</div>';
	}

	/**
	 * Check to display logo on top.
	 *
	 * @access public
	 * @return boolean display logo?
	 */
	public function has_top_logo() {
		if ( $this->settings['logo'] && in_array( $this->settings['logo'], array( 't', 'l' ), true ) && has_custom_logo() ) {
			return true;
		}
		return false;
	}

	/**
	 * Check to display logo on bottom.
	 *
	 * @access public
	 * @return boolean display logo?
	 */
	public function has_bottom_logo() {
		if ( $this->settings['logo'] && in_array( $this->settings['logo'], array( 'r', 'b' ), true ) && has_custom_logo() ) {
			return true;
		}
		return false;
	}

	/**
	 * Get classes of site identiy wrap.
	 *
	 * @access public
	 * @param string $section section name.
	 * @return string classnames
	 */
	public function get_classes( $section ) {
		$classes = brand_master_dashboard()->get_separation_class( $section, 'siteIdentity' );
		if ( $this->settings['logo'] && has_custom_logo() ) {
			$classes .= ' at-flx at-gap bm-logo-pos-' . esc_attr( $this->settings['logo'] );
			if ( in_array( $this->settings['logo'], array( 't', 'b' ), true ) ) {
				$classes .= ' at-flx-col';
			}
			if ( in_array( $this->settings['logo'], array( 'l', 'r' ), true ) ) {
				$classes .= ' at-al-itm-ctr';
			}
		}
		return $classes;
	}

	/**
	 * Get site identity HTML.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string $section section name.
	 * @return void
	 */
	public function get( $section ) {
		echo '<div class="bm-site-identity ' . esc_attr( $this->get_classes( $section ) ) . '">';
		if ( $this->has_top_logo() ) {
            /* phpcs:ignore */
            echo brand_master_esc_preserve_html( $this->get_logo() );//escaping function.
		}

		if ( $this->settings['sort'] ) {
			echo '<div class="bm-site-identity-txt">';
			foreach ( $this->settings['sort'] as $element ) {

				switch ( $element ) {
					case 'title':
						if ( $this->settings[ $element ] ) {
							echo '<h1 class="at-txt bm-site-title at-m">' . esc_html( get_bloginfo( 'name' ) ) . '</h1>';
						}
						break;

					case 'tagline':
						if ( $this->settings[ $element ] ) {
							$site_description = get_bloginfo( 'description', 'display' );
							echo '<p class="at-txt bm-site-tagline at-m">' . esc_html( $site_description ) . '</p>';
						}
						break;

					default:
						// code...
						break;
				}
			}
			echo '</div>';

		}

		if ( $this->has_bottom_logo() ) {
           /* phpcs:ignore */
            echo brand_master_esc_preserve_html( $this->get_logo() );//escaping function.
		}
		echo '</div>';
	}
}

if ( ! function_exists( 'brand_master_site_identity' ) ) {
	/**
	 * Display site identity.
	 *
	 * @since 1.0.0
	 * @param string $section section name.
	 * @return void
	 */
	function brand_master_site_identity( $section ) {//phpcs:ignore
		$bm_identity = new Brand_Master_Site_Identity();
		$bm_identity->get( $section );
	}
}
