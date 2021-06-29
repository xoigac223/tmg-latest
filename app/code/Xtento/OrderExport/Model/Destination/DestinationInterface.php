<?php

/**
 * Product:       Xtento_OrderExport (2.6.2)
 * ID:            xEHXWTQdTvsqM6Uaj+9fF4Ke0RGdP2hAINpkO3xYT0s=
 * Packaged:      2018-08-14T19:27:42+00:00
 * Last Modified: 2015-08-10T10:20:49+00:00
 * File:          app/code/Xtento/OrderExport/Model/Destination/DestinationInterface.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Destination;

interface DestinationInterface
{
    public function testConnection();
    public function saveFiles($fileArray);
}