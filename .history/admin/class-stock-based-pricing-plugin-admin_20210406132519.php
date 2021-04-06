<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Stock_based_pricing_plugin
 * @subpackage Stock_based_pricing_plugin/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Stock_based_pricing_plugin
 * @subpackage Stock_based_pricing_plugin/admin
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Stock_based_pricing_plugin_Admin {

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @param    string $hook      The plugin page slug.
	 */
	public function sbpp_admin_enqueue_styles( $hook ) {
		$screen = get_current_screen();
		if ( isset( $screen->id ) && 'makewebbetter_page_stock_based_pricing_plugin_menu' == $screen->id ) {

			wp_enqueue_style( 'mwb-sbpp-select2-css', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/select-2/stock-based-pricing-plugin-select2.css', array(), time(), 'all' );

			wp_enqueue_style( 'mwb-sbpp-meterial-css', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/material-design/material-components-web.min.css', array(), time(), 'all' );
			wp_enqueue_style( 'mwb-sbpp-meterial-css2', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/material-design/material-components-v5.0-web.min.css', array(), time(), 'all' );
			wp_enqueue_style( 'mwb-sbpp-meterial-lite', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/material-design/material-lite.min.css', array(), time(), 'all' );

			wp_enqueue_style( 'mwb-sbpp-meterial-icons-css', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/material-design/icon.css', array(), time(), 'all' );

			wp_enqueue_style( $this->plugin_name . '-admin-global', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'admin/src/scss/stock-based-pricing-plugin-admin-global.css', array( 'mwb-sbpp-meterial-icons-css' ), time(), 'all' );

			wp_enqueue_style( $this->plugin_name, STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'admin/src/scss/stock-based-pricing-plugin-admin.scss', array(), $this->version, 'all' );
			wp_enqueue_style( 'mwb-admin-min-css', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'admin/css/mwb-admin.min.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @param    string $hook      The plugin page slug.
	 */
	public function sbpp_admin_enqueue_scripts( $hook ) {

		$screen = get_current_screen();
		if ( isset( $screen->id ) && 'makewebbetter_page_stock_based_pricing_plugin_menu' == $screen->id ) {
			wp_enqueue_script( 'mwb-sbpp-select2', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/select-2/stock-based-pricing-plugin-select2.js', array( 'jquery' ), time(), false );

			wp_enqueue_script( 'mwb-sbpp-metarial-js', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/material-design/material-components-web.min.js', array(), time(), false );
			wp_enqueue_script( 'mwb-sbpp-metarial-js2', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/material-design/material-components-v5.0-web.min.js', array(), time(), false );
			wp_enqueue_script( 'mwb-sbpp-metarial-lite', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/material-design/material-lite.min.js', array(), time(), false );

			wp_register_script( $this->plugin_name . 'admin-js', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'admin/src/js/stock-based-pricing-plugin-admin.js', array( 'jquery', 'mwb-sbpp-select2', 'mwb-sbpp-metarial-js', 'mwb-sbpp-metarial-js2', 'mwb-sbpp-metarial-lite' ), $this->version, false );

			wp_localize_script(
				$this->plugin_name . 'admin-js',
				'sbpp_admin_param',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'reloadurl' => admin_url( 'admin.php?page=stock_based_pricing_plugin_menu' ),
					'sbpp_gen_tab_enable' => get_option( 'sbpp_radio_switch_demo' ),
				)
			);

			wp_enqueue_script( $this->plugin_name . 'admin-js' );
		}
	}

	/**
	 * Adding settings menu for stock-based-pricing-plugin.
	 *
	 * @since    1.0.0
	 */
	public function sbpp_options_page() {
		global $submenu;
		if ( empty( $GLOBALS['admin_page_hooks']['mwb-plugins'] ) ) {
			add_menu_page( __( 'MakeWebBetter', 'stock-based-pricing-plugin' ), __( 'MakeWebBetter', 'stock-based-pricing-plugin' ), 'manage_options', 'mwb-plugins', array( $this, 'mwb_plugins_listing_page' ), STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'admin/src/images/MWB_Grey-01.svg', 15 );
			$sbpp_menus = apply_filters( 'mwb_add_plugins_menus_array', array() );
			if ( is_array( $sbpp_menus ) && ! empty( $sbpp_menus ) ) {
				foreach ( $sbpp_menus as $sbpp_key => $sbpp_value ) {
					add_submenu_page( 'mwb-plugins', $sbpp_value['name'], $sbpp_value['name'], 'manage_options', $sbpp_value['menu_link'], array( $sbpp_value['instance'], $sbpp_value['function'] ) );
				}
			}
		}
	}

	/**
	 * Removing default submenu of parent menu in backend dashboard
	 *
	 * @since   1.0.0
	 */
	public function mwb_sbpp_remove_default_submenu() {
		global $submenu;
		if ( is_array( $submenu ) && array_key_exists( 'mwb-plugins', $submenu ) ) {
			if ( isset( $submenu['mwb-plugins'][0] ) ) {
				unset( $submenu['mwb-plugins'][0] );
			}
		}
	}


	/**
	 * stock-based-pricing-plugin sbpp_admin_submenu_page.
	 *
	 * @since 1.0.0
	 * @param array $menus Marketplace menus.
	 */
	public function sbpp_admin_submenu_page( $menus = array() ) {
		$menus[] = array(
			'name'            => __( 'stock-based-pricing-plugin', 'stock-based-pricing-plugin' ),
			'slug'            => 'stock_based_pricing_plugin_menu',
			'menu_link'       => 'stock_based_pricing_plugin_menu',
			'instance'        => $this,
			'function'        => 'sbpp_options_menu_html',
		);
		return $menus;
	}


	/**
	 * stock-based-pricing-plugin mwb_plugins_listing_page.
	 *
	 * @since 1.0.0
	 */
	public function mwb_plugins_listing_page() {
		$active_marketplaces = apply_filters( 'mwb_add_plugins_menus_array', array() );
		if ( is_array( $active_marketplaces ) && ! empty( $active_marketplaces ) ) {
			require STOCK_BASED_PRICING_PLUGIN_DIR_PATH . 'admin/partials/welcome.php';
		}
	}

	/**
	 * stock-based-pricing-plugin admin menu page.
	 *
	 * @since    1.0.0
	 */
	public function sbpp_options_menu_html() {

		include_once STOCK_BASED_PRICING_PLUGIN_DIR_PATH . 'admin/partials/stock-based-pricing-plugin-admin-dashboard.php';
	}


	/**
	 * stock-based-pricing-plugin admin menu page.
	 *
	 * @since    1.0.0
	 * @param array $sbpp_settings_general Settings fields.
	 */
	public function sbpp_admin_general_settings_page( $sbpp_settings_general ) {

		$sbpp_settings_general = array(
			array(
				'title' => __( 'Enable plugin', 'stock-based-pricing-plugin' ),
				'type'  => 'radio-switch',
				'description'  => __( 'Enable plugin to start the functionality.', 'stock-based-pricing-plugin' ),
				'id'    => 'sbpp_radio_switch_demo',
				'value' => get_option( 'sbpp_radio_switch_demo' ),
				'class' => 'sbpp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'stock-based-pricing-plugin' ),
					'no' => __( 'NO', 'stock-based-pricing-plugin' ),
				),
			),

			array(
				'type'  => 'button',
				'id'    => 'sbpp_button_demo',
				'button_text' => __( 'Button Demo', 'stock-based-pricing-plugin' ),
				'class' => 'sbpp-button-class',
			),
		);
		return $sbpp_settings_general;
	}

	/**
	 * stock-based-pricing-plugin admin menu page.
	 *
	 * @since    1.0.0
	 * @param array $sbpp_settings_template Settings fields.
	 */
	public function sbpp_admin_template_settings_page( $sbpp_settings_template ) {
		$sbpp_settings_template = array(
			array(
				'title' => __( 'Text Field Demo', 'stock-based-pricing-plugin' ),
				'type'  => 'text',
				'description'  => __( 'This is text field demo follow same structure for further use.', 'stock-based-pricing-plugin' ),
				'id'    => 'sbpp_text_demo',
				'value' => '',
				'class' => 'sbpp-text-class',
				'placeholder' => __( 'Text Demo', 'stock-based-pricing-plugin' ),
			),
			array(
				'title' => __( 'Number Field Demo', 'stock-based-pricing-plugin' ),
				'type'  => 'number',
				'description'  => __( 'This is number field demo follow same structure for further use.', 'stock-based-pricing-plugin' ),
				'id'    => 'sbpp_number_demo',
				'value' => '',
				'class' => 'sbpp-number-class',
				'placeholder' => '',
			),
			array(
				'title' => __( 'Password Field Demo', 'stock-based-pricing-plugin' ),
				'type'  => 'password',
				'description'  => __( 'This is password field demo follow same structure for further use.', 'stock-based-pricing-plugin' ),
				'id'    => 'sbpp_password_demo',
				'value' => '',
				'class' => 'sbpp-password-class',
				'placeholder' => '',
			),
			array(
				'title' => __( 'Textarea Field Demo', 'stock-based-pricing-plugin' ),
				'type'  => 'textarea',
				'description'  => __( 'This is textarea field demo follow same structure for further use.', 'stock-based-pricing-plugin' ),
				'id'    => 'sbpp_textarea_demo',
				'value' => '',
				'class' => 'sbpp-textarea-class',
				'rows' => '5',
				'cols' => '10',
				'placeholder' => __( 'Textarea Demo', 'stock-based-pricing-plugin' ),
			),
			array(
				'title' => __( 'Select Field Demo', 'stock-based-pricing-plugin' ),
				'type'  => 'select',
				'description'  => __( 'This is select field demo follow same structure for further use.', 'stock-based-pricing-plugin' ),
				'id'    => 'sbpp_select_demo',
				'value' => '',
				'class' => 'sbpp-select-class',
				'placeholder' => __( 'Select Demo', 'stock-based-pricing-plugin' ),
				'options' => array(
					'' => __( 'Select option', 'stock-based-pricing-plugin' ),
					'INR' => __( 'Rs.', 'stock-based-pricing-plugin' ),
					'USD' => __( '$', 'stock-based-pricing-plugin' ),
				),
			),
			array(
				'title' => __( 'Multiselect Field Demo', 'stock-based-pricing-plugin' ),
				'type'  => 'multiselect',
				'description'  => __( 'This is multiselect field demo follow same structure for further use.', 'stock-based-pricing-plugin' ),
				'id'    => 'sbpp_multiselect_demo',
				'value' => '',
				'class' => 'sbpp-multiselect-class mwb-defaut-multiselect',
				'placeholder' => '',
				'options' => array(
					'default' => __( 'Select currency code from options', 'stock-based-pricing-plugin' ),
					'INR' => __( 'Rs.', 'stock-based-pricing-plugin' ),
					'USD' => __( '$', 'stock-based-pricing-plugin' ),
				),
			),
			array(
				'title' => __( 'Checkbox Field Demo', 'stock-based-pricing-plugin' ),
				'type'  => 'checkbox',
				'description'  => __( 'This is checkbox field demo follow same structure for further use.', 'stock-based-pricing-plugin' ),
				'id'    => 'sbpp_checkbox_demo',
				'value' => '',
				'class' => 'sbpp-checkbox-class',
				'placeholder' => __( 'Checkbox Demo', 'stock-based-pricing-plugin' ),
			),

			array(
				'title' => __( 'Radio Field Demo', 'stock-based-pricing-plugin' ),
				'type'  => 'radio',
				'description'  => __( 'This is radio field demo follow same structure for further use.', 'stock-based-pricing-plugin' ),
				'id'    => 'sbpp_radio_demo',
				'value' => '',
				'class' => 'sbpp-radio-class',
				'placeholder' => __( 'Radio Demo', 'stock-based-pricing-plugin' ),
				'options' => array(
					'yes' => __( 'YES', 'stock-based-pricing-plugin' ),
					'no' => __( 'NO', 'stock-based-pricing-plugin' ),
				),
			),
			array(
				'title' => __( 'Enable', 'stock-based-pricing-plugin' ),
				'type'  => 'radio-switch',
				'description'  => __( 'This is switch field demo follow same structure for further use.', 'stock-based-pricing-plugin' ),
				'id'    => 'sbpp_radio_switch_demo',
				'value' => '',
				'class' => 'sbpp-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'stock-based-pricing-plugin' ),
					'no' => __( 'NO', 'stock-based-pricing-plugin' ),
				),
			),

			array(
				'type'  => 'button',
				'id'    => 'sbpp_button_demo',
				'button_text' => __( 'Button Demo', 'stock-based-pricing-plugin' ),
				'class' => 'sbpp-button-class',
			),
		);
		return $sbpp_settings_template;
	}

	/**
	* stock-based-pricing-plugin save tab settings.
	*
	* @since 1.0.0
	*/
	public function sbpp_admin_save_tab_settings() {
		global $sbpp_mwb_sbpp_obj;
		if ( isset( $_POST['sbpp_button_demo'] ) ) {
			$mwb_sbpp_gen_flag = false;
			$sbpp_genaral_settings = apply_filters( 'sbpp_general_settings_array', array() );
			$sbpp_button_index = array_search( 'submit', array_column( $sbpp_genaral_settings, 'type' ) );
			if ( isset( $sbpp_button_index ) && ( null == $sbpp_button_index || '' == $sbpp_button_index ) ) {
				$sbpp_button_index = array_search( 'button', array_column( $sbpp_genaral_settings, 'type' ) );
			}
			if ( isset( $sbpp_button_index ) && '' !== $sbpp_button_index ) {
				unset( $sbpp_genaral_settings[$sbpp_button_index] );
				if ( is_array( $sbpp_genaral_settings ) && ! empty( $sbpp_genaral_settings ) ) {
					foreach ( $sbpp_genaral_settings as $sbpp_genaral_setting ) {
						if ( isset( $sbpp_genaral_setting['id'] ) && '' !== $sbpp_genaral_setting['id'] ) {
							if ( isset( $_POST[$sbpp_genaral_setting['id']] ) ) {
								update_option( $sbpp_genaral_setting['id'], $_POST[$sbpp_genaral_setting['id']] );
							} else {
								update_option( $sbpp_genaral_setting['id'], '' );
							}
						}else{
							$mwb_sbpp_gen_flag = true;
						}
					}
				}
				if ( $mwb_sbpp_gen_flag ) {
					$mwb_sbpp_error_text = esc_html__( 'Id of some field is missing', 'stock-based-pricing-plugin' );
					$sbpp_mwb_sbpp_obj->mwb_sbpp_plug_admin_notice( $mwb_sbpp_error_text, 'error' );
				}else{
					$mwb_sbpp_error_text = esc_html__( 'Settings saved !', 'stock-based-pricing-plugin' );
					$sbpp_mwb_sbpp_obj->mwb_sbpp_plug_admin_notice( $mwb_sbpp_error_text, 'success' );
				}
			}
		}
	}
}
