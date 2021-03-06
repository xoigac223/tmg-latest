<?php

/**
 * Product:       Xtento_OrderExport (2.6.2)
 * ID:            xEHXWTQdTvsqM6Uaj+9fF4Ke0RGdP2hAINpkO3xYT0s=
 * Packaged:      2018-08-14T19:27:41+00:00
 * Last Modified: 2016-04-11T13:47:40+00:00
 * File:          app/code/Xtento/OrderExport/Block/Adminhtml/Widget/Menu.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Block\Adminhtml\Widget;

class Menu extends \Magento\Backend\Block\AbstractBlock
{
    protected $menuBar;

    protected $menu = [
        'manual' => [
            'name' => 'Manual Export',
            'action_name' => '',
            'last_link' => false,
            'is_link' => true
        ],
        'log' => [
            'name' => 'Execution Log',
            'action_name' => '',
            'last_link' => false,
            'is_link' => true
        ],
        'history' => [
            'name' => 'Export History',
            'action_name' => '',
            'last_link' => false,
            'is_link' => true
        ],
        'configuration' => [
            'name' => 'Configuration',
            'last_link' => false,
            'is_link' => false,
        ],
        'profile' => [
            'name' => 'Export Profiles',
            'action_name' => '',
            'last_link' => false,
            'is_link' => true
        ],
        'destination' => [
            'name' => 'Export Destinations',
            'action_name' => '',
            'last_link' => false,
            'is_link' => true
        ],
        'tools' => [
            'name' => 'Tools',
            'action_name' => '',
            'last_link' => false,
            'is_link' => true
        ],
    ];

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $adminhtmlData;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Helper\Data $adminhtmlData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->adminhtmlData = $adminhtmlData;
    }

    protected function getMenu()
    {
        return $this->menu;
    }

    protected function _toHtml()
    {
        $title = __('Sales Export Navigation');
        $this->menuBar = <<<EOT
        <style>
        .icon-head { padding-left: 0px; }
        </style>
        <div style="padding:8px; margin-bottom: 10px; border: 1px solid #e3e3e3; background: #f8f8f8; font-size:12px;">
            {$title}&nbsp;-&nbsp;
EOT;
        foreach ($this->getMenu() as $controllerName => $entryConfig) {
            if ($entryConfig['is_link']) {
                if (!$this->_authorization->isAllowed('Xtento_OrderExport::' . $controllerName)) {
                    // No rights to see
                    continue;
                }
                $this->addMenuLink(
                    __($entryConfig['name']),
                    $controllerName,
                    $entryConfig['action_name'],
                    $entryConfig['last_link']
                );
            } else {
                $this->menuBar .= $entryConfig['name'];
                if (!$entryConfig['last_link']) {
                    $this->menuBar .= '&nbsp;|&nbsp;';
                }
            }
        }
        $this->menuBar .= '<a href="http://support.xtento.com/wiki/Magento_2_Extensions:Magento_Order_Export_Module" target="_blank" style="font-weight: bold;">' . __(
                'Get Help'
            ) . '</a>';
        $this->menuBar .= '<div style="float:right;"><a href="http://www.xtento.com/" target="_blank" style="text-decoration:none;color:#57585B;"><img src="//www.xtento.com/media/images/extension_logo.png" alt="XTENTO" height="20" style="vertical-align:middle;"/> XTENTO Magento Extensions</a></div></div>';

        return $this->menuBar;
    }

    protected function addMenuLink($name, $controllerName, $actionName = '', $lastLink = false)
    {
        $isActive = '';
        if ($this->getRequest()->getControllerName() == $controllerName) {
            if ($actionName == '' || $this->getRequest()->getActionName() == $actionName) {
                $isActive = 'font-weight: bold;';
            }
        }
        $this->menuBar .= '<a href="' . $this->adminhtmlData->getUrl(
                '*/' . $controllerName . '/' . $actionName
            ) . '" style="' . $isActive . '">' . __(
                $name
            ) . '</a>';
        if (!$lastLink) {
            $this->menuBar .= '&nbsp;|&nbsp;';
        }
    }
}