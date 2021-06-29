define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'mage/translate',
    'jquery/ui',
], function ($, priceUtils, $t) {
    "use strict";
    $.widget('bss.configurableproductwholesale', {
        options: {
            jsonChildInfo: {},
            jsonChildName: {},
            fomatPrice: '',
            jsonPriceTableOrdering: {},
            jsonEditInfo: {},
            jsonSwatchConfig: {},
            // tierPriceAdvanced: false,
            jsonSystemConfig: {},
            classes: {
                optionClass: 'swatch-option',
                selectClass: 'super-attribute-select',
                qtyClass: 'bss-qty'
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
            selectorManager: '[data-role=swatch-options]',
            html : []
        },

        _init: function () {
            if (this.options.jsonPriceTableOrdering !== '' && this.options.jsonChildInfo !== '') {
                this._EventListener();
            } else {
                console.log('Bss Table Ordering : Error');
            }
        },

        _EventListener: function () {
            var self = this,
                opt = self.options,
                selectorManager = opt.selectorManager;

            $('#' + opt.ids.wrapper).on('click','.' + self.options.classes.optionClass, function () {
                if ($(this).hasClass('disabled') || !$(this).hasClass('selected') || $(this).hasClass('bss-table-row-attr')) {
                    return;
                }
                self._RerenderTableOrdering();
            });

            $('#' + opt.ids.wrapper).on('change','.' + self.options.classes.selectClass, function () {
                if ($(this).val() == 0 || $(this).parent().parent().hasClass('bss-last-select')) {
                    return;
                }
                self._RerenderTableOrdering();
            });

            if ($('#bss-no-swatch').length) {
                self._loadNoSwatch();
            }

            $('#' + opt.ids.wrapper).on('change','#' + self.options.ids.optionPrice, function () {
                var _this = this;
                $('.' + self.options.classes.qtyClass).each(function () {
                    self._CalcRowPrice($(this));
                    if ($(this).val() > 0) {
                        self._loadDetailTotal($(this));
                    }
                });

                self._reloadRangePrice();
            });
            self._RenderTableOrdering();
            if (self.options.jsonSystemConfig.textColor) {
                $('#bss-ptd-table thead th').css('color', '#'+self.options.jsonSystemConfig.textColor);
            }
            if (self.options.jsonSystemConfig.backGround) {
                $('#bss-ptd-table thead th').css('background-color', '#'+self.options.jsonSystemConfig.backGround);
            }
            $('.product-options-bottom .box-tocart .field.qty').remove();
            self.element.find('.bss-totals').html(self._getFormattedPrice(0));
            self.element.find('.bss-totals-qty').html(0);

            $('#' + opt.ids.wrapper).on('change','.' + self.options.classes.qtyClass, function () {
                var _this = this;
                self._CalcRowPrice($(_this));
                $('.' + self.options.classes.qtyClass).each(function () {
                    self._CalcRowPrice($(this));
                });
                self._loadDetailTotal($(_this));
            });

            self.element.on('keyup mousewheel','.' + self.options.classes.qtyClass, function () {
                self._checkError($(this));
            });

            $(this.options.selectorTableRow).hover(function () {
                if ($(this).find('.bss-tier-detailed').length > 0) {
                    $(this).find('.bss-tier-detailed').removeClass('bss-hidden');
                }
            }, function () {
                if ($(this).find('.bss-tier-detailed').length > 0) {
                    $(this).find('.bss-tier-detailed').addClass('bss-hidden');
                }
            });
            $('#' + opt.ids.wrapper).on("click", ".bss-remove", function() {
                var productId = $(this).attr("product-id");
                $('.bss-qty').each(function() {
                    if($(this).attr("data-product-id") === productId) {
                        $(this).val(0);
                    }
                })
                $('.bss-qty').change();
            });
        },

        _RerenderTableOrdering: function () {
            var self = this;
            $(self.options.selectorTableRow).each(function () {
                var $this = $(this),
                    countOption = 0,
                    countTmpOption = 0,
                    countSelect = 0,
                    countTmpSelect = 0,
                    selectorData = $this.find('.bss-data');
                if ($('.' + self.options.classes.optionClass).length > 0) {
                    $('.' + self.options.classes.optionClass).each(function () {
                        var id,
                            attrId;
                        if ($(this).hasClass('selected') && !$(this).hasClass('bss-table-row-attr')) {
                            id = $(this).closest('.swatch-attribute').attr('attribute-id');
                            attrId = $(this).attr('option-id');

                            if (selectorData.attr('data-option-'+id) == attrId) {
                                countTmpOption++;
                            }
                            countOption++;
                        }

                    });
                }

                if ($('#' + self.options.ids.wrapper + ' .' + self.options.classes.selectClass).length > 0) {
                    $('#' + self.options.ids.wrapper + ' .' + self.options.classes.selectClass).each(function () {
                        var id,
                            attrId;
                        if ($(this).val() != 0) {
                            id = $(this).closest('.swatch-attribute').attr('attribute-id');
                            attrId = $(this).val();
                            if (selectorData.attr('data-option-'+id) == attrId) {
                                countTmpSelect++;
                            }
                            countSelect++;
                        }
                    });
                }
                var productId = $this.find('.bss-qty-col .bss-qty').attr('data-product-id');
                if (countOption == countTmpOption && countSelect == countTmpSelect) {
                    self._ReplaceNameProduct(productId);
                    $this.removeClass('bss-hidden');
                    $('.bss-inventory .product-id-'+productId).removeClass('bss-hidden');
                } else {
                    $('.bss-inventory .product-id-'+productId).addClass('bss-hidden');
                    $this.addClass('bss-hidden');
                }
            });
            $('#bss-ptd-table').removeClass('bss-hidden');
        },

        _ReplaceNameProduct: function (productId) {
            var childName = this.options.jsonChildName;
            if (childName[productId]!= 'undefined') {
                $('.product-info-main .product .page-title span.base').text(childName[productId]);
            }else{
                $('.product-info-main .product .page-title span.base').text(childName['mainproduct']);
            }
        },

        _RenderTableOrdering: function () {
            var tableContent = '',
                array = {},
                inventory = '',
                _this = this,
                jsonChildInfo = this.options.jsonChildInfo,
                sortOrder = 0,
                option_id = 0,
                subtotalSelector = '',
                login = false,
                loginWhosale = false,
                hasLogin = false,
                hasLoginWhosale = false,
                cache = {};
            $.each(jsonChildInfo, function (key3) {
                var qty = 0,
                    html = '',
                    subtotalClass = '',
                    priceClass = '',
                    availabilityClass = '',
                    skuClass = '',
                    tierPriceClass = '',
                    mobileClass = ' bss-hidden-480',
                    tabletClass = ' bss-hidden-1024',
                    itemSelector = '',
                    disabledClass = '',
                    disabled = '',
                    detailedNote = '',
                    detailedPrice = '',
                    productId = Object.keys(jsonChildInfo[key3])[0],
                    self = jsonChildInfo[key3][productId];
                if (!self.status_stock) {
                    disabledClass = 'bss-disabled';
                    disabled = 'disabled';
                }

                if (typeof _this.options.jsonEditInfo.product != "undefined" && _this.options.jsonEditInfo.product[productId]) {
                    qty = _this.options.jsonEditInfo.product[productId];
                    itemSelector = '<input type="hidden" name="bss-item['+productId+']" value="'+_this.options.jsonEditInfo.item[productId]+'" />';
                }

                if (_this.options.jsonSystemConfig.showSubTotal) {
                    if (_this.options.jsonSystemConfig.mobile && !_this.options.jsonSystemConfig.mobile.subtotal) {
                        subtotalClass += mobileClass;
                    }
                    if (_this.options.jsonSystemConfig.tablet && !_this.options.jsonSystemConfig.tablet.subtotal) {
                        subtotalClass += tabletClass;
                    }
                    subtotalSelector = '<td class="bss-subtotal-'+productId+subtotalClass+' bss-hidden">';
                    subtotalSelector += _this._getFormattedPrice(0)+'</td>';
                }

                inventory += '<div class="bss-hidden product-id-'+productId+'">';
                html += '<tr class="bss-table-row '+disabledClass+'">';
                $.each(self, function (key2) {
                    var class_not_login = '';
                    var class_login = '';
                    var class_whosale = '';
                    if(key2 == "option_id") {
                        option_id = self.option_id;
                        return true;
                    }

                    if(key2 == "sort_order") {
                        sortOrder = self.sort_order;
                        return true;
                    }

                    if (key2 == 'other' || key2 == 'status_stock' || key2 == 'attribute_code') {
                        return;
                    }
                    if (key2 == 'option') {
                        var data = '',
                            optionId = '';
                        $.each(this, function (key,val) {
                            optionId = key.replace('data-option-', '');
                            data += key + '="' + val + '" ';
                            html += '<input class="product-id-'+productId+'" type="hidden" name="bss_super_attribute['+productId+']['+optionId+']" data-option-id="'+optionId+'" value="'+val+'" />';
                        });
                        html += '<input type="hidden" class="bss-data" ' + data +' value="" />';
                    } else if (key2 == 'price') {
                        if (_this.options.jsonSystemConfig.mobile && !_this.options.jsonSystemConfig.mobile.unit_price) {
                            priceClass += mobileClass;
                        }
                        if (_this.options.jsonSystemConfig.tablet && !_this.options.jsonSystemConfig.tablet.unit_price) {
                            priceClass += tabletClass;
                        }
                        html += '<td class="bss-unitprice-'+productId+priceClass+'">';
                        html += '<div class="bss-price" data-amount="'+this.min_tier_price+'">' + _this._getFormattedPrice(this.min_tier_price) + '</div>';
                        if (this.old_price) {
                            html += '<div class="bss-old-price" data-amount="'+this.old_price+'">' + _this._getFormattedPrice(this.old_price) + '</div>';
                        }
                        html += '</td>';
                    } else if (key2 == 'attribute') {
                        html += '';
                    } else if (key2 == 'qty_stock') {
                        inventory += '<strong>Inventory: ' + this + '</strong></div>';
                    } else if (key2 == 'sku') {
                        if (_this.options.jsonSystemConfig.mobile && !_this.options.jsonSystemConfig.mobile.sku) {
                            skuClass += mobileClass;
                        }
                        if (_this.options.jsonSystemConfig.tablet && !_this.options.jsonSystemConfig.tablet.sku) {
                            skuClass += tabletClass;
                        }
                        if (skuClass) {
                            html += '<td class="'+skuClass+'">';
                        } else {
                            html += '<td>';
                        }
                        html +=this + '</td>';
                    } else if(key2 == 'tier_price') {
                        for (var k in this){
                            if(this[k]["login"] && parseFloat(this[k]["login"]) > 0) {
                                login = true;
                                class_not_login = 'class="old-price not_login"';
                                class_login = 'class="old-price general"';
                            }
                            if(this[k]["whosale"] && parseFloat(this[k]["whosale"]) > 0) {
                                loginWhosale = true;
                                class_not_login = 'class="old-price not_login whosale"';
                            }
                        }
                        if(login) {
                            if(loginWhosale) {
                                html += '<td class="login_price"><p>'+ $t('Catalog') +'</p><p>'+ $t('Net') +'</p><p class="whosale-specical">'+ $t('Your Price') +'</p>';
                            }else {
                                html += '<td class="login_price"><p>'+ $t('Catalog') +'</p><p>'+ $t('Net') +'</p>';
                            }
                        }
                        for (var k in this){
                            html += '<td class="bss-tierprice" attributeQty="' + k + '">';
                            if(this[k]["not_login"] || this[k]["whosale"] || this[k]["login"]) {
                                cache["not_login"] = this[k]["not_login"];
                                if(!hasLoginWhosale) {
                                    cache["whosale"] = this[k]["not_login"];
                                }
                                if(!hasLogin) {
                                    cache["login"] = this[k]["not_login"];
                                }
                                if(this[k]["whosale"]) {
                                    hasLoginWhosale = true;
                                    cache["whosale"] = this[k]["whosale"];
                                    if(!hasLogin) {
                                        cache["login"] = this[k]["whosale"];
                                    }
                                }
                                if(this[k]["login"]) {
                                    hasLogin = true;
                                    cache["login"] = this[k]["login"];
                                }
                                html +='<p '+class_not_login + ' data-amount="'+cache["not_login"]+'">' + _this._getFormattedPrice(cache["not_login"]) + '</p>';
                                if(loginWhosale) {
                                    html +='<p '+class_login+' data-amount="'+cache["whosale"]+'">' + _this._getFormattedPrice(cache["whosale"]) + '</p>';
                                }
                                if(login) {
                                    html +='<p data-amount="'+cache["login"]+'">' + _this._getFormattedPrice(cache["login"]) + '</p>';
                                }
                                html +='</td>';
                            }
                        }
                    } else {
                        html += '<td>' + this + '</td>';
                    }
                });

                if (self.other) {
                    if (self.other.min_qty) {
                        itemSelector += '<input type="hidden" class="bss-min-qty" value="'+self.other.min_qty+'" />';
                    }
                    if (self.other.max_qty) {
                        itemSelector += '<input type="hidden" class="bss-max-qty" value="'+self.other.max_qty+'" />';
                    }
                }
                detailedNote += '<div generated="true" class="bss-note-detailed bss-hidden mage-error"></div>';

                html += subtotalSelector
                    + '<td class="bss-qty-col">'
                    + '<input type="number" name="bss-qty['+productId+']" maxlength="12" value="'+qty+'" title="Qty" class="input-text qty bss-qty" data-product-id="'+productId+'" '+disabled+'>'
                    + detailedPrice
                    + detailedNote
                    + itemSelector
                    + '</td>'
                    +'</tr>';
                array[sortOrder+productId] = html;
            });
            $(".bss-inventory").append(inventory);
            var keys = [], k, i, len;

            for (var k in array){
                if (array.hasOwnProperty(k)) {
                    keys.push(k);
                }
            }

            keys.sort();

            len = keys.length;

            for (i = 0; i < len; i++) {
                k = keys[i];
                _this.element.append(array[k]);
                tableContent += array[k];
            }
            // if(login) {
            //     _this.element.append("<div>*Pricing shown here is your Net Price per unit.</div>");
            // }
            //_this.element.append(tableContent);
            if ($('#bss-no-swatch').length && $('#bss-no-swatch').val() == 1) {
                _this.element.removeClass('bss-hidden');
            }
        },

        _CalcRowPrice: function ($this) {
            var productId,
                price = 0,
                qty = 0,
                bssOptionPrice,
                subtotal = 0,
                totalQty = 0,
                unitPrice = 0,
                tierPriceQty = "",
                tierPriceQtys = [];
            if (!_.isEmpty(this.options.jsonPriceTableOrdering)) {
                productId = $this.attr('data-product-id');
                jQuery('.bss-table-row').first().find('.bss-tierprice').each(function(){
                    tierPriceQtys.push($(this).attr('attributeqty'));
                });
                var i;
                for(i=1;i<tierPriceQtys.length;i++) {
                    if (parseFloat($this.val()) < parseFloat(tierPriceQtys[i])) {
                        tierPriceQty = tierPriceQtys[i-1];
                        break;
                    }
                    if(parseFloat($this.val()) >= parseFloat(tierPriceQtys[i-1]) && parseFloat($this.val()) < parseFloat(tierPriceQtys[i])) {
                        tierPriceQty = tierPriceQtys[i-1];
                        break;
                    }
                    if(parseFloat($this.val()) <= parseFloat(tierPriceQtys[i]) && parseFloat($this.val()) > parseFloat(tierPriceQtys[i-1])) {
                        tierPriceQty = tierPriceQtys[i];
                        break;
                    }
                    if(parseFloat(i) == parseFloat(tierPriceQtys.length-1) && parseFloat($this.val()) >= parseFloat(tierPriceQtys[i])) {
                        tierPriceQty = tierPriceQtys[i];
                        break;
                    }
                }
                bssOptionPrice = parseFloat($('#bss-option-price'+tierPriceQty).val());
                if ($this.val() && $this.val() > 0) {
                    qty = parseFloat($this.val());
                }
                this.options.values.totalsQty[productId] = qty;
                totalQty = this.options.values.totalsQty.reduce(function (total, num) {
                    return total + num;
                });
                for (var key in this.options.jsonPriceTableOrdering[productId]) {
                    if (!this.options.jsonSystemConfig.tierPriceAdvanced && qty >= parseFloat(key)) {
                        price = parseFloat(this.options.jsonPriceTableOrdering[productId][key]);
                    } else if (this.options.jsonSystemConfig.tierPriceAdvanced && totalQty >= parseFloat(key)) {
                        price = parseFloat(this.options.jsonPriceTableOrdering[productId][key]);
                    }
                }
                if (price && price > 0) {
                    var pricing = $('#bss-check-type-pricing').val();
                    if (pricing && pricing == 1) {
                        subtotal = bssOptionPrice * qty;
                        unitPrice = price;
                    } else {
                        subtotal = (bssOptionPrice) * qty;
                        unitPrice = price;
                    }
                } else {
                    unitPrice = this.options.jsonPriceTableOrdering[productId][1];
                }
                if(qty > 0)
                    subtotal += parseFloat($('#bss-total-price').val());
                this.options.values.totals[productId] = subtotal;

                this.element.find('.bss-subtotal-'+productId).html(this._getFormattedPrice(subtotal)+'<span class="bss-remove action delete" product-id="'+productId+'" title="Remove selected"></span>');
                //this.element.find('.bss-unitprice-'+productId+' .bss-price').attr('data-amount', unitPrice);
                this.element.find('.bss-totals').html(this._getFormattedPrice(this.options.values.totals.reduce(function (total, num) {
                    return total + num;
                })));
                this.element.find('.bss-totals-qty').html(totalQty);
                $('#qty').val(parseInt(totalQty));
                this._reloadUnitPrice($this);
            }
        },

        _reloadUnitPrice: function ($this) {
            var price,
                oldPrice,
                optionPrice,
                self = this,
                oldPriceElem = $this.closest('.bss-table-row').find('.bss-old-price'),
                element = $this.closest('.bss-table-row').find('.bss-price');
            optionPrice = parseFloat($('#'+self.options.ids.optionPrice).val());
            var unitPrice = 0;
            price = parseFloat(element.attr('data-amount'));
            if (oldPriceElem.length > 0) {
                oldPrice = parseFloat(oldPriceElem.attr('data-amount'));
                oldPriceElem.html(self._getFormattedPrice(oldPrice + optionPrice));
            }
            unitPrice = self._getFormattedPrice(price + optionPrice);
            element.html(unitPrice);

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
            $this.closest('.bss-table-row').find('.bss-tierprice').each(function(){
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
            jQuery('.bss-price-range .bss-price-from-value').text($this.closest('.bss-table-row').find('.bss-tierprice').last().find('p').last().text());
        },

        _getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, this.options.fomatPrice);
        },

        _reloadRangePrice: function () {
            var optionPrice,
                priceFrom,
                priceTo,
                priceUnit;
            optionPrice = parseFloat($('#'+this.options.ids.optionPrice).val());
            if ($('.bss-price-from').length > 0) {
                priceFrom = parseFloat($('.bss-price-from-value').attr('data-price-amount'));
                $('.bss-price-from-value').html(this._getFormattedPrice(priceFrom + optionPrice));
            }
            if ($('.bss-price-to').length > 0) {
                priceTo = parseFloat($('.bss-price-to-value').attr('data-price-amount'));
                $('.bss-price-to-value').html(this._getFormattedPrice(priceTo + optionPrice));
            }
            if ($('.bss-price-unit').length > 0) {
                priceUnit = parseFloat($('.bss-price-unit-value').attr('data-price-amount'));
                $('.bss-price-unit-value').html(this._getFormattedPrice(priceUnit + optionPrice));
            }
        },

        _checkError: function ($this) {
            var mess = '',
                minQty,
                maxQty,
                qty;
            minQty = parseFloat($this.closest('.bss-qty-col').find('.bss-min-qty').val());
            maxQty = parseFloat($this.closest('.bss-qty-col').find('.bss-max-qty').val());
            qty = parseFloat($this.val());
            if (!qty && qty != 0) {
                mess += '<div>'+$t("This is a required field.")+'</div>'
            }
            if (qty && qty < 0) {
                mess += '<div>'+$t("Please enter a number 0 or greater in this field.")+'</div>'
            }
            if (minQty && qty && qty > 0 && qty < minQty) {
                mess += '<div>'+$t("The fewest you may purchase is %1.").replace('%1', minQty)+'</div>'
            }
            if (maxQty && qty && qty > maxQty) {
                mess += '<div>'+$t("The most you may purchase is %1.").replace('%1', maxQty)+'</div>'
            }
            if (mess != '') {
                $this.closest('.bss-qty-col').find('.bss-note-detailed').html(mess).removeClass('bss-hidden');
            } else {
                $this.closest('.bss-qty-col').find('.bss-note-detailed').addClass('bss-hidden');
            }
        },

        _loadDetailTotal: function ($this) {
            var self = this,
                optionConfig,
                jsonChildInfo = this.options.jsonChildInfo,
                _this = this,
                id,
                attributeId,
                type,
                value,
                label,
                attr,
                text,
                attributeIdSelect,
                optionIdSelect,
                html = '',
                detailedEl,
                qty = 0,
                optionClass = self.options.classes.optionClass;
            if ($('#bss-no-swatch').length) {
                detailedEl = $('#' + self.options.ids.wrapper + ' .swatch-attribute:first');
            } else {
                detailedEl = $('[data-role=swatch-options] .swatch-attribute:first');
            }
            attributeId = detailedEl.attr('attribute-id');
            id = $this.closest('.bss-table-row').find('.bss-data').attr('data-option-'+attributeId);
            self.element.find('tbody .bss-data[data-option-'+attributeId+'='+id+']').each(function () {
                if (!$(this).closest('.bss-table-row').hasClass('bss-disabled')) {
                    var qtyCur = parseFloat($(this).closest('.bss-table-row').find('.bss-qty').val());
                    if (!isNaN(qtyCur) && parseFloat(qtyCur) > 0) {
                        qty += qtyCur;
                    }
                }
            });

            if (qty == 0) {
                self.element.find('tfoot [data-attribute-'+attributeId+'='+id+']').closest('tr').remove();
            } else {
                optionConfig = self.options.jsonSwatchConfig[attributeId];
                if (self.element.find('tfoot [data-attribute-'+attributeId+'='+id+']').length == 0) {
                    html += '<tr><td data-attribute-'+attributeId+'="'+id+'">';
                    if (typeof optionConfig != "undefined") {
                        type = parseInt(optionConfig[id].type, 10);
                        value = optionConfig[id].hasOwnProperty('value') ? optionConfig[id].value : '';
                        label = optionConfig[id].label ? optionConfig[id].label : '';
                        attr =
                            ' option-type="' + type + '"' +
                            ' option-id="' + id + '"' +
                            ' option-label="' + label + '"';
                        if (type === 0) {
                            // Text
                            html += '<div class="' + optionClass + ' text" ' + attr + '>' + (value ? value : label) +
                                '</div>';
                        } else if (type === 1) {
                            // Color
                            html += '<div class="' + optionClass + ' color" ' + attr +
                                '" style="background: ' + value +
                                ' no-repeat center; background-size: initial;">' + '' +
                                '</div>';
                        } else if (type === 2) {
                            // Image
                            html += '<div class="' + optionClass + ' image" ' + attr +
                                '" style="background: url(' + value + ') no-repeat center; background-size: initial;">' + '' +
                                '</div>';
                        } else if (type === 3) {
                            // Clear
                            html += '<div class="' + optionClass + '" ' + attr + '></div>';
                        } else {
                            // Defaualt
                            html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
                        }
                    } else {
                        attributeIdSelect = detailedEl.attr('attribute-id');
                        optionIdSelect = $this.closest('.bss-table-row').find('.bss-data').attr('data-option-'+attributeIdSelect);
                        if ($('#bss-no-swatch').length) {
                            text = detailedEl.find('option[value='+optionIdSelect+']').text();
                        } else {
                            text = detailedEl.find('.swatch-select option[option-id='+optionIdSelect+']').text();
                        }
                        html += '<div>' + text + '</div>';
                    }
                    var productId = $this.attr('data-product-id');
                    var firstRowTotal = $('.bss-subtotal-'+productId+'').html();
                    html += '</td>';
                    html += '<td class="bss-detailed-total" data-attribute-'+attributeId+'="'+id+'">'+qty+'</td>';
                    if (_this.options.jsonSystemConfig.showSubTotal) {
                        html += '<td colspan="4" class="bss-subtotal-'+productId+'">'+firstRowTotal+'</td>';
                    }
                    html += '</tr>';
                    self.element.find('tfoot').prepend(html);
                } else {
                    self.element.find('tfoot .bss-detailed-total[data-attribute-'+attributeId+'='+id+']').text(qty);
                }
            }
        },

        _loadNoSwatch: function () {
            var self = this,
                editInfo = self.options.jsonEditInfo;
            self.element.on('click','.bss-table-row-attr', function () {
                var optionId;
                if ($(this).hasClass('selected')) {
                    $(this).removeAttr('option-selected').removeClass('selected');
                } else {
                    optionId = $(this).attr('option-id');
                    $('#bss-ptd-table').find('.selected').removeClass('selected');
                    $(this).addClass('selected');
                    $('.bss-last-select .super-attribute-select').val(optionId).change();
                }
            });
            setTimeout(function () {
                if (_.isEmpty(editInfo)) {
                    $('#' + self.options.ids.wrapper + ' .super-attribute-select').each(function () {
                        var _this = this;
                        var value = $(_this).find('option:eq(1)').val();
                        $(_this).val(value).change();
                    });
                } else {
                    var ele = $('#' + self.options.ids.wrapper + ' .bss-last-select').prev().find('.super-attribute-select');
                    if (ele) {
                        ele.change();
                    }
                    if ($('.product-custom-option').length > 0) {
                        $('.product-custom-option').change();
                    } else {
                        $('.bss-qty').change();
                    }
                }
            }, 1000);
        },
    });
    return $.bss.configurableproductwholesale;
});
