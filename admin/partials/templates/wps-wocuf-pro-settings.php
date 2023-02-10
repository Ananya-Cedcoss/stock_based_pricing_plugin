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

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

if ( ! $id_nonce_verified ) {
	wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
}

// Save settings on Save changes.
if ( isset( $_POST['wps_wocuf_pro_common_settings_save'] ) ) {

	$wps_upsell_global_options = array();

	// Enable Plugin.
	$wps_upsell_global_options['wps_wocuf_pro_enable_plugin'] = ! empty( $_POST['wps_wocuf_pro_enable_plugin'] ) ? 'on' : 'off';

	// Global product id.
	$wps_upsell_global_options['global_product_id'] = ! empty( $_POST['global_product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['global_product_id'] ) ) : '';

	// Global product discount.
	$wps_upsell_global_options['global_product_discount'] = ! empty( $_POST['global_product_discount'] ) ? sanitize_text_field( wp_unslash( $_POST['global_product_discount'] ) ) : '';

	// Skip similar offer.
	$wps_upsell_global_options['skip_similar_offer'] = ! empty( $_POST['skip_similar_offer'] ) ? sanitize_text_field( wp_unslash( $_POST['skip_similar_offer'] ) ) : '';

	// Remove all styles.
	$wps_upsell_global_options['remove_all_styles'] = ! empty( $_POST['remove_all_styles'] ) ? sanitize_text_field( wp_unslash( $_POST['remove_all_styles'] ) ) : '';

	// Upsell action Message.
	$wps_upsell_global_options['upsell_actions_message'] = ! empty( $_POST['upsell_actions_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['upsell_actions_message'] ) ) : '';

	// Custom CSS.
	$wps_upsell_global_options['global_custom_css'] = ! empty( $_POST['global_custom_css'] ) ? sanitize_textarea_field( wp_unslash( $_POST['global_custom_css'] ) ) : '';

	// Custom JS.
	$wps_upsell_global_options['global_custom_js'] = ! empty( $_POST['global_custom_js'] ) ? sanitize_textarea_field( wp_unslash( $_POST['global_custom_js'] ) ) : '';

	// V3.5.0 :: Smart Skip If already purchased start.
	$wps_upsell_global_options['wps_wocuf_pro_enable_smart_skip'] = ! empty( $_POST['wps_wocuf_pro_enable_smart_skip'] ) ? 'on' : 'off';

	// V3.5.0 :: Price Html format.
	$wps_upsell_global_options['offer_price_html_type'] = ! empty( $_POST['offer_price_html_type'] ) ? sanitize_text_field( wp_unslash( $_POST['offer_price_html_type'] ) ) : '';

	// V3.6.0 :: Free Order Upsell.
	$wps_upsell_global_options['free_upsell_select'] = ! empty( $_POST['free_upsell_select'] ) ? sanitize_text_field( wp_unslash( $_POST['free_upsell_select'] ) ) : '';
	$wps_upsell_global_options['enable_free_upsell'] = ! empty( $_POST['enable_free_upsell'] ) ? 'on' : 'off';

	// Save.
	update_option( 'wps_wocuf_pro_enable_plugin', $wps_upsell_global_options['wps_wocuf_pro_enable_plugin'] );
	update_option( 'wps_upsell_global_options', $wps_upsell_global_options );

	?>

	<!-- Settings saved notice. -->
	<div class="notice notice-success is-dismissible"> 
		<p><strong><?php esc_html_e( 'Settings saved', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></strong></p>
	</div>
	<?php
}

// By default plugin will be enabled.
$wps_wocuf_pro_enable_plugin = get_option( 'wps_wocuf_pro_enable_plugin', 'on' );

$wps_upsell_global_settings = get_option( 'wps_upsell_global_options', array() );

?>

<form action="" method="POST">
	<div class="wps_upsell_table">
		<table class="form-table wps_wocuf_pro_creation_setting">
			<tbody>

				<!-- Enable Plugin start -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label for="wps_wocuf_pro_enable_plugin"><?php esc_html_e( 'Enable Upsell', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>

					<td class="forminp forminp-text">
						<?php
						$attribut_description = esc_html__( 'Enable Upsell plugin.', 'one-click-upsell-funnel-for-woocommerce-pro' );
						wps_wc_help_tip( $attribut_description );
						?>

						<label class="wps_wocuf_pro_enable_plugin_label">
							<input class="wps_wocuf_pro_enable_plugin_input" type="checkbox" <?php echo ( 'on' === $wps_wocuf_pro_enable_plugin ) ? esc_html( "checked='checked'" ) : ''; ?> name="wps_wocuf_pro_enable_plugin" >	
							<span class="wps_wocuf_pro_enable_plugin_span"></span>
						</label>
					</td>
				</tr>
				<!-- Enable Plugin end -->

				<!-- Payment Gateways start -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label><?php esc_html_e( 'Payment Gateways', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>

					<td class="forminp forminp-text">
						<?php
						$attribute_description = esc_html__( 'Please set up and activate Upsell supported payment gateways as offers will only appear through them.', 'one-click-upsell-funnel-for-woocommerce-pro' );
						wps_wc_help_tip( $attribute_description );
						?>
						<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ); ?>"><?php esc_html_e( 'Manage Upsell supported payment gateways &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>		
					</td>
				</tr>
				<!-- Payment Gateways end -->

				<!-- Free Order Upselling start -->

				<?php
					$redirection_based_gateway = wps_upsell_supported_gateways_with_redirection();
					$gateway_holder            = new WC_Payment_Gateways();
					$all_gateways              = $gateway_holder->get_available_payment_gateways();

					$enabled_redirection_gateways = array();

					// Stripe OCU is not compatible.
					unset( $all_gateways['wps-wocuf-pro-stripe-gateway'] );
					// Stripe is not compatible.
					unset( $all_gateways['stripe'] );

				foreach ( $redirection_based_gateway as $key => $gateway_id ) {

					if ( ! empty( $all_gateways[ $gateway_id ] ) ) {

						array_push( $enabled_redirection_gateways, $gateway_id );
					}
				}

				if ( ! empty( $enabled_redirection_gateways ) ) :
					?>
						<tr valign="top">

							<th scope="row" class="titledesc">
								<label for="wps_wocuf_pro_enable_free_upsell"><?php esc_html_e( 'Free Order Upsell', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
							</th>

							<td class="forminp forminp-text">
								<?php
								$attribut_description = esc_html__( 'Enable Upsell funnels even on Cart total zero.', 'one-click-upsell-funnel-for-woocommerce-pro' );
								wps_wc_help_tip( $attribut_description );
								?>
								<label class="wps_wocuf_pro_enable_free_upsell_label">
									<input id="wps_wocuf_pro_enable_free_upsell" class="wps_wocuf_pro_enable_free_upsell_input" type="checkbox" <?php echo ( isset( $wps_upsell_global_settings['enable_free_upsell'] ) && 'on' === $wps_upsell_global_settings['enable_free_upsell'] ) ? esc_html( "checked='checked'" ) : ''; ?> name="enable_free_upsell" >	
									<span class="wps_wocuf_pro_enable_free_upsell_span"></span>
								</label>
								<?php $need_to_hide = ! empty( $wps_upsell_global_settings['enable_free_upsell'] ) && 'on' === $wps_upsell_global_settings['enable_free_upsell'] ? '' : 'keep_hidden'; ?>
								<select class="wps_wocuf_pro_free_upsell_select <?php echo esc_attr( $need_to_hide ); ?>" name="free_upsell_select">
									<option value=""><?php esc_html_e( 'No Options Selected', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>
								<?php foreach ( $enabled_redirection_gateways as $key => $gateway_id ) : ?>
										<option <?php isset( $wps_upsell_global_settings['free_upsell_select'] ) ? selected( $wps_upsell_global_settings['free_upsell_select'], $gateway_id ) : ''; ?> value="<?php echo esc_attr( $gateway_id ); ?>"><?php echo esc_html( $all_gateways[ $gateway_id ]->title ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					<?php endif; ?>
					<!-- Free Order Upselling start -->

				<!-- Skip funnel for offers already in cart start -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label><?php esc_html_e( 'Skip Funnel for Same Offer', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>

					<td>

						<?php
						$attribut_description = __( 'Skip funnel if any offer product in funnel is already present during checkout.', 'one-click-upsell-funnel-for-woocommerce-pro' );
						wps_wc_help_tip( $attribut_description );
						?>

						<?php

						// Default : Yes.
						$skip_similar_offer = ! empty( $wps_upsell_global_settings['skip_similar_offer'] ) ? $wps_upsell_global_settings['skip_similar_offer'] : 'yes';

						?>

						<select class="wps_upsell_skip_similar_offer_select" name="skip_similar_offer">
							<option value="yes" <?php selected( $skip_similar_offer, 'yes' ); ?> ><?php esc_html_e( 'Yes', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>
							<option value="no" <?php selected( $skip_similar_offer, 'no' ); ?> ><?php esc_html_e( 'No', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>
						</select>
					</td>
				</tr>
				<!-- Skip funnel for offers already in cart end -->

				<!-- Remove all styles start -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label><?php esc_html_e( 'Remove Styles from Offer Pages', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>

					<td>

						<?php
						$attribut_description = __( 'Remove theme and other plugin styles from offer pages. (Not applicable for Custom Offer pages)', 'one-click-upsell-funnel-for-woocommerce-pro' );
						wps_wc_help_tip( $attribut_description );
						?>

						<?php

						// Default : Yes.
						$remove_all_styles = ! empty( $wps_upsell_global_settings['remove_all_styles'] ) ? $wps_upsell_global_settings['remove_all_styles'] : 'yes';

						?>

						<select class="wps_upsell_remove_all_styles_select" name="remove_all_styles">

							<option value="yes" <?php selected( $remove_all_styles, 'yes' ); ?> ><?php esc_html_e( 'Yes', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>
							<option value="no" <?php selected( $remove_all_styles, 'no' ); ?> ><?php esc_html_e( 'No', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>

						</select>
					</td>
				</tr>
				<!-- Remove all styles end -->

				<!-- V3.5.0 :: Smart Skip If already purchased start -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label for="wps_wocuf_pro_smart_skip_toggle"><?php esc_html_e( 'Smart Skip If Already Purchased', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>

					<td class="forminp forminp-text">
						<?php

							$attribut_description = esc_html__( 'The upsell funnel will be skipped if any of the offer product is already been purchased in previous orders. This will only work for logged in users.', 'one-click-upsell-funnel-for-woocommerce-pro' );
							wps_wc_help_tip( $attribut_description );

							// Default : No.
							$is_smart_skip_enabled = ! empty( $wps_upsell_global_settings['wps_wocuf_pro_enable_smart_skip'] ) ? $wps_upsell_global_settings['wps_wocuf_pro_enable_smart_skip'] : 'off';
						?>

						<label class="wps_wocuf_pro_enable_smart_skip_label">
							<input id="wps_wocuf_pro_smart_skip_toggle" class="wps_wocuf_pro_enable_smart_skip_input" type="checkbox" <?php echo ( 'on' === $is_smart_skip_enabled ) ? esc_html( "checked='checked'" ) : ''; ?> name="wps_wocuf_pro_enable_smart_skip">
							<span class="wps_wocuf_pro_enable_smart_skip_span"></span>
						</label>
					</td>
				</tr>
				<!-- V3.5.0 :: Smart Skip If already purchased end -->

				<!-- V3.5.0 :: Price html format start -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label><?php esc_html_e( 'Price html format', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>

					<td>

						<?php
						$attribut_description = esc_html__( 'Select the format for price html to be shown.', 'one-click-upsell-funnel-for-woocommerce-pro' );
						wps_wc_help_tip( $attribut_description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>

						<?php

						$offer_price_html_type = ! empty( $wps_upsell_global_settings['offer_price_html_type'] ) ? $wps_upsell_global_settings['offer_price_html_type'] : 'regular';
						?>

						<select class="wps_upsell_remove_all_styles_select" name="offer_price_html_type">
							<option value="regular" <?php selected( $offer_price_html_type, 'regular' ); ?> ><?php esc_html_e( '̶R̶e̶g̶u̶l̶a̶r̶ ̶P̶r̶i̶c̶e̶ Offer Price', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>
							<option value="sale" <?php selected( $offer_price_html_type, 'sale' ); ?> ><?php esc_html_e( '̶S̶a̶l̶e̶ ̶P̶r̶i̶c̶e̶  Offer Price', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>
						</select>
					</td>
				</tr>
				<!-- V3.5.0 :: Price html format end -->

				<!-- Global product start -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label><?php esc_html_e( 'Global Offer Product', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>

					<td>

						<?php
						$attribut_description = esc_html__( '( Not for Live Offer ) Set Global Offer Product for Sandbox View of Custom Offer Page.', 'one-click-upsell-funnel-for-woocommerce-pro' );
						wps_wc_help_tip( $attribut_description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>

						<select class="wc-offer-product-search wps_upsell_offer_product" name="global_product_id" data-placeholder="<?php esc_html_e( 'Search for a product&hellip;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>">
						<?php

							$global_product_id = ! empty( $wps_upsell_global_settings['global_product_id'] ) ? $wps_upsell_global_settings['global_product_id'] : '';

						if ( ! empty( $global_product_id ) ) {

							$global_product_title = get_the_title( $global_product_id );

							?>
							<option value="<?php echo esc_html( $global_product_id ); ?>" selected="selected"><?php echo esc_html( $global_product_title . "( #$global_product_id )" ); ?>
								</option>
							<?php
						}

						?>
						</select>
						<?php $display_class = ! empty( $global_product_id ) ? 'shown' : 'hidden'; ?>
						<input type="button" class="button button-small wps-upsell-offer-product-clear <?php echo( esc_html( $display_class ) ); ?>" value="<?php esc_html_e( 'Clear', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>" aria-label="<?php esc_html_e( 'Clear Offer Product', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>">
					</td>
				</tr>
				<!-- Global product end -->

				<!-- Global Offer Discount start -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label><?php esc_html_e( 'Global Offer Discount', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>

					<td>

						<div class="wps_upsell_attribute_description">

							<?php
							$attribut_description = esc_html__( '( Not for Live Offer ) Set Global Offer Discount in product price for Sandbox View of Custom Offer Page.', 'one-click-upsell-funnel-for-woocommerce-pro' );
							wps_wc_help_tip( $attribut_description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>

							<?php

							$global_product_discount = isset( $wps_upsell_global_settings['global_product_discount'] ) ? $wps_upsell_global_settings['global_product_discount'] : '50%';

							?>

							<input type="text" name="global_product_discount" value="<?php echo esc_html( $global_product_discount ); ?>">
						</div>
						<span class="wps_upsell_global_description"><?php esc_html_e( 'Specify new offer price or discount %', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
					</td>
				</tr>
				<!-- Global Offer Discount end -->

				<!-- Upsell Actions Message start -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label><?php esc_html_e( 'Upsell Actions Message', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>

					<td>

						<div class="wps_upsell_attribute_description">

							<?php
							$attribut_description = esc_html__( '( For Live Offer only ) This message will be shown along with a loader on clicking upsell Accept / Reject message.', 'one-click-upsell-funnel-for-woocommerce-pro' );
							wps_wc_help_tip( $attribut_description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>

							<?php

							$upsell_actions_message = isset( $wps_upsell_global_settings['upsell_actions_message'] ) ? $wps_upsell_global_settings['upsell_actions_message'] : '';

							?>

							<textarea name="upsell_actions_message" rows="4" cols="50"><?php echo esc_html( wp_unslash( $upsell_actions_message ) ); ?></textarea>
						</div>
						<span class="wps_upsell_global_description"><?php esc_html_e( 'Add a custom message on after upsell accept or reject button.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
					</td>
				</tr>
				<!-- Upsell Actions Message end -->

				<!-- Global Custom CSS start -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label><?php esc_html_e( 'Global Custom CSS', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>

					<td>

						<div class="wps_upsell_attribute_description">

							<?php
							$attribut_description = __( 'Enter your Custom CSS without style tags.', 'one-click-upsell-funnel-for-woocommerce-pro' );
							wps_wc_help_tip( $attribut_description );
							?>

							<?php

							$global_custom_css = ! empty( $wps_upsell_global_settings['global_custom_css'] ) ? $wps_upsell_global_settings['global_custom_css'] : '';

							?>

							<textarea name="global_custom_css" rows="4" cols="50"><?php echo esc_html( wp_unslash( $global_custom_css ) ); ?></textarea>
						</div>
					</td>
				</tr>
				<!-- Global Custom CSS end -->

				<!-- Global Custom JS start -->
				<tr valign="top">

					<th scope="row" class="titledesc">
						<label><?php esc_html_e( 'Global Custom JS', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>

					<td>

						<div class="wps_upsell_attribute_description">

							<?php
							$attribut_description = __( 'Enter your Custom JS without script tags.', 'one-click-upsell-funnel-for-woocommerce-pro' );
							wps_wc_help_tip( $attribut_description );
							?>

							<?php

							$global_custom_js = ! empty( $wps_upsell_global_settings['global_custom_js'] ) ? $wps_upsell_global_settings['global_custom_js'] : '';

							?>

							<textarea name="global_custom_js" rows="4" cols="50"><?php echo esc_html( wp_unslash( $global_custom_js ) ); ?></textarea>
						</div>
					</td>
				</tr>
				<!-- Global Custom JS end -->

				<?php do_action( 'wps_wocuf_pro_create_more_settings' ); ?>
			</tbody>
		</table>
	</div>

	<p class="submit">
	<input type="submit" value="<?php esc_html_e( 'Save Changes', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>" class="button-primary woocommerce-save-button" name="wps_wocuf_pro_common_settings_save" id="wps_wocuf_pro_creation_setting_save" >
	</p>
</form>
