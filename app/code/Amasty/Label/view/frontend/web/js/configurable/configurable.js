define([
    'jquery',
    'Amasty_Label/js/configurable/reload'
], function ($, reload) {
    'use strict';

    return function (widget) {
        $.widget('mage.configurable', widget, {
            _changeProductImage: function () {
                var productId = this.simpleProduct,
                    imageContainer = null;
                if (this.inProductList) {
                    imageContainer = this.element.closest('li.item').find(this.options.spConfig['label_category']);
                } else {
                    imageContainer = this.element.closest('.column.main').find(this.options.spConfig['label_product']);
                }
                imageContainer.find('.amasty-label-container').hide();
                if (typeof this.options.spConfig['label_reload'] != 'undefined'
                    && (!this.inProductList || this.options.spConfig['original_product_id'] != productId)
                ) {
                    reload(imageContainer, productId, this.options.spConfig['label_reload'], this.inProductList ? 1 : 0);
                }

                return this._super();
            }
        });

        return $.mage.configurable;
    }
});
