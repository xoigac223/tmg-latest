<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml;

class Designtemplates extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'designtemplate';
        $this->_headerText = __('Manage Designtemplates');        
        parent::_construct();
        $this->removeButton('add');
    }
}
