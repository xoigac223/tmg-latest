<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Productdesigner;

class downloadDesignImage extends \Magento\Backend\App\Action {

    public function execute() {

        $params = $this->getRequest()->getParams();
        $order_id = $params['order_id'];
        $image_id = $params['image_id'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order_increment_id = $objectManager->create('Magento\Sales\Model\Order')->load($order_id)->getIncrementId();
        $designImage = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('image_id', array('eq' => $image_id))->getData();

        //$order_increment_id = Mage::getModel('sales/order')->load($order_id)->getIncrementId();
        //$designImage = Mage::getModel('productdesigner/designimages')->getCollection()->addFieldToFilter('image_id', array('eq' => $image_id))->getData();
        $demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $url = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/designs/catalog/product/';
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $dir = $reader->getAbsolutePath() . 'productdesigner/designs/catalog/product';
        //$url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'productdesigner/designs/catalog/product/';
        //$dir = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'productdesigner' . DS . 'designs' . DS . 'catalog' . DS . 'product';
        $config = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

        $imageFormat = $config->getValue('productdesigner/general/downloadimagetype');

        if ($imageFormat == 'png') {

            $imageName = basename($designImage[0]['image_path']);
            $imageData = explode('.', $imageName);
            if ($imageData[1] == 'jpg')
                $image_name = $imageData[0] . '.png';
            else
                $image_name = $imageData[0] . '.' . $imageData[1];

            if ($designImage[0]['design_image_type'] == 'base_high') {
                $path = $url . 'base/' . $image_name;
                $dir_path = $dir . '/' . 'base' . '/' . $image_name;
            } else {
                $path = $url . 'orig/' . $image_name;
                $dir_path = $dir . '/' . 'orig' . '/' . $image_name;
            }
        } else {

            if ($designImage[0]['design_image_type'] == 'base_high') {
                $path = $url . 'base' . $designImage[0]['image_path'];
                $dir_path = $dir . '/' . 'base' . $designImage[0]['image_path'];
            } else {
                $path = $url . 'orig/' . $designImage[0]['image_path'];
                $dir_path = $dir . '/' . 'orig' . $designImage[0]['image_path'];
            }
        }
        $imgtype = getimagesize($path);

        if (file_exists($dir_path)) {
            $contentType = $imgtype['mime'];
            $this->getResponse()
                    ->setHeader('Content-Disposition', 'attachment; filename=' . $path)
                    ->setHeader('Content-Length', filesize($dir_path))
                    ->setHeader('Content-type', $contentType);

            $this->getResponse()->sendHeaders();

            readfile($path);            

        } else {
            $this->messageManager->addError("Image not found");
            //Mage::getSingleton('adminhtml/session')->addError("Image not found");
            $this->_redirect("productdesigner/Productdesigner/viewDesign/design_id/" . $designImage['design_id'] . "/order_id/" . $order_id);
        }
    }

}
