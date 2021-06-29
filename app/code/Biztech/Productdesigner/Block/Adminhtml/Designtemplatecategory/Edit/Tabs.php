<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml\Designtemplatecategory\Edit;

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
        $this->setId('biztech_productdesigner_designtemplatecategory_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Design Template Category'));
        $this->addTab(
                'templatecategory',
                [
                    'label' => __('Design Templates'),
                    'url' => $this->getUrl('*/*/templatecategory', ['_current' => true]),
                    'class' => 'ajax',
                    
                ]
            );
    }
}
