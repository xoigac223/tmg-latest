<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Biztech\Productdesigner\Controller\Adminhtml\Designtemplates;
class Getmydesigntemplate extends \Biztech\Productdesigner\Controller\Adminhtml\Designtemplates {
    /**
     * Getmydesigntemplate action
     *
     * @return $this
     */
    public function execute() { 
           
        $params = $this->getRequest()->getParams();
        $design_id = $params['data']['shapes_category_id'];
                   
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $designModel = $objectManager->create('Biztech\Productdesigner\Model\Designtemplates')->load($design_id);       
        $result = array();
        $result['selected_product_color'] = $designModel->getColorId();
        $result['designs'] = $designModel->getLayerImages();
        $result['masking'] = $designModel->getMaskingImages();
        $result['design_id'] = $design_id;
        $result['product_id'] = $designModel->getProductId();
        $result['isCustomerLoggedIn'] = 1;     
        $this->getResponse()->setBody(json_encode($result));
    }
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Biztech_Productdesigner::designtemplates');
    }

}
