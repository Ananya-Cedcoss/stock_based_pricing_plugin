<?php
/**
 * The file defines the global plugin functions.
 *
 * All Global functions that are used through out the plugin.
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      3.0.0
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/includes
 */

/**
 * Check if Elementor plugin is active or not.
 *
 * @since    3.0.0
 */
function wps_upsell_elementor_plugin_active() {

	if ( is_plugin_active( 'elementor/elementor.php' ) ) {

		return true;
	} else {

		return false;
	}
}

/**
 * Validate upsell nonce.
 *
 * @since    3.0.0
 */
function wps_upsell_validate_upsell_nonce() {

	$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
	$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

	if ( ! $id_nonce_verified ) {
		wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
	}

	if ( ! empty( $_GET['ocuf_ns'] ) ) {

		return true;

	} else {

		return false;
	}
}

/**
 * Get product discount.
 *
 * @since    3.0.0
 */
function wps_upsell_get_product_discount() {

	$wps_wocuf_pro_offered_discount = '';

	$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
	$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

	if ( ! $id_nonce_verified ) {
		wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
	}

	$funnel_id = isset( $_GET['ocuf_fid'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) ) : 'not_set';
	$offer_id  = isset( $_GET['ocuf_ofd'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) ) : 'not_set';

	// If Live offer.
	if ( 'not_set' !== $funnel_id && 'not_set' !== $offer_id ) {

		$wps_wocuf_pro_all_funnels = get_option( 'wps_wocuf_pro_funnels_list' );

		$wps_wocuf_pro_offered_discount = $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'][ $offer_id ];

		$wps_wocuf_pro_offered_discount = ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'][ $offer_id ] : '';
	} elseif ( current_user_can( 'manage_options' ) ) { // When not live and only for admin view.

		// Get funnel and offer id from current offer page post id.
		global $post;
		$offer_page_id = $post->ID;

		$funnel_data = get_post_meta( $offer_page_id, 'wps_upsell_funnel_data', true );

		$product_found_in_funnel = false;

		if ( ! empty( $funnel_data ) && is_array( $funnel_data ) && count( $funnel_data ) ) {

			$funnel_id = $funnel_data['funnel_id'];
			$offer_id  = $funnel_data['offer_id'];

			if ( isset( $funnel_id ) && isset( $offer_id ) ) {

				$wps_wocuf_pro_all_funnels = get_option( 'wps_wocuf_pro_funnels_list' );

				// When New offer is added ( Not saved ) so only at that time it will return 50%.
				$wps_wocuf_pro_offered_discount = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'][ $offer_id ] : '50%';

				$wps_wocuf_pro_offered_discount = ! empty( $wps_wocuf_pro_offered_discount ) ? $wps_wocuf_pro_offered_discount : '';
			}
		} else { // For Custom Page for Offer.

			// Get global product discount.
			$wps_upsell_global_settings = get_option( 'wps_upsell_global_options', array() );

			$global_product_discount = isset( $wps_upsell_global_settings['global_product_discount'] ) ? $wps_upsell_global_settings['global_product_discount'] : '50%';

			$wps_wocuf_pro_offered_discount = $global_product_discount;
		}
	}

	return $wps_wocuf_pro_offered_discount;
}

if ( ! function_exists( 'wps_upsell_get_pid_from_url_params' ) ) {

	/**
	 * Upsell product id from url funnel and offer params.
	 *
	 * @since    3.0.0
	 */
	function wps_upsell_get_pid_from_url_params() {

		$params['status'] = 'false';

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		if ( isset( $_GET['ocuf_ofd'] ) && isset( $_GET['ocuf_fid'] ) ) {

			$params['status'] = 'true';

			$params['offer_id']  = sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) );
			$params['funnel_id'] = sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) );
			$params['order_id']  = ! empty( $_GET['ocuf_ok'] ) ? wc_get_order_id_by_order_key( sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) ) ) : '';
		}

		return $params;
	}
}

/**
 * Upsell Live Offer URL parameters.
 *
 * @since    3.0.0
 */
function wps_upsell_live_offer_url_params() {

	$params['status'] = 'false';

	$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
	$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

	if ( ! $id_nonce_verified ) {
		wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
	}

	if ( isset( $_POST['ocuf_ns'] ) && isset( $_POST['ocuf_ok'] ) && isset( $_POST['ocuf_ofd'] ) && isset( $_POST['ocuf_fid'] ) && isset( $_POST['product_id'] ) ) {

		$params['status'] = 'true';

		$params['upsell_nonce'] = sanitize_text_field( wp_unslash( $_POST['ocuf_ns'] ) );
		$params['order_key']    = sanitize_text_field( wp_unslash( $_POST['ocuf_ok'] ) );
		$params['offer_id']     = sanitize_text_field( wp_unslash( $_POST['ocuf_ofd'] ) );
		$params['funnel_id']    = sanitize_text_field( wp_unslash( $_POST['ocuf_fid'] ) );
		$params['product_id']   = sanitize_text_field( wp_unslash( $_POST['product_id'] ) );
		$params['quantity']     = ! empty( $_POST['wps_wocuf_pro_quantity'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wocuf_pro_quantity'] ) ) : '1';

	} elseif ( isset( $_GET['ocuf_ns'] ) && isset( $_GET['ocuf_ok'] ) && isset( $_GET['ocuf_ofd'] ) && isset( $_GET['ocuf_fid'] ) && isset( $_GET['product_id'] ) ) {

		$params['status'] = 'true';

		$params['upsell_nonce'] = sanitize_text_field( wp_unslash( $_GET['ocuf_ns'] ) );
		$params['order_key']    = sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) );
		$params['offer_id']     = sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) );
		$params['funnel_id']    = sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) );
		$params['product_id']   = sanitize_text_field( wp_unslash( $_GET['product_id'] ) );
		$params['quantity']     = ! empty( $_GET['quantity'] ) ? sanitize_text_field( wp_unslash( $_GET['quantity'] ) ) : '1';
	}

	return $params;
}

/**
 * Handling Funnel offer-page posts deletion which are dynamically assigned.
 *
 * @since    3.0.0
 */
function wps_upsell_offer_page_posts_deletion() {

	// Get all funnels.
	$all_created_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );
	// Get all saved offer post ids.
	$saved_offer_post_ids = get_option( 'wps_upsell_offer_post_ids', array() );

	if ( ! empty( $all_created_funnels ) && is_array( $all_created_funnels ) && count(
		$all_created_funnels
	) && ! empty( $saved_offer_post_ids ) && is_array( $saved_offer_post_ids ) && count(
		$saved_offer_post_ids
	) ) {

		$funnel_offer_post_ids = array();

		// Retrieve all valid( present in funnel ) offer assigned page post ids.
		foreach ( $all_created_funnels as $funnel_id => $single_funnel ) {

			if ( ! empty( $single_funnel['wps_upsell_post_id_assigned'] ) && is_array( $single_funnel['wps_upsell_post_id_assigned'] ) && count( $single_funnel['wps_upsell_post_id_assigned'] ) ) {

				foreach ( $single_funnel['wps_upsell_post_id_assigned'] as $offer_post_id ) {

					if ( ! empty( $offer_post_id ) ) {

						$funnel_offer_post_ids[] = $offer_post_id;
					}
				}
			}
		}

		// Update saved offer post ids array.
		$saved_offer_post_ids = array_values( $saved_offer_post_ids );
		update_option( 'wps_upsell_offer_post_ids', $saved_offer_post_ids );

	}
}

/**
 * Sync Upsell Funnels from ORG plugin.
 *
 * @since    3.0.0
 */
function wps_upsell_sync_funnels() {

	$org_upsell_funnels = get_option( 'wps_wocuf_funnels_list' );
	$pro_upsell_funnels = get_option( 'wps_wocuf_pro_funnels_list' );

	$sync_funnels = false;

	if ( ! empty( $org_upsell_funnels ) && is_array( $org_upsell_funnels ) && empty( $pro_upsell_funnels ) ) {

		// Replace keys.
		$org_upsell_funnels = array_map(
			function( $single_funnel ) {

					$single_funnel['wps_wocuf_pro_funnel_id']              = $single_funnel['wps_wocuf_funnel_id'];
					$single_funnel['wps_wocuf_pro_funnel_name']            = $single_funnel['wps_wocuf_funnel_name'];
					$single_funnel['wps_wocuf_pro_target_pro_ids']         = ! empty( $single_funnel['wps_wocuf_target_pro_ids'] ) ? $single_funnel['wps_wocuf_target_pro_ids'] : '';
					$single_funnel['wps_wocuf_pro_products_in_offer']      = ! empty( $single_funnel['wps_wocuf_products_in_offer'] ) ? $single_funnel['wps_wocuf_products_in_offer'] : '';
					$single_funnel['wps_wocuf_pro_offer_discount_price']   = ! empty( $single_funnel['wps_wocuf_offer_discount_price'] ) ? $single_funnel['wps_wocuf_offer_discount_price'] : '';
					$single_funnel['wps_wocuf_pro_attached_offers_on_buy'] = ! empty( $single_funnel['wps_wocuf_attached_offers_on_buy'] ) ? $single_funnel['wps_wocuf_attached_offers_on_buy'] : '';
					$single_funnel['wps_wocuf_pro_attached_offers_on_no']  = ! empty( $single_funnel['wps_wocuf_attached_offers_on_no'] ) ? $single_funnel['wps_wocuf_attached_offers_on_no'] : '';

					$single_funnel['wps_wocuf_pro_offer_custom_page_url'] = ! empty( $single_funnel['wps_wocuf_offer_custom_page_url'] ) ? $single_funnel['wps_wocuf_offer_custom_page_url'] : '';
					$single_funnel['wps_wocuf_pro_applied_offer_number']  = ! empty( $single_funnel['wps_wocuf_applied_offer_number'] ) ? $single_funnel['wps_wocuf_applied_offer_number'] : '';

					// New Features key names are same in ORG and PRO so no need to handle.

					unset( $single_funnel['wps_wocuf_funnel_id'] );
					unset( $single_funnel['wps_wocuf_funnel_name'] );
					unset( $single_funnel['wps_wocuf_target_pro_ids'] );
					unset( $single_funnel['wps_wocuf_products_in_offer'] );
					unset( $single_funnel['wps_wocuf_offer_discount_price'] );
					unset( $single_funnel['wps_wocuf_attached_offers_on_buy'] );
					unset( $single_funnel['wps_wocuf_attached_offers_on_no'] );
					unset( $single_funnel['wps_wocuf_offer_custom_page_url'] );
					unset( $single_funnel['wps_wocuf_applied_offer_number'] );

					return $single_funnel;
			},
			$org_upsell_funnels
		);

		update_option( 'wps_wocuf_pro_funnels_list', $org_upsell_funnels );

		$sync_funnels = true;
	}

	return $sync_funnels;
}

/**
 * Apply Discount or Change price of Product.
 *
 * @since    1.0.0
 * @param    object $temp_product    object of product.
 * @param    string $price           offer price or disocunt %.
 * @return   object   $temp_product    object of product with new price
 */
function wps_upsell_change_product_price( $temp_product, $price ) {

	if ( ! empty( $price ) && ! empty( $temp_product ) ) {

		$payable_price = $temp_product->get_price();
		$sale_price    = $temp_product->get_sale_price();
		$regular_price = $temp_product->get_regular_price();
		$is_fixed      = false;

		// Change amount in case of chargeable currency is different.
		if ( ! empty( WC()->session ) && WC()->session->__isset( 's_selected_currency' ) && function_exists( 'wps_wmcs_fixed_price_for_simple_sales_price' ) ) {
			$_regular_price = wps_wmcs_fixed_price_for_simple_regular_price( $temp_product->get_id() );
			$_sale_price    = wps_wmcs_fixed_price_for_simple_sales_price( $temp_product->get_id() );

			$sale_price    = ! empty( $_sale_price ) ? $_sale_price : $sale_price;
			$regular_price = ! empty( $_regular_price ) ? $_regular_price : $regular_price;
			$payable_price = ! empty( $sale_price ) ? $sale_price : $regular_price;
			if ( ! empty( $_regular_price ) || ! empty( $_sale_price ) ) {
				$is_fixed = true;
			}
		}

		// Discount is in %.
		if ( false !== strpos( $price, '%' ) ) {

			$discounted_percent = trim( $price, '%' );
			$discounted_price   = floatval( $payable_price ) * ( floatval( $discounted_percent ) / 100 );

			// Original price must be greater than zero.
			if ( $payable_price > 0 ) {

				$offer_price = $payable_price - $discounted_price;

			} else {

				$offer_price = $payable_price;
			}
		} else { // Discount is fixed.

			$offer_price = floatval( $price );
		}

		/**
		 * Original price : $payable_price.
		 * Sale price : $sale_price.
		 * Regular price : $regular_price.
		 * Offer price : $offer_price.
		 */
		$wps_upsell_global_settings = get_option( 'wps_upsell_global_options', array() );

		$price_html_format = ! empty( $wps_upsell_global_settings['offer_price_html_type'] ) ? $wps_upsell_global_settings['offer_price_html_type'] : 'regular';

		// ̶S̶a̶l̶e̶ ̶P̶r̶i̶c̶e̶  Offer Price.
		if ( 'sale' === $price_html_format ) {

			if ( ! empty( $sale_price ) ) {

				$temp_product->set_regular_price( $sale_price );
				$temp_product->set_sale_price( $offer_price );
			} else {

				// No sale price is present.
				$temp_product->set_sale_price( $offer_price );
			}
		} else { // ̶R̶e̶g̶u̶l̶a̶r̶ ̶P̶r̶i̶c̶e̶ Offer Price.

			// In this case set the regular price as sale.
			$temp_product->set_sale_price( $offer_price );
		}

		// Change amount in case of chargeable currency is different.
		if ( false === $is_fixed && ! empty( WC()->session ) && WC()->session->__isset( 's_selected_currency' ) && class_exists( 'Mwb_Multi_Currency_Switcher_For_Woocommerce_Public' ) ) {
			$currency_switcher_obj = new Mwb_Multi_Currency_Switcher_For_Woocommerce_Public( 'WPS Multi Currency Switcher For WooCommerce', '1.2.0' );
			$offer_price           = $currency_switcher_obj->wps_mmcsfw_get_price_of_product( $offer_price, $temp_product->get_id() );
		}

		$temp_product->set_price( $offer_price );
	} else {
		/**
		 * If the discount is 0 and fixed.
		 */
		$temp_product->set_price( 0 );
	}

	return $temp_product;
}

/**
 * Upsell supported payment gateways.
 *
 * @since    3.0.0
 */
function wps_upsell_supported_gateways() {

	$supported_gateways = array(
		'bacs', // Direct bank transfer.
		'cheque', // Check payments.
		'cod', // Cash on delivery.
		'wps-wocuf-pro-stripe-gateway', // Upsell Stripe.
		'cardcom', // Official Cardcom.
		'paypal',    // Woocommerce Paypal ( Standard ).
		'wps-wocuf-pro-paypal-gateway', // Upsell Paypal ( Express Checkout ).
		'ppec_paypal', // https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/.
		'authorize', // https://wordpress.org/plugins/authorizenet-payment-gateway-for-woocommerce/.
		'paystack', // https://wordpress.org/plugins/woo-paystack/.
		'vipps', // https://wordpress.org/plugins/woo-vipps/.
		'transferuj', // TPAY.com https://wordpress.org/plugins/woocommerce-transferujpl-payment-gateway/.
		'razorpay', // https://wordpress.org/plugins/woo-razorpay/.
		'stripe_ideal', // Official Stripe - iDeal.
		'authorize_net_cim_credit_card', // Official Authorize.Net-CC.
		'square_credit_card', // Official Square-XL plugins.
		'braintree_cc', // Official Braintree for Woocommerce plugins.
		'paypal_express', // Angeleye Paypal Express Checkout.
		'stripe', // Official Stripe - CC.
		'', // For Free Product.
		'ppcp-gateway', // For Paypal payments plugin.
		'ppcp-credit-card-gateway', // For Paypal CC payments plugin.
	);

	return apply_filters( 'wps_wocuf_pro_supported_gateways', $supported_gateways );
}

/**
 * Upsell supported payment gateways which redirects to
 * their respective page for payment.
 *
 * ( Payment doesn't take place on site ).
 *
 * @since    3.0.0
 */
function wps_upsell_supported_gateways_with_redirection() {

	$supported_gateways_with_redirection = array(
		'wps-wocuf-pro-stripe-gateway', // Upsell Stripe ( not redirection ) Added coz we don't need cron for this.
		'wps-wocuf-pro-paypal-gateway', // Upsell Paypal ( Express Checkout ).
		'cardcom', // Official Cardcom.
		'paypal',  // Woocommerce Paypal ( Standard ).
		'ppec_paypal', // Upsell Paypal ( Express Checkout ).
		'authorize', // https://wordpress.org/plugins/authorizenet-payment-gateway-for-woocommerce/.
		'paystack', // https://wordpress.org/plugins/woo-paystack/.
		'vipps', // https://wordpress.org/plugins/woo-vipps/.
		'transferuj', // TPAY.com https://wordpress.org/plugins/woocommerce-transferujpl-payment-gateway/.
		'razorpay', // https://wordpress.org/plugins/woo-razorpay/.
		'stripe_ideal', // Official Stripe - iDeal.
		'stripe', // Official Stripe - CC.
		'', // For Free Product.
	);

	return apply_filters( 'wps_wocuf_pro_supported_gateways_with_redirection', $supported_gateways_with_redirection );
}

/**
 * Get json files
 *
 * @param string $url Url.
 *
 * @since    3.0.0
 */
function wps_upsell_get_template( $url = '' ) {

	if ( ! empty( $url ) ) {
		global $wp_filesystem;
		WP_Filesystem();
		$elementor_data = $wp_filesystem->get_contents( $url );
		return $elementor_data;
	}
}

/**
 * Elementor Upsell offer template 1.
 *
 * Standard Template ( Default ).
 *
 * @since    3.0.0
 */
function wps_upsell_elementor_offer_template_1() {
	$elementor_data = wps_upsell_get_template( WPS_WOCUF_PRO_DIRPATH . 'json/offer-template-1.json' );
	return $elementor_data;
}

/**
 * Elementor Upsell offer template 2.
 *
 * Creative Template.
 *
 * @since    3.0.0
 */
function wps_upsell_elementor_offer_template_2() {

	$elementor_data = wps_upsell_get_template( WPS_WOCUF_PRO_DIRPATH . 'json/offer-template-2.json' );

	return $elementor_data;
}

/**
 * Elementor Upsell offer template 3.
 *
 * Video Template.
 *
 * @since    3.0.0
 */
function wps_upsell_elementor_offer_template_3() {

	$elementor_data = wps_upsell_get_template( WPS_WOCUF_PRO_DIRPATH . 'json/offer-template-3.json' );

	return $elementor_data;
}

/**
 * Gutenberg Offer Page content.
 *
 * @since    3.0.0
 */
function wps_upsell_gutenberg_offer_content() {

	$post_content = '<!-- wp:spacer {"height":50} -->
		<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
		<!-- /wp:spacer -->

		<!-- wp:heading {"align":"center"} -->
		<h2 style="text-align:center">Exclusive Special One time Offer for you</h2>
		<!-- /wp:heading -->

		<!-- wp:spacer {"height":50} -->
		<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
		<!-- /wp:spacer -->

		<!-- wp:html -->
		<div class="wps_upsell_default_offer_image">[wps_upsell_image]</div>
		<!-- /wp:html -->

		<!-- wp:spacer {"height":20} -->
		<div style="height:20px" aria-hidden="true" class="wp-block-spacer"></div>
		<!-- /wp:spacer -->

		<!-- wp:heading {"align":"center"} -->
		<h2 style="text-align:center">[wps_upsell_title]</h2>
		<!-- /wp:heading -->

		<!-- wp:html -->
		<div class="wps_upsell_default_offer_description">[wps_upsell_desc]</div>
		<!-- /wp:html -->

		<!-- wp:heading {"level":3,"align":"center"} -->
		<h3 style="text-align:center">Special Offer Price : [wps_upsell_price]</h3>
		<!-- /wp:heading -->

		<!-- wp:spacer {"height":15} -->
		<div style="height:15px" aria-hidden="true" class="wp-block-spacer"></div>
		<!-- /wp:spacer -->

		<!-- wp:html -->
		<div class="wps_upsell_default_offer_variations">[wps_upsell_variations]</div>
		<!-- /wp:html -->

		<!-- wp:button {"customBackgroundColor":"#78c900","align":"center","className":"wps_upsell_default_offer_buy_now"} -->
		<div class="wp-block-button aligncenter wps_upsell_default_offer_buy_now"><a class="wp-block-button__link has-background" href="[wps_upsell_yes]" style="background-color:#78c900">Add this to my Order</a></div>
		<!-- /wp:button -->

		<!-- wp:spacer {"height":25} -->
		<div style="height:25px" aria-hidden="true" class="wp-block-spacer"></div>
		<!-- /wp:spacer -->

		<!-- wp:button {"customBackgroundColor":"#e50000","align":"center","className":"wps_upsell_default_offer_no_thanks"} -->
		<div class="wp-block-button aligncenter wps_upsell_default_offer_no_thanks"><a class="wp-block-button__link has-background" href="[wps_upsell_no]" style="background-color:#e50000">No thanks</a></div>
		<!-- /wp:button -->

		<!-- wp:html -->
		[wps_upsell_default_offer_identification]
		<!-- /wp:html -->

		<!-- wp:spacer {"height":50} -->
		<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
		<!-- /wp:spacer -->';

		return $post_content;
}

/**
 * Order processed status array
 *
 * Verify Intent status.
 *
 * @since    3.2.0
 */
function wps_non_intent_order_status() {

	return array(
		'processing',
		'cancelled',
	);
}

/**
 * Some payment methods process the order before upsell.
 * Smart offer upgrade works diffrently for this.
 *
 * @since    3.5.0
 */
function wps_supported_gateways_with_upsell_parent_order() {

	$supported_gateways = array(
		'wps-wocuf-pro-stripe-gateway', // Upsell Stripe.
		'stripe', // official stripe.
	);

	return apply_filters( 'wps_wocuf_pro_supported_gateways_with_upsell_parent_order', $supported_gateways );
}

/**
 * Some payment method integrations process the order before upsell.
 * We have to save payment details as meta.
 * Does not saves raw credit card or account details.
 *
 * @since    3.5.0
 */
function wps_upsell_supported_gateway_integrations() {

	$supported_gateways = array(
		'authorize_net_cim_credit_card', // Official Authorize.Net-CC.
		'square_credit_card', // Official Square-XL plugins.
		'braintree_cc', // Official Braintree for Woocommerce plugins.
		'paypal_express', // Angeleye Paypal Express Checkout.
		'ppcp-gateway', // For Paypal payments plugin.
		'ppcp-credit-card-gateway', // For Paypal CC payments plugin.
	);

	return apply_filters( 'wps_wocuf_pro_supported_gateways_integrations', $supported_gateways );
}

/**
 * Upsell supported payment gateways for which Parent Order is secured.
 * Either with Initial payment or via Cron.
 *
 * @since    3.5.0
 */
function wps_upsell_payment_gateways_with_parent_secured() {

	$gateways_with_parent_secured = array(
		'bacs', // Direct bank transfer.
		'cheque', // Check payments.
		'cod', // Cash on delivery.
		'wps-wocuf-pro-stripe-gateway', // Upsell Stripe.
		'authorize_net_cim_credit_card', // Official Authorize.Net-CC.
		'square_credit_card', // Official Square-XL plugins.
		'braintree_cc', // Official Braintree for Woocommerce plugins.
		'paypal_express', // Angeleye Paypal Express Checkout.
		'', // For Free Product.
		'ppcp-gateway', // For Paypal payments plugin.
		'ppcp-credit-card-gateway', // For Paypal CC payments plugin.
	);

	return apply_filters( 'wps_upsell_pg_with_parent_secured', $gateways_with_parent_secured );
}


/**
 * Subscriptions need customer id for creating the subscription object.
 * Sometimes we don't get the customer id for guest checkout.
 * So create and login the user.
 *
 * @param string $order_id order id.
 *
 * @since    3.5.0
 */
function wps_upsell_create_and_auth_customer( $order_id = '' ) {

	if ( empty( $order_id ) ) {
		return false;
	}

	$order = wc_get_order( $order_id );

	/**===================================================
	 * Is user logged in?
	 * If not then create a user and log in
	 * If login fails then we must process normal payment.
	=====================================================*/
	$current_user = wp_get_current_user();

	if ( ! empty( $current_user->ID ) ) {

		$user_id = $current_user->ID;

	} else {

		// Create A new one.
		$email      = $order->get_billing_email();
		$uname      = $order->get_billing_first_name() . uniqid();
		$create_new = true;

		if ( email_exists( $email ) !== false ) {

			$user       = get_user_by( 'email', $email );
			$user_id    = $user->ID;
			$create_new = false;
		} elseif ( username_exists( $uname ) !== false ) {

			$uname = $order->get_billing_last_name() . uniqid();
		}

		// Check still need to create new.
		if ( ! empty( $create_new ) ) {

			$user_id = wp_create_user( $uname, uniqid(), $email );
			if ( ! empty( $user_id->errors ) ) {

				foreach ( $user_id->errors  as $error_code => $error_msg ) {

					$order->add_order_note( 'User Creation failed :: Code- ' . $error_code );
				}
			}
		}

		if ( empty( $user_id ) ) {

			$order->add_order_note( 'User Creation failed. Empty user id. Processing initial order payment' );
		}

		$u = new WP_User( $user_id );
		$u->add_role( 'customer' );

		update_user_meta( $user_id, 'first_name', wp_unslash( $order->get_billing_first_name() ) );
		update_user_meta( $user_id, 'last_name', wp_unslash( $order->get_billing_last_name() ) );

		if ( ! empty( $user_id ) ) {

			wp_set_auth_cookie( $user_id );
			wp_set_current_user( $user_id );
		}
	}

	// Add in order and add role.
	$current_user = new WP_User( $user_id );
	$roles        = (array) $current_user->roles;

	if ( ! in_array( 'customer', $roles, true ) ) {

		array_push( $roles, 'customer' );

		if ( ! empty( $roles ) ) {

			foreach ( $roles as $key => $value ) {

				$current_user->add_role( $value );
			}
		}
	}

	update_post_meta( $order_id, '_customer_user', $user_id );
	return $user_id;
}

if ( ! function_exists( 'wps_wc_help_tip' ) ) {

	/**
	 * Get tooltip.
	 *
	 * @param mixed $tip message.
	 * @since    3.0.4
	 */
	function wps_wc_help_tip( $tip = '' ) {
		?>
		<span class="woocommerce-help-tip" data-tip="<?php echo esc_html( $tip ); ?>"></span>
		<?php
	}
}
