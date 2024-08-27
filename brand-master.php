<?php
/**
 * The plugin main file.
 *
 * @link              https://github.com/codersantosh/brand-master
 * @since             1.0.0
 * @package           Brand_Master
 *
 * Plugin Name:       Brand Master - Customize Login and User Frontend Dashboard
 * Plugin URI:        https://github.com/codersantosh/brand-master
 * Description:       Brand Master enhances WordPress site customization by allowing you to personalize login pages and introduce a sleek frontend dashboard for users. Elevate your brand with complete control over the user experience.
 * Version:           1.0.2
 * Author:            patternswp
 * Author URI:        https://github.com/codersantosh
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       brand-master
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin path.
 * Current plugin url.
 * Current plugin version.
 * Current plugin name.
 * Current plugin option name.
 */
define( 'BRAND_MASTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'BRAND_MASTER_URL', plugin_dir_url( __FILE__ ) );
define( 'BRAND_MASTER_VERSION', '1.0.2' );
define( 'BRAND_MASTER_PLUGIN_NAME', 'brand-master' );
define( 'BRAND_MASTER_OPTION_NAME', 'brand_master_options' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class--activator.php
 */
function brand_master_activate() {

	require_once BRAND_MASTER_PATH . 'includes/class-activator.php';
	Brand_Master_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-deactivator.php
 */
function brand_master_deactivate() {
	require_once BRAND_MASTER_PATH . 'includes/class-deactivator.php';
	Brand_Master_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'brand_master_activate' );
register_deactivation_hook( __FILE__, 'brand_master_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require BRAND_MASTER_PATH . 'includes/main.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function brand_master_run() {

	$plugin = new Brand_Master();
	$plugin->run();
}
brand_master_run();
