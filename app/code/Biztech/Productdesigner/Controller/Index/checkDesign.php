<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Index;

class checkDesign extends \Magento\Framework\App\Action\Action {

    protected $image;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Catalog\Helper\Image $image
    ) {
        parent::__construct($context);
        $this->image = $image;
    }

    /**
     * Index action
     *
     * @return $this
     */
    public function execute() {
          
            ini_set('display_errors', 1);
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $filesystem = $objectManager->get('Magento\Framework\Filesystem');
            $config = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
            
            $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $dir = $reader->getAbsolutePath();
           
            
        $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designs\Collection')->addFieldToFilter('customer_id', Array('eq' => 0));
           $alldesigns = $obj_product->getData();            
           
            try {
            foreach($alldesigns as $designs):
              
            $design_id = $designs['design_id'];    
            $delete_unused_designs_days = $config->getValue('productdesigner/general/delete_unused_designs');
            $date2 = strtotime($designs['created_at']);
            
            $date1 = time();
            if($date2 < $date1){
                  $dateDiff = $date1 - $date2;
            }
            if($date1 < $date2){
                  $dateDiff = $date2 - $date1;
             } 
             $fullDays = floor($dateDiff/(60*60*24));
             
             
             
             if($delete_unused_designs_days <= $fullDays){
                 echo "delete_unused_designs_days:".$delete_unused_designs_days;echo  "<br>";
                  echo "fullDays::".$fullDays;echo  "<br>";
                 $guestorderfound = 0;
                    
                    $orderDatamodel = $objectManager->get('Magento\Sales\Model\Order')->getCollection()->addFieldToFilter('customer_id',array('null' => true));
                     foreach($orderDatamodel as $orderDatamodel1){
                        $getid =  $orderDatamodel1->getData("increment_id");
                        $orderData = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($getid);
                         $getorderdata = $orderData->getData();
                         $orderItems = $orderData->getAllVisibleItems();
                         
                        
                         foreach($orderItems as $orderItems){
                                    foreach($orderItems->getData('product_options') as $key => $value){
                                        if($key == 'additional_options'){
                                           foreach($value as $val){
                                               if(isset($val['design_id'])){
                                                        if(($val['design_id'] == $designs['design_id'])){
                                                            echo $val['design_id']."=====".$designs['design_id'];echo  "<br>";
                                                        $guestorderfound =1;
                                                     }
                                               }
                                           }
                                           
                                          }
                                      }

                                
                       }
                       
                    }
                    
                    echo "guestorderfound::".$guestorderfound;echo  "<br>";
             }
            
           // if($delete_unused_designs_days <= $fullDays  && ($guestorderfound == 0)){
           //if($delete_unused_designs_days <= $fullDays){
           /*if($delete_unused_designs_days <= $fullDays  && ($guestorderfound == 0)){    
            
            $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id', Array('eq' => $design_id))->addFieldToFilter('design_image_type', 'base');
            $designBaseImages = $obj_product->getData();            
            
            foreach ($designBaseImages as $designBaseImage) {
                $explode = explode('.', $designBaseImage['image_path']);
                $explode2 = explode('/', $explode[0]);
                
               
                if(file_exists($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.jpg')){
                unlink($dir . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.jpg');
                }         
                
                if(file_exists($dir . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.png')){
                unlink($dir . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1]  . '.png');
                    
                }
                
                $designImagesModel = $objectManager->create('Biztech\Productdesigner\Model\Designimages');
                $designImagesModel->setImageId($designBaseImage['image_id'])->delete();
                
            }
            $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id', Array('eq' => $design_id))->addFieldToFilter('design_image_type', 'base_high');
            $designBaseHighImages = $obj_product->getData();
            foreach ($designBaseHighImages as $designBaseHighImage) {
                $explode = explode('.',$designBaseHighImage['image_path']);
                $explode2 = explode('/',$explode[0]);
                 if(file_exists($dir . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.jpg')){
                unlink($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] . '.jpg');
                 }
                  if(file_exists($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] .  '.png')){
                    unlink($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'base' . '/' . $explode2[1] . '.png');
                  }
                  
                  $designImagesModel = $objectManager->create('Biztech\Productdesigner\Model\Designimages');
                  $designImagesModel->setImageId($designBaseHighImage['image_id'])->delete();
            }
            $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id', Array('eq' => $design_id))->addFieldToFilter('design_image_type', 'canvas_image');
            $designCanvasImages = $obj_product->getData();
            foreach ($designCanvasImages as $designCanvasImage) {
                $explode = explode('.',$designCanvasImage['image_path']);
                $explode2 = explode('/',$explode[0]);
                
                 if(file_exists($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.jpg')){
                unlink($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.jpg');
                 }
                  if(file_exists($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.png')){
                unlink($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] . '.png');
                  }
                  
                  $designImagesModel = $objectManager->create('Biztech\Productdesigner\Model\Designimages');
                  $designImagesModel->setImageId($designCanvasImage['image_id'])->delete();
            }
            
             $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id', Array('eq' => $design_id))->addFieldToFilter('design_image_type', 'canvas_large_image');
             
            $designCanvasLargeImages = $obj_product->getData();
            foreach ($designCanvasLargeImages as $designCanvasLargeImage) {
                $explode = explode('.',$designCanvasLargeImage['image_path']);
                $explode2 = explode('/',$explode[0]);
                 if(file_exists($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.jpg')){
                unlink($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] . '.jpg');
                 }
                  if(file_exists($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.png')){
                unlink($dir  . 'productdesigner' . '/' . 'designs' . '/' . 'catalog' . '/' . 'product' . '/' . 'orig' . '/' . $explode2[1] .  '.png');
                  }
                  
                  $designImagesModel = $objectManager->create('Biztech\Productdesigner\Model\Designimages');
                  $designImagesModel->setImageId($designCanvasLargeImage['image_id'])->delete();
            }
             $designModel = $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Designs');
             $designModel->setDesignId($design_id)->delete();
            
            }
            
          */
            endforeach;
            die();
        } catch (Exception $e) {
                Mage::log($e->getMessage(), null, 'system.log', true);
                Mage::log($e->getMessage(), null, 'mylogfile.log');
                Mage::logException($e);
        }
            
            
         
        
         
    
        
        
       
    }
    

}

?>
