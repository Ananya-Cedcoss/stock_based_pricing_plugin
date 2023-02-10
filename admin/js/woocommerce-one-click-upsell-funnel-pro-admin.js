(function( $ ) {
	'use strict';
	$(document).ready(function(){
		$('#wps_wocuf_pro_target_pro_ids').select2();

		// Add multiselect to Funnel Schedule since v3.5.0
		if ( $( '.wps-upsell-funnel-schedule-search' ).length ) {

			$( '.wps-upsell-funnel-schedule-search' ).select2();

		}
	});
})( jQuery );

jQuery(document).ready( function($) {

	const checkBasePlugin = () => {
		if( 'false' === wps_wocuf_pro_obj_form.org_activated ) {
			let head = wps_wocuf_pro_obj_form.org_activation_text.title;
			let text = wps_wocuf_pro_obj_form.org_activation_text.text;
			let url = wps_wocuf_pro_obj_form.org_activation_text.url;

			Swal.fire({
				icon: 'warning',
				title: head,
				showConfirmButton: false,
				html: '<p>' + text + '</p><a class="button button-primary" target="_blank" href="' + url + '">Install</a>',
				allowOutsideClick : false,
				showClass: {
					popup: 'animate__animated animate__backInDown'
				},
				hideClass: {
					popup: 'animate__animated animate__hinge'
				},
			});
		}
	}

	checkBasePlugin();

	// Reflect Funnel name input value.
	$("#wps_upsell_funnel_name").on("change paste keyup", function() {
	    $("#wps_upsell_funnel_name_heading h2").text( $(this).val() );
	}); 

	// Funnel status Live <->  Sandbox.
	$('#wps_upsell_funnel_status_input').click( function() {

	    if( true === this.checked ) {

	    	$('.wps_upsell_funnel_status_on').addClass('active');
			$('.wps_upsell_funnel_status_off').removeClass('active');
	    }

	    else {

	    	$('.wps_upsell_funnel_status_on').removeClass('active');
			$('.wps_upsell_funnel_status_off').addClass('active');
	    }
	});

	// Preview respective template.
	$(document).on('click', '.wps_upsell_view_offer_template', function(e) {

		// Current template id.
		var template_id = $(this).data( 'template-id' );

		$('.wps_upsell_offer_template_previews').show();

		$('.wps_upsell_offer_template_preview_' + template_id ).addClass('active');

		$('body').addClass('wps_upsell_preview_body');

	});

	// Close Preview of respective template.
	$(document).on('click', '.wps_upsell_offer_preview_close', function(e) {

		$('body').removeClass('wps_upsell_preview_body');

		$('.wps_upsell_offer_template_preview_one').removeClass('active');
		$('.wps_upsell_offer_template_preview_two').removeClass('active');
		$('.wps_upsell_offer_template_preview_three').removeClass('active');

		$('.wps_upsell_offer_template_previews').hide();

	});

	$('.wps_upsell_slide_down_link').click(function(e) {

		e.preventDefault();

	    $('.wps_upsell_slide_down_content').slideToggle("fast");

	});

    // Dismiss Elementor inactive notice.
	$(document).on('click', '#wps_upsell_dismiss_elementor_inactive_notice', function(e) {

		e.preventDefault();

		$.ajax({
		    type:'POST',
		    url :wps_wocuf_pro_obj.ajaxUrl,
		    data:{
		    	action: 'wps_upsell_dismiss_elementor_inactive_notice',
		    },

		    success:function() {

		    	window.location.reload();
			}
	   });		
	});

	// License.
	jQuery('#wps_wocuf_pro_license_key').on("click",function(e){
		jQuery('#wps_wocuf_pro_license_activation_status').html("");
	});

	jQuery('form#wps_wocuf_pro_license_form').on("submit",function(e) {

		e.preventDefault();	

		$('#wps_upsell_license_ajax_loader').show();

		var license_key =  $('#wps_wocuf_pro_license_key').val();

		wps_wocuf_pro_send_license_request(license_key);		
	});

	function wps_wocuf_pro_send_license_request(license_key) {

		$.ajax({
	        type:'POST',
	        dataType:'JSON',
	        url :wps_wocuf_pro_obj.ajaxUrl,
	        data:{action:'wps_wocuf_pro_validate_license_key',purchase_code:license_key},

	        success:function(data) {

	        	$('#wps_upsell_license_ajax_loader').hide();

	        	if( data.status == true ) {

	        		$("#wps_wocuf_pro_license_activation_status").css("color", "#42b72a");

	        		jQuery('#wps_wocuf_pro_license_activation_status').html(data.msg);

					location = wps_wocuf_pro_location.location;
	        	}

	        	else {

	        		$("#wps_wocuf_pro_license_activation_status").css("color", "#ff3333");

	        		jQuery('#wps_wocuf_pro_license_activation_status').html(data.msg);

	        		jQuery('#wps_wocuf_pro_license_key').val("");
	        	}
	        }
		});
	}

	/**
	 * Custom Image setup.
	 * Wordpress image upload.
	 */
	jQuery(function($){
		/*
		 * Select/Upload image(s) event.
		 */
		jQuery('body').on('click', '.wps_wocuf_pro_upload_image_button', function(e){

			e.preventDefault();
    		var button = jQuery(this),
    		custom_uploader = wp.media({
				title: 'Insert image',
				library : {
					type : 'image'
				},
				button: {
					text: 'Use this image' 
				},
				multiple: false
			}).on('select', function() {
				var attachment = custom_uploader.state().get('selection').first().toJSON();
				jQuery(button).removeClass('button').html('<img class="true_pre_image" src="' + attachment.url + '" style="max-width:150px;display:block;" />').next().val(attachment.id).next().show();
			}).open();
		});
	 
		/*
		 * Remove image event.
		 */
		jQuery('body').on('click', '.wps_wocuf_pro_remove_image_button', function(e){
			e.preventDefault();
			jQuery(this).hide().prev().val('').prev().addClass('button').html('Upload image');
			return false;
		});
	});

	/*
	 * Change free order upsell select event.
	 */
	jQuery( '.wps_wocuf_pro_enable_free_upsell_input' ).on( 'change', function(){
		jQuery(this).prop('checked') ? jQuery( '.wps_wocuf_pro_free_upsell_select ' ).removeClass( 'keep_hidden' ) : jQuery( '.wps_wocuf_pro_free_upsell_select ' ).addClass( 'keep_hidden' );
	});

	if($('#wps_wocuf_pro_frm_tick').is(':checked')){
		show_fetched_tables();
		$("#wps_wocuf_pro_form_add_new_field").show();
		
	} 
	if( ! $('#wps_wocuf_pro_frm_tick').is(':checked')) {
		$("#wps_wocuf_pro_form_add_new_field").hide();
		$(".fieldsdata").html('');
	}

	$('body').on('click', '#wps_wocuf_pro_frm_tick', function(){
		if($('#wps_wocuf_pro_frm_tick').is(':checked')){
			show_fetched_tables();
			$("#wps_wocuf_pro_form_add_new_field").show();
			
		} 
		if( ! $('#wps_wocuf_pro_frm_tick').is(':checked')) {
			$("#wps_wocuf_pro_form_add_new_field").hide();
			$(".fieldsdata").html('');
		}
	})


	// When the submit button is pressed on the pop up on funnel creation page Show custom form fields
	jQuery('#wps_wocuf_field_submit').on('click',function($){
		var funnel_id = jQuery('#wps_wocuf_pro_funnel_id').val();
		var nameOfField        = jQuery('#wps_funnel_popup_custom_input_name').val();
		var placeholderOfField = jQuery('#wps_funnel_popup_custom_input_placeholder').val();
		var typeOfField        = jQuery('#wps_funnel_popup_custom_input_type').val();
		var description        = jQuery('#wps_funnel_popup_custom_input_description').val();
		if( nameOfField == '' || placeholderOfField == '' || typeOfField == '' || description == '' ) {
			alert('Please Fill All Fields');
			return false;
		}
		jQuery.ajax({
			type: 'post',
			dataType: 'json',
			url: wps_wocuf_pro_obj_form.ajaxurl,
			data: { 
				nonce         : wps_wocuf_pro_obj_form.nonce,
				action        : 'save_custom_form_fields',
				name          : nameOfField, // offer product id.
				placeholder   : placeholderOfField,
				type          : typeOfField,
				description   : description,
				funnel_id : funnel_id,
			},
			success: function( message ) {
				if(message == 'Value Entered Successfully') {
					alert('Value Entered Successfully');
					jQuery('#wps_funnel_popup_custom_input_name').val('');
					jQuery('#wps_funnel_popup_custom_input_placeholder').val('');
					jQuery('#wps_funnel_popup_custom_input_type').val('');
					jQuery('#wps_funnel_popup_custom_input_description').val('');
					jQuery(".fieldsdata").html('');
					show_fetched_tables()
					self.parent.tb_remove();
					
				}
				if(message == 'Field with this name already exist') {
					alert('Field with this name already exist');
					jQuery('#wps_funnel_popup_custom_input_name').val('');
				}
			}
		});
	});

	function show_fetched_tables() {
		
		var funnel_id = jQuery("#wps_wocuf_pro_funnel_id").val();
		// Ajax to show already added values from database in the table starts.
		jQuery.ajax({

			type: 'post',
			dataType: 'json',
			url: wps_wocuf_pro_obj_form.ajaxurl,
			data: {
				funnel_id : funnel_id,
				action: 'show_fields_in_table',
				nonce : wps_wocuf_pro_obj_form.nonce,	
			},
			success: function( data ) {
	
				// Number of keys in js object.
				no_of_keys = Object.keys(data).length;
			
				if( no_of_keys == 1 ) {
					return;
				}
				for ( i = 0; i < no_of_keys; i++ ) {
					// If order funnel id is same as the fields only then show.
					if( i == 0 ) {
						// So that header is shown only once.
						jQuery('.show_custom_form_fields_unique_id').html('<tr><td><ul class="wps_wocuf_field_head"><li scope="row" class="titledesc"><label>Name<label></li><li scope="row" class="titledesc"><label>Placeholder<label></li><li scope="row" class="titledesc"><label>Description<label></li><li scope="row" class="titledesc"><label>Type<label></li><li scope="row" colspan="2" class="titledesc"><label style="text-align:center">Action<label></li></ul></td></tr>');
					} else {
						jQuery('.show_custom_form_fields_header').after('<tr valign="top" class="fieldsdata"><td><ul class="wps_wocuf_field_list"><li class="forminp forminp-text nm">'+data[i]['name']+'</li><li class="forminp forminp-text plh">'+data[i]['placeholder']+'</li><li class="forminp forminp-text desc">'+data[i]['description']+'</li><li class="forminp forminp-text typ">'+data[i]['type']+'</li><li><input value="delete" type="button" href="#" id="'+data[i]['name']+'" class="delete_field_to_hide"></li><li><a href="#TB_inline?&inlineId=wps-fields-form-edit" id="'+data[i]['name']+'" class="thickbox wps_wocuf_pro_form_edit_field">Edit</a></li></ul></td></tr><br>');	
					}
				}
			}, error: function(error) {
				console.log(eval(error));
			}
		});
	}

	 // To delete a field in the pop up form on funnel creation page. 
	 jQuery('body').on('click', '.delete_field_to_hide',function(e){

		var wps_confirm = confirm("Are you sure you want to delete?");
		if (wps_confirm == true) {
			del_id = $(this).attr('id');
			var funnel_id = jQuery("#wps_wocuf_pro_funnel_id").val();
			var tr = $('#'+del_id).closest('tr').hide();
			var select_tr = (Object.values(tr)[0]);
			jQuery.ajax({
				type: 'post',
				dataType: 'json',
				url: wps_wocuf_pro_obj_form.ajaxurl,
				data: { 
					id : del_id,
					funnel_id : funnel_id,
					nonce : wps_wocuf_pro_obj_form.nonce,
					action: 'delete_a_custom_field_from_table',
				},
				success: function( data ) {
		
					}
				})
		}		
	});

	jQuery('body').on('click', '.wps_wocuf_pro_form_edit_field', function() {
		var row   = $(this).closest("tr");    // Find the row
		var name  = row.find(".nm").text();
		var place = row.find(".plh").text();
		var desc  = row.find(".desc").text();
		var type  = row.find(".typ").text();
		jQuery('#wps_funnel_custom_input_name').val(name);
		jQuery('#wps_hidden_name').val(name);
		jQuery('#wps_funnel_custom_input_placeholder').val(place);
		jQuery('#wps_funnel_custom_input_description').val(desc);
		jQuery('#wps_funnel_custom_input_type').val(type);
	})

	jQuery('#wps_wocuf_field_edit').on('click',function($){
		var funnel_id          = jQuery('#wps_wocuf_pro_funnel_id').val();
		var nameOfField        = jQuery('#wps_funnel_custom_input_name').val();
		var id_name            = jQuery('#wps_hidden_name').val();
		var placeholderOfField = jQuery('#wps_funnel_custom_input_placeholder').val();
		var typeOfField        = jQuery('#wps_funnel_custom_input_type').val();
		var description        = jQuery('#wps_funnel_custom_input_description').val();

		if( nameOfField == '' || placeholderOfField == '' || typeOfField == '' || description == '' ) {
			alert('Please Fill All Fields');
			return false;
		}
		jQuery.ajax({
			type: 'post',
			dataType: 'json',
			url: wps_wocuf_pro_obj_form.ajaxurl,
			data: { 
				nonce         : wps_wocuf_pro_obj_form.nonce,
				action        : 'edit_custom_form_fields',
				name          : nameOfField,
				id_name       : id_name,
				placeholder   : placeholderOfField,
				type          : typeOfField,
				description   : description,
				funnel_id     : funnel_id,
			},
			success: function( message ) {
				alert(message);
				jQuery(".fieldsdata").html('');
					show_fetched_tables()
					self.parent.tb_remove();
			}
		});
	});

	if(! $('#wps_wocuf_pro_add_products_tick').is(':checked')) {
		$('.wps_wocuf_pro_select_products').hide();
	}
	
	$('body').on('click', '#wps_wocuf_pro_add_products_tick', function(){
		$('.wps_wocuf_pro_select_products').toggle();
	})

// End of scripts.
});
