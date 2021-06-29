<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
// @codingStandardsIgnoreFile

namespace Biztech\Productdesigner\Block\Adminhtml\Masking\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface {

    
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
        return __('Masking Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle() {
        return __('Masking Information');
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
        $model = $this->_coreRegistry->registry('current_biztech_productdesigner_masking');
        $model1 = $this->_coreRegistry->registry('current_biztech_productdesigner_masking1');

        $collection = ($model1->getData());
        $id = $this->getRequest()->getParam('id');
        $template_array = array();
        foreach ($collection as $designtemplatescategry) {

            if ($id != $designtemplatescategry['masking_id']) {
                $label = $designtemplatescategry['masking_title'];



                $template_array[] = array(
                    'label' => $label,
                    'value' => $designtemplatescategry['masking_id']
                );
            }
        }
        sort($template_array);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();




        if ($id)
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Edit Masking Category')]);
        else
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Add Masking Category')]);

        if ($model->getId()) {
            $fieldset->addField('masking_id', 'hidden', ['name' => 'id']);
        }
        $fieldset->addField(
                'masking_title', 'text', ['name' => 'masking_title', 'label' => __('Masking Title'), 'title' => __('Masking Title'), 'required' => true]
        );

        $fieldset->addField(
                'is_root_category', 'checkbox', ['name' => 'is_root_category', 'label' => __('Is Root category?'), 'title' => __('Is root category'), 'onchange' => "showParentCategories(this)", 'checked' => false, 'onclick' => "this.value = this.checked ? 1 : 0;"]
        );
        $form->getElement('is_root_category')->setIsChecked(!empty($model['is_root_category']));
        $eventElem = $fieldset->addField(
                'parent_categories', 'select', ['name' => 'parent_categories', 'label' => __('Parent Categories'), 'title' => __('parent_categories'), 'disabled' => false, 'values' => $template_array]
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
        //$designtemplatescategies = Mage::getModel('productdesigner/quotescategory')->getCollection();

        $form->setValues($model->getData());

        /* media uploader */
        $field = $fieldset->addField(
                'customfield', 'text', [
            'name' => 'customfield',
            'title' => __('Custom Field'),
                ]
        );
        $renderer = $this->getLayout()->createBlock(
                'Biztech\Productdesigner\Block\Adminhtml\Masking\Gallery\Content');
        $field->setRenderer($renderer);



        $this->setForm($form);
        return parent::_prepareForm();
    }

}
