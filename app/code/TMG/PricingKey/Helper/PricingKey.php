<?php

namespace TMG\PricingKey\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option\Repository as OptionRepository;

//use CollectionInter

class PricingKey extends AbstractHelper
{
    const PRODUCT_ATTRIBUTE_PRICING_KEY_ARRAY = 'pricingkeyarray';
    
    const ITEM_ATTRIBUTE_PRICING_KEY = 'tmg_pricing_key';
    
    const XML_CONFIG_OPTION_LABEL_PRINT_METHOD = 'tmg_pricing_key/option_label/print_method';
    
    const XML_CONFIG_OPTION_LABEL_BUY_OPTION = 'tmg_pricing_key/option_label/buy_option';
    
    const XML_CONFIG_OPTION_LABEL_COLOR_CHARGE = 'tmg_pricing_key/option_label/color_charge';
    
    const XML_CONFIG_OPTION_VALUE_LABEL_ORDER_SAMPLE = 'tmg_pricing_key/option_value_label/order_sample';
    
    const XML_CONFIG_OPTION_VALUE_LABEL_COLOR_CHARGE = 'tmg_pricing_key/option_value_label/color_charge';
    
    const ORDER_SAMPLE_PRICING_KEY = 'RS';
    
    /**
     * @var OptionRepository
     */
    protected $optionRepository;
    
    public function __construct(
        Context $context,
        OptionRepository $optionRepository
    )
    {
        parent::__construct($context);
        $this->optionRepository = $optionRepository;
    }
    
    public function getPricingKeyOptions($product)
    {
        $result = array_values($this->getPricingKeyMapping($product));
        //$result[] = self::ORDER_SAMPLE_PRICING_KEY;
        return $result;
    }
    
    public function getColorChargeOptionLabel()
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_OPTION_LABEL_COLOR_CHARGE);
    }
    
    public function getBuyOptionOptionLabel()
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_OPTION_LABEL_BUY_OPTION);
    }
    
    public function getPrintMethodOptionLabel()
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_OPTION_LABEL_PRINT_METHOD);
    }
    
    public function getOrderSampleOptionValueLabel()
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_OPTION_VALUE_LABEL_ORDER_SAMPLE);
    }

    public function getColorChargeOptionValueLabel()
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_OPTION_VALUE_LABEL_COLOR_CHARGE);
    }
    
    public function isOrderSampleOptionValueLabel($label)
    {
        return $this->compareLabel($this->getOrderSampleOptionValueLabel(),$label);
    }
    
    /**
     * @param $product
     * @param bool $includeSample
     * @return array
     */
    public function getPricingKeyMapping($product, $includeSample = false)
    {
        $result = [];
        $pricingKeyStr = $this->getPricingKeyArrayString($product);
        if($pricingKeyStr) {
            foreach(explode('||',$pricingKeyStr) as $row) {
                $rowArr = explode('|',$row);
                if(count($rowArr) < 3) {
                    continue;
                }
                $key = ($rowArr[0] == 'See Price Method') ? $rowArr[1] : $rowArr[0];
                // DIRTY FIX!
                $key = ($key == 'Embroidered - Up to 8,000 Stitches') ? 'Embroidery, up to 8000 Stitches': $key;
                $result[$key] =  @$rowArr[2];
            }
            if($includeSample) {
                $result[$this->getOrderSampleOptionValueLabel()] = self::ORDER_SAMPLE_PRICING_KEY;
            }
        }
        return $result;
    }
    
    /**
     * @param $product
     * @param $label
     * @return int|null|string
     */
    public function getPricingKeyByLabel($product,$label)
    {
        foreach ($this->getPricingKeyMapping($product) as $pricingKeyLabel => $pricingKey) {
            if ($this->compareLabel($label,$pricingKeyLabel)) {
                return $pricingKey;
            }
        }
        return null;
    }
    
    /**
     * @todo Add Support For Data Object
     *
     * @param $product
     * @return mixed
     */
    public function getPricingKeyArrayString($product)
    {
        return $product->getData(self::PRODUCT_ATTRIBUTE_PRICING_KEY_ARRAY);
    }
    
    public function isTmgPrintMethodOption(\Magento\Catalog\Model\Product\Option $option)
    {
        return $this->compareLabel($option->getTitle(),$this->getPrintMethodOptionLabel());
    }
    
    public function isTmgBuyOptionOption(\Magento\Catalog\Model\Product\Option $option)
    {
        return $this->compareLabel($option->getTitle(),$this->getBuyOptionOptionLabel());
    }
    
    public function isTmgColorChargeOption(\Magento\Catalog\Model\Product\Option $option)
    {
        return $this->compareLabel($option->getTitle(),$this->getColorChargeOptionLabel());
    }
    
    /**
     * @ToDo Implement a better comparision
     *
     * @param $labelA
     * @param $labelB
     * @return bool
     */
    public function compareLabel($labelA,$labelB)
    {
        $cLabelA = $this->cleanLabel($labelA);
        $cLabelB = $this->cleanLabel($labelB);
        $result = ($cLabelA == $cLabelB);
//        if($result) {
//            $this->_logger->info(':: COMPARE :: "' . $cLabelA . '" VS "' . $cLabelB .'"');
//        }
        return $result;
    }
    
    protected function cleanLabel($label)
    {
        return str_replace(['/','-','_',',','.',''],'',mb_strtolower($label));
    }
    
    /**
     * @param QuoteItem $item
     * @param Product $product
     */
    public function setItemProductData(QuoteItem $item,Product $product)
    {
        if($this->shouldSkipSetProductAction()) {
            return;
        }
        
        if($pricingKey = $this->getItemPricingKey($item,$product)) {
            $item->setData(self::ITEM_ATTRIBUTE_PRICING_KEY, $pricingKey);
        }
    
    }
    
    public function getItemPricingKey(QuoteItem $item, Product $product)
    {
        $options        = $this->optionRepository->getProductOptions($product,true);
        $optionValues   = $item->getBuyRequest()->getOptions();
        $optionIds      = array_keys($optionValues);
        $buyOptionOption = null;
        $buyOptionValueId = null;
        $printMethodOption = null;
        $printMethodValueId = null;

        if(!$pricingKeyMapping = $this->getPricingKeyMapping($product)) {
            return null;
        }
    
        // Getting Options
        foreach ($options as $option) {
            /** @var \Magento\Catalog\Model\Product\Option $option  */
            if($buyOptionOption && $printMethodOption) {
                break;
            }
            if(!in_array($option->getOptionId(), $optionIds)) {
                continue;
            }
            if($this->isTmgBuyOptionOption($option)) {
                $buyOptionOption = $option;
            }
            if($this->isTmgPrintMethodOption($option)) {
                $printMethodOption = $option;
            }
            
        }
    
        // Skip Invalid Options
        if(!$buyOptionOption || !$printMethodOption) {
            return null;
        }
    
        // Loading Values
        // Sample Case
        if(!$buyOptionValue = $buyOptionOption->getValueById($optionValues[$buyOptionOption])) {
            return null;
        }
        if ($buyOptionValue->getTitle() == $this->getOrderSampleOptionValueLabel()) {
            return self::ORDER_SAMPLE_PRICING_KEY;
        }
        
        // Regular Case
        if(!$printMethodValue = $printMethodOption->getValueById($optionValues[$printMethodOption->getOptionId()])) {
            return null;
        }
//        $this->_logger->warning(print_r($pricingKeyMapping,true));
//        $this->_logger->warning(print_r([$buyOptionValue->getTitle(),$printMethodValue->getTitle(),$pricingKeyMapping],true));
        
        return isset($pricingKeyMapping[$printMethodValue->getTitle()])
            ? $pricingKeyMapping[$printMethodValue->getTitle()] : null;
        
    
    }



    public function getItemPricingKeyForShipping($optios, Product $product)
    {
        $result         = [];
        $pricingKeyStr  = $this->getPricingKeyArrayString($product);
        if($pricingKeyStr) 
        {
            $pricekeyArr = explode('||',$pricingKeyStr);
            $pricekeyRow = end($pricekeyArr);
            $rowArr      = explode('|',$pricekeyRow);
            return isset($rowArr[2])?$rowArr[2]:self::ORDER_SAMPLE_PRICING_KEY; 
        }
        else {
            return self::ORDER_SAMPLE_PRICING_KEY;
        }
        
        $options            = $this->optionRepository->getProductOptions($product,true);
        $optionIds          = array_keys($options);
        $buyOptionOption    = null;
        $buyOptionValueId   = null;
        $printMethodOption  = null;
        $printMethodValueId = null;
        $buyOptionValueTitle='';

        if(!$pricingKeyMapping = $this->getPricingKeyMapping($product)) {
            return null;
        }
        // Getting Options
         
        foreach ($options as $option) {
            /** @var \Magento\Catalog\Model\Product\Option $option  */  
            if(!in_array($option->getId(),$optionIds)) {
                continue;
            }           
            if($this->isTmgBuyOptionOption($option)) {   
                $buyOptionValueId   =  isset($optios[$option->getId()])?$optios[$option->getId()]:'';
                foreach ($option->getValues() as $buyOptionvalue) 
                {
                    if($buyOptionValueId == $buyOptionvalue->getData('option_type_id')){
                        $buyOptionValueTitle = $buyOptionvalue->getData('default_title');
                    }                    
                }
            }
            if($this->isTmgPrintMethodOption($option)) {
                $printMethodOption          = $option;
                $printOptionValueId         = isset($optios[$option->getId()])?$optios[$option->getId()]:'';
                foreach ($option->getValues() as $printOptionvalue) 
                {
                    if($printOptionValueId == $printOptionvalue->getData('option_type_id')){
                        $printMethodValue = $printOptionvalue->getData('default_title');
                    }                    
                }
            }
            
        }

           
       
        // Skip Invalid Options
        if(!$buyOptionValueId || !$printOptionValueId) {
            return null;
        }
        
        // Loading Values
        // Sample Case
        if(!$buyOptionValueId) {
            return null;
        }
        if ($buyOptionValueTitle && $buyOptionValueTitle == $this->getOrderSampleOptionValueLabel()) {
            return self::ORDER_SAMPLE_PRICING_KEY;
        }

        // Regular Case
        if(!$printOptionValueId) {
            return null;
        }
//        $this->_logger->warning(print_r($pricingKeyMapping,true));
//        $this->_logger->warning(print_r([$buyOptionValue->getTitle(),$printMethodValue->getTitle(),$pricingKeyMapping],true));
        
        return isset($pricingKeyMapping[$printMethodValue])
            ? $pricingKeyMapping[$printMethodValue] : null;
        
    
    }

    
    public function shouldSkipSetProductAction()
    {
        if($this->_getRequest()->getActionName() == 'load'
            && $this->_getRequest()->getModuleName() == 'customer') {
            return true;
        };
        return false;
    }
    
    
}
