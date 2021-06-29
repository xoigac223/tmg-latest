<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */
// @codingStandardsIgnoreFile

namespace Biztech\Productdesigner\Block\Adminhtml\Designtemplatecategory\Edit\Tab;

class Templatecategory extends \Magento\Backend\Block\Widget\Grid\Extended {

    protected $_productFactory;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Biztech\Productdesigner\Model\DesigntemplatesFactory $productFactory, array $data = []
    ) {
        $this->_productFactory = $productFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct() {
        parent::_construct();

        $this->setId('templatecategoryGrid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = $this->_productFactory->create()->getCollection();
//        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//        $collection = $objectManager->create('Biztech\Productdesigner\Model\Resource\Designtemplates\Collection')->getData();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column) {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedTemplates();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('designtemplates_id', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('designtemplates_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _getSelectedTemplates() {
        $template_id = $this->getRequest()->getParam('id'); // Used in grid to return selected templates values.
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $designs = $objectManager->create('Biztech\Productdesigner\Model\Designtemplatecategory')->load($template_id)->getDesigns();
        return explode(',', $designs);
    }

    public function getSelectedTemplates() {

        $template_id = $this->getRequest()->getParam('id');
        // if you save product id in your custom table 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $designs = $objectManager->create('Biztech\Productdesigner\Model\Designtemplatecategory')->load($template_id)->getDesigns();
        $ids = explode(',', $designs);
        $custIds = array();

        foreach ($ids as $cust) {
            $custIds[$cust] = array('position' => $cust);
        }

        return $custIds;
    }

    public function getGridUrl() {
        return $this->_getData(
                        'grid_url'
                ) ? $this->_getData(
                        'grid_url'
                ) : $this->getUrl(
                        '*/*/templatecategorygrid', ['_current' => true]
        );
    }

    protected function _prepareColumns() {

        $this->addColumn(
                'in_products', [
            'type' => 'checkbox',
            'html_name' => 'templatecategory',
            'required' => true,
            'values' => $this->_getSelectedTemplates(),
            'align' => 'center',
            'index' => 'designtemplates_id',
            'header_css_class' => 'a-center'
                ]
        );

         $this->addColumn('position' ,[
             'header' => __('ID'),
            'name' => 'position',
            'width' => 60,
            'type' => 'number',
            'validate_class' => 'validate-number',
            'index' => 'position',
            'editable' => true,
            'edit_only' => true,
            'column_css_class' => 'no-display',
            'header_css_class' => 'no-display',
        ]);

        $this->addColumn(
                'designtemplates_id', [
            'header' => __('ID'),
            'index' => 'designtemplates_id',
            'align' => 'right',
            'width' => '50px',
                ]
        );


        $this->addColumn(
                'design_image', [
            'header' => __('Image'),
            'index' => 'design_image',
            'align' => 'right',
            'width' => '25px',
            'renderer' => 'Biztech\Productdesigner\Block\Adminhtml\Designtemplates\Grid\Image'
                ]
        );



        return parent::_prepareColumns();
    }

}
