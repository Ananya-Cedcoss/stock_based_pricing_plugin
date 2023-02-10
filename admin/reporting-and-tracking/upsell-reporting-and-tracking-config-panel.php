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
 * @subpackage  woocommerce-one-click-upsell-funnel-pro/admin/reporting-and-tracking/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

if ( ! $id_nonce_verified ) {
	wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'reporting';

if ( 'overview' === get_transient( 'wps_upsell_default_settings_tab' ) ) {

	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'overview';
}
?>
<div class="wps-notice-wrapper">
<?php do_action( 'wps_wocuf_pro_setting_tab_active', '', '', '' ); ?>
</div>

<div class="wrap woocommerce" id="wps_wocuf_pro_setting_wrapper">

	<!-- To make WordPress notice appear at this place. As it searchs from top and appears at the 1st heading tag-->
	<h1></h1>

	<div class="hide"  id="wps_wocuf_pro_loader">	
		<img id="wps-wocuf-loading-image" src="<?php echo 'images/spinner-2x.gif'; ?>" >
	</div>

	<h1 class="wps_wocuf_pro_setting_title"><?php esc_html_e( 'WooCommerce One Click Upsell Funnel Pro', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>

	<span class="wps_wocuf_pro_setting_title_version">
	<?php
	esc_html_e( 'v', 'one-click-upsell-funnel-for-woocommerce-pro' );
	echo esc_html( WPS_WOCUF_PRO_VERSION );
	?>
	</span>

	</h1>

	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">

		<a class="nav-tab <?php echo 'reporting' === $active_tab ? 'nav-tab-active' : ''; ?>" href="?page=wps-wocuf-setting-tracking&tab=reporting"><?php esc_html_e( 'Sales Reports', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
		<a class="nav-tab <?php echo 'ga-setting' === $active_tab ? 'nav-tab-active' : ''; ?>" href="?page=wps-wocuf-setting-tracking&tab=ga-setting"><?php esc_html_e( 'Google Analytics', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
		<a class="nav-tab <?php echo 'pixel-setting' === $active_tab ? 'nav-tab-active' : ''; ?>" href="?page=wps-wocuf-setting-tracking&tab=pixel-setting"><?php esc_html_e( 'FB Pixel', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
		<a class="nav-tab <?php echo 'overview' === $active_tab ? 'nav-tab-active' : ''; ?>" href="?page=wps-wocuf-setting-tracking&tab=overview"><?php esc_html_e( 'Overview', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>

		<?php do_action( 'wps_wocuf_pro_setting_tab' ); ?>	
	</nav>
	<?php

	if ( 'reporting' === $active_tab ) {
		include_once 'templates/reporting.php';
	} elseif ( 'ga-setting' === $active_tab ) {
		include_once 'templates/ga-settings.php';
	} elseif ( 'pixel-setting' === $active_tab ) {
		include_once 'templates/pixel-settings.php';
	} elseif ( 'overview' === $active_tab ) {
		include_once 'templates/tracking-overview.php';
	}

	do_action( 'wps_wocuf_pro_setting_tab_html' );
	?>
</div>
