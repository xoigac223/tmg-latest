<?php
namespace Themagnet\Productimport\Model;

use Magento\Framework\Exception\LocalizedException;

class Productattributs extends \Magento\Framework\Model\AbstractModel
{
    const ROW_ADDED = 350000;
    const BEHAVIOR = 'append';
    const VALIDATION_STRATEGY = 'validation-stop-on-errors';
    const FIELD_SEPRATOR = ',';
    const VALUE_SEPRATOR = ',';
    const ATTRIBUTE_ENTITY = 'catalog_product';
    const PRICE_BEHAVIOR = 'replace';
    const PRICE_ENTITY = 'advanced_pricing';
    
    protected $importModel;
    protected $formKey;
    protected $_output;
    protected $_importlogger;
    protected $_csvfiles;
    protected $_productPriceArray;
    protected $_productMainPriceArray;
    protected $csv_run_file;
    protected $csv_config_file;
    protected $csv_simple_file;
    protected $pricing;
    protected $productMainPrice;
    protected $_productImageArray;
    protected $_objectManager;
    protected $cusromer_group = array('NOT LOGGED IN'=>0,'General'=>1, 'Wholesale'=>2);
    /**
     * @var Import\ImportFactory
     */
    private $importModelFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Themagnet\Productimport\Model\Import\ImportFactory $importModelFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        Csvfiles $csvfiles,
        \Themagnet\Productimport\Model\Import\AdvancedPricing $pricing,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->importModelFactory = $importModelFactory;
        $this->importModel = $this->importModelFactory->create();
        $this->formKey = $formKey;
        $this->_csvfiles = $csvfiles;
        $this->pricing = $pricing;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $registry);
    }

    /**
     * Method initNewObject
     *
     * @return Import\Import
     */
    public function initNewObject()
    {
        $this->importModel = $this->importModelFactory->create();
        return $this->importModel;
    }

    public function createProductPriceHeader()
    {
        $header = array('sku','tier_price_website','tier_price_customer_group','tier_price_qty','tier_price');
        $this->_productPriceArray[] = $header;
        $this->_csvfiles->writeProductPriceCsv($header);
    }

    public function createProductMainPriceHeader()
    {
        $header = array('sku','price');
        $this->_productMainPriceArray[] = $header;
        $this->_csvfiles->writeProductMainPriceCsv($header);
    }

    /**
     * Function createProductImageHeader
     *
     * @return bool
     */
    public function createProductImageHeader()
    {
        $header = [
            'sku',
            'base_image',
            'small_image',
            'thumbnail_image'
        ];
        $additionalImagesHeader = [
            'sku',
            'additional_images'
        ];
        $this->_productImageArray[] = $header;
        $this->_csvfiles->writeProductImageCsv($header);
        $this->_csvfiles->writeProductImageCsv($additionalImagesHeader, true);
        return true;
    }

    public function setExternalFiles()
    {
        $folderPath = $this->_csvfiles->setMediaDirectory().'/';
        $this->csv_config_file = $folderPath. Csvfiles::OUT_PUT_FILE;
        $this->csv_simple_file = $folderPath. Csvfiles::OUT_PUT_FILE_SIMPLE;
    }

    public function createPriceCsvSimple()
    {
        $this->csv_run_file = $this->csv_simple_file;
        return $this->createPriceCsv();
    }

    public function createPrcieCsvConfig()
    {
        $this->csv_run_file = $this->csv_config_file;
        return $this->createPriceConfigCsv();
    }

    public function getKeySortArray($arrayElement)
    {
        //ksort($array);
        foreach ($arrayElement as & $qtys) {
            ksort($qtys);
            foreach ($qtys as & $qty) {
                ksort($qty);
            }
        }
        return $arrayElement;
    }

    public function createPriceCsv()
    {
        if (filesize($this->csv_run_file) == 0) {
            $this->_importlogger->debugLog((string)__($this->csv_run_file. " file is empty"));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.$this->csv_run_file.' file is empty</comment>');
            }
            return false;
        }

        if (empty($this->_productPriceArray) !== false) {
            $this->createProductPriceHeader();
        }

        $csv = array_map('str_getcsv', file($this->csv_run_file));
        $headers = array_flip($csv[0]);
        array_splice($csv, 0, 1);

        $skus = [];
        
        foreach ($csv as $line) {
            $sku = trim($line[$headers['ItemNumber']]);
            $printMethod = trim($line[$headers['PrintMethod']]);
            $qty = $line[$headers['QuantityBreak']];
            if ($printMethod != 'Random Sample') {
                $notlogin = $this->cusromer_group['NOT LOGGED IN'];
                $general = $this->cusromer_group['General'];
                $wholesale = $this->cusromer_group['Wholesale'];
                $skus[$sku][$notlogin][$qty][] = $line[$headers['CatalogPrice']];
                $skus[$sku][$general][$qty][] = $line[$headers['StandardNetPrice']];
                $skus[$sku][$wholesale][$qty][] = $line[$headers['StandardNetPrice']];
            }
        }

        foreach ($skus as $sku => $groups) {
            $groups = $this->getKeySortArray($groups);
            //print_r($groups); exit;
            foreach ($groups as $group=>$qty) {
                $this->writePriceCsvs($sku, $group, $qty);
            }
        }
    }

    public function createPriceConfigCsv()
    {
        if (filesize($this->csv_run_file) == 0) {
            $this->_importlogger->debugLog((string)__($this->csv_run_file. " file is empty"));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.$this->csv_run_file.' file is empty</comment>');
            }
            return false;
        }

        if (empty($this->_productPriceArray) !== false) {
            $this->createProductPriceHeader();
        }

        $csv = array_map('str_getcsv', file($this->csv_run_file));
        $headers = array_flip($csv[0]);
        array_splice($csv, 0, 1);

        $skus = [];
        
        foreach ($csv as $line) {
            $sku = trim($line[$headers['ItemNumber']]);
            $associateproduct = trim($line[$headers['associateproduct']]);
            $printMethod = trim($line[$headers['PrintMethod']]);
            $qty = $line[$headers['QuantityBreak']];
            if ($printMethod != 'Random Sample') {
                $notlogin = $this->cusromer_group['NOT LOGGED IN'];
                $general = $this->cusromer_group['General'];
                $wholesale = $this->cusromer_group['Wholesale'];
                $skus[$sku]['associateproduct'] = $associateproduct;
                $skus[$sku][$notlogin][$qty][] = $line[$headers['CatalogPrice']];
                $skus[$sku][$general][$qty][] = $line[$headers['StandardNetPrice']];
                $skus[$sku][$wholesale][$qty][] = $line[$headers['StandardNetPrice']];
            }
        }
        $newSkus = array();
        foreach ($skus as $skuKey => $skuValue) {
            //$newSkus[$skuKey] = $skuValue;
            if (isset($skuValue['associateproduct']) && $skuValue['associateproduct'] != '') {
                $products = explode(',', $skuValue['associateproduct']);
                unset($skuValue['associateproduct']);
                $newSkus[$skuKey] = $skuValue;
                if (count($products)) {
                    foreach ($products as $product) {
                        $newSkus[$product] = $skuValue;
                    }
                }
            }
        }
        //print_r($newSkus); exit;
        foreach ($newSkus as $sku => $groups) {
            $this->productMainPrice = array();
            $groups = $this->getKeySortArray($groups);
            //print_r($groups); exit;
            foreach ($groups as $group=>$qty) {
                $this->writePriceCsvs($sku, $group, $qty);
            }
            $this->writeMainPriceCsvs($this->productMainPrice);
        }
    }

    public function writeMainPriceCsvs($prcedata)
    {
        if (empty($this->_productMainPriceArray) !== false) {
            $this->createProductMainPriceHeader();
        }
        if (count($prcedata) > 0) {
            foreach ($prcedata as $key=>$value) {
                $priceValue = max($value);
                $sku = $key;
                $sku = str_replace('-', '', $sku);
                $csvData = array($sku , $priceValue);
                $this->_csvfiles->writeProductMainPriceCsv($csvData);
            }
        }
    }

    public function writePriceCsvs($sku, $group, $qty)
    {
        $customerGroup = array_flip($this->cusromer_group);
        if ($sku != '' && is_array($qty)) {
            $priceValue = 0;
            $qtyValue = 0;
            foreach ($qty as $key=>$value) {
                arsort($value); /* Secending order*/
                $qtyValue = $key;
                $priceValue = isset($value[0])?$value[0]:0;
                $this->productMainPrice[$sku][] = $priceValue;
                if ($priceValue != 0) {
                    $csvData = array($sku , 'All Websites [USD]', $customerGroup[$group],$qtyValue,$priceValue);
                    $this->_csvfiles->writeProductPriceCsv($csvData);
                }
            }
        }
    }

    public function getProducSkusImage($filePath)
    {
        $skus = array();
        if (filesize($filePath) != 0) {
            $csv = array_map('str_getcsv', file($filePath));
            foreach ($csv as $line) {
                $skus[] = trim($line[0]);
            }
            $skus = array_values(array_unique($skus));
        }
        return $skus;
    }

    public function saveImageIntoFolder($value, $t)
    {
        if ($value != '') {
            $fileName = $value.'.jpg';
            return $this->_csvfiles->saveImageFiles($fileName, $t);
        }

        return false;
    }

    public function getConfigurableAsoProductImage($sku)
    {
        $configProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->loadByAttribute('sku', $sku);
        if (empty($configProduct) !== true) {
            if ($configProduct->getTypeId() == 'configurable') {
                $_children = $configProduct->getTypeInstance()->getUsedProducts($configProduct);
                //$this->saveImageCsv($sku);
                foreach ($_children as $child) {
                    $this->saveImageCsv($child->getSku());
                    $this->saveAdditionalImageCsv($child->getSku());
                }
            }
        }
        $this->saveImageCsv($sku);
        $this->saveAdditionalImageCsv($sku);
    }

    /**
     * Method saveImageCsv
     *
     * @param string $sku
     */
    public function saveImageCsv($sku)
    {
        $t = time();
        $this->saveImageIntoFolder($sku, $t);
        $csvData = [
            $sku ,
            trim(str_replace(' ', '-', $sku)).'-'.$t.'.jpg',
            trim(str_replace(' ', '-', $sku)).'-'.$t.'.jpg',
            trim(str_replace(' ', '-', $sku)).'-'.$t.'.jpg'
        ];
        $this->_csvfiles->writeProductImageCsv($csvData);
    }

    /**
     * Method saveAdditionalImageCsv
     *
     * @param string $sku
     */
    public function saveAdditionalImageCsv($sku)
    {
        $t = time();

        for ($i = 1; $i <= 20; $i++) {
            $imageName = $sku;
            if ($i < 10) {
                $imageName .= '-00' . $i;
            } else {
                $imageName .= '-0' . $i;
            }
            if ($this->saveImageIntoFolder($imageName, $t)) {
                $csvData = [
                    $sku,
                    trim(str_replace(' ', '-', $imageName)) . '-' . $t . '.jpg',
                ];
                $this->_csvfiles->writeProductImageCsv($csvData, true);
            }
        }
    }

    public function createImageCsv()
    {
        $filePath = $this->_csvfiles->getAllProductSku();
        if (filesize($filePath) == 0) {
            $this->_importlogger->debugLog((string)__($filePath. " file is empty"));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.$filePath.' file is empty</comment>');
            }
            return false;
        }

        if (empty($this->_productImageArray) !== false) {
            $this->createProductImageHeader();
        }

        $csv = $this->getProducSkusImage($filePath);
        
        foreach ($csv as $line) {
            $this->getConfigurableAsoProductImage($line);
        }
    }

    public function appendConfigToSimple()
    {
        $filepath = $this->_csvfiles->setMediaDirectory().'/'. Csvfiles::PRODUCT_ATTRIBUTS_CONFIG;
        if (filesize($filepath) == 0) {
            return false;
        }

        $csv = array_map('str_getcsv', file($filepath));
        $headers = array_flip($csv[0]);
        array_splice($csv, 0, 1);

        $skus = [];
        $this->_csvfiles->openAppnedMode();
        foreach ($csv as $line) {
            $this->_csvfiles->writeProductAttributeCsv($line);
        }
        $this->_csvfiles->closeAppnedMode();
    }

    public function importProducts($logger, $output = null)
    {
        $this->_importlogger = $logger;
        if ($output) {
            $this->_output = $output;
        }
        $this->appendConfigToSimple();
        //exit;
        $filepath = $this->_csvfiles->setMediaDirectory().'/'. Csvfiles::PRODUCT_ATTRIBUTS;
        if (filesize($filepath) == 0) {
            $this->_importlogger->debugLog((string)__($filepath. " file is empty"));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.$filepath.' file is empty</comment>');
            }
            return false;
        }
        if ($output) {
            $output->writeln('<info>Simple product import start</info>');
        }
        $param = array( 'form_key' => $this->getFormKey(),
                        'entity' => self::ATTRIBUTE_ENTITY,
                        'behavior' => self::BEHAVIOR,
                        'validation_strategy' => self::VALIDATION_STRATEGY,
                        'allowed_error_count' => self::ROW_ADDED,
                        '_import_field_separator' => self::FIELD_SEPRATOR,
                        '_import_multiple_value_separator' => self::VALUE_SEPRATOR,
                        'import_images_file_dir' => ''//$this->_csvfiles->setMediaDirectory()."/images/"
                       );
        $this->importModel->setWorkingDir($this->_csvfiles->getMediaObj());
        $this->importModel->setData($param);

        $this->importModel->importedFile = Csvfiles::PRODUCT_ATTRIBUTS;//'catalog_product.csv';
        $this->importModel->startedAt = date('Y-m-d H:i:s');
        $this->importModel->importWorkingDir = $this->_csvfiles->getMediaDateDir().'/';
        $this->importModel->_output = $this->_output;
        $this->importModel->_importlogger = $this->_importlogger;

        $errorAggregator = $this->importModel->getErrorAggregator();
        try {
            $source = $this->importModel->uploadFileAndGetSource();
            //print_r($source); exit;
            $isImport = $this->processValidationResult($this->importModel->validateSource($source));
            if ($isImport == 0) {
                $this->importModel->importSource();
            }
            if ($output) {
                $output->writeln('<info>Simple product import end</info>');
            }
            return true;
        } catch (\Exception $e) {
            $this->_importlogger->debugLog((string)__($e->getMessage()));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.(string)__($e->getMessage()).'</comment>');
            }
            return false;
        }
    }

    public function replaceProductTierPrice()
    {
        $filePath = $this->_csvfiles->getProductAdvancePrice();
        
        if (filesize($filePath) != 0) {
            $csv = array_map('str_getcsv', file($filePath));
            $headers = array_flip($csv[0]);
            array_splice($csv, 0, 1);
            $skus = array();
            foreach ($csv as $line) {
                $skus[] = trim($line[$headers['sku']]);
            }
            $skus = array_values(array_unique($skus));
            if (count($skus) > 0) {
                $this->pricing->deleteProductPrices($skus);
            }
        }
    }

    public function importPriceAttributs($logger, $output = null)
    {
        $this->_importlogger = $logger;
        if ($output) {
            $this->_output = $output;
        }
        $this->_csvfiles->openPriceFiles();
        $this->setExternalFiles();
        $this->createPriceCsvSimple();
        $this->createPrcieCsvConfig();
        $this->_csvfiles->closePriceFiles();
        $this->replaceProductTierPrice();
        //exit;
        //print_r($this->productMainPrice); exit;
        if (filesize($this->_csvfiles->getProductAdvancePrice()) == 0) {
            $this->_importlogger->debugLog((string)__($this->_csvfiles->getProductAdvancePrice(). " file is empty"));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.$this->_csvfiles->getProductAdvancePrice().' file is empty</comment>');
            }
            return false;
        }
        $param = array( 'form_key' => $this->getFormKey(),
                        'entity' => self::PRICE_ENTITY,
                        'behavior' => self::BEHAVIOR,
                        'validation_strategy' => self::VALIDATION_STRATEGY,
                        'allowed_error_count' => self::ROW_ADDED,
                        '_import_field_separator' => self::FIELD_SEPRATOR,
                        '_import_multiple_value_separator' => self::VALUE_SEPRATOR,
                        'import_images_file_dir' =>''
                       );
        $this->importModel->setWorkingDir($this->_csvfiles->getMediaObj());
        $this->importModel->setData($param);
        $this->importModel->importedFile = Csvfiles::PRODUCT_ADVANCE_PRICE;
        $this->importModel->startedAt = date('Y-m-d H:i:s');
        $this->importModel->importWorkingDir = $this->_csvfiles->getMediaDateDir().'/';
        $this->importModel->_output = $this->_output;
        $this->importModel->_importlogger = $this->_importlogger;

        $errorAggregator = $this->importModel->getErrorAggregator();
        try {
            $source = $this->importModel->uploadFileAndGetSource();
            //print_r($source); exit;
            $isImport = $this->processValidationResult($this->importModel->validateSource($source));
            if ($isImport == 0) {
                $this->importModel->importSource();
            }
            $this->importProductMainPrice($logger, $output);
        } catch (\Exception $e) {
            $this->_importlogger->debugLog((string)__($e->getMessage()));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.(string)__($e->getMessage()).'</comment>');
            }
            return false;
        }
    }
    
    public function importProductMainPrice($logger, $output = null)
    {
        $this->initNewObject();
        $this->_importlogger = $logger;
        if ($output) {
            $this->_output = $output;
        }
        //print_r($this->importModel->getData()); exit;
        $filepath = $this->_csvfiles->getProductMainPrice();
        if (filesize($filepath) == 0) {
            $this->_importlogger->debugLog((string)__($filepath. " file is empty"));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.$filepath.' file is empty</comment>');
            }
            return false;
        }
        if ($output) {
            $output->writeln('<info>Product main price import start</info>');
        }
        $param = array( 'form_key' => $this->getFormKey(),
                        'entity' => self::ATTRIBUTE_ENTITY,
                        'behavior' => self::BEHAVIOR,
                        'validation_strategy' => self::VALIDATION_STRATEGY,
                        'allowed_error_count' => self::ROW_ADDED,
                        '_import_field_separator' => self::FIELD_SEPRATOR,
                        '_import_multiple_value_separator' => self::VALUE_SEPRATOR,
                        'import_images_file_dir' => ''//$this->_csvfiles->setMediaDirectory()."/images/"
                       );

        $this->importModel->setWorkingDir($this->_csvfiles->getMediaObj());
        $this->importModel->setData($param);
        
        $this->importModel->importedFile = Csvfiles::PRODUCT_MAIN_PRICE;//'catalog_product.csv';
        $this->importModel->startedAt = date('Y-m-d H:i:s');
        $this->importModel->importWorkingDir = $this->_csvfiles->getMediaDateDir().'/';
        $this->importModel->_output = $this->_output;
        $this->importModel->_importlogger = $this->_importlogger;
        
        $errorAggregator = $this->importModel->getErrorAggregator();
        try {
            $source = $this->importModel->uploadFileAndGetSource();
            
            $isImport = $this->processValidationResult($this->importModel->validateSource($source));
            if ($isImport == 0) {
                $this->importModel->importSource();
            }
            if ($output) {
                $output->writeln('<info>Simple product import end</info>');
            }
            return true;
        } catch (\Exception $e) {
            $this->_importlogger->debugLog((string)__($e->getMessage()));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.(string)__($e->getMessage()).'</comment>');
            }
            return false;
        }
    }

    /**
     * Function importProductImages
     *
     * @param  $logger
     * @param  null $output
     * @return bool
     * @throws LocalizedException
     */
    public function importProductImages($logger, $output = null)
    {
        $this->_importlogger = $logger;
        if ($output) {
            $this->_output = $output;
        }
        $this->_csvfiles->openPriceFiles();
        $this->createImageCsv();
        $this->_csvfiles->closePriceFiles();

        $this->_output->writeln('<info>'.(string)__('Import Images...').'</info>');
        $this->processImportImages(Csvfiles::PRODUCT_IMAGES_IMPORT);

        // Import additional images
        $this->_output->writeln('<info>'.(string)__('Import Additional Images...').'</info>');
        $this->processImportImages(Csvfiles::PRODUCT_ADDITIONAL_IMAGES_IMPORT);

        return true;
    }

    /**
     * Function processImportImages
     *
     * @param  string $csvImportFile
     * @return bool
     * @throws LocalizedException
     */
    protected function processImportImages($csvImportFile)
    {
        $this->initNewObject();
        if (filesize($this->_csvfiles->getProductImageFile($csvImportFile)) == 0) {
            $this->_importlogger->debugLog((string)__($this->_csvfiles->getProductImageFile(). " file is empty"));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.$this->_csvfiles->getProductImageFile().' file is empty</comment>');
            }
            return false;
        }
        $param = [
            'form_key' => $this->getFormKey(),
            'entity' => self::ATTRIBUTE_ENTITY,
            'behavior' => self::BEHAVIOR,
            'validation_strategy' => self::VALIDATION_STRATEGY,
            'allowed_error_count' => self::ROW_ADDED,
            '_import_field_separator' => self::FIELD_SEPRATOR,
            '_import_multiple_value_separator' => self::VALUE_SEPRATOR,
            'import_images_file_dir' =>''
        ];

        $this->importModel->setWorkingDir($this->_csvfiles->getMediaObj());
        $this->importModel->setData($param);
        $this->importModel->importedFile = $csvImportFile;
        $this->importModel->startedAt = date('Y-m-d H:i:s');
        $this->importModel->importWorkingDir = $this->_csvfiles->getMediaDateDir().'/';
        $this->importModel->_output = $this->_output;
        $this->importModel->_importlogger = $this->_importlogger;

        $errorAggregator = $this->importModel->getErrorAggregator();
        try {
            $source = $this->importModel->uploadFileAndGetSource($csvImportFile);
            $isImport = $this->processValidationResult($this->importModel->validateSource($source));
            if ($isImport == 0) {
                $this->importModel->importSource();
            }
        } catch (\Exception $e) {
            $this->_importlogger->debugLog((string)__($e->getMessage()));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.(string)__($e->getMessage()).'</comment>');
            }
            return false;
        }

        return true;
    }

    public function importProductColor($logger, $output = null)
    {
        $this->_importlogger = $logger;
        if ($output) {
            $this->_output = $output;
        }
        if (filesize($this->_csvfiles->getProductColorFile()) == 0) {
            $this->_importlogger->debugLog((string)__($this->_csvfiles->getProductColorFile(). " file is empty"));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.$this->_csvfiles->getProductColorFile().' file is empty</comment>');
            }
            return false;
        }
        $param = array( 'form_key' => $this->getFormKey(),
                        'entity' => self::ATTRIBUTE_ENTITY,
                        'behavior' => self::BEHAVIOR,
                        'validation_strategy' => self::VALIDATION_STRATEGY,
                        'allowed_error_count' => self::ROW_ADDED,
                        '_import_field_separator' => self::FIELD_SEPRATOR,
                        '_import_multiple_value_separator' => self::VALUE_SEPRATOR,
                        'import_images_file_dir' =>''
                       );
        $this->importModel->setWorkingDir($this->_csvfiles->getMediaObj());
        $this->importModel->setData($param);
        $this->importModel->importedFile = Csvfiles::PRODUCT_ATTRIBUTS_COLOR;
        $this->importModel->startedAt = date('Y-m-d H:i:s');
        $this->importModel->importWorkingDir = $this->_csvfiles->getMediaDateDir().'/';
        $this->importModel->_output = $this->_output;
        $this->importModel->_importlogger = $this->_importlogger;
        
        $errorAggregator = $this->importModel->getErrorAggregator();
        try {
            $source = $this->importModel->uploadFileAndGetSource();
            //print_r($source); exit;
            $isImport = $this->processValidationResult($this->importModel->validateSource($source));
            if ($isImport == 0) {
                $this->importModel->importSource();
            }
            //
        } catch (\Exception $e) {
            $this->_importlogger->debugLog((string)__($e->getMessage()));
            if ($this->_output) {
                $this->_output->writeln('<comment>'.(string)__($e->getMessage()).'</comment>');
            }
            return false;
        }
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    private function getImport()
    {
        return $this->importModel;
    }

    private function processValidationResult($validationResult)
    {
        $import = $this->getImport();
        $error_count = 0;
        if (!$import->getProcessedRowsCount()) {
            if (!$import->getErrorAggregator()->getErrorsCount()) {
                $this->_output->writeln((string)__('This file is empty. Please try another one.'));
            } else {
                foreach ($import->getErrorAggregator()->getAllErrors() as $error) {
                    $this->_output->writeln((string)$error->getErrorMessage());
                }
            }
        } else {
            $errorAggregator = $import->getErrorAggregator();
            
            if (!$validationResult) {
                $this->_output->writeln(
                    (string)
                    __('Data validation failed. Please fix the following errors and upload the file again.')
                );
                $error_count = $this->collectErrors();
            } else {
                if ($import->isImportAllowed()) {
                    $this->_output->writeln('<info>'.(string)
                        __('File is valid! Starting import process').'</info>');
                } else {
                    $this->_output->writeln((string)__('The file is valid, but we can\'t import it for some reason.'));
                }
            }
            $this->_output->writeln(
                (string)
                __(
                    'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                    $import->getProcessedRowsCount(),
                    $import->getProcessedEntitiesCount(),
                    $errorAggregator->getInvalidRowsCount(),
                    $errorAggregator->getErrorsCount()
                )
            );
        }
        return $error_count;
    }

    private function collectErrors()
    {
        $import = $this->getImport();
        $errors = $import->getErrorAggregator()->getAllErrors();
        $i=0;
        /*echo "<prE>";
        print_r($errors); exit;*/
        foreach ($errors as $error) {
            $this->_output->writeln('<comment>Row No.'.$error->getRowNumber().' SKU: '.$error->getColumnName().' '.$error->getErrorMessage().'</comment>');
            $i++;
        }
        return $i;
    }
}
