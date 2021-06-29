<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Index;

class filterProduct extends \Magento\Framework\App\Action\Action {

    /**
     * Index action
     *
     * @return $this
     */
    public function execute() {
        $params = $this->getRequest()->getParams();
        //print_r($params); 
        $cat_id = $params['data'];       
        $layout = $this->_objectManager->create('Magento\Framework\View\LayoutInterface');
        $resultPage = $layout->createBlock('Biztech\Productdesigner\Block\Productdesigner');
        if ($cat_id != 0) {
             $result["products"] = $resultPage->setData(array("cat_id" => $cat_id))->setTemplate('productdesigner/products/products.phtml')->toHtml();
        } else {
             $result["products"] = $resultPage->setData(array("cat_id" => 0))->setTemplate('productdesigner/products/products.phtml')->toHtml();
         }
        if ($result["products"]) {
            $result["status"] = 'success';
        }
        $this->getResponse()->setBody(json_encode($result));
    }

}
