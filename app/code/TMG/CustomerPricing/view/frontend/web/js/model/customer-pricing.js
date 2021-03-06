define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'TMG_CustomerPricing/js/action/get-product-customer-pricing'
], function ($,customerData,getProductCustomerPricing) {
    'use strict';

    var customerPricing = {

        sku: null,

        delayInitCount: 0,

        initialized: false,

        options: {},

        config: {},

        dynamicOptionsParams: {},

        dynamicOptionsInitAborted: false,

        customerPricingNamespace: 'tmg-customer-pricing',

        productCustomerPricing: null,

        customerGroupId: '2',

        /**
         * Customer data initialization
         */
        init: function (sku,config,options,dynamicOptionsParams) {
            this.sku = sku;
            this.config = config;
            this.options = options;
            this.dynamicOptionsParams = dynamicOptionsParams;
            // Append Options
            this.initProductCustomerPricing();
        },

        /**********************************************************************************************************/

        getPrintMethodPricingKeyMapping: function ()
        {
            return this.config.print_method_pricing_key;
        },

        preparePrices: function ()
        {
            this.preparePrintMethodPrices();
            this.prepareOrderSamplePrices();
            this.prepareColorChargesPrices();
            this.initialized = true;
        },

        getFormattedTierPrices: function(tierPrices,priceKey)
        {
            var itemPrices = [];
            $.each(tierPrices,function(tierQty,tierData) {
                var priceObj = {'qty': tierQty, "price_type": "fixed", 'price': parseFloat(tierData[priceKey]) }
                itemPrices.push(priceObj);
                if(tierQty == 12) {
                    priceObj.qty = 1;
                    itemPrices.push(priceObj);
                }
            });
            return JSON.stringify(itemPrices);
        },

        preparePrintMethodPrices: function ()
        {
            // Insert Customer Tier Prices to Print Method
            var self = this;
            var printMethodOptionId = self.getPrintMethodOptionId();
            if (printMethodOptionId) {
                $.each(self.getPrintMethodPricingKeyMapping(), function (label, pricingKey) {
                    var tierPrices = self.productCustomerPricing[pricingKey];
                    var printMethodOptionValueId = self.getItemIdByLabel(self.options[printMethodOptionId].items, label);
                    if (printMethodOptionValueId != null) {
                        self.options[printMethodOptionId].items[printMethodOptionValueId]
                            .tier_price = self.getFormattedTierPrices(tierPrices,'item_price');
                    }
                });
            }
        },

        prepareOrderSamplePrices: function ()
        {
            var self = this;
            var buyOptionOptionId = self.getBuyOptionOptionId();
            if (buyOptionOptionId) {
                var label = self.config.option_value_label.order_sample;
                var orderSampleOptionValueId = self.getItemIdByLabel(self.options[buyOptionOptionId].items, label);
                var tierPrices = self.productCustomerPricing[self.config.order_sample_pricing_key];
                self.options[buyOptionOptionId].items[orderSampleOptionValueId]
                    .tier_price = self.getFormattedTierPrices(tierPrices,'item_price');
            }
            return this;
        },

        prepareColorChargesPrices: function ()
        {
            var self = this;
            $.each(self.options,function(optionKey,option) {
                if(self.isWholesaleCustomerGroup(option) && self.isColorChargeOption(option)) {
                    var pricingKey = self.getPrintMethodPricingKeyByCondition(option);
                    if(pricingKey) {
                        // Set Prices
                        $.each(option.items,function(itemKey,item) {
                            if(self.isColorChargeOptionValue(item)) {
                                var tierPrices = self.productCustomerPricing[pricingKey];
                                if(tierPrices) {
                                    self.options[optionKey].items[itemKey]
                                        .tier_price = self.getFormattedTierPrices(tierPrices,'color_price');
                                }
                            }
                        });
                    }
                }
            });
            return this;
        },

        isColorChargeOption: function(option)
        {
            return (option.title == this.config.option_label.color_charge);
        },

        isColorChargeOptionValue: function(option)
        {
            return (option.title == this.config.option_value_label.color_charge);
        },

        getPrintMethodPricingKeyByCondition: function(option)
        {
            var self = this;
            var result = null;
            $.each(this.getOptionConditions(option),function(k,condition) {
                if(condition.value in self.getPrintMethodPricingKeyMapping()) {
                    result = self.getPrintMethodPricingKeyMapping()[condition.value];
                    return false;
                }
            });
            return result;
        },

        isWholesaleCustomerGroup: function(option)
        {
            var groupId = option.customer_group.split(',');
            return (groupId.indexOf(this.customerGroupId) != -1);
        },

        getOptionConditions: function(option)
        {
            var self = this;
            var result = null;
            $.each(self.dynamicOptionsParams.config.section_conditions,function(key,condition) {
                if(condition.order == option.section_order) {
                    result = JSON.parse(condition.visibility_condition).conditions;
                    return false;
                }
            });
            return result;
        },

        getOptionIdByLabel: function(items,label)
        {
            var self = this;
            var result = null;
            $.each(items,function(k,v) {
                if (self.compareText(v.title, label)) {
                    result = k;
                    return false;
                }
            })
            return result;
        },

        getItemIdByLabel: function(items,label)
        {
            var self = this;
            var result = null;
            $.each(items,function(k,v) {
                // console.log(v,label,self.customerGroupId);
                if (v.customer_group == self.customerGroupId && self.compareText(v.title, label)) {
                    result = k;
                    return false;
                }
            })
            return result;
        },

        getBuyOptionOptionId: function()
        {
            return this.getOptionIdByLabel(this.options, this.config.option_label.buy_option)
        },

        getPrintMethodOptionId: function()
        {
            return this.getOptionIdByLabel(this.options,this.config.option_label.print_method);
        },

        /**
         * @todo Improve Comparision
         *
         * @param txta
         * @param txtb
         * @returns {boolean}
         */
        compareText: function(txta,txtb)
        {
            return (txta == txtb);
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
            if(!(sku in customerPricingData)) {
                return false;
            }
            return customerPricingData[sku];
        },

        productCustomerPricingLoadErrorHandler: function(sku,error)
        {
            console.warn(error);
        },

        productCustomerPricingLoadHandler: function(sku,response)
        {
            if(response.error) {
                console.warn(response.message);
                return;
            }
            // Session Save
            this.productCustomerPricing = response.result;
            this.setSessionProductCustomerPricing(sku,this.productCustomerPricing);
            this.preparePrices();
            if(this.dynamicOptionsInitAborted) {
                this.dynamicOptionsInit(0);
            }
            return;
        },

        loadProductCustomerPricing: function(sku)
        {
            getProductCustomerPricing(sku, this);
        },

        initProductCustomerPricing: function()
        {

            var sku = this.getSku();

            // Check Customer Logged In
            var firstname = customerData.get('customer')().firstname;
            /*if(firstname == undefined) {
                this.initialized = true;
                // console.log('initProductCustomerPricing :: CUSTOMER NOT LOGGED');
                return;
            }*/

            // Check From Session
            var sessionProductCustomerPricing = this.getSessionProductCustomerPricing(sku);
            if(!sessionProductCustomerPricing) {
                // console.log('initProductCustomerPricing :: NO DATA FROM SESSION');
                this.loadProductCustomerPricing(sku);
                return;
            }

            // console.log('initProductCustomerPricing :: GETTING DATA FROM SESSION');
            this.productCustomerPricing = sessionProductCustomerPricing;
            this.preparePrices();

        },

        dynamicOptionsInit: function(index)
        {
            var self = this;
            var dParams = self.dynamicOptionsParams;
            if(self.initialized) {
                // console.log('dynamicOptionsInit - OK');
                dParams.obj.initialize(dParams.config, self.options, dParams.isGrouped, dParams.tierPrices, dParams.translations);
            } else if (index < self.config.delay_options_init.max_retry) {
                index++;
                setTimeout( function() {
                    // console.log('dynamicOptionsInit - DELAY - Retry:' + index);
                    self.dynamicOptionsInit(index);
                }, self.config.delay_options_init.timeout);
            } else {
                // console.log('dynamicOptionsInit - LOAD ABORTED');
                dParams.obj.initialize(dParams.config, self.options, dParams.isGrouped, dParams.tierPrices, dParams.translations);
            }
            return;
        },

        getSku: function()
        {
            return this.sku;
        },

    };

    return customerPricing;

});
