<?php
/**
 * Dashboard template.
 *
 * Renders the frontend user dashboard from saved settings.
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Dashboard
 * @author     codersantosh <codersantosh@gmail.com>
 * @since      1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* For developer */
do_action( 'brand_master_before_dashboard' );

$brand_master_dashboard_settings       = brand_master_include()->get_settings()['dashboard'];
$brand_master_sorting_content          = $brand_master_dashboard_settings['sidebarContent'];
$brand_master_sorting_content_elements = $brand_master_sorting_content['sort'];
?>
<div class="bm-dashboard at-min-h at-max-w at-flx" data-bm-theme="light">
	<?php
	$brand_master_no_sidebar = '';
	if ( brand_master_dashboard()->has_sorting_contents( $brand_master_sorting_content, $brand_master_sorting_content_elements ) ) {
		echo '<div class="at-bg-cl at-p at-w at-pos at-h at-bdr at-flx at-flx-col bm-dashboard-sidebar">';
		brand_master_dashboard()->get_sorting_contents( $brand_master_sorting_content, $brand_master_sorting_content_elements, 'sidebar' );
		echo '</div>';
	} else {
		$brand_master_no_sidebar = ' bm-no-sidebar at-flx-grw-1';
	}
	?>
	<div class="at-bg-cl at-w at-m bm-dashboard-main<?php echo esc_attr( $brand_master_no_sidebar ); ?>">
		<?php
		$brand_master_sorting_content          = $brand_master_dashboard_settings['headingContent'];
		$brand_master_sorting_content_elements = $brand_master_sorting_content['sort'];

		if ( brand_master_dashboard()->has_sorting_contents( $brand_master_sorting_content, $brand_master_sorting_content_elements ) ) {
			echo '<header class="at-flx at-al-itm-ctr at-gap at-bg-cl at-p at-bdr bm-dashboard-header">';
			brand_master_dashboard()->get_sorting_contents( $brand_master_sorting_content, $brand_master_sorting_content_elements, 'header' );
			echo '</header>';
		}

		$brand_master_current_menu = brand_master_dashboard()->get_current_menu();
		if ( $brand_master_current_menu && $brand_master_current_menu['typeId'] ) {
			$brand_master_page_id = $brand_master_current_menu['typeId'];

			// Perform WP Query.
			$brand_master_page_query = new WP_Query( array( 'page_id' => absint( $brand_master_page_id ) ) );

			// Check if there are results.
			if ( $brand_master_page_query->have_posts() ) {
				// Loop through the results.
				while ( $brand_master_page_query->have_posts() ) {
					$brand_master_page_query->the_post();
					?>
					<div class="at-p bm-dashboard-cont">
						<?php
						the_content();
						?>
					</div>
					<?php

				}
			} else {
				esc_html_e( 'Page not found.', 'brand-master' );
			}
			wp_reset_postdata();
		} else {

			echo '<div class="at-p bm-dashboard-no-cont">';
			echo '<p class="at-txt">';
			esc_html_e( 'Page not selected.', 'brand-master' );
			echo '</p>';
			echo '</div>';

		}
		?>
	</div>	
</div>
<?php
/* For developer */
do_action( 'brand_master_after_dashboard' );
