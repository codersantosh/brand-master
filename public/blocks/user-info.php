<?php // phpcs:ignore Class file names should be based on the class name with "class-" prepended.
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Info.
 *
 * @link       https://patternswp.com
 * @since      1.0.0
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Dashboard/User_Info
 */

/**
 * User Info.
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Dashboard/User_Info
 * @author     codersantosh <codersantosh@gmail.com>
 */
class Brand_Master_User_Info {

	/**
	 * User Info settings.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var     array settings.
	 */
	public $settings;

	/**
	 * User ID.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var     integer user id.
	 */
	public $user_id;

	/**
	 * User Email.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var     string Email.
	 */
	public $user_email;

	/**
	 * User display name.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var     string display name.
	 */
	public $user_display_name;

	/**
	 * Initialize the class with storing settings for user info.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$dashboard_settings      = brand_master_include()->get_settings()['dashboard'];
		$this->settings          = $dashboard_settings['userInfo'];
		$bm_current_user         = wp_get_current_user();
		$this->user_id           = $bm_current_user->ID;
		$this->user_email        = $bm_current_user->user_email;
		$this->user_display_name = $bm_current_user->display_name;
	}

	/**
	 * Initialize the class and set up actions.
	 *
	 * @access public
	 * @return string Logo
	 */
	public function get_logo() {
		$avatar_args['class'] = 'bm-logo';
		$avatar_size          = 40;
		return get_avatar( $this->user_email, $avatar_size, '', '', $avatar_args );
	}

	/**
	 * Check to display logo on top.
	 *
	 * @access public
	 * @return boolean display logo?
	 */
	public function has_top_logo() {
		if ( $this->settings['logo'] && in_array( $this->settings['logo'], array( 't', 'l' ), true ) ) {
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
		if ( $this->settings['logo'] && in_array( $this->settings['logo'], array( 'r', 'b' ), true ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get classes of the wrap.
	 *
	 * @access public
	 * @param string $section section name.
	 * @return string classnames
	 */
	public function get_classes( $section ) {
		$classes = brand_master_dashboard()->get_separation_class( $section, 'userInfo' );
		if ( $this->settings['logo'] ) {
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
	 * Get user info HTML.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string $section section name.
	 * @return void
	 */
	public function get( $section ) {
		echo '<div class="bm-user-info' . esc_attr( $this->get_classes( $section ) ) . '">';
		if ( $this->has_top_logo() ) {
           /* phpcs:ignore */
			echo brand_master_esc_preserve_html( $this->get_logo() );// escaping function.
		}

		if ( $this->settings['sort'] ) {
			echo '<div class="bm-user-info-txt">';
			foreach ( $this->settings['sort'] as $element ) {

				switch ( $element ) {
					case 'name':
						if ( $this->settings[ $element ] ) {
							echo '<h4 class="at-txt bm-user-name at-m">' . esc_html( $this->user_display_name ) . '</h4>';
						}
						break;

					case 'desc':
						if ( $this->settings[ $element ] ) {
							echo '<p class="at-txt bm-user-desc at-m">' . wp_kses_post( get_user_meta( $this->user_id, 'description', true ) ) . '</p>';
						}
						break;

					default:
						// code... nothing for now.
						break;
				}
			}

			echo '</div>';

		}

		if ( $this->has_bottom_logo() ) {
            /* phpcs:ignore */
			echo brand_master_esc_preserve_html( $this->get_logo() );// escaping function.
		}
		echo '</div>';
	}
}

if ( ! function_exists( 'brand_master_user_info' ) ) {

	/**
	 * Display user info.
	 *
	 * @since 1.0.0
	 * @param string $section section name.
	 * @return void
	 */
	function brand_master_user_info( $section ) {//phpcs:ignore
		$bm_identity = new Brand_Master_User_Info();
		$bm_identity->get( $section );
	}
}
