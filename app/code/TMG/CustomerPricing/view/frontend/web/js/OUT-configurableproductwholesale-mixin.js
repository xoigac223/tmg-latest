define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'TMG_CustomerPricing/js/action/get-product-customer-pricing'
], function ($,customerData,getProductCustomerPricing) {
    'use strict';

    return function (widget) {

        $.widget('bss.configurableproductwholesale', widget, {

            customerPricingNamespace: 'tmg-customer-pricing',

            productCustomerPricing: null,

            /**********************************************************************************************************/

            _init: function () {
                this.initProductCustomerPricing();
                this._super();
            },

            _reloadUnitPrice: function($this) {
                var self = this;
                $this.closest('.bss-table-row').find('.bss-tierprice').each(function() {
                    var qty = $(this).attr('attributeqty');

                    $(this).find('p').each(function() {
                        if($(this).hasClass("not_login") || $(this).hasClass("general")) {
                            // console.log('SKIPPING');
                            return;
                        } else {
                            var priceRow = self.getProductCustomerPricingRow(qty);
                            var finalPrice = parseFloat(priceRow.item_price) + parseFloat(priceRow.color_price);
                            if(!isNaN(finalPrice)) {
                                $('#'+self.options.ids.optionPrice + qty).val(finalPrice);
                            }
                            // console.log(finalPrice);
                            return false;
                        }

                    });

                });
                self._super($this);
            },

            /**********************************************************************************************************/

            setSessionProductCustomerPricing: function(sku,data)
            {
                var customerPricingData = customerData.get(this.customerPricingNamespace)();
                customerPricingData[sku] = data;
                customerData.set(this.customerPricingNamespace,customerPricingData);
                return this;
            },

            getSessionProductCustomerPricing: function(sku)
            {
                var customerPricingData = customerData.get(this.customerPricingNamespace)();
                // console.log('SKU:',sku,'CustomerPricingData',customerPricingData);
                if(!(sku in customerPricingData)) {
                    return false;
                }
                return customerPricingData[sku];
            },

            productCustomerPricingLoadErrorHandler: function(sku,error) {
                console.warn(error);
            },

            productCustomerPricingLoadHandler: function(sku,response) {

                if(response.error) {
                    console.warn(response.message);
                }

                // Session Save
                this.productCustomerPricing = response.result;
                this.setSessionProductCustomerPricing(sku,this.productCustomerPricing);

                return;

            },

            loadProductCustomerPricing: function(sku) {
                getProductCustomerPricing(sku, this);
            },

            initProductCustomerPricing: function() {
                var sku = this.getSku();
                // Validate - isLoggedIn
                if(!customerData.get('customer')().firstname) {
                    return;
                }
                // Validate - Saved
                var sessionProductCustomerPricing = this.getSessionProductCustomerPricing(sku);
                if(!sessionProductCustomerPricing) {
                    this.loadProductCustomerPricing(sku);
                    return;
                }
                this.productCustomerPricing = sessionProductCustomerPricing;

            },

            getProductCustomerPricingRow: function(qty)
            {
                var pricingKey = this.getCurrentPricingKey();
                // console.log('Pricing Key: ', pricingKey ,'QTY',qty);
                if(this.productCustomerPricing == null) {
                    // console.log('getProductCustomerPricingRow - ERROR: No product customer pricing');
                    return false;
                };
                if(!pricingKey) {
                    // console.log('getProductCustomerPricingRow - ERROR: NO PricingKey ');
                    return false;
                }
                if(!(pricingKey in this.productCustomerPricing)) {
                    // console.log('getProductCustomerPricingRow - ERROR: INVALID PricingKey');
                    return false;
                }
                var priceRow = this.productCustomerPricing[pricingKey][qty];
                return priceRow;
            },

            getCurrentPricingKey: function() {
                return $('#tmg-pricing-key-value').val();
            },

            getSku: function() {
                return $('#tmg-product-code').val();
            },

            // getProductCustomerPricing: function() {
            //
            //
            //     if(this.getProductCustomerPricing == null) {
            //         return false;
            //     }
            //     var sku = this.getSku();
            //     var customerPricing = customerData.get('tmg-customer-pricing')();
            //     if (!customerPricing) {
            //         customerPricing = {};
            //     }
            //     if(!customerPricing[sku]) {
            //         customerPricing[sku] = productCustomerPricing();
            //     }
            //
            //     console.log(customerPricing);
            //
            //
            // },



        });

        return $.bss.configurableproductwholesale;
    }
});
