<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Designtemplatecategory extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'designtemplatecategory';
        $this->_headerText = __('Design Template Category');
        $this->_addButtonLabel = __('Add New Design Category');
        parent::_construct();
    }
}
