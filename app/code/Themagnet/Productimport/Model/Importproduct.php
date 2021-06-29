<?php
namespace Themagnet\Productimport\Model;
 
class Importproduct extends \Magento\Framework\Model\AbstractModel
{
    protected $_xmlcsv;
    protected $_helper;
    protected $_ftp;
    protected $_csvfiles;
    protected $_createjson;
    protected $_importlogger;
    protected $_connection;
    protected $_dyOption;
    protected $_typeList;
    protected $_objectManager;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Themagnet\Productimport\Model\Xmltocsv $xmlcsv,
        \Themagnet\Productimport\Helper\Data $helper,
        \Themagnet\Productimport\Model\Ftpfiles $ftp,
        \Themagnet\Productimport\Model\Csvfiles $csvfiles,
        \Themagnet\Productimport\Model\Createjson $createjson,
        \Themagnet\Productimport\Model\Logger $logger,
        \Magento\Framework\App\ResourceConnection $connection,
        \Itoris\DynamicProductOptions\Model\Rewrite\Option $dyOption,
        \Magento\Framework\App\Cache\TypeList $typeList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_xmlcsv = $xmlcsv;
        $this->_helper = $helper;
        $this->_ftp = $ftp;
        $this->_csvfiles = $csvfiles;
        $this->_createjson = $createjson;
        $this->_importlogger = $logger;
        $this->_connection = $connection;
        $this->_dyOption = $dyOption;
        $this->_objectManager = $objectManager;
        parent::__construct($context , $registry);
    }

    public function createFile()
    {
        /*if($this->_csvfiles->isJsonFileExixts() === false){
            $createFiles = $this->_xmlcsv->getAllXmlFiles($this->_importlogger);
            //$createFiles = true;
            if($createFiles === true){
                $this->_createjson->setExternalFiles($this->_importlogger);
                $simple = $this->_createjson->createJsonFileFromCsvSimple();
                $config = $this->_createjson->createJsonFileFromCsvConfig();
            }
        }else{
            $this->_importlogger->debugLog((string)__('Json File is already created'));
        }
        
        $this->importProduct();
        $this->importProductConfig();*/
    }

    public function createSimpleFile($output = null)
    {
        if($this->_csvfiles->isJsonFileExixts() === false){
            $createFiles = $this->_xmlcsv->getAllXmlFiles($this->_importlogger);
            
            if($createFiles === true){
                $this->_createjson->setExternalFiles($this->_importlogger,$output);
                $simple = $this->_createjson->createJsonFileFromCsvSimple();
                $config = $this->_createjson->createJsonFileFromCsvConfig();
            }
        }else{
            $this->_importlogger->debugLog((string)__('Simple Json File is already created'));
            if($output){
                $output->writeln('<info>Simple Json File is already created.</info>');
            }
        }
        
       $this->importProduct($output);
       $this->_csvfiles->createSimpleLokFile();
    }

    public function createConfigFile($output = null)
    {
        if($this->_csvfiles->isJsonFileExixtsConfig() === false){
            $createFiles = $this->_xmlcsv->getAllXmlFiles($this->_importlogger);
            if($createFiles === true){
                $this->_createjson->setExternalFiles($this->_importlogger, $output);
                $config = $this->_createjson->createJsonFileFromCsvConfig();
            }
        }else{
            $this->_importlogger->debugLog((string)__('Config Json File is already created'));
            if($output){
                $output->writeln('<info>Config Json File is already created.</info>');
            }
        }
        
        $this->importProductConfig($output);
        $this->_csvfiles->createConfigLokFile();
        //$this->_ftp->moveFtpFiles($this->_csvfiles);

    }

    public function remaneProcessingFolder($output = null)
    {
        $filepath = $this->_csvfiles->setMediaDirectory();
        $this->_csvfiles->remaneDirInMedia($filepath);
        if($output){
            $output->writeln('<info>Files cleaned, You can start process now</info>');
        }

    }

    public function postImport($output = null)
    {
        /*if($this->_csvfiles->isJsonFileExixts() === false){
            $this->_importlogger->debugLog((string)__('Your import process not completered, file not found.'));
            if($output){
                $output->writeln('<info>Your import process not completered file not found.</info>');
            }
        }else{*/
            $this->_ftp->moveFtpFiles($this->_csvfiles);
            if($output){
                $output->writeln('<info>You import process done.</info>');
            }
        //}

    }

    public function importImages($output = null)
    {
        $_productattributs = $this->_objectManager->create('Themagnet\Productimport\Model\Productattributs');
        if($this->_csvfiles->isJsonFileExixts() === false){
            $createFiles = $this->_xmlcsv->getAllXmlFiles($this->_importlogger);
        }
        
       $_productattributs->importProductImages($this->_importlogger, $output);
    }

    public function importProductColor($output = null)
    {
        $_productattributs = $this->_objectManager->create('Themagnet\Productimport\Model\Productattributs');
        if($this->_csvfiles->isJsonFileExixts() === false){
            $createFiles = $this->_xmlcsv->getAllXmlFiles($this->_importlogger);
        }
        
       $_productattributs->importProductColor($this->_importlogger, $output);
    }

    public function importProductAttributs($output = null)
    {
        $_productattributs = $this->_objectManager->create('Themagnet\Productimport\Model\Productattributs');
        $createFiles = $this->_xmlcsv->getAllXmlFiles($this->_importlogger);
        $this->_importlogger->debugLog((string)__('Product attribute files created'));
        if($output){
          $output->writeln('<info>Product attribute files created.</info>');
        }
        $_productattributs->importProducts($this->_importlogger, $output);
    }

   /* public function importProductAttributsConfig($output = null)
    {
        $_productattributs = $this->_objectManager->create('Themagnet\Productimport\Model\Productattributs');
        $_productattributs->importProductsConfig($this->_importlogger, $output);
    }*/

    public function importProductPrice($output = null)
    {
        $_productattributs = $this->_objectManager->create('Themagnet\Productimport\Model\Productattributs');
        if($this->_csvfiles->isProductAdvancePrice() === false){
            $createFiles = $this->_xmlcsv->getAllXmlFiles($this->_importlogger);
        }else{
            $this->_importlogger->debugLog((string)__('Product attribute files already created'));
            if($output){
                $output->writeln('<info>Product price files already created.</info>');
            }
        }
       $_productattributs->importPriceAttributs($this->_importlogger, $output);
    }

    public function importProduct($output = null)
    {        
        if($this->_csvfiles->isJsonFileExixts() === true){
            $file = $this->_csvfiles->getJsonFilePath();
            $str = file_get_contents($file);
            $configs = @json_decode($str, true);
            if ($configs && !is_null($configs)) {
                $res = $this->_connection;
                $con = $res->getConnection('write');
                $processed = []; $skippedSkus = [];
                $i = 0;
                foreach($configs as $config) {
                    
                    $sku = $config['product_sku'];
                    unset($config['product_sku']);
                    $productId = (int) $con->fetchOne("select `entity_id` from {$res->getTableName('catalog_product_entity')} where `sku` LIKE ".$con->quote($sku));
                    //$config['configuration'] = json_encode($config['configuration']);
                    /*echo "<pre>";
                    print_r($config['configuration']); exit;*/
                    
                    if ($productId) {
                        if (!isset($processed[$productId])) $con->query("delete from {$res->getTableName('itoris_dynamicproductoptions_options')} where `product_id`={$productId}"); 
                        //clean up
                        $processed[$productId] = $sku;
                        //inserting options configs
                        $con->exec("insert into {$res->getTableName('itoris_dynamicproductoptions_options')} set ".$this->getQueryString($con, array_merge($config, ['product_id' => $productId])));
                        //generate options
                        $this->_dyOption->duplicate($productId, $productId);
                        if($output){
                            $output->writeln('<info>SKU: '.$sku.' processed successfully</info>');
                        }
                    } else {
                       $skippedSkus[$sku] = true;
                       if($output){
                            $output->writeln('<error>SKU: '.$sku.' were skipped (Not found)</error>');
                        }
                    }
                    $i++;
                    /*echo $sku;
                    exit;*/
                }

                if (!empty($processed)) {
                    $this->_importlogger->debugLog((string)__('%1 SKUs processed successfully', count($processed)));
                    //invalidate FPC
                    //$cacheTypeList = $this->_typeList;
                    //$cacheTypeList->invalidate('full_page');
                }
                if (!empty($skippedSkus)){
                    $message_new = (string)__('%1 SKUs were skipped: %2', count($skippedSkus), implode(', ', array_keys($skippedSkus)));
                    $this->_importlogger->errorLog($message_new);
                } 
            } else {
                $message = (string)__('Incorrect file format. Should be JSON with dynamic options.');
                $this->_importlogger->errorLog($message);
                if($output){
                        $output->writeln('<error>'.$message.'</error>');
                }
            }

        }else{
            $this->_importlogger->errorLog((string)__('Json File not found'));
            if($output){
                        $output->writeln('<error>Json File not found</error>');
            }
        }
    }

    public function importProductConfig($output = null)
    {        
        if($this->_csvfiles->isJsonFileExixtsConfig() === true){
            $file = $this->_csvfiles->getJsonFilePathConfig();
            $str = file_get_contents($file);
            $configs = @json_decode($str, true);
            if ($configs && !is_null($configs)) {
                $res = $this->_connection;
                $con = $res->getConnection('write');
                $processed = []; $skippedSkus = [];
                $i = 0;
                foreach($configs as $config) {
                    
                    $sku = $config['product_sku'];
                    unset($config['product_sku']);
                    $productId = (int) $con->fetchOne("select `entity_id` from {$res->getTableName('catalog_product_entity')} where `sku` LIKE ".$con->quote($sku));
                    //$config['configuration'] = json_encode($config['configuration']);
                    
                    if ($productId) {
                        if (!isset($processed[$productId])) $con->query("delete from {$res->getTableName('itoris_dynamicproductoptions_options')} where `product_id`={$productId}"); 
                        //clean up
                        $processed[$productId] = $sku;
                        //inserting options configs
                        $con->exec("insert into {$res->getTableName('itoris_dynamicproductoptions_options')} set ".$this->getQueryString($con, array_merge($config, ['product_id' => $productId])));
                        //generate options
                        $this->_dyOption->duplicate($productId, $productId);
                        if($output){
                            $output->writeln('<info>SKU: '.$sku.' processed successfully</info>');
                        }
                    } else {
                       $skippedSkus[$sku] = true;
                       if($output){
                            $output->writeln('<error>SKU: '.$sku.' were skipped</error>');
                        }
                    }
                    $i++;
                    
                }

                if (!empty($processed)) {
                    $this->_importlogger->debugLog((string)__('%1 SKUs processed successfully', count($processed)));
                    //invalidate FPC
                    //$cacheTypeList = $this->_typeList;
                    //$cacheTypeList->invalidate('full_page');
                    
                }
                if (!empty($skippedSkus)){
                    $message_new = (string)__('%1 SKUs were skipped: %2', count($skippedSkus), implode(', ', array_keys($skippedSkus)));
                    $this->_importlogger->errorLog($message_new);
                    
                } 
            } else {
                $message = (string)__('Incorrect file format. Should be JSON with dynamic options.');
                $this->_importlogger->errorLog($message);
                if($output){
                        $output->writeln('<error>'.$message.'</error>');
                }
            }

        }else{
            $this->_importlogger->errorLog((string)__('Json File not found'));
            if($output){
                        $output->writeln('<error>Json File not found</error>');
            }
        }
    }
    
    public function getQueryString($con, $array, $primary = '') 
    {
        $pairs = [];
        if ($primary) unset($array[$primary]); //do not include primary column
        foreach($array as $key => $value) $pairs[] = "`$key`=".(!is_null($value) ? $con->quote($value) : 'NULL');
        return implode(',', $pairs);
    }
}