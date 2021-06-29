<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Areasize extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'areasize';
        $this->_headerText = __('Area size');
        $this->_addButtonLabel = __('Add New Area Size');
        parent::_construct();
    }
}
