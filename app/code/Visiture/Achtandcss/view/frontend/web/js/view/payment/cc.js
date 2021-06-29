define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'cc',
                component: 'Visiture_Achtandcss/js/view/payment/method-renderer/cc-method'
            }
        );
        return Component.extend({});
    }
);