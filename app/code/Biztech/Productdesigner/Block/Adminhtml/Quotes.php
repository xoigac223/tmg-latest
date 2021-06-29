<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Quotes extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'Quotes';
        $this->_headerText = __('Quotes');
        $this->_addButtonLabel = __('Add New Quote');
        parent::_construct();
    }
}
