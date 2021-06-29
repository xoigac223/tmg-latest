/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_InfiniteScroll
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'underscore',
    'Bss_InfiniteScroll/js/jquery.goup.min',
    'Bss_InfiniteScroll/js/jquery-ias.min',
    'Bss_InfiniteScroll/js/jquery.lazyload',
    'jquery/ui'
], function ($, _) {
    "use strict";
    $.widget('bss.infinite_scroll', {
        options: {
            bss_lazy_loader: false,
            bss_lazy_threshold: false,
            isLazy:'',
            jsonConfig : {},
            classes: {
                toolbar: '.toolbar-products .toolbar-number',
                item: '.item.product.product-item',
                container: '.column',
                pagination: '.pages .item',
                next: '.next',
                lazy: 'img.lazy',
                prev: '.previous'
            }
        },

        _create: function () {
            $("html,body").scrollTop(0);
            var self = this;
            setTimeout(function () {
                $.ias('destroy');
                self._intConfig();
            }, 500);
        },

        _intConfig: function () {
            var options = this.options,
                currentItem = parseInt($(options.classes.item).length),
                style = '',
                html = '',
                config = {},
                ias = $.ias({
                    container : options.classes.container,
                    item: options.classes.item,
                    pagination: options.classes.pagination,
                    next: options.classes.next,
                    prev: options.classes.prev,
                    delay: 2000,
                    initialize: false
                });

            $(options.classes.lazy).lazyload();
            $(options.classes.toolbar).text(currentItem);
            if (options.jsonConfig.button.background_btn_loadmore) {
                style += 'background: #' + options.jsonConfig.button.background_btn_loadmore + ';';
            }
            if (options.jsonConfig.button.color_btn_loadmore) {
                style += ' color: #' + options.jsonConfig.button.color_btn_loadmore + ';';
            }
            ias.extension(new window.IASPagingExtension());
            if (options.jsonConfig.general.use_previous) {
                ias.extension(new window.IASHistoryExtension({prev: options.classes.prev}));
            }
            config.offset = 9999;
            if (parseInt(options.jsonConfig.general.triggerpage_threshold) > 0) {
                config.offset = options.jsonConfig.general.triggerpage_threshold;
            }
            if (options.jsonConfig.button.text_btn_loadmore != null) {
                var loadMoreConfig = '<div class="ias-trigger ias-trigger-next" style="text-align: center; cursor: pointer;"><button style="' + style + '">';
                loadMoreConfig += options.jsonConfig.button.text_btn_loadmore;
                loadMoreConfig += '</buttons></div>';
                config.html = loadMoreConfig;
            }
            if (options.jsonConfig.button.text_btn_prev != null) {
                var prevConfig = '<div class="ias-trigger ias-trigger-prev" style="text-align: center; cursor: pointer;margin-bottom:35px"><button style="' + style + '">';
                prevConfig += options.jsonConfig.button.text_btn_prev;
                prevConfig += '</button></div>';
                config.htmlPrev = prevConfig;
            }
            if (Object.keys(config).length > 1) {
                ias.extension(new window.IASTriggerExtension(config));
            }
            if (options.jsonConfig.general.loadingIcon) {
                if (options.jsonConfig.general.loading_icon_text) {
                    html = '<div class="ias-spinner" style="text-align: center;"><img src="'+options.jsonConfig.general.loadingIcon+'"/><span style="display:block;">'+options.jsonConfig.general.loading_icon_text+'</span></div>' // optionally
                } else {
                    html = '<div class="ias-spinner" style="text-align: center;"><img src="'+options.jsonConfig.general.loadingIcon+'"/></div>' // optionally
                }
            }
            if (html) {
                ias.extension(new window.IASSpinnerExtension({
                    html: html
                }));
            } else {
                ias.extension(new window.IASSpinnerExtension({}));
            }

            if (options.jsonConfig.button.text_end_load) {
                ias.extension(new window.IASNoneLeftExtension({text: options.jsonConfig.button.text_end_load}));
            }

            if (options.bss_lazy_loader) {
                ias.on('render', function(items) {
                    var $items = $(items);
                    $items.find("img").each(function(){
                        var src = $(this).attr('src')
                        $(this).attr('data-src',$(this).attr('src'))
                        $(this).attr('srcset','')
                        $(this).addClass('lazy')
                        $(this).attr('src','data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
                    })
                })

                ias.on('rendered', function () {
                    $("img").unveil(parseInt(options.bss_lazy_threshold));
                });
            }
            ias.on('rendered', function() {
                $(options.classes.lazy).lazyload();
                $("[data-role='tocart-form'], .form.map.checkout").attr('data-mage-init', JSON.stringify({'catalogAddToCart': {}}));
                $('body').trigger('contentUpdated');
            });
            ias.on('loaded', function(data, items) {
                var $items = $(items);
                var currentItem = parseInt($(options.classes.item).length);
                var loadedItem = parseInt($items.length);
                $(options.classes.toolbar).text(currentItem + loadedItem);
                setTimeout(function(){
                    if (options.isLazy) {
                        $(options.classes.lazy).lazyload();
                    }
                }, 100);
            });
            ias.on('load', function(event) {
                event.ajaxOptions.cache = true;
                if (options.isLazy) {
                    $(options.classes.lazy).lazyload();
                }
            });
            $.ias().initialize();

            if (options.jsonConfig.gototop.enabled_gototop) {
                $.goup({
                    goupSpeed: options.jsonConfig.gototop.goup_speed,
                    location: options.jsonConfig.gototop.location,
                    locationOffset: parseInt(options.jsonConfig.gototop.location_offset),
                    bottomOffset: parseInt(options.jsonConfig.gototop.bottom_offset),
                    containerSize: parseInt(options.jsonConfig.gototop.container_size),
                    containerRadius: parseInt(options.jsonConfig.gototop.container_radius),
                    alwaysVisible: true,
                    trigger: options.jsonConfig.gototop.trigger,
                    hideUnderWidth: options.jsonConfig.gototop.hide_under_width,
                    containerColor: '#' + options.jsonConfig.gototop.container_color,
                    arrowColor: '#' + options.jsonConfig.gototop.arrow_color,
                    title: options.jsonConfig.gototop.text_hover,
                    zIndex: options.jsonConfig.gototop.zindex
                });
            }
        }
    });
    return $.bss.infinite_scroll;
});