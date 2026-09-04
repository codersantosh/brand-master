<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Removes all Brand Master data. Unlike deactivation (which keeps data unless
 * the deleteAll option is enabled), uninstalling always removes the options.
 *
 * @link       https://patternswp.com/wp-plugins/brand-master
 * @since      1.0.6
 *
 * @package    Brand_Master
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove plugin settings (login, dashboard, redirects, hideAdminBar, deleteAll, etc.).
delete_option( 'brand_master_options' );

// Remove any related transients (none currently registered, kept for safety).
delete_transient( 'brand_master_patterns' );
