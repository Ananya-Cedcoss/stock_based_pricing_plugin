
(function( $ ) {
	'use strict';

	 $(document).ready(function() {
		// it is used to get the price with the help of ajax method
		 $('input.variation_id').change( function(){
			 if ( '' != $('input.variation_id').val() ) {	
			 	var Variation_Id = $('input.variation_id').val();
			 	var Old_Price=jQuery('.woocommerce-variation-price').html();	
			 	$('.woocommerce-variation-price').text('');		
			 	var data = {
					 'action': 'action_to_get_variation_price',
					 'Variation_Id': Variation_Id,
					 'nonce': sbpp_common_param.nonce,	
				 };
				$.post(sbpp_common_param.ajaxurl, data, function(response) {
				if (response!="" && response!=undefined) {				
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