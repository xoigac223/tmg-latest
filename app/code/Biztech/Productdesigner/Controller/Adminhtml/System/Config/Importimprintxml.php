<?php

/**
 * Copyright © 2017-2018 AppJetty. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\System\Config;

use Biztech\Productdesigner\Helper\Data;
use Biztech\Productdesigner\Model\SelectionareaFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Xml\Parser;

class Importimprintxml extends Action {

    protected $resultJsonFactory;

    /**
     * @var Data
     */
    protected $helper;
    protected $request;
    protected $csvProcessor;
    protected $_imageFactory;
    protected $parser;
    protected $_selectionAreaFactory;
    protected $_resource;
    protected $jsonResultFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
    Context $context, JsonFactory $resultJsonFactory, Data $helper, Parser $parser, \Magento\Framework\App\Request\Http $request, \Magento\Framework\File\Csv $csvProcessor, SelectionareaFactory $selectionAreaFactory, \Magento\Framework\Image\Factory $imageFactory, \Magento\Framework\App\ResourceConnection $resource, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->request = $request;
        $this->_imageFactory = $imageFactory;
        $this->csvProcessor = $csvProcessor;
        $this->parser = $parser;
        $this->_selectionAreaFactory = $selectionAreaFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_resource = $resource;
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute() {

            $import_masking_file = $this->helper->getConfig('productdesigner/import_xml/import_imprint_file');
            $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');

            $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $import_imprint_csv_dir = $reader->getAbsolutePath() . 'productdesigner/importimprintxml/' . $import_masking_file;
            // $import_masking_image_dir = $reader->getAbsolutePath() . 'productdesigner/importMaskingImage/';
            $result = $this->jsonResultFactory->create();
            if (empty($import_masking_file)) {
                /** You may introduce your own constants for this custom REST API */
                $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
                $result->setData(['error_message' => __('Please Upload XML File.')]);
                return $result;
            }
            
            try {
                $parsedArray = $this->parser->load($import_imprint_csv_dir)->xmlToArray();                
            } catch (\Exception $e) {
                $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
                $result->setData(['error_message' => __('Please Upload Valid XML.')]);
                return $result;
            }

            $_productCollection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
            $_productCollection = $_productCollection->addAttributeToSelect('*')->load();
            $imageSideCollection = $this->_objectManager->create('Biztech\Productdesigner\Model\Mysql4\Side\Collection') /* ->addFieldToFilter('imageside_title', array('eq' => '')) */;

            $demoArray = array();
            if (isset($parsedArray['Products']['Product']['ItemNumber'])) {
                $demoArray['Products']['Product'][] = $parsedArray['Products']['Product'];
                $parsedArray = $demoArray;
            }
            $imageSideCollection1 = $this->_objectManager->create('Biztech\Productdesigner\Model\Mysql4\Side\Collection');
            $imageSideCollections = $imageSideCollection1->getData();
            foreach ($parsedArray['Products']['Product'] as $value) {
                // echo $value['ItemNumber'];
                if (!empty($value['ItemNumber'])) {
                    //echo $value['ItemNumber'];
                       if (!isset($value['DecorationMethods'][0])) {
                            $demo = $value['DecorationMethods'];
                            $value['DecorationMethods'] = null;
                            $value['DecorationMethods'][] = $demo;
                        }
                    if (isset($value['DecorationMethods'][0]['DecorationMethod'])) {
                        $i = 0;
                        foreach ($value['DecorationMethods'][0]['DecorationMethod'] as $decorationMethod) {
                            if ($i == 1) {
                                continue;
                            }
                            $i = 1;
                                if (isset($decorationMethod['ImprintAreas'])) {
                                    if (!isset($decorationMethod['ImprintAreas']['ImprintArea'][0])) {
                                        $demo = $decorationMethod['ImprintAreas']['ImprintArea'];
                                        $decorationMethod['ImprintAreas']['ImprintArea'] = null;
                                        $decorationMethod['ImprintAreas']['ImprintArea'][] = $demo;
                                    }
                                    foreach ($decorationMethod['ImprintAreas']['ImprintArea'] as $imprintAreaValue) {
                                        $tmp = 0;
                                        foreach ($_productCollection as $_product) {
                                            $productId = $_product->getEntityId();
                                            if ($_product->getSku() == $value['ItemNumber']) {
                                                $productRepository = $this->_objectManager->get('\Magento\Catalog\Model\ProductRepository');
                                                $sku = $_product->getSku(); // YOUR PRODUCT SKU
                                                $product = $productRepository->get($sku);
                                                $productMediaGalleryEntries = $product->getAllMediaGalleryImages();
                                                /*print_r(get_class_methods($product));
                                                die();
                                                foreach ($productMediaGalleryEntries as $image) {
                                                    print_r($image->getData());
                                                }
                                                die();*/
                                                foreach ($productMediaGalleryEntries as $image) {
                                                    foreach ($imageSideCollections as $imageSideCollection):
                                                        if ($image->getImageSideDefault() == $imageSideCollection['imageside_id'] && strtolower($imprintAreaValue['ImprintLocationName']) == strtolower($imageSideCollection['imageside_title'])) {

                                                            if ($_product->getEnableProductDesigner() == 1 && $image->getValueId() != '' /* && $image->getImageSideDefault() > 0 */) {
                                                                $selectionArea = $this->_getSelectionArea()
                                                                        ->getCollection()
                                                                        ->addFieldToFilter('image_id', $image->getValueId())
                                                                        ->getData();
                                                                if (count($selectionArea) == 0) {
                                                                    $height = (int) $imprintAreaValue['ImprintHeight'] * 96;
                                                                    $width = (int) $imprintAreaValue['ImprintWidth'] * 96;
                                                                    if ($height > 0 && $width > 0) {
                                                                        $configwidth = $this->helper->getConfig('productdesigner/productdesigner_general/design_image_width');
                                                                        $configwidth = $configwidth ? $configwidth : 650;
                                                                        $configheight = $this->helper->getConfig('productdesigner/productdesigner_general/design_image_height');
                                                                        $configheight = $configheight ? $configheight : 650;
                                                                        $productImageSize = getimagesize($image['path']);
                                                                        $productImageWidth = $productImageSize[0] / $configwidth;
                                                                        $productImageHeight = $productImageSize[1] / $configheight;
                                                                        $height = $height / $productImageWidth;
                                                                        $width = $width / $productImageHeight;
                                                                        $owidth = round((int) $width / 37.795275591, 2);
                                                                        $oheight = round((int) $height / 37.795275591, 2);

                                                                        $x1 = ((int) $configwidth / 2 - (int) $width / 2);
                                                                        $x2 = ((int) $configwidth / 2 + (int) $width / 2);
                                                                        $y1 = ((int) $configheight / 2 - (int) $height / 2);
                                                                        $y2 = ((int) $configheight / 2 + (int) $height / 2);

                                                                        $selection_area_arr = [
                                                                            'width' => $width,
                                                                            'height' => $height,
                                                                            'x1' => $x1,
                                                                            'y1' => $y1,
                                                                            'x2' => $x2,
                                                                            'y2' => $y2,
                                                                            'owidth' => $owidth,
                                                                            'oheight' => $oheight,
                                                                            'image_id' => $image->getValueId(),
                                                                        ];

                                                                        $selection = [
                                                                            'image_id' => $image->getValueId(),
                                                                            'selection_area' => json_encode($selection_area_arr),
                                                                            'product_id' => $productId,
                                                                            'imageside_id' => $image->getImageSideDefault(),
                                                                        ];
                                                                        //  ImprintDefaultLocation
                                                                        if (isset($imprintAreaValue['ImprintDefaultLocation'])) {
                                                                            /* echo "//";
                                                                              echo $imprintAreaValue['ImprintDefaultLocation'];
                                                                              echo "//";
                                                                              print_r($imprintAreaValue);
                                                                             */
                                                                            $imprintDefaultLocation = ($imprintAreaValue['ImprintDefaultLocation'] == 'Y') ? 1 : 0;
                                                                            $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
                                                                            $data = ['is_imprintdefaultlocation' => $imprintDefaultLocation];
                                                                            $table = $connection->getTableName('catalog_product_entity_media_gallery_value');
                                                                            $connection->update($table, $data, ['value_id = ?' => $image->getValueId()]);
                                                                        }

                                                                        try {
                                                                            $selectionareaModel = $this->_getSelectionArea()
                                                                                            ->setData($selection)->save();
                                                                        } catch (\Exception $e) {
                                                                            $this->_logger->critical($e->getMessage());
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    endforeach;
                                                }
                                            }
                                        }
                                    }
                                }
                            
                        }
                    }
                }
            }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['success' => 'true']);
        /** @var \Magento\Framework\Controller\Result\Json $result */
    }

    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Biztech_Productdesigner::config');
    }

    protected function getDispretionPath($fileName) {
        $char = 0;
        $dispretionPath = '';
        while (($char < 2) && ($char < strlen($fileName))) {
            if (empty($dispretionPath)) {
                $dispretionPath = DIRECTORY_SEPARATOR
                        . ('.' == $fileName[$char] ? '_' : $fileName[$char]);
            } else {
                $dispretionPath = self::_addDirSeparator($dispretionPath)
                        . ('.' == $fileName[$char] ? '_' : $fileName[$char]);
            }
            $char++;
        }
        return $dispretionPath;
    }

    static protected function _addDirSeparator($dir) {
        if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
            $dir .= DIRECTORY_SEPARATOR;
        }
        return $dir;
    }

    protected function _getSelectionArea() {
        return $this->_selectionAreaFactory->create();
    }

    public function getSideIdFromName($imageside_title) {
        $imageside_id = $this->_objectManager->create('Biztech\Productdesigner\Model\Mysql4\Side\Collection')
                        ->addFieldToFilter('imageside_title', $imageside_title)->getFirstItem()->getImagesideId();
        return $imageside_id;
    }

    protected function processNewAndExistingImages($product, array &$images) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $galleryvalue = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Gallery');
        foreach ($images['images'] as &$image) {
            if (empty($image['removed'])) {
                $data = [];
                $galleryvalue->deleteGalleryValueInStore(
                        $image['value_id'], $product->getData('entity_id'), $product->getStoreId()
                );
                $data['value_id'] = $image['value_id'];
                $data['label'] = isset($image['label']) ? $image['label'] : '';
                $data['position'] = isset($image['position']) ? (int) $image['position'] : 0;
                $data['disabled'] = isset($image['disabled']) ? (int) $image['disabled'] : 0;
                $data['store_id'] = (int) $product->getStoreId();
                $data['entity_id'] = $product->getData('entity_id');
                if (isset($image['image_side'])) {
                    $data['image_side'] = $image['image_side'];
                } else if (isset($image['image_side_default'])) {
                    $data['image_side'] = $image['image_side_default'];
                }
                if (isset($image['is_imprintdefaultlocation'])) {
                    $data['is_imprintdefaultlocation'] = $image['is_imprintdefaultlocation'];
                }
                $galleryvalue->insertGalleryValueInStore($data);
            }
        }
    }

}
