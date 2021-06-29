/**
 * Copyright © 2016 ITORIS INC. All rights reserved.
 * See license agreement for details
 */
 
if (!Itoris) {
    var Itoris = {};
}

Itoris.PriceFormula = {
    initialize : function(conditions, optionsData, specialPrice, dataBySku, productId, conversionRate, taxInfo, tierPrices) {
		if (!jQuery.mage || (!jQuery.mage.priceOptions && optionsData.length > 0) || !this.getPriceBox().priceBox) {
			var _this = this;
			//wait until priceOptions widget is loaded and initialized
			setTimeout(function(){_this.initialize(conditions, optionsData, specialPrice, dataBySku, productId, conversionRate, taxInfo, tierPrices);}, 200);
			return;
		}
        this.conditions = conditions;
        this.optionsData = optionsData;
		this.specialPrice = specialPrice;
		this.dataBySku = dataBySku;
        this.tierPrices = tierPrices;
		this.productId = productId;
		this.priceFormulaCurrencyConversionRate = conversionRate;
        this.priceFormulaTaxInfo = taxInfo;
		this.configurableConfig = {};
	
		if (!this.conditions.length /*|| !window['optionsPrice' + productId] || !window['optionsPrice' + productId].reload*/) return this;
		
		//if (!window['optionsPrice' + productId].productId) window['optionsPrice' + productId] = new Product.OptionsPrice(window.priceFormulaDefaultProductJsonConfig);
		
		for(var i=0; i<this.optionsData.length; i++) {
			if (this.optionsData[i].values) {
				for(var key in this.optionsData[i].values) {
					var option = this.optionsData[i].values[key];
					if (option.sku) this.dataBySku['{'+option.sku+'}'] = {type: this.optionsData[i].type, id: option.id};
				}
			} else if (this.optionsData[i].sku) {
				this.dataBySku['{'+this.optionsData[i].sku+'}'] = {type: this.optionsData[i].type, id: this.optionsData[i].id};
			}
		}
		
        this.getConfigurableOptionsConfig();

        var curObj = this;
        if ($('qty')) Event.observe($('qty'), 'change', function(){curObj.getPriceBox().trigger('updatePrice');});
        if ($('itoris_dynamicoptions_qty')) Event.observe($('itoris_dynamicoptions_qty'), 'change', function(){
			$('qty').value = $('itoris_dynamicoptions_qty').value;
			curObj.getPriceBox().trigger('updatePrice');
		});
		
		this.getPriceBox().on('reloadPrice', function(ev){
			var basePriceObj = jQuery('#itoris_dynamicproductoptions_popup_price [data-price-type="basePrice"] .price');
            if (!basePriceObj[0]) basePriceObj = jQuery('.product-info-main [data-role="priceBox"][data-product-id="'+curObj.productId+'"] [data-price-type="basePrice"] .price');
			var finalPriceObj = jQuery('#itoris_dynamicproductoptions_popup_price [data-price-type="finalPrice"] .price');
			if (!finalPriceObj[0]) finalPriceObj = jQuery('.product-info-main [data-role="priceBox"][data-product-id="'+curObj.productId+'"] [data-price-type="finalPrice"] .price');
            
			if (!basePriceObj[0]) basePriceObj[0] = finalPriceObj[0];
			
			var baseInitialAmount = basePriceObj[0] ? jQuery(basePriceObj[0]).closest('[data-price-type="basePrice"]').attr('data-price-amount') : 0;
			var finalInitialAmount = finalPriceObj[0] ? jQuery(finalPriceObj[0]).closest('[data-price-type="finalPrice"]').attr('data-price-amount') : 0;

			var taxRate = curObj.priceFormulaTaxInfo.priceAlreadyIncludesTax ? 1 / curObj.priceFormulaTaxInfo.taxRate : curObj.priceFormulaTaxInfo.taxRate;
			
            var decimalSymbol = curObj.getPriceBox().data('magePriceBox').options.priceConfig.priceFormat.decimalSymbol;
			var price = (baseInitialAmount ? basePriceObj[0] : finalPriceObj[0]).innerHTML;
            price = price.replace(/[^0-9]+/g,"") / (price.indexOf(decimalSymbol) > -1 ? 100 : 1);

			curObj.initialPrice = baseInitialAmount ? baseInitialAmount : finalInitialAmount;
            
            curObj.tierPrice = curObj.initialPrice;
            if ($('qty').value - 0 > 0) {
                for(var i=0; i<curObj.tierPrices.length; i++) {
                    if (curObj.tierPrices[i].qty <= $('qty').value - 0) curObj.tierPrice = curObj.tierPrices[i].price;
                }
            }

			var productCurrentPrice = price;
			curObj.configuredPrice = price;

			var priceForCompare = 0, multiplyByQty = false;
			for (var i = 0; i < conditions.length; i++) {
				var conditionData = conditions[i];
				this.isRightCondition = false;
				for (var j = 0; j < conditionData.length; j++) {
					if (!this.isRightCondition) {
						var condition = curObj.parseCondition(conditionData[j].condition, 0, price / curObj.priceFormulaCurrencyConversionRate, curObj.initialPrice, productCurrentPrice / curObj.priceFormulaCurrencyConversionRate);
						var priceCondition = curObj.parseCondition(conditionData[j].price, 0, price / curObj.priceFormulaCurrencyConversionRate, curObj.initialPrice, productCurrentPrice / curObj.priceFormulaCurrencyConversionRate);								

						eval("if ("+condition+") {this.isRightCondition = true; priceForCompare ="+ priceCondition +";}");
						
						if (priceForCompare > 0) productCurrentPrice = priceForCompare * curObj.priceFormulaCurrencyConversionRate;
						multiplyByQty = !!parseInt(conditionData[j].apply_to_total) && !parseInt(conditionData[j].frontend_total);
					} else {
						continue;
					}
				}
			}

			curObj.finalPrice = priceForCompare > 0 ? priceForCompare * curObj.priceFormulaCurrencyConversionRate / (multiplyByQty && parseFloat($('qty').value) > 0 ? $('qty').value : 1) : price;
			//curObj.finalPrice = curObj.finalPrice.toFixed(2);
			
			//var tierObj = jQuery('.product-info-main .prices-tier');
			//if (tierObj[0]) tierObj.css({display: priceForCompare > 0 ? 'none' : 'block'});
			
			if (priceForCompare > 0) {
				finalPriceObj.text(window._priceUtils.formatPrice(curObj.finalPrice * (!curObj.priceFormulaTaxInfo.priceAlreadyIncludesTax && curObj.priceFormulaTaxInfo.displayPriceMode > 1 ? taxRate : 1)));
				if (basePriceObj[0] && basePriceObj[0] !== finalPriceObj[0]) basePriceObj.text(window._priceUtils.formatPrice(curObj.finalPrice * (!curObj.priceFormulaTaxInfo.priceAlreadyIncludesTax && curObj.priceFormulaTaxInfo.displayPriceMode != 3 || curObj.priceFormulaTaxInfo.priceAlreadyIncludesTax && curObj.priceFormulaTaxInfo.displayPriceMode != 2 ? taxRate : 1)));
			}

		});
		this.getPriceBox().trigger('updatePrice');
		
		jQuery('#product-addtocart-button').on('click', function(event){
			var isError = false;
			if (window.itorisPriceFormulaErrors) {
				window.itorisPriceFormulaErrors.each(function(criteria){
					if (isError) return;
					var formula = curObj.parseCondition(criteria.formula, 0, curObj.configuredPrice / curObj.priceFormulaCurrencyConversionRate, curObj.initialPrice, curObj.finalPrice / curObj.priceFormulaCurrencyConversionRate);

					eval("if ("+formula+") {isError = true}");
					if (isError) {
						event.preventDefault();
						alert(criteria.message);
					}
				});
			}
		});
		
		return this;
    },
    getConfigurableOptionsConfig: function(){
        if (this.configurableConfig.length) return this.configurableConfig;
        
		if (window.spConfig && spConfig.config && spConfig.config.attributes) this.configurableConfig = spConfig.config.attributes;
        else try {
            this.configurableConfig = jQuery('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig.attributes;
        } catch(e){
            try {
                this.configurableConfig = jQuery('#product_addtocart_form').data('mageConfigurable').options.spConfig.attributes;
            } catch (e) { }
        }        
    },
	parseCondition: function(string, def_value, confPrice, initialPrice, productPrice) {
        this.getConfigurableOptionsConfig();
		if (!string.replace) return string;
		for (key in this.dataBySku) {
            if (key == '{tier_price}') continue;
			var value = def_value, oqty = 0, oprice = 0;
			if (this.dataBySku[key].value) value = this.dataBySku[key].value;
            for (var i = 0; i < this.optionsData.length; i++) {
                if (this.optionsData[i].type == 'field' || this.optionsData[i].type == 'area') {
                    if (this.dataBySku[key] && this.optionsData[i].id == this.dataBySku[key].id) {
                        if (this.dataBySku[key].type && this.optionsData[i].type == this.dataBySku[key].type) {
                            var textOptionId = 'options_' + this.optionsData[i].id + '_text';
                            if ($(textOptionId) && $(textOptionId).value) value = $(textOptionId).value;
                        }
                    }
                }
				if (this.optionsData[i].type == 'checkbox' || this.optionsData[i].type == 'radio') {
					if (this.dataBySku[key] && this.dataBySku[key].type && this.optionsData[i].type == this.dataBySku[key].type) {
						var optionId = 'options-' + this.optionsData[i].id + '-list';
						var input = $(optionId).select('input');
						for (var j = 0; j < input.length; j++) {
							if (input[j].checked) {
								var subOptionId = input[j].value;
								var values = this.optionsData[i].values;
								var subOptionById = values[subOptionId];
								var skuSubOption = subOptionById ? subOptionById.sku : '';
								var skuSubOptionStr = '{' + skuSubOption + '}';
								if (key == skuSubOptionStr) {
									value = $(optionId).select('label[for='+input[j].id+']')[0].innerHTML;
									_oqty = $(optionId).up().select('.option-qty')[0];
									if (_oqty && _oqty.value - 0 >= 1) oqty = _oqty.value - 0;
									if (subOptionById && subOptionById.price - 0 > 0) oprice = subOptionById.price - 0;
								}
							}
						}
					}
				}
				if (this.optionsData[i].type == 'drop_down') {
					if (this.dataBySku[key] && this.dataBySku[key].type && this.optionsData[i].type == this.dataBySku[key].type) {
						var selectId = 'select_' + this.optionsData[i].id;
						var selectValue = $(selectId).value;
						if (selectValue) {
							var values = this.optionsData[i].values;
							var subOptionById = values[selectValue];
							var skuSubOption = subOptionById ? subOptionById.sku : '';
							var skuSubOptionStr = '{' + skuSubOption + '}';
							if (key == skuSubOptionStr) {
								value = $(selectId).options[$(selectId).selectedIndex].text;
								_oqty = $(selectId).up().select('.option-qty')[0];
								if (_oqty && _oqty.value - 0 >= 1) oqty = _oqty.value - 0;
								if (subOptionById && subOptionById.price - 0 > 0) oprice = subOptionById.price - 0;
							}
						}
					}
				}
				if (this.optionsData[i].type == 'multiple') {
					if (this.dataBySku[key] && this.dataBySku[key].type && this.optionsData[i].type == this.dataBySku[key].type) {
						var selectId = 'select_' + this.optionsData[i].id;
						var options = $(selectId).select('option');
						for (var j = 0; j < options.length; j++) {
							if (options[j].selected) {
								var subOptionId = options[j].value;
								var values = this.optionsData[i].values;
								var subOptionById = values[subOptionId];
								var skuSubOption = subOptionById ? subOptionById.sku : '';
								var skuSubOptionStr = '{' + skuSubOption + '}';
								if (key == skuSubOptionStr) {
									value = options[j].innerHTML;
									if (subOptionById && subOptionById.price - 0 > 0) oprice = subOptionById.price - 0;
								}
							}
						}
					}
				}
            }
            value = _value = (value.replace ? value.replace(/^\s+|\s+$/g,"") : value);
			if (!isNaN(parseFloat(value)) && isFinite(value)) {} else value = "'"+value+"'";
			try {
				string = string.replace(new RegExp(key, "gi"), value);
				string = string.replace(new RegExp(key.replace('}','.qty}'), "gi"), oqty);
				string = string.replace(new RegExp(key.replace('}','.price}'), "gi"), oprice);
				string = string.replace(new RegExp(key.replace('}','.length}'), "gi"), _value.length ? _value.length : 0);
			} catch(e) {}
		}
		string = string.replace(new RegExp('{configured_price}', "gi"), confPrice);
		string = string.replace(new RegExp('{initial_price}', "gi"), initialPrice);
		string = string.replace(new RegExp('{special_price}', "gi"), this.specialPrice / this.priceFormulaCurrencyConversionRate);
		string = string.replace(new RegExp('{price}', "gi"), productPrice);
		string = string.replace(new RegExp('{tier_price}', "gi"), this.tierPrice);
		
		var crossChecks = [], configurablePid = 0;
		for (key in this.configurableConfig) {
			var data = this.configurableConfig[key];
            if (typeof this.configurableConfig[key] != 'object') continue;
            var value = "''", dd = $$('#attribute'+key+', [name="super_attribute['+data.id+']"]')[0];
			var _crossChecks = [];
			if (dd) {
				for(var i=0; i<data.options.length; i++) {
					var option = data.options[i];
					if (dd.value == option.id) {
						value = "'" + option.label.replace(/\'/gi, '\\\'') + "'";
						_crossChecks = option.allowedProducts ? option.allowedProducts : option.products;
						break;
					}
				}
			}
			crossChecks[crossChecks.length] = _crossChecks;
			string = string.replace(new RegExp('{'+data.code+'}', "gi"), value);
		}
		if (crossChecks.length > 0) {
			for(var o = 0; o < crossChecks[0].length; o ++) {
				var pid = crossChecks[0][o], isPidValid = true;
				for(var i=1; i<crossChecks.length; i++) {
					isPidValid = crossChecks[i].indexOf(pid) > -1;
					if (!isPidValid) break;
				}
				if (isPidValid && pid) {
					configurablePid = pid;
					break;
				}
			}
		}
		string = string.replace(new RegExp('{configurable_pid}', "gi"), configurablePid);
		
		if ($('qty')) {
			var qty = $('qty').value == 0 ? 1 : $('qty').value;
			string = string.replace(new RegExp('{qty}', "gi"), qty);
		}
		Object.getOwnPropertyNames(Math).each(function(key){
			string = string.replace(new RegExp(key, "g"), 'Math.'+key);
		});
		
		string = string.replace(/(\r\n|\n|\r)/gm,"");
		
		return string;
	},
	getPriceBox: function(){
		return jQuery('#itoris_dynamicproductoptions_popup_price [data-role="priceBox"], .product-info-price [data-role="priceBox"][data-product-id="'+this.productId+'"]');
	}
}