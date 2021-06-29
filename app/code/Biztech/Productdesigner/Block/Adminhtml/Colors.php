<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Colors extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'colors';
        $this->_headerText = __('Image Colors');
        $this->_addButtonLabel = __('Add New Colors');
        parent::_construct();
    }
}
