<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Shapes extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'shapes';
        $this->_headerText = __('Shape');
        $this->_addButtonLabel = __('Add New Shape');
        parent::_construct();
    }
}
