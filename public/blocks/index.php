<?php
/**
 * Includes necessary files
 *
 * @package Brand_Master
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once trailingslashit( __DIR__ ) . 'site-identity.php';
require_once trailingslashit( __DIR__ ) . 'menu.php';
require_once trailingslashit( __DIR__ ) . 'social.php';
require_once trailingslashit( __DIR__ ) . 'user-info.php';
require_once trailingslashit( __DIR__ ) . 'logout.php';
