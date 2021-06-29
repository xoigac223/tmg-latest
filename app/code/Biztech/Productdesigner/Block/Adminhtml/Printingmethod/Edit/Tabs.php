<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
namespace Biztech\Productdesigner\Block\Adminhtml\Printingmethod\Edit;

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
        $this->setId('biztech_productdesigner_printingmethod_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Printing Method'));


        $id = $this->getRequest()->getParam('id');
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $type = $objectManager->create('Biztech\Productdesigner\Model\Printingmethod')->load($id)->getColortype();


        


        if($type == 1):
            $this->addTab(
                'bycolorquantity',
                [
                    'label' => __('By Color Quantity'),
                    'url' => $this->getUrl('*/*/bycolorquantity', ['_current' => true]),
                    'class' => 'ajax',
                    
                ]
            );
        endif;
        

        if($type == 2):
            $this->addTab(
                'byareasize',
                [
                    'label' => __('By Area Size'),
                    'url' => $this->getUrl('*/*/byareasize', ['_current' => true]),
                    'class' => 'ajax',
                    
                ]
            );
        endif;


    }
}
