<?php // phpcs:ignore
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'brand_master_menu' ) ) {
	/**
	 * Display menu block.
	 * Create menu block according to the settings.
	 * Add menu heading, menu list and also logout button on condition.
	 *
	 * @since 1.0.0
	 *
	 * @param string $section section name.
	 * @return void
	 */
	function brand_master_menu( $section ) {
		$current_menu = brand_master_dashboard()->get_current_menu();

		$dashboard_settings = brand_master_include()->get_settings()['dashboard'];
		$menu_settings      = $dashboard_settings['menu'];
		?>
		<div class="at-m bm-menu <?php echo esc_attr( brand_master_dashboard()->get_separation_class( $section, 'menu' ) ); ?>">
			<?php
			if ( $menu_settings['heading'] ) {
				?>
				<h6 class="at-m bm-h6 bm-itm-lbl"><?php echo esc_html( brand_master_translate_default_label( $menu_settings['heading'], 'Navigations' ) ); ?></h6>
				<?php
			}
			$menu_items = $menu_settings['items'];
			if ( $menu_items || ( isset( $menu_settings['logout'] ) && $menu_settings['logout'] ) ) {
				$menu_classes = '';
				if ( 'sidebar' === $section ) {
					$menu_classes .= ' bm-menu-ul-vertical at-flx-col';
				}
				?>
				<ul class="at-ls at-flx at-gap bm-menu-ul <?php echo esc_attr( $menu_classes ); ?>">
					<?php
					if ( is_array( $menu_items ) ) {
						foreach ( $menu_items as $key => $item ) {
							if ( ! is_array( $item ) ) {
								continue;
							}
							$class = 'bm-menu-li';
							$item_slug = isset( $item['slug'] ) ? (string) $item['slug'] : '';
							if ( is_array( $current_menu ) && isset( $current_menu['slug'] ) && $current_menu['slug'] === $item_slug && '' !== $item_slug ) {
								$class .= ' bm-menu-active';
							}
							?>
							<li class="<?php echo esc_attr( $item_slug . ' ' . $class ); ?>"<?php echo 'bm-menu-li bm-menu-active' === $class ? ' aria-current="page"' : ''; ?>>
								<a href="<?php echo esc_url( add_query_arg( 'action', $item_slug, get_permalink() ) ); ?>" class="at-flx at-al-itm-ctr at-gap">
									<?php
									if ( isset( $item['icon']['svg'] ) && brand_master_is_valid_svg( $item['icon']['svg'] ) ) {
										/* phpcs:ignore*/
                                        echo brand_master_esc_svg( $item['icon']['svg'] );//escaping function.
									}
									if ( isset( $item['label'] ) && $item['label'] ) {
										echo esc_html( $item['label'] );
									}
									?>
								</a>
							</li>
							<?php
						}
					}

					if ( isset( $menu_settings['logout'] ) && $menu_settings['logout'] ) {
						?>
						<li class="bm-menu-li bm-menu-li-logout">
							<?php brand_master_logout( $section ); ?>
						</li>
						<?php
					}
					?>
				</ul>
				<?php
			}
			?>
		</div>
		<?php
	}
}
