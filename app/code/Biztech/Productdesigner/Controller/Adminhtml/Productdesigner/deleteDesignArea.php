<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Productdesigner;

class deleteDesignArea extends \Magento\Backend\App\Action {

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
        
        try{
            $result = array();
            $result["status"]  = 'success';
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $obj_selectionArea = $objectManager->create('Biztech\Productdesigner\Model\Selectionarea');
            $design_id = $this->getRequest()->getPost('design_id');
            $selectionArea = $obj_selectionArea->load($design_id);
            $selectionArea->delete();
        }
        catch (\Exception $e) {
            
            $result["status"]  = 'error';
            $result["message"] = $e->getMessage();
        }
        $jsonData = json_encode($result);    
        $this->getResponse()->setBody($jsonData); 


    }

}
