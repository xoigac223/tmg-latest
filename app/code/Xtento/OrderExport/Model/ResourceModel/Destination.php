<?php

/**
 * Product:       Xtento_OrderExport (2.6.2)
 * ID:            xEHXWTQdTvsqM6Uaj+9fF4Ke0RGdP2hAINpkO3xYT0s=
 * Packaged:      2018-08-14T19:27:42+00:00
 * Last Modified: 2015-10-11T13:28:37+00:00
 * File:          app/code/Xtento/OrderExport/Model/ResourceModel/Destination.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\ResourceModel;

class Destination extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('xtento_orderexport_destination', 'destination_id');
    }
}
