<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the html field for general tab.
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
global $sbpp_mwb_sbpp_obj;
$sbpp_genaral_settings = apply_filters( 'sbpp_general_settings_array', array() );
?>
<!--  template file for admin settings. -->
<form action="" method="POST" class="mwb-sbpp-gen-section-form">
	<div class="sbpp-secion-wrap">
		<?php
		$sbpp_general_html = $sbpp_mwb_sbpp_obj->mwb_sbpp_plug_generate_html( $sbpp_genaral_settings );
		echo esc_html( $sbpp_general_html );
		?>
	</div>
</form>