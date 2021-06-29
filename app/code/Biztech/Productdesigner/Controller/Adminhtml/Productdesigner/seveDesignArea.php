<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Productdesigner;

class seveDesignArea extends \Magento\Backend\App\Action {

    
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


        $params       = $this->getRequest()->getParams();
        $img_id       = $params['image_id'];
        $image_side       = $params['image_side'];
        $current_design_area_id       = $params['current_design_area_id'];
        $x1           = $params['x1'];
        $y1           = $params['y1'];
        $x2           = $params['x2'];
        $y2           = $params['y2'];
        $width        = $params['w'];
        $height       = $params['h'];
        $maskingid    = $params['maskingid'];
        $is_apply_all = $params['is_apply_all'];
        $product_id   = $params['product_id'];
        
        $selection_area_arr = array(
            'width'  => $width,
            'height' => $height,
            'x1'     => $x1,
            'y1'     => $y1,
            'x2'     => $x2,
            'y2'     => $y2,
        );

        
        $selection_area_arr['image_id'] = $img_id;
        $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Selectionarea');
        if($current_design_area_id){
            $model->load($current_design_area_id);
        }
        $selection_area = json_encode($selection_area_arr);
        //$selection_area_arr['image_id'] = $img_id;
        
        try {            
            $model->setImageId($img_id)
            ->setSelectionArea($selection_area)->setProductId($product_id)->setImagesideId($image_side)->setMaskingImageId($maskingid); 
            $model->save();
            $result["status"] = 'success';
            $jsonData = (json_encode($result));
            $jsonData = (json_encode($result));
            $this->getResponse()->setBody($jsonData);

        }catch(\Exception $e)
        {
            $result["status"] = 'error';
            $result["message"] = $e->getMessage();
        }





                
       
    }

}
