<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the html for system status.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Stock_based_pricing_plugin
 * @subpackage Stock_based_pricing_plugin/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Template for showing information about system status.
global $sbpp_mwb_sbpp_obj;
$sbpp_default_status = $sbpp_mwb_sbpp_obj->mwb_sbpp_plug_system_status();
$sbpp_wordpress_details = is_array( $sbpp_default_status['wp'] ) && ! empty( $sbpp_default_status['wp'] ) ? $sbpp_default_status['wp'] : array();
$sbpp_php_details = is_array( $sbpp_default_status['php'] ) && ! empty( $sbpp_default_status['php'] ) ? $sbpp_default_status['php'] : array();
?>
<div class="mwb-sbpp-table-wrap">
	<div class="mwb-col-wrap">
		<div id="mwb-sbpp-table-inner-container" class="table-responsive mdc-data-table">
			<div class="mdc-data-table__table-container">
				<table class="mwb-sbpp-table mdc-data-table__table mwb-table" id="mwb-sbpp-wp">
					<thead>
						<tr>
							<th class="mdc-data-table__header-cell"><?php esc_html_e( 'WP Variables', 'stock-based-pricing-plugin' ); ?></th>
							<th class="mdc-data-table__header-cell"><?php esc_html_e( 'WP Values', 'stock-based-pricing-plugin' ); ?></th>
						</tr>
					</thead>
					<tbody class="mdc-data-table__content">
						<?php if ( is_array( $sbpp_wordpress_details ) && ! empty( $sbpp_wordpress_details ) ) { ?>
							<?php foreach ( $sbpp_wordpress_details as $wp_key => $wp_value ) { ?>
								<?php if ( isset( $wp_key ) && 'wp_users' != $wp_key ) { ?>
									<tr class="mdc-data-table__row">
										<td class="mdc-data-table__cell"><?php echo esc_html( $wp_key ); ?></td>
										<td class="mdc-data-table__cell"><?php echo esc_html( $wp_value ); ?></td>
									</tr>
								<?php } ?>
							<?php } ?>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="mwb-col-wrap">
		<div id="mwb-sbpp-table-inner-container" class="table-responsive mdc-data-table">
			<div class="mdc-data-table__table-container">
				<table class="mwb-sbpp-table mdc-data-table__table mwb-table" id="mwb-sbpp-sys">
					<thead>
						<tr>
							<th class="mdc-data-table__header-cell"><?php esc_html_e( 'Sysytem Variables', 'stock-based-pricing-plugin' ); ?></th>
							<th class="mdc-data-table__header-cell"><?php esc_html_e( 'System Values', 'stock-based-pricing-plugin' ); ?></th>
						</tr>
					</thead>
					<tbody class="mdc-data-table__content">
						<?php if ( is_array( $sbpp_php_details ) && ! empty( $sbpp_php_details ) ) { ?>
							<?php foreach ( $sbpp_php_details as $php_key => $php_value ) { ?>
								<tr class="mdc-data-table__row">
									<td class="mdc-data-table__cell"><?php echo esc_html( $php_key ); ?></td>
									<td class="mdc-data-table__cell"><?php echo esc_html( $php_value ); ?></td>
								</tr>
							<?php } ?>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
