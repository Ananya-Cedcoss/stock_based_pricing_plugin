<?php
/**
 * Provides a Stripe payment method for WooCommerce One Click Upsell Funnel Pro.
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      3.2.0
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/admin/gateway/stripe
 * @author     wpswings <webmaster@wpswings.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Stripe class for custom gateway.
 */
class WPS_Wocuf_Pro_Stripe_Gateway_Admin extends WC_Payment_Gateway_CC {

	/**
	 * Capture immediate charge.
	 *
	 * @var bool
	 */
	public $capture;

	/**
	 * API access secret key
	 *
	 * @var string
	 */
	private $secret_key;

	/**
	 * API access publishable key
	 *
	 * @var string
	 */
	private $publishable_key;

	/**
	 * API error messages
	 *
	 * @var string
	 */
	private $error_message = '';


	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'wps-wocuf-pro-stripe-gateway';
		$this->has_fields         = true;
		$this->method_title       = __( 'Stripe ( One Click Upsell )', 'one-click-upsell-funnel-for-woocommerce-pro' );
		$this->method_title_short = $this->method_title;
		$this->method_description = __( 'Stripe works by adding payment fields on the checkout and then sending the details to Stripe for verification.', 'one-click-upsell-funnel-for-woocommerce-pro' );
		$this->supports           = array(
			'products',
			'refunds',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'multiple_subscriptions',
		);

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();
	
		// Get setting values.
		$this->title               = $this->get_option( 'title' );
		$this->enabled             = $this->get_option( 'enabled' );
		$this->description         = $this->get_option( 'description' );
		$this->testmode            = $this->get_option( 'testmode' );
		$this->gateway_description = $this->get_option( 'gateway_description' );
		$this->logging             = $this->get_option( 'logging' );
		$this->stripe_icons        = $this->get_option( 'stripe_icons' );
		$this->capture             = 'yes' === $this->get_option( 'capture' ) ? 'automatic' : 'manual';

		if ( 'yes' === $this->enabled ) {

			if ( 'yes' === $this->testmode ) {
				$this->publishable_key = $this->get_option( 'test_publishable_key' );
				$this->secret_key      = $this->get_option( 'test_secret_key' );
			} else {
				$this->publishable_key = $this->get_option( 'live_publishable_key' );
				$this->secret_key      = $this->get_option( 'live_secret_key' );
			}

			if ( empty( $this->publishable_key ) || empty( $this->secret_key ) ) {

				$this->enabled = 'no';
			}

			// Add admin notice when stripe keys are empty and disable gateway.
			add_action( 'admin_notices', array( $this, 'wps_wocuf_pro_stripe_admin_notices' ) );
		}

		// Set secret key for stripe library.
		\Stripe\Stripe::setApiKey( $this->secret_key );

		// Save hook for gateway settings.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		/**
		 * Enqueue Woocommerce card form js ( Will be only enqueued on checkout page ).
		 * As if this stripe method is loaded afterwards without page reload then this
		 * js doens't work.
		 * Afterwards means like before entering country order total is 0 coz product
		 * price is 0 so no payment comes but then as we change country then shipping
		 * applies and order total is greater than 0 then payment method comes up
		 * but card form js is not loaded properly, so need to enqueue it.
		 *
		 * This js is responsible for proper number validation of card number and
		 * automatically bringing 0 and / in exp date.
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'wps_enqueue_wc_card_form_js' ) );

		// Recurring payments for Subscriptions.
		add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'process_subscriptions_renewal_payment' ), 10, 2 );

		add_filter( 'woocommerce_payment_successful_result', array( $this, 'wps_wocuf_pro_intent_redirect' ), 99999, 2 );

		add_action( 'woocommerce_account_view-order_endpoint', array( $this, 'check_intent_status_on_order_page' ), 1 );
	}

	/**
	 * Enqueue Woocommerce card form js.
	 *
	 * @since    1.0.0
	 */
	public function wps_enqueue_wc_card_form_js() {

		wp_enqueue_script( 'wc-credit-card-form' );

		// If Stripe is not enabled.
		if ( 'no' === $this->enabled ) {
			return;
		}

		// If no SSL bail.
		if ( ! $this->testmode && ! is_ssl() ) {
			$this->wps_wocuf_pro_create_stripe_log( '', 'wps_enqueue_wc_card_form_js', 'Stripe live mode requires SSL.' );
			return;
		}

		wp_register_script( 'stripe', 'https://js.stripe.com/v3/', '', '3.0', true );

		wp_register_script( 'wps_wocuf_pro_stripe', plugin_dir_url( __FILE__ ) . 'assets/js/stripe.min.js', array( 'jquery-payment', 'stripe' ), '4.2.3', true );

		// No such requirement just for satisfying the Stripe.js need.
		$sepa_elements_options = apply_filters(
			'wc_stripe_sepa_elements_options',
			array(
				'supportedCountries' => array( 'SEPA' ),
				'placeholderCountry' => WC()->countries->get_base_country(),
				'style'              => array( 'base' => array( 'fontSize' => '15px' ) ),
			)
		);

		$stripe_params = array(
			'key'                   => $this->publishable_key,
			'i18n_terms'            => __( 'Please accept the terms and conditions first.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
			'i18n_required_fields'  => __( 'Please fill in required checkout fields first.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
			'invalid_request_error' => __( 'Unable to process this payment, please try again or use alternative method.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
			'ajaxurl'               => WC_AJAX::get_endpoint( '%%endpoint%%' ),
			'nonce'                 => wp_create_nonce( 'wps_wocuf_pro_stripe_nonce' ),
			'sepa_elements_options' => $sepa_elements_options,
			'elements_options'      => apply_filters( 'wc_stripe_elements_options', array() ),
		);

		wp_localize_script( 'wps_wocuf_pro_stripe', 'wc_stripe_params', apply_filters( 'wc_stripe_params', $stripe_params ) );
		wp_enqueue_script( 'wps_wocuf_pro_stripe' );
	}

	/**
	 * Stripe payment form on checkout.
	 *
	 * @since    3.2.0
	 */
	public function payment_fields() {

		$description = $this->get_description();

		if ( ! empty( $description ) ) {

			echo wp_kses( $description, array( 'p' ) );

			if ( 'yes' === $this->testmode ) : ?>
				<div class="wps-wocuf-stripe-test-mode"><b><?php esc_html_e( 'Test Mode Enabled', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></b></div>
				<p>
					<?php esc_html_e( 'In test mode, you can use these card numbers,', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>
					<p><b><?php esc_html_e( 'Normal payments :', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></b> <?php esc_html_e( '4242424242424242', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>
					<p><b><?php esc_html_e( 'SCA authentication :', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></b> <?php esc_html_e( '4000002500003155', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>
				<p>
					<?php esc_html_e( 'with any CVC and a valid expiration date or check the', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
					<a href="https://stripe.com/docs/testing" target="_blank"><?php esc_html_e( 'Stripe Testing documentation', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
				</p>
				<p id="wps-wocuf-stripe-test-mode-notice">
					<?php esc_html_e( 'Please use a Dummy email address in Billing email.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
				</p>
				<?php
			endif;
		}
		

		?>

		<div class="stripe-source-errors" role="alert"></div>
		<div class="wps_wocuf_pro_credit_card_form">
		<?php

		if ( $this->supports( 'tokenization' ) && is_checkout() ) {
			$this->tokenization_script();
			$this->saved_payment_methods();
			$this->form();
			$this->save_payment_method_checkbox();
		} else {
			$this->form();
		}

		?>
		</div>
		<?php
	}

	/**
	 * Checking enability of one click upsell stripe.
	 *
	 * @since    3.2.0
	 */
	public function wps_wocuf_pro_stripe_admin_notices() {

		if ( 'no' === $this->get_option( 'enabled' ) ) {

			return;
		}

		if ( empty( $this->publishable_key ) || empty( $this->secret_key ) ) {

			echo '<div class="error"><p>' . esc_html__( 'Stripe needs API Keys to work, please find your secret and publishable keys in the ', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '<a href="https://manage.stripe.com/account/apikeys" target="_blank">' . esc_html__( 'Stripe accounts section ', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</a></p></div>';

		}
	}

	/**
	 * One click upsell stripe form fields template.
	 *
	 * @since    3.2.0
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'              => array(
				'type'        => 'checkbox',
				'title'       => __( 'Enable/Disable', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'description' => __( 'Enable Stripe for WooCommerce One Click Upsell Funnel Pro', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'label'       => __( 'Enable Stripe ( One Click Upsell )', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'title'                => array(
				'type'        => 'text',
				'title'       => __( 'Title', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'description' => __( 'The title which your customer will see during checkout.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'default'     => __( 'Credit/Debit Card Payment ( Stripe )', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'desc_tip'    => true,
			),
			'description'          => array(
				'type'        => 'textarea',
				'title'       => __( 'Description', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'description' => __( 'Optional. The description, which the customer will see during checkout.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'default'     => __( 'Use your credit/debit card to make payments via Stripe', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'id'          => 'woocommerce_wps-wocuf-pro-stripe-gateway_description',
				'css'         => 'max-width:400px',
				'desc_tip'    => true,
			),
			'gateway_description'  => array(
				'type'        => 'text',
				'title'       => __( 'Gateway Description', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'description' => __( 'This may be up to 22 characters. The statement description must contain at least one letter, may not include ><"\' characters, and will appear on your customer\'s statement in capital letters.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'default'     => __( 'Upsell Stripe', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'desc_tip'    => true,
			),
			'stripe_icons'         => array(
				'title'   => __( 'Card Icons', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'label'   => __( 'Show card icons', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),
			'capture'              => array(
				'title'       => __( 'Capture', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'label'       => __( 'Capture charge immediately', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'type'        => 'checkbox',
				'description' => __( 'Whether or not to immediately capture the charge. When unchecked, the charge issues an authorization and will need to be captured later. Uncaptured charges expire in 7 days.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'testmode'             => array(
				'type'        => 'checkbox',
				'title'       => __( 'Test mode', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'description' => __( 'Use the test mode in Stripe\'s dashboard to verify that everything works before going live.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'label'       => __( 'Turn on testing', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'test_publishable_key' => array(
				'type'    => 'password',
				'title'   => __( 'Stripe API Test Publishable key', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'default' => '',
			),
			'test_secret_key'      => array(
				'type'    => 'password',
				'title'   => __( 'Stripe API Test Secret key', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'default' => '',
			),
			'live_publishable_key' => array(
				'type'    => 'password',
				'title'   => __( 'Stripe API Live Publishable key', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'default' => '',
			),
			'live_secret_key'      => array(
				'type'    => 'password',
				'title'   => __( 'Stripe API Live Secret key', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'default' => '',
			),
			'logging'              => array(
				'title'       => __( 'Logging', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'label'       => __( 'Log debug messages', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'type'        => 'checkbox',
				'description' => __( 'Save debug messages to the WooCommerce System Status log.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Get card icons from Woocommerce stripe.
	 *
	 * @since    1.0.0
	 */
	public function get_icon() {

		if ( 'yes' !== $this->stripe_icons ) {

			return;
		}

		$icons = array(
			'visa'       => '<img src="' . WPS_WOCUF_PRO_URL . 'gateways/stripe/assets/visa.svg" class="stripe-visa-icon stripe-icon" alt="Visa" />',
			'amex'       => '<img src="' . WPS_WOCUF_PRO_URL . 'gateways/stripe/assets/amex.svg" class="stripe-amex-icon stripe-icon" alt="American Express" />',
			'mastercard' => '<img src="' . WPS_WOCUF_PRO_URL . 'gateways/stripe/assets/mastercard.svg" class="stripe-mastercard-icon stripe-icon" alt="Mastercard" />',
			'discover'   => '<img src="' . WPS_WOCUF_PRO_URL . 'gateways/stripe/assets/discover.svg" class="stripe-discover-icon stripe-icon" alt="Discover" />',
			'diners'     => '<img src="' . WPS_WOCUF_PRO_URL . 'gateways/stripe/assets/diners.svg" class="stripe-diners-icon stripe-icon" alt="Diners" />',
			'jcb'        => '<img src="' . WPS_WOCUF_PRO_URL . 'gateways/stripe/assets/jcb.svg" class="stripe-jcb-icon stripe-icon" alt="JCB" />',
		);

		$icons_str = '';

		$icons_str .= $icons['visa'];
		$icons_str .= $icons['amex'];
		$icons_str .= $icons['mastercard'];

		if ( 'USD' === get_woocommerce_currency() ) {
			$icons_str .= $icons['discover'];
			$icons_str .= $icons['jcb'];
			$icons_str .= $icons['diners'];
		}

		return $icons_str;

	}

	/**
	 * Validate form fields.
	 *
	 * @since   1.0.0
	 * @return    boolean
	 */
	public function validate_fields() {

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		if ( empty( $_POST[ $this->id . '-card-number' ] ) || empty( $_POST[ $this->id . '-card-expiry' ] ) || empty( $_POST[ $this->id . '-card-cvc' ] ) ) {

			wc_add_notice( __( 'All Card details are required.', 'one-click-upsell-funnel-for-woocommerce-pro' ), 'error' );

			return false;
		}

		return true;
	}

	/**
	 * Process the payment( updated ).
	 *
	 * @since 3.2.0
	 * @param int $order_id             Main order id.
	 * @param int $upsell               Is Upsell transaction.
	 * @param int $parent_order_id      Parent order id.
	 *
	 * @throws Exception Throws exception when charge wasn't captured.
	 */
	public function process_payment( $order_id, $upsell = 'false', $parent_order_id = '' ) {

		// In Case of upsell or Renewal.
		if ( 'subs_renewal' === $upsell || 'true' === $upsell ) {

			// Get Intent details from Order id.
			$order        = wc_get_order( $order_id );
			$order_amount = $order->get_total();
			$upsell_class = new Woocommerce_One_Click_Upsell_Funnel_Pro_Public( 'woocommerce_one_click_upsell_funnel_pro', '3.2.0' );

			// If order amount is zero return with success.
			$result = $this->process_zero_total_order( $order_amount, $order );
			if ( 'true' === $result['message'] ) {

				return $result;
			}

			// Get paid intent for parent( Upsell/Subscriptions ).
			$parent_order_id = 'subs_renewal' === $upsell ? $parent_order_id : $order_id;

			// Handle for before SCA API.
			$token_before_14 = get_post_meta( $parent_order_id, 'wps_wocuf_stripe_info', true );
			if ( 'subs_renewal' === $upsell && ! empty( $token_before_14 ) ) {

				$this->charge_subs_before_sca( $order, $token_before_14 );
			}

			$parent_order_payment_intent_id = get_post_meta( $parent_order_id, '_saved_payment_intent_id', true );

			$parent_order_setup_intent_id = get_post_meta( $parent_order_id, '_setup_future_intent', true );

			if ( empty( $parent_order_payment_intent_id ) && empty( $parent_order_setup_intent_id ) ) {

				$order->update_status( 'upsell-failed' );
				$order->add_order_note( esc_html__( 'No Payment Intent found.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

				$this->wps_wocuf_pro_create_stripe_log( $order_id, 'process_payment', 'Upsell/Renewal Case', esc_html__( 'No Payment Intent found.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

				$upsell_class->wps_wocuf_pro_expire_offer_on_failed_upsell_payment( $order );
				return;
			}

			if ( ! empty( $parent_order_setup_intent_id ) ) {

				$intent_data = \Stripe\SetupIntent::retrieve( $parent_order_setup_intent_id );

			} else {

				$parent_order_payment_intent = \Stripe\PaymentIntent::retrieve( $parent_order_payment_intent_id );

				$intent_data = end( $parent_order_payment_intent->charges->data );
			}

			// Upsell Order.
			if ( 'true' === $upsell ) {

				$order_status = $order->get_status();

				if ( ( 'upsell-parent' !== $order_status ) && ( 'on-hold' !== $order_status ) ) {

					$order->add_order_note( __( "Upsell Order can't be processed until the parent order is paid.", 'one-click-upsell-funnel-for-woocommerce-pro' ) );

					// Upsell Offer Failed!
					$order->update_status( 'upsell-failed' );
					$this->wps_wocuf_pro_create_stripe_log( $order_id, 'process_payment', 'Upsell/Renewal Case', esc_html__( 'No Parent Intent found.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
					$upsell_class->wps_wocuf_pro_expire_offer_on_failed_upsell_payment( $order );
					return;
				}

				$order_amount = $order->get_total() - ( $intent_data->amount / 100 );

				// If order amount is zero return with success.
				$result = $this->process_zero_total_order( $order_amount, $order );
				if ( 'true' === $result['message'] ) {

					return $result;
				}
			}

			// Retrived Intent data.
			if ( empty( $intent_data ) ) {

				$failed_message = __( 'No Such Payment Intent found : ', 'one-click-upsell-funnel-for-woocommerce-pro' );
				$order->add_order_note( $failed_message );

				// Upsell Offer Failed!
				$this->wps_wocuf_pro_create_stripe_log( $order_id, 'process_payment', 'Upsell/Renewal Case', esc_html__( 'No Such Payment Intent found.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
				$order->update_status( 'upsell-failed' );
				$upsell_class->wps_wocuf_pro_expire_offer_on_failed_upsell_payment( $order );
				return;
			}

			// Specify which payment is that.
			$payment_desc = 'subs_renewal' === $upsell ? esc_html__( 'Renewal payment for order id : ', 'one-click-upsell-funnel-for-woocommerce-pro' ) : esc_html__( 'Upsell payment for order id : ', 'one-click-upsell-funnel-for-woocommerce-pro' );

			try {

				$wps_wocuf_pro_stripe_renewal_intent = \Stripe\PaymentIntent::create(
					array(
						'amount'               => (int) $this->wps_wocuf_pro_stripe_amount( $order_amount ),
						'currency'             => strtolower( get_woocommerce_currency() ),
						'payment_method_types' => array( 'card' ),
						'customer'             => $intent_data->customer,
						'payment_method'       => $intent_data->payment_method,
						'off_session'          => true,
						'confirm'              => true,
						'description'          => $payment_desc . $order_id,
						'capture_method'       => $this->capture,
					)
				);
			} catch ( Exception $e ) { // Catch if authentication failed in any case ( Never gonna happen in live ).

				// Upsell Offer Failed!
				$this->wps_wocuf_pro_create_stripe_log( $order_id, 'process_payment', 'Upsell/Renewal Case', $e->getMessage() );

				$order->update_status( 'upsell-failed', $e->getMessage() );
				$upsell_class->wps_wocuf_pro_expire_offer_on_failed_upsell_payment( $order );
			}

			if ( ! empty( $wps_wocuf_pro_stripe_renewal_intent ) ) {

				// Check if charge is been done.
				if ( ! empty( $wps_wocuf_pro_stripe_renewal_intent->charges->data ) ) {

					$payment_status = $wps_wocuf_pro_stripe_renewal_intent->charges->data;

					// If already captured!
					if ( $payment_status[0]->captured ) {

						$set_status = $order->needs_processing() ? 'processing' : 'completed';
						$order->update_status( $set_status );

						$order->payment_complete();

						$order->add_order_note( __( 'Payment completed.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

						$order->add_order_note( __( 'Stripe charge complete (Payment Intent ID:', 'one-click-upsell-funnel-for-woocommerce-pro' ) . $wps_wocuf_pro_stripe_renewal_intent->id );

						update_post_meta( $order_id, '_wps_wocuf_pro_stripe_upsell_intent', $wps_wocuf_pro_stripe_renewal_intent->id );

						if ( wps_upsell_order_contains_subscription( $order_id ) && wps_upsell_pg_supports_subs( $order_id ) ) {

							// If upsell parent order from stripe then activate subscriptions.
							WC_Subscriptions_Manager::activate_subscriptions_for_order( $order );
						}

						$this->wps_wocuf_pro_create_stripe_log( $order_id, 'process_payment', 'Upsell/Renewal Case', 'Success.' );

						return array(
							'result'   => 'success',
							'redirect' => $this->get_return_url( $order ),
						);

					} elseif ( 'manual' === $this->capture && 'requires_capture' === $wps_wocuf_pro_stripe_renewal_intent->status ) {

						// Payment Authorized and ready to capture.
						$order->add_order_note( 'Stripe Payment Authorized ( Payment Intent ID:' . $wps_wocuf_pro_stripe_renewal_intent->id . '). Please capture the payment within 7 days.' );

						$order->update_status( 'on-hold' );

						update_post_meta( $order_id, '_wps_wocuf_pro_capture_type', 'manual' );

						update_post_meta( $order_id, '_wps_wocuf_pro_stripe_upsell_intent', $wps_wocuf_pro_stripe_renewal_intent->id );

						if ( WC()->cart ) {

							WC()->cart->empty_cart();
						}

						return array(
							'result'   => 'success',
							'redirect' => $this->get_return_url( $order ),
						);
					} else {

						/**
						 * Handle for subscriptions and upsell failed payment seperately.
						 * In upsell payments just expire the offer.
						 * In subscriptions Do, nothing Just mark failed!
						 */
						if ( 'failed' === $wps_wocuf_pro_stripe_renewal_intent->status ) {

							$status_message = ( $wps_wocuf_pro_stripe_renewal_intent->last_payment_error )
								/* translators: %s: id */
								? sprintf( __( 'Payment processing failed : %s', 'one-click-upsell-funnel-for-woocommerce-pro' ), $wps_wocuf_pro_stripe_renewal_intent->last_payment_error->message )
								: __( 'Payment processing failed.', 'one-click-upsell-funnel-for-woocommerce-pro' );

							if ( 'subs_renewal' === $upsell ) {

								$this->wps_wocuf_pro_create_stripe_log( $order_id, 'process_payment', 'Upsell/Renewal Case', $wps_wocuf_pro_stripe_renewal_intent->last_payment_error );
								$order->update_status( 'failed', $status_message );

							} else {

								// Upsell Offer Failed!
								$this->wps_wocuf_pro_create_stripe_log( $order_id, 'process_payment', 'Upsell/Renewal Case', $wps_wocuf_pro_stripe_renewal_intent->last_payment_error );

								$order->update_status( 'upsell-failed', $status_message );
								$upsell_class->wps_wocuf_pro_expire_offer_on_failed_upsell_payment( $order );
							}

							return;
						}
					}
				}
			} else {

				$failed_message = __( 'Payment Intent Creation Failed.', 'one-click-upsell-funnel-for-woocommerce-pro' );
				$order->add_order_note( $failed_message );

				if ( 'subs_renewal' === $upsell ) {

					$order->update_status( 'failed', $failed_message );
					$this->wps_wocuf_pro_create_stripe_log( $order_id, 'process_payment', 'Upsell/Renewal Case', $failed_message );

				} else {

					// Upsell Offer Failed!
					$order->update_status( 'upsell-failed', $failed_message );
					$this->wps_wocuf_pro_create_stripe_log( $order_id, 'process_payment', 'Upsell/Renewal Case', $failed_message );
					$upsell_class->wps_wocuf_pro_expire_offer_on_failed_upsell_payment( $order );
				}
			}
		} elseif ( 'false' === $upsell ) {

			$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
			$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

			if ( ! $id_nonce_verified ) {
				wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
			}

			// Parent or single order.
			$this->order = wc_get_order( $order_id );

			$wps_wocuf_pro_stripe_card_number = isset( $_POST[ $this->id . '-card-number' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->id . '-card-number' ] ) ) : '';

			$wps_wocuf_pro_stripe_card_cvc = isset( $_POST[ $this->id . '-card-cvc' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->id . '-card-cvc' ] ) ) : '';

			$wps_wocuf_pro_stripe_card_exp = isset( $_POST[ $this->id . '-card-expiry' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->id . '-card-expiry' ] ) ) : '';

			$billing_first_name = $this->order->get_billing_first_name();
			$billing_last_name  = $this->order->get_billing_last_name();
			$billing_country    = $this->order->get_billing_country();
			$billing_state      = $this->order->get_billing_state();
			$billing_city       = $this->order->get_billing_city();
			$billing_postcode   = $this->order->get_billing_postcode();
			$billing_address_1  = $this->order->get_billing_address_1();
			$billing_address_2  = $this->order->get_billing_address_2();

			$wps_wocuf_pro_stripe_card_fname = isset( $billing_first_name ) ? sanitize_text_field( $billing_first_name ) : '';

			$wps_wocuf_pro_stripe_card_lname = isset( $billing_last_name ) ? sanitize_text_field( $billing_last_name ) : '';

			$wps_wocuf_pro_stripe_country = isset( $billing_country ) ? sanitize_text_field( $billing_country ) : '';

			$wps_wocuf_pro_stripe_state = isset( $billing_state ) ? sanitize_text_field( $billing_state ) : '';

			$wps_wocuf_pro_stripe_city = isset( $billing_city ) ? sanitize_text_field( $billing_city ) : '';

			$wps_wocuf_pro_stripe_zip = isset( $billing_postcode ) ? sanitize_text_field( $billing_postcode ) : '';

			$wps_wocuf_pro_stripe_address1 = isset( $billing_address_1 ) ? sanitize_text_field( $billing_address_1 ) : '';

			$wps_wocuf_pro_stripe_address2 = isset( $billing_address_2 ) ? sanitize_text_field( $billing_address_2 ) : '';

			$wps_wocuf_pro_stripe_name = $wps_wocuf_pro_stripe_card_fname . ' ' . $wps_wocuf_pro_stripe_card_lname;

			$wps_wocuf_pro_temp = explode( '/', $wps_wocuf_pro_stripe_card_exp );

			$wps_wocuf_pro_stripe_card_exp_month = isset( $wps_wocuf_pro_temp[0] ) ? $wps_wocuf_pro_temp[0] : '';

			$wps_wocuf_pro_stripe_card_exp_year = isset( $wps_wocuf_pro_temp[1] ) ? '20' . $wps_wocuf_pro_temp[1] : '';

			$wps_wocuf_pro_stripe_card_exp_year = str_replace( ' ', '', $wps_wocuf_pro_stripe_card_exp_year );

			$payment_method = '';
			$customer_id    = '';

			$create_payment_method = \Stripe\PaymentMethod::create(
				array(
					'type' => 'card',
					'card' => array(
						'number'    => $wps_wocuf_pro_stripe_card_number,
						'exp_month' => (int) $wps_wocuf_pro_stripe_card_exp_month,
						'exp_year'  => (int) $wps_wocuf_pro_stripe_card_exp_year,
						'cvc'       => $wps_wocuf_pro_stripe_card_cvc,
					),
				)
			);

			$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Payment Method', 'Single Order', 'Success.' . $create_payment_method );

			if ( ! empty( $create_payment_method->id ) ) {

				$payment_method_id = $create_payment_method->id;

				// Now create customer.
				$new_customer = \Stripe\Customer::create(
					array(
						'name'    => $wps_wocuf_pro_stripe_name,
						'phone'   => $this->order->get_billing_phone(),
						'email'   => $this->order->get_billing_email(),
						'address' => array(
							'city'        => $wps_wocuf_pro_stripe_city,
							'country'     => $wps_wocuf_pro_stripe_country,
							'line1'       => $wps_wocuf_pro_stripe_address1,
							'line2'       => $wps_wocuf_pro_stripe_address2,
							'state'       => $wps_wocuf_pro_stripe_state,
							'postal_code' => $wps_wocuf_pro_stripe_zip,
						),
					)
				);

				$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Customer create', 'Single Order', 'Success. <br>' . $new_customer );

				if ( ! empty( $new_customer->id ) ) {

					$customer_id = $new_customer->id;

					// Attach the payment method to id.
					$payment_method = \Stripe\PaymentMethod::retrieve( $payment_method_id );
					$payment_method->attach(
						array(
							'customer' => $customer_id,
						)
					);

				}
			}

			// Create intent after creating a customer and attaching it to the same.
			if ( ! empty( $customer_id ) && ! empty( $payment_method_id ) ) {

				$order_amount = (int) $this->order->get_total();

				// If order amount is zero return with success.{ Case called with in subscriptions with signup zero and also with some free trials }.
				if ( empty( $order_amount ) ) {

					$setup_intent = $this->setup_future_intent( $customer_id, $payment_method_id );

					// For Non Sca cards, it is possible to save details off session without charge.
					if ( 'succeeded' === $setup_intent->status ) {

						update_post_meta( $this->order->get_id(), '_setup_future_intent', $setup_intent->id );

						$this->order->payment_complete();
						return array(
							'message'  => 'true',
							'result'   => 'success',
							'redirect' => $this->get_return_url( $this->order ),
						);

					} else {

						/**
						 * Add code SCA cards handing here (later).
						 * On this condition if a subscription is already made
						 * by woocommerce.
						 * So, if the order gets succesful by different payment
						 * method then both will activated.
						 * And the old one will renew by same payment method.
						 * Hence better to delete it.
						 * And let the woocommerce handle the creation.
						 */
						WC_Subscriptions_Manager::maybe_delete_subscription( $order_id );
						throw new Exception( __( 'Sorry, Stripe cannot authenticate Intent for order total zero.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
					}
				}

				$payment_desc = esc_html__( 'Payment for order id : ', 'one-click-upsell-funnel-for-woocommerce-pro' );

				// Creating Payment Intent.
				$card_intent_info = array(
					'amount'               => (int) $this->wps_wocuf_pro_stripe_amount( $this->order->get_total() ),
					'currency'             => strtolower( get_woocommerce_currency() ),
					'payment_method_types' => array( 'card' ),
					'confirm'              => true,
					'customer'             => $customer_id,
					'payment_method'       => $payment_method_id,
					'setup_future_usage'   => 'off_session',
					'description'          => $payment_desc . $order_id,
					'capture_method'       => $this->capture,
				);

				$wps_wocuf_pro_stripe_create_intent = \Stripe\PaymentIntent::create( $card_intent_info );

				$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Payment Intent', 'Single Order ( Method )' . $this->capture, 'Success : <br>' . $wps_wocuf_pro_stripe_create_intent );

				update_post_meta( $order_id, '_saved_payment_intent_id', $wps_wocuf_pro_stripe_create_intent->id );

				// Check if the SCA Confirmation is required?
				if ( ! empty( $wps_wocuf_pro_stripe_create_intent->charges->data ) ) {

					$payment_status = $wps_wocuf_pro_stripe_create_intent->charges->data;

					// If already captured !
					if ( 'automatic' === $this->capture && $payment_status[0]->captured ) {

						$this->order->payment_complete();
						$this->order->add_order_note( __( 'Stripe charge complete :: Payment Intent ID:', 'one-click-upsell-funnel-for-woocommerce-pro' ) . $wps_wocuf_pro_stripe_create_intent->id );

						if ( WC()->cart ) {

							WC()->cart->empty_cart();
						}

						return array(
							'result'   => 'success',
							'redirect' => $this->get_return_url( $this->order ),
						);
					} elseif ( 'manual' === $this->capture && 'requires_capture' === $wps_wocuf_pro_stripe_create_intent->status ) {

						// Payment Authorized and ready to capture.
						$msg = sprintf(
							/* translators: %s: decimal */
							esc_html__( 'Stripe Payment Authorized ( Payment Intent ID: %s). Please capture the payment within 7 days.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							$wps_wocuf_pro_stripe_create_intent->id
						);
						$this->order->add_order_note( $msg );

						$this->order->update_status( 'on-hold' );

						update_post_meta( $order_id, '_wps_wocuf_pro_capture_type', 'manual' );

						if ( WC()->cart ) {

							WC()->cart->empty_cart();
						}

						return array(
							'result'   => 'success',
							'redirect' => $this->get_return_url( $this->order ),
						);

					} else {

						return array(
							'result'   => 'failure',
							'messages' => $wps_wocuf_pro_stripe_create_intent->last_payment_error,
							'redirect' => wc_get_cart_url(),
						);
					}
				}

				// If authentication is required.
				$wps_wocuf_pro_intent_confirmation = $wps_wocuf_pro_stripe_create_intent->confirm();

				// Acheive requires action. Use stripe.js for getting a modal.
				if ( ! empty( $wps_wocuf_pro_intent_confirmation ) ) {

					/**
					 * This URL contains only a hash, which will be sent to `checkout.js` where it will be set like this:
					 * `window.location = result.redirect`
					 * Once this redirect is sent to JS, the `onHashChange` function will execute `handleCardPayment`.
					 */

					$intent_redirect = array(
						'result'        => 'success',
						'redirect'      => $this->get_return_url( $this->order ),
						'intent_secret' => $wps_wocuf_pro_intent_confirmation->client_secret,
					);

					return $intent_redirect;
				}
			}
		}
	}

	/**
	 * Process the Renewal payment( Updated ).
	 *
	 * @since 3.2.0
	 * @param int $renewal_order_total renewal_order_total.
	 * @param int $renewal_order renewal_order.
	 */
	public function process_subscriptions_renewal_payment( $renewal_order_total, $renewal_order ) {

		$renewal_order_id = $renewal_order->get_id();

		$subscriptions = wcs_get_subscriptions_for_renewal_order( $renewal_order );

		foreach ( $subscriptions as  $subs_obj ) {

			$parent_order_id = $subs_obj->get_parent_id();
			break;
		}

		if ( ! empty( $parent_order_id ) ) {

			$this->process_payment( $renewal_order_id, 'subs_renewal', $parent_order_id );
		}
	}

	/**
	 * Converts the order total to be used over stripe.
	 *
	 * @since 3.2.0
	 * @param int $order_total Order total.
	 */
	public function wps_wocuf_pro_stripe_amount( $order_total ) {

		$currency = get_woocommerce_currency();

		switch ( strtoupper( $currency ) ) {
			case 'MGA':
			case 'PYG':
			case 'RWF':
			case 'VND':
			case 'VUV':
			case 'XAF':
			case 'XOF':
			case 'XPF':
			case 'BIF':
			case 'CLP':
			case 'DJF':
			case 'GNF':
			case 'JPY':
			case 'KMF':
			case 'KRW':
				$order_total = absint( $order_total );

				break;

			default:
				$order_total = round( $order_total, 2 ) * 100;

				break;
		}

		return $order_total;
	}

	/**
	 * Process the refund( Updated ).
	 *
	 * @since 3.2.0
	 * @param int    $order_id order_id.
	 * @param int    $amount amount.
	 * @param string $reason reason.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		$this->error_message = '';

		if ( ! empty( $order_id ) ) {

			$order = wc_get_order( $order_id );

			// Fetching charge id to create refund for that.( before SCA 3.2.0 ).
			$charge_id = get_post_meta( $order_id, 'wps_wocuf_pro_stripe_charge_id', true );

			// After SCA.
			$upsell_intent_id = get_post_meta( $order_id, '_wps_wocuf_pro_stripe_upsell_intent', true );

			// After Payment Intent Api.
			if ( empty( $charge_id ) ) {

				$wps_wocuf_pro_payment_intent = get_post_meta( $order_id, '_saved_payment_intent_id', true );

				$payment_intent = \Stripe\PaymentIntent::retrieve( $wps_wocuf_pro_payment_intent );

				$charge_id      = end( $payment_intent->charges->data )->id;
				$charged_amount = end( $payment_intent->charges->data )->amount;

			}

			// Handle Amount here( in cents ).
			$match_amount = (int) $amount * 100;

			if ( $match_amount > $charged_amount && ! empty( $upsell_intent_id ) ) {

				// Amount to be charged by upsell intent.
				$upsell_payment_intent = \Stripe\PaymentIntent::retrieve( $upsell_intent_id );
				$upsell_charge_id      = end( $upsell_payment_intent->charges->data )->id;
				$upsell_charged_amount = end( $upsell_payment_intent->charges->data )->amount;
				$upsell_refund_amount  = (int) $match_amount - $charged_amount;

				$extra_charge_info = array(
					'charge'   => $upsell_charge_id,
					'amount'   => $upsell_refund_amount,
					'reason'   => 'requested_by_customer',
					'metadata' => array( 'reason' => $reason ),
				);

				$wps_wocuf_pro_upsell_refund = \Stripe\Refund::create( $extra_charge_info );

				if ( ! empty( $wps_wocuf_pro_upsell_refund->status ) && 'succeeded' === $wps_wocuf_pro_upsell_refund->status ) {

						$refund_amount = floatval( ( $wps_wocuf_pro_upsell_refund->amount ) / 100 );

						$order->add_order_note( __( 'Order Refunded for Amount ', 'one-click-upsell-funnel-for-woocommerce-pro' ) . get_woocommerce_currency_symbol() . $refund_amount );

						$amount = $amount - $refund_amount;

				} else {

					$order->add_order_note( __( 'Order Refund Failed', 'one-click-upsell-funnel-for-woocommerce-pro' ) . get_woocommerce_currency_symbol() . $refund_amount );
					$order->add_order_note( __( 'Refund ::', 'one-click-upsell-funnel-for-woocommerce-pro' ) . $upsell_refund_amount );
					return false;
				}
			}

			if ( ! empty( $charge_id ) && ! empty( $amount ) ) {

				$charge_info = array(
					'charge'   => $charge_id,
					'amount'   => $charged_amount,
					'reason'   => 'requested_by_customer',
					'metadata' => array( 'reason' => $reason ),
				);

				// Creating refund.
				$wps_wocuf_pro_create_refund = \Stripe\Refund::create( $charge_info );

				if ( ! empty( $wps_wocuf_pro_create_refund->id ) ) {

					// Retrieving refund.
					$wps_wocuf_pro_process_refund = \Stripe\Refund::retrieve( $wps_wocuf_pro_create_refund->id );

					if ( 'succeeded' === $wps_wocuf_pro_process_refund->status ) {

						$refund_amount = floatval( ( $wps_wocuf_pro_process_refund->amount ) / 100 );

						$order->add_order_note( __( 'Order Refunded for Amount ', 'one-click-upsell-funnel-for-woocommerce-pro' ) . get_woocommerce_currency_symbol() . $refund_amount );

						return true;
					} else {

						if ( ! empty( $this->error_message ) ) {

							$order->add_order_note( $this->error_message );
						}

						$order->add_order_note( __( 'Order Refund Failed', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

						return false;
					}
				} else {

					$order->add_order_note( $this->error_message );
					$order->add_order_note( __( 'Order Refund Failed', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

					return false;
				}
			}
		}
	}

	/**
	 * Captures the payment on completion of order when charge is not captured immediately.
	 *
	 * @since 3.2.0
	 * @param int $order_id Order id.
	 */
	public function process_capture( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( $order->get_payment_method() !== $this->id ) {

			return;
		}

		$already_captured = get_post_meta( $order_id, '_wps_wocuf_pro_already_captured', true );

		if ( ! empty( $already_captured ) ) {

			$order->add_order_note( __( 'The Order is already captured', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

			return;
		}

		$intent_id_to_capture_parent = get_post_meta( $order_id, '_saved_payment_intent_id', true );

		$intent_id_to_capture_upsell = get_post_meta( $order_id, '_wps_wocuf_pro_stripe_upsell_intent', true );

		// Capture Upsell!
		if ( ! empty( $intent_id_to_capture_upsell ) ) {

			$captured_intent_upsell = \Stripe\PaymentIntent::retrieve( $intent_id_to_capture_upsell );
			$captured_intent_upsell->capture();

			if ( ! empty( $captured_intent_upsell ) && 'succeeded' === $captured_intent_upsell->status ) {

				$captured_amount = $captured_intent_upsell->amount / 100;

				$order->add_order_note( 'Upsell Payment successfully captured : ' . get_woocommerce_currency_symbol() . $captured_amount, 'one-click-upsell-funnel-for-woocommerce-pro' );

			} else {

				$order->add_order_note( 'Upsell Payment capture error : ' . $captured_intent_upsell->last_payment_error, 'one-click-upsell-funnel-for-woocommerce-pro' );
				return;
			}
		}

		if ( empty( $intent_id_to_capture_parent ) ) {

			$order->add_order_note( __( 'No Intent Id found.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

			return;
		}

		$captured_intent_parent = \Stripe\PaymentIntent::retrieve( $intent_id_to_capture_parent );
		$captured_intent_parent->capture();

		if ( ! empty( $captured_intent_parent ) && 'succeeded' === $captured_intent_parent->status ) {

			// Means amount captured successfully!
			$captured_amount = $captured_intent_parent->amount / 100;
			$order->payment_complete();
			$order->add_order_note( 'Payment successfully captured : ' . get_woocommerce_currency_symbol() . $captured_amount, 'one-click-upsell-funnel-for-woocommerce-pro' );

			return array(
				'result' => 'success',
			);

		} else {

			$order->add_order_note( $captured_intent_parent->last_payment_error, 'one-click-upsell-funnel-for-woocommerce-pro' );
			return;
		}
	}

	/**
	 * Writes error or response to the logs file.
	 *
	 * @since 3.2.0
	 * @param int $order_id         Main Order Id.
	 * @param int $step             Running Function.
	 * @param int $message          Message.
	 * @param int $final_response   Api Response.
	 */
	public function wps_wocuf_pro_create_stripe_log( $order_id, $step, $message, $final_response ) {

		if ( 'yes' === $this->logging ) {

			if ( ! defined( 'WC_LOG_DIR' ) ) {

				return;
			}

			$log_dir = WC_LOG_DIR;

			// As sometimes when dir is not present, and fopen cannot create directories.
			if ( ! is_dir( $log_dir ) ) {

				mkdir( $log_dir, 0755, true );
			}

			if ( ! is_dir( $log_dir ) ) {

				mkdir( $log_dir, 0755, true );
			}

			$log_dir_file = $log_dir . 'woocommerce-one-click-upsell-funnel-stripe-' . gmdate( 'j-F-Y' ) . '.log';

			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php'; // Since we are using the filesystem outside wp-admin.
			}

			global $wp_filesystem;  // Define global object of WordPress filesystem.
			WP_Filesystem();        // Intialise new file system object.

			if ( file_exists( $log_dir_file ) ) {
				$file_data = $wp_filesystem->get_contents( $log_dir_file );
			} else {
				$file_data = '';
			}

			$server = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			$log    = 'Website: ' . $server . PHP_EOL .
					'Time: ' . current_time( 'F j, Y  g:i a' ) . PHP_EOL .
					'Order ID : ' . $order_id . PHP_EOL .
					'Step: ' . $step . PHP_EOL .
					'Process: ' . $message . PHP_EOL .
					'Response: ' . print_r( $final_response, true ) . PHP_EOL . //phpcs:ignore.
					'----------------------------------------------------------------------------' . PHP_EOL;

			$file_data .= $log;
			$wp_filesystem->put_contents( $log_dir_file, $file_data );

		}
	}

	/**
	 * Attached to `woocommerce_payment_successful_result` with a late priority,
	 * this method will combine the "naturally" generated redirect URL from
	 * WooCommerce and a payment intent secret into a hash, which contains both
	 * the secret, and a proper URL, which will confirm whether the intent succeeded.
	 *
	 * @since 3.2.0
	 * @param array $result   The result from `process_payment`.
	 * @param int   $order_id The ID of the order which is being paid for.
	 * @return array
	 */
	public function wps_wocuf_pro_intent_redirect( $result, $order_id ) {

		// Only redirects with intents need to be modified.
		if ( ! isset( $result['intent_secret'] ) ) {

			return $result;
		}

		$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Intent Redirection Start', 'Success', $result );

		// Put the final thank you page redirect into the verification URL.
		$verification_url = add_query_arg(
			array(
				'order'       => $order_id,
				'nonce'       => wp_create_nonce( 'wps_wocuf_pro_confirm_nonce' ),
				'redirect_to' => rawurlencode( $result['redirect'] ),
			),
			WC_AJAX::get_endpoint( 'wps_wocuf_pro_verify_intent' )
		);

		// Combine into a hash.
		$redirect = sprintf( '#confirm-pi-%s:%s', $result['intent_secret'], rawurlencode( $verification_url ) );

		return array(
			'result'   => 'success',
			'redirect' => $redirect,
		);
	}

	/**
	 * Attempt to manually complete the payment process for orders, which are still pending
	 * before displaying the View Order page. This is useful in case webhooks have not been
	 * set up.
	 *
	 * @since 3.2.0
	 * @param int $order_id The ID that will be used for the thank you page.
	 */
	public function check_intent_status_on_order_page( $order_id ) {

		if ( empty( $order_id ) || absint( $order_id ) <= 0 ) {
			return;
		}

		$order = wc_get_order( absint( $order_id ) );
		$this->verify_intent_after_checkout( $order );
	}

	/**
	 * Executed between the "Checkout" and "Thank you" pages, this
	 * method updates orders based on the status of associated PaymentIntents.
	 *
	 * @since 3.2.0
	 * @param object $order The order which is in a transitional state.
	 * @param bool   $payment_complete The order which is in a transitional state.
	 */
	public function verify_intent_after_checkout( $order, $payment_complete = true ) {

		$payment_method = $order->get_payment_method();

		if ( $payment_method !== $this->id ) {

			// If this is not the payment method, an intent would not be available.
			return;
		}

		$intent = $this->get_intent_from_order( $order );

		if ( ! $intent ) {

			// No intent, redirect to the order received page for further actions.
			return;
		}

		$order_id = $order->get_id();

		clean_post_cache( $order_id );
		$order = wc_get_order( $order_id );

		if ( 'pending' !== $order->get_status() && 'failed' !== $order->get_status() ) {

			// If payment has already been completed, this function is redundant.
			return;
		}

		if ( 'succeeded' === $intent->status || 'requires_capture' === $intent->status ) {

			// Proceed with the payment completion.
			if ( 'succeeded' === $intent->status ) {

				if ( $payment_complete ) {

					$order->payment_complete( $intent->id );
				}

				$msg = sprintf(
					/* translators: %s: intent id */
					esc_html__( 'Stripe charge complete ( Payment Intent ID: %s )', 'one-click-upsell-funnel-for-woocommerce-pro' ),
					$intent->id
				);

				$order->add_order_note( $msg );
				$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Stripe charge', 'complete', $intent );
			}

			// Manual Capture.
			if ( 'requires_capture' === $intent->status ) {

				// Payment Authorized and ready to capture.
				$msg = sprintf(
					/* translators: %s: intent id */
					esc_html__( 'Stripe Payment Authorized ( Payment Intent ID: %s )', 'one-click-upsell-funnel-for-woocommerce-pro' ),
					$intent->id
				);

				$order->add_order_note( $msg );
				$order->update_status( 'on-hold' );

				update_post_meta( $order_id, '_wps_wocuf_pro_capture_type', 'manual' );
				$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Stripe charge', 'Manual', $intent );
			}
		} elseif ( 'requires_payment_method' === $intent->status || 'requires_source' === $intent->status ) {

			// `requires_payment_method` means that SCA got denied for the current payment method.

			$status_message = ( $intent->last_payment_error )
			/* translators: %s: intent id */
				? sprintf( __( 'Stripe SCA authentication failed. Reason: %s', 'one-click-upsell-funnel-for-woocommerce-pro' ), $intent->last_payment_error->message )
				: __( 'Stripe SCA authentication failed.', 'one-click-upsell-funnel-for-woocommerce-pro' );

			$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Stripe charge', 'Stripe SCA authentication failed.', $intent );

			$order->update_status( 'failed', $status_message );
		}
	}

	/**
	 * Attempt to retreive the payment intent for verification.
	 *
	 * @since 3.2.0
	 * @param int $order The order that will be used for verification.
	 */
	public function get_intent_from_order( $order ) {

		$wps_wocuf_pro_payment_intent = get_post_meta( $order->get_id(), '_saved_payment_intent_id', true );

		$wps_wocuf_pro_stripe_create_intent = \Stripe\PaymentIntent::retrieve( $wps_wocuf_pro_payment_intent );
		return $wps_wocuf_pro_stripe_create_intent;
	}

	/**
	 * Process upsell order for no amount.
	 *
	 * @since 3.2.0
	 * @param int $amount The order that will be used for processing.
	 * @param int $order The order total that will be charged for payment.
	 */
	public function process_zero_total_order( $amount, $order ) {

		// If order amount is zero return with success.
		if ( empty( $amount ) || $amount < 0 ) {

			if ( wps_upsell_order_contains_subscription( $order->get_id() ) && wps_upsell_pg_supports_subs( $order->get_id() ) ) {

				// If upsell parent order from stripe then activate subscriptions.
				WC_Subscriptions_Manager::activate_subscriptions_for_order( $order );
			}

			$set_status = $order->needs_processing() ? 'processing' : 'completed';
			$order->update_status( $set_status );
			$order->payment_complete();
			return array(
				'message'  => 'true',
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}

	/**
	 * Handle Subscriptions to Stripe SCA gateway.
	 *
	 * @since 3.2.0
	 * @param int $order                    Main Order Object.
	 * @param int $stripe_info              Stripe data.
	 */
	public function charge_subs_before_sca( $order, $stripe_info ) {

		$wps_wocuf_pro_stripe_card  = ! empty( $stripe_info['token'] ) ? $stripe_info['token'] : '';
		$wps_wocuf_pro_stripe_name  = ! empty( $stripe_info['stripe_name'] ) ? $stripe_info['stripe_name'] : '';
		$wps_wocuf_pro_cus_id['id'] = ! empty( $stripe_info['stripe_cus_id'] ) ? $stripe_info['stripe_cus_id'] : '';

		$charge_description = sprintf(
			/* translators: %s: decimal */
			__( '%1$s paid for order : %2$s', 'one-click-upsell-funnel-for-woocommerce-pro' ),
			$order->get_billing_email(),
			$order->get_order_number()
		);

		$order_id = $order->get_id();

		$charge_info = array(

			'amount'               => $this->wps_wocuf_pro_stripe_amount( $order->get_total() ),
			'currency'             => get_woocommerce_currency(),
			'customer'             => $wps_wocuf_pro_cus_id['id'],
			'capture'              => true,
			'statement_descriptor' => $this->gateway_description,
			'description'          => $charge_description,
			'metadata'             => array(
				'order_id'       => $order_id,
				'customer_email' => $order->get_billing_email(),
				'customer_name'  => $wps_wocuf_pro_stripe_name,
			),
		);

		// Creating Charge.
		$wps_wocuf_pro_stripe_charge = $this->stripe_api_callback( '\Stripe\Charge', 'create', $charge_info, 'process_payment', $order_id );

		if ( isset( $wps_wocuf_pro_stripe_charge->id ) ) {

			$this->wps_wocuf_pro_create_stripe_log( $order_id, 'charge_subs_before_sca', 'Creating charge for tokens', $wps_wocuf_pro_stripe_charge );

			update_post_meta( $order_id, 'wps_wocuf_pro_stripe_charge_id', $wps_wocuf_pro_stripe_charge->id );

			$order->payment_complete();

			$order->add_order_note( __( 'Renewal Payment completed.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

		} else {

			$order->add_order_note( __( 'Renewal Payment Cancelled due to unauthorized payment method.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

			$this->wps_wocuf_pro_create_stripe_log( $order_id, 'charge_subs_before_sca', 'Creating charge for tokens - Failed', $wps_wocuf_pro_stripe_charge );

			$order->update_status( 'failed' );
		}
	}

	/**
	 * Setup Future Intent for zero order, will be used in renewal payments.
	 *
	 * @since 3.2.0
	 * @param int $customer_id                  Customer Id.
	 * @param int $payment_method_id            Payment method id.
	 */
	public function setup_future_intent( $customer_id = '', $payment_method_id = '' ) {

		/**
		 * This case is for handling setup intents.
		 */
		$setup_intent = \Stripe\SetupIntent::create(
			array(
				'payment_method_types' => array( 'card' ),
				'payment_method'       => $payment_method_id,
				'customer'             => $customer_id,
				'confirm'              => true,
				'usage'                => 'off_session',
			)
		);

		return $setup_intent;
	}

	// End of class.
}
?>
