<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Biztech\Productdesigner\Controller\Index;

class deleteDesign extends \Magento\Framework\App\Action\Action {
     
    public function execute() {

        
        $data = $this->getRequest()->getParams();
        
        $design_id = $data['design_id'];

        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $filesystem = $objectManager->get('Magento\Framework\Filesystem');

            $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $dir = $reader->getAbsolutePath();
            $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id', Array('eq' => $design_id))->addFieldToFilter('design_image_type', 'base');
                $designBaseImages = $obj_product->getData();            
                
            
            foreach ($designBaseImages as $designBaseImage) {
                $explode = explode('.',
                        $designBaseImage['image_path']);
                $explode2 = explode('/',
                        $explode[0]);
                if(file_exists($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.jpg'))
                unlink($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.jpg');
                if(file_exists($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.png'))
                unlink($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1]  . '.png');
            }
            $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id', Array('eq' => $design_id))->addFieldToFilter('design_image_type', 'base_high');
            $designBaseHighImages = $obj_product->getData();
            foreach ($designBaseHighImages as $designBaseHighImage) {
                $explode = explode('.',
                        $designBaseHighImage['image_path']);
                $explode2 = explode('/',
                        $explode[0]);
                 if(file_exists($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.jpg'))
                unlink($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] . '.jpg');
                  if(file_exists($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.png'))
                unlink($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] . '.png');
            }
            $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id', Array('eq' => $design_id))->addFieldToFilter('design_image_type', 'canvas_image');
            $designCanvasImages = $obj_product->getData();
            foreach ($designCanvasImages as $designCanvasImage) {
                $explode = explode('.',
                        $designCanvasImage['image_path']);
                $explode2 = explode('/',
                        $explode[0]);
                 if(file_exists($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.jpg'))
                unlink($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.jpg');
                  if(file_exists($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.png'))
                unlink($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] . '.png');
            }
            
             $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id', Array('eq' => $design_id))->addFieldToFilter('design_image_type', 'canvas_large_image');
             
            $designCanvasLargeImages = $obj_product->getData();
            foreach ($designCanvasLargeImages as $designCanvasLargeImage) {
                $explode = explode('.',
                        $designCanvasLargeImage['image_path']);
                $explode2 = explode('/',
                        $explode[0]);
                 if(file_exists($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.jpg'))
                unlink($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] . '.jpg');
                  if(file_exists($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.png'))
                unlink($dir . '/' . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.png');
            }
            $designImagesModel = $objectManager->create('Biztech\Productdesigner\Model\Designimages');
            $designImagesModel->setDesignId($design_id)->delete();

            $designModel = $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Designs');

            $designModel->setDesignId($design_id)->delete();
            $this->messageManager->addSuccess('Design was successfully deleted');
            
            $this->_redirect('*/*/mydesigndashboard');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/myDesignsDashboard');
        }
    }

}
