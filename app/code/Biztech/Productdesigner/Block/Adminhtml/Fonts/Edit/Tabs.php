<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml\Fonts\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('biztech_productdesigner_fonts_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Fonts'));
    }
}
