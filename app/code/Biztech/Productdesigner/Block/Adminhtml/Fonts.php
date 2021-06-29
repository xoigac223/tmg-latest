<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Fonts extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'fonts';
        $this->_headerText = __('fonts');
        $this->_addButtonLabel = __('Add New Font');
        parent::_construct();
    }
}
