<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Index;

class filterQuotes extends \Magento\Framework\App\Action\Action {

    /**
     * Index action
     *
     * @return $this
     */
    public function execute() {
        $params = $this->getRequest()->getParams();
        $cat_id = $params['data']['quotes_category_id'];       
        $layout = $this->_objectManager->create('Magento\Framework\View\LayoutInterface');
        $resultPage = $layout->createBlock('Biztech\Productdesigner\Block\Productdesigner');
        if ($cat_id != NULL) {
            $result["quotes"] = $resultPage->setData(array("cat_id" => $cat_id))->setTemplate('productdesigner/quotes/list.phtml')->toHtml();
        } 
        if ($result["quotes"]) {
            $result["status"] = 'success';
        }
        $this->getResponse()->setBody(json_encode($result));
    }

}
