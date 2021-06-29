<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\designtemplates;

class getMyDesigntemplate extends \Magento\Framework\App\Action\Action {

    /**
     * Index action
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

        $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Selectionarea\Collection')->addFieldToFilter('product_id', $result['product_id']);
        $result['dimensions'] = $obj_product->getData();
      
        $this->getResponse()->setBody(json_encode($result));
    }

}
