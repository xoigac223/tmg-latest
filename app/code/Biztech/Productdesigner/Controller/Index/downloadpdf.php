<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Index;

class downloadpdf extends \Magento\Framework\App\Action\Action {
    
    /**
     * Index action
     *
     * @return $this
     */
    public function execute() {

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $prod_id = $params['data']['id'];
            $image_id = $params['data']['image_id'];
            //$image_id = $this->getRequest()->getPost('image_id');
            $parent_images_id = $params['data']['parentImageId'];
            $images = $params['data']['images'];
            //$images = $this->getRequest()->getPost('images');
            $paths = $this->_downloadpdf($prod_id, $image_id, $images, $parent_images_id);
            $path = $paths['download_img_url'];
            $result_new['url'] = $path;
        }



        $this->getResponse()->setBody(json_encode($result_new));
    }

    public function _downloadpdf($prod_id, $image_id, $images, $parent_images_id) {

        // make image from data


        $decoded_images = json_decode($images);


        // for parent images id
        $parent_images_id = json_decode($parent_images_id);

        // print_r($parent_images_id);die;

        $parent_images_id_final = null;
        foreach ($parent_images_id as $key => $image) {
            $newkey = str_replace('@', '', $key);
            $newkey2 = str_replace('&', '', strstr($newkey, "&", false));
            if (strstr($key, "&", true) == $image_id) {
                $parent_images_id_final = $image;
            }
        }



        // for parent images id



        $merged_images = array();
        foreach ($decoded_images as $key => $image) {
            $newkey = str_replace('@', '', $key);
            $newkey2 = str_replace('&', '', strstr($newkey, "&", false));
            if (strstr($key, "&", true) == $image_id) {
                $merged_images[$newkey2] = $image;
            }
        }
        $decodedimage = array();
        foreach ($merged_images as $key => $value) {
            $decodedimage[$key] = base64_decode($value);
        }
        $srcNew = array();
        foreach ($decodedimage as $key => $value) {
            $srcNew[$key] = imagecreatefromstring($value);
        }


        // make image from data
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
        $product = $obj_product->load($prod_id);

        $product_images = $product->getAllMediaGalleryImages();


        $product_type = $product->getTypeId();
        if ($product_type == 'configurable') {
            // get images of child product
            $obj_product = $objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
            //$childProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($id);
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



        $config = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $resize_width = $config->getValue('productdesigner/general/imagewidth');
        $resize_height = $config->getValue('productdesigner/general/imageheight');
        $demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        if (!isset($resize_width) && $resize_width == null) {
            $resize_width = 650;
        }
        if (!isset($resize_height) && $resize_height == null) {
            $resize_height = 650;
        }
        $imagehelper = $objectManager->create('Magento\Catalog\Helper\Image');
        foreach ($grouped_product_images as $product_image) {
            //echo $product_image->getId();
            if ($product_image->getId() == str_replace('@', '', $image_id)) {
                $prod_image_path = $imagehelper->setImageFile($product_image->getFile())->resize($resize_width, $resize_height)->keepAspectRatio(true)->constrainOnly(false)->getUrl();
                //$prod_image_path = $product_image->getPath();
            }
        }



        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $resize_width =  $config->getValue('productdesigner/general/imagewidth');
        $resize_height = $config->getValue('productdesigner/general/imageheight');


        $time = substr(md5(microtime()), rand(0, 26), 7);
        $base_image_name = "pd_" . $time . ".jpg";
        //$base_path = $this->getDispretionPath($base_image_name);
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');

        //$demo = $objectManager->create('\Magento\Framework\App\Filesystem\DirectoryList');
        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $prod_image_dir = $reader->getAbsolutePath(). 'productdesigner/designs/catalog/product/download/';
        
        //$prod_image_dir = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/designs/catalog/product/download/' . $base_image_name;
        //$prod_image_dir = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/designs/catalog/product/download/';






        $new_prod_image = $prod_image_dir . $base_image_name ;
        if (!file_exists($prod_image_dir)) {
            mkdir($prod_image_dir, 0777, true);
        }




        $demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $download_img_url = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/designs/catalog/product/download/' . $base_image_name;
        $dir_path = $reader->getAbsolutePath() . 'productdesigner/designs/catalog/product/download/' . $base_image_name;
        //$dir_path = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/designs/catalog/product/download/';
        $paths = array();
        $paths['download_img_url'] = $download_img_url;
        $paths['dir_path'] = $dir_path;

        $download_url = $this->makeDownloadImage($prod_image_path, $new_prod_image, $resize_width, $resize_height, $image_id, $srcNew, $download_img_url, $parent_images_id_final);



        return $paths;
    }
    
    public function makeDownloadImage($prod_image_path ,$new_prod_image,$resize_width,$resize_height,$image_id,$srcNew,$download_img_url, $parent_images_id_final)
    {


        $newPath_c = $new_prod_image;
        if(!isset($resize_width) && $resize_width == null){
            $resize_width = 650;
        }
        if(!isset($resize_height) && $resize_height == null){
            $resize_height = 650;
        }



        $resize = $this->resizeAndCreateDesignImage($prod_image_path,$newPath_c,$resize_width,$resize_height);




        if($resize){
            $info = getimagesize($newPath_c);
            $imgtype = $info['mime'];
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
                die('Invalid image type.');
            }


            $parent_images_id_final = '@'.$parent_images_id_final;
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Selectionarea\Collection')->addFieldToFilter('image_id',str_replace('@','',$parent_images_id_final));
            $dimensions = $obj_product->getData();
                    //$dimensions = Mage::getModel('productdesigner/selectionarea')->getCollection()->addFieldToFilter('image_id',str_replace('@','',$parent_images_id_final))->getData();






                    // add water mark start
            
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $filesystem = $om->get('Magento\Framework\Filesystem');
            $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $config = $om->create('Magento\Framework\App\Config\ScopeConfigInterface');
            
                    //$logo = $reader->getAbsolutePath(). 'productdesigner/uploadwatermark/'.$config->getValue('productdesigner/downloaddesign_general/watermark');
            
            if (($config->getValue('productdesigner/downloaddesign_general/watermark_text') != null) || ($config->getValue('productdesigner/downloaddesign_general/watermark_text') != '')):
                    //$logo = Mage::getBaseDir('media') . DS ."theme/default/textwatermark.png";
                $logo = $reader->getAbsolutePath(). 'productdesigner/uploadwatermark/default/textwatermark.png';
                
            elseif (($config->getValue('productdesigner/downloaddesign_general/watermark') != null) || ($config->getValue('productdesigner/downloaddesign_general/watermark') != '')):
                           //$logo = Mage::getBaseUrl('media') . DS . 'theme' . DS . Mage::getStoreConfig('productdesigner/downloaddesign_general/watermark');
             $logo = $reader->getAbsolutePath(). 'productdesigner/uploadwatermark/'.$config->getValue('productdesigner/downloaddesign_general/watermark');
         endif;
         
         
                    /*if(($config->getValue('productdesigner/downloaddesign_general/watermark') != null) ||($config->getValue('productdesigner/downloaddesign_general/watermark') != '')):
                        
                    $info = getimagesize($logo);
                    $imgtype = image_type_to_mime_type($info[2]);
                        switch ($imgtype) {
                            case 'image/jpeg':
                                $src = imagecreatefromjpeg($logo);
                                break;
                            case 'image/gif':
                                $src = imagecreatefromgif($logo);
                                break;
                            case 'image/png':
                                //$src = imagecreatefrompng($logo);
                                $percent = 0.5;
                                list($width, $height) = getimagesize($logo);
                                $newwidth = $width * $percent;
                                $newheight = $height * $percent;

                                $thumb = imagecreate($newwidth, $newheight);
                                $source = imagecreatefrompng($logo);
                                imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                                break;
                            default:
                                Mage::throwException('Invalid image type.');   
                        }
                        elseif (($config->getValue('productdesigner/downloaddesign_general/watermark_text') != null) || ($config->getValue('productdesigner/downloaddesign_general/watermark_text') != '')):
               // $thumb = '';
                $info = getimagesize($logo);
                $imgtype = image_type_to_mime_type($info[2]);
                switch ($imgtype) {
                    case 'image/jpeg':
                        $src = imagecreatefromjpeg($logo);
                        break;
                    case 'image/gif':
                        $src = imagecreatefromgif($logo);
                        break;
                    case 'image/png':
                        $percent = 0.5;
                        list($width, $height) = getimagesize($logo);
                        $newwidth = $width * $percent;
                        $newheight = $height * $percent;
                        $thumb = imagecreate($newwidth, $newheight);
                        $source = imagecreatefrompng($logo);
                        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);


                        break;
                    default:
                        Mage::throwException('Invalid image type.');
                }
            endif;*/
            if (($config->getValue('productdesigner/downloaddesign_general/watermark_text') != null) || ($config->getValue('productdesigner/downloaddesign_general/watermark_text') != '')) {
                if (getimagesize($logo)) {
                    $info = getimagesize($logo);
                    $imgtype = image_type_to_mime_type($info[2]);
                    switch ($imgtype) {
                        case 'image/jpeg':
                        $src = imagecreatefromjpeg($logo);
                        break;
                        case 'image/gif':
                        $src = imagecreatefromgif($logo);
                        break;
                        case 'image/png':

                        $percent = 1;
                        list($width, $height) = getimagesize($logo);
                        $newwidth = $width * $percent;
                        $newheight = $height * $percent;

                        $thumb = imagecreate($newwidth, $newheight);
                        $source = imagecreatefrompng($logo);

                        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                        break;
                        default:
                        Mage::throwException('Invalid image type.');
                    }
                    imagecopy($dest, $thumb, 0, 0, 0, 0, imagesx($thumb), imagesy($thumb));
                }
            } else if (($config->getValue('productdesigner/downloaddesign_general/watermark') != null) || ($config->getValue('productdesigner/downloaddesign_general/watermark') != '')) {
                if (getimagesize($logo)) {
                    $info = getimagesize($logo);
                    $imgtype = image_type_to_mime_type($info[2]);
                    switch ($imgtype) {
                        case 'image/jpeg':
                        $src = imagecreatefromjpeg($logo);
                        break;
                        case 'image/gif':
                        $src = imagecreatefromgif($logo);
                        break;
                        case 'image/png':

                            //$src = imagecreatefrompng($logo);

                        list($width, $height) = getimagesize($logo);
                        $newwidth = 375;
                        $newheight = 361;

                        $thumb = imagecreate($newwidth, $newheight);
                        $source = imagecreatefrompng($logo);

                        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

                        break;
                        default:
                        Mage::throwException('Invalid image type.');
                    }
                    imagecopy($dest, $thumb, 100, 200, 20, 13, imagesx($thumb), imagesy($thumb));
                }
            }
               // print_r($thumb);
               // imagecopy($dest, $thumb, 100, 200, 20, 13, imagesx($thumb), imagesy($thumb));
                // add water mark close



            foreach ($dimensions as $d) {
                $x1 = json_decode($d['selection_area'])->x1;
                $y1 = json_decode($d['selection_area'])->y1;
                if (isset($srcNew[$d['design_area_id']]))
                    imagecopy($dest, $srcNew[$d['design_area_id']], $x1, $y1, 0, 0, imagesx($srcNew[$d['design_area_id']]), imagesy($srcNew[$d['design_area_id']]));
            }

            imagesavealpha($dest, true);
            imagejpeg($dest,$newPath_c,100);
            $newPath_c_new =imagecreatefromstring(file_get_contents($newPath_c));
            
                    //copy($newPath_c , $imagepng_new);
                    //imagedestroy($imagepng_new);
            imagedestroy($newPath_c_new);
            imagedestroy($dest);
                    //imagedestroy($src);
                    //imagedestroy($newPath_c);
                    //imagedestroy($srcNew);
                    //imagedestroy($default_prod_image);


        }

        return $download_img_url;

    }
    public function resizeAndCreateDesignImage($source_image,$destination,$tn_w, $tn_h, $quality = 100)
    {
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

        if($imgtype == 'image/png'){
            $file = imagecreatetruecolor($tn_w, $tn_h);
            $new = imagecreatefrompng($source_image);
            $kek=imagecolorallocate($file, 255, 255, 255);
            imagefill($file,0,0,$kek);
            imagecopyresampled($file, $new,(($tn_w - $new_w)/ 2), (($tn_h - $new_h) / 2), 0, 0, $new_w, $new_h, $src_w, $src_h);

            if (imagejpeg($file, $destination, $quality)) {
                return true;
            }

        }else{


            $newpic = imagecreatetruecolor(round($new_w), round($new_h));
            imagecopyresampled($newpic, $source, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);
            $final = imagecreatetruecolor($tn_w, $tn_h);
            $backgroundColor = imagecolorallocate($final, 255, 255, 255);
            imagefill($final, 0, 0, $backgroundColor);
            imagecopy($final, $newpic, (($tn_w - $new_w)/ 2), (($tn_h - $new_h) / 2), 0, 0, $new_w, $new_h);

            if (imagejpeg($final, $destination, $quality)) {
                return true;
            }

        }
        return false;
    }
}
