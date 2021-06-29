<?php

namespace Themagnet\Productimport\Model;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Catalog\Model\Product\Url;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Xml\Parser;

/**
 * Class Themagnet\Productimport\Model\Xmltocsv
 */
class Xmltocsv extends \Magento\Framework\Model\AbstractModel
{
    const OUT_PUT_FILE = 'newfile.csv';
    const OUT_PUT_FILE_SIMPLE = 'newfile_simple.csv';
    const OUT_PUT_FILE_QTY = 'newfile_qty.csv';
    const OUT_PUT_FILE_COLOR = 'newfile_color.csv';
    const OUT_PUT_FILE_ADDITIONAL = 'newfile_additional.csv';
    const PRODUCT_DEFUALT_QTY = '100';
    const PRODUCT_ISINSTOCK = 1;
    const PRODUCT_SIMPLE_COLOR_ATTR = 'None';
    const URL_KEY = 'url_key';
    protected $_finalArray;
    protected $_finalSampleArray;
    protected $_finalQty;
    protected $_finalcolor;
    protected $_finaladditional;
    protected $_additionalcontent;
    protected $_csvfiles;
    protected $_ftpfiles;
    protected $_importlogger;
    protected $_attributeArray;
    protected $_attributeConfigArray;
    protected $_attributeColorArray;
    protected $_productPriceArray;
    protected $_resourceModel;
    protected $connection;
    protected $productUrl;

    /**
     * @var Repository
     */
    protected $attributeRepository;

    /**
     * @var string $defaultProp65
     */
    protected $defaultProp65 = '';

    /**
     * Xmltocsv constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Additionalcontent $additionalcontent
     * @param Csvfiles $csvfiles
     * @param Ftpfiles $ftpfiles
     * @param ResourceConnection $resource
     * @param Url $productUrl
     * @param Repository $attributeRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Additionalcontent $additionalcontent,
        Csvfiles $csvfiles,
        Ftpfiles $ftpfiles,
        ResourceConnection $resource,
        Url $productUrl,
        Repository $attributeRepository,
        array $data = []
    ) {
        $this->attributeRepository  = $attributeRepository;
        $this->_additionalcontent   = $additionalcontent;
        $this->_csvfiles            = $csvfiles;
        $this->_ftpfiles            = $ftpfiles;
        $this->_resourceModel       = $resource;
        $this->productUrl           = $productUrl;
        parent::__construct($context, $registry);
    }

    /**
     * Function getConnection
     *
     * @return AdapterInterface
     */
    protected function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->_resourceModel->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        }

        return $this->connection;
    }

    /**
     * Function getAllXmlFiles
     *
     * @param  Logger $logger
     * @return bool
     */
    public function getAllXmlFiles($logger)
    {
        $this->_importlogger = $logger;
        $ftpConnection = $this->_ftpfiles->getFtpConnection();
        if (!isset($ftpConnection['error'])) {
            $files = $this->_ftpfiles->getFiles();
            $this->_csvfiles->openFiles();
            $this->_ftpfiles->downloadFiles($this->_csvfiles);
            if (is_array($files)) {
                foreach ($files as $key => $filename) {
                    $filename = str_replace('xml-updates/', '', $filename);
                    if (in_array($filename, ['.', '..'])) {
                        continue;
                    }
                    $path = $this->_csvfiles->downloadFilesPath() . $filename;
                    if (file_exists($path)) {
                        $this->getXmlFileName($path);
                    }
                }
            }
            $this->_csvfiles->closeFiles();
            $this->_csvfiles->checkDublicateEntry();
            $this->_importlogger->debugLog((string)__('CSV files created successfully!'));
            return true;
        } else {
            $this->_importlogger->errorLog((string)__('Problem to connect FTP'));
            return false;
        }
    }

    public function getSaticFunction($value, $rowValue)
    {
        $this->_additionalcontent->setAdditionalValue($value, $rowValue);
        return $this->_additionalcontent;
    }

    public function is_multi2($a)
    {
        foreach ($a as $v) {
            if (is_array($v)) {
                return true;
            }
        }
        return false;
    }

    public function createQtyHeader()
    {
        $header = array('ItemNumber','ImprintLocationName');

        $this->_finalQty[] = $header;
        $this->_csvfiles->writeLocationCsv($header);
    }

    public function createColorHeader()
    {
        $header = array('ItemNumber','BRANDNAME','PRINTMETHOD','PRICEMETHOD','PRICINGKEY','STOCKCOLORCODE',
                    'PMSCOLORID','RUBNBUFF','HYPERFILL','COLORSWATCH','stock_pms_swatch','stock_pms');

        $this->_finalcolor[] = $header;
        $this->_csvfiles->writeColorsCsv($header);
    }

    public function createAdditionalHeader()
    {
        $header = array('ItemNumber','BrandName','PriceMethod','PricingKey','AdditionalChargeItemDescriptin','AdditionalChargeCatalogPrice','AdditionalChargeNetPrice','AdditionalChargeDiscountCode','dropshipchg','handlingfee3rdparty','less_than_minimum_qty','dropshipnetchg','handlingfee3rdpartynetchg','less_than_minimum_qty_netchg','additional_stitches_catalog_price_em','digitizing_fee_catalog_price_em','personalization_catalog_price_em','swatchproof_catalog_price_em','additional_stitches_net_price_em','digitizing_fee_net_price_em','personalization_net_price_em','swatchproof_net_price_em','setupchargecatalogprice_tp','setupchargecatalogprice_t4','setupchargecatalogprice_em','setupchargecatalogprice_db','setupchargecatalogprice_dm','setupchargecatalogprice_cg','netsetupcharge_tp','netsetupcharge_t4','netsetupcharge_em','netsetupcharge_dm','netsetupcharge_cg','netsetupcharge_db','setupchargedescription_tp','setupchargedescription_t4','setupchargedescription_em','setupchargedescription_dm','setupchargedescription_cg','minimumorderquantity_tp','minimumorderquantity_t4','minimumorderquantity_em','minimumorderquantity_cg','minimumorderquantity_rs','minimumorderquantity_bl','minimumorderquantity_dm','minimumorderquantity_db');

        $this->_finaladditional[] = $header;
        $this->_csvfiles->writeAdditionalCsv($header);
    }

    public function createSimpleHeader()
    {
        $header = array('ItemNumber','BRANDNAME','PrintMethod','PriceMethod','Select an Option Below','PricingKey','QuantityBreak','CatalogPrice','StandardNetPrice','DISCOUNTCODE','AddColorPrice','AddColorNetPrice','ADDCOLORDISCOUNTCODE','SetupChargeCatalogPrice','NetSetupCharge','SETUPCHARGEDISCOUNTCODE','currencycode','ITEMHASVARIATIONS');

        $this->_finalSampleArray[] = $header;
        $this->_csvfiles->writeSimpleCsv($header);
    }

    public function createHeader()
    {
        $header = array('ItemNumber','BRANDNAME','PrintMethod','PriceMethod','Select an Option Below','PricingKey','QuantityBreak',
                    'CatalogPrice','StandardNetPrice','DISCOUNTCODE','AddColorPrice','AddColorNetPrice','ADDCOLORDISCOUNTCODE','SetupChargeDescription','SetupChargeCatalogPrice','NetSetupCharge',
                    'SETUPCHARGEDISCOUNTCODE','currencycode','associateproduct');

        $this->_finalArray[] = $header;
        $this->_csvfiles->writeConfigCsv($header);
    }

    public function getXmlFileName($filexml)
    {
        if (file_exists($filexml)) {
            $this->convertXmlToCsvFile($filexml);
        }
    }

    public function arrayChangeKeyCaseRecursive($input, $case = null)
    {
        if (!is_array($input)) {
            return true;
        }
        // CASE_UPPER|CASE_LOWER
        if (null === $case) {
            $case = CASE_LOWER;
        }
        if (!in_array($case, array(CASE_UPPER, CASE_LOWER))) {
            return true;
        }
        $input = array_change_key_case($input, $case);
        foreach ($input as $key=>$array) {
            if (is_array($array)) {
                $input[$key] = $this->arrayChangeKeyCaseRecursive($array, $case);
            }
        }
        return $input;
    }

    /**
     * Function convertXmlToCsvFile
     *
     * @param string $xml_file_input
     */
    public function convertXmlToCsvFile($xml_file_input)
    {
        $xml = @simplexml_load_file($xml_file_input);
        $xml = json_decode(json_encode($xml), 1);
        $xml = $this->arrayChangeKeyCaseRecursive($xml, CASE_LOWER);

        if (!isset($xml['product'][0])) {
            $custom['product'][] = $xml['product'];
            $xml = $custom;
        }

        if (isset($xml['product']) && $xml['product'] != '') {
            foreach ($xml['product'] as $key => $value) {
                if (isset($value['itemnumber']) && isset($value['brandname']) && isset($value['decorationmethods'])) {
                    $rowValue = [];
                    $qtyValue = [];
                    $colorValue = [];
                    $additionalValue = [];
                    $priceData = [];
                    if (isset($value['itemhasvariations'])
                        && strtolower($value['itemhasvariations']) == strtolower('YES')
                    ) {
                        $this->getProductAttributeConfig($value);
                        $rowValue['itemvariations'] = isset($value['itemvariations'])?$value['itemvariations']: [];
                    } elseif (isset($value['itemhasvariations'])
                        && strtolower($value['itemhasvariations']) == strtolower('No')
                    ) {
                        $this->getProductAttribute($value);
                    }

                    $rowValue['itemnumber'] = $value['itemnumber'];
                    $rowValue['brandname'] = $value['brandname'];
                    $colorExists = false;
                    $priceDataArray = $value['decorationmethods']['decorationmethod'];
                    if (!isset($priceDataArray[0])) {
                        $priceData[] = $priceDataArray;
                    } else {
                        $priceData = $priceDataArray;
                    }

                    foreach ($priceData as $priceKey=>$priceValue) {
                        $rowValue['setupchargedescription'] = isset($priceValue['setupchargedescription']) ?
                            $priceValue['setupchargedescription'] : '';
                        $rowValue['setupchargecatalogprice'] = isset($priceValue['setupchargecatalogprice']) ?
                            $priceValue['setupchargecatalogprice'] : '';
                        $rowValue['netsetupcharge'] = isset($priceValue['netsetupcharge']) ?
                            $priceValue['netsetupcharge'] : '';
                        $rowValue['maxnumspotcolors'] = isset($priceValue['maxnumspotcolors']) ?
                            $priceValue['maxnumspotcolors'] : '';
                        $rowValue['minimumorderquantity'] = isset($priceValue['minimumorderquantity']) ?
                            $priceValue['minimumorderquantity'] : '';
                        $rowValue['setupchargediscountcode'] = isset($priceValue['setupchargediscountcode']) ?
                            $priceValue['setupchargediscountcode'] : '';
                        $rowValue['itemhasvariations'] = isset($value['itemhasvariations']) ?
                            $value['itemhasvariations'] : '';

                        if (isset($priceValue['printmethod']) && isset($priceValue['pricemethod'])) {
                            if ($priceValue['printmethod'] != 'Random Sample'
                                || $priceValue['pricemethod'] != 'Blank'
                            ) {
                                $rowValue['printmethod'] = isset($priceValue['printmethod']) ?
                                    $priceValue['printmethod'] : '';
                                $rowValue['pricemethod'] = isset($priceValue['pricemethod']) ?
                                    $priceValue['pricemethod'] : '';
                                $rowValue['pricingkey'] = isset($priceValue['pricingkey']) ?
                                    $priceValue['pricingkey'] : '';
                                if (isset($value['itemhasvariations'])
                                    && strtolower($value['itemhasvariations']) == strtolower('YES')
                                ) {
                                    $this->getTyerPrice($priceValue['prices'], $rowValue);
                                } elseif (isset($value['itemhasvariations'])
                                    && strtolower($value['itemhasvariations']) == strtolower('No')
                                ) {
                                    $this->getTyerSimplePrice($priceValue['prices'], $rowValue);
                                }
                                if (isset($priceValue['stockcolors']) && $priceValue['stockcolors'] != '') {
                                    if (empty($priceValue['stockcolors']) !== true) {
                                        $colorValue = $this->stockColor(
                                            $priceValue['stockcolors'],
                                            $colorValue,
                                            $rowValue
                                        );
                                    } else {
                                        $colorValue = $this->stockColorDefault($rowValue);
                                    }
                                    $colorExists = true;
                                }

                                if (isset($priceValue['sdditionalcharges']) && $priceValue['sdditionalcharges'] != '') {
                                    $additionalValue = $this->additionalCharges(
                                        $priceValue['sdditionalcharges'],
                                        $additionalValue,
                                        $rowValue
                                    );
                                }
                            }
                        }

                        if (isset($priceValue['imprintareas']) && $priceValue['imprintareas'] != '') {
                            $qtyValue = $this->imprintLocationName($priceValue['imprintareas'], $qtyValue, $rowValue);
                        }
                    }

                    if (empty($qtyValue) !== true) {
                        $this->writeImprintCsv($qtyValue);
                    } else {
                        $imprintArray[$rowValue['itemnumber']] = ['NA'];
                        $this->writeImprintCsv($imprintArray);
                    }

                    if ($colorExists !== true) {
                        $colorValue = $this->stockColorDefault($rowValue);
                    }
                }
            }
        }
    }

    public function createProductAttributeHeader()
    {
        $header = array('sku','attribute_set_code','product_type','product_websites','name','url_key','weight','description','product_online','tax_class_name','visibility','price','qty','is_in_stock','website_id','item_variation_link','additional_attributes','prop_65','prop65','production_section_summary_bl','production_section_summary_rs','brandname','primary_material','itemwidth','itemheight','itemweight','itemdepth','itemdimensions','imprintdimension','imprinttemplate','imprintmethodsummary','shipping_summary','production_section_summary','shipfromcity','shipfromstate','shipfromzip','shipfromcountry','shipweightinlbs','additional_charges_summary','additional_charges_net_summary','setupcharge_summary','netsetupcharge_summary','itemnumber','imprint_summary_sample','addcolornetprice_summary2','addcolorprice_summary2');

        $this->_attributeArray[] = $header;
        $this->_csvfiles->writeProductAttributeCsv($header);
    }

    public function createProductAttributeConfigHeader()
    {
        $header = array('sku','attribute_set_code','product_type','product_websites','name','url_key','weight','description','product_online','tax_class_name','visibility','price','qty','is_in_stock','website_id','item_variation_link','additional_attributes','prop_65','prop65','production_section_summary_bl','production_section_summary_rs','brandname','primary_material','itemwidth','itemheight','itemweight','itemdepth','itemdimensions','imprintdimension','imprinttemplate','imprintmethodsummary','shipping_summary','production_section_summary','shipfromcity','shipfromstate','shipfromzip','shipfromcountry','shipweightinlbs','additional_charges_summary','additional_charges_net_summary','setupcharge_summary','netsetupcharge_summary','setupcharge_summary','itemnumber','imprint_summary_sample','addcolornetprice_summary2','addcolorprice_summary2');

        $this->_attributeConfigArray[] = $header;
        $this->_csvfiles->writeProductConfigAttributeCsv($header);
    }

    public function createProductAttributeColor()
    {
        $header = array('sku','color');

        $this->_attributeColorArray[] = $header;
        $this->_csvfiles->writeProductColorCsv($header);
    }

    public function getProductAttribute($value)
    {
        if (empty($this->_attributeArray) !== false) {
            $this->createProductAttributeHeader();
        }
        //print_r($value); exit;
        $productType = 'simple';
        $data= array();
        if (isset($value['itemnumber']) && $value['itemnumber'] != '') {
            $product_detail = $this->getProductQty($value['itemnumber']);
            $itemName = (isset($value['itemname']))?$value['itemname']:'';
            $url_key = $this->getUrlKey($value['itemnumber'], $itemName);
            /*echo $value['itemnumber'];
            print_r($product_detail); exit;*/
            $price = $this->getProductPrice($value);
            $colorValue = self::PRODUCT_SIMPLE_COLOR_ATTR;
            $qty = self::PRODUCT_DEFUALT_QTY;
            $isInStock = self::PRODUCT_ISINSTOCK;
            if (isset($product_detail['qty'])) {
                $qty = $product_detail['qty'];
                $isInStock = $product_detail['is_in_stock'];
            }
            $webattentionflags = $this->getWebattentionflags($value);
            $prop_65 = $this->getProp65($value);
            $imprintValue = $this->getImprintValue($value);
            $shipFromLocations = $this->getShipFromLocations($value);
            $productionsectionsummary = $this->getProductionSectionSummary($value);
            $additionalchargessummary = $this->getAdditionalChargesSummary($value);
            //print_r($imprintValue); exit;
            $additional_attributes = array();
            if ($webattentionflags != '') {
                $additional_attributes[] = 'webattentionflags='.$webattentionflags;
            }
            if ($colorValue != '') {
                $additional_attributes[] = 'color='.$colorValue;
            }
            $additional_attributes = implode(\Themagnet\Productimport\Model\Productattributs::FIELD_SEPRATOR, $additional_attributes);
            //echo "<prE>";
            //print_r($additionalchargessummary); exit;
            $data = array($value['itemnumber'],
                          'Default',
                          $productType,
                          'base',
                          $itemName,
                          $url_key,
                          (isset($value['itemweightinlbs']))?$value['itemweightinlbs']:'',
                          (isset($value['itemdescription']))?$value['itemdescription']:'',
                          1,
                          'Taxable Goods',
                          'Catalog, Search',
                          (isset($price['price']))?$price['price']:'',
                          $qty,
                          $isInStock,
                          0,
                          '',
                          $additional_attributes,
                          $prop_65['prop_65'],
                          $prop_65['prop65'],
                          (isset($productionsectionsummary['production_section_summary_bl']))?$productionsectionsummary['production_section_summary_bl']:'',
                          (isset($productionsectionsummary['production_section_summary_rs']))?$productionsectionsummary['production_section_summary_rs']:'',
                          (isset($value['brandname']))?$value['brandname']:'',
                          (isset($value['primarymaterial']))?$value['primarymaterial']:'',
                          (isset($value['itemwidth']))?$value['itemwidth']:'',
                          (isset($value['itemheight']))?$value['itemheight']:'',
                          (isset($value['itemweightinlbs']))?$value['itemweightinlbs']:'',
                          (isset($value['itemdepth']))?$value['itemdepth']:'',
                          (isset($value['itemdimensions']))?$value['itemdimensions']:'',
                          (isset($imprintValue['imprintdimensions']))?$imprintValue['imprintdimensions']:'',
                          (isset($imprintValue['imprinttemplate']))?$imprintValue['imprinttemplate']:'',
                          (isset($imprintValue['imprintmethodsummary']))?$imprintValue['imprintmethodsummary']:'',
                          (isset($productionsectionsummary['shipping_summary']))?$productionsectionsummary['shipping_summary']:'',
                          (isset($productionsectionsummary['production_section_summary']))?$productionsectionsummary['production_section_summary']:'',
                          (isset($shipFromLocations['shipfromcity']))?$shipFromLocations['shipfromcity']:'',
                          (isset($shipFromLocations['shipfromstate']))?$shipFromLocations['shipfromstate']:'',
                          (isset($shipFromLocations['shipfromzipcode']))?$shipFromLocations['shipfromzipcode']:'',
                          (isset($shipFromLocations['shipfromcountry']))?$shipFromLocations['shipfromcountry']:'',
                          $this->getShipWeightinLBs($value),
                          (isset($additionalchargessummary['additional_charges_summary0']))?$additionalchargessummary['additional_charges_summary0']:'',
                          (isset($additionalchargessummary['additional_charges_net_summary0']))?$additionalchargessummary['additional_charges_net_summary0']:'',
                          (isset($productionsectionsummary['setupcharge_summary']))?$productionsectionsummary['setupcharge_summary']:'',
                          (isset($productionsectionsummary['netsetupcharge_summary']))?$productionsectionsummary['netsetupcharge_summary']:'',
                          (isset($value['itemnumber']))?$value['itemnumber']:'',
                          '',
                          (isset($price['addcolornetprice_summary']))?$price['addcolornetprice_summary']:'',
                          (isset($price['addcolorprice_summary']))?$price['addcolorprice_summary']:''
                          );
            //echo "<prE>";
            //print_r($data); exit;
            $this->_csvfiles->writeProductAttributeCsv($data);
        }
    }

    protected function getUrlKey($sku, $name)
    {
        $connection = $this->getConnection();
        $table = $connection->getTableName('catalog_product_entity');
        $url_rewrite = $connection->getTableName('url_rewrite');
        $select = $connection->select()
            ->from(array('main' =>$table), array('entity_id','sku'))
            ->join(array('url_rewrite' =>$url_rewrite), "main.entity_id = url_rewrite.entity_id AND entity_type = 'product'", array('*'))
            ->where('main.sku = ?', $sku);
        
        $result = $connection->fetchRow($select);
        if (!empty($result['request_path'])) {
            return $result['request_path'];
        }

        if (!empty($name)) {
            return $this->productUrl->formatUrlKey($name.'-'.$sku);
        }

        return '';
    }

    public function getProductAttributeConfig($value)
    {
        if (empty($this->_attributeArray) !== false) {
            $this->createProductAttributeHeader();
        }

        if (empty($this->_attributeConfigArray) !== false) {
            $this->createProductAttributeConfigHeader();
        }
        $productType = 'configurable';
        $data= array();
        if (isset($value['itemnumber']) && $value['itemnumber'] != '') {
            $product_detail = $this->getProductQty($value['itemnumber']);
            /*echo $value['itemnumber'];
            print_r($product_detail); exit;*/
            $this->getProductColorValue($value);
            $itemName = (isset($value['itemname']))?$value['itemname']:'';
            $url_key = $this->getUrlKey($value['itemnumber'], $itemName);

            $price = $this->getProductPrice($value);
            $qty = self::PRODUCT_DEFUALT_QTY;
            $isInStock = self::PRODUCT_ISINSTOCK;
            if (isset($product_detail['qty'])) {
                $qty = $product_detail['qty'];
                $isInStock = $product_detail['is_in_stock'];
            }
            $webattentionflags = $this->getWebattentionflags($value);
            $prop_65 = $this->getProp65($value);
            $imprintValue = $this->getImprintValue($value);
            $shipFromLocations = $this->getShipFromLocations($value);
            $productionsectionsummary = $this->getProductionSectionSummary($value);
            $additionalchargessummary = $this->getAdditionalChargesSummary($value);
            //echo "<pre>";
            //print_r($productionsectionsummary); exit;
            //print_r($imprintValue); exit;
            $additional_attributes ='';
            if ($webattentionflags != '') {
                $additional_attributes = 'webattentionflags='.$webattentionflags;
            }
            $itemweightinlbs = (isset($value['itemweightinlbs']))?$value['itemweightinlbs']:'';
            $itemweightinlbs = $this->subArraysToString($itemweightinlbs);
            $itemdescription = (isset($value['itemdescription']))?$value['itemdescription']:'';
            $itemdescription = $this->subArraysToString($itemdescription);
            $data = array($value['itemnumber'],
                          'Default',
                          $productType,
                          'base',
                          $itemName,
                          $url_key,
                          $itemweightinlbs,
                          $itemdescription,
                          1,
                          'Taxable Goods',
                          'Catalog, Search',
                          (isset($price['price']))?$price['price']:'',
                          $qty,
                          $isInStock,
                          0,
                          '',
                          $additional_attributes,
                          $prop_65['prop_65'],
                          $prop_65['prop65'],
                          (isset($productionsectionsummary['production_section_summary_bl']))?$productionsectionsummary['production_section_summary_bl']:'',
                          (isset($productionsectionsummary['production_section_summary_rs']))?$productionsectionsummary['production_section_summary_rs']:'',
                          '',//(isset($value['brandname']))?$value['brandname']:'',
                          (isset($value['primarymaterial']))?$value['primarymaterial']:'',
                          (isset($value['itemwidth']))?$value['itemwidth']:'',
                          (isset($value['itemheight']))?$value['itemheight']:'',
                          (isset($value['itemweightinlbs']))?$value['itemweightinlbs']:'',
                          (isset($value['itemdepth']))?$value['itemdepth']:'',
                          (isset($value['itemdimensions']))?$value['itemdimensions']:'',
                          (isset($imprintValue['imprintdimensions']))?$imprintValue['imprintdimensions']:'',
                          (isset($imprintValue['imprinttemplate']))?$imprintValue['imprinttemplate']:'',
                          (isset($imprintValue['imprintmethodsummary']))?$imprintValue['imprintmethodsummary']:'',
                          (isset($productionsectionsummary['shipping_summary']))?$productionsectionsummary['shipping_summary']:'',
                          (isset($productionsectionsummary['production_section_summary']))?$productionsectionsummary['production_section_summary']:'',
                          (isset($shipFromLocations['shipfromcity']))?$shipFromLocations['shipfromcity']:'',
                          (isset($shipFromLocations['shipfromstate']))?$shipFromLocations['shipfromstate']:'',
                          (isset($shipFromLocations['shipfromzipcode']))?$shipFromLocations['shipfromzipcode']:'',
                          (isset($shipFromLocations['shipfromcountry']))?$shipFromLocations['shipfromcountry']:'',
                          $this->getShipWeightinLBs($value),
                          (isset($additionalchargessummary['additional_charges_summary0']))?$additionalchargessummary['additional_charges_summary0']:'',
                          (isset($additionalchargessummary['additional_charges_net_summary0']))?$additionalchargessummary['additional_charges_net_summary0']:'',
                          (isset($productionsectionsummary['setupcharge_summary']))?$productionsectionsummary['setupcharge_summary']:'',
                          (isset($productionsectionsummary['netsetupcharge_summary']))?$productionsectionsummary['netsetupcharge_summary']:'',
                          (isset($value['itemnumber']))?$value['itemnumber']:'',
                          '',
                          (isset($price['addcolornetprice_summary']))?$price['addcolornetprice_summary']:'',
                          (isset($price['addcolorprice_summary']))?$price['addcolorprice_summary']:''
                          );
            $this->_csvfiles->writeProductConfigAttributeCsv($data);
        }
    }

    public function getProductColorValue($value)
    {
        $itemVariationArray = isset($value['itemvariations']['itemvariation'])?$value['itemvariations']['itemvariation']:array();
        if (count($itemVariationArray) > 0) {
            if (!isset($itemVariationArray[0])) {
                $variationData[] = $itemVariationArray;
            } else {
                $variationData = $itemVariationArray;
            }
            foreach ($variationData as $variationKey=>$variationValue) {
                if (isset($variationValue['itemvariationnumber']) && $variationValue['itemvariationnumber'] != '' && isset($variationValue['webswatchdescription']) && $variationValue['webswatchdescription'] != '') {
                    if (isset($value['brandname']) && strtolower($value['brandname']) == 'castelli') {
                        $data = array($variationValue['itemvariationnumber'],'C-'.$variationValue['webswatchdescription']);
                    } else {
                        $data = array($variationValue['itemvariationnumber'],$variationValue['webswatchdescription']);
                    }
                    if (empty($this->_attributeColorArray) !== false) {
                        $this->createProductAttributeColor();
                    }
                    $this->_csvfiles->writeProductColorCsv($data);
                }
            }
        }
    }

    public function getProductQty($sku)
    {
        $connection = $this->getConnection();
        $table = $connection->getTableName('catalog_product_entity');
        $stock_item = $connection->getTableName('cataloginventory_stock_item');
        $select = $connection->select()
            ->from(array('main' =>$table), array('entity_id','sku'))
            ->join(array('stock_item' =>$stock_item), 'main.entity_id = stock_item.product_id', array('*'))
            ->where('main.sku = ?', $sku);
        
        return $connection->fetchRow($select);
    }

    public function getWebattentionflags($value)
    {
        $webattentionflagsValue = isset($value['webattentionflags']['value'])?$value['webattentionflags']['value']:'';
        if (is_array($webattentionflagsValue)) {
            $webattentionflags = $value['webattentionflags']['value'];
            $webattentionflags=array_map('trim', $webattentionflags);
            return implode('|', $webattentionflags);
        } elseif ($webattentionflagsValue != '') {
            $webattentionflags[] = isset($value['webattentionflags']['value'])?$value['webattentionflags']['value']:'';
            $webattentionflags=array_map('trim', $webattentionflags);
            return implode('|', $webattentionflags);
        } else {
            return '';
        }
    }

    public function uniqueMultidimAarray($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public function getDecorationmethodArray($value)
    {
        $priceDataArray = $value['decorationmethods']['decorationmethod'];
        if (!isset($priceDataArray[0])) {
            $priceData[] = $priceDataArray;
        } else {
            $priceData = $priceDataArray;
        }
        $shipDetails = array();
        foreach ($priceData as $priceKey=>$priceValue) {
            if (isset($priceValue['shiplength']) && $priceValue['shiplength'] != '' && empty($priceValue['shiplength']) !== true) {
                $shippingSummaryData = array( 'shiplength' => (isset($priceValue['shiplength']))?$priceValue['shiplength']:'',
                                            'shipwidth' => (isset($priceValue['shipwidth']))?$priceValue['shipwidth']:'',
                                            'shipheight' => (isset($priceValue['shipheight']))?$priceValue['shipheight']:'',
                                            'numberperbox' => (isset($priceValue['numberperbox']))?$priceValue['numberperbox']:'',
                                            'shipweightinlbs' => (isset($priceValue['shipweightinlbs']))?$priceValue['shipweightinlbs']:'');
                $productionSectionSummary0 = array(
                                            'pricemethod' => (isset($priceValue['pricemethod']))?$priceValue['pricemethod']:'',
                                            'prodtimelo' => (isset($priceValue['prodtimelo']))?$priceValue['prodtimelo']:'',
                                            'prodtimehi' => (isset($priceValue['prodtimehi']))?$priceValue['prodtimehi']:'',
                                            'quickshipavailable' => (isset($priceValue['quickshipavailable']))?$priceValue['quickshipavailable']:'',
                                            'minimumorderquantity' => (isset($priceValue['minimumorderquantity']))?$priceValue['minimumorderquantity']:'');
                $productionSectionSummaryBlank0 = array(
                                            'pricemethod' => (isset($priceValue['pricemethod']))?$priceValue['pricemethod']:'',
                                            'prodtimelo' => (isset($priceValue['prodtimelo']))?$priceValue['prodtimelo']:'',
                                            'prodtimehi' => (isset($priceValue['prodtimehi']))?$priceValue['prodtimehi']:'',
                                            'quickshipavailable' => (isset($priceValue['quickshipavailable']))?$priceValue['quickshipavailable']:'',
                                            'minimumorderquantity' => (isset($priceValue['minimumorderquantity']))?$priceValue['minimumorderquantity']:'');

                $productionSectionSummaryRs0 = array(
                                            'pricemethod' => (isset($priceValue['pricemethod']))?$priceValue['pricemethod']:'',
                                            'prodtimelo' => (isset($priceValue['prodtimelo']))?$priceValue['prodtimelo']:'',
                                            'prodtimehi' => (isset($priceValue['prodtimehi']))?$priceValue['prodtimehi']:'',
                                            'quickshipavailable' => (isset($priceValue['quickshipavailable']))?$priceValue['quickshipavailable']:'',
                                            'minimumorderquantity' => (isset($priceValue['minimumorderquantity']))?$priceValue['minimumorderquantity']:'');


                $shipDetails['shipping_summary'][] = $shippingSummaryData;
                $shipDetails['production_section_summary_bl'][] = $productionSectionSummaryBlank0;
                $shipDetails['production_section_summary_rs'][] = $productionSectionSummaryRs0;
                $shipDetails['production_section_summary'][] = $productionSectionSummary0;
            }
            if (isset($priceValue['printmethod']) && $priceValue['printmethod'] != 'Random Sample' && isset($priceValue['pricemethod']) && $priceValue['pricemethod'] != 'Blank') {
                $setupcharge_summary = array(
                                            'pricemethod' => (isset($priceValue['pricemethod']))?$priceValue['pricemethod']:'',
                                            'setupchargecatalogprice' => (isset($priceValue['setupchargecatalogprice']))?$priceValue['setupchargecatalogprice']:'',
                                            'setupchargediscountcode' => (isset($priceValue['setupchargediscountcode']))?$priceValue['setupchargediscountcode']:'');
                $netsetupcharge_summary = array(
                                            'pricemethod' => (isset($priceValue['pricemethod']))?$priceValue['pricemethod']:'',
                                            'netsetupcharge' => (isset($priceValue['netsetupcharge']))?$priceValue['netsetupcharge']:'',
                                            'setupchargediscountcode' => (isset($priceValue['setupchargediscountcode']))?$priceValue['setupchargediscountcode']:'');
                $shipDetails['setupcharge_summary'][] = $setupcharge_summary;
                $shipDetails['netsetupcharge_summary'][] = $netsetupcharge_summary;
            }
        }
        if (empty($shipDetails) !== true) {
            if (isset($shipDetails['shipping_summary'])) {
                $shipDetails['shipping_summary'] = array_map("unserialize", array_unique(array_map("serialize", $shipDetails['shipping_summary'])));
            }
            if (isset($shipDetails['production_section_summary_bl'])) {
                $shipDetails['production_section_summary_bl'] = array_map("unserialize", array_unique(array_map("serialize", $shipDetails['production_section_summary_bl'])));
            }
            if (isset($shipDetails['production_section_summary_rs'])) {
                $shipDetails['production_section_summary_rs'] = array_map("unserialize", array_unique(array_map("serialize", $shipDetails['production_section_summary_rs'])));
            }
            if (isset($shipDetails['production_section_summary'])) {
                $shipDetails['production_section_summary'] = array_map("unserialize", array_unique(array_map("serialize", $shipDetails['production_section_summary'])));
            }
            if (isset($shipDetails['setupcharge_summary'])) {
                $shipDetails['setupcharge_summary'] = array_map("unserialize", array_unique(array_map("serialize", $shipDetails['setupcharge_summary'])));
            }
            if (isset($shipDetails['netsetupcharge_summary'])) {
                $shipDetails['netsetupcharge_summary'] = array_map("unserialize", array_unique(array_map("serialize", $shipDetails['netsetupcharge_summary'])));
            }
        }
        return $shipDetails;
    }

    public function subArraysToString($ar, $sep = ', ')
    {
        $str = '';
        if (is_array($ar) === false) {
            return $ar;
        }
        foreach ($ar as $val) {
            if (is_array($val) === true) {
                $str .= implode($sep, $val);
                $str .= $sep; // add separator between sub-arrays
            }
        }
        $str = rtrim($str, $sep); // remove last separator
        return $str;
    }

    public function getShippingSummaryHtml($selectedValue)
    {
        $data = array();
        if (count($selectedValue) > 0) {
            foreach ($selectedValue as $key=>$item) {
                $shipheight = isset($item['shipheight'])?$item['shipheight']:'';
                $shipwidth = isset($item['shipwidth'])?$item['shipwidth']:'';
                $shiplength = isset($item['shiplength'])?$item['shiplength']:'';
                $shipweightinlbs = isset($item['shipweightinlbs'])?$item['shipweightinlbs']:'';
                $numberperbox = isset($item['numberperbox'])?$item['numberperbox']:'';
                $shipheight = $this->subArraysToString($shipheight);
                $shipwidth = $this->subArraysToString($shipwidth);
                $shiplength = $this->subArraysToString($shiplength);
                $shipweightinlbs = $this->subArraysToString($shipweightinlbs);
                $numberperbox = $this->subArraysToString($numberperbox);
                $data[] = '<tr><td>Shipping Dimensions (Inches): </td><td>'. $shipheight. ' X '. $shipwidth. ' X '. $shiplength. '</td></tr><tr><td>Shipment Weight (lbs): </td><td>'. $shipweightinlbs. '</td></tr><tr><td>Number Per Box: </td><td>'. $numberperbox. '</td></tr>';
            }
        }
        return $data;
    }

    public function getProductionSectionSummaryBlHtml($selectedValue)
    {
        $data = array();
        if (count($selectedValue) > 0) {
            foreach ($selectedValue as $key=>$item) {
                $pricemethod = isset($item['pricemethod'])?$item['pricemethod']:'';
                $prodtimelo = isset($item['prodtimelo'])?$item['prodtimelo']:'';
                $prodtimehi = isset($item['prodtimehi'])?$item['prodtimehi']:'';
                $quickshipavailable = isset($item['quickshipavailable'])?$item['quickshipavailable']:'';
                $minimumorderquantity = isset($item['minimumorderquantity'])?$item['minimumorderquantity']:'';
                $pricemethod = $this->subArraysToString($pricemethod);
                $prodtimelo = $this->subArraysToString($prodtimelo);
                $prodtimehi = $this->subArraysToString($prodtimehi);
                $quickshipavailable = $this->subArraysToString($quickshipavailable);
                $minimumorderquantity = $this->subArraysToString($minimumorderquantity);
                $data[] = '<tr><td><i class="fa fa-caret-right" aria-hidden="true"></i> <strong>Imprint Method/Option (Blank):</strong> </td><td><strong>'.$pricemethod .'</strong></td></tr><tr><td>Production Time(s): </td><td>'.$prodtimelo. ' to '.$prodtimehi. ' days After Proof Approval'. '</td></tr><tr><td>QuickShip Available: </td><td>'.$quickshipavailable. '</td></tr><tr><td> Minimum Order Qty: </td><td>'.$minimumorderquantity.'</td></tr>';
            }
        }
        return $data;
    }

    public function getProductionSectionSummaryHtml($selectedValue)
    {
        $data = array();
        if (count($selectedValue) > 0) {
            foreach ($selectedValue as $key=>$item) {
                $pricemethod = isset($item['pricemethod'])?$item['pricemethod']:'';
                $prodtimelo = isset($item['prodtimelo'])?$item['prodtimelo']:'';
                $prodtimehi = isset($item['prodtimehi'])?$item['prodtimehi']:'';
                $quickshipavailable = isset($item['quickshipavailable'])?$item['quickshipavailable']:'';
                $minimumorderquantity = isset($item['minimumorderquantity'])?$item['minimumorderquantity']:'';
                $pricemethod = $this->subArraysToString($pricemethod);
                $prodtimelo = $this->subArraysToString($prodtimelo);
                $prodtimehi = $this->subArraysToString($prodtimehi);
                $quickshipavailable = $this->subArraysToString($quickshipavailable);
                $minimumorderquantity = $this->subArraysToString($minimumorderquantity);
                $data[] = '<tr><td><i class="fa fa-caret-right" aria-hidden="true"></i> <strong>Imprint Method/Option:</strong> </td><td><strong>'.$pricemethod .'</strong></td></tr><tr><td>Production Time(s): </td><td>'.$prodtimelo.' to '.$prodtimehi.' days After Proof Approval'.'</td></tr><tr><td>QuickShip Available: </td><td>'.$quickshipavailable.'</td></tr><tr><td>Minimum Order Qty: </td><td>'.$minimumorderquantity.'</td></tr>';
            }
        }
        return $data;
    }

    public function productionSectionSummaryRsHtml($selectedValue)
    {
        $data = array();
        if (count($selectedValue) > 0) {
            foreach ($selectedValue as $key=>$item) {
                $pricemethod = isset($item['pricemethod'])?$item['pricemethod']:'';
                $prodtimelo = isset($item['prodtimelo'])?$item['prodtimelo']:'';
                $prodtimehi = isset($item['prodtimehi'])?$item['prodtimehi']:'';
                $quickshipavailable = isset($item['quickshipavailable'])?$item['quickshipavailable']:'';
                $minimumorderquantity = isset($item['minimumorderquantity'])?$item['minimumorderquantity']:'';
                $pricemethod = $this->subArraysToString($pricemethod);
                $prodtimelo = $this->subArraysToString($prodtimelo);
                $prodtimehi = $this->subArraysToString($prodtimehi);
                $quickshipavailable = $this->subArraysToString($quickshipavailable);
                $minimumorderquantity = $this->subArraysToString($minimumorderquantity);
                $data[] = '<tr><td><i class="fa fa-caret-right" aria-hidden="true"></i> <strong>Imprint Method/Option (Random Sample):</strong> </td><td><strong>'.$pricemethod .'</strong></td></tr><tr><td>Production Time(s): </td><td>'.$prodtimelo.' to '.$prodtimehi. ' days After Proof Approval'. '</td></tr><tr><td>QuickShip Available: </td><td>'.$quickshipavailable.'</td></tr><tr><td> Minimum Order Qty: </td><td>'.$minimumorderquantity. '</td></tr>';
            }
        }
        return $data;
    }

    public function setupchargeSummaryHtml($selectedValue)
    {
        $data = array();
        if (count($selectedValue) > 0) {
            foreach ($selectedValue as $key=>$item) {
                $pricemethod = isset($item['pricemethod'])?$item['pricemethod']:'';
                $setupchargecatalogprice = isset($item['setupchargecatalogprice'])?$item['setupchargecatalogprice']:'';
                $setupchargediscountcode = isset($item['setupchargediscountcode'])?$item['setupchargediscountcode']:'';
                $pricemethod = $this->subArraysToString($pricemethod);
                $setupchargecatalogprice = $this->subArraysToString($setupchargecatalogprice);
                $setupchargediscountcode = $this->subArraysToString($setupchargediscountcode);
                if ($setupchargecatalogprice != '' || $setupchargediscountcode != '') {
                    $data[] = '<tr><td colspan = "2"><strong><i class="fa fa-caret-right" aria-hidden="true"></i> '.$pricemethod.'</strong></td></tr><tr><td>Setup Charges:  </td><td>$'.$setupchargecatalogprice.' ('.$setupchargediscountcode.') </td></tr>';
                } else {
                    $data[0] = '<td colspan="2">N/A</td>';
                }
            }
        }
        return $data;
    }

    public function netsetupchargeSummaryHtml($selectedValue)
    {
        $data = array();
        if (count($selectedValue) > 0) {
            foreach ($selectedValue as $key=>$item) {
                if (isset($item['setupchargediscountcode']) && is_array($item['setupchargediscountcode'])) {
                    $setupchargediscountcode = implode(' ', $item['setupchargediscountcode']);
                } else {
                    $setupchargediscountcode = $item['setupchargediscountcode'];
                }

                $pricemethod = isset($item['pricemethod'])?$item['pricemethod']:'';
                $netsetupcharge = isset($item['netsetupcharge'])?$item['netsetupcharge']:'';
                $pricemethod = $this->subArraysToString($pricemethod);
                $netsetupcharge = $this->subArraysToString($netsetupcharge);
                $setupchargediscountcode = $this->subArraysToString($setupchargediscountcode);
                if ($netsetupcharge != '' || $setupchargediscountcode != '') {
                    $data[] = '<tr><td colspan = "2"><strong><i class="fa fa-caret-right" aria-hidden="true"></i> '.$pricemethod. '</strong></td></tr><tr><td>Setup Charges:  </td><td>$'.$netsetupcharge.'</td></tr>';
                } else {
                    $data[0] = '<td colspan="2">N/A</td>';
                }
            }
        }
        return $data;
    }

    public function getProductionSectionSummary($value)
    {
        $detail = $this->getDecorationmethodArray($value);
        $data = array();
        //print_r($detail); exit;
        if (count($detail)>0) {
            foreach ($detail as $key=>$item) {
                if ($key == 'shipping_summary') {
                    $getShippingSummaryHtml = $this->getShippingSummaryHtml($item);
                    if (count($getShippingSummaryHtml)>0) {
                        $data[$key] = implode(' ', $getShippingSummaryHtml);
                    } else {
                        $data[$key] = '';
                    }
                }
                if ($key == 'production_section_summary_bl') {
                    $getProductionSectionSummaryBlHtml = $this->getProductionSectionSummaryBlHtml($item);
                    if (count($getProductionSectionSummaryBlHtml)>0) {
                        $data[$key] = implode(' ', $getProductionSectionSummaryBlHtml);
                    } else {
                        $data[$key] = '';
                    }
                }

                if ($key == 'production_section_summary_rs') {
                    $productionSectionSummaryRsHtml = $this->productionSectionSummaryRsHtml($item);
                    if (count($productionSectionSummaryRsHtml)>0) {
                        $data[$key] = implode(' ', $productionSectionSummaryRsHtml);
                    } else {
                        $data[$key] = '';
                    }
                }

                if ($key == 'production_section_summary') {
                    $getProductionSectionSummaryHtml = $this->getProductionSectionSummaryHtml($item);
                    if (count($productionSectionSummaryRsHtml)>0) {
                        $data[$key] = implode(' ', $getProductionSectionSummaryHtml);
                    } else {
                        $data[$key] = '';
                    }
                }

                if ($key == 'setupcharge_summary') {
                    $setupchargeSummaryHtml = $this->setupchargeSummaryHtml($item);

                    if (count($setupchargeSummaryHtml)>0) {
                        $data[$key] = implode(' ', $setupchargeSummaryHtml);
                    } else {
                        $data[$key] = '';
                    }
                }

                if ($key == 'netsetupcharge_summary') {
                    $netsetupchargeSummaryHtml = $this->netsetupchargeSummaryHtml($item);
                    if (count($netsetupchargeSummaryHtml)>0) {
                        $data[$key] = implode(' ', $netsetupchargeSummaryHtml);
                    } else {
                        $data[$key] = '';
                    }
                }
            }
        }
        return $data;
    }

    public function getAdditionalChargesArray($value)
    {
        $decorationValue['additionalcharges'] = '';
        $decorationDataArray = $value['decorationmethods']['decorationmethod'];
        if (!isset($decorationDataArray[0])) {
            $decorationData[] = $decorationDataArray;
        } else {
            $decorationData = $decorationDataArray;
        }

        $decorationDetails = array();
        foreach ($decorationData as $decorationKey=>$decorationValue) {
            if (isset($decorationValue['printmethod']) && $decorationValue['printmethod'] != 'Random Sample' && isset($decorationValue['pricemethod']) && $decorationValue['pricemethod'] != 'Blank') {
                $additionalchargesDataArray = isset($decorationValue['additionalcharges']['additionalcharge'])?$decorationValue['additionalcharges']['additionalcharge']:array();
                if (!isset($additionalchargesDataArray[0])) {
                    $additionalchargesData[] = $additionalchargesDataArray;
                } else {
                    $additionalchargesData = $additionalchargesDataArray;
                }

                foreach ($additionalchargesData as $additionalKey=>$additionalValue) {
                    $additional_charges = array(
                        'pricemethod' => (isset($decorationValue['pricemethod']))?$decorationValue['pricemethod']:'',
                        'additionalchargeitemdescription' => (isset($additionalValue['additionalchargeitemdescription']))?$additionalValue['additionalchargeitemdescription']:'',
                        'additionalchargecatalogprice' => (isset($additionalValue['additionalchargecatalogprice']))?$additionalValue['additionalchargecatalogprice']:'',
                        'additionalchargediscountcode' => (isset($additionalValue['additionalchargediscountcode']))?$additionalValue['additionalchargediscountcode']:'',
                        'additionalchargediscountcode' => (isset($additionalValue['additionalchargediscountcode']))?$additionalValue['additionalchargediscountcode']:''
                    );

                    $additional_charges_net = array(
                        'pricemethod' => (isset($decorationValue['pricemethod']))?$decorationValue['pricemethod']:'',
                        'additionalchargeitemdescription' => (isset($additionalValue['additionalchargeitemdescription']))?$additionalValue['additionalchargeitemdescription']:'',
                        'additionalchargenetprice' => (isset($additionalValue['additionalchargenetprice']))?$additionalValue['additionalchargenetprice']:'',
                        'additionalchargediscountcode' => (isset($additionalValue['additionalchargediscountcode']))?$additionalValue['additionalchargediscountcode']:'',
                        'additionalchargediscountcode' => (isset($additionalValue['additionalchargediscountcode']))?$additionalValue['additionalchargediscountcode']:''
                    );

                    $decorationDetails['additional_charges_summary0'][] = $additional_charges;
                    $decorationDetails['additional_charges_net_summary0'][] = $additional_charges_net;
                }
            }
        }
        if (empty($decorationDetails) !== true) {
            $decorationDetails['additional_charges_summary0'] = array_map("unserialize", array_unique(array_map("serialize", $decorationDetails['additional_charges_summary0'])));
            $decorationDetails['additional_charges_net_summary0'] = array_map("unserialize", array_unique(array_map("serialize", $decorationDetails['additional_charges_net_summary0'])));
        }
        return $decorationDetails;
    }

    public function additionalChargesSummaryHtml($selectedValue)
    {
        $data = array();

        if (count($selectedValue) > 0) {
            foreach ($selectedValue as $key=>$item) {
                if (isset($item['additionalchargeitemdescription']) && $item['additionalchargeitemdescription'] != '' && isset($item['additionalchargecatalogprice']) && $item['additionalchargecatalogprice'] != '' && isset($item['additionalchargediscountcode']) && $item['additionalchargediscountcode'] != '') {
                    $data['html'][$item['pricemethod']][] = '<tr><td>'.$item['additionalchargeitemdescription'] . ': </td><td>$'.$item['additionalchargecatalogprice'] . '('.$item['additionalchargediscountcode'] .')</td></tr>';
                    $data['pricemethod'] = $item['pricemethod'];
                }
            }
        }
        return $data;
    }

    public function additionalChargesNetSummaryHtml($selectedValue)
    {
        $data = array();
        //print_r($selectedValue);
        if (count($selectedValue) > 0) {
            foreach ($selectedValue as $key=>$item) {
                if (isset($item['additionalchargeitemdescription']) && $item['additionalchargeitemdescription'] != '' && isset($item['additionalchargenetprice']) && $item['additionalchargenetprice'] != '' && isset($item['additionalchargediscountcode']) && $item['additionalchargediscountcode'] != '') {
                    $data['html'][$item['pricemethod']][] = '<tr><td>'.$item['additionalchargeitemdescription'] . ': </td><td>$'.$item['additionalchargenetprice'] . '</td></tr>';
                    $data['pricemethod'] = $item['pricemethod'];
                }
            }
        }
        return $data;
    }

    public function getAdditionalChargesSummary($value)
    {
        $detail = $this->getAdditionalChargesArray($value);
        $data = array();

        if (count($detail)>0) {
            foreach ($detail as $key=>$item) {
                if ($key == 'additional_charges_summary0') {
                    $additionalChargesSummaryHtml = $this->additionalChargesSummaryHtml($item);
                    if (count($additionalChargesSummaryHtml)>0) {
                        $chargeHTML = '';
                        foreach ($additionalChargesSummaryHtml['html'] as $chargeKey=>$chargeValue) {
                            $html0 = implode(' ', $chargeValue);
                            $chargeHTML .= '<tr><td><strong><i class="fa fa-caret-right" aria-hidden="true"></i>'. $chargeKey.'</strong></td><td>&nbsp;</td></tr>,'.$html0.' <tr><td colspan="2">&nbsp;</td></tr>';
                        }
                        $data[$key] = $chargeHTML;
                    } else {
                        $data[$key] = '<td colspan="2">N/A</td>';
                    }
                }
                if ($key == 'additional_charges_net_summary0') {
                    $additionalChargesNetSummaryHtml = $this->additionalChargesNetSummaryHtml($item);
                    //print_r($additionalChargesNetSummaryHtml);
                    if (count($additionalChargesNetSummaryHtml)>0) {
                        $chargeNetHTML = '';
                        foreach ($additionalChargesNetSummaryHtml['html'] as $chargeNetKey=>$chargeNetValue) {
                            $htmlNet0 = implode(' ', $chargeNetValue);
                            $chargeNetHTML .= '<tr><td><strong><i class="fa fa-caret-right" aria-hidden="true"></i>'. $chargeNetKey.'</strong></td><td>&nbsp;</td></tr>,'.$htmlNet0.'<tr><td colspan="2">&nbsp;</td></tr>';
                        }
                        $data[$key] = $chargeNetHTML;
                    } else {
                        $data[$key] = '<td colspan="2">N/A</td>';
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Function getProp65
     *
     * @param array $value
     * @return array
     * @throws NoSuchEntityException
     */
    public function getProp65($value)
    {
        $prop65Value = isset($value['compliance']['value']) ? $value['compliance']['value'] : '';
        if (is_string($prop65Value)) {
            // Remove new line character
            $prop65Value = trim(preg_replace('/\s\s+/', ' ', $prop65Value));
        }

        if (is_array($prop65Value)) {

            $prop65 = $value['compliance']['value'];
            $prop65 = array_map('trim', $prop65);
            $prop65 = implode('|', $prop65);

            // Remove new line character
            $prop65 = trim(preg_replace('/\s\s+/', ' ', $prop65));
            if ($prop65 == 'Prop65/CA65 - California Proposition 65') {
                $prop65 = $this->getDefaultAttributeProp65();
            }
            return ['prop_65' => 'Yes', 'prop65' => $prop65];
        } elseif ($prop65Value != '') {

            $prop65[] = isset($value['compliance']['value']) ? $value['compliance']['value'] : '';
            $prop65 = array_map('trim', $prop65);
            $prop65 = implode('|', $prop65);

            // Remove new line character
            $prop65 = trim(preg_replace('/\s\s+/', ' ', $prop65));
            if ($prop65 == 'Prop65/CA65 - California Proposition 65') {
                $prop65 = $this->getDefaultAttributeProp65();
            }
            return ['prop_65' => 'Yes', 'prop65' => $prop65];
        } else {
            return ['prop_65' => 'No', 'prop65' => ''];
        }
    }

    /**
     * Method getDefaultAttributeProp65
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getDefaultAttributeProp65()
    {
        if (!$this->defaultProp65) {
            $this->defaultProp65 = $this->attributeRepository->get('prop65')->getDefaultValue();
            if (!$this->defaultProp65) {
                $this->defaultProp65 =
                    '<img src="https://images.themagnetgroup.com/webimages/compliance/Prop65Caution.png">'.
                    ' <br> <p ><strong>WARNING:</strong> Cancer and Reproductive Harm</p> '.
                    '<a href="http://www.p65warnings.ca.gov/">www.P65Warnings.ca.gov</a>';
            }
        }

        return $this->defaultProp65;
    }

    public function getShipFromLocations($value)
    {
        $shipfromlocations = isset($value['shipfromlocations']['shipfromlocation'])?$value['shipfromlocations']['shipfromlocation']:'';
        if (isset($shipfromlocations[0])) {
            $locations = $shipfromlocations[0];
        } else {
            $locations = isset($value['shipfromlocations']['shipfromlocation'])?$value['shipfromlocations']['shipfromlocation']:'';
        }
        return $locations;
    }

    public function getProductPriceSummery($value, $row)
    {
        $priceDataArray = isset($value['quantities']['quantity'])?$value['quantities']['quantity']:array();
        if (!isset($priceDataArray[0])) {
            $priceData[] = $priceDataArray;
        } else {
            $priceData = $priceDataArray;
        }
        $data = array();
        foreach ($priceData as $priceKey=>$priceValue) {
            if (isset($priceValue['addcolorprice']) || isset($priceValue['addcolornetprice'])) {
                $addcolorprice_summary = array(
                            'quantitybreak' => (isset($priceValue['quantitybreak']))?$priceValue['quantitybreak']:'',
                            'addcolorprice' => (isset($priceValue['addcolorprice']))?$priceValue['addcolorprice']:'',
                            'addcolordiscountcode' => (isset($priceValue['addcolordiscountcode']))?$priceValue['addcolordiscountcode']:''
                        );
                $addcolornetprice_summary = array(
                            'quantitybreak' => (isset($priceValue['quantitybreak']))?$priceValue['quantitybreak']:'',
                            'addcolornetprice' => (isset($priceValue['addcolornetprice']))?$priceValue['addcolornetprice']:'',
                            'addcolordiscountcode' => (isset($priceValue['addcolordiscountcode']))?$priceValue['addcolordiscountcode']:''
                        );
                $data['addcolorprice_summary'][] = $addcolorprice_summary;
                $data['addcolornetprice_summary'][] = $addcolornetprice_summary;
            }
        }
        return $data;
    }

    public function getProductPriceMain($value, $row, $key)
    {
        if ($key != 0) {
            return '';
        }
        $priceDataArray = isset($value['quantities']['quantity'])?$value['quantities']['quantity']:array();
        if (!isset($priceDataArray[0])) {
            $priceData[] = $priceDataArray;
        } else {
            $priceData = $priceDataArray;
        }
        $data = array();
        if (isset($row['printmethod']) && strtolower($row['printmethod']) != strtolower('Random Sample')) {
            foreach ($priceData as $priceKey=>$priceValue) {
                $data[$priceValue['quantitybreak']] = $priceValue['catalogprice'];
            }
        }
        $minKey = '';
        if (empty($data) !== true) {
            $minKey = min(array_keys($data));
        }
        return isset($data[$minKey])?$data[$minKey]:'';
    }

    public function addcolorpriceSummaryHtml($value, $pricemethod)
    {
        $dataItems = array();
        $dataItemsHtml = '';
        if (count($value) > 0) {
            foreach ($value as $item) {
                $dataItems[] = '<tr><td>'.$item['quantitybreak'].'</td><td>$'.$item['addcolorprice'].' ('.$item['addcolordiscountcode']. ')</td></tr>';
            }
            $dataItemsHtml = '<tr><td colspan="2"><h4>Additional Run Charges</h4></td></tr><tr><td><strong>Quantity/Imprint Method</strong></td><td><strong>Add Color Price</strong></td></tr><tr><td>'.$pricemethod.'</td><td>&nbsp;</td></tr>'.implode('<tr><td colspan="2">&nbsp;</td></tr>', $dataItems);
        }

        return $dataItemsHtml;
    }

    public function addcolornetpriceSummaryHtml($value, $pricemethod)
    {
        $dataItems = array();
        $dataItemsHtml = '';
        if (count($value) > 0) {
            foreach ($value as $item) {
                $dataItems[] = '<tr><td>'.$item['quantitybreak'].'</td><td>$'.$item['addcolornetprice'].' ('.$item['addcolordiscountcode']. ')</td></tr>';
            }
            $dataItemsHtml = '<tr><td colspan="2"><h4>Additional Run Charges</h4></td></tr><tr><td><strong>Quantity/Imprint Method</strong></td><td><strong>Add Color Price</strong></td></tr><tr><td>'.$pricemethod.'</td><td>&nbsp;</td></tr>'.implode('<tr><td colspan="2">&nbsp;</td></tr>', $dataItems);
        }
        return $dataItemsHtml;
    }

    public function getProductPrice($value)
    {
        $priceDataArray = $value['decorationmethods']['decorationmethod'];
        if (!isset($priceDataArray[0])) {
            $priceData[] = $priceDataArray;
        } else {
            $priceData = $priceDataArray;
        }
        $priceTier = array('addcolorprice_summary' =>array() , 'addcolornetprice_summary' =>array());
        $i = 0;
        $main_price = array();
        foreach ($priceData as $priceKey=>$priceValue) {
            $pricesValue = $priceValue['prices']['price'];
            if (!isset($pricesValue[0])) {
                $pricesArrayValue[] = $pricesValue;
            } else {
                $pricesArrayValue = $pricesValue;
            }
            foreach ($pricesArrayValue as $key=>$valuePart) {
                if (isset($valuePart['currencycode']) && $valuePart['currencycode'] == 'USD') {
                    $priecevaluearray = $this->getProductPriceSummery($valuePart, $value);
                    $main_price[] = $this->getProductPriceMain($valuePart, $priceValue, $priceKey);
                    if (isset($priecevaluearray['addcolorprice_summary']) && empty($priecevaluearray['addcolorprice_summary']) !== true) {
                        $priceTier['addcolorprice_summary'] = array_merge($priceTier['addcolorprice_summary'], $priecevaluearray['addcolorprice_summary']);
                    }
                    if (isset($priecevaluearray['addcolornetprice_summary']) && empty($priecevaluearray['addcolornetprice_summary']) !== true) {
                        $priceTier['addcolornetprice_summary'] = array_merge($priceTier['addcolornetprice_summary'], $priecevaluearray['addcolornetprice_summary']);
                    }
                    /*if(!isset($priceTier['price']) && $valuePart['aslowascatalog'] != 0){
                    $priceTier['price'] = (isset($valuePart['aslowascatalog']))?$valuePart['aslowascatalog']:0;
                    }*/
                }
            }
            $priceTier['pricemethod'] = $priceValue['pricemethod'];
        }
        if (isset($priceTier['addcolorprice_summary']) && isset($priceTier['pricemethod'])) {
            $priceTier['addcolorprice_summary'] = $this->addcolorpriceSummaryHtml($priceTier['addcolorprice_summary'], $priceTier['pricemethod']);
        }
        if (isset($priceTier['addcolornetprice_summary']) && isset($priceTier['pricemethod'])) {
            $priceTier['addcolornetprice_summary'] = $this->addcolornetpriceSummaryHtml($priceTier['addcolornetprice_summary'], $priceTier['pricemethod']);
        }

        if (count($main_price)> 0) {
            $priceTier['price'] = max($main_price);
        }
        return $priceTier;
    }

    public function getImprintValue($value)
    {
        $priceDataArray = $value['decorationmethods']['decorationmethod'];
        if (!isset($priceDataArray[0])) {
            $priceData[] = $priceDataArray;
        } else {
            $priceData = $priceDataArray;
        }
        $imprintValue = array();
        $htmlArray = array();
        $imprintmethodValue = array();
        foreach ($priceData as $priceKey=>$priceValue) {
            $pricesValue = isset($priceValue['imprintareas'])?$priceValue['imprintareas']:'';
            if (isset($pricesValue['imprintarea']) && $pricesValue['imprintarea'] != '') {
                if (isset($pricesValue['imprintarea'][0])) {
                    foreach ($pricesValue['imprintarea'] as $key=>$valueImprint) {
                        if (isset($valueImprint['imprintlocationname']) && $valueImprint['imprintlocationname'] == 'Default') {
                            $imprintValue['imprintdimensions'] = (isset($valueImprint['imprintdimensions']))?$this->subArraysToString($valueImprint['imprintdimensions']):'';
                            $imprintValue['imprinttemplate'] = (isset($valueImprint['imprinttemplate']))?$this->subArraysToString($valueImprint['imprinttemplate']):'';
                        } else {
                            $imprintmethodValue[$priceValue['pricemethod']][] =  $this->subArraysToString($valueImprint['imprintlocationname']).' - '.$this->subArraysToString($valueImprint['imprintdimensions']).' - '.$this->subArraysToString($valueImprint['imprintareashape']);
                        }
                    }
                } else {
                    $valueImprint = $pricesValue['imprintarea'];
                    if (isset($valueImprint['imprintlocationname']) && $valueImprint['imprintlocationname'] == 'Default') {
                        $imprintValue['imprintdimensions'] = (isset($valueImprint['imprintdimensions']))?$this->subArraysToString($valueImprint['imprintdimensions']):'';
                        $imprintValue['imprinttemplate'] = (isset($valueImprint['imprinttemplate']))?$this->subArraysToString($valueImprint['imprinttemplate']):'';
                    } else {
                        $imprintmethodValue[$priceValue['pricemethod']][] =  $this->subArraysToString($valueImprint['imprintlocationname']).' - '.$this->subArraysToString($valueImprint['imprintdimensions']).' - '.$this->subArraysToString($valueImprint['imprintareashape']);
                    }
                }
            }
        }

        if (count($imprintmethodValue) > 0) {
            $htmlArray = $this->getImprintMethodSummaryHtml($imprintmethodValue);
        }

        if (count($htmlArray) > 0) {
            $imprintValue['imprintmethodsummary'] = implode('', $htmlArray);
        }
        return $imprintValue;
    }

    public function getImprintMethodSummaryHtml($imprintmethodValue)
    {
        $html = array();
        if (count($imprintmethodValue) > 0) {
            foreach ($imprintmethodValue as $key=>$value) {
                $value = array_unique($value);
                $imprintmethodValueString = implode('<br>', $value);
                $pricemethod = $key;
                $html[] = '<tr><td><strong><i class="fa fa-caret-right" aria-hidden="true"></i> '.$pricemethod.':</strong></td><td>'.$imprintmethodValueString.'</td><tr>';
            }
        }
        return $html;
    }

    public function getShipWeightinLBs($value)
    {
        $priceDataArray = $value['decorationmethods']['decorationmethod'];
        if (!isset($priceDataArray[0])) {
            $priceData[] = $priceDataArray;
        } else {
            $priceData = $priceDataArray;
        }
        foreach ($priceData as $priceKey=>$priceValue) {
            if (isset($priceValue['shipweightinlbs']) && $priceValue['shipweightinlbs'] != '') {
                return (isset($valueImprint['shipweightinlbs']))?$valueImprint:'';
            }
        }
        return '';
    }

    public function writeImprintCsv($qtyValue)
    {
        if (empty($qtyValue) !== true) {
            foreach ($qtyValue as $key=>$value) {
                $uniqeArray = array_unique($value);
                foreach ($uniqeArray as $ind_value) {
                    if (empty($this->_finalQty) !== false) {
                        $this->createQtyHeader();
                    }
                    $data = array($key,$ind_value);
                    $this->_csvfiles->writeLocationCsv($data);
                }
            }
        }
    }

    public function selectAnOptionBelow($rowValue)
    {
        if (isset($rowValue['printmethod']) && strtolower($rowValue['printmethod']) == strtolower('Random Sample')) {
            return  'Order Sample';
        } elseif (isset($rowValue['pricemethod']) && strtolower($rowValue['pricemethod']) == strtolower('Blank')) {
            return 'Order Without Logo';
        } else {
            return 'Place Order With Logo';
        }
    }

    public function additionalCharges($AdditionalCharges, $additionalValue, $rowValue)
    {
        if (isset($AdditionalCharges['additionalcharge']) && $AdditionalCharges['additionalcharge'] != '') {
            foreach ($AdditionalCharges['additionalcharge'] as $key=>$value) {
                if (isset($value['additionalchargeitemdescription'])) {
                    //$colorValue[] = array($value['ImprintLocationName']);
                    if (empty($this->_finaladditional) !== false) {
                        $this->createAdditionalHeader();
                    }
                    $static = $this->getSaticFunction($value, $rowValue);
                    $data = array($rowValue['itemnumber'],
                      $rowValue['brandname'],
                      $rowValue['printmethod'],
                      $rowValue['pricemethod'],
                      $this->subArraysToString($rowValue['pricingkey']),
                      $value['additionalchargeitemdescription'],
                      $value['additionalchargecatalogPrice'],
                      $value['additionalchargenetPrice'],
                      $value['additionalchargediscountCode'],
                      $static->dropshipchg(),
                      $static->handlingfee3rdparty(),
                      $static->less_than_minimum_qty(),
                      $static->dropshipnetchg(),
                      $static->handlingfee3rdpartynetchg(),
                      $static->less_than_minimum_qty_netchg(),
                      $static->additional_stitches_catalog_price_em(),
                      $static->digitizing_fee_catalog_price_em(),
                      $static->personalization_catalog_price_em(),
                      $static->swatchproof_catalog_price_em(),
                      $static->additional_stitches_net_price_em(),
                      $static->digitizing_fee_net_price_em(),
                      $static->personalization_net_price_em(),
                      $static->swatchproof_net_price_em(),
                      $static->setupchargecatalogprice_tp(),
                      $static->setupchargecatalogprice_t4(),
                      $static->setupchargecatalogprice_em(),
                      $static->setupchargecatalogprice_db(),
                      $static->setupchargecatalogprice_dm(),
                      $static->setupchargecatalogprice_cg(),
                      $static->netsetupcharge_tp(),
                      $static->netsetupcharge_t4(),
                      $static->netsetupcharge_em(),
                      $static->netsetupcharge_dm(),
                      $static->netsetupcharge_cg(),
                      $static->netsetupcharge_db(),
                      $static->setupchargedescription_tp()
                      );
                    $static->unsAdditionalValue();
                    $this->_csvfiles->writeAdditionalCsv($data);
                }
            }
        }
        return $additionalValue;
    }

    public function stockColorDefault($rowValue)
    {
        $data = array($rowValue['itemnumber'],
                      $rowValue['brandname'],
                      '',
                      '',
                      '',
                      '',
                      '',
                      '',
                      '',
                      '',
                      '<img src ="none.png " alt =" None "  /> None',
                      'none'
                      );
        if (empty($this->_finalcolor) !== false) {
            $this->createColorHeader();
        }
        $this->_csvfiles->writeColorsCsv($data);
    }

    public function stockColor($StockColor, $colorValue, $rowValue)
    {
        if (isset($StockColor['stockcolor']) && $StockColor['stockcolor'] != '') {
            foreach ($StockColor['stockcolor'] as $key=>$value) {
                if (isset($value['stockcolorcode'])) {
                    //$colorValue[] = array($value['ImprintLocationName']);
                    if (empty($this->_finalcolor) !== false) {
                        $this->createColorHeader();
                    }
                    $ColorSwatch = trim($value['colorswatch']);
                    $StockColorCode = trim($value['stockcolorcode']);
                    $PMSColorID = '';
                    if (is_array($value['pmscolorid'])) {
                        $PMSColorID = implode('', $value['pmscolorid']);
                    } else {
                        $PMSColorID = trim($value['pmscolorid']);
                    }
                    $data = array($rowValue['itemnumber'],
                      $rowValue['brandname'],
                      $rowValue['printmethod'],
                      $rowValue['pricemethod'],
                      $this->subArraysToString($rowValue['pricingkey']),
                      $StockColorCode,
                      $PMSColorID,
                      trim($value['rubnbuff']),
                      trim($value['hyperfill']),
                      $ColorSwatch,
                      '<img src ="'.trim($ColorSwatch).'" alt ="'.$StockColorCode.'"  /> '.$StockColorCode.' ('.$PMSColorID.')',
                      $StockColorCode.' ('.$PMSColorID.')'
                      );
                    $this->_csvfiles->writeColorsCsv($data);
                }
            }
        }
        return $colorValue;
    }

    public function imprintLocationName($ImprintArea, $qtyValue, $rowValue)
    {
        if (isset($ImprintArea['imprintarea']) && $ImprintArea['imprintarea'] != '') {
            if ($this->is_multi2($ImprintArea['imprintarea'])) {
                foreach ($ImprintArea['imprintarea'] as $key=>$value) {
                    if (isset($value['imprintlocationname'])) {
                        $qtyValue[$rowValue['itemnumber']][] = $value['imprintlocationname'];
                    }
                }
            } else {
                foreach ($ImprintArea['imprintarea'] as $key=>$value) {
                    if ($key == 'imprintlocationname') {
                        $qtyValue[$rowValue['itemnumber']][] = $value;
                    }
                }
            }
        }
        return $qtyValue;
    }

    public function getTyerSimplePrice($Prices, $rowValue)
    {
        if (isset($Prices['price']) && $Prices['price'] != '') {
            if (isset($Prices['price'][0])) {
                foreach ($Prices['price'] as $key=>$value) {
                    if (isset($value['currencycode']) && $value['currencycode'] == 'USD') {
                        $this->getUSDSimplePrice($value, $rowValue);
                    }
                }
            } else {
                $value = $Prices['price'];
                if (isset($value['currencycode']) && $value['currencycode'] == 'USD') {
                    $this->getUSDSimplePrice($value, $rowValue);
                }
            }
        }
    }

    public function singleSimpleArrayPrice($value, $rowValue)
    {
        $data = array($rowValue['itemnumber'],
                    $rowValue['brandname'],
                    $rowValue['printmethod'],
                    $rowValue['pricemethod'],
                    $this->selectAnOptionBelow($rowValue),
                    $this->subArraysToString($rowValue['pricingkey']),
                    isset($value['quantitybreak'])?$value['quantitybreak']:'',
                    isset($value['catalogprice'])?$value['catalogprice']:'',
                    isset($value['standardnetprice'])?$value['standardnetprice']:'',
                    isset($value['discountcode'])?$value['discountcode']:'',
                    isset($value['addcolorprice'])?$value['addcolorprice']:'',
                    isset($value['addcolornetprice'])?$value['addcolornetprice']:'',
                    isset($value['addcolordiscountcode'])?$value['addcolordiscountcode']:'',
                    isset($rowValue['setupchargecatalogprice'])?$rowValue['setupchargecatalogprice']:'',
                    isset($rowValue['netsetupcharge'])?$rowValue['netsetupcharge']:'',
                    isset($rowValue['setupchargediscountcode'])?$rowValue['setupchargediscountcode']:'',
                    isset($value['currencycode'])?$value['currencycode']:'USD',
                    isset($rowValue['itemhasvariations'])?$rowValue['itemhasvariations']:'',
                    );
        if (empty($this->_finalSampleArray) !== false) {
            $this->createSimpleHeader();
        }
        $this->_finalSampleArray[] = $data;
        $this->_csvfiles->writeSimpleCsv($data);
    }

    public function getUSDSimplePrice($Prices, $rowValue)
    {
        if (isset($Prices['quantities']['quantity']) && $Prices['quantities']['quantity'] != '' && $this->is_multi2($Prices['quantities']['quantity'])) {
            foreach ($Prices['quantities']['quantity'] as $key=>$value) {
                $data = array($rowValue['itemnumber'],
                        $rowValue['brandname'],
                        $rowValue['printmethod'],
                        $rowValue['pricemethod'],
                        $this->selectAnOptionBelow($rowValue),
                        $this->subArraysToString($rowValue['pricingkey']),
                        isset($value['quantitybreak'])?$value['quantitybreak']:'',
                        isset($value['catalogprice'])?$value['catalogprice']:'',
                        isset($value['standardnetprice'])?$value['standardnetprice']:'',
                        isset($value['discountcode'])?$value['discountcode']:'',
                        isset($value['addcolorprice'])?$value['addcolorprice']:'',
                        isset($value['addcolornetprice'])?$value['addcolornetprice']:'',
                        isset($value['addcolordiscountcode'])?$value['addcolordiscountcode']:'',
                        isset($rowValue['setupchargecatalogprice'])?$rowValue['setupchargecatalogprice']:'',
                        isset($rowValue['netsetupcharge'])?$rowValue['netsetupcharge']:'',
                        isset($rowValue['setupchargediscountcode'])?$rowValue['setupchargediscountcode']:'',
                        isset($value['currencycode'])?$value['currencycode']:'USD',
                        isset($rowValue['itemhasvariations'])?$rowValue['itemhasvariations']:'',
                        );
                if (empty($this->_finalSampleArray) !== false) {
                    $this->createSimpleHeader();
                }
                $this->_finalSampleArray[] = $data;
                $this->_csvfiles->writeSimpleCsv($data);
            }
        } elseif (isset($Prices['quantities']['quantity']) && $Prices['quantities']['quantity'] != '') {
            $this->singleSimpleArrayPrice($Prices['quantities']['quantity'], $rowValue);
        }
    }

    public function getTyerPrice($Prices, $rowValue)
    {
        if (isset($Prices['price']) && $Prices['price'] != '') {
            if (isset($Prices['price'][0])) {
                foreach ($Prices['price'] as $key=>$value) {
                    if (isset($value['currencycode']) && $value['currencycode'] == 'USD') {
                        $this->getUSDPrice($value, $rowValue);
                    }
                }
            } else {
                $value = $Prices['price'];
                if (isset($value['currencycode']) && $value['currencycode'] == 'USD') {
                    $this->getUSDPrice($value, $rowValue);
                }
            }
        }
    }

    public function getConfigAssociateProduct($value)
    {
        $variationDataArray = isset($value['itemvariations']['itemvariation'])?$value['itemvariations']['itemvariation']:array();
        if (!isset($variationDataArray[0])) {
            $variationData[] = $variationDataArray;
        } else {
            $variationData = $variationDataArray;
        }
        $variation = array();
        foreach ($variationData as $variationKey=>$variationValue) {
            $variation[] = (isset($variationValue['itemvariationnumber']))?$variationValue['itemvariationnumber']:'';
        }
        return $variation;
    }

    public function singleArrayPrice($value, $rowValue)
    {
        $variation = $this->getConfigAssociateProduct($rowValue);
        $variationString = '';
        if (count($variation) > 0) {
            $variationString = implode(',', $variation);
        }

        $data = array($rowValue['itemnumber'],
                    $rowValue['brandname'],
                    $rowValue['printmethod'],
                    $rowValue['pricemethod'],
                    $this->selectAnOptionBelow($rowValue),
                    $this->subArraysToString($rowValue['pricingkey']),
                    isset($value['quantitybreak'])?$value['quantitybreak']:'',
                    isset($value['catalogprice'])?$value['catalogprice']:'',
                    isset($value['standardnetprice'])?$value['standardnetprice']:'',
                    isset($value['discountcode'])?$value['discountcode']:'',
                    isset($value['addcolorprice'])?$value['addcolorprice']:'',
                    isset($value['addcolornetprice'])?$value['addcolornetprice']:'',
                    isset($value['addcolordiscountcode'])?$value['addcolordiscountcode']:'',
                    isset($rowValue['setupchargedescription'])?$rowValue['setupchargedescription']:'',
                    isset($rowValue['setupchargecatalogprice'])?$rowValue['setupchargecatalogprice']:'',
                    isset($rowValue['netsetupcharge'])?$rowValue['netsetupcharge']:'',
                    isset($rowValue['setupchargediscountcode'])?$rowValue['setupchargediscountcode']:'',
                    'USD',
                    $variationString
                    );
        if (empty($this->_finalArray) !== false) {
            $this->createHeader();
        }
        $this->_finalArray[] = $data;
        $this->_csvfiles->writeConfigCsv($data);
    }

    public function getUSDPrice($Prices, $rowValue)
    {
        $variation = $this->getConfigAssociateProduct($rowValue);
        $variationString = '';
        if (count($variation) > 0) {
            $variationString = implode(',', $variation);
        }
        if (isset($Prices['quantities']['quantity']) && $Prices['quantities']['quantity'] != '' && $this->is_multi2($Prices['quantities']['quantity'])) {
            foreach ($Prices['quantities']['quantity'] as $key=>$value) {
                $data = array($rowValue['itemnumber'],
                            $rowValue['brandname'],
                            $rowValue['printmethod'],
                            $rowValue['pricemethod'],
                            $this->selectAnOptionBelow($rowValue),
                            $this->subArraysToString($rowValue['pricingkey']),
                            isset($value['quantitybreak'])?$value['quantitybreak']:'',
                            isset($value['catalogprice'])?$value['catalogprice']:'',
                            isset($value['standardnetprice'])?$value['standardnetprice']:'',
                            isset($value['discountcode'])?$value['discountcode']:'',
                            isset($value['addcolorprice'])?$value['addcolorprice']:'',
                            isset($value['addcolornetprice'])?$value['addcolornetprice']:'',
                            isset($value['addcolordiscountcode'])?$value['addcolordiscountcode']:'',
                            isset($rowValue['setupchargedescription'])?$rowValue['setupchargedescription']:'',
                            isset($rowValue['setupchargecatalogprice'])?$rowValue['setupchargecatalogprice']:'',
                            isset($rowValue['netsetupcharge'])?$rowValue['netsetupcharge']:'',
                            isset($rowValue['setupchargediscountcode'])?$rowValue['setupchargediscountcode']:'',
                            'USD',
                            $variationString
                            );
                if (empty($this->_finalArray) !== false) {
                    $this->createHeader();
                }
                $this->_finalArray[] = $data;
                $this->_csvfiles->writeConfigCsv($data);
            }
        } elseif (isset($Prices['quantities']['quantity']) && $Prices['quantities']['quantity'] != '') {
            $this->singleArrayPrice($Prices['quantities']['quantity'], $rowValue);
        }
    }
}
