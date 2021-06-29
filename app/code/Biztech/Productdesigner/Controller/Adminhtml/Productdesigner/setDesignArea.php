<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Productdesigner;

class setDesignArea extends \Magento\Backend\App\Action {

    
    protected $resultForwardFactory;
    protected $resultPageFactory;
    

    public function __construct(
            \Magento\Backend\App\Action\Context $context,
             \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory
            //\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig                       
            
    ) {
        // $this->_coreRegistry = $coreRegistry;
        //$this->_scopeConfig = $scopeConfig;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        // $this->_scopeConfig = $scopeConfig;
    }

    public function getWidth() {
        //return $this->_scopeConfig->getValue(self::WIDTH,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function execute() {
        $params = $this->getRequest()->getParams();
        $img_id = $params['imageid'];
        $img_url = $params['image_url'];
        $product_id = $params['product_id'];
        $image_side = $params['image_Side'];
        $current_design_area_id = $params['current_design_area_id'];
        $next_design_area_id = $params['next_design_area_id'];

        $layout = $this->_objectManager->create('Magento\Framework\View\LayoutInterface');
        $resultPage = $layout->createBlock('Biztech\Productdesigner\Block\Productdesigner');
        try {


            $coll_selectionarea = $this->_objectManager->create('Biztech\Productdesigner\Model\Mysql4\Selectionarea\Collection')->addFieldToFilter('image_id', $img_id);
            foreach ($coll_selectionarea as $key => $value) {
                $selection_data[$key] = isset($value['selection_area']) ? $value['selection_area'] : '';
                $design_id[$key] = isset($value['design_area_id']) ? $value['design_area_id'] : '';
            }


            if (empty($selection_data))
                $selection_data = '';
            if (empty($design_id))
                $design_id = '';
            

            $result["status"] = 'success';
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $obj_selectionArea = $objectManager->create('Biztech\Productdesigner\Model\Selectionarea');
            $obj_selectionArea->load($current_design_area_id);
            $current_selection_data = $obj_selectionArea->getSelectionArea();           

            $result["design_area"] = $resultPage->setData(array("image_id" => $img_id,"image_side" => $image_side, "product_img_url" => $img_url, "product_id" => $product_id,"selection_data" => $selection_data, "current_design_area_id" => $current_design_area_id, "next_design_area_id" => $next_design_area_id,"design_id"=>$design_id))->setTemplate('helper/designarea.phtml')->toHtml();
            $result["selection"] = $selection_data;
            $result["current_design_area_id"] = $current_design_area_id;
            $result["next_design_area_id"] = $next_design_area_id;
            $result["image_side"] = $image_side;


            

        } catch (\Exception $e) {
            $result["status"] = 'error';
            $result["message"] = $e->getMessage();
        }
        $this->getResponse()->setBody(json_encode($result));
    }

}
