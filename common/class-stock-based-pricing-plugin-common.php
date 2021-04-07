<?php
/**
 * The common functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Stock_based_pricing_plugin
 * @subpackage Stock_based_pricing_plugin/common
 */

/**
 * The common functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the common stylesheet and JavaScript.
 * namespace stock_based_pricing_plugin_common.
 *
 * @package    Stock_based_pricing_plugin
 * @subpackage Stock_based_pricing_plugin/common
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Stock_based_pricing_plugin_Common {
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
		// All the Action and filter that will be used to show  dynamic pricing to the single product page.

		// filter used to display changed price.
		add_filter( 'woocommerce_get_price_html', array( $this, 'sbp_change_product_price_display' ) );
		// action to get price data through ajax.
		add_action( 'wp_ajax_action_to_get_variation_price', array( $this, 'action_to_get_variation_price' ) );

	}

	/**
	 * Register the stylesheets for the common side of the site.
	 *
	 * @since    1.0.0
	 */
	public function sbpp_common_enqueue_styles() {
		wp_enqueue_style( $this->plugin_name . 'common', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'common/src/scss/stock-based-pricing-plugin-common.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the common side of the site.
	 *
	 * @since    1.0.0
	 */
	public function sbpp_common_enqueue_scripts() {
		wp_register_script( $this->plugin_name . 'common', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'common/src/js/stock-based-pricing-plugin-common.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name . 'common', 'sbpp_common_param', array( 'ajaxurl' => admin_url( 'admin-ajax.php'),
		'nonce' => wp_create_nonce( 'ajax-nonce' ), ) );
		wp_enqueue_script( $this->plugin_name . 'common' );
	}


	/** This function sbp_change_product_price_display is used to display price
	 *
	 * @param string $price    The price of this plugin.
	 */
	public function sbp_change_product_price_display( $price ) {
		global $post; // is used to get post object for the current post.
		$flag              = false; // assign the boolean value.
		$sbpp_product_type = get_the_terms( $post->ID, 'product_type' )[0]->slug; // assign the type of product.



		if ( is_product() ) {

			if ( $sbpp_product_type == 'simple' ) {
				$sbpp_product = wc_get_product( $post->ID );// assigning the product data to the variable.
				if ( ! is_cart() ) {
					$stock = $sbpp_product->get_stock_quantity(); // get the quantity of stock.
				}

				$sbpp_data = get_post_meta( $post->ID, '_price_acc_to_stock' ); // assign post meta to the variable.

				$pricing = json_decode( $sbpp_data[0], true ); // converting the data to the array and storing it.

				$priceofstock = ''; // new blank variable declaration.

				if ( ! empty( $pricing ) ) {		
					foreach ( $pricing as $key => $value ) {

						$minimum_val = $value['Min']; // assigning the min value.
						$max_value   = $value['Max']; // assigning the max value.
						$amount      = $value['Amount']; // assigning the amount value.

						if ( $stock >= $minimum_val && $stock <= $max_value ) {

							$priceofstock = $amount; // assigning amount value to the variable.
							$flag = true; // assign bool variable.
							update_post_meta( $post->ID, 'Price_of_Selected_variation', $priceofstock );// used to update the post meta data.
						}
					}
				}	
			}
			if ( $flag === true ) {
				return get_woocommerce_currency_symbol() . $priceofstock; // return the price according to stock based pricing.
			} else {
				return $price;// return the regular price.
			}
		} else {

			if ( $sbpp_product_type == 'simple' ) {
				$sbpp_product = wc_get_product( $post->ID );// assigning the product data to the variable.
				if ( ! is_cart() ) {
					$stock = $sbpp_product->get_stock_quantity(); // get the quantity of stock.
				}

				$sbpp_data = get_post_meta( $post->ID, '_price_acc_to_stock' ); // assign post meta to the variable.

				$pricing = json_decode( $sbpp_data[0], true ); // converting the data to the array and storing it.

				$priceofstock = ''; // new blank variable declaration.

			if ( ! empty( $pricing ) ) {	
					foreach ( $pricing as $key => $value ) {

						$minimum_val = $value['Min']; // assigning the min value.
						$max_value   = $value['Max']; // assigning the max value.
						$amount      = $value['Amount']; // assigning the amount value.

						if ( $stock >= $minimum_val && $stock <= $max_value ) {

							$priceofstock = $amount; // assigning amount value to the variable.
							$flag = true; // assign bool variable.
							update_post_meta( $post->ID, 'Price_of_Selected_variation', $priceofstock );// used to update the post meta data.
						}
					}
				}
			}
			if ( $flag === true ) {
				echo get_woocommerce_currency_symbol() . $priceofstock; // return the price according to stock based pricing.
			} else {
				echo $price;// return the regular price.
			}

		}

	}

	/** This my_actionssforshortcode is used to return content to the ajax calling  */
	public function action_to_get_variation_price() {

		if ( ! wp_verify_nonce( ( $_POST['nonce'] ), 'ajax-nonce' ) ) {
			die( 'Busted!' );
		}
		if ( ! empty( $_POST['Variation_Id'] ) ) {
			$variation_id = ( $_POST['Variation_Id'] ); // assigning variation id.
		}


			$sbpp_variation_obj  = new WC_Product_variation( $variation_id ); // assigning variation object to the variable.
			$sbpp_stock_quantity = $sbpp_variation_obj->get_stock_quantity(); // assigning the stock quantity.
			$sbpp_postmetadata   = get_post_meta( $variation_id, '_price_acc_to_stock_var' ); // assigning post meta data to the variable.
			$sbpp_pricing        = json_decode( $sbpp_postmetadata[0], true ); // decoding the data into array.

			$priceofstock = ''; // assigning blank variable.

		foreach ( $sbpp_pricing as $key => $value ) {

			$minimum_val = $value['Min']; // get min value.
			$max_value   = $value['Max']; // get max value.
			$amount      = $value['Amount']; // get amount value.

			if ( $sbpp_stock_quantity >= $minimum_val && $sbpp_stock_quantity <= $max_value ) {
				$priceofstock = $amount; // assign amount to the current price of stock.
				$flag         = true; // makes the flag true.
			}
		}
		if ( $flag === true ) {
			$result = $priceofstock; // assign priceofstock to result.
		} else {
			$result = $sbpp_variation_obj->get_regular_price(); // assign regular price from variation object.
		}
		update_post_meta( $variation_id, 'Price_of_Selected_variation', $result ); // update price to the post meta data.
		echo esc_attr( $result ); // echo the result to the ajax calling.
		wp_die(); // this is required to terminate immediately and return a proper response.
	}

}
