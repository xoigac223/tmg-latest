<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Index;



/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class brush extends \Magento\Framework\App\Action\Action {

   
    public function execute() {
            $params = $this->getRequest()->getParams();
                       
            $image = base64_decode($params['data']['image']);

            $time = substr(md5(microtime()),rand(0,26),7);
            $image_name = "brush_".$time.".png";
            //$image_path = $this->getDispretionPath($image_name);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
             $filesystem = $objectManager->get('Magento\Framework\Filesystem');
            $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $dir = $reader->getAbsolutePath() . 'productdesigner/designs/catalog/product/tmp';
            //$dir= Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).DS.'productdesigner'.DS.'designs'.DS.'catalog'.DS.'product'.DS.'tmp'.$image_path;
                if(!file_exists($dir)){
                    mkdir($dir, 0777, true);
                }
            $dirImg= $dir.'/'.$image_name;

            file_put_contents($dirImg, $image);
            $demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
            $url = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/designs/catalog/product/tmp/' . $image_name;
            


            
            $result = array('status' => 'success');
            $result['url'] = $url;
            $this->getResponse()->setBody(json_encode($result));
    }
}
