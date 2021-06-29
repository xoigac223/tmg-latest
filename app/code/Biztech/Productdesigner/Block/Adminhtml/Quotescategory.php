<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Quotescategory extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'Quote-Category';
        $this->_headerText = __('Quote-Category');
        $this->_addButtonLabel = __('Add New Quote Category');
        parent::_construct();
    }
}
