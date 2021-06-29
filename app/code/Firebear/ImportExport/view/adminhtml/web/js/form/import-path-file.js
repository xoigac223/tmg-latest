/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
        'Firebear_ImportExport/js/form/import-dep-file',
        'Magento_Ui/js/lib/spinner',
        'uiRegistry'
    ],
    function (Element, loader,reg) {
        'use strict';

        return Element.extend(
            {
                defaults: {
                    listens: {
                        "value": "onChangeValue",
                        "${$.ns}.${$.ns}.source.type_file:value": "onFormatValue"
                    }
                },
                onChangeValue: function (value) {
                    if (this.isShown) {
                        var map = reg.get(this.ns + '.' + this.ns + '.source_data_map_container.source_data_map');
                        var mapCategory = reg.get(this.ns + '.' + this.ns + '.source_data_map_container_category.source_data_categories_map');
                        var removeMapping = reg.get(this.ns + '.' + this.ns + '.source.remove_current_mappings');
                        if (removeMapping !== undefined && removeMapping.value() == 1) {
                            map.deleteRecords();
                        }
                        map._updateCollection();
                        mapCategory.deleteRecords();
                        mapCategory._updateCollection();
                        reg.get(this.ns + '.' + this.ns + '.source.check_button').showMap(0);
                        reg.get(this.ns + '.' + this.ns + '.source.check_button').validMap = 0;
                    }
                },
                onFormatValue: function (value) {
                    if (this.isShown) {
                        this.value('');
                    }
                }
            }
        );
    }
);
