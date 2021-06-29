<?php

namespace Biztech\Productdesigner\Model\Adminhtml\Config\Source;

class FontFamily extends \Magento\Config\Block\System\Config\Form\Field {

    protected $_scopeConfig;
    const EnableFont = 'productdesigner/textconfiguration/enablegooglefonts';
    const FontList = 'productdesigner/textconfiguration/googlefontlist';
    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;                
    }

    public function toOptionArray() {
        $option_array = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Productdesignerfonts\Collection')->addFieldToFilter('disabled', 0)->setOrder('font_label', 'asc');
        $font_styles = $model->getData();        
        foreach ($font_styles as $font) {
            $option_array[] = array(
                'value' => $font['font_label'],
                'label' => $font['font_label']
            );
        }
        $isGoogleFontEnable = $this->_scopeConfig->getValue(self::EnableFont, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);        
        //$isGoogleFontEnable = Mage::getStoreConfig('productdesigner/text_general/enabled_google_fonts');
        if (isset($isGoogleFontEnable) && $isGoogleFontEnable) {
            $googleFontList = $this->_scopeConfig->getValue(self::FontList, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);                    
            foreach (explode(',', $googleFontList) as $googleFont) {
                $option_array[] = array(
                    'value' => $googleFont,
                    'label' => str_replace('+', ' ', $googleFont)
                );
            }
        }


        return $option_array;
    }

}
