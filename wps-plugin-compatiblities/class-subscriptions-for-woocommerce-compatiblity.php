<?php
/**
 * The file defines the Woocommerce subscriptions compatibility and handling functions.
 *
 * All functions that are used for adding compatibility with Woocommerce Subscriptions
 * and handling them.
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      3.6.1
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/wps-plugin-compatiblities
 */

if ( ! class_exists( 'Subscriptions_For_Woocommerce_Public' ) ) {
	return;
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 * namespace subscriptions_for_woocommerce_public.
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/wps-plugin-compatiblities
 * @author     wpswings <webmaster@wpswings.com>
 */
class Subscriptions_For_Woocommerce_Compatiblity extends Subscriptions_For_Woocommerce_Public {

	/**
	 * Check if Subscription plugin is active.
	 *
	 * @since    3.6.1
	 */
	public static function parent_plugin_active() {

		if ( class_exists( 'Subscriptions_For_Woocommerce_Public' ) ) {

			return true;

		} else {

			return false;
		}
	}

	/**
	 * Check Order Item is Subscription product.
	 *
	 * @param string $order_id order id.
	 *
	 * @since    3.6.1
	 */
	public static function order_contains_subscription( $order_id ) {

		if ( empty( $order_id ) || ! self::parent_plugin_active() ) {

			return false;
		}

		$contains_subscription = false;

		$order = wc_get_order( $order_id );

		$order_items = $order->get_items();

		if ( ! empty( $order_items ) && is_array( $order_items ) ) {

			foreach ( $order_items as $single_item ) {

				$product_id = $single_item->get_product_id();

				$product = wc_get_product( $product_id );

				if ( self::is_subscription_product( $product ) ) {

					$contains_subscription = true;
					break;
				}
			}
		}

		return $contains_subscription;
	}


	/**
	 * Check if product is Subscription Product.
	 *
	 * @param string $product Product id.
	 * @since    3.6.1
	 */
	public static function is_subscription_product( $product ) {

		if ( empty( $product ) || ! self::parent_plugin_active() ) {

			return false;
		}

		if ( function_exists( 'wps_sfw_check_product_is_subscription' ) && wps_sfw_check_product_is_subscription( $product ) ) {

			return true;

		} else {

			return false;
		}
	}

	/**
	 * Subcription supported gateways.
	 *
	 * @since    3.6.1
	 */
	public static function supported_gateways() {

		$subs_supported_gateways = array(
			'stripe', // Official Stripe-CC.
			'ppec_paypal', // Paypal Express.
			'cod', // Cash on delivery.
			'bacs', // Direct bank transfer.
			'cheque', // Check payments.
			'paypal',   // Paypal Standard.
		);

		return apply_filters( 'wps_compatible_subs_supported_gateways', $subs_supported_gateways );
	}


	/**
	 * Check that the payment gateway supports subscriptions.
	 *
	 * @param string $order_id order_id.
	 * @since    3.6.1
	 */
	public static function pg_supports_subs( $order_id ) {

		if ( empty( $order_id ) ) {

			return false;
		}

		$order = wc_get_order( $order_id );

		$payment_gateway = $order->get_payment_method();

		if ( in_array( $payment_gateway, self::supported_gateways(), true ) ) {

			return true;
		} else {

			return false;
		}
	}


	/**
	 * Error in creating Subscription.
	 *
	 * @since    3.6.1
	 */
	public function trigger_create_error() {

		$shop_page_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : get_permalink( woocommerce_get_page_id( 'shop' ) );

		?>
		<div style="text-align: center;margin-top: 30px;" id="offer_expired"><h2 style="font-weight: 200;"><?php esc_html_e( 'Sorry, Could not create Subscription', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h2><a class="button wc-backward" href="<?php echo esc_url( $shop_page_url ); ?>"><?php esc_html_e( 'Return to Shop ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>&rarr;</a></div>
		<?php
		wp_die();
	}

	/**
	 * Create Subscriptions for Order.
	 *
	 * @param string $order_id order_id.
	 * @param array  $posted_data POST.
	 * @since    3.6.1
	 */
	public function create_subscriptions_for_order( $order_id, $posted_data = array() ) {

		if ( empty( $order_id ) && empty( $order ) ) {

			return;
		}

		if ( empty( $order ) ) {

			$order = wc_get_order( $order_id );
		}

		return $this->wps_sfw_process_checkout( $order_id, $posted_data );
	}



	/**
	 * Check Offer Products are Subscription.
	 *
	 * @param array $order_items Offer products saved in funnel.
	 *
	 * @since    3.5.0
	 */
	public static function offer_contains_subscription( $order_items = array() ) {

		$contains_subscription = false;

		if ( ! empty( $order_items ) && is_array( $order_items ) ) {

			foreach ( $order_items as $single_item_id ) {

				$product = wc_get_product( $single_item_id );

				if ( self::is_subscription_product( $product ) ) {

					$contains_subscription = true;
					break;
				}
			}
		}

		return $contains_subscription;
	}

	/**
	 * Find if triggered funnel contains subscription in offer/target products.
	 *
	 * @param string $order_id Order id.
	 * @param array  $offer_products Order items.
	 *
	 * @since    3.5.0
	 */
	public static function funnel_contains_any_subscription( $order_id = false, $offer_products = false ) {

		if ( empty( $order_id ) || ! self::parent_plugin_active() ) {

			return false;
		}

		/**
		 * Do only id target or offer products are subscriptions.
		 */
		$result = false;

		if ( self::order_contains_subscription( $order_id ) && self::pg_supports_subs( $order_id ) ) {

			$result = true;
		}

		if ( false === $result && self::offer_contains_subscription( $offer_products ) && self::pg_supports_subs( $order_id ) ) {

			$result = true;
		}

		return $result;
	}

	/**
	 * Create Subscription for Current Upsell offer product
	 * that is added to Order.
	 *
	 * @param string $order_id Order id.
	 * @param string $item_id  Line Item id.
	 *
	 * @since    3.6.1
	 *
	 * @throws Exception Return error.
	 */
	public function create_upsell_subscription( $order_id, $item_id ) {

		if ( empty( $order_id ) || empty( $item_id ) ) {
			return;
		}

		$cart_item = new WC_Order_Item_Product( $item_id );

		if ( wps_sfw_check_product_is_subscription( $cart_item->get_product() ) ) {

			$order       = wc_get_order( $order_id );
			$posted_data = array(
				'payment_method' => $order->get_payment_method(),
			);

			if ( $cart_item->get_product()->is_on_sale() ) {
				$price = $cart_item->get_product()->get_sale_price();
			} else {
				$price = $cart_item->get_product()->get_regular_price();
			}
			$wps_recurring_total = $price * $cart_item['quantity'];

			$product_id = $cart_item->get_product()->get_id();

			$wps_recurring_data                        = $this->wps_sfw_get_subscription_recurring_data( $product_id );
			$wps_recurring_data['wps_recurring_total'] = $wps_recurring_total;
			$wps_recurring_data['product_id']          = $product_id;
			$wps_recurring_data['product_name']        = $cart_item->get_product()->get_name();
			$wps_recurring_data['product_qty']         = $cart_item['quantity'];

			$wps_recurring_data['line_tax_data']     = $cart_item['line_tax_data'];
			$wps_recurring_data['line_subtotal']     = $cart_item['line_subtotal'];
			$wps_recurring_data['line_subtotal_tax'] = $cart_item['line_subtotal_tax'];
			$wps_recurring_data['line_total']        = $cart_item['line_total'];
			$wps_recurring_data['line_tax']          = $cart_item['line_tax'];

			$wps_recurring_data = apply_filters( 'wps_sfw_cart_data_for_susbcription', $wps_recurring_data, $cart_item );

			if ( apply_filters( 'wps_sfw_is_upgrade_downgrade_order', false, $wps_recurring_data, $order, $posted_data, $cart_item ) ) {
				return;
			}

			$subscription = $this->wps_sfw_create_subscription( $order, $posted_data, $wps_recurring_data );
			if ( is_wp_error( $subscription ) ) {
				throw new Exception( $subscription->get_error_message() );
			} else {
				$wps_has_susbcription = get_post_meta( $order_id, 'wps_sfw_order_has_subscription', true );
				if ( 'yes' !== $wps_has_susbcription ) {
					update_post_meta( $order_id, 'wps_sfw_order_has_subscription', 'yes' );
				}
			}
		}

		$wps_has_susbcription = get_post_meta( $order_id, 'wps_sfw_order_has_subscription', true );

		return $wps_has_susbcription ? 'yes' : 'no';
	}

	/**
	 * Activate Subscription for Current Upsell offer product.
	 *
	 * @param string $order_id Order id.
	 *
	 * @since    3.6.1
	 */
	public function activate_subs_after_upsell( $order_id ) {

		$wps_has_susbcription = get_post_meta( $order_id, 'wps_sfw_order_has_subscription', true );

		if ( 'yes' === $wps_has_susbcription ) {

			$args = array(
				'numberposts' => -1,
				'post_type'   => 'wps_subscriptions',
				'post_status' => 'wc-wps_renewal',
				'meta_query'  => array(     //phpcs:ignore
					'relation' => 'AND',
					array(
						'key'   => 'wps_parent_order',
						'value' => $order_id,
					),
					array(
						'key'   => 'wps_subscription_status',
						'value' => 'pending',
					),
				),
			);

			$wps_subscriptions = get_posts( $args );

			if ( isset( $wps_subscriptions ) && ! empty( $wps_subscriptions ) && is_array( $wps_subscriptions ) ) {
				foreach ( $wps_subscriptions as $key => $value ) {

					$current_time = time();

					update_post_meta( $value->ID, 'wps_subscription_status', 'active' );
					update_post_meta( $value->ID, 'wps_schedule_start', $current_time );

					$wps_susbcription_trial_end = wps_sfw_susbcription_trial_date( $value->ID, $current_time );
					update_post_meta( $value->ID, 'wps_susbcription_trial_end', $wps_susbcription_trial_end );

					$wps_next_payment_date = wps_sfw_next_payment_date( $value->ID, $current_time, $wps_susbcription_trial_end );

					$wps_next_payment_date = apply_filters( 'wps_sfw_next_payment_date', $wps_next_payment_date, $value->ID );

					update_post_meta( $value->ID, 'wps_next_payment_date', $wps_next_payment_date );

					$wps_susbcription_end = wps_sfw_susbcription_expiry_date( $value->ID, $current_time, $wps_susbcription_trial_end );
					update_post_meta( $value->ID, 'wps_susbcription_end', $wps_susbcription_end );
				}

				update_post_meta( $order_id, 'wps_sfw_subscription_activated', 'yes' );
			}
		}
	}

	// End of class.
}
