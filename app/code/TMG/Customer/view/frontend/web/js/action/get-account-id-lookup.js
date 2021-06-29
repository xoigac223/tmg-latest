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
        return function (params,view) {

            var viewObj = view;

            viewObj.clearMessages();

            return storage.post(
                urlBuilder.build('tmg-customer/json/accountIdLookup'),
                JSON.stringify(params),
                false
            ).done(
                function (response) {
                    viewObj.idLookupResponseHandler(params,response);
                }
            ).error(
                function (e) {
                    viewObj.idLookupErrorHandler(params,e);
                }
            ).always(
                function() {
                    viewObj.isLoading(false);
                }
            );

        };
    }
);