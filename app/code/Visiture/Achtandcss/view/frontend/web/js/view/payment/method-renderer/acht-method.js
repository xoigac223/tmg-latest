define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        "mage/validation"
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Visiture_Achtandcss/payment/acht',
                bankRoutingNumber: '',
                bankAccountNumber: '',
                checkNumber: ''
            },

            context: function() {
                return this;
            },

            getCode: function() {
                return 'acht';
            },

            isActive: function() {
                return true;
            },

            initObservable: function () {
                this._super()
                    .observe(['bankRoutingNumber','bankAccountNumber','checkNumber']);
                return this;
            },

            getData: function () {
                return {
                    "method": this.item.method,
                    'additional_data': {
                        'bank_routing_number': this.bankRoutingNumber(),
                        'bank_account_number': this.bankAccountNumber(),
                        'check_number': this.checkNumber(),
                    }
                };

            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            }
        });
    }
);