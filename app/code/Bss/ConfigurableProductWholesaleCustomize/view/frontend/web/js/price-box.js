define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'underscore',
    'mage/template',
    'jquery/ui'
], function ($, utils, _, mageTemplate) {
    'use strict';

    return function (priceBox) {
        return $.widget('mage.priceBox', priceBox, {
            updatePrice: function updatePrice(newPrices)
            {
                var prices = this.cache.displayPrices,
                    additionalPrice = {},
                    pricesCode = [];

                this.cache.additionalPriceObject = this.cache.additionalPriceObject || {};

                if (newPrices) {
                    $.extend(this.cache.additionalPriceObject, newPrices);
                }

                if (!_.isEmpty(additionalPrice)) {
                    pricesCode = _.keys(additionalPrice);
                } else if (!_.isEmpty(prices)) {
                    pricesCode = _.keys(prices);
                }

                _.each(this.cache.additionalPriceObject, function (additional) {
                    if (additional && !_.isEmpty(additional)) {
                        pricesCode = _.keys(additional);
                    }
                    _.each(pricesCode, function (priceCode) {
                        var priceValue = additional[priceCode] || {};
                        priceValue.amount = +priceValue.amount || 0;
                        priceValue.adjustments = priceValue.adjustments || {};

                        additionalPrice[priceCode] = additionalPrice[priceCode] || {
                            'amount': 0,
                            'adjustments': {}
                        };
                        additionalPrice[priceCode].amount =  0 + (additionalPrice[priceCode].amount || 0)
                            + priceValue.amount;
                        _.each(priceValue.adjustments, function (adValue, adCode) {
                            additionalPrice[priceCode].adjustments[adCode] = 0
                                + (additionalPrice[priceCode].adjustments[adCode] || 0) + adValue;
                        });
                    });
                });

                if (_.isEmpty(additionalPrice)) {
                    this.cache.displayPrices = utils.deepClone(this.options.prices);
                } else {
                    var flag = false;
                    _.each(additionalPrice, function (option, priceCode) {
                        var origin = this.options.prices[priceCode] || {},
                            final = prices[priceCode] || {};
                        option.amount = option.amount || 0;
                        origin.amount = origin.amount || 0;
                        origin.adjustments = origin.adjustments || {};
                        final.adjustments = final.adjustments || {};

                        final.amount = 0 + origin.amount + option.amount;
                        _.each(option.adjustments, function (pa, paCode) {
                            final.adjustments[paCode] = 0 + (origin.adjustments[paCode] || 0) + pa;
                        });
                        if(typeof Object === 'object') {
                            var str = Object.keys(newPrices)[0];
                            if ($('#bss-option-price').length > 0 && str && str.indexOf('options') == 0 && !flag) {
                                $('#bss-option-price').val(option.amount).trigger('change');
                                flag = true;
                            }
                        }
                    }, this);
                }
                this.element.trigger('reloadPrice');
            },

        });
    };
});
