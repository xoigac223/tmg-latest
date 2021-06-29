<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\fonts;

/*
$a = BP . '/' . 'lib' . '/' . 'productdesigner' . '/' . 'pdf' . '/config/' . 'tcpdf_config_alt.php';
$b = BP . '/' . 'lib' . '/' . 'productdesigner' . '/' . 'pdf' . '/' . 'tcpdf.php';
require_once($a);
require_once($b); */
$a = BP . '/' . 'lib' . '/' . 'productdesigner' . '/' . 'pdf' . '/' . 'html2pdf.class.php';
require_once($a);

//$b = BP.'/'.'lib'.'/'.'productdesigner'.'/'.'pdf'.'/'.'tcpdf.php.';
//require_once($b);
class downloadLayerPdf extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute() {
        $params = $this->getRequest()->getParams();

        $order_id = $params['order_id'];
        $design_id = $params['design_id'];
        $image_key = $params['image_key'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order_increment_id = $objectManager->create('Magento\Sales\Model\Order')->load($order_id)->getIncrementId();
        $path = $this->getLayerImageData($design_id,$image_key);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $mediaUrl = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $imgtype = getimagesize($mediaUrl.$path);
        $path = $mediaUrl.$path;
        if ($imgtype) {
            
        } else {
            $name = basename($path, '.svg') . '.pdf';
            $imageName = basename($path, '.svg') . '.svg';
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);

                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                //$pdf->SetHeaderMargin(-3);
                //$pdf->SetFooterMargin(0);

                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);


                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                $path = $reader->getAbsolutePath() . 'productdesigner/designs/catalog/product/layers/'.$imageName;
                //$path = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).DS.'productdesigner'.DS.'designs'.DS.'catalog'.DS.'product'.DS.'layers'.DS.'l'.DS.'a'.DS.$imageName;
                $pdf->AddPage();
                $pdf->ImageSVG($file=$path, 40, 70, 100, 100, '', 'center', '', 1, false);

                //$pdf->SetFont('helvetica', '', 11);
                //$pdf->SetY(250);
                //$txt = "Order ID # ".$order_increment_id;
                //$pdf->Write(0, $txt, '', 10, 'R', true, 1, false, false, 0);

                $pdf->Output($name, 'D');
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
