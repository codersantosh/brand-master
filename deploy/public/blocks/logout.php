<?php // phpcs:ignore
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'brand_master_logout' ) ) {
	/**
	 * Display logout block.
	 * Create block according to the settings and add redirect url too.
	 *
	 * @since 1.0.0
	 *
	 * @param string $section section name.
	 * @return void
	 */
	function brand_master_logout( $section ) {
		$dashboard_settings = brand_master_include()->get_settings()['dashboard'];
		$item               = $dashboard_settings['logout'];

		$redirect_logout = brand_master_include()->get_settings()['redirectLogout'];
		$redirect_to     = '';
		if ( isset( $redirect_logout['on'] ) && isset( $redirect_logout['url'] ) && $redirect_logout['on'] && $redirect_logout['url'] ) {
			$redirect_to = $redirect_logout['url'];
		}
		?>
		<a class="at-flx at-al-itm-ctr at-p at-m at-bdr at-gap bm-logout <?php echo esc_attr( brand_master_dashboard()->get_separation_class( $section, 'logout' ) ); ?>" href="<?php echo esc_url( wp_logout_url( $redirect_to ) ); ?>">
			<?php
			if ( isset( $item['icon']['svg'] ) && brand_master_is_valid_svg( $item['icon']['svg'] ) ) {
                /* phpcs:ignore*/
				echo brand_master_esc_svg( $item['icon']['svg'] );//escaping function.
			}
			if ( $item['label'] ) {
				echo esc_html( $item['label'] );
			}
			?>
		</a>
		<?php
	}
}
