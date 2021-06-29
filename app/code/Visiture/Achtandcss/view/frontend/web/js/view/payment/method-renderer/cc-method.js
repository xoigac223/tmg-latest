define([
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Payment/js/model/credit-card-validation/validator'
    ],
    function ($, Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Visiture_Achtandcss/payment/cc'
            },

            context: function() {
                return this;
            },

            getCode: function() {
                return 'cc';
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            }
        });
    }
);