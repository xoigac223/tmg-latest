<?php

/**
 * Product:       Xtento_OrderExport (2.6.2)
 * ID:            xEHXWTQdTvsqM6Uaj+9fF4Ke0RGdP2hAINpkO3xYT0s=
 * Packaged:      2018-08-14T19:27:41+00:00
 * Last Modified: 2015-09-10T15:24:06+00:00
 * File:          app/code/Xtento/OrderExport/Block/Adminhtml/History.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml;

class History extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->removeButton('add');
    }
}
