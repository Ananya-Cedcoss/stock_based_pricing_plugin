<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    Makewebbetter_Onboarding
 * @subpackage Makewebbetter_Onboarding/admin/onboarding
 */

global $pagenow, $sbpp_mwb_sbpp_obj;
if ( empty( $pagenow ) || 'plugins.php' != $pagenow ) {
	return false;
}

$sbpp_onboarding_form_deactivate = apply_filters( 'mwb_sbpp_deactivation_form_fields', array() );
?>
<?php if ( ! empty( $sbpp_onboarding_form_deactivate ) ) : ?>
	<div class="mdc-dialog mdc-dialog--scrollable">
		<div class="mwb-sbpp-on-boarding-wrapper-background mdc-dialog__container">
			<div class="mwb-sbpp-on-boarding-wrapper mdc-dialog__surface" role="alertdialog" aria-modal="true" aria-labelledby="my-dialog-title" aria-describedby="my-dialog-content">
				<div class="mdc-dialog__content">
					<div class="mwb-sbpp-on-boarding-close-btn">
						<a href="#">
							<span class="sbpp-close-form material-icons mwb-sbpp-close-icon mdc-dialog__button" data-mdc-dialog-action="close">clear</span>
						</a>
					</div>

					<h3 class="mwb-sbpp-on-boarding-heading mdc-dialog__title"></h3>
					<p class="mwb-sbpp-on-boarding-desc"><?php esc_html_e( 'May we have a little info about why you are deactivating?', 'stock-based-pricing-plugin' ); ?></p>
					<form action="#" method="post" class="mwb-sbpp-on-boarding-form">
						<?php 
						$sbpp_onboarding_deactive_html = $sbpp_mwb_sbpp_obj->mwb_sbpp_plug_generate_html( $sbpp_onboarding_form_deactivate );
						echo esc_html( $sbpp_onboarding_deactive_html );
						?>
						<div class="mwb-sbpp-on-boarding-form-btn__wrapper mdc-dialog__actions">
							<div class="mwb-sbpp-on-boarding-form-submit mwb-sbpp-on-boarding-form-verify ">
								<input type="submit" class="mwb-sbpp-on-boarding-submit mwb-on-boarding-verify mdc-button mdc-button--raised" value="Send Us">
							</div>
							<div class="mwb-sbpp-on-boarding-form-no_thanks">
								<a href="#" class="mwb-deactivation-no_thanks mdc-button"><?php esc_html_e( 'Skip and Deactivate Now', 'stock-based-pricing-plugin' ); ?></a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="mdc-dialog__scrim"></div>
	</div>
<?php endif; ?>
