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
		// adding action to display price at cart page.

		// it is used to add custom price which is price according to stock based pricing to the cart page.
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'add_custom_price' ) );
		// it is used to display the custom price range from min to max amount according to different pricing.
		add_filter( 'woocommerce_format_price_range', array( $this, 'sbp_change_price_range_for_variation' ), 10, 3 );


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

	}

}
