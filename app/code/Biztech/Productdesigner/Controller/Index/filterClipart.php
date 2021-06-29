<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Index;

class filterClipart extends \Magento\Framework\App\Action\Action {

    /**
     * Index action
     *
     * @return $this
     */
    public function execute() {
        $params = $this->getRequest()->getParams();
        //print_r($params['data']) ;
        if(isset($params['data']['clipart_category_id']))
        {
            $cat_id = $params['data']['clipart_category_id'];
             $tags = $params['data']['search_tag_field'];
        }else
        {
            $cat_id = $params['clipart_category_id'];
              if(isset($params['search_tag_field'])) {
              $tags = $params['search_tag_field'];
             } else{
                $tags = '';
             }
        }
        
       
        $layout = $this->_objectManager->create('Magento\Framework\View\LayoutInterface');
        $resultPage = $layout->createBlock('Biztech\Productdesigner\Block\Productdesigner');
        if ($cat_id) {
            $result["images"] = $resultPage->setData(array("cat_id" => $cat_id, "tags" => $tags))->setTemplate('productdesigner/clipart/images.phtml')->toHtml();
        } else {
            $result["images"] = $resultPage->setData(array("tags" => $tags))->setTemplate('productdesigner/clipart/images.phtml')->toHtml();
        }
        if ($result["images"]) {
            $result["status"] = 'success';
        }
        $this->getResponse()->setBody(json_encode($result));
    }

}
