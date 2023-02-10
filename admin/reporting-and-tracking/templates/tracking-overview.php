<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package     woocommerce-one-click-upsell-funnel-pro
 * @subpackage  woocommerce-one-click-upsell-funnel-pro/admin/reporting-and-tracking/templates/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div id="wps_upsell_lite_overview">
	<div id="wps_upsell_lite_overview_pro_version">

		<h2><?php esc_html_e( 'eCommerce Analytics & Tracking', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h2>
		<h3><?php esc_html_e( 'Supported Analytics Tools', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h3>

		<div class="wps_upsell_overview_supported_product">
			<div class="wps_upsell_overview_product_icon simple">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-wocuf-setting-tracking&tab=ga-setting' ) ); ?>">
					<img class="wps_upsell_lite_tool_ga" src="<?php echo esc_url( WPS_WOCUF_PRO_URL . 'admin/reporting-and-tracking/resources/icons/google-analytics.svg' ); ?>">
				</a>
			</div>
			<div class="wps_upsell_overview_product_icon simple">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-wocuf-setting-tracking&tab=pixel-setting' ) ); ?>">
					<img class="wps_upsell_lite_tool_fb" src="<?php echo esc_url( WPS_WOCUF_PRO_URL . 'admin/reporting-and-tracking/resources/icons/facebook-pixel.png' ); ?>">
				</a>
			</div>
		</div>
	</div>
</div>
