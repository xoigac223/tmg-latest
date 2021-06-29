define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, customerData) {
    'use strict';

    return function (widget) {


        // $.widget('mage.SwatchRenderer', widget, {
        $.widget('bss.SwatchRenderer', widget, {

            _RenderControls: function () {
                this._super();
                $('.bss-inventory').hide();
            },

            isLoggedIn: function() {
                return !(customerData.get('customer')().firstname === undefined)
            },

            _OnClick: function($this, $widget){

                this._super($this, $widget)

                if(!this.isLoggedIn()) {
                    return;
                }

                if ($('body.catalog-product-view').size() <= 0) {
                    return;
                }

                var simpleProductId = 0;
                var products = $widget._CalcProducts();
                if(products.length) {
                    var simpleProductId = products[0];
                }
                if(simpleProductId) {
                    this.inventoryUpdate(simpleProductId, $widget);
                }
            },

            inventoryUpdate: function (simpleProductId, $widget)
            {
                var stock,
                    sku = $widget.options.jsonConfig.tmg_sku_mapping[simpleProductId];
                if (sku in $widget.options.jsonConfig.tmg_inventory) {
                    stock = $widget.options.jsonConfig.tmg_inventory[sku]
                } else {
                    stock = ($widget.options.jsonConfig.tmg_inventory['all'])
                        ? $widget.options.jsonConfig.tmg_inventory['all'] : null;
                }
                // // Update Value
                if($widget.options.jsonConfig.tmg_inventory_visible){
                    this.getInventoryContainer().find('.inventory-qty').empty().append("<strong>" + stock.qty + "</strong>");
                    this.getInventoryContainer().find('.inventory-message').empty().append("<i>" + stock.message + "</i>");    
                } else {
                    $('.tmg-inventory-container').hide();
                    $(".bss-table-row").find('td').each(function(){
                        $(this).find("p:nth-child(2)").hide();
                        $(this).find("p:nth-child(1)").css('text-decoration','none');
                    });
                }

            },

            getInventoryContainer: function()
            {
                var sel = '.tmg-inventory-container';
                var $container = $(sel);
                if($container.length < 1) {
                    this.createInventoryContainer();
                    $container = $(sel);
                }
                return $container;
            },

            createInventoryContainer: function()
            {
                var $parent = $('span.swatch-attribute-selected-option').parent();
                var content  = '<div id="tmg-inventory-container" class="tmg-inventory-container">'
                    + '<span class="swatch-attribute-label">' + $.mage.__('Inventory: ') + ' </span>'
                    + '<span class="swatch-attribute-value inventory-qty"></span>'
                    + '<br/><span class="swatch-attribute-value inventory-message"></span>'
                    + '</div>';
                $parent.prepend(content);
            }

        });
        // return $.mage.SwatchRenderer;
        return $.bss.SwatchRenderer;
    }
});