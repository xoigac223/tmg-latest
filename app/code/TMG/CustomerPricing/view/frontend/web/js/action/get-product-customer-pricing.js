/*global define,alert*/
define(
    [
        'jquery',
        'mage/storage',
        'mage/url',
        'mageUtils'
    ],
    function ($, storage, urlBuilder) {
        'use strict';
        return function (sku,obj) {

            var params = {
                'sku': sku
            };

            return storage.post(
                urlBuilder.build(window.authenticationPopup.baseUrl+'tmgCustomerPricing/json/productCustomerPricing'),
                JSON.stringify(params),
                false
            ).done(
                function (response) {
                    obj.productCustomerPricingLoadHandler(sku,response);
                }
            ).error(
                function (e) {
                    obj.productCustomerPricingLoadErrorHandler(sku,e);
                }
            );

        };
    }
);