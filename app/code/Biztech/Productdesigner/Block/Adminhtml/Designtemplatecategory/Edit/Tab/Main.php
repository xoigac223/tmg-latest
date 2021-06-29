<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
// @codingStandardsIgnoreFile

namespace Biztech\Productdesigner\Block\Adminhtml\Designtemplatecategory\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface {

    /**
     * {@inheritdoc}
     */
    public function getTabLabel() {
        return __('Category Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle() {
        return __('Category Information');
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

        $model = $this->_coreRegistry->registry('current_biztech_productdesigner_designtemplatecategory');
        $model1 = $this->_coreRegistry->registry('current_biztech_productdesigner_designtemplatecategory1');
        $id = $this->getRequest()->getParam('id');

        $template_array = array();
        $collection = ($model1->getData());
        foreach ($collection as $designtemplatescategry) {

            if ($id != $designtemplatescategry['designtemplatescategory_id']) {
                $label = $designtemplatescategry['category_title'];

               
                $template_array[] = array(
                    'label' => $label,
                    'value' => $designtemplatescategry['designtemplatescategory_id']
                );
            }
        }
        sort($template_array);
        

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        if (isset($id))
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Edit Design Template Category')]);
        else
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Add Design Template Category')]);
        
        if ($model->getId()) {
            $fieldset->addField('designtemplatescategory_id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
                'category_title', 'text', ['name' => 'category_title', 'label' => __('Category Title'), 'title' => __('Category Title'), 'required' => true]
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
        $fieldset->addField(
                'is_root_category', 'checkbox', ['name' => 'is_root_category', 'label' => __('Is Root category?'), 'title' => __('Is root category'), 'onchange' => "showParentCategories(this)", 'checked' => false, 'onclick' => "this.value = this.checked ? 1 : 0;"]
        );

        $form->getElement('is_root_category')->setIsChecked(!empty($model['is_root_category']));
        $eventElem = $fieldset->addField(
                'parent_categories', 'select', ['name' => 'parent_categories', 'label' => __('Parent Categories'), 'title' => __('parent_categories'), 'disabled' => false, 'values' => $template_array]
        );
        $eventElem->setAfterElementHtml('<script>
                require(["jquery", "jquery/ui"], function ($) {
                jQuery(document).ready(function(){                                                   
               
                if(jQuery("#is_root_category").val()==1){
                 jQuery("#parent_categories").prop("disabled",true);
                }
                else{
                jQuery("#parent_categories").prop("disabled",false);
                }
                 }); 
                 });
                function showParentCategories(checkboxElem){
                
                if(checkboxElem.checked){
              
                //jQuery("parent_categories").disabled=false;
                jQuery("#parent_categories").prop("disabled",true);
                }
                else{
                
                //jQuery("parent_categories").disabled=true;
                jQuery("#parent_categories").prop("disabled",false);
                }
                }
               
            </script>');
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
