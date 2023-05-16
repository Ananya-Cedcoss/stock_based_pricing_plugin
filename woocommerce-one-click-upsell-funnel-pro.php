<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since             1.0.0
 * @package           woocommerce-one-click-upsell-funnel-pro
 *
 * @wordpress-plugin
 * Plugin Name:       One Click Upsell Funnel For WooCommerce Pro
 * Plugin URI:        http://wpswings.com/product/one-click-upsell-funnel-for-woocommerce-pro/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * Description:       Show exclusive post-checkout offers to your customers. Create dedicated Upsell offer pages. Offers that are relevant and benefits your customers on the existing purchase and so increase Average Order Value and your Revenue.
 * Version:           3.6.6
 *
 * Requires at least:     4.4
 * Tested up to:          5.9.2
 * WC requires at least:  3.0
 * WC tested up to:       6.3.1
 *
 * Author:            WP Swings
 * Author URI:        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * License:           WP Swings License
 * License URI:       https://wpswings.com/license-agreement.txt
 * Text Domain:       one-click-upsell-funnel-for-woocommerce-pro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * When activated this plugin.
 */
function wps_upsell_plugin_activation() {

	$activation['status']  = true;
	$activation['message'] = '';

	// Dependant plugin.
	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

		$activation['status']  = false;
		$activation['message'] = 'woo_inactive';

	}

	return $activation;
}

$old_org_present   = false;
$installed_plugins = get_plugins();

if ( array_key_exists( 'woo-one-click-upsell-funnel/woocommerce-one-click-upsell-funnel.php', $installed_plugins ) ) {
	$base_plugin = $installed_plugins['woo-one-click-upsell-funnel/woocommerce-one-click-upsell-funnel.php'];
	if ( version_compare( $base_plugin['Version'], '3.2.0', '<' ) ) {
		$old_org_present = true;
	}
}

$wps_upsell_plugin_activation = wps_upsell_plugin_activation();

if ( true === $wps_upsell_plugin_activation['status'] ) {

	// Migrate license keys now.
	$wps_wocuf_pro_license_key = get_option( 'wps_wocuf_pro_license_key', '' );
	$mwb_wocuf_pro_license_key = get_option( 'mwb_wocuf_pro_license_key', '' );
	$thirty_days               = get_option( 'mwb_wocuf_pro_activated_timestamp', 0 );
	$license_check             = get_option( 'mwb_wocuf_pro_license_check', false );

	if ( ! empty( $mwb_wocuf_pro_license_key ) && empty( $wps_wocuf_pro_license_key ) ) {
		update_option( 'wps_wocuf_pro_license_key', $mwb_wocuf_pro_license_key );
		update_option( 'wps_wocuf_pro_activated_timestamp', $thirty_days );
		update_option( 'wps_wocuf_pro_license_check', $license_check );
		$wps_wocuf_pro_license_key = get_option( 'wps_wocuf_pro_license_key', '' );
	}

	if ( true === $old_org_present ) {

		// Try org update to minimum.
		add_action( 'admin_notices', 'wps_upgrade_old_plugin' );
		/**
		 * Try org update to minimum.
		 */
		function wps_upgrade_old_plugin() {
			require_once 'wps-wocuf-auto-download-free.php';
			wps_wocuf_org_replace_plugin();
		}

		/**
		 * Migration to new domain notice.
		 *
		 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
		 * @param array  $plugin_data An array of plugin data.
		 * @param string $status Status filter currently applied to the plugin list.
		 */
		function wps_wocuf_upgrade_notice( $plugin_file, $plugin_data, $status ) {

			?>
		<tr class="plugin-update-tr active notice-warning notice-alt">
			<td colspan="4" class="plugin-update colspanchange">
				<div class="notice notice-error inline update-message notice-alt">
					<p class='wps-notice-title wps-notice-section'>
						<?php esc_html_e( 'Heads up, We highly recommend Also Update Latest Org Plugin. The latest update includes some substantial changes across different areas of the plugin.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
					</p>
				</div>
			</td>
		</tr>
		<style>
			.wps-notice-section > p:before {
				content: none;
			}
		</style>
			<?php
		}
		add_action( 'after_plugin_row_woo-one-click-upsell-funnel/woocommerce-one-click-upsell-funnel.php', 'wps_wocuf_upgrade_notice', 0, 3 );
		add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), 'wps_wocuf_upgrade_notice', 0, 3 );
	} else {
		add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), 'wps_wocuf_migrate_notice', 0, 3 );
		/**
		 * Migration to new domain notice.
		 *
		 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
		 * @param array  $plugin_data An array of plugin data.
		 * @param string $status Status filter currently applied to the plugin list.
		 */
		function wps_wocuf_migrate_notice( $plugin_file, $plugin_data, $status ) {

			?>
			<tr class="plugin-update-tr active notice-warning notice-alt">
				<td colspan="4" class="plugin-update colspanchange">
					<div class="notice notice-error inline update-message notice-alt">
						<p class='wps-notice-title wps-notice-section'>
							<?php esc_html_e( 'Heads up. The latest update includes some substantial changes across different areas of the plugin. Please ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-wocuf-pro-setting&tab=funnels-list' ) ); ?>"><?php esc_html_e( 'Click Here', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
							<?php esc_html_e( 'to goto migration panel.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
						</p>
					</div>
				</td>
			</tr>
			<style>
				.wps-notice-section > p:before {
					content: none;
				}
			</style>
			<?php
		}
	}

	define( 'WPS_WOCUF_PRO_URL', plugin_dir_url( __FILE__ ) );

	define( 'WPS_WOCUF_PRO_DIRPATH', plugin_dir_path( __FILE__ ) );

	define( 'WPS_WOCUF_PRO_VERSION', '3.6.6' );

	if ( ! defined( 'WPS_WOCUF_PRO_SPECIAL_SECRET_KEY' ) ) {
		define( 'WPS_WOCUF_PRO_SPECIAL_SECRET_KEY', '59f32ad2f20102.74284991' );
	}

	if ( ! defined( 'WPS_WOCUF_PRO_LICENSE_SERVER_URL' ) ) {
		define( 'WPS_WOCUF_PRO_LICENSE_SERVER_URL', 'https://wpswings.com' );
	}

	if ( ! defined( 'WPS_WOCUF_PRO_ITEM_REFERENCE' ) ) {
		define( 'WPS_WOCUF_PRO_ITEM_REFERENCE', 'WooCommerce One Click Upsell Funnel Pro' );
	}

	add_filter( 'plugin_row_meta', 'wps_wocuf_pro_add_important_links', 10, 2 );

	/**
	 * Add custom links for getting premium version.
	 *
	 * @param   string $links link to index file of plugin.
	 * @param   string $file index file of plugin.
	 *
	 * @since    1.0.0
	 */
	function wps_wocuf_pro_add_important_links( $links, $file ) {

		if ( strpos( $file, 'woocommerce-one-click-upsell-funnel-pro.php' ) !== false ) {

			$row_meta = array(
				'demo'    => '<a href="https://demo.wpswings.com/one-click-upsell-funnel-for-woocommerce-pro/?utm_source=wpswings-upsell-demo&utm_medium=upsell-pro-backend&utm_campaign=upsell-demo" target="_blank"><img style="width: 15px;margin: 2px;" src="' . esc_url( WPS_WOCUF_PRO_URL ) . 'admin/resources/icons/Demo.svg" class="wps-info-img" alt="Demo image">' . esc_html__( 'Demo', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</a>',
				'doc'     => '<a href="https://docs.wpswings.com/one-click-upsell-funnel-for-woocommerce-pro/?utm_source=wpswings-upsell-doc&utm_medium=upsell-pro-backend&utm_campaign=upsell-doc" target="_blank"><img style="width: 15px;margin: 2px;" src="' . esc_url( WPS_WOCUF_PRO_URL ) . 'admin/resources/icons/Documentation.svg" class="wps-info-img" alt="Documentation image">' . esc_html__( 'Documentation', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</a>',
				'support' => '<a href="https://wpswings.com/submit-query/?utm_source=wpswings-submit-query&utm_medium=oorder-bump-pro-backend&utm_campaign=query" target="_blank"><img style="width: 15px;margin: 2px;" src="' . esc_url( WPS_WOCUF_PRO_URL ) . 'admin/resources/icons/Support.svg" class="wps-info-img" alt="DeSupportmo image">' . esc_html__( 'Support', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-woocommerce-one-click-upsell-funnel-pro-activator.php
	 */
	function activate_woocommerce_one_click_upsell_funnel_pro() {
		if ( ! wp_next_scheduled( 'wps_wocuf_pro_check_license_daily' ) ) {
			wp_schedule_event( time(), 'daily', 'wps_wocuf_pro_check_license_daily' );
		}

		require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-one-click-upsell-funnel-pro-activator.php';
		Woocommerce_One_Click_Upsell_Funnel_Pro_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-woocommerce-one-click-upsell-funnel-pro-deactivator.php
	 */
	function deactivate_woocommerce_one_click_upsell_funnel_pro() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-one-click-upsell-funnel-pro-deactivator.php';
		woocommerce_one_click_upsell_funnel_pro_Deactivator::deactivate();
	}

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wps_wocuf_pro_plugin_settings_link' );

	/**
	 * Add plugin link.
	 *
	 * @param array $links Links.
	 */
	function wps_wocuf_pro_plugin_settings_link( $links ) {
		if ( is_plugin_active( 'woo-one-click-upsell-funnel/woocommerce-one-click-upsell-funnel.php' ) ) {

			$plugin_links = array(
				'<a href="' .
					admin_url( 'admin.php?page=wps-wocuf-pro-setting' ) .
					'">' . __( 'Settings', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</a>',
			);

			$links = array_merge( $plugin_links, $links );

		} else {

			$plugin_links = array(
				'<a href="' .
				admin_url( 'plugin-install.php?s=upsell%20funnel&tab=search&type=term' ) .
				'">' . __( 'Get Free Plugin', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</a>',
			);

			$links = array_merge( $plugin_links, $links );

			$plugin_links = array(
				'<a href="' .
					admin_url( 'admin.php?page=wps-wocuf-pro-setting' ) .
					'">' . __( 'Settings', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</a>',
			);

			$links = array_merge( $plugin_links, $links );
		}

		return $links;
	}

	register_activation_hook( __FILE__, 'activate_woocommerce_one_click_upsell_funnel_pro' );

	register_deactivation_hook( __FILE__, 'deactivate_woocommerce_one_click_upsell_funnel_pro' );

	/**
	 * Plugin Auto Update.
	 */
	function auto_update_woocommerce_one_click_upsell_funnel_pro() {

		$wps_wocuf_pro_license_key = get_option( 'wps_wocuf_pro_license_key', '' );
		$mwb_wocuf_pro_license_key = get_option( 'mwb_wocuf_pro_license_key', '' );
		if ( ! empty( $mwb_wocuf_pro_license_key ) && empty( $wps_wocuf_pro_license_key ) ) {
			update_option( 'wps_wocuf_pro_license_key', $mwb_wocuf_pro_license_key );
			$wps_wocuf_pro_license_key = get_option( 'wps_wocuf_pro_license_key', '' );
		}

		define( 'WPS_WOCUF_PRO_LICENSE_KEY', $wps_wocuf_pro_license_key );
		define( 'WPS_WOCUF_PRO_BASE_FILE', __FILE__ );
		require_once 'class-wps-wocuf-pro-update.php';
	}

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-one-click-upsell-funnel-pro.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_woocommerce_one_click_upsell_funnel_pro() {

		// Plugin Auto Update.
		auto_update_woocommerce_one_click_upsell_funnel_pro();
		$plugin = new Woocommerce_One_Click_Upsell_Funnel_Pro();
		$plugin->run();
	}

	run_woocommerce_one_click_upsell_funnel_pro();

	// Add admin error notice.
	add_action( 'admin_notices', 'wps_upsell_plugin_activation_admin_notice' );

	/**
	 * This function is used to display plugin activation error notice.
	 */
	function wps_upsell_plugin_activation_admin_notice() {

		global $wps_upsell_plugin_activation;

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		// To hide Plugin activated notice.
		unset( $_GET['activate'] );

		?>

		<?php if ( ! is_plugin_active( 'woo-one-click-upsell-funnel/woocommerce-one-click-upsell-funnel.php' ) ) : ?>

			<div class="notice notice-error is-dismissible">
				<p><strong><?php esc_html_e( 'One Click Upsell Funnel for Woocommerce' ); ?></strong><?php esc_html_e( ' is not activated, Please activate One Click Upsell Funnel for Woocommerce first to activate ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?><strong><?php esc_html_e( 'WooCommerce One Click Upsell Funnel Pro' ); ?></strong><?php esc_html_e( '.' ); ?></p>
			</div>

			<?php
		endif;
	}
} else {

	add_action( 'admin_init', 'wps_upsell_plugin_activation_failure' );

	/**
	 * Deactivate this plugin.
	 */
	function wps_upsell_plugin_activation_failure() {

		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	// Add admin error notice.
	add_action( 'admin_notices', 'wps_upsell_plugin_activation_admin_notice' );

	/**
	 * This function is used to display plugin activation error notice.
	 */
	function wps_upsell_plugin_activation_admin_notice() {

		global $wps_upsell_plugin_activation;

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		// To hide Plugin activated notice.
		unset( $_GET['activate'] );

		?>

		<?php if ( 'woo_inactive' === $wps_upsell_plugin_activation['message'] ) : ?>

			<div class="notice notice-error is-dismissible">
				<p><strong><?php esc_html_e( 'WooCommerce', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></strong><?php esc_html_e( ' is not activated, Please activate WooCommerce first to activate ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?><strong><?php esc_html_e( 'WooCommerce One Click Upsell Funnel Pro', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></strong><?php esc_html_e( '.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>
			</div>

		<?php endif; ?>
		<?php
	}
}
