<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
// @codingStandardsIgnoreFile

namespace Biztech\Productdesigner\Block\Adminhtml\Shapes\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface {

    /**
     * {@inheritdoc}
     */
    public function getTabLabel() {
        return __('Shape Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle() {
        return __('Shape Information');
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

        $model = $this->_coreRegistry->registry('current_biztech_productdesigner_shapes');
        $model1 = $this->_coreRegistry->registry('current_biztech_productdesigner_shapes1');
        $id = $this->getRequest()->getParam('id');

        $template_array = array();
        $collection = ($model1->getData());
        foreach ($collection as $designtemplatescategry) {

            if ($id != $designtemplatescategry['shapes_id']) {
                $label = $designtemplatescategry['shapes_title'];

                $template_array[] = array(
                    'label' => $label,
                    'value' => $designtemplatescategry['shapes_id']
                );
            }
        }
        sort($template_array);
        

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        if($id)
        $fieldset = $form->addFieldset('base_fieldset',['legend' => __('Edit Shapes')]);
        else
            $fieldset = $form->addFieldset('base_fieldset',['legend' => __('Add Shapes')]);
        if ($model->getId()) {
            $fieldset->addField('shapes_id',
                    'hidden',
                    ['name' => 'id']);
        }

        $fieldset->addField(
                'shapes_title',
                'text',
                ['name' => 'shapes_title', 'label' => __('Shape Title'), 'title' => __('Shape Title'), 'required' => true]
        );
        $fieldset->addField(
                'is_root_category',
                'checkbox',
                ['name' => 'is_root_category', 'label' => __('Is Root category?'), 'title' => __('Is root category'), 'onchange' => "showParentCategories(this)", 'checked' => false, 'onclick' => "this.value = this.checked ? 1 : 0;"]
        );

        $form->getElement('is_root_category')->setIsChecked(!empty($model['is_root_category']));
        $eventElem = $fieldset->addField(
                'parent_categories',
                'select',
                ['name' => 'parent_categories', 'label' => __('Parent Categories'), 'title' => __('Parent Categories'), 'disabled' => false, 'values' => $template_array]
        );
        $fieldset->addField(
                'status',
                'select',
                ['name' => 'status', 'label' => __('Status'), 'title' => __('status'), 'values' => array(
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
                jQuery("#parent_categories").prop("disabled",true);
                }
                else{                                
                jQuery("#parent_categories").prop("disabled",false);
                }
                }
               
            </script>');
        $form->setValues($model->getData());
        
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
       'Biztech\Productdesigner\Block\Adminhtml\Shapes\Gallery\Content');
        $field->setRenderer($renderer);



        $this->setForm($form);
        return parent::_prepareForm();
    }

}
