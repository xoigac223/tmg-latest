<?php

namespace Biztech\Productdesigner\Block;

class Group extends \Magento\Framework\View\Element\Template {

    protected $_scopeConfig;

    const EnableFont = 'productdesigner/textconfiguration/enablegooglefonts';
    const FontList = 'productdesigner/textconfiguration/googlefontlist';
    const FontSize = 'productdesigner/textconfiguration/defaultfontsize';
    const FontFamily = 'productdesigner/textconfiguration/defaultfont';

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context , /* \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig , */ \Magento\Framework\Registry $coreRegistry, array $data = []
    ) {
       // $this->_scopeConfig = $scopeConfig;
         $this->_scopeConfig = $context->getScopeConfig();
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
    }

    public function getProduct() {

        $product_id = $this->getRequest()->getParam('id');
        if ($product_id == null) {
            $product_id = $this->getRequest()->getParam('product_id');
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
        $product = $obj_product->load($product_id);
        return $product;
    }

    public function getAttributeCollection() {
        $product_id = $this->getRequest()->getParam('id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
        $product = $obj_product->load($product_id);
        return $product->getAttributes();
    }

    public function getJsonConfigSwatch() {
        $color = array();
        $size = array();
        $product_id = $this->getRequest()->getParam('id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $ids = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getChildrenIds($product_id);
        $sizeid = '';

        foreach ($ids[0] as $id) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
            $product = $obj_product->load($id);
            $attributes = $product->getAttributes();
            
            foreach ($attributes as $attribute) {
                if ($attribute['attribute_code'] == 'color') {
                    $colorid = $attribute['attribute_id'];
                }
                if ($attribute['attribute_code'] == 'size') {
                    $sizeid = $attribute['attribute_id'];
                }
            }
            if (!isset($color[$colorid][$product['color']])) {
                $color[$colorid][$product['color']] = $product['color'];
            }
            if (!isset($color[$sizeid][$product['size']])) {
                $color[$sizeid][$product['size']] = $product['size'];
            }
            $color[$sizeid][$product['size']] = $id;
        }
        
        return json_encode($color);
        
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

    public function getFontfamily() {
        return $this->_scopeConfig->getValue(self::FontFamily, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
