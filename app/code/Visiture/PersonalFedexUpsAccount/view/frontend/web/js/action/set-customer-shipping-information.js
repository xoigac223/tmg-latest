define(
    [
        'Visiture_PersonalFedexUpsAccount/js/model/customer-shipping-save-processor'
    ],
    function (customerShippingSaveProcessor) {
        'use strict';
        return function () {
            return customerShippingSaveProcessor.saveCustomerShippingInformation();
        }
    }
);
