<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_PRICE_FORMULA
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\ProductPriceFormula\Helper;

class Price extends Data
{

    public function getProductFinalPrice($item, $forReindex = false) {
        if (!$this->getDataHelper()->isEnabled()) return;
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection =  $resource->getConnection('read');
        /** @var $item Mage_Sales_Model_Quote_Item */
        $productId = (int)$item->getProductId();
        if (!$productId) return null;
        $options = $forReindex ? null : $this->_getBuyRequest($item)->getOptions();
        $optionsQty = $forReindex ? [] : (array) $this->_getBuyRequest($item)->getOptionsQty();
        $tableCondition = $resource->getTableName('itoris_productpriceformula_conditions');
        $tableSettings = $resource->getTableName('itoris_productpriceformula_formula');
        $tableGroup = $resource->getTableName('itoris_productpriceformula_group');
        $conditionData = $connection->fetchAll("
            select {$tableSettings}.*, {$tableCondition}.*, group_concat({$tableGroup}.group_id) as group_id from {$tableCondition}
            join {$tableSettings} on {$tableCondition}.formula_id={$tableSettings}.formula_id
            and {$tableSettings}.product_id={$productId} and {$tableSettings}.status=1
            left join {$tableGroup} on {$tableCondition}.formula_id={$tableGroup}.formula_id
            group by {$tableCondition}.condition_id
            order by {$tableSettings}.position, {$tableCondition}.position
        ");
        $conditionPrice = [];
        if (!count($conditionData)) return;
        
        $storeId = (int)$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getStoreId();
        $product = $item->getProduct()->load($productId);

        $optionData = [];
        if (is_array($options)) $optionData = $this->getOptionData($options, $optionsQty, $product);

        $dataBySku = (array) $this->getAttributeData($product);
        $price = $product->getFinalPrice((int)$item->getQty());
        $minPrice = $price;
        if ($product->getSpecialPrice()) {
            $minPrice = $product->getTierPrice($item->getQty()) ? min($product->getSpecialPrice(), $product->getTierPrice($item->getQty()), $product->getPrice()) : min($product->getSpecialPrice(), $product->getPrice());
        } else {
            $minPrice = $product->getTierPrice($item->getQty()) ? min($product->getTierPrice($item->getQty()), $product->getPrice()) : $product->getPrice();
        }
        //if ($minPrice && $minPrice < $price) $price = $minPrice;
        
        $dataBySku['{configured_price}'] = $price;
        $dataBySku['{initial_price}'] = $product->getPrice();
        $dataBySku['{special_price}'] = $product->getSpecialPrice();
        $dataBySku['{tier_price}'] = $product->getTierPrice($item->getQty());
        
        if ($item->getProductType() == 'configurable') {
            foreach((array) $item->getBuyRequest()->getSuperAttribute() as $key => $value) {
                $attr = $this->_objectManager->get('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->load($key);
                $label = $attr->getSource()->getOptionText($value);
                $dataBySku['{'.$attr->getAttributeCode().'}'] = $label;
            }
            $childs = $item->getChildren();
            if (count($childs)) {
                $dataBySku['{configurable_pid}'] = (int) $childs[0]->getProduct()->getId();
            } else $dataBySku['{configurable_pid}'] = 0;
        }
        
        $dataBySku['{qty}'] = $item->getQty();
        foreach($optionData as $key => $value) $dataBySku[$key] = $value;
        unset($dataBySku['{price}']);

        $productCurrentPrice = $price;
        $disallowCriteria = [];
        foreach ($conditionData as $value) {
            if ($value['store_ids'] && $storeId) {
                $storeIds = explode(',', $value['store_ids']);
                if (!in_array($storeId, $storeIds)) continue;
            }
            if (!$this->correctDate($value['active_from'], $value['active_to']) || !$this->customerGroup($value['group_id'])) continue;
            $conditionString = $value['condition'];
            $priceString = $value['price'];
            $weightString = $value['weight'];
            $overrideWeight = (int)$value['override_weight'];
            $disallowCriteria[(int) $value['formula_id']] = (array) json_decode($value['disallow_criteria']);
            $qtyInResult = strrpos($value['price'], '{qty}') !== false ? (int) $dataBySku['{qty}'] : 1;
            foreach ($dataBySku as $sku => $valueOption) {
                if ($valueOption != '' && !is_array($valueOption)) {
                    if (!is_numeric($valueOption)) $valueOption = '"'.addslashes($valueOption).'"';
                    $conditionString = str_ireplace($sku, $valueOption, $conditionString);
                    $priceString = str_ireplace($sku, $valueOption, $priceString);
                    if ($overrideWeight) $weightString = str_ireplace($sku, $valueOption, $weightString);
                    foreach($disallowCriteria[(int) $value['formula_id']] as $key => $criteria) {
                        $disallowCriteria[(int) $value['formula_id']][$key]->formula = str_ireplace($sku, $valueOption, $criteria->formula);
                    }
                }
            }
            $conditionString = str_ireplace('{price}', '@price', $conditionString);
            $priceString = str_ireplace('{price}', '@price', $priceString);
            $weightString = str_ireplace('{price}', '@price', $weightString);
            $conditionString = preg_replace('/\{(.*)}/U', 'false', $conditionString);
            $priceString = preg_replace('/\{(.*)}/U', '0', $priceString);
            $weightString = preg_replace('/\{(.*)}/U', '0', $weightString);
            foreach($disallowCriteria[(int) $value['formula_id']] as $key => $criteria) {
                $disallowCriteria[(int) $value['formula_id']][$key]->formula = str_ireplace('{price}', '@price', $criteria->formula);
                $disallowCriteria[(int) $value['formula_id']][$key]->formula = preg_replace('/\{(.*)}/U', '0', $criteria->formula);
            }
                    
            //JS -> PHP math constants conversion
            $map = ["E" => "M_E","LN2" => "M_LN2","LN10" => "M_LN10","LOG2E" => "M_LOG2E","LOG10E" => "M_LOG10E","PI" => "M_PI","SQRT1_2" => "M_SQRT1_2","SQRT2" => "M_SQRT2"];
            $conditionString = str_replace(array_keys($map), array_values($map), $conditionString);
            $priceString = str_replace(array_keys($map), array_values($map), $priceString);
            $weightString = str_replace(array_keys($map), array_values($map), $weightString);
            foreach($disallowCriteria[(int) $value['formula_id']] as $key => $criteria) {
                $disallowCriteria[(int) $value['formula_id']][$key]->formula = str_replace(array_keys($map), array_values($map), $criteria->formula);
            }
            
            preg_match_all('/\{.*}/U', $conditionString, $resultCond);
            if (!array_key_exists($value['formula_id'], $conditionPrice)) {
                $conditionPrice[$value['formula_id']] = [];
            }
            //if (empty($resultCond[0])) {
                $conditionPrice[$value['formula_id']][] = ['price' => $priceString, 'condition' => $conditionString, 'qty_in_result' => $qtyInResult, 'apply_to_total' => $value['apply_to_total'], 'override_weight' => $overrideWeight, 'weight' => $weightString];
            //} else {
            //    $conditionPrice[$value['formula_id']][] = ['price' => $priceString, 'condition' => false, 'qty_in_result' => $qtyInResult, 'apply_to_total' => $value['apply_to_total'], 'override_weight' => $overrideWeight, 'weight' => $weightString];
            //}
        }
        $priceForCompare = 0; $apply_to_total = false; $weight = $item->getWeight();
        foreach ($conditionPrice as $formulaId => $values) {
            $isRightCondition = false;
            foreach ($values as $value) {
                if (!$isRightCondition) {
                    $condition = str_ireplace('@price', $productCurrentPrice, $value['condition']);
                    $priceCond = str_ireplace('@price', $productCurrentPrice, $value['price']);

                    $qtyInResult = $value['qty_in_result'];
                    $apply_to_total = (int)$value['apply_to_total'];
                    if ($condition !== false && $condition == '') {
                        $condition = true;
                    }
                    if ($condition != '') {
                        $formulaFunc = create_function('&$isRightCondition, &$priceForCompare', 'if (' . $condition . ') {$isRightCondition=true; $priceForCompare = (' . $priceCond . ');}');
                        $formulaFunc($isRightCondition, $priceForCompare);
                        if ($priceForCompare > 0) $productCurrentPrice = $priceForCompare;
                        if ($isRightCondition && $value['override_weight']) {
                            $weightCond = str_ireplace('@price', $productCurrentPrice, $value['weight']);
                            $weightValue = create_function('&$weight', '$weight = '.$weightCond.';');
                            $weightValue($weight);
                        }
                    }
                } else {
                    continue;
                }
            }
        }
        $finalPrice = $priceForCompare > 0 ? $priceForCompare / ($apply_to_total ? $item->getQty() : 1) : null;

        if (!$forReindex && $item->getQuote()) {
            $hasError = false;
            foreach($disallowCriteria as $formula) {
                foreach($formula as $criteria) {
                    $validationFunc = create_function('&$hasError', 'if (' . $criteria->formula . ') $hasError = true;');
                    $validationFunc($hasError);
                    
                    if ($hasError) {
                        $item->setPriceFormulaError($criteria->message);
                        $item->getQuote()->setHasError(true);
                        break 2;
                    }
                }
            }
        }
    
        $item->setWeight($weight);
        
        return $finalPrice;

    }

    public function _getBuyRequest($item) {
        $option = $item->getOptionByCode('info_buyRequest');
        if ($option) {
            $value = json_decode($option->getValue(), true); //in M2.2 json used for the buy request
            if (is_null($value)) $value = unserialize($option->getValue()); //in M<2.2 the buy request is serialized
        } else $value = [];

        $buyRequest = new \Magento\Framework\DataObject($value);

        // Overwrite standard buy request qty, because item qty could have changed since adding to quote
        $buyRequest->setOriginalQty($buyRequest->getQty())
            ->setQty($item->getQty() * 1);

        return $buyRequest;
    }

    protected function getOptionData($options, $optionsQty, $product) {
        $valueBySku = [];
        foreach ($options as $optionId => $optionValue) {
            $optionData = $product->getOptionById($optionId);
            if (is_array($optionValue) && $optionData) {
                foreach ($optionValue as $subOptionId) {
                    if ($optionData->getValues()) {
                        foreach ($optionData->getValues() as $subOptionData) {
                            if ($subOptionData->getOptionTypeId() == (int)$subOptionId) {
                                $valueBySku['{' . $subOptionData->getSku() . '}'] = $subOptionData->getTitle();
                                $valueBySku['{' . $subOptionData->getSku() . '.qty}'] = isset($optionsQty[$optionId][$subOptionId]) ? (int) $optionsQty[$optionId][$subOptionId] : 0;
                                $valueBySku['{' . $subOptionData->getSku() . '.price}'] = $subOptionData->getPrice();
                                $valueBySku['{' . $subOptionData->getSku() . '.length}'] = strlen(trim($subOptionData->getTitle()));
                            }
                        }
                    }

                }
            } else if (is_object($optionData)) {
                if (!is_null($optionData->getSku()) && $optionData->getSku()) {
                    $valueBySku['{' . $optionData->getSku() . '}'] = $optionValue;
                    $valueBySku['{' . $optionData->getSku() . '.qty}'] = 0;
                    $valueBySku['{' . $optionData->getSku() . '.price}'] = $optionData->getPrice();
                    $valueBySku['{' . $optionData->getSku() . '.length}'] = strlen(trim($optionValue));

                } else {
                    if ($optionData->getValues()) {
                        foreach ($optionData->getValues() as $subOptionData) {
                            if ($subOptionData->getOptionTypeId() == (int)$optionValue) {
                                $valueBySku['{' . $subOptionData->getSku() . '}'] = $subOptionData->getTitle();
                                $valueBySku['{' . $subOptionData->getSku() . '.qty}'] = isset($optionsQty[$optionId]) ? (int) $optionsQty[$optionId] : 0;
                                $valueBySku['{' . $subOptionData->getSku() . '.price}'] = $subOptionData->getPrice();
                                 $valueBySku['{' . $subOptionData->getSku() . '.length}'] = strlen(trim($subOptionData->getTitle()));
                            }
                        }
                    }
                }
            }
        }
        return $valueBySku;
    }

    protected function getAttributeData($product) {
        $attributes = $product->getAttributes();
        $valueBySku = [];
        foreach ($attributes as $attribute) {
            try {
                $attributeCode = $attribute->getAttributeCode();
                $attributeCodeStr = '{' . $attributeCode . '}';
                $options = $attribute->getOptions();
                $value = $product->getData($attributeCode);
                if (!empty($options)) foreach($options as $option) {
                    if ((int)$option['value'] == (int)$value) {
                        $value = $option['label'];
                        break;
                    }
                }
                $valueBySku[$attributeCodeStr] = $value;
            } catch (\Exception $e) { }

        }
        return $valueBySku;
    }
    
    public function getDataHelper() {
        return $this->_objectManager->get('Itoris\ProductPriceFormula\Helper\Data');
    }
}