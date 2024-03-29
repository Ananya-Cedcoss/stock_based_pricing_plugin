<?php
/**
 * The migration-specific functionality of the plugin.
 *
 * @link       https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-org-backend&utm_campaign=official
 * @since      3.2.0
 *
 * @package     woo_one_click_upsell_funnel
 * @subpackage woo_one_click_upsell_funnel/migration
 */

/**
 * The migration-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the migration-specific stylesheet and JavaScript.
 *
 * @package     woo_one_click_upsell_funnel
 * @subpackage  woo_one_click_upsell_funnel/migration
 * @author      wpswings <webmaster@wpswings.com>
 */
class WPS_OCU_Migration {

	/**
	 * Register the stylesheets for the migration area.
	 *
	 * @since    3.2.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in woocommerce_one_click_upsell_funnel_pro_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The woocommerce_one_click_upsell_funnel_pro_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();

		if ( isset( $screen->id ) ) {
			$pagescreen = $screen->id;

			if ( 'toplevel_page_wps-wocuf-pro-setting' === $pagescreen || '1-click-upsell_page_wps-wocuf-pro-setting-tracking' === $pagescreen ) {
				wp_register_style( 'wps_wocuf_pro_migrator_style', plugin_dir_url( __FILE__ ) . 'css/wps-ocu-migrator.css', array(), WPS_WOCUF_PRO_VERSION, 'all' );
				wp_enqueue_style( 'wps_wocuf_pro_fa_fa_style', plugin_dir_url( __FILE__ ) . 'css/fa-fa-lib.css', array(), WPS_WOCUF_PRO_VERSION, 'all' );
				wp_enqueue_style( 'wps_wocuf_pro_migrator_style' );
			}
		}
	}

	/**
	 * Register the JavaScript for the migration area.
	 *
	 * @since    3.2.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in woocommerce_one_click_upsell_funnel_pro_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The woocommerce_one_click_upsell_funnel_pro_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();

		if ( isset( $screen->id ) ) {
			$pagescreen = $screen->id;

			if ( 'toplevel_page_wps-wocuf-pro-setting' === $pagescreen || '1-click-upsell_page_wps-wocuf-pro-setting-tracking' === $pagescreen ) {

				wp_enqueue_script( 'wps_wocuf_pro_migrator_script', plugin_dir_url( __FILE__ ) . 'js/wps-ocu-migrator.js', array( 'jquery' ), WPS_WOCUF_PRO_VERSION, false );

				wp_localize_script(
					'wps_wocuf_pro_migrator_script',
					'wps_ocu_migrator_obj',
					array(
						'ajaxUrl' => admin_url( 'admin-ajax.php' ),
						'nonce'   => wp_create_nonce( 'ajax_nonce' ),
						'title'   => array(
							'settings' => esc_html__( 'Attention Required', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							'metas'    => esc_html__( 'Attention Required', 'one-click-upsell-funnel-for-woocommerce-pro' ),
						),
						'content' => array(
							'settings' => esc_html__( 'Attention Required', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							'metas'    => esc_html__( 'Attention Required', 'one-click-upsell-funnel-for-woocommerce-pro' ),
						),
						'data'    => apply_filters(
							'wps_mirgation_localised_data',
							array(
								'settings' => $this->get_options_keys(),
								'metas'    => $this->get_post_meta_keys(),
								'pages'    => $this->get_pages_ids_with_shortcodes(),
							)
						),
					)
				);
			}
		}
	}

	/**
	 * Check for all the options saved via current crm plugin.
	 *
	 * @return array.
	 */
	private function get_options_keys() {

		global $wpdb;
		$table_name = $wpdb->prefix . 'options';
		$sql        = "SELECT option_name FROM `$table_name` WHERE `option_name` LIKE '%mwb_wocuf%' OR `option_name` LIKE '%mwb_upsell%'";
		return $this->wps_wocuf_get_query_results( $sql, ARRAY_A );
	}

	/**
	 * Check for all the options saved via current crm plugin.
	 *
	 * @return array.
	 */
	private function get_post_meta_keys() {

		global $wpdb;
		$table_name = $wpdb->prefix . 'postmeta';
		$sql        = "SELECT DISTINCT `meta_key` FROM `$table_name` WHERE `meta_key` LIKE '%mwb_wocuf%' OR `meta_key` LIKE '%mwb_upsell%' OR `meta_key` LIKE '%mwb_ocuf%'";
		return $this->wps_wocuf_get_query_results( $sql, ARRAY_A );
	}

	/**
	 * Check for all the options saved via current crm plugin.
	 *
	 * @return array.
	 */
	private function get_pages_ids_with_shortcodes() {

		global $wpdb;
		$table_name    = $wpdb->prefix . 'posts';
		$sql           = "SELECT DISTINCT `ID` FROM `$table_name` WHERE ( `post_type` = 'revision' OR `post_type` = 'page' ) AND `post_content` LIKE '%[mwb_%'";
		$content_pages = $this->wps_wocuf_get_query_results( $sql, ARRAY_A );

		$table_name = $wpdb->prefix . 'postmeta';
		$sql        = "SELECT `post_id` FROM `$table_name` WHERE `meta_value` LIKE '%[mwb_%'";
		$meta_pages = $this->wps_wocuf_get_query_results( $sql, ARRAY_A );

		$ids = array_merge( $meta_pages, $content_pages );

		return $ids;
	}

	/**
	 * Get query results from database
	 *
	 * @param  string $query Query to be executed.
	 * @return array         Result data.
	 */
	private function wps_wocuf_get_query_results( $query ) {
		global $wpdb;
		$result = $wpdb->get_results( $query, ARRAY_A ); // @codingStandardsIgnoreLine.
		return $result;
	}


	/**
	 * Ajax Call back
	 */
	public function process_ajax_events() {
		check_ajax_referer( 'ajax_nonce', 'nonce' );
		$event = ! empty( $_POST['event'] ) ? sanitize_text_field( wp_unslash( $_POST['event'] ) ) : '';
		if ( method_exists( $this, $event ) ) {
			$data = $this->$event( $_POST );
		} else {
			$data = esc_html__( 'Method not found', 'one-click-upsell-funnel-for-woocommerce-pro' );
		}
		wp_send_json( $data );
	}

	/**
	 * Import Single option.
	 *
	 * @param array $posted_data the posted data.
	 * @since       3.2.0
	 */
	public function import_single_option( $posted_data = array() ) {

		$settings = ! empty( $posted_data['settings'] ) ? $posted_data['settings'] : array();

		if ( empty( $settings ) ) {
			return array();
		}

		foreach ( $settings as $key => $value ) {
			$old_key = ! empty( $value['option_name'] ) ? $value['option_name'] : '';
			unset( $settings[ $key ] );
			break;
		}

		$this->import_option( $old_key );
		return $settings;
	}

	/**
	 * Import Single meta in all posts at once.
	 *
	 * @param array $posted_data the posted data.
	 * @since       3.2.0
	 */
	public function import_single_meta( $posted_data = array() ) {

		$meta_keys = ! empty( $posted_data['metas'] ) ? $posted_data['metas'] : array();

		if ( empty( $meta_keys ) ) {
			return array();
		}

		foreach ( $meta_keys as $key => $value ) {
			$old_key = ! empty( $value['meta_key'] ) ? $value['meta_key'] : '';
			unset( $meta_keys[ $key ] );
			break;
		}

		$this->import_postmeta( $old_key );
		return $meta_keys;
	}

	/**
	 * Import Single option.
	 *
	 * @param array $posted_data the posted data.
	 * @since       3.2.0
	 */
	public function import_single_page( $posted_data = array() ) {

		$pages = ! empty( $posted_data['pages'] ) ? $posted_data['pages'] : array();

		if ( empty( $pages ) ) {
			update_option( 'wocuf_pro_migration_status', true );
			return array();
		}

		foreach ( $pages as $key => $value ) {
			$page_id = ! empty( $value['ID'] ) ? $value['ID'] : '';
			if ( empty( $page_id ) ) {
				$page_id = ! empty( $value['post_id'] ) ? $value['post_id'] : '';
			}
			unset( $pages[ $key ] );
			break;
		}

		$page_obj = get_post( $page_id );

		if ( ! empty( $page_obj ) ) {
			$content = $page_obj->post_content;
			$content = str_replace( 'mwb_', 'wps_', $content );
			$my_post = array(
				'ID'           => $page_id,
				'post_content' => $content,
			);
			wp_update_post( $my_post );

			$elementor_data = get_post_meta( $page_id, '_elementor_data', true );
			if ( ! empty( $elementor_data ) ) {
				$elementor_data = str_replace( 'mwb_', 'wps_', $elementor_data );
				update_post_meta( $page_id, '_elementor_data', $elementor_data );
			}
		}

		if ( empty( $pages ) ) {
			update_option( 'wocuf_pro_migration_status', true );
			return array();
		}

		return $pages;
	}

	/**
	 * Modify Single option.
	 *
	 * @param string|array $option_name The option key.
	 * @since       3.2.0
	 */
	public function import_option( $option_name = '' ) {

		if ( empty( $option_name ) ) {
			return;
		}

		$new_option_name = str_replace( 'mwb', 'wps', $option_name );
		$option_value    = get_option( $option_name );

		// Update the same value to wps key.
		if ( ! empty( $option_value ) ) {
			if ( is_array( $option_value ) ) {

				switch ( $option_name ) {
					case 'mwb_upsell_global_options':
					case 'mwb_upsell_lite_global_options':
						$option_value = $this->moderate_keys( $option_value );
						break;

					case 'mwb_wocuf_pro_funnels_list':
					case 'mwb_wocuf_funnels_list':
						foreach ( $option_value as $key => $value ) {
							$option_value[ $key ] = $this->moderate_keys( $value );
						}
						break;
				}
			}

			// Delete Old key and Add New key.
			update_option( $new_option_name, $option_value );
			delete_option( $option_name );
		}
	}

	/**
	 * Init Migration for postmeta.
	 *
	 * @param string $meta_key meta key to import.
	 * @since       3.2.0
	 */
	public function import_postmeta( $meta_key = false ) {

		if ( ! empty( $meta_key ) ) {

			$new_meta_key = str_replace( 'mwb', 'wps', $meta_key );
			global $wpdb;
			$table_name = $wpdb->prefix . 'postmeta';
			$sql        = "UPDATE `$table_name` SET
			`meta_key` = '$new_meta_key'
			WHERE `meta_key` = '$meta_key'";

			$this->wps_wocuf_get_query_results( $sql );
		}

	}

	/**
	 * Init Migration for postmeta.
	 *
	 * @since       3.2.0
	 */
	public function import_stripe_meta() {

		global $wpdb;
		$table_name = $wpdb->prefix . 'postmeta';
		$sql        = "UPDATE `$table_name` SET `meta_value` = 'wps-wocuf-pro-stripe-gateway' WHERE `meta_key` = '_payment_method' AND `meta_value` = 'mwb-wocuf-pro-stripe-gateway';";
		$this->wps_wocuf_get_query_results( $sql );
		return array();
	}

	/**
	 * Init Migration formatting.
	 *
	 * @param array $array Values.
	 * @since       3.2.0
	 */
	public function moderate_keys( $array = array() ) {

		if ( ! empty( $array ) && is_array( $array ) ) {
			foreach ( $array as $key => $value ) {
				$new_key           = str_replace( 'mwb', 'wps', $key );
				$array[ $new_key ] = $value;

				if ( $new_key !== $key ) {
					unset( $array[ $key ] );
				}
			}
			return $array;
		}

		return $array;
	}

	// End of class.
}

