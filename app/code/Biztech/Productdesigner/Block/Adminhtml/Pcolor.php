<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Pcolor extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'pcolor';
        $this->_headerText = __('Add Printable Color');
        $this->_addButtonLabel = __('Add Printable Color');
        parent::_construct();
    }
}
