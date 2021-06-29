<?php

namespace Biztech\Productdesigner\Block;

use Magento\Config\Block\System\Config\Form\Field;

class Designer extends Field {

    protected $_scopeConfig;
    protected $_urlInterface;
    protected $_storeManager;

    const ProductGeneralEnable = 'productdesigner/categoryproductsconfiguration/enablecategoryproducts';
    const ProductDefaultCategory = 'productdesigner/categoryproductsconfiguration/setdefaultcategory';
    const View = 'productdesigner/selectview/Selectview';
    const EnableFont = 'productdesigner/textconfiguration/enablegooglefonts';
    const FontList = 'productdesigner/textconfiguration/googlefontlist';
    const FontSize = 'productdesigner/textconfiguration/defaultfontsize';
    const QuotesCategory = 'productdesigner/quotesconfiguration/setdefaultquotescategory';
    const Confirmation = 'productdesigner/customimageuploadconfiguration/askforusersconfirmationbeforeuploadingimage';
    const ImageText = 'productdesigner/customimageuploadconfiguration/textforuserconfirmationbeforeuploadimage';
    const Instruction = 'productdesigner/customimageuploadconfiguration/showinstruction';
    const Instructiontext = 'productdesigner/customimageuploadconfiguration/instructiontext';
    const ShapesCategory = 'productdesigner/shapesconfiguration/setdefaultshapescategory';
    const ClipartCategory = 'productdesigner/clipartconfiguration/setdefaultclipartcategory';
    const MaskingCategory = 'productdesigner/maskingconfiguration/setdefaultmaskingcategory';
    const TemplateCategory = 'productdesigner/designtemplates/setdefaultcategory';

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, /* \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,  \Magento\Framework\UrlInterface $urlInterface,  \Magento\Store\Model\StoreManagerInterface $storeManager, */ array $data = []
    ) {
        //  $this->_scopeConfig = $scopeConfig;
        $this->_scopeConfig = $context->getScopeConfig();
        // $this->_storeManager = $storeManager;
        $this->_storeManager = $context->getStoreManager();
        //  $this->_urlInterface = $urlInterface;        
        $this->_urlInterface = $context->getUrlBuilder();
        parent::__construct($context, $data);
    }

    public function getProductGeneralEnable() {
        return $this->_scopeConfig->getValue(self::ProductGeneralEnable, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductDefaultCategory() {
        return $this->_scopeConfig->getValue(self::ProductDefaultCategory, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getView() {
        return $this->_scopeConfig->getValue(self::View, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getEnableFont() {
        return $this->_scopeConfig->getValue(self::EnableFont, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getFontList() {
        return $this->_scopeConfig->getValue(self::FontList, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getFontSize() {
        return $this->_scopeConfig->getValue(self::FontSize, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultQuotesCategory() {
        return $this->_scopeConfig->getValue(self::QuotesCategory, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function userConfirmation() {
        return $this->_scopeConfig->getValue(self::Confirmation, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function imageText() {
        return $this->_scopeConfig->getValue(self::ImageText, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showInstruction() {
        return $this->_scopeConfig->getValue(self::Instruction, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function InstructionText() {
        return $this->_scopeConfig->getValue(self::Instructiontext, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultShapesCategory() {
        return $this->_scopeConfig->getValue(self::ShapesCategory, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultClipartCategory() {
        return $this->_scopeConfig->getValue(self::ClipartCategory, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultMaskingCategory() {
        return $this->_scopeConfig->getValue(self::MaskingCategory, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultTempalateCategory() {
        return $this->_scopeConfig->getValue(self::TemplateCategory, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getbaseurl() {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

}
