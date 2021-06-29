<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Productdesigner;

$a = BP.'/'.'lib'.'/'.'productdesigner'.'/'.'pdf'.'/'.'html2pdf.class.php';
require_once($a);
class downloadAllImages extends \Magento\Backend\App\Action {

    public function execute() {
        $params = $this->getRequest()->getParams();

        $order_id = $params['order_id'];
        $design_id = $params['design_id'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order_increment_id = $objectManager->create('Magento\Sales\Model\Order')->load($order_id)->getIncrementId();
        $designImages = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id', array('eq' => $design_id))->getData();

        $demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $dir = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/designs/catalog/product/';
        $content = '';
        foreach ($designImages as $designImage) {
            if ($designImage['design_image_type'] == 'base_high' || $designImage['design_image_type'] == 'canvas_large_image') {
                $path = $dir . 'base' . $designImage['image_path'];
                //$imgtype = getimagesize($path);
                 if($designImage['design_image_type'] == 'canvas_large_image')
                    {
                        $path = $dir.'orig'.$designImage['image_path'];
                    }

//                if ($designImage['design_image_type'] == 'canvas_extra_large_image') {
//                    $path = $dir . 'orig' . $designImage['image_path'];
                    
                    $imgtype = getimagesize($path);

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
                //}



                
                if ($imgtype[0] >= 550) {
                    $content.= "<page><div style='margin:0 auto; text-align:center; vertical-align:middle;'><img src='" . $path . "' width='550'></div><page_footer><div style='text-align:right'>Order ID # " . $order_increment_id . "</div></page_footer></page>";
                } else {
                    $content.= "<page><div style='margin:0 auto; text-align:center; vertical-align:middle;'><img src='" . $path . "'></div><page_footer><div style='text-align:right'>Order ID # " . $order_increment_id . "</div></page_footer></page>";
                }
            }
        }

//        $designDetail =  $objectManager->create('Biztech\Productdesigner\Model\Resource\Designs\Collection')->addFieldToFilter('design_id', array('eq' => $design_id))->getData();
//        $LayerImagesData = json_decode($designDetail[0]['layer_images'], true);
//
//
//        foreach ($LayerImagesData as $key => $_layerImage) {
//            $html = '';
//            $path = $_layerImage['url'];
//
//            $imgtype = getimagesize($path);
//
//            $size = 'A4';
//            $cnt_height = ($imgtype[1] * 550) / $imgtype[0];
//            if ($cnt_height < 800) {
//                $size = 'A4';
//            }
//            if ($cnt_height > 800 && $cnt_height < 1150) {
//                $size = 'A3';
//            } else if ($cnt_height > 1150 && $cnt_height < 1650) {
//                $size = 'A2';
//            } else if ($cnt_height > 1650 && $cnt_height < 2350) {
//                $size = 'A1';
//            }
//
//
//
//            if (isset($_layerImage['fontfamily'])) {
//                $html.= '<div><span>Font Family: </span>' . $_layerImage['fontfamily'] . '</div>';
//            }
//
//
//
//            if ($imgtype[0] >= 550) {
//                $content.= "<page><div style='margin:0 auto; text-align:center; vertical-align:middle;'><img src='" . $path . "' width='550'></div><page_footer>" . $html . "<div style='text-align:right'>Order ID # " . $order_increment_id . "</div></page_footer></page>";
//            } else {
//                $content.= "<page><div style='margin:0 auto; text-align:center; vertical-align:middle;'><img src='" . $path . "'></div><page_footer>" . $html . "<div style='text-align:right'>Order ID # " . $order_increment_id . "</div></page_footer></page>";
//            }
//        }

        $name = $order_increment_id . '_designs.pdf';
        $this->createPdf($content, $name, $size);
       
    }

    public function createPdf($content, $name, $size) {
        $html2pdf = new \HTML2PDF('P', $size, 'en');
        $html2pdf->WriteHTML($content);
        $html2pdf->Output($name, 'D');
    }

}
