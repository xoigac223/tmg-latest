<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Index;

class filterTemplate extends \Magento\Framework\App\Action\Action {

    /**
     * Index action
     *
     * @return $this
     */
    public function execute() {
        $params = $this->getRequest()->getParams();
        /*print_r($params);
        die();*/
        if(isset($params['data']['template_category_id']))
        {
            $cat_id = $params['data']['template_category_id'];       
        }
        else{
            $cat_id = isset($params['template_category_id']) ? $params['template_category_id'] : "";       
        }
        $layout = $this->_objectManager->create('Magento\Framework\View\LayoutInterface');
        $resultPage = $layout->createBlock('Biztech\Productdesigner\Block\Productdesigner');
        $result=array();
        if ($cat_id) {
            $result["images"] = $resultPage->setData(array("cat_id" => $cat_id))->setTemplate('productdesigner/templates/list.phtml')->toHtml();
        } 
        if (isset($result["images"])) {
            $result["status"] = 'success';
        } else{
            $result["status"] = 'fail';
        }
        $this->getResponse()->setBody(json_encode($result));
    }

}
