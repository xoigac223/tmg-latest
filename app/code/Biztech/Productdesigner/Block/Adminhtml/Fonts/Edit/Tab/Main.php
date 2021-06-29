<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
// @codingStandardsIgnoreFile

namespace Biztech\Productdesigner\Block\Adminhtml\Fonts\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface {

    /**
     * {@inheritdoc}
     */
    public function getTabLabel() {
        return __('Fonts Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle() {
        return __('Fonts Information');
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
    public function _prepareLayout() {
        parent::_prepareLayout();
    }

    protected function _prepareForm() {



        

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();





        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Upload Fonts')]);
        
        

        
        //$designtemplatescategies = Mage::getModel('productdesigner/quotescategory')->getCollection();
        
        /*media uploader */
        $field = $fieldset->addField(
               'customfield',
               'text',
               [
                       'name'   => 'customfield',
                       'title'  => __('Custom Field'),
                ]
        );
        $renderer = $this->getLayout()->createBlock(
       'Biztech\Productdesigner\Block\Adminhtml\Fonts\Gallery\Content');
        $field->setRenderer($renderer);

        

        $this->setForm($form);
        return parent::_prepareForm();

        
        
    }

}
