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
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Funnels Listing Template.
 *
 * This template is used for listing all existing funnels with
 * view/edit and delete option.
 */

$secure_nonce      = wp_create_nonce( 'wps-upsell-auth-nonce' );
$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-upsell-auth-nonce' );

if ( ! $id_nonce_verified ) {
	wp_die( esc_html__( 'Nonce Not verified', 'one-click-upsell-funnel-for-woocommerce-pro' ) );
}

// Sync funnels from ORG plugin.
if ( ! empty( $_GET['sync_funnel'] ) && 'true' === $_GET['sync_funnel'] ) {

	$sync_funnels = wps_upsell_sync_funnels();

	?>

	<!-- Sync Funnels notice -->

	<?php if ( $sync_funnels ) : ?>

		<div class="notice notice-success is-dismissible"> 
			<p><?php esc_html_e( 'Funnels Synced successfully.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>
		</div>

	<?php else : ?>

		<div class="notice notice-success is-dismissible"> 
			<p><?php esc_html_e( 'Sorry, there was some error. Funnels could not be synced. Please create them again or contact support.', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>
		</div>

	<?php endif; ?>

	<?php
}

// Delete funnel.
if ( isset( $_GET['del_funnel_id'] ) ) {

	$funnel_id = sanitize_text_field( wp_unslash( $_GET['del_funnel_id'] ) );

	// Get all funnels.
	$wps_wocuf_pro_funnels = get_option( 'wps_wocuf_pro_funnels_list' );

	foreach ( $wps_wocuf_pro_funnels as $single_funnel => $data ) {

		if ( (int) $funnel_id === (int) $single_funnel ) {

			delete_option( 'wps_wocuf_custom_form_values_' . $funnel_id );
			unset( $wps_wocuf_pro_funnels[ $single_funnel ] );
			break;
		}
	}

	update_option( 'wps_wocuf_pro_funnels_list', $wps_wocuf_pro_funnels );

	wp_redirect( admin_url( 'admin.php' ) . '?page=wps-wocuf-pro-setting&tab=funnels-list' ); //phpcs:ignore

	exit();
}


// Get all funnels.
$wps_wocuf_pro_funnels_list = get_option( 'wps_wocuf_pro_funnels_list' );

if ( ! empty( $wps_wocuf_pro_funnels_list ) ) {

	// Temp funnel variable.
	$wps_wocuf_pro_funnel_duplicate = $wps_wocuf_pro_funnels_list;

	// Make key pointer point to the end funnel.
	end( $wps_wocuf_pro_funnel_duplicate );

	// Now key function will return last funnel key.
	$wps_wocuf_pro_funnel_number = key( $wps_wocuf_pro_funnel_duplicate );
} else {
	// When no funnel is there then new funnel id will be 1 (0+1).
	$wps_wocuf_pro_funnel_number = 0;
}

?>

<div class="wps_wocuf_pro_funnels_list">

	<?php if ( empty( $wps_wocuf_pro_funnels_list ) ) : ?>

		<p class="wps_wocuf_pro_no_funnel"><?php esc_html_e( 'No funnels added', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>

	<?php endif; ?>

	<?php if ( ! empty( $wps_wocuf_pro_funnels_list ) ) : ?>
		<table>
			<tr>
				<th><?php esc_html_e( 'Funnel Name', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></th>
				<th><?php esc_html_e( 'Status', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></th>
				<th class="wps_upsell_funnel_list_target_th"><?php esc_html_e( 'Target Product(s) and Categories', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></th>
				<th class="wps_upsell_funnel_list_target_th"><?php esc_html_e( 'Offers', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></th>
				<th class="wps_upsell_funnel_list_target_th"><?php esc_html_e( 'Additional Offers', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></th>
				<th><?php esc_html_e( 'Action', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></th>
				<?php do_action( 'wps_wocuf_pro_funnel_add_more_col_head' ); ?>
			</tr>

			<!-- Foreach Funnel start -->
			<?php
			foreach ( $wps_wocuf_pro_funnels_list as $key => $value ) :

				$offers_count = ! empty( $value['wps_wocuf_pro_products_in_offer'] ) ? $value['wps_wocuf_pro_products_in_offer'] : array();

				$offers_count = count( $offers_count );

				?>

				<tr>		
					<!-- Funnel Name -->
					<td><a class="wps_upsell_funnel_list_name" href="?page=wps-wocuf-pro-setting&tab=creation-setting&funnel_id=<?php echo esc_html( $key ); ?>"><?php echo ! empty( $value['wps_wocuf_pro_funnel_name'] ) ? esc_html( $value['wps_wocuf_pro_funnel_name'] ) : esc_html( 'Funnel #' . $key ); ?></a></td>

					<!-- Funnel Status -->
					<td>

						<?php

						$funnel_status       = ! empty( $value['wps_upsell_funnel_status'] ) ? $value['wps_upsell_funnel_status'] : 'no';
						$global_funnel       = ! empty( $value['wps_wocuf_global_funnel'] ) ? $value['wps_wocuf_global_funnel'] : 'no';
						$exclusive_offer     = ! empty( $value['wps_wocuf_exclusive_offer'] ) ? $value['wps_wocuf_exclusive_offer'] : 'no';
						$smart_offer_upgrade = ! empty( $value['wps_wocuf_smart_offer_upgrade'] ) ? $value['wps_wocuf_smart_offer_upgrade'] : 'no';

						// Pre v3.0.0 Funnels will be live.
						$funnel_status = ! empty( $value['wps_upsell_fsav3'] ) ? $funnel_status : 'yes';

						if ( 'yes' === $funnel_status ) {

							echo '<span class="wps_upsell_funnel_list_live"></span><span class="wps_upsell_funnel_list_live_name">' . esc_html__( 'Live', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</span>';
						} else {

							echo '<span class="wps_upsell_funnel_list_sandbox"></span><span class="wps_upsell_funnel_list_sandbox_name">' . esc_html__( 'Sandbox', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</span>';
						}
						?>
						<div class='wps-upsell-funnel-attributes <?php echo esc_html( $funnel_status ); ?>'>
						<?php

						if ( 'yes' === $global_funnel ) {

							echo '<p>' . esc_html__( 'Global Funnel', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</p>';
						}

						if ( 'yes' === $exclusive_offer ) {

							echo '<p>' . esc_html__( 'Exclusive Offer', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</p>';
						}

						if ( 'yes' === $smart_offer_upgrade ) {

							echo '<p>' . esc_html__( 'Smart Offer Upgrade', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</p>';
						}

						?>
						</div>

					</td>

					<!-- Funnel Target Product(s) and Categories. -->
					<td>
						<?php

						// Target Product(s).

						if ( ! empty( $value['wps_wocuf_pro_target_pro_ids'] ) ) {

							echo '<p><i>' . esc_html__( 'Target Product(s) -', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</i></p>';

							echo '<div class="wps_upsell_funnel_list_targets">';

							foreach ( $value['wps_wocuf_pro_target_pro_ids'] as $single_target_product ) :

								$product = wc_get_product( $single_target_product );

								if ( empty( $product ) ) {

									continue;
								}
								?>
								<p><?php echo esc_html( $product->get_title() . "( #$single_target_product )" ); ?></p>
								<?php

							endforeach;

							echo '</div>';
						} else {

							?>

							<p><i><?php esc_html_e( 'No Product(s) added', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></i></p>

							<?php
						}

						echo '<hr>';

						// Target Categories.

						if ( ! empty( $value['target_categories_ids'] ) ) {

							echo '<p><i>' . esc_html__( 'Target Categories -', 'one-click-upsell-funnel-for-woocommerce-pro' ) . '</i></p>';

							echo '<div class="wps_upsell_funnel_list_targets">';

							foreach ( $value['target_categories_ids'] as $single_target_category_id ) :

								$category_name = get_the_category_by_ID( $single_target_category_id );

								if ( empty( $category_name ) ) {

									continue;
								}
								?>
								<p><?php echo esc_html( $category_name . "( #$single_target_category_id )" ); ?></p>
								<?php

							endforeach;

							echo '</div>';
						} else {

							?>

							<p><i><?php esc_html_e( 'No Categories added', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></i></p>

							<?php
						}


						?>
					</td>

					<!-- Offers -->
					<td>
						<?php

						if ( ! empty( $value['wps_wocuf_pro_products_in_offer'] ) ) {

							echo '<div class="wps_upsell_funnel_list_targets">';

							echo '<p><i>' . esc_html__( 'Offers Count', 'one-click-upsell-funnel-for-woocommerce-pro' ) . ' - ' . esc_html( $offers_count ) . '</i></p>';

							foreach ( $value['wps_wocuf_pro_products_in_offer'] as $offer_key => $single_offer_product ) :

								$product = wc_get_product( $single_offer_product );

								if ( empty( $product ) ) {

									continue;
								}
								?>
								<p><?php echo '<strong>' . esc_html__( 'Offer', 'one-click-upsell-funnel-for-woocommerce-pro' ) . ' #' . esc_html( $offer_key ) . '</strong> &rarr; ' . esc_html( $product->get_title() ) . '( #' . esc_html( $single_offer_product ) . ' )'; ?></p>
								<?php

							endforeach;

							echo '</div>';
						} else {

							?>

								<p><i><?php esc_html_e( 'No Offers added', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></i></p>

							<?php
						}

						?>

					</td> 

						<!-- additional Offers -->
						<td>
						<?php

						$additional_offers = ! empty( $value['wps_wocuf_pro_add_products_in_offer'] ) ? $value['wps_wocuf_pro_add_products_in_offer'] : array();

						if ( ! empty( $additional_offers ) ) {

							array_unshift( $additional_offers, 'extras' );
							unset( $additional_offers[0] );

							$offers_count = count( $additional_offers );

							echo '<div class="wps_upsell_funnel_list_targets">';

							echo '<p><i>' . esc_html__( 'Offers Count', 'one-click-upsell-funnel-for-woocommerce-pro' ) . ' - ' . esc_html( $offers_count ) . '</i></p>';

							foreach ( $additional_offers as $offer_key => $single_offer_product ) :

								$product = wc_get_product( $single_offer_product );

								if ( empty( $product ) ) {

									continue;
								}
								?>
								<p><?php echo '<strong>' . esc_html__( 'Offer', 'one-click-upsell-funnel-for-woocommerce-pro' ) . ' #' . esc_html( $offer_key ) . '</strong> &rarr; ' . esc_html( $product->get_title() ) . '( #' . esc_html( $single_offer_product ) . ' )'; ?></p>
								<?php

							endforeach;

							echo '</div>';
						} else {

							?>

								<p><i><?php esc_html_e( 'No Offers added', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></i></p>

							<?php
						}

						?>

					</td> 

					<!-- Funnel Action -->
					<td>

						<!-- Funnel View/Edit link -->
						<a class="wps_wocuf_pro_funnel_links" href="?page=wps-wocuf-pro-setting&tab=creation-setting&funnel_id=<?php echo esc_html( $key ); ?>"><?php esc_html_e( 'View / Edit', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>

						<!-- Funnel Delete link -->
						<a class="wps_wocuf_pro_funnel_links" href="?page=wps-wocuf-pro-setting&tab=funnels-list&del_funnel_id=<?php echo esc_html( $key ); ?>"><?php esc_html_e( 'Delete', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
					</td>

					<?php do_action( 'wps_wocuf_pro_funnel_add_more_col_data' ); ?>
				</tr>
			<?php endforeach; ?>
			<!-- Foreach Funnel end -->
		</table>
	<?php endif; ?>
</div>

<br>

<!-- Create New Funnel -->
<div class="wps_wocuf_pro_create_new_funnel">
	<a href="?page=wps-wocuf-pro-setting&tab=creation-setting&funnel_id=<?php echo esc_html( $wps_wocuf_pro_funnel_number + 1 ); ?>"><?php esc_html_e( '+Create New Funnel', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
</div>

<?php if ( empty( get_option( 'wocuf_pro_migration_status', false ) ) ) { ?>

<!-- Create New Migration -->
<div class="wps_wocuf_pro_create_new_funnel">
	<p class="wps_wocuf_pro_desc"><?php esc_html_e( 'Not getting saved funnels and settings from previous version?', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></p>
	<a id="wps_wocuf_pro_migration_button" class="wps_wocuf_pro_init_migration" href="javascript:void(0)"><?php esc_html_e( 'Try Migration', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>
</div>

<?php } ?>

<!-- Sync Funnels from ORG upsell plugin -->
<div id="wps_upsell_sync_funnels">

	<?php

	$org_upsell_funnels = get_option( 'wps_wocuf_funnels_list' );
	$pro_upsell_funnels = get_option( 'wps_wocuf_pro_funnels_list' );

	if ( ! empty( $org_upsell_funnels ) && empty( $pro_upsell_funnels ) ) {

		?>

		<hr>

		<a href="?page=wps-wocuf-pro-setting&tab=funnels-list&sync_funnel=true"><?php echo '&#8634; ' . esc_html__( 'Sync Funnels from ORG plugin', 'one-click-upsell-funnel-for-woocommerce-pro' ); ?></a>

		<?php
	}

	?>

</div>

<?php do_action( 'wps_wocuf_pro_extend_funnels_listing' ); ?>
