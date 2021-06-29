<?php

namespace Themagnet\Productimport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Unserialize\Unserialize;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Class Themagnet\Productimport\Helper\Data
 */
class Data extends AbstractHelper
{
    const XPATH_IMAGE_SOURCE_PATH = 'themagnet/general/ftp_image_source_path';
    const XPATH_BLANK_PRODUCT_SKU = 'themagnet/general/blank_product_sku';
    const XPATH_BLANK_VARIATION_PRODUCT_SKUS = 'themagnet/general/blank_with_variations_product_sku';

    /**
     * Function getConfig
     *
     * @param  string $config_path
     * @return mixed
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue($config_path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Function getImageSourcePath
     *
     * @return string
     */
    public function getImageSourcePath()
    {
        return $this->getConfig(self::XPATH_IMAGE_SOURCE_PATH);
    }

    /**
     * Method getBlankProductSkus
     *
     * @return array|false
     */
    public function getBlankProductSkus()
    {
        $value = $this->scopeConfig->getValue(self::XPATH_BLANK_PRODUCT_SKU, ScopeInterface::SCOPE_STORE);

        if (empty($value)) {
            return false;
        }

        if ($this->isSerialized($value)) {
            $unserializer = ObjectManager::getInstance()->get(Unserialize::class);
        } else {
            $unserializer = ObjectManager::getInstance()->get(Json::class);
        }

        $products = $unserializer->unserialize($value);
        return $this->getProductSkuArray($products);
    }

    /**
     * Method getBlankVariationProductSkus
     *
     * @return array|false
     */
    public function getBlankVariationProductSkus()
    {
        $value = $this->scopeConfig->getValue(self::XPATH_BLANK_VARIATION_PRODUCT_SKUS, ScopeInterface::SCOPE_STORE);

        if (empty($value)) {
            return false;
        }

        if ($this->isSerialized($value)) {
            $unserializer = ObjectManager::getInstance()->get(Unserialize::class);
        } else {
            $unserializer = ObjectManager::getInstance()->get(Json::class);
        }

        $products = $unserializer->unserialize($value);
        return $this->getProductSkuArray($products);
    }

    /**
     * Method getProductSkuArray
     *
     * @param $products
     * @return array
     */
    public function getProductSkuArray($products)
    {
        $values = [];
        if (count($products)>0) {
            foreach ($products as $sku) {
                $values[] = $sku['blank_sku'];
            }
        }
        return $values;
    }

    /**
     * Method isSerialized
     *
     * @param $value
     * @return bool
     */
    private function isSerialized($value)
    {
        return (boolean) preg_match('/^((s|i|d|b|a|O|C):|N;)/', $value);
    }
}
