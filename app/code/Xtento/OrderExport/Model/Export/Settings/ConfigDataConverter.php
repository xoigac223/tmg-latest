<?php

/**
 * Product:       Xtento_OrderExport (2.6.2)
 * ID:            xEHXWTQdTvsqM6Uaj+9fF4Ke0RGdP2hAINpkO3xYT0s=
 * Packaged:      2018-08-14T19:27:42+00:00
 * Last Modified: 2017-11-27T20:04:32+00:00
 * File:          app/code/Xtento/OrderExport/Model/Export/Settings/ConfigDataConverter.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Export\Settings;

class ConfigDataConverter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $settings = [];
        foreach ($source->getElementsByTagName('setting') as $setting) {
            $name = $setting->getAttribute('name');
            $settings[$name] = $setting->nodeValue;
        }
        return $settings;
    }
}
