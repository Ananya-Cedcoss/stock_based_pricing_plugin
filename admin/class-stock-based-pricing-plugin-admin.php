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
		if ( isset( $screen->id ) && 'product' == $screen->id  ) {

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
		if ( isset( $screen->id ) && 'makewebbetter_page_stock_based_pricing_plugin_menu' == $screen->id || 'product' == $screen->id ) {

			wp_enqueue_script( 'mwb-sbpp-select2', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/select-2/stock-based-pricing-plugin-select2.js', array( 'jquery' ), time(), false );

			wp_enqueue_script( 'mwb-sbpp-metarial-js', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/material-design/material-components-web.min.js', array(), time(), false );
			wp_enqueue_script( 'mwb-sbpp-metarial-js2', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/material-design/material-components-v5.0-web.min.js', array(), time(), false );
			wp_enqueue_script( 'mwb-sbpp-metarial-lite', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'package/lib/material-design/material-lite.min.js', array(), time(), false );


			wp_register_script( $this->plugin_name . 'admin-js', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'admin/src/js/stock-based-pricing-plugin-admin.js', array( 'jquery', 'mwb-sbpp-select2', 'mwb-sbpp-metarial-js', 'mwb-sbpp-metarial-js2', 'mwb-sbpp-metarial-lite', 'sbpp_my_custom_script' ), $this->version, false );


			wp_localize_script(
				$this->plugin_name . 'admin-js',
				'sbpp_admin_param',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'reloadurl' => admin_url( 'admin.php?page=stock_based_pricing_plugin_menu' ),
					'sbpp_gen_tab_enable' => get_option( 'sbpp_radio_switch_demo' ),
				)
			);
				// Adding mwb-admin.js js to the page.

			wp_register_script( 'sbpp_my_custom_script', STOCK_BASED_PRICING_PLUGIN_DIR_URL . 'admin/js/mwb-admin.js', array( 'jquery' ), $this->version, false );			
			 wp_localize_script(
				'sbpp_my_custom_script' ,
				'sbpp_productedit_param',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'fill_stock' => __('Fill Stock Quantity !!', 'stock-based-pricing-plugin' ) ,
					'min_quantity' => __('Enter the Min Quantity !!', 'stock-based-pricing-plugin') ,
					'max_quantity' => __('Enter the Max Quantity !!', 'stock-based-pricing-plugin') ,
					'amount' => __('Enter the Amount !!', 'stock-based-pricing-plugin') ,
					'greater_than_minimum' => __( 'Enter Value Greater Than Minimum Quantity', 'stock-based-pricing-plugin' ),
					'less_than_stock' => __( 'Enter Maximum Quantity less than Stock Value', 'stock-based-pricing-plugin' ),
				)
			);
			wp_enqueue_script( 'sbpp_my_custom_script' );			
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
						} else {
							$mwb_sbpp_gen_flag = true;
						}
					}
				}
				if ( $mwb_sbpp_gen_flag ) {
					$mwb_sbpp_error_text = esc_html__( 'Id of some field is missing', 'stock-based-pricing-plugin' );
					$sbpp_mwb_sbpp_obj->mwb_sbpp_plug_admin_notice( $mwb_sbpp_error_text, 'error' );
				} else {
					$mwb_sbpp_error_text = esc_html__( 'Settings saved !', 'stock-based-pricing-plugin' );
					$sbpp_mwb_sbpp_obj->mwb_sbpp_plug_admin_notice( $mwb_sbpp_error_text, 'success' );
				}
			}
		}
	}


	/**  The woocommerce_product_custom_fields frunction is used to create custom field */
	public function sbpp_product_custom_table_and_checkbox() {
		global $post; // is used to get post object for the current post.	
		wp_nonce_field(	basename(__FILE__), 'sbpp-checkbox-table-nonce' );
		$sbpp_data = get_post_meta( $post->ID, '_price_acc_to_stock' );// storing post meta of _price_acc_to_stock to sbpp_data variable.
		if ( ! empty( $sbpp_data ) ) {
			$sbpp_pricing    = json_decode( $sbpp_data[0], true );// it is used to encode it into array and store it to pricing.
			$sbpp_count_data = count( $sbpp_pricing );// it is used to get number of data in array.
		} else {
			$sbpp_count_data = 0;
		}	
		echo '<div class=" product_custom_field show_if_simple "> '; // it is used to display the main div.
		echo  "<div id='my_stock_div' >  <table id='Stock_table' ><tr> <th>" . esc_html__('Min Quanity', 'stock-based-pricing-plugin') ." </th>  <th>" . esc_html__('Max Quanity', 'stock-based-pricing-plugin') ." </th>  <th> " . esc_html__('Amount', 'stock-based-pricing-plugin') ."</th>   <th> " . esc_html__('Action', 'stock-based-pricing-plugin') ."</th> </tr>"; // it is used to display the table header.
		$sbpp_index = 1;
		if ( $sbpp_count_data > 0 ) {			
			foreach ( $sbpp_pricing as $key => $value ) {
				$sbpp_minimum_val = $value['Min'];
				$sbpp_max_value   = $value['Max'];
				$sbpp_amount      = $value['Amount'];
				echo  "<tr ><td> <input type='text' value='" . esc_attr( $sbpp_minimum_val ) . "' onkeypress='return AllowOnlyNumbers(event);'  name='Min[]'  id='Min_Quantity_" . esc_attr( $sbpp_index ) . "'/>  </td> ";// it displays the first td of table when data already exists.
				echo  " <td> <input type='text' value='" . esc_attr( $sbpp_max_value ) . "' onkeypress='return AllowOnlyNumbers(event);'  name='Max[]' onblur='validateMaxamount(this," . esc_attr( $sbpp_index ) . ",0)' id='Max_Quantity_" . esc_attr( $sbpp_index ) . "' />  </td>"; // it displays the second td of table when data already exists.
				echo  " <td> <input type='text' value='" . esc_attr( $sbpp_amount ) . "' onkeypress='return AllowOnlyNumbers(event);'  name='Amount[]' id='Amount_" . esc_attr( $sbpp_index ) . "' />  </td>"; // it displays the third td of table when data already exists.
				if ( $sbpp_index == 1 ) {
					echo  " <td> <span  onclick='GenerateNewRow()'><u> " . esc_html__('Add Row', 'stock-based-pricing-plugin') ." </u></span> </td> ";
				} else {
					echo  "<td> <span class='delete_row' onclick='DeleteExistingRow(this)'><u> " . esc_html__('Delete Row', 'stock-based-pricing-plugin') ."  </u></span> </td> ";
				}
				echo  '</tr>';
				$sbpp_index = ++$sbpp_index; // it used to increase the index value by 1;.
			}
		} else {
			echo  "<tr><td> <input type='text' onkeypress='return AllowOnlyNumbers(event);'  name='Min[]'  id='Min_Quantity_1'/>  </td>  "; // it is used to display first td when there is no existing data.
			echo  " <td> <input type='text' onkeypress='return AllowOnlyNumbers(event);'  name='Max[]' onblur='validateMaxamount(this,1,0)' id='Max_Quantity_1' />  </td>"; // it is used to display second td when there is no existing data.
			echo  "<td> <input type='text' onkeypress='return AllowOnlyNumbers(event);'  name='Amount[]' id='Amount_1' />  </td>"; // it is used to display third td when there is no existing data.			
			if ( $sbpp_index == 1 ) {
				echo  " <td> <span  onclick='GenerateNewRow()'><u> " . esc_html__(' Add Row', 'stock-based-pricing-plugin') ." </u></span> </td> ";
			} else {
				echo  " <td> <sapn class='delete_row' onclick='DeleteExistingRow(this)'><u> " . esc_html__('Delete Row', 'stock-based-pricing-plugin') ." </u></span> </td>  ";
			}
			echo ' </tr> ';
		}
		echo  ' </table></div></div> '; // it display the button to gererate new row.
	}

	/** This Function is used to save all dynamic Pricing in Simple Type Product
	 *
	 * @param int $post_id is used to get the post id of current post.
	 */
	public function saving_dynamic_pricing( $post_id ) {
		
		if ( ! isset($_POST['sbpp-checkbox-table-nonce']) || ! wp_verify_nonce( $_POST['sbpp-checkbox-table-nonce'], basename(__FILE__) ) ) {
			return $post_id;	
		}
		$sbpp_min            = isset( $_POST['Min'] ) ? map_deep( $_POST['Min'], 'sanitize_text_field' ) : ''; // it is used to assign all Min value from the TextBox of Minimum Quantity.
		$sbpp_max            = isset( $_POST['Max'] ) ? map_deep( $_POST['Max'], 'sanitize_text_field' )  : ''; // it is used to assign all Max value from the TextBox of Maximum Quantity.
		$sbpp_amount         = isset( $_POST['Amount'] ) ? map_deep( $_POST['Amount'], 'sanitize_text_field' )  : '' ; // it is used to assign all Amount value from the TextBox of Amount.
		$sbpp_main_arry      = array(); // It is used to store sub array of Min,Max and Amount .
		$sbpp_data_sub_array = array(); // it is used to store Min, Max, Amount value.
		foreach ( $sbpp_min as $key => $value ) {
			$sbpp_data_sub_array['Min']    = $value; // Assigning the Min value.
			$sbpp_data_sub_array['Max']    = $sbpp_max[ $key ]; // Assigning the Max value.
			$sbpp_data_sub_array['Amount'] = $sbpp_amount[ $key ]; // Assigning the Amount value.
			if ( ! empty($sbpp_data_sub_array['Min']) || ! empty( $sbpp_data_sub_array['Max'] ) || ! empty( $sbpp_data_sub_array['Amount'] ) ){
				array_push( $sbpp_main_arry, $sbpp_data_sub_array ); // Push the sbpp_data_sub_array array to main array.
			}
		}	
		if ( count( $sbpp_main_arry ) > 0 ) {
		$product = wc_get_product( $post_id ); // It is used to assign the data of product.
		$product->update_meta_data( '_price_acc_to_stock', ( wp_json_encode( $sbpp_main_arry ) ) ); // Updating the post meta data .
		$product->save(); // Saving the Product data.
		}
	}

	/** The sbp_add_custom_field_to_variations function is used to add custom fields to Variable poducts.
	 *
	 * @param    int     $loop Position in the loop.
	 * @param    array   $variation_data Variation data.
	 * @param WP_Post $variation Post data.
	 */
	public function sbp_add_custom_field_to_variations( $loop, $variation_data, $variation ) {
		wp_nonce_field(	basename( __FILE__ ), 'sbpp-checkbox-table-variation-nonce' );	
		$sbpp_index_loop = $loop + 1; // assigning index according to loop variable.
		$sbpp_data       = get_post_meta( $variation->ID, '_price_acc_to_stock_var' ); // Assigning the post meta data to the variable.
		$sbpp_pricing    = json_decode( $sbpp_data[0], true ); // decoding the data and converting it to array.
		$sbpp_count_data = count( $sbpp_pricing ); // assigning the length of the array.
			echo '<div class=" product_custom_field show_if_variation_manage_stock"> '; // Displays the main div.
			echo "<div class='my_stock_div_forVariation' id='my_stock_div_forVariation_" . esc_attr( $sbpp_index_loop ) . "' >  " . esc_html__('Give Price Acc To Stock', 'stock-based-pricing-plugin') ." <br> <table id='Stock_table_variation_" . esc_attr( $sbpp_index_loop ) . "'  ><tr> <th>" . esc_html__( 'Min Quanity', 'stock-based-pricing-plugin' ) ." </th>  <th>" . esc_html__('Max Quanity', 'stock-based-pricing-plugin') ." </th>  <th> " . esc_html__( 'Amount', 'stock-based-pricing-plugin') ."</th> <th> " . esc_html__( 'Action', 'stock-based-pricing-plugin' ) ." </th> </tr>";// Display the Table Header.
			$sbpp_index = 1;
		if ( $sbpp_count_data > 0 ) {		
			foreach ( $sbpp_pricing as $key => $value ) {
				$sbpp_minimum_val = $value['Min']; // Assigning the Min value.
				$sbpp_max_value   = $value['Max']; // Assigning the Max value.
				$sbpp_amount      = $value['Amount']; // Assigning the Amount value.			
				echo "<tr><td> <input  type='text' value='" . esc_attr( $sbpp_minimum_val ) . "'  name='Min_Var_" . esc_attr( $variation->ID ) . "[]'  id='Min_Quantity_Var_" . $sbpp_index_loop . $sbpp_index . "'   onkeypress='return AllowOnlyNumbers(event);'/>  </td>  "; // it displays the First td of table when data already exists.
				echo " <td> <input type='text' value='" . esc_attr( $sbpp_max_value ) . "'  name='Max_Var_" . esc_attr( $variation->ID ) . "[]'   onkeypress='return AllowOnlyNumbers(event);' onblur='validateMaxamount(this," . $sbpp_index_loop . $sbpp_index . ", -1, ".$loop.")' id='Max_Quantity_Var_" . $sbpp_index_loop . $sbpp_index . "'/>  </td>"; // it displays the Second td of table when data already exists.
				echo "<td> <input type='text' value='" . esc_attr( $sbpp_amount ) . "'  onkeypress='return AllowOnlyNumbers(event);'  name='Amount_Var_" . esc_attr( $variation->ID ) . "[]'  id='Amount_Var_" . $sbpp_index_loop . $sbpp_index . "' />  </td>"; // it displays the Third td of table when data already exists.
				if ( $sbpp_index == 1 ) {				
					echo  " <td> <span  onclick='GenerateNewRow_Variation(" . esc_attr( $loop ) . ", " . esc_attr( $variation->ID ) . ")' ><u> " . esc_html__('Add Row', 'stock-based-pricing-plugin') ." </u> </span> </td> ";
				} else {				
					echo  " <td> <sapn class='delete_row' onclick='DeleteExistingRow(this)'><u>" . esc_html__('Delete Row', 'stock-based-pricing-plugin') ." </u></span> </td>  ";
				}
				echo '</tr> ';
				$sbpp_index = ++$sbpp_index; // it is used to increase the index value by 1.
			}
		} else {
			echo "<tr><td> <input type='text' name='Min_Var_" . esc_attr( $variation->ID ) . "[]'  onkeypress='return AllowOnlyNumbers(event);' id='Min_Quantity_Var_" . esc_attr( $sbpp_index_loop ) . "1'/>  </td> ";// it is used to display first td when there is no existing data.
			echo " <td> <input type='text' onkeypress='return AllowOnlyNumbers(event);' name='Max_Var_" . esc_attr( $variation->ID ) . "[]' onblur='validateMaxamount(this," . $sbpp_index_loop . "1,-1,".$loop.")' onkeypress='return AllowOnlyNumbers(event);' id='Max_Quantity_Var_" . esc_attr( $sbpp_index_loop ) . "1' />  </td>";// it is used to display second td when there is no existing data.
			echo " <td> <input type='text'   name='Amount_Var_" . esc_attr( $variation->ID ) . "[]'     id='Amount_Var_" . esc_attr( $sbpp_index_loop ) . "1' />  </td>";
			if ( $sbpp_index == 1 ) {
				echo  " <td> <span  onclick='GenerateNewRow_Variation(" . esc_attr( $loop ) . ", " . esc_attr( $variation->ID ) . ")' ><u> " . esc_html__('Add Row', 'stock-based-pricing-plugin') ." </u></span> </td> ";
			} else {
				echo  " <td> <sapn class='delete_row' onclick='DeleteExistingRow(this)'><u> " . esc_html__('Delete Row', 'stock-based-pricing-plugin') ."</u></span> </td>  ";
			}
			echo ' </tr> ';
		}
		echo '</table></div></div>';// it is used to display third td when there is no existing data.		
	}

	/** Function is used to save post meta data of variation of product df.
	 *
	 * @param                                    int $variation_id is the Id of current variation.
	 * @param                                    int $i is the index of the current variation.
	 */
	public function sbp_save_custom_field_variations( $variation_id, $i ) {	
		if ( ! isset($_POST['sbpp-checkbox-table-variation-nonce']) || !wp_verify_nonce( $_POST['sbpp-checkbox-table-variation-nonce'], basename(__FILE__) ) ) {
			return ;	
		}	
		$sbpp_min_alldatavariation    = isset( $_POST['Min_Var_' . $variation_id] ) ? map_deep( $_POST['Min_Var_' . $variation_id], 'sanitize_text_field' ) : ''; // assign all minimum value to the min variable.
		$sbpp_max_alldatavariation    = isset( $_POST['Max_Var_' . $variation_id] ) ? map_deep( $_POST['Max_Var_' . $variation_id], 'sanitize_text_field' ) : ''; // assign all maximum value to the max variable.
		$sbpp_amount_alldatavariation = isset( $_POST['Amount_Var_' . $variation_id] ) ? map_deep( $_POST['Amount_Var_' . $variation_id], 'sanitize_text_field' ) : ''; // assign all amount value to the amount variable.
		$sbpp_main_array_variation    = array(); // It is used to store sub array of Min,Max and Amount .
		$sbpp_data_subarray_variation = array(); // it is used to store Min, Max, Amount value.
		foreach ( $sbpp_min_alldatavariation as $key => $value ) {
			$sbpp_data_subarray_variation['Min']    = $value; // store single min value.
			$sbpp_data_subarray_variation['Max']    = $sbpp_max_alldatavariation[ $key ]; // store single max value.
			$sbpp_data_subarray_variation['Amount'] = $sbpp_amount_alldatavariation[ $key ]; // store single amount value.
			array_push( $sbpp_main_array_variation, $sbpp_data_subarray_variation ); // push the data array to main array.
		}	
		update_post_meta( $variation_id, '_price_acc_to_stock_var', ( wp_json_encode( $sbpp_main_array_variation ) ) ); // update post meta to save the values.
		die(); // this is required to terminate immediately and return a proper response.
	}
}
