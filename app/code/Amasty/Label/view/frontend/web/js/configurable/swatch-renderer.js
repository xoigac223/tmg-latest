define([
    'jquery',
    'Amasty_Label/js/configurable/reload'
], function ($, reload) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            _loadMedia: function () {
                this.loadLabels();

                return this._super();
            },

            _LoadProductMedia: function () {
                this.loadLabels();

                return this._super();
            },

            loadLabels: function () {
                var productIds = this._CalcProducts(),
                    imageContainer = null;
                if (this.inProductList) {
                    imageContainer = this.element.closest('li.item').find(this.options.jsonConfig['label_category']);
                } else {
                    imageContainer = this.element.closest('.column.main').find(this.options.jsonConfig['label_product']);
                }
                imageContainer.find('.amasty-label-container').hide();
                if (typeof this.options.jsonConfig['label_reload'] != 'undefined'
                    && typeof productIds[0] != 'undefined'
                    && (!this.inProductList || this.options.jsonConfig['original_product_id'] != productIds[0])
                ) {
                    reload(imageContainer, productIds[0], this.options.jsonConfig['label_reload'], this.inProductList ? 1 : 0);
                }
            }
        });

        return $.mage.SwatchRenderer;
    }
});
