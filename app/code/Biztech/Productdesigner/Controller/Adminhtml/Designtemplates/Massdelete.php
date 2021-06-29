<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Designtemplates;

class Massdelete extends \Biztech\Productdesigner\Controller\Adminhtml\Designtemplates {

    public function execute() {

        $id = $this->getRequest()->getParam('designtemplates');

        if ($id) {
            try {
                
                
                 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $filesystem = $objectManager->get('Magento\Framework\Filesystem');

                $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                $dir = $reader->getAbsolutePath();
        
                $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designtemplateimages\Collection')->addFieldToFilter('designtemplates_id', Array('eq' => $id))->addFieldToFilter('design_image_type', 'base');
                    $designBaseImages = $obj_product->getData();            
                    
                    foreach ($designBaseImages as $designBaseImage) {
                        $explode = explode('.',
                                $designBaseImage['image_path']);
                        $explode2 = explode('/', $explode[0]);
                        
                        if(file_exists($dir . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.jpg'))
                        unlink($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.jpg');
                        if(file_exists($dir . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.png'))
                        unlink($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1]  . '.png');
                        
                         $designImagesModel = $objectManager->create('Biztech\Productdesigner\Model\Designtemplateimages');
                         $designImagesModel->setImageId($designBaseImage['image_id'])->delete();
                        
                    }
                    
                    
                    
                    $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designtemplateimages\Collection')->addFieldToFilter('designtemplates_id', Array('eq' => $id))->addFieldToFilter('design_image_type', 'base_high');
            $designBaseHighImages = $obj_product->getData();
            foreach ($designBaseHighImages as $designBaseHighImage) {
                $explode = explode('.',
                        $designBaseHighImage['image_path']);
                $explode2 = explode('/',
                        $explode[0]);
                 if(file_exists($dir . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.jpg'))
                unlink($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] . '.jpg');
                  if(file_exists($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.png'))
                unlink($dir . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] . '.png');
                  
                         $designImagesModel = $objectManager->create('Biztech\Productdesigner\Model\Designtemplateimages');
                         $designImagesModel->setImageId($designBaseHighImage['image_id'])->delete();
            }
            
            
             $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designtemplateimages\Collection')->addFieldToFilter('designtemplates_id', Array('eq' => $id))->addFieldToFilter('design_image_type', 'canvas_image');
            $designCanvasImages = $obj_product->getData();
            foreach ($designCanvasImages as $designCanvasImage) {
                $explode = explode('.',
                        $designCanvasImage['image_path']);
                $explode2 = explode('/',
                        $explode[0]);
                 if(file_exists($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.jpg'))
                unlink($dir . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.jpg');
                  if(file_exists($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.png'))
                unlink($dir . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] . '.png');
                  
                  
                         $designImagesModel = $objectManager->create('Biztech\Productdesigner\Model\Designtemplateimages');
                         $designImagesModel->setImageId($designCanvasImage['image_id'])->delete();
                  
            }
            
             $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designtemplateimages\Collection')->addFieldToFilter('designtemplates_id', Array('eq' => $id))->addFieldToFilter('design_image_type', 'canvas_large_image');
             
            $designCanvasLargeImages = $obj_product->getData();
            foreach ($designCanvasLargeImages as $designCanvasLargeImage) {
                $explode = explode('.',
                        $designCanvasLargeImage['image_path']);
                $explode2 = explode('/',
                        $explode[0]);
                 if(file_exists($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.jpg'))
                unlink($dir . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] . '.jpg');
                  if(file_exists($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.png'))
                unlink($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.png');
                  
                  
                        $designImagesModel = $objectManager->create('Biztech\Productdesigner\Model\Designtemplateimages');
                        $designImagesModel->setImageId($designCanvasLargeImage['image_id'])->delete();  
            }

                $designModel1 = $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Designtemplates');
                $designModel1->setDesigntemplatesId($id)->delete();
                
                
                $this->messageManager->addSuccess(__('You deleted the item.'));
                $this->_redirect('productdesigner/designtemplates/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        $this->_redirect('productdesigner/designtemplates/');
    }

}
