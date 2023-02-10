<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/admin
 */

use Elementor\Core\Admin\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/admin
 * @author     wpswings <webmaster@wpswings.com>
 */
class Woocommerce_One_Click_Upsell_Funnel_Pro_Admin {

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
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
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

		$screen        = get_current_screen();
		$valid_screens = array(
			'toplevel_page_wps-wocuf-pro-setting',
			'1-click-upsell_page_wps-wocuf-setting-tracking',
		);

		if ( isset( $screen->id ) ) {
			$pagescreen = $screen->id;

			if ( in_array( $pagescreen, $valid_screens, true ) ) {

				wp_register_style( 'wps_wocuf_pro_admin_style', plugin_dir_url( __FILE__ ) . 'css/woocommerce-one-click-upsell-funnel-pro-admin.css', array(), $this->version, 'all' );

				wp_enqueue_style( 'wps_wocuf_pro_admin_style' );

				wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );

				wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );

				wp_enqueue_style( 'woocommerce_admin_menu_styles' );

				wp_enqueue_style( 'woocommerce_admin_styles' );
			}
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
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

		$screen        = get_current_screen();
		$valid_screens = array(
			'toplevel_page_wps-wocuf-pro-setting',
			'1-click-upsell_page_wps-wocuf-setting-tracking',
		);

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		if ( isset( $screen->id ) ) {
			$pagescreen = $screen->id;

			if ( in_array( $pagescreen, $valid_screens, true ) ) {

				wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, false );

				wp_enqueue_script( $this->plugin_name . '_swals', plugin_dir_url( __FILE__ ) . 'js/swal.js', array( 'jquery' ), $this->version, false );

				wp_enqueue_media();

				wp_enqueue_script( 'wps_wocuf_pro_admin_script', plugin_dir_url( __FILE__ ) . 'js/woocommerce-one-click-upsell-funnel-pro-admin.js', array( 'jquery' ), $this->version, false );

				wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip', 'wc-enhanced-select' ), WC_VERSION, false );

				wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.js', array( 'jquery' ), WC_VERSION, true );
					$locale  = localeconv();
					$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';
					$params  = array(
						/* translators: %s: decimal */
						'i18n_decimal_error'               => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'one-click-upsell-funnel-for-woocommerce-pro' ), $decimal ),
						/* translators: %s: price decimal separator */
						'i18n_mon_decimal_error'           => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'one-click-upsell-funnel-for-woocommerce-pro' ), wc_get_price_decimal_separator() ),
						'i18n_country_iso_error'           => __( 'Please enter in country code with two capital letters.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
						'i18_sale_less_than_regular_error' => __( 'Please enter in a value less than the regular price.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
						'decimal_point'                    => $decimal,
						'mon_decimal_point'                => wc_get_price_decimal_separator(),
						'strings'                          => array(
							'import_products' => __( 'Import', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							'export_products' => __( 'Export', 'one-click-upsell-funnel-for-woocommerce-pro' ),
						),
						'urls'                             => array(
							'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
							'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
						),
					);

					wp_localize_script(
						'wps_wocuf_pro_admin_script',
						'wps_wocuf_pro_obj',
						array(
							'ajaxUrl'               => admin_url( 'admin-ajax.php' ),
							'alert_preview_title'   => esc_html__( 'Attention Required', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							'alert_preview_content' => esc_html__( 'We are preparing your migration to WP Swings. Please do not leave the page until prompted. This may take a while according to your previous upsell orders count.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
						)
					);

					wp_localize_script(
						'wps_wocuf_pro_admin_script',
						'wps_wocuf_pro_obj_form',
						array(
							'ajaxurl'             => admin_url( 'admin-ajax.php' ),
							'mobile_view'         => wp_is_mobile(),
							'nonce'               => wp_create_nonce( 'wps_wocuf_nonce' ),
							'org_activated'       => is_plugin_active( 'woo-one-click-upsell-funnel/woocommerce-one-click-upsell-funnel.php' ) ? 'true' : 'false',
							'org_activation_text' => array(
								'title' => esc_html__( 'Attention Required', 'one-click-upsell-funnel-for-woocommerce-pro' ),
								'text'  => esc_html__( 'The plugin requires free plugin to be activated. Please install and activate the free plugin.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
								'url'   => admin_url( 'plugin-install.php?s=upsell%20funnel&tab=search&type=term' ),
							),
						)
					);

					wp_localize_script(
						'wps_wocuf_pro_admin_script',
						'wps_wocuf_pro_location',
						array(
							'location' => admin_url( 'admin.php' ) . '?page=wps-wocuf-pro-setting&tab=settings',
						)
					);

					wp_enqueue_script( 'wps_wocuf_pro_admin_script' );

					wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );

					wp_enqueue_script( 'woocommerce_admin' );

					wp_enqueue_script( 'wps-wocuf-pro-add_new-offer-script', plugin_dir_url( __FILE__ ) . 'js/wps_wocuf_pro_add_new_offer_script.js', array( 'woocommerce_admin', 'wc-enhanced-select' ), $this->version, false );

					wp_localize_script(
						'wps-wocuf-pro-add_new-offer-script',
						'ajax_url',
						array(
							'ajaxUrl' => admin_url( 'admin-ajax.php' ),
						)
					);

				if ( ! empty( $_GET['wps-upsell-offer-section'] ) ) {

					$upsell_offer_section['value'] = sanitize_text_field( wp_unslash( $_GET['wps-upsell-offer-section'] ) );

					wp_localize_script( 'wps-wocuf-pro-add_new-offer-script', 'offer_section_obj', $upsell_offer_section );
				}

					wp_enqueue_style( 'wp-color-picker' );

					wp_enqueue_script( 'wps-wocuf-pro-color-picker-handle', plugin_dir_url( __FILE__ ) . 'js/wps_wocuf_pro_color_picker_handle.js', array( 'jquery', 'wp-color-picker' ), $this->version, true );
			}

			wp_enqueue_script( 'wps_wocuf_pro_admin_refund_script', plugin_dir_url( __FILE__ ) . 'js/wps_wocuf_pro_refund.js', array( 'jquery' ), $this->version, false );

			if ( isset( $_GET['section'] ) && 'wps-wocuf-pro-stripe-gateway' === $_GET['section'] ) {
				wp_enqueue_script( 'wps-wocuf-pro-stripe-script', plugin_dir_url( __FILE__ ) . 'js/woocommerce-one-click-upsell-funnel-pro-stripe.js', array( 'jquery' ), $this->version, false );
			}
		}
	}

	/**
	 * Include Upsell screen for Onboarding pop-up.
	 *
	 * @param mixed $valid_screens valid screens.
	 * @since    3.5.0
	 */
	public function add_wps_frontend_screens( $valid_screens = array() ) {

		if ( is_array( $valid_screens ) ) {

			// Push your screen here.
			array_push( $valid_screens, 'toplevel_page_wps-wocuf-pro-setting' );
		}

		return $valid_screens;
	}

	/**
	 * Include Upsell plugin for Deactivation pop-up.
	 *
	 * @param mixed $valid_screens valid screens.
	 * @since    3.5.0
	 */
	public function add_wps_deactivation_screens( $valid_screens = array() ) {

		if ( is_array( $valid_screens ) ) {

			// Push your screen here.
			array_push( $valid_screens, 'one-click-upsell-funnel-for-woocommerce-pro' );
		}

		return $valid_screens;
	}

	/**
	 * Adding upsell menu page.
	 *
	 * @since    1.0.0
	 */
	public function wps_wocuf_pro_admin_menu() {
		add_menu_page(
			__( '1 Click Upsell', 'one-click-upsell-funnel-for-woocommerce-pro' ),
			__( '1 Click Upsell', 'one-click-upsell-funnel-for-woocommerce-pro' ),
			'manage_woocommerce',
			'wps-wocuf-pro-setting',
			array( $this, 'upsell_menu_html' ),
			'dashicons-chart-area',
			57
		);

		/**
		 * Add sub-menu for funnel settings.
		 */
		add_submenu_page( 'wps-wocuf-pro-setting', esc_html__( 'Funnels & Settings', 'one-click-upsell-funnel-for-woocommerce-pro' ), esc_html__( 'Funnels & Settings', 'one-click-upsell-funnel-for-woocommerce-pro' ), 'manage_options', 'wps-wocuf-pro-setting' );

		/**
		 * Add sub-menu for reportings settings.
		 */
		add_submenu_page( 'wps-wocuf-pro-setting', esc_html__( 'Reports, Analytics & Tracking', 'one-click-upsell-funnel-for-woocommerce-pro' ), esc_html__( 'Reports, Analytics & Tracking', 'one-click-upsell-funnel-for-woocommerce-pro' ), 'manage_options', 'wps-wocuf-setting-tracking', array( $this, 'add_submenu_page_reporting_callback' ) );
	}

	/**
	 * Callable function for upsell menu page.
	 *
	 * @since    1.0.0
	 */
	public function upsell_menu_html() {

		if ( ! empty( $_GET['reset_migration'] ) && true == $_GET['reset_migration'] ) {  //phpcs:ignore
			$nonce = ! empty( $_GET['wocuf_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['wocuf_nonce'] ) ) : '';
			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wocuf_migration' ) ) {
				die( 'Nonce not verified' );
			}
			delete_option( 'wocuf_pro_migration_status' );
			wp_safe_redirect( admin_url() . '?page=wps-wocuf-pro-setting&tab=funnels-list' );
		}

		$callname_lic         = Woocommerce_One_Click_Upsell_Funnel_pro::$lic_callback_function;
		$callname_lic_initial = Woocommerce_One_Click_Upsell_Funnel_pro::$lic_ini_callback_function;
		$day_count            = Woocommerce_One_Click_Upsell_Funnel_pro::$callname_lic_initial();

		if ( Woocommerce_One_Click_Upsell_Funnel_pro::$callname_lic() || 0 <= $day_count ) {

			if ( ! Woocommerce_One_Click_Upsell_Funnel_pro::$callname_lic() && 0 <= $day_count ) :

				$day_count_warning = floor( $day_count );
				/* translators: %s: decimal */
				$day_string = sprintf( _n( '%s day', '%s days', $day_count_warning, 'one-click-upsell-funnel-for-woocommerce-pro' ), number_format_i18n( $day_count_warning ) );
				?>

				<div id="wps-wocuf-thirty-days-notify" class="notice notice-warning">
					<p>
						<strong>
							<a href="?page=wps-wocuf-pro-setting&tab=license">
								<?php esc_html_e( 'Activate', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							</a>
							<?php esc_html_e( ' the license key before ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							<span id="wps-wocuf-day-count">
								<?php echo esc_html( $day_string ); ?>
							</span>
							<?php esc_html_e( ' or you may risk losing data and the plugin will also become dysfunctional.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
						</strong>
					</p>
				</div>
				<?php
			endif;
			if ( empty( get_option( 'wocuf_pro_migration_status', false ) ) ) {
				?>
				<div id="wps-wocuf-thirty-days-notify" class="notice notice-error">
					<p>
						<strong>
							<?php esc_html_e( 'We have done a major changes in plugin! Please ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							<a href="?page=wps-wocuf-pro-setting&tab=funnels-list#wps_wocuf_pro_migration_button">
								<?php esc_html_e( 'Migrate', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							</a>
							<?php esc_html_e( ' or you may risk losing data and the plugin will also become dysfunctional.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
						</strong>
					</p>
				</div>
				<?php
			} else {
				?>
				<div id="wps-wocuf-thirty-days-notify" class="notice notice-success">
					<p>
						<strong>
							<?php esc_html_e( 'Migration was successful! If you want to reset the migration, please click here. ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							<a href="?page=wps-wocuf-pro-setting&tab=funnels-list&reset_migration=1&wocuf_nonce=<?php echo esc_attr( wp_create_nonce( 'wocuf_migration' ) ); ?>">
								<?php esc_html_e( 'Reset Migration', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							</a>
						</strong>
					</p>
				</div>
				<?php
			}

			require_once plugin_dir_path( __FILE__ ) . '/partials/woocommerce-one-click-upsell-funnel-pro-admin-display.php';
		} else {

			?>

			<div class="wrap woocommerce" id="wps_wocuf_pro_setting_wrapper">

				<h1 class="wps_wocuf_pro_setting_title"><?php esc_html_e( 'WooCommerce One Click Upsell Funnel Pro', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>

					<span class="wps_wocuf_pro_setting_title_version">
					<?php
					esc_html_e( 'v', 'one-click-upsell-funnel-for-woocommerce-pro' );
					echo esc_html( WPS_WOCUF_PRO_VERSION );
					?>
					</span>
				</h1>

				<?php

				require_once plugin_dir_path( __FILE__ ) . '/partials/templates/woocommerce-one-click-upsell-funnel-pro-license.php';

				?>

			</div>

				<?php
		}
	}

	/**
	 * Reporting and Funnel Stats Sub menu callback.
	 *
	 * @since       3.5.0
	 */
	public function add_submenu_page_reporting_callback() {

		$callname_lic         = Woocommerce_One_Click_Upsell_Funnel_pro::$lic_callback_function;
		$callname_lic_initial = Woocommerce_One_Click_Upsell_Funnel_pro::$lic_ini_callback_function;
		$day_count            = Woocommerce_One_Click_Upsell_Funnel_pro::$callname_lic_initial();

		if ( Woocommerce_One_Click_Upsell_Funnel_pro::$callname_lic() || 0 <= $day_count ) {
			if ( ! Woocommerce_One_Click_Upsell_Funnel_pro::$callname_lic() && 0 <= $day_count ) :

				$day_count_warning = floor( $day_count );

				/* translators: %s: decimal */
				$day_string = sprintf( _n( '%s day', '%s days', $day_count_warning, 'one-click-upsell-funnel-for-woocommerce-pro' ), number_format_i18n( $day_count_warning ) );
				?>
				<div id="wps-wocuf-thirty-days-notify" class="notice notice-warning">
					<p>
						<strong>
							<a href="?page=wps-wocuf-pro-setting&tab=license">
								<?php esc_html_e( 'Activate', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							</a>
							<?php esc_html_e( ' the license key before ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							<span id="wps-wocuf-day-count">
								<?php echo esc_html( $day_string ); ?>
							</span>
							<?php esc_html_e( ' or you may risk losing data and the plugin will also become dysfunctional.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
						</strong>
					</p>
				</div>

				<?php

			endif;

			if ( empty( get_option( 'wocuf_pro_migration_status', false ) ) ) {
				?>

				<div id="wps-wocuf-thirty-days-notify" class="notice notice-warning">
					<p>
						<strong>
							<?php esc_html_e( 'We have done a major changes in plugin please ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							<a href="?page=wps-wocuf-pro-setting&tab=funnels-list#wps_wocuf_pro_migration_button">
								<?php esc_html_e( 'Migrate', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							</a>
							<?php esc_html_e( ' or you may risk losing data and the plugin will also become dysfunctional.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
						</strong>
					</p>
				</div>
`
				<?php
			} else {
				?>
				<div id="wps-wocuf-thirty-days-notify" class="notice notice-success">
					<p>
						<strong>
							<?php esc_html_e( 'Migration was successful! If you want to reset the migration, please click here. ', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							<a href="?page=wps-wocuf-pro-setting&tab=funnels-list&reset_migration=1&wocuf_nonce=<?php echo esc_attr( wp_create_nonce( 'wocuf_migration' ) ); ?>">
								<?php esc_html_e( 'Reset Migration', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
							</a>
						</strong>
					</p>
				</div>
				<?php
			}

			require_once WPS_WOCUF_PRO_DIRPATH . 'admin/reporting-and-tracking/upsell-reporting-and-tracking-config-panel.php';

		} else {

			?>

			<div class="wrap woocommerce" id="wps_wocuf_pro_setting_wrapper">

				<h1 class="wps_wocuf_pro_setting_title"><?php esc_html_e( 'WooCommerce One Click Upsell Funnel Pro', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
					<span class="wps_wocuf_pro_setting_title_version">
					<?php
					esc_html_e( 'v', 'one-click-upsell-funnel-for-woocommerce-pro' );
					echo esc_html( WPS_WOCUF_PRO_VERSION );
					?>
					</span>
				</h1>

				<?php

				require_once plugin_dir_path( __FILE__ ) . '/partials/templates/woocommerce-one-click-upsell-funnel-pro-license.php';

				?>

			</div>

				<?php
		}
	}

	/**
	 * Offer Html for appending in funnel when add new offer is clicked - ajax handle function.
	 * Also Dynamic page post is created while adding new offer.
	 *
	 * @since    1.0.0
	 */
	public function return_funnel_offer_section_content() {

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		if ( isset( $_POST['wps_wocuf_pro_flag'] ) && isset( $_POST['wps_wocuf_pro_funnel'] ) ) {

			// New Offer id.
			$offer_index = sanitize_text_field( wp_unslash( $_POST['wps_wocuf_pro_flag'] ) );
			// Funnel id.
			$funnel_id = sanitize_text_field( wp_unslash( $_POST['wps_wocuf_pro_funnel'] ) );

			unset( $_POST['wps_wocuf_pro_flag'] );
			unset( $_POST['wps_wocuf_pro_funnel'] );

			$funnel_offer_post_html = '<input type="hidden" name="wps_upsell_post_id_assigned[' . $offer_index . ']" value="">';

			$funnel_offer_template_section_html = '';
			$funnel_offer_post_id               = '';

			if ( wps_upsell_elementor_plugin_active() ) {

				// Create post for corresponding funnel and offer id.
				$funnel_offer_post_id = wp_insert_post(
					array(
						'comment_status' => 'closed',
						'ping_status'    => 'closed',
						'post_content'   => '',
						'post_name'      => uniqid( 'special-offer-' ), // post slug.
						'post_title'     => 'Special Offer',
						'post_status'    => 'publish',
						'post_type'      => 'page',
						'page_template'  => 'elementor_canvas',
					)
				);

				if ( $funnel_offer_post_id ) {

					$elementor_data = wps_upsell_elementor_offer_template_1();
					update_post_meta( $funnel_offer_post_id, '_elementor_data', $elementor_data );
					update_post_meta( $funnel_offer_post_id, '_elementor_edit_mode', 'builder' );

					$wps_upsell_funnel_data = array(
						'funnel_id' => $funnel_id,
						'offer_id'  => $offer_index,
					);

					update_post_meta( $funnel_offer_post_id, 'wps_upsell_funnel_data', $wps_upsell_funnel_data );

					$funnel_offer_post_html = '<input type="hidden" name="wps_upsell_post_id_assigned[' . $offer_index . ']" value="' . $funnel_offer_post_id . '">';

					$funnel_offer_template_section_html = $this->get_funnel_offer_template_section_html( $funnel_offer_post_id, $offer_index, $funnel_id );

					// Save an array of all created upsell offer-page post ids.
					$upsell_offer_post_ids = get_option( 'wps_upsell_offer_post_ids', array() );

					$upsell_offer_post_ids[] = $funnel_offer_post_id;

					update_option( 'wps_upsell_offer_post_ids', $upsell_offer_post_ids );

				}
			} else {    // When Elementor is not active.

				// Will return 'Feature not supported' part as $funnel_offer_post_id is empty.
				$funnel_offer_template_section_html = $this->get_funnel_offer_template_section_html( $funnel_offer_post_id, $offer_index, $funnel_id );
			}

			// Get all funnels.
			$wps_wocuf_pro_funnel = get_option( 'wps_wocuf_pro_funnels_list' );

			// Funnel offers array.
			$wps_wocuf_pro_offers_to_add = isset( $wps_wocuf_pro_funnel[ $funnel_id ]['wps_wocuf_pro_applied_offer_number'] ) ? $wps_wocuf_pro_funnel[ $funnel_id ]['wps_wocuf_pro_applied_offer_number'] : array();

			// Buy now action select html.
			$buy_now_action_select_html = '<select name="wps_wocuf_pro_attached_offers_on_buy[' . $offer_index . ']"><option value="thanks">' . __( 'Order ThankYou Page', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</option>';

			// No thanks action select html.
			$no_thanks_action_select_html = '<select name="wps_wocuf_pro_attached_offers_on_no[' . $offer_index . ']"><option value="thanks">' . __( 'Order ThankYou Page', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</option>';

			// If there are other offers then add them to select html.
			if ( ! empty( $wps_wocuf_pro_offers_to_add ) ) {

				foreach ( $wps_wocuf_pro_offers_to_add as $offer_id ) {

					$buy_now_action_select_html .= '<option value=' . $offer_id . '>' . __( 'Offer #', 'one-click-upsell-funnel-for-woocommerce-pro' ) . $offer_id . '</option>';

					$no_thanks_action_select_html .= '<option value=' . $offer_id . '>' . __( 'Offer #', 'one-click-upsell-funnel-for-woocommerce-pro' ) . $offer_id . '</option>';
				}
			}

			$buy_now_action_select_html   .= '</select>';
			$no_thanks_action_select_html .= '</select>';

			$offer_scroll_id_val = "#offer-section-$offer_index";

			$data = '<div style="display:none;" data-id="' . $offer_index . '" data-scroll-id="' . $offer_scroll_id_val . '" class="new_created_offers wps_upsell_single_offer">
			<h2 class="wps_upsell_offer_title">' . __( 'Offer #', 'one-click-upsell-funnel-for-woocommerce-pro' ) . $offer_index . '</h2>
			<table>
			<tr>
			<th><label><h4>' . __( 'Offer Product', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</h4></label></th>
			<td><select class="wc-offer-product-search wps_upsell_offer_product" name="wps_wocuf_pro_products_in_offer[' . $offer_index . ']" data-placeholder="' . esc_html__( 'Search for a product&hellip;', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '"></select></td>
			</tr>
			<tr>
			<th><label><h4>' . __( 'Offer Price / Discount', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</h4></label></th>
			<td>
			<input type="text" class="wps_upsell_offer_price" name="wps_wocuf_pro_offer_discount_price[' . $offer_index . ']" value="50%" >
			<span class="wps_upsell_offer_description" >' . esc_html__( 'Specify new offer price or discount %', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</span>
			</td>
			</tr>
			<tr>
			<th><label><h4>' . esc_html__( 'Offer Image', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</h4></label>
			</th>
			<td>' . $this->wps_wocuf_pro_image_uploader_field( $offer_index ) . '<span class="wps_upsell_offer_description">' . esc_html__( 'This will not work for Variable Offer product.', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</span></td>
			</tr>
		    <tr>
		    <th><label><h4>' . esc_html__( 'After \'Buy Now\' go to', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</h4></label></th>
		    <td>' . $buy_now_action_select_html . '<span class="wps_upsell_offer_description">' . esc_html__( 'Select where the customer will be redirected after accepting this offer', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</span></td>
		    </tr>
		    <tr>
		    <th><label><h4>' . esc_html__( 'After \'No thanks\' go to', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</h4></label></th>
		    <td>' . $no_thanks_action_select_html . '<span class="wps_upsell_offer_description">' . esc_html__( 'Select where the customer will be redirected after rejecting this offer', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</td>
		    </tr>' . $funnel_offer_template_section_html . '
		    <tr>
		    <th><label><h4>' . __( 'Offer Custom Page Link', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</h4></label></th>
		    <td>
		    <input type="text" class="wps_upsell_custom_offer_page_url" name="wps_wocuf_pro_offer_custom_page_url[' . $offer_index . ']" >
		    </td>
		    </tr>
		    <tr>
		    <td colspan="2">
		    <button class="button wps_wocuf_pro_delete_new_created_offers" data-id="' . $offer_index . '">' . esc_html__( 'Remove', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</button>
		    </td>
		    </tr>
		    </table>
		    <input type="hidden" name="wps_wocuf_pro_applied_offer_number[' . $offer_index . ']" value="' . $offer_index . '">
		    ' . $funnel_offer_post_html . '

		    </div>';

			$new_data = apply_filters( 'wps_wocuf_pro_add_more_to_offers', $data );

			echo $new_data; // phpcs:ignore
		}

		wp_die();
	}

	/**
	 * Returns Funnel Offer Template section html.
	 *
	 * @param mixed $funnel_offer_post_id funnel_offer_post_id.
	 * @param mixed $offer_index offer_index.
	 * @param mixed $funnel_id funnel_id.
	 * @since    3.5.0
	 */
	public function get_funnel_offer_template_section_html( $funnel_offer_post_id, $offer_index, $funnel_id ) {

		ob_start();

		?>

		<!-- Section : Offer template start -->
		<tr>
			<th><label><h4><?php esc_html_e( 'Offer Template', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h4></label>
			</th>

			<?php

			$assigned_post_id        = ! empty( $funnel_offer_post_id ) ? $funnel_offer_post_id : '';
			$current_offer_id        = $offer_index;
			$wps_wocuf_pro_funnel_id = $funnel_id;

			?>

			<td>

				<?php if ( ! empty( $assigned_post_id ) ) : ?>

					<?php

					// As default is "one".
					$offer_template_active = 'one';

					$offer_templates_array = array(
						'one'   => esc_html__( 'STANDARD TEMPLATE', 'one-click-upsell-funnel-for-woocommerce-pro' ),
						'two'   => esc_html__( 'CREATIVE TEMPLATE', 'one-click-upsell-funnel-for-woocommerce-pro' ),
						'three' => esc_html__( 'VIDEO TEMPLATE', 'one-click-upsell-funnel-for-woocommerce-pro' ),
					);

					?>

					<!-- Offer templates parent div start -->
					<div class="wps_upsell_offer_templates_parent">

						<input class="wps_wocuf_pro_offer_template_input" type="hidden" name="wps_wocuf_pro_offer_template[<?php echo esc_html( $current_offer_id ); ?>]" value="<?php echo esc_html( $offer_template_active ); ?>">

						<?php foreach ( $offer_templates_array as $template_key => $template_name ) : ?>
							<!-- Offer templates foreach start-->
							<div class="wps_upsell_offer_template <?php echo (string) $template_key === (string) $offer_template_active ? 'active' : ''; ?>">

								<div class="wps_upsell_offer_template_sub_div"> 

									<h5><?php echo esc_html( $template_name ); ?></h5>

									<div class="wps_upsell_offer_preview">

										<a href="javascript:void(0)" class="wps_upsell_view_offer_template" data-template-id="<?php echo esc_html( $template_key ); ?>" ><img src="<?php echo esc_url( WPS_WOCUF_PRO_URL . "admin/resources/offer-thumbnails/offer-template-$template_key.jpg" ); ?>"></a>
									</div>

									<div class="wps_upsell_offer_action">

										<?php if ( (string) $template_key !== (string) $offer_template_active ) : ?>

										<button class="button-primary wps_upsell_activate_offer_template" data-template-id="<?php echo esc_html( $template_key ); ?>" data-offer-id="<?php echo esc_html( $current_offer_id ); ?>" data-funnel-id="<?php echo esc_html( $wps_wocuf_pro_funnel_id ); ?>" data-offer-post-id="<?php echo esc_html( $assigned_post_id ); ?>" ><?php esc_html_e( 'Insert and Activate', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></button>

										<?php else : ?>

											<a class="button" href="<?php echo esc_url( get_permalink( $assigned_post_id ) ); ?>" target="_blank"><?php esc_html_e( 'View &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>

											<a class="button" href="<?php echo esc_html( admin_url( "post.php?post=$assigned_post_id&action=elementor" ) ); ?>" target="_blank"><?php esc_html_e( 'Customize &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>

										<?php endif; ?>
									</div>
								</div>

							</div>
							<!-- Offer templates foreach end-->
						<?php endforeach; ?>

						<!-- Offer link to custom page start-->
						<div class="wps_upsell_offer_template wps_upsell_custom_page_link_div <?php echo esc_html( 'custom' === $offer_template_active ? 'active' : '' ); ?>">

							<h5><?php esc_html_e( 'LINK TO CUSTOM PAGE', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h5>

							<?php if ( 'custom' !== $offer_template_active ) : ?>

								<button class="button-primary wps_upsell_activate_offer_template" data-template-id="custom" data-offer-id="<?php echo esc_html( $current_offer_id ); ?>" data-funnel-id="<?php echo esc_html( $wps_wocuf_pro_funnel_id ); ?>" data-offer-post-id="<?php echo esc_html( $assigned_post_id ); ?>" ><?php esc_html_e( 'Activate', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></button>

							<?php else : ?>	

								<h5><?php esc_html_e( 'Activated', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h5>
								<p><?php esc_html_e( 'Please enter and save your custom page link below.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>


							<?php endif; ?>

						</div>
						<!-- Offer link to custom page end-->

					</div>
					<!-- Offer templates parent div end -->

				<?php else : ?>

					<div class="wps_upsell_offer_template_unsupported">
						<h4><?php esc_html_e( 'Feature not supported for this Offer, please add a new Offer with Elementor active.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h4>
					</div>

				<?php endif; ?>
			</td>
		</tr>
		<!-- Section : Offer template end -->

		<?php

		return ob_get_clean();
	}

	/**
	 * Insert and Activate respective template ajax handle function.
	 *
	 * @since    3.5.0
	 */
	public function activate_respective_offer_template() {

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$funnel_id     = isset( $_POST['funnel_id'] ) ? sanitize_text_field( wp_unslash( $_POST['funnel_id'] ) ) : '';
		$offer_id      = isset( $_POST['offer_id'] ) ? sanitize_text_field( wp_unslash( $_POST['offer_id'] ) ) : '';
		$template_id   = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';
		$offer_post_id = isset( $_POST['offer_post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['offer_post_id'] ) ) : '';

		// IF custom then don't update and just return.
		if ( 'custom' === $template_id ) {

			echo wp_json_encode( array( 'status' => true ) );
			wp_die();
		}

		$offer_templates_array = array(
			'one'   => 'wps_upsell_elementor_offer_template_1',
			'two'   => 'wps_upsell_elementor_offer_template_2',
			'three' => 'wps_upsell_elementor_offer_template_3',
		);

		foreach ( $offer_templates_array as $template_key => $callback_function ) {

			if ( (string) $template_id === (string) $template_key ) {

				// Delete previous elementor css.
				delete_post_meta( $offer_post_id, '_elementor_css' );

				$elementor_data = $callback_function();
				update_post_meta( $offer_post_id, '_elementor_data', $elementor_data );

				break;
			}
		}

		echo wp_json_encode( array( 'status' => true ) );
		wp_die();
	}

	/**
	 * Select2 search for adding funnel target products.
	 *
	 * @since    1.0.0
	 */
	public function seach_products_for_funnel() {
		$return = array();

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$search_results = new WP_Query(
			array(
				's'                   => ! empty( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '',
				'post_type'           => array( 'product', 'product_variation' ),
				'post_status'         => array( 'publish' ),
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => -1,
			)
		);

		if ( $search_results->have_posts() ) :

			while ( $search_results->have_posts() ) :

				$search_results->the_post();

				$title = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;

				/**
				 * Check for post type as query sometimes returns posts even after mentioning post_type.
				 * As some plugins alter query which causes issues.
				 */
				$post_type = get_post_type( $search_results->post->ID );

				if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {

					continue;
				}

				$product      = wc_get_product( $search_results->post->ID );
				$downloadable = $product->is_downloadable();
				$stock        = $product->get_stock_status();
				$product_type = $product->get_type();

				$unsupported_product_types = array(
					'grouped',
					'external',
				);

				if ( in_array( $product_type, $unsupported_product_types, true ) || 'outofstock' === $stock ) {

					continue;
				}

				$return[] = array( $search_results->post->ID, $title );

			endwhile;

		endif;

		echo wp_json_encode( $return );

		wp_die();
	}

	/**
	 * Select2 search for adding funnel target product categories
	 *
	 * @since    3.0.1
	 */
	public function search_product_categories_for_funnel() {
		$return = array();

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$args = array(
			'search'   => ! empty( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '',
			'taxonomy' => 'product_cat',
			'orderby'  => 'name',
		);

		$product_categories = get_terms( $args );

		if ( ! empty( $product_categories ) && is_array( $product_categories ) && count( $product_categories ) ) {

			foreach ( $product_categories as $single_product_category ) {

				$cat_name = ( mb_strlen( $single_product_category->name ) > 50 ) ? mb_substr( $single_product_category->name, 0, 49 ) . '...' : $single_product_category->name;

				$return[] = array( $single_product_category->term_id, $single_product_category->name );
			}
		}

		echo wp_json_encode( $return );

		wp_die();
	}


	/**
	 * Select2 search for adding offer products
	 *
	 * @since    1.0.0
	 */
	public function seach_products_for_offers() {
		$return = array();

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$search_results = new WP_Query(
			array(
				's'                   => ! empty( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '',
				'post_type'           => array( 'product', 'product_variation' ),
				'post_status'         => array( 'publish' ),
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => -1,
			)
		);

		if ( $search_results->have_posts() ) :

			while ( $search_results->have_posts() ) :

				$search_results->the_post();

				$title = ( mb_strlen( $search_results->post->post_title ) > 50 ) ? mb_substr( $search_results->post->post_title, 0, 49 ) . '...' : $search_results->post->post_title;

				/**
				 * Check for post type as query sometimes returns posts even after mentioning post_type.
				 * As some plugins alter query which causes issues.
				 */
				$post_type = get_post_type( $search_results->post->ID );

				if ( 'product' !== $post_type && 'product_variation' !== $post_type ) {

					continue;
				}

				$product      = wc_get_product( $search_results->post->ID );
				$downloadable = $product->is_downloadable();
				$stock        = $product->get_stock_status();
				$product_type = $product->get_type();

				$unsupported_product_types = array(
					'grouped',
					'external',
				);

				if ( in_array( $product_type, $unsupported_product_types, true ) || 'outofstock' === $stock ) {

					continue;
				}

				$return[] = array( $search_results->post->ID, $title );

			endwhile;

		endif;

		echo wp_json_encode( $return );

		wp_die();
	}

	/**
	 * Adding custom column in orders table at backend
	 *
	 * @since    1.0.0
	 * @param  array $columns    array of columns on orders table.
	 * @return   array    $columns    array of columns on orders table alongwith upsell column
	 */
	public function wps_wocuf_pro_add_columns_to_admin_orders( $columns ) {

		$columns['wps-upsell-orders'] = __( 'Upsell Orders', 'one-click-upsell-funnel-for-woocommerce-pro' );

		return $columns;
	}


	/**
	 * Populating Upsell Orders column with Single Order or Upsell order.
	 *
	 * @since    1.0.0
	 * @param    array $column    Array of available columns.
	 * @param    int   $post_id   Current Order post id.
	 */
	public function wps_wocuf_pro_populate_upsell_order_column( $column, $post_id ) {

		$upsell_order = get_post_meta( $post_id, 'wps_wocuf_upsell_order', true );

		switch ( $column ) {

			case 'wps-upsell-orders':
				if ( 'true' === $upsell_order ) :
					?>
					<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>" ><?php esc_html_e( 'Upsell Order', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
				<?php else : ?>
					<?php esc_html_e( 'Single Order', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
					<?php
				endif;
				break;
		}
	}


	/**
	 * Add Upsell Filtering dropdown for All Orders, No Upsell Orders, Only Upsell Orders.
	 *
	 * @since    1.0.0
	 */
	public function wps_wocuf_pro_restrict_manage_posts() {

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		if ( isset( $_GET['post_type'] ) && 'shop_order' === $_GET['post_type'] ) {
			if ( isset( $_GET['wps_wocuf_pro_upsell_filter'] ) ) :
				?>
				<select name="wps_wocuf_pro_upsell_filter">
					<option value="all" <?php echo 'all' === $_GET['wps_wocuf_pro_upsell_filter'] ? 'selected=selected' : ''; ?>><?php esc_html_e( 'All Orders', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>
					<option value="no_upsells" <?php echo 'no_upsells' === $_GET['wps_wocuf_pro_upsell_filter'] ? 'selected=selected' : ''; ?>><?php esc_html_e( 'No Upsell Orders', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>
					<option value="all_upsells" <?php echo 'all_upsells' === $_GET['wps_wocuf_pro_upsell_filter'] ? 'selected=selected' : ''; ?>><?php esc_html_e( 'Only Upsell Orders', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>
				</select>
				<?php
			endif;

			if ( ! isset( $_GET['wps_wocuf_pro_upsell_filter'] ) ) :
				?>
				<select name="wps_wocuf_pro_upsell_filter">
					<option value="all"><?php esc_html_e( 'All Orders', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>
					<option value="no_upsells"><?php esc_html_e( 'No Upsell Orders', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>
					<option value="all_upsells"><?php esc_html_e( 'Only Upsell Orders', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></option>
				</select>
				<?php
			endif;
		}
	}

	/**
	 * Modifying query vars for filtering Upsell Orders.
	 *
	 * @since    1.0.0
	 * @param    array $vars    array of queries.
	 * @return   array    $vars    array of queries alongwith select dropdown query for upsell
	 */
	public function wps_wocuf_pro_request_query( $vars ) {

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		if ( isset( $_GET['wps_wocuf_pro_upsell_filter'] ) && 'all_upsells' === $_GET['wps_wocuf_pro_upsell_filter'] ) {
			$vars = array_merge( $vars, array( 'meta_key' => 'wps_wocuf_upsell_order' ) ); //phpcs:ignore
		} elseif ( isset( $_GET['wps_wocuf_pro_upsell_filter'] ) && 'no_upsells' === $_GET['wps_wocuf_pro_upsell_filter'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key'     => 'wps_wocuf_upsell_order', //phpcs:ignore
					'meta_compare' => 'NOT EXISTS',
				)
			);
		}

		return $vars;
	}


	/**
	 * Add Upsell supported payment gateways to Woocommerce.
	 *
	 * @since    1.0.0
	 * @param    array $load_gateways   Array of Woocommerce gateways classes.
	 * @return   array   $load_gateways   Array of Woocommerce gateways classes along with Upsell gateways classes.
	 */
	public function wps_wocuf_pro_add_payment_gateways( $load_gateways ) {

		if ( class_exists( 'WPS_Wocuf_Pro_Stripe_Gateway_Admin' ) ) {

			$load_gateways[] = 'WPS_Wocuf_Pro_Stripe_Gateway_Admin';
		}

		return $load_gateways;
	}

	/**
	 * Validating makewebbetter license
	 *
	 * @since    1.0.0
	 */
	public function wps_wocuf_pro_validate_license_key() {

		$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
		$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

		if ( ! $id_nonce_verified ) {
			wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
		}

		$wps_wocuf_pro_purchase_code = ! empty( $_POST['purchase_code'] ) ? sanitize_text_field( wp_unslash( $_POST['purchase_code'] ) ) : '';

		$api_params = array(
			'slm_action'         => 'slm_activate',
			'secret_key'         => WPS_WOCUF_PRO_SPECIAL_SECRET_KEY,
			'license_key'        => $wps_wocuf_pro_purchase_code,
			'_registered_domain' => ! empty( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '',
			'item_reference'     => rawurlencode( WPS_WOCUF_PRO_ITEM_REFERENCE ),
			'product_reference'  => 'WPSPK-2965',

		);

		$query = esc_url_raw( add_query_arg( $api_params, WPS_WOCUF_PRO_LICENSE_SERVER_URL ) );

		$wps_wocuf_pro_response = wp_remote_get(
			$query,
			array(
				'timeout'   => 20,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $wps_wocuf_pro_response ) ) {

			echo wp_json_encode(
				array(
					'status' => false,
					'msg'    => __(
						'An unexpected error occurred. Please try again.',
						'one-click-upsell-funnel-for-woocommerce-pro'
					),
				)
			);
		} else {

			$wps_wocuf_pro_license_data = json_decode( wp_remote_retrieve_body( $wps_wocuf_pro_response ) );

			if ( isset( $wps_wocuf_pro_license_data->result ) && 'success' === $wps_wocuf_pro_license_data->result ) {
				update_option( 'wps_wocuf_pro_license_key', $wps_wocuf_pro_purchase_code );
				update_option( 'wps_wocuf_pro_license_check', true );

				echo wp_json_encode(
					array(
						'status' => true,
						'msg'    => esc_html__(
							'Successfully Verified. Please Wait.',
							'one-click-upsell-funnel-for-woocommerce-pro'
						),
					)
				);
			} elseif ( isset( $wps_wocuf_pro_license_data->result ) && 'error' === $wps_wocuf_pro_license_data->result ) {
				echo wp_json_encode(
					array(
						'status' => false,
						'msg'    => $wps_wocuf_pro_license_data->message,
					)
				);
			} else {

				// Try Fallback method with $query.
				$ch = curl_init(); //phpcs:ignore
				curl_setopt( $ch, CURLOPT_URL, $query ); //phpcs:ignore
				curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true ); //phpcs:ignore
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); //phpcs:ignore 
				$wps_wocuf_pro_response = curl_exec( $ch ); //phpcs:ignore
				curl_close( $ch ); //phpcs:ignore

				$wps_wocuf_pro_license_data = json_decode( $wps_wocuf_pro_response );
				if ( isset( $wps_wocuf_pro_license_data->result ) && 'success' === $wps_wocuf_pro_license_data->result ) {

					update_option( 'wps_wocuf_pro_license_key', $wps_wocuf_pro_purchase_code );
					update_option( 'wps_wocuf_pro_license_check', true );

					echo wp_json_encode(
						array(
							'status' => true,
							'msg'    => esc_html__(
								'Successfully Verified. Please Wait.',
								'one-click-upsell-funnel-for-woocommerce-pro'
							),
						)
					);

				} elseif ( isset( $wps_wocuf_pro_license_data->result ) && 'error' === $wps_wocuf_pro_license_data->result ) {
					echo wp_json_encode(
						array(
							'status' => false,
							'msg'    => $wps_wocuf_pro_license_data->message,
						)
					);
				} else {
					echo wp_json_encode(
						array(
							'status' => false,
							'msg'    => esc_html__(
								'Something Went Wrong. Please contact with support forum.',
								'one-click-upsell-funnel-for-woocommerce-pro'
							),
						)
					);
				}
			}
		}

		wp_die();
	}

	/**
	 * Checking makewebbetter license on daily basis.
	 *
	 * @since    1.0.0
	 */
	public function wps_wocuf_pro_check_license() {

		$user_license_key = get_option( 'wps_wocuf_pro_license_key', '' );
		$api_params       = array(
			'slm_action'         => 'slm_check',
			'secret_key'         => WPS_WOCUF_PRO_SPECIAL_SECRET_KEY,
			'license_key'        => $user_license_key,
			'_registered_domain' => ! empty( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '',
			'item_reference'     => rawurlencode( WPS_WOCUF_PRO_ITEM_REFERENCE ),
			'product_reference'  => 'WPSPK-2965',
		);

		$query = esc_url_raw( add_query_arg( $api_params, WPS_WOCUF_PRO_LICENSE_SERVER_URL ) );

		$wps_response = wp_remote_get(
			$query,
			array(
				'timeout'   => 20,
				'sslverify' => false,
			)
		);

		$license_data = json_decode( wp_remote_retrieve_body( $wps_response ) );

		if ( isset( $license_data->result ) && 'success' === $license_data->result && isset( $license_data->status ) && 'active' === $license_data->status ) {

			update_option( 'wps_wocuf_pro_license_check', true );

		} else {

			// Try Fallback method with $query.
			$ch = curl_init(); //phpcs:ignore
			curl_setopt( $ch, CURLOPT_URL, $query ); //phpcs:ignore
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true ); //phpcs:ignore
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); //phpcs:ignore
			$wps_response = curl_exec( $ch ); //phpcs:ignore
			curl_close( $ch ); //phpcs:ignore

			$wps_wocuf_pro_license_data = json_decode( $wps_response );
			if ( isset( $wps_wocuf_pro_license_data->result ) && 'success' === $wps_wocuf_pro_license_data->result ) {
				update_option( 'wps_wocuf_pro_license_check', true );
			} else {
				delete_option( 'wps_wocuf_pro_license_check' );
			}
		}
	}

	/**
	 * Adding distraction free mode to the offers page.
	 *
	 * @since       1.0.0
	 * @param mixed $page_template default template for the page.
	 */
	public function wps_wocuf_pro_page_template( $page_template ) {
		$pages_available = get_posts(
			array(
				'posts_per_page' => -1,
				'post_type'      => 'any',
				'post_status'    => 'publish',
				's'              => '[wps_wocuf_pro_funnel_default_offer_page]',
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);

		$pages_available = array_merge(
			get_posts(
				array(
					'posts_per_page' => -1,
					'post_type'      => 'any',
					'post_status'    => 'publish',
					's'              => '[wps_upsell_default_offer_identification]',
					'orderby'        => 'ID',
					'order'          => 'ASC',
				)
			),
			$pages_available
		);

		foreach ( $pages_available as $single_page ) {

			if ( is_page( $single_page->ID ) ) {

				$page_template = dirname( __FILE__ ) . '/partials/templates/wps-wocuf-pro-template.php';
			}
		}

		return $page_template;
	}

	/**
	 * Add Upsell Reporting in Woo Admin reports.
	 *
	 * @param mixed $reports reports.
	 * @since       3.5.0
	 */
	public function add_upsell_reporting( $reports ) {

		$reports['upsell'] = array(

			'title'   => esc_html__( '1 Click Upsell', 'one-click-upsell-funnel-for-woocommerce-pro' ),
			'reports' => array(

				'sales_by_date'     => array(
					'title'       => esc_html__( 'Upsell Sales by date', 'one-click-upsell-funnel-for-woocommerce-pro' ),
					'description' => '',
					'hide_title'  => 1,
					'callback'    => array( 'Woocommerce_One_Click_Upsell_Funnel_Pro_Admin', 'upsell_reporting_callback' ),
				),

				'sales_by_product'  => array(
					'title'       => esc_html__( 'Upsell Sales by product', 'one-click-upsell-funnel-for-woocommerce-pro' ),
					'description' => '',
					'hide_title'  => 1,
					'callback'    => array( 'Woocommerce_One_Click_Upsell_Funnel_Pro_Admin', 'upsell_reporting_callback' ),
				),

				'sales_by_category' => array(
					'title'       => esc_html__( 'Upsell Sales by category', 'one-click-upsell-funnel-for-woocommerce-pro' ),
					'description' => '',
					'hide_title'  => 1,
					'callback'    => array( 'Woocommerce_One_Click_Upsell_Funnel_Pro_Admin', 'upsell_reporting_callback' ),
				),
			),
		);

		return $reports;
	}

	/**
	 * Add custom report. callback.
	 *
	 * @param mixed $report_type report_type.
	 * @since       3.5.0
	 */
	public static function upsell_reporting_callback( $report_type ) {

		$report_file      = ! empty( $report_type ) ? str_replace( '_', '-', $report_type ) : '';
		$preformat_string = ! empty( $report_type ) ? ucwords( str_replace( '_', ' ', $report_type ) ) : '';
		$class_name       = ! empty( $preformat_string ) ? 'WPS_Upsell_Report_' . str_replace( ' ', '_', $preformat_string ) : '';

		/**
		 * The file responsible for defining reporting.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'reporting/class-wps-upsell-report-' . $report_file . '.php';

		if ( class_exists( $class_name ) ) {

			$report = new $class_name();
			$report->output_report();

		} else {

			?>
			<div class="wps_wocuf_report_error_wrap" style="text-align: center;">
				<h2 class="wps_wocuf_report_error_text">
					<?php esc_html_e( 'Some Error Occured while creating report.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
				</h2>
			</div>
			<?php
		}
	}

	/**
	 * Include Upsell supported payment gateway classes after plugins are loaded.
	 *
	 * @since       1.0.0
	 */
	public function wps_wocuf_pro_plugins_loaded() {

		/**
		 * The class responsible for defining all methods of Stripe payment gateway.
		 */
		require_once WPS_WOCUF_PRO_DIRPATH . 'gateways/stripe/class-wps-wocuf-pro-stripe-gateway-admin.php';

		// Stripe library with composer.
		require_once WPS_WOCUF_PRO_DIRPATH . 'gateways/stripe/vendor/autoload.php';

	}

	/**
	 * Hide Upsell offer pages in admin panel 'Pages'.
	 *
	 * @param mixed $query query.
	 * @since       3.5.0
	 */
	public function hide_upsell_offer_pages_in_admin( $query ) {

		// Make sure we're in the admin and it's the main query.
		if ( ! is_admin() && ! $query->is_main_query() ) {

			return;
		}

		global $typenow;

		// Only do this for pages.
		if ( ! empty( $typenow ) && 'page' === $typenow ) {

			$saved_offer_post_ids = get_option( 'wps_upsell_offer_post_ids', array() );

			if ( ! empty( $saved_offer_post_ids ) && is_array( $saved_offer_post_ids ) && count( $saved_offer_post_ids ) ) {

				// Don't show the special pages.
				$query->set( 'post__not_in', $saved_offer_post_ids );

				return;
			}
		}

	}

	/**
	 * Add 'Upsell Support' column on payment gateways page.
	 *
	 * @param mixed $default_columns default_columns.
	 * @since       3.5.0
	 */
	public function upsell_support_in_payment_gateway( $default_columns ) {

		$new_column['wps_upsell'] = esc_html__( 'Upsell Supported', 'one-click-upsell-funnel-for-woocommerce-pro' );

		// Place at second last position.
		$position = count( $default_columns ) - 1;

		$default_columns = array_slice( $default_columns, 0, $position, true ) + $new_column + array_slice( $default_columns, $position, count( $default_columns ) - $position, true );

		return $default_columns;
	}

	/**
	 * 'Upsell Support' content on payment gateways page.
	 *
	 * @param mixed $gateway gateway.
	 * @since       3.5.0
	 */
	public function upsell_support_content_in_payment_gateway( $gateway ) {

		$supported_gateways = wps_upsell_supported_gateways();

		echo '<td class="wps_upsell_supported">';

		if ( in_array( $gateway->id, $supported_gateways, true ) ) {

			echo '<span class="status-enabled">' . esc_html__( 'Yes', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</span>';
		} else {

			echo '<span class="status-disabled">' . esc_html__( 'No', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</span>';
		}

		echo '</td>';
	}

	/**
	 * Dismiss Elementor inactive notice.
	 *
	 * @since       3.5.0
	 */
	public function dismiss_elementor_inactive_notice() {

		set_transient( 'wps_upsell_elementor_inactive_notice', 'notice_dismissed' );

		wp_die();
	}

	/**
	 * Add our custom Order status.
	 *
	 * @since       3.2.0
	 */
	public function wps_wocuf_pro_register_order_status() {

		register_post_status(
			'wc-upsell-parent',
			array(
				'label'                     => _x( 'Parent Order Completed', 'Order status', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,

				/* translators: %s: decimal */
				'label_count'               => _n_noop( 'Parent Order Completed <span class="count">(%s)</span>', 'Parent Upsell Order<span class="count">(%s)</span>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
			)
		);

		register_post_status(
			'wc-upsell-failed',
			array(
				'label'                     => _x( 'Upsell Order Failed', 'Order status', 'one-click-upsell-funnel-for-woocommerce-pro' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,

				/* translators: %s: decimal */
				'label_count'               => _n_noop( 'Upsell Order Failed <span class="count">(%s)</span>', 'Upsell Order Failed<span class="count">(%s)</span>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
			)
		);
	}

	/**
	 * Register in wc_order_statuses.
	 *
	 * @param mixed $order_statuses order_statuses.
	 * @since       3.2.0
	 */
	public function wps_wocuf_pro_order_statuses( $order_statuses ) {

		$order_statuses['wc-upsell-parent'] = _x( 'Parent Order Completed', 'Order status', 'one-click-upsell-funnel-for-woocommerce-pro' );

		$order_statuses['wc-upsell-failed'] = _x( 'Upsell Order Failed', 'Order status', 'one-click-upsell-funnel-for-woocommerce-pro' );

		return $order_statuses;
	}

	/**
	 * Apply CSS in custom wc_order_statuses.
	 *
	 * @since       3.2.0
	 */
	public function wps_wocuf_pro_order_styling() {
		global $pagenow, $post;

		if ( 'edit.php' !== $pagenow ) {
			return; // Exit.
		}
		if ( ! empty( $post ) && 'shop_order' !== get_post_type( $post->ID ) ) {
			return; // Exit.
		}

		?>
		<style>
			mark.order-status.status-upsell-failed.tips  {
				background: #eba3a3;
				color: #88251d;
			}

				mark.order-status.status-upsell-parent.tips  {
				background: #c6e1c6;
				color: #618620;
			}
		</style>
		<?php
	}

	/**
	 * Add custom image upload.
	 *
	 * @since       3.5.0
	 * @param      string $hidden_field_index       Offer index in funnel array.
	 * @param      string $image_post_id            Image post Id.
	 */
	public function wps_wocuf_pro_image_uploader_field( $hidden_field_index, $image_post_id = '' ) {

		$image   = ' button">' . esc_html__( 'Upload image', 'one-click-upsell-funnel-for-woocommerce-pro' );
		$display = 'none'; // Display state ot the "Remove image" button.

		if ( ! empty( $image_post_id ) ) {

			// $image_attributes[0] - Image URL.
			// $image_attributes[1] - Image width.
			// $image_attributes[2] - Image height.
			$image_attributes = wp_get_attachment_image_src( $image_post_id, 'thumbnail' );

			$image   = '"><img src="' . $image_attributes[0] . '" style="max-width:150px;display:block;" />';
			$display = 'inline-block';
		}

		return '<div class="wps_wocuf_saved_custom_image">
		<a href="#" class="wps_wocuf_pro_upload_image_button' . $image . '</a>
		<input type="hidden" name="wps_upsell_offer_image[' . $hidden_field_index . ']" id="wps_upsell_offer_image_for_' . $hidden_field_index . '" value="' . esc_attr( $image_post_id ) . '" />
		<a href="#" class="wps_wocuf_pro_remove_image_button button" style="display:inline-block;margin-top: 10px;display:' . $display . '">Remove image</a>
		</div>';
	}

	/**
	 * Function to accept custom values such as name, placeholder,type from user and set those
	 * values to woocommerce_admin_fields array parameter.
	 *
	 * @return void
	 */
	public function save_custom_form_fields() {

		check_ajax_referer( 'wps_wocuf_nonce', 'nonce' );
		$funnel_id   = ! empty( $_POST['funnel_id'] ) ? sanitize_text_field( wp_unslash( $_POST['funnel_id'] ) ) : '';
		$name        = ! empty( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$placeholder = ! empty( $_POST['placeholder'] ) ? sanitize_text_field( wp_unslash( $_POST['placeholder'] ) ) : '';
		$type        = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$description = ! empty( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';

		if ( get_option( 'wps_wocuf_custom_form_values_' . $funnel_id ) ) {
			$custom_input_array = get_option( 'wps_wocuf_custom_form_values_' . $funnel_id, array() );
		} else {
			$custom_input_array = array( 0 => 'null' );
		}

		foreach ( $custom_input_array as $key => $val ) {
			if ( ! empty( $val['name'] ) && $val['name'] === $name ) {
				echo wp_json_encode( 'Field with this name already exist' );
				wp_die();
			}
		}

		$new_data = array(
			'name'        => $name,
			'placeholder' => $placeholder,
			'type'        => $type,
			'description' => $description,
			'funnel_id'   => $funnel_id,
		);

		array_push( $custom_input_array, $new_data );

		update_option( 'wps_wocuf_custom_form_values_' . $funnel_id, $custom_input_array );

		echo wp_json_encode( 'Value Entered Successfully' );
		wp_die();
	}

	/**
	 * Function to get the value of option and show it onto the table in bump creation window.
	 * when the toggle button is switched on.
	 *
	 * @return void
	 */
	public function show_fields_in_table() {

		check_ajax_referer( 'wps_wocuf_nonce', 'nonce' );
		$funnel_id    = ! empty( $_POST['funnel_id'] ) ? sanitize_text_field( wp_unslash( $_POST['funnel_id'] ) ) : '';
		$return_array = get_option( 'wps_wocuf_custom_form_values_' . $funnel_id, false );
		echo wp_json_encode( $return_array );
		wp_die();
	}

	/**
	 * Function to delete a particular row from the table on creation page of order bump.
	 */
	public function delete_a_custom_field_from_table() {
		check_ajax_referer( 'wps_wocuf_nonce', 'nonce' );
		$id            = ! empty( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		$funnel_id     = ! empty( $_POST['funnel_id'] ) ? sanitize_text_field( wp_unslash( $_POST['funnel_id'] ) ) : '';
		$updated_array = array();

		$updated_array = get_option( 'wps_wocuf_custom_form_values_' . $funnel_id, false );

		foreach ( $updated_array as $key => $value ) {

			if ( ! empty( $value['name'] ) && $value['name'] === $id ) {
				unset( $updated_array[ $key ] );
			}
		}

		$updated_array = array_values( $updated_array );
		update_option( 'wps_wocuf_custom_form_values_' . $funnel_id, $updated_array );
		echo wp_json_encode( 'deleted' );
		wp_die();
	}

	/**
	 * Function to accept custom values such as name, placeholder,type from user and set those
	 * values to woocommerce_admin_fields array parameter.
	 *
	 * @return void
	 */
	public function edit_custom_form_fields() {

		check_ajax_referer( 'wps_wocuf_nonce', 'nonce' );
		$funnel_id   = ! empty( $_POST['funnel_id'] ) ? sanitize_text_field( wp_unslash( $_POST['funnel_id'] ) ) : '';
		$name        = ! empty( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$id_name     = ! empty( $_POST['id_name'] ) ? sanitize_text_field( wp_unslash( $_POST['id_name'] ) ) : '';
		$placeholder = ! empty( $_POST['placeholder'] ) ? sanitize_text_field( wp_unslash( $_POST['placeholder'] ) ) : '';
		$type        = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$description = ! empty( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';

		$custom_input_array = get_option( 'wps_wocuf_custom_form_values_' . $funnel_id, array() );

		$new_data = array(
			'name'        => $name,
			'placeholder' => $placeholder,
			'type'        => $type,
			'description' => $description,
			'funnel_id'   => $funnel_id,
		);

		foreach ( $custom_input_array as $key => $val ) {
			if ( isset( $val['name'] ) ) {
				if ( $val['name'] === $id_name ) {
					unset( $custom_input_array[ $key ] );
				}
			}
		}
		$custom_input_array = array_values( $custom_input_array );
		array_push( $custom_input_array, $new_data );

		update_option( 'wps_wocuf_custom_form_values_' . $funnel_id, $custom_input_array );

		echo wp_json_encode( 'Values Edited Successfully' );
		wp_die();
	}

	/**
	 * Upsell refund.
	 *
	 * @since  3.5.0
	 *
	 * @param object  $refund  Refund Object Created.
	 * @param array   $args    Arguments for refund Request.
	 * @param boolean $refund_handler refund handler.
	 *
	 * @throws Exception Exception.
	 */
	public function upsell_modify_refund( $refund = '', $args = '', $refund_handler = false ) {

		$order_id       = $args['order_id'] ? $args['order_id'] : false;
		$order          = wc_get_order( $order_id );
		$payment_method = $order->get_payment_method();

		switch ( $payment_method ) {
			case 'stripe':
				$refund_handler = new WPS_Stripe_Payment_Gateway();
				break;
			default:
				return;
		}

		$upsell_items = get_post_meta( $order->get_id(), '_upsell_remove_items_on_fail', true );

		if ( ! empty( $upsell_items ) && ! empty( $refund_handler ) ) {

			/**
			 * Get upsell items first.
			 * Calculate Refund Amount of these amounts.
			 * Set amount for rest of the payment to be done and set it the refund object.
			 */

			/**
			 * Saturate refund items:: Upsell and Line items seperately.
			 */
			$saturated_items = array();
			foreach ( $args['line_items'] as $item_id => $item ) {

				if ( ! empty( $item['qty'] ) ) {

					if ( ! empty( $item_id ) && in_array( $item_id, $upsell_items, true ) ) {
						$saturated_items['upsell-items'][ $item_id ] = $item;
						$saturated_items['upsell-items']['amount']  += $item['refund_total'] + $item['refund_tax'][1];
					} else {
						$saturated_items['line-items'][ $item_id ] = $item;
						$saturated_items['line-items']['amount']  += $item['refund_total'] + $item['refund_tax'][1];
					}
				}
			}

			$upsell_amount     = ! empty( $saturated_items['upsell-items']['amount'] ) ? $saturated_items['upsell-items']['amount'] : 0;
			$new_refund_amount = ! empty( $saturated_items['line-items']['amount'] ) ? $saturated_items['line-items']['amount'] : 0;

			$upsell_refund = $refund_handler->process_refund( $order_id, $upsell_amount, $args['reason'] );
			if ( $upsell_refund ) {
				$refund->set_amount( $new_refund_amount );
			} else {
				throw new Exception( 'Error Processing Refund. Please refund via merchant portal.', 1 );
			}
		}
		wp_die();
	}

	/**
	 * Paypal Capture Order Action.
	 *
	 * @since  3.6.3
	 *
	 * @param array  $actions Arguments for refund Request.
	 * @param object $order   refund handler.
	 *
	 * @throws Exception Exception.
	 */
	public function add_woocommerce_paypal_order_action( $actions, $order ) {

		$dependent_capture_gateways = array(
			'ppcp-gateway', // For Paypal payments plugin.
			'ppcp-credit-card-gateway', // For Paypal CC payments plugin.
		);

		if ( in_array( $order->get_payment_method(), $dependent_capture_gateways, true ) ) {
			$actions['attempt_manual_capture'] = esc_html__( 'Attempt Manual Capture', 'one-click-upsell-funnel-for-woocommerce-pro' );
		}

		return $actions;
	}

	/**
	 * Paypal Capture Order Action callback.
	 *
	 * @since  3.6.3
	 *
	 * @param object $order   refund handler.
	 */
	public function attempt_capture_paypal_payments( $order = false ) {
		if ( empty( $order ) || 'pending' !== $order->get_status() ) {
			return false;
		}

		$session_handler = get_post_meta( $order->get_id(), 'auto_capture_session_handler', true );

		if ( empty( $session_handler ) ) {
			$order->add_order_note( 'PayPal Payment Details not found!' );
			return false;
		}

		try {
			$order_id           = $order->get_id();
			$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
			$payment_method     = $order->get_payment_method();
			$payment_result     = $available_gateways[ $payment_method ]->process_payment( $order_id );
		} catch ( \Throwable $th ) {
			$error = $th->getMessage();
		}
	}

	/**
	 * Stripe Upsell Fees.
	 *
	 * @param [type] $order_id is the current order id of.
	 * @return void
	 */
	public function display_order_fee_upsell( $order_id ) {
		if ( apply_filters( 'wc_stripe_hide_display_order_payout', false, $order_id ) ) {
			return;
		}
		$fees_stripe = get_post_meta( $order_id, 'upsell_stripe_fee', true );
		$currency = get_post_meta( $order_id, '_stripe_currency', true );
		if ( ! empty( $fees_stripe ) ) {
			$fees_stripe = $fees_stripe / 100;
		}
		if ( ! $fees_stripe || ! $currency ) {
			return;
		}

		?>

	<tr>
		<td class="label stripe-fee">
			<?php echo wc_help_tip( __( 'This represents the fee Stripe collects for the transaction.', 'woocommerce-gateway-stripe' ) ); // wpcs: xss ok. ?>
			<?php esc_html_e( 'Upsell Stripe Fee:', 'woocommerce-gateway-stripe' ); ?>
		</td>
		<td width="1%"></td>
		<td class="total">
			-<?php echo wc_price( $fees_stripe, array( 'currency' => $currency ) ); // wpcs: xss ok. ?>
		</td>
	</tr>

		<?php
	}

	/**
	 * Stripe upsell payout.
	 *
	 * @param [type] $order_id is the current order id of.
	 * @return void
	 */
	public function display_order_payout_upsell( $order_id ) {
		$fees_stripe = get_post_meta( $order_id, 'upsell_stripe_amount', true );
		$currency = get_post_meta( $order_id, '_stripe_currency', true );
		if ( ! empty( $fees_stripe ) ) {
			$fees_stripe = $fees_stripe / 100;
		}
		if ( ! $fees_stripe || ! $currency ) {
			return;
		}
		?>
		<tr>
			<td class="label stripe-payout">
				<?php echo wc_help_tip( __( 'This represents the net total that will be credited to your Stripe bank account. This may be in the currency that is set in your Stripe account.', 'woocommerce-gateway-stripe' ) ); // wpcs: xss ok. ?>
				<?php esc_html_e( 'Upsell Stripe Payout:', 'woocommerce-gateway-stripe' ); ?>
			</td>
			<td width="1%"></td>
			<td class="total">
				<?php echo wc_price( $fees_stripe, array( 'currency' => $currency ) ); // wpcs: xss ok. ?>
			</td>
		</tr>
		<?php
	}





	// End of class.
}
