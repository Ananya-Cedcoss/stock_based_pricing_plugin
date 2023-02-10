<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link  https://wpswings.com/
 * @since 1.0.0
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/public
 * @author     wpswings <webmaster@wpswings.com>
 */
class Woocommerce_One_Click_Upsell_Funnel_Pro_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_One_Click_Upsell_Funnel_Pro_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_One_Click_Upsell_Funnel_Pro_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-one-click-upsell-funnel-pro-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'wps-upsell-caraousel-slick-css', plugin_dir_url( __FILE__ ) . 'css/slick.min.css', array(), $this->version, 'all' );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'wps-upsell-sweet-alert-js', plugin_dir_url( __FILE__ ) . 'js/sweet-alert.js', array(), '2.1.2', true );

		wp_enqueue_script( 'woocommerce-one-click-upsell-public-script', plugin_dir_url( __FILE__ ) . 'js/woocommerce-one-click-upsell-funnel-public.js', array( 'jquery' ), $this->version, true );

		$show_upsell_loader = false;

		// Add Upsell loader only when Live Offer or admin view.
		if ( $this->validate_shortcode() ) {

			$show_upsell_loader    = true;
			$upsell_global_options = get_option( 'wps_upsell_global_options', array() );
			$upsell_loader_message = ! empty( $upsell_global_options['upsell_actions_message'] ) ? sanitize_text_field( $upsell_global_options['upsell_actions_message'] ) : '';
		}

		wp_localize_script(
			'woocommerce-one-click-upsell-public-script',
			'wps_upsell_public',
			array(
				'ajaxurl'                => admin_url( 'admin-ajax.php' ),
				'nonce'                  => wp_create_nonce( 'wps_wocuf_nonce' ),
				'alert_preview_title'    => esc_html__( 'One Click Upsell', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'alert_preview_content'  => esc_html__( 'This is Preview Mode, please checkout to see Live Offers.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'show_upsell_loader'     => $show_upsell_loader,
				'upsell_actions_message' => ! empty( $show_upsell_loader ) ? $upsell_loader_message : '',
			)
		);

		// Caraousel Slider.
		wp_enqueue_script( 'wps-upsell-caraousel-slider-js', plugin_dir_url( __FILE__ ) . 'js/caraousel-slider.js', array(), '2.1.2', true );
		wp_enqueue_script( 'wps-upsell-caraousel-slick-js', plugin_dir_url( __FILE__ ) . 'js/slick.min.js', array(), '2.1.2', true );

	}

	/**
	 * Initiate Upsell Orders before processing payment.
	 *
	 * @param int $order_id order id.
	 *
	 * @since 1.0.0
	 *
	 * @throws Exception Throws exception when error.
	 */
	public function wps_wocuf_initate_upsell_orders( $order_id ) {

		// Validate if correct hook.
		if ( empty( $_GET['wc-ajax'] ) || 'checkout' !== $_GET['wc-ajax'] ) {
			return;
		}

		$order = new WC_Order( $order_id );

		$payment_method = $order->get_payment_method();

		$supported_gateways = wps_upsell_supported_gateways();

		if ( empty( $payment_method ) ) {

			// Free Order upsell payment method is ''.
			// Check for payment method is preconfgured or not.
			$wps_upsell_global_settings = get_option( 'wps_upsell_global_options', array() );
			$free_order_enabled         = ! empty( $wps_upsell_global_settings['enable_free_upsell'] ) ? $wps_upsell_global_settings['enable_free_upsell'] : 'no';
			$free_order_enabled_gateway = ! empty( $wps_upsell_global_settings['free_upsell_select'] ) ? $wps_upsell_global_settings['free_upsell_select'] : '';
			$gateway_holder             = new WC_Payment_Gateways();
			$all_gateways               = $gateway_holder->get_available_payment_gateways();

			if ( 'on' !== $free_order_enabled || empty( $free_order_enabled_gateway ) || empty( $all_gateways[ $free_order_enabled_gateway ] ) ) {
				return;
			}
		}

		// remove upsell products and process upsell if same order is processed earlier.
		if ( $this->expire_further_offers( $order_id ) ) {
			$this->upsell_products_removal( $order_id );
		}

		if ( in_array( $payment_method, $supported_gateways, true ) ) {

			$wps_wocuf_pro_all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );

			$wps_wocuf_pro_flag = 0;

			$wps_wocuf_pro_proceed = false;

			if ( empty( $wps_wocuf_pro_all_funnels ) ) {
				return;
			} elseif ( empty( $order ) ) {
				return;
			}

			$funnel_redirect = false;

			if ( ! empty( $order ) ) {

				$wps_wocuf_pro_placed_order_items = $order->get_items();

				$ocuf_ok = $order->get_order_key();

				$ocuf_ofd = 0;

				if ( is_array( $wps_wocuf_pro_all_funnels ) ) {

					// Move Global Funnels at the last of the array while maintaining it's key, so they execute at last.
					foreach ( $wps_wocuf_pro_all_funnels as $funnel_key => $single_funnel_array ) {

						$global_funnel = ! empty( $single_funnel_array['wps_wocuf_global_funnel'] ) ? $single_funnel_array['wps_wocuf_global_funnel'] : '';

						// Check if global funnel.
						if ( 'yes' === $global_funnel ) {

							// Unset.
							unset( $wps_wocuf_pro_all_funnels[ $funnel_key ] );

							// Append at last with the same key.
							$wps_wocuf_pro_all_funnels[ $funnel_key ] = $single_funnel_array;
						}
					}

					// Main Foreach for Triggering Upsell Offers.
					foreach ( $wps_wocuf_pro_all_funnels as $wps_wocuf_pro_single_funnel => $wps_wocuf_pro_funnel_data ) {

						$is_global_funnel = ! empty( $wps_wocuf_pro_funnel_data['wps_wocuf_global_funnel'] ) && 'yes' === $wps_wocuf_pro_funnel_data['wps_wocuf_global_funnel'] ? $wps_wocuf_pro_funnel_data['wps_wocuf_global_funnel'] : false;

						// Check if current funnel is saved after version 3.0.0.
						$funnel_saved_after_version_3 = ! empty( $wps_wocuf_pro_funnel_data['wps_upsell_fsav3'] ) ? $wps_wocuf_pro_funnel_data['wps_upsell_fsav3'] : '';

						// For funnels saved after version 3.0.0.
						if ( 'true' === $funnel_saved_after_version_3 ) {

							// Check if funnel is live or not.
							$funnel_status = ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_upsell_funnel_status'] ) ? $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_upsell_funnel_status'] : '';

							/**
							 * For Admin Funnel Will trigger for both Live and Sandbox statuses.
							 *
							 * Since v3.5.0
							 */
							if ( ! current_user_can( 'administrator' ) ) {

								if ( 'yes' !== $funnel_status ) {

									// Continue to next funnel.
									continue;
								}
							}
						}

						// Condition to stop funnel if minimum cart amount is not satisfied.
						if ( ( ! empty( $wps_wocuf_pro_funnel_data['wps_wocuf_pro_funnel_cart_amount'] ) ) && $wps_wocuf_pro_funnel_data['wps_wocuf_pro_funnel_cart_amount'] > WC()->cart->cart_contents_total ) {
							continue;
						}

						/**
						 * Check for funnel schedule.
						 * Since v3.5.0 convert data into array first.
						 */
						$wps_wocuf_pro_funnel_schedule = ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_funnel_schedule'] ) ? $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_funnel_schedule'] : array( '7' );

						if ( '0' === $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_funnel_schedule'] ) {

							$wps_wocuf_pro_funnel_schedule = array( '0' );
						} elseif ( ! is_array( $wps_wocuf_pro_funnel_schedule ) ) {

							$wps_wocuf_pro_funnel_schedule = array( $wps_wocuf_pro_funnel_schedule );
						}

						// In order to use server time only.
						$current_schedule = (string) gmdate( 'w' );

						if ( in_array( '7', $wps_wocuf_pro_funnel_schedule, true ) ) {

							$wps_wocuf_pro_proceed = true;

						} elseif ( in_array( $current_schedule, $wps_wocuf_pro_funnel_schedule, true ) ) {

							$wps_wocuf_pro_proceed = true;
						}

						if ( false === $wps_wocuf_pro_proceed ) {

							// Continue to next funnel.
							continue;
						}

						$wps_wocuf_pro_funnel_target_products = ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_target_pro_ids'] ) ? $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_target_pro_ids'] : array();

						$funnel_target_product_categories = ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['target_categories_ids'] ) ? $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['target_categories_ids'] : array();

						$wps_wocuf_pro_existing_offers = ! empty( $wps_wocuf_pro_funnel_data['wps_wocuf_pro_applied_offer_number'] ) ? $wps_wocuf_pro_funnel_data['wps_wocuf_pro_applied_offer_number'] : array();

						// To get the first offer from current funnel.
						if ( count( $wps_wocuf_pro_existing_offers ) ) {

							foreach ( $wps_wocuf_pro_existing_offers as $key => $value ) {

								$ocuf_ofd = $key;
								break;
							}
						}

						if ( is_array( $wps_wocuf_pro_placed_order_items ) && count( $wps_wocuf_pro_placed_order_items ) ) {
							foreach ( $wps_wocuf_pro_placed_order_items as $target_item_key => $wps_wocuf_pro_single_item ) {
								$wps_wocuf_pro_variation_id = $wps_wocuf_pro_single_item->get_variation_id();

								$wps_wocuf_pro_product_id = $wps_wocuf_pro_single_item->get_product_id();

								$product_categories = array();

								if ( ! empty( $funnel_target_product_categories ) ) {

									$product_cat_obj_array = get_the_terms( $wps_wocuf_pro_product_id, 'product_cat' );

									if ( ! empty( $product_cat_obj_array ) && is_array( $product_cat_obj_array ) && count( $product_cat_obj_array ) ) {

										foreach ( $product_cat_obj_array as $product_cat_obj ) {

											if ( ! empty( $product_cat_obj->term_id ) ) {

												$product_categories[] = $product_cat_obj->term_id;
											}
										}
									}
								}

								if ( in_array( (string) $wps_wocuf_pro_product_id, $wps_wocuf_pro_funnel_target_products, true ) || ( ! empty( $wps_wocuf_pro_variation_id ) && in_array( (string) $wps_wocuf_pro_variation_id, $wps_wocuf_pro_funnel_target_products, true ) ) || ( ! empty( $product_categories ) && ! empty( array_intersect( $product_categories, $funnel_target_product_categories ) ) ) || ( $is_global_funnel ) ) {

									// Array of offers with product id.
									if ( ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_products_in_offer'] ) && is_array( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_products_in_offer'] ) ) {

										/**
										 * Set funnel as shown if is exclusive offer funnel.
										 * Do it just after checking target.
										 * Exclusive Offer.
										 */
										if ( ! empty( $wps_wocuf_pro_funnel_data['wps_wocuf_exclusive_offer'] ) && 'yes' === $wps_wocuf_pro_funnel_data['wps_wocuf_exclusive_offer'] ) {

											$offer_already_shown_to_users = ! empty( $wps_wocuf_pro_funnel_data['offer_already_shown_to_users'] ) ? $wps_wocuf_pro_funnel_data['offer_already_shown_to_users'] : array();

											$current_customer = ! empty( $order ) ? $order->get_billing_email() : '';

											if ( ! empty( $current_customer ) && ! empty( $offer_already_shown_to_users ) && in_array( $current_customer, $offer_already_shown_to_users, true ) ) {

												// Skip to next funnel.
												break;
											}

											// Not skipped. Mark as shown to this customer.
											array_push( $offer_already_shown_to_users, $current_customer );
											$wps_wocuf_pro_funnel_data['offer_already_shown_to_users'] = $offer_already_shown_to_users;

											$wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ] = $wps_wocuf_pro_funnel_data;

											// Sort Funnels before saving.
											$sorted_upsell_funnels = $wps_wocuf_pro_all_funnels;

											ksort( $sorted_upsell_funnels );

											update_option( 'wps_wocuf_pro_funnels_list', $sorted_upsell_funnels );
										}

										// To skip funnel if any funnel offer product is already present during checkout ( Order Items ) - Default : Yes.
										$wps_upsell_global_settings = get_option( 'wps_upsell_global_options', array() );

										$skip_similar_offer = ! empty( $wps_upsell_global_settings['skip_similar_offer'] ) ? $wps_upsell_global_settings['skip_similar_offer'] : 'yes';

										if ( 'yes' === $skip_similar_offer ) {

											$offer_product_in_cart = false;

											foreach ( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_products_in_offer'] as $product_in_funnel_id_array ) {

												if ( ! empty( $product_in_funnel_id_array ) ) {

													// In v2.0.0, it was array so handling accordingly.
													if ( is_array( $product_in_funnel_id_array ) && count( $product_in_funnel_id_array ) ) {

														foreach ( $product_in_funnel_id_array as $product_in_funnel_id ) {

															foreach ( $wps_wocuf_pro_placed_order_items as $item_key => $wps_wocuf_pro_single_item ) {

																/**
																 * Get get_product()->get_id() will return actual id, no need to call
																 * get_variation_id() separately.
																 */
																if ( (int) $wps_wocuf_pro_single_item->get_product()->get_id() === absint( $product_in_funnel_id ) ) {

																	$offer_product_in_cart = true;
																	break 3;

																}
															}
														}
													} else {

														foreach ( $wps_wocuf_pro_placed_order_items as $item_key => $wps_wocuf_pro_single_item ) {

															$product_in_funnel_offer = wc_get_product( $product_in_funnel_id_array );

															// If offer product is variable product then match all variations.
															if ( 'variable' === $product_in_funnel_offer->get_type() ) {

																$product_in_funnel_offer_variations = $product_in_funnel_offer->get_children();

																if ( ! empty( $product_in_funnel_offer_variations ) && is_array( $product_in_funnel_offer_variations ) ) {

																	foreach ( $product_in_funnel_offer_variations as $product_in_funnel_offer_variation_id ) {

																		if ( (int) $wps_wocuf_pro_single_item->get_product()->get_id() === (int) $product_in_funnel_offer_variation_id ) {

																			$offer_product_in_cart = true;
																			break 3;

																		}
																	}
																}
															} else { // if not variable then match with the offer product id.

																/**
																 * Get get_product()->get_id() will return actual id, no need to call
																 * get_variation_id() separately.
																 */
																if ( (int) $wps_wocuf_pro_single_item->get_product()->get_id() === absint( $product_in_funnel_id_array ) ) {

																	$offer_product_in_cart = true;
																	break 2;

																}
															}
														}
													}
												}
											}

											if ( true === $offer_product_in_cart ) {

												break;
											}
										}

										// To skip funnel if any offer product is already purchased in previous orders - Default : No.
										$wps_wocuf_pro_enable_smart_skip = ! empty( $wps_upsell_global_settings['wps_wocuf_pro_enable_smart_skip'] ) ? $wps_upsell_global_settings['wps_wocuf_pro_enable_smart_skip'] : '';

										if ( is_user_logged_in() && ! empty( $wps_wocuf_pro_enable_smart_skip ) && 'on' === $wps_wocuf_pro_enable_smart_skip ) {

											// Handle the case here.
											if ( ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_products_in_offer'] ) && is_array( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_products_in_offer'] ) ) {

												$is_already_purchased = false;

												foreach ( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_products_in_offer'] as $key => $single_offer_id ) {

													$is_already_purchased = $this->wps_wocuf_pro_skip_for_pre_order( $single_offer_id );

													if ( true === $is_already_purchased ) {

														break;
													}
												}
											}

											// If already purchased, skip.
											if ( true === $is_already_purchased ) {

												break;
											}
										}

										$product_in_funnel_stock_out = false;

										foreach ( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_products_in_offer'] as $product_in_funnel_id_array ) {

											if ( ! empty( $product_in_funnel_id_array ) ) {

												// In v2.0.0, it was array so handling accordingly.
												if ( is_array( $product_in_funnel_id_array ) && count( $product_in_funnel_id_array ) ) {

													foreach ( $product_in_funnel_id_array as $product_in_funnel_id ) {

														$product_in_funnel = wc_get_product( $product_in_funnel_id );

														if ( ! $product_in_funnel->is_in_stock() ) {

															$product_in_funnel_stock_out = true;
															break 2;
														}
													}
												} else {

													$product_in_funnel = wc_get_product( $product_in_funnel_id_array );

													if ( ! $product_in_funnel->is_in_stock() ) {

														$product_in_funnel_stock_out = true;
														break;
													}
												}
											}
										}

										if ( true === $product_in_funnel_stock_out ) {

											break;
										}
									}

									/**
									 * Since v3.5.0.
									 * Smart Offer Upgrade.
									 * At this phase, the offer product is purchaseble completely.
									 * Better to save the target item key up here.
									 * ( Will not work for Global Funnel )
									 */

									if ( ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_smart_offer_upgrade'] ) && 'yes' === $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_smart_offer_upgrade'] && ! $is_global_funnel ) {

										update_post_meta( $order_id, '_wps_wocufpro_replace_target', $target_item_key );
									}

									// $ocuf_ofd is first offer id in funnel, check if product id is set in it.
									if ( ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_products_in_offer'][ $ocuf_ofd ] ) ) {

										$funnel_offer_post_id_assigned = ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_upsell_post_id_assigned'][ $ocuf_ofd ] ) ? $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_upsell_post_id_assigned'][ $ocuf_ofd ] : '';

										// When funnel is saved after v3.0.0 and offer post id is assigned and elementor active.
										if ( ! empty( $funnel_offer_post_id_assigned ) && 'true' === $funnel_saved_after_version_3 && wps_upsell_elementor_plugin_active() ) {

											$redirect_to_upsell = false;

											$offer_template = ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_offer_template'][ $ocuf_ofd ] ) ? $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_offer_template'][ $ocuf_ofd ] : '';

											// When template is set to custom.
											if ( 'custom' === $offer_template ) {

												$custom_offer_page_url = ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_offer_custom_page_url'][ $ocuf_ofd ] ) ? $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_offer_custom_page_url'][ $ocuf_ofd ] : '';

												if ( ! empty( $custom_offer_page_url ) ) {

													$redirect_to_upsell = true;
													$redirect_to_url    = $custom_offer_page_url;
												}
											} elseif ( ! empty( $offer_template ) ) { // When template is set to one, two or three.

												$offer_assigned_post_id = ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_upsell_post_id_assigned'][ $ocuf_ofd ] ) ? $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_upsell_post_id_assigned'][ $ocuf_ofd ] : '';

												if ( ! empty( $offer_assigned_post_id ) && 'publish' === get_post_status( $offer_assigned_post_id ) ) {

													$redirect_to_upsell = true;
													$redirect_to_url    = get_page_link( $offer_assigned_post_id );
												}
											}

											if ( true === $redirect_to_upsell ) {

												$funnel_redirect = true;

												$wps_wocuf_pro_nonce = wp_create_nonce( 'funnel_offers' );

												$result = add_query_arg(
													array(
														'ocuf_ns'  => $wps_wocuf_pro_nonce,
														'ocuf_fid' => $wps_wocuf_pro_single_funnel,
														'ocuf_ok'  => $ocuf_ok,
														'ocuf_ofd' => $ocuf_ofd,
													),
													$redirect_to_url
												);

												$wps_wocuf_pro_flag = 1;

												// Break from placed order items loop with both funnel redirect and pro flag as true.
												break;
											}
										} else { // When funnel is saved before v3.0.0.

											$wps_wocuf_pro_offer_page_id = get_option( 'wps_wocuf_pro_funnel_default_offer_page', '' );

											if ( isset( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_offer_custom_page_url'][ $ocuf_ofd ] ) && ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_offer_custom_page_url'][ $ocuf_ofd ] ) ) {
												$redirect_to_url = $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_offer_custom_page_url'][ $ocuf_ofd ];
											} elseif ( ! empty( $wps_wocuf_pro_offer_page_id ) && 'publish' === get_post_status( $wps_wocuf_pro_offer_page_id ) ) {
												$redirect_to_url = get_page_link( $wps_wocuf_pro_offer_page_id );
											} else {

												// Break from placed order items loop and move to next funnel.
												break;
											}

											$funnel_redirect = true;

											$wps_wocuf_pro_nonce = wp_create_nonce( 'funnel_offers' );

											$result = add_query_arg(
												array(
													'ocuf_ns'  => $wps_wocuf_pro_nonce,
													'ocuf_fid' => $wps_wocuf_pro_single_funnel,
													'ocuf_ok'  => $ocuf_ok,
													'ocuf_ofd' => $ocuf_ofd,
												),
												$redirect_to_url
											);

											$wps_wocuf_pro_flag = 1;

											// Break from placed order items loop with both funnel redirect and pro flag as true.
											break;
										}
									}
								}
							}
						}

						if ( 1 === $wps_wocuf_pro_flag ) {

							// Break from 'all funnels' loop.
							break;
						}
					}
				}

				if ( $funnel_redirect ) {

					if ( ! empty( $_POST ) ) {
						update_post_meta( $order_id, 'mwb_upsell_payment_data_post', $_POST );   // phpcs:ignore.
						$funnel_redirect = true;
					}

					$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

					/**===============================================
					 * Official/Custom Stripe payment method handling.
					===============================================*/
					if ( 'stripe' === $payment_method ||'stripe_cc' === $payment_method  ) {

						/**
						 * Use process function of official stripe and if succesfull, then show and add.
						 * upsell products afterwards normally.
						 * When done with adding process the payment for dummy order with same products
						 * and process payment via same source.
						 * On failed payment remove orders.
						 */
						if ( empty( $available_gateways[ $payment_method ] ) ) {

							wc_clear_notices();
							throw new Exception( __( 'Error processing checkout. Please try again with another payment method.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
						} else {

							// Process Subscriptions for pre upsell products from Order.
							if ( wps_upsell_order_contains_subscription( $order_id ) && wps_upsell_pg_supports_subs( $order_id ) ) {

								wps_upsell_create_subscriptions_for_order( $order_id, $order );
							}

							/**
							 * Process WPS Subscriptions for pre upsell products from Order.
							 */
							if ( class_exists( 'Subscriptions_For_Woocommerce_Compatiblity' ) && true === Subscriptions_For_Woocommerce_Compatiblity::pg_supports_subs( $order_id ) && true === Subscriptions_For_Woocommerce_Compatiblity::order_contains_subscription( $order_id ) ) {

								$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
								$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

								if ( ! $id_nonce_verified ) {
									wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								}

								$compat_class = new Subscriptions_For_Woocommerce_Compatiblity( 'Subscriptions_For_Woocommerce_Compatiblity', '1.0.0' );
								$compat_class->create_subscriptions_for_order( $order_id, $_POST );
							}

							// Official Stripe is available to work.
							$stripe_class = $available_gateways[ $payment_method ];

							$payment_result = $stripe_class->process_payment( $order_id, false, true );
                            
                            
							$is_3d_supported = false;
							$order          = wc_get_order( $order_id );
							$payment_result_order = get_post_meta($order_id ,'_payment_intent',true);
							if ( ! empty ( $payment_result_order )) {
								if ( ! empty( $payment_result_order['payment_method'] ) ) {
									if ( ! empty(  $payment_result_order['payment_method']['card'] ) ) {
										if ( ! empty( $payment_result_order['payment_method']['card']['three_d_secure_usage'] ) ) {
											$is_3d_supported = $payment_result_order['payment_method']['card']['three_d_secure_usage']['supported'];
										}

									}

								}

							}
							
                            
							if ( $is_3d_supported ) {
								return;
							}
                            
                            if ( 'failed' === $order->get_status() ) {

								/**
								 * Initial order is failed.
								 * Delete the subscriptions if made.
								 */
								if ( wps_upsell_order_contains_subscription( $order_id ) && wps_upsell_pg_supports_subs( $order_id ) ) {

									WC_Subscriptions_Manager::maybe_delete_subscription( $order_id );
								}

								if ( class_exists( 'Subscriptions_For_Woocommerce_Compatiblity' ) && true === Subscriptions_For_Woocommerce_Compatiblity::order_contains_subscription( $order_id ) ) {

									/*delete failed order subscription*/
									wps_sfw_delete_failed_subscription( $order_id );
								}

								wc_clear_notices();
								throw new Exception( __( 'Sorry, we are unable to process your payment at this time. Please retry later.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

							} elseif ( 'success' === $payment_result['result'] && ! empty( $payment_result['payment_intent_secret'] ) ) {

								// The intent needs verification here. Upsell will not work.
								return $payment_result;

							} elseif ( 'success' === $payment_result['result'] ) {

								update_post_meta( $order_id, '_wps_wocuf_stripe_parent_paid', true );

								// Process Subscriptions for pre upsell products from Order.
								if ( wps_upsell_order_contains_subscription( $order_id ) && wps_upsell_pg_supports_subs( $order_id ) ) {

									// If upsell parent order is paid then activate subscriptions.
									WC_Subscriptions_Manager::activate_subscriptions_for_order( $order );
								}

								$order->update_status( 'upsell-parent' );

								$this->initial_redirection_to_upsell_offer_and_triggers( $order_id, $wps_wocuf_pro_single_funnel, $result );
							}
						}
					} elseif ( 'wps-wocuf-pro-stripe-gateway' === $payment_method ) {

						/**
						 * Creating Payment Intent. If any there's any error, exception will be thrown.
						 * stopping further execution and notice will be shown ( via try catch
						 * of class-wc-checkout.php ).
						 *
						 * Thus this will validate card details and only redirect to funnel offer
						 * when there is no error.
						 *
						 * And now also testing customer create, update and retrieve so that so that
						 * wrong cvv and other errors are displayed on checkout page without being redirected
						 * to offers.
						 *
						 * The above update is imprtant because without it the error was identified after
						 * offers and shown on thankyou page and in latest woocommerce 3.5.1 even that
						 * notice was not showing.
						 * So payment failed and user was on thankyou page so it was very important to handle.
						 */

						$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
						$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );
						if ( ! $id_nonce_verified ) {
							wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
						}

						$wps_wocuf_pro_stripe_card_number = isset( $_POST[ $payment_method . '-card-number' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $payment_method . '-card-number' ] ) ) : '';
						$wps_wocuf_pro_stripe_card_cvc    = isset( $_POST[ $payment_method . '-card-cvc' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $payment_method . '-card-cvc' ] ) ) : '';
						$wps_wocuf_pro_stripe_card_exp    = isset( $_POST[ $payment_method . '-card-expiry' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $payment_method . '-card-expiry' ] ) ) : '';

						$billing_first_name = $order->get_billing_first_name();
						$billing_last_name  = $order->get_billing_last_name();
						$billing_country    = $order->get_billing_country();
						$billing_state      = $order->get_billing_state();
						$billing_city       = $order->get_billing_city();
						$billing_postcode   = $order->get_billing_postcode();
						$billing_address_1  = $order->get_billing_address_1();
						$billing_address_2  = $order->get_billing_address_2();

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
						$gateway        = new WPS_Wocuf_Pro_Stripe_Gateway_Admin();

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

						$this->wps_wocuf_pro_create_stripe_log( $order_id, 'PaymentMethod', 'Success', $create_payment_method );

						if ( ! empty( $create_payment_method->id ) ) {

							$payment_method_id = $create_payment_method->id;

							// Now create customer.
							$new_customer = \Stripe\Customer::create(
								array(
									'name'    => $wps_wocuf_pro_stripe_name,
									'phone'   => $order->get_billing_phone(),
									'email'   => $order->get_billing_email(),
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

							$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Customer', 'Success', $new_customer );

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

							// Zero order payments for upsell !
							$order_amount = (int) $order->get_total();

							// This Case will be handled for subscription zero order total only.
							if ( empty( $order_amount ) ) {

								$setup_intent = $gateway->setup_future_intent( $customer_id, $payment_method_id );

								// For Non Sca cards, it is possible to save details off session without charge.
								if ( 'succeeded' === $setup_intent->status ) {

									update_post_meta( $order->get_id(), '_setup_future_intent', $setup_intent->id );

									if ( wps_upsell_order_contains_subscription( $order_id ) && wps_upsell_pg_supports_subs( $order_id ) ) {

										// If upsell parent order from stripe then create subscriptions. payment complete will activate them.
										wps_upsell_create_subscriptions_for_order( $order_id, $order );
									}

									$order->update_status( 'upsell-parent' );
									$order->add_order_note( __( 'Payment Free Order.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

									// For renewal payments.
									update_post_meta( $order_id, 'wps_upsell_order_started', 'true' );

									$this->initial_redirection_to_upsell_offer_and_triggers( $order_id, $wps_wocuf_pro_single_funnel, $result );

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
									// Delete Subscriptions for failed pre-upsell products from Order.
									if ( wps_upsell_order_contains_subscription( $order_id ) && wps_upsell_pg_supports_subs( $order_id ) ) {

										WC_Subscriptions_Manager::maybe_delete_subscription( $order_id );
									}

									throw new Exception( __( 'Sorry, Stripe cannot authenticate Intent for order total zero.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								}
							} else {        // Order total greater than 0.

								// Creating Payment Intent.
								$payment_desc     = esc_html__( 'Payment for order id : ', 'one-click-upsell-funnel-for-woocommerce-pro' );
								$card_intent_info = array(
									'amount'               => (int) $gateway->wps_wocuf_pro_stripe_amount( $order->get_total() ),
									'currency'             => strtolower( get_woocommerce_currency() ),
									'payment_method_types' => array( 'card' ),
									'confirm'              => true,
									'customer'             => $customer_id,
									'payment_method'       => $payment_method,
									'setup_future_usage'   => 'off_session',
									'description'          => $payment_desc . $order_id,
									'capture_method'       => $gateway->capture,
								);

								$wps_wocuf_pro_stripe_create_intent = \Stripe\PaymentIntent::create( $card_intent_info );

								$this->wps_wocuf_pro_create_stripe_log( $order_id, 'PaymentIntent', 'Success', $wps_wocuf_pro_stripe_create_intent );

								update_post_meta( $order_id, '_saved_payment_intent_id', $wps_wocuf_pro_stripe_create_intent->id );

								// Check if the SCA Confirmation is required?
								if ( ! empty( $wps_wocuf_pro_stripe_create_intent->charges->data ) ) {

									$payment_status = $wps_wocuf_pro_stripe_create_intent->charges->data;

									// If already captured!
									if ( $payment_status[0]->captured ) {

										$order->update_status( 'upsell-parent' );
										$msg = sprintf(
										/* translators: %s: intent id */
											esc_html__( 'Stripe charge complete ( Payment Intent ID: %s )', 'one-click-upsell-funnel-for-woocommerce-pro' ),
											$wps_wocuf_pro_stripe_create_intent->id
										);

										$order->add_order_note( $msg );

										$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Stripe charge complete', 'Success', $payment_status );

										if ( WC()->cart ) {
											WC()->cart->empty_cart();
										}
									} elseif ( 'manual' === $gateway->capture && 'requires_capture' === $wps_wocuf_pro_stripe_create_intent->status ) {

										// Payment Authorized and ready to capture.
										$msg = sprintf(
										/* translators: %s: intent id */
											esc_html__( 'Stripe Payment Authorized ( Payment Intent ID: %s )', 'one-click-upsell-funnel-for-woocommerce-pro' ),
											$wps_wocuf_pro_stripe_create_intent->id
										);

										$order->add_order_note( $msg );

										$order->update_status( 'on-hold' );

										update_post_meta( $order_id, '_wps_wocuf_pro_capture_type', 'manual' );

										if ( WC()->cart ) {

											WC()->cart->empty_cart();
										}
									}
								} else {

									// When SCA Auth is required.
									$wps_wocuf_pro_intent_confirmation = $wps_wocuf_pro_stripe_create_intent->confirm();

									// Acheive requires action. Use stripe.js for getting a modal.
									if ( ! empty( $wps_wocuf_pro_intent_confirmation ) ) {

										/**
										 * This URL contains only a hash, which will be sent to `checkout.js` where it will be set like this:
										 * `window.location = result.redirect`
										 * Once this redirect is sent to JS, the `onHashChange` function will execute `handleCardPayment`.
										 */

										$stripe_sca_upsell_redirect = array(
											'upsell_offer_link' => $result,
											'funnel_id' => $wps_wocuf_pro_single_funnel,
										);

										// For Stripe SCA save this!
										update_post_meta( $order_id, 'stripe_sca_upsell_redirect', $stripe_sca_upsell_redirect );

										$intent_redirect = array(
											'result'   => 'success',
											'redirect' => $gateway->get_return_url( $order ),
											'intent_secret' => $wps_wocuf_pro_intent_confirmation->client_secret,
										);

										return $intent_redirect;
									}
								}
							}
						}
					} elseif ( 'ppec_paypal' === $payment_method && function_exists( 'wc_gateway_ppec' ) && property_exists( wc_gateway_ppec(), 'checkout' ) ) {

						$checkout = wc_gateway_ppec()->checkout;

						if ( ! empty( $checkout ) && method_exists( $checkout, 'start_checkout_from_checkout' ) && class_exists( 'PayPal_API_Exception' ) ) {

							try {

								$checkout->start_checkout_from_checkout( $order_id, false );

							} catch ( PayPal_API_Exception $e ) {
								return;
							}
						}
					} elseif ( in_array( $payment_method, wps_upsell_supported_gateway_integrations(), true ) ) {

						$offer_products = ! empty( $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_products_in_offer'] ) ? $wps_wocuf_pro_all_funnels[ $wps_wocuf_pro_single_funnel ]['wps_wocuf_pro_products_in_offer'] : array();

						$funnel_contains_subscription = wps_upsell_funnel_contains_any_subscription( $order_id, $offer_products );

						/**
						 * In case reprocessing for failed order due to any previous errors
						 * Process the normal payments.
						 * Or
						 * Angeleye is payment method and needs subscription.
						 */
						if ( 'failed' === $order->get_status() || ( $funnel_contains_subscription && 'paypal_express' === $payment_method ) ) {

							$order->update_status( 'pending' );

							if ( wps_upsell_order_contains_subscription( $order_id ) && wps_upsell_pg_supports_subs( $order_id ) ) {

								if ( class_exists( 'WC_Subscriptions_Checkout' ) ) {
									WC_Subscriptions_Manager::maybe_delete_subscription( $order_id );
								}
							}

							// Now process as usual Checkout Procedure.
							if ( class_exists( 'WC_Subscriptions_Checkout' ) ) {
								WC_Subscriptions_Checkout::process_checkout( $order_id, $_POST );
							}

							// Process the normal Payment.
							$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
							$payment_result     = $available_gateways[ $payment_method ]->process_payment( $order_id );
							return $payment_result;
						}

						/**
						 * Mostly Common Error for subscription.
						 * Payment methods doesn't gets saved,
						 * Need to force to save payment method,
						 *
						 * When target product is simple but offer is subscription,
						 * Guest users orders don't have user_id in order.
						 * Need to Login the customer here.
						 * Also add customer id to order.
						 */
						if ( empty( $order->get_user_id() ) ) {

							// Create and auth/login the user.
							$funnel_contains_subscription && function_exists( 'wps_upsell_create_and_auth_customer' ) && wps_upsell_create_and_auth_customer( $order_id );
						}

						$payment_result_flag = $this->fetch_transaction_data( $_POST, $order_id, $funnel_contains_subscription );

						if ( false === $payment_result_flag ) {

							/**
							 * Some error Occured.
							 * Process Payment Error.
							 */
							throw new Exception( 'Payment Processing Request Error', 1 );
						}
					}

					$supported_gateways_with_redirection = wps_upsell_supported_gateways_with_redirection();

					// Can't run cron for paypal and cardcom and authorize.net as it involves redirection.
					if ( ! in_array( $payment_method, $supported_gateways_with_redirection, true ) ) {

						// For cron - Upsell is initialized. As just going to Redirect.
						update_post_meta( $order_id, 'wps_ocufp_upsell_initialized', time() );
					}

					// Process Subscriptions for pre upsell products from Order.
					if ( wps_upsell_order_contains_subscription( $order_id ) && wps_upsell_pg_supports_subs( $order_id ) ) {

						wps_upsell_create_subscriptions_for_order( $order_id, $order );
					}

					$this->initial_redirection_to_upsell_offer_and_triggers( $order_id, $wps_wocuf_pro_single_funnel, $result );

				} else {

					return;
				}
			}

			return;

		}

	}

	/**
	 * Initial redirection to Upsell offers
	 * and important Triggers.
	 *
	 * @param mixed $order_id          order id.
	 * @param mixed $funnel_id         funnel id.
	 * @param mixed $upsell_offer_link upsell offer link.
	 * @param mixed $safe_redirect     safe redirect.
	 * @since 3.5.0
	 */
	public function initial_redirection_to_upsell_offer_and_triggers( $order_id, $funnel_id, $upsell_offer_link, $safe_redirect = false ) {
		/**
		 * As just going to redirect, means upsell is initialized for this order.
		 *
		 * This can be used to track upsell orders in which browser window was closed
		 * and other purposes.
		 */
		update_post_meta( $order_id, 'wps_upsell_order_started', 'true' );

		// Add Upsell Funnel Id to order meta for Sales by Funnel tracking.
		update_post_meta( $order_id, 'wps_upsell_funnel_id', $funnel_id );

		// Add a timestamp for stripe, can be used when abandoned upsell.
		$order = wc_get_order( $order_id );
		if ( ! empty( $order ) && in_array( $order->get_payment_method(), wps_supported_gateways_with_upsell_parent_order(), true ) ) {

			update_post_meta( $order_id, '_wps_wocuf_pro_upsell_shown_timestamp', time() );
		}

		// Add Funnel Triggered count and Offer View Count for the current Funnel.
		$sales_by_funnel = new WPS_Upsell_Report_Sales_By_Funnel( $funnel_id );

		$sales_by_funnel->add_funnel_triggered_count();
		$sales_by_funnel->add_offer_view_count();

		if ( function_exists( 'WC' ) && ! empty( WC()->session ) ) {

			// Store Order ID in session so it can be re-used after payment failure.
			WC()->session->set( 'order_awaiting_payment', $order_id );
		}

		$upsell_result = array(
			'result'   => 'success',
			'redirect' => $upsell_offer_link,
		);

		// Redirect to upsell offer page via ajax or redirection.
		if ( $safe_redirect ) {

			wp_redirect( $upsell_result['redirect'] );  //phpcs:ignore
			exit;
		}

		if ( ! is_ajax() ) {
			wp_redirect( $upsell_result['redirect'] );  //phpcs:ignore
			exit;
		} else {
			wp_send_json( $upsell_result );
		}
	}

	/**
	 * Writes error or response to the logs file.
	 *
	 * @since 3.2.0
	 * @param int $order_id       Main Order Id.
	 * @param int $step           Running Function.
	 * @param int $message        Message.
	 * @param int $final_response Api Response.
	 */
	public function wps_wocuf_pro_create_stripe_log( $order_id, $step, $message, $final_response ) {
		$upsell_stripe_settings = get_option( 'woocommerce_wps-wocuf-pro-stripe-gateway_settings', array() );

		$stripe_logging = ! empty( $upsell_stripe_settings['logging'] ) ? $upsell_stripe_settings['logging'] : 'yes';

		if ( 'yes' === $stripe_logging ) {

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
				include_once ABSPATH . 'wp-admin/includes/file.php'; // Since we are using the filesystem outside wp-admin.
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
	 * When user clicks on No thanks for Upsell offer.
	 *
	 * @since 1.0.0
	 */
	public function wps_wocuf_pro_process_the_funnel() {
		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		if ( isset( $_GET['ocuf_th'] ) && ! empty( $_GET['ocuf_th'] ) && isset( $_GET['ocuf_ofd'] ) && isset( $_GET['ocuf_fid'] ) && isset( $_GET['ocuf_ok'] ) && isset( $_GET['ocuf_ns'] ) ) {

			$offer_id = sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) );

			$funnel_id = sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) );

			$order_key = sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) );

			$wp_nonce = sanitize_text_field( wp_unslash( $_GET['ocuf_ns'] ) );

			$order_id = wc_get_order_id_by_order_key( $order_key );

			// Expire Offer when order already processed or process_payment was called.
			if ( ! empty( $order_id ) ) {

				$order = wc_get_order( $order_id );

				$already_processed_order_statuses = array(
					'processing',
					'completed',
					'failed',
				);

				// If order or payment is already processed.
				if ( in_array( $order->get_status(), $already_processed_order_statuses, true ) || $this->expire_further_offers( $order_id ) ) {

					$this->expire_offer();
				}

				// Check for offers processed.
				$current_offer_id = $offer_id;
				$this->validate_offers_processed_on_upsell_action( $order_id, $current_offer_id );
			}

			// Add Offer Reject Count for the current Funnel.
			$sales_by_funnel = new WPS_Upsell_Report_Sales_By_Funnel( $funnel_id );
			$sales_by_funnel->add_offer_reject_count();

			$wps_wocuf_pro_all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );

			$wps_wocuf_pro_action_on_no = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_attached_offers_on_no'] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_attached_offers_on_no'] : array();

			$wps_wocuf_pro_check_action = isset( $wps_wocuf_pro_action_on_no[ $offer_id ] ) ? $wps_wocuf_pro_action_on_no[ $offer_id ] : '';

			if ( 'thanks' === $wps_wocuf_pro_check_action ) {

				$this->initiate_order_payment_and_redirect( $order_id );
			} elseif ( 'thanks' !== $wps_wocuf_pro_check_action ) {

				// Next offer id.
				$offer_id = $wps_wocuf_pro_check_action;

				// Check if next offer has product.
				$wps_wocuf_pro_upcoming_offer = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'][ $offer_id ] : array();

				// If next offer has no product then redirect.
				if ( empty( $wps_wocuf_pro_upcoming_offer ) ) {

					$this->initiate_order_payment_and_redirect( $order_id );
				}

				$funnel_saved_after_version_3 = ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_upsell_fsav3'] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_upsell_fsav3'] : '';

				$funnel_offer_post_id_assigned = ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_upsell_post_id_assigned'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_upsell_post_id_assigned'][ $offer_id ] : '';

				// When funnel is saved after v3.0.0 and offer post id is assigned and elementor active.
				if ( ! empty( $funnel_offer_post_id_assigned ) && 'true' === $funnel_saved_after_version_3 && wps_upsell_elementor_plugin_active() ) {

					$redirect_to_upsell = false;

					$offer_template = ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_template'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_template'][ $offer_id ] : '';

					// When template is set to custom.
					if ( 'custom' === $offer_template ) {

						$custom_offer_page_url = ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_custom_page_url'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_custom_page_url'][ $offer_id ] : '';

						if ( ! empty( $custom_offer_page_url ) ) {

							$redirect_to_upsell = true;
							$redirect_to_url    = $custom_offer_page_url;
						}
					} elseif ( ! empty( $offer_template ) ) { // When template is set to one, two or three.

						$offer_assigned_post_id = ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_upsell_post_id_assigned'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_upsell_post_id_assigned'][ $offer_id ] : '';

						if ( ! empty( $offer_assigned_post_id ) && 'publish' === get_post_status( $offer_assigned_post_id ) ) {

							$redirect_to_upsell = true;
							$redirect_to_url    = get_page_link( $offer_assigned_post_id );
						}
					}

					if ( true === $redirect_to_upsell ) {

						$url = add_query_arg(
							array(
								'ocuf_ns'  => $wp_nonce,
								'ocuf_fid' => $funnel_id,
								'ocuf_ok'  => $order_key,
								'ocuf_ofd' => $offer_id,
							),
							$redirect_to_url
						);

						// Set offers processed when there is another offer to come up means when not last offer.
						$this->set_offers_processed_on_upsell_action( $order_id, $current_offer_id, $url );

					} else {

						$this->initiate_order_payment_and_redirect( $order_id );
					}
				} else { // When funnel is saved before v3.0.0!

					$wps_wocuf_pro_offer_page_id = get_option( 'wps_wocuf_pro_funnel_default_offer_page', '' );

					if ( isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_custom_page_url'][ $offer_id ] ) && ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_custom_page_url'][ $offer_id ] ) ) {

						$wps_wocuf_pro_next_offer_url = $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_custom_page_url'][ $offer_id ];
					} elseif ( ! empty( $wps_wocuf_pro_offer_page_id ) && 'publish' === get_post_status( $wps_wocuf_pro_offer_page_id ) ) {
						$wps_wocuf_pro_next_offer_url = get_page_link( $wps_wocuf_pro_offer_page_id );
					} else {

						$this->initiate_order_payment_and_redirect( $order_id );
					}

					$url = add_query_arg(
						array(
							'ocuf_ns'  => $wp_nonce,
							'ocuf_fid' => $funnel_id,
							'ocuf_ok'  => $order_key,
							'ocuf_ofd' => $offer_id,
						),
						$wps_wocuf_pro_next_offer_url
					);
				}

				// Add Offer View Count for the current Funnel.
				$sales_by_funnel = new WPS_Upsell_Report_Sales_By_Funnel( $funnel_id );
				$sales_by_funnel->add_offer_view_count();

				wp_redirect( $url ); //phpcs:ignore
				exit();
			}
		}
	}

	/**
	 * Funnel offer shortcode callback.
	 *
	 * @since 1.0.0
	 */
	public function wps_wocuf_pro_funnel_offers_shortcode() {
		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$result = '';

		if ( isset( $_GET['ocuf_ok'] ) ) {
			$order_key = sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) );

			$order_id = wc_get_order_id_by_order_key( $order_key );

			if ( isset( $_GET['ocuf_ofd'] ) && isset( $_GET['ocuf_fid'] ) ) {
				$offer_id = sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) );

				$funnel_id = sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) );

				if ( isset( $_GET['ocuf_ns'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['ocuf_ns'] ) ), 'funnel_offers' ) ) {
					$wp_nonce = sanitize_text_field( wp_unslash( $_GET['ocuf_ns'] ) );

					$wps_wocuf_pro_all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );

					$wps_wocuf_pro_buy_text = get_option( 'wps_wocuf_pro_buy_text', __( 'Buy Now', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

					$wps_wocuf_pro_no_text = get_option( 'wps_wocuf_pro_no_text', __( 'No,thanks', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

					$wps_wocuf_pro_before_offer_price_text = get_option( 'wps_wocuf_pro_before_offer_price_text', __( 'Special Offer Price', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

					$wps_wocuf_pro_offered_products = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'] : array();

					$wps_wocuf_pro_offered_discount = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'] : array();

					$wps_wocuf_pro_buy_button_color = get_option( 'wps_wocuf_pro_buy_button_color', '' );

					$ocuf_th_button_color = get_option( 'wps_wocuf_pro_thanks_button_color', '' );

					$result .= '<div style="display:none;" id="wps_wocuf_pro_offer_loader"><img id="wps-wocuf-loading-offer" src="' . WPS_WOCUF_PRO_URL . 'public/images/ajax-loader.gif"></div><div class="wps_wocuf_pro_offer_container"><div class="woocommerce"><div class="wps_wocuf_pro_special_offers_for_you">';

					$wps_wocuf_pro_offer_banner_text = get_option( 'wps_wocuf_pro_offer_banner_text', __( 'Special Offer For You Only', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

					$result .= '<div class="wps_wocuf_pro_special_offer_banner"><h1>' . trim( $wps_wocuf_pro_offer_banner_text, '"' ) . '</h1></div>';

					$wps_wocuf_pro_single_offered_product = '';

					if ( ! empty( $wps_wocuf_pro_offered_products[ $offer_id ] ) ) {

						// In v2.0.0, it was array so handling to get the first product id.
						if ( is_array( $wps_wocuf_pro_offered_products[ $offer_id ] ) && count( $wps_wocuf_pro_offered_products[ $offer_id ] ) ) {

							foreach ( $wps_wocuf_pro_offered_products[ $offer_id ] as $handling_offer_product_id ) {

								$wps_wocuf_pro_single_offered_product = absint( $handling_offer_product_id );
								break;
							}
						} else {

							$wps_wocuf_pro_single_offered_product = absint( $wps_wocuf_pro_offered_products[ $offer_id ] );
						}
					}

					$wps_wocuf_pro_original_offered_product = wc_get_product( $wps_wocuf_pro_single_offered_product );

					$original_price = $wps_wocuf_pro_original_offered_product->get_price_html();

					$product = $wps_wocuf_pro_original_offered_product;

					if ( ! $wps_wocuf_pro_original_offered_product->is_type( 'variable' ) ) {
						$wps_wocuf_pro_offered_product = wps_upsell_change_product_price( $wps_wocuf_pro_original_offered_product, $wps_wocuf_pro_offered_discount[ $offer_id ] );

						$product = $wps_wocuf_pro_offered_product;
					}

					$result .= '<div class="wps_wocuf_pro_main_wrapper">';

					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $wps_wocuf_pro_single_offered_product ), 'full' );

					if ( empty( $image[0] ) ) {
						$image[0] = wc_placeholder_img_src();
					}

					$result .= '<div class="wps_wocuf_pro_product_image"><img src="' . $image[0] . '"></div>';

					$result .= '<div class="wps_wocuf_pro_offered_product"><div class="wps_wocuf_pro_product_title"><h2>' . $product->get_title() . '</h2></div>';

					$result .= '<div class="wps_wocuf_pro_offered_product_description">
								<p class="wps_wocuf_pro_product_desc">' . $product->get_description() . '</p></div>';

					$result .= '<div class="wps_wocuf_pro_product_price">
								<h4>' . $wps_wocuf_pro_before_offer_price_text . ' : ' . $product->get_price_html() . '</h4></div></div></div>';

					if ( $product->is_type( 'variable' ) ) {
						$variations = $product->get_available_variations();

						if ( ! empty( $variations ) ) {
							foreach ( $variations as &$v ) {
								if ( ! empty( $v['variation_id'] ) ) {
									$v_product = wc_get_product( $v['variation_id'] );

									$v_product = wps_upsell_change_product_price( $v_product, $wps_wocuf_pro_offered_discount[ $offer_id ] );

									$v['price_html'] = $v_product->get_price_html();
								}
							}
						}

						$attributes = $product->get_variation_attributes();

						$wps_wocuf_pro_unavailable_text = __( '<p>Please select an option.</p>', 'one-click-upsell-funnel-for-woocommerce-pro' );

						$wps_wocuf_pro_unavailable_text = apply_filters( 'wps_wocuf_pro_options_unavailability_text', $wps_wocuf_pro_unavailable_text );

						$result .= '<div class="wps_wocuf_pro_variations">';

						$result .= '<form class="variations_form cart" method="post" enctype="multipart/form-data" data-product_id=" ' . absint( $product->get_id() ) . '" data-product_variations="' . htmlspecialchars( wp_json_encode( $variations ) ) . '">';

						$result .= '<table class="variations"><tbody>';

						foreach ( $attributes as $attribute_name => $attribute_options ) {
							$result .= '<tr><td class="label"><label for="' . sanitize_title( $attribute_name ) . '">' . wc_attribute_label( $attribute_name ) . '</label></td>';

							/**
							 * Default attribute $selected which shows choose an option
							 * if no default is set.
							 *
							 * Passing $attribute_options[0] will auto select
							 * first available option in dropdown.
							 */
							$selected = $product->get_variation_default_attribute( $attribute_name );
							$result  .= '<td class="value">';
							$result  .= $this->wps_wocuf_pro_variation_attribute_options(
								array(
									'options'   => $attribute_options,
									'attribute' => $attribute_name,
									'product'   => $product,
									'selected'  => $attribute_options[0],
									'class'     => 'wps_wocuf_offer_variation_select',
								)
							);
							$result  .= '</td></tr>';

						}

						$result .= '</tbody></table></form></div>';

						// Enqueue add-to-cart-variation script.
						// Using Woocommerce script here to hide out of stock variations.
						wp_enqueue_script( 'wc-add-to-cart-variation' );

						?>
							<script type="text/javascript">
								jQuery(document).ready(function($){
									var variations = <?php echo wp_json_encode( $variations ); ?>;
									var before_price_text = <?php echo wp_json_encode( $wps_wocuf_pro_before_offer_price_text ); ?>;
									var unavailable_text = <?php echo wp_json_encode( $wps_wocuf_pro_unavailable_text ); ?>;
									var default_image = <?php echo wp_json_encode( $image[0] ); ?>;
									jQuery('.wps_wocuf_offer_variation_select').change(function(){
										var options_selected = false;
										var selectors = {};
										var $variation_price_box = jQuery( '.wps_wocuf_pro_product_price');
										var $variation_image_img = jQuery( '.wps_wocuf_pro_product_image img' );
										var $variation_id = jQuery( '.wps_wocuf_pro_variation_id' );
										var $variation_price = jQuery( '.wps_wocuf_pro_product_price h4');
										jQuery('.wps_wocuf_offer_variation_select').each(function(){
											var $this = jQuery(this);
											if( $this.val() != null && $this.val().length == 0 )
											{
												options_selected = false;
												$variation_price_box.removeClass('wps_wocuf_pro_hide');
												$variation_price_box.addClass('wps_wocuf_pro_display');
												$variation_price.html(unavailable_text);
												jQuery('.wps_wocuf_pro_buy').prop('disabled', true );
											}
											else
											{
												options_selected = true;
												selectors[$this.attr('name')] = $this.val();
											}
										});

										var matching_variations = wc_variation_form_matcher.find_matching_variations( variations, selectors );

										if( options_selected == true ) {

											var selected_variation = {};

											selected_variation = matching_variations.shift();

											if( typeof selected_variation != 'undefined' )
											{
												if( selected_variation.is_purchasable == true && selected_variation.is_in_stock == 1 )
												{    

													/**
													 * Save current selected attributes.
													 * 
													 * As if particular variation has any
													 * attribute left as 'Any..' in backend 
													 * then that selected attribute has no value
													 * in order item meta so we've add that 
													 * by code.
													 */
													var attr_array = {};

													for ( var key in selectors ) {

														// skip loop if the property is from prototype
														if ( ! selectors.hasOwnProperty( key ) ) continue;

														attr_array[key] = selectors[key];

													}

													var attr_array_string = JSON.stringify(attr_array);

													attr_array_string = attr_array_string.replace(/"/g , "'");

													var variation_attributes_div = $('#wps_wocuf_pro_variation_attributes');

													// Add selected attributes array as string in input type hidden to be retrieved on form submit.
													variation_attributes_div.html( '<input type="hidden" name="wocuf_var_attb" value="' + attr_array_string + '" >' );



													$variation_price_box.removeClass('wps_wocuf_pro_hide');
													$variation_price_box.addClass('wps_wocuf_pro_display');
													if( selected_variation.image.full_src )
													{
														$variation_image_img.attr('src',selected_variation.image.full_src);
													}
													else
													{
														$variation_image_img.attr('src',default_image);
													}

													$variation_id.val( selected_variation.variation_id );
													$variation_price.html( before_price_text + ' : ' + selected_variation.price_html );
													jQuery( '.wps_wocuf_pro_buy' ).prop( 'disabled', false );
												}
												else
												{
													$variation_price_box.removeClass( 'wps_wocuf_pro_hide' );
													$variation_price_box.addClass( 'wps_wocuf_pro_display' );
													if( selected_variation.image.full_src )
													{
														$variation_image_img.attr( 'src', selected_variation.image.full_src );
													}
													else
													{
														$variation_image_img.attr( 'src', default_image );
													}

													$variation_price.html( unavailable_text );
													jQuery( '.wps_wocuf_pro_buy' ).prop( 'disabled', true );
												}
											}
											else
											{
												$variation_price_box.removeClass( 'wps_wocuf_pro_hide' );
												$variation_price_box.addClass( 'wps_wocuf_pro_display' );
												$variation_image_img.attr( 'src', default_image );
												$variation_price.html( unavailable_text );
												jQuery( '.wps_wocuf_pro_buy' ).prop( 'disabled', true );
											}
										}
									});
									jQuery( '.wps_wocuf_offer_variation_select' ).trigger( 'change' );
								});

								var wc_variation_form_matcher = {
									find_matching_variations: function( product_variations, settings ) {
										var matching = [];
										for ( var i = 0; i < product_variations.length; i++ ) {
											var variation    = product_variations[i];

											if ( wc_variation_form_matcher.variations_match( variation.attributes, settings ) ) {
												matching.push( variation );
											}
										}
										return matching;
									},
									variations_match: function( attrs1, attrs2 ) {
										var match = true;
										for ( var attr_name in attrs1 ) {
											if ( attrs1.hasOwnProperty( attr_name ) ) {
												var val1 = null != attrs1[ attr_name ] ? attrs1[ attr_name ] : '';
												var val2 = null != attrs2[ attr_name ] ? attrs2[ attr_name ] : '';
												if ( val1 != undefined && val2 != undefined && val1.length != 0 && val2.length != 0 && val1 != val2 ) {
													match = false;
												}
											}
										}
										return match;
									}
								};
							</script>
						<?php
					}

					$result .= '<div class="wps_wocuf_pro_offered_product_actions">
								<form class="wps_wocuf_pro_offer_form" method="post">
								<input type="hidden" name="ocuf_ns" value="' . $wp_nonce . '">
								<input type="hidden" name="ocuf_fid" value="' . $funnel_id . '">
								<input type="hidden" class="wps_wocuf_pro_variation_id" name="product_id" value="' . absint( $product->get_id() ) . '">
								<div id="wps_wocuf_pro_variation_attributes" ></div>
								<input type="hidden" name="ocuf_ofd" value="' . $offer_id . '">
								<input type="hidden" name="ocuf_ok" value="' . $order_key . '">
								<button data-id="' . $funnel_id . '" style="background-color:' . $wps_wocuf_pro_buy_button_color . '" class="wps_wocuf_pro_buy wps_wocuf_pro_custom_buy" type="submit" name="wps_wocuf_pro_buy">' . $wps_wocuf_pro_buy_text . '</button></form>
								<a style="color:"' . $ocuf_th_button_color . '" 
								class="wps_wocuf_pro_skip wps_wocuf_pro_no" href="?ocuf_ns="' . $wp_nonce . '"
								&ocuf_th=1&ocuf_ok="' . $order_key . '"
								&ocuf_ofd="' . $offer_id . '"
								&ocuf_fid="' . $funnel_id . '"
								>"' . $wps_wocuf_pro_no_text . '"</a>
								</div>
							</div></div>';

					$result .= '</div>';

					$result .= '</div></div></div>';
				} else {
					$error_msg = __( 'You ran out of the special offers session.', 'one-click-upsell-funnel-for-woocommerce-pro' );

					$link_text = __( 'Go to the "Order details" page.', 'one-click-upsell-funnel-for-woocommerce-pro' );

					$error_msg = apply_filters( 'wps_wocuf_pro_error_message', $error_msg );

					$link_text = apply_filters( 'wps_wocuf_pro_order_details_link_text', $link_text );

					$order_received_url = wc_get_endpoint_url( 'order-received', $order_id, wc_get_page_permalink( 'checkout' ) );

					$order_received_url = add_query_arg( 'key', $order_key, $order_received_url );

					$result .= $error_msg . '<a href="' . $order_received_url . '" class="button">' . $link_text . '</a>';
				}
			} else {
				$error_msg = __( 'You ran out of the special offers session.', 'one-click-upsell-funnel-for-woocommerce-pro' );

				$link_text = __( 'Go to the "Order details" page.', 'one-click-upsell-funnel-for-woocommerce-pro' );

				$error_msg = apply_filters( 'wps_wocuf_pro_error_message', $error_msg );

				$link_text = apply_filters( 'wps_wocuf_pro_order_details_link_text', $link_text );

				$order_received_url = wc_get_endpoint_url( 'order-received', $order_id, wc_get_page_permalink( 'checkout' ) );

				$order_received_url = add_query_arg( 'key', $order_key, $order_received_url );

				$result .= $error_msg . '<a href="' . $order_received_url . '" class="button">' . $link_text . '</a>';
			}
		}

		if ( ! isset( $_GET['ocuf_ok'] ) || ! isset( $_GET['ocuf_ofd'] ) || ! isset( $_GET['ocuf_fid'] ) ) {
			$wps_wocuf_pro_no_offer_text = get_option( 'wps_wocuf_pro_no_offer_text', __( 'Sorry, you have no offers', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

			$result .= '<div class="wps-wocuf_pro-no-offer"><h2>' . trim( $wps_wocuf_pro_no_offer_text, '"' ) . '</h2>';

			$result .= '<a class="button wc-backward" href="' . esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ) . '">' . __( 'Return to Shop', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</a></div>';

		}

		return $result;
	}

	/**
	 * When user clicks on Add upsell product to my Order.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 * @throws Exception Exception.
	 */
	public function wps_wocuf_pro_charge_the_offer() {
		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		if ( isset( $_POST['wps_wocuf_pro_buy'] ) || isset( $_GET['wps_wocuf_pro_buy'] ) ) {

			unset( $_POST['wps_wocuf_pro_buy'] );

			$live_offer_url_params = wps_upsell_live_offer_url_params();

			if ( 'true' === $live_offer_url_params['status'] ) {

				$is_product_with_variations = false;

				if ( ! empty( $_POST['wocuf_var_attb'] ) ) {

					// Retrieve all variations from form.
					$variation_attributes = sanitize_text_field( wp_unslash( $_POST['wocuf_var_attb'] ) );
					$variation_attributes = stripslashes( $variation_attributes );
					$variation_attributes = str_replace( "'", '"', $variation_attributes );

					$variation_attributes = json_decode( $variation_attributes, true );

					$is_product_with_variations = true;
				}

				$wp_nonce = $live_offer_url_params['upsell_nonce'];

				$offer_id = $live_offer_url_params['offer_id'];

				$funnel_id = $live_offer_url_params['funnel_id'];

				$product_id = $live_offer_url_params['product_id'];

				$order_key = $live_offer_url_params['order_key'];

				$quantity = $live_offer_url_params['quantity'];

				$order_id = wc_get_order_id_by_order_key( $order_key );

				$_POST = get_post_meta( $order_id, 'mwb_upsell_payment_data_post' );

				// Expire Offer when order already processed or process_payment was called.
				if ( ! empty( $order_id ) ) {

					$order = wc_get_order( $order_id );

					$already_processed_order_statuses = array(
						'processing',
						'completed',
						'failed',
						'upsell-failed',
					);

					// If order or payment is already processed.
					if ( in_array( $order->get_status(), $already_processed_order_statuses, true ) || $this->expire_further_offers( $order_id ) ) {

						$this->expire_offer();
					}

					// Check for offers processed.
					$current_offer_id = $offer_id;
					$this->validate_offers_processed_on_upsell_action( $order_id, $current_offer_id );
				}

				if ( ! empty( $order ) ) {
					$upsell_product = wc_get_product( $product_id );

					if ( ! empty( $upsell_product ) && $upsell_product->is_purchasable() ) {

						$wps_wocuf_pro_all_funnels = get_option( 'wps_wocuf_pro_funnels_list' );

						$wps_wocuf_pro_offered_discount = ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'][ $offer_id ] : '';

						$upsell_product = wps_upsell_change_product_price( $upsell_product, $wps_wocuf_pro_offered_discount );

						if ( $is_product_with_variations ) {

							$upsell_product_type = $upsell_product->get_type();

							if ( 'variation' === $upsell_product_type || 'subscription_variation' === $upsell_product_type ) {

								$upsell_var_attb = $upsell_product->get_variation_attributes();

								// Variation has blank attribute when it is set as 'Any..' in backend.

								// Check if upsell product variation has any blank attribute ?
								if ( false !== array_search( '', $upsell_var_attb, true ) ) {

									// If yes then set attributes retrieved from form.
									$upsell_product->set_attributes( $variation_attributes );
								}
							}
						}

						/**
						 * Save item ids, which are being added.
						 */
						$upsell_items = get_post_meta( $order_id, '_upsell_remove_items_on_fail', true );
						$upsell_item_id_subscription = '';
						if ( empty( $upsell_items ) ) {

							$upsell_items = array();
						}

						if ( wps_upsell_is_subscription_product( $upsell_product ) && wps_upsell_pg_supports_subs( $order_id ) ) {

							// If Subscription product then create Subscription for current Upsell offer.
							wps_upsell_create_subscription_for_upsell_product( $order_id, $upsell_product, $quantity );

							// If Subscription product then handle Subscription price that will be set for current Upsell Product to be added to Order.
							$upsell_product_subs_modified = wps_upsell_subs_set_price_accordingly( $upsell_product );
							$is_subscription_product = true;

								$sale_price    = get_post_meta( $upsell_product->get_ID(), '_sale_price', true );
								$regular_price = get_post_meta( $upsell_product->get_ID(), '_subscription_price', true );
							if ( ! empty( $sale_price ) ) {

								$get_sybscription_order_total = $sale_price;
								$upsell_product_subs_modified->set_sale_price( $get_sybscription_order_total );
							} else {
								$get_sybscription_order_total = $regular_price;
							}

							$get_sybscription_order_total = $get_sybscription_order_total * $quantity;

							$upsell_item_id = $order->add_product( $upsell_product_subs_modified, $quantity );
							update_post_meta( $order_id, 'subscription_product_price' . $upsell_item_id, $get_sybscription_order_total );
							update_post_meta( $order_id, 'subscription_product_price_meta_id', $upsell_item_id );

						} else {

							// Update ( v3.6.7 ) starts.
							if ( class_exists( 'WC_PB_Order' ) && $upsell_product && $upsell_product->is_type( 'bundle' ) ) {

								global $wpdb;
								$instance       = WC_PB_Order::instance();
								$upsell_item_id = $instance->add_bundle_to_order( $upsell_product, $order, 1, array() );
								$order->save();

								$bundled_items = $upsell_product->get_bundled_data_items();

								if ( ! empty( $bundled_items ) && is_array( $bundled_items ) ) {
									foreach ( $bundled_items as $bundle_item ) {
										$bundle_data  = $bundle_item->get_data();
										$_product_id  = $bundle_data['product_id'];
										$download_ids = array_keys( (array) wc_get_product( $_product_id )->get_downloads() );

										if ( ! empty( $download_ids ) && is_array( $download_ids ) ) {
											foreach ( $download_ids as $download_id ) {

												if ( apply_filters( 'woocommerce_process_product_file_download_paths_grant_access_to_new_file', true, $download_id, $product_id, $order ) ) {
													// Grant permission if it doesn't already exist.
													if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT 1=1 FROM {$wpdb->prefix}woocommerce_downloadable_product_permissions WHERE order_id = %d AND product_id = %d AND download_id = %s", $order->get_id(), $product_id, $download_id ) ) ) {
														wc_downloadable_file_permission( $download_id, $_product_id, $order );
													}
												}
											}
										}
									}
								}
								// Update ( v3.6.7 ) ends.

							} else {
								$upsell_item_id = $order->add_product( $upsell_product, $quantity );

							}
							/**
							 * Process WPS Subscriptions for pre upsell products from Order.
							 */
							if ( class_exists( 'Subscriptions_For_Woocommerce_Compatiblity' ) && true === Subscriptions_For_Woocommerce_Compatiblity::pg_supports_subs( $order_id ) && true === Subscriptions_For_Woocommerce_Compatiblity::is_subscription_product( $upsell_product ) ) {
								$compat_class = new Subscriptions_For_Woocommerce_Compatiblity( 'Subscriptions_For_Woocommerce_Compatiblity', '1.0.0' );
								$compat_class->create_upsell_subscription( $order_id, $upsell_item_id );
							}
						}

						// Update (v3.6.7) starts.
						try {
							// Grant Download permissions if upsell product is downloadable.
							if ( ! empty( $upsell_product ) && $upsell_product->is_downloadable() ) {
								$downloads = (array) $upsell_product->get_downloads();
								if ( ! empty( $downloads ) ) {
									foreach ( $downloads as $d_id => $download ) {

										wc_downloadable_file_permission( $d_id, $upsell_product, $order );
									}
								}
							}
						} catch ( Exception $e ) {

							$error_message = $e->getMessage();
							// For Woocommerce Try block.
							throw new Exception( $error_message );
						}
						// Update(v3.6.7) ends.

						// Add Offer Accept Count for the current Funnel.
						$sales_by_funnel = new WPS_Upsell_Report_Sales_By_Funnel( $funnel_id );
						$sales_by_funnel->add_offer_accept_count();

						array_push( $upsell_items, $upsell_item_id );
						update_post_meta( $order_id, '_upsell_remove_items_on_fail', $upsell_items );

						/**
						 * Add meta to item id so that it upsell product can be distinguished.
						 */
						wc_add_order_item_meta( $upsell_item_id, 'is_upsell_purchase', 'true' );

						$data  = ! empty( $_GET['data'] ) ? sanitize_text_field( wp_unslash( $_GET['data'] ) ) : '';
						$data1 = ! empty( $_POST['formdata'] ) ? sanitize_text_field( wp_unslash( $_POST['formdata'] ) ) : '';
						$data1 = str_replace( '~', '"', $data1 );

						if ( ! empty( $data1 ) ) {
							$data = $data1;
						}

						do_action( 'wps_save_form', $data, $upsell_item_id );

						/**
						 * Upsell offer is accepted and product object is ready here.
						 * After adding the upsell product, remove target product.
						 */
						$target_item_id = get_post_meta( $order_id, '_wps_wocufpro_replace_target', true );
						$remove_flag    = false;

						if ( ! empty( $target_item_id ) && is_numeric( $target_item_id ) ) {

							/**
							 * In case of stripe this item will be already paid,So handled in initiate payment functions.
							 * Just call the initiate function forcefully.
							 * For other payment methods we need to remove the target product here.
							 * And forcefully initiate payment
							 * Change flag key as non numeric.
							 */
							if ( in_array( $order->get_payment_method(), wps_supported_gateways_with_upsell_parent_order(), true ) ) {

								$remove_flag = true;

							} else {

								// Remove the target item and also if it was subscription then delete subscription too.
								$remove_subscription_id = get_post_meta( $order_id, '_wps_wocufpro_replace_target_subs_id', true );

								if ( ! empty( $remove_subscription_id ) ) {

									$subscriptions = wcs_get_subscriptions_for_order( $order_id );

									// We get the related subscription for this order.
									foreach ( $subscriptions as $subscription_id => $subscription_obj ) {

										if ( (int) $remove_subscription_id === (int) $subscription_id ) {

											if ( $subscription_obj->can_be_updated_to( 'cancelled' ) ) {
												$subscription_obj->update_status( 'cancelled' );
												$subscription_obj->update_status( 'trash' );
												break;
											}
										}
									}
								}

								$order->remove_item( $target_item_id );
								$order->save();

								$order->calculate_totals();
								update_post_meta( $order_id, '_wps_wocufpro_replace_target', 'upgraded' );
								$remove_flag = true;
							}
						}

						$order->calculate_totals();
						$order = wc_get_order( $order_id );

						$order->save();

						// Upsell product was purchased for this order.
						update_post_meta( $order_id, 'wps_wocuf_upsell_order', 'true' );

					}

					$wps_wocuf_pro_all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );

					$wps_wocuf_pro_buy_action = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_attached_offers_on_buy'] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_attached_offers_on_buy'] : '';

					$url = '';

					$force_to_payment = ! empty( $remove_flag ) && true === $remove_flag ? 'yes' : '';

					if ( 'yes' === $force_to_payment || ( isset( $wps_wocuf_pro_buy_action[ $offer_id ] ) && 'thanks' === $wps_wocuf_pro_buy_action[ $offer_id ] ) ) {

						$this->initiate_order_payment_and_redirect( $order_id );

					} elseif ( isset( $wps_wocuf_pro_buy_action[ $offer_id ] ) && 'thanks' !== $wps_wocuf_pro_buy_action[ $offer_id ] ) {
						// Next offer id.
						$offer_id = $wps_wocuf_pro_buy_action[ $offer_id ];

						// Check if next offer has product.
						$wps_wocuf_pro_upcoming_offer = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'][ $offer_id ] : '';

						// If next offer has no product then redirect.
						if ( empty( $wps_wocuf_pro_upcoming_offer ) ) {

							$this->initiate_order_payment_and_redirect( $order_id );

						} else {

							$funnel_saved_after_version_3 = ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_upsell_fsav3'] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_upsell_fsav3'] : '';

							$funnel_offer_post_id_assigned = ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_upsell_post_id_assigned'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_upsell_post_id_assigned'][ $offer_id ] : '';

							// When funnel is saved after v3.0.0 and offer post id is assigned and elementor active.
							if ( ! empty( $funnel_offer_post_id_assigned ) && 'true' === $funnel_saved_after_version_3 && wps_upsell_elementor_plugin_active() ) {

								$redirect_to_upsell = false;

								$offer_template = ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_template'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_template'][ $offer_id ] : '';

								// When template is set to custom.
								if ( 'custom' === $offer_template ) {

									$custom_offer_page_url = ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_custom_page_url'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_custom_page_url'][ $offer_id ] : '';

									if ( ! empty( $custom_offer_page_url ) ) {

										$redirect_to_upsell = true;
										$redirect_to_url    = $custom_offer_page_url;
									}
								} elseif ( ! empty( $offer_template ) ) { // When template is set to one, two or three.

									$offer_assigned_post_id = ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_upsell_post_id_assigned'][ $offer_id ] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_upsell_post_id_assigned'][ $offer_id ] : '';

									if ( ! empty( $offer_assigned_post_id ) && 'publish' === get_post_status( $offer_assigned_post_id ) ) {

										$redirect_to_upsell = true;
										$redirect_to_url    = get_page_link( $offer_assigned_post_id );
									}
								}

								if ( true === $redirect_to_upsell ) {

									$url = add_query_arg(
										array(
											'ocuf_ns'  => $wp_nonce,
											'ocuf_fid' => $funnel_id,
											'ocuf_ok'  => $order_key,
											'ocuf_ofd' => $offer_id,
										),
										$redirect_to_url
									);

									// Set offers processed when there is another offer to come up means when not last offer.
									$this->set_offers_processed_on_upsell_action( $order_id, $current_offer_id, $url );

								} else {

									$this->initiate_order_payment_and_redirect( $order_id );
								}
							} else { // When funnel is saved before v3.0.0.

								$wps_wocuf_pro_offer_page_id = get_option( 'wps_wocuf_pro_funnel_default_offer_page', '' );

								if ( isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_custom_page_url'][ $offer_id ] ) && ! empty( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_custom_page_url'][ $offer_id ] ) ) {

									$wps_wocuf_pro_next_offer_url = $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_custom_page_url'][ $offer_id ];
								} elseif ( ! empty( $wps_wocuf_pro_offer_page_id ) && 'publish' === get_post_status( $wps_wocuf_pro_offer_page_id ) ) {

									$wps_wocuf_pro_next_offer_url = get_page_link( $wps_wocuf_pro_offer_page_id );
								} else {

									$this->initiate_order_payment_and_redirect( $order_id );
								}

								$url = add_query_arg(
									array(
										'ocuf_ns'  => $wp_nonce,
										'ocuf_fid' => $funnel_id,
										'ocuf_ok'  => $order_key,
										'ocuf_ofd' => $offer_id,
									),
									$wps_wocuf_pro_next_offer_url
								);

							}
						}

						// Add Offer View Count for the current Funnel.
						$sales_by_funnel = new WPS_Upsell_Report_Sales_By_Funnel( $funnel_id );
						$sales_by_funnel->add_offer_view_count();

						wp_redirect( $url ); //phpcs:ignore
						exit;
					}
				} else {

					$this->initiate_order_payment_and_redirect( $order_id );
				}
			}
		}
	}

	/**
	 * Add custom cron recurrence time interval.
	 *
	 * @since 1.0.0
	 * @param array $schedules Array of cron Schedule times for recurrence.
	 */
	public function set_cron_schedule_time( $schedules ) {
		if ( ! isset( $schedules['wps_wocuf_twenty_minutes'] ) ) {

			$schedules['wps_wocuf_twenty_minutes'] = array(
				'interval' => 20 * 60,
				'display'  => __( 'Once every 20 minutes', 'one-click-upsell-funnel-for-woocommerce-pro' ),
			);

		}

		return $schedules;
	}

	/**
	 * Cron schedule fire Event for Order payment process.
	 *
	 * @since 1.0.0
	 */
	public function order_payment_cron_fire_event() {

		// Pending Orders.
		$pending_upsell_orders = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'wc-pending',
				'fields'      => 'ids', // return only ids.
				'meta_key'    => 'wps_ocufp_upsell_initialized', //phpcs:ignore
			'post_type'       => 'shop_order',
			'order'           => 'ASC',
			)
		);

		if ( ! empty( $pending_upsell_orders ) && is_array( $pending_upsell_orders ) && count( $pending_upsell_orders ) ) {

			foreach ( $pending_upsell_orders as $order_id ) {

				$time_stamp = get_post_meta( $order_id, 'wps_ocufp_upsell_initialized', true );

				if ( ! empty( $time_stamp ) ) {

					$fifteen_minutes = strtotime( '+15 minutes', $time_stamp );

					$current_time = time();

					$time_diff = $fifteen_minutes - $current_time;

					if ( 0 > $time_diff ) {

						global $woocommerce;

						$gateways = $woocommerce->payment_gateways->get_available_payment_gateways();

						$order = new WC_Order( $order_id );

						// For cron - Payment initialized.
						delete_post_meta( $order_id, 'wps_ocufp_upsell_initialized' );

						$payment_method = $order->get_payment_method();

						$dependent_capture_gateways = array(
							'paypal_express', // For Paypal Angeleye.
						);

						// Do not work for Angeleye/Paypal payments Integration.
						if ( ! in_array( $order->get_payment_method(), $dependent_capture_gateways, true ) ) {

							if ( in_array( $payment_method, wps_upsell_supported_gateway_integrations(), true ) ) {

								/**
								 * Get transaction data.
								 */
								$transaction_data = get_post_meta( $order_id, $payment_method . '_transaction_data', true );

								if ( ! empty( $transaction_data ) ) {
									$_POST = $transaction_data;
									delete_post_meta( $order_id, $payment_method . '_transaction_data' );
								}
								if ( empty( $transaction_data ) ) {
									$_POST = get_post_meta( $order_id, 'mwb_upsell_payment_data_post' );
								}
							}

							$gateways[ $payment_method ]->process_payment( $order_id, 'cron' );
						}
					}
				}
			}
		}

		/**
		 * Order with Accepted Offer products.
		 * Abandon Upsell Orders with accepted or rejected upsell offers.
		 */
		$pending_upsell_orders_with_accepted_products = get_posts(
			array(
				'numberposts' => -1,
				'post_status' => 'wc-upsell-parent',
				'fields'      => 'ids', // return only ids.
				'meta_key'    => 'wps_upsell_order_started', //phpcs:ignore
			'post_type'       => 'shop_order',
			'order'           => 'ASC',
			)
		);

		if ( ! empty( $pending_upsell_orders_with_accepted_products ) && is_array( $pending_upsell_orders_with_accepted_products ) && count( $pending_upsell_orders_with_accepted_products ) ) {

			foreach ( $pending_upsell_orders_with_accepted_products as $key => $order_id ) {

				$time_stamp = get_post_meta( $order_id, '_wps_wocuf_pro_upsell_shown_timestamp', true );

				if ( ! empty( $time_stamp ) ) {

					$fifteen_minutes = strtotime( '+15 minutes', $time_stamp );

					$current_time = time();

					$time_diff = $fifteen_minutes - $current_time;

					if ( 0 > $time_diff ) {

						$order = new WC_Order( $order_id );

						$is_upsell_accepted = get_post_meta( $order_id, 'wps_wocuf_upsell_order', true );

						if ( ! empty( $is_upsell_accepted ) ) {

							// For removing - accepted offer.
							$this->wps_wocuf_pro_expire_offer_on_failed_upsell_payment( $order, false );
							delete_post_meta( $order_id, 'wps_wocuf_upsell_order' );
						}

						$set_status = $order->needs_processing() ? 'processing' : 'completed';
						$order->update_status( $set_status );

						delete_post_meta( $order_id, '_wps_wocuf_pro_upsell_shown_timestamp' );
					}
				}
			}
		}
	}

	/**
	 * Initiate Order Payment and redirect.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID.
	 */
	public function initiate_order_payment_and_redirect( $order_id ) {
		$order = new WC_Order( $order_id );

		if ( empty( $order ) ) {

			return false;
		}

		// As Order Payment is initiated so Expire further Offers.
		update_post_meta( $order_id, '_wps_upsell_expire_further_offers', true );

		// Delete Offers Processed data as now we don't need it.
		delete_post_meta( $order_id, '_wps_upsell_offers_processed' );

		/**
		 * If the target is removed after processing upsell payment,
		 * The mail triggered will contain the target product as well.
		 * Hence best will be the target is removed first and after the payment should
		 * initialised.
		 * In case upsell payment is failed. Just show order with upsell payment failed.
		 */
		if ( in_array( $order->get_payment_method(), wps_supported_gateways_with_upsell_parent_order(), true ) ) {

			$this->process_target_upgrade_for_upsell_parent_order( $order_id );
		}

		// Now process the payment.
		$result = $this->upsell_order_final_payment( $order_id );
		$url    = $order->get_checkout_order_received_url();
		$old_order = $order;

		if ( isset( $result['result'] ) && 'success' === $result['result'] ) {

			$args = array(
				'post_parent' => $order_id,
				'post_type'   => 'shop_subscription',
				'post_status' => array( 'closed' ),
				'numberposts' => -1,
			);
if ( ! empty ($args) ) {
	$all_plans = get_posts( $args );
}
		

			$product_price = 0;
			if ( ! empty( $all_plans ) ) {

				$order = wc_get_order( $all_plans[0]->ID );
				if ( ! empty( $order ) ) {

					foreach ( $order->get_items() as $item_id => $item ) {

						$product_id = $item->get_product_id();
						$sale_price    = get_post_meta( $product_id, '_sale_price', true );
						$regular_price = get_post_meta( $product_id, '_subscription_price', true );
						if ( ! empty( $sale_price ) ) {
							$get_sybscription_order_total = $sale_price;
						} else {
							$get_sybscription_order_total = $regular_price;
						}
						$product_price += $get_sybscription_order_total;
						$item->set_subtotal( $get_sybscription_order_total );

						$item->set_total( $get_sybscription_order_total );
					}
				}

				$order->save();
				update_post_meta( $all_plans[0]->ID, '_order_total', $product_price );
			}

			add_filter( 'woocommerce_new_order_email_allows_resend', '__return_true' );
			WC()->mailer()->emails['WC_Email_New_Order']->trigger( $old_order->get_id(), $old_order, true );
			remove_filter( 'woocommerce_new_order_email_allows_resend', '__return_true' );

			wp_redirect( $result['redirect'] ); //phpcs:ignore

			exit;
		}

		if ( isset( $result['result'] ) && 'failure' === $result['result'] ) {

			global $woocommerce;
			$cart_page_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : $woocommerce->cart->get_cart_url();

			wp_redirect( $cart_page_url ); //phpcs:ignore
			exit;
		} else {
			wp_redirect( $url ); //phpcs:ignore
			exit;
		}
	}

	/**
	 * Process Payment for Upsell order.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID.
	 */
	public function upsell_order_final_payment( $order_id = '' ) {
		if ( empty( $order_id ) ) {

			return false;
		}

		global $woocommerce;

		$gateways = $woocommerce->payment_gateways->get_available_payment_gateways();

		$order = new WC_Order( $order_id );

		// For cron - Payment initialized.
		delete_post_meta( $order_id, 'wps_ocufp_upsell_initialized' );

		$payment_method = $order->get_payment_method();

		// If Payment Method are one of Official Integrations.
		if ( in_array( $payment_method, wps_upsell_supported_gateway_integrations(), true ) ) {

			if ( 'paypal_express' === $payment_method ) {

				delete_post_meta( $order_id, '_' . $payment_method . '_payment_session_details' );

			} else {

				/**
				 * Get transaction data.
				 */
				$transaction_data = get_post_meta( $order_id, $payment_method . '_transaction_data', true );

				if ( ! empty( $transaction_data ) ) {
					$_POST = $transaction_data;
					delete_post_meta( $order_id, $payment_method . '_transaction_data' );
				}
			}

			$result = $gateways[ $payment_method ]->process_payment( $order_id );

		} elseif ( 'stripe' === $payment_method || 'stripe_cc'== $payment_method ) { // If Payment Method is Official Stripe.
	        $payment_method ='stripe';
			// Before initiating offer payment set payment complete flag open.
			delete_post_meta( $order_id, '_wps_wocuf_stripe_parent_initiating' );
			$stripe_compat  = new WPS_Stripe_Payment_Gateway();
			$payment_result = $stripe_compat->process_upsell_payment( $order_id );

			$order = wc_get_order( $order_id );

			// Check if succesful.
			if ( true === $payment_result ) {

				// Handle success payment.
				if ( 'processing' === $order->get_status() ) {

					$stripe_obj = new WC_Gateway_Stripe();
					if ( wps_upsell_order_contains_subscription( $order_id ) && wps_upsell_pg_supports_subs( $order_id ) ) {

						WC_Subscriptions_Manager::activate_subscriptions_for_order( $order );
					}

					// Process WPS Subscriptions for pre upsell products from Order.
					if ( class_exists( 'Subscriptions_For_Woocommerce_Compatiblity' ) && true === Subscriptions_For_Woocommerce_Compatiblity::pg_supports_subs( $order_id ) && true === Subscriptions_For_Woocommerce_Compatiblity::order_contains_subscription( $order_id ) ) {
						$subs_compatibility = new Subscriptions_For_Woocommerce_Compatiblity( 'Subscriptions_For_Woocommerce', '1.0.1' );
						$subs_compatibility->activate_subs_after_upsell( $order_id );
					}

					return array(
						'result'   => 'success',
						'redirect' => $stripe_obj->get_return_url( $order ),
					);
				}
			} else {

				// Handle failed payment.
				if ( 'upsell-failed' === $order->get_status() ) {
					$result = $this->wps_wocuf_pro_expire_offer_on_failed_upsell_payment( $order );

					if ( class_exists( 'Subscriptions_For_Woocommerce_Compatiblity' ) && true === Subscriptions_For_Woocommerce_Compatiblity::pg_supports_subs( $order_id ) && true === Subscriptions_For_Woocommerce_Compatiblity::order_contains_subscription( $order_id ) ) {

						/*delete failed order subscription*/
						wps_sfw_delete_failed_subscription( $order_id );
					}
				}
			}
		} elseif ( empty( $payment_method ) ) {

			// Free Order upsell payment method is ''.
			// Check for order total and process_payment.
			$may_be_updated_order_total = floatval( $order->get_total() );
			if ( 0 < $may_be_updated_order_total ) {

				$wps_upsell_global_settings = get_option( 'wps_upsell_global_options', array() );
				$free_order_enabled         = ! empty( $wps_upsell_global_settings['enable_free_upsell'] ) ? $wps_upsell_global_settings['enable_free_upsell'] : 'no';
				$free_order_enabled_gateway = ! empty( $wps_upsell_global_settings['free_upsell_select'] ) ? $wps_upsell_global_settings['free_upsell_select'] : '';

				if ( 'on' === $free_order_enabled && ! empty( $free_order_enabled_gateway ) && ! empty( $gateways[ $free_order_enabled_gateway ] ) ) {
					$result = $gateways[ $free_order_enabled_gateway ]->process_payment( $order_id );

					if ( empty( $result ) ) {

						// Remove upsell products and process free order.
						$this->upsell_products_removal( $order_id );

						// Order total is stil zero.
						$order->payment_complete();
						wc_empty_cart();
						return true;
					}
				}
			} else {
				// stripe_bancontact..
				// stripe_bancontact.
				// Order total is stil zero.
				$order->payment_complete();
				wc_empty_cart();
				return true;
			}
		} else {

            if ( wps_upsell_order_contains_subscription( $order_id ) && wps_upsell_pg_supports_subs( $order_id ) ) {

				WC_Subscriptions_Manager::activate_subscriptions_for_order( $order );
			}

			// Process WPS Subscriptions for pre upsell products from Order.
			if ( class_exists( 'Subscriptions_For_Woocommerce_Compatiblity' ) && true === Subscriptions_For_Woocommerce_Compatiblity::pg_supports_subs( $order_id ) && true === Subscriptions_For_Woocommerce_Compatiblity::order_contains_subscription( $order_id ) ) {
				$subs_compatibility = new Subscriptions_For_Woocommerce_Compatiblity( 'Subscriptions_For_Woocommerce', '1.0.1' );
				$subs_compatibility->activate_subs_after_upsell( $order_id );
			}

			$result = $gateways[ $payment_method ]->process_payment( $order_id, 'true' );

		}

		return $result;
	}

	/**
	 * Custom Handling for payment_complete() for official stripe.
	 *
	 * @param mixed $order_statuses order stasuses.
	 * @param mixed $order          order.
	 * @since 3.5.0
	 */
	public function handle_order_completion_for_downloadable_products( $order_statuses = array( 'on-hold', 'pending', 'failed', 'cancelled' ), $order = false ) {
		if ( ! empty( $order ) && 'stripe' === $order->get_payment_method() && ! empty( get_post_meta( $order->get_id(), '_wps_wocuf_stripe_parent_initiating', true ) ) ) {
			return array();
		}

		return $order_statuses;
	}

	/**
	 * Product Variations dropdown content.
	 *
	 * @since  1.0.0
	 * @param  mixed $args args for variable product dropdown.
	 * @return $html for variable product dropdown.
	 */
	public function wps_wocuf_pro_variation_attribute_options( $args = array() ) {
		$args = wp_parse_args(
			apply_filters( 'woocommerce_dropdown_variation_attribute_options_args', $args ),
			array(
				'options'          => false,
				'attribute'        => false,
				'product'          => false,
				'selected'         => false,
				'name'             => '',
				'id'               => '',
				'class'            => '',
				'show_option_none' => false,
			)
		);

		$options               = $args['options'];
		$product               = $args['product'];
		$attribute             = $args['attribute'];
		$name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
		$id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
		$class                 = $args['class'];
		$show_option_none      = $args['show_option_none'] ? true : false;
		$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'one-click-upsell-funnel-for-woocommerce-pro' );

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[ $attribute ];
		}

		$html  = '<select id="' . esc_attr( $id ) . '" class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '" data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '">';
		$html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

		if ( ! empty( $options ) ) {
			if ( $product && taxonomy_exists( $attribute ) ) {
				// Get terms if this is a taxonomy - ordered. We need the names too.
				$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );

				foreach ( $terms as $term ) {
					if ( in_array( $term->slug, $options, true ) ) {
						$html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
					}
				}
			} else {
				foreach ( $options as $option ) {
					// This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
					$selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
					$html    .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
				}
			}
		}

		$html .= '</select>';

		return $html;
	}

	/**
	 * Shortcodes for Upsell action and Product attributes.
	 * The life of this plugin.
	 *
	 * @since 1.0.0
	 */
	public function upsell_shortcodes() {
		// OLD shortcodes :->.

		// Creating shortcode for accept link on custom page.
		add_shortcode( 'wps_wocuf_pro_yes', array( $this, 'wps_wocuf_pro_custom_page_action_link_yes' ) );

		// creating shortcode for thanks link on custom page.
		add_shortcode( 'wps_wocuf_pro_no', array( $this, 'wps_wocuf_pro_custom_page_action_link_no' ) );

		// creating shortcode for showing product price on custom page.
		add_shortcode( 'wps_wocuf_pro_offer_price', array( $this, 'wps_wocuf_pro_custom_page_product_offer_price' ) );
		// creating shortcode for showing order details link on custom page.
		add_shortcode( 'wps_wocuf_pro_order_details', array( $this, 'wps_wocuf_pro_custom_page_order_details_link' ) );

		// creating shortcode for showing product variation selector on custom page.
		add_shortcode( 'wps_wocuf_pro_selector', array( $this, 'wps_wocuf_pro_custom_page_selector' ) );

		// adding shortcode for default funnel offer page.
		add_shortcode( 'wps_wocuf_pro_funnel_default_offer_page', array( $this, 'wps_wocuf_pro_funnel_offers_shortcode' ) );

		// New shortcodes.

		// Custom Form.
		add_shortcode( 'wps_form', array( $this, 'wps_form_shortcode_callback' ) );

		// Upsell Action.

		add_shortcode( 'wps_upsell_yes', array( $this, 'buy_now_shortcode_content' ) );

		add_shortcode( 'wps_upsell_no', array( $this, 'no_thanks_shortcode_content' ) );

		// Product.
		add_shortcode( 'wps_upsell_title', array( $this, 'product_title_shortcode_content' ) );

		add_shortcode( 'wps_upsell_desc', array( $this, 'product_description_shortcode_content' ) );

		add_shortcode( 'wps_upsell_desc_short', array( $this, 'product_short_description_shortcode_content' ) );

		add_shortcode( 'wps_upsell_image', array( $this, 'product_image_shortcode_content' ) );

		add_shortcode( 'wps_upsell_price', array( $this, 'product_price_shortcode_content' ) );

		add_shortcode( 'wps_upsell_variations', array( $this, 'variations_selector_shortcode_content' ) );

		// Review.
		add_shortcode( 'wps_upsell_star_review', array( $this, 'product_star_review' ) );

		// Default Gutenberg offer.
		add_shortcode( 'wps_upsell_default_offer_identification', array( $this, 'default_offer_identification' ) );

		/**
		 * Shortcodes since v3.5.0
		 * Quantity Field and Timer Shortcode.
		 */
		add_shortcode( 'wps_upsell_timer', array( $this, 'timer_shortcode_content' ) );

		add_shortcode( 'wps_upsell_quantity', array( $this, 'quantity_shortcode_content' ) );

		/**
		 * Shortcode to display Additional offers.
		 */
		add_shortcode( 'wps_additional_offers', array( $this, 'wps_wocuf_additional_offers' ) );

	}

	/**
	 * Remove http and https from Upsell Action shortcodes added by Page Builders.
	 *
	 * @param mixed $content content.
	 * @since 3.2.1
	 */
	public function filter_upsell_shortcodes_content( $content = '' ) {
		$upsell_yes_shortcode = array( 'http://[wps_upsell_yes]', 'https://[wps_upsell_yes]' );
		$upsell_no_shortcode  = array( 'http://[wps_upsell_no]', 'https://[wps_upsell_no]' );

		$content = str_replace( $upsell_yes_shortcode, '[wps_upsell_yes]', $content );

		$content = str_replace( $upsell_no_shortcode, '[wps_upsell_no]', $content );

		return $content;
	}

	/**
	 * Get upsell product id from offer page id.
	 *
	 * @since 3.0.0
	 */
	public function get_upsell_product_id_for_shortcode() {
		// Firstly try to get product id from url offer and funnel id i.e. the case of live offer.

		if ( ! function_exists( 'wps_upsell_get_pid_from_url_params' ) ) {

			return;
		}

		$product_id_from_get = wps_upsell_get_pid_from_url_params();

		// When it is live offer.
		if ( 'true' === $product_id_from_get['status'] ) {

			$funnel_id = $product_id_from_get['funnel_id'];
			$offer_id  = $product_id_from_get['offer_id'];

			// Get all funnels.
			$all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );

			$product_id = ! empty( $all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'][ $offer_id ] ) ? $all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'][ $offer_id ] : '';

			return $product_id;
		}

		// Will only execute from here when it is not live offer.

		// Get product id from current offer page post id.
		global $post;
		$offer_page_id = $post->ID;

		$funnel_data = get_post_meta( $offer_page_id, 'wps_upsell_funnel_data', true );

		$product_found_in_funnel = false;

		if ( ! empty( $funnel_data ) && is_array( $funnel_data ) && count( $funnel_data ) ) {

			$funnel_id = $funnel_data['funnel_id'];
			$offer_id  = $funnel_data['offer_id'];

			// Get all funnels.
			$all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );

			$product_id = ! empty( $all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'][ $offer_id ] ) ? $all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'][ $offer_id ] : '';

			if ( ! empty( $product_id ) ) {

				$product_found_in_funnel = true;
				return $product_id;
			}
		}

		// Get global product only for Custom Offer page and not for Upsell offer templates.
		if ( empty( $funnel_data ) && ! $product_found_in_funnel ) {

			$wps_upsell_global_settings = get_option( 'wps_upsell_global_options', array() );

			$product_id = ! empty( $wps_upsell_global_settings['global_product_id'] ) ? $wps_upsell_global_settings['global_product_id'] : '';

			if ( ! empty( $product_id ) ) {

				return $product_id;
			}
		}

		/**
		 * Product not selected? show alert! Will run one time in one reload.
		 * Run this alert only on a page.
		 */
		if ( is_page() && false === wp_cache_get( 'wps_upsell_no_product_in_offer' ) ) {

			$product_not_selected_alert = esc_html__( 'One Click Upsell', 'one-click-upsell-funnel-for-woocommerce-pro' );

			// For Upsell offer template.
			if ( ! empty( $funnel_data ) ) {

				$product_not_selected_content = esc_html__( 'Offer Product is not selected, please save a Offer Product in Funnel Offer settings.', 'one-click-upsell-funnel-for-woocommerce-pro' );
			} else { // For Custom offer page.

				$product_not_selected_content = esc_html__( 'Custom Offer page - detected! Please save a global Offer product in Global settings for testing purpose.', 'one-click-upsell-funnel-for-woocommerce-pro' );
			}

			?>

			<script src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'js/sweet-alert.js' ); ?>"></script> <?php //phpcs:ignore ?>

			<script type="text/javascript">

				var product_not_selected_alert = '<?php echo esc_html( $product_not_selected_alert ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped It just displayes message that is already escaped above. ?>';

				var product_not_selected_content = '<?php echo esc_html( $product_not_selected_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped It just displayes message that is already escaped above. ?>';

				swal( product_not_selected_alert , product_not_selected_content, 'warning' )

			</script>

			<?php
		}

		wp_cache_set( 'wps_upsell_no_product_in_offer', 'true' );

	}

	/**
	 * Validate shortcode for rendering content according to user( live offer ).
	 * and admin ( for viewing purpose ).
	 *
	 * @since 3.0.0
	 */
	public function validate_shortcode() {
		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		if ( isset( $_GET['ocuf_ns'] ) && isset( $_GET['ocuf_ok'] ) && isset( $_GET['ocuf_ofd'] ) && isset( $_GET['ocuf_fid'] ) ) {

			if ( wps_upsell_validate_upsell_nonce() ) {

				return 'live_offer';
			}
		} elseif ( current_user_can( 'manage_options' ) ) {

			return 'admin_view';
		}

		return false;
	}

	/**
	 * Shortcode for Upsell product title.
	 * Returns : Just the Content :).
	 *
	 * @since 3.0.0
	 */
	public function product_title_shortcode_content() {
		$validate_shortcode = $this->validate_shortcode();

		if ( $validate_shortcode ) {

			$product_id = $this->get_upsell_product_id_for_shortcode();

			if ( ! empty( $product_id ) ) {

				$post_type = get_post_type( $product_id );

				if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {

					return '';
				}

				$upsell_product = wc_get_product( $product_id );

				$upsell_product_title = $upsell_product->get_title();
				$upsell_product_title = ! empty( $upsell_product_title ) ? $upsell_product_title : '';

				if ( 'bundle' === $upsell_product->get_type() ) {
					$bundled_items = $upsell_product->get_bundled_data_items();
					if ( ! empty( $bundled_items ) && is_array( $bundled_items ) ) {

						$bundle_products = '<div class="wocuf_pro_bundle_wrap">';
						foreach ( $bundled_items as $item ) {

							$data = $item->get_data();
							$p_id = ! empty( $data['product_id'] ) ? $data['product_id'] : '';
							if ( ! empty( $p_id ) ) {
								$prod      = wc_get_product( $p_id );
								$prod_data = $prod->get_data();
								$prod_name = ! empty( $prod_data['name'] ) ? $prod_data['name'] : '';

								$bundle_products .= '<a href="' . get_permalink( $p_id ) . '" class="wocuf_pro_bundle_show" target="_blank">' . $prod_name . '</a>';
							}
						}
						$bundle_products       = '</div>' . $bundle_products;
						$upsell_product_title .= $bundle_products;
					}
				}
				return $upsell_product_title;
			}
		}
	}

	/**
	 * Shortcode for Upsell product description.
	 * Returns : Just the Content :)
	 *
	 * @since 3.0.0
	 */
	public function product_description_shortcode_content() {
		$validate_shortcode = $this->validate_shortcode();

		if ( $validate_shortcode ) {

			$product_id = $this->get_upsell_product_id_for_shortcode();

			if ( ! empty( $product_id ) ) {

				$post_type = get_post_type( $product_id );

				if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {

					return '';
				}

				$upsell_product = wc_get_product( $product_id );

				$upsell_product_desc = $upsell_product->get_description();
				$upsell_product_desc = ! empty( $upsell_product_desc ) ? $upsell_product_desc : '';

				return $upsell_product_desc;
			}
		}

	}

	/**
	 * Shortcode for Upsell product short description.
	 * Returns : Just the Content :)
	 *
	 * @since 3.0.0
	 */
	public function product_short_description_shortcode_content() {
		$validate_shortcode = $this->validate_shortcode();

		if ( $validate_shortcode ) {

			$product_id = $this->get_upsell_product_id_for_shortcode();

			if ( ! empty( $product_id ) ) {

				$post_type = get_post_type( $product_id );

				if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {

					return '';
				}

				$upsell_product = wc_get_product( $product_id );

				$upsell_product_short_desc = $upsell_product->get_short_description();
				$upsell_product_short_desc = ! empty( $upsell_product_short_desc ) ? $upsell_product_short_desc : '';

				return $upsell_product_short_desc;
			}
		}

	}

	/**
	 * Shortcode for Upsell product image.
	 *
	 * @param mixed $atts shortcode attributes.
	 * @since 3.5.0
	 */
	public function product_image_shortcode_content( $atts ) {
		$validate_shortcode = $this->validate_shortcode();

		if ( $validate_shortcode ) {

			$live_params_from_url = wps_upsell_get_pid_from_url_params();

			if ( ! empty( $live_params_from_url['status'] ) && 'true' === $live_params_from_url['status'] ) {

				$offer_id  = ! empty( $live_params_from_url['offer_id'] ) ? wc_clean( $live_params_from_url['offer_id'] ) : '';
				$funnel_id = ! empty( $live_params_from_url['funnel_id'] ) ? wc_clean( $live_params_from_url['funnel_id'] ) : '';

				if ( ! empty( $funnel_id ) && ! empty( $offer_id ) ) {

					$all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );

					$upsell_product_image_post_id = ! empty( $all_funnels[ $funnel_id ]['wps_upsell_offer_image'][ $offer_id ] ) ? $all_funnels[ $funnel_id ]['wps_upsell_offer_image'][ $offer_id ] : '';

					if ( ! empty( $upsell_product_image_post_id ) ) {

						$image_attributes = wp_get_attachment_image_src( $upsell_product_image_post_id, 'full' );

						$upsell_product_image_src = ! empty( $image_attributes[0] ) && filter_var( $image_attributes[0], FILTER_VALIDATE_URL ) ? $image_attributes[0] : false;
					}

					if ( ! empty( $upsell_product_image_src ) ) {

						// Shortcode attributes.
						$atts = shortcode_atts(
							array(
								'id'    => '',
								'class' => '',
								'style' => '',
							),
							$atts
						);

						$id    = $atts['id'];
						$class = $atts['class'];
						$style = $atts['style'];

						$upsell_product_image_src_div =
						"<div id='$id' class='wps_upsell_offer_product_image $class' style='$style'>
								<img src='$upsell_product_image_src'>
							</div>";

						return $upsell_product_image_src_div;
					}
				}
			} else {   // When not Live Offer.

				global $post;
				$offer_page_id = $post->ID;

				// Means this is Upsell offer template.
				$funnel_data = get_post_meta( $offer_page_id, 'wps_upsell_funnel_data', true );

				if ( ! empty( $funnel_data ) && is_array( $funnel_data ) ) {

					$funnel_id = $funnel_data['funnel_id'];
					$offer_id  = $funnel_data['offer_id'];

					if ( ! empty( $funnel_id ) && ! empty( $offer_id ) ) {

						$all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );

						$upsell_product_image_post_id = ! empty( $all_funnels[ $funnel_id ]['wps_upsell_offer_image'][ $offer_id ] ) ? $all_funnels[ $funnel_id ]['wps_upsell_offer_image'][ $offer_id ] : '';

						if ( ! empty( $upsell_product_image_post_id ) ) {

							$image_attributes = wp_get_attachment_image_src( $upsell_product_image_post_id, 'full' );

							$upsell_product_image_src = ! empty( $image_attributes[0] ) && filter_var( $image_attributes[0], FILTER_VALIDATE_URL ) ? $image_attributes[0] : false;
						}

						if ( ! empty( $upsell_product_image_src ) ) {

							// Shortcode attributes.
							$atts = shortcode_atts(
								array(
									'id'    => '',
									'class' => '',
									'style' => '',
								),
								$atts
							);

							$id    = $atts['id'];
							$class = $atts['class'];
							$style = $atts['style'];

							$upsell_product_image_src_div =
							"<div id='$id' class='wps_upsell_offer_product_image $class' style='$style'>
									<img src='$upsell_product_image_src'>
								</div>";

							return $upsell_product_image_src_div;
						}
					}
				}
			}

			$product_id = $this->get_upsell_product_id_for_shortcode();

			if ( ! empty( $product_id ) ) {

				$post_type = get_post_type( $product_id );

				if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {

					return '';
				}

				$upsell_product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'full' );

				$upsell_product_image_src = ! empty( $upsell_product_image[0] ) ? $upsell_product_image[0] : wc_placeholder_img_src();

				// Shortcode attributes.
				$atts = shortcode_atts(
					array(
						'id'    => '',
						'class' => '',
						'style' => '',
					),
					$atts
				);

				$id    = $atts['id'];
				$class = $atts['class'];
				$style = $atts['style'];

				$upsell_product_image_src_div =
				"<div id='$id' class='wps_upsell_offer_product_image $class' style='$style'>
						<img src='$upsell_product_image_src'>
					</div>";

				return $upsell_product_image_src_div;
			}
		}
	}

	/**
	 * Callback for Upsell product price html.
	 *
	 * @param mixed $upsell_product shortcode atributes.
	 * @param mixed $upsell_offered_discount shortcode atributes.
	 * @since 3.0.0
	 */
	public function get_variation_price_html( $upsell_product, $upsell_offered_discount ) {
		if ( ! empty( $upsell_product ) ) {

			$product_id = $upsell_product->get_id();
			$post_type  = get_post_type( $product_id );

			if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {

				return '';
			}

			// Apply discount on product.
			if ( ! empty( $upsell_offered_discount ) ) {

				$upsell_product = wps_upsell_change_product_price( $upsell_product, $upsell_offered_discount );
			} else {
				$upsell_product->set_price( 0 );
			}

			$upsell_product_price_html = $upsell_product->get_price_html();
			$upsell_product_price_html = ! empty( $upsell_product_price_html ) ? $upsell_product_price_html : '';

			/**
			 * Replaces the currency switcher fixed price.
			 */
			if ( ! empty( WC()->session ) && WC()->session->__isset( 's_selected_currency' ) ) {

				if ( function_exists( 'wps_wmcs_fixed_price_for_variable_sales_price' ) ) {
					$_regular_price = wps_wmcs_fixed_price_for_variable_regular_price( $upsell_product->get_id() );
					$_sale_price    = wps_wmcs_fixed_price_for_variable_sales_price( $upsell_product->get_id() );
				} else {
					$_regular_price = $upsell_product->get_regular_price();
					$_sale_price    = $upsell_product->get_price();
				}

				if ( empty( $upsell_offered_discount ) ) {
					$_sale_price = 'full_disc';
				}

				// In case of fixed custom price in currency switcher.
				if ( ! empty( $_regular_price ) || ! empty( $_sale_price ) ) {
					/**
						* Upsell offer will be zero then the offer price will not be changed.
						* In that case add sale price as the payable price.
						*/
					if ( empty( $upsell_offered_discount ) ) {
						if ( ! empty( $_sale_price ) && 'full_disc' !== $_sale_price ) {
							$upsell_product_price_html = wc_price( $_sale_price );
						} elseif ( 'full_disc' === $_sale_price ) {
							$upsell_product_price_html = wc_price( 0 );
						} else {
							$upsell_product_price_html = wc_price( $_regular_price );
						}
					} else {
						$upsell_product_price_html = wc_price( $upsell_product->get_price() );
					}
				}
			}

			if ( ! empty( WC()->session ) && WC()->session->__isset( 's_selected_currency' ) ) {
				$selected_currency = WC()->session->get( 's_selected_currency' );
				$store_currency    = get_woocommerce_currency();

				if ( $selected_currency !== $store_currency ) {
					$store_currency_symbol    = get_option( 'wps_mmcsfw_symbol_' . $store_currency );
					$selected_currency_symbol = get_option( 'wps_mmcsfw_symbol_' . $selected_currency );

					// Remove default currency into selected currency.
					$upsell_product_price_html = str_replace( $store_currency_symbol, $selected_currency_symbol, $upsell_product_price_html );
				}
			}
			return $upsell_product_price_html;
		}
	}


	/**
	 * Shortcode for Upsell product price.
	 *
	 * @param mixed $atts shortcode atributes.
	 * @since 3.0.0
	 */
	public function product_price_shortcode_content( $atts ) {
		$validate_shortcode = $this->validate_shortcode();

		if ( $validate_shortcode ) {

			$product_id = $this->get_upsell_product_id_for_shortcode();
			$sign_up_price_boolean = false;
			if ( ! empty( $product_id ) ) {

				$post_type = get_post_type( $product_id );

				if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {

					return '';
				}

				$upsell_product = wc_get_product( $product_id );

				// Get offer discount.
				$upsell_offered_discount = wps_upsell_get_product_discount();

				 $upsell_product_price_html_sign_up = $upsell_product->get_price_html();

				 $sign_up_price = get_post_meta( $upsell_product->get_id(), '_subscription_sign_up_fee', true );

				if ( $upsell_product->get_type() == 'subscription_variation' || $upsell_product->get_type() == 'subscription' && ! empty( $sign_up_price ) ) {

					if ( ! empty( $sign_up_price ) ) {

						// $upsell_product_price_html = str_replace($sign_up_price,$offer_price ,$upsell_product_price_html );

						$sign_up_price_boolean = true;

					}
				}

				if ( ! empty( $upsell_offered_discount ) ) {

					$upsell_product = wps_upsell_change_product_price( $upsell_product, $upsell_offered_discount );
					$upsell_product_price_html = $upsell_product->get_price_html();

				} else {
					$upsell_product->set_price( 0 );
				}

				// Apply discount on product.
				if ( ! empty( $sign_up_price_boolean ) ) {

					 $offer_price = $upsell_product->get_price();
					$upsell_product_price_html = str_replace( $sign_up_price, $offer_price, $upsell_product_price_html_sign_up );
				}

				$upsell_product_price_html = ! empty( $upsell_product_price_html ) ? $upsell_product_price_html : '';

				/**
				 * Replaces the currency switcher fixed price.
				 */
				if ( ! empty( WC()->session ) && WC()->session->__isset( 's_selected_currency' ) ) {

					if ( function_exists( 'wps_wmcs_fixed_price_for_simple_sales_price' ) ) {
						$_regular_price = wps_wmcs_fixed_price_for_simple_regular_price( $upsell_product->get_id() );
						$_sale_price    = wps_wmcs_fixed_price_for_simple_sales_price( $upsell_product->get_id() );
					} else {
						$_regular_price = $upsell_product->get_regular_price();
						$_sale_price    = $upsell_product->get_price();
					}

					if ( empty( $upsell_offered_discount ) ) {
						$_sale_price = 'full_disc';
					}

					// In case of fixed custom price in currency switcher.
					if ( ! empty( $_regular_price ) || ! empty( $_sale_price ) ) {
						/**
						 * Upsell offer will be zero then the offer price will not be changed.
						 * In that case add sale price as the payable price.
						 */
						if ( empty( $upsell_offered_discount ) ) {
							if ( ! empty( $_sale_price ) && 'full_disc' !== $_sale_price ) {
								$upsell_product_price_html = wc_format_sale_price( $_regular_price, $_sale_price );
							} elseif ( 'full_disc' === $_sale_price ) {
								$upsell_product_price_html = wc_format_sale_price( $_regular_price, 0 );
							} else {
								$upsell_product_price_html = wc_price( $_regular_price );
							}
						} else {
							$upsell_product_price_html = wc_format_sale_price( $_regular_price, $upsell_product->get_price() );
						}
					}
				}

				// Remove amount class, as it changes price css wrt theme change.
				$upsell_product_price_html = str_replace( ' amount', ' wps-upsell-amount', $upsell_product_price_html );

				// Shortcode attributes.
				$atts = shortcode_atts(
					array(
						'id'    => '',
						'class' => '',
						'style' => '',
					),
					$atts
				);

				$id    = $atts['id'];
				$class = $atts['class'];
				$style = $atts['style'];

				$upsell_product_price_html_div = "<div id='$id' class='wps_upsell_offer_product_price $class' style='$style'>$upsell_product_price_html</div>";

				if ( ! empty( WC()->session ) && WC()->session->__isset( 's_selected_currency' ) ) {
					$selected_currency = WC()->session->get( 's_selected_currency' );
					$store_currency    = get_woocommerce_currency();

					if ( $selected_currency !== $store_currency ) {
						$store_currency_symbol    = get_option( 'wps_mmcsfw_symbol_' . $store_currency );
						$selected_currency_symbol = get_option( 'wps_mmcsfw_symbol_' . $selected_currency );

						// Remove default currency into selected currency.
						$upsell_product_price_html_div = str_replace( $store_currency_symbol, $selected_currency_symbol, $upsell_product_price_html_div );
					}
				}

				return $upsell_product_price_html_div;
			}
		}
	}

	/**
	 * Shortcode for offer - Buy now button.
	 * Returns : Link :)
	 *
	 * Also Requires the ID to be applied on the link or button, for variable products only.
	 * Using this ID form is submitted from js.
	 *
	 * @since 3.0.0
	 */
	public function buy_now_shortcode_content() {
		$validate_shortcode = $this->validate_shortcode();

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		if ( $validate_shortcode ) {

			$product_id = $this->get_upsell_product_id_for_shortcode();

			if ( ! empty( $product_id ) ) {

				$post_type = get_post_type( $product_id );

				if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {

					return '';
				}

				if ( 'live_offer' === $validate_shortcode ) {

					$upsell_product = wc_get_product( $product_id );

					if ( $upsell_product->is_type( 'variable' ) ) {

						// In this case buy now form ( in variation selector shortcode ) will be posted from js.
						$buy_now_link = '#wps_upsell';
					} else {

						$wp_nonce  = isset( $_GET['ocuf_ns'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ns'] ) ) : '';
						$order_key = isset( $_GET['ocuf_ok'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) ) : '';
						$offer_id  = isset( $_GET['ocuf_ofd'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) ) : '';
						$funnel_id = isset( $_GET['ocuf_fid'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) ) : '';

						$buy_now_link = '?wps_wocuf_pro_buy=true&ocuf_ns=
						' . $wp_nonce . '&ocuf_ok=
						' . $order_key . '&ocuf_ofd=
						' . $offer_id . '&ocuf_fid=
						' . $funnel_id . '&product_id=
						' . $product_id . '&quantity=1';
					}
				} elseif ( 'admin_view' === $validate_shortcode ) {

					$buy_now_link = '#preview';
				}

				return $buy_now_link;
			}
		}
	}

	/**
	 * Shortcode for offer - No thanks button.
	 * Returns : Link :)
	 *
	 * @since 3.0.0
	 */
	public function no_thanks_shortcode_content() {
		$validate_shortcode = $this->validate_shortcode();

		if ( $validate_shortcode ) {

			$product_id = $this->get_upsell_product_id_for_shortcode();

			if ( ! empty( $product_id ) ) {

				$post_type = get_post_type( $product_id );

				if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {

					return '';
				}

				$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
				$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

				if ( ! $id_nonce_verified ) {
					wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
				}

				if ( 'live_offer' === $validate_shortcode ) {

					$wp_nonce  = isset( $_GET['ocuf_ns'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ns'] ) ) : '';
					$order_key = isset( $_GET['ocuf_ok'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) ) : '';
					$offer_id  = isset( $_GET['ocuf_ofd'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) ) : '';
					$funnel_id = isset( $_GET['ocuf_fid'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) ) : '';

					$no_thanks_link = add_query_arg(
						array(
							'ocuf_ns'  => $wp_nonce,
							'ocuf_fid' => $funnel_id,
							'ocuf_ok'  => $order_key,
							'ocuf_ofd' => $offer_id,
							'ocuf_th'  => '1',
						)
					);

				} elseif ( 'admin_view' === $validate_shortcode ) {

					$no_thanks_link = '#preview';
				}

				return $no_thanks_link;
			}
		}
	}

	/**
	 * Shortcode for offer - product variations.
	 *
	 * @param mixed $atts shortcode atributes.
	 * @since 3.0.0
	 */
	public function variations_selector_shortcode_content( $atts ) {
		$validate_shortcode = $this->validate_shortcode();

		if ( $validate_shortcode ) {

			$product_id = $this->get_upsell_product_id_for_shortcode();

			if ( ! empty( $product_id ) ) {

				$post_type = get_post_type( $product_id );

				if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {

					return '';
				}

				// Upsell product.
				$product = wc_get_product( $product_id );

				if ( $product->is_type( 'variable' ) ) {

					// Variations of upsell variable product.
					$variations = $product->get_available_variations();

					// Change all variations 'price html' according to discounted price.
					if ( ! empty( $variations ) ) {

						foreach ( $variations as &$v ) {

							if ( ! empty( $v['variation_id'] ) ) {

								$v_product = wc_get_product( $v['variation_id'] );

								// Get offer discount.
								$upsell_offered_discount = wps_upsell_get_product_discount();
								$v['price_html']         = $this->get_variation_price_html( $v_product, $upsell_offered_discount );

							}
						}
					}

					// Attributes of upsell variable product.
					$attributes = $product->get_variation_attributes();

					$unavailable_text = __( 'Please select other option.', 'one-click-upsell-funnel-for-woocommerce-pro' );

					// Shortcode attributes.
					$atts = shortcode_atts(
						array(
							'id'    => '',
							'class' => '',
							'style' => '',
						),
						$atts
					);

					$id    = $atts['id'];
					$class = $atts['class'];
					$style = $atts['style'];

					$variations_html =
					"<div id='$id' class='wps_upsell_offer_product_variations $class' style='$style'>";

					// Using this form is important otherwise out of stock variations are not hidden.
					$variations_html .= '<form class="variations_form cart" method="post" enctype="multipart/form-data" data-product_id=" ' . absint( $product_id ) . '" data-product_variations="' . htmlspecialchars( wp_json_encode( $variations ) ) . '">';

					$variations_html .= '<table class="variations"><tbody>';

					foreach ( $attributes as $attribute_name => $attribute_options ) {

						$variations_html .= '<tr><td class="label"><label for="' . sanitize_title( $attribute_name ) . '">' . wc_attribute_label( $attribute_name ) . '</label></td>';

						/**
						* Default attribute $selected which shows choose an option
						* if no default is set.
						*
						* Passing $attribute_options[0] will auto select
						* first available option in dropdown.
						*/
						$selected         = $product->get_variation_default_attribute( $attribute_name );
						$variations_html .= '<td class="value">';
						$variations_html .= $this->wps_wocuf_pro_variation_attribute_options(
							array(
								'options'   => $attribute_options,
								'attribute' => $attribute_name,
								'product'   => $product,
								'selected'  => $attribute_options[0],
								'class'     => 'wps_upsell_offer_variation_select wps_upsell_offer_variations',
							)
						);

						$variations_html .= '</td></tr>';
					}

					$variations_html .= '</tbody></table></form>';

					$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
					$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

					if ( ! $id_nonce_verified ) {
						wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
					}

					$wp_nonce  = isset( $_GET['ocuf_ns'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ns'] ) ) : '';
					$order_key = isset( $_GET['ocuf_ok'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) ) : '';
					$offer_id  = isset( $_GET['ocuf_ofd'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) ) : '';
					$funnel_id = isset( $_GET['ocuf_fid'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) ) : '';

					$variations_html .= '<form method="post" id="wps_upsell_offer_buy_now_form">
								<input type="hidden" name="ocuf_ns" value="' . $wp_nonce . '">
								<input type="hidden" name="ocuf_fid" value="' . $funnel_id . '">
								<input type="hidden" name="product_id" class="wps_upsell_offer_buy_now_pid" value="' . absint( $product_id ) . '">
								<input type="hidden" name="ocuf_ofd" value="' . $offer_id . '">
								<input type="hidden" name="ocuf_ok" value="' . $order_key . '">
								<div id="wps_upsell_offer_variation_attributes"></div>
								<input type="hidden" name="wps_wocuf_pro_buy" value="true">
								<input type="hidden" name="wps_wocuf_pro_quantity" value="1">
							</form>';

					$variations_html .= '</div>';

					$upsell_product_image     = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'full' );
					$upsell_product_image_src = ! empty( $upsell_product_image[0] ) ? $upsell_product_image[0] : wc_placeholder_img_src();

					$href_upsell          = '#wps_upsell';
					$href_upsell_disabled = '#wps_upsell_disabled';

					/**
					 * Add this script only once per reload, no matter how many
					 * times this shortcode is called.
					 *
					 * Add this only on a page.
					 */
					if ( is_page() && false === wp_cache_get( 'wps_upsell_variations_script' ) ) :

						/**
						 * Enqueue add-to-cart-variation script.
						 * Using this Woocommerce script here to hide out of stock variations.
						 */
						wp_enqueue_script( 'wc-add-to-cart-variation' );

						?>
						<script type="text/javascript">
							jQuery(document).ready(function($){

								var variations = <?php echo wp_json_encode( $variations ); ?>;
								var unavailable_text = <?php echo wp_json_encode( $unavailable_text ); ?>;
								var default_image = <?php echo wp_json_encode( $upsell_product_image_src ); ?>;
								var href_upsell = <?php echo wp_json_encode( $href_upsell ); ?>;
								var href_upsell_disabled = <?php echo wp_json_encode( $href_upsell_disabled ); ?>;

								$('.wps_upsell_offer_variation_select').change(function(){

									var options_selected = false;
									var onetimeflag = true;
									var selectors = {};

									var $variation_price = jQuery( '.wps_upsell_offer_product_price');

									var $variation_image_img = jQuery( '.wps_upsell_offer_product_image img' );

									var $variation_id = jQuery( '.wps_upsell_offer_buy_now_pid' );

									jQuery('.wps_upsell_offer_variation_select').each(function(){

										var $this = jQuery(this);

										if( $this.val() != null && $this.val().length == 0 ) {

											onetimeflag = false;
											options_selected = false;

											$variation_price.html(unavailable_text);
											$variation_image_img.attr( 'src', default_image );

											// Disable Buy Now :(
											$('a[href="' + href_upsell + '"]').attr( 'href', href_upsell_disabled );
											$('a[href="' + href_upsell_disabled + '"]').css( 'pointer-events', 'none' );
											$('a[href="' + href_upsell_disabled + '"]').css( 'cursor', 'default' );

										} else {

											options_selected = true;
											selectors[$this.attr('name')] = $this.val();
										}
									});

									var matching_variations = wc_variation_form_matcher.find_matching_variations( variations, selectors );

									if( onetimeflag == true && options_selected == true ) {

										var selected_variation = {};

										selected_variation = matching_variations.shift();

										if( typeof selected_variation != 'undefined' ) {

											if( selected_variation.is_purchasable == true && selected_variation.is_in_stock == 1 ) {    

												/**
												 * Save current selected attributes.
												 * 
												 * As if particular variation has any
												 * attribute left as 'Any..' in backend 
												 * then that selected attribute has no value
												 * in order item meta so we've add that 
												 * by code.
												 */
												var attr_array = {};

												for ( var key in selectors ) {

													// skip loop if the property is from prototype
													if ( ! selectors.hasOwnProperty( key ) ) continue;

													attr_array[key] = selectors[key];

												}

												var attr_array_string = JSON.stringify(attr_array);

												attr_array_string = attr_array_string.replace(/"/g , "'");

												var variation_attributes_div = $('#wps_upsell_offer_variation_attributes');

												// Add selected attributes array as string in input type hidden to be retrieved on form submit.
												variation_attributes_div.html( '<input type="hidden" name="wocuf_var_attb" value="' + attr_array_string + '" >' );

												if( selected_variation.image.full_src ) {

													$variation_image_img.attr( 'src', selected_variation.image.full_src );
												}

												else {

													$variation_image_img.attr( 'src', default_image );
												}

												$variation_id.val( selected_variation.variation_id );

												// Remove amount class from price html.
												var variation_price_html = selected_variation.price_html.replace( ' amount', ' wps-upsell-amount' );
												$variation_price.html( variation_price_html );

												// Enable Buy Now :)
												$('a[href="' + href_upsell_disabled + '"]').attr( 'href', href_upsell );
												$('a[href="' + href_upsell + '"]').css( 'pointer-events', 'visible' );
												$('a[href="' + href_upsell + '"]').css( 'cursor', 'pointer' );
											}

											else {

												if( selected_variation.image.full_src ) {

													$variation_image_img.attr( 'src', selected_variation.image.full_src );
												}

												else {

													$variation_image_img.attr( 'src', default_image );
												}

												$variation_price.html( unavailable_text );

												// Disable Buy Now.
												$('a[href="' + href_upsell + '"]').attr( 'href', href_upsell_disabled );
												$('a[href="' + href_upsell_disabled + '"]').css( 'pointer-events', 'none' );
												$('a[href="' + href_upsell_disabled + '"]').css( 'cursor', 'default' );
											}
										} else {

											$variation_image_img.attr( 'src', default_image );
											$variation_price.html( unavailable_text );

											// Disable Buy Now.
											$('a[href="' + href_upsell + '"]').attr( 'href', href_upsell_disabled );
											$('a[href="' + href_upsell_disabled + '"]').css( 'pointer-events', 'none' );
											$('a[href="' + href_upsell_disabled + '"]').css( 'cursor', 'default' );
										}
									}
								});

								// To automatically set values ( image and price ) according to selected variations on doc ready.
								$( '.wps_upsell_offer_variation_select' ).trigger( 'change' );

								// Form submit - add this variation to order.
								$(document).on('click', 'a[href="' + href_upsell + '"]', function(e) {

									var href = $(this).attr('href');

									if( href_upsell == href ) {

										e.preventDefault();

										$('#wps_upsell_offer_buy_now_form').submit();
									}
								});

								var selected_val = '';
								var attr_name = '';

								// When this shortcode is used at multiple places then they all will change together.
								$(document).on( 'change', '.wps_upsell_offer_variations', function() {

									selected_val = $(this).val();
									attr_name = $(this).attr('data-attribute_name');

									$( 'select[data-attribute_name="' + attr_name + '"]' ).removeClass( 'wps_upsell_offer_variations' );

									$( 'select[data-attribute_name="' + attr_name + '"]' ).val( selected_val );

									$( 'select[data-attribute_name="' + attr_name + '"]' ).trigger( 'change' );

									// This will cause problems as we only need the changed one to be triggered.
									$( 'select[data-attribute_name="' + attr_name + '"]' ).addClass( 'wps_upsell_offer_variations' );
								});
							});

							var wc_variation_form_matcher = {
								find_matching_variations: function( product_variations, settings ) {
									var matching = [];
									for ( var i = 0; i < product_variations.length; i++ ) {
										var variation    = product_variations[i];

										if ( wc_variation_form_matcher.variations_match( variation.attributes, settings ) ) {
											matching.push( variation );
										}
									}
									return matching;
								},
								variations_match: function( attrs1, attrs2 ) {
									var match = true;
									for ( var attr_name in attrs1 ) {
										if ( attrs1.hasOwnProperty( attr_name ) ) {
											var val1 = null != attrs1[ attr_name ] ? attrs1[ attr_name ] : '';
											var val2 = null != attrs2[ attr_name ] ? attrs2[ attr_name ] : '';
											if ( val1 != undefined && val2 != undefined && val1.length != 0 && val2.length != 0 && val1 != val2 ) {
												match = false;
											}
										}
									}
									return match;
								}
							};
						</script>
						<?php

					endif;

					wp_cache_set( 'wps_upsell_variations_script', 'true' );

					return $variations_html;
				}
			}
		}
	}

	/**
	 * Shortcode for star review.
	 * Returns : star review html.
	 *
	 * @param mixed $atts shortcode atributes.
	 * @since 3.0.0
	 */
	public function product_star_review( $atts ) {
		$stars = ! empty( $atts['stars'] ) ? abs( $atts['stars'] ) : '5';

		$stars = ( $stars >= 1 && $stars <= 5 ) ? $stars : '5';

		$stars_percent = $stars * 20;

		$review_html = '<div class="wps-upsell-star-rating"><span style="width: ' . $stars_percent . '%;"></div>';

		return $review_html;

	}

	/**
	 * Shortcode for offer - Timer button.
	 * Returns : html :)
	 *
	 * @param mixed $atts shortcode atributes.
	 * @since 3.5.0
	 */
	public function timer_shortcode_content( $atts ) {
		$validate_shortcode = $this->validate_shortcode();

		if ( $validate_shortcode ) {

			$minutes    = ! empty( $atts['minutes'] ) ? abs( $atts['minutes'] ) : 5;
			$expiration = $minutes * 60;

			if ( empty( $expiration ) || ! is_numeric( $expiration ) ) {

				return esc_html__( 'Time is not specified correctly.', 'one-click-upsell-funnel-for-woocommerce-pro' );
			}

			$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
			$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

			if ( ! $id_nonce_verified ) {
				wp_die( esc_html__( 'Nonce Not verified', ' woo-one-click-upsell-funnel-pro' ) );
			}

			?>

			<?php ob_start(); ?>

			<?php if ( false === wp_cache_get( 'wps_upsell_countdown_timer' ) ) : ?>

				<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> <?php //phpcs:ignore ?>

				<script type="text/javascript">

					jQuery(document).ready(function($) {

						// Set the date we're counting down to.
						var current = new Date();
						var expiration = parseFloat( <?php echo( esc_html( $expiration ) ); ?> ); // Digit in seconds.
						var offer_id = <?php echo ! empty( $_GET['ocuf_ofd'] ) ? esc_html( sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) ) ) : 'null'; ?>;

						var timer_limit = sessionStorage.getItem( 'timerlimit_' + offer_id );
						var countDowntime = null != offer_id && null != timer_limit ? timer_limit : current.setSeconds( current.getSeconds()+expiration );

						// Update the count down every 1 second.
						var  timer  = setInterval(function() {

							// Find the distance between now and the count down time.
							var distance = countDowntime - new Date().getTime();

							// Time calculations for days, hours, minutes and seconds
							var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
							var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
							var seconds = Math.floor((distance % (1000 * 60)) / 1000);

							// If the count down is finished, redirect;
							if ( distance < 0 ) {

								clearInterval( timer );

								// Expired the session before redirecting.
								$( 'a' ).each(function() {

									if( this.href.includes( 'ocuf_th' ) ) {

										jQuery( this )[0].click();
									}
								});

							} else {

								if( seconds.toString().length == '1' ) {

									seconds = '0' + seconds;

								} 

								if( minutes.toString().length == '1' ) {

									minutes = '0' + minutes;

								}

								$('.wps_upsell_lite_display_minutes').html( minutes );
								$('.wps_upsell_lite_display_seconds').html( seconds );

							}

						}, 300 );

						sessionStorage.setItem( 'timerlimit_' + offer_id, countDowntime );
					});

				</script>

				<?php wp_cache_set( 'wps_upsell_countdown_timer', 'true' ); ?>

			<?php endif; ?>

			<!-- Countdown timer html. -->
			<span class="wps_upsell_lite_display_timer_wrap">
				<span class="wps_upsell_lite_timer_digit">
					<span class="wps_upsell_lite_display_minutes wps_upsell_lite_display_timer">00</span>
					<span class="wps_upsell_lite_text"><?php esc_html_e( 'minutes', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
				</span>
				<span class="wps_upsell_lite_timer_digit">
					<span class="wps_upsell_lite_display_timer_col">:</span>
				</span>
				<span class="wps_upsell_lite_timer_digit">
					<span class="wps_upsell_lite_display_seconds wps_upsell_lite_display_timer">00</span>
					<span class="wps_upsell_lite_text"><?php esc_html_e( 'seconds', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
				</span>
			</span>

			<?php

			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}
	}


	/**
	 * Shortcode for quantity.
	 * Returns : html :)
	 *
	 * Shows woocommerce quantity field.
	 *
	 * @param mixed $atts shortcode atributes.
	 * @since 3.5.0
	 */
	public function quantity_shortcode_content( $atts ) {
		$validate_shortcode = $this->validate_shortcode();

		if ( $validate_shortcode ) {

			$maximum = ! empty( $atts['max'] ) ? abs( $atts['max'] ) : 3;
			$minimum = ! empty( $atts['min'] ) ? abs( $atts['min'] ) : 1;

			$product_id = $this->get_upsell_product_id_for_shortcode();

			if ( ! empty( $product_id ) ) {

				$post_type = get_post_type( $product_id );
				$product   = wc_get_product( $product_id );

				if ( empty( $product ) ) {

					return '';
				}

				if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {

					return '';
				}

				ob_start();
				?>

				<!-- Quantity timer html. -->
				<div class="wps_upsell_quantity quantity">
					<label class="screen-reader-text" for="wps_upsell_quantity_field"><?php echo esc_html( $product->get_title() ); ?></label>
					<input type="number" id="wps_upsell_quantity_field" class="input-text qty text wps_upsell_quantity_input" step="1" min="<?php echo( esc_html( $minimum ) ); ?>" max="<?php echo( esc_html( $maximum ) ); ?>" value="1" title="Qty" inputmode="numeric">
				</div>

				<?php

				$output = ob_get_contents();
				ob_end_clean();

				return $output;
			}
		}
	}

	/**
	 * Hide upsell Items meta string.
	 *
	 * @param mixed $formatted_meta formatted meta.
	 * @since 3.5.0
	 */
	public function hide_order_item_formatted_meta_data( $formatted_meta ) {
		foreach ( $formatted_meta as $key => $meta ) {

			if ( ! empty( $meta->key ) && 'is_upsell_purchase' === $meta->key ) {

				unset( $formatted_meta[ $key ] );
			}
		}

		return $formatted_meta;
	}


	/**
	 * Delete the Timer data in browser session for Timer shortcode.
	 *
	 * @since 3.5.0
	 */
	public function reset_timer_session_data() {
		// Do this only on thank you page.
		if ( ! is_wc_endpoint_url( 'order-received' ) && ! is_wc_endpoint_url( apply_filters( 'wps_wocuf_custom_thankyou_page_endpoint', 'order-received' ) ) ) {

			return;
		}

		?>

		<script type="text/javascript">

			// Clear timestamp from SessionStorage.
			if( typeof sessionStorage != 'undefined' && sessionStorage.length > 0 ) {

				// Must reduce these variable.
				sessionStorage.removeItem( 'timerlimit_1' );
				sessionStorage.removeItem( 'timerlimit_null' );

				for ( var i = 0; i < sessionStorage.length; i++ ) {

					if( sessionStorage.key(i).search( 'timerlimit_' ) == 0 ) {

						sessionStorage.removeItem( sessionStorage.key(i) );
					}
				}
			}

		</script>
		<?php
	}


	/**
	 * Shortcode for Default Gutenberg offer identification.
	 * Returns : empty string.
	 *
	 * @since 3.0.0
	 */
	public function default_offer_identification() {
		return '';

	}

	/**
	 * Creating shortcode for special price on custom page.
	 *
	 * @since 1.0.0
	 * @param mixed $atts    attributes of the shortcode.
	 * @param mixed $content content under wrapping mode.
	 */
	public function wps_wocuf_pro_custom_page_product_offer_price( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'style' => '',
				'class' => '',
			),
			$atts
		);

		return $this->wps_wocuf_pro_custom_page_offer_price_for_all(
			array(
				'style' => $atts['style'],
				'class' => $atts['class'],
			),
			$content
		);
	}

	/**
	 * Creating shortcode for yes link on custom page.
	 *
	 * @since 1.0.0
	 * @param mixed $atts    attributes of the shortcode.
	 * @param mixed $content content under wrapping mode.
	 */
	public function wps_wocuf_pro_custom_page_action_link_yes( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'style' => '',
				'class' => '',
			),
			$atts
		);

		return $this->wps_wocuf_pro_custom_page_yes_link_for_all(
			array(
				'style' => $atts['style'],
				'class' => $atts['class'],
			),
			$content
		);
	}

	/**
	 * Creating shortcode for showing order details page.
	 *
	 * @since 1.0.0
	 * @param mixed $atts    attributes of the shortcode.
	 * @param mixed $content content under wrapping mode.
	 */
	public function wps_wocuf_pro_custom_page_order_details_link( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'style' => '',
				'class' => '',
			),
			$atts
		);

		return $this->wps_wocuf_pro_custom_page_order_details_link_for_all(
			array(
				'style' => $atts['style'],
				'class' => $atts['class'],
			),
			$content
		);
	}

	/**
	 * Creating shortcode for yes link on custom page.
	 *
	 * @since 1.0.0
	 * @param mixed $atts    attributes of the shortcode.
	 * @param mixed $content content under wrapping mode.
	 */
	public function wps_wocuf_pro_custom_page_selector( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'style' => '',
				'class' => '',
			),
			$atts
		);

		return $this->wps_wocuf_pro_custom_page_variation_selector(
			array(
				'style' => $atts['style'],
				'class' => $atts['class'],
			),
			$content
		);
	}


	/**
	 * Custom page order Details.
	 *
	 * @param  [type] $atts    atributes.
	 * @param  string $content content.
	 * @return $result
	 */
	public function wps_wocuf_pro_custom_page_order_details_link_for_all( $atts, $content = '' ) {
		$result = '';

		if ( empty( $atts['style'] ) ) {
			$atts['style'] = '';
		}

		if ( empty( $atts['class'] ) ) {
			$atts['class'] = '';
		}

		if ( empty( $content ) ) {
			$content = __( 'Show Order Details', 'one-click-upsell-funnel-for-woocommerce-pro' );
			$content = apply_filters( 'wps_wocuf_pro_order_details_link_text', $content );
		}

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$order_key = isset( $_GET['ocuf_ok'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) ) : '';

		$order_id = wc_get_order_id_by_order_key( $order_key );

		$order_received_url = wc_get_endpoint_url( 'order-received', $order_id, wc_get_page_permalink( 'checkout' ) );

		$order_received_url = add_query_arg( 'key', $order_key, $order_received_url );

		$result = '<a href="' . $order_received_url . '"
		 class="button' . $atts['class'] . '"
		  style="' . $atts['style'] . '"
		  >' . $content . '</a>';

		return $result;
	}


	/**
	 * Custom page price.
	 *
	 * @param mixed $atts    atributes.
	 * @param mixed $content content.
	 */
	public function wps_wocuf_pro_custom_page_offer_price_for_all( $atts, $content = '' ) {
		$result = '';

		if ( empty( $atts['style'] ) ) {
			$atts['style'] = '';
		}

		if ( empty( $atts['class'] ) ) {
			$atts['class'] = '';
		}

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$offer_id = isset( $_GET['ocuf_ofd'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) ) : '';

		$funnel_id = isset( $_GET['ocuf_fid'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) ) : '';

		$order_key = isset( $_GET['ocuf_ok'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) ) : '';

		$wp_nonce = isset( $_GET['ocuf_ns'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ns'] ) ) : '';

		$order_id = wc_get_order_id_by_order_key( $order_key );

		$wps_wocuf_pro_all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );

		$wps_wocuf_pro_before_offer_price_text = get_option( 'wps_wocuf_pro_before_offer_price_text', __( 'Special Offer Price', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

		$wps_wocuf_pro_offered_products = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'] : array();

		$wps_wocuf_pro_offered_discount = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'] : array();

		$wps_wocuf_pro_single_offered_product = '';

		if ( ! empty( $wps_wocuf_pro_offered_products[ $offer_id ] ) ) {

			// In v2.0.0, it was array so handling to get the first product id.
			if ( is_array( $wps_wocuf_pro_offered_products[ $offer_id ] ) && count( $wps_wocuf_pro_offered_products[ $offer_id ] ) ) {

				foreach ( $wps_wocuf_pro_offered_products[ $offer_id ] as $handling_offer_product_id ) {

					$wps_wocuf_pro_single_offered_product = absint( $handling_offer_product_id );
					break;
				}
			} else {

				$wps_wocuf_pro_single_offered_product = absint( $wps_wocuf_pro_offered_products[ $offer_id ] );
			}
		}

		if ( ! empty( $wps_wocuf_pro_single_offered_product ) ) {

			$wps_wocuf_pro_original_offered_product = wc_get_product( $wps_wocuf_pro_single_offered_product );

			$wps_wocuf_pro_offered_product = wps_upsell_change_product_price( $wps_wocuf_pro_original_offered_product, $wps_wocuf_pro_offered_discount[ $offer_id ] );

			$product = $wps_wocuf_pro_offered_product;

			$result .= '<div style="' . $atts['style'] . '"
			 class="wps_wocuf_pro_custom_offer_price ' . $atts['class'] . '">
			 ' . $wps_wocuf_pro_before_offer_price_text . ' :
				 ' . $product->get_price_html() . '</div>';

		} else {
			$result .= '<div style="' . $atts['style'] . '" 
			class="wps_wocuf_pro_custom_offer_price ' . $atts['class'] . '">
			' . $content . '</div>';
		}

		return $result;
	}

	/**
	 * Creating shortcode for yes link on custom page for simple as well as variable product
	 *
	 * @since 1.0.0
	 * @param mixed $atts    atributes.
	 * @param mixed $content content.
	 */
	public function wps_wocuf_pro_custom_page_yes_link_for_all( $atts, $content = '' ) {
		$result = '';

		if ( empty( $atts[0] ) ) {
			$atts[0] = 'yes';
		}

		if ( empty( $atts['style'] ) ) {
			$atts['style'] = '';
		}

		if ( empty( $atts['class'] ) ) {
			$atts['class'] = '';
		}

		$wps_wocuf_pro_all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );

		$wps_wocuf_pro_buy_text = get_option( 'wps_wocuf_pro_buy_text', __( 'Add to my order', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

		if ( empty( $content ) ) {
			$content = $wps_wocuf_pro_buy_text;
		}

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$offer_id = isset( $_GET['ocuf_ofd'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) ) : '';

		$funnel_id = isset( $_GET['ocuf_fid'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) ) : '';

		$order_key = isset( $_GET['ocuf_ok'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) ) : '';

		$wp_nonce = isset( $_GET['ocuf_ns'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ns'] ) ) : '';

		$order_id = wc_get_order_id_by_order_key( $order_key );

		$wps_wocuf_pro_offered_products = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'] : array();

		$wps_wocuf_pro_offered_discount = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'] : array();

		$wps_wocuf_pro_single_offered_product = '';

		if ( ! empty( $wps_wocuf_pro_offered_products[ $offer_id ] ) ) {

			// In v2.0.0, it was array so handling to get the first product id.
			if ( is_array( $wps_wocuf_pro_offered_products[ $offer_id ] ) && count( $wps_wocuf_pro_offered_products[ $offer_id ] ) ) {

				foreach ( $wps_wocuf_pro_offered_products[ $offer_id ] as $handling_offer_product_id ) {

					$wps_wocuf_pro_single_offered_product = absint( $handling_offer_product_id );
					break;
				}
			} else {

				$wps_wocuf_pro_single_offered_product = absint( $wps_wocuf_pro_offered_products[ $offer_id ] );
			}
		}

		if ( ! empty( $wps_wocuf_pro_single_offered_product ) ) {

			$wps_wocuf_pro_original_offered_product = wc_get_product( $wps_wocuf_pro_single_offered_product );

			$wps_wocuf_pro_offered_product = wps_upsell_change_product_price( $wps_wocuf_pro_original_offered_product, $wps_wocuf_pro_offered_discount[ $offer_id ] );

			$product = $wps_wocuf_pro_offered_product;

			$result .= '<form method="post" class="wps_wocuf_pro_custom_offer">
							<input type="hidden" name="ocuf_ns" value="' . $wp_nonce . '">
							<input type="hidden" name="ocuf_fid" value="' . $funnel_id . '">
							<input type="hidden" name="product_id" class="wps_wocuf_pro_variation_id" value="' . absint( $product->get_id() ) . '">
							<input type="hidden" name="ocuf_ofd" value="' . $offer_id . '">
							<input type="hidden" name="ocuf_ok" value="' . $order_key . '">
							<button style="' . $atts['style'] . '" class="wps_wocuf_pro_custom_buy ' . $atts['class'] . '" type="submit" onclick="" name="wps_wocuf_pro_buy">' . $content . '</button>
						</form>';

		} else {
			$result .= '<form method="post" class="wps_wocuf_pro_custom_offer">
						<input type="hidden" name="ocuf_ns" value="' . $wp_nonce . '">
						<input type="hidden" name="ocuf_fid" value="' . $funnel_id . '">
						<input type="hidden" name="product_id" class="wps_wocuf_pro_variation_id" value="">
						<input type="hidden" name="ocuf_ofd" value="' . $offer_id . '">
						<input type="hidden" name="ocuf_ok" value="' . $order_key . '">
						<button style="' . $atts['style'] . '" class="wps_wocuf_pro_custom_buy ' . $atts['class'] . '" type="submit" name="wps_wocuf_pro_buy">' . $content . '</button>
					</form>';
		}

		return $result;
	}

	/**
	 * Creating shortcode for thanks link on custom page.
	 *
	 * @since 1.0.0
	 * @param mixed $atts    attributes of the shortcode.
	 * @param mixed $content content under wrapping mode.
	 */
	public function wps_wocuf_pro_custom_page_action_link_no( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
				'style' => '',
				'class' => '',
			),
			$atts
		);

		return $this->wps_wocuf_pro_custom_page_no_link_for_all(
			array(
				'style' => $atts['style'],
				'class' => $atts['class'],
			),
			$content
		);
	}

	/**
	 * Creating shortcode for thanks link on custom page for simple as well variable product.
	 *
	 * @since 1.0.0
	 * @param mixed $atts    attributes of the shortcode.
	 * @param mixed $content content under wrapping mode.
	 */
	public function wps_wocuf_pro_custom_page_no_link_for_all( $atts, $content = '' ) {
		$result = '';

		if ( empty( $atts['style'] ) ) {
			$atts['style'] = '';
		}

		if ( empty( $atts['class'] ) ) {
			$atts['class'] = '';
		}

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$offer_id = isset( $_GET['ocuf_ofd'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) ) : '';

		$funnel_id = isset( $_GET['ocuf_fid'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) ) : '';

		$order_key = isset( $_GET['ocuf_ok'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) ) : '';

		$wp_nonce = isset( $_GET['ocuf_ns'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ns'] ) ) : '';

		$order_id = wc_get_order_id_by_order_key( $order_key );

		$th = 1;

		$wps_wocuf_pro_no_text = get_option( 'wps_wocuf_pro_no_text', __( 'No,thanks', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

		if ( empty( $content ) ) {
			$content = $wps_wocuf_pro_no_text;
		}

		if ( ! empty( $offer_id ) && ! empty( $order_key ) && ! empty( $wp_nonce ) ) {
			$result .= '<a style="' . $atts['style'] . '" class="wps_wocuf_pro_no wps_wocuf_pro_custom_skip 
			' . $atts['class'] . '" href="?ocuf_ns=
			' . $wp_nonce . '&ocuf_th=1&ocuf_ok=
			' . $order_key . '&ocuf_ofd=
			' . $offer_id . '&ocuf_fid=
			' . $funnel_id . '">
			' . $content . '</a>';
		} else {
			$result .= '<a style="' . $atts['style'] . '" 
			class="wps_wocuf_pro_custom_skip ' . $atts['class'] . '"
			 href="">' . $content . '</a>';
		}

		return $result;
	}

	/**
	 * Creating shortcode for thanks link on custom page for simple as well variable product.
	 *
	 * @since 1.0.0
	 * @param mixed $atts attributes of the shortcode.
	 */
	public function wps_wocuf_pro_custom_page_variation_selector( $atts ) {
		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$result = '';

		if ( empty( $atts['style'] ) ) {
			$atts['style'] = '';
		}

		if ( empty( $atts['class'] ) ) {
			$atts['class'] = '';
		}

		$wps_wocuf_pro_all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );

		$wps_wocuf_pro_before_offer_price_text = get_option( 'wps_wocuf_pro_before_offer_price_text', __( 'Special Offer Price', 'one-click-upsell-funnel-for-woocommerce-pro' ) );

		$offer_id = isset( $_GET['ocuf_ofd'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) ) : '';

		$funnel_id = isset( $_GET['ocuf_fid'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) ) : '';

		$order_key = isset( $_GET['ocuf_ok'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) ) : '';

		$wp_nonce = isset( $_GET['ocuf_ns'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ns'] ) ) : '';

		$order_id = wc_get_order_id_by_order_key( $order_key );

		$wps_wocuf_pro_offered_products = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'] : array();

		$wps_wocuf_pro_offered_discount = isset( $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'] ) ? $wps_wocuf_pro_all_funnels[ $funnel_id ]['wps_wocuf_pro_offer_discount_price'] : array();

		$wps_wocuf_pro_single_offered_product = '';

		if ( ! empty( $wps_wocuf_pro_offered_products[ $offer_id ] ) ) {

			// In v2.0.0, it was array so handling to get the first product id.
			if ( is_array( $wps_wocuf_pro_offered_products[ $offer_id ] ) && count( $wps_wocuf_pro_offered_products[ $offer_id ] ) ) {

				foreach ( $wps_wocuf_pro_offered_products[ $offer_id ] as $handling_offer_product_id ) {

					$wps_wocuf_pro_single_offered_product = absint( $handling_offer_product_id );
					break;
				}
			} else {

				$wps_wocuf_pro_single_offered_product = absint( $wps_wocuf_pro_offered_products[ $offer_id ] );
			}
		}

		if ( ! empty( $wps_wocuf_pro_single_offered_product ) ) {

			$wps_wocuf_pro_original_offered_product = wc_get_product( $wps_wocuf_pro_single_offered_product );

			if ( $wps_wocuf_pro_original_offered_product->is_type( 'variable' ) ) {
				$product = $wps_wocuf_pro_original_offered_product;

				$variations = $product->get_available_variations();

				if ( ! empty( $variations ) ) {
					foreach ( $variations as &$v ) {
						if ( ! empty( $v['variation_id'] ) ) {
							$v_product       = wc_get_product( $v['variation_id'] );
							$v_product       = wps_upsell_change_product_price( $v_product, $wps_wocuf_pro_offered_discount[ $offer_id ] );
							$v['price_html'] = $v_product->get_price_html();
						}
					}
				}

				$attributes = $product->get_variation_attributes();

				$wps_wocuf_pro_unavailable_text = __( '<p>Please select an option.</p>', 'one-click-upsell-funnel-for-woocommerce-pro' );

				$wps_wocuf_pro_unavailable_text = apply_filters( 'wps_wocuf_pro_options_unavailability_text', $wps_wocuf_pro_unavailable_text );

				$result .= '<form class="variations_form cart" method="post" enctype="multipart/form-data" data-product_id=" ' . absint( $product->get_id() ) . '" data-product_variations="' . htmlspecialchars( wp_json_encode( $variations ) ) . '">';

				$result .= '<table class="variations"><tbody>';

				foreach ( $attributes as $attribute_name => $attribute_options ) {
					$result .= '<tr><td class="label"><label for="' . sanitize_title( $attribute_name ) . '">' . wc_attribute_label( $attribute_name ) . '</label></td>';

					/**
					 * Default attribute $selected which shows choose an option
					 * if no default is set.
					 *
					 * Passing $attribute_options[0] will auto select
					 * first available option in dropdown.
					 */
					$selected = $product->get_variation_default_attribute( $attribute_name );
					$result  .= '<td class="value">';
					$result  .= $this->wps_wocuf_pro_variation_attribute_options(
						array(
							'options'   => $attribute_options,
							'attribute' => $attribute_name,
							'product'   => $product,
							'selected'  => $attribute_options[0],
							'class'     => 'wps_wocuf_offer_variation_select',
						)
					);
					$result  .= '</td></tr>';
				}

				$result .= '</tbody></table></form>';

				// Enqueue add-to-cart-variation script.
				// Using Woocommerce script here for hiding out of stock variations.
				wp_enqueue_script( 'wc-add-to-cart-variation' );

				?>
				<script type="text/javascript">
					jQuery(document).ready(function($){
						var variations = <?php echo wp_json_encode( $variations ); ?>;
						var before_price_text = <?php echo wp_json_encode( $wps_wocuf_pro_before_offer_price_text ); ?>;
						var unavailable_text = <?php echo wp_json_encode( $wps_wocuf_pro_unavailable_text ); ?>;
						jQuery('.wps_wocuf_offer_variation_select').change(function(){
							var options_selected = false;
							var selectors = {};
							var $variation_price_box = jQuery( '.wps_wocuf_pro_custom_offer_price');
							var $variation_image_img = jQuery( '.wps_wocuf_pro_product_image img' );
							var $variation_id = jQuery( '.wps_wocuf_pro_variation_id' );
							var $variation_price = jQuery( '.wps_wocuf_pro_custom_offer_price');
							jQuery('.wps_wocuf_offer_variation_select').each(function(){
								var $this = jQuery(this);
								if( $this.val() != null && $this.val().length == 0 )
								{
									options_selected = false;
									$variation_price_box.removeClass( 'wps_wocuf_pro_hide' );
									$variation_price_box.addClass( 'wps_wocuf_pro_display' );
									$variation_price.html( unavailable_text );
									jQuery( '.wps_wocuf_pro_custom_buy' ).prop( 'disabled', true );
								}
								else
								{
									options_selected = true;
									selectors[$this.attr('name')] = $this.val();
								}
							});

							var matching_variations = wc_variation_form_matcher.find_matching_variations( variations, selectors );

							if( options_selected == true ) {

								var selected_variation = {};

								selected_variation = matching_variations.shift();

								if( typeof selected_variation != 'undefined' )
								{
									if( selected_variation.is_purchasable == true && selected_variation.is_in_stock == 1 )
									{    
										/**
										 * Save current selected attributes.
										 * 
										 * As if particular variation has any
										 * attribute left as 'Any..' in backend 
										 * then that selected attribute has no value
										 * in order item meta so we've add that 
										 * by code.
										 */
										var attr_array = {};

										for ( var key in selectors ) {

											// skip loop if the property is from prototype
											if ( ! selectors.hasOwnProperty( key ) ) continue;

											attr_array[key] = selectors[key];

										}

										var attr_array_string = JSON.stringify(attr_array);

										attr_array_string = attr_array_string.replace(/"/g , "'");

										var variation_attributes_div = $('#wps_wocuf_pro_variation_attributes');

										// Add selected attributes array as string in input type hidden to be retrieved on form submit.
										variation_attributes_div.html( '<input type="hidden" name="wocuf_var_attb" value="' + attr_array_string + '" >' );


										$variation_price_box.removeClass( 'wps_wocuf_pro_hide' );
										$variation_price_box.addClass( 'wps_wocuf_pro_display' );

										if( selected_variation.image.full_src ) {

											$variation_image_img.attr('src',selected_variation.image.full_src);
										}
										else
										{
											$variation_image_img.attr('src', '' );
										}

										$variation_id.val( selected_variation.variation_id );
										$variation_price.html( before_price_text + ' : ' +selected_variation.price_html );
										jQuery( '.wps_wocuf_pro_custom_buy' ).prop( 'disabled', false );
									}
									else
									{
										$variation_price_box.removeClass( 'wps_wocuf_pro_hide' );
										$variation_price_box.addClass( 'wps_wocuf_pro_display' );

										if( selected_variation.image.full_src ) {

											$variation_image_img.attr( 'src', selected_variation.image.full_src );
										}
										else
										{
											$variation_image_img.attr( 'src', '' );
										}

										$variation_price.html( unavailable_text );
										jQuery( '.wps_wocuf_pro_custom_buy' ).prop( 'disabled', true );
									}
								}
								else
								{
									$variation_price_box.removeClass( 'wps_wocuf_pro_hide' );
									$variation_price_box.addClass( 'wps_wocuf_pro_display' );
									$variation_image_img.attr( 'src', '' );
									$variation_price.html( unavailable_text );
									jQuery( '.wps_wocuf_pro_custom_buy' ).prop( 'disabled',  true );
								}
							}
						});

						jQuery( '.wps_wocuf_offer_variation_select' ).trigger( 'change' );
					});

					var wc_variation_form_matcher = {
						find_matching_variations: function( product_variations, settings ) {
							var matching = [];
							for ( var i = 0; i < product_variations.length; i++ ) {
								var variation    = product_variations[i];

								if ( wc_variation_form_matcher.variations_match( variation.attributes, settings ) ) {
									matching.push( variation );
								}
							}
							return matching;
						},
						variations_match: function( attrs1, attrs2 ) {
							var match = true;
							for ( var attr_name in attrs1 ) {
								if ( attrs1.hasOwnProperty( attr_name ) ) {
									var val1 = null != attrs1[ attr_name ] ? attrs1[ attr_name ] : '';
									var val2 = null != attrs2[ attr_name ] ? attrs2[ attr_name ] : '';
									if ( val1 != undefined && val2 != undefined && val1.length != 0 && val2.length != 0 && val1 != val2 ) {
										match = false;
									}
								}
							}
							return match;
						}
					};
				</script>
				<?php
			}
		}

		return $result;
	}

	/**
	 * Capturing paypal and stripe payment when order is completed.
	 * When order status is changed from on hold to processing or completed.
	 *
	 * @since 1.0.0
	 * @param mixed $order_id Id of the order which is completed.
	 */
	public function wps_wocuf_pro_capture( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {

			return;
		}

		$payment_method = $order->get_payment_method();

		if ( 'wps-wocuf-pro-paypal-gateway' === $payment_method || 'wps-wocuf-pro-stripe-gateway' === $payment_method || 'wps-wocuf-pro-authorize-gateway' === $payment_method ) {

			global $woocommerce;
			$gateways = $woocommerce->payment_gateways->get_available_payment_gateways();

			if ( ! empty( $gateways[ $payment_method ] ) && method_exists( $gateways[ $payment_method ], 'process_capture' ) ) {

				$gateways[ $payment_method ]->process_capture( $order_id );
			}
		}
	}


	/**
	 * Remove all styles from offer pages.
	 *
	 * @since 3.0.0
	 */
	public function remove_styles_offer_pages() {
		$saved_offer_post_ids = get_option( 'wps_upsell_offer_post_ids', array() );

		if ( ! empty( $saved_offer_post_ids ) && is_array( $saved_offer_post_ids ) && count( $saved_offer_post_ids ) ) {

			global $post;

			if ( ! empty( $post->ID ) && in_array( (string) $post->ID, $saved_offer_post_ids, true ) ) {

				global $wp_styles;

				// To dequeue all wp-content styles.
				foreach ( $wp_styles->registered as $k => $s ) {

					if ( mb_strpos( $s->src, 'wp-content/' ) ) {

						// Except for upsell and elementor plugins.
						if ( mb_strpos( $s->src, 'elementor' ) || mb_strpos( $s->src, 'woocommerce-one-click-upsell-funnel' ) ) {

							continue;
						}

						wp_deregister_style( $k );
					}
				}

				global $wp_scripts;

				// To dequeue all theme scripts.
				foreach ( $wp_scripts->registered as $k => $s ) {

					if ( mb_strpos( $s->src, 'wp-content/themes/' ) ) {

						wp_deregister_script( $k );
					}
				}

				?>

				<style type="text/css">

					body{
						margin: auto;
					}
				</style>

				<?php
			}
		}
	}

	/**
	 * Hide upsell offer pages from nav menu front-end.
	 *
	 * @param mixed $args arguments.
	 * @since 3.0.0
	 */
	public function exclude_pages_from_front_end( $args ) {
		$saved_offer_post_ids = get_option( 'wps_upsell_offer_post_ids', array() );

		if ( ! empty( $saved_offer_post_ids ) && is_array( $saved_offer_post_ids ) && count( $saved_offer_post_ids ) ) {

			$exclude_pages     = $saved_offer_post_ids;
			$exclude_pages_ids = '';

			foreach ( $exclude_pages as $_post_id ) {

				if ( ! empty( $exclude_pages_ids ) ) {

					$exclude_pages_ids .= ', ';
				}

				$exclude_pages_ids .= $_post_id;
			}

			if ( ! empty( $args['exclude'] ) ) {

				$args['exclude'] .= ',';
			} else {

				$args['exclude'] = '';
			}

			$args['exclude'] .= $exclude_pages_ids;

		}

		return $args;
	}

	/**
	 * Hide upsell offer pages from added menu list in customizer and admin panel.
	 *
	 * @param mixed $items items.
	 * @since 3.0.0
	 */
	public function exclude_pages_from_menu_list( $items ) {
		$saved_offer_post_ids = get_option( 'wps_upsell_offer_post_ids', array() );

		if ( ! empty( $saved_offer_post_ids ) && is_array( $saved_offer_post_ids ) && count( $saved_offer_post_ids ) ) {

			$exclude_pages     = $saved_offer_post_ids;
			$exclude_pages_ids = array();

			foreach ( $exclude_pages as $_post_id ) {

				array_push( $exclude_pages_ids, $_post_id );
			}

			if ( ! empty( $exclude_pages_ids ) ) {

				foreach ( $items as $key => $item ) {

					if ( in_array( (string) $item->object_id, $exclude_pages_ids, true ) ) {

						unset( $items[ $key ] );
					}
				}
			}
		}

		return $items;
	}

	/**
	 * Redirect upsell offer pages if not admin or upsell nonce expired.
	 *
	 * @since 3.0.0
	 */
	public function upsell_offer_page_redirect() {
		$saved_offer_post_ids = get_option( 'wps_upsell_offer_post_ids', array() );

		if ( ! empty( $saved_offer_post_ids ) && is_array( $saved_offer_post_ids ) && count( $saved_offer_post_ids ) ) {

			global $post;

			// When current page is one of the upsell offer page.
			if ( ! empty( $post->ID ) && in_array( (string) $post->ID, $saved_offer_post_ids, true ) ) {

				$validate_shortcode = $this->validate_shortcode();

				if ( false === $validate_shortcode ) {

					$this->expire_offer();

				}
			}
		}

		/**
		 * Redirect to upsell page if parent order is done via stripe.
		 * After v3.3.0
		 */
		// Delete_this...
		// exit if we are not on the Thank You page.
		if ( is_wc_endpoint_url( 'order-received' ) ) {

			$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
			$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

			if ( ! $id_nonce_verified ) {
				wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
			}

			$order_id = ! empty( $_GET['key'] ) ? wc_get_order_id_by_order_key( sanitize_text_field( wp_unslash( $_GET['key'] ) ) ) : false;

			$order = wc_get_order( $order_id );

			if ( ! empty( $order ) ) {

				/**
				 * Check if order is paid via official stripe.
				 * Has order status processing completed.
				 * Has upsell redirect link.
				 */
				$redirect_url          = get_post_meta( $order_id, '_upsell_redirect_link', true );
				$do_not_redirect_again = get_post_meta( $order_id, '_wps_wocuf_stripe_parent_paid', true );

				if ( 'stripe' === $order->get_payment_method() || 'stripe_cc' === $order->get_payment_method() && 'processing' === $order->get_status() && ! empty( $redirect_url ) && wp_http_validate_url( $redirect_url ) && empty( $do_not_redirect_again ) ) {

					// Safe to redirect.
					update_post_meta( $order_id, '_wps_wocuf_stripe_parent_paid', true );
					$order->update_status( 'upsell-parent' );
					wp_redirect( $redirect_url ); //phpcs:ignore
					exit();
				}
			}
		}
	}

	/**
	 * Expire offer and show return to shop link.
	 *
	 * @since 3.0.0
	 */
	private function expire_offer() {
		$shop_page_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : get_permalink( woocommerce_get_page_id( 'shop' ) );
		?>
		<div style="text-align: center;margin-top: 30px;" id="wps_upsell_offer_expired"><h2 style="font-weight: 200;"><?php esc_html_e( 'Sorry, Offer expired.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h2><a class="button wc-backward" href="<?php echo esc_url( $shop_page_url ); ?>"><?php esc_html_e( 'Return to Shop ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>&rarr;</a></div>
		<?php
		wp_die();
	}

	/**
	 * Expire further Offers Order meta value.
	 *
	 * @param mixed $order_id order id.
	 * @since 3.5.0
	 */
	private function expire_further_offers( $order_id = 0 ) {
		$expire_further_offers = get_post_meta( $order_id, '_wps_upsell_expire_further_offers', true );

		if ( ! empty( $expire_further_offers ) ) {

			return true;
		} else {

			return false;
		}
	}

	/**
	 * Expire offer and show return to shop link.
	 *
	 * @since 3.0.0
	 */
	private function failed_upsell_payment() {
		$shop_page_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : get_permalink( woocommerce_get_page_id( 'shop' ) );
		?>
		<div style="text-align: center;margin-top: 30px;" id="wps_upsell_offer_expired"><h2 style="font-weight: 200;"><?php esc_html_e( 'Sorry, Your Offer Payment is failed.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h2><a class="button wc-backward" href="<?php echo esc_url( $shop_page_url ); ?>"><?php esc_html_e( 'Return to Shop ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>&rarr;</a></div>
		<?php
		wp_die();
	}

	/**
	 * Handle Upsell Orders on Thankyou for Success Rate and Stats.
	 *
	 * @param int $order_id The order id.
	 *
	 * @since 3.5.0
	 */
	public function upsell_sales_by_funnel_handling( $order_id ) {
		if ( ! $order_id ) {

			return;
		}

		// Process once and only for Upsells.
		$funnel_id = get_post_meta( $order_id, 'wps_upsell_funnel_id', true );

		if ( empty( $funnel_id ) ) {

			return;
		}

		$order = new WC_Order( $order_id );

		if ( empty( $order ) ) {

			return;
		}

		$processed_order_statuses = array(
			'processing',
			'completed',
			'on-hold',
		);

		if ( ! in_array( $order->get_status(), $processed_order_statuses, true ) ) {

			return;
		}

		$order_items = $order->get_items();

		$upsell_purchased = false;

		if ( ! empty( $order_items ) && is_array( $order_items ) ) {

			$upsell_item_total = 0;

			foreach ( $order_items as $item_id => $single_item ) {

				if ( ! empty( wc_get_order_item_meta( $item_id, 'is_upsell_purchase', true ) ) ) {

					$upsell_purchased   = true;
					$upsell_item_total += wc_get_order_item_meta( $item_id, '_line_total', true );
				}
			}
		}

		if ( $upsell_purchased ) {

			// Add Funnel Success count and Total Sales for the current Funnel.
			$sales_by_funnel = new WPS_Upsell_Report_Sales_By_Funnel( $funnel_id );

			$sales_by_funnel->add_funnel_success_count();
			$sales_by_funnel->add_funnel_total_sales( $upsell_item_total );
		}

		/**
		 * Delete Funnel id so that this is processed only once and funnel id
		 * might change so no need to associate the order with it.
		 */
		delete_post_meta( $order_id, 'wps_upsell_funnel_id' );
	}

	/**
	 * Add Base Code for Google Analyics and Facebook Pixel.
	 *
	 * @since 3.5.0
	 */
	public function add_ga_and_fb_pixel_base_code() {
		$upsell_analytics_options = get_option( 'wps_upsell_analytics_configuration', array() );

		$ga_analytics_config = ! empty( $upsell_analytics_options['google-analytics'] ) ? $upsell_analytics_options['google-analytics'] : array();
		$fb_pixel_config     = ! empty( $upsell_analytics_options['facebook-pixel'] ) ? $upsell_analytics_options['facebook-pixel'] : array();

		$add_ga_base_code       = false;
		$add_fb_pixel_base_code = false;

		if ( ! empty( $ga_analytics_config['enable_ga_gst'] ) && 'yes' === $ga_analytics_config['enable_ga_gst'] && ! empty( $ga_analytics_config['ga_account_id'] ) ) {

			$add_ga_base_code = true;

			$ga_tracking_id = $ga_analytics_config['ga_account_id'];
		}

		if ( ! empty( $fb_pixel_config['enable_pixel_basecode'] ) && 'yes' === $fb_pixel_config['enable_pixel_basecode'] && ! empty( $fb_pixel_config['pixel_account_id'] ) ) {

			$add_fb_pixel_base_code = true;

			$pixel_id = $fb_pixel_config['pixel_account_id'];
		}

		if ( $add_ga_base_code ) :

			?>
			<!-- Global site tag (gtag.js) - Google Analytics - Start ( 1 Click Upsell Plugin ) -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga_tracking_id ); ?>"></script> <?php //phpcs:ignore ?>
			<script>
				window.dataLayer = window.dataLayer || [];
				function gtag(){dataLayer.push(arguments)};
				gtag('js', new Date());

				gtag('config', '<?php echo esc_attr( $ga_tracking_id ); ?>');
			</script>
			<!-- Global site tag (gtag.js) - Google Analytics - End ( 1 Click Upsell Plugin ) -->
			<?php

		endif;

		if ( $add_fb_pixel_base_code ) :

			?>
			<!-- Facebook Pixel Code ( 1 Click Upsell Plugin ) -->
			<script>
				!function(f,b,e,v,n,t,s)
				{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
				n.callMethod.apply(n,arguments):n.queue.push(arguments)};
				if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
				n.queue=[];t=b.createElement(e);t.async=!0;
				t.src=v;s=b.getElementsByTagName(e)[0];
				s.parentNode.insertBefore(t,s)}(window, document,'script',
				'https://connect.facebook.net/en_US/fbevents.js');
				fbq('init', '<?php echo( esc_html( $pixel_id ) ); ?>');
				fbq('track', 'PageView');
			</script>
			<noscript>
				<img height="1" width="1" style="display:none"
			src="https://www.facebook.com/tr?id=<?php echo( esc_html( $pixel_id ) ); ?>&ev=PageView&noscript=1"
			/>
			</noscript>
			<!-- End Facebook Pixel Code ( 1 Click Upsell Plugin ) -->
			<?php

		endif;
	}

	/**
	 * GA and FB Pixel Purchase Event - Track Parent Order on 1st Upsell Offer Page.
	 *
	 * @since 3.5.0
	 */
	public function ga_and_fb_pixel_purchase_event_for_parent_order() {
		$validate_shortcode = $this->validate_shortcode();

		if ( 'live_offer' === $validate_shortcode ) {

			$upsell_analytics_options = get_option( 'wps_upsell_analytics_configuration', array() );

			$ga_analytics_config = ! empty( $upsell_analytics_options['google-analytics'] ) ? $upsell_analytics_options['google-analytics'] : array();
			$fb_pixel_config     = ! empty( $upsell_analytics_options['facebook-pixel'] ) ? $upsell_analytics_options['facebook-pixel'] : array();

			$add_ga_purchase_event       = false;
			$add_fb_pixel_purchase_event = false;

			if ( ! empty( $ga_analytics_config['enable_purchase_event'] ) && 'yes' === $ga_analytics_config['enable_purchase_event'] ) {

				$add_ga_purchase_event = true;
			}

			if ( ! empty( $fb_pixel_config['enable_purchase_event'] ) && 'yes' === $fb_pixel_config['enable_purchase_event'] ) {

				$add_fb_pixel_purchase_event = true;
			}

			if ( $add_ga_purchase_event ) :

				$this->add_ga_purchase_event_for_parent_order();

			endif;

			if ( $add_fb_pixel_purchase_event ) :

				$this->add_fb_pixel_purchase_event_for_parent_order();

			endif;
		}
	}

	/**
	 * GA and FB Pixel Purchase Event - Track Order on Thankyou page.
	 *
	 * @param int $order_id The order id.
	 *
	 * @since 3.5.0
	 */
	public function ga_and_fb_pixel_purchase_event( $order_id = '' ) {
		$upsell_analytics_options = get_option( 'wps_upsell_analytics_configuration', array() );

		$ga_analytics_config = ! empty( $upsell_analytics_options['google-analytics'] ) ? $upsell_analytics_options['google-analytics'] : array();
		$fb_pixel_config     = ! empty( $upsell_analytics_options['facebook-pixel'] ) ? $upsell_analytics_options['facebook-pixel'] : array();

		$add_ga_purchase_event       = false;
		$add_fb_pixel_purchase_event = false;

		if ( ! empty( $ga_analytics_config['enable_purchase_event'] ) && 'yes' === $ga_analytics_config['enable_purchase_event'] ) {

			$add_ga_purchase_event = true;
		}

		if ( ! empty( $fb_pixel_config['enable_purchase_event'] ) && 'yes' === $fb_pixel_config['enable_purchase_event'] ) {

			$add_fb_pixel_purchase_event = true;
		}

		if ( $add_ga_purchase_event ) :

			$this->add_ga_purchase_event( $order_id );

		endif;

		if ( $add_fb_pixel_purchase_event ) :

			$this->add_fb_pixel_purchase_event( $order_id );

		endif;
	}

	/**
	 * Google Analyics Purchase Event for Parent Order.
	 *
	 * @since 3.5.0
	 */
	public function add_ga_purchase_event_for_parent_order() {
		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$order_key = isset( $_GET['ocuf_ok'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) ) : '';

		$order_id = wc_get_order_id_by_order_key( $order_key );

		// Process once and only for Upsells.
		if ( empty( $order_id ) || ! in_array( get_post_meta( $order_id, 'wps_upsell_order_started', true ), array( 1, '1' ), true ) || ! empty( get_post_meta( $order_id, '_wps_upsell_ga_parent_tracked', true ) ) || in_array( get_post_meta( $order_id, '_wps_upsell_ga_tracked', true ), array( 1, '1' ), true ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {

			return;
		}

		// Only for those payment gateways with which Parent Order is Secured.
		$payment_method = $order->get_payment_method();

		$gateways_with_parent_secured = wps_upsell_payment_gateways_with_parent_secured();

		if ( ! in_array( $payment_method, $gateways_with_parent_secured, true ) ) {

			return;
		}

		// Start Tracking handling.

		global $woocommerce;

		// Get Coupon Codes.
		$coupons_list = '';

		if ( version_compare( $woocommerce->version, '3.7', '>' ) ) {

			if ( $order->get_coupon_codes() ) {

				$coupons_count = count( $order->get_coupon_codes() );
				$i             = 1;

				foreach ( $order->get_coupon_codes() as $coupon ) {

					$coupons_list .= $coupon;
					if ( $i < $coupons_count ) {
						$coupons_list .= ', ';
					}
					$i++;
				}
			}
		} else {

			if ( $order->get_used_coupons() ) {

				$coupons_count = count( $order->get_used_coupons() );
				$i             = 1;

				foreach ( $order->get_used_coupons() as $coupon ) {

					$coupons_list .= $coupon;
					if ( $i < $coupons_count ) {
						$coupons_list .= ', ';
					}
					$i++;
				}
			}
		}

		// All Order items.
		$order_items = $order->get_items();

		if ( ! empty( $order_items ) && is_array( $order_items ) ) {

			foreach ( $order_items as $item ) {

				$_product = $item->get_product();

				if ( isset( $_product->variation_data ) ) {

					$categories = esc_js( wc_get_formatted_variation( $_product->get_variation_attributes(), true ) );
				} else {

					$out = array();

					$categories = get_the_terms( $_product->get_id(), 'product_cat' );

					if ( $categories ) {

						foreach ( $categories as $category ) {

							$out[] = $category->name;
						}
					}

					$categories = esc_js( join( ',', $out ) );
				}

				$product_data[ get_permalink( $_product->get_id() ) ] = array(
					'p_id'    => esc_html( $_product->get_id() ),
					'p_sku'   => esc_js( $_product->get_sku() ? $_product->get_sku() : $_product->get_id() ),
					'p_name'  => html_entity_decode( $_product->get_title() ),
					'p_price' => esc_js( $order->get_item_total( $item ) ),
					'p_cat'   => $categories,
					'p_qty'   => esc_js( $item['qty'] ),
				);
			}

			if ( ! empty( $product_data ) ) {

				// Add Product data json.
				wc_enqueue_js( 'wps_upsell_ga_pd=' . wp_json_encode( $product_data ) . ';' );
			}
		}

		// Get Shipping total.
		$total_shipping = $order->get_total_shipping();

		$order_total     = $order->get_total();
		$order_total_tax = $order->get_total_tax();

		$upsell_ga_parent_tracked_data = array(
			'order_total'          => $order_total,
			'order_total_tax'      => $order_total_tax,
			'order_total_shipping' => $total_shipping,
		);

		$transaction_data = array(
			'id'          => esc_js( $order->get_order_number() ),
			'affiliation' => esc_js( get_bloginfo( 'name' ) ),
			'revenue'     => esc_js( $order_total ),
			'tax'         => esc_js( $order_total_tax ),
			'shipping'    => esc_js( $total_shipping ),
			'coupon'      => $coupons_list,
			'currency'    => get_woocommerce_currency(),
			'label'       => 'Parent Purchase',
		);

		// Add Transaction data json.
		wc_enqueue_js( 'wps_upsell_ga_td=' . wp_json_encode( $transaction_data ) . ';' );

		$ga_purchase_js = '
				 var items = [];
				//set local currencies
			gtag("set", {"currency": wps_upsell_ga_td.currency});
			for(var p_item in wps_upsell_ga_pd){
				items.push({
					"id": wps_upsell_ga_pd[p_item].p_sku,
					"name": wps_upsell_ga_pd[p_item].p_name, 
					"category": wps_upsell_ga_pd[p_item].p_cat,
					"price": wps_upsell_ga_pd[p_item].p_price,
					"quantity": wps_upsell_ga_pd[p_item].p_qty,
				});
			   
			}
			gtag("event", "purchase", {
				"transaction_id":wps_upsell_ga_td.id,
				"affiliation": wps_upsell_ga_td.affiliation,
				"value":wps_upsell_ga_td.revenue,
				"tax": wps_upsell_ga_td.tax,
				"shipping": wps_upsell_ga_td.shipping,
				"coupon": wps_upsell_ga_td.coupon,
				"event_category": "ecommerce",
				"event_label": wps_upsell_ga_td.label,
				"non_interaction": true,
				"items":items
			});
		';

		wc_enqueue_js( $ga_purchase_js );

		update_post_meta( $order_id, '_wps_upsell_ga_parent_tracked', $upsell_ga_parent_tracked_data );
	}

	/**
	 * Google Analyics Purchase Event for Parent Order.
	 *
	 * @param int $order_id The order id.
	 *
	 * @since 3.5.0
	 */
	public function add_ga_purchase_event( $order_id = '' ) {
		if ( empty( $order_id ) || 1 === get_post_meta( $order_id, '_wps_upsell_ga_tracked', true ) ) {

			return;
		}

		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {

			return;
		}

		// Start Tracking handling.

		global $woocommerce;

		// Get Coupon Codes.
		$coupons_list = '';

		if ( version_compare( $woocommerce->version, '3.7', '>' ) ) {

			if ( $order->get_coupon_codes() ) {

				$coupons_count = count( $order->get_coupon_codes() );
				$i             = 1;

				foreach ( $order->get_coupon_codes() as $coupon ) {

					$coupons_list .= $coupon;
					if ( $i < $coupons_count ) {
						$coupons_list .= ', ';
					}
					$i++;
				}
			}
		} else {

			if ( $order->get_used_coupons() ) {

				$coupons_count = count( $order->get_used_coupons() );
				$i             = 1;

				foreach ( $order->get_used_coupons() as $coupon ) {

					$coupons_list .= $coupon;
					if ( $i < $coupons_count ) {
						$coupons_list .= ', ';
					}
					$i++;
				}
			}
		}

		$upsell_ga_parent_tracked      = false;
		$upsell_ga_parent_tracked_data = get_post_meta( $order_id, '_wps_upsell_ga_parent_tracked', true );

		if ( ! empty( $upsell_ga_parent_tracked_data ) && is_array( $upsell_ga_parent_tracked_data ) ) {

			$upsell_ga_parent_tracked = true;
			$upsell_purchase          = false;
		}

		// All Order items.
		$order_items = $order->get_items();

		if ( ! empty( $order_items ) && is_array( $order_items ) ) {

			foreach ( $order_items as $item_id => $item ) {

				// When Parent Order is already tracked.
				if ( $upsell_ga_parent_tracked ) {

					// If not Upsell Purchase Item then Continue. So this loop will only add Upsell items if Parent Order is already tracked.
					if ( empty( wc_get_order_item_meta( $item_id, 'is_upsell_purchase', true ) ) ) {

						continue;
					}

					// Did not continue means there is an Upsell item.
					$upsell_purchase = true;
				}

				$_product = $item->get_product();

				if ( isset( $_product->variation_data ) ) {

					$categories = esc_js( wc_get_formatted_variation( $_product->get_variation_attributes(), true ) );
				} else {

					$out = array();

					$categories = get_the_terms( $_product->get_id(), 'product_cat' );

					if ( $categories ) {

						foreach ( $categories as $category ) {

							$out[] = $category->name;
						}
					}

					$categories = esc_js( join( ',', $out ) );
				}

				$product_data[ get_permalink( $_product->get_id() ) ] = array(
					'p_id'    => esc_html( $_product->get_id() ),
					'p_sku'   => esc_js( $_product->get_sku() ? $_product->get_sku() : $_product->get_id() ),
					'p_name'  => html_entity_decode( $_product->get_title() ),
					'p_price' => esc_js( $order->get_item_total( $item ) ),
					'p_cat'   => $categories,
					'p_qty'   => esc_js( $item['qty'] ),
				);
			}

			if ( ! empty( $product_data ) ) {

				// Add Product data json.
				wc_enqueue_js( 'wps_upsell_ga_pd=' . wp_json_encode( $product_data ) . ';' );
			}
		}

		// When Parent Order is already tracked.
		if ( $upsell_ga_parent_tracked ) {

			// No Upsell Items so return as no need to send any data to GA as it's already tracked.
			if ( false === $upsell_purchase ) {

				update_post_meta( $order_id, '_wps_upsell_ga_tracked', 1 );
				return;
			}
		}

		// Get Shipping total.
		$total_shipping = $order->get_total_shipping();

		$order_total     = $order->get_total();
		$order_total_tax = $order->get_total_tax();

		$transaction_data = array(
			'id'          => esc_js( $order->get_order_number() ),
			'affiliation' => esc_js( get_bloginfo( 'name' ) ),
			'revenue'     => esc_js( $order_total ),
			'tax'         => esc_js( $order_total_tax ),
			'shipping'    => esc_js( $total_shipping ),
			'coupon'      => $coupons_list,
			'currency'    => get_woocommerce_currency(),
			'label'       => 'Purchase',

		);

		// When Parent Order is already tracked.
		if ( $upsell_ga_parent_tracked ) {

			$parent_order_total          = ! empty( $upsell_ga_parent_tracked_data['order_total'] ) ? $upsell_ga_parent_tracked_data['order_total'] : 0;
			$parent_order_total_tax      = ! empty( $upsell_ga_parent_tracked_data['order_total_tax'] ) ? $upsell_ga_parent_tracked_data['order_total_tax'] : 0;
			$parent_order_total_shipping = ! empty( $upsell_ga_parent_tracked_data['order_total_shipping'] ) ? $upsell_ga_parent_tracked_data['order_total_shipping'] : 0;

			$current_order_total          = $order_total - $parent_order_total;
			$current_order_total_tax      = $order_total_tax - $parent_order_total_tax;
			$current_order_total_shipping = $total_shipping - $parent_order_total_shipping;

			$transaction_data = array(
				'id'          => esc_js( $order->get_order_number() ),
				'affiliation' => esc_js( get_bloginfo( 'name' ) ),
				'revenue'     => esc_js( $current_order_total ),
				'tax'         => esc_js( $current_order_total_tax ),
				'shipping'    => esc_js( $current_order_total_shipping ),
				'currency'    => get_woocommerce_currency(),
				'label'       => 'Upsell Purchase',
			);
		}

		// Add Transaction data json.
		wc_enqueue_js( 'wps_upsell_ga_td=' . wp_json_encode( $transaction_data ) . ';' );

		$ga_purchase_js = '
				 var items = [];
				//set local currencies
			gtag("set", {"currency": wps_upsell_ga_td.currency});
			for(var p_item in wps_upsell_ga_pd){
				items.push({
					"id": wps_upsell_ga_pd[p_item].p_sku,
					"name": wps_upsell_ga_pd[p_item].p_name, 
					"category": wps_upsell_ga_pd[p_item].p_cat,
					"price": wps_upsell_ga_pd[p_item].p_price,
					"quantity": wps_upsell_ga_pd[p_item].p_qty,
				});
			   
			}
			gtag("event", "purchase", {
				"transaction_id":wps_upsell_ga_td.id,
				"affiliation": wps_upsell_ga_td.affiliation,
				"value":wps_upsell_ga_td.revenue,
				"tax": wps_upsell_ga_td.tax,
				"shipping": wps_upsell_ga_td.shipping,
				"coupon": wps_upsell_ga_td.coupon,
				"event_category": "ecommerce",
				"event_label": wps_upsell_ga_td.label,
				"non_interaction": true,
				"items":items
			});
		';

		wc_enqueue_js( $ga_purchase_js );

		update_post_meta( $order_id, '_wps_upsell_ga_tracked', 1 );
	}

	/**
	 * Facebook Pixel Purchase Event for Parent Order.
	 *
	 * @since 3.5.0
	 */
	public function add_fb_pixel_purchase_event_for_parent_order() {
		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$order_key = isset( $_GET['ocuf_ok'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ok'] ) ) : '';

		$order_id = wc_get_order_id_by_order_key( $order_key );

		// Process once and only for Upsells.
		if ( empty( $order_id ) || ! in_array( get_post_meta( $order_id, 'wps_upsell_order_started', true ), array( 1, '1' ), true ) || ! empty( get_post_meta( $order_id, '_wps_upsell_fbp_parent_tracked', true ) ) || in_array( get_post_meta( $order_id, '_wps_upsell_fbp_parent_tracked', true ), array( 1, '1' ), true ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {

			return;
		}

		// Only for those payment gateways with which Parent Order is Secured.
		$payment_method = $order->get_payment_method();

		$gateways_with_parent_secured = wps_upsell_payment_gateways_with_parent_secured();

		if ( ! in_array( $payment_method, $gateways_with_parent_secured, true ) ) {

			return;
		}

		// Start Tracking handling.

		$order_total = $order->get_total() ? $order->get_total() : 0;

		$content_type = 'product';
		$product_ids  = array();

		foreach ( $order->get_items() as $item ) {
			$product = wc_get_product( $item['product_id'] );

			$product_ids[] = $product->get_id();

			if ( $product->get_type() === 'variable' ) {
				$content_type = 'product_group';
			}
		}

		$params = array(
			'content_ids'  => wp_json_encode( $product_ids ),
			'content_type' => $content_type,
			'value'        => $order_total,
			'currency'     => get_woocommerce_currency(),
		);

		$fb_purchase_js = sprintf( "fbq('%s', '%s', %s);", 'track', 'Purchase', wp_json_encode( $params, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT ) );

		wc_enqueue_js( $fb_purchase_js );

		$upsell_fb_pixel_parent_tracked_data = array(
			'order_total' => $order_total,
		);

		update_post_meta( $order_id, '_wps_upsell_fbp_parent_tracked', $upsell_fb_pixel_parent_tracked_data );
	}

	/**
	 * Facebook Pixel Purchase Event.
	 *
	 * @param int $order_id The order id.
	 *
	 * @since 3.5.0
	 */
	public function add_fb_pixel_purchase_event( $order_id = '' ) {
		if ( empty( $order_id ) || 1 === get_post_meta( $order_id, '_wps_upsell_fbp_tracked', true ) ) {

			return;
		}

		$order = wc_get_order( $order_id );

		if ( empty( $order ) ) {

			return;
		}

		// Start Tracking handling.

		$order_total = $order->get_total() ? $order->get_total() : 0;

		$content_type = 'product';
		$product_ids  = array();

		$upsell_fb_pixel_parent_tracked      = false;
		$upsell_fb_pixel_parent_tracked_data = get_post_meta( $order_id, '_wps_upsell_fbp_parent_tracked', true );

		if ( ! empty( $upsell_fb_pixel_parent_tracked_data ) && is_array( $upsell_fb_pixel_parent_tracked_data ) ) {

			$upsell_fb_pixel_parent_tracked = true;
			$upsell_purchase                = false;
		}

		foreach ( $order->get_items() as $item_id => $item ) {

			// When Parent Order is already tracked.
			if ( $upsell_fb_pixel_parent_tracked ) {

				// If not Upsell Purchase Item then Continue.
				if ( empty( wc_get_order_item_meta( $item_id, 'is_upsell_purchase', true ) ) ) {

					continue;
				}

				// Did not continue means there is an Upsell item.
				$upsell_purchase = true;
			}

			$product = wc_get_product( $item['product_id'] );

			$product_ids[] = $product->get_id();

			if ( $product->get_type() === 'variable' ) {
				$content_type = 'product_group';
			}
		}

		// When Parent Order is already tracked.
		if ( $upsell_fb_pixel_parent_tracked ) {

			// No Upsell Items so return as no need to send any data.
			if ( false === $upsell_purchase ) {

				update_post_meta( $order_id, '_wps_upsell_fbp_tracked', true );
				return;
			}
		}

		// When Parent Order is already tracked.
		if ( $upsell_fb_pixel_parent_tracked ) {

			$parent_order_total = ! empty( $upsell_fb_pixel_parent_tracked_data['order_total'] ) ? $upsell_fb_pixel_parent_tracked_data['order_total'] : 0;

			$order_total = $order_total - $parent_order_total;

			$params = array(
				'content_ids'  => wp_json_encode( $product_ids ),
				'content_type' => $content_type,
				'value'        => $order_total,
				'currency'     => get_woocommerce_currency(),
			);
		} else {

			$params = array(
				'content_ids'  => wp_json_encode( $product_ids ),
				'content_type' => $content_type,
				'value'        => $order_total,
				'currency'     => get_woocommerce_currency(),
			);
		}

		$fb_purchase_js = sprintf( "fbq('%s', '%s', %s);", 'track', 'Purchase', wp_json_encode( $params, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT ) );

		wc_enqueue_js( $fb_purchase_js );

		update_post_meta( $order_id, '_wps_upsell_fbp_tracked', true );
	}


	/**
	 * Compatibility for Enhanced Ecommerce Google Analytics Plugin by Tatvic.
	 * Remove plugin's Purchase Event from Thankyou page when
	 * Upsell Purchase is enabled.
	 *
	 * @since 3.5.0
	 */
	public function upsell_ga_compatibility_for_eega() {
		if ( ! class_exists( 'Enhanced_Ecommerce_Google_Analytics' ) ) {

			return;
		}

		$upsell_analytics_options = get_option( 'wps_upsell_analytics_configuration', array() );

		$ga_analytics_config = ! empty( $upsell_analytics_options['google-analytics'] ) ? $upsell_analytics_options['google-analytics'] : array();

		$add_ga_purchase_event = false;

		if ( ! empty( $ga_analytics_config['enable_purchase_event'] ) && 'yes' === $ga_analytics_config['enable_purchase_event'] ) {

			$add_ga_purchase_event = true;
		}

		// Only when Upsell Purchase is enabled.
		if ( $add_ga_purchase_event ) {

			global $wp_filter;

			foreach ( $wp_filter['woocommerce_thankyou']->callbacks as $key => $plugin_cbs ) {

				// Only remove the one with default priority.
				if ( 10 !== $key || '10' !== $key ) {
					continue;
				}

				foreach ( $plugin_cbs as $cb_key => $cb_obj ) {

					if ( isset( $cb_obj['function'] ) && is_array( $cb_obj['function'] ) ) {

						// Check if the current object belongs to the class.
						if ( is_a( $cb_obj['function']['0'], 'Enhanced_Ecommerce_Google_Analytics_Public' ) ) {

							$enhanced_ecommerce_google_analytics = $cb_obj['function']['0'];

							remove_action( 'woocommerce_thankyou', array( $enhanced_ecommerce_google_analytics, 'ecommerce_tracking_code' ) );

							break 2;
						}
					}
				}
			}
		}
	}

	/**
	 * Compatibility for Facebook for WooCommerce plugin.
	 * Remove plugin's Purchase Event from Thankyou page when
	 * Upsell Purchase is enabled.
	 *
	 * @since 3.5.0
	 */
	public function upsell_fbp_compatibility_for_ffw() {
		if ( ! class_exists( 'WC_Facebookcommerce_EventsTracker' ) ) {

			return;
		}

		$wc_integrations = WC()->integrations->get_integrations();

		if ( isset( $wc_integrations['facebookcommerce'] ) && $wc_integrations['facebookcommerce'] instanceof WC_Facebookcommerce_Integration ) {

			$upsell_analytics_options = get_option( 'wps_upsell_analytics_configuration', array() );

			$fb_pixel_config = ! empty( $upsell_analytics_options['facebook-pixel'] ) ? $upsell_analytics_options['facebook-pixel'] : array();

			$add_fb_pixel_purchase_event = false;

			if ( ! empty( $fb_pixel_config['enable_purchase_event'] ) && 'yes' === $fb_pixel_config['enable_purchase_event'] ) {

				$add_fb_pixel_purchase_event = true;
			}

			if ( $add_fb_pixel_purchase_event ) {

				// For Facebook for WooCommerce plugin version >= 1.1.0.
				remove_action( 'woocommerce_thankyou', array( $wc_integrations['facebookcommerce']->events_tracker, 'inject_purchase_event' ), 40 );
			}
		}
	}

	/**
	 * Global Custom CSS.
	 *
	 * @since 3.0.0
	 */
	public function global_custom_css() {
		// Ignore admin, feed, robots or trackbacks.
		if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {
			return;
		}

		$wps_upsell_global_settings = get_option( 'wps_upsell_global_options', array() );

		$global_custom_css = ! empty( $wps_upsell_global_settings['global_custom_css'] ) ? $wps_upsell_global_settings['global_custom_css'] : '';

		if ( empty( $global_custom_css ) ) {
			return;
		}

		wp_register_style( 'wps_upsell_global_custom_css', false, array(), WC_VERSION, 'all' );
		wp_enqueue_style( 'wps_upsell_global_custom_css' );
		wp_add_inline_style( 'wps_upsell_global_custom_css', $global_custom_css );
	}

	/**
	 * Global Custom JS.
	 *
	 * @since 3.0.0
	 */
	public function global_custom_js() {
		// Ignore admin, feed, robots or trackbacks.
		if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {

			return;
		}

		$wps_upsell_global_settings = get_option( 'wps_upsell_global_options', array() );

		$global_custom_js = ! empty( $wps_upsell_global_settings['global_custom_js'] ) ? $wps_upsell_global_settings['global_custom_js'] : '';

		if ( empty( $global_custom_js ) ) {

			return;
		}

		wp_register_script( 'wps_upsell_global_custom_js', false, array( 'jquery' ), WC_VERSION, false );
		wp_enqueue_script( 'wps_upsell_global_custom_js' );
		wp_add_inline_script( 'wps_upsell_global_custom_js', $global_custom_js );

	}

	/**
	 * Add support for Upsell template for pages.
	 *
	 * @since 3.1.0
	 */
	public function add_upsell_template_support() {
		$post_type = 'page';

		add_filter( "theme_{$post_type}_templates", array( $this, 'add_page_templates' ), 10, 4 );
	}

	/**
	 * Add support for Upsell Offer template for pages.
	 *
	 * @param string $page_templates Page template.
	 * @param string $wp_theme       theme template.
	 * @param object $post           Page      template.
	 *
	 * @since 3.1.0
	 */
	public function add_page_templates( $page_templates, $wp_theme, $post ) {
		$page_templates = array(
			'wps_upsell_template' => esc_html__( 'Upsell Offer', 'one-click-upsell-funnel-for-woocommerce-pro' ),
		) + $page_templates;

		return $page_templates;
	}

	/**
	 * Include Upsell Offer template.
	 *
	 * @param string $template The template string.
	 *
	 * @since 3.1.0
	 */
	public function upsell_template_include( $template ) {
		if ( is_singular() && is_page() ) {

			$post_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

			if ( 'wps_upsell_template' === $post_template ) {

				$template = WPS_WOCUF_PRO_DIRPATH . 'public/templates/upsell-offer-template.php';
			}
		}

		return $template;
	}

	/**
	 * Verify Intent success at order endpoints.
	 *
	 * @since 3.2.0
	 *
	 * @throws Exception Exception.
	 */
	public function wps_wocuf_pro_verify_intent() {
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'wps_wocuf_pro_confirm_nonce' ) ) {

			$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Nonce Verification', 'Failed', $result );

			$this->expire_offer();
		}

		global $woocommerce;

		// Get order.
		$order_id = ! empty( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : '';
		if ( empty( $order_id ) ) {

			$redirect_url = $woocommerce->cart->is_empty()
			? get_permalink( woocommerce_get_page_id( 'shop' ) )
			: wc_get_checkout_url();
			wp_redirect( $redirect_url ); //phpcs:ignore

			wc_add_notice( esc_html__( 'Missing order ID for payment confirmation', 'one-click-upsell-funnel-for-woocommerce-pro' ), 'error' );

			$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Payment Verification', 'Missing order ID for payment confirmation', $result );
			return;
		}

		$order = wc_get_order( $order_id );

		// Check order status, if already processed return.
		if ( in_array( $order->get_status(), wps_non_intent_order_status(), true ) ) {
			update_post_meta( $order_id, 'verification_called', 'true' );
			return;
		}

		try {

			$gateway = new WPS_Wocuf_Pro_Stripe_Gateway_Admin();
			$gateway->verify_intent_after_checkout( $order, false );

			if ( ! isset( $_GET['is_ajax'] ) ) {
				$redirect_url         = isset( $_GET['redirect_to'] )
				? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) )
				: $gateway->get_return_url( $order );
				$stripe_error_message = 'Response for Customer';

				// If upsell parent order then redirect to the upsell page! Ahoy!
				$stripe_sca_upsell_redirect = get_post_meta( $order_id, 'stripe_sca_upsell_redirect', true );

				if ( ! empty( $stripe_sca_upsell_redirect['upsell_offer_link'] ) ) {

					/**
					 * As just going to redirect, means upsell is initialized for this order.
					 *
					 * This can be used to track upsell orders in which browser window was closed
					 * and other purposes.
					 * Set as parent order is processed.
					 * Empty the cart as well!
					 */

					if ( WC()->cart ) {

						WC()->cart->empty_cart();
					}

					if ( 'manual' === $gateway->capture ) {

						$order->update_status( 'on-hold' );
					} else {

						$order->update_status( 'upsell-parent' );
					}

					// Process Subscriptions for pre upsell products from Order.
					if ( wps_upsell_order_contains_subscription( $order_id ) && wps_upsell_pg_supports_subs( $order_id ) ) {

						// If upsell parent order is paid then activate subscriptions.
						WC_Subscriptions_Manager::activate_subscriptions_for_order( $order );
					}

					$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Upsell Offer Redirection', 'started', 'true' );

					// Redirect to upsell offer page.
					$this->initial_redirection_to_upsell_offer_and_triggers( $order_id, $stripe_sca_upsell_redirect['funnel_id'], $stripe_sca_upsell_redirect['upsell_offer_link'], true );
				}

				// Non upsell order normally redirect to thank you page.
				$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Order Sucessful.', 'Redirecting to thankyou.', 'true' );
				wp_redirect( $redirect_url ); //phpcs:ignore
			}

			exit;

		} catch ( Exception $e ) {

			$stripe_error_message = $e->getMessage();

			$order->add_order_note( __( 'Response for Customer: ', 'one-click-upsell-funnel-for-woocommerce-pro' ) . $stripe_error_message );

			$this->wps_wocuf_pro_create_stripe_log( $order_id, 'Response for Customer', 'Failed', $stripe_error_message );

			$order->update_status( 'failed' );

			// For Woocommerce Try block.
			throw new Exception( $stripe_error_message );
		}
	}


	/**
	 * Set status as upsell failed.
	 * Remove All offer products or make order rollback
	 * Redirect to thanks page.
	 * Set parent order as processing
	 *
	 * @param object $order          The WC Order.
	 * @param bool   $set_processing The WC Order status.
	 *
	 * @since 3.2.0
	 */
	public function wps_wocuf_pro_expire_offer_on_failed_upsell_payment( $order, $set_processing = true ) {
		// If order is not from upsell Stripe.
		if ( ! in_array( $order->get_payment_method(), wps_supported_gateways_with_upsell_parent_order(), true ) ) {

			$this->expire_offer();
		}

		$is_target_upgraded = get_post_meta( $order->get_id(), '_wps_wocufpro_replace_target', true );

		if ( ! empty( $is_target_upgraded ) && 'upgraded' === $is_target_upgraded ) {

			// Target product is removed and upsell products are present. Just show failed upsell payment page.
			$this->failed_upsell_payment();

			// Do not remove upsell products.
			return;
		}

		// Remove all upsell offer products.
		$upsell_items = get_post_meta( $order->get_id(), '_upsell_remove_items_on_fail', true );

		if ( ! empty( $upsell_items ) && is_array( $upsell_items ) ) {

			foreach ( (array) $order->get_items() as $item_id => $item ) {

				if ( in_array( $item_id, $upsell_items, true ) ) {

					$order->remove_item( $item_id );
				}
			}

			$order->save();
			$order->calculate_totals();
		}

		if ( $set_processing ) {

			// Set Order status back to Processing from Upsell Order Failed.
			$set_status = $order->needs_processing() ? 'processing' : 'completed';
			$order->update_status( $set_status );

		}

		$message = __( 'Your Upsell Offer Payment is failed. Please Contact site Owner for details.', 'one-click-upsell-funnel-for-woocommerce-pro' );

		if ( function_exists( 'wc_add_notice' ) ) {

			wc_clear_notices();
			wc_add_notice( $message, 'error' );
		}

		// Just Redirect for parent Order!
		if ( 'wps-wocuf-pro-stripe-gateway' === $order->get_payment_method() ) {

			$gateway = new WPS_Wocuf_Pro_Stripe_Gateway_Admin();

		} elseif ( 'stripe' === $order->get_payment_method() || 'stripe_cc' === $order->get_payment_method()  ) {

			$gateway = new WC_Gateway_Stripe();
		}

		return array(
			'result'   => 'success',
			'redirect' => $gateway->get_return_url( $order ),
		);
	}

	/**
	 * The upsell products has been paid seperately.
	 * Need to refund for the target product.
	 * When refund is successfull then remove target product.
	 * Cancel and delete if subscription has been made.
	 *
	 * @param string $order_id The WC Order.
	 *
	 * @since 3.5.0
	 */
	public function process_target_upgrade_for_upsell_parent_order( $order_id ) {
		/**
		 * Upsell offer is accepted and product object is ready here.
		 * Before adding remove target product.
		 */
		$target_item_id      = get_post_meta( $order_id, '_wps_wocufpro_replace_target', true );
		$is_upsell_purchased = get_post_meta( $order_id, 'wps_wocuf_upsell_order', true );
		$order               = wc_get_order( $order_id );

		if ( empty( $order ) ) {

			return false;
		}

		// Not an upsell order.
		if ( empty( $is_upsell_purchased ) || 'true' !== $is_upsell_purchased ) {

			return false;
		}

		if ( ! empty( $target_item_id ) && is_numeric( $target_item_id ) ) {

			/**
			 * In case of stripe this item will be already paid,
			 * Hence we need to refund for this particular payment first.
			 * After refund, check if success and also set order status as parent order
			 * completed.
			 * Change flag key to non numeric.
			 */
			$remove_flag = false;

			try {

				$target_item        = $order->get_item( $target_item_id );
				$target_item_total  = $order->get_item_total( $target_item, true );
				$reason             = esc_html__( 'Upsell offer accpeted with Smart Offer Upgrade Feature.', 'one-click-upsell-funnel-for-woocommerce-pro' );
				$payment_method     = $order->get_payment_method();
				$gateways           = WC()->payment_gateways->get_available_payment_gateways();
				$payment_method_obj = $gateways[ $payment_method ];

				$refund_result = $payment_method_obj->process_refund( $order_id, $target_item_total, $reason );

			} catch ( Exception $e ) { // Catch exception.
				$order->add_order_note( $e->getMessage() );
				$remove_flag = false;
			}

			// Check if refund was successfull.
			$remove_flag = ! empty( $refund_result ) ? $refund_result : false;

			// Update (v3.6.7) starts.
			// If the target product is free then manage error refund response in case of smart order upgrade.
			if ( is_wp_error( $remove_flag ) && empty( $target_item_total ) ) {
				$remove_flag = true;
			}
			// Update (v3.6.7) end.

			if ( true === $remove_flag ) {

				// In case target product was subscriptions cancel that too.
				$remove_subscription_id = get_post_meta( $order_id, '_wps_wocufpro_replace_target_subs_id', true );
				if ( ! empty( $remove_subscription_id ) ) {

					$subscriptions = wcs_get_subscriptions_for_order( $order_id );

					// We get the related subscription for this order.
					foreach ( $subscriptions as $subscription_id => $subscription_obj ) {

						if ( (int) $remove_subscription_id === (int) $subscription_id ) {

							if ( $subscription_obj->can_be_updated_to( 'cancelled' ) ) {
								$subscription_obj->update_status( 'cancelled' );
								$subscription_obj->update_status( 'trash' );
								break;
							}
						}
					}
				}

				$order->remove_item( $target_item_id );
				$order->save();
				$order->calculate_totals();
				update_post_meta( $order_id, '_wps_wocufpro_replace_target', 'upgraded' );
				update_post_meta( $order_id, '_wps_wocufpro_replace_target_subs_id', 'upgraded' );
				return true;
			}
		}
	}


	/**
	 * Skip offer product in case of the purchased in prevous orders.
	 *
	 * @param string $offer_product_id The Offer product id to check.
	 *
	 * @since 3.5.0
	 */
	public static function wps_wocuf_pro_skip_for_pre_order( $offer_product_id = '' ) {
		if ( empty( $offer_product_id ) ) {

			return;
		}

		$offer_product = wc_get_product( $offer_product_id );

		// In case the offer is variable parent then no need to check this.
		if ( ! empty( $offer_product ) && is_object( $offer_product ) && $offer_product->has_child() ) {

			return false;
		}

		// Current user ID.
		$customer_user_id = get_current_user_id();

		// Return for Guest users.
		if ( empty( $customer_user_id ) ) {

			return false;
		}

		// Getting current customer orders.
		$order_statuses = array( 'wc-on-hold', 'wc-processing', 'wc-completed' );

		$customer_orders = get_posts(
			array(
				'numberposts' => -1,
				'fields'      => 'ids', // Return only order ids.
				'meta_key'    => '_customer_user', //phpcs:ignore
			'meta_value'  => $customer_user_id, //phpcs:ignore
			'post_type'       => wc_get_order_types(),
			'post_status'     => $order_statuses,
			'order'           => 'DESC', // Get last order first.
			)
		);

		// Past Orders.
		foreach ( $customer_orders as $key => $single_order_id ) {

			// Continue if order is not a valid one.
			if ( ! $single_order_id ) {

				continue;
			}

			$single_order = wc_get_order( $single_order_id );

			// Continue if Order object is not a valid one.
			if ( empty( $single_order ) || ! is_object( $single_order ) || is_wp_error( $single_order ) ) {

				continue;
			}

			$items_purchased = $single_order->get_items();

			foreach ( $items_purchased as $key => $single_item ) {

				$product_id = ! empty( $single_item['variation_id'] ) ? $single_item['variation_id'] : $single_item['product_id'];

				if ( (int) $product_id === (int) $offer_product_id ) {

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Returns the gateway id with dashes in place of underscores, and
	 * appropriate for use in frontend element names, classes and ids
	 *
	 * @param string $string id with dashes in place of underscores.
	 *
	 * @since 3.5.0
	 *
	 * @return string gateway id with dashes in place of underscores
	 */
	public function get_id_dasherized( $string = '' ) {
		return str_replace( '_', '-', $string );
	}


	/**
	 * Returns the transaction data required for processing payments after upsell.
	 *
	 * @since 3.5.0
	 *
	 * @param array  $posted_data         data present in $_POST.
	 * @param string $order_id            order id.
	 * @param bool   $force_save_required save required or not.
	 *
	 * @return string plugin id with dashes in place of underscores
	 */
	public function fetch_transaction_data( $posted_data = '', $order_id = false, $force_save_required = false ) {
		// Order id is must.
		if ( empty( $order_id ) ) {
			return false;
		}

		$order                     = wc_get_order( $order_id );
		$payment_method            = ! empty( $posted_data['payment_method'] ) ? $posted_data['payment_method'] : '';
		$payment_method_dasherized = $this->get_id_dasherized( $payment_method );

		$transaction_data = array();

		// Added in update.
		$posted_data = apply_filters( 'wps_wocuf_pro_additional_data_recovery_to_post', $posted_data, $order_id );

		if ( ! empty( $posted_data ) && is_array( $posted_data ) ) {

			foreach ( $posted_data as $index => $value ) {

				if ( false !== strrpos( $index, $payment_method ) || false !== strrpos( $index, $payment_method_dasherized ) ) {

					$transaction_data[ $index ] = ! empty( $value ) ? sanitize_text_field( wp_unslash( $value ) ) : '';
				}
			}
		}

		/**
		 * Add force save payment method for subscription products.
		 */
		if ( true === $force_save_required ) {

			if ( 'braintree_cc' === $payment_method || 'braintree_credit_card' == $payment_method ) {

				$transaction_data['braintree_cc_save_method'] = 'on';
			} elseif ( 'square_credit_card' === $payment_method && ! empty( $transaction_data['wc-square-credit-card-payment-nonce'] ) ) {

				$transaction_data['wc-square-credit-card-tokenize-payment-method'] = true;
			}
		}

		/**
		 * Save session data for angeleye integration.
		 * In case it gets abandoned after upsell.
		 */
		if ( 'paypal_express' === $payment_method ) {

			update_post_meta( $order_id, '_' . $payment_method . '_payment_session_details', WC()->session->get( 'paypal_express_checkout' ) );
			update_post_meta( $order_id, $payment_method . '_transaction_data', $posted_data );
			return true;
		}

		if ( WC()->session->__isset( 'ppcp' ) ) {
			update_post_meta( $order_id, 'auto_capture_session_handler', WC()->session->get( 'ppcp' ) );
			return true;
		}

		// Save Order Meta.
		if ( ! empty( $transaction_data ) ) {
			update_post_meta( $order_id, $payment_method . '_transaction_data', $transaction_data );
		}

		// Return a flag.
		return ! empty( $transaction_data ) ? true : false;
	}



	/**
	 * Custom Page redirection to thank you page.
	 * For abandon recovery.
	 *
	 * @since 3.5.0
	 */
	public function wps_wocuf_pro_custom_th_redirection() {
		$return = false;

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		// Do nothing if we are not on the appropriate page.
		if ( ! is_wc_endpoint_url( 'order-received' ) || empty( $_GET['key'] ) ) {
			$return = true;
		} elseif ( ! is_wc_endpoint_url( apply_filters( 'wps_wocuf_custom_thankyou_page_endpoint', 'order-received' ) ) ) {
			$return = true;
		} elseif ( ! current_user_can( 'administrator' ) ) {
			$return = true;
		}

		if ( true === $return ) {
			return;
		}

		$order_id = ! empty( $_GET['key'] ) ? wc_get_order_id_by_order_key( sanitize_text_field( wp_unslash( $_GET['key'] ) ) ) : false;
		$order    = wc_get_order( $order_id );

		// check for order which are on pending payment or failed.
		if ( $order->has_status( 'cancelled' ) || $order->has_status( 'pending' ) || $order->has_status( 'failed' ) ) {
			return;
		}

		// Fetch redirect link.
		$is_background_processed = ! empty( get_post_meta( $order_id, 'wps_ocufp_performing_paypal_processing', true ) ) ? true : false;

		// Delete After Use.
		delete_post_meta( $order_id, 'wps_ocufp_performing_paypal_processing' );

		if ( ! empty( $is_background_processed ) && true === $is_background_processed ) {

			$shop_page_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : get_permalink( woocommerce_get_page_id( 'shop' ) );

			wp_redirect( $shop_page_url ); //phpcs:ignore
			exit;
		}
	}

	/**
	 * Set Email Triggers for Upsell Custom Status transitions.
	 * Parent Order Completed - upsell-parent
	 * Upsell Order Failed - upsell-failed
	 *
	 * @since 3.5.0
	 */
	public function hook_order_confirmation_emails_for_upsell_custom_statuses() {
		$wc_mails = WC()->mailer();

		// Sent to customer when set to processing.
		add_action( 'woocommerce_order_status_upsell-parent_to_processing', array( $wc_mails->emails['WC_Email_Customer_Processing_Order'], 'trigger' ), 10, 2 );
		add_action( 'woocommerce_order_status_upsell-failed_to_processing', array( $wc_mails->emails['WC_Email_Customer_Processing_Order'], 'trigger' ), 10, 2 );

		// WC_Email_Customer_Completed_Order will always be sent as Woo uses woocommerce_order_status_completed_notification and not specific transitions.

		// Sent to Admin when set to processing.
		add_action( 'woocommerce_order_status_upsell-parent_to_processing', array( $wc_mails->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
		add_action( 'woocommerce_order_status_upsell-failed_to_processing', array( $wc_mails->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );

		// Sent to Admin when set to completed.
		add_action( 'woocommerce_order_status_upsell-parent_to_completed', array( $wc_mails->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );
		add_action( 'woocommerce_order_status_upsell-failed_to_completed', array( $wc_mails->emails['WC_Email_New_Order'], 'trigger' ), 10, 2 );

	}

	/**
	 * Set Offers Processed for Order on Upsell Action.
	 * Except for last offer.
	 *
	 * @param string $order_id         The wc order id.
	 * @param string $current_offer_id The Offer id.
	 * @param string $url              The url.
	 *
	 * @since 3.5.1
	 */
	public function set_offers_processed_on_upsell_action( $order_id, $current_offer_id, $url ) {
		if ( empty( $order_id ) || empty( $current_offer_id ) || empty( $url ) ) {

			return;
		}

		$offers_processed = get_post_meta( $order_id, '_wps_upsell_offers_processed', true );

		$offers_processed = ! empty( $offers_processed ) ? $offers_processed : array();

		$offers_processed[ $current_offer_id ] = $url;

		update_post_meta( $order_id, '_wps_upsell_offers_processed', $offers_processed );
	}

	/**
	 * Validate if current offer is already processed on Upsell Action.
	 *
	 * @param string $order_id         Order id.
	 * @param string $current_offer_id Offer id.
	 * @since 3.5.1
	 */
	public function validate_offers_processed_on_upsell_action( $order_id, $current_offer_id ) {
		if ( empty( $order_id ) || empty( $current_offer_id ) ) {

			return;
		}

		$offers_processed = get_post_meta( $order_id, '_wps_upsell_offers_processed', true );

		if ( ! empty( $offers_processed ) && is_array( $offers_processed ) ) {

			foreach ( $offers_processed as $offer_id => $url ) {

				// When offer is already processed, redirect to previous result of action that was taken.
				if ( (string) $current_offer_id === (string) $offer_id ) {

					wp_redirect( $url ); //phpcs:ignore
					exit;
				}
			}
		}
	}

	/**
	 * Remove upsell products.
	 *
	 * @param string $order_id Order id.
	 *
	 * @since 3.5.1
	 */
	public function upsell_products_removal( $order_id = null ) {
		if ( empty( $order_id ) ) {
			return false;
		}

		$order = wc_get_order( $order_id );

		// Remove all upsell offer products.
		$upsell_items = get_post_meta( $order_id, '_upsell_remove_items_on_fail', true );

		if ( ! empty( $upsell_items ) && is_array( $upsell_items ) ) {

			foreach ( (array) $order->get_items() as $item_id => $item ) {

				if ( in_array( (string) $item_id, $upsell_items, true ) ) {
					$order->remove_item( $item_id );
				}
			}

			$order->save();
			$order->calculate_totals();

			delete_post_meta( $order_id, 'wps_upsell_order_started' );
			delete_post_meta( $order_id, 'wps_upsell_funnel_id' );
			delete_post_meta( $order_id, '_wps_wocuf_pro_upsell_shown_timestamp' );
			delete_post_meta( $order_id, 'wps_wocuf_upsell_order' );
			delete_post_meta( $order_id, '_wps_upsell_expire_further_offers' );
		}
	}

	/**
	 * Shortcode for custom form.
	 */
	public function wps_form_shortcode_callback() {
		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$offer_id  = ! empty( $_GET['ocuf_ofd'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_ofd'] ) ) : '';
		$funnel_id = ! empty( $_GET['ocuf_fid'] ) ? sanitize_text_field( wp_unslash( $_GET['ocuf_fid'] ) ) : '';

		// Show to first offer only.
		if ( isset( $funnel_id ) && '1' === $offer_id ) {

			$all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );
			$show_form   = isset( $all_funnels[ $funnel_id ]['wps_wocuf_wps_form'] ) ? $all_funnels[ $funnel_id ]['wps_wocuf_wps_form'] : false;

			$fields_array = get_option( 'wps_wocuf_custom_form_values_' . $funnel_id, false );

			if ( $fields_array && $show_form ) {
				$fld_len = count( $fields_array );
				?>
			<div class="wpsfrm">
				<ul>
					<li>
						<form id="frm" action="" method="post" onsubmit="return false" >
						<?php for ( $i = 1; $i < $fld_len; $i++ ) : ?>
						<div class="wps-wocuf__front-form-item">
							<label for="text<?php echo esc_html( $i ); ?>" class="wps_label"  id="tlabel<?php echo esc_html( $i ); ?>"><?php echo esc_html( $fields_array[ $i ]['name'] ); ?></label>
							<?php $min_attr = ''; ?>
							<?php if ( 'number' === $fields_array[ $i ]['type'] ) : ?>
								<?php $min_attr = 'min=0'; ?>
							<?php endif; ?>
							<input <?php echo esc_attr( $min_attr ); ?> type="<?php echo esc_html( $fields_array[ $i ]['type'] ); ?>" title="<?php echo esc_html( $fields_array[ $i ]['description'] ); ?>" class="wps_wocuf_box wps_wocuf_field_<?php echo esc_html( $fields_array[ $i ]['type'] ); ?>" id="text<?php echo esc_html( $i ); ?>" name="text<?php echo esc_html( $i ); ?>" placeholder="<?php echo esc_html( $fields_array[ $i ]['placeholder'] ); ?>" required>
						</div>
						<?php endfor; ?>
						</form>
					</li>
				</ul>    
			</div>
				<?php
			}
		} elseif ( empty( $offer_id ) ) {
			global $post;
			$offer_page_id = $post->ID;

			// Means this is Upsell offer template.
			$funnel_data = get_post_meta( $offer_page_id, 'wps_upsell_funnel_data', true );
			$offer_id    = ! empty( $funnel_data['offer_id'] ) ? wc_clean( $funnel_data['offer_id'] ) : '';
			$funnel_id   = ! empty( $funnel_data['funnel_id'] ) ? wc_clean( $funnel_data['funnel_id'] ) : '';

			$all_funnels = get_option( 'wps_wocuf_pro_funnels_list', array() );
			$show_form   = isset( $all_funnels[ $funnel_id ]['wps_wocuf_wps_form'] ) ? $all_funnels[ $funnel_id ]['wps_wocuf_wps_form'] : false;

			$fields_array = get_option( 'wps_wocuf_custom_form_values_' . $funnel_id, false );

			if ( $fields_array && $show_form ) {
				$fld_len = count( $fields_array );
				?>

				<div class="wpsfrm">
				<ul>
					<li>
						<form id="frm" action="" method="post" onsubmit="return false" >
				<?php for ( $i = 1; $i < $fld_len; $i++ ) : ?>
							<div class="wps-wocuf__front-form-item">
									<label for="text<?php echo esc_html( $i ); ?>" class="wps_label"  id="tlabel<?php echo esc_html( $i ); ?>"><?php echo esc_html( $fields_array[ $i ]['name'] ); ?></label>

									<input type="<?php echo esc_html( $fields_array[ $i ]['type'] ); ?>" title="<?php echo esc_html( $fields_array[ $i ]['description'] ); ?>" class="wps_wocuf_box" id="text<?php echo esc_html( $i ); ?>" name="text<?php echo esc_html( $i ); ?>" placeholder="<?php echo esc_html( $fields_array[ $i ]['placeholder'] ); ?>" required>
							</div>
				<?php endfor; ?>
						</form>
					</li>
				</ul>
				</div>
				<?php
			}
		}

	}

	/**
	 * Save Custom upsell form.
	 *
	 * @param  mixed $data    form data.
	 * @param  mixed $item_id order item id.
	 * @return void
	 */
	public function wps_form_save( $data, $item_id ) {
		if ( ! empty( $data ) ) {
			$jdata = json_decode( $data );
			foreach ( $jdata as $key => $value ) {
				wc_add_order_item_meta( $item_id, $key, $value );
			}
		}
	}

	/**
	 * Shortcode for displaying additional offers on Offer page.
	 *
	 * @return void
	 */
	public function wps_wocuf_additional_offers() {
		$live_params_from_url = wps_upsell_get_pid_from_url_params();
		$funnel_id            = ! empty( $live_params_from_url['funnel_id'] ) ? sanitize_text_field( wp_unslash( $live_params_from_url['funnel_id'] ) ) : '';
		$offer_id             = ! empty( $live_params_from_url['offer_id'] ) ? sanitize_text_field( wp_unslash( $live_params_from_url['offer_id'] ) ) : '';
		$order_id             = ! empty( $live_params_from_url['order_id'] ) ? sanitize_text_field( wp_unslash( $live_params_from_url['order_id'] ) ) : '';

		// If both ids are present in url then it is live offer.
		if ( ! empty( $offer_id ) && ! empty( $funnel_id ) ) {
			$action = 'javascript:void(0);';
		} else {
			$action = '#preview';
		}

		if ( empty( $offer_id ) ) {
			global $post;
			$offer_page_id = $post->ID;

			// Means this is Upsell offer template.
			$funnel_data = get_post_meta( $offer_page_id, 'wps_upsell_funnel_data', true );
			$offer_id    = ! empty( $funnel_data['offer_id'] ) ? wc_clean( $funnel_data['offer_id'] ) : '';
			$funnel_id   = ! empty( $funnel_data['funnel_id'] ) ? wc_clean( $funnel_data['funnel_id'] ) : '';
		}

		$all_funnels          = get_option( 'wps_wocuf_pro_funnels_list', array() );
		$show_offers          = isset( $all_funnels[ $funnel_id ]['wps_wocuf_add_products'] ) ? $all_funnels[ $funnel_id ]['wps_wocuf_add_products'] : false;
		$add_offer_products   = isset( $all_funnels[ $funnel_id ]['wps_wocuf_pro_add_products_in_offer'] ) ? $all_funnels[ $funnel_id ]['wps_wocuf_pro_add_products_in_offer'] : false;
		$upsell_offer_product = isset( $all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'][ $offer_id ] ) ? $all_funnels[ $funnel_id ]['wps_wocuf_pro_products_in_offer'][ $offer_id ] : false;

		// Check if any of the addtitional offer is available on the offered product.
		if ( ! empty( $add_offer_products ) && is_array( $add_offer_products ) && in_array( $upsell_offer_product, $add_offer_products, true ) ) {
			$key = array_search( $upsell_offer_product, $add_offer_products, true );
			if ( ! empty( $key ) || 0 === $key ) {
				unset( $add_offer_products[ $key ] );
			}
		}

		?>
		<?php if ( $show_offers && $add_offer_products ) : ?>
			<div class="wps-banner">
				<div class="wps-banner__carousel">
				<?php if ( ! empty( $add_offer_products ) && is_array( $add_offer_products ) ) : ?>
					<?php foreach ( $add_offer_products as $key => $product_id ) : ?>
						<?php
						$post_type                = get_post_type( $product_id );
						$upsell_product           = wc_get_product( $product_id );
						$upsell_product_image     = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' );
						$upsell_product_image_src = ! empty( $upsell_product_image[0] ) ? $upsell_product_image[0] : wc_placeholder_img_src();

						if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {
							continue;
						}
						?>
					<div class="wps-banner__carousel--item offer-<?php echo esc_html( $upsell_product->get_type() ); ?>">
						<div class="wps-banner__carousel--item-inner">
							<div class="wps-banner__carousel-img">
								<img class="wps_additional_offer_image" src="<?php echo esc_url( $upsell_product_image_src ); ?>">
							</div>
							<h3 class="wps-banner__carousel-heading">
								<?php echo esc_html( $upsell_product->get_name() ); ?>
							</h3>
							<p class="wps-banner__carousel-desc">
								<?php $this->additional_product_price_html( $product_id ); ?>
							</p>
							<?php if ( 'variable' === $upsell_product->get_type() ) : ?>
								<?php $this->additional_product_variation_html( $upsell_product, $funnel_id, $offer_id ); ?> 
							<?php endif; ?>
							<?php $is_added = get_post_meta( $order_id, 'wps_wocuf_additional_added_' . $product_id, true ); ?>
							<?php if ( ! empty( $is_added ) ) : ?>
								<button type="submit" class="button" disabled="" aria-disabled="true"><?php esc_html_e( 'Offer Added', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></button>
							<?php else : ?>
								<a class="wps_accept_add_offer checkout-button button alt wc-forward" data-id="<?php echo esc_attr( $product_id ); ?>" href="<?php echo esc_html( $action ); ?>"><?php esc_html_e( 'Add offer', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
							<?php endif; ?>
						</div>
					</div>
					<?php endforeach; ?>
				<?php endif; ?>
				</div>
			</div>

			<!-- No thanks Button. -->
			<div class="wps_wocuf_continue">
				<a id="wps_wocuf_pro_add_continue" href="<?php echo esc_html( '#preview' !== $action ? '[wps_upsell_no]' : '#preview' ); ?>"><?php esc_html_e( 'Continue without main offer', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Additional Offer.
	 */
	public function add_additional_offer_to_order() {
		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		check_ajax_referer( 'wps_wocuf_nonce', 'nonce' );

		$order_key      = ! empty( $_POST['order_key'] ) ? sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) : '';
		$funnel_id      = ! empty( $_POST['funnel_id'] ) ? sanitize_text_field( wp_unslash( $_POST['funnel_id'] ) ) : '';
		$quantity       = 1;
		$order_id       = wc_get_order_id_by_order_key( $order_key );
		$product_id     = ! empty( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		$upsell_product = wc_get_product( $product_id );
		$order          = new WC_Order( $order_id );

		if ( empty( $product_id ) ) {
			return;
		}

		/**
		 * Save item ids, which are being added.
		 */
		$upsell_items = get_post_meta( $order_id, '_upsell_remove_items_on_fail', true );

		if ( empty( $upsell_items ) ) {
			$upsell_items = array();
		}

		// If Downloadable.
		if ( $upsell_product->is_downloadable() ) {

			// Add downloadable permission for each file.
			$download_files = $upsell_product->get_downloads();
			foreach ( $download_files as $download_id => $file ) {
				wc_downloadable_file_permission( $download_id, $upsell_product->id, $order );
			}
		}

		if ( wps_upsell_is_subscription_product( $upsell_product ) && wps_upsell_pg_supports_subs( $order_id ) ) {

			// If Subscription product then create Subscription for current Upsell offer.
			wps_upsell_create_subscription_for_upsell_product( $order_id, $upsell_product, $quantity );

			// If Subscription product then handle Subscription price that will be set for current Upsell Product to be added to Order.
			$upsell_product_subs_modified = wps_upsell_subs_set_price_accordingly( $upsell_product );

			$upsell_item_id = $order->add_product( $upsell_product_subs_modified, $quantity );

		} else {

			// Update (v3.6.7) starts.
			if ( class_exists( 'WC_PB_Order' ) && $upsell_product && $upsell_product->is_type( 'bundle' ) ) {

				global $wpdb;
				$instance       = WC_PB_Order::instance();
				$upsell_item_id = $instance->add_bundle_to_order( $upsell_product, $order, 1, array() );
				$order->save();

				$bundled_items = $upsell_product->get_bundled_data_items();

				if ( ! empty( $bundled_items ) && is_array( $bundled_items ) ) {
					foreach ( $bundled_items as $bundle_item ) {
						$bundle_data  = $bundle_item->get_data();
						$_product_id  = $bundle_data['product_id'];
						$download_ids = array_keys( (array) wc_get_product( $_product_id )->get_downloads() );

						if ( ! empty( $download_ids ) && is_array( $download_ids ) ) {
							foreach ( $download_ids as $download_id ) {

								if ( apply_filters( 'woocommerce_process_product_file_download_paths_grant_access_to_new_file', true, $download_id, $product_id, $order ) ) {
									// Grant permission if it doesn't already exist.
									if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT 1=1 FROM {$wpdb->prefix}woocommerce_downloadable_product_permissions WHERE order_id = %d AND product_id = %d AND download_id = %s", $order->get_id(), $product_id, $download_id ) ) ) {
										wc_downloadable_file_permission( $download_id, $_product_id, $order );
									}
								}
							}
						}
					}
				}
				// Update (v3.6.7) ends.
			} else {
				$upsell_item_id = $order->add_product( $upsell_product, $quantity );

			}

			// Process WPS Subscriptions for pre upsell products from Order.
			if ( class_exists( 'Subscriptions_For_Woocommerce_Compatiblity' ) && true === Subscriptions_For_Woocommerce_Compatiblity::pg_supports_subs( $order_id ) && true === Subscriptions_For_Woocommerce_Compatiblity::is_subscription_product( $upsell_product ) ) {
				$compat_class = new Subscriptions_For_Woocommerce_Compatiblity( 'Subscriptions_For_Woocommerce_Compatiblity', '1.0.0' );
				$compat_class->create_upsell_subscription( $order_id, $upsell_item_id );
			}
		}

		// Add Offer Accept Count for the current Funnel.
		$sales_by_funnel = new WPS_Upsell_Report_Sales_By_Funnel( $funnel_id );
		$sales_by_funnel->add_offer_accept_count();

		array_push( $upsell_items, $upsell_item_id );
		update_post_meta( $order_id, '_upsell_remove_items_on_fail', $upsell_items );

		/**
		 * Add meta to item id so that it upsell product can be distinguished.
		 */
		wc_add_order_item_meta( $upsell_item_id, 'is_upsell_purchase', 'true' );

		update_post_meta( $order_id, 'wps_wocuf_additional_added_' . $product_id, 'true' );

		if ( ! empty( $upsell_product->get_parent_id() ) ) {
			$product_id = $upsell_product->get_parent_id();
			update_post_meta( $order_id, 'wps_wocuf_additional_added_' . $product_id, 'true' );
		}

		update_post_meta( $order_id, 'wps_wocuf_upsell_order', 'true' );

		$order->calculate_totals( true );
		echo wp_json_encode( 'added' );
		wp_die();
	}

	/**
	 * Remove Currency switcher on upsell page.
	 *
	 * @param mixed $content content.
	 * @since 3.6.3
	 */
	public function hide_switcher_on_upsell_page( $content = '' ) {

		$validate_shortcode = $this->validate_shortcode();
		if ( 'live_offer' === $validate_shortcode ) {
			return '';
		} else {
			return $content;
		}
	}

	/**
	 * Remove Currency switcher in session on deactivate.
	 *
	 * @since 3.6.3
	 */
	public function check_compatibltiy_instance_cs() {
		if ( function_exists( 'wps_upsell_lite_is_plugin_active' ) ) {
			$cs_exists = wps_upsell_lite_is_plugin_active( 'wps-multi-currency-switcher-for-woocommerce/wps-multi-currency-switcher-for-woocommerce.php' );
			if ( false === $cs_exists ) {
				if ( ! empty( WC()->session ) && WC()->session->__isset( 's_selected_currency' ) ) {
					WC()->session->__unset( 's_selected_currency' );
				}
			}
		}
	}

	/**
	 * Shortcode for Upsell product price.
	 *
	 * @param mixed $product_id shortcode atributes.
	 * @param mixed $action shortcode atributes.
	 * @since 3.0.0
	 */
	public function additional_product_price_html( $product_id, $action = '' ) {
		if ( ! empty( $product_id ) ) {
			$post_type = get_post_type( $product_id );
			if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {
				return '';
			}

			$upsell_product            = wc_get_product( $product_id );
			$upsell_product_price_html = $upsell_product->get_price_html();
			$upsell_product_price_html = ! empty( $upsell_product_price_html ) ? $upsell_product_price_html : '';

			if ( ! empty( WC()->session ) && WC()->session->__isset( 's_selected_currency' ) && function_exists( 'wps_wmcs_fixed_price_for_simple_sales_price' ) ) {
				if ( empty( $upsell_product->get_parent_id() ) ) {
					$_regular_price = wps_wmcs_fixed_price_for_variable_regular_price( $upsell_product->get_id() );
					$_sale_price    = wps_wmcs_fixed_price_for_variable_sales_price( $upsell_product->get_id() );
				} else {
					$_regular_price = wps_wmcs_fixed_price_for_simple_regular_price( $upsell_product->get_id() );
					$_sale_price    = wps_wmcs_fixed_price_for_simple_sales_price( $upsell_product->get_id() );
				}

				// In case of fixed custom price in currency switcher.
				if ( ! empty( $_regular_price ) || ! empty( $_sale_price ) ) {
					if ( ! empty( $_sale_price ) ) {
						$upsell_product_price_html = wc_format_sale_price( $_regular_price, $_sale_price );
					} else {
						$upsell_product_price_html = wc_price( $_regular_price );
					}
				}
			}

			$upsell_product_price_html_div = "<div class='wps_upsell_additional_offer_product_price'>$upsell_product_price_html</div>";

			if ( ! empty( WC()->session ) && WC()->session->__isset( 's_selected_currency' ) ) {
				$selected_currency = WC()->session->get( 's_selected_currency' );
				$store_currency    = get_woocommerce_currency();

				if ( $selected_currency !== $store_currency ) {
					$store_currency_symbol    = get_option( 'wps_mmcsfw_symbol_' . $store_currency );
					$selected_currency_symbol = get_option( 'wps_mmcsfw_symbol_' . $selected_currency );

					// Remove default currency into selected currency.
					$upsell_product_price_html_div = str_replace( $store_currency_symbol, $selected_currency_symbol, $upsell_product_price_html_div );
				}
			}

			if ( 'return' === $action ) {
				return $upsell_product_price_html_div;
			} else {
				echo wp_kses_post( $upsell_product_price_html_div, wps_upsell_lite_allowed_html() );
			}
		}
	}

	/**
	 * Variation product.
	 *
	 * @param mixed $product   WC product object.
	 * @param mixed $funnel_id funnel id triggered.
	 * @param mixed $offer_id  offer id triggered.
	 *
	 * @since @since 3.6.4
	 */
	public function additional_product_variation_html( $product = false, $funnel_id = false, $offer_id = false ) {

		$attributes = $product->get_variation_attributes();

		// Return if no attributes are present.
		if ( empty( $attributes ) ) {
			return;
		}

		$all_variations = $product->get_available_variations();
		$v_matcher_ids  = array();
		$v_matcher_img  = array();
		$v_attributes   = array();
		$v_p_html       = array();

		if ( ! empty( $all_variations ) && is_array( $all_variations ) ) {
			foreach ( $all_variations as $key => $variation ) {

				foreach ( $variation['attributes'] as $key => $value ) {
					if ( empty( $value ) ) {
						$variation['attributes'][ $key ] = 'any';
					}
				}

				$v_matcher_ids[] = ! empty( $variation['variation_id'] ) ? $variation['variation_id'] : '';
				$v_matcher_img[] = ! empty( $variation['image']['src'] ) ? $variation['image']['src'] : '';
				$v_attributes[]  = ! empty( $variation['attributes'] ) ? $variation['attributes'] : '';
				$v_p_html[]      = $this->additional_product_price_html( $variation['variation_id'], 'return' );
			}
		}

		$v_matcher_ids = wp_json_encode( $v_matcher_ids );
		$v_matcher_img = wp_json_encode( $v_matcher_img );
		$v_attributes  = wp_json_encode( $v_attributes );
		$v_p_html      = wp_json_encode( $v_p_html );
		?>

		<input type="hidden" 
		data-ids="<?php echo esc_attr( $v_matcher_ids ); ?>"
		data-img="<?php echo esc_attr( $v_matcher_img ); ?>"
		data-attr="<?php echo esc_attr( $v_attributes ); ?>"
		data-p-html="<?php echo esc_attr( $v_p_html ); ?>"
		class="wps-wocuf-prod-variation-matcher">

		<!-- Printing all the variation dropdown. -->
		<?php foreach ( $attributes as $attribute_name => $options ) : ?>
			<div class="wps_wocuf_input_row">
				<p class="wps_wocuf_pro_bump_attributes_name">
					<!-- In case slug is encountered. -->
					<?php $show_title = str_replace( 'pa_', '', $attribute_name ); ?>
					<?php $attribute_name = str_replace( ' ', '-', $attribute_name ); ?>
					<?php echo esc_html( $show_title ); ?>
				</p>
				<?php
					// Function to return variations select html.
					$variation_dropdown = $this->wps_wocuf_variation_dropdown(
						array(
							'options'   => $options,
							'attribute' => $attribute_name,
							'product'   => $product,
							'selected'  => '',
							'id'        => 'attribute_' . strtolower( $attribute_name ),
							'class'     => 'wps_wocuf_additional_variation_select',
							'funnel_id' => $funnel_id,
							'offer_id'  => $offer_id,
						)
					);

					echo $variation_dropdown;  // phpcs:ignore
				?>
			</div>

		<?php endforeach; ?>
			<?php
	}

	/**
	 * Variation product.
	 *
	 * @param array $args arguments.
	 * @since 3.6.4
	 */
	public function wps_wocuf_variation_dropdown( $args = array() ) {

		$args = wp_parse_args(
			apply_filters( 'woocommerce_dropdown_variation_attribute_options_args', $args ),
			array(
				'options'          => false,
				'attribute'        => false,
				'selected'         => false,
				'name'             => '',
				'id'               => '',
				'class'            => '',
				'show_option_none' => false,
			)
		);

		$funnel_id             = ! empty( $args['funnel_id'] ) ? $args['funnel_id'] : '0';
		$offer_id              = ! empty( $args['offer_id'] ) ? $args['offer_id'] : '0';
		$options               = $args['options'];
		$product               = $args['product'];
		$attribute             = $args['attribute'];
		$name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
		$id                    = $args['id'] ? sanitize_title( $args['id'] ) : sanitize_title( $attribute );
		$class                 = $args['class'];
		$show_option_none      = $args['show_option_none'] ? true : false;
		$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : esc_html__( 'Choose an option', 'one-click-upsell-funnel-for-woocommerce-pro' );

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[ $attribute ];
		}

		$html = '<select funnel_id="' . $funnel_id . '" offer_id="' . $offer_id . '" order="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" class="' . esc_attr( sanitize_title( $class ) ) . '" name="' . esc_attr( $name ) . '" data-attribute-name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '" >';

		$html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

		if ( ! empty( $options ) ) {

			if ( $product && taxonomy_exists( $attribute ) ) {

				// Get terms if this is a taxonomy - ordered. We need the names too.
				$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );

				foreach ( $terms as $term ) {

					if ( in_array( $term->slug, $options, true ) ) {
						$html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
					}
				}
			} else {

				foreach ( $options as $option ) {

					// This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
					$selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );

					$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
				}
			}
		}

		$html .= '</select>';

		return $html;
	}

	// End of class.
}