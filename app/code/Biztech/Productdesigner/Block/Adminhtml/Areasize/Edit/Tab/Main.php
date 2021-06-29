<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
// @codingStandardsIgnoreFile

namespace Biztech\Productdesigner\Block\Adminhtml\Areasize\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface {

    /**
     * {@inheritdoc}
     */
    public function getTabLabel() {
        return __('Area Size Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle() {
        return __('Area Size Information');
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
        $model = $this->_coreRegistry->registry('current_biztech_productdesigner_areasize');
        

       
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();




        if($id)
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Edit Area Size')]);
        else
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Add Area Size')]);
        if ($model->getId()) {
            $fieldset->addField('areasize_id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField(
                'area_size', 'text', ['name' => 'area_size', 'label' => __('Area size'), 'title' => __('Areasize'), 'required' => true]
        );

        $fieldset->addField(
                'area_price', 'text', ['name' => 'area_price', 'label' => __('Price'), 'title' => __('Price'), 'required' => true]
        );

       
        
        
        //$designtemplatescategies = Mage::getModel('productdesigner/quotescategory')->getCollection();

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
