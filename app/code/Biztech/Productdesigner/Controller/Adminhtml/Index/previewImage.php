<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Index;

class previewImage extends \Magento\Framework\App\Action\Action {

    /**
     * Index action
     *
     * @return $this
     */
    public function execute() {
        //$session = Mage::getSingleton('customer/session');
        $result = array('status' => 'fail');
        if ($this->getRequest()->isPost()) {
            // $customerData = $session->getCustomer();
            // $customer_id = $customerData->getId();
            $params = $this->getRequest()->getParams();
            $images = $params['data']['images'];            
            $saveDesign = $this->_saveDesign($images);
        }        
        $this->getResponse()->setBody(json_encode($saveDesign));
    }

    public function _saveDesign($images) {
        $params = $this->getRequest()->getParams();
        $images = json_decode($images);
        $parentImageId = json_decode($params['data']['parentImageId']);

        $prod_id = $params['data']['id'];
        $image_id = $params['data']['image_id'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
        $product = $obj_product->load($prod_id);

        //$prod_url            = Mage::getBaseUrl().$product->getUrlPath();
        $product_images = $product->getAllMediaGalleryImages();
        if(isset($params['data']['color']))
        {
        $color_id = $params['data']['color'];
        }
        $product_type = $product->getTypeId();

        if ($product_type == 'configurable') {
            // get images of child product
            $obj_product = $objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
            $childProducts = $obj_product->getChildrenIds($prod_id);
            $grouped_product_images = array();
            foreach ($childProducts[0] as $childProduct) {
                $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
                $childProduct = $obj_product->load($childProduct);
                foreach ($childProduct->getAllMediaGalleryImages() as $_image) {
                    $grouped_product_images[] = $_image;
                }
            }
            // get images of child product
        } else {
            $grouped_product_images = array();
            $grouped_product_images = $product_images;
        }

        $merged_images = array();
        foreach ($images as $key => $image) {
            $imageKey = explode('&',$key);
            $newkey = str_replace('@', '', $key);
            $newkey1 = strstr($newkey, "&", true);
            if($imageKey[0] == $image_id)
            {
                $newkey2 = str_replace('&', '', strstr($newkey, "&", false));
                $merged_images[$newkey1][$newkey2] = $image;
            }
        }

        $parentImageids = array();
        foreach ($parentImageId as $key => $parentImage) {
            $imageKey = explode('&',$key);
            $newkey = str_replace('@', '', $key);
            $newkey1 = strstr($newkey, "&", true);
            if($imageKey[0] == $image_id)
            {
                $newkey2 = str_replace('&', '', strstr($newkey, "&", false));
                $parentImageids[$newkey1] = $parentImage;
            }
        }
        $demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        foreach ($merged_images as $key => $image) {
            foreach ($grouped_product_images as $product_image) {
                if ($product_image->getId() == $key) {
                    $prod_image_path = $product_image->getPath();
                    $image_id = $parentImageids[$key];
                    $design_images = $this->saveDesignImages($prod_image_path, $image, $image_id);
                    return '<img src="'.$demo->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'productdesigner/designs/catalog/product/base'.$design_images['base'].'" />';
                }
            }
        }
    }

    public function saveDesignImages($prod_image_path, $image, $image_id) {

        $result = array();

        $decodedimage = array();
        foreach ($image as $key => $value) {
            $decodedimage[$key] = base64_decode($value);
        }
        $srcNew = array();
        foreach ($decodedimage as $key => $value) {
            $srcNew[$key] = imagecreatefromstring($value);
        }
        $time = substr(md5(microtime()), rand(0, 26), 7);

        //image store in orig start  -- 2- des png and des large png starts

        $image_name = "des_" . $time . ".png";
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $dir = $reader->getAbsolutePath() . 'productdesigner/designs/catalog/product/orig';

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $dirImg = $dir . '/' . $image_name;
        list($o_width, $o_height) = getimagesize($prod_image_path);
        $dest = imagecreatetruecolor($o_width, $o_height);

        $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Selectionarea\Collection')->addFieldToFilter('image_id', $image_id);
        $dimensions = $obj_product->getData();

        foreach ($dimensions as $d) {

            $x1 = json_decode($d['selection_area'])->x1;
            $y1 = json_decode($d['selection_area'])->y1;
            if (isset($srcNew[$d['design_area_id']]))                
                imagecopy($dest, $srcNew[$d['design_area_id']], $x1, $y1, 0, 0, imagesx($srcNew[$d['design_area_id']]), imagesy($srcNew[$d['design_area_id']]));
        }
        header('Content-type: image/png');
        imagepng($dest, $dirImg);


        $config = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $resize_width = $config->getValue('productdesigner/general/imagewidth');
        $resize_height = $config->getValue('productdesigner/general/imageheight');
        $base_image_name = "pd_" . $time . ".jpg";

        $prod_image_dir = $reader->getAbsolutePath() . 'productdesigner/designs/catalog/product/base';
        $new_prod_image = $prod_image_dir . '/' . $base_image_name;

        $default_prod_image_path = $reader->getAbsolutePath() . 'catalog/product';
        $default_prod_image = $default_prod_image_path . '/' . $base_image_name;
        if (!file_exists($default_prod_image_path)) {
            mkdir($default_prod_image_path, 0777, true);
        }
        if (!file_exists($prod_image_dir)) {
            mkdir($prod_image_dir, 0777, true);
        }
        $newPath_c = $new_prod_image;
        if (!isset($resize_width) && $resize_width == null) {
            $resize_width = 650;
        }
        if (!isset($resize_height) && $resize_height == null) {
            $resize_height = 650;
        }

        $resize = $this->resizeAndCreateDesignImage($prod_image_path, $newPath_c, $resize_width, $resize_height);


        if ($resize) {

            $info = getimagesize($newPath_c);
            $imgtype = image_type_to_mime_type($info[2]);
            switch ($imgtype) {
                case 'image/jpeg':
                    $dest = imagecreatefromjpeg($newPath_c);
                    break;
                case 'image/gif':
                    $dest = imagecreatefromgif($newPath_c);
                    break;
                case 'image/png':
                    $dest = imagecreatefrompng($newPath_c);
                    break;
                default:
                   break;
            }
            $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Selectionarea\Collection')->addFieldToFilter('image_id', $image_id);
            $dimensions = $obj_product->getData();
            foreach ($dimensions as $d) {
                $x1 = json_decode($d['selection_area'])->x1;
                $y1 = json_decode($d['selection_area'])->y1;
                if (isset($srcNew[$d['design_area_id']]))
                    imagecopy($dest, $srcNew[$d['design_area_id']], $x1, $y1, 0, 0, imagesx($srcNew[$d['design_area_id']]), imagesy($srcNew[$d['design_area_id']]));
            }
            header('Content-Type: image/png');
            imagesavealpha($dest, true);
            imagejpeg($dest, $newPath_c, 100);
            copy($newPath_c, imagepng(imagecreatefromstring(file_get_contents($newPath_c)), $default_prod_image));
            $result = array("base" => '/' . $base_image_name);
        }

        return $result;
    }

    public function resizeAndCreateDesignImage($source_image, $destination, $tn_w, $tn_h, $quality = 100) {
        $info = getimagesize($source_image);
        $imgtype = image_type_to_mime_type($info[2]);

        switch ($imgtype) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($source_image);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($source_image);
                break;
            case 'image/png':
                $source = imagecreatefrompng($source_image);
                break;
            default:
                break;
        }

        $src_w = imagesx($source);
        $src_h = imagesy($source);

        $x_ratio = $tn_w / $src_w;
        $y_ratio = $tn_h / $src_h;

        if (($src_w <= $tn_w) && ($src_h <= $tn_h)) {
            $new_w = $src_w;
            $new_h = $src_h;
        } elseif (($x_ratio * $src_h) < $tn_h) {
            $new_h = ceil($x_ratio * $src_h);
            $new_w = $tn_w;
        } else {
            $new_w = ceil($y_ratio * $src_w);
            $new_h = $tn_h;
        }

        if ($imgtype == 'image/png') {
            $file = imagecreatetruecolor($tn_w, $tn_h);
            $new = imagecreatefrompng($source_image);
            $kek = imagecolorallocate($file, 255, 255, 255);
            imagefill($file, 0, 0, $kek);
            imagecopyresampled($file, $new, (($tn_w - $new_w) / 2), (($tn_h - $new_h) / 2), 0, 0, $new_w, $new_h, $src_w, $src_h);

            if (imagejpeg($file, $destination, $quality)) {
                return true;
            }
        } else {


            $newpic = imagecreatetruecolor(round($new_w), round($new_h));
            imagecopyresampled($newpic, $source, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);
            $final = imagecreatetruecolor($tn_w, $tn_h);
            $backgroundColor = imagecolorallocate($final, 255, 255, 255);
            imagefill($final, 0, 0, $backgroundColor);
            imagecopy($final, $newpic, (($tn_w - $new_w) / 2), (($tn_h - $new_h) / 2), 0, 0, $new_w, $new_h);

            if (imagejpeg($final, $destination, $quality)) {
                return true;
            }
        }
        return false;
    }

}
