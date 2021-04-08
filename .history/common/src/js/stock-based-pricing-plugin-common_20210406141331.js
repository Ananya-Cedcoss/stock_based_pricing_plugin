(function( $ ) {
	'use strict';

	/**
	 * All of the code for your common JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	 $(document).ready(function() {
		// it is used to get the price with the help of ajax method
		 $('input.variation_id').change( function(){
			 debugger;
			 if( '' != $('input.variation_id').val() ) {
	
			 var Variation_Id = $('input.variation_id').val();
			 var Old_Price=jQuery('.woocommerce-variation-price').html();
	
			 $('.woocommerce-variation-price').text('');	
	
			 var data = {
				 'action': 'action_to_get_variation_price',
					 'Variation_Id': Variation_Id,
					 'nonce': sbpp_common_param.nonce,
	
				 };
				 $.post(sbpp_common_param.ajaxurl, data, function(response) {
				 // alert('Got this from the server: ' + response);		
	
			if(response!="" && response!=undefined){
				
			var innerhtml='<span class="price"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">'+$($('.woocommerce-Price-currencySymbol')[0]).text()+'</span>'+response+'</bdi></span></span>';
					$('.woocommerce-variation-price').append(innerhtml);
				} else {
	
					$('.woocommerce-variation-price').append(Old_Price);
				}
	
			 });
	
			}
		 });
	
	});
	
	})( jQuery );
	