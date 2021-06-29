<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
// @codingStandardsIgnoreFile

namespace Biztech\Productdesigner\Block\Adminhtml\Simpleprintingmethod\Edit\Tab;

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
        return __('Simpleprintingmethod Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle() {
        return __('Simpleprintingmethod Information');
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
        $model = $this->_coreRegistry->registry('current_biztech_productdesigner_simpleprintingmethod');
        

       
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();




        if($id)
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Simple Printingmethod')]);
        else
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Simple Printingmethod')]);
        if ($model->getId()) {
            $fieldset->addField('simpleprinting_id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
                'simpleprinting_name', 'text', ['name' => 'simpleprinting_name', 'label' => __('Name'), 'title' => __('Name'), 'required' => true]
        );

        $fieldset->addField(
                'simpleprinting_description', 'text', ['name' => 'simpleprinting_description', 'label' => __('Description'), 'title' => __('Description'), 'required' => true]
        );

        $fieldset->addField(
                'simpleprinting_code', 'text', ['name' => 'simpleprinting_code', 'label' => __('Code'), 'title' => __('Code'), 'after_element_html' => __('Code must not contain White space'), 'required' => true]
        );

        $fieldset->addField(
                'minimum_quantity', 'text', ['name' => 'minimum_quantity', 'label' => __('Minimum Quantity'), 'class' => __('validate-number'), 'title' => __('Minimum Quantity'), 'required' => true]
        );

        $fieldset->addField(
                'front_surcharge', 'text', ['name' => 'front_surcharge', 'label' => __('Front Surcharge'), 'title' => __('Front Surcharge'), 'required' => true]
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


        
        //$designtemplatescategies = Mage::getModel('productdesigner/quotescategory')->getCollection();

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
