<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
// @codingStandardsIgnoreFile

namespace Biztech\Productdesigner\Block\Adminhtml\Printingmethod\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface {

    
     protected $_systemStore;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    /**
     * {@inheritdoc}
     */
    public function getTabLabel() {
        return __('Printingmethod Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle() {
        return __('Printingmethod Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden() {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm() {




        //$collection = $this->mymodulemodelFactory->create();
//	var_dump($collection);
        $model = $this->_coreRegistry->registry('current_biztech_productdesigner_printingmethod');
        

       
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();




        if($id)
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Printing Method')]);
        else
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Printing Method')]);
        if ($model->getId()) {
            $fieldset->addField('printing_id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
                'printing_name', 'text', ['name' => 'printing_name', 'label' => __('Name'), 'title' => __('Name'), 'required' => true]
        );

        $fieldset->addField(
                'printing_code', 'text', ['name' => 'printing_code', 'label' => __('Code'), 'title' => __('Code'), 'after_element_html' => __('Code must not contain White space'), 'required' => true]
        );

        $fieldset->addField(
                'printing_description', 'textarea', ['name' => 'printing_description', 'label' => __('Description'), 'title' => __('Description'), 'required' => true]
        );

        $fieldset->addField(
                'minimum_quantity', 'text', ['name' => 'minimum_quantity', 'label' => __('Minimum Quantity'), 'class' => __('validate-number'), 'title' => __('Minimum Quantity'), 'required' => true]
        );



            
        


        $eventElem = $fieldset->addField(
           'store_id',
           'multiselect',
           [
             'name'     => 'stores[]',
             'label'    => __('Store Views'),
             'title'    => __('Store Views'),
             'required' => true,
             'values'   => $this->_systemStore->getStoreValuesForForm(false, true),
             
           ]
        );



        
        




        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $groupOptions = $objectManager->get('\Magento\Customer\Model\ResourceModel\Group\Collection')->toOptionArray();
        $eventElem = $fieldset->addField(
                'customer_groups', 'multiselect', ['name' => 'customer_groups', 'label' => __('Customer Groups'), 'title' => __('Customer Groups'), 'values' => $groupOptions]
        );


        

        $fieldset->addField(
                'status', 'select', ['name' => 'status', 'label' => __('Status'), 'title' => __('Status'), 'values' => array(
                array(
                    'value' => 1,
                    'label' => __('Enable')
                ),
                array(
                    'value' => 2,
                    'label' => __('Disabled')
                ))
                ]
        );
        
        $method_types = array(
            0 =>array(
                'label' => 'Please Select Type',
                'value' => ""
            ),
            1 =>array(
                'label' => 'By Color Quantity',
                'value' => 1
            ),
            2 => array(
                'label' => 'By Area Range',
                'value' => 2
            )            
        );
        $eventElem = $fieldset->addField(
                'colortype', 'select', ['name' => 'colortype', 'label' => __('Method Type'), 'title' => __('Method Type'), 'width'=> __('10%'), 'values' => $method_types, 'required' => true]
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
