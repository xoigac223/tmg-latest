<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Side extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'side';
        $this->_headerText = __('Image Side');
        $this->_addButtonLabel = __('Add New Side');
        parent::_construct();
    }
}
