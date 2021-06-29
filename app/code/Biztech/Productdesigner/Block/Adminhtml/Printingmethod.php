<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Printingmethod extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'printingmethod';
        $this->_headerText = __('Printing Method');
        $this->_addButtonLabel = __('Add New Printingmethod');
        parent::_construct();
    }
}
