<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Productdesigner;

class downloadLayerImage extends \Magento\Backend\App\Action {

    public function execute() {

        $params = $this->getRequest()->getParams();
        $order_id = $params['order_id'];
        $design_id = $params['design_id'];
        $image_key = $params['image_key'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order_increment_id = $objectManager->create('Magento\Sales\Model\Order')->load($order_id)->getIncrementId();

        $path = $this->getLayerImageData($design_id, $image_key);
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();    
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        //$demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        //$dir = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $layerPath;
        $dir = $reader->getAbsolutePath();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $mediaUrl = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        //echo $dir.$path; die;
        $path = $mediaUrl.$path;
        $imgtype = getimagesize($path);

        $image_name = basename($path);
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $dir = $reader->getAbsolutePath() . 'productdesigner/designs/catalog/product/layers';

        //$dir = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'productdesigner' . DS . 'designs' . DS . 'catalog' . DS . 'product' . DS . 'layers' . DS . 'l' . DS . 'a';

        $dir_path = $dir . '/' . $image_name;

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
            $this->_redirect("productdesigner/Productdesigner/viewDesign/design_id/" . $designImage['design_id'] . "/order_id/" . $order_id);
        }
    }

    public function getLayerImageData($design_id, $image_key) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $designDetail = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designs\Collection')->addFieldToFilter('design_id', array('eq' => $design_id))->getData();
        $LayerImagesData = json_decode($designDetail[0]['layer_images'], true);

        foreach ($LayerImagesData as $key => $_layerImage) {
            if ($key == $image_key) {
                return $_layerImage['url'];
            }
        }
    }

}
