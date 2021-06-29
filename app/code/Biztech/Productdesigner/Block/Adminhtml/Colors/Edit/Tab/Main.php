<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
// @codingStandardsIgnoreFile

namespace Biztech\Productdesigner\Block\Adminhtml\Colors\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface {

    /**
     * {@inheritdoc}
     */
    public function getTabLabel() {
        return __('Colors Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle() {
        return __('Colors Information');
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
        $model = $this->_coreRegistry->registry('current_biztech_productdesigner_colors');
        

       
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();




        if($id)
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Colors Counter')]);
        else
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Add Image Colors')]);
        if ($model->getId()) {
            $fieldset->addField('colors_id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField(
                'colors_counter', 'text', ['name' => 'colors_counter', 'label' => __('Image Colors'), 'title' => __('Image Colors'), 'required' => true]
        );

        $fieldset->addField(
                'colors_price', 'text', ['name' => 'colors_price', 'label' => __('Price'), 'title' => __('Price'), 'required' => true]
        );

       
       
        
        //$designtemplatescategies = Mage::getModel('productdesigner/quotescategory')->getCollection();

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
