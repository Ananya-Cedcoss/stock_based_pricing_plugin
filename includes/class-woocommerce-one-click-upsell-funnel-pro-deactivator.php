<?php
/**
 * Fired during plugin deactivation
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run when plugin is deactivated.
 *
 * @since      1.0.0
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/includes
 * @author     wpswings <webmaster@wpswings.com>
 */
class Woocommerce_One_Click_Upsell_Funnel_Pro_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		// Clear scheduled cron for User update.
		if ( wp_next_scheduled( 'wps_wocuf_order_cron_schedule' ) ) {

			wp_clear_scheduled_hook( 'wps_wocuf_order_cron_schedule' );
		}

	}

}
