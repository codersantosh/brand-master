<?php // phpcs:ignore Class file names should be based on the class name with "class-" prepended.
/**
 * The frontend dashboard functionality of the plugin.
 *
 * @link       https://github.com/codersantosh
 * @since      1.0.0
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Dashboard
 */

/**
 * The frontend dashboard functionality of the plugin.
 *
 * Define and execute the hooks for overall functionalities of customization of the frontend dashboard provided by this plugin..
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Dashboard
 * @author     codersantosh <codersantosh@gmail.com>
 */
class Brand_Master_Dashboard {

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
	 * Get separation class.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $section section.
	 * @param string $element Element id.
	 *
	 * @return string The separation class.
	 */
	public function get_separation_class( $section, $element ) {
		if ( 'sidebar' === $section ) {
			$dashboard_settings = brand_master_include()->get_settings()['dashboard'];
			$sidebar_content    = $dashboard_settings['sidebarContent'];
			$separation_element = isset( $sidebar_content['sepEl'] ) ? $sidebar_content['sepEl'] : '';
			if ( $separation_element && $element === $separation_element ) {
				return ' at-m bm-b bm-sep-el-sdbar';
			}
		} elseif ( 'header' === $section ) {
			$dashboard_settings = brand_master_include()->get_settings()['dashboard'];
			$heading_content    = $dashboard_settings['headingContent'];
			$separation_element = isset( $heading_content['sepEl'] ) ? $heading_content['sepEl'] : '';

			if ( $separation_element && $element === $separation_element ) {
				return ' at-m bm-l bm-sep-el-head';
			}
		}
		return '';
	}

	/**
	 * Has elements.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $sorting_content list of sorting content.
	 * @param array $sorting_content_elements sorting array.
	 * @return boolean
	 */
	public function has_sorting_contents( $sorting_content, $sorting_content_elements ) {

		if ( $sorting_content_elements ) {
			foreach ( $sorting_content_elements as $element ) {
				if ( isset( $sorting_content[ $element ] ) ) {
					switch ( $element ) {
						case 'siteIdentity':
							if ( $sorting_content[ $element ] ) {
								return true;
							}
							break;

						case 'menu':
							if ( $sorting_content[ $element ] ) {
								return true;

							}
							break;

						case 'social':
							if ( $sorting_content[ $element ] ) {
								return true;

							}
							break;

						case 'userInfo':
							if ( $sorting_content[ $element ] ) {
								return true;
							}
							break;

						case 'logout':
							if ( $sorting_content[ $element ] ) {
								return true;
							}
							break;

						case 'pageTitle':
							if ( $sorting_content[ $element ] ) {
								return true;
							}
							break;

						default:
							break;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Map sorting and contents.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array  $sorting_content list of sorting content.
	 * @param array  $sorting_content_elements sorting array.
	 * @param string $section section.
	 * @return void
	 */
	public function get_sorting_contents( $sorting_content, $sorting_content_elements, $section ) {

		if ( $sorting_content_elements ) {
			foreach ( $sorting_content_elements as $element ) {
				if ( isset( $sorting_content[ $element ] ) ) {
					switch ( $element ) {
						case 'siteIdentity':
							if ( $sorting_content[ $element ] ) {
								brand_master_site_identity( $section );
							}
							break;

						case 'menu':
							if ( $sorting_content[ $element ] ) {
								brand_master_menu( $section );
							}
							break;

						case 'social':
							if ( $sorting_content[ $element ] ) {
								brand_master_social( $section );
							}
							break;

						case 'userInfo':
							if ( $sorting_content[ $element ] ) {
								brand_master_user_info( $section );
							}
							break;

						case 'logout':
							if ( $sorting_content[ $element ] ) {
								brand_master_logout( $section );
							}
							break;

						case 'pageTitle':
							if ( $sorting_content[ $element ] ) {
								the_title( '<h3 class="bm-page-title' . brand_master_dashboard()->get_separation_class( $section, 'pageTitle' ) . '">', '</h3>' );
							}
							break;

						default:
							break;
					}
				}
			}
		}
	}

	/**
	 * Get the menu item based on the provided slug or return the first menu item if no match is found.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array The menu item corresponding to the provided slug or the first menu item if no match is found.
	 */
	public function get_current_menu() {

		/* phpcs:ignore */
		$current_slug = ! empty( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
		$dashboard_settings = brand_master_include()->get_settings()['dashboard'];
		$menu_settings      = $dashboard_settings['menu'];
		$menu_items         = $menu_settings['items'];

		// Validate $menu.
		if ( ! is_array( $menu_items ) || empty( $menu_items ) ) {
			return false;
		}

		// Validate $current_slug.
		if ( ! is_string( $current_slug ) || empty( $current_slug ) ) {
			// If no match is found, return the first menu item.
			return $menu_items[0];
		}

		foreach ( $menu_items as $item ) {
			if ( $item['slug'] === $current_slug ) {
				return $item;
			}
		}

		return false;

	}

	/**
	 * Initialize the class and set up actions.
	 * Add shortcode.
	 * Add scripts.
	 *
	 * @access public
	 * @return void
	 */
	public function run() {

		/* Shortcodes */
		add_shortcode( 'brand_master_dashboard', array( $this, 'add_dashboard_shortcode' ) );
		add_shortcode( 'brand_master_login', array( $this, 'add_dashboard_login' ) );

		/* Shortcode scripts */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_resources' ) );

		/* Menu pages redirect */
		add_action( 'template_redirect', array( $this, 'redirect_to_dashboard' ) );

	}

	/**
	 * Add dashboard HTML/CSS/JS/PHP etc.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string HTML of dashboard.
	 */
	public function add_dashboard_shortcode() {

		ob_start();

		/* This will add css/js to footer  */
		brand_master_dashboard()->enqueue_dashboard_resources();

		if ( is_user_logged_in() ) {
			require_once BRAND_MASTER_PATH . 'public/blocks/index.php';
			require_once BRAND_MASTER_PATH . 'public/templates/dashboard.php';
		} else {
			$dashboard_settings = brand_master_include()->get_settings()['dashboard'];
			echo '<div class="bm-dashboard">';
			if ( $dashboard_settings['noLoginContent'] ) {
				// Perform WP Query.
				$page_query = new WP_Query( array( 'page_id' => absint( $dashboard_settings['noLoginContent'] ) ) );

				// Check if there are results.
				if ( $page_query->have_posts() ) {
					// Loop through the results.
					while ( $page_query->have_posts() ) {
						$page_query->the_post();
						the_content();
					}
				}
				wp_reset_postdata();
			} else {
				/* phpcs:ignore */
				echo $this->add_dashboard_login();
			}
			echo '</div>';
		}

		return apply_filters( 'brand_master_dashboard', ob_get_clean() );
	}

	/**
	 * Add WordPress default login form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string HTML of dashboard.
	 */
	public function add_dashboard_login() {

		ob_start();
		wp_enqueue_style( 'login' );
		echo '<div class="login login-action-login wp-core-ui">';
		echo '<div id="login">';
		wp_login_form();
		echo '</div>';
		echo '</div>';

		return apply_filters( 'add_dashboard_login', ob_get_clean() );
	}

	/**
	 * Add Atomic Css on public facing sites.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_public_resources() {
		/* Atomic CSS */
		wp_enqueue_style( 'atomic' );
	}

	/**
	 * Add Public Dashboard resources JS/CSS on the shortcode.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_dashboard_resources() {

		$version = BRAND_MASTER_VERSION;

		wp_enqueue_style( BRAND_MASTER_PLUGIN_NAME, BRAND_MASTER_URL . 'build/public/dashboard.css', array( 'atomic' ), $version );

		/*Scripts dependency files*/
		$deps_file = BRAND_MASTER_PATH . 'build/public/dashboard.asset.php';

		/*Fallback dependency array*/
		$dependency = array();

		/*Set dependency and version*/
		if ( file_exists( $deps_file ) ) {
			$deps_file  = require $deps_file;
			$dependency = $deps_file['dependencies'];
			$version    = $deps_file['version'];
		}

		wp_enqueue_script( BRAND_MASTER_PLUGIN_NAME, BRAND_MASTER_URL . 'build/public/dashboard.js', $dependency, $version, true );
		wp_set_script_translations( BRAND_MASTER_PLUGIN_NAME, BRAND_MASTER_PLUGIN_NAME );

		wp_add_inline_script(
			BRAND_MASTER_PLUGIN_NAME,
			sprintf(
				"var pwpBrandMasterData = JSON.parse( decodeURIComponent( '%s' ) );",
				rawurlencode(
					wp_json_encode(
						array(
							'BRAND_MASTER_URL' => BRAND_MASTER_URL,
							'rest_url'         => get_rest_url(),
						)
					)
				),
			),
			'before'
		);

	}

	/**
	 * Coming Soon (Maintenance) request
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @return void
	 */
	public function redirect_to_dashboard() {
		$dashboard_settings = brand_master_include()->get_settings()['dashboard'];

		/*
		Must be page
		Must have menu items
		Must enable menu
		Must have url
		*/
		if ( is_page() &&
		isset( $dashboard_settings['menu']['items'] ) && $dashboard_settings['menu']['items'] &&
		isset( $dashboard_settings['menu']['redirect']['on'] ) && $dashboard_settings['menu']['redirect']['on'] &&
		isset( $dashboard_settings['menu']['redirect']['url'] ) && $dashboard_settings['menu']['redirect']['url']
		) {
			$menu_items = $dashboard_settings['menu']['items'];

			$menu_ids = array();
			foreach ( $menu_items as $item ) {
				if ( $item['typeId'] ) {
					$menu_ids[] = absint( $item['typeId'] );
				}
			}
			if ( $menu_ids && in_array( absint( get_the_ID() ), $menu_ids, true ) ) {
				wp_safe_redirect( esc_url( $dashboard_settings['menu']['redirect']['url'] ) );
				exit;
			}
		}
	}
}

if ( ! function_exists( 'brand_master_dashboard' ) ) {
	/**
	 * Return instance of  Brand_Master_Dashboard class
	 *
	 * @since 1.0.0
	 *
	 * @return Brand_Master_Dashboard
	 */
	function brand_master_dashboard() {
		return Brand_Master_Dashboard::get_instance();
	}
}
brand_master_dashboard()->run();
