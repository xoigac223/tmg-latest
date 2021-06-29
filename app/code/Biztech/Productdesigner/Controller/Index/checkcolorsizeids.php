<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Index;

class checkcolorsizeids extends \Magento\Framework\App\Action\Action {

    /**
     * Index action
     *
     * @return $this
     */
    public function execute() {
        $params = $this->getRequest()->getParams();
        $cat_id = $params['data']['colorid'];
       
       
        $layout = $this->_objectManager->create('Magento\Framework\View\LayoutInterface');
        $resultPage = $layout->createBlock('Biztech\Productdesigner\Block\Productdesigner');

        if ($this->getRequest()->isPost()) {
            $colorid = $this->getRequest()->getPost('colorid');
        }

        if ($params['data']['colorid']) {
            $result = $resultPage->setData(array("selectedcolorid" => $cat_id, "id" => $params['data']['product']))->setTemplate('productdesigner/addtocart/colorsize.phtml')->toHtml();
        } 
        $this->getResponse()->setBody(json_encode($result));
    }

}
