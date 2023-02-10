<?php
/**
 * The update-specific functionality of the plugin.
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPS_Wocuf_Pro_Update' ) ) {
	/**
	 * Update Handler class.
	 */
	class WPS_Wocuf_Pro_Update {

		/**
		 * Constructer.
		 */
		public function __construct() {
			register_activation_hook( WPS_WOCUF_PRO_BASE_FILE, array( $this, 'wps_check_activation' ) );
			add_action( 'wps_wocuf_pro_check_event', array( $this, 'wps_check_update' ) );
			add_filter( 'http_request_args', array( $this, 'wps_updates_exclude' ), 5, 2 );
			register_deactivation_hook( WPS_WOCUF_PRO_BASE_FILE, array( $this, 'wps_check_deactivation' ) );

			$plugin_update = get_option( 'wps_wocuf_plugin_update', 'false' );

			if ( 'true' === $plugin_update ) {

				// To add view details content in plugin update notice on plugins page.
				add_action( 'install_plugins_pre_plugin-information', array( $this, 'wps_wocuf_details' ) );
				// To add plugin update notice after plugin update message.
				add_action( 'in_plugin_update_message-woocommerce-one-click-upsell-funnel-pro/woocommerce-one-click-upsell-funnel-pro.php', array( $this, 'wps_wocuf_in_plugin_update_notice' ), 10, 2 );
			}

		}

		/**
		 * Deactivation Checkup Event.
		 */
		public function wps_check_deactivation() {
			wp_clear_scheduled_hook( 'wps_wocuf_pro_check_event' );
		}

		/**
		 * Activation Checkup Event.
		 */
		public function wps_check_activation() {
			wp_schedule_event( time(), 'daily', 'wps_wocuf_pro_check_event' );
		}

		/**
		 * Update Checkup Event.
		 */
		public function wps_wocuf_details() {

			global $tab;

			$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
			$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

			if ( ! $id_nonce_verified ) {
				wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
			}

			// change $_REQUEST['plugin] to your plugin slug name.
			if ( 'plugin-information' === $tab && ! empty( $_REQUEST['plugin'] ) && 'woocommerce-one-click-upsell-funnel-pro' === $_REQUEST['plugin'] ) {

				$data = $this->get_plugin_update_data();

				if ( is_wp_error( $data ) || empty( $data ) ) {
					return;
				}

				if ( empty( $data['response']['code'] ) || 200 !== $data['response']['code'] ) {
					return;
				}

				if ( ! empty( $data['body'] ) ) {

					$all_data = json_decode( $data['body'], true );

					if ( ! empty( $all_data ) && is_array( $all_data ) ) {

						$this->create_html_data( $all_data );

						wp_die();
					}
				}
			}
		}

		/**
		 * Update Checkup Event.
		 */
		public function get_plugin_update_data() {

			// replace with your plugin url.
			$url      = 'https://wpswings.com/pluginupdates/woocommerce-one-click-upsell-funnel-pro/update.php';
			$postdata = array(
				'action'       => 'check_update',
				'license_code' => WPS_WOCUF_PRO_LICENSE_KEY,
			);

			$args = array(
				'method' => 'POST',
				'body'   => $postdata,
			);

			$data = wp_remote_post( $url, $args );

			return $data;
		}

		/**
		 * Render HTML content.
		 *
		 * @param array $all_data complete banner data.
		 */
		public function create_html_data( $all_data ) {
			?>
			<style>
				#TB_window{
					top : 4% !important;
				}
				.wps_wocuf_banner > img {
					width: 50%;
				}
				.wps_wocuf_banner > h1 {
					margin-top: 0px;
				}
				.wps_wocuf_banner {
					text-align: center;
				}
				.wps_wocuf_description > h4 {
					background-color: #3779B5;
					padding: 5px;
					color: #ffffff;
					border-radius: 5px;
				}
				.wps_wocuf_changelog_details > h4 {
					background-color: #3779B5;
					padding: 5px;
					color: #ffffff;
					border-radius: 5px;
				}
			</style>
			<?php
				$plugin_name        = ! empty( $all_data['name'] ) ? $all_data['name'] : '';
				$plugin_version     = ! empty( $all_data['version'] ) ? $all_data['version'] : '';
				$plugin_logo        = ! empty( $all_data['banners']['logo'] ) ? $all_data['banners']['logo'] : '';
				$plugin_description = ! empty( $all_data['sections']['description'] ) ? $all_data['sections']['description'] : '';
				$plugin_changelog   = ! empty( $all_data['sections']['changelog'] ) ? $all_data['sections']['changelog'] : '';
			?>
			<div class="wps_wocuf_details_wrapper">
				<div class="wps_wocuf_banner">
					<h1><?php echo esc_html( $plugin_name ) . ' ' . esc_html( $plugin_version ); ?></h1>
					<img src="<?php echo esc_url( $plugin_logo ); ?>"> 
				</div>

				<div class="wps_wocuf_description">
					<h4><?php esc_html_e( 'Plugin Description', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h4>
					<span><?php echo wp_kses_post( $plugin_description ); ?></span>
				</div>
				<div class="wps_wocuf_changelog_details">
					<h4><?php esc_html_e( 'Plugin Change Log', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h4>
					<span><?php echo wp_kses_post( $plugin_changelog ); ?></span>
				</div>
			</div>
			<?php
		}

		/**
		 * Render update notice content.
		 */
		public function wps_wocuf_in_plugin_update_notice() {

			$data = $this->get_plugin_update_data();

			if ( is_wp_error( $data ) || empty( $data ) ) {

				return;
			}

			if ( empty( $data['response']['code'] ) || 200 !== $data['response']['code'] ) {
				return false;
			}

			if ( isset( $data['body'] ) ) {

				$all_data = json_decode( $data['body'], true );

				if ( is_array( $all_data ) && ! empty( $all_data['sections']['update_notice'] ) ) {

					?>

					<style type="text/css">

						#woocommerce-one-click-upsell-funnel-pro-update .dummy {
							display: none;
						}

						#wps_wocuf_in_plugin_update_div p:before {
							content: none;
						}

						#wps_wocuf_in_plugin_update_div {
							border-top: 1px solid #ffb900;
							margin-left: -13px;
							padding-left: 20px;
							padding-top: 10px;
							padding-bottom: 5px;
						}

						#wps_wocuf_in_plugin_update_div ul {
							list-style-type: decimal;
							padding-left: 20px;
						}

					</style>

					<?php

					echo '</p><div id="wps_wocuf_in_plugin_update_div">' . wp_kses_post( $all_data['sections']['update_notice'] ) . '</div><p class="dummy">';
				}
			}
		}

		/**
		 * Event: update.
		 */
		public function wps_check_update() {
			global $wp_version;
			$update_check_wocuf = 'https://wpswings.com/pluginupdates/woocommerce-one-click-upsell-funnel-pro/update.php';
			$plugin_folder      = plugin_basename( dirname( WPS_WOCUF_PRO_BASE_FILE ) );
			$plugin_file        = basename( ( WPS_WOCUF_PRO_BASE_FILE ) );
			if ( defined( 'WP_INSTALLING' ) ) {
				return false;
			}
			$postdata = array(
				'action'      => 'check_update',
				'license_key' => WPS_WOCUF_PRO_LICENSE_KEY,
			);

			$args = array(
				'method' => 'POST',
				'body'   => $postdata,
			);

			$response = wp_remote_post( $update_check_wocuf, $args );

			if ( is_wp_error( $response ) || empty( $response['body'] ) ) {

				return;
			}

			if ( empty( $response['response']['code'] ) || 200 !== $response['response']['code'] ) {
				update_option( 'wps_wocuf_plugin_update', 'false' );
				return false;
			}

			list($version, $url) = explode( '~', $response['body'] );

			if ( $this->wps_plugin_get( 'Version' ) >= $version ) {

				update_option( 'wps_wocuf_plugin_update', 'false' );
				return false;
			}

			update_option( 'wps_wocuf_plugin_update', 'true' );

			$plugin_transient = get_site_transient( 'update_plugins' );
			$a                = array(
				'slug'        => $plugin_folder,
				'new_version' => $version,
				'url'         => $this->wps_plugin_get( 'AuthorURI' ),
				'package'     => $url,
			);
			$o                = (object) $a;
			$plugin_transient->response[ $plugin_folder . '/' . $plugin_file ] = $o;
			set_site_transient( 'update_plugins', $plugin_transient );
		}

		/**
		 * Exclude update notice content.
		 *
		 * @param array  $r    request.
		 * @param string $url request url.
		 */
		public function wps_updates_exclude( $r, $url ) {
			if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) ) {
				return $r;
			}
			$plugins = unserialize( $r['body']['plugins'] ); //phpcs:ignore
			if ( ! empty( $plugins->plugins ) ) {
				unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
			}
			if ( ! empty( $plugins->active ) ) {
				unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active, true ) ] );
			}
			$r['body']['plugins'] = serialize( $plugins ); //phpcs:ignore
			return $r;
		}

		/**
		 * Returns current plugin info.
		 *
		 * @param string $i index.
		 */
		public function wps_plugin_get( $i ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugin_folder = get_plugins( '/' . plugin_basename( dirname( WPS_WOCUF_PRO_BASE_FILE ) ) );
			$plugin_file   = basename( ( WPS_WOCUF_PRO_BASE_FILE ) );
			return $plugin_folder[ $plugin_file ][ $i ];
		}
	}
	new WPS_Wocuf_Pro_Update();
}
?>
