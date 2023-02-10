<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/includes
 * @author     wpswings <webmaster@wpswings.com>
 */
class Woocommerce_One_Click_Upsell_Funnel_Pro {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woocommerce_One_Click_Upsell_Funnel_Pro_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'WPS_WOCUF_PRO_VERSION' ) ) {
			$this->version = WPS_WOCUF_PRO_VERSION;
		} else {
			$this->version = '3.6.10';
		}

		$this->plugin_name = 'one-click-upsell-funnel-for-woocommerce-pro';

		global $pagenow;

		if ( 'plugins.php' !== $pagenow ) {

			$this->load_dependencies();
			$this->define_mirator_hooks();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();

		} else {

			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			$this->load_base_dependencies();
			$plugin_admin = new Woocommerce_One_Click_Upsell_Funnel_Pro_Admin( $this->get_plugin_name(), $this->get_version() );
			$this->loader->add_action( 'admin_menu', $plugin_admin, 'wps_wocuf_pro_admin_menu' );
		}
	}

	/**
	 * Responsible for Upsell migrator for WPS.
	 *
	 * @since    3.2.0
	 * @access   private
	 */
	private function define_mirator_hooks() {

		/**
		 * The file responsible for Upsell migrator for WPS.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'migrator/class-wps-ocu-migration.php';

		$plugin_migrator = new WPS_OCU_Migration();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_migrator, 'enqueue_styles' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_migrator, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_ajax_process_ajax_events', $plugin_migrator, 'process_ajax_events' );

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woocommerce_One_Click_Upsell_Funnel_Pro_Loader. Orchestrates the hooks of the plugin.
	 * - Woocommerce_One_Click_Upsell_Funnel_Pro_I18n. Defines internationalization functionality.
	 * - Woocommerce_One_Click_Upsell_Funnel_Pro_Admin. Defines all hooks for the admin area.
	 * - Woocommerce_One_Click_Upsell_Funnel_Pro_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-one-click-upsell-funnel-pro-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-one-click-upsell-funnel-pro-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-one-click-upsell-funnel-pro-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce-one-click-upsell-funnel-pro-public.php';

		/**
		 * The file responsible for defining global plugin functions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-one-click-upsell-funnel-pro-global-functions.php';

		/**
		 * The file responsible for handling Official stripe compatibility with seperate
		 * payments order.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'gateways/compatibilities/class-wps-stripe-payment-gateway.php';

		/**
		 * The file responsible for defining Woocommerce Subscriptions compatibility
		 * and handling functions.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-one-click-upsell-funnel-pro-subs-comp.php';

		/**
		 * The class responsible for defining all actions that occur in the onboarding the site data
		 * in the admin side of the site.
		 */
		if ( ! class_exists( 'WPSwings_Onboarding_Helper' ) ) {

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpswings-onboarding-helper.php';
		}

		if ( class_exists( 'WPSwings_Onboarding_Helper' ) ) {

			$this->onboard = new WPSwings_Onboarding_Helper();
		}

		/**
		 * These files are responsible for compatiblity with makewebbetter plugins.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'wps-plugin-compatiblities/class-subscriptions-for-woocommerce-compatiblity.php';

		/**
		 * The file responsible for Upsell Sales by Funnel - Data handling and Stats.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'reporting/class-wps-upsell-report-sales-by-funnel.php';

		$this->loader = new Woocommerce_One_Click_Upsell_Funnel_Pro_Loader();

		/**
		 * The file responsible for Upsell Widgets added within every page builder.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'page-builders/class-wps-upsell-widget-loader.php';
		if ( class_exists( 'WPS_Upsell_Widget_Loader' ) ) {
			WPS_Upsell_Widget_Loader::get_instance();
		}

	}

	/**
	 * Load the deafult dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woocommerce_One_Click_Upsell_Funnel_Pro_Loader. Orchestrates the hooks of the plugin.
	 * - Woocommerce_One_Click_Upsell_Funnel_Pro_I18n. Defines internationalization functionality.
	 * - Woocommerce_One_Click_Upsell_Funnel_Pro_Admin. Defines all hooks for the admin area.
	 * - Woocommerce_One_Click_Upsell_Funnel_Pro_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    3.6.2
	 * @access   private
	 */
	private function load_base_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-one-click-upsell-funnel-pro-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-one-click-upsell-funnel-pro-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the onboarding the site data
		 * in the admin side of the site.
		 */
		if ( ! class_exists( 'WPSwings_Onboarding_Helper' ) ) {

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpswings-onboarding-helper.php';
		}

		if ( class_exists( 'WPSwings_Onboarding_Helper' ) ) {

			$this->onboard = new WPSwings_Onboarding_Helper();
		}

		$this->loader = new Woocommerce_One_Click_Upsell_Funnel_Pro_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woocommerce_One_Click_Upsell_Funnel_Pro_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woocommerce_One_Click_Upsell_Funnel_Pro_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woocommerce_One_Click_Upsell_Funnel_Pro_Admin( $this->get_plugin_name(), $this->get_version() );

		$wps_wocuf_pro_enable_plugin = get_option( 'wps_wocuf_pro_enable_plugin', 'on' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'wps_wocuf_pro_admin_menu' );

		$this->loader->add_action( 'wp_ajax_seach_products_for_offers', $plugin_admin, 'seach_products_for_offers' );

		$this->loader->add_action( 'wp_ajax_seach_products_for_funnel', $plugin_admin, 'seach_products_for_funnel' );

		$this->loader->add_action( 'wp_ajax_search_product_categories_for_funnel', $plugin_admin, 'search_product_categories_for_funnel' );

		$this->loader->add_action( 'wp_ajax_wps_wocuf_pro_validate_license_key', $plugin_admin, 'wps_wocuf_pro_validate_license_key' );

		// Dismiss Elementor inactive notice.
		$this->loader->add_action( 'wp_ajax_wps_upsell_dismiss_elementor_inactive_notice', $plugin_admin, 'dismiss_elementor_inactive_notice' );

		$this->loader->add_action( 'wp_ajax_wps_upsell_init_migrator', $plugin_admin, 'wps_upsell_init_migrator' );

		// Hide Upsell offer pages in admin panel 'Pages'.
		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'hide_upsell_offer_pages_in_admin' );

		$callname_lic         = self::$lic_callback_function;
		$callname_lic_initial = self::$lic_ini_callback_function;
		$day_count            = self::$callname_lic_initial();

		if ( self::$callname_lic() || 0 <= $day_count ) {

			// Include Upsell supported payment gateway classes after plugins are loaded.
			$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'wps_wocuf_pro_plugins_loaded' );

			// Upsell Report.
			$this->loader->add_filter( 'woocommerce_admin_reports', $plugin_admin, 'add_upsell_reporting' );

			// Add Upsell supported payment gateways to Woocommerce.
			$this->loader->add_filter( 'woocommerce_payment_gateways', $plugin_admin, 'wps_wocuf_pro_add_payment_gateways' );

			$this->loader->add_action( 'wps_wocuf_pro_check_license_daily', $plugin_admin, 'wps_wocuf_pro_check_license' );

			$this->loader->add_filter( 'page_template', $plugin_admin, 'wps_wocuf_pro_page_template' );

			// Create new offer - ajax handle function.
			$this->loader->add_action( 'wp_ajax_wps_wocuf_pro_return_offer_content', $plugin_admin, 'return_funnel_offer_section_content' );

			// Insert and Activate respective template ajax handle function.
			$this->loader->add_action( 'wp_ajax_wps_upsell_activate_offer_template_ajax', $plugin_admin, 'activate_respective_offer_template' );

			// Register Parent Order Status for New Stripe, Authorize.net gateway.
			$this->loader->add_action( 'init', $plugin_admin, 'wps_wocuf_pro_register_order_status' );

			// Add custom Status for Stripe, Authorize.net.
			$this->loader->add_filter( 'wc_order_statuses', $plugin_admin, 'wps_wocuf_pro_order_statuses' );

			$this->loader->add_filter( 'admin_head', $plugin_admin, 'wps_wocuf_pro_order_styling' );

			if ( 'on' === $wps_wocuf_pro_enable_plugin ) {

				// Adding Upsell Orders column in Orders table in backend.
				$this->loader->add_filter( 'manage_edit-shop_order_columns', $plugin_admin, 'wps_wocuf_pro_add_columns_to_admin_orders', 11 );

				// Populating Upsell Orders column with Single Order or Upsell order.
				$this->loader->add_action( 'manage_shop_order_posts_custom_column', $plugin_admin, 'wps_wocuf_pro_populate_upsell_order_column', 10, 2 );

				// Add Upsell Filtering dropdown for All Orders, No Upsell Orders, Only Upsell Orders.
				$this->loader->add_filter( 'restrict_manage_posts', $plugin_admin, 'wps_wocuf_pro_restrict_manage_posts' );

				// Modifying query vars for filtering Upsell Orders.
				$this->loader->add_filter( 'request', $plugin_admin, 'wps_wocuf_pro_request_query' );

				// Add 'Upsell Support' column on payment gateways page.
				$this->loader->add_filter( 'woocommerce_payment_gateways_setting_columns', $plugin_admin, 'upsell_support_in_payment_gateway' );

				// 'Upsell Support' content on payment gateways page.
				$this->loader->add_action( 'woocommerce_payment_gateways_setting_column_wps_upsell', $plugin_admin, 'upsell_support_content_in_payment_gateway' );

				// Hook to ajax when submit button is clicked on popup for custom fields.
				$this->loader->add_action( 'wp_ajax_save_custom_form_fields', $plugin_admin, 'save_custom_form_fields' );
				$this->loader->add_action( 'wp_ajax_nopriv_save_custom_form_fields', $plugin_admin, 'save_custom_form_fields' );

				// Hook to ajax when toggle is clicked, the rows will be appended to existing table.
				$this->loader->add_action( 'wp_ajax_show_fields_in_table', $plugin_admin, 'show_fields_in_table' );
				$this->loader->add_action( 'wp_ajax_nopriv_show_fields_in_table', $plugin_admin, 'show_fields_in_table' );

				// Hook to delete a row from table.
				$this->loader->add_action( 'wp_ajax_delete_a_custom_field_from_table', $plugin_admin, 'delete_a_custom_field_from_table' );
				$this->loader->add_action( 'wp_ajax_nopriv_delete_a_custom_field_from_table', $plugin_admin, 'delete_a_custom_field_from_table' );

				// Hook to ajax when edit button is clicked on popup for custom fields.
				$this->loader->add_action( 'wp_ajax_edit_custom_form_fields', $plugin_admin, 'edit_custom_form_fields' );
				$this->loader->add_action( 'wp_ajax_nopriv_edit_custom_form_fields', $plugin_admin, 'edit_custom_form_fields' );
				
				$this->loader->add_action( 'woocommerce_admin_order_totals_after_total', $plugin_admin, 'display_order_fee_upsell' );
				$this->loader->add_action( 'woocommerce_admin_order_totals_after_total', $plugin_admin, 'display_order_payout_upsell' );
				
			}

			$this->loader->add_action( 'woocommerce_create_refund', $plugin_admin, 'upsell_modify_refund', 10, 3 );
			$this->loader->add_filter( 'woocommerce_order_actions', $plugin_admin, 'add_woocommerce_paypal_order_action', 10, 2 );
			$this->loader->add_action( 'woocommerce_order_action_attempt_manual_capture', $plugin_admin, 'attempt_capture_paypal_payments' );

		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woocommerce_One_Click_Upsell_Funnel_Pro_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Set cron recurrence time for 'wps_wocuf_twenty_minutes' schedule.
		$this->loader->add_filter( 'cron_schedules', $plugin_public, 'set_cron_schedule_time' );

		// Redirect upsell offer pages if not admin or upsell nonce expired.
		$this->loader->add_action( 'template_redirect', $plugin_public, 'upsell_offer_page_redirect' );

		// Add support for Upsell Offer template for pages.
		$this->loader->add_action( 'init', $plugin_public, 'add_upsell_template_support' );

		// Include Upsell Offer template.
		$this->loader->add_filter( 'template_include', $plugin_public, 'upsell_template_include', 11 /* After Plugins/WooCommerce */ );
		/**
		 * Process capture for paypal and stripe on order processing and completion for orders.
		 * Using completion coz some products don't allow processing like downloadable products.
		 */
		$this->loader->add_action( 'woocommerce_order_status_on-hold_to_processing', $plugin_public, 'wps_wocuf_pro_capture' );

		$this->loader->add_action( 'woocommerce_order_status_on-hold_to_completed', $plugin_public, 'wps_wocuf_pro_capture' );

		// Added verification ajax for payment intents( after version 3.2.0 ).
		$this->loader->add_action( 'wc_ajax_wps_wocuf_pro_verify_intent', $plugin_public, 'wps_wocuf_pro_verify_intent' );

		// Hide upsell offer pages from nav menu front-end.
		$this->loader->add_filter( 'wp_page_menu_args', $plugin_public, 'exclude_pages_from_front_end', 99 );

		// Hide upsell offer pages from added menu list in customizer and admin panel.
		$this->loader->add_filter( 'wp_get_nav_menu_items', $plugin_public, 'exclude_pages_from_menu_list', 10, 3 );

		$wps_upsell_global_settings = get_option( 'wps_upsell_global_options', array() );

		$remove_all_styles = ! empty( $wps_upsell_global_settings['remove_all_styles'] ) ? $wps_upsell_global_settings['remove_all_styles'] : 'yes';

		if ( 'yes' === $remove_all_styles && wps_upsell_elementor_plugin_active() ) {

			// Remove styles from offer pages.
			$this->loader->add_action( 'wp_print_styles', $plugin_public, 'remove_styles_offer_pages' );
		}

		$wps_wocuf_pro_enable_plugin = get_option( 'wps_wocuf_pro_enable_plugin', 'on' );

		$callname_lic         = self::$lic_callback_function;
		$callname_lic_initial = self::$lic_ini_callback_function;
		$day_count            = self::$callname_lic_initial();

		if ( self::$callname_lic() || 0 <= $day_count ) {

			$this->loader->add_action( 'init', $plugin_public, 'upsell_shortcodes' );

			// Remove http and https from Upsell Action shortcodes added by Page Builders.
			$this->loader->add_filter( 'the_content', $plugin_public, 'filter_upsell_shortcodes_content' );

			// Hide currency switcher on any page.
			$this->loader->add_filter( 'wps_currency_switcher_side_switcher_after_html', $plugin_public, 'hide_switcher_on_upsell_page' );

			if ( 'on' === $wps_wocuf_pro_enable_plugin ) {

				// Initiate Upsell Orders before processing payment.
				$this->loader->add_action( 'woocommerce_checkout_order_processed', $plugin_public, 'wps_wocuf_initate_upsell_orders',90 );

				// When user clicks on No thanks for Upsell offer.
				! is_admin() && $this->loader->add_action( 'wp_loaded', $plugin_public, 'wps_wocuf_pro_process_the_funnel' );

				// When user clicks on Add upsell product to my Order.
				! is_admin() && $this->loader->add_action( 'wp_loaded', $plugin_public, 'wps_wocuf_pro_charge_the_offer' );

				// Define Cron schedule fire Event for Order payment process.
				$this->loader->add_action( 'wps_wocuf_order_cron_schedule', $plugin_public, 'order_payment_cron_fire_event' );

				// Global Custom CSS.
				$this->loader->add_action( 'wp_head', $plugin_public, 'global_custom_css' );

				// Global custom JS.
				$this->loader->add_action( 'wp_footer', $plugin_public, 'global_custom_js' );

				// Reset Timer session for Timer shortcode.
				$this->loader->add_action( 'wp_footer', $plugin_public, 'reset_timer_session_data' );

				// Hide the upsell meta for Upsell order item for Customers.
				! is_admin() && $this->loader->add_filter( 'woocommerce_order_item_get_formatted_meta_data', $plugin_public, 'hide_order_item_formatted_meta_data' );

				// Handle Upsell Orders on Thankyou for Success Rate and Stats.
				$this->loader->add_action( 'woocommerce_thankyou', $plugin_public, 'upsell_sales_by_funnel_handling' );

				// Google Analytics and Facebook Pixel Tracking - Start.

				// GA and FB Pixel Base Code.
				$this->loader->add_action( 'wp_head', $plugin_public, 'add_ga_and_fb_pixel_base_code' );

				// GA and FB Pixel Purchase Event - Track Parent Order on 1st Upsell Offer Page.
				$this->loader->add_action( 'wp_head', $plugin_public, 'ga_and_fb_pixel_purchase_event_for_parent_order', 100 );

				// GA and FB Pixel Purchase Event - Track Order on Thankyou page.
				$this->loader->add_action( 'woocommerce_thankyou', $plugin_public, 'ga_and_fb_pixel_purchase_event' );

				/**
				 * Compatibility for Enhanced Ecommerce Google Analytics Plugin by Tatvic.
				 * Remove plugin's Purchase Event from Thankyou page when
				 * Upsell Purchase is enabled.
				 */
				$this->loader->add_action( 'wp_loaded', $plugin_public, 'upsell_ga_compatibility_for_eega' );

				/**
				 * Compatibility for Facebook for WooCommerce plugin.
				 * Remove plugin's Purchase Event from Thankyou page when
				 * Upsell Purchase is enabled.
				 */
				$this->loader->add_action( 'woocommerce_init', $plugin_public, 'upsell_fbp_compatibility_for_ffw' );

				// Google Analytics and Facebook Pixel Tracking - End.
			}

			// Hide Redirect to thankyou page.
			$this->loader->add_action( 'template_redirect', $plugin_public, 'wps_wocuf_pro_custom_th_redirection' );

			// Downloadable products issue fix.
			$this->loader->add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', $plugin_public, 'handle_order_completion_for_downloadable_products', 99, 2 );

			// Set Email Triggers for Upsell Custom Status transitions.
			$this->loader->add_action( 'woocommerce_init', $plugin_public, 'hook_order_confirmation_emails_for_upsell_custom_statuses' );

			// wps save custom form.
			$this->loader->add_action( 'wps_save_form', $plugin_public, 'wps_form_save', 20, 2 );

		}
		// add additional offer product to order.
		$this->loader->add_action( 'wp_ajax_add_additional_offer_to_order', $plugin_public, 'add_additional_offer_to_order' );
		$this->loader->add_action( 'wp_ajax_nopriv_add_additional_offer_to_order', $plugin_public, 'add_additional_offer_to_order' );
		$this->loader->add_action( 'woocommerce_init', $plugin_public, 'check_compatibltiy_instance_cs' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woocommerce_One_Click_Upsell_Funnel_Pro_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Public static variable to be accessed in this plugin.
	 *
	 * @var string $lic_callback_function lic_callback_function.
	 */
	public static $lic_callback_function = 'check_lcns_validity';

	/**
	 * Public static variable to be accessed in this plugin.
	 *
	 * @var string $lic_ini_callback_function lic_ini_callback_function.
	 */
	public static $lic_ini_callback_function = 'check_lcns_initial_days';

	/**
	 * Validate the use of features of this plugin.
	 *
	 * @since    1.0.0
	 */
	public static function check_lcns_validity() {

		$wps_wocuf_lcns_key = get_option( 'wps_wocuf_pro_license_key', '' );

		$wps_wocuf_lcns_status = get_option( 'wps_wocuf_pro_license_check', '' );

		if ( $wps_wocuf_lcns_key && ( true === $wps_wocuf_lcns_status || '1' === $wps_wocuf_lcns_status ) ) {
			return true;
		} else {

			return false;
		}
	}

	/**
	 * Validate the use of features of this plugin for initial days.
	 *
	 * @since    1.0.0
	 */
	public static function check_lcns_initial_days() {

		$thirty_days = get_option( 'wps_wocuf_pro_activated_timestamp', 0 );

		$current_time = time();

		$day_count = ( $thirty_days - $current_time ) / ( 24 * 60 * 60 );

		return 10;
	}
}
