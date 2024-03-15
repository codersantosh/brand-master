<?php // phpcs:ignore Class file names should be based on the class name with "class-" prepended.
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/codersantosh
 * @since      1.0.0
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Brand_Master
 * @subpackage Brand_Master/includes
 * @author     codersantosh <codersantosh@gmail.com>
 */
class Brand_Master_Deactivator {

	/**
	 * Fired during plugin deactivation.
	 *
	 * Removing options, table and all data related to plugin if user select remove data on deactivate.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		if ( brand_master_get_options( 'deleteAll' ) ) {
			delete_option( BRAND_MASTER_OPTION_NAME );
		}
	}
}
