define([
    'jquery', 
    'uiComponent', 
    'ko', 
    'mage/translate',
    'Magento_Checkout/js/model/quote'
], function ($, Component, ko, $t, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Visiture_PersonalFedexUpsAccount/customer-shipping'
        },

        methodCode: ko.observable(shippingCode),
        isAllow : ko.observable(isAllowChargeFreight3rdpartyHandling),
        availableAcType: ko.observableArray(accountTypes),
        optionsCaption: ko.observable($t("-- Select an account type --")),
        frmAction: ko.observable(formAction),
        acnumber: ko.observable(accountNumber),
        actype: ko.observable(accountType),
        custommsg: ko.observable(customMessage),
        shippingMessage: ko.observable(shippingMessage),
        iscustommsg: ko.computed(function () { return customMessage.length > 0? 1:0;}),
        isSelected: ko.computed(function () { return quote.shippingMethod() ? quote.shippingMethod().carrier_code == shippingCode? 1 : 0 : 0;}),

        initialize: function () {
            self = this;
            this._super();
            window.formAction = formAction;
        },
    });
});