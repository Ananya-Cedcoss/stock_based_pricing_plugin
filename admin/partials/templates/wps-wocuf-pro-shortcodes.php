<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link        https://wpswings.com/?utm_source=wpswings-official&utm_medium=upsell-pro-backend&utm_campaign=official
 * @since      1.0.0
 *
 * @package    woocommerce-one-click-upsell-funnel-pro
 * @subpackage woocommerce-one-click-upsell-funnel-pro/admin/partials/templates
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wps_upsell_table wps_upsell_new_shortcodes">
	<table class="form-table wps_wocuf_pro_shortcodes">
			<tbody>
				<!-- Upsell Action shortcodes start-->
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label><?php esc_html_e( 'Upsell Action shortcodes', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<div class="wps_upsell_shortcode_div">
							<p class="wps_upsell_shortcode">
								<?php
								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s</p><p class="wps_upsell_tip_tip">%s</p>', esc_html__( 'Accept Offer.', 'one-click-upsell-funnel-for-woocommerce-pro' ), esc_html__( 'This shortcode only returns the link so it has to be used in the link section. In html use it as href="[wps_upsell_yes]" of anchor tag.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );
								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'Buy Now &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo '[wps_upsell_yes]'; ?></span>
							</p>
						</div>
						<div class="wps_upsell_shortcode_div" >
							<p class="wps_upsell_shortcode">
								<?php
								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s</p><p class="wps_upsell_tip_tip">%s</p>', esc_html__( 'Reject Offer.', 'one-click-upsell-funnel-for-woocommerce-pro' ), esc_html__( 'This shortcode only returns the link so it has to be used in the link section. In html use it as href="[wps_upsell_no]" of anchor tag.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );
								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'No Thanks &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo '[wps_upsell_no]'; ?></span>
							</p>
						</div>		
					</td>
				</tr>
				<!-- Upsell Action shortcodes end-->

				<!-- Product shortcodes start-->
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label><?php esc_html_e( 'Product shortcodes', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<div class="wps_upsell_shortcode_div">
							<p class="wps_upsell_shortcode">
								<?php
								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s</p>', esc_html__( 'This shortcode returns the product title.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );
								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'Product Title &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo '[wps_upsell_title]'; ?></span>
							</p>
						</div>
						<div class="wps_upsell_shortcode_div" >
							<p class="wps_upsell_shortcode">
								<?php
								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s</p>', esc_html__( 'This shortcode returns the product description.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );
								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'Product Description &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo '[wps_upsell_desc]'; ?></span>
							</p>
						</div>	
						<div class="wps_upsell_shortcode_div" >
							<p class="wps_upsell_shortcode">
								<?php
								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s</p>', esc_html__( 'This shortcode returns the product short description.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );
								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'Product Short Description &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo '[wps_upsell_desc_short]'; ?></span>
							</p>
						</div>	
						<hr class="wps_upsell_shortcodes_hr">
						<div class="wps_upsell_shortcode_div" >
							<p class="wps_upsell_shortcode">
								<?php
								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s</p>', esc_html__( 'This shortcode returns the product image.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );
								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'Product Image &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo '[wps_upsell_image]'; ?></span>
							</p>
						</div>
						<div class="wps_upsell_shortcode_div" >
							<p class="wps_upsell_shortcode">
								<?php
								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s</p>', esc_html__( 'This shortcode returns the product price.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );
								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'Product Price &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo '[wps_upsell_price]'; ?></span>
							</p>
						</div>
						<div class="wps_upsell_shortcode_div" >
							<p class="wps_upsell_shortcode">
								<?php
								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s</p>', esc_html__( 'This shortcode returns the product variations if offer product is a variable product.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );
								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'Product Variations &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo '[wps_upsell_variations]'; ?></span>
							</p>
						</div>
					</td>
				</tr>
				<!-- Product shortcodes start-->

				<!-- Other shortcodes start-->
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label><?php esc_html_e( 'Other shortcodes', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<div class="wps_upsell_shortcode_div">
							<p class="wps_upsell_shortcode">
								<?php
								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s</p>', esc_html__( 'This shortcode returns Star ratings. You can the specify the number of stars like [wps_upsell_star_review stars=4.5] .', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );
								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'Star Ratings &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo esc_html__( '[wps_upsell_star_review]' ); ?></span>
							</p>
						</div>
						<div class="wps_upsell_shortcode_div">
							<p class="wps_upsell_shortcode">
								<?php

								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s <br>%s</p>', esc_html__( 'This shortcode returns quantity field. You can restrict the customer to select the quantity offered. [wps_upsell_quantity max=4 min=1 ] .', 'one-click-upsell-funnel-for-woocommerce-pro' ), esc_html__( '<b>Default Quantity</b><br> Min : 1<br> Max : 3.', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );

								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'Offer Quantity &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo esc_html__( '[wps_upsell_quantity]' ); ?></span>
							</p>
						</div>
						<div class="wps_upsell_shortcode_div">
							<p class="wps_upsell_shortcode">
								<?php

								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s</p>', esc_html__( 'This shortcode returns urgency timer. You can specify the timer limit as [wps_upsell_timer minutes=5] .', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );

								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'Urgency Timer &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo esc_html__( '[wps_upsell_timer]' ); ?></span>
							</p>
						</div>
						<div class="wps_upsell_shortcode_div">
							<p class="wps_upsell_shortcode">
								<?php

								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s</p>', esc_html__( 'This shortcode creates a custom form on upsell offer page .', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );

								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'Custom Form &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo esc_html__( '[wps_form]' ); ?></span>
							</p>
						</div>
						<div class="wps_upsell_shortcode_div">
							<p class="wps_upsell_shortcode">
								<?php

								$attribute_description = sprintf( '<p class="wps_upsell_tip_tip">%s</p>', esc_html__( 'This shortcode Displays Additional offer products on upsell offer page .', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
								wps_wc_help_tip( $attribute_description );

								?>
								<span class="wps_upsell_shortcode_title"><?php esc_html_e( 'Additional Offers &rarr;', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></span>
								<span class="wps_upsell_shortcode_content"><?php echo esc_html__( '[wps_additional_offers]' ); ?></span>
							</p>
						</div>
					</td>
				</tr>
				<!-- Other shortcodes end-->	
			</tbody>
		</table>
</div>
