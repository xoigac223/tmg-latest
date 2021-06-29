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
 
namespace Itoris\ProductPriceFormula\Block;

class Formula extends \Magento\Backend\Block\Widget\Container
{
    protected $_template = 'Itoris_ProductPriceFormula::product/formula.phtml';
    
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }
    
    public function getCurrentProduct(){
        return $this->_coreRegistry->registry('product');
    }
    
    public function getDataHelper() {
        return $this->_objectManager->get('Itoris\ProductPriceFormula\Helper\Data');
    }
    
    public function getConditions($productId) {
        $conditions = $this->prepareConditions($productId);
        return $conditions;
    }

    protected function prepareConditions($productId) {
        $conditionCollection = $this->_objectManager->create('Itoris\ProductPriceFormula\Model\Condition')->getCollection();
        $conditionCollection->getSelect()->join(['settings' => $this->_objectManager->get('Magento\Framework\App\ResourceConnection')->getTableName('itoris_productpriceformula_formula')],
            'settings.formula_id = main_table.formula_id and settings.status=1 and settings.product_id = ' . $productId,
            ['active_from' => 'settings.active_from', 'active_to' => 'settings.active_to', 'run_always' => 'settings.run_always', 
            'apply_to_total' => 'settings.apply_to_total', 'frontend_total' => 'settings.frontend_total',
            'disallow_criteria' => 'settings.disallow_criteria', 'store_ids' => 'settings.store_ids']
        );
        $conditionCollection->getSelect()->order(['settings.position', 'main_table.position']);
        $formulas = [];
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection =  $resource->getConnection('read');
        $tableGroup = $resource->getTableName('itoris_productpriceformula_group');
        $storeId = (int)$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getStoreId();
        foreach ($conditionCollection as $model) {
            $formulaId = $model->getFormulaId();
            $conditionId = $model->getId();
            if ($model->getStoreIds() && $storeId) {
                $storeIds = explode(',', $model->getStoreIds());
                if (!in_array($storeId, $storeIds)) continue;
            }
            $groupIds = $connection->fetchAll("select group_id from {$tableGroup} where formula_id={$formulaId}");
            if (!array_key_exists($formulaId, $formulas)) {
                $formulas[$formulaId] = [
                    'formula_id' => $formulaId,
                    'active_from' => $model->getActiveFrom(),
                    'active_to'   => $model->getActiveTo(),
                    'run_always'  => $model->getRunAlways(),
                    'apply_to_total'  => $model->getApplyToTotal(),
                    'frontend_total'  => $model->getFrontendTotal(),
                    'disallow_criteria'  => (array) json_decode($model->getDisallowCriteria()),
                    'groups'      => $groupIds,
                    'conditions'  => []
                ];
            }
            if (!array_key_exists($conditionId, $formulas[$formulaId]['conditions'])) {
                $formulas[$formulaId]['conditions'][$conditionId] = [
                    'condition_id' => $conditionId,
                    'condition'    => $model->getCondition(),
                    'price'        => $model->getPrice(),
                    'weight'        => $model->getWeight()
                ];
            }
        }
        foreach ($formulas as $key => $condition) {
            $formulas[$key]['conditions'] = array_values($condition['conditions']);
        }
        $formulas = array_values($formulas);
        return $formulas;
    }

    public function getDataBySku($product) {
        $dataBySku = [];
        $allConditionPriceArray = [];
        $replaceVars = ['.qty}','.price}','.length}'];
        foreach ($this->getConditions($product->getId()) as $conditionData) {
            foreach ($conditionData['conditions'] as $value) {
                preg_match_all('/\{.*}/U', str_replace($replaceVars, '}', $value['condition']), $conditionMatch);
                $allConditionPriceArray[] = $conditionMatch[0];
                preg_match_all('/\{.*}/U', str_replace($replaceVars, '}', $value['price']), $priceMatch);
                $allConditionPriceArray[] = $priceMatch[0];
                preg_match_all('/\{.*}/U', str_replace($replaceVars, '}', $value['weight']), $weightMatch);
                $allConditionPriceArray[] = $weightMatch[0];
            }
            foreach ($conditionData['disallow_criteria'] as $value) {
                $value = (array) $value;
                preg_match_all('/\{.*}/U', str_replace($replaceVars, '}', $value['formula']), $conditionMatch);
                $allConditionPriceArray[] = $conditionMatch[0];
            }
        }
        $dataBySku = $this->prepareDataBySku($allConditionPriceArray, $product);
        return $dataBySku;
    }

    protected function prepareDataBySku($allConditionPriceArray, $product) {
        $dataBySku = [];
        foreach ($allConditionPriceArray as $value) {
            foreach ($value as $condition) {
                if (!array_key_exists($condition, $dataBySku)) {
                    $options = $product->getProductOptionsCollection();
                    if (count($options)) {
                        foreach ($options as $option) {
                            $optionSku = '{' . $option->getSku() . '}';
                            if ($optionSku == $condition) {
                                $dataBySku[$condition] = ['type' => $option->getType(), 'id' => $option->getId()];
                            } else {
                                $values  = $option->getValues();
                                if (count($values)) {
                                    foreach ($values as $value) {
                                        $optionSku = '{' . $value->getSku() . '}';
                                        if ($optionSku == $condition) {
                                            $dataBySku[$condition] = ['type' => $option->getType(), 'id' => $option->getId()];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $attributes = $product->getAttributes();
                    foreach ($attributes as $attribute) {
                        $attributeCode = $attribute->getAttributeCode();
                        $attributeType = $attribute->getBackendType();
                        $attributeCodeStr = '{' . $attributeCode . '}';
                        if ($attributeCodeStr == $condition && $attributeCodeStr != '{price}') {
                            $options = $attribute->getOptions();
                            $value = $product->getData($attributeCode);
                            if (!empty($options)) foreach($options as $option) {
                                if ((int)$option['value'] == (int)$value) {
                                    $value = $option['label'];
                                    break;
                                }
                            }
                            $dataBySku[$condition] = ['value' => $value];
                        }
                    }
                }
            }
        }
        return $dataBySku;
    }

    public function getOptions($product) {
        return $this->prepareOptions($product);
    }

    protected function prepareOptions($product) {
        $options = $product->getProductOptionsCollection();
        $productId = $product->getId();
        $optionsData = [];
        foreach ($options as $option) {
            $optionId = $option->getId();
            $values  = (array) $option->getValues();
            if (!array_key_exists($optionId, $optionsData)) {
                $optionsData[$optionId] = [
                    'sku'    => $option->getSku(),
                    'type'   => $option->getType(),
                    'id'     => $optionId,
                    'values' => [],
                    'price'    => (float) $option->getPrice()
                ];
            }
            foreach ($values as $value) {
                $valueId = $value->getId();
                if (!array_key_exists($valueId, $optionsData[$optionId]['values'])) {
                    $optionsData[$optionId]['values'][$valueId] = [
                        'sku' => $value->getSku(),
                        'id'  => $valueId,
                        'price'    => (float) $value->getPrice()
                    ];
                }
            }
        }
        $optionsData = array_values($optionsData);
        return $optionsData;
    }

    public function specialPrice($product) {
        return $product->getSpecialPrice();
    }
    
    public function getTierPrices() {
        $tierPrices = [];
        $product = $this->getCurrentProduct();
        $priceCurrency = $this->_objectManager->get('Magento\Framework\Pricing\PriceCurrencyInterface');
        $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
        foreach ($tierPricesList as $tierPrice) {
            $tierPrices[] = ['qty' => $tierPrice['price_qty'], 'price' => $priceCurrency->convert($tierPrice['website_price'])];
        }
        return $tierPrices;
    }
}