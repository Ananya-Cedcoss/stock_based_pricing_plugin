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
		wp_localize_script( $this->plugin_name . 'common', 'sbpp_common_param', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_script( $this->plugin_name . 'common' );
	}
}