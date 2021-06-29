<?php

namespace Biztech\Productdesigner\Helper;

use Magento\Catalog\Block\Product\View\Gallery;

class Info extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_scopeConfig;
    protected $_helper;

    const ResizeWidth = 'productdesigner/general/imagewidth';
    const ResizeHeight = 'productdesigner/general/imageheight';

    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Helper\Image $helper, \Magento\Framework\Image\Factory $imageFactory
    ) {
        $this->_helper = $helper;
        $this->_scopeConfig = $scopeConfig;
        $this->imageFactory = $imageFactory;
    }

    public function isPdEnable($product_id) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $isPdEnable = '';
        $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
        $product = $obj_product->load($product_id);
        if (!$product->getEnableProductDesigner()) {
            $isPdEnable = 0;
        } else {
            $isPdEnable = $product->getEnableProductDesigner();
        }

        return $isPdEnable;
    }

    public function getProductInfo($id) {

        $defaulImageId = '';

        $defaulImageId = $this->getDefaultImage($id) ? $this->getDefaultImage($id) : '0';
        $resize_width = $this->_scopeConfig->getValue(self::ResizeWidth, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $resize_height = $this->_scopeConfig->getValue(self::ResizeHeight, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


        if (!isset($resize_width) && $resize_width == null) {
            $resize_width = 650;
        }
        if (!isset($resize_height) && $resize_height == null) {
            $resize_height = 650;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
        $product = $obj_product->load($id);

        $product_type = $product->getTypeId();

        if ($product_type == 'configurable') {

            $defaulImageId = $product->getDefaultColor();
            $obj_product = $objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
            //$childProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($id);
            $childProducts = $obj_product->getChildrenIds($id);

            $attrs = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
            foreach ($attrs as $attr) {
                if (0 == strcmp("color", $attr['attribute_code'])) {
                    $colors = $attr['values'];
                }
            }



            $image = array();
            foreach ($colors as $color) {
                $image = array();

                foreach ($childProducts[0] as $childProductid) {

                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
                    $childProduct = $obj_product->load($childProductid);

                    if ($color['value_index'] == $childProduct->getColor()) {


                        if (count($childProduct->getAllMediaGalleryImages()) > 0) {


                            
                            foreach ($childProduct->getAllMediaGalleryImages() as $product_image) {
                                if ($product_image->getDisabled()!=1 ) {
                                        continue;
                                }
                                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                /* $customOption = $objectManager->create('Magento\Catalog\Api\Data\ProductCustomOptionInterface');
                                  $customOption->setTitle('Text')
                                  ->setType('area')
                                  ->setIsRequire(true)
                                  ->setSortOrder(1)
                                  ->setPrice(1.00)
                                  ->setPriceType('fixed')
                                  ->setMaxCharacters(50)
                                  ->setProductSku($product->getSku());


                                  $customOptions[] = $customOption;
                                  $childProduct->setOptions($customOptions)->save(); */
                                $b = $product_image->getFile();
                                $imageSideCollection1 = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Side\Collection')->addFieldToFilter('status', array('eq' => 1));
                                $imageSideCollection = $imageSideCollection1->getData();
                                foreach ($imageSideCollection as $imageSide):
                                    if ($product_image->getImageSideDefault() == $imageSide['imageside_id']) {
                                        $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Selectionarea\Collection')->addFieldToFilter('product_id', $id)->addFieldToFilter('imageside_id', $product_image->getImageSideDefault());
                                        $dimensions = $obj_product->getData();
                                        $side = $imageSide['imageside_title'];
                                    }
                                endforeach;
                                //$obj_product = $objectManager->create('Biztech\Productdesigner\Model\Resource\Selectionarea\Collection')->addFieldToFilter('product_id', $id)->addFieldToFilter('imageside_id', '1');
                                //$dimensions = $obj_product->getData();
                                //$side = 'front';



                                $image_url = $this->_helper->init($product, 'product_page_image')->setImageFile($product_image->getFile())->resize($resize_width, $resize_height)->keepAspectRatio(true)->constrainOnly(false)->getUrl();

                                list($width, $height, $type, $attr) = getimagesize($image_url);
                                //list($orig_width) = getimagesize(Mage::helper('catalog/image')->init($product, 'image', $product_image->getFile())->__toString());
                                list($orig_width) = getimagesize($this->_helper->init($product, 'product_page_image')->setImageFile($product_image->getFile())->keepAspectRatio(true)->constrainOnly(false)->getUrl());

                                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
                                $currentStore = $storeManager->getStore();
                                $path = $objectManager->create('Biztech\Productdesigner\Model\Maskingmedia')->load($dimensions[0]['masking_image_id'])->getImagePath();
                                //echo $path;die; 
                                if($path)                      
                                    $path = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/masking' . $path;
                                if (isset($dimensions) && count($dimensions) > 1):



                                    $selectionAreanew = array();
                                    for ($i = 0; $i < count($dimensions); $i++) {
                                        $strng1 = substr($dimensions[$i]['selection_area'], 0, -1);
                                        $strng2 = $strng1 . ',"design_area_id":"' . $dimensions[$i]['design_area_id'] . '","masking_image_path":"' . $path . '"}';
                                        $selectionAreanew[$i] = $strng2;
                                    }
                                    $selectionAreanewImpolode = '[' . implode(',', $selectionAreanew) . ']';


                                    $imageSideNew = array();
                                    for ($i = 0; $i < count($dimensions); $i++) {
                                        $imageSideNew[$i] = $product_image->getImageSideDefault() . '-' . $i;
                                    }


                                    $image['@' . $product_image->getId()] = array(
                                        'dim' => json_decode($selectionAreanewImpolode),
                                        'image_id' => $product_image->getId(),
                                        'url' => $image_url,
                                        'width' => $width,
                                        'height' => $height,
                                        'orig_width' => $orig_width,
                                        'is_configurable' => 1,
                                        'side' => $side,
                                        'image_side' => $product_image->getImageSideDefault(),
                                        //'image_side' => 1,
                                        'image_side_area' => $imageSideNew
                                    );

                                else:
                                    $path = $objectManager->create('Biztech\Productdesigner\Model\Maskingmedia')->load($dimensions[0]['masking_image_id'])->getImagePath();
                                //echo $path;die; 
                                if($path)                      
                                    $path = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/masking' . $path;
                                    if (isset($dimensions) && count($dimensions) > 0) {
                                        $imageSideNew = array();
                                        for ($i = 0; $i < count($dimensions); $i++) {
                                            $imageSideNew[$i] = "1" . '-' . $i;
                                        }

                                        
                                        $image['@' . $product_image->getId()] = array(
                                            'dim' => isset($dimensions[0]['selection_area']) ? json_decode($dimensions[0]['selection_area']) : null,
                                            'image_id' => $product_image->getId(),
                                            'url' => $image_url,
                                            'designArea_id' => isset($dimensions[0]['design_area_id']) ? $dimensions[0]['design_area_id'] : null,
                                            'width' => $width,
                                            'height' => $height,
                                            'orig_width' => $orig_width,
                                            'masking_image_path' => $path,
                                            'is_configurable' => 1,
                                            'side' => $side,
                                            //'image_side' => 1,
                                            'image_side' => $product_image->getImageSideDefault(),
                                            'image_side_area' => $imageSideNew,
                                        );
                                    }
                                endif;
                            }
                        }
                    }
                }
                if (isset($image)) {
                    $images_color[$color['value_index']] = $image;
                }
            }
        } else {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
            $product = $obj_product->load($id);
            $product->getResource()->getAttribute('media_gallery')
                    ->getBackend()->afterLoad($product);
            $product_images = $product->getAllMediaGalleryImages();
            $images_color=array();
            if ($product_images) {
                    
                foreach ($product->getAllMediaGalleryImages() as $product_image) {

                    if ($product_image->getDisabled()!=1) {
                        continue;
                    }
                    // die();
                    $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Selectionarea\Collection')->addFieldToFilter('product_id', $id)->addFieldToFilter('imageside_id', $product_image->getImageSideDefault());
                    $dimensions = $obj_product->getData();
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
                    $currentStore = $storeManager->getStore();
                    $path = '';
                    if(isset($dimensions[0]['masking_image_id'])){
                        $path = $objectManager->create('Biztech\Productdesigner\Model\Maskingmedia')->load($dimensions[0]['masking_image_id'])->getImagePath();
                    }
                    //echo $path;die;  
                    if($path)                      
                        $path = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/masking' . $path;
                    if (isset($dimensions) && $dimensions != null) {

                        //$image_url = Mage::helper('catalog/image')->init($product, 'image', $product_image->getFile())->resize($resize_width, $resize_height)->keepAspectRatio(true)->constrainOnly(true)->__toString();
                        $image_url = $this->_helper->init($product, 'product_page_image')->setImageFile($product_image->getFile())->resize($resize_width, $resize_height)->keepAspectRatio(true)->constrainOnly(false)->getUrl();

                        list($width, $height, $type, $attr) = getimagesize($image_url);
                        //list($orig_width) = getimagesize(Mage::helper('catalog/image')->init($product, 'image', $product_image->getFile())->__toString());
                        list($orig_width) = getimagesize($this->_helper->init($product, 'product_page_image')->setImageFile($product_image->getFile())->keepAspectRatio(true)->constrainOnly(false)->getUrl());
                        //list($orig_width) = getimagesize($image_url);


                        if (count($dimensions) > 1):
                            $selectionAreanew = array();


                            for ($i = 0; $i < count($dimensions); $i++) {
                                $strng1 = substr($dimensions[$i]['selection_area'], 0, -1);
                                $strng2 = $strng1 . ',"design_area_id":"' . $dimensions[$i]['design_area_id'] . '","masking_image_path":"' . $path . '"}';
                                $selectionAreanew[$i] = $strng2;
                            }
                            $selectionAreanewImpolode = '[' . implode(',', $selectionAreanew) . ']';



                            $imageSideNew = array();
                            for ($i = 0; $i < count($dimensions); $i++) {
                                $imageSideNew[$i] = $product_image->getImageSideDefault() . '-' . $i;
                            }


                            $image['@' . $product_image->getId()] = array(
                                'url' => $image_url,
                                'image_id' => $product_image->getId(),
                                //'dim'      =>Mage::helper('core')->jsonDecode($dimensions[0]['selection_area']),
                                'dim' => json_decode($selectionAreanewImpolode),
                                'width' => $width,
                                'height' => $height,
                                'orig_width' => $orig_width,
                                'image_side' => $product_image->getImageSideDefault(),
                                'image_side_area' => $imageSideNew,
                                'is_configurable' => 0
                            );


                        else:


                            $imageSideNew = array();
                            for ($i = 0; $i < count($dimensions); $i++) {
                                $imageSideNew[$i] = $product_image->getImageSideDefault() . '-' . $i;
                            }

                            $image['@' . $product_image->getId()] = array(
                                'url' => $image_url,
                                'image_id' => $product_image->getId(),
                                'designArea_id' => $dimensions[0]['design_area_id'],
                                'masking_image_path' => $path,
                                'dim' => json_decode($dimensions[0]['selection_area']),
                                'width' => $width,
                                'height' => $height,
                                'orig_width' => $orig_width,
                                'image_side' => $product_image->getImageSideDefault(),
                                'image_side_area' => $imageSideNew,
                                'is_configurable' => 0
                            );

                        endif;
                    }
                }
            }
            $image = isset($image) ? $image : '';
            $images_color[$defaulImageId] = $image;
        }



        if ($product_type == 'configurable') {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $configprintingCollection = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Printingmethod\Collection')->addFieldToFilter('status', array('eq' => 1));

            if (count($configprintingCollection) != 0):
                foreach ($configprintingCollection as $config_print_method) {
                    $product_tier_price_details1 = array();
                    if ($config_print_method->getColortype() == 1) {
                        foreach (explode(',', $config_print_method->getColorqty()) as $print_data) {
                            $colorObject = $objectManager->create('Biztech\Productdesigner\Model\Colors')->load($print_data)->getData();
                            if(!isset($colorObject['colors_counter'])){
                                $colorObject['colors_counter'] = 1;
                            }
                            if(!isset($colorObject['colors_price'])){
                                $colorObject['colors_price'] = 0;
                            }
                            $product_details[] = array(
                                'printing_id' => $config_print_method->getId(),
                                'color_counter' => $colorObject['colors_counter'],
                                'fixed_product_price' => $colorObject['colors_price'],
                            );
                        }
                        $print_method_array[$config_print_method->getId()] = array(
                            'printing_type' => $config_print_method->getColortype(),
                            'printing_name' => $config_print_method->getPrintingName(),
                            'product_details' => $product_details,
                            'minimum_quantity' => $config_print_method->getMinimumQuantity(),
                        );
                    } else {

                        foreach (explode(',', $config_print_method->getAreasize()) as $print_data) {
                            $areaObject = $objectManager->create('Biztech\Productdesigner\Model\Areasize')->load($print_data)->getData();

                            $product_details[strtolower(str_replace(" - ", "_", trim($areaObject['area_size'])))] = array(
                                'printing_id' => $config_print_method->getId(),
                                'area_size' => $areaObject['area_size'],
                                'product_id_by_color_price' => $areaObject['area_price'],
                            );
                        }
                        $print_method_array[$config_print_method->getId()] = array(
                            'printing_type' => $config_print_method->getColortype(),
                            'printing_name' => $config_print_method->getPrintingName(),
                            'product_details' => $product_details,
                            'minimum_quantity' => $config_print_method->getMinimumQuantity(),
                        );
                    }
                }
            else:
                $print_method_array = null;
            endif;
        } else if ($product_type == 'simple') {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $simpleprintingCollection = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Simpleprintingmethod\Collection')->addFieldToFilter('status', array('eq' => 1));

            if (count($simpleprintingCollection) != 0):
                foreach ($simpleprintingCollection as $print_method) {
                    if ($print_method->getStatus() == 1) {
                        $print_method_array[$print_method['simpleprinting_id']] = array(
                            'printing_name' => $print_method['simpleprinting_name'],
                            'minimum_quantity' => $print_method['minimum_quantity'],
                            'product_details' => "",
                            'front_surcharge' => $print_method['front_surcharge'],
                        );
                    }
                }
            else:
                $print_method_array = null;
            endif;
        }




        $images_data['images'] = $images_color;
        $images_data['default_color'] = $defaulImageId;
        $images_data['printing_method_array'] = $print_method_array;

        //$images_data['artist_image_url'] = $defaulImageId['image_url'];

        $jsonData = json_encode($images_data);


        return $jsonData;
    }

    public function getDefaultImage($id) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
        $product = $obj_product->load($id);
        // $product = Mage::getModel('catalog/product')->load($id);
        $product_type = $product->getTypeId();
        if ($product_type == 'configurable') {
            return $defaulImageId = $product->getDefaultColor();
        }
    }

    public function checkDesignArea($id) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Selectionarea\Collection')->addFieldToFilter('product_id', $id);
        $dimensions = $obj_product->getData();
        if (isset($dimensions) && $dimensions != null) {
            return true;
        } else {
            return false;
        }
    }

}
