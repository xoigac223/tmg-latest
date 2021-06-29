<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\designtemplates;

class delete extends \Magento\Framework\App\Action\Action {

    /**
     * Index action
     *
     * @return $this
     */
    public function execute() {
        $params = $this->getRequest()->getParams();
        $designId = $params['data']['design_id'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $designImages = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id', array('eq' => $designId))->getData();
        //$designImages = Mage::getModel('productdesigner/designimages')->getCollection()->addFieldToFilter('design_id',array('eq'=>$designId));
        try {
            foreach ($designImages as $image) {
                $designImages1 = $objectManager->create('Biztech\Productdesigner\Model\Designimages')->load($image['image_id']);
                //$designImages = Mage::getModel('productdesigner/designimages')->load($image->getId());
                $designImages1->delete();
            }
            $designTemplate = $objectManager->create('Biztech\Productdesigner\Model\Designs')->load($designId);
            $designTemplate->delete();

            $result = array();

            //$session = Mage::getSingleton('customer/session');
            //$customerData = $session->getCustomer();
            //$customer_id = $customerData->getId();        

            $resultPage = $objectManager->create('Magento\Framework\View\LayoutInterface');
            $layout = $resultPage->createBlock('Biztech\Productdesigner\Block\Productdesigner');

            $result["designs"] = $layout->setData(array("customer_id" => 1))->setTemplate('productdesigner/mydesigns/list.phtml')->toHtml();
            $result['status'] = 'success';
//            $layout = $this->getLayout()->createBlock('productdesigner/productdesigner');
//            $result["designs"] = $layout->setData(array("customer_id"=>$customer_id))->setTemplate('productdesigner/mydesigns/list.phtml')->toHtml();
//            $result['status'] = 'success';




            $this->getResponse()->setBody(json_encode($result));
        } catch (\Exception $e) {
            
        }
    }

}
