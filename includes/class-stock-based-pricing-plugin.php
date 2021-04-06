<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Stock_based_pricing_plugin
 * @subpackage Stock_based_pricing_plugin/includes
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
 * @package    Stock_based_pricing_plugin
 * @subpackage Stock_based_pricing_plugin/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Stock_based_pricing_plugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Stock_based_pricing_plugin_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $sbpp_onboard    To initializsed the object of class onboard.
	 */
	protected $sbpp_onboard;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area,
	 * the public-facing side of the site and common side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'STOCK_BASED_PRICING_PLUGIN_VERSION' ) ) {

			$this->version = STOCK_BASED_PRICING_PLUGIN_VERSION;
		} else {

			$this->version = '1.0.0';
		}

		$this->plugin_name = 'stock-based-pricing-plugin';

		$this->stock_based_pricing_plugin_dependencies();
		$this->stock_based_pricing_plugin_locale();
		if ( is_admin() ) {
			$this->stock_based_pricing_plugin_admin_hooks();
		} else {
			$this->stock_based_pricing_plugin_public_hooks();
		}
		$this->stock_based_pricing_plugin_common_hooks();

		$this->stock_based_pricing_plugin_api_hooks();


	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Stock_based_pricing_plugin_Loader. Orchestrates the hooks of the plugin.
	 * - Stock_based_pricing_plugin_i18n. Defines internationalization functionality.
	 * - Stock_based_pricing_plugin_Admin. Defines all hooks for the admin area.
	 * - Stock_based_pricing_plugin_Common. Defines all hooks for the common area.
	 * - Stock_based_pricing_plugin_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function stock_based_pricing_plugin_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-stock-based-pricing-plugin-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-stock-based-pricing-plugin-i18n.php';

		if ( is_admin() ) {

			// The class responsible for defining all actions that occur in the admin area.
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-stock-based-pricing-plugin-admin.php';

			// The class responsible for on-boarding steps for plugin.
			if ( is_dir(  plugin_dir_path( dirname( __FILE__ ) ) . 'onboarding' ) && ! class_exists( 'Stock_based_pricing_plugin_Onboarding_Steps' ) ) {
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-stock-based-pricing-plugin-onboarding-steps.php';
			}

			if ( class_exists( 'Stock_based_pricing_plugin_Onboarding_Steps' ) ) {
				$sbpp_onboard_steps = new Stock_based_pricing_plugin_Onboarding_Steps();
			}
		} else {

			// The class responsible for defining all actions that occur in the public-facing side of the site.
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-stock-based-pricing-plugin-public.php';

		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'package/rest-api/class-stock-based-pricing-plugin-rest-api.php';

		/**
		 * This class responsible for defining common functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/class-stock-based-pricing-plugin-common.php';

		$this->loader = new Stock_based_pricing_plugin_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Stock_based_pricing_plugin_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function stock_based_pricing_plugin_locale() {

		$plugin_i18n = new Stock_based_pricing_plugin_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function stock_based_pricing_plugin_admin_hooks() {

		$sbpp_plugin_admin = new Stock_based_pricing_plugin_Admin( $this->sbpp_get_plugin_name(), $this->sbpp_get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $sbpp_plugin_admin, 'sbpp_admin_enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $sbpp_plugin_admin, 'sbpp_admin_enqueue_scripts' );

		// Add settings menu for stock-based-pricing-plugin.
		$this->loader->add_action( 'admin_menu', $sbpp_plugin_admin, 'sbpp_options_page' );
		$this->loader->add_action( 'admin_menu', $sbpp_plugin_admin, 'mwb_sbpp_remove_default_submenu', 50 );

		// All admin actions and filters after License Validation goes here.
		$this->loader->add_filter( 'mwb_add_plugins_menus_array', $sbpp_plugin_admin, 'sbpp_admin_submenu_page', 15 );
		$this->loader->add_filter( 'sbpp_template_settings_array', $sbpp_plugin_admin, 'sbpp_admin_template_settings_page', 10 );
		$this->loader->add_filter( 'sbpp_general_settings_array', $sbpp_plugin_admin, 'sbpp_admin_general_settings_page', 10 );

		// Saving tab settings.
		$this->loader->add_action( 'admin_init', $sbpp_plugin_admin, 'sbpp_admin_save_tab_settings' );

	}

	/**
	 * Register all of the hooks related to the common functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function stock_based_pricing_plugin_common_hooks() {

		$sbpp_plugin_common = new Stock_based_pricing_plugin_Common( $this->sbpp_get_plugin_name(), $this->sbpp_get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $sbpp_plugin_common, 'sbpp_common_enqueue_styles' );

		$this->loader->add_action( 'wp_enqueue_scripts', $sbpp_plugin_common, 'sbpp_common_enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function stock_based_pricing_plugin_public_hooks() {

		$sbpp_plugin_public = new Stock_based_pricing_plugin_Public( $this->sbpp_get_plugin_name(), $this->sbpp_get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $sbpp_plugin_public, 'sbpp_public_enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $sbpp_plugin_public, 'sbpp_public_enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the api functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function stock_based_pricing_plugin_api_hooks() {

		$sbpp_plugin_api = new Stock_based_pricing_plugin_Rest_Api( $this->sbpp_get_plugin_name(), $this->sbpp_get_version() );

		$this->loader->add_action( 'rest_api_init', $sbpp_plugin_api, 'mwb_sbpp_add_endpoint' );

	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function sbpp_run() {
		$this->loader->sbpp_run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function sbpp_get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Stock_based_pricing_plugin_Loader    Orchestrates the hooks of the plugin.
	 */
	public function sbpp_get_loader() {
		return $this->loader;
	}


	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Stock_based_pricing_plugin_Onboard    Orchestrates the hooks of the plugin.
	 */
	public function sbpp_get_onboard() {
		return $this->sbpp_onboard;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function sbpp_get_version() {
		return $this->version;
	}

	/**
	 * Predefined default mwb_sbpp_plug tabs.
	 *
	 * @return  Array       An key=>value pair of stock-based-pricing-plugin tabs.
	 */
	public function mwb_sbpp_plug_default_tabs() {

		$sbpp_default_tabs = array();

		$sbpp_default_tabs['stock-based-pricing-plugin-general'] = array(
			'title'       => esc_html__( 'General Setting', 'stock-based-pricing-plugin' ),
			'name'        => 'stock-based-pricing-plugin-general',
		);
		$sbpp_default_tabs = apply_filters( 'mwb_sbpp_plugin_standard_admin_settings_tabs', $sbpp_default_tabs );

		$sbpp_default_tabs['stock-based-pricing-plugin-system-status'] = array(
			'title'       => esc_html__( 'System Status', 'stock-based-pricing-plugin' ),
			'name'        => 'stock-based-pricing-plugin-system-status',
		);
		$sbpp_default_tabs['stock-based-pricing-plugin-template'] = array(
			'title'       => esc_html__( 'Templates', 'stock-based-pricing-plugin' ),
			'name'        => 'stock-based-pricing-plugin-template',
		);
		$sbpp_default_tabs['stock-based-pricing-plugin-overview'] = array(
			'title'       => esc_html__( 'Overview', 'stock-based-pricing-plugin' ),
			'name'        => 'stock-based-pricing-plugin-overview',
		);

		return $sbpp_default_tabs;
	}

	/**
	 * Locate and load appropriate tempate.
	 *
	 * @since   1.0.0
	 * @param string $path path file for inclusion.
	 * @param array  $params parameters to pass to the file for access.
	 */
	public function mwb_sbpp_plug_load_template( $path, $params = array() ) {

		$sbpp_file_path = STOCK_BASED_PRICING_PLUGIN_DIR_PATH . $path;

		if ( file_exists( $sbpp_file_path ) ) {

			include $sbpp_file_path;
		} else {

			/* translators: %s: file path */
			$sbpp_notice = sprintf( esc_html__( 'Unable to locate file at location "%s". Some features may not work properly in this plugin. Please contact us!', 'stock-based-pricing-plugin' ), $sbpp_file_path );
			$this->mwb_sbpp_plug_admin_notice( $sbpp_notice, 'error' );
		}
	}

	/**
	 * Show admin notices.
	 *
	 * @param  string $sbpp_message    Message to display.
	 * @param  string $type       notice type, accepted values - error/update/update-nag.
	 * @since  1.0.0
	 */
	public static function mwb_sbpp_plug_admin_notice( $sbpp_message, $type = 'error' ) {

		$sbpp_classes = 'notice ';

		switch ( $type ) {

			case 'update':
			$sbpp_classes .= 'updated is-dismissible';
			break;

			case 'update-nag':
			$sbpp_classes .= 'update-nag is-dismissible';
			break;

			case 'success':
			$sbpp_classes .= 'notice-success is-dismissible';
			break;

			default:
			$sbpp_classes .= 'notice-error is-dismissible';
		}

		$sbpp_notice  = '<div class="' . esc_attr( $sbpp_classes ) . ' mwb-errorr-8">';
		$sbpp_notice .= '<p>' . esc_html( $sbpp_message ) . '</p>';
		$sbpp_notice .= '</div>';

		echo wp_kses_post( $sbpp_notice );
	}


	/**
	 * Show wordpress and server info.
	 *
	 * @return  Array $sbpp_system_data       returns array of all wordpress and server related information.
	 * @since  1.0.0
	 */
	public function mwb_sbpp_plug_system_status() {
		global $wpdb;
		$sbpp_system_status = array();
		$sbpp_wordpress_status = array();
		$sbpp_system_data = array();

		// Get the web server.
		$sbpp_system_status['web_server'] = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		// Get PHP version.
		$sbpp_system_status['php_version'] = function_exists( 'phpversion' ) ? phpversion() : __( 'N/A (phpversion function does not exist)', 'stock-based-pricing-plugin' );

		// Get the server's IP address.
		$sbpp_system_status['server_ip'] = isset( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) : '';

		// Get the server's port.
		$sbpp_system_status['server_port'] = isset( $_SERVER['SERVER_PORT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_PORT'] ) ) : '';

		// Get the uptime.
		$sbpp_system_status['uptime'] = function_exists( 'exec' ) ? @exec( 'uptime -p' ) : __( 'N/A (make sure exec function is enabled)', 'stock-based-pricing-plugin' );

		// Get the server path.
		$sbpp_system_status['server_path'] = defined( 'ABSPATH' ) ? ABSPATH : __( 'N/A (ABSPATH constant not defined)', 'stock-based-pricing-plugin' );

		// Get the OS.
		$sbpp_system_status['os'] = function_exists( 'php_uname' ) ? php_uname( 's' ) : __( 'N/A (php_uname function does not exist)', 'stock-based-pricing-plugin' );

		// Get WordPress version.
		$sbpp_wordpress_status['wp_version'] = function_exists( 'get_bloginfo' ) ? get_bloginfo( 'version' ) : __( 'N/A (get_bloginfo function does not exist)', 'stock-based-pricing-plugin' );

		// Get and count active WordPress plugins.
		$sbpp_wordpress_status['wp_active_plugins'] = function_exists( 'get_option' ) ? count( get_option( 'active_plugins' ) ) : __( 'N/A (get_option function does not exist)', 'stock-based-pricing-plugin' );

		// See if this site is multisite or not.
		$sbpp_wordpress_status['wp_multisite'] = function_exists( 'is_multisite' ) && is_multisite() ? __( 'Yes', 'stock-based-pricing-plugin' ) : __( 'No', 'stock-based-pricing-plugin' );

		// See if WP Debug is enabled.
		$sbpp_wordpress_status['wp_debug_enabled'] = defined( 'WP_DEBUG' ) ? __( 'Yes', 'stock-based-pricing-plugin' ) : __( 'No', 'stock-based-pricing-plugin' );

		// See if WP Cache is enabled.
		$sbpp_wordpress_status['wp_cache_enabled'] = defined( 'WP_CACHE' ) ? __( 'Yes', 'stock-based-pricing-plugin' ) : __( 'No', 'stock-based-pricing-plugin' );

		// Get the total number of WordPress users on the site.
		$sbpp_wordpress_status['wp_users'] = function_exists( 'count_users' ) ? count_users() : __( 'N/A (count_users function does not exist)', 'stock-based-pricing-plugin' );

		// Get the number of published WordPress posts.
		$sbpp_wordpress_status['wp_posts'] = wp_count_posts()->publish >= 1 ? wp_count_posts()->publish : __( '0', 'stock-based-pricing-plugin' );

		// Get PHP memory limit.
		$sbpp_system_status['php_memory_limit'] = function_exists( 'ini_get' ) ? (int) ini_get( 'memory_limit' ) : __( 'N/A (ini_get function does not exist)', 'stock-based-pricing-plugin' );

		// Get the PHP error log path.
		$sbpp_system_status['php_error_log_path'] = ! ini_get( 'error_log' ) ? __( 'N/A', 'stock-based-pricing-plugin' ) : ini_get( 'error_log' );

		// Get PHP max upload size.
		$sbpp_system_status['php_max_upload'] = function_exists( 'ini_get' ) ? (int) ini_get( 'upload_max_filesize' ) : __( 'N/A (ini_get function does not exist)', 'stock-based-pricing-plugin' );

		// Get PHP max post size.
		$sbpp_system_status['php_max_post'] = function_exists( 'ini_get' ) ? (int) ini_get( 'post_max_size' ) : __( 'N/A (ini_get function does not exist)', 'stock-based-pricing-plugin' );

		// Get the PHP architecture.
		if ( PHP_INT_SIZE == 4 ) {
			$sbpp_system_status['php_architecture'] = '32-bit';
		} elseif ( PHP_INT_SIZE == 8 ) {
			$sbpp_system_status['php_architecture'] = '64-bit';
		} else {
			$sbpp_system_status['php_architecture'] = 'N/A';
		}

		// Get server host name.
		$sbpp_system_status['server_hostname'] = function_exists( 'gethostname' ) ? gethostname() : __( 'N/A (gethostname function does not exist)', 'stock-based-pricing-plugin' );

		// Show the number of processes currently running on the server.
		$sbpp_system_status['processes'] = function_exists( 'exec' ) ? @exec( 'ps aux | wc -l' ) : __( 'N/A (make sure exec is enabled)', 'stock-based-pricing-plugin' );

		// Get the memory usage.
		$sbpp_system_status['memory_usage'] = function_exists( 'memory_get_peak_usage' ) ? round( memory_get_peak_usage( true ) / 1024 / 1024, 2 ) : 0;

		// Get CPU usage.
		// Check to see if system is Windows, if so then use an alternative since sys_getloadavg() won't work.
		if ( stristr( PHP_OS, 'win' ) ) {
			$sbpp_system_status['is_windows'] = true;
			$sbpp_system_status['windows_cpu_usage'] = function_exists( 'exec' ) ? @exec( 'wmic cpu get loadpercentage /all' ) : __( 'N/A (make sure exec is enabled)', 'stock-based-pricing-plugin' );
		}

		// Get the memory limit.
		$sbpp_system_status['memory_limit'] = function_exists( 'ini_get' ) ? (int) ini_get( 'memory_limit' ) : __( 'N/A (ini_get function does not exist)', 'stock-based-pricing-plugin' );

		// Get the PHP maximum execution time.
		$sbpp_system_status['php_max_execution_time'] = function_exists( 'ini_get' ) ? ini_get( 'max_execution_time' ) : __( 'N/A (ini_get function does not exist)', 'stock-based-pricing-plugin' );

		// Get outgoing IP address.
		$sbpp_system_status['outgoing_ip'] = function_exists( 'file_get_contents' ) ? file_get_contents( 'http://ipecho.net/plain' ) : __( 'N/A (file_get_contents function does not exist)', 'stock-based-pricing-plugin' );

		$sbpp_system_data['php'] = $sbpp_system_status;
		$sbpp_system_data['wp'] = $sbpp_wordpress_status;

		return $sbpp_system_data;
	}

	/**
	 * Generate html components.
	 *
	 * @param  string $sbpp_components    html to display.
	 * @since  1.0.0
	 */
	public function mwb_sbpp_plug_generate_html( $sbpp_components = array() ) {
		if ( is_array( $sbpp_components ) && ! empty( $sbpp_components ) ) {
			foreach ( $sbpp_components as $sbpp_component ) {
				if ( ! empty( $sbpp_component['type'] ) &&  ! empty( $sbpp_component['id'] ) ) {
					switch ( $sbpp_component['type'] ) {

						case 'hidden':
						case 'number':
						case 'email':
						case 'text':
						?>
						<div class="mwb-form-group mwb-sbpp-<?php echo esc_attr($sbpp_component['type']); ?>">
							<div class="mwb-form-group__label">
								<label for="<?php echo esc_attr( $sbpp_component['id'] ); ?>" class="mwb-form-label"><?php echo ( isset( $sbpp_component['title'] ) ? esc_html( $sbpp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="mwb-form-group__control">
								<label class="mdc-text-field mdc-text-field--outlined">
									<span class="mdc-notched-outline">
										<span class="mdc-notched-outline__leading"></span>
										<span class="mdc-notched-outline__notch">
											<?php if ( 'number' != $sbpp_component['type'] ) { ?>
												<span class="mdc-floating-label" id="my-label-id" style=""><?php echo ( isset( $sbpp_component['placeholder'] ) ? esc_attr( $sbpp_component['placeholder'] ) : '' ); ?></span>
											<?php } ?>
										</span>
										<span class="mdc-notched-outline__trailing"></span>
									</span>
									<input
									class="mdc-text-field__input <?php echo ( isset( $sbpp_component['class'] ) ? esc_attr( $sbpp_component['class'] ) : '' ); ?>" 
									name="<?php echo ( isset( $sbpp_component['name'] ) ? esc_html( $sbpp_component['name'] ) : esc_html( $sbpp_component['id'] ) ); ?>"
									id="<?php echo esc_attr( $sbpp_component['id'] ); ?>"
									type="<?php echo esc_attr( $sbpp_component['type'] ); ?>"
									value="<?php echo ( isset( $sbpp_component['value'] ) ? esc_attr( $sbpp_component['value'] ) : '' ); ?>"
									placeholder="<?php echo ( isset( $sbpp_component['placeholder'] ) ? esc_attr( $sbpp_component['placeholder'] ) : '' ); ?>"
									>
								</label>
								<div class="mdc-text-field-helper-line">
									<div class="mdc-text-field-helper-text--persistent mwb-helper-text" id="" aria-hidden="true"><?php echo ( isset( $sbpp_component['description'] ) ? esc_attr( $sbpp_component['description'] ) : '' ); ?></div>
								</div>
							</div>
						</div>
						<?php
						break;

						case 'password':
						?>
						<div class="mwb-form-group">
							<div class="mwb-form-group__label">
								<label for="<?php echo esc_attr( $sbpp_component['id'] ); ?>" class="mwb-form-label"><?php echo ( isset( $sbpp_component['title'] ) ? esc_html( $sbpp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="mwb-form-group__control">
								<label class="mdc-text-field mdc-text-field--outlined mdc-text-field--with-trailing-icon">
									<span class="mdc-notched-outline">
										<span class="mdc-notched-outline__leading"></span>
										<span class="mdc-notched-outline__notch">
										</span>
										<span class="mdc-notched-outline__trailing"></span>
									</span>
									<input 
									class="mdc-text-field__input <?php echo ( isset( $sbpp_component['class'] ) ? esc_attr( $sbpp_component['class'] ) : '' ); ?> mwb-form__password" 
									name="<?php echo ( isset( $sbpp_component['name'] ) ? esc_html( $sbpp_component['name'] ) : esc_html( $sbpp_component['id'] ) ); ?>"
									id="<?php echo esc_attr( $sbpp_component['id'] ); ?>"
									type="<?php echo esc_attr( $sbpp_component['type'] ); ?>"
									value="<?php echo ( isset( $sbpp_component['value'] ) ? esc_attr( $sbpp_component['value'] ) : '' ); ?>"
									placeholder="<?php echo ( isset( $sbpp_component['placeholder'] ) ? esc_attr( $sbpp_component['placeholder'] ) : '' ); ?>"
									>
									<i class="material-icons mdc-text-field__icon mdc-text-field__icon--trailing mwb-password-hidden" tabindex="0" role="button">visibility</i>
								</label>
								<div class="mdc-text-field-helper-line">
									<div class="mdc-text-field-helper-text--persistent mwb-helper-text" id="" aria-hidden="true"><?php echo ( isset( $sbpp_component['description'] ) ? esc_attr( $sbpp_component['description'] ) : '' ); ?></div>
								</div>
							</div>
						</div>
						<?php
						break;

						case 'textarea':
						?>
						<div class="mwb-form-group">
							<div class="mwb-form-group__label">
								<label class="mwb-form-label" for="<?php echo esc_attr( $sbpp_component['id'] ); ?>"><?php echo ( isset( $sbpp_component['title'] ) ? esc_html( $sbpp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="mwb-form-group__control">
								<label class="mdc-text-field mdc-text-field--outlined mdc-text-field--textarea"  	for="text-field-hero-input">
									<span class="mdc-notched-outline">
										<span class="mdc-notched-outline__leading"></span>
										<span class="mdc-notched-outline__notch">
											<span class="mdc-floating-label"><?php echo ( isset( $sbpp_component['placeholder'] ) ? esc_attr( $sbpp_component['placeholder'] ) : '' ); ?></span>
										</span>
										<span class="mdc-notched-outline__trailing"></span>
									</span>
									<span class="mdc-text-field__resizer">
										<textarea class="mdc-text-field__input <?php echo ( isset( $sbpp_component['class'] ) ? esc_attr( $sbpp_component['class'] ) : '' ); ?>" rows="2" cols="25" aria-label="Label" name="<?php echo ( isset( $sbpp_component['name'] ) ? esc_html( $sbpp_component['name'] ) : esc_html( $sbpp_component['id'] ) ); ?>" id="<?php echo esc_attr( $sbpp_component['id'] ); ?>" placeholder="<?php echo ( isset( $sbpp_component['placeholder'] ) ? esc_attr( $sbpp_component['placeholder'] ) : '' ); ?>"><?php echo ( isset( $sbpp_component['value'] ) ? esc_textarea( $sbpp_component['value'] ) : '' ); // WPCS: XSS ok. ?></textarea>
									</span>
								</label>

							</div>
						</div>

						<?php
						break;

						case 'select':
						case 'multiselect':
						?>
						<div class="mwb-form-group">
							<div class="mwb-form-group__label">
								<label class="mwb-form-label" for="<?php echo esc_attr( $sbpp_component['id'] ); ?>"><?php echo ( isset( $sbpp_component['title'] ) ? esc_html( $sbpp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="mwb-form-group__control">
								<div class="mwb-form-select">
									<select id="<?php echo esc_attr( $sbpp_component['id'] ); ?>" name="<?php echo ( isset( $sbpp_component['name'] ) ? esc_html( $sbpp_component['name'] ) : '' ); ?><?php echo ( 'multiselect' === $sbpp_component['type'] ) ? '[]' : ''; ?>" id="<?php echo esc_attr( $sbpp_component['id'] ); ?>" class="mdl-textfield__input <?php echo ( isset( $sbpp_component['class'] ) ? esc_attr( $sbpp_component['class'] ) : '' ); ?>" <?php echo 'multiselect' === $sbpp_component['type'] ? 'multiple="multiple"' : ''; ?> >
										<?php
										foreach ( $sbpp_component['options'] as $sbpp_key => $sbpp_val ) {
											?>
											<option value="<?php echo esc_attr( $sbpp_key ); ?>"
												<?php
												if ( is_array( $sbpp_component['value'] ) ) {
													selected( in_array( (string) $sbpp_key, $sbpp_component['value'], true ), true );
												} else {
													selected( $sbpp_component['value'], (string) $sbpp_key );
												}
												?>
												>
												<?php echo esc_html( $sbpp_val ); ?>
											</option>
											<?php
										}
										?>
									</select>
									<label class="mdl-textfield__label" for="octane"><?php echo esc_html( $sbpp_component['description'] ); ?><?php echo ( isset( $sbpp_component['description'] ) ? esc_attr( $sbpp_component['description'] ) : '' ); ?></label>
								</div>
							</div>
						</div>

						<?php
						break;

						case 'checkbox':
						?>
						<div class="mwb-form-group">
							<div class="mwb-form-group__label">
								<label for="<?php echo esc_attr( $sbpp_component['id'] ); ?>" class="mwb-form-label"><?php echo ( isset( $sbpp_component['title'] ) ? esc_html( $sbpp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="mwb-form-group__control mwb-pl-4">
								<div class="mdc-form-field">
									<div class="mdc-checkbox">
										<input 
										name="<?php echo ( isset( $sbpp_component['name'] ) ? esc_html( $sbpp_component['name'] ) : esc_html( $sbpp_component['id'] ) ); ?>"
										id="<?php echo esc_attr( $sbpp_component['id'] ); ?>"
										type="checkbox"
										class="mdc-checkbox__native-control <?php echo ( isset( $sbpp_component['class'] ) ? esc_attr( $sbpp_component['class'] ) : '' ); ?>"
										value="<?php echo ( isset( $sbpp_component['value'] ) ? esc_attr( $sbpp_component['value'] ) : '' ); ?>"
										<?php checked( $sbpp_component['value'], '1' ); ?>
										/>
										<div class="mdc-checkbox__background">
											<svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">
												<path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>
											</svg>
											<div class="mdc-checkbox__mixedmark"></div>
										</div>
										<div class="mdc-checkbox__ripple"></div>
									</div>
									<label for="checkbox-1"><?php echo ( isset( $sbpp_component['description'] ) ? esc_attr( $sbpp_component['description'] ) : '' ); ?></label>
								</div>
							</div>
						</div>
						<?php
						break;

						case 'radio':
						?>
						<div class="mwb-form-group">
							<div class="mwb-form-group__label">
								<label for="<?php echo esc_attr( $sbpp_component['id'] ); ?>" class="mwb-form-label"><?php echo ( isset( $sbpp_component['title'] ) ? esc_html( $sbpp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="mwb-form-group__control mwb-pl-4">
								<div class="mwb-flex-col">
									<?php
									foreach ( $sbpp_component['options'] as $sbpp_radio_key => $sbpp_radio_val ) {
										?>
										<div class="mdc-form-field">
											<div class="mdc-radio">
												<input
												name="<?php echo ( isset( $sbpp_component['name'] ) ? esc_html( $sbpp_component['name'] ) : esc_html( $sbpp_component['id'] ) ); ?>"
												value="<?php echo esc_attr( $sbpp_radio_key ); ?>"
												type="radio"
												class="mdc-radio__native-control <?php echo ( isset( $sbpp_component['class'] ) ? esc_attr( $sbpp_component['class'] ) : '' ); ?>"
												<?php checked( $sbpp_radio_key, $sbpp_component['value'] ); ?>
												>
												<div class="mdc-radio__background">
													<div class="mdc-radio__outer-circle"></div>
													<div class="mdc-radio__inner-circle"></div>
												</div>
												<div class="mdc-radio__ripple"></div>
											</div>
											<label for="radio-1"><?php echo esc_html( $sbpp_radio_val ); ?></label>
										</div>	
										<?php
									}
									?>
								</div>
							</div>
						</div>
						<?php
						break;

						case 'radio-switch':
						?>

						<div class="mwb-form-group">
							<div class="mwb-form-group__label">
								<label for="" class="mwb-form-label"><?php echo ( isset( $sbpp_component['title'] ) ? esc_html( $sbpp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
							</div>
							<div class="mwb-form-group__control">
								<div>
									<div class="mdc-switch">
										<div class="mdc-switch__track"></div>
										<div class="mdc-switch__thumb-underlay">
											<div class="mdc-switch__thumb"></div>
											<input name="<?php echo ( isset( $sbpp_component['name'] ) ? esc_html( $sbpp_component['name'] ) : esc_html( $sbpp_component['id'] ) ); ?>" type="checkbox" id="<?php echo esc_html( $sbpp_component['id'] ); ?>" value="on" class="mdc-switch__native-control <?php echo ( isset( $sbpp_component['class'] ) ? esc_attr( $sbpp_component['class'] ) : '' ); ?>" role="switch" aria-checked="<?php if ( 'on' == $sbpp_component['value'] ) echo 'true'; else echo 'false'; ?>"
											<?php checked( $sbpp_component['value'], 'on' ); ?>
											>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
						break;

						case 'button':
						?>
						<div class="mwb-form-group">
							<div class="mwb-form-group__label"></div>
							<div class="mwb-form-group__control">
								<button class="mdc-button mdc-button--raised" name= "<?php echo ( isset( $sbpp_component['name'] ) ? esc_html( $sbpp_component['name'] ) : esc_html( $sbpp_component['id'] ) ); ?>"
									id="<?php echo esc_attr( $sbpp_component['id'] ); ?>"> <span class="mdc-button__ripple"></span>
									<span class="mdc-button__label <?php echo ( isset( $sbpp_component['class'] ) ? esc_attr( $sbpp_component['class'] ) : '' ); ?>"><?php echo ( isset( $sbpp_component['button_text'] ) ? esc_html( $sbpp_component['button_text'] ) : '' ); ?></span>
								</button>
							</div>
						</div>

						<?php
						break;

						case 'multi':
							?>
							<div class="mwb-form-group mwb-isfw-<?php echo esc_attr( $sbpp_component['type'] ); ?>">
								<div class="mwb-form-group__label">
									<label for="<?php echo esc_attr( $sbpp_component['id'] ); ?>" class="mwb-form-label"><?php echo ( isset( $sbpp_component['title'] ) ? esc_html( $sbpp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
									</div>
									<div class="mwb-form-group__control">
									<?php
									foreach ( $sbpp_component['value'] as $component ) {
										?>
											<label class="mdc-text-field mdc-text-field--outlined">
												<span class="mdc-notched-outline">
													<span class="mdc-notched-outline__leading"></span>
													<span class="mdc-notched-outline__notch">
														<?php if ( 'number' != $component['type'] ) { ?>
															<span class="mdc-floating-label" id="my-label-id" style=""><?php echo ( isset( $sbpp_component['placeholder'] ) ? esc_attr( $sbpp_component['placeholder'] ) : '' ); ?></span>
														<?php } ?>
													</span>
													<span class="mdc-notched-outline__trailing"></span>
												</span>
												<input 
												class="mdc-text-field__input <?php echo ( isset( $sbpp_component['class'] ) ? esc_attr( $sbpp_component['class'] ) : '' ); ?>" 
												name="<?php echo ( isset( $sbpp_component['name'] ) ? esc_html( $sbpp_component['name'] ) : esc_html( $sbpp_component['id'] ) ); ?>"
												id="<?php echo esc_attr( $component['id'] ); ?>"
												type="<?php echo esc_attr( $component['type'] ); ?>"
												value="<?php echo ( isset( $sbpp_component['value'] ) ? esc_attr( $sbpp_component['value'] ) : '' ); ?>"
												placeholder="<?php echo ( isset( $sbpp_component['placeholder'] ) ? esc_attr( $sbpp_component['placeholder'] ) : '' ); ?>"
												<?php echo esc_attr( ( 'number' === $component['type'] ) ? 'max=10 min=0' : '' ); ?>
												>
											</label>
								<?php } ?>
									<div class="mdc-text-field-helper-line">
										<div class="mdc-text-field-helper-text--persistent mwb-helper-text" id="" aria-hidden="true"><?php echo ( isset( $sbpp_component['description'] ) ? esc_attr( $sbpp_component['description'] ) : '' ); ?></div>
									</div>
								</div>
							</div>
								<?php
							break;
						case 'color':
						case 'date':
						case 'file':
							?>
							<div class="mwb-form-group mwb-isfw-<?php echo esc_attr( $sbpp_component['type'] ); ?>">
								<div class="mwb-form-group__label">
									<label for="<?php echo esc_attr( $sbpp_component['id'] ); ?>" class="mwb-form-label"><?php echo ( isset( $sbpp_component['title'] ) ? esc_html( $sbpp_component['title'] ) : '' ); // WPCS: XSS ok. ?></label>
								</div>
								<div class="mwb-form-group__control">
									<label class="mdc-text-field mdc-text-field--outlined">
										<input 
										class="<?php echo ( isset( $sbpp_component['class'] ) ? esc_attr( $sbpp_component['class'] ) : '' ); ?>" 
										name="<?php echo ( isset( $sbpp_component['name'] ) ? esc_html( $sbpp_component['name'] ) : esc_html( $sbpp_component['id'] ) ); ?>"
										id="<?php echo esc_attr( $sbpp_component['id'] ); ?>"
										type="<?php echo esc_attr( $sbpp_component['type'] ); ?>"
										value="<?php echo ( isset( $sbpp_component['value'] ) ? esc_attr( $sbpp_component['value'] ) : '' ); ?>"
										<?php echo esc_html( ( 'date' === $sbpp_component['type'] ) ? 'max='. date( 'Y-m-d', strtotime( date( "Y-m-d", mktime() ) . " + 365 day" ) ) .' ' . 'min=' . date( "Y-m-d" ) . '' : '' ); ?>
										>
									</label>
									<div class="mdc-text-field-helper-line">
										<div class="mdc-text-field-helper-text--persistent mwb-helper-text" id="" aria-hidden="true"><?php echo ( isset( $sbpp_component['description'] ) ? esc_attr( $sbpp_component['description'] ) : '' ); ?></div>
									</div>
								</div>
							</div>
							<?php
						break;

						case 'submit':
						?>
						<tr valign="top">
							<td scope="row">
								<input type="submit" class="button button-primary" 
								name="<?php echo ( isset( $sbpp_component['name'] ) ? esc_html( $sbpp_component['name'] ) : esc_html( $sbpp_component['id'] ) ); ?>"
								id="<?php echo esc_attr( $sbpp_component['id'] ); ?>"
								class="<?php echo ( isset( $sbpp_component['class'] ) ? esc_attr( $sbpp_component['class'] ) : '' ); ?>"
								value="<?php echo esc_attr( $sbpp_component['button_text'] ); ?>"
								/>
							</td>
						</tr>
						<?php
						break;

						default:
						break;
					}
				}
			}
		}
	}
}
