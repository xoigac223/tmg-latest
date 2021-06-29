<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Clipart extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'clipart';
        $this->_headerText = __('Clipart');
        $this->_addButtonLabel = __('Add New Clipart');
        parent::_construct();
    }
}
