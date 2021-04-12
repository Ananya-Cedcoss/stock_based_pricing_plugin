<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Stock_based_pricing_plugin
 * @subpackage Stock_based_pricing_plugin/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit(); // Exit if accessed directly.
}

global $sbpp_mwb_sbpp_obj;
$sbpp_active_tab   = isset( $_GET['sbpp_tab'] ) ? sanitize_key( $_GET['sbpp_tab'] ) : 'stock-based-pricing-plugin-general';
$sbpp_default_tabs = $sbpp_mwb_sbpp_obj->mwb_sbpp_plug_default_tabs();
?>
<header>
	<div class="mwb-header-container mwb-bg-white mwb-r-8">
		<h1 class="mwb-header-title"><?php echo esc_attr( strtoupper( str_replace( '-', ' ', $sbpp_mwb_sbpp_obj->sbpp_get_plugin_name() ) ) ); ?></h1>
		<a href="https://docs.makewebbetter.com/" target="_blank" class="mwb-link"><?php esc_html_e( 'Documentation', 'stock-based-pricing-plugin' ); ?></a>
		<span>|</span>
		<a href="https://makewebbetter.com/contact-us/" target="_blank" class="mwb-link"><?php esc_html_e( 'Support', 'invoice-system-for-woocommerce' ); ?></a>
	</div>
</header>

<main class="mwb-main mwb-bg-white mwb-r-8">
	<nav class="mwb-navbar">
		<ul class="mwb-navbar__items">
			<?php
			if ( is_array( $sbpp_default_tabs ) && ! empty( $sbpp_default_tabs ) ) {

				foreach ( $sbpp_default_tabs as $sbpp_tab_key => $sbpp_default_tabs ) {

					$sbpp_tab_classes = 'mwb-link ';

					if ( ! empty( $sbpp_active_tab ) && $sbpp_active_tab === $sbpp_tab_key ) {
						$sbpp_tab_classes .= 'active';
					}
					?>
					<li>
						<a id="<?php echo esc_attr( $sbpp_tab_key ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=stock_based_pricing_plugin_menu' ) . '&sbpp_tab=' . esc_attr( $sbpp_tab_key ) ); ?>" class="<?php echo esc_attr( $sbpp_tab_classes ); ?>"><?php echo esc_html( $sbpp_default_tabs['title'] ); ?></a>
					</li>
					<?php
				}
			}
			?>
		</ul>
	</nav>

	<section class="mwb-section">
		<div>
			<?php 
				do_action( 'mwb_sbpp_before_general_settings_form' );
						// if submenu is directly clicked on woocommerce.
				if ( empty( $sbpp_active_tab ) ) {
					$sbpp_active_tab = 'mwb_sbpp_plug_general';
			}
				// look for the path based on the tab id in the admin templates.
				$sbpp_tab_content_path = 'admin/partials/' . $sbpp_active_tab . '.php';

				$sbpp_mwb_sbpp_obj->mwb_sbpp_plug_load_template( $sbpp_tab_content_path );

				do_action( 'mwb_sbpp_after_general_settings_form' ); 
			?>
		</div>
	</section>
