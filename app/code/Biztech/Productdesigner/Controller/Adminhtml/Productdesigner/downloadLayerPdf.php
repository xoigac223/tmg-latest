<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Productdesigner;
$a = BP.'/'.'lib'.'/'.'productdesigner'.'/'.'pdf'.'/'.'html2pdf.class.php';
require_once($a);
class downloadLayerPdf extends \Magento\Backend\App\Action {
    
    public function execute() {
        
        $params = $this->getRequest()->getParams();
        
        $order_id = $params['order_id'];
        $design_id = $params['design_id'];
        $image_key = $params['image_key'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order_increment_id = $objectManager->create('Magento\Sales\Model\Order')->load($order_id)->getIncrementId();
        $path = $this->getLayerImageData($design_id, $image_key);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $mediaUrl = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $imgtype = getimagesize($mediaUrl.$path);
         $path = $mediaUrl.$path;

        if ($imgtype) {
            switch ($imgtype['mime']) {
                case 'image/jpeg':
                    $name = basename($path, '.jpg') . '.pdf';
                    break;

                case 'image/png':
                    $name = basename($path, '.png') . '.pdf';
                    break;
                default:
                    $name = basename($path, '.jpg') . '.pdf';
            }

            $size = 'A4';
            $cnt_height = ($imgtype[1] * 550) / $imgtype[0];
            if ($cnt_height < 800) {
                $size = 'A4';
            }
            if ($cnt_height > 800 && $cnt_height < 1150) {
                $size = 'A3';
            } else if ($cnt_height > 1150 && $cnt_height < 1650) {
                $size = 'A2';
            } else if ($cnt_height > 1650 && $cnt_height < 2350) {
                $size = 'A1';
            }
            $designDetail = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designs\Collection')->addFieldToFilter('design_id', array('eq' => $design_id))->getData();
            $LayerImagesData = json_decode($designDetail[0]['layer_images'], true);
//            $designDetail = Mage::getModel('productdesigner/designs')->getCollection()->addFieldToFilter('design_id', array('eq' => $design_id))->getData();
//            $LayerImagesData = json_decode($designDetail[0]['layer_images'], true);
            $html = '';
            foreach ($LayerImagesData as $key => $_layerImage) {
                if ($key == $image_key) {

                    if (isset($_layerImage['fontfamily'])) {
                        $html.= '<div><span>Font Family: </span>' . $_layerImage['fontfamily'] . '</div>';
                    }
                }
            }







            if ($imgtype[0] >= 550) {
                $content = "<page><div style='margin:0 auto; text-align:center; vertical-align:middle;'><img src='" . $path . "' width='550'></div><page_footer>" . $html . "<div style='text-align:right'>Order ID # " . $order_increment_id . "</div></page_footer></page>";
            } else {
                $content = "<page><div style='margin:0 auto; text-align:center; vertical-align:middle;'><img src='" . $path . "'></div><page_footer>" . $html . "<div style='text-align:right'>Order ID # " . $order_increment_id . "</div></page_footer></page>";
            }
            $this->createPdf($content, $name, $size);
        } else {
            
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

    public function createPdf($content, $name, $size) {       
        $html2pdf = new \HTML2PDF('P', $size, 'en');
        $html2pdf->WriteHTML($content);
        $html2pdf->Output($name, 'D');
    }

}
