/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    'jquery',
    'underscore',
    'jquery/ui',
    'Magento_Swatches/js/swatch-renderer'
], function ($, _) {
    'use strict';
    
    /* jQuery load event */
    $(document).ready(function () {
        localStorage.setItem('processed', '');
    });
    
    $.widget('firebear.SwatchRenderer', $.mage.SwatchRenderer, {

        /**
         * Get default options values settings with either URL query parameters
         * @private
         */
        _getSelectedAttributes: function () {

            if (typeof this.options.jsonConfig.defaultValues !== 'undefined') {
                return this.options.jsonConfig.defaultValues;
            }

            var hashIndex = window.location.href.indexOf('#'),
                selectedAttributes = {},
                params;

            if (hashIndex !== -1) {
                params = $.parseQuery(window.location.href.substr(hashIndex + 1));

                selectedAttributes = _.invert(_.mapObject(_.invert(params), function (attributeId) {
                    var attribute = this.options.jsonConfig.attributes[attributeId];

                    return attribute ? attribute.code : attributeId;
                }.bind(this)));
            }

            return selectedAttributes;
        },

        /**
         * Emulate mouse click on all swatches that should be selected
         * @param {Object} [selectedAttributes]
         * @private
         */
        _EmulateSelected: function (selectedAttributes) {
            $.each(selectedAttributes, $.proxy(function (attributeCode, optionId) {
                var el = this.element.find('.' + this.options.classes.attributeClass +
                    '[attribute-code="' + attributeCode + '"] [option-id="' + optionId + '"]');

                if (el.hasClass('selected')) {
                    return;
                }

                el.trigger('click');
            }, this));
        },

        /**
         * Change product attributes.
         */
        _ReplaceData: function (simpleProductId, $widget) {
            if (typeof $widget.options.jsonConfig.customAttributes[simpleProductId] !== 'undefined') {
                $.each($widget.options.jsonConfig.customAttributes[simpleProductId], function (attributeCode, data) {
                    var $block = $(data.class);

                    if (typeof data.replace != 'undefined' && data.replace) {
                        if (data.value == '') {
                            $block.remove();
                        }

                        if ($block.length > 0) {
                            $block.replaceWith(data.value);
                        } else {
                            $(data.container).html(data.value);
                        }
                    } else {
                        if ($block.length > 0) {
                            $block.html(data.value);
                        }
                    }
                });
            }
        },

        /**
         * Event for swatch options
         *
         * @param $this
         * @param $widget
         * @private
         */
        _OnClick: function ($this, $widget) {
            /* Fix issue cannot add product to cart */
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $wrapper = $this.parents('.' + $widget.options.classes.attributeOptionsWrapper),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);
            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                $parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
                $this.attr('aria-checked', false);
            } else {
                $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
                $label.text($this.attr('option-label'));
                $input.val($this.attr('option-id'));
                $input.attr('data-attr-name', this._getAttributeCodeById(attributeId));
                $this.addClass('selected');
                if (typeof $widget._toggleCheckedAttributes !== "undefined") {
                    $widget._toggleCheckedAttributes($this, $wrapper);
                }
            }
            /**
            * Get simpleProduct Id by simple product URL
            */
            var currentURL = window.location.href;
            var simpleProductId = '';
            if (!localStorage.getItem('processed')) {
                var selectedOptionId = '', selectedLabel = '';
                if (typeof this.options.jsonConfig.urls !== 'undefined') {
                    $.each(this.options.jsonConfig.urls, function (productId, productUrl) {
                        if (productUrl == currentURL) {
                            simpleProductId = productId;
                            return true;
                        }
                    });
                }
                if (simpleProductId) {
                    $.each(this.options.jsonConfig.attributes, function () {
                        var item = this;
                        var allOptions = item.options;
                        $.each(allOptions, function (key, optionObj) {
                            var products = optionObj.products;
                            for (var i = 0; i < products.length; i++) {
                                var childProductId = optionObj.products[i];
                                if (simpleProductId === childProductId) {
                                   selectedOptionId = optionObj.id;
                                   selectedLabel = optionObj.label;
                                   var select = $('div[attribute-id="'+ item.id +'"]').find('select');
                                   if (select.find('option').length > 0) {
                                       select.val(selectedOptionId).trigger('change');
                                   } else {
                                        var parent = $('div[attribute-id="'+ item.id +'"]'),
                                        label = parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                                        input = parent.find('.' + $widget.options.classes.attributeInput);
                                        parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                                        parent.attr('option-selected', selectedOptionId);
                                        label.text(selectedLabel);
                                        $('input[name="super_attribute['+ item.id +']"]').val(selectedOptionId);
                                        $('.swatch-option[option-id='+selectedOptionId+']').addClass('selected');
                                   }
                                }
                            }
                        });
                    });
                }
            }
            
            $widget._Rebuild();

            if ($widget.element.parents($widget.options.selectorProduct)
                    .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
            ) {
                $widget._UpdatePrice();
            }

            /**
             * Update product data & url.
             * @author Firebear Studio <fbeardev@gmail.com>
             */
            var products = $widget._CalcProducts();

            /**
             * Do not replace data on category view page.
             * @see \Firebear\ConfigurableProducts\Plugin\Block\ConfigurableProduct\Product\View\Type::afterGetJsonConfig()
             */
            if (products.length && !this.options.jsonConfig.doNotReplaceData) {
                if (!simpleProductId) {
                    var simpleProductId = products[0];
                }
                /**
                 * Change product attributes.
                 */
                $widget._ReplaceData(simpleProductId, this);
                /* Update input type hidden - fix base image doesn't change when choose option */
                if (simpleProductId && document.getElementsByName('product').length) {
                    document.getElementsByName('product')[0].value = simpleProductId;
                }
                /**/
                var config = this.options.jsonConfig;
                require(['jqueryHistory'], function () {

                    if (typeof config.urls !== 'undefined' && typeof config.urls[simpleProductId] !== 'undefined') {
                        var url = config.urls[simpleProductId];
                        var title = null;
                        if (config.customAttributes[simpleProductId].name.value !== 'undefined') {
                            title = config.customAttributes[simpleProductId].name.value;
                        }
                        History.replaceState(null, title, url);
                    }
                });
            }

            $widget._LoadProductMedia();
            $input.trigger('change');
            localStorage.setItem('processed',true);
        },

        /**
         * Event for select
         *
         * @param $this
         * @param $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
            /* Fix issue cannot add product to cart */
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);
            if ($widget.productForm.length > 0) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }
            /**/

            if ($this.val() > 0) {
                $parent.attr('option-selected', $this.val());
                $input.val($this.val());
            } else {
                $parent.removeAttr('option-selected');
                $input.val('');
            }

            $widget._Rebuild();
            $widget._UpdatePrice();

            /**
             * Update product data & url.
             * @author Firebear Studio <fbeardev@gmail.com>
             */
            var products = $widget._CalcProducts();
            /**
             * Do not replace data on category view page.
             * @see \Firebear\ConfigurableProducts\Plugin\Block\ConfigurableProduct\Product\View\Type::afterGetJsonConfig()
             */
            if (products.length && !this.options.jsonConfig.doNotReplaceData) {
                var simpleProductId = products[0];
                /**
                 * Change product attributes.
                 */
                $widget._ReplaceData(simpleProductId, this);
                /**
                 * Update input type hidden - fix base image doesn't change when choose option
                 */
                if (simpleProductId && document.getElementsByName('product').length) {
                    document.getElementsByName('product')[0].value = simpleProductId;
                }
                var config = this.options.jsonConfig;
                require(['jqueryHistory'], function () {
                    if (typeof config.urls !== 'undefined' && typeof config.urls[simpleProductId] !== 'undefined') {
                        var url = config.urls[simpleProductId];
                        var title = null;
                        if (config.customAttributes[simpleProductId].name.value !== 'undefined') {
                            title = config.customAttributes[simpleProductId].name.value;
                        }
                        History.replaceState(null, title, url);
                    }
                });
            }

            $widget._LoadProductMedia();
            $input.trigger('change');
        },
        
        /**
         * Get human readable attribute code (eg. size, color) by it ID from configuration
         *
         * @param {Number} attributeId
         * @returns {*}
         * @private
         */
        _getAttributeCodeById: function (attributeId) {
            var attribute = this.options.jsonConfig.attributes[attributeId];

            return attribute ? attribute.code : attributeId;
        },

        /**
         * Update total price
         *
         * @private
         */
        _UpdatePrice: function () {
            var $widget = this,
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                options = _.object(_.keys($widget.optionsMap), {}),
                result;

            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');

                options[attributeId] = $(this).attr('option-selected');
            });

            result = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, options)];

            $productPrice.trigger(
                'updatePrice',
                {
                    'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                }
            );

            if (typeof result !== 'undefined' && result.oldPrice.amount !== result.finalPrice.amount) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }
        },
    });

    return $.firebear.SwatchRenderer;
});
