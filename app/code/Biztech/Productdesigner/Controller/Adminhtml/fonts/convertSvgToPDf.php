<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\fonts;
    
 $a = BP . '/' . 'lib' . '/' . 'productdesigner' . '/' . 'pdf' . '/' . 'html2pdf.class.php';
require_once($a);
/*$a = BP . '/' . 'lib' . '/' . 'productdesigner' . '/' . 'pdf' . '/' . 'html2pdf.class.php';
require($a);


$d = BP . '/' . 'lib' . '/' . 'productdesigner' . '/' . 'pdf' . '/' . 'config'. '/' . 'tcpdf_config_alt.php';
require($d);

$b = BP . '/' . 'lib' . '/' . 'productdesigner' . '/' . 'pdf' . '/' . 'tcpdf.php';
require($b);*/

/*$c = BP . '/' . 'lib' . '/' . 'productdesigner' . '/' . 'pdf' . '/' . 'tcpdf_include.php';
require($c);*/

//$b = BP.'/'.'lib'.'/'.'productdesigner'.'/'.'pdf'.'/'.'tcpdf.php.';
//require_once($b);
    
class convertSvgToPDf extends \Magento\Backend\App\Action {    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute() {

        //$this->loadLayout();
        $params = $this->getRequest()->getParams();
        $design_id = $params['design_id'];
        $order_id = $params['order_id'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $designImages = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designs\Collection')->addFieldToFilter('design_id',
                        array('eq' => $design_id))->getData();
        $LayerImagesData = json_decode($designImages[0]['layer_images'],
                true);

        foreach ($LayerImagesData as $key => $_layerImage) {            
            $html = '';
            $layerPath = $_layerImage['url'];

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            
            $filesystem = $objectManager->get('Magento\Framework\Filesystem');
            $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

             $dir = $reader->getAbsolutePath();
             //echo $dir;
            //$demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
            //$dir = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $layerPath;
           
            $imgtype = getimagesize($dir.$layerPath);
            if (!$imgtype) {                
                $filesystem = $objectManager->get('Magento\Framework\Filesystem');
                $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                

                $pdf_dir = $reader->getAbsolutePath() . '/productdesigner/designs/catalog/product/pdf';
                $name = basename($layerPath,
                                '.svg') . '.pdf';
                $imageName = basename($layerPath,
                                '.svg') . '.svg';

                $layerPdf = $pdf_dir . '/' . $name;

                $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                $pdf->SetMargins(PDF_MARGIN_LEFT,0,PDF_MARGIN_RIGHT);
                $pdf->SetAutoPageBreak(TRUE,PDF_MARGIN_BOTTOM);
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                $path = $reader->getAbsolutePath() . '/productdesigner/designs/catalog/product/layers/' . $imageName;
                // add a page
                $pdf->AddPage();
                $pdf->ImageSVG($path, $x=15, $y=30, $w='', $h='', $link='', $align='', $palign='', $border='', $fitonpage='');

                $pdf->Output($layerPdf, 'F');                                                           
            }
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order_increment_id = $objectManager->create('Magento\Sales\Model\Order')->load($order_id)->getIncrementId();
        $designImages = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id',
                        array('eq' => $design_id))->getData();

        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        $dir = $reader->getAbsolutePath() . 'productdesigner/designs/catalog/product';
        $config = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $imageFormat = $config->getValue('productdesigner/general/downloadimagetype');

        $zip_dir = $reader->getAbsolutePath() . 'productdesigner/designs/catalog/product/zip';
        $demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $zip_url = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/designs/catalog/product/zip/';
        //$zip_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'productdesigner/designs/catalog/product/zip/';
        if (!file_exists($zip_dir)) {
            mkdir($zip_dir,
                    0777,
                    true);
        }        
        if ($imageFormat == 'png') {

            foreach ($designImages as $designImage) {
                $image_name = basename($designImage['image_path'],
                                '.jpg') . '.png';

                if ($designImage['design_image_type'] == 'base_high' || $designImage['design_image_type'] == 'canvas_large_image') {
                    if ($designImage['design_image_type'] == 'base_high')
                        $zip_path[] = $dir . '/' . 'base/' . $image_name;
                    if ($designImage['design_image_type'] == 'canvas_large_image') {
                        $zip_path[] = $dir . '/' . 'orig/' . $image_name;
                    }
                }
            }
            $name = $zip_dir . '/' . $order_increment_id . '_designs_PNG.zip';
            $url = $zip_url . $order_increment_id . '_designs_PNG.zip';
        } else {
            foreach ($designImages as $designImage) {
                if ($designImage['design_image_type'] == 'base_high' || $designImage['design_image_type'] == 'canvas_large_image') {
                    if ($designImage['design_image_type'] == 'base_high')
                        $zip_path[] = $dir . '/' . 'base' . $designImage['image_path'];
                    if ($designImage['design_image_type'] == 'canvas_large_image') {
                        $zip_path[] = $dir . '/' . 'orig' . $designImage['image_path'];
                    }
                }
            }
            $name = $zip_dir . '/' . $order_increment_id . '_designs_JPG.zip';
            $url = $zip_url . $order_increment_id . '_designs_JPG.zip';
        }

        $designDetail = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designs\Collection')->addFieldToFilter('design_id',
                        array('eq' => $design_id))->getData();
        $LayerImagesData = json_decode($designDetail[0]['layer_images'],
                true);


        foreach ($LayerImagesData as $key => $_layerImage) {
            $path = $_layerImage['url'];

            $image_name = basename($path);



            $dir = $reader->getAbsolutePath() . 'productdesigner/designs/catalog/product/layers';

            $dir_path = $dir . '/' . $image_name;
            $zip_path[] = $dir_path;
        }

        /** create pdf for product images
         * Starts
         */        
        $productPdfDir = $reader->getAbsolutePath() . '/productdesigner/designs/catalog/product/';

        $productPdfUrl = $reader->getAbsolutePath() . '/productdesigner/designs/catalog/product/';

        $pdf_dir = $reader->getAbsolutePath() . 'productdesigner/designs/catalog/product/pdf';

        $pdf_url = $reader->getAbsolutePath() . 'productdesigner/designs/catalog/product/pdf';
        if (!file_exists($pdf_dir)) {
            mkdir($pdf_dir,
                    0777,
                    true);
        }

        $productDesignImages = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id',
                        array('eq' => $design_id))->getData();

        foreach ($productDesignImages as $designImage) {
            if ($designImage['design_image_type'] == 'base_high' || $designImage['design_image_type'] == 'canvas_extra_large_image'):

                if ($designImage['design_image_type'] == 'base_high') {
                    $path = $productPdfUrl . 'base' . $designImage['image_path'];
                } else {
                    $path = $productPdfUrl . 'orig' . $designImage['image_path'];
                }

                $imgtype = getimagesize($path);

                switch ($imgtype['mime']) {
                    case 'image/jpeg':
                        $pdfName = basename($designImage['image_path'],
                                        '.jpg') . '.pdf';
                        break;

                    case 'image/png':
                        $pdfName = basename($designImage['image_path'],
                                        '.png') . '.pdf';
                        break;
                    default:
                        $pdfName = basename($designImage['image_path'],
                                        '.jpg') . '.pdf';
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
                if ($imgtype[0] >= 550) {
                    $productPdfContent = "<page><div style='margin:0 auto; text-align:center; vertical-align:middle;'><img src='" . $path . "' width='550'></div><page_footer><div style='text-align:right'>Order ID # " . $order_increment_id . "</div></page_footer></page>";
                } else {
                    $productPdfContent = "<page><div style='margin:0 auto; text-align:center; vertical-align:middle;'><img src='" . $path . "'></div><page_footer><div style='text-align:right'>Order ID # " . $order_increment_id . "</div></page_footer></page>";
                }

                $productPdf = $pdf_dir . '/' . $pdfName;                
                $this->createPdfToZip($productPdfContent,
                        $productPdf,
                        $size);

                $zip_path[] = $productPdf;

            endif;
        }

        /** create pdf for product images
         * Ends
         */
        /** create pdf for layer images
         * Starts
         */        
        $designDetail = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designs\Collection')->addFieldToFilter('design_id',
                        array('eq' => $design_id))->getData();
        $LayerImagesData = json_decode($designDetail[0]['layer_images'],
                true);

        foreach ($LayerImagesData as $key => $_layerImage) {
            $html = '';
            $layerPath = $_layerImage['url'];

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $mediaUrl = $objectManager->get('Magento\Store\Model\StoreManagerInterface')
                                    ->getStore()
                                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
           // echo $layerPath; die;
            $imgtype = getimagesize($mediaUrl.$layerPath);
            $layerPath = $mediaUrl.$layerPath;

            if ($imgtype) {
                switch ($imgtype['mime']) {
                    case 'image/jpeg':
                        $layerName = basename($layerPath,
                                        '.jpg') . '.pdf';
                        break;
                    case 'image/png':
                        $layerName = basename($layerPath,
                                        '.png') . '.pdf';
                        break;
                    default:
                        $layerName = basename($layerPath,
                                        '.jpg') . '.pdf';
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

                if (isset($_layerImage['fontfamily'])) {
                    $html = '<div><span>Font Family: </span>' . $_layerImage['fontfamily'] . '</div>';
                }

                if ($imgtype[0] >= 550) {
                    $layerContent = "<page><div style='margin:0 auto; text-align:center; vertical-align:middle;'><img src='" . $layerPath . "' width='550'></div><page_footer>" . $html . "<div style='text-align:right'>Order ID # " . $order_increment_id . "</div></page_footer></page>";
                } else {
                    $layerContent = "<page><div style='margin:0 auto; text-align:center; vertical-align:middle;'><img src='" . $layerPath . "'></div><page_footer>" . $html . "<div style='text-align:right'>Order ID # " . $order_increment_id . "</div></page_footer></page>";
                }

                $layerPdf = $pdf_dir . '/' . $layerName;

                $this->createPdfToZip($layerContent,
                        $layerPdf,
                        $size);

                $zip_path[] = $layerPdf;
            } else {
                $svgname = basename($layerPath,
                                '.svg') . '.pdf';
                $layerPdf = $pdf_dir . '/' . $svgname;
                $zip_path[] = $layerPdf;
            }
        }

        //print_r($zip_path);exit;

        /** create pdf for layer images
         * Ends
         */
        $valid_files = array();

        if (is_array($zip_path)) {
            foreach ($zip_path as $file) {
                if (file_exists($file)) {
                    $valid_files[] = $file;
                }
            }
        }

        if (count($valid_files)) {

            if (file_exists($name)) {
                unlink($name);
            }

            $zip = new \ZipArchive();

            if ($zip->open($name,
                            \ZIPARCHIVE::CREATE) !== true) {
                return false;
            }

            foreach ($valid_files as $file) {

                $zip->addFile($file,
                        basename($file));
            }
            $zip->close();

            $new_filename = $order_increment_id . '_designs_JPG.zip';


            header("Content-Type: application/zip");
            header("Content-Length: " . filesize($name));
            header('Content-Disposition: attachment; filename="' . $new_filename . '"');
            header("Pragma: no-cache");
            header("Expires: 0");

            readfile($name);            
        }
    }

    public function createPdfToZip($content,
            $pdfPath,
            $size) {        
        $html2pdf = new \HTML2PDF('P',
                $size,
                'en');
        
        $html2pdf->WriteHTML($content);
        $html2pdf->Output($pdfPath,
                'F');
    }

}
