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
        return function (email,view) {

            var viewObj = view;

            viewObj.clearMessages();

            var params = {
                'email': email
            };

            return storage.post(
                urlBuilder.build('tmg-customer/json/accountEmailLookup'),
                JSON.stringify(params),
                false
            ).done(
                function (response) {
                    viewObj.emailLookupResponseHandler(params,response);
                }
            ).error(
                function (e) {
                    viewObj.emailLookupErrorHandler(params,e);
                }
            ).always(
                function() {
                    viewObj.isLoading(false);
                }
            );

        };
    }
);