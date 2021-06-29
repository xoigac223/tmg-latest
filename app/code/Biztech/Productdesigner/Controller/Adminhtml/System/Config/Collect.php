<?php

/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\System\Config;

use Biztech\Productdesigner\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Collect extends Action {

    protected $resultJsonFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
    Context $context, JsonFactory $resultJsonFactory, Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute() {

        try {

            $watermarktext = $this->getRequest()->getParam('watermarktext');

            //The name of the directory that we need to create.
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $filesystem = $om->get('Magento\Framework\Filesystem');
            $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $prod_image_dir = $reader->getAbsolutePath() . 'productdesigner/uploadwatermark/';
            $config = $om->create('Magento\Framework\App\Config\ScopeConfigInterface');

            //Mage::getModel('core/config')->saveConfig('productdesigner/downloaddesign_general/watermark_text', $watermarktext);
            //echo $watermarktext;
            /* $this->_resourceConfig->saveConfig(
              'productdesigner/downloaddesign_general/watermark_text',
              $watermarktext,
              'default',
              0
              ); */
            // die('12121212');
            //Check if the directory already exists.
            if (!is_dir($prod_image_dir)) {
                //Directory does not exist, so lets create it.
                mkdir($prod_image_dir, 0777);
            }
            $basepath1 = $reader->getAbsolutePath() . 'productdesigner/uploadwatermark/default';
            //Check if the directory already exists.
            if (!is_dir($basepath1)) {
                //Directory does not exist, so lets create it.
                mkdir($basepath1, 0777);
            }

            if ($watermarktext != '') {
                // $im = imagecreatetruecolor(750, 723);
                // $white = imagecolorallocate($im, 255, 255, 255);
                // $grey = imagecolorallocate($im, 230, 230, 230);
                // $black = imagecolorallocate($im, 0, 0, 0);
                // $red = imagecolorallocate($im, 255, 0, 0);
                // $newgrey = imagecolorallocate($im, 236, 236, 236);
                // imagefilledrectangle($im, 0, 0, 749, 722, $newgrey);
                // ini_set('display_errors', 1);
                // $col_transparent = imagecolorallocatealpha($im, 255, 255, 255,0);
                //       imagefill($im, 0, 0, $col_transparent);  // set the transparent colour as the background.
                //       imagecolortransparent ($im, $col_transparent); // actually make it transparent
                //       imagefilledrectangle($im, 0, 0, 749, 722, $col_transparent);
                //       $text = $this->getRequest()->getParam('watermarktext');
                //       $om = \Magento\Framework\App\ObjectManager::getInstance();
                //       $filesystem = $om->get('Magento\Framework\Filesystem');
                //       $font = $reader->getAbsolutePath() . 'productdesigner/fonts/OpenSans-Bold.ttf';
                //       if(strlen($text) <=6){
                //         imagettftext($im, 180, 45, 120, 730, $newgrey, $font, $text);
                //         imagettftext($im, 180, 45, 120, 730, $red, $font, $text);
                //       }else if(strlen($text) > 6 &&  strlen($text) <= 9  ){
                //         imagettftext($im, 140, 45, 120, 730, $newgrey, $font, $text);
                //         imagettftext($im, 140, 45, 120, 730, $red, $font, $text);
                //       }else if(strlen($text) > 10 ){
                //         imagettftext($im, 110, 45, 120, 730, $newgrey, $font, $text);
                //         imagettftext($im, 110, 45, 120, 730, $red, $font, $text);
                //       }
                $imgWidth = 650;
                $imgHeight = 650;
                $im = imagecreatetruecolor($imgWidth, $imgHeight);
                $font = $reader->getAbsolutePath() . 'productdesigner/fonts/ubuntu-regular.ttf';
                $text = $this->getRequest()->getParam('watermarktext');
                //create some colors
                $white = imagecolorallocate($im, 255, 255, 255);
                $grey = imagecolorallocate($im, 128, 128, 128);
                $black = imagecolorallocate($im, 0, 0, 0);
                imagefilledrectangle($im, 0, 0, $imgWidth - 2, $imgHeight - 2, $white);

                //break lines
                $splitText = explode("\\n", $text);
                $lines = count($splitText);

                foreach ($splitText as $txt) {

                    $textBox = imagettfbbox(50, 30, $font, $txt);

                    $textWidth = abs(max($textBox[2], $textBox[4]));
                    $textHeight = abs(max($textBox[5], $textBox[7]));
                    $x = (imagesx($im) - $textWidth) / 2;
                    $y = ((imagesy($im) + $textHeight) / 2) - ($lines - 2) * $textHeight;

                    $lines = $lines - 1;

                    //add some shadow to the text
                    //add the text
                    imagettftext($im, 50, 30, $x, $y, $black, $font, $txt);
                }

                $prod_image_dir = $reader->getAbsolutePath() . 'productdesigner/uploadwatermark/';
                $image_path = $prod_image_dir . 'default/textwatermark.png';

                imagepng($im, $image_path);
                chmod($image_path, 0777);

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
                $png = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/uploadwatermark/default/textwatermark.png';
                $basedirpath = $reader->getAbsolutePath() . 'productdesigner/uploadwatermark/default/textwatermark.png';

                $dimensions = getimagesize($png);
                $x = $dimensions[0];
                $y = $dimensions[1];
                $im = imagecreatetruecolor($x, $y);
                imagealphablending($im, false);
                imagesavealpha($im, true);
                $col = imagecolorallocatealpha($im, 255, 255, 255, 127);
                imagefill($im, 0, 0, $col);

                $src_ = imagecreatefrompng($png);
                //  $alpha_channel = imagecolorallocatealpha($im, 0, 0, 0, 127);
                //imagecolortransparent($im, $alpha_channel);
                //imagefill($im, 0, 0, $alpha_channel);
                imagecopy($im, $src_, 0, 0, 0, 0, $x, $y);
                imagesavealpha($im, true);
                imagepng($im, $basedirpath, 9);

                chmod($basedirpath, 0777);

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
                $png1 = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/uploadwatermark/default/textwatermark.png';
                $image_path1 = $reader->getAbsolutePath() . 'productdesigner/uploadwatermark/default/textwatermark.png';

                $image = imagecreatefrompng($png1);
                $opacity = 0.25;
                imagealphablending($image, false); // imagesavealpha can only be used by doing this for some reason
                imagesavealpha($image, true); // this one helps you keep the alpha.
                $transparency = 1 - $opacity;
                imagefilter($image, IMG_FILTER_COLORIZE, 0, 0, 0, 127 * $transparency); // the fourth parameter is alpha
                header('Content-type: image/png');
                imagepng($image, $image_path1);
            }
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        return $result->setData(['success' => true, 'watermarktext' => $watermarktext]);
    }

    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Biztech_Productdesigner::config');
    }

}
