<?php
/**
 * Disable WordPress.org update checks in the disposable runtime smoke-test site.
 *
 * @package Brand_Master
 */

/**
 * Return a complete, fresh update transient without making an HTTP request.
 *
 * @return stdClass
 */
function brand_master_runtime_empty_update_transient() {
	return (object) array(
		'version_checked' => get_bloginfo( 'version' ),
		'checked'         => time(),
		'last_checked'    => time(),
		'responses'       => array(),
		'no_update'       => array(),
		'translations'    => array(),
	);
}

add_filter( 'pre_site_transient_update_plugins', 'brand_master_runtime_empty_update_transient' );
add_filter( 'pre_site_transient_update_core', 'brand_master_runtime_empty_update_transient' );
add_filter( 'pre_site_transient_update_themes', 'brand_master_runtime_empty_update_transient' );
