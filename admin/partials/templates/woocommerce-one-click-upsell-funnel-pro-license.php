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
 * @subpackage woocommerce-one-click-upsell-funnel-pro/admin/partials/templates
 */

?>

<div class="wps-wocuf-pro-wrap">
	<h1><?php esc_html_e( 'Your License', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h1>
	<div class="wps_wocuf_pro_license_text">

		<p>
		<?php
			esc_html_e( 'This is the License Activation Panel. After purchasing extension from WP Swings you will get the purchase code of this extension. Please verify your purchase below so that you can use feature of this plugin.', 'one-click-upsell-funnel-for-woocommerce-pro' );
		?>
		</p>

		<form id="wps_wocuf_pro_license_form"> 
			<table class="wps-wocuf-pro-form-table">
				<tr>
				<th scope="row"><label for="puchase-code"><?php esc_html_e( 'Purchase Code : ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label></th>
				<td>
					<input type="text" id="wps_wocuf_pro_license_key" name="purchase-code" required="" size="30" class="wps-wocuf-pro-purchase-code" value="" placeholder="<?php esc_html_e( 'Enter your code here...', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>">
					<div id="wps_upsell_license_ajax_loader"><img src="<?php echo 'images/spinner.gif'; ?>"></div>
				</td>
				</tr>
			</table>
			<p id="wps_wocuf_pro_license_activation_status"></p>
			<p class="submit">
			<button id="wps_wocuf_pro_license_activate" required="" class="button-primary woocommerce-save-button" name="wps_wocuf_pro_license_settings"><?php esc_html_e( 'Validate', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></button>
			</p>
		</form>
	</div>

</div>
