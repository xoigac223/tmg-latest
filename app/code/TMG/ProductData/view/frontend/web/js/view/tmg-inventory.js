define([
    'ko',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function (ko, Component, customerData, $t) {

    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();
        },
        // displayContent: ko.observable(true),
        isLoggedIn: function() {
            return !(customerData.get('customer')().firstname === undefined)
        },
        // getAccountLinkLabel: function() {
        //     return (this.isLoggedIn())
        //         ? customerData.get('customer')().firstname
        //         : this['login_label'];
        // },
        // getAccountLinkUrl: function()
        // {
        //     return (this.isLoggedIn())
        //         ? this['account_url']
        //         : this['login_url'];
        // },
    });

});