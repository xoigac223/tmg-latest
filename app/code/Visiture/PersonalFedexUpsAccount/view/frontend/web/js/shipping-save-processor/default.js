/*global define,alert*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
    ],
    function (
        $,
        ko,
        quote,
        storage,
        errorProcessor,
        fullScreenLoader,
    ) {
        'use strict';

        return {
            saveCustomerShippingInformation: function () {
                var personal_ac_number = $('[name="personal_ac_number"]').val();
                var personal_ac_type = $('[name="personal_ac_type"]').val();
                var payload;
                
                payload = {
                    personal_ac_number: personal_ac_number,
                    personal_ac_type: personal_ac_type
                };

                fullScreenLoader.startLoader();

                return storage.post(
                    window.formAction,
                    payload,
                    true,
                    "application/x-www-form-urlencoded"
                ).done(
                    function (response) {
                        fullScreenLoader.stopLoader();
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        fullScreenLoader.stopLoader();
                    }
                );
            }
        };
    }
);