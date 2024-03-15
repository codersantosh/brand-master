<?php // phpcs:ignore
if ( ! function_exists( 'brand_master_social' ) ) {

	/**
	 * Display site identity
	 *
	 * @since 1.0.0
	 *
	 * @param string $section section name.
	 * @return void
	 */
	function brand_master_social( $section ) {
		$dashboard_settings = brand_master_include()->get_settings()['dashboard'];
		$menu_settings      = $dashboard_settings['social'];
		?>
		<div class="bm-social <?php /* phpcs:ignore */ echo brand_master_dashboard()->get_separation_class( $section,'social' ); ?>">
			<?php
			if ( $menu_settings['heading'] ) {
				?>
				<h6 class="at-m bm-h6 bm-itm-lbl"><?php echo esc_html( $menu_settings['heading'] ); ?></h6>
				<?php
			}
			$menu_items = $menu_settings['items'];
			if ( $menu_items ) {
				$classes = ' at-flx at-gap ';
				if ( isset( $menu_settings['layout'] ) ) {

					if ( 'hor' === $menu_settings['layout'] ) {
						$classes .= 'at-al-itm-ctr bm-social-horizontal';
					} else {
						$classes .= ' at-flx-col bm-social-vertical';
					}
				}
				?>
				<ul class="at-ls bm-social-ul<?php /* phpcs:ignore */echo $classes; ?>">
					<?php
					foreach ( $menu_items as $item ) {
						$target = isset( $item['target'] ) && $item['target'] ? ' target="' . esc_attr( $item['target'] ) . '"' : ''
						?>
						<li class="bm-social-li">
							<a  class="at-flx at-al-itm-ctr at-gap" href="<?php echo esc_url( $item['url'] ); ?>" <?php /* known value no need to escape. */  /*  phpcs:ignore */ echo $target;
							?>
							>
								<?php
								if ( isset( $item['icon']['svg'] ) && brand_master_is_valid_svg( $item['icon']['svg'] ) ) {
		                            /* phpcs:ignore */
									echo $item['icon']['svg'];
								}
								if ( $item['label'] ) {
									echo esc_html( $item['label'] );
								}
								?>
							</a>
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
