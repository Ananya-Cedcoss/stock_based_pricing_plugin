<?php
/**
 * Provides a Stripe Express Gateway for WooCommerce One Click Upsell Funnel Pro
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      3.2.0
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/admin/gateway/stripe
 * @author     wpswings <webmaster@wpswings.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Custom gateway extension for Stripe support.
 */
class WPS_Stripe_Payment_Gateway  {

	/**
	 * Process the upsell payment.
	 *
	 * @since 3.5.0
	 * @param int $order_id Order id.
	 */
	public function process_upsell_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		// Get all upsell offer products.
		$upsell_remove = get_post_meta( $order_id, '_upsell_remove_items_on_fail', true );
		$upsell_total  = 0;
		if ( ! empty( $upsell_remove ) && is_array( $upsell_remove ) ) {

			foreach ( (array) $order->get_items() as $item_id => $item ) {

				if ( in_array( (int) $item_id, $upsell_remove, true ) ) {

					$upsell_total = $upsell_total + $item->get_total() + $item->get_total_tax();
				}
			}
			// Save for later use.
			update_post_meta( $order_id, '_upsell_items_charge_amount', $upsell_total );
		}

		$order_payment =  $order->get_payment_method() ;
		if ( 'stripe_cc' == $order_payment ) {
			$order_payment = 'stripe';

		}

		if ( 'stripe' !== $order_payment ) {
			
			return false;
		}

		$is_successful = false;

		try {

			$gateway = $this->get_wc_gateway();

			$source = $gateway->prepare_order_source( $order );

			$response = WC_Stripe_API::request( $this->generate_payment_request( $order, $source ) );


			// Log here complete response.
			if ( is_wp_error( $response ) ) {

				// @todo handle the error part here/failure of order.
				$error_message = sprintf( __( 'Something Went Wrong. Please see log file for more info.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

			} else {

				if ( ! empty( $response->error ) ) {

					$is_successful = false;
					/* translators: %s: decimal */
					$order_note = sprintf( esc_html__( 'Stripe Transaction Failed (%s)', 'one-click-upsell-funnel-for-woocommerce-pro' ), $response->error->message );
					$order->update_status( 'upsell-failed', $order_note );

				} else {

					// @todo handle the success part here/failure of order.
					update_post_meta( $order_id, '_upsell_payment_transaction_id', $response->id );
					/* translators: %s: decimal */
					$order_note = sprintf( __( 'Stripe Upsell Transaction Successful (%s)', 'one-click-upsell-funnel-for-woocommerce-pro' ), $response->id );
					
					if ( ! empty ( $response->balance_transaction->fee || ! empty( $response->balance_transaction->amount ) ) ) {
						update_post_meta( $order_id,'upsell_stripe_fee', $response->balance_transaction->fee );
						update_post_meta( $order_id,'upsell_stripe_amount', $response->balance_transaction->net );
					}
					
					// Update (v3.6.7) starts.
					// Manage order status according to dowbloadable products.
					if ( true === $order->needs_processing() ) {
						$order->update_status( 'processing', $order_note );
					} else {
						$order->update_status( 'completed', $order_note );
					}
					// Update (v3.6.7) ends.
					$is_successful = true;
				}
			}

			// Returns boolean.
			return $is_successful;

		} catch ( Exception $e ) {

			// @todo transaction failure to handle here.
			/* translators: %s: decimal */
			$order_note = sprintf( esc_html__( 'Stripe Transaction Failed (%s)', 'one-click-upsell-funnel-for-woocommerce-pro' ), $e->getMessage() );
			$order->update_status( 'upsell-failed', $order_note );
			return false;
		}
	}

	/**
	 * Generate the request for the payment.
	 *
	 * @since  3.5.0
	 * @param  WC_Order $order order.
	 * @param  object   $source source.
	 *
	 * @return array()
	 */
	protected function generate_payment_request( $order, $source ) {
		$order_id      = $order->get_id();
		$charge_amount = get_post_meta( $order_id, '_upsell_items_charge_amount', true );

		$gateway               = $this->get_wc_gateway();
		$post_data             = array();
		$post_data['currency'] = strtolower( $this->get_order_currency( $order ) );
		$post_data['amount']   = WC_Stripe_Helper::get_stripe_amount( $charge_amount, $post_data['currency'] );
		/* translators: %s: decimal */
		$post_data['description'] = sprintf( __( '%1$s - Order %2$s - upsell payment.', 'one-click-upsell-funnel-for-woocommerce-pro' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_order_number() );
		$post_data['capture']     = $gateway->capture ? 'true' : 'false';
		$billing_first_name       = $order->get_billing_first_name();
		$billing_last_name        = $order->get_billing_last_name();
		$billing_email            = $order->get_billing_email( $order, 'billing_email' );

		if ( ! empty( $billing_email ) && apply_filters( 'wc_stripe_send_stripe_receipt', false ) ) {
			$post_data['receipt_email'] = $billing_email;
		}
		$metadata              = array(
			__( 'customer_name', 'one-click-upsell-funnel-for-woocommerce-pro' ) => sanitize_text_field( $billing_first_name ) . ' ' . sanitize_text_field( $billing_last_name ),
			__( 'customer_email', 'one-click-upsell-funnel-for-woocommerce-pro' ) => sanitize_email( $billing_email ),
			'order_id' => $order_id,
		);
		$post_data['expand[]'] = 'balance_transaction';
		$post_data['metadata'] = apply_filters( 'wc_stripe_payment_metadata', $metadata, $order, $source );

		if ( $source->customer ) {
			$post_data['customer'] = ! empty( $source->customer ) ? $source->customer : '';
		}

		if ( $source->source ) {
			$post_data['source'] = ! empty( $source->source ) ? $source->source : '';
		}

		return apply_filters( 'wc_stripe_generate_payment_request', $post_data, $order, $source );
	}

	/**
	 * Get payment gateway.
	 *
	 * @since  3.5.0
	 * @return WC_Payment_Gateway.
	 */
	public function get_wc_gateway() {
		global $woocommerce;
		$gateways = $woocommerce->payment_gateways->payment_gateways();
		if ( ! empty( $gateways['stripe'] ) ) {
			return $gateways['stripe'];
		}
		return false;
	}

	/**
	 * Get order currency.
	 *
	 * @since  3.5.0
	 * @param  WC_Order $order Order.
	 *
	 * @return mixed|string
	 */
	public static function get_order_currency( $order ) {

		if ( version_compare( WC_VERSION, '3.0.0', 'ge' ) ) {
			return $order ? $order->get_currency() : get_woocommerce_currency();
		} else {
			return $order ? $order->get_order_currency() : get_woocommerce_currency();

		}
	}


	/**
	 * Refund a charge.
	 *
	 * @param   int    $order_id order id.
	 * @param   float  $amount refund amount.
	 * @param   string $reason reason of refund.
	 *
	 * @return bool
	 * @throws Exception Throws exception when charge wasn't captured.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return false;
		}

		$request = array();

		$order_currency = $order->get_currency();
		$captured       = $order->get_meta( '_stripe_charge_captured', true );
		$charge_id      = $order->get_meta( '_upsell_payment_transaction_id', true );

		if ( ! $charge_id ) {
			return false;
		}

		if ( ! is_null( $amount ) ) {
			$request['amount'] = WC_Stripe_Helper::get_stripe_amount( $amount, $order_currency );
		}

		// If order is only authorized, don't pass amount.
		if ( 'yes' !== $captured ) {
			unset( $request['amount'] );
		}

		if ( $reason ) {
			// Trim the refund reason to a max of 500 characters due to Stripe limits: https://stripe.com/docs/api/metadata.
			if ( strlen( $reason ) > 500 ) {
				$reason = function_exists( 'mb_substr' ) ? mb_substr( $reason, 0, 450 ) : substr( $reason, 0, 450 );
				// Add some explainer text indicating where to find the full refund reason.
				$reason = $reason . '... [See WooCommerce order page for full text.]';
			}

			$request['metadata'] = array(
				'reason' => $reason,
			);
		}

		$request['charge'] = $charge_id;
		WC_Stripe_Logger::log( "Info: Beginning refund for order {$charge_id} for the amount of {$amount}" );

		$request = apply_filters( 'wc_stripe_refund_request', $request, $order );

		$intent           = $this->get_intent_from_order( $order );
		$intent_cancelled = false;
		if ( $intent ) {
			// If the order has a Payment Intent pending capture, then the Intent itself must be refunded (cancelled), not the Charge.
			if ( ! empty( $intent->error ) ) {
				$response         = $intent;
				$intent_cancelled = true;
			} elseif ( 'requires_capture' === $intent->status ) {
				$result           = WC_Stripe_API::request(
					array(),
					'payment_intents/' . $intent->id . '/cancel'
				);
				$intent_cancelled = true;

				if ( ! empty( $result->error ) ) {
					$response = $result;
				} else {
					$charge   = end( $result->charges->data );
					$response = end( $charge->refunds->data );
				}
			}
		}

		if ( ! $intent_cancelled && 'yes' === $captured ) {
			$response = WC_Stripe_API::request( $request, 'refunds' );
		}

		if ( ! empty( $response->error ) ) {
			WC_Stripe_Logger::log( 'Error: ' . $response->error->message );

			return $response;

		} elseif ( ! empty( $response->id ) ) {
			$formatted_amount = wc_price( $response->amount / 100 );
			if ( in_array( strtolower( $order->get_currency() ), WC_Stripe_Helper::no_decimal_currencies(), true ) ) {
				$formatted_amount = wc_price( $response->amount );
			}

			// If charge wasn't captured, skip creating a refund and cancel order.
			if ( 'yes' !== $captured ) {
				/* translators: amount (including currency symbol) */
				$order->add_order_note( sprintf( __( 'Pre-Authorization for %s voided.', 'one-click-upsell-funnel-for-woocommerce-pro' ), $formatted_amount ) );
				$order->update_status( 'cancelled' );
				// If amount is set, that means this function was called from the manual refund form.
				if ( ! is_null( $amount ) ) {
					// Throw an exception to provide a custom message on why the refund failed.
					throw new Exception( __( 'The authorization was voided and the order cancelled. Click okay to continue, then refresh the page.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
				} else {
					// If refund was initiaded by changing order status, prevent refund without errors.
					return false;
				}
			}

			$order->update_meta_data( '_stripe_refund_id', $response->id );

			if ( isset( $response->balance_transaction ) ) {
				$this->update_fees( $order, $response->balance_transaction );
			}

			/* translators: 1) amount (including currency symbol) 2) transaction id 3) refund message */
			$refund_message = sprintf( __( 'Refunded %1$s - Refund ID: %2$s - Reason: %3$s', 'one-click-upsell-funnel-for-woocommerce-pro' ), $formatted_amount, $response->id, $reason );

			$order->add_order_note( $refund_message );
			WC_Stripe_Logger::log( 'Success: ' . html_entity_decode( wp_strip_all_tags( $refund_message ) ) );

			return true;
		}
	}

	/**
	 * Retrieves the payment intent, associated with an order.
	 *
	 * @since 4.2
	 * @param WC_Order $order The order to retrieve an intent for.
	 * @return obect|bool     Either the intent object or `false`.
	 */
	public function get_intent_from_order( $order ) {
		$intent_id = $order->get_meta( '_stripe_intent_id' );

		if ( $intent_id ) {
			return $this->get_intent( 'payment_intents', $intent_id );
		}

		// The order doesn't have a payment intent, but it may have a setup intent.
		$intent_id = $order->get_meta( '_stripe_setup_intent' );

		if ( $intent_id ) {
			return $this->get_intent( 'setup_intents', $intent_id );
		}

		return false;
	}

	/**
	 * Retrieves intent from Stripe API by intent id.
	 *
	 * @param string $intent_type   Either 'payment_intents' or 'setup_intents'.
	 * @param string $intent_id     Intent id.
	 * @return object|bool          Either the intent object or `false`.
	 * @throws Exception            Throws exception for unknown $intent_type.
	 */
	private function get_intent( $intent_type, $intent_id ) {
		if ( ! in_array( $intent_type, array( 'payment_intents', 'setup_intents' ), true ) ) {
			throw new Exception( "Failed to get intent of type $intent_type. Type is not allowed" );
		}

		$response = WC_Stripe_API::request( array(), "$intent_type/$intent_id", 'GET' );

		if ( $response && isset( $response->{ 'error' } ) ) {
			$error_response_message = print_r( $response, true ); //phpcs:ignore.
			WC_Stripe_Logger::log( "Failed to get Stripe intent $intent_type/$intent_id." );
			WC_Stripe_Logger::log( "Response: $error_response_message" );
			return false;
		}

		return $response;
	}

	/**
	 * Updates Stripe fees/net.
	 * e.g usage would be after a refund.
	 *
	 * @since 4.0.0
	 * @version 4.0.6
	 * @param object $order The order object.
	 * @param int    $balance_transaction_id balance_transaction_id.
	 */
	public function update_fees( $order, $balance_transaction_id ) {
		$balance_transaction = WC_Stripe_API::retrieve( 'balance/history/' . $balance_transaction_id );

		if ( empty( $balance_transaction->error ) ) {
			if ( isset( $balance_transaction ) && isset( $balance_transaction->fee ) ) {
				// Fees and Net needs to both come from Stripe to be accurate as the returned
				// values are in the local currency of the Stripe account, not from WC.
				$fee_refund = ! empty( $balance_transaction->fee ) ? WC_Stripe_Helper::format_balance_fee( $balance_transaction, 'fee' ) : 0;
				$net_refund = ! empty( $balance_transaction->net ) ? WC_Stripe_Helper::format_balance_fee( $balance_transaction, 'net' ) : 0;

				// Current data fee & net.
				$fee_current = WC_Stripe_Helper::get_stripe_fee( $order );
				$net_current = WC_Stripe_Helper::get_stripe_net( $order );

				// Calculation.
				$fee = (float) $fee_current + (float) $fee_refund;
				$net = (float) $net_current + (float) $net_refund;

				WC_Stripe_Helper::update_stripe_fee( $order, $fee );
				WC_Stripe_Helper::update_stripe_net( $order, $net );

				$currency = ! empty( $balance_transaction->currency ) ? strtoupper( $balance_transaction->currency ) : null;
				WC_Stripe_Helper::update_stripe_currency( $order, $currency );

				if ( is_callable( array( $order, 'save' ) ) ) {
					$order->save();
				}
			}
		} else {
			WC_Stripe_Logger::log( 'Unable to update fees/net meta for order: ' . $order->get_id() );
		}
	}


	// End of class.

}