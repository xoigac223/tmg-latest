<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
// @codingStandardsIgnoreFile

namespace Biztech\Productdesigner\Block\Adminhtml\Side\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface {

    /**
     * {@inheritdoc}
     */
    public function getTabLabel() {
        return __('Side Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle() {
        return __('Side Information');
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
        $model = $this->_coreRegistry->registry('current_biztech_productdesigner_side');
        

       
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();




        if($id)
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Edit Image Side')]);
        else
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Add Image Side')]);
        if ($model->getId()) {
            $fieldset->addField('imageside_id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField(
                'imageside_title', 'text', ['name' => 'imageside_title', 'label' => __('Image Side'), 'title' => __('Image Side'), 'required' => true]
        );

       
        $fieldset->addField(
                'status', 'select', ['name' => 'status', 'label' => __('Status'), 'title' => __('status'), 'values' => array(
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
        
        //$designtemplatescategies = Mage::getModel('productdesigner/quotescategory')->getCollection();

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
