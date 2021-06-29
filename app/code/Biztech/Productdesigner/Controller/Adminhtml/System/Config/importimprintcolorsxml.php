<?php

/**
 * Copyright © 2017-2018 AppJetty. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\System\Config;

use Biztech\Productdesigner\Helper\Data;
use Biztech\Productdesigner\Model\SelectionareaFactory;
use Biztech\Productdesigner\Model\Stockcolors;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Xml\Parser;

class importimprintcolorsxml extends \Magento\Framework\App\Action\Action {

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
    protected $_dataStockColor;
    //protected $_objectManager;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
    Context $context, JsonFactory $resultJsonFactory, Data $helper, Parser $parser, Stockcolors $dataStockColor, /*\Magento\Framework\ObjectManagerInterface $objectManager,*/ \Magento\Framework\App\Request\Http $request, \Magento\Framework\File\Csv $csvProcessor, SelectionareaFactory $selectionAreaFactory, \Magento\Framework\Image\Factory $imageFactory, \Magento\Framework\App\ResourceConnection $resource, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
    ) {
        $this->_dataStockColor = $dataStockColor;
        /*$this->_objectManager = $objectManager;*/
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

        /*
          $model = $this->_dataStockColor;
          $model->addData([
          "stockcolor_product_id" => 'p1',
          "stockcolor_product_id"=>'111',
          "stockcolor_product_pmscolorid" => 'PMS167',
          "stockcolor_product_colorswatch" => 'darkorange.gif'
          ]);
          $saveData = $model->save();
          if ($saveData) {
          echo "done";
          }
         */
        $import_colors_xml_file = $this->helper->getConfig('productdesigner/import_xml/import_imprint_colors_xml');

        $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');

        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $import_imprint_csv_dir = $reader->getAbsolutePath() . 'productdesigner/importimprintcolorsxml/' . $import_colors_xml_file;
        $pentoneColorFile = $reader->getAbsolutePath() . 'productdesigner/importimprintcolorsxml/pantone_CMYK_RGB_Hex.json';
        $pantone_json = file_get_contents($pentoneColorFile);
        $pantone = json_decode($pantone_json, true);


        // $import_masking_image_dir = $reader->getAbsolutePath() . 'productdesigner/importMaskingImage/';
        $result = $this->jsonResultFactory->create();
        if (empty($import_colors_xml_file)) {
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

        $_productCollection = $this->_objectManager->create('Magento\Catalog\Model\Product')->getCollection();
        //$_productCollection = $_productCollection->addAttributeToSelect('*')->load();
        /* ->addFieldToFilter('imageside_title', array('eq' => '')) */;

        $demoArray = array();
        if (isset($parsedArray['Products']['Product']['ItemNumber'])) {
            $demoArray['Products']['Product'][] = $parsedArray['Products']['Product'];
            $parsedArray = $demoArray;
        }
        foreach ($parsedArray['Products']['Product'] as $value) {
            //echo $value['StockColorCode'];
            // echo $value['ItemNumber'];
            $colorsCodeInfo = "";
            if (!empty($value['ItemNumber'])) {
                foreach ($_productCollection as $_product) {
                    $productId = $_product->getId();
                    if ($_product->getSku() == $value['ItemNumber']) {//Load Product collection by SKU
                        if (!isset($value['DecorationMethods'][0])) {
                            $demo = $value['DecorationMethods'];
                            $value['DecorationMethods'] = null;
                            $value['DecorationMethods'][] = $demo;
                        }
                        $allowPMSColor;
                        if (isset($value['DecorationMethods'][0]['DecorationMethod'])) {
                            $count = 1;
                            $i = 0;
                            foreach ($value['DecorationMethods'][0]['DecorationMethod'] as $decorationMethod) {
                                if (isset($decorationMethod['AllowPMSColor'])) {

                                    if ($count == 1) {
                                        $allowPMSColor = $decorationMethod['AllowPMSColor'];
                                    }

                                    $count++;
                                }
                                if ($i == 1) {
                                    continue;
                                }
                                $i = 1;
                                $colorcodeArry = array();
                                $colorsArray = array();
                                if (isset($decorationMethod['StockColors'])) {
                                    foreach ($decorationMethod['StockColors']['StockColor'] as $Stockcolorvalue) {

                                        if (isset($Stockcolorvalue['PMSColorID']) && isset($Stockcolorvalue['ColorSwatch'])) {
                                            $pmscolorcode = $Stockcolorvalue['PMSColorID'];
                                            $str2 = substr($pmscolorcode, 3);
                                            $pmshexcode = '';
                                            foreach ($pantone as $key => $value5) {
                                                if ($value5["Code"] == $str2) {
                                                    $pmshexcode = $value5["Hex"];
                                                }
                                            }
                                            if ($pmshexcode != null) {
                                                $colorsArray = array(
                                                    "StockColorCode" => $Stockcolorvalue['StockColorCode'],
                                                    "PMSColorID" => $pmshexcode,
                                                    "ColorSwatch" => $Stockcolorvalue['ColorSwatch']
                                                );
                                                array_push($colorcodeArry, $colorsArray);
                                            }
                                        }
                                    }
                                    // print_r($colorcodeArry);
                                    $colorsCodeInfo = json_encode($colorcodeArry);
                                }
                            }
                        }

                        $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Stockcolors'); //$this->_dataStockColor;                                           

                        $customer_data = $model->getCollection()->addFieldToFilter('stockcolor_product_id', array('eq' => $productId))->getFirstItem();
                        if (count($customer_data) > 0) {
                            $model->addData([
                                "product_stockcolor_id" => $customer_data->getProductStockcolorId(),
                                "stockcolor_product_id" => $productId,
                                "stockcolor_product_colorsinfo" => $colorsCodeInfo,
                            ]);
                        } else {
                            $model->addData([
                                "stockcolor_product_id" => $productId,
                                "stockcolor_product_colorsinfo" => $colorsCodeInfo,
                            ]);
                        }
                        $model->save(); //Save data

                        if ($allowPMSColor != null) {
                            if (strtolower($allowPMSColor) == 'n') {
                                $allowPMSColor_id = 0;
                            } else {
                                $allowPMSColor_id = 1;
                            }
                            //Update Allows PMS Color value
                            $_productdata = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                            $_productdata->setAllowPmsColor($allowPMSColor_id);
                            $_productdata->save();
                            //end:Update Allows PMS Color value
                        }
                    }//End Product Collection by SKU
                }
                //echo $value['ItemNumber'];
            }
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['success' => 'true']);
        /** @var \Magento\Framework\Controller\Result\Json $result */
    }
}
