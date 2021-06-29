<?php

namespace Biztech\Productdesigner\Block;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Productdesigner extends Field {

    protected $_scopeConfig;
    protected $_storeManager;
    protected $_directory;
    protected $_urlInterface;
    protected $helper;
    protected $localeFormat;

    const WIDTH = 'productdesigner/general/imagewidth';
    const HEIGHT = 'productdesigner/general/imageheight';
    const ProductGeneralEnable = 'productdesigner/categoryproductsconfiguration/enablecategoryproducts';
    const TEXT = 'productdesigner/textconfiguration/enabletexttab';
    const QUOTE = 'productdesigner/quotesconfiguration/enablequotes';
    const CLIPART = 'productdesigner/clipartconfiguration/enableclipart';
    const UPLOAD = 'productdesigner/customimageuploadconfiguration/enableuploadcustomimage';
    const TEMPLATE = 'productdesigner/designtemplates/enabledesigntemplates';
    const MYDESIGN = 'productdesigner/mydesigns/showmydesignsatfrontend';
    const SHAPE = 'productdesigner/shapesconfiguration/enableshapes';
    const Masking = 'productdesigner/maskingconfiguration/enablemasking';
    const Designtemplates = 'productdesigner/designtemplates/enabledesigntemplates';
    const DownloadDesign = 'productdesigner/downloaddesignfromdesignerpage/Downloaddesignfromdesignerpage';
    const ClipartLimit = 'productdesigner/clipartconfiguration/setclipartimagelimit';
    const ClipartImageLimit = 'productdesigner/clipartconfiguration/imagelimit';
    const LimitAlert = 'productdesigner/clipartconfiguration/errormessageclipart';

    /**
     * @var PropertyLocker
     */

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param PropertyLocker $propertyLocker
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Template\Context $context,
       /*     \Magento\Framework\UrlInterface $urlInterface,
            \Magento\Framework\Filesystem $filesystem,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Store\Model\StoreManagerInterface $storeManager, */
            \Magento\Tax\Helper\Data $helper,
            \Magento\Framework\Locale\FormatInterface $localeFormat,
            array $data = []
    ) {
      /*  $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_urlInterface = $urlInterface; */
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $context->getStoreManager();
        $this->_urlInterface = $context->getUrlBuilder();
        $this->localeFormat = $localeFormat;
        $this->helper = $helper;

        parent::__construct($context,$data);
    }

    public function getWidth() {

        return $this->_scopeConfig->getValue(self::WIDTH,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getHeight() {

        return $this->_scopeConfig->getValue(self::HEIGHT,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductGeneralEnable() {
        return $this->_scopeConfig->getValue(self::ProductGeneralEnable,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getBaseUrl() {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function textEnable() {
        return $this->_scopeConfig->getValue(self::TEXT,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function quotesEnable() {
        return $this->_scopeConfig->getValue(self::QUOTE,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function clipartEnable() {
        return $this->_scopeConfig->getValue(self::CLIPART,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function uploadEnable() {
        return $this->_scopeConfig->getValue(self::UPLOAD,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function designTemplate() {
        return $this->_scopeConfig->getValue(self::TEMPLATE,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function myDesign() {
        return $this->_scopeConfig->getValue(self::MYDESIGN,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function shapeEnable() {
        return $this->_scopeConfig->getValue(self::SHAPE,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function MaskingEnabled() {
        return $this->_scopeConfig->getValue(self::Masking,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function DesigntemplatesEnable() {
        return $this->_scopeConfig->getValue(self::Designtemplates,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function downloaddesignenabled() {
        return $this->_scopeConfig->getValue(self::DownloadDesign,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getimageurl() {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function clipartLimit() {
        return $this->_scopeConfig->getValue(self::ClipartLimit,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function clipartImageLimit() {
        return $this->_scopeConfig->getValue(self::ClipartImageLimit,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function limitAlert() {
        return $this->_scopeConfig->getValue(self::LimitAlert,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProduct() {
        $product_id = $this->getRequest()->getParam('id');


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
        $product = $obj_product->load($product_id);



        return $product;
    }

    public function getJsonConfig() {
        $config = array();
        /* if (!$this->hasOptions()) {
          return Mage::helper('core')->jsonEncode($config);
          } */

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $_request = $objectManager->get('\Magento\Tax\Model\Calculation')->getRateRequest(false,
                false,
                false);
        //$_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        /* @var $product Mage_Catalog_Model_Product */
        $product = $this->getProduct();
        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = $objectManager->get('\Magento\Tax\Model\Calculation')->getRate($_request);

        $_request = $objectManager->get('\Magento\Tax\Model\Calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = $objectManager->get('\Magento\Tax\Model\Calculation')->getRate($_request);

        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();
        $taxprice = $objectManager->create('Magento\Catalog\Helper\Data');
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        if ($product->getTypeId() == 'bundle') {
            $_priceInclTax = $taxprice->getTaxPrice($product,
                    $_finalPrice,
                    true,
                    null,
                    null,
                    null,
                    null,
                    null,
                    false);
            $_priceExclTax = $taxprice->getTaxPrice($product,
                    $_finalPrice,
                    false,
                    null,
                    null,
                    null,
                    null,
                    null,
                    false);
        } else {
            $_priceInclTax = $taxprice->getTaxPrice($product,
                    $_finalPrice,
                    true);
            $_priceExclTax = $taxprice->getTaxPrice($product,
                    $_finalPrice);
        }
        $_tierPrices = array();
        $_tierPricesInclTax = array();
        foreach ($product->getTierPrice() as $tierPrice) {
            $_tierPrices[] = $priceHelper->currency($tierPrice['website_price'],
                    false,
                    false);
            /*$_tierPricesInclTax[] = $priceHelper->currency(
                    $taxprice->getTaxPrice->getPrice($product,
                            (int) $tierPrice['website_price'],
                            true),
                    false,
                    false);*/
            $_tierPricesInclTax[] = $priceHelper->currency(
            $taxprice->getTaxPrice($product, (int) $tierPrice['website_price'], true),
            false,
            false);
        }
        $locale = $objectManager->create('\Magento\Directory\Model\Currency');
        $config = array(
            'productId' => $this->getRequest()->getParam('id'),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'includeTax' => $this->helper->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax' => $this->helper->displayPriceIncludingTax(),
            'showBothPrices' => $this->helper->displayBothPrices(),
            'productPrice' => $priceHelper->currency($_finalPrice,
                    false,
                    false),
            'productOldPrice' => $priceHelper->currency($_regularPrice,
                    false,
                    false),
            'priceInclTax' => $priceHelper->currency($_priceInclTax,
                    false,
                    false),
            'priceExclTax' => $priceHelper->currency($_priceExclTax,
                    false,
                    false),
            /**
             * @var skipCalculate
             * @deprecated after 1.5.1.0
             */
            'skipCalculate' => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax' => $defaultTax,
            'currentTax' => $currentTax,
            'idSuffix' => '_clone',
            'oldPlusDisposition' => 0,
            'plusDisposition' => 0,
            'plusDispositionTax' => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition' => 0,
            'tierPrices' => $_tierPrices,
            'tierPricesInclTax' => $_tierPricesInclTax,
        );

//        $responseObject = new Varien_Object();
//        Mage::dispatchEvent('catalog_product_view_config', array('response_object' => $responseObject));
//        if (is_array($responseObject->getAdditionalOptions())) {
//            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
//                $config[$option] = $value;
//            }
//        }
        //return Mage::helper('core')->jsonEncode($config);
        return json_encode($config);
    }

    public function getLogoSrc() {
        if (empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = $this->_getLogoUrl();
        }
        return $this->_data['logo_src'];
    }

    protected function _getLogoUrl() {
        $folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $storeLogoPath = $this->_scopeConfig->getValue(
                'design/header/logo_src',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $storeLogoPath;
        $logoUrl = $this->_urlBuilder
                        ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;

        if ($storeLogoPath !== null) {
            $url = $logoUrl;
        } elseif ($this->getLogoFile()) {
            $url = $this->getViewFileUrl($this->getLogoFile());
        } else {
            $url = $this->getViewFileUrl('images/logo.svg');
        }
        return $url;
    }

    public function getLogoWidth() {
        if (empty($this->_data['logo_width'])) {
            $this->_data['logo_width'] = $this->_scopeConfig->getValue(
                    'design/header/logo_width',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return (int) $this->_data['logo_width'] ? : (int) $this->getLogoImgWidth();
    }

    /**
     * Retrieve logo height
     *
     * @return int
     */
    public function getLogoHeight() {
        if (empty($this->_data['logo_height'])) {
            $this->_data['logo_height'] = $this->_scopeConfig->getValue(
                    'design/header/logo_height',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return (int) $this->_data['logo_height'] ? : (int) $this->getLogoImgHeight();
    }

    

    /**
     * {@inheritdoc}
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    

}