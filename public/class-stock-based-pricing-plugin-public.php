<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Stock_based_pricing_plugin
 * @subpackage Stock_based_pricing_plugin/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 * namespace stock_based_pricing_plugin_public.
 *
 * @package    Stock_based_pricing_plugin
 * @subpackage Stock_based_pricing_plugin/public
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Stock_based_pricing_plugin_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;	

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function sbpp_public_enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'public/src/scss/stock-based-pricing-plugin-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function sbpp_public_enqueue_scripts() {
		wp_register_script( $this->plugin_name, STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'public/src/js/stock-based-pricing-plugin-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'sbpp_public_param', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_script( $this->plugin_name );
		// Adding mwb-admin.js 
		wp_register_script( 'sbpp_my_custom_script', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'public/js/mwb-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( 'sbpp_my_custom_script', 'sbpp_public_custom_param', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),'sbpp_gen_tab_enable' => get_option( 'sbpp_radio_switch_demo' ), ) );
		wp_enqueue_script( 'sbpp_my_custom_script' );
	}


	/** This Function add_custom_price is used to display the Price According to stock.
	 *
	 * @param object $cart_object is used to get all cart objects.
	 */
	public function add_custom_price( $cart_object ) {
		foreach ( $cart_object->cart_contents as $key => $value ) {
			$sbpp_custom_price = 0; // assigning custom price to 0.
		
			if ( $value['variation_id'] == 0 ) { // checking variation value.
				$sbpp_get_price = get_post_meta( $value['product_id'], 'Price_of_Selected_variation' ); // assigning price from post meta data to the variable if it is type of variable product.
				$post_id        = $value['product_id'];
			} else {
				$sbpp_get_price = get_post_meta( $value['variation_id'], 'Price_of_Selected_variation' ); // assign price to the variable if it is simple product type.
			}		
			if ( ! empty( $sbpp_get_price[0] ) ) {
				global $WOOCS; // global variable.
				if ( class_exists( 'WOOCS' ) ) {
					$sbpp_regular_price_u_s_d = get_post_meta( $post_id, '_woocs_regular_price_' . $WOOCS->current_currency . '' ); // assign post meta to the variable.				
					if (! empty( $sbpp_regular_price_u_s_d[0] ) ) {
						if ( $WOOCS->current_currency != $WOOCS->default_currency ) {
						echo $price;
						}
					} else {
						$sbpp_custom_price = $sbpp_get_price[0];
					//	$abc = apply_filters( 'woocs_convert_price', $sbpp_get_price[0], false );
						$value['data']->set_price( $sbpp_custom_price );
					}
				} else {
					$sbpp_custom_price = $sbpp_get_price[0];					
					$value['data']->set_price( $sbpp_custom_price );
				}
			}
		}
	}


	/** Update the price range according to stock base pricing
	 *
	 * @param int $price is the price of the product.
	 * @param int $from is the Minimum price.
	 * @param int $to is the maximum rice.
	 */
	public function sbp_change_price_range_for_variation( $price, $from, $to ) {
		global $post;  // is used to get post object for the current post.
		$sbpp_min_to_display        = 0; // set sbpp_min_to_display to 0.
		$sbpp_max_to_display        = 0; // set sbpp_min_to_display to 0;.
		$product                    = wc_get_product( $post->ID ); // get the product data.
		$sbpp_current_products      = $product->get_children(); // get all the variation of any product if it is variable type product.
		$sbpp_current_product_count = count( $sbpp_current_products ); // get number of variation of any product.
		if ( $sbpp_current_product_count > 0 ) {
			foreach ( $sbpp_current_products as $key => $variation_id ) {
				$sbpp_data         = get_post_meta( $variation_id, '_price_acc_to_stock_var' ); // assigning post meta data to the sbpp_data variable.
				$sbpp_pricing_list = json_decode( $sbpp_data[0], true ); // Convert the post meta into array and assign it to variable.
				$children_product  = wc_get_product( $variation_id ); // get the product data.
				$stock             = $children_product->get_stock_quantity();
				if ( ! empty( $sbpp_pricing_list )) {
					foreach ( $sbpp_pricing_list as $key => $value ) {
						$amount = $value['Amount']; // set the amount of each list.
						if ( $sbpp_min_to_display == 0 ) {
							$sbpp_min_to_display = $amount; // if sbpp_min_to_display is 0 then amount will be assigned.
						} else {
							if ( $amount < $sbpp_min_to_display ) {
								if ( $amount != '' ) {
									$sbpp_min_to_display = $amount; // assign value of amount if amount will be less than.
								}
							}
						}
						if ( $amount > $sbpp_max_to_display ) {
							if ( $amount != '' ) {
								$sbpp_max_to_display = $amount; // assign value of amount if amount will be greater than sbpp_max_to_display.
							}
						}
					}
				}
			}
		}	
		if ( ! empty( $sbpp_min_to_display ) ) { // check id  sbpp_min_to_display is not empty.
			if ( $sbpp_min_to_display > $from ) {
				$final_min = $from;
			} else {
					$final_min = $sbpp_min_to_display;
			}
			if ( $sbpp_max_to_display > $to ) {

				$final_max = $sbpp_max_to_display;
			} else {
					$final_max = $to;
			}
			return sprintf( '%s: %s', wc_price( $final_min ), wc_price( $final_max ) ); // return the price according to stock based pricing.
		} else {
			return sprintf( '%s: %s', wc_price( $from ), wc_price( $to ) ); // return the regular price range for the variations.
		}
	}

	
	public function sbpp_change_minicart_item_price( $price, $cart_item, $cart_item_key ) {
		if( class_exists( 'WOOCS' ) ) {
			return $price;
		}
		if ( ! is_cart() ) {			
			$price = $cart_item['data']->variation_id;
			if ( ! empty($cart_item['data']->variation_id) ) {
				
				$sbpp_get_price = get_post_meta( $cart_item['data']->variation_id, 'Price_of_Selected_variation' ); // assigning price from post meta data to the variable if it is type of variable product.
			} else {
				$sbpp_get_price = get_post_meta( $cart_item['data']->id, 'Price_of_Selected_variation' ); // assign price to the variable if it is simple product type.
			}	
			if ( ! empty( $sbpp_get_price[0] ) ) {
				$price = $sbpp_get_price[0];
			return $price;
			}
		}	
	return $price;
	}
}
