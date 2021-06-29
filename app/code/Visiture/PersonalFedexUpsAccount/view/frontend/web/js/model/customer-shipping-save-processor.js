define(
    [
        'Visiture_PersonalFedexUpsAccount/js/shipping-save-processor/default'
    ],
    function(defaultProcessor) {
        'use strict';
        var processors = [];
        processors['default'] =  defaultProcessor;

        return {
            saveCustomerShippingInformation: function () {
                return processors['default'].saveCustomerShippingInformation();
            }
        }
    }
);
