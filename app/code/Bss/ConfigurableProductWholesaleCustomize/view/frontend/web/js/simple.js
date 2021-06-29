define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'mage/translate',
    'jquery/ui',
], function ($, priceUtils, $t) {
    "use strict";

    $.widget('bss.configurableproductwholesalesimple', {
        options: {
            fomatPrice: '',
            classes: {
                qtyClass: '#qty'
            },
            values: {
                totals: [],
                totalsQty: []
            },
            ids: {
                wrapper: 'product-options-wrapper',
                optionPrice: 'bss-option-price'
            },
            selectorTableRow: '.bss-table-row',
            html : []
        },

        _init: function () {
            var self = this;
            $('#qty').parents('.field.qty').hide();
            // $('.qty.bss-qty').val($('#qty').val());
            $('.product-info-main').on('change', '#qty', function() {
                self._CalcRowPrice($(this));
            });
            $('.bss-qty-simple').on('change', '.qty.bss-qty', function() {
                $('#qty').val($(this).val());
                $('#qty').change();
            });
        },

        _CalcRowPrice: function ($this) {
            var price = 0,
                qty = 0,
                bssOptionPrice,
                subtotal = 0,
                unitPrice = 0,
                tierPriceQty = "",
                self = this,
                tierPriceQtys = [];
            jQuery('.bss-tierprice').each(function(){
                tierPriceQtys.push($(this).attr('attributeqty'));
            });
            var i;
            if(parseFloat($this.val()) >= parseFloat(tierPriceQtys[0])) {
                for (i = 1; i < tierPriceQtys.length; i++) {
                    if (parseFloat($this.val()) < parseFloat(tierPriceQtys[i])) {
                        tierPriceQty = tierPriceQtys[i - 1];
                        break;
                    }
                    if (parseFloat($this.val()) >= parseFloat(tierPriceQtys[i - 1]) && parseFloat($this.val()) < parseFloat(tierPriceQtys[i])) {
                        tierPriceQty = tierPriceQtys[i - 1];
                        break;
                    }
                    if (parseFloat($this.val()) <= parseFloat(tierPriceQtys[i]) && parseFloat($this.val()) > parseFloat(tierPriceQtys[i - 1])) {
                        tierPriceQty = tierPriceQtys[i];
                        break;
                    }
                    if (parseFloat(i) == parseFloat(tierPriceQtys.length - 1) && parseFloat($this.val()) >= parseFloat(tierPriceQtys[i])) {
                        tierPriceQty = tierPriceQtys[i];
                        break;
                    }
                }
            }
            bssOptionPrice = parseFloat($('#bss-option-price'+tierPriceQty).val());
            if ($this.val() && $this.val() > 0) {
                qty = parseFloat($this.val());
            }
            subtotal = bssOptionPrice * qty;

            if(qty > 0)
                subtotal += parseFloat($('#bss-total-price').val());

            $('.bss-totals').html(self._getFormattedPrice(subtotal));
            $('.bss-totals-qty').html(qty);
            this._reloadUnitPrice($this);
        },

        _reloadUnitPrice: function ($this) {
            var price,
                oldPrice,
                optionPrice,
                self = this;
            optionPrice = parseFloat($('#'+self.options.ids.optionPrice).val());
            // 
            
            if (window.customerPricing && $('.login_price').length > 0) {
                var sku = window.customerPricing.sku;
                var special_prices = window.customerPricing.getSessionProductCustomerPricing(sku);
                var special_prices_empty = true;
                for(var pricekey in special_prices) {
                    if (special_prices[pricekey].length !== 0) {
                        special_prices_empty = false;
                        break;
                    }
                    
                }
                if (!special_prices || special_prices_empty) {
                    $('.whosale-specical').hide();
                    $('.bss-tierprice p').each(function(){
                        if (!$(this).hasClass("not_login") && !$(this).hasClass("general")) {
                            $(this).hide();
                            $('.mess-specical-price span').show();
                            $('.mess-specical-price').show();
                        }
                    })
                } else {
                    $('.whosale-specical').show();
                    $('.bss-tierprice p').each(function(){
                        if (!$(this).hasClass("not_login") && !$(this).hasClass("general")) {
                            $(this).show();
                            $('.mess-specical-price span').hide();
                            $('.mess-specical-price').hide();
                        }
                    })
                }
            }
            $('.bss-tierprice').each(function(){
                var sefl = this;
                $(this).find('p').each(function(){
                    if($(this).hasClass("not_login")) {
                        var tierPrice = 0;
                        optionPrice = parseFloat($('#'+self.options.ids.optionPrice + $(sefl).attr('attributeqty')+'_2').val());
                        price = parseFloat($(this).attr('data-amount'));
                        tierPrice = self._getFormattedPrice(optionPrice);
                        $(this).html(tierPrice);
                    } else if($(this).hasClass("general")) {
                        var tierPrice = 0;
                        optionPrice = parseFloat($('#'+self.options.ids.optionPrice + $(sefl).attr('attributeqty')+'_1').val());
                        price = parseFloat($(this).attr('data-amount'));
                        tierPrice = self._getFormattedPrice(optionPrice);
                        $(this).html(tierPrice);
                    }else {
                        var tierPrice = 0;
                        optionPrice = parseFloat($('#'+self.options.ids.optionPrice + $(sefl).attr('attributeqty')).val());
                        price = parseFloat($(this).attr('data-amount'));
                        tierPrice = self._getFormattedPrice(optionPrice);
                        $(this).html(tierPrice);
                    }
                })
            });
            jQuery('.bss-price-range .bss-price-from-value').text(jQuery('.bss-tierprice').last().find('p').last().text());
        },

        _getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, this.options.fomatPrice);
        },
    });
    return $.bss.configurableproductwholesalesimple;
});
