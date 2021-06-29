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

class Importlocationcsv extends Action {

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
    protected $_fileCsv;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
    Context $context, JsonFactory $resultJsonFactory, Data $helper, Parser $parser, \Magento\Framework\App\Request\Http $request, \Magento\Framework\File\Csv $csvProcessor, SelectionareaFactory $selectionAreaFactory, \Magento\Framework\Image\Factory $imageFactory, \Magento\Framework\App\ResourceConnection $resource, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
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


        $import_information_csv = $this->helper->getConfig('productdesigner/import_xml/import_information_csv');

        $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');

        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $import_imprint_csv_dir = $reader->getAbsolutePath() . 'productdesigner/importlocationcsv/' . $import_information_csv;
        // $import_masking_image_dir = $reader->getAbsolutePath() . 'productdesigner/importMaskingImage/';
        $result = $this->jsonResultFactory->create();
        if (empty($import_information_csv)) {
            /** You may introduce your own constants for this custom REST API */
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
            $result->setData(['error_message' => __('Please Upload CSV File.')]);
            return $result;
        }


        try {
            $parsedArray = $this->csvProcessor->getData($import_imprint_csv_dir);

            //$csvData = $this->csv->getData($import_imprint_csv_dir);
        } catch (\Exception $e) {
            $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
            $result->setData(['error_message' => __('Please Upload Valid CSV.')]);
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
        $row_num = 0;
        foreach ($parsedArray as $value) {
            if ($row_num > 0) {
                foreach ($_productCollection as $_product) {
                    if ($_product->getSku() == $value[0]) {
                        $productRepository = $this->_objectManager->get('\Magento\Catalog\Model\ProductRepository');
                        $sku = $_product->getSku(); // YOUR PRODUCT SKU
                        $product = $productRepository->get($sku);
                        $productMediaGalleryEntries = $product->getAllMediaGalleryImages();
                        foreach ($productMediaGalleryEntries as $image) {
                            if ($image->getDisabledDefault()) {//Only work on Hide images
                                if (!$image->getImageSideDefault()) {//Check Image Side set or not
                                    $img_name = $image->getFile();
                                    $img_arr = explode("/", $img_name);
                                    $total_imgarr = count($img_arr);
                                    $product_img_name = strtolower($img_arr[$total_imgarr - 1]);
                                    $csv_img_arry = explode(",", $value[1]);
                                    $csv_img_side_arry = explode(",", $value[2]);
                                    for ($i = 0; $i < count($csv_img_arry); $i++) {
                                        if ($product_img_name == strtolower($csv_img_arry[$i])) {
                                            foreach ($imageSideCollections as $imageSideCollection):
                                                if (strtolower($csv_img_side_arry[$i]) == strtolower($imageSideCollection['imageside_title'])) {
                                                    $get_img_sideId = $imageSideCollection['imageside_id']; //get id of image side
                                                    if (isset($get_img_sideId)) {
                                                        $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
                                                        $data = ['image_side' => $get_img_sideId];
                                                        $table = $connection->getTableName('catalog_product_entity_media_gallery_value');
                                                        $connection->update($table, $data, ['value_id = ?' => $image->getValueId()]);
                                                        // echo "Update Done";
                                                    }
                                                }
                                            endforeach;
                                        }
                                    }
                                }//End://Check Image Side set or not
                            }//Only work on Hide images                            
                        }
                    }
                }
            }
            $row_num++;
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['success' => 'true']);
        /** @var \Magento\Framework\Controller\Result\Json $result */
    }
}