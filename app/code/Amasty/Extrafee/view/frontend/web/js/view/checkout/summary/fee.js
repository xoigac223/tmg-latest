define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'Magento_Customer/js/customer-data',
        'uiRegistry'
    ],
    function (Component, quote, priceUtils, totals, storage, registry) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Amasty_Extrafee/checkout/summary/fee'
            },
            totals: quote.getTotals(),
            /**
             * Get formatted price
             * @returns {*|String}
             */
            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('amasty_extrafee').value;
                }
                return this.getFormattedPrice(price);
            },
            /**
             * @returns {string}
             */
            getMethods: function(){
                var title = '',
                    amastySegmentExtraFee = totals.getSegment('amasty_extrafee');
                if (this.totals() && amastySegmentExtraFee !== null &&  amastySegmentExtraFee.value > 0) {
                    title = amastySegmentExtraFee.title;
                }
                return title;
            },
            /**
             * @override
             */
            isDisplayed: function () {
                var amastySegmentExtraFee = totals.getSegment('amasty_extrafee'),
                    feeFieldSet = registry.get('checkout.sidebar.summary.block-amasty-extrafee-summary.amasty-extrafee-fieldsets')
                        ? registry.get('checkout.sidebar.summary.block-amasty-extrafee-summary.amasty-extrafee-fieldsets').elems()
                        : null,
                    isVisible = true;
                if (feeFieldSet) {
                    feeFieldSet.map(function (field) {
                        if (!field.visible()) {
                            isVisible = false;
                        }
                    })
                }

                if (this.totals()
                    && amastySegmentExtraFee !== null
                    && amastySegmentExtraFee.value > 0
                    && isVisible
                ) {
                    return true;
                }
                return false;
            }
        });
    }
);