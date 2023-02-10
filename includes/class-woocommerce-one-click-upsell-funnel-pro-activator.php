<?php
/**
 * Fired during plugin activation
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/includes
 * @author     wpswings <webmaster@wpswings.com>
 */
class Woocommerce_One_Click_Upsell_Funnel_Pro_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		$timestamp = get_option( 'wps_wocuf_pro_activated_timestamp', 'not_set' );

		if ( 'not_set' === $timestamp ) {

			$current_time = time();

			$thirty_days = strtotime( '+30 days', $current_time );

			update_option( 'wps_wocuf_pro_activated_timestamp', $thirty_days );
		}

		/**
		 * Generating default offer page at the time of plugin activation
		 */
		$wps_wocuf_pro_offer_default_page_id = get_option( 'wps_wocuf_pro_funnel_default_offer_page', '' );

		// For WordPress 5 and + versions, Guttenberg content will be used for default offer page.
		$post_content = '5' <= get_bloginfo( 'version' ) && function_exists( 'wps_upsell_gutenberg_offer_content' ) ? wps_upsell_gutenberg_offer_content() : '[wps_wocuf_pro_funnel_default_offer_page]';
		if ( empty( $wps_wocuf_pro_offer_default_page_id ) || get_post_status( $wps_wocuf_pro_offer_default_page_id ) !== 'publish' ) {
			$wps_wocuf_pro_funnel_page = array(
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_content'   => $post_content,
				'post_name'      => 'special-offer',
				'post_status'    => 'publish',
				'post_title'     => 'Special Offer',
				'post_type'      => 'page',
			);

			$wps_wocuf_pro_post = wp_insert_post( $wps_wocuf_pro_funnel_page );

			update_option( 'wps_wocuf_pro_funnel_default_offer_page', $wps_wocuf_pro_post );
		}

		// Schedule cron for Order payment process If redirected for upsell and still pending.
		if ( ! wp_next_scheduled( 'wps_wocuf_order_cron_schedule' ) ) {

			wp_schedule_event( time(), 'wps_wocuf_twenty_minutes', 'wps_wocuf_order_cron_schedule' );
		}

	}

}
