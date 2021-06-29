<?php
namespace Themagnet\Productimport\Model;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\Dir;

/**
 * Class Class Themagnet\Productimport\Model\Csvfiles
 */
class Csvfiles extends \Magento\Framework\Model\AbstractModel
{
    const OUT_PUT_FILE = 'input_config_file.csv';
    const IMAGE_FILE_PATH = 'ftpdownload.themagnetgroup/Product/';
    const IMAGE_SOURCE = '/home/aa290c0e/8a81a83a22.nxcli.net/html';
    const OUT_PUT_FILE_SIMPLE = 'input_file_simple.csv';
    const OUT_PUT_FILE_QTY = 'imprint_location_file.csv';
    const OUT_PUT_FILE_COLOR = 'stock_colors_csv_file.csv';
    const OUT_PUT_FILE_ADDITIONAL = 'addl_charges_csv_file.csv';
    const OUT_PUT_FILE_JSON = 'general-template-v2.json';
    const OUT_PUT_FILE_JSON_BLANK = 'blank-template.json';
    const OUT_PUT_FILE_JSON_BLANK_VARIATION = 'blank-template-variations.json';
    const OUT_PUT_FILE_JSON_SIMPLE = 'Simple_local_import.json';
    const OUT_PUT_FILE_JSON_CONFIG = 'config_local_import.json';
    const COMPLETE_SIMPLE = 'simple.lok';
    const COMPLETE_CONFIG = 'config.lok';
    const PRODUCT_ATTRIBUTS = 'catalog_product_attribte.csv';
    const PRODUCT_MAIN_PRICE = 'catalog_main_price.csv';
    const PRODUCT_ATTRIBUTS_CONFIG = 'catalog_product_config.csv';
    const PRODUCT_ATTRIBUTS_COLOR = 'catalog_product_color.csv';
    const PRODUCT_ADVANCE_PRICE = 'advanced_pricing.csv';
    const PRODUCT_IMAGES_IMPORT = 'product_image_import.csv';
    const PRODUCT_ADDITIONAL_IMAGES_IMPORT = 'product_additional_image_import.csv';
    const ALL_PRODUCT = 'all_product_skus.csv';
    const PRODUCT_IMAGE_URL_PATH = 'http://ftpdownload.themagnetgroup.com/Product/';
    protected $resultRawFactory;
    protected $csvWriter;
    protected $fileFactory;
    protected $directoryList;
    protected $_filesystem;
    protected $config_file;
    protected $file_simple;
    protected $location_file;
    protected $colors_csv_file;
    protected $addl_charges;
    protected $_files;
    protected $_reader;
    protected $_store;
    protected $_product_attribute;
    protected $_product_advance_price;
    protected $_product_main_price;
    protected $_product_image_import;
    protected $_product_attribute_config;
    protected $_all_product;
    protected $_color_attribute;

    /**
     * @var \Themagnet\Productimport\Helper\Data
     */
    protected $helper;

    /**
     * @var false|resource
     */
    protected $_product_additional_image_import;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\File\Csv $csvWriter,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $files,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Store\Model\StoreManagerInterface $store,
        \Themagnet\Productimport\Helper\Data $helper,
        array $data = []
    ) {
        $this->csvWriter = $csvWriter;
        $this->resultRawFactory = $resultRawFactory;
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->_files = $files;
        $this->_reader = $reader;
        $this->_filesystem = $filesystem;
        $this->_store = $store;
        $this->helper = $helper;

        parent::__construct($context , $registry);
    }

    public function copyFiles() 
    {
        $moduleDirectory = $this->_reader->getModuleDir(Dir::MODULE_ETC_DIR, 'Themagnet_Productimport').'/Json/'.self::OUT_PUT_FILE_JSON;
        $destination = $this->setMediaDirectory().'/'.self::OUT_PUT_FILE_JSON;
        $this->_files->cp($moduleDirectory, $destination );
        /* Blank Template */
        $moduleBlankDirectory = $this->_reader->getModuleDir(Dir::MODULE_ETC_DIR, 'Themagnet_Productimport').'/Json/'.self::OUT_PUT_FILE_JSON_BLANK;
        $destinationBlank = $this->setMediaDirectory().'/'.self::OUT_PUT_FILE_JSON_BLANK;
        $this->_files->cp($moduleBlankDirectory, $destinationBlank );
        /* Blank With variation Template */
        $moduleBlankVariationDirectory = $this->_reader->getModuleDir(Dir::MODULE_ETC_DIR, 'Themagnet_Productimport').'/Json/'.self::OUT_PUT_FILE_JSON_BLANK_VARIATION;
        $destinationBlankVariation = $this->setMediaDirectory().'/'.self::OUT_PUT_FILE_JSON_BLANK_VARIATION;
        $this->_files->cp($moduleBlankVariationDirectory, $destinationBlankVariation );

    }

    public function isLokFileExists($type)
    {
        $dirPath = $this->setMediaDirectory();
        if($type == 'simple'){
            $filePath =  $dirPath.'/'. self::COMPLETE_SIMPLE;
        }else{
            $filePath =  $dirPath.'/'. self::COMPLETE_CONFIG;
        }
        if (file_exists($filePath)){
            return true;
        }
        return false;
    }

    public function createSimpleLokFile() 
    {
        $dirPath = $this->setMediaDirectory();
        $filePath =  $dirPath.'/'. self::COMPLETE_SIMPLE;
        if (!file_exists($filePath)) 
        {
            file_put_contents($filePath, '');
        }
        return $filePath;

    }

    public function createConfigLokFile() 
    {
        $dirPath = $this->setMediaDirectory();
        $filePath =  $dirPath.'/'. self::COMPLETE_CONFIG;
        if (!file_exists($filePath)) 
        {
            file_put_contents($filePath, '');
        }
        return $filePath;

    }

    public function openFiles() 
    {
        $this->copyFiles();
        $this->config_file = fopen($this->setFiles(self::OUT_PUT_FILE), 'w');
        $this->location_file = fopen($this->setFiles(self::OUT_PUT_FILE_QTY), 'w');
        $this->colors_csv_file = fopen($this->setFiles(self::OUT_PUT_FILE_COLOR), 'w');
        $this->addl_charges = fopen($this->setFiles(self::OUT_PUT_FILE_ADDITIONAL), 'w');
        $this->file_simple = fopen($this->setFiles(self::OUT_PUT_FILE_SIMPLE), 'w');
        $this->_product_attribute = fopen($this->setFiles(self::PRODUCT_ATTRIBUTS), 'w');
        $this->_product_attribute_config = fopen($this->setFiles(self::PRODUCT_ATTRIBUTS_CONFIG), 'w');
        $this->_product_advance_price = fopen($this->setFiles(self::PRODUCT_ADVANCE_PRICE), 'w');
        $this->_all_product = fopen($this->setFiles(self::ALL_PRODUCT), 'w');
        $this->_color_attribute = fopen($this->setFiles(self::PRODUCT_ATTRIBUTS_COLOR), 'w');
    }

    public function openPriceFiles() 
    {
        $this->_product_attribute = fopen($this->setFiles(self::PRODUCT_ATTRIBUTS), 'w');
        $this->_product_attribute_config = fopen($this->setFiles(self::PRODUCT_ATTRIBUTS_CONFIG), 'w');
        $this->_product_advance_price = fopen($this->setFiles(self::PRODUCT_ADVANCE_PRICE), 'w');
        $this->_product_main_price = fopen($this->setFiles(self::PRODUCT_MAIN_PRICE), 'w');
        $this->_product_image_import = fopen($this->setFiles(self::PRODUCT_IMAGES_IMPORT), 'w');
        $this->_product_additional_image_import = fopen($this->setFiles(self::PRODUCT_ADDITIONAL_IMAGES_IMPORT), 'w');
    }

    public function openAppnedMode() 
    {
        $this->_product_attribute = fopen($this->setFiles(self::PRODUCT_ATTRIBUTS), 'a');
    }

    public function closeFiles() 
    {
        fclose($this->config_file);
        fclose($this->location_file);
        fclose($this->colors_csv_file);
        fclose($this->addl_charges);
        fclose($this->file_simple);
        fclose($this->_product_attribute);
        fclose($this->_product_attribute_config);
        fclose($this->_product_advance_price);
        fclose($this->_all_product);
        fclose($this->_color_attribute);
    }

    public function closeAppnedMode() 
    {
        fclose($this->_product_attribute);
    }

    public function closePriceFiles() 
    {
        fclose($this->_product_attribute);
        fclose($this->_product_attribute_config);
        fclose($this->_product_advance_price);
        fclose($this->_product_main_price);
        fclose($this->_product_image_import);
        fclose($this->_product_additional_image_import);
    }

    public function checkDublicateEntry() 
    {
        $fileArray = array(self::OUT_PUT_FILE, self::OUT_PUT_FILE_QTY, self::OUT_PUT_FILE_COLOR, self::OUT_PUT_FILE_ADDITIONAL, self::OUT_PUT_FILE_SIMPLE,self::PRODUCT_ATTRIBUTS,self::PRODUCT_ATTRIBUTS_CONFIG);
        $dirPath = $this->setMediaDirectory().'/';
        foreach($fileArray as $file){
            $csv = explode('.', $file);
            $csv[0] = $csv[0].'_new';
            $new_file_name = implode('.', $csv);
            $this->removeDublicateEntry($dirPath.$file , $dirPath.$new_file_name);
            $this->changeFileName($dirPath.$file , $dirPath.$new_file_name);
        }
    }


    public function removeDublicateEntry($filename, $filename2)
    {
        if (!file_exists($filename2)) 
        {
            file_put_contents($filename2, '');
        }
        if(filesize($filename) > 0){
            $file = fopen($filename, "r"); 
            $read = fread($file, filesize($filename)); 
            $split = array_unique(explode("\n", $read)); 
            fclose($file); 

            $file2 = fopen($filename2, "a"); 
            foreach($split as $key=>$value) { 
                if($value != "") { 
                    fwrite($file2, $value . "\n"); 
                } 
            } 
            fclose($file2);
        }
         
    }

    public function changeFileName($filename, $filename2)
    {
        if (file_exists($filename)) 
        {
            $this->_files->rm($filename);
        }
        if (file_exists($filename2)) 
        {
            $this->_files->mv($filename2,$filename);
        }

    }

    public function writeConfigCsv($value) 
    {
        $this->writeCsv($this->config_file, $value);
        
    }

    public function getImagePath()
    {
        $fileDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $newdirPath =  $fileDirectory . "import/";
        if (!file_exists($newdirPath)) 
        {
            $this->_files->mkdir($newdirPath, 0777);
        }
        return $newdirPath;
    }

    /**
     * Function getImagePathSource
     *
     * @return string
     */
    public function getImagePathSource()
    {
        return $this->helper->getImageSourcePath();
    }

    public function getImageSourceUrl($sourceFilePath, $filename) 
    {
        $url = $sourceFilePath.rawurlencode($filename);
        if(file_exists($url)){
            return $url;  
        }else{
            $filename = trim(str_replace('-', '', $filename));
            $url = $sourceFilePath.rawurlencode($filename);
            if(file_exists($url)){
                return $url;  
            }
        }
        return $url;
    }

    public function saveImageFiles($filename, $t) 
    {

        //$url = self::PRODUCT_IMAGE_URL_PATH.rawurlencode($filename);
        $sourceFilePath = $this->getImagePathSource();
        //echo $sourceFilePath; exit;
        $url = $this->getImageSourceUrl($sourceFilePath, $filename);
        $filename = trim(str_replace(' ', '-', $filename));
        $filename = trim(str_replace('.jpg', '-'.$t.'.jpg', $filename));
        $filePath = $this->getImagePath().$filename;

        if (file_exists($url)) 
        {
           if (file_exists($filePath)) 
            {
                unlink($filePath);
            }
            if(copy($url, $filePath)) {
                return true;
            }
        }
        return false;
        
        
        /*$ch = curl_init($url);
        $fp = fopen($filePath, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);*/
        
    }

    public function writeSimpleCsv($value) 
    {
       
        $this->writeCsv($this->file_simple, $value);
        
    }

    public function writeLocationCsv($value) 
    {
        $this->writeCsv($this->location_file, $value);

    }

    public function writeColorsCsv($value) 
    {
        $this->writeCsv($this->colors_csv_file, $value);
    }

    public function writeAdditionalCsv($value) 
    {
        $this->writeCsv($this->addl_charges, $value);
    }

    public function writeProductConfigAttributeCsv($value) 
    {
        if(isset($value[0]) && $value[0] != 'sku'){

            $this->writeAllProducts(array($value[0]));
        }
        $this->writeCsv($this->_product_attribute_config, $value);
    }

    public function writeProductAttributeCsv($value) 
    {
        if(isset($value[0]) && $value[0] != 'sku'){

            $this->writeAllProducts(array($value[0]));
        }

        $this->writeCsv($this->_product_attribute, $value);
    }

    public function writeAllProducts($value) 
    {
        $this->writeCsv($this->_all_product, $value);
    }

    public function writeProductPriceCsv($value) 
    {
        $this->writeCsv($this->_product_advance_price, $value);
    }

    public function writeProductMainPriceCsv($value) 
    {
        $this->writeCsv($this->_product_main_price, $value);
    }

    /**
     * Function writeProductImageCsv
     *
     * @param $value
     * @param null $isAdditional
     */
    public function writeProductImageCsv($value, $isAdditional = false)
    {
        if ($isAdditional) {
            $this->writeCsv($this->_product_additional_image_import, $value);
        } else {
            $this->writeCsv($this->_product_image_import, $value);
        }
    }

    public function writeProductColorCsv($value) 
    {
        $this->writeCsv($this->_color_attribute, $value);
    }

    public function writeCsv($resource, $value) 
    {
        @fputcsv($resource, $value);
    }

    public function checkAlreadyImported($file_name)
    {
        $fileTime = filectime($file_name);
        $current =  time();
        $difference = $current - $fileTime;
        $hours = floor($difference / (60) );
        if($hours > 60){
            $fileDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $newdirPath =  $fileDirectory . "csvexport/processed_".$hours.'_'.date('Y-m-d');
            $this->_files->mv($file_name,$newdirPath);
        }
    }

    public function remaneDirInMedia($file_name)
    {
        
        $fileDirectory = $file_name;
        $newdirPath =  $fileDirectory .'_'.time().'_uat2';
        if (file_exists($fileDirectory)) 
        {
            $this->_files->mv($file_name,$newdirPath);
        }
        
    }

    public function getMediaObj()
    {
        return $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    public function getMediaDateDir()
    {
        return "csvexport/".date('Y-m-d');
    }

    public function getMediaImageDir()
    {
        return $this->getMediaDateDir()."/images/";
    }

    public function setMediaDirectory()
    {
        $fileDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $dirPath =  $fileDirectory . $this->getMediaDateDir();

        if (file_exists($dirPath)) 
        {
            $this->checkAlreadyImported($dirPath);
        }

        if (!file_exists($dirPath)) 
        {
            $this->_files->mkdir($dirPath, 0777);
        }
        return $dirPath;
    }

    public function getMediaUrl()
    {
        
        return $this->_store->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'colors/';
    }

    public function downloadFilesPath()
    {
        $dirPath = $this->setMediaDirectory();
        $dirPath =  $dirPath.'/xml-updates/';
        if (!file_exists($dirPath)) 
        {
            $this->_files->mkdir($dirPath, 0775);
        }
        return $dirPath;
    }

    public function setFiles($fileName)
    {
        $dirPath = $this->setMediaDirectory();
        $filePath =  $dirPath.'/'. $fileName;
        if (!file_exists($filePath)) 
        {
            file_put_contents($filePath, '');
        }
        return $filePath;
    }

    public function getJsonFilePath()
    {
        return $this->setMediaDirectory().'/'.self::OUT_PUT_FILE_JSON_SIMPLE;
    }

    public function isJsonFileExixts()
    {
        $filePath = $this->getJsonFilePath();
        if (file_exists($filePath)){
            return true;
        }
        return false;
    }

    public function getJsonFilePathConfig()
    {
        return $this->setMediaDirectory().'/'.self::OUT_PUT_FILE_JSON_CONFIG;
    }

    public function isJsonFileExixtsConfig()
    {
        $filePath = $this->getJsonFilePathConfig();
        if (file_exists($filePath)){
            return true;
        }
        return false;
    }

    public function getProductAttributs()
    {
        return $this->setMediaDirectory().'/'.self::PRODUCT_ATTRIBUTS;
    }
    
    public function isProductAttributsExixts()
    {
        $filePath = $this->getProductAttributs();
        if (file_exists($filePath)){
            return true;
        }
        return false;
    }

    public function getProductAdvancePrice()
    {
        return $this->setMediaDirectory().'/'.self::PRODUCT_ADVANCE_PRICE;
    }

    public function getProductMainPrice()
    {
        return $this->setMediaDirectory().'/'.self::PRODUCT_MAIN_PRICE;
    }

    public function getAllProductSku()
    {
        return $this->setMediaDirectory().'/'.self::ALL_PRODUCT;
    }

    /**
     * Function getProductImageFile
     *
     * @param bool|string $csvImportFile
     * @return string
     */
    public function getProductImageFile($csvImportFile = false)
    {
        $csvImportFile = $csvImportFile ? : self::PRODUCT_IMAGES_IMPORT;
        return $this->setMediaDirectory().'/' . $csvImportFile;
    }

    public function getProductColorFile()
    {
        return $this->setMediaDirectory().'/'.self::PRODUCT_ATTRIBUTS_COLOR;
    }
    
    public function isProductAdvancePrice()
    {
        $filePath = $this->getProductAdvancePrice();
        if (file_exists($filePath)){
            return true;
        }
        return false;
    }
}