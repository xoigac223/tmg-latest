<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Productdesigner;

class getDesignArea extends \Magento\Backend\App\Action {

    protected $resultForwardFactory;
    protected $resultPageFactory;

    public function __construct(
    \Magento\Backend\App\Action\Context $context,
            \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {

        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute() {

        
        $params = $this->getRequest()->getParams();

        $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Mysql4\Selectionarea\Collection');
        $model1 = $model->addFieldToFilter('image_id',
                $params['imageid']);
        //$model = Mage::getModel('productdesigner/selectionarea')->getCollection()->addFieldToFilter('image_id',$imageid)->getData();
        $alldesignAreaIds = array();
        foreach ($model1 as $key => $value) {
            //$selection_data[$key] = isset($value['selection_area']) ? $value['selection_area'] : '';
            $alldesignAreaIds[] = $value['design_area_id'];
        }
        /* $total_design_area = count($model1);
          $result['selection'] = $selection_data;
          $result['design_id'] = $design_id;
          $result["area_count"] = $total_design_area; */
       $jsonData = (json_encode($alldesignAreaIds));
        $this->getResponse()->setBody($jsonData);
    }

}
