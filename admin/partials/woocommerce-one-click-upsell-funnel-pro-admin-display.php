<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ONBOARD_PLUGIN_NAME', 'WooCommerce One Click Upsell Funnel Pro' );

if ( class_exists( 'WPSwings_Onboarding_Helper' ) ) {
	$this->onboard = new WPSwings_Onboarding_Helper();
}

$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

if ( ! $id_nonce_verified ) {
	wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'settings';
?>
<div class="wps-notice-wrapper">
<?php do_action( 'wps_wocuf_pro_setting_tab_active', '', '', '' ); ?>
</div>

<?php if ( ! wps_upsell_elementor_plugin_active() && false === get_transient( 'wps_upsell_elementor_inactive_notice' ) ) : ?>

<div id="wps_upsell_elementor_notice" class="notice notice-info is-dismissible">
	<p><span class="wps_upsell_heading_span"><?php esc_html_e( 'We have integrated with Elementor', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span><?php esc_html_e( ' – now the most advanced WordPress page builder can be used to completely customize Upsell Offer pages. Moreover we provide three stunning and beautiful offer templates.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>

	<p><?php esc_html_e( 'To completely utilize all features of this plugin please activate Elementor.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>

	<p><?php esc_html_e( 'Elementor is FREE and available on ORG ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?><a href="https://wordpress.org/plugins/elementor/" target="_blank"><?php esc_html_e( 'here', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a></p>

	<p><?php esc_html_e( 'You don\'t need to worry about Elementor as it works independently and won\'t conflict with other page builders or WordPress new editor.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>

	<p class="submit">

		<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ) ); ?>" id="wps_upsell_activate_elementor" class="button" target="_blank"><?php esc_html_e( 'Install and activate Elementor now &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
		<br>
		<a id="wps_upsell_dismiss_elementor_inactive_notice" href="javascript:void(0)" class="button"><?php esc_html_e( 'Dismiss this notice', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>

	</p>
</div>

<?php endif; ?>

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
		<a class="nav-tab <?php echo esc_html( 'creation-setting' === $active_tab ? 'nav-tab-active' : '' ); ?>" href="?page=wps-wocuf-pro-setting&tab=creation-setting"><?php esc_html_e( 'Save Funnel', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
		<a class="nav-tab <?php echo esc_html( 'funnels-list' === $active_tab ? 'nav-tab-active' : '' ); ?>" href="?page=wps-wocuf-pro-setting&tab=funnels-list"><?php esc_html_e( 'Funnels List', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
		<a class="nav-tab <?php echo esc_html( 'shortcodes' === $active_tab ? 'nav-tab-active' : '' ); ?>" href="?page=wps-wocuf-pro-setting&tab=shortcodes"><?php esc_html_e( 'Shortcodes', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
		<a class="nav-tab <?php echo esc_html( 'settings' === $active_tab ? 'nav-tab-active' : '' ); ?>" href="?page=wps-wocuf-pro-setting&tab=settings"><?php esc_html_e( 'Global Settings', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>

		<?php

		$callname_lic = Woocommerce_One_Click_Upsell_Funnel_pro::$lic_callback_function;

		if ( ! Woocommerce_One_Click_Upsell_Funnel_pro::$callname_lic() ) :
			?>

			<a href="?page=wps-wocuf-pro-setting&tab=license" class="nav-tab <?php echo 'license' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'License Activation', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>

		<?php endif; ?>

		<?php do_action( 'wps_wocuf_pro_setting_tab' ); ?>	
	</nav>
	<?php

	if ( 'creation-setting' === $active_tab ) {
		include_once 'templates/wps-wocuf-pro-creation.php';
	} elseif ( 'funnels-list' === $active_tab ) {
		include_once 'templates/wps-wocuf-pro-funnels-list.php';
	} elseif ( 'shortcodes' === $active_tab ) {
		include_once 'templates/wps-wocuf-pro-shortcodes.php';
	} elseif ( 'settings' === $active_tab ) {
		include_once 'templates/wps-wocuf-pro-settings.php';
	} elseif ( 'license' === $active_tab && ! Woocommerce_One_Click_Upsell_Funnel_Pro::$callname_lic() ) {
		include_once 'templates/woocommerce-one-click-upsell-funnel-pro-license.php';
	}
		do_action( 'wps_wocuf_pro_setting_tab_html' );
	?>
</div>
