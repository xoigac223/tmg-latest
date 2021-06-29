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
                type: 'acht',
                component: 'Visiture_Achtandcss/js/view/payment/method-renderer/acht-method'
            }
        );
        return Component.extend({});
    }
);