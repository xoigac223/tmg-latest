<?php

/**
 * Product:       Xtento_OrderExport (2.6.2)
 * ID:            xEHXWTQdTvsqM6Uaj+9fF4Ke0RGdP2hAINpkO3xYT0s=
 * Packaged:      2018-08-14T19:27:41+00:00
 * Last Modified: 2018-06-25T15:04:27+00:00
 * File:          app/code/Xtento/OrderExport/Helper/Module.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Helper;

class Module extends \Xtento\XtCore\Helper\AbstractModule
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Module constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Server $serverHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Server $serverHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context, $registry, $serverHelper, $utilsHelper);
        $this->resource = $resource;
    }

    protected $edition = 'CE';
    protected $module = 'Xtento_OrderExport';
    protected $extId = 'MTWOXtento_OrderExport917370';
    protected $configPath = 'orderexport/general/';

    // Module specific functionality below
    public function getDebugEnabled()
    {
        return $this->scopeConfig->isSetFlag($this->configPath . 'debug');
    }

    public function isDebugEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            $this->configPath . 'debug'
        ) && ($debug_email = $this->scopeConfig->getValue($this->configPath . 'debug_email')) && !empty($debug_email);
    }

    public function getDebugEmail()
    {
        return $this->scopeConfig->getValue($this->configPath . 'debug_email');
    }

    public function isModuleProperlyInstalled()
    {
        return true; // Not required, Magento 2 does the job of handling upgrades better than Magento 1
        // Check if DB table(s) have been created.
        return ($this->resource->getConnection('core_read')->showTableStatus(
                $this->resource->getTableName('xtento_orderexport_profile')
            ) !== false);
    }

    public function getExportBkpDir()
    {
        return $this->serverHelper->getBaseDir()->getAbsolutePath() . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "export_bkp" . DIRECTORY_SEPARATOR;
    }
}
