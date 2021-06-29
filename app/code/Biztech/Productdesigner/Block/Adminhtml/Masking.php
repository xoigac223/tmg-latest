<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Masking extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'masking';
        $this->_headerText = __('Masking');
        $this->_addButtonLabel = __('Add Masking Image');
        parent::_construct();
    }
}
