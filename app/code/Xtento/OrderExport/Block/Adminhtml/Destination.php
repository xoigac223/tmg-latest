<?php

/**
 * Product:       Xtento_OrderExport (2.6.2)
 * ID:            xEHXWTQdTvsqM6Uaj+9fF4Ke0RGdP2hAINpkO3xYT0s=
 * Packaged:      2018-08-14T19:27:41+00:00
 * Last Modified: 2015-09-10T15:24:06+00:00
 * File:          app/code/Xtento/OrderExport/Block/Adminhtml/Destination.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml;

class Destination extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_addButtonLabel = __('Add New Destination');
        parent::_construct();
    }
}
