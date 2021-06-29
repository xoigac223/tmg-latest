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
 * @package    ITORIS_M2_DYNAMIC_PRODUCT_OPTIONS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Bss\DynamicProductOptionsProductionCharge\Model\Rewrite\Option\Type;

class Select extends \Itoris\DynamicProductOptions\Model\Rewrite\Option\Type\Select
{
    public function getOptionPrice($optionValue, $basePrice) {
        $price = $this->_getOptionPrice($optionValue, $basePrice); //relative price

        if ($this->getItorisHelper()->isEnabledOnFrontend()) {
            $product = $this->getOption()->getProduct();
            $itemOption = $this->getConfigurationItemOption();
            if ($itemOption) $item = $itemOption->getItem(); else $item = false;
            if ($item && $this->checkProductionCharge($optionValue)) {

                if($item->getPriceProductionCharge()) {
                    $priceProductionCharge = $item->getPriceProductionCharge();
                }else {
                    $priceProductionCharge = [];
                }
                $priceProductionCharge[$optionValue] = $price;
                $item->setPriceProductionCharge($priceProductionCharge);
                $price = 0;
            }
            if ($item && $item->getOptionsAbsolutePricing() || !$item && $product->getOptionsAbsolutePricing()) return $price;
            $dpoObj = $this->_objectManager->create('Itoris\DynamicProductOptions\Model\Options')->setStoreId($product->getStoreId())->load($product->getId(), 'product_id');
            if (!$dpoObj->getConfigId()) $dpoObj->setStoreId(0)->load($product->getId(), 'product_id');
            if ($dpoObj->getAbsolutePricing() == 1) { //absolute price
                if ($product->getTypeId() == 'configurable') {
                    $price -= $basePrice;
                } else {
                    $price -= $product->getFinalPrice();
                }
                $product->setOptionsAbsolutePricing(1);
                if ($item) $item->setOptionsAbsolutePricing(1);
            } else if ($dpoObj->getAbsolutePricing() == 2) { //fixed price
                $price = 0;
            } else {
                $product->setOptionsAbsolutePricing(2);
                if ($item) $item->setOptionsAbsolutePricing(2);
            }
        }
        return $price;
    }

    public function checkProductionCharge($valueId){
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection('read');
        $option_value_table = $resource->getTableName('itoris_dynamicproductoptions_option_value');
        $storeId = $this->_objectManager->create('\Magento\Store\Model\StoreManagerInterface')->getStore()->getStoreId();
        $config = $connection->fetchOne("select `configuration` from {$option_value_table} where `orig_value_id` = ".floatval($valueId)." and store_id = ".intval($storeId));
        if (!$config && intval($storeId) > 0) $config = $connection->fetchOne("select `configuration` from {$option_value_table} where `orig_value_id` = ".floatval($valueId)." and store_id = 0");
        if (!$config) return false;
        $config = json_decode($config, true);
        $product = $this->getOption()->getProduct();
        $valueConfiguration = \Zend_Json::decode($this->_objectManager->create('Itoris\DynamicProductOptions\Model\Options')->load($product->getId())->getConfiguration());

        foreach($valueConfiguration as $val) {
            if ($val && isset($val['fields'])) {
                foreach ($val['fields'] as $v) {
                    if ($v && isset($v['option_id']) && ($config['option_id'] == $v['option_id']) && isset($v['html_args']) && $v['html_args'] == 'production_charges="true"') {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}