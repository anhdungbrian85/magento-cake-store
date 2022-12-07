/*** Copyright © Ulmod. All rights reserved. **/
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/mage',
    'jquery/ui'
], function ($, modal) {
    'use strict';

    $.widget('ulmod.processUmProductinquiry', {

        /**
         * Bind handlers to events.
         */
        _create: function () {

			var self = this,
			popup_umproductinquiry_options = {
					type: 'popup',
					responsive: true,
					innerScroll: true,
					title: this.options.umProdInquiryPopupTitle,
					wrapperClass: 'modals-wrapper umprodinquiry-modals-wrapper',
					modalClass : 'um-prodinquiry-m-container',				
					buttons: [{
						text: $.mage.__('Submit'),
						class: 'umprodinquiry_submit action primary',
						attr: {
							'data-action': 'confirm'
						},
						click: function () {
							$('#um-prodinq-form').submit();
						}
					}]
			};
		
			modal(popup_umproductinquiry_options, $('#um-prodinq-popup'));
		
			$(document).on('click', '.umprodinquiry_clickme', function () {
				var product = $(this).parent().find('input[name="product"]').val();
				var product_name = $(this).parent().find('input[name="product_name"]').val();
				var product_sku = $(this).parent().find('input[name="product_sku"]').val();       
				$('.um-prodinquiry-actions .product_ids').val(product);
				$('.um-prodinquiry-actions .product_name').val(product_name);
				$('.um-prodinquiry-actions .product_sku').val(product_sku);   
				var product_img_abspath = $('#product_img_' + product).attr('src');
				$('.um-prodinquiry-top-block .um-prodimg').attr('src', product_img_abspath);
				$('.um-prodinquiry-top-block .um-prodname').text(product_name);
				$('.um-prodinquiry-top-block .um-prodsku').text(product_sku);    
				$('#um-prodinq-form')[0].reset();
				$('#um-prodinq-popup').modal('openModal');
				$('.modal-footer').hide();
			}); 

			// checkbox yes/no
			$('#extra_field_checkbox').change(function(){
				if ($(this).attr('checked')) {
					  $(this).val('Yes');
				 } else {
					  $(this).val('No');
				 }    
			});			
           
        }
    });

    return $.ulmod.processUmProductinquiry;
});