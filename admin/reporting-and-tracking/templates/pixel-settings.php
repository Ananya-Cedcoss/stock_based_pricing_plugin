<?php
/**
 * Provide a admin area view for the plugin
 *
 * Facebook Pixel Settings.
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      3.5.0
 *
 * @package     woocommerce-one-click-upsell-funnel-pro
 * @subpackage  woocommerce-one-click-upsell-funnel-pro/admin/reporting-and-tracking/templates/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Save settings on Save changes.
if ( isset( $_POST['wps_wocuf_pro_common_settings_save'] ) ) {

	// Nonce verification.
	$wps_wocuf_pro_create_nonce = ! empty( $_POST['wps_wocuf_pro_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_wocuf_pro_nonce'] ) ) : '';

	if ( empty( $wps_wocuf_pro_create_nonce ) || ! wp_verify_nonce( $wps_wocuf_pro_create_nonce, 'wps_wocuf_pro_setting_nonce' ) ) : ?>

		<div class="notice notice-error is-dismissible wps-noticee">
			<p><?php esc_html_e( 'Sorry, due to some security issue, your settings could not be saved. Please reload the page.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>
		</div>
		<?php
		return false;

	endif;

	$wps_upsell_analytics_options = get_option( 'wps_upsell_analytics_configuration', array() );

	$wps_upsell_fb_pixel_config = ! empty( $wps_upsell_analytics_options['facebook-pixel'] ) ? $wps_upsell_analytics_options['facebook-pixel'] : array();

	$wps_upsell_ga_analytics_config = ! empty( $wps_upsell_analytics_options['google-analytics'] ) ? $wps_upsell_analytics_options['google-analytics'] : array();


	// Handle Data is POST here.
	$wps_upsell_fb_pixel_config = array(
		'pixel_account_id'      => ! empty( $_POST['pixel_account_id'] ) ? sanitize_text_field( wp_unslash( $_POST['pixel_account_id'] ) ) : '',
		'enable_pixel_basecode' => ! empty( $_POST['enable_pixel_basecode'] ) ? 'yes' : 'no',
		'enable_purchase_event' => ! empty( $_POST['enable_purchase_event'] ) ? 'yes' : 'no',
	);

	if ( ! empty( $wps_upsell_fb_pixel_config ) || ! empty( $wps_upsell_ga_analytics_config ) ) {

		$wps_upsell_analytics_options = array(
			'facebook-pixel'   => $wps_upsell_fb_pixel_config,
			'google-analytics' => $wps_upsell_ga_analytics_config,
		);

		// Save.
		update_option( 'wps_upsell_analytics_configuration', $wps_upsell_analytics_options );
	}

	?>

	<!-- Settings saved notice. -->
	<div class="notice notice-success is-dismissible"> 
		<p><strong><?php esc_html_e( 'Settings saved', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></strong></p>
	</div>
	<?php
}

$wps_upsell_analytics_options = get_option( 'wps_upsell_analytics_configuration', array() );

$wps_upsell_fb_pixel_config = ! empty( $wps_upsell_analytics_options['facebook-pixel'] ) ? $wps_upsell_analytics_options['facebook-pixel'] : array();

$wps_upsell_ga_analytics_config = ! empty( $wps_upsell_analytics_options['google-analytics'] ) ? $wps_upsell_analytics_options['google-analytics'] : array();

// Form Fields Mapping.
$google_analytics_fields = array(

	'wps_wocuf_pixel_account_id'      => array(
		'name'                  => 'pixel_account_id',
		'label'                 => 'Fb Pixel ID',
		'type'                  => 'text',
		'required'              => true,
		'attribute_description' => esc_html__( 'Log into your Facebook Pixel account to find your ID. eg: 580XXXXXXXXX325.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
		'note'                  => esc_html__( 'You can fetch Pixel ID from', 'one-click-upsell-funnel-for-woocommerce-pro' ),
		'note_html'             => 'https://www.facebook.com/ads/manager/pixel/facebook_pixel',
		'value'                 => ! empty( $wps_upsell_fb_pixel_config['pixel_account_id'] ) ? sanitize_text_field( wp_unslash( $wps_upsell_fb_pixel_config['pixel_account_id'] ) ) : '',
	),

	'wps_wocuf_enable_pixel_basecode' => array(
		'name'                  => 'enable_pixel_basecode',
		'label'                 => 'Enable Pixel Base code',
		'type'                  => 'checkbox',
		'required'              => false,
		'attribute_description' => esc_html__( 'Add Facebook Pixel Base Code to your website', 'one-click-upsell-funnel-for-woocommerce-pro' ),
		'note'                  => esc_html__( 'Only Enable this when you are not using any other Facebook Pixel tracking on your website.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
		'value'                 => ! empty( $wps_upsell_fb_pixel_config['enable_pixel_basecode'] ) ? sanitize_text_field( wp_unslash( $wps_upsell_fb_pixel_config['enable_pixel_basecode'] ) ) : 'no',
	),

	'wps_wocuf_enable_purchase_event' => array(
		'name'                  => 'enable_purchase_event',
		'label'                 => 'Enable Purchase Event',
		'type'                  => 'checkbox',
		'required'              => false,
		'attribute_description' => esc_html__( 'This will trigger Facebook Pixel Purchase Event for Parent Order and for Upsells accordingly with respect to payment gateways.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
		'note'                  => esc_html__( 'Make sure you disable your Purchase event if you are using any other Facebook Pixel tracking on your website else it will track data twice.', 'one-click-upsell-funnel-for-woocommerce-pro' ),
		'value'                 => ! empty( $wps_upsell_fb_pixel_config['enable_purchase_event'] ) ? sanitize_text_field( wp_unslash( $wps_upsell_fb_pixel_config['enable_purchase_event'] ) ) : 'no',
	),
);

?>

<!-- Other Tracking Plugins Compatibilities - Start -->
<div class="wps_upsell_slide_down_title">
	<h2><?php esc_html_e( 'Other Tracking Plugins Compatibilities', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></h2>
	<a href="#" class="wps_upsell_slide_down_link"><img src="<?php echo esc_url( WPS_WOCUF_PRO_URL . 'admin/resources/down.png' ); ?>"></a>
</div>

<div class="wps_upsell_table wps_upsell_slide_down_content">
	<table class="form-table wps_wocuf_pro_creation_setting wps_upsell_slide_down_table">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label><a target="_blank" href="https://wordpress.org/plugins/facebook-for-woocommerce/"><?php esc_html_e( 'Facebook for WooCommerce', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a></label>
					<span class="wps_upsell_other_plugin_author_name"><?php esc_html_e( 'By Facebook', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
				</th>
				<td class="forminp forminp-text">
					<span class="wps_upsell_global_description">
						<?php esc_html_e( 'We have added inbuilt Compatibility with Facebook for WooCommerce plugin so it\'s Facebook Pixel Purchase Event will be automatically disabled as soon as you Enable Facebook Pixel Purchase Event by Upsell.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
					</span>		
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label><a target="_blank" href="https://wordpress.org/plugins/pixelyoursite/"><?php esc_html_e( 'PixelYourSite', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a> 
					</label>
					<label><a target="_blank" href="https://www.pixelyoursite.com/"><?php esc_html_e( 'PixelYourSite PRO', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a></label>
					<span class="wps_upsell_other_plugin_author_name light"><?php esc_html_e( 'By PixelYourSite', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
				</th>
				<td class="forminp forminp-text">
					<span class="wps_upsell_global_description">
						<?php
						printf(
							'%s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s',
							esc_html__( 'Please Go to', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'WordPress Admin Dashboard', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'PixelYourSite', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'WooCommerce tab', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Scroll down to', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Default E-Commerce events', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'and click on', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Track Purchases', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'settings icon and Toggle Off the', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Enable the Purchase event on Facebook (required for DPA)', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'and', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Save', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '.', 'one-click-upsell-funnel-for-woocommerce-pro' )
						);
						?>
					</span>		
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label><a target="_blank" href="https://wordpress.org/plugins/pixel-caffeine/"><?php esc_html_e( 'Pixel Caffeine', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a></label>
					<span class="wps_upsell_other_plugin_author_name light"><?php esc_html_e( 'By AdEspresso', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
				</th>
				<td class="forminp forminp-text">
					<span class="wps_upsell_global_description">
						<?php
						printf(
							'%s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b>%s',
							esc_html__( 'Please Go to', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'WordPress Admin Dashboard', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Pixel Caffeine', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'General Settings tab', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Track this eCommerce Conversions', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'and Uncheck the', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Purchase', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Event and', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Save', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '.', 'one-click-upsell-funnel-for-woocommerce-pro' )
						);
						?>
					</span>		
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label><a target="_blank" href="https://wordpress.org/plugins/woocommerce-conversion-tracking/"><?php esc_html_e( 'WooCommerce Conversion Tracking', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a></label>
					<span class="wps_upsell_other_plugin_author_name light"><?php esc_html_e( 'By weDevs', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
				</th>
				<td class="forminp forminp-text">
					<span class="wps_upsell_global_description">
						<?php
						printf(
							'%s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b>%s',
							esc_html__( 'Please Go to', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'WordPress Admin Dashboard', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'WooCommerce', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Conversion Tracking', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Facebook', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'and Uncheck the', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Purchase', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Event and', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Save', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '.', 'one-click-upsell-funnel-for-woocommerce-pro' )
						);
						?>
					</span>		
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label><a target="_blank" href="https://wordpress.org/plugins/wp-facebook-pixel/"><?php esc_html_e( 'remarketable ( formerly - WP Facebook Pixel Plugin )', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a></label>
					<span class="wps_upsell_other_plugin_author_name light"><?php esc_html_e( 'By Night Shift Apps', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
				</th>
				<td class="forminp forminp-text">
					<span class="wps_upsell_global_description">
						<?php
						printf(
							'%s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b> %s <b>%s</b>%s',
							esc_html__( 'Please Go to', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'WordPress Admin Dashboard', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Settings', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'remarketable / WP Facebook Pixel', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Woocommerce Options tab', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '>', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Order Received Events', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'and set the', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Purchase', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Event option to', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'No', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'and', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( 'Save', 'one-click-upsell-funnel-for-woocommerce-pro' ),
							esc_html__( '.', 'one-click-upsell-funnel-for-woocommerce-pro' )
						);
						?>
					</span>		
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label><?php esc_html_e( 'Other Facebook Pixel Tracking Plugins', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<span class="wps_upsell_global_description">
						<?php esc_html_e( 'Please make sure to Disable the Purchase Event from your plugin\'s settings before you Enable Facebook Pixel Purchase Event by Upsell. If you can\'t find the settings or in case of any confusion please contact our support', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?> <a target="_blank" href="https://wpswings.com/contact-us/?utm_source=wpswings-official&utm_medium=upsell-org-page&utm_campaign=official"><?php esc_html_e( 'here', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a><?php esc_html_e( '.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>
					</span>		
				</td>
			</tr>
		</tbody>
	</table>
</div>
<!-- Other Tracking Plugins Compatibilities - End -->

<form action="" method="POST">
	<div class="wps_upsell_table">
		<table class="form-table wps_wocuf_pro_creation_setting">
			<tbody>

				<!-- Nonce field here. -->
				<?php wp_nonce_field( 'wps_wocuf_pro_setting_nonce', 'wps_wocuf_pro_nonce' ); ?>

				<?php if ( ! empty( $google_analytics_fields ) && is_array( $google_analytics_fields ) ) : ?>
					<?php foreach ( $google_analytics_fields as $field_id => $field_data ) : ?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_html( $field_id ); ?>"><?php echo esc_html( $field_data['label'] ); ?></label>
							</th>

							<td class="forminp forminp-text">
								<?php wps_wc_help_tip( $field_data['attribute_description'] ); ?>

								<?php if ( 'text' === $field_data['type'] ) : ?>

									<input <?php echo( ! empty( $field_data['required'] ) ? esc_html( 'required' ) : '' ); ?> class="wps_wocuf_pro_enable_plugin_input" type="text"  name="<?php echo esc_html( $field_data['name'] ); ?>" value="<?php echo esc_html( $field_data['value'] ); ?>" id="<?php echo esc_html( $field_id ); ?>">

								<?php else : ?>

									<label class="wps_wocuf_pro_enable_plugin_label">
										<input <?php echo( ! empty( $field_data['required'] ) ? esc_html( 'required' ) : '' ); ?> class="wps_wocuf_pro_enable_plugin_input" type="checkbox" name="<?php echo esc_html( $field_data['name'] ); ?>" id="<?php echo esc_html( $field_id ); ?>" <?php checked( 'yes', $field_data['value'] ); ?>>
										<span class="wps_wocuf_pro_enable_plugin_span"></span>
									</label>

								<?php endif; ?>

								<span class="wps_upsell_global_description">
								<?php echo ! empty( $field_data['note'] ) ? esc_html( $field_data['note'] ) : ''; ?>
								<?php
								if ( ! empty( $field_data['note_html'] ) ) :
									?>
									<a href="<?php echo esc_url( $field_data['note_html'] ); ?>" target="_blank"><?php esc_html_e( 'here', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
								<?php endif; ?>
								</span>
							</td>
						</tr>

					<?php endforeach; ?>
				<?php endif; ?>	
				<?php do_action( 'wps_wocuf_pro_create_more_settings' ); ?>
			</tbody>
		</table>
	</div>

	<p class="submit">
	<input type="submit" value="<?php esc_html_e( 'Save Changes', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?>" class="button-primary woocommerce-save-button" name="wps_wocuf_pro_common_settings_save" id="wps_wocuf_pro_creation_setting_save" >
	</p>
</form>
