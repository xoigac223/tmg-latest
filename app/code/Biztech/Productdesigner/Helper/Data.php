<?php

namespace Biztech\Productdesigner\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

   
    const XML_PATH_INSTALLED = 'productdesigner/activation/installed';
    const XML_PATH_DATA = 'productdesigner/activation/data';
    const XML_PATH_WEBSITES = 'productdesigner/activation/websites';
    const XML_PATH_EN = 'productdesigner/activation/en';
    const XML_PATH_KEY = 'productdesigner/activation/key';
    const XML_PATH_ENABLED = 'productdesigner/general/enable';
    protected $_backendUrl;
    protected $_logger;
    protected $_moduleList;
    protected $_zend;
    protected $_resourceConfig;
    protected $_encryptor;
    protected $_web;
    protected $_objectManager;
    protected $_coreConfig;
    protected $_store;

    public function __construct(
    \Magento\Framework\App\Helper\Context $context, \Magento\Framework\Encryption\EncryptorInterface $encryptor, \Magento\Framework\Module\ModuleListInterface $moduleList, /* \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, */ \Zend\Json\Json $zend, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Config\Model\ResourceModel\Config $resourceConfig, \Magento\Framework\ObjectManagerInterface $objectmanager, \Magento\Framework\App\Config\ReinitableConfigInterface $coreConfig, \Magento\Store\Model\Website $web, \Magento\Store\Model\StoreManagerInterface $store,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->_zend = $zend;
        $this->_logger = $context->getLogger();
        $this->_moduleList = $moduleList;
        $this->_storeManager = $storeManager;
        $this->_resourceConfig = $resourceConfig;
        $this->_encryptor = $encryptor;
        $this->_web = $web;
        $this->_objectManager = $objectmanager;
        $this->_coreConfig = $coreConfig;
        $this->_store = $store;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_backendUrl = $backendUrl;
        parent::__construct($context);
        
    }
    public function getProductsGridUrl()
    {
        return $this->_backendUrl->getUrl('templatecategory/products', ['_current' => true]);
    }
    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    public function getTmpMediaUrl($file) {
        $file = $this->_prepareFileForUrl($file);

        if (substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }

        return $this->getBaseTmpMediaUrl() . '/' . $file;
    }

    public function getTmpMaskingUrl($file) {
        $file = $this->_prepareFileForUrl($file);

        if (substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }

        return $this->getBaseTmpMaskingUrl() . '/' . $file;
    }

    public function getTmpShapeUrl($file) {
        $file = $this->_prepareFileForUrl($file);

        if (substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }

        return $this->getBaseTmpShapesUrl() . '/' . $file;
    }

    public function getTmpFontUrl($file) {
        $file = $this->_prepareFileForUrl($file);

        if (substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }

        return $this->getBaseTmpFontUrl() . '/' . $file;
    }

    public function getTmpAttributeUrl($file) {
        $file = $this->_prepareFileForUrl($file);

        if (substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }

        return $this->getBaseTmpAttributeUrl() . '/' . $file;
    }

    public function getTmpUploadedImageUrl($file) {
        $file = $this->_prepareFileForUrl($file);

        if (substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }

        return $this->getBaseTmpUploadedImageUrl() . '/' . $file;
    }

    
    public function getConfig($data, $storeid = '') {
        if ($storeid) {
            $store = $this->_store->getStore($storeid);
            return $this->_scopeConfig->getValue($data, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getCode());
        } else {
            return $this->_scopeConfig->getValue($data, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }
    

    public function getBaseTmpMediaUrlAddition() {
        return 'productdesigner/clipart';
    }

    public function getBaseTmpMaskingUrlAddition() {
        return 'productdesigner/masking';
    }

    public function getBaseTmpShapesUrlAddition() {
        return 'productdesigner/shapes';
    }
  
    public function getBaseTmpAttributeUrlAddition() {
        return 'productdesigner/attribute';
    }    

    public function getBaseTmpUploadedImageAddition() {
        return 'productdesigner/uploadedImage';
    }

   public function getAllStoreDomains() {
            $domains = array();
            foreach ($this->_storeManager->getWebsites() as $website) {
                $url = $website->getConfig('web/unsecure/base_url');
                if ($domain = trim(preg_replace('/^.*?\/\/(.*)?\//', '$1', $url))) {
                    $domains[] = $domain;
                }
                $url = $website->getConfig('web/secure/base_url');
                if ($domain = trim(preg_replace('/^.*?\/\/(.*)?\//', '$1', $url))) {
                    $domains[] = $domain;
                }
            }
            return array_unique($domains);
    }
    
    public function getDataInfo() {
        
        $data = $this->scopeConfig->getValue(self::XML_PATH_DATA, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return json_decode(base64_decode($this->_encryptor->decrypt($data)));
       
    }

    public function getAllWebsites() {

        $value = $this->scopeConfig->getValue(self::XML_PATH_INSTALLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$value) {
            return array();
        }
        $data = $this->scopeConfig->getValue(self::XML_PATH_DATA, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $web = $this->scopeConfig->getValue(self::XML_PATH_WEBSITES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//$websites = explode(',', str_replace($data, '', $this->_encryptor->decrypt($web)));
        $websites = explode(',', str_replace($data, '', $this->_encryptor->decrypt($web)));
        $websites = array_diff($websites, array(""));
        return $websites;
    }

    public function getFormatUrl($url) {
        $input = trim($url, '/');
        if (!preg_match('#^http(s)?://#', $input)) {
            $input = 'http://' . $input;
        }
        $urlParts = parse_url($input);
        if (isset($urlParts['path'])) {
            $domain = preg_replace('/^www\./', '', $urlParts['host'] . $urlParts['path']);
        } else {
            $domain = preg_replace('/^www\./', '', $urlParts['host']);
        }
        return $domain;
    }

    public function isEnable() {
        $websiteId = $this->_store->getStore()->getWebsite()->getId();
        $isenabled = $this->scopeConfig->getValue(self::XML_PATH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($isenabled) {
            if ($websiteId) {
                $websites = $this->getAllWebsites();
                $key = $this->scopeConfig->getValue(self::XML_PATH_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($key == null || $key == '') {
                    return false;
                } else {
                    $en = $data = $this->scopeConfig->getValue(self::XML_PATH_EN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    if ($isenabled && $en && in_array($websiteId, $websites)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            } else {
                $en = $en = $data = $this->scopeConfig->getValue(self::XML_PATH_EN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($isenabled && $en) {
                    return true;
                }
            }
        }
    }

}
