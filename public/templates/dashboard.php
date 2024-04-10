<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard template
 *
 * @package    Brand_Master
 * @subpackage Brand_Master/Dashboard
 * @author codersantosh <codersantosh@gmail.com>
 * @since      1.0.0
 */

/* For developer */
do_action( 'brand_master_before_dashboard' );

$dashboard_settings       = brand_master_include()->get_settings()['dashboard'];
$sorting_content          = $dashboard_settings['sidebarContent'];
$sorting_content_elements = $sorting_content['sort'];
?>
<div class="bm-dashboard at-min-h at-max-w at-flx" data-bm-theme="light">
	<?php
	$bm_no_sidebar = '';
	if ( brand_master_dashboard()->has_sorting_contents( $sorting_content, $sorting_content_elements ) ) {
		echo '<div class="at-bg-cl at-p at-w at-pos at-h at-bdr at-flx at-flx-col bm-dashboard-sidebar">';
		brand_master_dashboard()->get_sorting_contents( $sorting_content, $sorting_content_elements, 'sidebar' );
		echo '</div>';
	} else {
		$bm_no_sidebar = ' bm-no-sidebar at-flx-grw-1';
	}
	?>
	<div class="at-bg-cl at-w at-m bm-dashboard-main<?php echo esc_attr( $bm_no_sidebar ); ?>">
		<?php
		$sorting_content          = $dashboard_settings['headingContent'];
		$sorting_content_elements = $sorting_content['sort'];

		if ( brand_master_dashboard()->has_sorting_contents( $sorting_content, $sorting_content_elements ) ) {
			echo '<header class="at-flx at-al-itm-ctr at-gap at-bg-cl at-p at-bdr bm-dashboard-header">';
			brand_master_dashboard()->get_sorting_contents( $sorting_content, $sorting_content_elements, 'header' );
			echo '</header>';
		}

		$current_menu = brand_master_dashboard()->get_current_menu();
		if ( $current_menu && $current_menu['typeId'] ) {
			$page_id = $current_menu['typeId'];

			// Perform WP Query.
			$page_query = new WP_Query( array( 'page_id' => absint( $page_id ) ) );

			// Check if there are results.
			if ( $page_query->have_posts() ) {
				// Loop through the results.
				while ( $page_query->have_posts() ) {
					$page_query->the_post();
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
