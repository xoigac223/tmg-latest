<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Simpleprintingmethod extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'simpleprintingmethod';
        $this->_headerText = __('Simple Printingmethod');
        $this->_addButtonLabel = __('Add New Simple Printingmethod');
        parent::_construct();
    }
}
