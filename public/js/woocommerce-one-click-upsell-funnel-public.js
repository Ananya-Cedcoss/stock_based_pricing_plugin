var wps_wocuf_pro_custom_offer_bought = false;

jQuery(document).ready(function($) {

    jQuery('#wps_wocuf_pro_offer_loader').hide();

    jQuery('.wps_wocuf_pro_custom_buy').on('click', function(e) {
        jQuery('#wps_wocuf_pro_offer_loader').show();
        if (wps_wocuf_pro_custom_offer_bought) {
            e.preventDefault();
            return;
        }
        wps_wocuf_pro_custom_offer_bought = true;
    });

    jQuery('.wps_wocuf_pro_no').on('click', function(e) {

        jQuery('#wps_wocuf_pro_offer_loader').show();

    });

    jQuery('.wps_wocuf_field_number').on('change, keyup', function(e) {
        if (jQuery(this).val() < 0) {
            jQuery(this).val('');
        }

    });

    /**
     * Shortcode Scripts since v3.5.0
     */
    jQuery('#wps_upsell_quantity_field').on('ready change keyup', function(e) {

        var updated_quantity = jQuery(this).val();
        var max_quantity = jQuery(this).attr('max');

        if (max_quantity < updated_quantity) {

            updated_quantity = max_quantity;
            jQuery(this).val(max_quantity);
        }

        jQuery('a').map(function() {

            // Check if any of them are empty.
            if (this.href.includes('wps_wocuf_pro_buy')) {

                if (false == this.href.includes('quantity')) {

                    var paramurl = this.href + '&quantity=1';
                    jQuery(this).attr('href', paramurl);
                }

                var currentquantity = jQuery(this).attr('href').split('quantity=');

                if ('' != currentquantity[1]) {

                    currentquantity = currentquantity[1];
                } else {

                    currentquantity = 1;
                }

                var newUrl = this.href.replace('quantity=' + currentquantity, 'quantity=' + updated_quantity);
                jQuery(this).attr('href', newUrl);

            }

            // For variable products.
            else if (this.href.includes('#wps_upsell')) {

                jQuery('input[name="wps_wocuf_pro_quantity"]').val(updated_quantity);

            }
        });
    });

    /**
     * Sweet Alert when Upsell Action Buttons are clicked in Preview Mode. 
     * since v3.5.0
     */
    $('a[href="#preview"]').on('click', function(e) {

        e.preventDefault();

        swal(wps_upsell_public.alert_preview_title, wps_upsell_public.alert_preview_content, 'info');
    });


    /**
     * Adding Upsell Loader since v3.5.0
     */
    if ('undefined' !== typeof(wps_upsell_public)) {

        if (wps_upsell_public.show_upsell_loader) {

            wps_upsell_loader_message = wps_upsell_public.upsell_actions_message;

            wps_upsell_loader_message_html = '';

            if (wps_upsell_loader_message.length) {

                wps_upsell_loader_message_html = '<p class="wps_upsell_loader_text">' + wps_upsell_loader_message + '</p>';
            }

            jQuery('body').append('<div class="wps_upsell_loader">' + wps_upsell_loader_message_html + '</div>');

            jQuery(document).on('click', 'a', function(e) {

                // Check if any of them are empty.
                if (this.href.includes('wps_wocuf_pro_buy') || this.href.includes('#wps_upsell') || this.href.includes('ocuf_th')) {

                    if (this.href.includes('wps_wocuf_pro_buy')) {

                        $this = this;
                        get_wps_form(e, $this);

                    }
                }
            });
        }
    }

    jQuery(document).on('submit', '#wps_upsell_offer_buy_now_form', function(e) {

        get_varition_wps_form(e);

    });

    function get_varition_wps_form(e) {

        if ($('#wps_upsell_offer_buy_now_form').length) {
            var fields = {};
            var err = 0;
            var labeltxt = [];
            jQuery('.wps_label').each(function() {
                var labelt = jQuery(this).text();
                labeltxt.push(labelt);
            });

            jQuery('.wps_wocuf_box').each(function(index) {

                if (jQuery(this).attr('type') == 'checkbox') {
                    if (jQuery(this).is(':checked')) {
                        var txt = 'yes';
                    } else {
                        var txt = 'no';
                    }
                } else if (jQuery(this).attr('type') == 'email') {
                    if (false == IsEmail(jQuery(this).val())) {
                        e.preventDefault();
                        swal('Error', 'Please enter a valid email in ' + labeltxt[index], 'error');
                        err++;
                        return;
                    }
                } else {
                    var txt = jQuery(this).val();
                }

                if (txt !== '') {
                    fields[labeltxt[index]] = txt;
                } else {
                    e.preventDefault();
                    swal('Error', 'Please fill ' + labeltxt[index], 'error');
                    err++;
                    return;
                }
            });

            if (err == 0) {
                // Show loader on click.
                jQuery('.wps_upsell_loader').show();
                var myJSON = JSON.stringify(fields);
                myJSON = myJSON.replace(/"/g, '~');
                jQuery('#wps_upsell_offer_buy_now_form').append('<input name="formdata" id="wpsvarform" type="hidden" value="' + myJSON + '" >');
            } else {
                jQuery('.wps_upsell_loader').hide();
            }
        }
    }

    function IsEmail(email) {
        var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if (!regex.test(email)) {
            return false;
        } else {
            return true;
        }
    }

    function get_wps_form(e, $this) {
        e.preventDefault();
        var link = $($this).attr('href');
        var fields = {};
        var c = 0;
        var err = 0;
        var labeltxt = [];
        jQuery('.wps_label').each(function() {


            var labelt = jQuery(this).text();

            labeltxt.push(labelt);

        });

        jQuery('.wps_wocuf_box').each(function() {
            if (jQuery(this).attr('type') == 'checkbox') {
                if (jQuery(this).is(':checked')) {
                    var txt = 'yes';
                } else {
                    var txt = 'no';
                }
            } else if (jQuery(this).attr('type') == 'email' && false == IsEmail(jQuery(this).val())) {
                alert('Please fill valid email');
                err = 1;
            } else {
                var txt = jQuery(this).val();
            }

            if (txt !== '') {
                fields[labeltxt[c]] = txt;

            } else {
                alert('Please fill ' + labeltxt[c]);
                err++;
            }
            c++;
        });

        if (err == 0) {
            // Show loader on click.
            jQuery('.wps_upsell_loader').show();
            var myJSON = JSON.stringify(fields);
            link += '&data=';
            link += myJSON;
            window.location.replace(link);

        }
    }

    // WhenEveN aDD OFFER BUTTON IS ADDED!
    jQuery(document).on('click', '.wps_accept_add_offer', function(e) {

        const addedButton = '<button type="submit" class="button" disabled="" aria-disabled="true">Offer Added</button>';
        let id = jQuery(this).attr('data-id');
        var btn = jQuery(this);
        let urlParams = new URLSearchParams(window.location.search);
        let order_key = urlParams.get('ocuf_ok');
        let funnel_id = urlParams.get('ocuf_fid');
        let offer_id = urlParams.get('ocuf_ofd');
        let wp_nonce = urlParams.get('ocuf_ns');

        if (id.length == 0) {
            return;
        }

        jQuery.ajax({
            type: 'post',
            dataType: 'json',
            url: wps_upsell_public.ajaxurl,
            data: {
                nonce: wps_upsell_public.nonce,
                action: 'add_additional_offer_to_order',
                id: id,
                order_key: order_key,
                funnel_id: funnel_id,
                offer_id: offer_id,
                wp_nonce: wp_nonce,
            },
            success: function(response) {
                btn.replaceWith(addedButton);
            },
            error: function(response) {
                jQuery(this).replaceWith(btn);
            }
        })
    })

    jQuery(document).on('change', '.wps_wocuf_additional_variation_select', function(e) {

        let wrapper = jQuery(this).closest('.offer-variable');
        let selects = wrapper.find('.wps_wocuf_additional_variation_select');
        let variationIds = wrapper.find('.wps-wocuf-prod-variation-matcher').attr('data-ids');
        let variationImg = wrapper.find('.wps-wocuf-prod-variation-matcher').attr('data-img');
        let variationAttr = wrapper.find('.wps-wocuf-prod-variation-matcher').attr('data-attr');
        let variationPhtml = wrapper.find('.wps-wocuf-prod-variation-matcher').attr('data-p-html');

        if (null != variationIds) {
            variationIds = JSON.parse(variationIds);
        }

        if (null != variationImg) {
            variationImg = JSON.parse(variationImg);
        }

        if (null != variationAttr) {
            variationAttr = JSON.parse(variationAttr);
        }

        if (null != variationPhtml) {
            variationPhtml = JSON.parse(variationPhtml);
        }

        let isEmpty = false;
        let attrInputs = {};
        selects.each(function(index) {
            if (0 == $(this).val().length) {
                isEmpty = true;
                return false;
            } else {
                attrInputs[$(this).attr('id')] = $(this).val();
            }
        });

        if (true == isEmpty) {
            return;
        }

        var foundKey = search(attrInputs, variationAttr);

        if ('NotFound' != foundKey) {
            id = variationIds[foundKey];
            img = variationImg[foundKey];
            phtml = variationPhtml[foundKey];
            wrapper.find('.wps_accept_add_offer').attr('data-id', id);
            wrapper.find('.wps_additional_offer_image').attr('src', img);
            wrapper.find('.wps_additional_offer_image').attr('style', 'max-width : 150px');
            wrapper.find('.wps_additional_offer_image').trigger('change');
            wrapper.find('.wps_upsell_additional_offer_product_price').html(phtml);
        } else {
            swal('Sorry', 'No Such Product Available', 'info');
        }
    });

    jQuery('.offer-variable').find('.wps_accept_add_offer').each(function(index) {
        $(this).attr('data-id', '');
    });

    // Seach variations.
    function search(selectedAttr, attrBundle) {
        for (var i = 0; i < attrBundle.length; i++) {
            if (JSON.stringify(selectedAttr) == JSON.stringify(attrBundle[i])) {
                return i;
            }
        }

        return 'NotFound';
    }

});

// Design fixes v3.6.1 START
jQuery(document).ready(function() {
    jQuery('.wps-wocuf__front-form-item input[type=checkbox]').parent().addClass('wps-wocuf__front-form-checkbox-label');
});
// Design fixes v3.6.1 START