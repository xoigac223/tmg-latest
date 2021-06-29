<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
// @codingStandardsIgnoreFile

namespace Biztech\Productdesigner\Block\Adminhtml\Quotes\Edit\Tab;

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
        return __('Quotes Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle() {
        return __('Quotes Information');
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
    // $model1 = $this->_objectManager->create('Biztech\Productdesigner\Model\Resource\Quotescategory\Collection');
    $model = $this->_coreRegistry->registry('current_biztech_productdesigner_quotes');
    $model1 = $this->_coreRegistry->registry('current_biztech_productdesigner_quotes1');
    $id = $this->getRequest()->getParam('id');
    
    $collection = ($model1->getData());
   
    $template_array = array();
   foreach($collection as $designtemplatescategry)
            {
                
              
                $label = $designtemplatescategry['category_title'];
                
                $level = $designtemplatescategry['level'];
                $padLength = $level*10;
                $label_new = str_pad($label, $padLength, "-", STR_PAD_LEFT);
                
                $template_array[] = array(
                    'label' => $label_new,
                    'value' => $designtemplatescategry['quotescategory_id']
                );
            
            }
           
    sort($template_array);
    /** @var \Magento\Framework\Data\Form $form */
    $form = $this->_formFactory->create();




   
    if($id)
    $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Edit Quotes')]);
    else
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Add Quotes')]);
    if ($model->getId()) {
        $fieldset->addField('quotes_id', 'hidden', ['name' => 'id']);
    }
   
    $fieldset->addField(
            'quotes_text', 'text', ['name' => 'quotes_text', 'label' => __('Quotes'), 'title' => __('Quotes text'), 'required' => true]
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
        $fieldset->addField('category_id','select', ['name' => 'category_id', 'label' => __('Category'), 'title' => __('category'),'values' => $template_array]);


    //$designtemplatescategies = Mage::getModel('productdesigner/quotescategory')->getCollection();

    $form->setValues($model->getData());
    $this->setForm($form);
    return parent::_prepareForm();
}

}
