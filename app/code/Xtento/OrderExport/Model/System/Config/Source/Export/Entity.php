<?php

/**
 * Product:       Xtento_OrderExport (2.6.2)
 * ID:            xEHXWTQdTvsqM6Uaj+9fF4Ke0RGdP2hAINpkO3xYT0s=
 * Packaged:      2018-08-14T19:27:42+00:00
 * Last Modified: 2016-03-01T16:11:12+00:00
 * File:          app/code/Xtento/OrderExport/Model/System/Config/Source/Export/Entity.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\System\Config\Source\Export;

use Magento\Framework\Option\ArrayInterface;

/**
 * @codeCoverageIgnore
 */
class Entity implements ArrayInterface
{
    /**
     * @var \Xtento\OrderExport\Model\Export
     */
    protected $exportModel;

    /**
     * Entity constructor.
     * @param \Xtento\OrderExport\Model\Export $exportModel
     */
    public function __construct(\Xtento\OrderExport\Model\Export $exportModel)
    {
        $this->exportModel = $exportModel;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->exportModel->getEntities();
    }
}
